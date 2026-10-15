<?php

namespace App\Imports;

use App\Models\Admission;
use App\Models\Master\MasterStandard;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\ToCollection;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

/**
 * Imports school class lists in the format:
 *
 *   ULTRA ENGLISH SCHOOL      1-A                       <- title row: standard + division
 *   NAME | GR No. | PHONE | BIRTHDATE | ADMDATE | MOTHERNAME | CAST | RELIGION
 *   BALAS DHIYA DINESHBHAI | 2502 | 9409448248 | 16-03-20 | 06-05-2026 | CHAMPABEN | KOLI (GHEDIYA) | Hindu
 *
 * A sheet may contain several title/header blocks; each block applies to the rows below it.
 * NAME is split as "<first_name> <last_name> <father_name...>".
 */
class StudentImport implements ToCollection
{
    public $report = [
        'inserted' => [],
        'skipped'  => [],
        'errors'   => [],
    ];

    protected array $columnAliases = [
        'name'         => ['name', 'studentname', 'fullname'],
        'gr_no'        => ['grno', 'gr', 'grnumber', 'grnum'],
        'mobile_no'    => ['phone', 'phoneno', 'mobile', 'mobileno', 'contact', 'contactno'],
        'dob'          => ['birthdate', 'dob', 'dateofbirth', 'bdate'],
        'admission'    => ['admdate', 'admissiondate', 'doa', 'dateofadmission'],
        'mother_name'  => ['mothername', 'mother'],
        'father_name'  => ['fathername', 'father'],
        'cast'         => ['cast', 'caste'],
        'religion'     => ['religion'],
        'category'     => ['category'],
        'gender'       => ['gender', 'sex'],
        'aadhaar'      => ['aadhaar', 'aadhar', 'aadharno', 'aadhaarno', 'aadharcardno', 'aadhaarcardno'],
        'standard'     => ['std', 'standard', 'class'],
        'division'     => ['div', 'division'],
        'address'      => ['address', 'permanentaddress'],
    ];

    /** @var string[] */
    protected array $standards = [];

    /** @var array<string,bool> */
    protected array $seenGrNos = [];

    public function collection(Collection $rows)
    {
        $this->standards = MasterStandard::where('status', 'active')->orderBy('id')->pluck('name')->filter()->values()->all();

        $columnMap = null;
        $standard = null;
        $division = null;
        $titleStandardRaw = null;

        foreach ($rows as $index => $row) {
            $excelRow = $index + 1;
            $cells = array_map(fn($v) => is_string($v) ? trim($v) : $v, $row->toArray());
            $nonEmpty = array_values(array_filter($cells, fn($v) => $v !== null && $v !== ''));

            if (empty($nonEmpty)) {
                continue;
            }

            if ($this->isHeaderRow($cells)) {
                $columnMap = $this->buildColumnMap($cells);
                continue;
            }

            if (count($nonEmpty) <= 3 && ($title = $this->parseTitle(implode(' ', array_map('strval', $nonEmpty))))) {
                $titleStandardRaw = $title['standard'];
                $standard = $this->resolveStandard($title['standard']);
                $division = $title['division'];
                $columnMap = null;
                continue;
            }

            if ($columnMap === null) {
                continue;
            }

            $get = fn(string $key) => isset($columnMap[$key]) ? ($cells[$columnMap[$key]] ?? null) : null;

            $fullName = $this->cleanText($get('name'));
            $grNo = $this->cleanNumberText($get('gr_no'));

            if ($fullName === null && $grNo === null) {
                continue;
            }

            $rowStandard = $standard;
            $rowStandardRaw = $titleStandardRaw;
            if ($this->cleanText($get('standard')) !== null) {
                $rowStandardRaw = $this->cleanText($get('standard'));
                $rowStandard = $this->resolveStandard($rowStandardRaw);
            }
            $rowDivision = $this->cleanText($get('division')) ?? $division;

            if ($fullName === null) {
                $this->skip($excelRow, $grNo, 'Name is missing');
                continue;
            }
            if ($grNo === null) {
                $this->skip($excelRow, $grNo, 'GR No. is missing (' . $fullName . ')');
                continue;
            }
            if ($rowStandardRaw !== null && $rowStandard === null) {
                $this->skip($excelRow, $grNo, 'Standard "' . $rowStandardRaw . '" not found in Standard Master (' . $fullName . ')');
                continue;
            }
            if (isset($this->seenGrNos[$grNo]) || Admission::where('gr_no', $grNo)->exists()) {
                $this->skip($excelRow, $grNo, 'GR No. ' . $grNo . ' already exists (' . $fullName . ')');
                continue;
            }

            $nameParts = preg_split('/\s+/', mb_strtoupper($fullName));
            $fatherName = $this->cleanText($get('father_name'));

            try {
                Admission::create([
                    'gr_no'             => $grNo,
                    'first_name'        => $nameParts[0],
                    'last_name'         => $nameParts[1] ?? null,
                    'father_name'       => $fatherName !== null ? mb_strtoupper($fatherName) : (count($nameParts) > 2 ? implode(' ', array_slice($nameParts, 2)) : null),
                    'mother_name'       => $this->upperOrNull($get('mother_name')),
                    'mobile_no'         => $this->cleanNumberText($get('mobile_no')),
                    'date_of_birth'     => $this->parseDate($get('dob')),
                    'admission_date'    => $this->parseDate($get('admission')),
                    'admission_std'     => $rowStandard,
                    'current_std'       => $rowStandard,
                    'division'          => $rowDivision !== null ? mb_strtoupper($rowDivision) : null,
                    'cast'              => $this->upperOrNull($get('cast')),
                    'religion'          => $this->cleanText($get('religion')),
                    'category'          => $this->cleanText($get('category')),
                    'gender'            => $this->normalizeGender($get('gender')),
                    'aadhar_card_no'    => $this->cleanNumberText($get('aadhaar')),
                    'permanent_address' => $this->cleanText($get('address')),
                    'created_by'        => Auth::id(),
                ]);

                $this->seenGrNos[$grNo] = true;
                $this->report['inserted'][] = ['row' => $excelRow, 'id' => $grNo];
            } catch (\Exception $e) {
                $this->report['errors'][] = [
                    'row' => $excelRow,
                    'id' => $grNo,
                    'message' => $fullName . ': ' . $e->getMessage(),
                ];
            }
        }
    }

