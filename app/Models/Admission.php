<?php

namespace App\Models;

use App\Models\Master\MasterCourse;
use Illuminate\Foundation\Auth\User as Authenticatable;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class Admission extends Authenticatable
{
    use SoftDeletes;
    use HasRoles;
    use HasApiTokens, Notifiable;

    public $table = 'admission';

    protected $fillable = [
        'biometric_id',
        'gr_no',
        'bus_route_village',
        'admission_date',
        'admission_std',
        'current_std',
        'division',
        'stream',
        'aadhar_card_no',
        'pen_no',
        'first_name',
        'last_name',
        'father_name',
        'mother_name',
        'temporary_address',
        'permanent_address',
        'mobile_no',
        'parent_mobile_no',
        'other_mobile_no',
        'whatsapp_no',
        'cast',
        'occupation',
        'mother_occupation',
        'date_of_birth',
        'birth_place',
        'gender',
        'profile_pic',
        'religion',
        'house',
        'category',
        'spid',
        'apaar_id_abc_id',
        'udise',
        'bank_name',
        'bank_account_no',
        'is_new_admission',
        'last_school_name',
        'old_gr_no',
        'passed_standard',
        'lc_no',
        'lc_date',
        'attendance',
        'gdrivefolderurl',
        'gdrivefolderid',
        'gdrivefoldername',
        'status',
        'created_by',
        'updated_by',
    ];

    public function marksheetIssues()
    {
        return $this->hasMany(StudentMarksheetIssue::class, 'admission_id');
    }

    public function setDateOfBirthAttribute($value)
    {
        if (empty($value)) {
            $this->attributes['date_of_birth'] = null;
            return;
        }
        $str = trim((string)$value);
        if (preg_match('/^(\d{1,2})[-\/\.](\d{1,2})[-\/\.](\d{4})$/', $str, $matches)) {
            $this->attributes['date_of_birth'] = sprintf('%04d-%02d-%02d', $matches[3], $matches[2], $matches[1]);
        } else {
            try {
                $this->attributes['date_of_birth'] = \Carbon\Carbon::parse($str)->format('Y-m-d');
            } catch (\Exception $e) {
                $this->attributes['date_of_birth'] = $str;
            }
        }
    }

    public function setAdmissionDateAttribute($value)
    {
        if (empty($value)) {
            $this->attributes['admission_date'] = null;
            return;
        }
        $str = trim((string)$value);
        if (preg_match('/^(\d{1,2})[-\/\.](\d{1,2})[-\/\.](\d{4})$/', $str, $matches)) {
            $this->attributes['admission_date'] = sprintf('%04d-%02d-%02d', $matches[3], $matches[2], $matches[1]);
        } else {
            try {
                $this->attributes['admission_date'] = \Carbon\Carbon::parse($str)->format('Y-m-d');
            } catch (\Exception $e) {
                $this->attributes['admission_date'] = $str;
            }
        }
    }

    public function setLcDateAttribute($value)
    {
        if (empty($value)) {
            $this->attributes['lc_date'] = null;
            return;
        }
        $str = trim((string)$value);
        if (preg_match('/^(\d{1,2})[-\/\.](\d{1,2})[-\/\.](\d{4})$/', $str, $matches)) {
            $this->attributes['lc_date'] = sprintf('%04d-%02d-%02d', $matches[3], $matches[2], $matches[1]);
        } else {
            try {
                $this->attributes['lc_date'] = \Carbon\Carbon::parse($str)->format('Y-m-d');
            } catch (\Exception $e) {
                $this->attributes['lc_date'] = $str;
            }
        }
    }

    public function getMarksheetSemestersAttribute()
    {
        return $this->marksheetIssues()->pluck('semester')->toArray();
    }

    // Admission.php
    // public function courseRegistrations()
    // {
    //     return $this->hasMany(CourceRegistration::class, 'register_id');
    // }
    // app/Models/Admission.php
    public function feesCollections()
    {
        return $this->hasMany(FeesCollection::class);
    }
    public function courses()
    {
        return $this->hasMany(CourceRegistration::class, 'register_id', 'id');
    }


    public function courceRegistration()
    {
        return $this->hasOne(CourceRegistration::class, 'admission_id');
    }

    public function course()
    {
        return $this->belongsTo(MasterCourse::class, 'course_id');
    }

    /**
     * Machine punch biometric_id → admission_id
     * Example: biometric_id 232 → VORA SHRUTI (admission id 61)
     */
    public static function findByBiometricId($biometricId): ?self
    {
        $biometricId = trim((string) $biometricId);

        if ($biometricId === '') {
            return null;
        }

        return static::query()
            ->where('biometric_id', $biometricId)
            ->first();
    }

    public function educationDetails()
    {
        return $this->hasMany(EducationDetails::class, 'admission_id');
    }
    public function testMarks()
    {
        return $this->hasMany(\App\Models\StudentTestMark::class, 'register_id', 'id');
    }

    public function getFullNameAttribute()
    {
        #full_name
        $first = trim($this?->first_name ?? '');
        $last = trim($this?->last_name ?? '');
        $father = trim($this?->father_name ?? '');

        if ($first !== '' && $father !== '') {
            $cleanFather = trim(preg_replace('/^' . preg_quote($first, '/') . '\s+/i', '', $father));
        } else {
            $cleanFather = $father;
        }

        return trim("{$first} {$last} {$cleanFather}");
    }

    public function getFatherFullNameAttribute()
    {
        $first = trim($this?->first_name ?? '');
        $father = trim($this?->father_name ?? '');

        if ($first !== '' && $father !== '') {
            $cleanFather = trim(preg_replace('/^' . preg_quote($first, '/') . '\s+/i', '', $father));
        } else {
            $cleanFather = $father;
        }

        return trim("{$first} {$cleanFather}");
    }

    public function getMotherFullNameAttribute()
    {
        $first = trim($this?->first_name ?? '');
        $mother = trim($this?->mother_name ?? '');

        if ($first !== '' && $mother !== '') {
            $cleanMother = trim(preg_replace('/^' . preg_quote($first, '/') . '\s+/i', '', $mother));
        } else {
            $cleanMother = $mother;
        }

        return trim("{$first} {$cleanMother}");
    }

    public function getProfilePicUrlAttribute()
    {
        # profile_pic_url
        if (!$this?->profile_pic) {
            return "";
        }
        $profilePic = $this?->profile_pic;
        if (strpos($profilePic, 'http') === 0) {
            return $profilePic;
        }
        return asset($profilePic);
    }
}
