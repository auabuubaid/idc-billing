@extends('users.layout.master')

@section('content')

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6">
          <h1>Change Location</h1>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('cabletv-subscribers') }}">Cable TV</a></li>
            <li class="breadcrumb-item active">Change Location</li>
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
                  <a class="nav-link active" id="custom-content-above-relocation-tab" data-toggle="pill" href="#custom-content-above-relocation" role="tab" aria-controls="custom-content-above-relocation" aria-selected="false"><i class="fas fa-house-damage"></i> Change Location</a>
                </li>                  
                <li class="nav-item">
                  <a class="nav-link" href="{{ route('cabletv-terminate-service') }}"><i class="fas fa-times-circle"></i> Terminate</a>
                </li>
                <li class="nav-item">
                  <a class="nav-link" href="{{ route('cabletv-reconnect-service') }}"><i class="fas fa-plug"></i> Reconnect</a>
                </li>
                <li class="nav-item">
                  <a class="nav-link" href="{{ route('cabletv-change-owner') }}"><i class="fas fa-user-check"></i> Change Owner</a>
                </li>
              </ul>
              <div class="tab-content" id="custom-content-above-tabContent">
                <div class="tab-pane fade show active" id="custom-content-above-relocation" role="tabpanel" aria-labelledby="custom-content-above-profile-tab">
                  <form role="form" action="{{route('update-cabletv-location')}}" method="post">
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
                                  <input type="text" name="service_time" value="{{ currentTime() }}" class="form-control datetimepicker-input" data-target="#timepicker"/>
                                  <div class="input-group-append" data-target="#timepicker" data-toggle="datetimepicker">
                                    <div class="input-group-text"><i class="far fa-clock"></i>
                                  </div>
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
                                  <input type="radio" name="customer_type" value="P" disabled {{ old('customer_type')=="P" ? "checked" : ""}} id="CustomerTypePersonal">
                                  <label for="CustomerTypePersonal">Personal</label>
                                </div>
                                <div class="icheck-primary d-inline">
                                  <input type="radio" name="customer_type" value="S" disabled {{ old('customer_type')=="S" ? "checked" : ""}} id="CustomerTypeShop">
                                  <label for="CustomerTypeShop">Company</label>
                                </div>
                              </div>
                            </div>
                          </div>
                          <div class="col-md-4"></div>
                          <div class="col-md-4">
                            <div class="form-group">
                              <label for="InputCustomerEmail">Customer Email</label>
                              <input name="customer_email" type="text" class="form-control" id="InputCustomerEmail" readonly placeholder="Customer email" value="{{ old('customer_email') }}">
                            </div>
                          </div>
                          <div class="col-md-4">
                            <div class="form-group">
                              <label for="InputCustomerMobile">Customer Mobile</label>
                              <input name="customer_mobile" type="text" class="form-control" id="InputCustomerMobile" readonly placeholder="Customer mobile" value="{{ old('customer_mobile') }}">
                            </div>
                          </div>                                  
                          <div class="col-md-4"></div>
                          <div class="col-md-4">
                            <div class="form-group">
                              <label for="InputShopName">Shop Name</label>
                              <input name="shop_name" type="text" class="form-control" id="InputShopName" readonly placeholder="Shop name" value="{{ old('shop_name') }}">
                            </div>
                          </div>
                          <div class="col-md-4">
                            <div class="form-group">
                              <label for="InputShopEmail">Shop Email</label>
                              <input name="shop_email" type="text" class="form-control" id="InputShopEmail" readonly placeholder="Shop email" value="{{ old('shop_email') }}">
                            </div>
                          </div>
                          <div class="col-md-2">
                            <div class="form-group">
                              <label for="InputShopMobile">Shop Mobile</label>
                              <input name="shop_mobile" type="text" class="form-control" id="InputShopMobile" readonly placeholder="Shop mobile" value="{{ old('shop_mobile') }}">
                            </div>
                          </div>
                          <div class="col-md-2">
                            <div class="form-group">
                              <label for="InputVATNumber">VAT Number</label>
                              <input name="shop_vat_no" type="text" class="form-control" id="InputVATNumber" readonly placeholder="Shop vat no" value="{{ old('shop_vat_no') }}">
                            </div>
                          </div>
                        </div>
                      </fieldset> 
                      <br/>
                      <div class="row">
                        <div class="col-md-6">
                          <fieldset style="border: 1px solid #ced4da; padding:0px 15px;">
                            <legend>Current Location</legend>
                            <div class="row">
                              <div class="col-md-7">
                                <div class="form-group">
                                  <label for="InputCustomerAddress">Address</label>
                                  <input name="customer_address" type="text" class="form-control" id="InputCustomerAddress" readonly placeholder="Current address" value="{{ old('customer_address') }}">
                                  <input name="previous_address_id" type="hidden" id="PreviousAddressID">
                                </div>
                              </div>
                              <div class="col-md-5">
                                <div class="form-group">
                                  <label for="InputCustomerMobile">Mobile</label>
                                  <input name="current_mobile" type="text" class="form-control" id="InputCurrentMobile" readonly placeholder="Current mobile" value="{{ old('current_mobile') }}">
                                </div>
                                <input name="plan_id" type="hidden" id="InputPlan">
                              </div>
                            </div>
                          </fieldset>
                        </div>
                        <div class="col-md-6">
                          <fieldset style="border: 1px solid #ced4da; padding:0px 15px;">
                            <legend>Relocation</legend>
                            <div class="row">
                              <div class="col-md-7">
                                <div class="form-group">
                                  <label for="SelectRelocateAddress">Address<span class="text-danger">*</span></label>
                                  <select name="relocate_address" class="form-control selectpicker select2" id="SelectRelocateAddress">
                                    <option value="">--Select--</option>
                                    @if(count($unitsAddressArr)>0) 
                                      @foreach($unitsAddressArr as $lkey=>$lval)
                                        <optgroup label="{!! '&#xf1ad;' !!} - {{ buildingNameByLocationID($lkey) }}">
                                          @foreach($lval as $uval)
                                            <option value="{{ $uval->id }}" {{ old('relocate_address')==$uval->id ?"selected":"" }}>{{ buildingLocationAndUnitNumberByUnitID($uval->id) }}</option>
                                          @endforeach
                                        </optgroup>
                                      @endforeach
                                    @endif
                                  </select>
                                  @error('relocate_address')<span class="text-danger">{{ $message }}</span>@enderror
                                </div>
                              </div>
                              <div class="col-md-5">
                                <div class="form-group">
                                  <label for="InputRelocateCustomerMobile">Mobile<span class="text-danger">*</span></label>
                                  <input name="relocate_customer_mobile" type="text" class="form-control" id="InputRelocateCustomerMobile" data-inputmask='"mask": "999-999-9999"' data-mask placeholder="Enter mobile" value="{{ old('relocate_customer_mobile') }}">
                                  @error('relocate_customer_mobile')<span class="text-danger">{{ $message }}</span>@enderror
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
                              <label for="InputReinstallationFee">Re-Installation Fee<span class="text-danger">*</span></label>
                              <div class="input-group">
                                <div class="input-group-prepend">
                                  <span class="input-group-text"><i class="fas fa-dollar-sign"></i></span>
                                </div>
                                <input name="reinstallation_fee" type="text" class="form-control fee" id="InputReinstallationFee" placeholder="Enter re-installation fee" value="{{ old('reinstallation_fee')}}">
                              </div>
                              @error('reinstallation_fee')<span class="text-danger">{{ $message }}</span>@enderror
                            </div>
                          </div>
                          <div class="col-md-4">
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
                          <div class="col-md-4">
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
                              <label for="InputPaidBy">Payment Mode<span class="text-danger">*</span></label>
                              <div class="form-group clearfix" style="padding-top:8px">
                                <div class="icheck-primary d-inline" style="padding-right:15px;">
                                  <input type="radio" onchange="fillPaymentDescription(this.value)" name="payment_mode" {{ old('payment_mode')=='CA'? "checked": "" }} value="CA" id="radioPaidByCash">
                                  <label for="radioPaidByCash">Cash</label>
                                </div>
                                <div class="icheck-primary d-inline" style="padding-right:15px;">
                                  <input type="radio" onchange="fillPaymentDescription(this.value)" name="payment_mode" {{ old('payment_mode')=='BA'? "checked": "" }} value="BA" id="radioPaidByBank">
                                  <label for="radioPaidByBank">Bank</label>
                                </div>
                                <div class="icheck-primary d-inline" style="padding-right:15px;">
                                  <input type="radio" onchange="fillPaymentDescription(this.value)" name="payment_mode" {{ old('payment_mode')=='CH'? "checked": "" }} value="CH" id="radioPaidByCheque">
                                  <label for="radioPaidByCheque">Cheque</label>
                                </div>
                                <div class="icheck-primary d-inline" style="padding-right:15px;">
                                  <input type="radio" onchange="fillPaymentDescription(this.value)" name="payment_mode" {{ old('payment_mode')=='OT'? "checked": "" }} value="OT" id="radioPaidByOther">
                                  <label for="radioPaidByOther">Other</label>
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
                              <label for="InputPaidBy">Paid By<span class="text-danger">*</span></label>
                              <div class="form-group clearfix"> 
                                <div class="icheck-primary d-inline" style="padding-right:5px;">
                                  <input type="radio" name="payment_by" {{ old('payment_by')=='CP'? "checked": "" }} value="CP" id="radioCustomerPay">
                                  <label for="radioCustomerPay">Customer Pay</label>
                                </div>
                                <div class="icheck-primary d-inline">
                                  <input type="radio" name="payment_by" value="CR" disabled id="radioCompanyRefund">
                                  <label for="radioCompanyRefund">Company Refund</label>
                                </div>
                              </div>
                              @error('payment_by')<span class="text-danger">{{ $message }}</span>@enderror
                            </div>
                          </div>
                          <div class="col-md-3">
                            <div class="form-group">
                              <label for="InputPaidBy">Paid<span class="text-danger">*</span></label>
                              <div class="form-group clearfix" style="padding-top:8px;">
                                <div class="icheck-primary d-inline" style="padding-right:15px;">
                                  <input type="radio" name="paid" {{ old('paid')=='Y'? "checked": "" }}  value="Y" id="PaidYes">
                                  <label for="PaidYes">Yes</label>
                                </div>
                                <div class="icheck-primary d-inline" style="padding-right:15px;">
                                  <input type="radio" name="paid" {{ old('paid')=='N'? "checked": "" }} value="N" id="PaidNo">
                                  <label for="PaidNo">No</label>
                                </div>
                              </div>
                              @error('paid')<span class="text-danger">{{ $message }}</span>@enderror
                            </div>
                          </div>
                        </div>
                      </fieldset>
                      <br>
                      <div class="col-md-12">
                        <div class="form-group">
                          <label for="InputRemark">Remark</label>
                          <textarea name="remark" class="form-control" rows="2" placeholder="Enter remark" id="InputRemark"> </textarea>
                          @error('remark')<span class="text-danger">{{ $message }}</span>@enderror
                          <input type="hidden" name="monthly_fee" value="" id="InputMonthlyFee">
                          <input type="hidden" name="refrence" value="" id="InputRefrenceID">
                          <input type="hidden" name="refrence_type" value="" id="InputRefrenceType">
                        </div>
                      </div>  
                    </div>
                    <!-- /.card-body -->
                    <div class="card-footer">
                      <a href="{{ route('cabletv-history') }}" class="btn btn-warning"> <i class="fas fa-backward"></i> Back</a>
                      <a href="{{ route('cabletv-change-location') }}" class="btn btn-primary"> <i class="fas fa-redo"></i> Refresh</a>
                      <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Save</button>
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
  //Phone
  $('[data-mask]').inputmask()
  //Date picker
  $('.datePicker').daterangepicker({
    locale: {  format: 'DD-MM-YYYY' },
    autoclose: true,
    singleDatePicker: true,
  })
})

