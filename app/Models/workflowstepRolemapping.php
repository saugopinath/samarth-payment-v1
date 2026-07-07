<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use OwenIt\Auditing\Contracts\Auditable;

class WorkflowsteproleMapping extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    protected $table = "workflowstep_rolemappings";
    protected $guarded = [];
    protected $casts = [
        'is_final_step' => 'boolean',
    ];
    public static function getLevelRoleIdsByRole($schemeId, $roleId, $rank = null)
    {
        $query = self::query();
        if ($rank !== null) {
            $query->where('workflow_step_id', $rank);
        } else {
            $query->where('role_id', $roleId);
        }
        return $query->where('scheme_id', $schemeId)
            ->with('workflowstep')
            ->first(['same_level_role_id', 'next_level_role_id', 'is_first_step', 'is_final_step', 'workflow_step_id']);
    }

    public static function getMinMaxWorkflowStep(int $schemeId): array
    {
        return [
            'min' => self::where('scheme_id', $schemeId)->where('module_id', null)->min('workflow_step_id'),
            'max' => self::where('scheme_id', $schemeId)->where('module_id', null)->max('workflow_step_id'),
        ];
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

    public function module()
    {
        return $this->belongsTo(DynamicWorkflowSchemeModule::class, 'module_id');
    }

    public function label()
    {
        return $this->belongsTo(DynamicWorkflowLabel::class, 'workflow_step_id');
    }

    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function workflowstep()
    {
        return $this->belongsTo(WorkflowStep::class, 'workflow_step_id', 'id');
    }
}
