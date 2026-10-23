<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FeeMaster extends Model
{
    use SoftDeletes;

    public $table = 'fee_masters';

    public const TERMS = [
        'FIRST'  => 'FIRST',
        'SECOND' => 'SECOND',
    ];

    public const FEE_TYPES = [
        'admission_fee' => 'Admission Fee',
        'deposit_fee'  => 'Deposit Fee',
        'term_fee'     => 'Term Fee',
        'school_fee'   => 'School Fee',
    ];

    protected $fillable = [
        'academic_year_id',
        'standard_id',
        'term',
        'admission_fee',
        'deposit_fee',
        'term_fee',
        'school_fee',
        'total',
        'status',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'admission_fee' => 'float',
        'deposit_fee'  => 'float',
        'term_fee'     => 'float',
        'school_fee'   => 'float',
        'total'        => 'float',
    ];

    public function standard()
    {
        return $this->belongsTo(MasterStandard::class, 'standard_id');
    }

    public function academicYear()
    {
        return $this->belongsTo(MasterAcademicYear::class, 'academic_year_id')->withTrashed();
    }

    public function recalculateTotal(): float
    {
        return round(
            (float) $this->admission_fee
            + (float) $this->deposit_fee
            + (float) $this->term_fee
            + (float) $this->school_fee,
            2
        );
    }
}
