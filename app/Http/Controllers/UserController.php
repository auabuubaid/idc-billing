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
use App\Models\User;
use App\Models\Logs;
use App\Models\Admin;
use App\Models\UnitAddress;

class UserController extends Controller
{
    /**
     * Show User Dashboard Page.
     *
     * @param  Request  $request
     * @return View
     */
    public function dashboard(Request $request)
    {   
        $user = new User;
        $unit_address = new UnitAddress;
        $userArr = $user->where('id', $request->session()->get('userID'))->first();
        $unitsGroupAddressArr = $unit_address->where('status','A')->get()->groupBy('location_id');

        if(!$request->session()->has('userID')){
            redirect()->route('login');
        }
        $iyear = currentYear();
        $cyear = currentYear();

        if($request->input('iyear')){
            $iyear = $request->input('iyear');
        }
        if($request->input('cyear')){
            $cyear = $request->input('cyear');
        }
        $folder = userTypeAbbreviationToFullName($userArr->user_type)['folder'];
        return view('users.'.$folder.'.dashboard')
                    ->with(['userArr'=>$userArr,'iyear'=>$iyear, 'cyear'=>$cyear, 'unitsAddressArr'=>$unitsGroupAddressArr]);
    }

    /**
     * Show Reset Password Page.
     *
     * @param  Request  $request
     * @return View
     */
    public function forgot_password()
    {   
        return view('users.forgot_password');
    }

    /**
     * Validate User Email.
     *
     * @param  Request  $request
     * @return Response
     */
    public function validate_email(Request $request)
    {
        $request->validate([
            'email'=> 'required|email|exists:users'
        ]);

        $userInfo = User::where('email', '=', $request->email)->first();
           
        if(!$userInfo){
            return back()->with('fail', 'We can not find a user with that email address!');
        }else{
            $token= Str::random(64);
            DB::table('password_resets')->insert(['email'=>$request->email, 'token'=>$token, 'created_at'=>Carbon::now()]);
            return redirect()->route('reset-password',$token);
        }
    }

    /**
     * Reset User Password Page.
     *
     * @param  Request  $request
     * @return View
     */
    public function reset_password(Request $request)
    {
        $token= $request->segment(2);
        $userArr= DB::table('password_resets')->where(['token'=>$token])->first();
        if(!$userArr){
            return redirect()->route('forgot-password')->with('fail','This password reset token is invalid, Please try again!');
        }else{
            return view('users.reset_password')->with(['email'=>$userArr->email,'token'=>$token]);
        }
    }

