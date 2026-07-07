<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class User_audit_trail extends Model 
{
    protected $fillable = [
        'old_password',
        'new_password',
        'operation_type',
        'operate_by', 
        'operate_to_user_id', 
        'ip_address', 
        'user_agent', 
        'operation_time'];
}
 