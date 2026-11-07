<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LeavingCertificateEntry extends Model
{
    use SoftDeletes;

    protected $table = 'leaving_certificate_entries';

    protected $fillable = [
        'admission_id',
        'mode',
        'academic_year',
        'uid_no',
        'gr_no',
        'student_name',
        'caste',
        'category',
        'birthdate',
        'birthplace',
        'last_school',
        'admission_date',
        'admission_std',
        'last_std',
        'last_division',
        'leave_date',
        'studied_std',
        'studied_from',
        'leave_reason',
        'behavior',
        'progress',
        'attendance',
        'attendance_from',
        'leaving_certi_no',
        'certi_date',
        'note',
        'fee',
        'photo',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'birthdate'      => 'date',
        'admission_date'  => 'date',
        'leave_date'     => 'date',
        'certi_date'     => 'date',
        'fee'            => 'decimal:2',
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
