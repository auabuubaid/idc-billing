@extends('users.layout.master')

@section('content')

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6">
          <h1>Change Plan</h1>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{route('dashboard')}}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{route('internet-subscribers')}}">Internet</a></li>
            <li class="breadcrumb-item active">Change Plan</li>
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
          <div class="card card-primary card-outline">
            <div class="card-body">
              <ul class="nav nav-tabs" id="custom-content-above-tab" role="tablist">
                <li class="nav-item">
                  <a class="nav-link active" id="custom-content-above-change-plan-tab" data-toggle="pill" href="#custom-content-above-change-plan" role="tab" aria-controls="custom-content-above-change-plan" aria-selected="true"><i class="fas fa-exchange-alt"></i> Change Plan</a>
                </li>
                <li class="nav-item">
                  <a class="nav-link" href="{{route('internet-change-location')}}"><i class="fas fa-house-damage"></i> Change Location</a>
                </li>
                <li class="nav-item">
                  <a class="nav-link" href="{{route('internet-suspend-service')}}"><i class="fas fa-power-off"></i> Suspension</a>
                </li>
                <li class="nav-item">
                  <a class="nav-link" href="{{route('internet-reconnect-service')}}"><i class="fas fa-plug"></i> Reconnect</a>
                </li>
                <li class="nav-item">
                  <a class="nav-link" href="{{route('internet-terminate-service')}}"><i class="fas fa-times-circle"></i> Terminate</a>
                </li>
              </ul>
              <div class="tab-content" id="custom-content-above-tabContent">
                <div class="tab-pane fade show active" id="custom-content-above-change-plan" role="tabpanel" aria-labelledby="custom-content-above-home-tab">
                  <form role="form" action="{{ route('update-internet-plan') }}" method="post">
                    @csrf
                    <div class="card-body">
                      <fieldset style="border: 1px solid #ced4da; padding:0px 15px;">
                        <legend>Date & Time</legend>
                        <div class="row">
                          <div class="col-md-4">
                            <div class="form-group">
                              <label for="InputServiceDate">Date<span class="text-danger">*</span></label>
                                <div class="input-group">
                                  <div class="input-group">
                                    <div class="input-group-prepend">
                                      <span class="input-group-text"><i class="far fa-calendar-alt"></i></span>
                                    </div>
                                    <input type="text" name="service_date" value="{{ old('service_date') }}" class="form-control pull-right datePicker">
                                  </div>
                                </div>
                              @error('service_date')<span class="text-danger">{{ $message }}</span>@enderror
                            </div>
                          </div>
                          <div class="col-md-4">
                            <div class="bootstrap-timepicker">
                              <div class="form-group">
                                <label for="InputServiceTime">Time<span class="text-danger">*</span></label>
                                  <div class="input-group date" id="timepicker" data-target-input="nearest">
                                    <input type="text" name="service_time" value="{{ empty(old('service_time')) ? currentTime() : old('service_time') }}" class="form-control datetimepicker-input" data-target="#timepicker"/>
                                    <div class="input-group-append" data-target="#timepicker" data-toggle="datetimepicker">
                                      <div class="input-group-text"><i class="far fa-clock"></i></div>
                                    </div>
                                  </div>
                                @error('service_time')<span class="text-danger">{{ $message }}</span>@enderror
                              </div>
                            </div>
                          </div>
                          <div class="col-md-3">
                            <div class="form-group">
                              <label for="InputLastEntryDate">Last Entry Date</label>
                                <div class="input-group">
                                  <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="far fa-calendar-alt"></i></span>
                                  </div>
                                  <input name="last_entry_date" type="text" class="form-control" readonly id="InputLastEntryDate" placeholder="Last entry date" value="{{ old('last_entry_date')}}">
                                </div>
                              @error('last_entry_date')<span class="text-danger">{{ $message }}</span>@enderror
                            </div>
                          </div>
                        </div>
                      </fieldset>
                      <br/> 
                      <fieldset style="border: 1px solid #ced4da; padding:0px 15px;">
                        <legend>Customer Details</legend>
                        <div class="row">
                          <div class="col-md-4">
                            <div class="form-group">
                              <label for="SelectCustomerName">Customer Name<span class="text-danger">*</span></label>
                                <select name="customer_name" class="form-control select2" onchange="getCustomerDetails(this.value)" id="SelectCustomerName">
                                  <option value="">--Select--</option>
                                  @if(count($customersArr)>0)
                                    @foreach($customersArr as $cval)
                                      <option value="{{ $cval->customer_id }}" {{ old('customer_name')==$cval->customer_id ?"selected":""}}>@if(customerDetailsByID($cval->customer_id)['type']=="P"){{ customerDetailsByID($cval->customer_id)['name'] }} @else {{ customerDetailsByID($cval->customer_id)['name'].' ('.customerDetailsByID($cval->customer_id)['shop_name'].' )' }} @endif | {{ buildingLocationAndUnitNumberByUnitID($cval->address_id) }}</option>
                                    @endforeach
                                  @endif
                                </select>
                              @error('customer_name')<span class="text-danger">{{ $message }}</span>@enderror
                            </div>
                          </div>
                          <div class="col-md-4" style="padding-top:7px">
                            <div class="form-group">
                              <label for="InputAgreementPeriod">Customer Type</label>
                              <div class="form-group clearfix">
                                <div class="icheck-primary d-inline" style="padding-right:80px;">
                                  <input type="radio" name="customer_type" value="P" disabled id="CustomerTypePersonal">
                                  <label for="CustomerTypePersonal">Personal</label>
                                </div>
                                <div class="icheck-primary d-inline">
                                  <input type="radio" name="customer_type" value="S" disabled id="CustomerTypeShop">
                                  <label for="CustomerTypeShop">Shop</label>
                                </div>
                              </div>
                            </div>
                          </div>
                          <div class="col-md-4">
                            <div class="form-group">
                              <label for="InputCustomerAddress">Customer Address</label>
                              <input name="customer_address" type="text" class="form-control" id="InputCustomerAddress" readonly placeholder="Customer address" value="{{ old('customer_address')}}">
                              <input name="address_id" type="hidden" id="InputCustomerAddressID" value="">
                            </div>
                          </div>
                          <div class="col-md-4">
                            <div class="form-group">
                              <label for="InputCustomerEmail">Customer Email</label>
                              <input name="customer_email" type="text" class="form-control" id="InputCustomerEmail" readonly placeholder="Customer email" value="{{ old('customer_email')}}">
                            </div>
                          </div>
                          <div class="col-md-4">
                            <div class="form-group">
                              <label for="InputCustomerMobile">Customer Mobile</label>
                              <input name="customer_mobile" type="text" class="form-control" id="InputCustomerMobile" readonly placeholder="Customer mobile" value="{{ old('customer_mobile')}}">
                            </div>
                          </div>                                  
                          <div class="col-md-4"></div>
                          <div class="col-md-3">
                            <div class="form-group">
                              <label for="InputShopName">Shop Name</label>
                              <input name="shop_name" type="text" class="form-control" id="InputShopName" readonly placeholder="Shop name" value="{{ old('shop_name')}}">
                            </div>
                          </div>
                          <div class="col-md-3">
                            <div class="form-group">
                              <label for="InputShopEmail">Shop Email</label>
                              <input name="shop_email" type="text" class="form-control" id="InputShopEmail" readonly placeholder="Shop email" value="{{ old('shop_email')}}">
                            </div>
                          </div>
                          <div class="col-md-3">
                            <div class="form-group">
                              <label for="InputShopMobile">Shop Mobile</label>
                              <input name="shop_mobile" type="text" class="form-control" id="InputShopMobile" readonly placeholder="Shop mobile" value="{{ old('shop_mobile')}}">
                            </div>
                          </div>
                          <div class="col-md-3">
                            <div class="form-group">
                              <label for="InputVATNumber">VAT Number</label>
                              <input name="shop_vat_no" type="text" class="form-control" id="InputVATNumber" readonly placeholder="Shop vat no" value="{{ old('shop_vat_no')}}">
                            </div>
                          </div>
                        </div>
                      </fieldset>  
                      <br/>
                      <div class="row">
                        <div class="col-md-6">
                          <fieldset style="border: 1px solid #ced4da; padding:0px 15px;">
                            <legend>Current Plan</legend>
                            <div class="row">
                              <div class="col-md-6">
                                <div class="form-group">
                                  <label for="InputCurrentPlan">Internet Plan</label>
                                  <input name="current_plan" type="text" class="form-control" id="InputCurrentPlan" readonly placeholder="Plan name" value="{{ old('current_plan')}}">
                                </div>
                              </div>
                              <div class="col-md-6">
                                <div class="form-group">
                                  <label for="InputCurrentMonthlyFee">Monthly Fee</label>
                                  <div class="input-group">
                                    <div class="input-group-prepend">
                                      <span class="input-group-text"><i class="fas fa-dollar-sign"></i></span>
                                    </div>
                                    <input name="current_monthly_fee" type="text" class="form-control" readonly id="InputCurrentMonthlyFee" placeholder="Monthly fee" value="{{ old('current_monthly_fee')}}">
                                  </div>
                                </div>
                              </div>
                              <div class="col-md-4">
                                <div class="form-group">
                                  <label for="InputCurrentSpeed">Speed</label>
                                  <input name="current_speed" type="text" class="form-control" id="InputCurrentSpeed" readonly placeholder="Speed" value="{{ old('current_speed')}}">
                                </div>
                              </div>
                              <div class="col-md-2">
                                <div class="form-group" style="padding-top:37px">
                                  <label for="CurrentSpeedUnit">Mbps</label>
                                </div>
                              </div>
                              <div class="col-md-4">
                                <div class="form-group">
                                  <label for="InputCurrentUploadSpeed">Upload Speed</label>
                                  <input name="current_upload_speed" type="text" class="form-control" id="InputCurrentUploadSpeed" readonly placeholder="Upload speed" value="{{ old('current_upload_speed')}}">
                                </div>
                              </div>
                              <div class="col-md-2">
                                <div class="form-group" style="padding-top:37px">
                                  <label for="CurrentUploadSpeedUnit">Mbps</label>
                                </div>
                              </div>
                            </div>
                          </fieldset>
                        </div>
                        <div class="col-md-6">  
                          <fieldset style="border: 1px solid #ced4da; padding:0px 15px;">
                            <legend>Change Plan</legend>
                            <div class="row">
                              <div class="col-md-6">
                                <div class="form-group">
                                  <label for="SelectInternetPlan">Internet Plan<span class="text-danger">*</span></label>
                                    <select name="internet_plan" class="form-control" onchange="getInternetPlanDetails(this.value)" id="SelectInternetPlan">
                                      <option value="">--Select--</option>
                                      @if(count($internetServicesArr)>0)
                                        @foreach($internetServicesArr as $ival)
                                          <option value="{{ $ival->id }}" {{ old('internet_plan')==$ival->id ?"selected":""}}>{{ $ival->service_name }}</option>
                                        @endforeach
                                      @endif
                                    </select>
                                  @error('internet_plan')<span class="text-danger">{{ $message }}</span>@enderror
                                </div>
                              </div>
                              <div class="col-md-6">
                                <div class="form-group">
                                  <label for="InputMonthlyFee">Monthly Fee</label>
                                  <div class="input-group">
                                    <div class="input-group-prepend">
                                      <span class="input-group-text"><i class="fas fa-dollar-sign"></i></span>
                                    </div>
                                    <input name="monthly_fee" type="text" class="form-control" readonly id="InputMonthlyFee" placeholder="Monthly fee" value="{{ old('monthly_fee')}}">
                                  </div>
                                </div>
                              </div>
                              <div class="col-md-4">
                                <div class="form-group">
                                  <label for="InputSpeed">Speed</label>
                                  <input name="speed" type="text" class="form-control" id="InputSpeed" readonly placeholder="Speed" value="{{ old('speed')}}">
                                </div>
                              </div>
                              <div class="col-md-2">
                                <div class="form-group" style="padding-top:37px">
                                  <label for="SpeedUnit">Mbps</label>
                                </div>
                              </div>
                              <div class="col-md-4">
                                <div class="form-group">
                                  <label for="InputUploadSpeed">Upload Speed</label>
                                  <input name="upload_speed" type="text" class="form-control" id="InputUploadSpeed" readonly placeholder="Upload speed" value="{{ old('upload_speed')}}">
                                </div>
                              </div>
                              <div class="col-md-2">
                                <div class="form-group" style="padding-top:37px">
                                  <label for="UploadSpeedUnit">Mbps</label>
                                </div>
                              </div>
                            </div>
                          </fieldset>
                        </div>
                      </div>
                      <br/> 
                      <fieldset style="border: 1px solid #ced4da; padding:0px 15px;">
                        <legend>Payment Details</legend>
                        <div class="row">
                          <div class="col-md-4">
                            <div class="form-group">
                              <label for="InputNewPlanDepositFee">New Plan Deposit</label>
                              <div class="input-group">
                                <div class="input-group-prepend">
                                  <span class="input-group-text"><i class="fas fa-dollar-sign"></i></span>
                                </div>
                                <input name="new_plan_deposit_fee" readonly type="text" class="form-control" id="InputNewPlanDepositFee" placeholder="New plan deposit fee" value="{{ old('new_plan_deposit_fee')}}">
                              </div>
                            </div>
                          </div>
                          <div class="col-md-4">
                            <div class="form-group">
                              <label for="InputPreviousDepositFee">Previous Deposit</label>
                              <div class="input-group">
                                <div class="input-group-prepend">
                                  <span class="input-group-text"><i class="fas fa-dollar-sign"></i></span>
                                </div>
                                <input name="previous_deposit_fee" readonly type="text" class="form-control" id="InputPreviousDepositFee" placeholder="Previous deposit" value="{{ old('previous_deposit_fee')}}">
                              </div>
                            </div>
                          </div>
                          <div class="col-md-4">
                            <div class="form-group">
                              <label for="InputNewDepositFee">New Deposit</label>
                                <div class="input-group">
                                  <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-dollar-sign"></i></span>
                                  </div>
                                  <input name="new_deposit_fee" type="text" class="form-control fee" id="InputNewDepositFee" placeholder="Enter new deposit" value="{{ old('new_deposit_fee') }}">
                                </div>
                              @error('new_deposit_fee')<span class="text-danger">{{ $message }}</span>@enderror
                            </div>
                          </div>
                          <div class="col-md-4">
                            <div class="form-group">
                              <label for="InputRefund">Refund</label>
                              <div class="input-group">
                                <div class="input-group-prepend">
                                  <span class="input-group-text"><i class="fas fa-dollar-sign"></i></span>
                                </div>
                                <input name="refund_amount" type="text" class="form-control fee" id="InputRefund" placeholder="Enter refund amount" value="{{ old('refund_amount') }}">
                              </div>
                            </div>
                          </div>
                          <div class="col-md-4">
                            <div class="form-group">
                              <label for="InputOthersFee">Others</label>
                                <div class="input-group">
                                  <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-dollar-sign"></i></span>
                                  </div>
                                  <input name="others_fee" type="text" class="form-control fee" id="InputOthersFee" placeholder="Enter others fee" value="{{ old('others_fee') }}">
                                </div>
                              @error('others_fee')<span class="text-danger">{{ $message }}</span>@enderror
                            </div>
                          </div>
                          <div class="col-md-4">
                            <div class="form-group">
                              <label for="InputTotalAmount">Total</label>
                                <div class="input-group">
                                  <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-dollar-sign"></i></span>
                                  </div>
                                  <input name="total_amount" type="text" class="form-control" id="InputTotalAmount" readonly value="{{ old('total_amount') }}">
                                </div>
                              @error('total_amount')<span class="text-danger">{{ $message }}</span>@enderror
                            </div>
                          </div>
                          <div class="col-md-5" style="max-width:400px;">
                            <div class="form-group">
                              <label for="InputPaymentMode">Payment Mode<span class="text-danger">*</span></label>
                                <div class="form-group clearfix">
                                  <div class="icheck-primary d-inline" style="padding-right:15px;">
                                    <input type="radio" name="payment_mode" onchange="fillPaymentDescription(this.value)" {{ old('payment_mode')=='CA'? "checked": "" }} value="CA" id="PaymentModeCash">
                                    <label for="PaymentModeCash">Cash</label>
                                  </div>
                                  <div class="icheck-primary d-inline" style="padding-right:15px;">
                                    <input type="radio" name="payment_mode" onchange="fillPaymentDescription(this.value)" {{ old('payment_mode')=='BA'? "checked": "" }} value="BA" id="PaymentModeBank">
                                    <label for="PaymentModeBank">Bank</label>
                                  </div>
                                  <div class="icheck-primary d-inline" style="padding-right:15px;">
                                    <input type="radio" name="payment_mode" onchange="fillPaymentDescription(this.value)" {{ old('payment_mode')=='CH'? "checked": "" }} value="CH" id="PaymentModeCheque">
                                    <label for="PaymentModeCheque">Cheque</label>
                                  </div>
                                  <div class="icheck-primary d-inline" style="padding-right:15px;">
                                    <input type="radio" name="payment_mode" onchange="fillPaymentDescription(this.value)" {{ old('payment_mode')=='OT'? "checked": "" }} value="OT" id="PaymentModeOther">
                                    <label for="PaymentModeOther">Other</label>
                                  </div>
                                </div>
                              @error('payment_mode')<span class="text-danger">{{ $message }}</span>@enderror
                            </div>
                          </div>
                          <div class="col-md-3">
                            <div class="form-group" id="InputPaymentDescription">
                              <label for="InputPaymentDescription">&nbsp;</label>
                                <input name="payment_description" type="text" class="form-control" placeholder="Payment description" value="{{ old('payment_description') }}">
                              @error('payment_description')<span class="text-danger">{{ $message }}</span>@enderror
                            </div>
                          </div>
                          <div class="col-md-4">
                            <div class="form-group">
                              <label for="InputPaidBy">Paid By<span class="text-danger">*</span></label>
                                <div class="form-group clearfix">
                                  <div class="icheck-primary d-inline" style="padding-right:5px;">
                                    <input type="radio" name="payment_by" value="CP" id="radioCustomerPay">
                                    <label for="radioCustomerPay">Customer Pay</label>
                                  </div>
                                  <div class="icheck-primary d-inline">
                                    <input type="radio" name="payment_by" value="CR" id="radioCompanyRefund">
                                    <label for="radioCompanyRefund">Company Refund</label>
                                  </div>
                                </div>
                              @error('payment_by')<span class="text-danger">{{ $message }}</span>@enderror
                            </div>
                          </div>
                          <div class="col-md-3">
                            <div class="form-group">
                              <label for="InputPaid">Paid<span class="text-danger">*</span></label>
                                <div class="form-group clearfix" style="padding-top:8px;">
                                  <div class="icheck-primary d-inline" style="padding-right:5px;">
                                    <input type="radio" name="paid" {{ old('paid')=="Y" ? "checked" : ""}} value="Y" id="PaidYes">
                                    <label for="PaidYes">Yes</label>
                                  </div>
                                  <div class="icheck-primary d-inline">
                                    <input type="radio" name="paid" {{ old('paid')=="N" ? "checked" : ""}} value="N" id="PaidNo">
                                    <label for="PaidNo">No</label>
                                  </div>
                                </div>
                              @error('paid')<span class="text-danger">{{ $message }}</span>@enderror
                            </div>
                          </div>
                        </div>
                      </fieldset>
                      <br/>
                      <div class="col-md-12">
                        <div class="form-group">
                          <label for="InputRemark">Remark</label>
                            <textarea name="remark" class="form-control" rows="2" placeholder="Enter remark" id="InputRemark"> {{ old('remark') }} </textarea>
                            <input type="hidden" name="refrence" id="InputRefrenceID">
                            <input type="hidden" name="refrence_type" id="InputRefrenceType">
                          @error('remark')<span class="text-danger">{{ $message }}</span>@enderror
                        </div>
                      </div>
                    </div>
                    <!-- /.card-body -->
                    <div class="card-footer">
                      <a href="{{route('internet-history')}}" class="btn btn-warning"> <i class="fas fa-backward"></i> Back</a>
                      <a href="{{route('internet-change-service')}}" class="btn btn-primary"> <i class="fas fa-redo"></i> Refresh</a>
                      <button type="submit" class="btn btn-success"> <i class="fas fa-save"></i> Save</button>
                    </div>
                  </form>
                </div>
              </div>
            </div>
            <!-- /.card -->
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
<!-- InputMask -->
<script src="{{ asset('assets/plugins/moment/moment.min.js')}} "></script>
<script src="{{ asset('assets/plugins/inputmask/min/jquery.inputmask.bundle.min.js')}} "></script>
<!-- date-range-picker -->
<script src="{{ asset('assets/plugins/daterangepicker/daterangepicker.js')}}"></script>
<!-- Page script -->
<script>
$(function () {
  //Initialize Select2 Elements
  $('.select2').select2()
  //Datemask dd/mm/yyyy
  $('#datemask').inputmask('dd-mm-yyyy', { 'placeholder': 'dd-mm-yyyy' })
  //Timepicker
  $('#timepicker').datetimepicker({ format: 'hh:mm A' })
  //Date picker
  $('.datePicker').daterangepicker({
    locale: {  format: 'DD-MM-YYYY' },
    autoclose: true,
    singleDatePicker: true,
  })
})
$( document ).ready(function() {

  var mode= $("input[name='payment_mode']:checked").val();
  fillPaymentDescription(mode);

  //Fill Customer Details on ready
  var custID = $('#SelectCustomerName').val();
  getCustomerDetails(custID);

  // Fill Internet Plan Details
  var planID = $('#SelectInternetPlan').val();
  getInternetPlanDetails(planID);

  // On blur add total amount
  $(".fee").on("blur", function(){
    var sum=0;
    $(".fee").each(function(){
      if($(this).val() !=="")
        sum += parseFloat($(this).val());   
    });    
    $("#InputTotalAmount").val(sum.toFixed(2));
  });

});

