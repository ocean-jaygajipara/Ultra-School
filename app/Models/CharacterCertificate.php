<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CharacterCertificate extends Model
{
    use SoftDeletes;

    protected $table = 'character_certificates';

    protected $fillable = [
        'admission_id',
        'gr_no',
        'uid_no',
        'student_name',
        'gender',
        'standard_id',
        'std',
        'std_display',
        'class',
        'academic_year',
        'print_date',
        'place',
        'created_by',
        'updated_by',
    ];

    public function admission()
    {
        return $this->belongsTo(Admission::class, 'admission_id');
    }
}
