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
              <h3 class="col-md-12 card-title">                                
                <form role="form" action="{{route('customers')}}" method="get" id="customers-form">
                  <div class="row">
                    @if(checkPermission($userArr->write))
                      <div class="col-md-2">
                        <div class="form-group" style="padding-top:27px;">
                          <label for="Add New">&nbsp;</label>
                          <a href="{{route('add-customer')}}" class="btn btn-primary"><i class="fas fa-plus"></i> Add New</a>
                        </div>
                      </div>
                    @endif
                    <div class="col-md-2">
                      <div class="form-group">
                        <label for="SelectMonthYear">Month Year</label>
                        <select name="monthlyYear" class="form-control select2" id="SelectMonthYear" onchange="this.form.submit()">
                          <option value="">--Select--</option>
                          @php $start = $month = strtotime(currentYearMonth().'-01') @endphp  @php  $end = strtotime('2017-10-01') @endphp
                          @while($end < $month)
                            <option value="{{ date('m-Y', $month) }}" {{ $monthYear==date('m-Y', $month) ? "selected": "" }}>{{ date('M-Y', $month), PHP_EOL }}</option>
                            @php $month = strtotime("-1 month", $month) @endphp
                          @endwhile
                        </select>
                      </div>
                    </div>
                    <div class="col-md-2">
                      <div class="form-group">
                        <label for="SelectCustType">Type</label>
                        <select name="cust_type" class="form-control select2" id="SelectCustType" onchange="this.form.submit()">
                          <option value="">--All--</option>
                          <option value="P" {{ $cust_type=='P' ? "selected": "" }}>Personal</option>
                          <option value="S" {{ $cust_type=='S' ? "selected": "" }}>Shop</option>
                        </select>
                      </div>
                    </div>
                    <div class="col-md-2">
                      <div class="form-group">
                        <label for="Selectaddress">Address</label>
                        <select name="addressID" class="form-control select2" id="Selectaddress" onchange="this.form.submit()">
                          <option value="">--Select--</option>
                          @if(count($unitsAddressArr)>0)
                            @foreach($unitsAddressArr as $lkey=>$lval)
                              <optgroup label="{{ buildingNameByLocationID($lkey) }}">
                                @foreach($lval as $uval)
                                  <option value="{{ $uval->id }}" {{ $addressID==$uval->id ? "selected": "" }}>{{ buildingLocationAndUnitNumberByUnitID($uval->id) }}</option>
                                @endforeach
                              </optgroup>    
                            @endforeach 
                          @endif
                        </select>
                      </div>
                    </div>
                    <div class="col-md-2">
                      <div class="form-group">
                        <label for="SelectServiceType">Service Type</label>
                        <select name="serviceType" class="form-control select2" id="SelectServiceType" onchange="this.form.submit()">
                          <option value="I" {{ $serviceType=='I' ? "selected": "" }}>Internet</option>
                          <option value="C" {{ $serviceType=='C' ? "selected": "" }}>Cable TV</option>
                        </select>
                      </div>
                    </div>
                  </div>
                  <input type="hidden" id="records" name="records" value="{{ $records }}">
                </form>
              </h3>
            </div>
            <!-- /.card-header -->
            <div class="card-body table-responsive p-2">
              <table id="customers" class="table table-bordered table-striped" style="max-width:99%; text-align:center">
                <thead>
                  <tr>
                    <th>Sr</th>
                    @if($serviceType=='I')<th style="max-width:30px">Internet ID</th>@endif
                    @if($serviceType=='C')<th style="max-width:30px">CableTV ID</th>@endif
                    <th>Type</th>
                    <th>Name</th>
                    <th>Address</th>
                    <th style="min-width:100px">Mobile</th>
                    <th>Register Date</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                  @if(count($customers_arr)>0)
                    @foreach($customers_arr as $ckey=>$cval)
                    <tr>
                      <td>{{ $ckey+$customers_arr->firstItem() }}.</td>
                      @if($serviceType=='I')<td>{{ $cval->internet_id }}</td>@endif
                      @if($serviceType=='C')<td>{{ $cval->cabletv_id }}</td>@endif
                      <td><span title="{{ customerApplicationTypeAbbreviationToFullName($cval->type)['full'] }}" class="text-{{ customerApplicationTypeAbbreviationToFullName($cval->type)['color'] }}"><i class="{{ customerApplicationTypeAbbreviationToFullName($cval->type)['icon'] }}"></i></span></td>
                      <td>{{ $cval->name }}<br> @if($cval->type=='S') {{ $cval->shop_name }} @endif</td>
                      <td>{{ buildingLocationAndUnitNumberByUnitID($cval->address_id) }}</td>
                      <td>@if($cval->type=='P'){{ $cval->mobile }} @else {{ $cval->shop_mobile }} @endif</td>
                      <td>{{ ($serviceType=='C') ? cableTVRegisterDateByCustID($cval->id) : internetRegisterDateByCustID($cval->id) }}</td>
                      <td>
                        @if(checkPermission($userArr->read)) <a href="{{route('view-customer', ['id'=>$cval->id])}}" class="btn btn-info"><i class="fas fa-eye"></i></a> @endif
                        @if(checkPermission($userArr->write)) <a href="{{route('edit-customer', ['id'=>$cval->id])}}" class="btn btn-warning"><i class="fas fa-edit"></i></a> @endif
                        @if(checkPermission($userArr->delete)) <a href="{{route('destroy-customer', ['id'=>$cval->id])}}" class="btn btn-danger delete"><i class="fas fa-trash"></i></a> @endif
                      </td>
                    </tr>
                    @endforeach
                  @endif
                </tbody>
              </table>
              <div class="float-left">
                <div class="input-group input-group-lg mb-3">
                  <div class="input-group-prepend mr-2">
                    <select class="form-control" id="SelectRecords" onchange="showNoRecords(this.value)">
                      <option value="10" {{ $records=='10' ? "selected": "" }}>10</option>
                      <option value="50" {{ $records=='50' ? "selected": "" }}>50</option>
                      <option value="100" {{ $records=='100' ? "selected": "" }}>100</option>
                      <option value="500" {{ $records=='500' ? "selected": "" }}>500</option>
                      <option value="1000" {{ $records=='1000' ? "selected": "" }}>1000</option>
                    </select>
                  </div>
                  <span class="pt-1">Showing {{ $customers_arr->firstItem() }} to {{ $customers_arr->lastItem() }} of {{ $customers_arr->total() }} entries</span>
                </div>                
              </div>
              <div class="float-right">{{ $customers_arr->links() }}</div>
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
  //Initialize Select2 Elements
  $('.select2').select2();

  $("#customers").DataTable({
    "paging": false,
    "lengthChange": true,
    "searching": false,
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

$(document).ready(function(){
  $(".delete").click(function(){
    if (!confirm("Do you really want to delete?")){
      return false;
    }
  });
});

function showNoRecords(records)
{
  $('#records').val(records);
  document.getElementById('customers-form').submit();
}
</script>
@endsection