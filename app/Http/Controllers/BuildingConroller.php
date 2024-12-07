<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

use Maatwebsite\Excel\Facades\Excel;

use App\Models\Logs;
use App\Models\Admin;
use App\Models\BuildingLocation;
use App\Imports\BuildingsLocationImport;

class BuildingConroller extends Controller
{
    /**
     * Show Building Location Listing Page.
     *
     * @param  Request  $request
     * @return View
     */
    public function index(Request $request)
    {
        $admin = new Admin;
		$building_location = new BuildingLocation;   

        $admin_arr= $admin->where('id', $request->session()->get('adminID'))->first();
        $records= !empty($request->input('records')) ? $request->input('records') : env('PER_PAGE_RECORDS');
        $buildings_location_arr = $building_location->paginate($records)->withQueryString();
        
        return view('admin.building.index')
                ->with(['buildings_location_arr'=>$buildings_location_arr, 'admin_arr'=>$admin_arr, 'records'=>$records]);
    }

    /**
     * Show Buildings Location Import page.
     *
     * @param  Request  $request
     * @return View
     */
    public function import_building_location(Request $request)
    {
        $admin = new Admin;
        $admin_arr= $admin->where('id', $request->session()->get('adminID'))->first();
        
        if(!checkPermission($admin_arr->upload)){
            // Permission not granted
            Session::flash('color','warning');
            Session::flash('icon','fa fa-exclamation-triangle');
            Session::flash('msg','Upload permission not granted!');
            return redirect()->route('buildings-location');
        }
            return view('admin.building.import')
                        ->with(['admin_arr'=>$admin_arr]);
    }

    /**
     * Validate & Import Buildings Location.
     *
     * @param  Request  $request
     * @return Response
     */
    public function store_import_buildings_location(Request $request)
    {
        $rules= array(
            'import_file'=> 'required|mimes:xlsx,xls',
        );
        $validator = Validator::make($request->all(), $rules);
        
        if ($validator->fails()) {
            // validation unsuccessful!
            Session::flash('color','warning');
            Session::flash('icon','fas fa-exclamation-triangle');
            Session::flash('msg','Please fill all mandatory fields!');             
            return redirect()->route('import-buildings-location')
                    ->withErrors($validator) 
                        ->withInput();
        } else {

            $logs = new Logs;
            Excel::import(new BuildingsLocationImport,request()->file('import_file'));
            
            // Entry in logs table
            $logs->admin_id = $request->session()->get('adminID');
            $logs->logs_area = logsArea("Buildings location import has done.");
            $logs->ip_address = $request->ip();
            $logs->save();

            Session::flash('color','success');
            Session::flash('icon','fa fa-check');
            Session::flash('msg','Import has done successfully.');
            return redirect()->route('buildings-location');
        }
    }

    /**
     * Show Add Building Location Page.
     *
     * @param  Request  $request
     * @return View
     */
    public function add_building_location(Request $request){

        $admin = new Admin;
        $admin_arr= $admin->where('id', $request->session()->get('adminID'))->first();

        if(!checkPermission($admin_arr->write)){
            // Permission not granted
            Session::flash('color','warning');
            Session::flash('icon','fa fa-exclamation-triangle');
            Session::flash('msg','Write permission not granted!');
            return redirect()->route('buildings-location');
        }
        return view('admin.building.add')
                ->with(['admin_arr'=>$admin_arr]);
    }

     /**
     *  Validate & Store Building Location.
     *
     * @param  Request  $request
     * @return Response
     */
    public function store_building_location(Request $request)
    {
        $rules= array(
            'location'=> 'required',
            'type'=> 'required',
            'name'=> 'required|alpha_dash',
            'sort_order'=> !empty($request->input('sort_order')) ? 'required|numeric|integer' : '',
            'status'=> 'required|in:A,N'
        );
        $validator = Validator::make($request->all(), $rules);
        
        if ($validator->fails()) {
             // validation unsuccessful!
             Session::flash('color','warning');
             Session::flash('icon','fas fa-exclamation-triangle');
             Session::flash('msg','Please fill all mandatory fields!');             
            return redirect()->route('add-building-location')
                    ->withErrors($validator) 
                        ->withInput();            
        } else {
            // saving building in database
			$logs = new Logs;
            $building_location = new BuildingLocation;

            $building_location->location = $request->input('location');
            $building_location->type = $request->input('type');
            $building_location->name = $request->input('name');            
            $building_location->sort_order = $request->input('sort_order');
            $building_location->status = $request->input('status');            
            $flag = $building_location->save();

			if ($flag) {
                // Entry in logs table
                $logs->admin_id = $request->session()->get('adminID');
                $logs->logs_area = logsArea("Added new building location reference id [".$building_location->id."].");
                $logs->ip_address = $request->ip();
                $logs->save();

                // validation successful!
                Session::flash('color','success');
                Session::flash('icon','fa fa-check');
                Session::flash('msg','Record has been added successfully!');
				return redirect()->route('buildings-location');
			} else {
                // database insertion was not successful, send back to add form
                Session::flash('color','danger');
                Session::flash('icon','fa fa-ban');
                Session::flash('msg','Record is not added, please try again!');
				return redirect()->route('add-building-location');
			}
			
        }
    }

