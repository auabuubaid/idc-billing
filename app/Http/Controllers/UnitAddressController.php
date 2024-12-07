<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

use Maatwebsite\Excel\Facades\Excel;

use App\Models\Logs;
use App\Models\Admin;
use App\Models\UnitAddress;
use App\Models\BuildingLocation;
use App\Imports\UnitsAddressImport;

class UnitAddressController extends Controller
{
    /**
     * Show Units Address Listing Page.
     *
     * @param  Request  $request
     * @return View
     */
    public function index(Request $request)
    {
        $admin = new Admin;
        $unit_address = new UnitAddress;
        
        $admin_arr = $admin->where('id', $request->session()->get('adminID'))->first();
        $records = !empty($request->input('records')) ? $request->input('records') : env('PER_PAGE_RECORDS');
        $units_address_arr = $unit_address->paginate($records)->withQueryString();

        return view('admin.unit_address.index')
                    ->with(['units_address_arr'=>$units_address_arr, 'admin_arr'=>$admin_arr, 'records'=>$records]);
    }

    /**
     * Show Units Address Import page.
     *
     * @param  Request  $request
     * @return View
     */
    public function import_units_address(Request $request)
    {   
        $admin = new Admin;
        $admin_arr = $admin->where('id', $request->session()->get('adminID'))->first();
        
        if(!checkPermission($admin_arr->upload)){
            // Permission not granted
            Session::flash('color','warning');
            Session::flash('icon','fa fa-exclamation-triangle');
            Session::flash('msg','Upload permission not granted!');
            return redirect()->route('units-address');
        }
            return view('admin.unit_address.import')
                        ->with(['admin_arr'=>$admin_arr]);
    }

