@extends('users.layout.master')

@section('content')

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6">
          <h1>CableTV Subscribers</h1>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('cabletv-history') }}">History</a></li>
            <li class="breadcrumb-item active">View</li>
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
        <!-- left column -->
        <div class="col-md-12">
          <!-- general form elements -->
          <div class="card card-primary">
            <div class="card-header">
              <h3 class="card-title">View CableTV History</h3>
            </div>
            <!-- /.card-header -->
            <div class="card-body">
              <table class="table table-bordered table-striped">
                <thead>
                  <tr>
                    <th colspan="2" style="text-align:center">&nbsp;</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td style="width:700px;">Date & Time </td>
                    <td>{{ readDateTimeFormat($ctvhistoryArr->start_date_time)  }}</td>
                  </tr>
                  <tr>
                    <td>Type</td>
                    <td><span class="text-{{ internetHistoryTypeAbbreviationToFullName($ctvhistoryArr->entry_type)['color'] }}"><i class="{{ internetHistoryTypeAbbreviationToFullName($ctvhistoryArr->entry_type)['icon'] }}"></i></span> ({{ internetHistoryTypeAbbreviationToFullName($ctvhistoryArr->entry_type)['full'] }})</td>
                  </tr>
                </tbody>
                <thead>
                  <tr>
                    <th colspan="2" style="text-align:center">Customer Details</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td>Application Type</td>
                    <td><span class="text-{{ customerApplicationTypeAbbreviationToFullName(customerDetailsByID($ctvhistoryArr->customer_id)['type'])['color'] }}" title="{{ customerApplicationTypeAbbreviationToFullName(customerDetailsByID($ctvhistoryArr->customer_id)['type'])['full'] }}"><i class="{{ customerApplicationTypeAbbreviationToFullName(customerDetailsByID($ctvhistoryArr->customer_id)['type'])['icon'] }}"></i></span></td>
                  </tr>
                  <tr>
                    <td>Name / Authorized Person</td>
                    <td>{{ customerNameByID($ctvhistoryArr->customer_id) }}</td>
                  </tr>
                  @if(customerDetailsByID($ctvhistoryArr->customer_id)['type']=="P")
                  <tr>
                    <td>Mobile</td>
                    <td>{{ customerDetailsByID($ctvhistoryArr->customer_id)['mobile'] }}</td>
                  </tr>
                  <tr>
                    <td>Email</td>
                    <td>{{ customerDetailsByID($ctvhistoryArr->customer_id)['email'] }}</td>
                  </tr>
                  @endif
                  @if(customerDetailsByID($ctvhistoryArr->customer_id)['type']=="S")
                  <tr>
                    <td>Shop Name</td>
                    <td>{{ customerDetailsByID($ctvhistoryArr->customer_id)['shop_name'] }}</td>
                  </tr>
                  <tr>
                    <td>Shop Mobile</td>
                    <td>{{ customerDetailsByID($ctvhistoryArr->customer_id)['shop_mobile'] }}</td>
                  </tr>
                  <tr>
                    <td>Shop Email</td>
                    <td>{{ customerDetailsByID($ctvhistoryArr->customer_id)['shop_email'] }}</td>
                  </tr>
                  <tr>
                    <td>VAT Number</td>
                    <td>{{ customerDetailsByID($ctvhistoryArr->customer_id)['vat_no'] }}</td>
                  </tr>
                  @endif
                  <tr>
                    <td>Country</td>
                    <td>{{ customerDetailsByID($ctvhistoryArr->customer_id)['country'] }}</td>
                  </tr>
                </tbody>
                @if($ctvhistoryArr->entry_type=="CL")
                <thead>
                  <tr>
                    <th colspan="2" style="text-align:center">Previous Address Details</th>
                  </tr>
                </thead>
                <tbody>  
                  <tr>
                    <td>Address</td>
                    <td>{{ unitAddressByUnitID(cTVHistoryDetailsByID($ctvhistoryArr->refrence_id)['address_id'])  }}</td>
                  </tr>
                </tbody>
                @endif
                <thead>
                  <tr>
                    <th colspan="2" style="text-align:center">Current Address Details</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td>Address</td>
                    <td>{{ unitAddressByUnitID($ctvhistoryArr->address_id) }}</td>
                  </tr>
                </tbody>
                
                <thead>
                  <tr>
                    <th colspan="2" style="text-align:center">Plan Details</th>
                  </tr>
                </thead>
                <tbody>  
                  <tr>
                    <td>CableTV Plan</td>
                    <td>{{ cableTVPlanNameByID($ctvhistoryArr->plan_id)  }}</td>
                  </tr>
                  <tr>
                    <td>Monthly Fee</td>
                    <td>${{ amountFormat2($ctvhistoryArr->monthly_fee) }}</td>
                  </tr>
                  @if($ctvhistoryArr->entry_type=="NR")
                  <tr>
                    <td>Extra TV(s)</td>
                    <td>{{ $ctvhistoryArr->extra_tvs }}</td>
                  </tr>
                  <tr>
                    <td>Subscribe Start Date</td>
                    <td>@if(!empty($ctvhistoryArr->subscribe_start_date)) {{ pdfDateFormat($ctvhistoryArr->subscribe_start_date) }} @endif</td>
                  </tr>
                  <tr>
                    <td>Subscribe End Date</td>
                    <td>@if(!empty($ctvhistoryArr->subscribe_start_date)) {{ pdfDateFormat($ctvhistoryArr->subscribe_end_date) }} @endif</td>
                  </tr>
                  <tr>
                    <td>Period</td>
                    <td>@if(!empty($ctvhistoryArr->period)) {{ $ctvhistoryArr->period }} Month(s) @endif</td>
                  </tr>
                  @endif
                </tbody>
                @if($ctvhistoryArr->entry_type=="TS")
                <thead>
                  <tr>
                    <th colspan="2" style="text-align:center">Suspend/Terminate</th>
                  </tr>
                </thead>
                <tbody>  
                  <tr>
                    <td>Effective Date</td>
                    <td>{{ pdfDateFormat($ctvhistoryArr->start_date_time) }}</td>
                  </tr>
                  <tr>
                    <td>Suspension Start Date</td>
                    <td>{{ pdfDateFormat($ctvhistoryArr->suspension_start_date) }}</td>
                  </tr>
                  <tr>
                    <td>Suspension End Date</td>
                    <td>{{ pdfDateFormat($ctvhistoryArr->suspension_end_date) }}</td>
                  </tr>
                  <tr>
                    <td>Suspension Period</td>
                    <td>{{ $ctvhistoryArr->period }} Month(s)</td>
                  </tr>
                </tbody>
                @endif
                <thead>
                  <tr>
                    <th colspan="2" style="text-align:center">Payment Details</th>
                  </tr>
                </thead>
                <tbody>
                  @if($ctvhistoryArr->entry_type=="NR")                      
                  <tr>
                    <td>Installation Fee</td>
                    <td>${{ amountFormat2($ctvhistoryArr->installation_fee) }}</td>
                  </tr>
                  <tr>
                    <td>Extra TV Fee</td>
                    <td>${{ amountFormat2($ctvhistoryArr->extra_tvs_fee) }}</td>
                  </tr>
                  <tr>
                    <td>Months Total Fee</td>
                    <td>${{ amountFormat2($ctvhistoryArr->months_total_fee) }}</td>
                  </tr>
                  <tr>
                    <td>Others Fee</td>
                    <td>${{ amountFormat2($ctvhistoryArr->others_fee) }}</td>
                  </tr>
                  <tr>
                    <td><b>Total Amount <small>(Installation + Extra TV + Months Total + Others Fee)</small></b></td>
                    <td><b>${{ amountFormat2($ctvhistoryArr->total_amount) }}</b></td>
                  </tr>
                  @endif
                  
                  @if($ctvhistoryArr->entry_type=="CL")                      
                  <tr>
                    <td>Re-Installation Fee</td>
                    <td>${{ amountFormat2($ctvhistoryArr->reinstallation_fee) }}</td>
                  </tr>
                  <tr>
                    <td>Others Fee</td>
                    <td>${{ amountFormat2($ctvhistoryArr->others_fee) }}</td>
                  </tr>
                  <tr>
                    <td><b>Total Amount <small>(Re-Installation + Others Fee)</small></b></td>
                    <td><b>${{ amountFormat2($ctvhistoryArr->total_amount) }}</b></td>
                  </tr>
                  @endif
                  @if($ctvhistoryArr->entry_type=="TS")
                  <tr>
                    <td>Previous Due</td>
                    <td>${{ amountFormat2($ctvhistoryArr->due_amount) }}</td>
                  </tr>
                  <tr>
                    <td>Others Fee</td>
                    <td>${{ amountFormat2($ctvhistoryArr->others_fee) }}</td>
                  </tr>
                  <tr>
                    <td><b>Total Amount <small>(Previous Due + Others Fee)</small></b></td>
                    <td><b>${{ amountFormat2($ctvhistoryArr->total_amount) }}</b></td>
                  </tr>
                  @endif
                  @if($ctvhistoryArr->entry_type=="RC")
                  <tr>
                    <td>Reconnect Fee</td>
                    <td>${{ amountFormat2($ctvhistoryArr->reconnect_fee) }}</td>
                  </tr>
                  <tr>
                    <td>Previous Due</td>
                    <td>${{ amountFormat2($ctvhistoryArr->due_amount) }}</td>
                  </tr>
                  <tr>
                    <td>Others Fee</td>
                    <td>${{ amountFormat2($ctvhistoryArr->others_fee) }}</td>
                  </tr>
                  <tr>
                    <td><b>Total Amount <small>(Reconnect + Previous Due + Others Fee)</small></b></td>
                    <td><b>${{ amountFormat2($ctvhistoryArr->total_amount) }}</b></td>
                  </tr>
                  @endif                      
                  @if($ctvhistoryArr->entry_type=="TS")
                  <tr>
                    <td>Due Amount</td>
                    <td>${{ amountFormat2($ctvhistoryArr->due_amount) }}</td>
                  </tr>
                  <tr>
                    <td>Refund Amount</td>
                    <td>${{ amountFormat2($ctvhistoryArr->refund_amount) }}</td>
                  </tr>
                  <tr>
                    <td>Payable Amount</td>
                    <td>@if($ctvhistoryArr->refund_amount > $ctvhistoryArr->due_amount) ${{ amountFormat($ctvhistoryArr->refund_amount - $ctvhistoryArr->due_amount) }} @else ${{ amountFormat($ctvhistoryArr->due_amount - $ctvhistoryArr->refund_amount) }} @endif</td>
                  </tr>
                  <tr>
                    <td><b>Total Amount <small>(Payable + Others Fee)</small></b></td>
                    <td><b>${{ amountFormat2($ctvhistoryArr->total_amount) }}</b></td>
                  </tr>
                  @endif
                  <tr>
                    <td>Payment Mode</td>
                    <td>{{ paymentModeAbbreviationToFull($ctvhistoryArr->payment_mode) }}</td>
                  </tr>
                  @if(in_array($ctvhistoryArr->payment_mode,["BA", "CH", "OT"]))
                  <tr>
                    <td>Payment Description</td>
                    <td>{{ $ctvhistoryArr->payment_description }}</td>
                  </tr>
                  @endif
                  <tr>
                    <td>Payment By</td>
                    <td><span class="text-{{ paidByAbbreviationToFull($ctvhistoryArr->payment_by)['color'] }}">{{ paidByAbbreviationToFull($ctvhistoryArr->payment_by)['full'] }}</span></td>
                  </tr>
                  <tr>
                    <td>Remark</td>
                    <td>
                      {{ \Illuminate\Support\Str::limit($ctvhistoryArr->remark, 50, $end='...') }}
                      @if(strlen($ctvhistoryArr->remark)>50)<a href="javascript:void(0);" title="{{ $ctvhistoryArr->remark }}"> read more</a>@endif
                    </td>
                  </tr>
                </tbody>
                <thead>
                  <tr>
                    <th colspan="2" style="text-align:center">User Details</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td>Created  By</td>
                    <td>{{ userNameByUserID($ctvhistoryArr->user_id) }}</td>
                  </tr>
                  <tr>
                    <td>Created Date</td>
                    <td>{{ readDateTimeFormat($ctvhistoryArr->created_at) }}</td>
                  </tr>
                </tbody>
              </table>               
            </div>
            <!-- /.card-body -->
            <div class="card-footer">
              <a href="{{ route('cabletv-history') }}" class="btn btn-warning"> <i class="fas fa-backward"></i> Back</a>
            </div>
          </div>
          <!-- /.card -->
        </div>
        <!--/.col (left) -->
      </div>
      <!-- /.row -->
    </div><!-- /.container-fluid -->
  </section>
  <!-- /.content -->
</div>
<!-- /.content-wrapper -->
<script>
$(function () {
  $('[data-toggle="tooltip"]').tooltip()
})
</script>
@endsection