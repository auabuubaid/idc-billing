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
              <h3 class="card-title">View Exchange Rate</h3>
            </div>
            <!-- /.card-header -->
            <div class="card-body">
              <table class="table table-bordered table-striped">
                <tbody>
                  <tr>
                    <td>From Currency</td>
                    <td>{{ $exchangeRateArr->from_currency }}</td>
                  </tr>
                  <tr>
                    <td>To Currency</td>
                    <td>{{ $exchangeRateArr->to_currency }}</td>
                  </tr>
                  <tr>
                    <td>Exchange Rate</td>
                    <td>{{ $exchangeRateArr->rate }}</td>
                  </tr>
                  <tr>
                    <td>Exchange Month Year</td>
                    <td>{{ monthYear($exchangeRateArr->monthly_date) }}</td>
                  </tr>
                  <tr>
                    <td>Created By</td>
                    <td>{{ userNameByUserID($exchangeRateArr->created_by) }}</td>
                  </tr>
                  <tr>
                    <td>Created Date</td>
                    <td>{{ readDateTimeFormat($exchangeRateArr->created_at) }}</td>
                  </tr>
                  <tr>
                    <td>Updated Date</td>
                    <td>{{ readDateTimeFormat($exchangeRateArr->updated_at) }} </td>
                  </tr>
                </tbody>
              </table>               
            </div>
            <!-- /.card-body -->
            <div class="card-footer">
              <a href="{{route('exchange-rate')}}" class="btn btn-warning"> <i class="fas fa-backward"></i> Back</a>
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