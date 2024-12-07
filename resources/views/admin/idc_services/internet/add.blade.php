@extends('admin.layout.master')

@section('content')

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6">
          <h1>Internet Plans</h1>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{route('admin-dashboard')}}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{route('internet-services')}}">Internet Plans</a></li>
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
              <h3 class="card-title">Add Internet Plan</h3>
            </div>
            <!-- /.card-header -->
            <form role="form" action="{{ route('store-internet-service') }}" method="post">
              @csrf
              <div class="card-body">
                <div class="row">
                  <div class="col-md-3">
                    <div class="form-group">
                      <label for="InputServiceName">Service Name<span class="text-danger">*</span></label>
                      <input name="service_name" type="text" class="form-control" id="InputServiceName" placeholder="Enter service name" value="{{ old('service_name')}}">
                      @error('service_name')<span class="text-danger">{{ $message }}</span>@enderror
                    </div>
                  </div>
                  <div class="col-md-3">
                    <div class="form-group">
                      <label for="InputPlanName">Plan Name<span class="text-danger">*</span></label>
                      <input name="plan_name" type="text" class="form-control" id="InputPlanName" placeholder="Enter plan name" value="{{ old('plan_name')}}">
                      @error('plan_name')<span class="text-danger">{{ $message }}</span>@enderror
                    </div>
                  </div>
                  <div class="col-md-2">
                    <div class="form-group">
                      <label for="InputSpeed">Speed<span class="text-danger">*</span></label>
                      <input name="speed" type="text" class="form-control" id="InputSpeed" placeholder="Enter speed" value="{{ old('speed') }}">
                      @error('speed')<span class="text-danger">{{ $message }}</span>@enderror
                    </div>
                  </div>
                  <div class="col-md-1">
                    <div class="form-group">
                      <label for="SelectSpeedUnit">&nbsp;</label>
                      <select name="speed_unit" class="form-control" id="SelectSpeedUnit">
                        <option value="">--Select--</option>
                        <option value="Bps" {{ old('speed_unit')=='Bps' ?"selected":""}}>Bps</option>
                        <option value="Kbps" {{ old('speed_unit')=='Kbps' ?"selected":""}}>Kbps</option>
                        <option value="Mbps" {{ old('speed_unit')=='Mbps' ?"selected":""}}>Mbps</option>
                        <option value="Gbps" {{ old('speed_unit')=='Gbps' ?"selected":""}}>Gbps</option>
                        <option value="Tbps" {{ old('speed_unit')=='Tbps' ?"selected":""}}>Tbps</option>
                      </select>
                      @error('speed_unit')<span class="text-danger">{{ $message }}</span>@enderror
                    </div>
                  </div>
                  <div class="col-md-2">
                    <div class="form-group">
                      <label for="InputUploadSpeed">Upload Speed<span class="text-danger">*</span></label>
                      <input name="upload_speed" type="text" class="form-control" id="InputUploadSpeed" placeholder="Enter upload speed" value="{{ old('upload_speed') }}">
                      @error('upload_speed')<span class="text-danger">{{ $message }}</span>@enderror
                    </div>
                  </div>
                  <div class="col-md-1">
                    <div class="form-group">
                      <label for="SelectUploadSpeedUnit">&nbsp;</label>
                      <select name="upload_speed_unit" class="form-control" id="SelectUploadSpeedUnit">
                        <option value="">--Select--</option>
                        <option value="Bps" {{ old('upload_speed_unit')=='Bps' ?"selected":""}}>Bps</option>
                        <option value="Kbps" {{ old('upload_speed_unit')=='Kbps' ?"selected":""}}>Kbps</option>
                        <option value="Mbps" {{ old('upload_speed_unit')=='Mbps' ?"selected":""}}>Mbps</option>
                        <option value="Gbps" {{ old('upload_speed_unit')=='Gbps' ?"selected":""}}>Gbps</option>
                        <option value="Tbps" {{ old('upload_speed_unit')=='Tbps' ?"selected":""}}>Tbps</option>
                      </select>
                      @error('upload_speed_unit')<span class="text-danger">{{ $message }}</span>@enderror
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="form-group">
                      <label for="InputDepositFee">Deposit Fee<span class="text-danger">*</span></label>
                      <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-dollar-sign"></i></span>
                        </div>
                        <input name="deposit_fee" type="text" class="form-control" id="InputDepositFee" placeholder="Enter deposit fee" value="{{ old('deposit_fee')}}">
                      </div>
                      @error('deposit_fee')<span class="text-danger">{{ $message }}</span>@enderror
                    </div>
                  </div>                  
                  <div class="col-md-4">
                    <div class="form-group">
                      <label for="InputMonthlyFee">Monthly Fee<span class="text-danger">*</span></label>
                      <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-dollar-sign"></i></span>
                        </div>
                        <input name="monthly_fee" type="text" class="form-control" id="InputMonthlyFee" placeholder="Enter monthly fee" value="{{ old('monthly_fee')}}">
                      </div>
                      @error('monthly_fee')<span class="text-danger">{{ $message }}</span>@enderror
                    </div>
                  </div>                  
                  <div class="col-md-4">
                    <div class="form-group">
                      <label for="InputInstallationFee">Installation Fee<span class="text-danger">*</span></label>
                      <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-dollar-sign"></i></span>
                        </div>
                        <input name="installation_fee" type="text" class="form-control" id="InputInstallationFee" placeholder="Enter installation fee" value="{{ old('installation_fee')}}">
                      </div>
                      @error('installation_fee')<span class="text-danger">{{ $message }}</span>@enderror
                    </div>
                  </div>                  
                </div>
                <div class="row">
                  <div class="col-md-4">
                    <div class="form-group">
                      <label for="InputVAT">VAT<span class="text-danger">*</span></label>
                      <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-percentage"></i></span>
                        </div>
                        <input name="vat" type="text" class="form-control" id="InputVAT" placeholder="Enter vat percentage (10)" value="{{ old('vat')}}">
                      </div>
                      @error('vat')<span class="text-danger">{{ $message }}</span>@enderror
                    </div>
                  </div>                  
                  <div class="col-md-4">
                    <div class="form-group">
                      <label for="SelectDataUsage">Data Usage<span class="text-danger">*</span></label>
                      <select name="data_usage" class="form-control" id="SelectDataUsage">
                        <option value="">--Select--</option>
                        <option value="U" {{ old('data_usage')=='U' ?"selected":""}}>Unlimited</option>
                        <option value="L" {{ old('data_usage')=='L' ?"selected":""}}>Limited</option>
                      </select>
                      @error('data_usage')<span class="text-danger">{{ $message }}</span>@enderror
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="form-group">
                      <label for="SelectStatus">Status<span class="text-danger">*</span></label>
                      <select name="status" class="form-control" id="SelectStatus">
                        <option value="A" {{ old('status')=='A' ?"selected":""}}>Active</option>
                        <option value="N" {{ old('status')=='N' ?"selected":""}}>Not Active</option>
                      </select>
                      @error('status')<span class="text-danger">{{ $message }}</span>@enderror
                    </div>
                  </div>
                </div>
              </div>
              <!-- /.card-body -->
              <div class="card-footer">
                <a href="{{route('internet-services')}}" class="btn btn-warning"> <i class="fas fa-backward"></i> Back</a>
                <a href="{{route('add-internet-service')}}" class="btn btn-info"> <i class="fas fa-redo-alt"></i> Refresh</a>
                <button type="submit" class="btn btn-primary"> <i class="fas fa-save"></i> Save</button>
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
<!-- Page script -->
<script>
$(function () {
  //Initialize Select2 Elements
  $('.select2').select2()
})
</script>
@endsection