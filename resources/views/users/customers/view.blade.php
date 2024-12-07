@extends('users.layout.master')

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
            <li class="breadcrumb-item"><a href="{{route('dashboard')}}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{route('customers')}}">Customers</a></li>
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
              <h3 class="card-title">View Customer</h3>
            </div>
            <!-- /.card-header -->
            <div class="card-body">
              <table class="table table-bordered table-striped">
                <thead>
                  <tr style="text-align:center">
                    <th colspan="2">Application & Address</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td>Application Type</td>
                    <td>{{ customerApplicationTypeAbbreviationToFullName($customerArr->type)['full'] }}</td>
                  </tr>
                  <tr>
                    <td>Full Address</td>
                    <td>{{ unitAddressByUnitID($customerArr->address_id) }}</td>
                  </tr>
                  <tr>
                    <td>Country</td>
                    <td>{{ $customerArr->country }}</td>
                  </tr>
                </tbody>
                @if($customerArr->type=="P")
                <thead>
                  <tr style="text-align:center">
                    <th colspan="2">Personal Details</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td>Name</td>
                    <td>{{ $customerArr->name }}</td>
                  </tr>
                  <tr>
                    <td>Mobile</td>
                    <td>{{ $customerArr->mobile }}</td>
                  </tr>
                  <tr>
                    <td>Email</td>
                    <td>{{ $customerArr->email }}</td>
                  </tr>
                  <tr>
                    <td>Is Living</td>
                    <td>{{ yesnoAbbreviationToFull($customerArr->is_living) }}</td>
                  </tr>
                  <tr>
                    <td>Sex</td>
                    <td>{{ customerSexAbbreviationToFullName($customerArr->sex) }}</td>
                  </tr>
                </tbody>
                @endif
                @if($customerArr->type=="S")
                <thead>
                  <tr style="text-align:center">
                    <th colspan="2">Shop Details</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td>Authorized Person</td>
                    <td>{{ $customerArr->name }}</td>
                  </tr>
                  <tr>
                    <td>Shop Name</td>
                    <td>{{ $customerArr->shop_name }}</td>
                  </tr>
                  <tr>
                    <td>Shop Mobile</td>
                    <td>{{ $customerArr->shop_mobile }}</td>
                  </tr>
                  <tr>
                    <td>Shop Email</td>
                    <td>{{ $customerArr->shop_email }}</td>
                  </tr>
                  <tr>
                    <td>VAT Number</td>
                    <td>{{ $customerArr->vat_no }}</td>
                  </tr>
                </tbody>
                @endif
                @if(!empty($customerArr->internet_id))
                <thead>
                  <tr style="text-align:center">
                    <th colspan="2">Internet Details</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td>User ID</td>
                    <td>{{ $customerArr->internet_id }}</td>
                  </tr>
                  <tr>
                    <td>Password</td>
                    <td>{{ $customerArr->internet_password }}</td>
                  </tr>
                  <tr>
                    <td>IP Address</td>
                    <td>{{ $customerArr->ip_address }}</td>
                  </tr>
                </tbody>
                @endif
                @if(!empty($customerArr->cabletv_id))
                <thead>
                  <tr style="text-align:center">
                    <th colspan="2">Cable TV Details</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td>User ID</td>
                    <td>{{ $customerArr->cabletv_id }}</td>
                  </tr>
                </tbody>
                @endif
                <thead>
                  <tr style="text-align:center">
                    <th colspan="2">Date</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td>Created By</td>
                    <td>{{ userNameByUserID($customerArr->created_by) }}</td>
                  </tr>
                  <tr>
                    <td>Created Date</td>
                    <td>{{ readDateTimeFormat($customerArr->created_at) }}</td>
                  </tr>
                  <tr>
                    <td>Updated Date</td>
                    <td>{{ readDateTimeFormat($customerArr->updated_at) }} </td>
                  </tr>
                </tbody>
              </table>               
            </div>
            <!-- /.card-body -->
            <div class="card-footer">
              <a href="{{route('customers')}}" class="btn btn-warning"> <i class="fas fa-backward"></i> Back</a>
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