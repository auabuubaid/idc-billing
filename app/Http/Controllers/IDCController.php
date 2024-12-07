<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

use App\Models\Logs;
use App\Models\Admin;
use App\Models\CableTVService;
use App\Models\InternetService;

// CORE NETWORK
use \RouterOS\Query;
use \RouterOS\Config;
use \RouterOS\Client;


class IDCController extends Controller
{
    /**
     * Show Internet Plans Listing Page.
     *
     * @param  Request  $request
     * @return View
     */
    public function internet_services(Request $request)
    {
        $admin = new Admin;
        $internet = new InternetService;
        
        $admin_arr = $admin->where('id', $request->session()->get('adminID'))->first();
        $records = !empty($request->input('records')) ? $request->input('records') : env('PER_PAGE_RECORDS'); 
        $internetServicesArr = $internet->paginate($records)->withQueryString();

        return view('admin.idc_services.internet.index')
                    ->with(['internet_services_arr'=>$internetServicesArr,'admin_arr'=>$admin_arr, 'records'=>$records]);
    }

    /**
     * Show Internet Plan Add Page.
     *
     * @param  Request  $request
     * @return View
     */
    public function add_internet_service(Request $request)
    {
        $admin = new Admin;

        $admin_arr = $admin->where('id', $request->session()->get('adminID'))->first();
        
        if(!checkPermission($admin_arr->write)){
            // Permission not granted
            Session::flash('color','warning');
            Session::flash('icon','fa fa-exclamation-triangle');
            Session::flash('msg','Write permission not granted!');
            return redirect()->route('internet-services');
        }

        return view('admin.idc_services.internet.add')
                    ->with(['admin_arr'=>$admin_arr]);
    }

    /**
     * Validate & Store Internet Plan.
     *
     * @param  Request  $request
     * @return Response
     */
    public function store_internet_service(Request $request)
    {
        $rules = array(
            'service_name'=> 'required',
            'plan_name'=> 'required',
            'speed'=> 'required|numeric',
            'speed_unit'=> 'required|in:Bps,Kbps,Mbps,Gbps,Tbps',
            'upload_speed'=> 'required|numeric',
            'upload_speed_unit'=> 'required|in:Bps,Kbps,Mbps,Gbps,Tbps',
            'deposit_fee'=> 'required|numeric|between:0.00,9999.99',
            'monthly_fee'=> 'required|numeric|between:0.00,9999.99',
            'installation_fee'=> 'required|numeric|between:0.00,9999.99',
            'vat'=> 'required|numeric|between:0,99.99',
            'data_usage'=> 'required|in:U,L',
            'status'=> 'required|in:A,N'
        );
        $validator = Validator::make($request->all(), $rules);
        
        if ($validator->fails()) {
            // validation unsuccessful!
            Session::flash('color','warning');
            Session::flash('icon','fas fa-exclamation-triangle');
            Session::flash('msg','Please fill all mandatory fields!');             
            return redirect()->route('add-internet-service')
                    ->withErrors($validator) 
                        ->withInput();
            
        } else {
            // saving internet service in database
            $logs = new Logs;
			$internet = new InternetService;

            $internet->service_name = $request->input('service_name');
            $internet->plan_name = $request->input('plan_name');
            $internet->speed = $request->input('speed');
            $internet->speed_unit = $request->input('speed_unit');
            $internet->upload_speed = $request->input('upload_speed');
            $internet->upload_speed_unit = $request->input('upload_speed_unit');
            $internet->deposit_fee = $request->input('deposit_fee');
            $internet->monthly_fee = $request->input('monthly_fee');
            $internet->installation_fee = $request->input('installation_fee');
            $internet->vat = $request->input('vat');
            $internet->data_usage = $request->input('data_usage');
            $internet->status = $request->input('status');
            $internet->created_by = $request->session()->get('adminID');            
            $flag = $internet->save();

            // Create core network config object with parameters
            $config = (new Config())
                ->set('timeout', 1)
                ->set('host', env('CORE_NETWORK_HOST'))
                ->set('user', env('CORE_NETWORK_USER'))
                ->set('pass', env('CORE_NETWORK_PASSWORD'));

            // Initiate core network client with config object
            $client = new Client($config);

            $downloadSpeed = $request->input('speed').substr($request->input('speed_unit'), 0, 1);
            $uploadSpeed = $request->input('upload_speed').substr($request->input('upload_speed_unit'), 0, 1);

            $query = (new Query('/ppp/profile/add'))
                ->equal('name', $request->input('plan_name'))
                ->equal('local-address', 'PPPoE_Server')
                ->equal('remote-address', 'PPPoE_Server')
                ->equal('rate-limit', $downloadSpeed.'/'.$uploadSpeed);
            $response = $client->query($query)->read();

			if ($flag) {
                // Entry in logs table
                $logs->admin_id = $request->session()->get('adminID');
                $logs->logs_area = logsArea("Added new internet plan reference id [".$internet->id."].");
                $logs->ip_address = $request->ip();
                $logs->save();

                // validation successful!
                Session::flash('color','success');
                Session::flash('icon','fa fa-check');
                Session::flash('msg','Record has been added successfully!');
				return redirect()->route('internet-services');
			} else {
                // database insertion was not successful, send back to add form
                Session::flash('color','danger');
                Session::flash('icon','fa fa-ban');
                Session::flash('msg','Record is not added, please try again!');
				return redirect()->route('add-internet-service');
			}
			
        }
    }