    /**
     * Show View Building Location Page.
     *
     * @param  Request  $request
     * @return View
     */
    public function view_building_location(Request $request)
    {
        $admin = new Admin;
        $building_location= new BuildingLocation;
        $id= $request->segment(2);

        $admin_arr = $admin->where('id', $request->session()->get('adminID'))->first();
        $building_location_arr = $building_location->where('id', $id)->first();

        if(!checkPermission($admin_arr->read)){
            // Permission not granted
            Session::flash('color','warning');
            Session::flash('icon','fa fa-exclamation-triangle');
            Session::flash('msg','Read permission not granted!');
            return redirect()->route('buildings-location');
        }
        return view('admin.building.view')
                ->with(['building_location_arr'=>$building_location_arr, 'admin_arr'=>$admin_arr]);
    }

    /**
     * Show Edit Building Location Page.
     *
     * @param  Request  $request
     * @return View
     */
    public function edit_building_location(Request $request)
    {
        $admin = new Admin;
        $building_location = new BuildingLocation;
        $id = $request->segment(2);

        $admin_arr = $admin->where('id', $request->session()->get('adminID'))->first();
        $building_location_arr = $building_location->where('id', $id)->first();

        if(!checkPermission($admin_arr->write)){
            // Permission not granted
            Session::flash('color','warning');
            Session::flash('icon','fa fa-exclamation-triangle');
            Session::flash('msg','Write permission not granted!');
            return redirect()->route('buildings-location');
        }
        return view('admin.building.edit')
                ->with(['building_location_arr'=>$building_location_arr, 'admin_arr'=>$admin_arr]);
    }

    /**
     * Validate & Update Building Location.
     *
     * @param  Request  $request
     * @return Response
     */
    public function update_building_location(Request $request)
    {
        $rules= array(
            'location'=> 'required',
            'type'=> 'required',
            'name'=> 'required|alpha_dash',
            'sort_order'=> !empty($request->input('sort_order')) ? 'required|numeric|integer' : '',
            'status'=> 'required|in:A,N'
        );
        $validator = Validator::make($request->all(), $rules);
        $id=$request->input('referenceID');

        if ($validator->fails()) {
             // validation unsuccessful!
             Session::flash('color','warning');
             Session::flash('icon','fas fa-exclamation-triangle');
             Session::flash('msg','Please fill all mandatory fields!');             
            return redirect()->route('edit-building-location', ['id' => $id])
                    ->withErrors($validator) 
                        ->withInput();            
        } else {
            // saving building in database
            $logs = new Logs;
			$building_location = new BuildingLocation;

            $flag = $building_location->where('id',$id)
                        ->update([
                            'location'=>$request->input('location'),
                            'type'=>$request->input('type'),
                            'name'=>$request->input('name'),
                            'sort_order'=>$request->input('sort_order'),
                            'status'=>$request->input('status')
                        ]);
			if ($flag) {
                // Entry in logs table
                $logs->admin_id = $request->session()->get('adminID');
                $logs->logs_area = logsArea("Updated building location reference id [".$id."].");
                $logs->ip_address = $request->ip();
                $logs->save();

                // validation successful!
                Session::flash('color','success');
                Session::flash('icon','fa fa-check');
                Session::flash('msg','Record has been updated successfully!');
                return redirect()->route('edit-building-location', ['id' => $id]);
			} else {
                // database insertion was not successful, send back to add form
                Session::flash('color','danger');
                Session::flash('icon','fa fa-ban');
                Session::flash('msg','Record is not updated, please try again!');
				return redirect()->route('edit-building-location', ['id' => $id]);
			}
			
        }
    }

    /**
     * Delete Building Location.
     *
     * @param  Request  $request
     * @return Response
     */
    public function destroy_building_location(Request $request)
    {
        $logs = new Logs;
        $admin = new Admin;
        $building_location = new BuildingLocation;
        $admin_arr = $admin->where('id', $request->session()->get('adminID'))->first();
        $id = $request->segment(2);
        if(!checkPermission($admin_arr->delete)){
            // Permission not granted
            Session::flash('color','warning');
            Session::flash('icon','fa fa-exclamation-triangle');
            Session::flash('msg','Delete permission not granted!');
            return redirect()->route('buildings-location');
        }
        $flag = $building_location->where('id', $id)->delete();

        if ($flag) {
            // Entry in logs table
            $logs->admin_id = $request->session()->get('adminID');
            $logs->logs_area = logsArea("Deleted building location reference id [".$id."].");
            $logs->ip_address = $request->ip();
            $logs->save();

            // database deletion was successful, send back to listing form
            Session::flash('color','success');
            Session::flash('icon','fa fa-check');
            Session::flash('msg','Record has been deleted successfully!');
            return redirect()->route('buildings-location');
        } else {
            // database deletion was not successful, send back to listing form
            Session::flash('color','danger');
            Session::flash('icon','fa fa-ban');
            Session::flash('msg','Record is not deleted, please try again!');
            return redirect()->route('buildings-location');
        }
    }
}
