@extends('admin.layout.master')

@section('content')

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6">
          <h1>CableTV Plans</h1>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{route('admin-dashboard')}}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{route('cabletv-services')}}">CableTV Plans</a></li>
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
              <h3 class="card-title">Edit CableTV Plan</h3>
            </div>
            <!-- /.card-header -->
            <form role="form" action="{{route('update-cabletv-service')}}" method="post">
              @csrf
              <div class="card-body">
                <div class="row">
                  <div class="col-md-4">
                    <div class="form-group">
                      <label for="InputPlanName">Plan Name<span class="text-danger">*</span></label>
                      <input name="plan_name" type="text" class="form-control" id="InputPlanName" placeholder="Enter plan name" value="{{ $cabletvServiceArr->plan_name }}">
                      @error('plan_name')<span class="text-danger">{{ $message }}</span>@enderror
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="form-group">
                      <label for="InputInstallationFee">Installation Fee<span class="text-danger">*</span></label>
                      <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-dollar-sign"></i></span>
                        </div>
                        <input name="installation_fee" type="text" class="form-control" id="InputInstallationFee" placeholder="Enter installation fee" value="{{ $cabletvServiceArr->installation_fee }}">
                      </div>
                      @error('installation_fee')<span class="text-danger">{{ $message }}</span>@enderror
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="form-group">
                      <label for="InputMonthlyFee">Monthly Fee<span class="text-danger">*</span></label>
                      <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-dollar-sign"></i></span>
                        </div>
                        <input name="monthly_fee" type="text" class="form-control" id="InputMonthlyFee" placeholder="Enter monthly fee" value="{{ $cabletvServiceArr->monthly_fee }}">
                      </div>
                      @error('monthly_fee')<span class="text-danger">{{ $message }}</span>@enderror
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="form-group">
                      <label for="InputPerTVFee">New TV Fee<span class="text-danger">*</span></label>
                      <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-dollar-sign"></i></span>
                        </div>
                        <input name="new_tv_fee" type="text" class="form-control" id="InputPerTVFee" placeholder="Enter new tv fee" value="{{ $cabletvServiceArr->per_tv_fee }}">
                      </div>
                      @error('new_tv_fee')<span class="text-danger">{{ $message }}</span>@enderror
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="form-group">
                      <label for="SelectStatus">Status<span class="text-danger">*</span></label>
                      <select name="status" class="form-control" id="SelectStatus">
                        <option value="A" {{ $cabletvServiceArr->status=='A' ?"selected":""}}>Active</option>
                        <option value="N" {{ $cabletvServiceArr->status=='N' ?"selected":""}}>Not Active</option>
                      </select>
                      @error('status')<span class="text-danger">{{ $message }}</span>@enderror
                    </div>
                  </div>
                </div>
              </div>
              <!-- /.card-body -->
              <div class="card-footer">
                <a href="{{route('cabletv-services')}}" class="btn btn-warning"> <i class="fas fa-backward"></i> Back</a>
                <a href="{{route('edit-cabletv-service',['id'=>$cabletvServiceArr->id])}}" class="btn btn-info"> <i class="fas fa-redo-alt"></i> Refresh</a>
                <input name="referenceID" type="hidden" value="{{ $cabletvServiceArr->id }}">
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
@endsection