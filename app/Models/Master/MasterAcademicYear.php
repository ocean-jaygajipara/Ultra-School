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

    /**
     * Active year immediately before $relativeTo (by start_date).
     * Uses Master list only — no hardcoded year names.
     */
    public static function previous(?self $relativeTo = null): ?self
    {
        $relativeTo ??= static::current();
        if (!$relativeTo?->start_date) {
            return null;
        }

        return static::where('status', 'active')
            ->whereDate('start_date', '<', $relativeTo->start_date->toDateString())
            ->orderByDesc('start_date')
            ->first();
    }

    /**
     * Active year immediately after $relativeTo (by start_date).
     */
    public static function next(?self $relativeTo = null): ?self
    {
        $relativeTo ??= static::current();
        if (!$relativeTo?->start_date) {
            return null;
        }

        return static::where('status', 'active')
            ->whereDate('start_date', '>', $relativeTo->start_date->toDateString())
            ->orderBy('start_date')
            ->first();
    }

    /**
     * Resolve a year by id, falling back to current when missing.
     */
    public static function resolve(?int $id = null): ?self
    {
        if ($id) {
            $year = static::withTrashed()->find($id);
            if ($year) {
                return $year;
            }
        }

        return static::current();
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
