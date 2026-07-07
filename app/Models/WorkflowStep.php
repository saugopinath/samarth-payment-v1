<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use OwenIt\Auditing\Contracts\Auditable;

class WorkflowStep extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    public function scheme()
    {
        return $this->belongsTo(Scheme::class);
    }

    public function parent()
    {
        return $this->belongsTo(WorkflowStep::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(WorkflowStep::class, 'parent_id');
    }
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'workflowstep_rolemappings', 'workflow_step_id', 'role_id')
            ->withPivot(['rank', 'same_level_role_id', 'next_level_role_id'])
            ->withTimestamps();
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