// On change service date
$(".datePicker").on("change", function(){
  var custID = $('#SelectCustomerName').val();
  getCustomerDetails(custID);
});

$( document ).ready(function() {

  var mode= $("input[name='payment_mode']:checked").val();
  fillPaymentDescription(mode);

  //Fill Customer Details on ready
  var custID = $('#SelectCustomerName').val();
  getCustomerDetails(custID);

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

function getCustomerDetails(custID)
{
  $.ajax({
    url: '{{ route("customer-with-cabletv-plan") }}',
    type: 'post',
    data: { "_token":"{{ csrf_token() }}" , "custID": custID },
    dataType: 'json',
    success: function(response){
      //alert(response);
      if(response != null){
          var sum=0;
          (response.type == "P") ? $('#CustomerTypePersonal').prop( "checked", true ): $('#CustomerTypeShop').prop( "checked", true );
          $('#InputCustomerEmail').val(response.email);
          $('#InputCustomerMobile').val(response.mobile);
          $('#InputShopName').val(response.shop_name);
          $('#InputShopEmail').val(response.shop_email);
          $('#InputShopMobile').val(response.shop_mobile);
          $('#InputVATNumber').val(response.vat_no);
          $('#InputPlan').val(response.plan_id);
          $('#InputCustomerAddress').val(response.full_address);
          $('#PreviousAddressID').val(response.address_id);
          (response.type=="P")?$('#InputCurrentMobile').val(response.mobile):$('#InputCurrentMobile').val(response.shop_mobile);
          $('#InputMonthlyFee').val(response.monthly_fee);
          $('#InputLastEntryDate').val(response.last_entry_date);
          $('#InputReinstallationFee').val(parseFloat("{{ env('CABLETV_CHANGE_LOCATION_FEE') }}"));
          $('#InputRefrenceID').val(response.refrence_id);
          $('#InputRefrenceType').val(response.entry_type);
          $(".fee").each(function(){
          if($(this).val() !=="")
            sum += parseFloat($(this).val());   
          });          
          $("#InputTotalAmount").val(sum.toFixed(2));
      }else{
          $('#CustomerTypePersonal').prop( "checked", false );
          $('#CustomerTypeShop').prop( "checked", false );
          $('#InputCustomerEmail').val("");
          $('#InputCustomerMobile').val("");
          $('#InputShopName').val("");
          $('#InputShopEmail').val("");
          $('#InputShopMobile').val("");
          $('#InputVATNumber').val("");
          $('#InputPlan').val("");
          $('#InputCustomerAddress').val("");
          $('#PreviousAddressID').val("");
          $('#InputCurrentMobile').val("");
          $('#InputMonthlyFee').val("");
          $('#InputReinstallationFee').val("");
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