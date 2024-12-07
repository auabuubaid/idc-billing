@extends('users.layout.master')

@section('content')

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6">
          <h1>View Internet History</h1>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{route('dashboard')}}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{route('internet-subscribers')}}">Internet Subscribers</a></li>
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
              <h3 class="card-title">View Internet History</h3>
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
                    <td>Date & Time </td>
                    <td>{{ readDateTimeFormat($ihistoryArr->start_date_time)  }}</td>
                  </tr>
                  <tr>
                    <td>Type</td>
                    <td><span class="text-{{ internetHistoryTypeAbbreviationToFullName($ihistoryArr->entry_type)['color'] }}"><i class="{{ internetHistoryTypeAbbreviationToFullName($ihistoryArr->entry_type)['icon'] }}"></i></span> ({{ internetHistoryTypeAbbreviationToFullName($ihistoryArr->entry_type)['full'] }})</td>
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
                    <td><span class="text-{{ customerApplicationTypeAbbreviationToFullName(customerDetailsByID($ihistoryArr->customer_id)['type'])['color'] }}" title="{{ customerApplicationTypeAbbreviationToFullName(customerDetailsByID($ihistoryArr->customer_id)['type'])['full'] }}"><i class="{{ customerApplicationTypeAbbreviationToFullName(customerDetailsByID($ihistoryArr->customer_id)['type'])['icon'] }}"></i></span></td>
                  </tr>
                  <tr>
                    <td>Name / Authorized Person</td>
                    <td>{{ customerNameByID($ihistoryArr->customer_id) }}</td>
                  </tr>
                  @if(customerDetailsByID($ihistoryArr->customer_id)['type']=="P")
                  <tr>
                    <td>Mobile</td>
                    <td>{{ customerDetailsByID($ihistoryArr->customer_id)['mobile'] }}</td>
                  </tr>
                  <tr>
                    <td>Email</td>
                    <td>{{ customerDetailsByID($ihistoryArr->customer_id)['email'] }}</td>
                  </tr>
                  @endif
                  @if(customerDetailsByID($ihistoryArr->customer_id)['type']=="S")
                  <tr>
                    <td>Shop Name</td>
                    <td>{{ customerDetailsByID($ihistoryArr->customer_id)['shop_name'] }}</td>
                  </tr>
                  <tr>
                    <td>Shop Mobile</td>
                    <td>{{ customerDetailsByID($ihistoryArr->customer_id)['shop_mobile'] }}</td>
                  </tr>
                  <tr>
                    <td>Shop Email</td>
                    <td>{{ customerDetailsByID($ihistoryArr->customer_id)['shop_email'] }}</td>
                  </tr>
                  <tr>
                    <td>VAT Number</td>
                    <td>{{ customerDetailsByID($ihistoryArr->customer_id)['vat_no'] }}</td>
                  </tr>
                  @endif
                  <tr>
                    <td>Country</td>
                    <td>{{ customerDetailsByID($ihistoryArr->customer_id)['country'] }}</td>
                  </tr>
                </tbody>
                @if($ihistoryArr->entry_type=="CL")
                <thead>
                  <tr>
                    <th colspan="2" style="text-align:center">Previous Address Details</th>
                  </tr>
                </thead>
                <tbody>  
                  <tr>
                    <td>Address</td>
                    <td>{{ unitAddressByUnitID(ihistoryDetailsByID($ihistoryArr->refrence_id)['address_id'])  }}</td>
                  </tr>
                </tbody>
                @endif
                <thead>
                  <tr>
                    <th colspan="2" style="text-align:center">@if($ihistoryArr->entry_type=="CL") Current @endif  Address Details</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td>Address</td>
                    <td>{{ unitAddressByUnitID($ihistoryArr->address_id) }}</td>
                  </tr>
                </tbody>
                @if($ihistoryArr->entry_type=="CP")
                <thead>
                  <tr>
                    <th colspan="2" style="text-align:center">Previous Plan Details</th>
                  </tr>
                </thead>
                <tbody>  
                  <tr>
                    <td>Internet Plan</td>
                    <td>{{ planDetailsByPlanID(ihistoryDetailsByID($ihistoryArr->refrence_id)['plan_id'])['plan_name']   }}</td>
                  </tr>
                  <tr>
                    <td>Speed / Upload Speed</td>
                    <td>{{ planDetailsByPlanID(ihistoryDetailsByID($ihistoryArr->refrence_id)['plan_id'])['speed'] }} Mbps  /  {{ planDetailsByPlanID(ihistoryDetailsByID($ihistoryArr->refrence_id)['plan_id'])['upload_speed'] }} Mbps</td>
                  </tr>
                  <tr>
                    <td>Data Usage</td>
                    <td>{{ LimitedUnlimitedAbbreviationToFull(planDetailsByPlanID(ihistoryDetailsByID($ihistoryArr->refrence_id)['plan_id'])['data_usage']) }}</td>
                  </tr>
                  <tr>
                    <td>Deposit Fee</td>
                    <td>${{ amountFormat($ihistoryArr->previous_deposit_fee) }}</td>
                  </tr>
                  <tr>
                    <td>Monthly Fee</td>
                    <td>${{ amountFormat(ihistoryDetailsByID($ihistoryArr->refrence_id)['monthly_fee']) }}</td>
                  </tr>
                </tbody>
                @endif
                <thead>
                  <tr>
                    <th colspan="2" style="text-align:center">@if($ihistoryArr->entry_type=="CP") Current @endif Plan Details</th>
                  </tr>
                </thead>
                <tbody>  
                  <tr>
                    <td>Internet Plan</td>
                    <td>{{ planDetailsByPlanID($ihistoryArr->plan_id)['plan_name']   }}</td>
                  </tr>
                  <tr>
                    <td>Speed / Upload Speed</td>
                    <td>{{ planDetailsByPlanID($ihistoryArr->plan_id)['speed'] }} Mbps  /  {{ planDetailsByPlanID($ihistoryArr->plan_id)['upload_speed'] }} Mbps</td>
                  </tr>
                  <tr>
                    <td>Data Usage</td>
                    <td>{{ LimitedUnlimitedAbbreviationToFull(planDetailsByPlanID($ihistoryArr->plan_id)['data_usage']) }}</td>
                  </tr>
                  @if($ihistoryArr->entry_type=="MP")
                  <tr>
                    <td>Plan Expired Date</td>
                    <td>{{ pdfDateFormat($ihistoryArr->month_end_date) }}</td>
                  </tr>
                  @endif
                  @if($ihistoryArr->entry_type!="TS")
                  <tr>
                    <td>Deposit Fee</td>
                    <td>${{ amountFormat($ihistoryArr->deposit_fee) }}</td>
                  </tr>
                  <tr>
                    <td>Monthly Fee</td>
                    <td>${{ amountFormat($ihistoryArr->monthly_fee) }}</td>
                  </tr>
                  @endif
                  @if($ihistoryArr->entry_type=="CP")
                  <tr>
                    <td>Susbcription Level</td>
                    <td><span class="text-{{ subscriptionLevelToFull($ihistoryArr->plan_level)['color'] }}" title="{{ subscriptionLevelToFull($ihistoryArr->plan_level)['full'] }}"><i class="{{ subscriptionLevelToFull($ihistoryArr->plan_level)['icon'] }}"></i></span></td>
                  </tr>
                  @endif
                </tbody>
                @if($ihistoryArr->entry_type=="SS")
                <thead>
                  <tr>
                    <th colspan="2" style="text-align:center">Suspension Details</th>
                  </tr>
                </thead>
                <tbody>  
                  <tr>
                    <td>Effective Date</td>
                    <td>{{ pdfDateFormat($ihistoryArr->start_date_time) }}</td>
                  </tr>
                  <tr>
                    <td>Suspension Start Date</td>
                    <td>{{ pdfDateFormat($ihistoryArr->suspension_start_date) }}</td>
                  </tr>
                  <tr>
                    <td>Suspension End Date</td>
                    <td>@if($ihistoryArr->suspension_end_date!=''){{ pdfDateFormat($ihistoryArr->suspension_end_date) }}@endif</td>
                  </tr>
                  <tr>
                    <td>Suspension Period</td>
                    <td>{{ $ihistoryArr->suspension_period }} Days</td>
                  </tr>
                </tbody>
                @endif
                <thead>
                  <tr>
                    <th colspan="2" style="text-align:center">Payment Details</th>
                  </tr>
                </thead>
                <tbody>
                  @if($ihistoryArr->entry_type=="NR")
                  <tr>
                    <td>Deposit Fee</td>
                    <td>${{ amountFormat($ihistoryArr->deposit_fee) }}</td>
                  </tr>
                  <tr>
                    <td>Installation Fee</td>
                    <td>${{ amountFormat($ihistoryArr->installation_fee) }}</td>
                  </tr>
                  <tr>
                    <td>Others Fee</td>
                    <td>${{ amountFormat($ihistoryArr->others_fee) }}</td>
                  </tr>
                  <tr>
                    <td><b>Total Amount <small>(Deposit + Installation + Other Fee)</small></b></td>
                    <td><b>${{ amountFormat($ihistoryArr->total_amount) }}</b></td>
                  </tr>
                  @endif
                  @if($ihistoryArr->entry_type=="CP")
                  <tr>
                    <td>New Deposit</td>
                    <td>${{ amountFormat($ihistoryArr->deposit_fee) }}</td>
                  </tr>
                  <tr>
                    <td>Prevoius Deposit Fee</td>
                    <td>${{ amountFormat($ihistoryArr->previous_deposit_fee) }}</td>
                  </tr>
                  <tr>
                    <td>Others Fee</td>
                    <td>${{ amountFormat($ihistoryArr->others_fee) }}</td>
                  </tr>
                  <tr>
                    <td>Refund Amount <small>@if($ihistoryArr->plan_level=='DOWNGRADE') {{ '(Prevoius Deposit - New Deposit)' }} @endif</td>
                    <td>${{ amountFormat($ihistoryArr->refund_amount) }}</td>
                  </tr>
                  <tr>
                    <td><b>Total Amount <small>@if($ihistoryArr->plan_level=='UPGRADE') {{ '(New Deposit - Prevoius Deposit + Other Fee)' }} @else {{ '(Refund Amount + Other Fee)' }} @endif</small></b></td>
                    <td><b>${{ amountFormat($ihistoryArr->total_amount) }}</b></td>
                  </tr>
                  @endif                    
                  @if($ihistoryArr->entry_type=="CL")
                  <tr>
                    <td>Re-Installation Fee</td>
                    <td>${{ amountFormat($ihistoryArr->reinstallation_fee) }}</td>
                  </tr>
                  <tr>
                    <td>Others Fee</td>
                    <td>${{ amountFormat($ihistoryArr->others_fee) }}</td>
                  </tr>
                  <tr>
                    <td><b>Total Amount <small>(Re-Installation + Other Fee)</small></b></td>
                    <td><b>${{ amountFormat($ihistoryArr->total_amount) }}</b></td>
                  </tr>
                  @endif
                  @if($ihistoryArr->entry_type=="SS")
                  <tr>
                    <td>Due Amount</td>
                    <td>${{ amountFormat($ihistoryArr->due_amount) }}</td>
                  </tr>
                  <tr>
                    <td>Others Fee</td>
                    <td>${{ amountFormat($ihistoryArr->others_fee) }}</td>
                  </tr>
                  <tr>
                    <td><b>Total Amount <small>(Due + Other Fee)</small></b></td>
                    <td><b>${{ amountFormat($ihistoryArr->total_amount) }}</b></td>
                  </tr>
                  @endif
                  @if($ihistoryArr->entry_type=="RC")
                  <tr>
                    <td>Reconnect Fee</td>
                    <td>${{ amountFormat($ihistoryArr->reconnect_fee) }}</td>
                  </tr>
                  <tr>
                    <td>Others Fee</td>
                    <td>${{ amountFormat($ihistoryArr->others_fee) }}</td>
                  </tr>
                  <tr>
                    <td><b>Total Amount <small>(Reconnect Fee + Other Fee)</small></b></td>
                    <td><b>${{ amountFormat($ihistoryArr->total_amount) }}</b></td>
                  </tr>
                  @endif
                  @if($ihistoryArr->entry_type=="TS")
                  <tr>
                    <td>Deposit Amount</td>
                    <td>${{ amountFormat($ihistoryArr->refund_amount) }}</td>
                  </tr>
                  <tr>
                    <td>Due Amount</td>
                    <td>${{ amountFormat($ihistoryArr->due_amount) }}</td>
                  </tr>
                  <tr>
                    <td>Payable Amount</td>
                    <td>@if($ihistoryArr->refund_amount > $ihistoryArr->due_amount) ${{ amountFormat($ihistoryArr->refund_amount - $ihistoryArr->due_amount) }} @else ${{ amountFormat($ihistoryArr->due_amount - $ihistoryArr->refund_amount) }} @endif</td>
                  </tr>
                  <tr>
                    <td>Others Fee</td>
                    <td>${{ amountFormat($ihistoryArr->others_fee) }}</td>
                  </tr>
                  <tr>
                    <td><b>Total Amount <small>(Payable + Others Fee)</small></b></td>
                    <td><b>${{ amountFormat($ihistoryArr->total_amount) }}</b></td>
                  </tr>
                  @endif
                  @if(in_array($ihistoryArr->entry_type,["CP", "CL", "TS", "NR","MP","RC"]))
                  <tr>
                    <td>Payment Mode</td>
                    <td>{{ paymentModeAbbreviationToFull($ihistoryArr->payment_mode) }}</td>
                  </tr>
                  <tr>
                    <td>Payment By</td>
                    <td><span class="text-{{ paidByAbbreviationToFull($ihistoryArr->payment_by)['color'] }}">{{ paidByAbbreviationToFull($ihistoryArr->payment_by)['full'] }}</span></td>
                  </tr>
                  @endif
                  <tr>
                    <td>Remark</td>
                    <td>
                      {{ \Illuminate\Support\Str::limit($ihistoryArr->remark, 50, $end='...') }}
                      @if(strlen($ihistoryArr->remark)>50)<a href="javascript:void(0);" title="{{ $ihistoryArr->remark }}"> read more</a>@endif
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
                    <td>{{ userNameByUserID($ihistoryArr->user_id) }}</td>
                  </tr>
                  <tr>
                    <td>Created Date</td>
                    <td>{{ readDateTimeFormat($ihistoryArr->start_date_time) }}</td>
                  </tr>
                </tbody>
              </table>               
            </div>
            <!-- /.card-body -->
            <div class="card-footer">
              <a href="{{route('internet-history')}}" class="btn btn-warning"> <i class="fas fa-backward"></i> Back</a>
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
@endsection