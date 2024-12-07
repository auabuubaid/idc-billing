@extends('users.layout.master')

@section('content')

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6">
          <h1>Subscribers</h1>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{route('dashboard')}}">Dashboard</a></li>
            <li class="breadcrumb-item">Cable TV</li>
            <li class="breadcrumb-item active">Subscribers</li>
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
                <form role="form" action="{{route('cabletv-subscribers')}}" method="get" id="cabletv-subscribers-form">
                  <div class="row">
                    <div class="col-md-2">
                      <div class="form-group">
                        <label for="SelectMonthYear">Month Year</label>
                        <select name="monthYear" class="form-control select2" id="SelectMonthYear" onchange="this.form.submit()">
                          <option value="">--Select--</option>
                          @php $start = $month = strtotime(currentYearMonth().'-01') @endphp  @php  $end = strtotime('2022-07-01') @endphp
                          @while($end < $month)
                            <option value="{{ date('m-Y', $month) }}" {{ $monthYear==date('m-Y', $month) ? "selected": "" }}>{{ date('m-Y', $month), PHP_EOL }}</option>
                            @php $month = strtotime("-1 month", $month) @endphp
                          @endwhile
                        </select>
                      </div>
                    </div>
                    <div class="col-md-2">
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
                  </div>
                  <input type="hidden" id="records" name="records" value="{{ $records }}">
                </form>   
              </h3>
            </div>
            <div class="card-body table-responsive p-2">
              <table id="subscribers" class="table table-bordered table-striped" style="width:99%; text-align: center">
                <thead>
                  <tr>
                    <th>Sr</th>
                    <th>Register Date</th>
                    <th style="min-width:50px">Type</th>
                    <th>Customer Name</th>
                    <th>Address</th>
                    <th>Plan Name</th>
                    <th>Status</th>
                    <th>Advance Payment</th>
                  </tr>
                </thead>
                <tbody>
                  @if(count($subscribersArr)>0)
                    @foreach($subscribersArr as $skey=>$sval)
                    <tr>
                      <td>{{ $skey+$subscribersArr->firstItem() }}.</td>
                      <td>{{ pdfDateFormat($sval->registration_date) }}</td>
                      <td><span title="{{ customerApplicationTypeAbbreviationToFullName(customerDetailsByID($sval->customer_id)['type'])['full'] }}" class="text-{{ customerApplicationTypeAbbreviationToFullName(customerDetailsByID($sval->customer_id)['type'])['color'] }}"><i class="{{ customerApplicationTypeAbbreviationToFullName(customerDetailsByID($sval->customer_id)['type'])['icon'] }}"></i></span></td>
                      <td>{{ customerNameByID($sval->customer_id) }}</td>
                      <td>{{ buildingLocationAndUnitNumberByUnitID($sval->address_id) }}</td>
                      <td>{{ cableTVPlanNameByPlanID($sval->plan_id) }}</td>
                      <td><span class="text-{{ (currentCableTVSubscribersStatusByID($sval->customer_id)=='Active')? 'success' : 'danger' }}" title="{{ currentCableTVSubscribersStatusByID($sval->customer_id) }}"> <i class="fas fa-{{ (currentCableTVSubscribersStatusByID($sval->customer_id)=='Active')? 'user-check' : 'user-slash' }}"></i></span></td>
                      <td>@if(currentCableTVSubscribersStatusByID($sval->customer_id)=='Active')<a href="{{ route('edit-cabletv-advance-payment',$sval->customer_id) }}"  class="btn btn-warning"> <i class="fas fa-money-bill-alt"></i></span>@endif</td>
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
              <div class="float-right">{{ $subscribersArr->withQueryString()->links() }}</div>
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
      "targets": 7,
      "orderable": false
    }],
  });
})

function showNoRecords(records)
{
  $('#records').val(records);
  document.getElementById('cabletv-subscribers-form').submit();
}
</script>
@endsection