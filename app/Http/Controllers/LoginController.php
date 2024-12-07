<?php

namespace App\Http\Controllers;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

use DB;
use Carbon\Carbon;
use App\Models\Logs;
use App\Models\User;
use App\Models\Admin;

class LoginController extends Controller
{
    /**
     * Show User Login Page.
     *
     * @param  Request  $request
     * @return View
     */
    public function index(Request $request)
    {   
        if($request->session()->has('userID')){
            return redirect()->route('dashboard');
        }
        return view('users.login');
    }

    /**
     * Authenticate User Login.
     *
     * @param  Request  $request
     * @return Response
     */
    public function authenticate(Request $request)
    {
        $request->validate([
            'email'=> 'required|email|exists:users,email',
            'password'=> 'required|min:6|max:20',
            'user_type'=> 'required|in:MST,MT,IDC,ET,N',
        ]);
        
        $logs = new Logs;
        $credentials = $request->only('email', 'password');
        $remember = $request->has('remember') ? true : false;
        if(Auth::attempt($credentials, $remember)){
            $userInfo = Auth::user();
            if($request->user_type === $userInfo->user_type){                     
                $countNum = $logs->select('user_id')->where('user_id', '=', $userInfo->id)->get()->count('user_id');                    
                if($countNum>0){
                    // Set UserID in to session
                    $request->session()->put('userID',$userInfo->id);
                    // Entry in logs table
                    $logs->user_id = $userInfo->id;
                    $logs->logs_area = logsArea("User logged in.");
                    $logs->ip_address = $request->ip();
                    $logs->save();
                    return redirect()->route('dashboard');
                }else{
                    $token= Str::random(64);
                    DB::table('password_resets')->insert(['email'=>$userInfo->email, 'token'=>$token, 'created_at'=>Carbon::now()]);
                    return redirect()->route('reset-password',$token);
                }
            }else{
                return back()->with('fail', 'Incorrect user type!')->withInput();
            }
        }else{
            return back()->with('fail', 'Email or password is wrong.')->withInput();
        }
    }

     /**
     * User Logout to Login Page.
     *
     * @param  Request  $request
     * @return Response
     */
    public function logout(Request $request)
    {
        $logs = new Logs;
        // Entry in logs table
        $logs->user_id = $request->session()->get('userID');
        $logs->logs_area = logsArea("User logout.");
        $logs->ip_address = $request->ip();
        $logs->save();

        Auth::logout();
        $request->session()->invalidate();
        return redirect()->route('login')->with('success','User has been logged out successfully!');
    }


    /**
     * Show Admin Login Page.
     *
     * @param  Request  $request
     * @return View
     */
    public function admin_login(Request $request)
    {   
        if($request->session()->has('adminID')){
            return  redirect()->route('admin-dashboard');
        }
        return view('admin.login');
    }

     /**
     * Authenticate Admin Login.
     *
     * @param  Request  $request
     * @return Response
     */
    public function admin_authenticate(Request $request)
    {
        $request->validate([
            'email'=> 'required|email',
            'password'=> 'required|min:6|max:20'
        ]);

        $logs = new Logs;
        $adminInfo = Admin::where('email', '=', $request->email)->first();        

        if(!$adminInfo){
            return back()->with('fail', 'Email does not exist!')->withInput();
        }else{
            if(Hash::check($request->password, $adminInfo->password)){
                $countNum = $logs->select('admin_id')->where('admin_id', '=', $adminInfo->id)->get()->count('admin_id');
                    if($countNum>0){
                        // Set AdminID in to session
                        $request->session()->put('adminID',$adminInfo->id);
                        // Entry in logs table
                        $logs->admin_id = $adminInfo->id;
                        $logs->logs_area = logsArea("Admin logged in.");
                        $logs->ip_address = $request->ip();
                        $logs->save();
                        return redirect()->route('admin-dashboard');
                    }else{
                        $token= Str::random(64);
                        DB::table('password_resets')->insert(['email'=>$adminInfo->email, 'token'=>$token, 'created_at'=>Carbon::now()]);
                        return redirect()->route('admin-reset-password',$token);
                    }
            }else{
                return back()->with('fail', 'Incorrect password!')->withInput();
            }
        }
    }

    /**
     * Admin Logout to Login Page.
     *
     * @param  Request  $request
     * @return Response
     */
    public function admin_logout(Request $request)
    {
        $logs = new Logs;
        // Entry in logs table
        $logs->admin_id = $request->session()->get('adminID');
        $logs->logs_area = logsArea("Admin logout.");
        $logs->ip_address = $request->ip();
        $logs->save();

        Auth::logout();
        $request->session()->invalidate();
        return redirect()->route('admin-login')->with('success','Admin has been logged out successfully!');
    }
}