    /**
     * Show Internet Plan View Page.
     *
     * @param  Request  $request
     * @return View
     */
    public function view_internet_service(Request $request)
    {
        $admin = new Admin;
        $internet = new InternetService;
        
        $id = $request->segment(2);
        $admin_arr = $admin->where('id', $request->session()->get('adminID'))->first();
        $internetServiceArr = $internet->where('id', $id)->first();

        if(!checkPermission($admin_arr->read)){
            // Permission not granted
            Session::flash('color','warning');
            Session::flash('icon','fa fa-exclamation-triangle');
            Session::flash('msg','Read permission not granted!');
            return redirect()->route('internet-services');
        }
        
        return view('admin.idc_services.internet.view')
                    ->with(['internet_service_arr'=>$internetServiceArr, 'admin_arr'=>$admin_arr]);
    }

    /**
     * Show Internet Plan Edit page.
     *
     * @param  Request  $request
     * @return View
     */
    public function edit_internet_service(Request $request)
    {
        $admin = new Admin;
        $internet= new InternetService;
        
        $id = $request->segment(2);
        $admin_arr = $admin->where('id', $request->session()->get('adminID'))->first();
        $internetServiceArr = $internet->where('id', $id)->first();

        if(!checkPermission($admin_arr->write)){
            // Permission not granted
            Session::flash('color','warning');
            Session::flash('icon','fa fa-exclamation-triangle');
            Session::flash('msg','Write permission not granted!');
            return redirect()->route('internet-services');
        }
        
        return view('admin.idc_services.internet.edit')
                    ->with(['internet_service_arr'=>$internetServiceArr, 'admin_arr'=>$admin_arr]);
    }