    /**
     * Validate & Import Units Address.
     *
     * @param  Request  $request
     * @return Response
     */
    public function store_import_units_address(Request $request)
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
            return redirect()->route('import-units-address')
                    ->withErrors($validator) 
                        ->withInput();            
        } else {

            $logs = new Logs;
            Excel::import(new UnitsAddressImport,request()->file('import_file'));
            
            // Entry in logs table
            $logs->admin_id = $request->session()->get('adminID');
            $logs->logs_area = logsArea("Units address import has done.");
            $logs->ip_address = $request->ip();
            $logs->save();

            Session::flash('color','success');
            Session::flash('icon','fa fa-check');
            Session::flash('msg','Import has done successfully.');
            return redirect()->route('units-address');
        }
    }

    /**
     * Show Add Unit Address Page.
     *
     * @param  Request  $request
     * @return View
     */
    public function add_unit_address(Request $request)
    {   
        $admin = new Admin;
        $building_location = new BuildingLocation;
        
        $admin_arr = $admin->where('id', $request->session()->get('adminID'))->first();
        $building_location_arr = $building_location->where('status', 'A')->get();

        if(!checkPermission($admin_arr->write)){
            // Permission not granted
            Session::flash('color','warning');
            Session::flash('icon','fa fa-exclamation-triangle');
            Session::flash('msg','Write permission not granted!');
            return redirect()->route('units-address');
        }
        
        return view('admin.unit_address.add')
                ->with(['building_location_arr'=>$building_location_arr,'admin_arr'=>$admin_arr]);
    }

     /**
     * Validate & Store Unit Address.
     *
     * @param  Request  $request
     * @return Response
     */
    public function store_unit_address(Request $request)
    {
        $rules= array(
            'location'=> 'required',
            'unit_number'=> 'required|alpha_num',
            'sort_order'=> !empty($request->input('sort_order')) ? 'required|numeric|integer' : '',
            'status'=> 'required|in:A,N'
        );
        $validator = Validator::make($request->all(), $rules);
        
        if ($validator->fails()) {
            // validation unsuccessful!
            Session::flash('color','warning');
            Session::flash('icon','fas fa-exclamation-triangle');
            Session::flash('msg','Please fill all mandatory fields!');             
            return redirect()->route('add-unit-address')
                    ->withErrors($validator) 
                        ->withInput();            
        } else {
            // saving unit address in database
            $logs = new Logs;
			$unit_address = new UnitAddress;

            $unit_address->location_id = $request->input('location');
            $unit_address->unit_number = $request->input('unit_number');            
            $unit_address->sort_order = $request->input('sort_order');
            $unit_address->status = $request->input('status');            
            $flag = $unit_address->save();

			if ($flag) {
                // Entry in logs table
                $logs->admin_id = $request->session()->get('adminID');
                $logs->logs_area = logsArea("Added new unit address reference id [".$unit_address->id."].");
                $logs->ip_address = $request->ip();
                $logs->save();

                // validation successful!
                Session::flash('color','success');
                Session::flash('icon','fa fa-check');
                Session::flash('msg','Record has been added successfully!');
				return redirect()->route('units-address');
			} else {
                // database insertion was not successful, send back to add form
                Session::flash('color','danger');
                Session::flash('icon','fa fa-ban');
                Session::flash('msg','Record is not added, please try again!');
				return redirect()->route('add-unit-address');
			}
			
        }
    }

    /**
     * Show Unit Address View Page.
     *
     * @param  Request  $request
     * @return View
     */
    public function view_unit_address(Request $request)
    {
        $admin = new Admin;
        $unit_address = new UnitAddress;

        $id = $request->segment(2);
        $admin_arr = $admin->where('id', $request->session()->get('adminID'))->first();
        $unit_address_arr = $unit_address->where('id', $id)->first();

        if(!checkPermission($admin_arr->read)){
            // Permission not granted
            Session::flash('color','warning');
            Session::flash('icon','fa fa-exclamation-triangle');
            Session::flash('msg','Read permission not granted!');
            return redirect()->route('units-address');
        }

        return view('admin.unit_address.view')
                ->with(['unit_address_arr'=>$unit_address_arr, 'admin_arr'=>$admin_arr]);
    }


    /**
     * Show Unit Address Edit Page.
     *
     * @param  Request  $request
     * @return View
     */
    public function edit_unit_address(Request $request)
    {
        $admin = new Admin;
        $unit_address = new UnitAddress;
        $building_location = new BuildingLocation;
        
        $id = $request->segment(2);
        $admin_arr = $admin->where('id', $request->session()->get('adminID'))->first();
        $building_location_arr = $building_location->where('status', 'A')->get();
        $unit_address_arr = $unit_address->where('id', $id)->first();

        if(!checkPermission($admin_arr->write)){
            // Permission not granted
            Session::flash('color','warning');
            Session::flash('icon','fa fa-exclamation-triangle');
            Session::flash('msg','Write permission not granted!');
            return redirect()->route('units-address');
        }

        return view('admin.unit_address.edit')
                ->with(['unit_address_arr'=>$unit_address_arr, 'building_location_arr'=>$building_location_arr, 'admin_arr'=>$admin_arr]);
    }

     /**
     * Validate & Update Unit Address.
     *
     * @param  Request  $request
     * @return Response
     */
    public function update_unit_address(Request $request)
    {
        $rules= array(
            'location'=> 'required',
            'unit_number'=> 'required|alpha_num',
            'sort_order'=> !empty($request->input('sort_order')) ? 'required|numeric|integer' : '',
            'status'=> 'required|in:A,N'
        );
        $validator = Validator::make($request->all(), $rules);
        $id = $request->input('referenceID');

        if ($validator->fails()) {
            // validation unsuccessful!
            Session::flash('color','warning');
            Session::flash('icon','fas fa-exclamation-triangle');
            Session::flash('msg','Please fill all mandatory fields!');             
            return redirect()->route('edit-unit-address',['id' => $id])
                    ->withErrors($validator) 
                        ->withInput();            
        } else {
            // saving building in database
            $logs = new Logs;
			$unit_address = new UnitAddress;

            $flag = $unit_address->where('id',$id)
                        ->update([
                            'location_id'=>$request->input('location'),
                            'unit_number'=>$request->input('unit_number'),
                            'sort_order'=>$request->input('sort_order'),
                            'status'=>$request->input('status')
                        ]);
			if ($flag) {
                // Entry in logs table
                $logs->admin_id = $request->session()->get('adminID');
                $logs->logs_area = logsArea("Updated unit address reference id [".$id."].");
                $logs->ip_address = $request->ip();
                $logs->save();

                // database insertion was successful, send back to edit form
                Session::flash('color','success');
                Session::flash('icon','fa fa-check');
                Session::flash('msg','Record has been updated successfully!');
				return redirect()->route('edit-unit-address',['id' => $id]);
			} else {
                // database insertion was not successful, send back to edit form
                Session::flash('color','danger');
                Session::flash('icon','fa fa-ban');
                Session::flash('msg','Record is not updated, please try again!');
				return redirect()->route('edit-unit-address',['id' => $id]);
			}
			
        }
    }

     /**
     * Delete Unit Address.
     *
     * @param  Request  $request
     * @return Response
     */
    public function destroy_unit_address(Request $request)
    {
        $logs = new Logs;
        $admin = new Admin;
        $unit_address = new UnitAddress;

        $id = $request->segment(2);
        $admin_arr = $admin->where('id', $request->session()->get('adminID'))->first();

        if(!checkPermission($admin_arr->delete)){
            // Permission not granted
            Session::flash('color','warning');
            Session::flash('icon','fa fa-exclamation-triangle');
            Session::flash('msg','Delete permission not granted!');
            return redirect()->route('units-address');
        }
        $flag = $unit_address->where('id', $id)->delete();

        if ($flag) {
            // Entry in logs table
            $logs->admin_id = $request->session()->get('adminID');
            $logs->logs_area = logsArea("Deleted unit address reference id [".$id."].");
            $logs->ip_address = $request->ip();
            $logs->save();

            // database deletion was successful, send back to listing form
            Session::flash('color','success');
            Session::flash('icon','fa fa-check');
            Session::flash('msg','Record has been deleted successfully!');
            return redirect()->route('units-address');
        } else {
            // database deletion was not successful, send back to listing form
            Session::flash('color','danger');
            Session::flash('icon','fa fa-ban');
            Session::flash('msg','Record is not deleted, please try again!');
            return redirect()->route('units-address');
        }
    }
}
