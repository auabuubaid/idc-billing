@extends('users.layout.master')

@section('content')

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6">
          <h1>Internet Subscribers</h1>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{route('dashboard')}}">Dashboard</a></li>
            <li class="breadcrumb-item active">Internet Subscribers</li>
          </ol>
        </div>
      </div>
    </div><!-- /.container-fluid -->
  </section>
  <!-- Main content -->
  <section class="content">
    <div class="container-fluid">
      @if(Session::has('msg')) 
        <div class="alert alert-{{ Session::get('color') }} alert-dismissible">
          <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
          <h4><i class="icon {{ Session::get('icon') }}"></i> {{ Session::get('msg') }}</h4>
        </div>
      @endif
      <div class="row">
        <div class="col-12">
          <div class="card">
            <div class="card-header">
              <h3 class="col-md-12 card-title">
                <form role="form" action="{{route('internet-subscribers')}}" method="get" id="internet-subscribers-form">
                  <div class="row">
                    <div class="col-md-2 mt-2">
                      <div class="form-group">
                        <label for="SelectMonthYear">Month Year</label>
                        <select name="monthYear" class="form-control select2" id="SelectMonthYear" onchange="this.form.submit()">
                          <option value="">--Select--</option>
                          @php $start = $month = strtotime(currentYearMonth().'-01') @endphp  @php  $end = strtotime('2017-10-01') @endphp
                          @while($end < $month)
                            <option value="{{ date('m-Y', $month) }}" {{ $monthYear==date('m-Y', $month) ? "selected": "" }}>{{ date('m-Y', $month), PHP_EOL }}</option>
                            @php $month = strtotime("-1 month", $month) @endphp
                          @endwhile
                        </select>
                      </div>
                    </div>
                    <div class="col-md-2 mt-2">
                      <div class="form-group">
                        <label for="Selectaddress">Unit No.</label>
                        <select name="addressID" class="form-control select2" id="Selectaddress" onchange="this.form.submit()">
                          <option value="">--Select--</option>
                          @if(count($unitsAddressArr)>0)
                            @foreach($unitsAddressArr as $lkey=>$lval)
                              <optgroup label="{{ buildingNameByLocationID($lkey) }}">
                                @foreach($lval as $uval)
                                  <option value="{{ $uval->id }}" {{ $addressID==$uval->id ? "selected": "" }}>{{ buildingLocationAndUnitNumberByUnitID($uval->id) }}</option>
                                @endforeach
                              </optgroup>    
                            @endforeach 
                          @endif
                        </select>
                      </div>
                    </div>
                    <div class="col-md-4"></div>
                    @if(checkPermission($userArr->download))
                    <div class="col-md-4" style="display: flex; justify-content: flex-end;">
                      <fieldset class="row" style="padding-inline-start: 0.25em; padding-inline-end: 0.25em; min-inline-size: min-content; border-width: 1px; border-style: dashed; border-color: #ced4da;">
                        <legend>DMC</legend>
                        <div class="form-group">
                          <a href="{{route('export-customer-info',['monthYear'=>$monthYear])}}" class="btn btn-success pull-right mr-2"><i class="fas fa-file-excel"></i> <small>Customer Info</small></a>
                        </div>
                        <div class="form-group">
                          <a href="{{route('dmc-summary',['monthYear'=>$monthYear])}}" title="DMC Summary" class="btn bg-gradient-primary mr-2"><i class="fas fa-file-excel"></i> <small>Summary</small></a>
                        </div>
                        <div class="form-group">
                          <a href="{{route('upstream-downstream',['monthYear'=>$monthYear])}}" title="DMC Up/Down Stream" class="btn bg-gradient-warning"><i class="fas fa-file-excel"></i> <small>Up/Down Stream</small></a>
                        </div>
                      </fieldset>                        
                    </div>                    
                    @endif
                  </div>
                  <input type="hidden" id="records" name="records" value="{{ $records }}">
                </form>   
              </h3>
            </div>
            <div class="card-body table-responsive p-2">
              <table id="subscribers" class="table table-bordered table-striped" style="width:99%; text-align: center">
                <thead>
                  <tr>
                    <th colspan="6" style="background-color:#17a2b8; color:#fff">Billing Information</th>
                    <th colspan="7" style="background-color:#007bff; color:#fff">PPPOE Service</th>
                  </tr>
                  <tr>
                    <th>Sr.</th>
                    <th style="min-width: 90px;">Subscribe Date</th>
                    <th>Customer Name</th>
                    <th style="min-width: 100px;">Address</th>
                    <th style="min-width: 50px;">Status</th>
                    <th style="min-width: 10px;">Advance Payment</th>

                    <th>Internet ID</th>
                    <th>Password</th>  
                    <th>IP Address</th>
                    <th>Mac Address</th>            
                    <th style="min-width: 70px;">Plan Name</th>
                    <th style="min-width: 50px;">Status</th>
                    <th style="min-width: 70px;">Action</th>
                  </tr>
                </thead>
                <tbody>
                  @if(count($subscribersArr)>0)
                    @foreach($subscribersArr as $skey=>$sval)
                      <tr>
                        <td>{{ $skey+$subscribersArr->firstItem() }}.</td>
                        <td>{{ pdfDateFormat(internetRegisterDateByCustID($sval->customer_id)) }}</td>
                        <td>{{ customerNameByID($sval->customer_id) }} <br>{{ customerDetailsByID($sval->customer_id)['shop_name'] }} </td>
                        <td>{{ buildingLocationAndUnitNumberByUnitID(currentInternetCustomersAddressByID($sval->customer_id)) }}</td>
                        <td><span class="text-{{ (currentInternetCustomersStatusByID($sval->customer_id)=='Active')? 'success' : 'danger' }}" title="{{ currentInternetCustomersStatusByID($sval->customer_id) }}"> <i class="fas fa-{{ (currentInternetCustomersStatusByID($sval->customer_id)=='Active')? 'user-check' : 'user-slash' }}"></i></span></td>
                        <td>@if(currentInternetCustomersStatusByID($sval->customer_id)=='Active')<a href="{{ route('add-internet-advance-payment',$sval->customer_id) }}"  class="btn btn-outline-dark btn-xs"> <i class="fas fa-money-bill-alt"></i></span>@endif</td>

                        <td>{{ customerDetailsByID($sval->customer_id)['internet_id'] }}</td>
                        <td>{{ passwordPlanCoreSubscriberByInternetID(customerDetailsByID($sval->customer_id)['internet_id'])['password'] }}</td>
                        <td>{{ activeCoreSubscriberByInternetID(customerDetailsByID($sval->customer_id)['internet_id'])['address'] }}</td>
                        <td>{{ activeCoreSubscriberByInternetID(customerDetailsByID($sval->customer_id)['internet_id'])['caller-id'] }}</td>
                        <td>{{ passwordPlanCoreSubscriberByInternetID(customerDetailsByID($sval->customer_id)['internet_id'])['plan-name'] }}</td>
                        <td><span class="text-{{ (activeCoreSubscriberByInternetID(customerDetailsByID($sval->customer_id)['internet_id'])['address']=='-')? 'danger' : 'success' }}"> <i class="fas fa-{{ (activeCoreSubscriberByInternetID(customerDetailsByID($sval->customer_id)['internet_id'])['address']=='-')? 'user-slash' : 'user-check' }}"></i></span></td>
                        <td>
                          <a href="{{ route('pppoe-connection-image',[$sval->customer_id]) }}"  class="btn btn-info btn-xs mr-2"> <i class="fas fa-image"></i></span>
                          <a href="{{ route('change-pppoe-status',['custID'=>$sval->customer_id, 'plan'=>passwordPlanCoreSubscriberByInternetID(customerDetailsByID($sval->customer_id)['internet_id'])['plan-name']]) }}"  class="btn btn-{{ (passwordPlanCoreSubscriberByInternetID(customerDetailsByID($sval->customer_id)['internet_id'])['plan-name']=='Expired-Alert')? 'success' : 'danger' }} btn-xs"> <i class="fas fa-{{ (passwordPlanCoreSubscriberByInternetID(customerDetailsByID($sval->customer_id)['internet_id'])['plan-name']=='Expired-Alert')? 'signal' : 'power-off' }}"></i></span>
                        </td>
                      </tr>
                    @endforeach
                  @endif
                </tbody>
              </table>
              <div class="float-left">
                <div class="input-group input-group-lg mb-3">
                  <div class="input-group-prepend mr-2">
                    <select class="form-control" id="SelectRecords" onchange="showNoRecords(this.value)">
                      <option value="10" {{ $records=='10' ? "selected": "" }}>10</option>
                      <option value="50" {{ $records=='50' ? "selected": "" }}>50</option>
                      <option value="100" {{ $records=='100' ? "selected": "" }}>100</option>
                      <option value="500" {{ $records=='500' ? "selected": "" }}>500</option>
                      <option value="1000" {{ $records=='1000' ? "selected": "" }}>1000</option>
                    </select>
                  </div>
                  <span class="pt-1">Showing {{ $subscribersArr->firstItem() }} to {{ $subscribersArr->lastItem() }} of {{ $subscribersArr->total() }} entries</span>
                </div>
              </div>
              <div class="float-right">{{ $subscribersArr->links() }}</div>
            </div>
            <!-- /.card-body -->
          </div>
          <!-- /.card -->
        </div>
        <!-- /.col -->
      </div>
      <!-- /.row -->
    </div><!-- /.container-fluid -->
  </section>
  <!-- /.content -->
</div>
<!-- /.content-wrapper -->
<!-- Page script -->
<script>
$(function () {
  //Initialize Select2 Elements
  $('.select2').select2();

  $("#subscribers").DataTable({
    "paging": false,
    "lengthChange": true,
    "searching": false,
    "order": [],
    "info": false,
    "autoWidth": false,
    "responsive": true,
    "columnDefs": [{
      "targets": [4,5,7,8,9,11,12],
      "orderable": false
    }],
  });
})

function showNoRecords(records)
{
  $('#records').val(records);
  document.getElementById('internet-subscribers-form').submit();
}
</script>
@endsection