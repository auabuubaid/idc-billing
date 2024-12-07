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
            <li class="breadcrumb-item"><a href="{{ route('admin-dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('buildings-location') }}">Buildings Location</a></li>
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
              <h3 class="card-title">Edit Building</h3>
            </div>
            <!-- /.card-header -->
            <form role="form" action="{{ route('update-building-location') }}" method="post">
              @csrf
              <div class="card-body">
                <div class="form-group">
                  <label for="exampleSelectLocation">Location<span class="text-danger">*</span></label>
                  <select name="location" class="form-control" id="exampleSelectLocation">
                    <option value="">--Select--</option>
                    <option value="R1" {{ $building_location_arr->location=='R1' ?"selected":""}}>R1</option>
                    <option value="R2" {{ $building_location_arr->location=='R2' ?"selected":""}}>R2</option>
                    <option value="M1" {{ $building_location_arr->location=='M1' ?"selected":""}}>M1</option>
                    <option value="M2" {{ $building_location_arr->location=='M2' ?"selected":""}}>M2</option>
                    <option value="C1" {{ $building_location_arr->location=='C1' ?"selected":""}}>C1</option>
                    <option value="C2" {{ $building_location_arr->location=='C2' ?"selected":""}}>C2</option>
                  </select>
                  @error('location')<span class="text-danger">{{ $message }}</span>@enderror
                </div>
                <div class="form-group">
                  <label for="exampleSelectType">Type<span class="text-danger">*</span></label>
                  <select name="type" class="form-control" id="exampleSelectType">
                    <option value="">--Select--</option>
                    <option value="A" {{ $building_location_arr->type=='A' ?"selected":""}}>Apartment</option>
                    <option value="T" {{ $building_location_arr->type=='T' ?"selected":""}}>Town House</option>
                    <option value="S" {{ $building_location_arr->type=='S' ?"selected":""}}>Shop</option>
                    <option value="V" {{ $building_location_arr->type=='V' ?"selected":""}}>Villa</option>
                  </select>
                  @error('type')<span class="text-danger">{{ $message }}</span>@enderror
                </div>
                <div class="form-group">
                  <label for="exampleInputName">Name<span class="text-danger">*</span></label>
                  <input name="name" type="text" class="form-control" id="exampleInputName" placeholder="Enter building name" value="{{ $building_location_arr->name }}">
                  @error('name')<span class="text-danger">{{ $message }}</span>@enderror
                </div>
                <div class="form-group">
                  <label for="exampleInputOrder">Sort Order @error('sort_order')<span class="text-danger">*</span>@enderror</label>
                  <input name="sort_order" type="text" class="form-control" id="exampleInputOrder" placeholder="Enter sort order" value="{{ $building_location_arr->sort_order }}">
                  @error('sort_order')<span class="text-danger">{{ $message }}</span>@enderror
                </div>
                <div class="form-group">
                  <label for="exampleSelectStatus">Status<span class="text-danger">*</span></label>
                  <select name="status" class="form-control" id="exampleSelectStatus">
                    <option value="A" {{ $building_location_arr->status=='A' ?"selected":""}}>Active</option>
                    <option value="N" {{ $building_location_arr->status=='N' ?"selected":""}}>Not Active</option>
                  </select>
                  @error('status')<span class="text-danger">{{ $message }}</span>@enderror
                </div>
              </div>
              <!-- /.card-body -->
              <div class="card-footer">
                <a href="{{ route('buildings-location') }}" class="btn btn-warning"> <i class="fas fa-backward"></i> Back</a>
                <a href="{{ route('edit-building-location',['id'=>$building_location_arr->id]) }}" class="btn btn-info"> <i class="fas fa-redo-alt"></i> Refresh</a>
                <input name="referenceID" type="hidden" value="{{ $building_location_arr->id }}">
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