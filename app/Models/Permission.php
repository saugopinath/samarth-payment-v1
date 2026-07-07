<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use OwenIt\Auditing\Contracts\Auditable;
use Spatie\Permission\Models\Permission as SpatiePermission;
use Illuminate\Support\Facades\Auth;

class Permission extends SpatiePermission implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    use HasFactory;

    protected $guarded = ['id'];
    public $timestamps = true;

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    protected static function booted()
    {
        static::deleting(function ($permission) {
            $permission->children()->each(function ($child) {
                $child->delete();
            });
        });
    }
    public function validationScore()
    {
        return $this->hasOne(ValidationScoreMapping::class);
    }
    public function transformAudit(array $data): array
    {
        $userId = Auth::id();
        $userRole = UserRoleSchemeOfficeMapping::where('user_id', $userId)
            ->value('role_id');
        $data['tags'] = class_basename($this) . '_' . $data['event'];
        $data['session_id'] = session()->getId();
        // $data['other_details'] = [
        //     'updated_by_role' => $userRole,
        //     'user_agent' => \Illuminate\Support\Facades\Request::userAgent(),
        //     'url' => \Illuminate\Support\Facades\Request::fullUrl(),
        //     'method' => \Illuminate\Support\Facades\Request::method(),
        //     'referrer' => \Illuminate\Support\Facades\Request::header('referer'),
        // ];
        $data['other_details'] = json_encode([
            'updated_by_role' => $userRole,
            'user_agent' => request()->userAgent(),
            'url' => request()->fullUrl(),
            'method' => request()->method(),
            'referrer' => request()->header('referer'),
        ]);
        if (app()->has('livewire_action_log_id')) {
            $data['livewire_action_log_id'] = (string) app('livewire_action_log_id');
        }
        if (app()->has('user_page_visit_log_id')) {
            $data['user_page_visit_log_id'] = (string) app('user_page_visit_log_id');
        }

        return $data;
    }
}
