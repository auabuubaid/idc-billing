@extends('users.layout.master')

@section('content')

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6">
          <h1>Terminate</h1>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('cabletv-subscribers') }}">Cable TV</a></li>
            <li class="breadcrumb-item active">Terminate</li>
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
                  <a class="nav-link" href="{{ route('cabletv-change-location') }}"><i class="fas fa-house-damage"></i> Change Location</a>
                </li>
                <li class="nav-item">
                  <a class="nav-link active" id="custom-content-above-terminate-tab" data-toggle="pill" href="#custom-content-above-terminate" role="tab" aria-controls="custom-content-above-terminate" aria-selected="false"><i class="fas fa-times-circle"></i> Terminate</a>
                </li>
                <li class="nav-item">
                  <a class="nav-link" href="{{ route('cabletv-reconnect-service') }}"><i class="fas fa-plug"></i> Reconnect</a>
                </li>
                <li class="nav-item">
                  <a class="nav-link" href="{{ route('cabletv-change-owner') }}"><i class="fas fa-user-check"></i> Change Owner</a>
                </li>
              </ul>
              <div class="tab-content" id="custom-content-above-tabContent">
                <div class="tab-pane fade show active" id="custom-content-above-terminate" role="tabpanel" aria-labelledby="custom-content-above-messages-tab">
                  <form role="form" action="{{route('update-cabletv-termination')}}" method="post">
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
                                    <input type="text" name="service_date" value="{{ old('service_date') }}" id="InputServiceDate" class="form-control pull-right datePicker">
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
                                    <input type="text" name="service_time" id="InputServiceTime" value="{{ currentTime() }}" class="form-control datetimepicker-input" data-target="#timepicker"/>
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
                                  <input name="last_entry_date" type="text" class="form-control" readonly id="InputLastEntryDate" placeholder="Last entry date" value="{{ old('last_entry_date') }}">
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
                                      <option value="{{ $cval->customer_id }}" {{ old('customer_name')==$cval->customer_id ?"selected":""}}> @if(customerDetailsByID($cval->customer_id)['type']=="P"){{ customerDetailsByID($cval->customer_id)['name'] }} @else {{ customerDetailsByID($cval->customer_id)['name'].' ('.customerDetailsByID($cval->customer_id)['shop_name'].' )' }} @endif  | {{ buildingLocationAndUnitNumberByUnitID($cval->address_id) }}</option>
                                    @endforeach
                                  @endif
                                </select>
                              @error('customer_name')<span class="text-danger">{{ $message }}</span>@enderror
                            </div>
                          </div>
                          <div class="col-md-4">
                            <div class="form-group">
                              <label for="InputAgreementPeriod">Customer Type</label>
                              <div class="form-group clearfix" style="padding-top:7px">
                                <div class="icheck-primary d-inline" style="padding-right:80px;">
                                  <input type="radio" name="customer_type" disabled value="P" {{ old('customer_type')=="P" ? "checked" : ""}} id="CustomerTypePersonal">
                                  <label for="CustomerTypePersonal">Personal</label>
                                </div>
                                <div class="icheck-primary d-inline">
                                  <input type="radio" name="customer_type" disabled value="S" {{ old('customer_type')=="S" ? "checked" : ""}} id="CustomerTypeShop">
                                  <label for="CustomerTypeShop">Company</label>
                                </div>
                              </div>
                            </div>
                          </div>
                          <div class="col-md-4">
                            <div class="form-group">
                              <label for="InputCustomerAddress">Customer Address</label>
                                <input name="customer_address" type="text" class="form-control" id="InputCustomerAddress" readonly placeholder="Customer address" value="{{ old('customer_address')}}">
                                <input name="address_id" id="InputCustomerAddressID" type="hidden" value="">
                              @error('customer_address')<span class="text-danger">{{ $message }}</span>@enderror
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
                          <div class="col-md-4">
                            <div class="form-group">
                              <label for="InputShopName">Shop Name</label>
                              <input name="shop_name" type="text" class="form-control" id="InputShopName" readonly placeholder="Shop name" value="{{ old('shop_name')}}">
                            </div>
                          </div>
                          <div class="col-md-4">
                            <div class="form-group">
                              <label for="InputShopEmail">Shop Email</label>
                              <input name="shop_email" type="text" class="form-control" id="InputShopEmail" readonly placeholder="Shop email" value="{{ old('shop_email')}}">
                            </div>
                          </div>
                          <div class="col-md-2">
                            <div class="form-group">
                              <label for="InputShopMobile">Shop Mobile</label>
                              <input name="shop_mobile" type="text" class="form-control" id="InputShopMobile" readonly placeholder="Shop mobile" value="{{ old('shop_mobile')}}">
                            </div>
                          </div>
                          <div class="col-md-2">
                            <div class="form-group">
                              <label for="InputVATNumber">VAT Number</label>
                              <input name="shop_vat_no" type="text" class="form-control" id="InputVATNumber" readonly placeholder="Shop vat no" value="{{ old('shop_vat_no')}}">
                            </div>
                          </div>
                        </div>
                      </fieldset> 
                      <br/>
                      <fieldset style="border: 1px solid #ced4da; padding:0px 15px;">
                        <legend>CableTV Plan</legend>
                        <div class="row">
                          <div class="col-md-3">
                            <div class="form-group">
                              <label for="InputPlan">Plan</label>
                              <input name="plan_name" type="text" class="form-control" id="InputPlan" readonly placeholder="Internet plan" value="{{ old('plan_name')}}">
                              <input name="plan_id" type="hidden" id="InputPlanID" value="{{ old('plan_id')}}">
                            </div>
                          </div>
                          <div class="col-md-3">
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
                        </div>
                      </fieldset>
                      </br>
                      <fieldset style="border: 1px solid #ced4da; padding:0px 15px;">
                        <legend>Payment Details</legend>
                        <div class="row">
                          <div class="col-md-3">
                            <div class="form-group">
                              <label for="InputRefund">Refund<span class="text-danger">*</span></label>
                                <div class="input-group">
                                  <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-dollar-sign"></i></span>
                                  </div>
                                  <input name="refund_amount" type="text" class="form-control updateTotal" id="InputRefund" placeholder="Refund amount" value="{{ old('refund_amount')}}">
                                </div>
                              @error('refund_amount')<span class="text-danger">{{ $message }}</span>@enderror
                            </div>
                          </div>
                          <div class="col-md-3">
                            <div class="form-group">
                              <label for="InputDueAmount">Due Amount<span class="text-danger">*</span></label>
                                <div class="input-group">
                                  <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-dollar-sign"></i></span>
                                  </div>
                                  <input name="due_amount" type="text" class="form-control updateTotal" id="InputDueAmount" placeholder="Due amount" value="{{ old('due_amount')}}">
                                </div>
                              @error('due_amount')<span class="text-danger">{{ $message }}</span>@enderror
                            </div>
                          </div>
                          <div class="col-md-3">
                            <div class="form-group">
                              <label for="InputOthersFee">Others</label>
                                <div class="input-group">
                                  <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-dollar-sign"></i></span>
                                  </div>
                                  <input name="others_fee" type="text" class="form-control fee" id="InputOthersFee" placeholder="Enter others fee" value="{{ old('others_fee')}}">
                                </div>
                              @error('others_fee')<span class="text-danger">{{ $message }}</span>@enderror
                            </div>
                          </div>
                          <div class="col-md-3">
                            <div class="form-group">
                              <label for="InputTotalFee">Total</label>
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
                                <div class="form-group clearfix" style="padding-top:8px;">
                                  <div class="icheck-primary d-inline" style="padding-right:5px;">
                                    <input type="radio" name="payment_mode" onchange="fillPaymentDescription(this.value)" {{ old('payment_mode')=='CA'? "checked": "" }} value="CA" id="PaymentModeCash">
                                    <label for="PaymentModeCash">Cash</label>
                                  </div>
                                  <div class="icheck-primary d-inline" style="padding-right:5px;">
                                    <input type="radio" name="payment_mode" onchange="fillPaymentDescription(this.value)" {{ old('payment_mode')=='BA'? "checked": "" }} value="BA" id="PaymentModeBank">
                                    <label for="PaymentModeBank">Bank</label>
                                  </div>
                                  <div class="icheck-primary d-inline" style="padding-right:5px;">
                                    <input type="radio" name="payment_mode" onchange="fillPaymentDescription(this.value)" {{ old('payment_mode')=='CH'? "checked": "" }} value="CH" id="PaymentModeCheque">
                                    <label for="PaymentModeCheque">Cheque</label>
                                  </div>
                                  <div class="icheck-primary d-inline">
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
                                <input name="payment_description" type="text" class="form-control" placeholder="Payment description" value="{{ old('payment_description')}}">
                              @error('payment_description')<span class="text-danger">{{ $message }}</span>@enderror
                            </div>
                          </div>
                          <div class="col-md-4">
                            <div class="form-group">
                              <label for="InputPaymentTo">Paid By<span class="text-danger">*</span></label>
                                <div class="form-group clearfix" style="padding-top:8px;">
                                  <div class="icheck-primary d-inline" style="padding-right:5px;">
                                    <input type="radio" name="payment_by" value="CP" id="CustomerPay">
                                    <label for="CustomerPay">Customer Pay</label>
                                  </div>
                                  <div class="icheck-primary d-inline">
                                    <input type="radio" name="payment_by" value="CR" id="CompanyRefund">
                                    <label for="CompanyRefund">Company Refund</label>
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
                          <div class="col-md-12">
                            <div class="form-group">
                              <label for="InputRemark">Remark</label>
                                <textarea name="remark" class="form-control" rows="2" placeholder="Enter remark" id="InputRemark"> {{ old('remark') }} </textarea>
                                <input type="hidden" name="refrence" value="" id="InputRefrenceID">
                                <input type="hidden" name="refrence_type" value="" id="InputRefrenceType">
                              @error('remark')<span class="text-danger">{{ $message }}</span>@enderror                                
                            </div>
                          </div>
                        </div>
                      </fieldset>
                    </div>
                    <!-- /.card-body -->
                    <div class="card-footer">
                      <a href="{{ route('cabletv-history') }}" class="btn btn-warning"> <i class="fas fa-backward"></i> Back</a>
                      <a href="{{ route('cabletv-terminate-service') }}" class="btn btn-primary"> <i class="fas fa-redo"></i> Refresh</a>
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

  // On blur add total amount
  $(".fee").on("blur", function(){
    $(".fee").each(function(){
      if($(this).val() !=="")
        sum += parseFloat($(this).val());   
    });
    $("#InputTotalAmount").val(sum.toFixed(2));
  });

});

