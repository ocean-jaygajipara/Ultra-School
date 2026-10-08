<?php

namespace App\Models\Master;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MasterFeeDetail extends Model
{
    use SoftDeletes;

    public $table = 'fee_details';

    protected $fillable = [
        'fee_name',
        'amount',
        'status',
        'created_by',
        'updated_by',
        'deleted_by',
    ];
}
