@extends('admin.layout.master')

@section('content')

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6">
          <h1>Users</h1>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{route('admin-dashboard')}}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{route('users')}}">Users</a></li>
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
              <h3 class="card-title">Edit User</h3>
            </div>
            <!-- /.card-header -->
            <!-- form start -->
            <form role="form" action="{{ route('update-user') }}" method="post">
              @csrf
              <div class="card-body">
                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="InputUserName">Name<span class="text-danger">*</span></label>
                      <div class="input-group">
                        <div class="input-group-prepend">
                          <span class="input-group-text">
                            <i class="fas fa-user"></i>
                          </span>
                        </div>
                        <input name="user_name" type="text" class="form-control" id="InputUserName" placeholder="Enter user name" value="{{ $userArr->name }}">
                      </div>  
                      @error('user_name')<span class="text-danger">{{ $message }}</span>@enderror
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="InputUserEmail">Email</label>
                      <div class="input-group">
                        <div class="input-group-prepend">
                          <span class="input-group-text">
                            <i class="fas fa-envelope"></i>
                          </span>
                        </div>
                        <input name="user_email" type="text" class="form-control" id="InputUserEmail" readonly placeholder="Enter user email" value="{{ $userArr->email }}">
                      </div>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="SelectDepartment">Department<span class="text-danger">*</span></label>
                      <select name="department" class="custom-select" id="SelectDepartment">
                        <option value="">--Select--</option>
                        <option value="IDC" {{ $userArr->user_type=="IDC"?"selected":""}}>{{ userTypeAbbreviationToFullName('IDC')['full'] }}</option>
                      </select>
                      @error('department')<span class="text-danger">{{ $message }}</span>@enderror
                    </div>
                  </div>
                  <div class="col-md-12">
                    <div class="form-group clearfix">
                      <label for="InputPermission">Permission<span class="text-danger">*</span></label>
                      <div class="input-group">
                        <div class="icheck-success d-inline" style="padding-right:100px;">
                          <input name="read_permission" type="checkbox" {{ ($userArr->read=='Y')?'checked':'' }} id="permissionRead">
                          <label for="permissionRead"> Read</label>
                          @error('read_permission')<span class="text-danger">{{ $message }}</span>@enderror
                        </div>
                        <div class="icheck-success d-inline" style="padding-right:100px;">
                          <input name="write_permission" type="checkbox" {{ ($userArr->write=='Y')?'checked':'' }} id="permissionWrite">
                          <label for="permissionWrite"> Write</label>
                        </div>
                        <div class="icheck-success d-inline" style="padding-right:100px;">
                          <input name="delete_permission" type="checkbox" {{ ($userArr->delete=='Y')?'checked':'' }} id="permissionDelete">
                          <label for="permissionDelete">Delete</label>
                        </div>
                        <div class="icheck-success d-inline" style="padding-right:100px;">
                          <input name="download_permission" type="checkbox" {{ ($userArr->download=='Y')?'checked':'' }} id="permissionDownload">
                          <label for="permissionDownload">Download</label>
                        </div>
                        <div class="icheck-success d-inline" style="padding-right:100px;">
                          <input name="upload_permission" type="checkbox" {{ ($userArr->upload=='Y')?'checked':'' }} id="permissionUpload">
                          <label for="permissionUpload">Upload</label>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>               
              </div>
              <!-- /.card-body -->
              <div class="card-footer">
                <a href="{{route('users')}}" class="btn btn-warning"> <i class="fas fa-backward"></i> Back</a>
                <a href="{{route('edit-user',['id'=>$userArr->id])}}" class="btn btn-info"> <i class="fas fa-redo-alt"></i> Refresh</a>
                <input type="hidden" name="referenceID" value="{{$userArr->id}}">
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