     /**
     * Validate & Update Internet Plan.
     *
     * @param  Request  $request
     * @return Response
     */
    public function update_internet_service(Request $request)
    {   
        $rules = array(
            'service_name'=> 'required',
            'plan_name'=> 'required',
            'speed'=> 'required|numeric',
            'speed_unit'=> 'required|in:Bps,Kbps,Mbps,Gbps,Tbps',
            'upload_speed'=> 'required|numeric',
            'upload_speed_unit'=> 'required|in:Bps,Kbps,Mbps,Gbps,Tbps',
            'deposit_fee'=> 'required|numeric|between:0.00,9999.99',
            'monthly_fee'=> 'required|numeric|between:0.00,9999.99',
            'installation_fee'=> 'required|numeric|between:0.00,9999.99',
            'vat'=> 'required|numeric|between:0,99.99',
            'data_usage'=> 'required|in:U,L',
            'status'=> 'required|in:A,N'
        );
        $validator = Validator::make($request->all(), $rules);
        $id = $request->input('referenceID');

        if ($validator->fails()) {
            // validation unsuccessful!
            Session::flash('color','warning');
            Session::flash('icon','fas fa-exclamation-triangle');
            Session::flash('msg','Please fill all mandatory fields!');             
            return redirect()->route('edit-internet-service',['id' => $id])
                    ->withErrors($validator) 
                        ->withInput();            
        } else {
            // saving building in database
            $logs = new Logs;
			$internet = new InternetService;

            $flag = $internet->where('id',$id)->
                        update([
                            'service_name'=> $request->input('service_name'),
                            'plan_name'=> $request->input('plan_name'),
                            'speed'=> $request->input('speed'),
                            'speed_unit'=> $request->input('speed_unit'),
                            'upload_speed'=> $request->input('upload_speed'),
                            'upload_speed_unit'=> $request->input('upload_speed_unit'),
                            'deposit_fee'=> $request->input('deposit_fee'),
                            'monthly_fee'=> $request->input('monthly_fee'),
                            'installation_fee'=> $request->input('installation_fee'),
                            'vat'=> $request->input('vat'),
                            'data_usage'=> $request->input('data_usage'),
                            'status'=> $request->input('status'),
                            'updated_by'=> $request->session()->get('adminID')
                        ]);

			if ($flag) {
                // Entry in logs table
                $logs->admin_id = $request->session()->get('adminID');
                $logs->logs_area = logsArea("Updated internet plan reference id [".$id."].");
                $logs->ip_address = $request->ip();
                $logs->save();

                // database insertion was successful, send back to edit form
                Session::flash('color','success');
                Session::flash('icon','fa fa-check');
                Session::flash('msg','Record has been updated successfully!');
				return redirect()->route('edit-internet-service', ['id' => $id]);
			} else {
                // database insertion was not successful, send back to edit form
                Session::flash('color','danger');
                Session::flash('icon','fa fa-ban');
                Session::flash('msg','Record is not updated, please try again!');
				return redirect()->route('edit-internet-service',['id' => $id]);
			}
			
        }
    }

    /**
     * Delete Internet Plan.
     *
     * @param  Request  $request
     * @return Response
     */
    public function destroy_internet_service(Request $request)
    {   
        $logs = new Logs;
        $admin = new Admin;
        $internet= new InternetService;
        
        $id = $request->segment(2);
        $admin_arr = $admin->where('id', $request->session()->get('adminID'))->first();

        if(!checkPermission($admin_arr->delete)){
            // Permission not granted
            Session::flash('color','warning');
            Session::flash('icon','fa fa-exclamation-triangle');
            Session::flash('msg','Delete permission not granted!');
            return redirect()->route('internet-services');
        }

        $flag = $internet->where('id', $id)->delete();
        
        if ($flag) {
            // Entry in logs table
            $logs->admin_id = $request->session()->get('adminID');
            $logs->logs_area = logsArea("Deleted internet plan reference id [".$id."].");
            $logs->ip_address = $request->ip();
            $logs->save();

            // database deletion was successful, send back to listing form
            Session::flash('color','success');
            Session::flash('icon','fa fa-check');
            Session::flash('msg','Record has been deleted successfully!');
            return redirect()->route('internet-services');
        } else {
            // database deletion was not successful, send back to listing form
            Session::flash('color','danger');
            Session::flash('icon','fa fa-ban');
            Session::flash('msg','Record is not deleted, please try again!');
            return redirect()->route('internet-services');
        }
    }

    /**
     * Show Cable TV Palns Listing Page.
     *
     * @param  Request  $request
     * @return View
     */
    public function cabletv_services(Request $request)
    {   
        $admin = new Admin;
        $cabletv = new CableTVService;
        
        $admin_arr = $admin->where('id', $request->session()->get('adminID'))->first();
        $records = !empty($request->input('records')) ? $request->input('records') : env('PER_PAGE_RECORDS'); 
        $cabletvServicesArr = $cabletv->paginate($records)->withQueryString();

        return view('admin.idc_services.cabletv.index')
                    ->with(['cabletvServicesArr'=>$cabletvServicesArr,'admin_arr'=>$admin_arr, 'records'=>$records]);
    }

