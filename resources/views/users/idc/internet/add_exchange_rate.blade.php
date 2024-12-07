@extends('users.layout.master')

@section('content')

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6">
          <h1>Exchange Rate</h1>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="{{route('dashboard')}}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{route('internet-subscribers')}}">Internet</a></li>
            <li class="breadcrumb-item"><a href="{{route('exchange-rate')}}">Exchange Rate</a></li>
            <li class="breadcrumb-item active">Add</li>
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
              <h3 class="card-title">Add Exchange Rate</h3>
            </div>
            <!-- /.card-header -->
            <form role="form" action="{{route('store-exchange-rate')}}" method="post">
              @csrf
              <div class="card-body">                  
                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="InputFromCurrency">From <span class="text-danger">*</span></label>
                        <input name="from_currency" type="text" class="form-control" id="InputFromCurrency" readonly value="USD">
                      @error('from_currency')<span class="text-danger">{{ $message }}</span>@enderror
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="InputToCurrency">To <span class="text-danger">*</span></label>
                        <input name="to_currency" type="text" class="form-control" id="InputToCurrency" readonly value="KHR">
                      @error('to_currency')<span class="text-danger">{{ $message }}</span>@enderror
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="InputRate">Rate <span class="text-danger">*</span></label>
                        <input name="rate" type="text" class="form-control" id="InputRate" placeholder="Enter exchange rate" value="{{ old('rate') }}">
                      @error('rate')<span class="text-danger">{{ $message }}</span>@enderror
                    </div>
                  </div>                                        
                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="InputExchangeMonthYear">Exchange Month Year<span class="text-danger">*</span></label>
                        <select name="exchange_month_year" class="form-control select2" id="InputExchangeMonthYear">
                          <option value="">--Select--</option>
                          @php $start = $month = strtotime(currentDate2()) @endphp  @php  $end = strtotime('2017-11-01') @endphp
                          @while($end < $month)
                            <option value="{{ date('Y-m-d', $month) }}"{{ old('exchange_month_year')==date('Y-m-d', $month) ?"selected":""}}>{{ date('M-Y', $month), PHP_EOL }}</option>
                            @php $month = strtotime("-1 month", $month) @endphp
                          @endwhile
                        </select>
                      @error('exchange_month_year')<span class="text-danger">{{ $message }}</span>@enderror
                    </div>
                  </div>
                </div>
              </div>
              <!-- /.card-body -->
              <div class="card-footer">
                <a href="{{route('exchange-rate')}}" class="btn btn-warning"> <i class="fas fa-backward"></i> Back</a>
                <a href="{{route('add-exchange-rate')}}" class="btn btn-info"> <i class="fas fa-redo-alt"></i> Refresh</a>
                <button type="submit" class="btn btn-primary loader"> <i class="fas fa-save"></i> Save</button>
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

  //Date picker
  $('.datePicker').daterangepicker({
    locale: {  format: 'DD-MM-YYYY' },
    autoclose: true,
    singleDatePicker: true,
  })
})
</script>
@endsection