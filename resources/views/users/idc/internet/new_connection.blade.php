@extends('users.layout.master')

@section('content')

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6">
          <h1>New Connection</h1>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{route('dashboard')}}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{route('internet-subscribers')}}">Internet</a></li>
            <li class="breadcrumb-item active">New Connection</li>
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
              <h3 class="card-title">New Connection</h3>
            </div>
            <!-- /.card-header -->
            <form role="form" action="{{route('store-internet-connection')}}" method="post">
              @csrf
              <div class="card-body">
                <fieldset style="border: 1px solid #ced4da; padding:0px 15px;">
                  <legend>Date & Time</legend>
                  <div class="row">
                    <div class="col-md-4">
                      <div class="form-group">
                        <label for="InputInstallationDate">Installation Date<span class="text-danger">*</span></label>
                          <div class="input-group">
                            <div class="input-group">
                              <div class="input-group-prepend">
                                <span class="input-group-text"><i class="far fa-calendar-alt"></i></span>
                              </div>
                              <input type="text" name="installation_date" placeholder="dd-mm-yyyy" value="{{ old('installation_date') }}" class="form-control pull-right datePicker">
                            </div>
                          </div>
                        @error('installation_date')<span class="text-danger">{{ $message }}</span>@enderror
                      </div>
                    </div>
                    <div class="col-md-4">
                      <div class="bootstrap-timepicker">
                        <div class="form-group">
                          <label for="InputInstallationTime">Time<span class="text-danger">*</span></label>
                            <div class="input-group date" id="timepicker" data-target-input="nearest">
                              <input type="text" name="installation_time" value="{{ empty(old('installation_time')) ? currentTime() : old('installation_time') }}" class="form-control datetimepicker-input" data-target="#timepicker"/>
                              <div class="input-group-append" data-target="#timepicker" data-toggle="datetimepicker">
                                <div class="input-group-text"><i class="far fa-clock"></i></div>
                              </div>
                            </div>
                          @error('installation_time')<span class="text-danger">{{ $message }}</span>@enderror
                        </div>
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
                                <option value="{{ $cval->id }}" {{ old('customer_name')==$cval->id ?"selected":""}}> @if($cval->type=='P') {{ $cval->name }} @else {{ $cval->name.' - ('.$cval->shop_name.')' }} @endif | {{ buildingLocationAndUnitNumberByUnitID($cval->address_id) }}</option>
                              @endforeach
                            @endif
                          </select>
                        @error('customer_name')<span class="text-danger">{{ $message }}</span>@enderror
                      </div>
                    </div>
                    <div class="col-md-1">
                      <div class="form-group" style="padding-top:37px">
                        <a href="{{route('add-customer')}}" class="text-primary" title="Add Customer"><i class="fas fa-user-plus"></i></a>
                      </div>
                    </div>
                    <div class="col-md-3" style="padding-top:7px">
                      <div class="form-group">
                        <label for="InputAgreementPeriod">Application Type</label>
                        <div class="form-group clearfix">
                          <div class="icheck-primary d-inline" style="padding-right:40px;">
                            <input type="radio" name="customer_type" disabled value="P" {{ old('customer_type')=="P" ? "checked" : ""}} id="CustomerTypePersonal">
                            <label for="CustomerTypePersonal">Personal</label>
                          </div>
                          <div class="icheck-primary d-inline">
                            <input type="radio" name="customer_type" disabled value="S" {{ old('customer_type')=="S" ? "checked" : ""}} id="CustomerTypeShop">
                            <label for="CustomerTypeShop">Shop</label>
                          </div>
                        </div>
                      </div>
                    </div>
                    <div class="col-md-4">
                      <div class="form-group">
                        <label for="InputCustomerAddress">Customer Address</label>
                        <input name="customer_address" type="text" class="form-control" id="InputCustomerAddress" readonly placeholder="Customer address" value="{{ old('customer_address') }}">
                        <input name="address_id" id="InputCustomerAddressID" type="hidden" value="">
                      </div>
                    </div>
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
                <fieldset style="border: 1px solid #ced4da; padding:0px 15px;">
                  <legend>Plan Details</legend>
                  <div class="row">
                    <div class="col-md-4">
                      <div class="form-group">
                        <label for="SelectInternetPlan">Internet Plan<span class="text-danger">*</span></label>
                          <select name="internet_plan" class="form-control" onchange="internetPlanDetails(this.value)" id="SelectInternetPlan">
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
                    <div class="col-md-3">
                      <div class="form-group">
                        <label for="InputSpeed">Speed</label>
                          <input name="speed" type="text" class="form-control" id="InputSpeed" readonly placeholder="Speed" value="{{ old('speed') }}">
                        @error('speed')<span class="text-danger">{{ $message }}</span>@enderror
                      </div>
                    </div>
                    <div class="col-md-1">
                      <div class="form-group" style="padding-top:37px">
                        <label for="SpeedUnit">Mbps</label>
                      </div>
                    </div>
                    <div class="col-md-3">
                      <div class="form-group">
                        <label for="InputUploadSpeed">Upload Speed</label>
                          <input name="upload_speed" type="text" class="form-control" id="InputUploadSpeed" readonly placeholder="Upload speed" value="{{ old('upload_speed') }}">
                        @error('upload_speed')<span class="text-danger">{{ $message }}</span>@enderror
                      </div>
                    </div>
                    <div class="col-md-1">
                      <div class="form-group" style="padding-top:37px">
                        <label for="UploadSpeedUnit">Mbps</label>
                      </div>
                    </div>
                    <div class="col-md-3">
                      <div class="form-group">
                        <label for="InputMonthlyFee">Monthly Fee<span class="text-danger">*</span></label>
                          <div class="input-group">
                            <div class="input-group-prepend">
                              <span class="input-group-text"><i class="fas fa-dollar-sign"></i></span>
                            </div>
                            <input name="monthly_fee" type="text" class="form-control" id="InputIMonthlyFee" placeholder="Enter monthly fee" value="{{ old('monthly_fee') }}">
                          </div>
                        @error('monthly_fee')<span class="text-danger">{{ $message }}</span>@enderror
                      </div>
                    </div>                      
                  </div>
                </fieldset>
                <br/> 
                <fieldset style="border: 1px solid #ced4da; padding:0px 15px;">
                  <legend>Payment Details</legend>
                  <div class="row">
                    <div class="col-md-4">
                      <div class="form-group">
                        <label for="InputInstallationFee">Installation Fee<span class="text-danger">*</span></label>
                          <div class="input-group">
                            <div class="input-group-prepend">
                              <span class="input-group-text"><i class="fas fa-dollar-sign"></i></span>
                            </div>
                            <input name="installation_fee" type="text" class="form-control fee" id="InputInstallationFee" placeholder="Enter installation fee" value="{{ old('installation_fee') }}">
                          </div>
                        @error('installation_fee')<span class="text-danger">{{ $message }}</span>@enderror
                      </div>
                    </div>
                    <div class="col-md-4">
                      <div class="form-group">
                        <label for="InputDepositFee">Deposit Fee<span class="text-danger">*</span></label>
                          <div class="input-group">
                            <div class="input-group-prepend">
                              <span class="input-group-text"><i class="fas fa-dollar-sign"></i></span>
                            </div>
                            <input name="deposit_fee" type="text" class="form-control fee" id="InputDepositFee" placeholder="Enter deposit fee" value="{{ old('deposit_fee') }}">
                          </div>
                        @error('deposit_fee')<span class="text-danger">{{ $message }}</span>@enderror
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
                    <div class="col-md-3">
                      <div class="form-group">
                        <label for="InputTotalAmount">Total Amount</label>
                        <div class="input-group">
                          <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-dollar-sign"></i></span>
                          </div>
                          <input name="total_amount" type="text" readonly class="form-control" id="InputTotalAmount" value="{{ old('total_amount') }}">
                        </div>
                      </div>
                    </div>
                    <div class="col-md-5">
                      <div class="form-group">
                        <label for="InputPaymentMode">Payment Mode<span class="text-danger">*</span></label>
                          <div class="form-group clearfix" style="padding-top:8px;">
                            <div class="icheck-primary d-inline" style="padding-right:20px;">
                              <input type="radio" onchange="fillPaymentDescription(this.value)" name="payment_mode" {{ old('payment_mode')=='CA'? "checked": "" }}  value="CA" id="PaymentModeCash">
                              <label for="PaymentModeCash">Cash</label>
                            </div>
                            <div class="icheck-primary d-inline" style="padding-right:20px;">
                              <input type="radio" onchange="fillPaymentDescription(this.value)" name="payment_mode" {{ old('payment_mode')=='BA'? "checked": "" }} value="BA" id="PaymentModeBank">
                              <label for="PaymentModeBank">Bank</label>
                            </div>
                            <div class="icheck-primary d-inline" style="padding-right:20px;">
                              <input type="radio" onchange="fillPaymentDescription(this.value)" name="payment_mode" {{ old('payment_mode')=='CH'? "checked": "" }} value="CH" id="PaymentModeCheque">
                              <label for="PaymentModeCheque">Cheque</label>
                            </div>
                            <div class="icheck-primary d-inline">
                              <input type="radio" onchange="fillPaymentDescription(this.value)" name="payment_mode" {{ old('payment_mode')=='OT'? "checked": "" }} value="OT" id="PaymentModeOther">
                              <label for="PaymentModeOther">Other</label>
                            </div>
                          </div>
                        @error('payment_mode')<span class="text-danger">{{ $message }}</span>@enderror
                      </div>
                    </div>
                    <div class="col-md-4">
                      <div class="form-group" id="InputPaymentDescription">
                        <label for="InputPaymentDescription">&nbsp;</label>
                          <input name="payment_description" type="text" class="form-control" placeholder="Payment description" value="{{ old('payment_description') }}">
                        @error('payment_description')<span class="text-danger">{{ $message }}</span>@enderror
                      </div>
                    </div>
                  <div>
                </fieldset>
                <br/>
                <div class="row">
                  <div class="col-md-12">
                    <div class="form-group">
                      <label for="InputRemark">Remark</label>
                        <textarea name="remark" class="form-control" rows="2" placeholder="Enter remark" id="InputRemark"> {{ old('remark') }} </textarea>
                      @error('remark')<span class="text-danger">{{ $message }}</span>@enderror
                    </div>
                  </div>
                </div>
              </div>
              <!-- /.card-body -->
              <div class="card-footer">
                <a href="{{route('internet-subscribers')}}" class="btn btn-warning"> <i class="fas fa-backward"></i> Back</a>
                <a href="{{route('new-internet-connection')}}" class="btn btn-info"> <i class="fas fa-redo-alt"></i> Refresh</a>
                <button type="submit" class="btn btn-success"> <i class="fas fa-save"></i> Save</button>
              </div>
            </form>
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

  var custID= $("select[name='customer_name']").val();
  getCustomerDetails(custID);
  
  var planID= $("select[name='internet_plan']").val();
  internetPlanDetails(planID);
  
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
    url: '{{ route("get-customer-details") }}',
    type: 'post',
    data: { "_token":"{{ csrf_token() }}" , "custID": custID },
    dataType: 'json',
    success: function(response){
    //alert(response);
      if(response != null){
        if(response.type == "P"){
            $('#CustomerTypePersonal').prop( "checked", true );
            $('#CustomerTypeShop').prop( "checked", false );
        }else{
            $('#CustomerTypePersonal').prop( "checked", false );
            $('#CustomerTypeShop').prop( "checked", true );
        }
        $('#InputCustomerEmail').val(response.email);
        $('#InputCustomerMobile').val(response.mobile);
        $('#InputCustomerAddress').val(response.full_address);
        $('#InputCustomerAddressID').val(response.address_id);
        $('#InputShopName').val(response.shop_name);
        $('#InputShopEmail').val(response.shop_email);
        $('#InputShopMobile').val(response.shop_mobile);
        $('#InputVATNumber').val(response.vat_no);
        $('#InputCustomerCountry').val(response.country);
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
        $('#InputCustomerCountry').val("");
      }
    }
  });
}

function internetPlanDetails(planID) 
{  
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
        $('#InputDepositFee').val(response.deposit_fee);
        $('#InputInstallationFee').val(response.installation_fee);
        $('#InputIMonthlyFee').val(response.monthly_fee);
        var sum=0;
        $(".fee").each(function(){
            if($(this).val() !=="")
              sum += parseFloat($(this).val());   
        });
        
        $("#InputTotalAmount").val(sum.toFixed(2));
      }else{
        $('#InputPlanName').val("");
        $('#InputSpeed').val("");
        $('#InputUploadSpeed').val("");
        $('#InputDepositFee').val("");
        $('#InputInstallationFee').val("");
        $('#InputIMonthlyFee').val("");
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