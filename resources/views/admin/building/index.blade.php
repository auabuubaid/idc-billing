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
            <li class="breadcrumb-item"><a href="{{route('admin-dashboard')}}">Dashboard</a></li>
            <li class="breadcrumb-item active">Buildings Location</li>
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
              <h3 class="card-title">
                @if(checkPermission($admin_arr->write)) <a href="{{route('add-building-location')}}" class="btn btn-primary"><i class="fas fa-plus"></i> Add New</a> @endif
              </h3>
              <h3 class="card-title float-right">
                @if(checkPermission($admin_arr->upload)) <a href="{{route('import-buildings-location')}}" class="btn btn-warning"><i class="fas fa-file-import"></i> Import</a>@endif
              </h3>
            </div>
            <!-- /.card-header -->
            <div class="card-body table-responsive p-2">
              <table id="buildingLoc" class="table table-bordered table-striped">
                <thead>
                  <tr>
                    <th>Sr</th>
                    <th>Location</th>
                    <th>Type</th>
                    <th>Name</th>
                    <th>Order</th>
                    <th>Status</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                @if(count($buildings_location_arr)>0)
                  @foreach($buildings_location_arr as $bkey=>$bval)
                    <tr>
                      <td>{{ $bkey + $buildings_location_arr->firstItem() }}.</td>
                      <td>{{ $bval->location }}</td>
                      <td>{{ buildingTypeAbbreviationToFullName($bval->type) }}</td>
                      <td>{{ $bval->name }}</td>
                      <td>{{ $bval->sort_order }}</td>
                      <td>@if($bval->status=='A') <span class="text-info">{{ activeNotActiveAbbreviationToFull($bval->status) }}</span> @else <span class="text-danger"> {{ activeNotActiveAbbreviationToFull($bval->status) }} </span> @endif</td>
                      <td>
                          @if(checkPermission($admin_arr->read)) <a href="{{route('view-building-location', ['id'=>$bval->id])}}" class="btn btn-info"><i class="fas fa-eye"></i></a> @endif
                          @if(checkPermission($admin_arr->write)) <a href="{{route('edit-building-location', ['id'=>$bval->id])}}" class="btn btn-warning"><i class="fas fa-edit"></i></a> @endif
                          @if(checkPermission($admin_arr->delete)) <a href="{{route('destroy-building-location', ['id'=>$bval->id])}}" class="btn btn-danger delete"><i class="fas fa-trash"></i></a> @endif
                      </td>
                    </tr>
                  @endforeach
                @endif
                </tbody>
              </table>
              <div class="float-left">
                <form action="{{route('buildings-location')}}" method="get">
                  @csrf
                  <div class="input-group input-group-lg mb-3">
                    <div class="input-group-prepend mr-2">
                      <select class="form-control" name="records" onchange="this.form.submit()">
                        <option value="10" {{ $records=='10' ? "selected": "" }}>10</option>
                        <option value="50" {{ $records=='50' ? "selected": "" }}>50</option>
                        <option value="100" {{ $records=='100' ? "selected": "" }}>100</option>
                        <option value="500" {{ $records=='500' ? "selected": "" }}>500</option>
                        <option value="1000" {{ $records=='1000' ? "selected": "" }}>1000</option>
                        <option value="{{ $buildings_location_arr->total() }}" {{ $records==$buildings_location_arr->total() ? "selected": "" }}>All</option>
                      </select>
                    </div>
                    <span class="pt-1">Showing {{ $buildings_location_arr->firstItem() }} to {{ $buildings_location_arr->lastItem() }} of {{ $buildings_location_arr->total() }} entries</span>
                  </div>
                </form>
              </div>
              <div class="float-right">{{ $buildings_location_arr->links() }}</div>
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
    $('#buildingLoc').DataTable({
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