    /**
     * Show CableTV Plan Add Page.
     *
     * @param  Request  $request
     * @return View
     */
    public function add_cabletv_service(Request $request)
    {
        $admin = new Admin;
        $admin_arr = $admin->where('id', $request->session()->get('adminID'))->first();
        
        if(!checkPermission($admin_arr->write)){
            // Permission not granted
            Session::flash('color','warning');
            Session::flash('icon','fa fa-exclamation-triangle');
            Session::flash('msg','Write permission not granted!');
            return redirect()->route('cabletv-services');
        }

        return view('admin.idc_services.cabletv.add')
                    ->with(['admin_arr'=>$admin_arr]);
    }

    /**
     * Validate & Store CableTV Plan.
     *
     * @param  Request  $request
     * @return Response
     */
    public function store_cabletv_service(Request $request)
    {   
        $rules = array(
            'plan_name'=> 'required|alpha_dash',
            'installation_fee'=> 'required|numeric|between:0.01,9999.99',
            'monthly_fee'=> 'required|numeric|between:0.01,9999.99',
            'new_tv_fee'=> 'required|numeric|between:0.01,9999.99',
            'status'=> 'required|in:A,N'
        );
        $validator = Validator::make($request->all(), $rules);
        
        if ($validator->fails()) {
            // validation unsuccessful!
            Session::flash('color','warning');
            Session::flash('icon','fas fa-exclamation-triangle');
            Session::flash('msg','Please fill all mandatory fields!');             
            return redirect()->route('add-cabletv-service')
                    ->withErrors($validator) 
                        ->withInput();            
        } else {
            // saving cabletv service in database
            $logs = new Logs;
			$cabletv = new CableTVService;

            $cabletv->plan_name = $request->input('plan_name');
            $cabletv->installation_fee = $request->input('installation_fee');
            $cabletv->monthly_fee = $request->input('monthly_fee');
            $cabletv->per_tv_fee = $request->input('new_tv_fee');
            $cabletv->status = $request->input('status');
            $cabletv->created_by = $request->session()->get('adminID');            
            $flag=$cabletv->save();

			if ($flag) {
                // Entry in logs table
                $logs->admin_id = $request->session()->get('adminID');
                $logs->logs_area = logsArea("Added new cabletv service reference id [".$cabletv->id."].");
                $logs->ip_address = $request->ip();
                $logs->save();

                // validation successful!
                Session::flash('color','success');
                Session::flash('icon','fa fa-check');
                Session::flash('msg','Record has been added successfully!');
				return redirect()->route('cabletv-services');
			} else {
                // database insertion was not successful, send back to add form
                Session::flash('color','danger');
                Session::flash('icon','fa fa-ban');
                Session::flash('msg','Record is not added, please try again!');
				return redirect()->route('add-cabletv-service');
			}
			
        }
    }

    /**
     * Show CableTV Plan View Page.
     *
     * @param  Request  $request
     * @return View
     */
    public function view_cabletv_service(Request $request)
    {
        $admin = new Admin;
        $cabletv = new CableTVService;

        $admin_arr = $admin->where('id', $request->session()->get('adminID'))->first();
        
        if(!checkPermission($admin_arr->read)){
            // Permission not granted
            Session::flash('color','warning');
            Session::flash('icon','fa fa-exclamation-triangle');
            Session::flash('msg','Read permission not granted!');
            return redirect()->route('cabletv-services');
        }
        
        $id = $request->segment(2);
        $cabletvServiceArr = $cabletv->where('id', $id)->first();

        return view('admin.idc_services.cabletv.view')
                    ->with(['cabletvServiceArr'=>$cabletvServiceArr, 'admin_arr'=>$admin_arr]);
    }

    /**
     * Show CableTV Plan Edit page.
     *
     * @param  Request  $request
     * @return View
     */
    public function edit_cabletv_service(Request $request)
    {
        $admin = new Admin;
        $cabletv = new CableTVService;        

        $admin_arr = $admin->where('id', $request->session()->get('adminID'))->first();
        
        if(!checkPermission($admin_arr->write)){
            // Permission not granted
            Session::flash('color','warning');
            Session::flash('icon','fa fa-exclamation-triangle');
            Session::flash('msg','Write permission not granted!');
            return redirect()->route('cabletv-services');
        }
        
        $id= $request->segment(2);
        $cabletvServiceArr = $cabletv->where('id', $id)->first();
        
        return view('admin.idc_services.cabletv.edit')->with(['cabletvServiceArr'=>$cabletvServiceArr, 'admin_arr'=>$admin_arr]);
    }

