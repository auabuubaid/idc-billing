@extends('users.layout.master')

@section('content')

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6">
          <h1>Advance Payment</h1>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{route('dashboard')}}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{route('internet-subscribers')}}">Internet</a></li>
            <li class="breadcrumb-item active">Advance Payment</li>
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
                <form role="form" action="{{route('internet-advance-payment')}}" method="get" id="advance-payment-form">
                  <div class="row">
                    <div class="col-md-2">
                      <div class="form-group">
                        <label for="SelectAddress">Unit No.</label>
                        <select name="addressID" class="form-control select2" id="SelectAddress" onchange="this.form.submit()">
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
                  </div> 
                  <input type="hidden" id="records" name="records" value="{{ $records }}"> 
                </form>   
              </h3>
            </div>
            <div class="card-body table-responsive p-2">
              <table id="internetAdvancePayment" class="table table-bordered table-striped" style="width:99%; text-align: center">
                <thead>
                  <tr>
                    <th>Sr</th>
                    <th>Customer Name</th>
                    <th>Address</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                    <th>Period</th>
                    <th>Monthly Fee</th>
                    <th>Amount</th>
                    <th>Paid Date</th>
                    <th>Remark</th>
                    <th>Status</th>
                  </tr>
                </thead>
                <tbody>
                  @if(count($advancePaymentArr)>0)
                    @foreach($advancePaymentArr as $akey=>$apval)
                    <tr>
                      <td>{{ $akey+$advancePaymentArr->firstItem() }}.</td>
                      <td>{{ customerNameByID($apval->customer_id) }}</td>
                      <td>{{ buildingLocationAndUnitNumberByUnitID($apval->address_id) }}</td>
                      <td>{{ pdfDateFormat($apval->start_date) }}</td>
                      <td>{{ pdfDateFormat($apval->end_date) }}</td>
                      <td>{{ $apval->period }} Month(s)</td>
                      <td>${{ amountFormat2($apval->monthly_fee) }}</td>
                      <td><b>${{ amountFormat2($apval->total_amount) }}</b></td>
                      <td>{{ pdfDateFormat($apval->paid_date) }}</td>
                      <td>{{ \Illuminate\Support\Str::limit($apval->remark, 20, $end='...') }}
                      @if(strlen($apval->remark)>20)<a href="javascript:void(0);" title="{{ $apval->remark }}"> read more</a>@endif</td>
                      <td>@if(dataBaseFormat($apval->end_date) < currentDate2()) <span class="text-danger">Expired</span>@else <span class="text-success">Valid</span> @endif</td>
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
                  <span class="pt-1">Showing {{ $advancePaymentArr->firstItem() }} to {{ $advancePaymentArr->lastItem() }} of {{ $advancePaymentArr->total() }} entries</span>
                </div>
              </div>
              <div class="float-right">{{ $advancePaymentArr->links() }}</div>
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

  $("#internetAdvancePayment").DataTable({
    "paging": false,
    "lengthChange": true,
    "searching": false,
    "order": [],
    "info": false,
    "autoWidth": false,
    "responsive": true,
    "columnDefs": [{
      "targets": 10,
      "orderable": false
    }],
  });
})

function showNoRecords(records)
{
  $('#records').val(records);
  document.getElementById('advance-payment-form').submit();
}
</script>
@endsection