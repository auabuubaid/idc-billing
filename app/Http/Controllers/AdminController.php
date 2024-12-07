<?php

namespace App\Http\Controllers;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

use DB;
use Carbon\Carbon;
use App\Models\Admin;
use App\Models\Logs;

class AdminController extends Controller
{
    /**
     * Show Admin Dashboard Page.
     *
     * @param  Request  $request
     * @return View
     */
    public function dashboard(Request $request)
    {   
        $admin = new Admin;
        $admin_arr= $admin->where('id', $request->session()->get('adminID'))->first();

        return view('admin.dashboard')
                    ->with(['admin_arr'=>$admin_arr]);
    }

    /**
     * Show Reset Password Page.
     *
     * @param  Request  $request
     * @return View
     */
    public function forgot_password()
    {        
        return view('admin.forgot_password');
    }

    /**
     * Validate Admin Email.
     *
     * @param  Request  $request
     * @return Response
     */
    public function validate_admin_email(Request $request)
    {
        $request->validate([
            'email'=> 'required|email|exists:admins'
        ]);

        $adminInfo = Admin::where('email', '=', $request->email)->first();
            
        if(!$adminInfo){
            return back()->with('fail', 'We can not find a admin with that email address!');
        }else{
            $token = Str::random(64);
            DB::table('password_resets')->insert(['email'=>$request->email, 'token'=>$token, 'created_at'=>Carbon::now()]);
            return redirect()->route('admin-reset-password',$token);
        }
    }

    /**
     * Reset Admin Password Page.
     *
     * @param  Request  $request
     * @return View
     */
    public function reset_password(Request $request)
    {

        $token = $request->segment(3);
        $adminArr = DB::table('password_resets')->where(['token'=>$token])->first();
        if(!$adminArr){
            return redirect()->route('admin-forgot-password',$token)->with('fail','This password reset token is invalid, Please try again!');
        }else{
            return view('admin.reset_password')->with(['email'=>$adminArr->email,'token'=>$token]);
        }
    }

    /**
     * Reset Admin Password.
     *
     * @param  Request  $request
     * @return Response
     */
    public function recover_password(Request $request)
    {   
        $rules = array(
            'password'=> 'required|min:6|max:20',
            'confirm_password'=> 'required|min:6|max:20|same:password',
        );        
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            // validation unsuccessful!
            return back()->withErrors($validator)->with('fail', 'Please fill all mandatory fields.');           
        } else {
            // validating token in password_resets table
            $adminArr = DB::table('password_resets')->where(['email'=>$request->email,'token'=>$request->token])->first();
            if(!$adminArr){
                return redirect()->route('admin-forgot-password')->with('fail','Invalid token, Please try again!');
            }else{
                $logs = new Logs;
                $admin = new Admin;

                $admin_Arr = $admin->where('email', $adminArr->email)->first();
                // resetting password
                $flag= $admin->where('email',$adminArr->email)->update(['password'=>Hash::make($request->password)]);
                if($flag){
                    // deleting token which already reset password
                    DB::table('password_resets')->where(['email'=>$adminArr->email])->delete();

                    // Entry in logs table
                    $logs->admin_id = $admin_Arr->id;
                    $logs->logs_area = logsArea("Admin reset password.");
                    $logs->ip_address = $request->ip();
                    $logs->save();
                    return redirect()->route('admin-login')->with('success','Password reset successfully!');
                }else{
                    return redirect()->route('admin-login')->with('fail','Something went wrong, Please try again!');
                }
            }           
       }
    }    
}