function getInternetPlanDetails(planID)
{  
  var previousDeposit = $('#InputPreviousDepositFee').val();
  $('#InputNewDepositFee').val(0);
  $('#InputRefund').val(0);
  var sum = 0;
  $.ajax({
    url: '{{ route("get-plan-details") }}',
    type: 'post',
    data: { "_token":"{{ csrf_token() }}" , "planID": planID },
    dataType: 'json',
    success: function(response){
      if(response != null){
        //alert(response);
        $('#InputPlanName').val(response.service_name);
        $('#InputSpeed').val(response.speed);
        $('#InputUploadSpeed').val(response.upload_speed);
        $('#InputNewPlanDepositFee').val(response.deposit_fee);
        $('#InputMonthlyFee').val(response.monthly_fee);
        if(response.deposit_fee!='' && previousDeposit!="")
        {
          if(response.deposit_fee > previousDeposit) 
          {
            $('#InputNewDepositFee').val((response.deposit_fee-previousDeposit).toFixed(2));
            $('#radioCustomerPay').prop("checked", true);
          }else{
            $('#InputRefund').val((previousDeposit-response.deposit_fee).toFixed(2));
            $('#radioCompanyRefund').prop("checked", true);
          }
        }      
        $(".fee").each(function(){
          if($(this).val() !=="")
            sum += parseFloat($(this).val());   
        });        
        $("#InputTotalAmount").val(sum.toFixed(2));
      }else{
        $('#InputPlanName').val("");
        $('#InputSpeed').val("");
        $('#InputUploadSpeed').val("");
        $('#InputNewPlanDepositFee').val("");
        $('#InputMonthlyFee').val("");
        $("#InputTotalAmount").val(sum.toFixed(2));
      }
    }
  });
}

