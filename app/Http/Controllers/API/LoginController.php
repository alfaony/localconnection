<?php

namespace App\Http\Controllers\API;
   
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Http\Controllers\API\BaseController as BaseController;
use App\Models\User;

class LoginController extends BaseController
{
    /**
     * Login api
     *
     * @return \Illuminate\Http\Response
     */
    public function login(Request $request)
    {
        if(Auth::attempt(['email' => $request->email, 'password' => $request->password])){ 
            $user = Auth::user(); 
            $success['token'] =  $user->createToken('MyApp')-> accessToken; 
            $success['name'] =  $user->name;
   
            return $this->sendResponse($success, 'User login successfully.');
        } 
        else{ 
            return $this->sendError('Unauthorised.', ['error'=>'Unauthorised']);
        } 
    }


    /**
     * Controller untuk Mobile 
     *
     * @return \Illuminate\Http\Response
     */
    public function login_flutter(Request $request)
    {
        if(Auth::attempt(['email' => $request->email, 'password' => $request->password])){ 
            $user = Auth::user(); 
            $success['token'] = $user->createToken('flutter-app-token')->accessToken;
            $success['name'] =  $user->name;

            // $division = $user->divisions()->first();
            // if ($division) {
            //     $success['division_id'] = $division->id;
            //     $success['division_name'] = $division->name;
            // } else {
            //     $success['division_id'] = null;
            //     $success['division_name'] = "No Division Assigned";
            // }
            $success['divisions'] = $user->divisions->map(function ($div) {
                return [
                    'id' => $div->id,
                    'name' => $div->name
                ];
            });

            if ($user->role) {
                $success['role'] = [
                    'id'   => $user->role->id,
                    'name' => $user->role->name,
                    'slug' => $user->role->slug,
                ];
            } else {
                $success['role'] = null;
            }
    
            return $this->sendResponse($success, 'User login successfully.');
        } 
        else{ 
            return $this->sendError('Unauthorised.', ['error'=>'Unauthorised']);
        } 
    }

    //Controller logout untuk mobile
    public function logout(Request $request)
    {
        $token = $request->user()->token();
        $token->revoke();

        return $this->sendResponse([], 'User logged out successfully.');
    }
}
