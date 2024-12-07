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
            <li class="breadcrumb-item"><a href="{{ route('admin-dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('units-address') }}">Units Address</a></li>
            <li class="breadcrumb-item active">Edit</li>
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
              <h3 class="card-title">Edit Unit Address</h3>
            </div>
            <!-- /.card-header -->
            <form role="form" action="{{ route('update-unit-address') }}" method="post">
              @csrf
              <div class="card-body">
                <div class="form-group">
                  <label for="exampleSelectLocation">Location<span class="text-danger">*</span></label>
                  <select name="location" class="form-control" id="exampleSelectLocation">
                    <option value="">--Select--</option>
                    @if(count($building_location_arr)>0)
                      @foreach($building_location_arr as $bkey=>$bval)
                        <option value="{{ $bval->id }}" {{ $bval->id==$unit_address_arr->location_id ?"selected":""}}>{{buildingLocationByLocationID($bval->id)}}</option>
                      @endforeach
                    @endif
                  </select>
                  @error('location')<span class="text-danger">{{ $message }}</span>@enderror
                </div>
                <div class="form-group">
                  <label for="exampleInputUnitNumber">Unit Number<span class="text-danger">*</span></label>
                  <input name="unit_number" type="text" class="form-control" id="exampleInputUnitNumber" placeholder="Enter unit number" value="{{ $unit_address_arr->unit_number }}">
                  @error('unit_number')<span class="text-danger">{{ $message }}</span>@enderror
                </div>
                <div class="form-group">
                  <label for="exampleInputOrder">Sort Order @error('sort_order')<span class="text-danger">*</span>@enderror</label>
                  <input name="sort_order" type="text" class="form-control" id="exampleInputOrder" placeholder="Enter sort order" value="{{ $unit_address_arr->sort_order }}">
                  @error('sort_order')<span class="text-danger">{{ $message }}</span>@enderror
                </div>
                <div class="form-group">
                  <label for="exampleSelectStatus">Status<span class="text-danger">*</span></label>
                  <select name="status" class="form-control" id="exampleSelectStatus">
                    <option value="A" {{ $unit_address_arr->status=='A' ?"selected":""}}>Active</option>
                    <option value="N" {{ $unit_address_arr->status=='N' ?"selected":""}}>Not Active</option>
                  </select>
                  @error('status')<span class="text-danger">{{ $message }}</span>@enderror
                </div>
              </div>
              <!-- /.card-body -->
              <div class="card-footer">
                <a href="{{ route('units-address') }}" class="btn btn-warning"> <i class="fas fa-backward"></i> Back</a>
                <a href="{{ route('edit-unit-address', ['id'=>$unit_address_arr->id]) }}" class="btn btn-info"> <i class="fas fa-redo-alt"></i> Refresh</a>
                <input name="referenceID" type="hidden" value="{{ $unit_address_arr->id }}">
                <button type="submit" class="btn btn-primary"> <i class="fas fa-save"></i> Save</button>
              </div>
            </form>
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