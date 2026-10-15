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
     * One row per student per academic year: the student's current
     * Standard / Division / Roll No. are saved into that year's row.
     */
    public static function record(Admission $admission, ?int $userId = null, ?MasterAcademicYear $year = null): ?self
    {
        $year ??= MasterAcademicYear::current();
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
     * Save the roll number into the row of the student's current class,
     * falling back to the current academic year.
     */
    public static function recordRoll(Admission $admission, ?int $userId = null): ?self
    {
        $latest = static::where('admission_id', $admission->id)
            ->where('standard_id', $admission->current_std_id)
            ->where('division', $admission->division ?: null)
            ->orderByDesc('id')
            ->first();

        $year = $latest?->academicYear ?? MasterAcademicYear::current();

        return static::record($admission, $userId, $year);
    }
}
