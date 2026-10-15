<?php

namespace App\Models\Master;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MasterAcademicYear extends Model
{
    use SoftDeletes;

    public $table = 'master_academic_years';

    protected $fillable = [
        'name',
        'start_date',
        'end_date',
        'is_current',
        'status',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
        'is_current' => 'boolean',
    ];

    public static function current(): ?self
    {
        return static::where('is_current', true)->where('status', 'active')->first();
    }

    public static function forDate($date): ?self
    {
        if (empty($date)) {
            return null;
        }

        try {
            $day = Carbon::parse($date)->toDateString();
        } catch (\Throwable $e) {
            return null;
        }

        return static::where('status', 'active')
            ->whereDate('start_date', '<=', $day)
            ->whereDate('end_date', '>=', $day)
            ->orderByDesc('start_date')
            ->first();
    }

    public function label(): string
    {
        return $this->name . ' (' . $this->start_date->format('d-m-Y') . ' to ' . $this->end_date->format('d-m-Y') . ')';
    }
}