    /**
     * Reset User Password.
     *
     * @param  Request  $request
     * @return Response
     */
    public function recover_password(Request $request)
    {   
        $rules= array(
            'password'=> 'required|min:6|max:20',
            'confirm_password'=> 'required|min:6|max:20|same:password',
        );
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return back()->withErrors($validator)->with('fail', 'Please fill all mandatory fields.');
           
        } else {
            // validating token in password_resets table
            $userArr= DB::table('password_resets')->where(['email'=>$request->email,'token'=>$request->token])->first();
            if(!$userArr){
                return redirect()->route('forgot-password')->with('fail','Invalid token, Please try again!');
            }else{
                $user = new User;
                $logs = new Logs;
                $user_Arr = $user->where('email', $userArr->email)->first();
                // resetting password
                $flag = $user->where('email',$userArr->email)->update(['password'=>Hash::make($request->password)]);
                if($flag){
                    // deleting token which already reset password
                    DB::table('password_resets')->where(['email'=>$userArr->email])->delete();
                    
                    // Entry in logs table
                    $logs->user_id = $user_Arr->id;
                    $logs->logs_area = logsArea("User reset password.");
                    $logs->ip_address = $request->ip();
                    $logs->save();
                    return redirect()->route('login')->with('success','Password reset successfully!');
                }else{
                    return redirect()->route('login')->with('fail','Something went wrong, Please try again!');
                }
            }           
        }
    }

    /**
    * Show Users Page.
    *
    * @param  Request  $request
    * @return View
    */
    public function index(Request $request)
    {       
        $user = new User;
        $admin = new Admin;

        $adminArr= $admin->where('id', $request->session()->get('adminID'))->first();
        $records = !empty($request->input('records')) ? $request->input('records') : env('PER_PAGE_RECORDS'); 
        $userArr= $user->paginate($records)->withQueryString();
            
        return view('admin.users.index')
                ->with(['userArr'=>$userArr, 'admin_arr'=>$adminArr, 'records'=>$records]);
    }

    /**
     * Show Add User page.
     *
     * @param  Request  $request
     * @return View
     */
    public function add(Request $request)
    {   
        $admin = new Admin;
        
        $adminArr= $admin->where('id', $request->session()->get('adminID'))->first();
        
        if(!checkPermission($adminArr->write)){
            // Permission not granted
            Session::flash('color','warning');
            Session::flash('icon','fa fa-exclamation-triangle');
            Session::flash('msg','Write permission not granted!');
            return redirect()->route('users');
        }
        return view('admin.users.add')
                    ->with(['admin_arr'=>$adminArr]);
    }

    /**
     * Validate & Store User.
     *
     * @param  Request  $request
     * @return Response
     */
    public function store(Request $request)
    {
        $rules = array(
            'user_name'=> 'required|string',
            'user_email'=> 'required|email|unique:users,email',
            'department'=> 'required|in:MST,MT,IDC,ET,N',
            'user_password'=> 'required',
            'read_permission'=> 'required|in:Y,N',
        );
        $validator = Validator::make($request->all(), $rules);
        
        if ($validator->fails()) {
            // validation unsuccessful!
            Session::flash('color','warning');
            Session::flash('icon','fas fa-exclamation-triangle');
            Session::flash('msg','Please fill all mandatory fields!');             
            return redirect()->route('add-user')
                    ->withErrors($validator) 
                        ->withInput();            
        } else {
            // saving resolution payment in database
            $logs = new Logs;
			$user = new User;
            
            $user->name = $request->input('user_name');
            $user->email = $request->input('user_email');
            $user->user_type = $request->input('department');
            $user->password = Hash::make($request->input('user_password'));
            $user->read = ($request->input('read_permission')=='on') ? 'Y' : 'N';
            $user->write = ($request->input('write_permission')=='on') ? 'Y' : 'N';
            $user->delete = ($request->input('delete_permission')=='on') ? 'Y' : 'N';
            $user->download = ($request->input('download_permission')=='on') ? 'Y' : 'N';
            $user->upload = ($request->input('upload_permission')=='on') ? 'Y' : 'N';            
            $flag = $user->save();

			if ($flag) {
                // Entry in logs table
                $logs->admin_id = $request->session()->get('adminID');
                $logs->logs_area = logsArea("Added new user reference id [".$user->id."].");
                $logs->ip_address = $request->ip();
                $logs->save();

                // validation successful!
                Session::flash('color','success');
                Session::flash('icon','fa fa-check');
                Session::flash('msg','Record has been added successfully!');
				return redirect()->route('users');
			} else {
                // database insertion was not successful, send back to add form
                Session::flash('color','danger');
                Session::flash('icon','fa fa-ban');
                Session::flash('msg','Record is not added, please try again!');
				return redirect()->route('add-user');
			}
        }
    }

    /**
     * Show View User Page.
     *
     * @param  Request  $request
     * @return View
     */
    public function view(Request $request)
    {
        $user = new User;
        $admin = new Admin;

        $adminArr = $admin->where('id', $request->session()->get('adminID'))->first();
        
        if(!checkPermission($adminArr->read)){
            // Permission not granted
            Session::flash('color','warning');
            Session::flash('icon','fa fa-exclamation-triangle');
            Session::flash('msg','Read permission not granted!');
            return redirect()->route('users');
        }

        $id = $request->segment(2);
        $userArr = $user->where('id', $id)->first();

        return view('admin.users.view')
                    ->with(['userArr'=>$userArr,'admin_arr'=>$adminArr]);
    }

    /**
     * Show Edit User Page.
     *
     * @param  Request  $request
     * @return View
     */
    public function edit(Request $request)
    {
        $user = new User;
        $admin = new Admin;

        $adminArr = $admin->where('id', $request->session()->get('adminID'))->first();
        
        if(!checkPermission($adminArr->write)){
            // Permission not granted
            Session::flash('color','warning');
            Session::flash('icon','fa fa-exclamation-triangle');
            Session::flash('msg','Write permission not granted!');
            return redirect()->route('users');
        }
        
        $id = $request->segment(2);
        $userArr = $user->where('id', $id)->first();

        return view('admin.users.edit')
                    ->with(['userArr'=>$userArr,'admin_arr'=>$adminArr]);
    }

    /**
     * Validate & Update User.
     *
     * @param  Request  $request
     * @return Response
     */
    public function update(Request $request)
    {   
        $rules = array(
            'user_name'=> 'required|string',
            'department'=> 'required|in:MST,MT,IDC,ET,N',
            'read_permission'=> 'required:in:Y,N',
        );
        $validator = Validator::make($request->all(), $rules);
        $id = $request->input('referenceID');

        if ($validator->fails()) {
            // validation unsuccessful!
            Session::flash('color','warning');
            Session::flash('icon','fas fa-exclamation-triangle');
            Session::flash('msg','Please fill all mandatory fields!');             
            return redirect()->route('edit-user', ['id' => $id])
                    ->withErrors($validator) 
                        ->withInput();            
        } else {
            // saving building in database
            $logs = new Logs;
			$user = new User;

            $readPermission=($request->input('read_permission')=="on") ? "Y" : "N";
            $writePermission=($request->input('write_permission')=="on") ? "Y" : "N";
            $deletePermission=($request->input('delete_permission')=="on") ? "Y" : "N";
            $downloadPermission=($request->input('download_permission')=="on") ? "Y" : "N";
            $uploadPermission=($request->input('upload_permission')=="on") ? "Y" : "N";

            $flag = $user->where('id',$id)->
                        update([
                            'name'=>$request->input('user_name'),
                            'user_type'=>$request->input('department'),
                            'read'=>$readPermission,
                            'write'=>$writePermission,
                            'delete'=>$deletePermission,
                            'download'=>$downloadPermission,
                            'upload'=>$uploadPermission 
                        ]);
			if ($flag) {                
                // Entry in logs table
                $logs->admin_id = $request->session()->get('adminID');
                $logs->logs_area = logsArea("Updated user details reference id [".$id."].");
                $logs->ip_address = $request->ip();
                $logs->save();

                // database insertion was successful, send back to edit form
                Session::flash('color','success');
                Session::flash('icon','fa fa-check');
                Session::flash('msg','Record has been updated successfully!');
				return redirect()->route('edit-user', ['id' => $id]);
			} else {
                // database insertion was not successful, send back to edit form
                Session::flash('color','danger');
                Session::flash('icon','fa fa-ban');
                Session::flash('msg','Record is not updated, please try again!');
				return redirect()->route('edit-user', ['id' => $id]);
			}
			
        }
    }

    /**
     * Delete User.
     *
     * @param  Request  $request
     * @return View
     */
    public function destroy(Request $request)
    {
        $logs = new Logs;
        $user = new User;
        $admin = new Admin;
        
        $adminArr = $admin->where('id', $request->session()->get('adminID'))->first();

        if(!checkPermission($adminArr->delete)){
            // Permission not granted
            Session::flash('color','warning');
            Session::flash('icon','fa fa-exclamation-triangle');
            Session::flash('msg','Delete permission not granted!');
            return redirect()->route('users');
        }

        $id = $request->segment(2);
        $flag = $user->where('id', $id)->delete();

        if ($flag) {
            // Entry in logs table
            $logs->admin_id = $request->session()->get('adminID');
            $logs->logs_area = logsArea("Deleted user reference id [".$id."].");
            $logs->ip_address = $request->ip();
            $logs->save();

            // database deletion was successful, send back to listing form
            Session::flash('color','success');
            Session::flash('icon','fa fa-check');
            Session::flash('msg','Record has been deleted successfully!');            
            return redirect()->route('users');
        } else {
            // database deletion was not successful, send back to listing form
            Session::flash('color','danger');
            Session::flash('icon','fa fa-ban');
            Session::flash('msg','Record is not deleted, please try again!');
            return redirect()->route('users');
        }
    }
}
