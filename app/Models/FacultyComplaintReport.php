<?php

namespace App\Models;

use App\Models\Master\MasterAcademicYear;
use App\Models\Master\MasterStandard;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FacultyComplaintReport extends Model
{
    use SoftDeletes;

    protected $table = 'teacher_complaint_report';

    protected $fillable = [
        'academic_year_id',
        'standard_id',
        'division',
        'admission_id',
        'gr_no',
        'date',
        'faculty_id',
        'complaint',
        'is_read',
    ];

    protected $casts = [
        'date' => 'date',
        'is_read' => 'boolean',
    ];

    public function admission()
    {
        return $this->belongsTo(Admission::class, 'admission_id');
    }

    public function faculty()
    {
        return $this->belongsTo(User::class, 'faculty_id');
    }

    public function academicYear()
    {
        return $this->belongsTo(MasterAcademicYear::class, 'academic_year_id');
    }

    public function standard()
    {
        return $this->belongsTo(MasterStandard::class, 'standard_id');
    }
}