     /**
     * Validate & Update CableTV Plan.
     *
     * @param  Request  $request
     * @return Response
     */
    public function update_cabletv_service(Request $request)
    {   
        $rules = array(
            'plan_name'=> 'required|alpha_dash',
            'installation_fee'=> 'required|numeric|between:0.01,9999.99',
            'monthly_fee'=> 'required|numeric|between:0.01,9999.99',
            'new_tv_fee'=> 'required|numeric|between:0.01,9999.99',
            'status'=> 'required|in:A,N'
        );
        $validator = Validator::make($request->all(), $rules);
        $id = $request->input('referenceID');

        if ($validator->fails()) {
            // validation unsuccessful!
            Session::flash('color','warning');
            Session::flash('icon','fas fa-exclamation-triangle');
            Session::flash('msg','Please fill all mandatory fields!');             
            return redirect()->route('edit-cabletv-service',['id' => $id])
                    ->withErrors($validator) 
                        ->withInput();            
        } else {
            // updating cabletv services in database
            $logs = new Logs;
			$cabletv = new CableTVService;

            $flag = $cabletv->where('id',$id)->
                        update([
                            'plan_name'=> $request->input('plan_name'),
                            'installation_fee'=> $request->input('installation_fee'),
                            'monthly_fee'=> $request->input('monthly_fee'),
                            'per_tv_fee'=> $request->input('new_tv_fee'), 
                            'status'=> $request->input('status'),
                            'updated_by'=> $request->session()->get('adminID') 
                        ]);

			if ($flag) {
                // Entry in logs table
                $logs->admin_id = $request->session()->get('adminID');
                $logs->logs_area = logsArea("Updated cabletv service reference id [".$id."].");
                $logs->ip_address = $request->ip();
                $logs->save();

                // database insertion was successful, send back to edit form
                Session::flash('color','success');
                Session::flash('icon','fa fa-check');
                Session::flash('msg','Record has been updated successfully!');
				return redirect()->route('edit-cabletv-service', ['id' => $id]);
			} else {
                // database insertion was not successful, send back to edit form
                Session::flash('color','danger');
                Session::flash('icon','fa fa-ban');
                Session::flash('msg','Record is not updated, please try again!');
				return redirect()->route('edit-cabletv-service',['id' => $id]);
			}
			
        }
    }

    /**
     * Delete CableTV Plan.
     *
     * @param  Request  $request
     * @return Response
     */
    public function destroy_cabletv_service(Request $request)
    {   
        $logs = new Logs;
        $admin = new Admin;
        $cabletv = new CableTVService;
        
        $admin_arr = $admin->where('id', $request->session()->get('adminID'))->first();

        if(!checkPermission($admin_arr->delete)){
            // Permission not granted
            Session::flash('color','warning');
            Session::flash('icon','fa fa-exclamation-triangle');
            Session::flash('msg','Delete permission not granted!');
            return redirect()->route('cabletv-services');
        }

        $id = $request->segment(2);
        $flag = $cabletv->where('id', $id)->delete();
        
        if ($flag) { 
            // Entry in logs table
            $logs->admin_id = $request->session()->get('adminID');
            $logs->logs_area = logsArea("Deleted cabletv service reference id [".$id."].");
            $logs->ip_address = $request->ip();
            $logs->save();

            // database deletion was successful, send back to listing form
            Session::flash('color','success');
            Session::flash('icon','fa fa-check');
            Session::flash('msg','Record has been deleted successfully!');
            return redirect()->route('cabletv-services');
        } else {
            // database deletion was not successful, send back to listing form
            Session::flash('color','danger');
            Session::flash('icon','fa fa-ban');
            Session::flash('msg','Record is not deleted, please try again!');
            return redirect()->route('cabletv-services');
        }
    }
}
