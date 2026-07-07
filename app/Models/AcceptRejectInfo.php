<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use OwenIt\Auditing\Contracts\Auditable;
class AcceptRejectInfo extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    protected $table = 'accept_reject_infos';

    protected $fillable = [
        'application_id',
        'scheme_id',
        'beneficiary_id',
        'ip_address',
        'user_id',
        'browser',
        'model_name',
        'op_type',
        'revert_reason_cause_id',
        'revert_reason_remarks',
        'parent_id',
        'critical_changes',
        'old_value',
        'new_value',
    ];
    protected $casts = [
        'old_value' => 'array',
        'new_value' => 'array',
    ];
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
        return $data;
    }
    public function revertReason()
    {
        return $this->belongsTo(Codemaster::class, 'revert_reason_cause_id');
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function opType()
    {
        return $this->belongsTo(Codemaster::class, 'op_type');
    }
}
