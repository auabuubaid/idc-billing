@extends('admin.layout.master')

@section('content')

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6">
          <h1>Buildings Location</h1>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{ route('admin-dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('buildings-location') }}">Buildings Location</a></li>
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
              <h3 class="card-title">View Building</h3>
            </div>
            <!-- /.card-header -->
              <div class="card-body">
                <table class="table table-bordered table-striped">
                  <thead>
                    <tr>
                      <th>Title</th>
                      <th>Value</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr>
                      <td>Location</td>
                      <td>{{ $building_location_arr->location }}</td>
                    </tr>
                    <tr>
                      <td>Type</td>
                      <td>{{ buildingTypeAbbreviationToFullName($building_location_arr->type) }}</td>
                    </tr>
                    <tr>
                      <td>Building Number</td>
                      <td>{{ $building_location_arr->name }}</td>
                    </tr>
                    <tr>
                      <td>Status</td>
                      <td>{{ activeNotActiveAbbreviationToFull($building_location_arr->status) }}</td>
                    </tr>
                    <tr>
                      <td>Sort Order</td>
                      <td>{{ $building_location_arr->sort_order }}</td>
                    </tr>
                    <tr>
                      <td>Created Date</td>
                      <td>{{ readDateTimeFormat($building_location_arr->created_at) }}</td>
                    </tr>
                    <tr>
                      <td>Updated Date</td>
                      <td>{{ readDateTimeFormat($building_location_arr->updated_at) }} </td>
                    </tr>
                  </tbody>
                </table>               
              </div>
              <!-- /.card-body -->
              <div class="card-footer">
                <a href="{{ route('buildings-location') }}" class="btn btn-warning"> <i class="fas fa-backward"></i> Back</a>
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