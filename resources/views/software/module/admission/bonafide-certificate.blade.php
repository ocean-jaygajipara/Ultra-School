<!DOCTYPE html>
<html lang="en">

<head>
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('admin/assets/images/favicon/favicon-32.png') }}" />

    <meta charset="UTF-8">
    <title>Bonafide Certificate - Ultra English School</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@600;700;800;900&family=Lora:ital,wght@0,400;0,500;0,600;0,700;1,400;1,600&family=Merriweather:ital,wght@0,400;0,700;0,900;1,400&family=Playfair+Display:ital,wght@0,700;0,800;0,900;1,700&display=swap" rel="stylesheet">
    <style>
        @page {
            margin: 8mm 10mm;
        }

        * {
            box-sizing: border-box;
        }

        html,
        body {
            margin: 0;
            padding: 0;
            background: #eef2f6;
            color: #000;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
            -webkit-font-smoothing: antialiased;
        }


        /* Certificate Container */
        .certificate-container {
            width: 100%;
            max-width: 820px;
            margin: 24px auto 40px;
            background: #fff;
            padding: 12px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
            border-radius: 4px;
        }

        .certificate-inner {
            border: 2.5px solid #000;
            padding: 24px 34px 28px;
            background: #fff;
            position: relative;
            overflow: hidden;
        }

        .certificate-inner > *:not(.cert-watermark) {
            position: relative;
            z-index: 1;
        }

        .cert-watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            width: 62%;
            max-width: 480px;
            transform: translate(-50%, -45%);
            opacity: 0.08;
            pointer-events: none;
            user-select: none;
            z-index: 0;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        /* Default Font Palette: Attractive Classic Serif (Cinzel + Lora) */
        .certificate-container.font-attractive,
        .certificate-container {
            font-family: 'Lora', 'Merriweather', Georgia, 'Times New Roman', serif;
        }

        .certificate-container.font-attractive .brand-ultra,
        .certificate-container.font-attractive .brand-school,
        .certificate-container.font-attractive .header-place,
        .certificate-container.font-attractive .title-box {
            font-family: 'Cinzel', 'Playfair Display', 'Merriweather', Georgia, serif;
        }

        .certificate-container.font-attractive .cert-body,
        .certificate-container.font-attractive .student-name-line,
        .certificate-container.font-attractive .dob-words-line,
        .certificate-container.font-attractive .footer-section {
            font-family: 'Lora', 'Merriweather', Georgia, 'Times New Roman', serif;
        }


        /* Letterhead */
        .letterhead {
            display: flex;
            align-items: center;
            gap: 18px;
            padding-bottom: 12px;
        }

        .letterhead-logo {
            width: 92px;
            height: 92px;
            flex-shrink: 0;
            object-fit: contain;
        }

        .letterhead-text {
            flex: 1;
            text-align: center;
        }

        .brand-school {
            color: #1f2580;
            font-size: 26px;
            font-weight: 900;
            letter-spacing: 1.2px;
            line-height: 1.2;
            text-transform: uppercase;
            white-space: nowrap;
        }

        .letterhead-meta {
            width: 92px;
            flex-shrink: 0;
        }

        .cert-divider {
            height: 6px;
            margin: 0 0 24px;
            border-top: 3px solid #1f2580;
            border-bottom: 1px solid #1f2580;
        }

        /* Title Box */
        .title-box-wrap {
            text-align: center;
            margin-bottom: 26px;
        }

        .title-box {
            display: inline-block;
            border: 2px solid #555;
            box-shadow: 4px 4px 0px #888;
            padding: 7px 46px 8px;
            color: #0033cc;
            font-weight: 800;
            font-size: 24px;
            letter-spacing: 2.5px;
            background: #fff;
        }

        /* Certificate Body */
        .cert-body {
            font-size: 19.5px;
            line-height: 2.38;
            color: #111;
            margin-top: 4px;
        }

        .certify-intro-text {
            font-style: italic;
            font-weight: 600;
            color: #222;
            margin-right: 2px;
        }

        .student-name-val {
            font-weight: 800;
            font-size: 21.5px;
            text-transform: uppercase;
            border-bottom: 2px solid #000;
            padding-bottom: 1px;
            letter-spacing: 1px;
            color: #000;
            display: inline;
            -webkit-box-decoration-break: clone;
            box-decoration-break: clone;
        }

        .cert-text-justified {
            font-size: 19.5px;
            line-height: 2.38;
            text-align: justify;
            text-justify: inter-word;
            color: #111;
            margin-bottom: 12px;
            word-spacing: 0.5px;
        }

        .val-underline {
            display: inline;
            border-bottom: 2px solid #000;
            padding: 0 4px 1px;
            line-height: inherit;
            font-weight: 700;
            color: #000;
            -webkit-box-decoration-break: clone;
            box-decoration-break: clone;
        }

        .text-blue {
            color: #0033cc;
            font-weight: 800;
        }

        .dob-words-val {
            border-bottom: 2px solid #000;
            padding-bottom: 1px;
            font-weight: 700;
            font-size: 19.5px;
            color: #000;
            display: inline;
            -webkit-box-decoration-break: clone;
            box-decoration-break: clone;
        }

        /* Footer Section */
        .footer-section {
            margin-top: 45px;
            width: 100%;
        }

        .footer-table {
            width: 100%;
            border-collapse: collapse;
        }

        .footer-table td {
            vertical-align: bottom;
            padding: 0;
        }

        .footer-left {
            width: 36%;
            font-size: 18px;
            line-height: 2.1;
            font-weight: 700;
        }

        .footer-photo-wrap {
            width: 28%;
            text-align: center;
        }

        .photo-box {
            width: 120px;
            height: 150px;
            border: 2px solid #000;
            margin: 0 auto;
            background: #fff;
            text-align: center;
            overflow: hidden;
            line-height: 150px;
        }

        .photo-box img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            vertical-align: middle;
        }

        .footer-right {
            width: 36%;
            text-align: right;
            font-size: 19.5px;
            font-weight: 800;
            padding-bottom: 6px;
        }

        .print-bar {
            max-width: 820px;
            margin: 20px auto 0;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }

        .print-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 24px;
            border: 0;
            border-radius: 10px;
            background: #1f2580;
            color: #fff;
            font-family: Arial, sans-serif;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            box-shadow: 0 6px 16px rgba(31, 37, 128, .25);
        }

        .print-btn:hover {
            background: #e1141c;
        }

        @media print {
            html,
            body {
                background: #fff;
            }

            .no-print {
                display: none !important;
            }

            .certificate-container {
                box-shadow: none;
                margin: 0;
                padding: 0;
                max-width: 100%;
                width: 100%;
                height: calc(100vh - 2px);
                page-break-inside: avoid;
                break-inside: avoid;
            }

            .certificate-inner {
                border: 2.5px solid #000;
                padding: 34px 36px 40px;
                height: 100%;
                display: flex;
                flex-direction: column;
            }

            .letterhead-logo {
                width: 92px;
                height: 92px;
            }

            .letterhead-meta {
                width: 92px;
            }

            .brand-school {
                font-size: 26px;
                white-space: nowrap;
            }

            .cert-divider {
                margin-bottom: 40px;
            }

            .title-box-wrap {
                margin-bottom: 44px;
            }

            .cert-body,
            .cert-text-justified {
                font-size: 20px;
                line-height: 2.75;
            }

            .footer-section {
                margin-top: auto;
            }
        }
    </style>
