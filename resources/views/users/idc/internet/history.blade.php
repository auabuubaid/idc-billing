@extends('users.layout.master')

@section('content')

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6">
          <h1>History</h1>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{route('dashboard')}}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{route('internet-subscribers')}}">Internet</a></li>
            <li class="breadcrumb-item active">History</li>
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
        <div class="col-md-12">
          <div class="card">
            <div class="card-header">
              <h3 class="col-md-12 card-title">
                <form role="form" action="{{route('internet-history')}}" method="get" id="internet-history-form">
                  <div class="row">
                    <div class="col-md-2">
                      <div class="form-group">
                        <label for="SelectMonthYear">Month Year</label>
                        <select name="monthlyYear" class="form-control select2" id="SelectMonthYear" onchange="this.form.submit()">
                          <option value="">--Select--</option>
                          @php $start = $month = strtotime(currentYearMonth().'-01') @endphp  @php  $end = strtotime('2017-10-01') @endphp
                          @while($end < $month)
                            <option value="{{ date('m-Y', $month) }}" {{ $monthYear==date('m-Y', $month) ? "selected": "" }}>{{ date('m-Y', $month), PHP_EOL }}</option>
                            @php $month = strtotime("-1 month", $month) @endphp
                          @endwhile
                        </select>
                      </div>
                    </div>
                    <div class="col-md-2">
                      <div class="form-group">
                        <label for="SelectEntryType">Type</label>
                        <select name="entry_type" class="form-control" id="SelectEntryType" onchange="this.form.submit()">
                          <option value="">--Select--</option>
                          <option value="NR" {{ $entry_type=='NR' ? "selected": "" }}>{{ internetHistoryTypeAbbreviationToFullName('NR')['full'] }}</option>
                          <option value="CP" {{ $entry_type=='CP' ? "selected": "" }}>{{ internetHistoryTypeAbbreviationToFullName('CP')['full'] }}</option>
                          <option value="CL" {{ $entry_type=='CL' ? "selected": "" }}>{{ internetHistoryTypeAbbreviationToFullName('CL')['full'] }}</option>
                          <option value="SS" {{ $entry_type=='SS' ? "selected": "" }}>{{ internetHistoryTypeAbbreviationToFullName('SS')['full'] }}</option>
                          <option value="RC" {{ $entry_type=='RC' ? "selected": "" }}>{{ internetHistoryTypeAbbreviationToFullName('RC')['full'] }}</option>
                          <option value="TS" {{ $entry_type=='TS' ? "selected": "" }}>{{ internetHistoryTypeAbbreviationToFullName('TS')['full'] }}</option>
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
                    <div class="col-md-2">
                      <div class="form-group">
                        <label for="SelectStatus">Status</label>
                        <select name="status" class="form-control" id="SelectStatus" onchange="this.form.submit()">
                          <option value="">--All--</option>
                          <option value="Y" {{ $status=='Y' ? "selected": "" }}>Paid</option>
                          <option value="N" {{ $status=='N' ? "selected": "" }}>Not Paid</option>
                        </select>
                      </div>
                    </div>
                  </div>
                  <input type="hidden" id="records" name="records" value="{{ $records }}">
                </form>   
              </h3>
            </div>
            <div class="card-body" style="overflow: auto;">
              <table id="history" class="table table-bordered table-striped" style="width:99%; text-align: center">
                <thead>
                  <tr>
                    <th>Sr</th>
                    <th>Date</th>
                    <th>Type</th>
                    <th>Customer Name</th>
                    <th>Address</th>
                    <th>Plan</th>
                    <th>Deposit</th>
                    <th>Payment By</th>
                    <th>Total</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                  @if(count($ihistoryArr)>0)
                    @foreach($ihistoryArr as $hkey=>$hval)
                      <tr>
                        <td>{{ $hkey+$ihistoryArr->firstItem() }}.</td>
                        <td>{{ pdfDateFormat($hval->start_date_time) }}</td>
                        <td><span title="{{ internetHistoryTypeAbbreviationToFullName($hval->entry_type)['full'] }}" class="text-{{ internetHistoryTypeAbbreviationToFullName($hval->entry_type)['color'] }}"><i class="{{ internetHistoryTypeAbbreviationToFullName($hval->entry_type)['icon'] }}"></i></span></td>
                        <td>{{ customerNameByID($hval->customer_id) }}<br/>{{ customerDetailsByID($hval->customer_id)['shop_name'] }}</td>
                        <td>@if($hval->entry_type=='CL') {{ buildingLocationAndUnitNumberByUnitID(ihistoryDetailsByID($hval->refrence_id)['address_id']) }} <span class="text-danger"><i class="fas fa-arrow-circle-right"></i></span> @endif {{ buildingLocationAndUnitNumberByUnitID($hval->address_id) }}</td>
                        <td>@if($hval->entry_type=='CP') {{ planDetailsByPlanID(ihistoryDetailsByID($hval->refrence_id)['plan_id'])['plan_name'] }} <span class="text-danger"><i class="fas fa-arrow-alt-circle-right"></i></span> @endif{{ planDetailsByPlanID($hval->plan_id)['plan_name'] }}</td>
                        <td>${{ amountFormat($hval->deposit_fee) }}</td>
                        <td><span class="text-{{ ($hval->paid=='Y') ? 'success' : 'danger' }}">{{ ($hval->paid=='Y') ? paidByAbbreviationToFull($hval->payment_by)['full'] : (($hval->payment_by=='CR') ? 'Not Refunded':'Not Paid') }}</span></td>
                        <td><span class="text-{{ ($hval->paid=='Y') ? 'success' : 'danger' }}"><b>${{ amountFormat($hval->total_amount) }}</b></span></td>
                        <td>
                            @if(checkPermission($userArr->read))<a href="{{route('view-internet-transaction',['id'=>$hval->id])}}" class="btn btn-info" title="View Invoice"><i class="fas fa-eye"></i></a> @endif
                            @if(checkPermission($userArr->download))<a href="{{route('generate-internet-invoice',['id'=>$hval->id])}}" target="_blank" class="btn btn-danger" title="Generate Invoice"><i class="fas fa-file-pdf"></i></a> @endif
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
                  <span class="pt-1">Showing {{ $ihistoryArr->firstItem() }} to {{ $ihistoryArr->lastItem() }} of {{ $ihistoryArr->total() }} entries</span>
                </div>                
              </div>
              <div class="float-right">{{ $ihistoryArr->links() }}</div>
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

  $("#history").DataTable({
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

function showNoRecords(records)
{
  $('#records').val(records);
  document.getElementById('internet-history-form').submit();
}
</script>
@endsection