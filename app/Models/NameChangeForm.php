<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class NameChangeForm extends Model
{
    use SoftDeletes;

    protected $table = 'name_change_forms';

    protected $fillable = [
        'admission_id',
        'academic_year',
        'sr_no',
        'form_student_id',
        'std',
        'class',
        'roll_no',
        'form_date',
        'type_of_student',
        'app_no',
        'student_name',
        'date_of_birth',
        'school_joining_date',
        'medium',
        'primary_school_type',
        'father_profession',
        'father_income',
        'gr_no',
        'cast',
        'gender',
        'mother_name',
        'aadhar',
        'bpl_card_no',
        'email',
        'is_widow_child',
        'is_minority',
        'minority_type',
        'address',
        'pincode',
        'mobile',
        'uid',
        'marital_status',
        'subject_marks',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'form_date'           => 'date',
        'date_of_birth'       => 'date',
        'school_joining_date' => 'date',
        'subject_marks'       => 'array',
    ];

    public function admission()
    {
        return $this->belongsTo(Admission::class, 'admission_id');
    }

    public function formatDate(?string $field): string
    {
        $value = $this->{$field} ?? null;
        if (!$value) {
            return '';
        }

        return $value->format('d-m-Y');
    }
}