</head>

<body>
    @php
        $certificateData = $certificateData ?? [];
        $schoolName = \App\Services\SchoolDatabaseManager::getActiveSchool()['name'] ?? 'Ultra English School';

        // 1. Student Name
        $studentName = trim(
            ($admission->first_name ?? '') . ' ' .
            ($admission->last_name ?? '') . ' ' .
            ($admission->father_name ?? '')
        );
        $studentName = mb_strtoupper($studentName ?: '________________________________________');

        // 2. Gender pronouns
        $gender = strtolower(trim($admission->gender ?? ''));
        $isFemale = in_array($gender, ['female', 'f', 'girl'], true);
        $hisHer = $isFemale ? 'Her' : 'His';
        $heShe = $isFemale ? 'she' : 'he';

        // 3. Issue Date
        $issueDate = $certificateData['issue_date'] ?? now()->format('d-m-Y');

        // 4. Standard with word formatting e.g. 6th (Sixth ), 1st (First )
        $rawStd = trim($certificateData['standard'] ?? $admission->current_std ?? $admission->admission_std ?? $courseRegistration?->class?->class_name ?? $courseRegistration?->course?->course_name ?? '');

        $formatStandardWithWords = function (?string $std) {
            if (empty($std)) {
                return '________';
            }
            $s = trim($std);
            if (preg_match('/\(.*\)/', $s)) {
                return $s;
            }
            $map = [
                '1' => '1st (First )',
                '1st' => '1st (First )',
                'std 1' => '1st (First )',
                '2' => '2nd (Second )',
                '2nd' => '2nd (Second )',
                'std 2' => '2nd (Second )',
                '3' => '3rd (Third )',
                '3rd' => '3rd (Third )',
                'std 3' => '3rd (Third )',
                '4' => '4th (Fourth )',
                '4th' => '4th (Fourth )',
                'std 4' => '4th (Fourth )',
                '5' => '5th (Fifth )',
                '5th' => '5th (Fifth )',
                'std 5' => '5th (Fifth )',
                '6' => '6th (Sixth )',
                '6th' => '6th (Sixth )',
                'std 6' => '6th (Sixth )',
                '7' => '7th (Seventh )',
                '7th' => '7th (Seventh )',
                'std 7' => '7th (Seventh )',
                '8' => '8th (Eighth )',
                '8th' => '8th (Eighth )',
                'std 8' => '8th (Eighth )',
                '9' => '9th (Ninth )',
                '9th' => '9th (Ninth )',
                'std 9' => '9th (Ninth )',
                '10' => '10th (Tenth )',
                '10th' => '10th (Tenth )',
                'std 10' => '10th (Tenth )',
                '11' => '11th (Eleventh )',
                '11th' => '11th (Eleventh )',
                'std 11' => '11th (Eleventh )',
                '12' => '12th (Twelfth )',
                '12th' => '12th (Twelfth )',
                'std 12' => '12th (Twelfth )',
            ];
            $lower = strtolower($s);
            if (isset($map[$lower])) {
                return $map[$lower];
            }
            if (preg_match('/(\d+)/', $s, $m) && isset($map[$m[1]])) {
                return $map[$m[1]];
            }
            return $s;
        };

        $standardDisplay = $formatStandardWithWords($rawStd);

        // 5. Academic Year (e.g. 2021-2022 or 2026-2027)
        $academicYear = trim((string)($certificateData['academic_year'] ?? ''));
        if (empty($academicYear)) {
            $today = now();
            $currY = (int)$today->format('Y');
            $academicYear = ($today->month >= 6) ? $currY . '-' . ($currY + 1) : ($currY - 1) . '-' . $currY;
        } elseif (preg_match('/^\d{4}$/', $academicYear)) {
            $y = (int) $academicYear;
            $academicYear = $y . '-' . ($y + 1);
        }

        // 6. School General Register No. (e.g. 1,900 or 2,502)
        $rawGrNo = trim((string)($admission->gr_no ?? $admission->old_gr_no ?? ''));
        if (is_numeric($rawGrNo)) {
            $grNoDisplay = number_format((float)$rawGrNo);
        } else {
            $grNoDisplay = $rawGrNo ?: '________';
        }

        // 7. Date of birth formatting and words conversion
        $dob = $admission->date_of_birth ?? null;
        $dobFormatted = !empty($dob) ? \Carbon\Carbon::parse($dob)->format('d-m-Y') : '__________';

        $numberToWords = function(int $n) use (&$numberToWords): string {
            $ones = [
                "", "One", "Two", "Three", "Four", "Five", "Six", "Seven", "Eight", "Nine", "Ten",
                "Eleven", "Twelve", "Thirteen", "Fourteen", "Fifteen", "Sixteen", "Seventeen", "Eighteen", "Nineteen"
            ];
            $tens = ["", "", "Twenty", "Thirty", "Forty", "Fifty", "Sixty", "Seventy", "Eighty", "Ninety"];

            if ($n === 0) return "";
            if ($n < 20) return $ones[$n];
            if ($n < 100) return $tens[intdiv($n, 10)] . ($n % 10 !== 0 ? " " . $ones[$n % 10] : "");
            if ($n < 1000) return $ones[intdiv($n, 100)] . " Hundred" . ($n % 100 !== 0 ? " " . $numberToWords($n % 100) : "");
            if ($n < 100000) return $numberToWords(intdiv($n, 1000)) . " Thousand" . ($n % 1000 !== 0 ? " " . $numberToWords($n % 1000) : "");
            return (string)$n;
        };

        $dobToWords = function(?string $dobDate) use ($numberToWords): string {
            if (empty($dobDate)) {
                return '________________________________________';
            }
            try {
                $d = \Carbon\Carbon::parse($dobDate);
            } catch (\Exception $e) {
                return '________________________________________';
            }

            $dayWords = [
                1 => 'One', 2 => 'Two', 3 => 'Three', 4 => 'Four', 5 => 'Five',
                6 => 'Six', 7 => 'Seven', 8 => 'Eight', 9 => 'Nine', 10 => 'Ten',
                11 => 'Eleven', 12 => 'Twelve', 13 => 'Thirteen', 14 => 'Fourteen', 15 => 'Fifteen',
                16 => 'Sixteen', 17 => 'Seventeen', 18 => 'Eighteen', 19 => 'Nineteen', 20 => 'Twenty',
                21 => 'Twenty One', 22 => 'Twenty Two', 23 => 'Twenty Three', 24 => 'Twenty Four', 25 => 'Twenty Five',
                26 => 'Twenty Six', 27 => 'Twenty Seven', 28 => 'Twenty Eight', 29 => 'Twenty Nine', 30 => 'Thirty',
                31 => 'Thirty One'
            ];

            $dayText = $dayWords[$d->day] ?? $numberToWords($d->day);
            $monthText = $d->format('F');
            $yearText = $numberToWords($d->year);

            return trim($dayText . '  ' . $monthText . ', ' . $yearText);
        };

        $dobInWords = $dobToWords($dob);

        // 8. Only Caste
        $caste = trim($admission->cast ?? '');
        $casteDisplay = $caste !== '' ? mb_strtoupper($caste) : '________';

        // 9. Photo box is intentionally kept empty for physical photograph / stamp
    @endphp

    <div class="print-bar no-print">
        <button type="button" class="print-btn" onclick="window.print()">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9V2h12v7"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
            Print
        </button>
    </div>

    <div class="certificate-container font-attractive" id="certContainer">
        <div class="certificate-inner">
            <img src="{{ asset('admin/assets/images/logo-light.png') }}" alt="" class="cert-watermark">
            <div class="top-section">
                <div class="letterhead">
                    <img src="{{ asset('admin/assets/images/logo-light.png') }}" alt="School Logo" class="letterhead-logo">
                    <div class="letterhead-text">
                        <div class="brand-school">{{ $schoolName }} - KESHOD</div>
                    </div>
                    <div class="letterhead-meta"></div>
                </div>
                <div class="cert-divider"></div>

                {{-- Certificate Title Box --}}
                <div class="title-box-wrap">
                    <div class="title-box">BONAFIDE CERTIFICATE</div>
                </div>

                {{-- Certificate Body --}}
                <div class="cert-body">
                    <div class="cert-text-justified">
                        <span class="certify-intro-text">This is to certify that,</span> &nbsp;<span class="student-name-val">{{ $studentName }}</span> &nbsp;was a bonafide student of this institution and was studying in Standard &nbsp;<span class="val-underline">{{ $standardDisplay }}</span>&nbsp;&nbsp; during year &nbsp;<span class="val-underline">{{ $academicYear }}</span>. According to School General Register No. &nbsp;<span class="val-underline text-blue">&nbsp;{{ $grNoDisplay }}&nbsp;</span> {{ $hisHer }} Birthdate is &nbsp;&nbsp;<span class="val-underline">{{ $dobFormatted }}</span>&nbsp;&nbsp; and In Words &nbsp;<span class="dob-words-val">{{ $dobInWords }}</span>.&nbsp; And {{ $hisHer }} Caste &nbsp;&nbsp;<span class="val-underline">{{ $casteDisplay }}</span>&nbsp;&nbsp; is registered. According to our knowledge and belief {{ $heShe }} bears good conduct and moral character.
                    </div>
                </div>
            </div>

            {{-- Footer Section --}}
            <div class="footer-section">
                <table class="footer-table">
                    <tr>
                        <td class="footer-left">
                            <div>Date &nbsp;: <span class="text-blue">{{ $issueDate }}</span></div>
                            <div>Place : <span class="text-blue">KESHOD</span></div>
                        </td>
                        <td class="footer-photo-wrap">
                            <div class="photo-box">
                                {{-- Kept empty for physical photograph / official school stamp --}}
                            </div>
                        </td>
                        <td class="footer-right">
                            Principal
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

</body>

</html>
