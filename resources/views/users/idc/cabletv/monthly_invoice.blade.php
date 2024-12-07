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
            <li class="breadcrumb-item">Cable TV</li>
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
                  <div class="col-md-9">
                    <form role="form" action="{{route('cabletv-monthly-invoice')}}" method="get" id="monthly-invoice-form">
                      <div class="row">
                        <div class="col-md-2">
                          <div class="form-group">
                            <label for="SelectMonthYear">Month Year</label>
                            <select name="monthlyYear" class="form-control select2" id="SelectMonthYear" onchange="this.form.submit()">
                              <option value="">-Select-</option>
                              @php $start = $month = strtotime(currentYearMonth().'-01') @endphp  @php  $end = strtotime('2022-06-01') @endphp
                              @while($end < $month)
                                <option value="{{ date('m-Y', $month) }}" {{ $monthYear==date('m-Y', $month) ? "selected" : ""}}>{{ date('m-Y', $month), PHP_EOL }}</option>
                                @php $month = strtotime("-1 month", $month) @endphp
                              @endwhile
                            </select>
                          </div>
                        </div>
                        <div class="col-md-3">
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
                            <label for="SelectMonthYear">Unit No.</label>
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
                            <label for="SelectMonthYear">Status</label>
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
                  @if(checkPermission($userArr->write))
                  <div class="col-md-1 mt-4" style="max-width:55px;">
                    <form role="form" action="{{route('generate-cabletv-monthly-invoice')}}" method="post">
                      @csrf
                      <input type="hidden" name="invoice_month_year" id="InvoiceMonthYear" value="" >
                      <button title="Generate Invoice" class="btn bg-gradient-primary invoice"><i class="fas fa-file-invoice"></i></button>
                    </form>
                  </div>
                  @endif
                  @if(checkPermission($userArr->download))
                  <div class="col-md-1 mt-4" style="max-width:55px;">
                    <form role="form" action="{{route('export-cabletv-monthly-payment')}}" method="post">
                      @csrf
                      <input type="hidden" name="export_month_year" id="InputExportMonthYear" value="" >
                      <button type="submit" title="Download Monthly Payment" class="btn bg-gradient-warning"><i class="fas fa-file-excel"></i></button>
                    </form>
                  </div>                 
                  <div class="col-md-1 mt-4" style="max-width:55px;">
                    <form role="form" action="{{route('export-cabletv-monthly-invoice')}}" method="post">
                      @csrf
                      <input type="hidden" name="invoice_month_year" id="InputInvoiceMonthYear" value="" >
                      <button type="submit" title="Download Monthly Invoice" class="btn bg-gradient-danger"><i class="fas fa-file-pdf"></i></button>
                    </form>
                  </div>                  
                  <div class="col-md-1 mt-4" style="max-width:55px;">
                    <a href="{{route('export-unpaid-cabletv',['monthYear'=>$monthYear])}}" title="Export Unpaid Invoice" class="btn bg-gradient-info pull-right"><i class="fas fa-file-download"></i></a>
                  </div>
                  @endif
                  @if(checkPermission($userArr->upload))
                  <div class="col-md-1 mt-4" style="max-width:55px;">
                    <a href="{{route('import-unpaid-cabletv')}}" title="Import Unpaid Invoice" class="btn bg-gradient-success pull-right"><i class="fas fa-file-upload"></i></a> 
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
                    <th style="min-width: 85px;">Date</th>
                    <th style="min-width: 50px;">Invoice Number</th>
                    <th>Customer Name</th>
                    <th>Address</th>
                    <th>Plan Name</th>
                    <th>Monthly Fee</th>
                    <th>Amount</th>
                    <th>Paid Date</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                  @if(count($customerArr)>0)
                    @foreach($customerArr as $ckey=>$cval)
                      <tr>
                        <td>{{ $ckey+$customerArr->firstItem() }}.</td>
                        <td>{{ pdfDateFormat($cval->monthly_invoice_date) }}</td>
                        <td>{{ $cval->invoice_number }}</td>
                        <td>{{ customerNameByID($cval->customer_id) }} <br/> {{ customerDetailsByID($cval->customer_id)['shop_name'] }}</td>
                        <td>{{ buildingLocationAndUnitNumberByUnitID($cval->address_id) }} @if($cval->paid=='N' && $addressID=='')<a href="{{ route('cabletv-monthly-invoice',['custID'=>$cval->customer_id,'addressID'=>$cval->address_id,'paid'=>'N']) }}" target="_blank"><sup class="right badge rounded-pill badge-danger">{{ notPaidCableTVCount($cval->customer_id, $cval->address_id) }}</sup> @endif</td>
                        <td>{{ cableTVPlanNameByPlanID($cval->plan_id) }}</td>
                        <td>${{ amountFormat2($cval->monthly_fee) }}</td>
                        <td><b>${{ amountFormat2($cval->total_amount) }}</b></td>
                        <td>@if($cval->paid=="Y") {{ pdfDateFormat($cval->paid_date) }} @endif</td>
                        <td>
                            @if($cval->paid=="Y")<span class="text-success"><i class="fas fa-check"></i></span> @else <span class="text-danger"><i class="fas fa-times"></i></span> @endif &nbsp;&nbsp;&nbsp;
                            <a href="{{route('edit-cabletv-monthly-invoice', ['id'=>$cval->id ])}}" class="text-warning" title="Edit Monthly Invoice"><i class="fas fa-edit"></i></a>&nbsp;&nbsp;&nbsp;
                            @if(checkPermission($userArr->download))<a href="{{route('generate-cabletv-invoice', ['id'=>$cval->id ])}}" target="_blank" class="text-danger" title="Generate Invoice"><i class="fas fa-file-pdf"></i></a> @endif
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

  $("#monthlyInvoice").DataTable({
    "paging": false,
    "lengthChange": true,
    "searching": false,
    "order": [],
    "info": false,
    "autoWidth": false,
    "responsive": true,
    "columnDefs": [{
      "targets": 9,
      "orderable": false
    }],
  });  
})
  
$( document ).ready(function() {
  var monthYear = $('#SelectMonthYear').val();  
  $('#InputExportMonthYear').val(monthYear);   
  $('#InputInvoiceMonthYear').val(monthYear);
});

$( ".invoice" ).click(function() {
  var monthYear = $('#SelectMonthYear').val();
  $('#InvoiceMonthYear').val(monthYear);
});

function showNoRecords(records)
{
  $('#records').val(records);
  document.getElementById('monthly-invoice-form').submit();
}
</script>
@endsection