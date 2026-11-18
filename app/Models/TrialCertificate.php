<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TrialCertificate extends Model
{
    use SoftDeletes;

    protected $table = 'trial_certificates';

    protected $fillable = [
        'batch_no',
        'admission_id',
        'standard_id',
        'std',
        'class',
        'roll_no',
        'gr_no',
        'student_name',
        'uid_no',
        'ssc_index',
        'print_date',
        'trial',
        'exam_month',
        'exam_year',
        'exam_label',
        'place',
        'created_by',
        'updated_by',
    ];

    public function admission()
    {
        return $this->belongsTo(Admission::class, 'admission_id');
    }
}
