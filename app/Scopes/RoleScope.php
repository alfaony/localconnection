<?php
  
namespace App\Scopes;
  
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;
use App\Schemas\RoleSchema;

class RoleScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return void
     */
    public function apply(Builder $builder, Model $model)
    {
        $table = $model->getTable();
        switch (Auth::user()->role->name) 
        {
            case RoleSchema::ROOT:
                break;
            case RoleSchema::BM:
                if($table == 'task_assigns')
                {
                    $builder->byCompany(Auth::user()->company_id);
                }
                if($table == 'attendances')
                {
                    $builder->byCompany(Auth::user()->company_id);
                }
                if($table == 'security_checks')
                {
                    $builder->byCompany(Auth::user()->company_id);
                }
                if($table == 'cctv_checks')
                {
                    $builder->byCompany(Auth::user()->company_id);
                }
                break;
            case RoleSchema::OB:
                if($table == 'task_assigns')
                {
                    $builder->where('user_assign_id',Auth::user()->id);
                }
                if($table == 'attendances')
                {
                    $builder->where('user_id',Auth::user()->id);
                }
                if($table == 'security_checks')
                {
                    $builder->where('user_id',Auth::user()->id);
                }
                if($table == 'cctv_checks')
                {
                    $builder->where('user_id',Auth::user()->id);
                }
                break;
            case RoleSchema::SECURITY:
                if($table == 'task_assigns')
                {
                    $builder->where('user_assign_id',Auth::user()->id);
                }
                if($table == 'attendances')
                {
                    $builder->where('user_id',Auth::user()->id);
                }
                if($table == 'security_checks')
                {
                    $builder->where('user_id',Auth::user()->id);
                }
                if($table == 'cctv_checks')
                {
                    $builder->where('user_id',Auth::user()->id);
                }
                break;
        }
    }
}