$(".updateTotal").on("change", function(){

  var refund = parseFloat($("#InputRefund").val());
  var due_amount = parseFloat($("#InputDueAmount").val());    
  var sum=(refund > due_amount) ? (refund - due_amount) : (due_amount - refund);
  (refund>due_amount) ? $('#CompanyRefund').prop( "checked", true ) : $('#CustomerPay').prop( "checked", true );

  $(".fee").each(function(){
    if($(this).val() !=="")
      sum += parseFloat($(this).val());   
  });
  $("#InputTotalAmount").val(sum.toFixed(2));
});

// On change service date
$(".datePicker").on("change", function(){
  var custID = $('#SelectCustomerName').val();
  getCustomerDetails(custID);
});

function getCustomerDetails(custID)
{
  var date= $("#InputServiceDate").val();
  var time= $("#InputServiceTime").val();
  $.ajax({
    url: '{{ route("cabletv-terminate-details") }}',
    type: 'post',
    data: { "_token":"{{ csrf_token() }}" , "custID": custID, "terminateDateTime": date+' '+time },
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
        $('#InputPlan').val(response.plan_name);
        $('#InputPlanID').val(response.plan_id);
        $('#InputMonthlyFee').val(response.monthly_fee);
        $('#InputDueAmount').val(response.due_amount);
        $('#InputRefund').val(response.refund);
        $('#InputLastEntryDate').val(response.last_entry_date);
        $('#InputRefrenceID').val(response.refrence_id);
        $('#InputRefrenceType').val(response.entry_type);
        (parseFloat(response.refund)>parseFloat(response.due_amount)) ? $('#InputTotalAmount').val((parseFloat(response.refund)-parseFloat(response.due_amount)).toFixed(2)) : $('#InputTotalAmount').val((parseFloat(response.due_amount)-parseFloat(response.refund)).toFixed(2));
        (parseFloat(response.refund)>parseFloat(response.due_amount)) ? $('#CompanyRefund').prop( "checked", true ) : $('#CustomerPay').prop( "checked", true );
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
        $('#InputPlan').val("");
        $('#InputPlanID').val("");
        $('#InputMonthlyFee').val("");
        $('#InputDueAmount').val("");
        $('#InputRefund').val("");
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