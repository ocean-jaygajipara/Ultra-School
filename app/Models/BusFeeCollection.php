<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BusFeeCollection extends Model
{
    use SoftDeletes;

    protected $table = 'bus_fee_collections';

    protected $fillable = [
        'receipt_no',
        'admission_id',
        'student_name',
        'gr_no',
        'standard',
        'division',
        'bus_route_village',
        'academic_year',
        'period_label',
        'date',
        'amount',
        'mode',
        'upi_id',
        'cheque_no',
        'remark',
        'status',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function admission()
    {
        return $this->belongsTo(Admission::class, 'admission_id');
    }

    public function createdByUser()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public static function nextReceiptNo(): string
    {
        $year = date('Y');
        $prefix = 'BF-' . $year . '-';

        $last = static::withTrashed()
            ->where('receipt_no', 'like', $prefix . '%')
            ->orderByDesc('id')
            ->value('receipt_no');

        $next = 1;
        if ($last && preg_match('/(\d+)$/', $last, $m)) {
            $next = (int) $m[1] + 1;
        }

        return $prefix . str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }
}
