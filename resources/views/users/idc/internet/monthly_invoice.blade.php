@extends('users.layout.master')

@section('content')

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6">
          <h1>Monthly Invoice</h1>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{route('dashboard')}}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{route('internet-subscribers')}}">Internet</a></li>
            <li class="breadcrumb-item active">Monthly Invoice</li>
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
                <div class="row">
                  <div class="col-md-10">
                    <form role="form" action="{{route('internet-monthly-invoice')}}" method="get" id="monthly-invoice-form">
                      <div class="row">
                        <div class="col-md-2">
                          <div class="form-group">
                            <label for="SelectMonthYear">Month Year</label>
                            <select name="monthlyYear" class="form-control select2" id="SelectMonthYear" onchange="this.form.submit()">
                              <option value="">--Select--</option>
                              @php $start = $month = strtotime(currentYearMonth().'-01') @endphp  @php  $end = strtotime('2017-11-01') @endphp
                              @while($end < $month)
                                <option value="{{ date('m-Y', $month) }}" {{ $monthlyYear==date('m-Y', $month) ?"selected":""}}>{{ date('m-Y', $month), PHP_EOL }}</option>
                                @php $month = strtotime("-1 month", $month) @endphp
                              @endwhile
                            </select>
                          </div>
                        </div>
                        <div class="col-md-2">
                          <div class="form-group">
                            <label for="SelectPlanName">Plan Name</label>
                            <select name="iplan" class="form-control select2" id="SelectPlanName" onchange="this.form.submit()">
                              <option value="">--Select--</option>
                              @if(is_object($iPlanArr))
                                @foreach($iPlanArr as $ival)
                                  <option value="{{ $ival->id }}" {{ $iplan==$ival->id ?"selected":""}}> {{ $ival->plan_name }}</option>
                                @endforeach
                              @endif
                            </select>
                          </div>
                        </div>
                        <div class="col-md-2">
                          <div class="form-group">
                            <label for="SelectUnitType">Unit Type</label>
                            <select name="unitType" class="form-control select2" id="SelectUnitType" onchange="this.form.submit()">
                              <option value="">--Select--</option>
                              @if(is_object($blocationArr))
                                @foreach($blocationArr as $bval)
                                  <option value="{{ $bval->id }}" {{ $unitType==$bval->id ?"selected":""}}> {{ buildingNameByLocationID($bval->id) }}</option>
                                @endforeach
                              @endif
                            </select>
                          </div>
                        </div>
                        <div class="col-md-3">
                          <div class="form-group">
                            <label for="InputAddress">Unit No.</label>
                            <select name="addressID" class="form-control select2" id="SelectAddress" onchange="this.form.submit()">
                              <option value="">--Select--</option>
                              @if(count($unitsAddressArr)>0)
                                @foreach($unitsAddressArr as $lkey=>$lval)
                                  <optgroup label="{{ buildingNameByLocationID($lkey) }}">
                                    @foreach($lval as $uval)
                                      <option value="{{ $uval->id }}" {{ $addressID==$uval->id ?"selected":""}}> {{ buildingLocationAndUnitNumberByUnitID($uval->id) }}</option>
                                    @endforeach
                                  </optgroup>    
                                @endforeach    
                              @endif
                            </select>
                          </div>
                        </div> 
                        <div class="col-md-2">
                          <div class="form-group">
                            <label for="InputAddress">Status</label>
                            <select name="paid" class="form-control" id="SelectPaid" onchange="this.form.submit()">
                              <option value="">--All--</option>
                              <option value="Y" {{ $paid=="Y" ?"selected":""}}>Paid</option>
                              <option value="N" {{ $paid=="N" ?"selected":""}}>Not Paid</option>
                            </select>
                          </div>
                        </div> 
                      </div>
                      <input type="hidden" id="records" name="records" value="{{ $records }}">
                      <input type="hidden" id="custID" name="custID" value="{{ $custID }}">
                    </form>
                  </div>
                  @if(checkPermission($userArr->download))
                    <div class="col-md-2 mt-4">
                      <div class="row">
                        <a href="{{route('model-house-summary',['monthYear'=>$monthlyYear])}}" title="Model House Summary" class="btn bg-gradient-dark mr-2"><i class="fas fa-file-excel"></i> <small>MH</small></a>
                        <form role="form" action="{{route('export-monthly-payment')}}" method="post" class="mr-2">
                          @csrf
                          <input type="hidden" name="export_month_year" id="InputExportMonthYear" value="">
                          <input type="hidden" name="status" id="InputStatus" value="">
                          <input type="hidden" name="address" id="InputAddress" value="">
                          <button type="submit" title="Management Office Monthly Payment" class="btn bg-gradient-primary pull-right"><i class="fas fa-file-excel"></i> <small>MO</small></button>
                        </form>
                        <a href="{{route('export-unpaid-internet',['monthYear'=>$monthlyYear])}}" title="Export Unpaid Invoice" class="btn bg-gradient-info pull-right mr-2"><i class="fas fa-file-download"></i></a>
                        <a href="{{route('import-unpaid-internet')}}" title="Import Unpaid Invoice" class="btn bg-gradient-success pull-right"><i class="fas fa-file-upload"></i></a>
                      </div>
                    </div>
                  @endif
                </div>
              </h3>
            </div>
            <div class="card-body table-responsive p-2">
              <table id="monthlyInvoice" class="table table-bordered table-striped" style="width:99%; text-align: center">
                <thead>
                  <tr>
                    <th>Sr</th>
                    <th style="min-width:80px">Internet ID</th>
                    <th style="min-width: 70px;">Date</th>
                    <th style="min-width: 90px;">Invoice No.</th>
                    <th style="min-width: 150px;">Customer Name</th>
                    <th style="min-width: 150px;">Address</th>
                    <th style="min-width: 100px;">Plan Name</th>
                    <th style="min-width: 90px;">Monthly Fee</th>
                    <th>Balance</th>
                    <th>VAT</th>
                    <th>Amount</th>
                    <th style="min-width: 70px;">Paid Date</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                  @if(count($customerArr)>0)
                    @foreach($customerArr as $ckey=>$cval)
                      <tr>
                        <td>{{ $ckey+$customerArr->firstItem() }}.</td>
                        <td>{{ customerDetailsByID($cval->customer_id)['internet_id'] }}</td>
                        <td>{{ datePickerFormat($cval->monthly_invoice_date) }}</td>
                        <td>{{ $cval->invoice_number }}</td>
                        <td>{{ customerNameByID($cval->customer_id) }} <br> {{ customerDetailsByID($cval->customer_id)['shop_name'] }}</td>
                        <td>{{ buildingLocationAndUnitNumberByUnitID($cval->address_id) }} @if($cval->paid=='N' && $addressID=='')<a href="{{ route('internet-monthly-invoice',['custID'=>$cval->customer_id,'addressID'=>$cval->address_id,'paid'=>'N']) }}" target="_blank"><sup class="right badge rounded-pill badge-danger">{{ notPaidInternetCount($cval->customer_id, $cval->address_id) }}</sup> @endif</td>
                        <td>{{ planDetailsByPlanID($cval->plan_id)['plan_name'] }}</td>
                        <td>${{ amountFormat2($cval->monthly_fee) }}</td>
                        <td>${{ amountFormat2($cval->balance) }}</td>
                        <td>${{ amountFormat2($cval->vat_amount) }}</td>
                        <td><b>${{ amountFormat2($cval->total_amount) }}</b></td>
                        <td>@if($cval->paid=="Y") {{ pdfDateFormat($cval->paid_date) }} @endif</td>
                        <td class="col-md-2">
                          <div class="form-group clearfix">
                            <span class="text-{{ ($cval->paid=='Y') ? 'success' : 'danger' }}"><i class="fas fa-{{ ($cval->paid=='Y') ? 'check' : 'times' }}"></i></span> &nbsp;&nbsp;&nbsp;
                            <a href="{{route('edit-monthly-invoice',['id'=>$cval->id])}}" class="text-warning" title="Pay Monthly Invoice"><i class="fas fa-edit"></i></a>&nbsp;&nbsp;&nbsp;
                            @if(checkPermission($userArr->download))<a href="{{route('generate-internet-invoice',['id'=>$cval->id])}}" target="_blank" class="text-danger" title="Generate Invoice"><i class="fas fa-file-pdf"></i></a> @endif
                          </div>
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
                  <span class="pt-1">Showing {{ $customerArr->firstItem() }} to {{ $customerArr->lastItem() }} of {{ $customerArr->total() }} entries</span>
                </div>
              </div>
              <div class="float-right">{{ $customerArr->links() }}</div>
            </div>
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

  $("#monthlyInvoice").DataTable({
    "paging": false,
    "lengthChange": true,
    "searching": false,
    "order": [],
    "info": false,
    "autoWidth": false,
    "responsive": true,
    "columnDefs": [{
      "targets": 12,
      "orderable": false
    }],
  });
})

$( document ).ready(function() {
  var monthYear = $('#SelectMonthYear').val();
  var addressID = $('#SelectAddress').val();
  var paid = $('#SelectPaid').val();
  
  $('#InputExportMonthYear').val(monthYear);
  $('#InputInvoiceMonthYear').val(monthYear);   
  $('#DMCInvoiceMonthYear').val(monthYear);
  $('#InputAddress').val(addressID);
  $('#InputStatus').val(paid);
});

$( ".invoice" ).click(function() {
  var monthYear = $('#SelectMonthYear').val();
  $('#InvoiceMonthYear').val(monthYear);
  $('#DMCInvoiceMonthYear').val(monthYear);
});  

function showNoRecords(records)
{
  $('#records').val(records);
  document.getElementById('monthly-invoice-form').submit();
}
</script>
@endsection