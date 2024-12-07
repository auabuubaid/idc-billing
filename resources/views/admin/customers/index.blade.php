@extends('admin.layout.master')

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
            <li class="breadcrumb-item"><a href="{{route('admin-dashboard')}}">Dashboard</a></li>
            <li class="breadcrumb-item active">Customers</li>
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
              <h3 class="card-title float-right">
                @if(checkPermission($admin_arr->upload))<a href="{{route('admin-import-customers')}}" class="btn btn-warning"><i class="fas fa-file-import"></i> Import</a>@endif
              </h3>
            </div>
            <!-- /.card-header -->
            <div class="card-body table-responsive p-2">
              <table id="customers" class="table table-bordered table-striped" style="text-align:center">
                <thead>
                  <tr>
                    <th>Sr</th>
                    <th>Type</th>
                    <th>Name</th>
                    <th>Shop Name</th>
                    <th style="min-width:100px">Address</th>
                    <th>Mobile</th>
                    <th>Register Date</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                  @if(count($customersArr)>0)
                    @foreach($customersArr as $ckey=>$cval)
                    <tr>
                      <td>{{ $ckey+$customersArr->firstItem() }}.</td>
                      <td><span title="{{ customerApplicationTypeAbbreviationToFullName($cval->type)['full'] }}" class="text-{{ customerApplicationTypeAbbreviationToFullName($cval->type)['color'] }}"><i class="{{ customerApplicationTypeAbbreviationToFullName($cval->type)['icon'] }}"></i></span></td>
                      <td>{{ $cval->name }}</td>
                      <td>@if($cval->type=='S') {{ $cval->shop_name }} @endif</td>
                      <td>{{ unitAddressByUnitID($cval->address_id) }}</td>
                      <td>@if($cval->type=='P'){{ $cval->mobile }} @else {{ $cval->shop_mobile }} @endif</td>
                      <td>{{ $cval->created_at }}</td>
                      <td>
                        @if(checkPermission($admin_arr->read)) <a href="{{route('view-admin-customer', ['id'=>$cval->id])}}" class="btn btn-info"><i class="fas fa-eye"></i></a> @endif
                      </td>
                    </tr>
                    @endforeach
                  @endif
                </tbody>
              </table>
              <div class="float-left">
                <form action="{{route('admin-customers')}}" method="get">
                  @csrf
                  <div class="input-group input-group-lg mb-3">
                    <div class="input-group-prepend mr-2">
                      <select class="form-control" name="records" onchange="this.form.submit()">
                        <option value="10" {{ $records=='10' ? "selected": "" }}>10</option>
                        <option value="50" {{ $records=='50' ? "selected": "" }}>50</option>
                        <option value="100" {{ $records=='100' ? "selected": "" }}>100</option>
                        <option value="500" {{ $records=='500' ? "selected": "" }}>500</option>
                        <option value="1000" {{ $records=='1000' ? "selected": "" }}>1000</option>
                        <option value="{{ $customersArr->total() }}" {{ $records==$customersArr->total() ? "selected": "" }}>All</option>
                      </select>
                    </div>
                    <span class="pt-1">Showing {{ $customersArr->firstItem() }} to {{ $customersArr->lastItem() }} of {{ $customersArr->total() }} entries</span>
                  </div>
                </form>
              </div>
              <div class="float-right">{{ $customersArr->links() }}</div>
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
$(function () {
  $('#customers').DataTable({
    "paging": false,
    "lengthChange": false,
    "searching": true,
    "order": [],
    "info": false,
    "autoWidth": false,
    "responsive": true,
    "columnDefs": [{
      "targets": 7,
      "orderable": false
    }], 
  });
})
</script>
@endsection