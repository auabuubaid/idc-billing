@extends('admin.layout.master')

@section('content')

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6">
          <h1>Units Address</h1>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{route('admin-dashboard')}}">Dashboard</a></li>
            <li class="breadcrumb-item active">Units Address</li>
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
              <h3 class="card-title"> @if(checkPermission($admin_arr->write)) <a href="{{route('add-unit-address')}}" class="btn btn-primary"><i class="fas fa-plus"></i> Add New</a> @endif </h3>
              <h3 class="card-title float-right">
                @if(checkPermission($admin_arr->upload)) <a href="{{route('import-units-address')}}" class="btn btn-warning"><i class="fas fa-file-import"></i> Import</a>@endif
              </h3>
            </div>
            <!-- /.card-header -->
            <div class="card-body table-responsive p-2">
              <table id="unitsAddress" class="table table-bordered table-striped">
                <thead>
                  <tr>
                    <th>Sr</th>
                    <th>Location</th>
                    <th>Unit Number</th>
                    <th>Order</th>
                    <th>Status</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                @if(count($units_address_arr)>0)
                  @foreach($units_address_arr as $ukey=>$uval)
                    <tr>
                      <td>{{ $ukey + $units_address_arr->firstItem() }}.</td>
                      <td>{{ buildingLocationByLocationID($uval->location_id) }}</td>
                      <td>{{ $uval->unit_number }}</td>
                      <td>{{ $uval->sort_order }}</td>
                      <td>@if($uval->status=='A') <span class="text-info"> {{ activeNotActiveAbbreviationToFull($uval->status) }}</span> @else <span class="text-danger"> {{ activeNotActiveAbbreviationToFull($uval->status) }}</span> @endif</td>
                      <td>
                          @if(checkPermission($admin_arr->read))  <a href="{{route('view-unit-address', ['id'=>$uval->id])}}" class="btn btn-info"><i class="fas fa-eye"></i></a> @endif
                          @if(checkPermission($admin_arr->write)) <a href="{{route('edit-unit-address', ['id'=>$uval->id])}}" class="btn btn-warning"><i class="fas fa-edit"></i></a> @endif
                          @if(checkPermission($admin_arr->delete)) <a href="{{route('destroy-unit-address', ['id'=>$uval->id])}}" class="btn btn-danger delete"><i class="fas fa-trash"></i></a> @endif
                      </td>
                    </tr>
                  @endforeach
                @endif
                </tbody>
              </table>
              <div class="float-left">
                <form action="{{route('units-address')}}" method="get">
                  @csrf
                  <div class="input-group input-group-lg mb-3">
                    <div class="input-group-prepend mr-2">
                      <select class="form-control" name="records" onchange="this.form.submit()">
                        <option value="10" {{ $records=='10' ? "selected": "" }}>10</option>
                        <option value="50" {{ $records=='50' ? "selected": "" }}>50</option>
                        <option value="100" {{ $records=='100' ? "selected": "" }}>100</option>
                        <option value="500" {{ $records=='500' ? "selected": "" }}>500</option>
                        <option value="1000" {{ $records=='1000' ? "selected": "" }}>1000</option>
                        <option value="{{ $units_address_arr->total() }}" {{ $records==$units_address_arr->total() ? "selected": "" }}>All</option>
                      </select>
                    </div>
                    <span class="pt-1">Showing {{ $units_address_arr->firstItem() }} to {{ $units_address_arr->lastItem() }} of {{ $units_address_arr->total() }} entries</span>
                  </div>
                </form>
              </div>
              <div class="float-right">{{ $units_address_arr->links() }}</div>
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
  $('#unitsAddress').DataTable({
    "paging": false,
    "lengthChange": false,
    "searching": true,
    "order": [],
    "info": false,
    "autoWidth": false,
    "responsive": true,
    "columnDefs": [{
      "targets": 5,
      "orderable": false
    }], 
  });
});
</script>
@endsection