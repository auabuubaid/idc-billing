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
            <li class="breadcrumb-item active">CableTV Plans</li>
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
              <h3 class="card-title">@if(checkPermission($admin_arr->write)) <a href="{{route('add-cabletv-service')}}" class="btn btn-primary"><i class="fas fa-plus"></i> Add New</a> @endif</h3>
            </div>
            <!-- /.card-header -->
            <div class="card-body table-responsive p-2">
              <table id="example1" class="table table-bordered table-striped">
                <thead>
                  <tr>
                    <th>Sr</th>
                    <th>Service Name</th>
                    <th>Installation Fee</th>
                    <th>Monthly Fee</th>
                    <th>Add New TV Fee</th>
                    <th>Status</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                  @if(count($cabletvServicesArr)>0)
                    @foreach($cabletvServicesArr as $ckey=>$cval)
                    <tr>
                      <td>{{ $ckey+$cabletvServicesArr->firstItem() }}.</td>
                      <td>{{ $cval->plan_name }}</td>
                      <td>${{ amountFormat($cval->installation_fee) }}</td>
                      <td>${{ amountFormat($cval->monthly_fee) }}</td>
                      <td>${{ amountFormat($cval->per_tv_fee) }}</td>
                      <td>{{ activeNotActiveAbbreviationToFull($cval->status) }}</td>
                      <td>
                          @if(checkPermission($admin_arr->read)) <a href="{{route('view-cabletv-service', ['id'=>$cval->id])}}" class="btn btn-info"><i class="fas fa-eye"></i></a>  @endif
                          @if(checkPermission($admin_arr->write)) <a href="{{route('edit-cabletv-service', ['id'=>$cval->id])}}" class="btn btn-warning"><i class="fas fa-edit"></i></a>  @endif
                          @if(checkPermission($admin_arr->delete)) <a href="{{route('destroy-cabletv-service', ['id'=>$cval->id])}}" class="btn btn-danger delete"><i class="fas fa-trash"></i></a>  @endif
                      </td>
                    </tr>
                    @endforeach
                  @endif
                </tbody>
              </table>
              <div class="float-left">
                <form action="{{route('cabletv-services')}}" method="get">
                  @csrf
                  <div class="input-group input-group-lg mb-3">
                    <div class="input-group-prepend mr-2">
                      <select class="form-control" name="records" onchange="this.form.submit()">
                        <option value="10" {{ $records=='10' ? "selected": "" }}>10</option>
                        <option value="50" {{ $records=='50' ? "selected": "" }}>50</option>
                        <option value="100" {{ $records=='100' ? "selected": "" }}>100</option>
                        <option value="500" {{ $records=='500' ? "selected": "" }}>500</option>
                        <option value="1000" {{ $records=='1000' ? "selected": "" }}>1000</option>
                        <option value="{{ $cabletvServicesArr->total() }}" {{ $records==$cabletvServicesArr->total() ? "selected": "" }}>All</option>
                      </select>
                    </div>
                    <span class="pt-1">Showing {{ $cabletvServicesArr->firstItem() }} to {{ $cabletvServicesArr->lastItem() }} of {{ $cabletvServicesArr->total() }} entries</span>
                  </div>
                </form>
              </div>
              <div class="float-right">{{ $cabletvServicesArr->links() }}</div>
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
<script>
$(document).ready(function(){
  $(".delete").click(function(){
    if (!confirm("Do you really want to delete?")){
      return false;
    }
  });
}); 

$(function () {
  $("#example1").DataTable({
    "paging": false,
    "lengthChange": false,
    "searching": true,
    "order": [],
    "info": false,
    "autoWidth": false,
    "responsive": true,
    "columnDefs": [{
      "targets": 6,
      "orderable": false
    }], 
  });
});
</script>
@endsection