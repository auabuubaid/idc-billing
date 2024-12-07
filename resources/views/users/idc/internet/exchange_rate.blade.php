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
            <li class="breadcrumb-item active">Exchange Rate</li>
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
        <div class="col-md-12">
          <div class="card">
            <div class="card-header">
              <h3 class="col-md-12 card-title">
                @if(checkPermission($userArr->write))<a href="{{route('add-exchange-rate')}}" class="btn btn-primary"><i class="fas fa-plus"></i> Add New</a>@endif
              </h3>
            </div>
            <div class="card-body" style="overflow: auto;">
              <table id="exchangeRate" class="table table-bordered table-striped" style="width:99%; text-align: center">
                <thead>
                  <tr>
                    <th>Sr</th>
                    <th>From</th>
                    <th>To</th>
                    <th>Rate</th>
                    <th>Month Year</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                  @if(count($exchangeRateArr)>0)
                    @foreach($exchangeRateArr as $ekey=>$eval)
                      <tr>
                        <td>{{ $ekey+$exchangeRateArr->firstItem() }}.</td>
                        <td>{{ $eval->from_currency }}</td>
                        <td>{{ $eval->to_currency }}</td>
                        <td>{{ $eval->rate }}</td>
                        <td>{{ monthYear($eval->monthly_date) }} </td>
                        <td>
                          @if(checkPermission($userArr->read))<a href="{{route('view-exchange-rate',['id'=>$eval->id])}}" class="btn btn-info"><i class="fas fa-eye"></i></a> @endif
                        </td>
                      </tr>
                    @endforeach
                  @endif
                </tbody>
              </table>
              <div class="float-left">
                <form role="form" action="{{route('exchange-rate')}}" method="get" id="exchange-rate-form">
                  @csrf
                  <div class="input-group input-group-lg mb-3">
                    <div class="input-group-prepend mr-2">
                      <select class="form-control" name="records" id="SelectRecords" onchange="this.form.submit()">
                        <option value="10" {{ $records=='10' ? "selected": "" }}>10</option>
                        <option value="50" {{ $records=='50' ? "selected": "" }}>50</option>
                        <option value="100" {{ $records=='100' ? "selected": "" }}>100</option>
                        <option value="500" {{ $records=='500' ? "selected": "" }}>500</option>
                        <option value="1000" {{ $records=='1000' ? "selected": "" }}>1000</option>
                      </select>
                    </div>
                    <span class="pt-1">Showing {{ $exchangeRateArr->firstItem() }} to {{ $exchangeRateArr->lastItem() }} of {{ $exchangeRateArr->total() }} entries</span>
                  </div>
                </form>                
              </div>
              <div class="float-right">{{ $exchangeRateArr->links() }}</div>
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

  $("#exchangeRate").DataTable({
    "paging": false,
    "lengthChange": true,
    "searching": false,
    "order": [],
    "info": false,
    "autoWidth": false,
    "responsive": true,
    "columnDefs": [{
      "targets": 5,
      "orderable": false
    }],
  });
})
$(document).ready(function(){
  $(".delete").click(function(){
    if (!confirm("Do you really want to delete?")){
      return false;
    }
  });
});
</script>
@endsection