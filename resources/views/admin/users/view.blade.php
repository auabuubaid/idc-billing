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
            <li class="breadcrumb-item active">View</li>
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
              <h3 class="card-title">View User</h3>
            </div>
            <!-- /.card-header -->
            <div class="card-body">
              <table class="table table-bordered table-striped">
                <thead>
                  <tr style="text-align:center">
                    <th colspan="2">Personal</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td>Full Name</td>
                    <td>{{ $userArr->name }}</td>
                  </tr>
                  <tr>
                    <td>Email</td>
                    <td>{{ $userArr->email }}</td>
                  </tr>
                  <tr>
                    <td>Department</td>
                    <td>{{ userTypeAbbreviationToFullName($userArr->user_type)['full'] }}</td>
                  </tr>
                </tbody>  
                <thead>
                  <tr style="text-align:center">
                    <th colspan="2">Permission</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td>Read</td>
                    <td>@if($userArr->read=='Y') <span class="text-success"><i class="fas fa-check-circle"></i></span> @else  <span class="text-danger"><i class="fas fa-times-circle"></i></span> @endif</td>
                  </tr>
                  <tr>
                    <td>Write</td>
                    <td>@if($userArr->write=='Y') <span class="text-success"><i class="fas fa-check-circle"></i></span> @else  <span class="text-danger"><i class="fas fa-times-circle"></i></span> @endif</td>
                  </tr>
                  <tr>
                    <td>Delete</td>
                    <td>@if($userArr->delete=='Y') <span class="text-success"><i class="fas fa-check-circle"></i></span> @else  <span class="text-danger"><i class="fas fa-times-circle"></i></span> @endif</td>
                  </tr>
                  <tr>
                    <td>Download</td>
                    <td>@if($userArr->download=='Y') <span class="text-success"><i class="fas fa-check-circle"></i></span> @else  <span class="text-danger"><i class="fas fa-times-circle"></i></span> @endif</td>
                  </tr>
                  <tr>
                    <td>Upload</td>
                    <td>@if($userArr->upload=='Y') <span class="text-success"><i class="fas fa-check-circle"></i></span> @else  <span class="text-danger"><i class="fas fa-times-circle"></i></span> @endif</td>
                  </tr>
                </tbody>
                <thead>
                  <tr style="text-align:center">
                    <th colspan="2">Date</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td>Created Date</td>
                    <td>{{ readDateTimeFormat($userArr->created_at) }}</td>
                  </tr>
                  <tr>
                    <td>Updated Date</td>
                    <td>{{ readDateTimeFormat($userArr->updated_at) }} </td>
                  </tr>
                </tbody>
              </table>               
            </div>
            <!-- /.card-body -->
            <div class="card-footer">
              <a href="{{route('users')}}" class="btn btn-warning"> <i class="fas fa-backward"></i> Back</a>
            </div>
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