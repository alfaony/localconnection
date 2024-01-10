<?php

namespace App\Http\Middleware;

use App\Models\Role;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ApiRolePermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $user = auth()->user();
        if ($user) {
            $role = Role::findOrFail($user->role_id);
            $permissions = $role->permissions;
            // get requested action
            $actionMethod = $request->route()->getActionMethod();
            $firstUrl = $request->segment(2);
            if (isset($firstUrl)) 
            {
                $url = Str::plural(str_replace('-', '_', $firstUrl));
                // check if requested action is in permissions list
                $data['url'] = $firstUrl;
                $data['actionMethod'] = $actionMethod;
                foreach ($permissions as $permission){
                    if ($actionMethod == $permission->method && $url == $permission->table){
                        // authorized request
                        return $next($request);
                    }
                }
                // none authorized request
                return response()->json
                (
                    [
                        'message' => 'Unauthenticated'
                    ], 403
                );
            }
            return $next($request);
        }
        // none authorized request
        return response()->json([
            'message' => 'Unauthenticated'
        ], 403);
    }
}