    protected function skip(int $row, $grNo, string $reason): void
    {
        $this->report['skipped'][] = ['row' => $row, 'id' => $grNo, 'reason' => $reason];
    }

    protected function normalizeHeader($value): string
    {
        return preg_replace('/[^a-z0-9]/', '', mb_strtolower((string) $value));
    }

    protected function isHeaderRow(array $cells): bool
    {
        $headers = array_map(fn($v) => $this->normalizeHeader($v), $cells);
        $hasName = (bool) array_intersect($headers, $this->columnAliases['name']);
        $hasGr = (bool) array_intersect($headers, $this->columnAliases['gr_no']);
        return $hasName && $hasGr;
    }

    protected function buildColumnMap(array $cells): array
    {
        $map = [];
        foreach ($cells as $col => $value) {
            $header = $this->normalizeHeader($value);
            if ($header === '') {
                continue;
            }
            foreach ($this->columnAliases as $field => $aliases) {
                if (!isset($map[$field]) && in_array($header, $aliases, true)) {
                    $map[$field] = $col;
                    break;
                }
            }
        }
        return $map;
    }

    /**
     * "ULTRA ENGLISH SCHOOL 1-A" => ['standard' => '1', 'division' => 'A']
     * "BALVATIKA - B"            => ['standard' => 'BALVATIKA', 'division' => 'B']
     * "ULTRA ENGLISH SCHOOL SKG-A" => ['standard' => 'SKG', 'division' => 'A']
     */
    protected function parseTitle(string $text): ?array
    {
        if (preg_match('/(bal\s*vatika|\d{1,2}(?:st|nd|rd|th)?|[a-z.]+)\s*[-\/]\s*([a-z])\s*$/i', trim($text), $m)) {
            $standard = strtoupper(preg_replace('/[\s.]+/', '', $m[1]));
            if ($this->resolveStandard($standard) === null && !preg_match('/^\d/', $standard)) {
                return null;
            }
            return ['standard' => $standard, 'division' => strtoupper($m[2])];
        }
        return null;
    }

    /**
     * Match "1", "1st", "Balvatika", "9 English" etc. against Standard Master names.
     */
    protected function resolveStandard(?string $raw): ?string
    {
        if ($raw === null || trim($raw) === '') {
            return null;
        }
        $needle = $this->normalizeHeader($raw);

        foreach ($this->standards as $name) {
            if ($this->normalizeHeader($name) === $needle) {
                return $name;
            }
        }

        if (preg_match('/^(\d{1,2})/', $needle, $m)) {
            foreach ($this->standards as $name) {
                if (preg_match('/^' . $m[1] . '(st|nd|rd|th)?\b/i', trim($name))) {
                    return $name;
                }
            }
        }

        foreach ($this->standards as $name) {
            if (str_starts_with($this->normalizeHeader($name), $needle)) {
                return $name;
            }
        }

        return null;
    }

    protected function cleanText($value): ?string
    {
        if ($value === null) {
            return null;
        }
        $value = trim(preg_replace('/\s+/', ' ', (string) $value));
        return $value === '' ? null : $value;
    }

    protected function upperOrNull($value): ?string
    {
        $value = $this->cleanText($value);
        return $value === null ? null : mb_strtoupper($value);
    }

    /**
     * Excel stores phone / GR numbers as floats; keep them as plain digit strings.
     */
    protected function cleanNumberText($value): ?string
    {
        if (is_float($value) || is_int($value)) {
            return number_format($value, 0, '', '');
        }
        return $this->cleanText($value);
    }

    protected function normalizeGender($value): ?string
    {
        $value = mb_strtolower((string) $this->cleanText($value));
        return match (true) {
            in_array($value, ['m', 'male', 'boy', 'b'], true) => 'Male',
            in_array($value, ['f', 'female', 'girl', 'g'], true) => 'Female',
            in_array($value, ['o', 'other'], true) => 'Other',
            default => null,
        };
    }

    /**
     * Accepts Excel serial dates and dd-mm-yy / dd-mm-yyyy (also with / or .).
     */
    protected function parseDate($raw): ?string
    {
        if ($raw === null || trim((string) $raw) === '') {
            return null;
        }

        if (is_numeric($raw)) {
            try {
                return ExcelDate::excelToDateTimeObject($raw)->format('Y-m-d');
            } catch (\Exception $e) {
                return null;
            }
        }

        $str = trim((string) $raw);
        if (preg_match('/^(\d{1,2})[-\/\.](\d{1,2})[-\/\.](\d{2}|\d{4})$/', $str, $m)) {
            $year = (int) $m[3];
            if (strlen($m[3]) === 2) {
                $year += ($year <= (int) date('y') + 1) ? 2000 : 1900;
            }
            if (checkdate((int) $m[2], (int) $m[1], $year)) {
                return sprintf('%04d-%02d-%02d', $year, $m[2], $m[1]);
            }
            return null;
        }

        try {
            return Carbon::parse($str)->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }
}
