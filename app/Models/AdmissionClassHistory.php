<?php

namespace App\Models;

use App\Models\Master\MasterAcademicYear;
use App\Models\Master\MasterStandard;
use Illuminate\Database\Eloquent\Model;

class AdmissionClassHistory extends Model
{
    protected $table = 'admission_class_histories';

    protected $fillable = [
        'admission_id',
        'academic_year_id',
        'standard_id',
        'division',
        'roll_no',
        'created_by',
        'updated_by',
    ];

    public function academicYear()
    {
        return $this->belongsTo(MasterAcademicYear::class, 'academic_year_id')->withTrashed();
    }

    public function standard()
    {
        return $this->belongsTo(MasterStandard::class, 'standard_id')->withTrashed();
    }

    /**
     * @deprecated Use MasterAcademicYear::previous()
     */
    public static function previousAcademicYear(?MasterAcademicYear $relativeTo = null): ?MasterAcademicYear
    {
        return MasterAcademicYear::previous($relativeTo);
    }

    /**
     * One row per student per academic year: the student's current
     * Standard / Division / Roll No. are saved into that year's row.
     */
    public static function record(Admission $admission, ?int $userId = null, ?MasterAcademicYear $year = null): ?self
    {
        $year ??= MasterAcademicYear::resolve($admission->academic_year_id);
        $standardId = $admission->current_std_id;
        if (!$year || !$standardId) {
            return null;
        }

        $row = static::firstOrNew([
            'admission_id'     => $admission->id,
            'academic_year_id' => $year->id,
        ]);

        if (!$row->exists) {
            $row->created_by = $userId;
        }
        $row->standard_id = $standardId;
        $row->division = $admission->division ?: null;
        $row->roll_no = $admission->roll_no;
        $row->updated_by = $userId;
        $row->save();

        return $row;
    }

    /**
     * Freeze the student's current class into their from-year
     * before promoting them into $toYear (always from Academic Year Master).
     */
    public static function freezeBeforePromotion(Admission $admission, MasterAcademicYear $toYear, ?int $userId = null): ?self
    {
        $fromYear = MasterAcademicYear::resolve($admission->academic_year_id);

        // If student year is missing or already the target, use previous of target
        if (!$fromYear || (int) $fromYear->id === (int) $toYear->id) {
            $fromYear = MasterAcademicYear::previous($toYear);
        }

        if (!$fromYear || (int) $fromYear->id === (int) $toYear->id) {
            return null;
        }

        return static::record($admission, $userId, $fromYear);
    }

    /**
     * Make sure Class History always has the current class, and (when promoted)
     * also a previous-year row for the admission standard.
     */
    public static function ensureForStudent(Admission $admission, ?int $userId = null): void
    {
        if (!$admission->current_std_id) {
            return;
        }

        $year = MasterAcademicYear::resolve($admission->academic_year_id);

        if ($year) {
            $exists = static::where('admission_id', $admission->id)
                ->where('academic_year_id', $year->id)
                ->exists();

            if (!$exists) {
                static::record($admission, $userId, $year);
            }

            if (!$admission->academic_year_id) {
                $admission->academic_year_id = $year->id;
                $admission->saveQuietly();
            }
        }

        static::backfillAdmissionStandard($admission, $userId);
    }

    /**
     * If student was admitted in an earlier standard and that year is missing
     * from history, add one row for the previous academic year (from Master).
     */
    public static function backfillAdmissionStandard(Admission $admission, ?int $userId = null): void
    {
        if (!$admission->admission_std_id || !$admission->current_std_id) {
            return;
        }
        if ((int) $admission->admission_std_id === (int) $admission->current_std_id) {
            return;
        }

        $alreadyHasAdmissionStd = static::where('admission_id', $admission->id)
            ->where('standard_id', $admission->admission_std_id)
            ->exists();
        if ($alreadyHasAdmissionStd) {
            return;
        }

        $anchorYear = MasterAcademicYear::resolve($admission->academic_year_id);
        $prevYear = MasterAcademicYear::previous($anchorYear);
        if (!$prevYear) {
            return;
        }

        $exists = static::where('admission_id', $admission->id)
            ->where('academic_year_id', $prevYear->id)
            ->exists();
        if ($exists) {
            return;
        }

        static::create([
            'admission_id'       => $admission->id,
            'academic_year_id'  => $prevYear->id,
            'standard_id'       => $admission->admission_std_id,
            'division'          => $admission->division ?: null,
            'roll_no'           => null,
            'created_by'        => $userId,
            'updated_by'        => $userId,
        ]);
    }

    /**
     * Save the roll number into the row of the student's current class,
     * falling back to the current academic year from Master.
     */
    public static function recordRoll(Admission $admission, ?int $userId = null): ?self
    {
        $latest = static::where('admission_id', $admission->id)
            ->where('standard_id', $admission->current_std_id)
            ->where('division', $admission->division ?: null)
            ->orderByDesc('id')
            ->first();

        $year = $latest?->academicYear ?? MasterAcademicYear::resolve($admission->academic_year_id);

        return static::record($admission, $userId, $year);
    }
}
