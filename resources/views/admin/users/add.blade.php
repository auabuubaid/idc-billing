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
              <h3 class="card-title">Add User</h3>
            </div>
            <!-- /.card-header -->
            <form role="form" action="{{route('store-user')}}" method="post">
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
                        <input name="user_name" type="text" class="form-control" id="InputUserName" placeholder="Enter user name" value="{{ old('user_name')}}">
                      </div>  
                      @error('user_name')<span class="text-danger">{{ $message }}</span>@enderror
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="InputUserEmail">Email<span class="text-danger">*</span></label>
                      <div class="input-group">
                        <div class="input-group-prepend">
                          <span class="input-group-text">
                            <i class="fas fa-envelope"></i>
                          </span>
                        </div>
                        <input name="user_email" type="text" class="form-control" id="InputUserEmail" placeholder="Enter user email" value="{{ old('user_email')}}">
                      </div>
                      @error('user_email')<span class="text-danger">{{ $message }}</span>@enderror
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="SelectDepartment">Department<span class="text-danger">*</span></label>
                      <select name="department" class="custom-select" id="SelectDepartment">
                        <option value="">--Select--</option>
                        <option value="IDC" {{ old('department')=="IDC"?"selected":""}}>{{ userTypeAbbreviationToFullName('IDC')['full'] }}</option>
                      </select>
                      @error('department')<span class="text-danger">{{ $message }}</span>@enderror
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="InputPassword">Password<span class="text-danger">*</span></label>
                      <div class="input-group">
                        <div class="input-group-prepend">
                          <span class="input-group-text">
                            <i class="fas fa-lock"></i>
                          </span>
                        </div>
                        <input name="user_password" type="text" class="form-control" id="InputPassword" placeholder="Enter user password" data-size="15" data-character-set="a-z,A-Z,0-9,#" value="{{ old('user_password')}}">
                        <div class="input-group-append generate-password">
                          <span class="input-group-text">
                            <i class="fas fa-sync-alt"></i>
                          </span>
                        </div>
                      </div>
                      @error('user_password')<span class="text-danger">{{ $message }}</span>@enderror
                    </div>
                  </div>
                  <div class="col-md-12">
                    <div class="form-group clearfix">
                      <label for="InputPermission">Permission<span class="text-danger">*</span></label>
                      <div class="input-group">
                        <div class="icheck-success d-inline" style="padding-right:100px;">
                          <input name="read_permission" type="checkbox" checked id="permissionRead">
                          <label for="permissionRead"> Read</label>
                        </div>
                        <div class="icheck-success d-inline" style="padding-right:100px;">
                          <input name="write_permission" type="checkbox" id="permissionWrite">
                          <label for="permissionWrite"> Write</label>
                        </div>
                        <div class="icheck-success d-inline" style="padding-right:100px;">
                          <input name="delete_permission" type="checkbox" id="permissionDelete">
                          <label for="permissionDelete">Delete</label>
                        </div>
                        <div class="icheck-success d-inline" style="padding-right:100px;">
                          <input name="download_permission" type="checkbox" id="permissionDownload">
                          <label for="permissionDownload">Download</label>
                        </div>
                        <div class="icheck-success d-inline" style="padding-right:100px;">
                          <input name="upload_permission" type="checkbox" id="permissionUpload">
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
                <a href="{{route('add-user')}}" class="btn btn-info"> <i class="fas fa-redo-alt"></i> Refresh</a>
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
$(document).ready(function(){
  $(".generate-password").click(function(){
    var field = $(this).parent('div').find('input[name="user_password"]');
    field.val(randString(field));
  });
}); 

// Generate a password string
function randString(id)
{
  var dataSet = $(id).attr('data-character-set').split(',');  
  var possible = '';
  if($.inArray('a-z', dataSet) >= 0){
    possible += 'abcdefghijklmnopqrstuvwxyz';
  }
  if($.inArray('A-Z', dataSet) >= 0){
    possible += 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
  }
  if($.inArray('0-9', dataSet) >= 0){
    possible += '0123456789';
  }
  if($.inArray('#', dataSet) >= 0){
    possible += '![]{}()%&*$#^<>~@|';
  }
  var text = '';
  for(var i=0; i < $(id).attr('data-size'); i++) {
    text += possible.charAt(Math.floor(Math.random() * possible.length));
  }
  return text;
} 
</script>
@endsection