function getCustomerDetails(custID)
{  
  $.ajax({
    url: '{{ route("get-customer-with-internet-plan") }}',
    type: 'post',
    data: { "_token":"{{ csrf_token() }}" , "custID": custID },
    dataType: 'json',
    success: function(response){
      //alert(response);
      if(response != null){
        (response.type == "P") ? $('#CustomerTypePersonal').prop( "checked", true ): $('#CustomerTypeShop').prop( "checked", true );
        $('#InputCustomerEmail').val(response.email);
        $('#InputCustomerMobile').val(response.mobile);
        $('#InputCustomerAddress').val(response.full_address);
        $('#InputCustomerAddressID').val(response.address_id);
        $('#InputShopName').val(response.shop_name);
        $('#InputShopEmail').val(response.shop_email);
        $('#InputShopMobile').val(response.shop_mobile);
        $('#InputVATNumber').val(response.vat_no);
        $('#InputCurrentPlan').val(response.current_plan_name);
        $('#InputCurrentMonthlyFee').val(response.current_monthly_fee);
        $('#InputCurrentSpeed').val(response.current_speed);
        $('#InputCurrentUploadSpeed').val(response.current_upload_speed);
        $('#InputPreviousDepositFee').val(response.current_deposit_fee);
        $('#InputLastEntryDate').val(response.last_entry_date);
        $('#InputRefrenceID').val(response.refrence_id);
        $('#InputRefrenceType').val(response.entry_type);
      }else{
        $('#CustomerTypePersonal').prop( "checked", false );
        $('#CustomerTypeShop').prop( "checked", false );
        $('#InputCustomerEmail').val("");
        $('#InputCustomerMobile').val("");
        $('#InputCustomerAddress').val("");
        $('#InputCustomerAddressID').val("");
        $('#InputShopName').val("");
        $('#InputShopEmail').val("");
        $('#InputShopMobile').val("");
        $('#InputVATNumber').val("");
        $('#InputCurrentPlan').val("");
        $('#InputCurrentMonthlyFee').val("");
        $('#InputCurrentSpeed').val("");
        $('#InputCurrentUploadSpeed').val("");
        $('#InputPreviousDepositFee').val("");
        $('#InputLastEntryDate').val("");
        $('#InputRefrenceID').val("");
        $('#InputRefrenceType').val("");
      }
    }
  });
}

function fillPaymentDescription(mode)
{  
  if(mode=="BA" || mode=="CH" || mode=="OT"){
    $('#InputPaymentDescription').show();
  }else{
    $("input[name='payment_description']").val("");
    $('#InputPaymentDescription').hide();
  }
}
</script>
@endsection