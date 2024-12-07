@extends('users.layout.master')

@section('content')

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6">
          <h1>CableTV</h1>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{route('dashboard')}}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{route('cabletv-monthly-invoice')}}">Monthly Invoice</a></li>
            <li class="breadcrumb-item active">Edit</li>
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
              <h3 class="card-title">Edit Monthly Invoice</h3>
            </div>
            <!-- /.card-header -->
            <form role="form" action="{{route('update-cabletv-monthly-invoice')}}" method="post">
              @csrf
              <div class="card-body">
                <fieldset style="border: 1px solid #ced4da; padding:0px 15px;">
                  <legend>Date</legend>
                  <div class="row">
                    <div class="col-md-4">
                      <div class="form-group">
                        <label for="InputPaymentDate">Payment Date<span class="text-danger">*</span></label>
                          <div class="input-group">
                            <div class="input-group">
                              <div class="input-group-prepend">
                                <span class="input-group-text"><i class="far fa-calendar-alt"></i></span>
                              </div>
                              <input type="text" name="paid_date" placeholder="dd-mm-yyyy" value="{{ empty($ctvhistoryArr->paid_date) ? old('paid_date') : datePickerFormat($ctvhistoryArr->paid_date) }}" class="form-control pull-right datePicker">
                            </div>
                          </div>
                        @error('paid_date')<span class="text-danger">{{ $message }}</span>@enderror
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
                        <label for="SelectCustomerName">Customer Name</label>
                        <input name="customer_name" type="text" class="form-control" id="SelectCustomerName" disabled placeholder="Customer name" value="{{ customerNameByID($ctvhistoryArr->customer_id) }}">
                      </div>
                    </div>
                    <div class="col-md-4">
                      <div class="form-group">
                        <label for="InputCustomerAddress">Customer Address</label>
                        <input name="customer_address" type="text" class="form-control" id="InputCustomerAddress" disabled placeholder="Customer address" value="{{ unitAddressByUnitID($ctvhistoryArr->address_id) }}">
                      </div>
                    </div>
                    <div class="col-md-4">
                      <div class="form-group">
                        <label for="InputInvoiceDate">Invoice Date</label>
                        <div class="input-group">
                          <div class="input-group">
                            <div class="input-group-prepend">
                              <span class="input-group-text"><i class="far fa-calendar-alt"></i></span>
                            </div>
                            <input type="text" name="invoice_date" value="{{ pdfDateFormat($ctvhistoryArr->monthly_invoice_date) }}" disabled class="form-control pull-right">
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </fieldset>                
                <br/> 
                <fieldset style="border: 1px solid #ced4da; padding:0px 15px;">
                  <legend>Payment Details</legend>
                  <div class="row">
                    <div class="col-md-1">
                      <div class="form-group">
                        <label for="InputMonthlyFee">Monthly Fee<span class="text-danger">*</span></label>
                          <div class="input-group">
                            <div class="input-group-prepend">
                              <span class="input-group-text"><i class="fas fa-dollar-sign"></i></span>
                            </div>
                            <input name="monthly_fee" type="text" class="form-control" id="InputMonthlyFee" disabled placeholder="Monthly fee" value="{{ amountFormat2($ctvhistoryArr->monthly_fee) }}">
                          </div>
                        @error('monthly_fee')<span class="text-danger">{{ $message }}</span>@enderror
                      </div>
                    </div>                       
                    <div class="col-md-1">
                      <div class="form-group">
                        <label for="InputTotalAmount">Amount</label>
                        <div class="input-group">
                          <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-dollar-sign"></i></span>
                          </div>
                          <input name="total_amount" type="text" readonly class="form-control" id="InputTotalAmount" value="{{ amountFormat2($ctvhistoryArr->total_amount) }}">
                        </div>
                      </div>
                    </div>
                    <div class="col-md-2">
                      <div class="form-group">
                        <label for="InputPaid">Paid<span class="text-danger">*</span></label>
                          <div class="form-group clearfix" style="padding-top:8px;">
                            <div class="icheck-primary d-inline" style="padding-right:5px;">
                              <input type="radio" name="paid" {{ $ctvhistoryArr->paid=='Y' ? "checked": "" }}  value="Y" id="PaidYes">
                              <label for="PaidYes">Yes</label>
                            </div>
                            <div class="icheck-primary d-inline">
                              <input type="radio" name="paid" {{ $ctvhistoryArr->paid=='N' ? "checked": "" }} value="N" id="PaidNo">
                              <label for="PaidNo">No</label>
                            </div>
                          </div>
                        @error('paid')<span class="text-danger">{{ $message }}</span> @enderror
                      </div>
                    </div>
                    <div class="col-md-5" style="max-width:400px">
                      <div class="form-group">
                        <label for="InputPaymentMode">Payment Mode<span class="text-danger">*</span></label>
                          <div class="form-group clearfix" style="padding-top:8px;">
                            <div class="icheck-primary d-inline" style="padding-right:20px;">
                              <input type="radio" onchange="fillPaymentDescription(this.value)" name="payment_mode" {{ $ctvhistoryArr->payment_mode=='CA'? "checked": "" }}  value="CA" id="PaymentModeCash">
                              <label for="PaymentModeCash">Cash</label>
                            </div>
                            <div class="icheck-primary d-inline" style="padding-right:20px;">
                              <input type="radio" onchange="fillPaymentDescription(this.value)" name="payment_mode" {{ $ctvhistoryArr->payment_mode=='BA'? "checked": "" }} value="BA" id="PaymentModeBank">
                              <label for="PaymentModeBank">Bank</label>
                            </div>
                            <div class="icheck-primary d-inline" style="padding-right:20px;">
                              <input type="radio" onchange="fillPaymentDescription(this.value)" name="payment_mode" {{ $ctvhistoryArr->payment_mode=='CH'? "checked": "" }} value="CH" id="PaymentModeCheque">
                              <label for="PaymentModeCheque">Cheque</label>
                            </div>
                            <div class="icheck-primary d-inline">
                              <input type="radio" onchange="fillPaymentDescription(this.value)" name="payment_mode" {{ $ctvhistoryArr->payment_mode=='OT'? "checked": "" }} value="OT" id="PaymentModeOther">
                              <label for="PaymentModeOther">Other</label>
                            </div>
                          </div>
                        @error('payment_mode')<span class="text-danger">{{ $message }}</span>@enderror
                      </div>
                    </div>
                    <div class="col-md-4">
                      <div class="form-group" id="InputPaymentDescription">
                        <label for="InputPaymentDescription">&nbsp;</label>
                          <input name="payment_description" type="text" class="form-control" placeholder="Payment description" value="{{ $ctvhistoryArr->payment_description }}">
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
                        <textarea name="remark" class="form-control" rows="2" placeholder="Enter remark" id="InputRemark">{{ $ctvhistoryArr->remark }}</textarea>
                        <input name="refrenceID" type="hidden" value="{{ $ctvhistoryArr->id }}">
                      @error('remark')<span class="text-danger">{{ $message }}</span>@enderror
                    </div>
                  </div>
                </div>
              </div>              
              <!-- /.card-body -->
              <div class="card-footer">
                <a href="{{route('cabletv-monthly-invoice')}}" class="btn btn-warning"> <i class="fas fa-backward"></i> Back</a>
                <a href="{{route('edit-cabletv-monthly-invoice',['id'=>$ctvhistoryArr->id])}}" class="btn btn-info"> <i class="fas fa-redo-alt"></i> Refresh</a>
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