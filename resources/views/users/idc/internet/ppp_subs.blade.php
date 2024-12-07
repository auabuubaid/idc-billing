@extends('users.layout.master')

@section('content')

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6">
          <h1>PPP Subscribers</h1>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{route('dashboard')}}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{route('internet-subscribers')}}">Internet Subscribers</a></li>
            <li class="breadcrumb-item active">PPP Subscribers</li>
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
        <div class="col-12">
          <div class="card">
            <div class="card-header">
              <h3 class="col-md-12 card-title">
                <form role="form" action="{{route('ppp-subscribers')}}" method="get" id="ppp-subscribers-form">
                  <div class="row">
                    
                  </div>
                  <input type="hidden" id="records" name="records" value="{{ $records }}">
                </form>   
              </h3>
            </div>
            <div class="card-body table-responsive p-2">
              <table id="subscribers" class="table table-bordered table-striped" style="width:99%; text-align: center">
                <thead>
                  <tr>
                    <th>Sr.</th>
                    <th style="min-width: 100px;">Address</th>
                    <th>InternetID</th>
                    <th>Password</th>
                    <th>Customer Name</th>
                    <th>Plan Name</th>
                    <th>IP Address</th>
                    <th>Mac Address</th>
                    <th style="min-width: 50px;">Status</th>
                  </tr>
                </thead>
                <tbody>
                  @if(is_array($pppSubscribers) && count($pppSubscribers)>0)
                    @foreach($pppSubscribers as $skey=>$sval)
                      <tr>
                        <td>{{ $skey+1 }}.</td>
                        <td>{{ customerDetailsByInternetID($sval['name'])['address'] }}</td>
                        <td>{{ $sval['name'] }}</td>
                        <td>{{ $sval['password'] }}</td>
                        <td>{{ customerDetailsByInternetID($sval['name'])['name'] }}</td>
                        <td>{{ $sval['profile'] }}</td>
                        <td>{{ activeCoreSubscriberByInternetID($sval['name'])['address'] }}</td>
                        <td>{{ activeCoreSubscriberByInternetID($sval['name'])['caller-id'] }}</td>
                        <td><strong class="text-{{ (activeCoreSubscriberByInternetID($sval['name'])['status']=='A') ? 'success' : 'danger' }}">{{ (activeCoreSubscriberByInternetID($sval['name'])['status']=='A') ? 'Active' : 'Not Active' }}</strong></td>
                      </tr>
                    @endforeach
                  @endif
                </tbody>
              </table>
            </div>
            <!-- /.card-body -->
          </div>
          <!-- /.card -->
        </div>
        <!-- /.col -->
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
  $('.select2').select2();

  $("#subscribers").DataTable({
    "paging": true,
    "lengthChange": true,
    "searching": true,
    "order": [],
    "info": true,
    "autoWidth": false,
    "responsive": true,
    "columnDefs": [{
      "targets": [5,6,7],
      "orderable": false
    }],
  });
})

function showNoRecords(records)
{
  $('#records').val(records);
  document.getElementById('ppp-subscribers-form').submit();
}
</script>
@endsection