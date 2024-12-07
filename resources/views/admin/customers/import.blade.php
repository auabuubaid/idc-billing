@extends('admin.layout.master')

@section('content')

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6">
          <h1>Customers</h1>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{route('admin-dashboard')}}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{route('admin-customers')}}">Customers</a></li>
            <li class="breadcrumb-item active">Import</li>
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
              <h3 class="card-title">Import Customers</h3>
            </div>
            <!-- /.card-header -->
            <!-- form start -->
            <form role="form" action="{{ route('store-import-customers') }}" method="post" accept-charset="UTF-8" enctype="multipart/form-data">
              @csrf
              <div class="card-body">
                <div class="row">
                  <div class="col-md-4"></div>
                  <div class="col-md-4">
                    <div class="form-group">
                      <label for="File">File input<span class="text-danger">*</span></label>
                      <div class="input-group">
                        <div class="custom-file">
                          <input type="file" class="custom-file-input" name="import_file" id="ImportFile">
                          <label class="custom-file-label" for="ImportFile">Choose file</label>
                        </div>
                      </div>
                    </div>
                    @error('import_file')<span class="text-danger">{{ $message }}</span>@enderror
                  </div>
                </div>
              </div>
              <!-- /.card-body -->
              <div class="card-footer">
                <a href="{{route('admin-customers')}}" class="btn btn-warning"> <i class="fas fa-backward"></i> Back</a>
                <a href="{{route('admin-import-customers')}}" class="btn btn-info"> <i class="fas fa-redo-alt"></i> Refresh</a>
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
<!-- bs-custom-file-input -->
<script src="{{ asset('assets/plugins/bs-custom-file-input/bs-custom-file-input.min.js') }}"></script>
<!-- Page script -->
<script>
$(document).ready(function () {
  bsCustomFileInput.init();
});
</script>
@endsection