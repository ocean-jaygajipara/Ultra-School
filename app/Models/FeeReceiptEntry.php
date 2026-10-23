<?php

namespace App\Models;

use App\Models\Master\FeeMaster;
use App\Models\Master\MasterAcademicYear;
use App\Models\Master\MasterStandard;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FeeReceiptEntry extends Model
{
    use SoftDeletes;

    protected $table = 'fee_receipt_entries';

    protected $fillable = [
        'receipt_no',
        'date',
        'academic_year_id',
        'admission_id',
        'standard_id',
        'division',
        'term',
        'gr_no',
        'roll_no',
        'student_name',
        'last_yr_pending',
        'admission_fee',
        'term_fee',
        'school_fee',
        'vehicle_fee',
        'total',
        'less_amount',
        'net_amount',
        'print_copies',
        'is_preview',
        'mode',
        'remark',
        'fee_snapshot',
        'status',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'date'            => 'date',
        'last_yr_pending' => 'float',
        'admission_fee'   => 'float',
        'term_fee'        => 'float',
        'school_fee'      => 'float',
        'vehicle_fee'     => 'float',
        'total'           => 'float',
        'less_amount'     => 'float',
        'net_amount'      => 'float',
        'print_copies'    => 'integer',
        'is_preview'      => 'boolean',
        'fee_snapshot'    => 'array',
    ];

    public function admission()
    {
        return $this->belongsTo(Admission::class, 'admission_id');
    }

    public function standard()
    {
        return $this->belongsTo(MasterStandard::class, 'standard_id');
    }

    public function academicYear()
    {
        return $this->belongsTo(MasterAcademicYear::class, 'academic_year_id')->withTrashed();
    }

    public function feeLines(): array
    {
        return [
            ['key' => 'last_yr_pending', 'label' => 'Last Yr. Pending Fee', 'amount' => $this->last_yr_pending],
            ['key' => 'admission_fee', 'label' => 'Admission Fee', 'amount' => $this->admission_fee],
            ['key' => 'term_fee', 'label' => 'Term Fee', 'amount' => $this->term_fee],
            ['key' => 'school_fee', 'label' => 'School Fee', 'amount' => $this->school_fee],
            ['key' => 'vehicle_fee', 'label' => 'Vehicle Fee', 'amount' => $this->vehicle_fee],
        ];
    }
}
