@extends('admin.layout.master')
@section('content')
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6">
          <h1>Old Monthly Payment</h1>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{route('admin-dashboard')}}">Dashboard</a></li>
            <li class="breadcrumb-item active">Old Monthly Payment</li>
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
                <div class="row">
                  <div class="col-md-10">
                    <form role="form" action="{{route('old-monthly-payment')}}" method="get">
                      @csrf
                      <div class="row">
                        <div class="col-md-2">
                          <div class="form-group">
                            <label for="InputMonthYear">Month Year</label>
                            <select name="monthlyYear" class="form-control select2" id="SelectMonthYear" onchange="this.form.submit()">
                              <option value="Jan 25, 2021" {{ $monthYear=='Jan 25, 2021' ?"selected":""}}> January 2021</option>
                              <option value="Feb 25, 2021" {{ $monthYear=='Feb 25, 2021' ?"selected":""}}> February 2021</option>
                              <option value="Mar 25, 2021" {{ $monthYear=='Mar 25, 2021' ?"selected":""}}> March 2021</option>
                              <option value="Apr 25, 2021" {{ $monthYear=='Apr 25, 2021' ?"selected":""}}> April 2021</option>
                              <option value="May 25, 2021" {{ $monthYear=='May 25, 2021' ?"selected":""}}> May 2021</option>
                              <option value="Jun 25, 2021" {{ $monthYear=='Jun 25, 2021' ?"selected":""}}> June 2021</option>
                              <option value="Jul 25, 2021" {{ $monthYear=='Jul 25, 2021' ?"selected":""}}> July 2021</option>
                              <option value="Aug 25, 2021" {{ $monthYear=='Aug 25, 2021' ?"selected":""}}> August 2021</option>
                              <option value="Sep 25, 2021" {{ $monthYear=='Sep 25, 2021' ?"selected":""}}> September 2021</option>
                              <option value="Oct 25, 2021" {{ $monthYear=='Oct 25, 2021' ?"selected":""}}> October 2021</option>
                              <option value="Nov 25, 2021" {{ $monthYear=='Nov 25, 2021' ?"selected":""}}> November 2021</option>
                              <option value="Dec 25, 2021" {{ $monthYear=='Dec 25, 2021' ?"selected":""}}> December 2021</option>
                              <option value="Jan 25, 2022" {{ $monthYear=='Jan 25, 2022' ?"selected":""}}> January 2022</option>
                              <option value="Feb 25, 2022" {{ $monthYear=='Feb 25, 2022' ?"selected":""}}> February 2022</option>
                              <option value="Mar 25, 2022" {{ $monthYear=='Mar 25, 2022' ?"selected":""}}> March 2022</option>
                              <option value="Apr 25, 2022" {{ $monthYear=='Apr 25, 2022' ?"selected":""}}> April 2022</option>
                              <option value="May 25, 2022" {{ $monthYear=='May 25, 2022' ?"selected":""}}> May 2022</option>
                              <option value="Jun 25, 2022" {{ $monthYear=='Jun 25, 2022' ?"selected":""}}> June 2022</option>
                              <option value="Jul 25, 2022" {{ $monthYear=='Jul 25, 2022' ?"selected":""}}> July 2022</option>
                              <option value="Aug 25, 2022" {{ $monthYear=='Aug 25, 2022' ?"selected":""}}> August 2022</option>
                              <option value="Sep 25, 2022" {{ $monthYear=='Sep 25, 2022' ?"selected":""}}> September 2022</option>
                              <option value="Oct 25, 2022" {{ $monthYear=='Oct 25, 2022' ?"selected":""}}> October 2022</option>
                              <option value="Nov 25, 2022" {{ $monthYear=='Nov 25, 2022' ?"selected":""}}> November 2022</option>
                              <option value="Dec 25, 2022" {{ $monthYear=='Dec 25, 2022' ?"selected":""}}> December 2022</option>                                 
                            </select>
                          </div>
                        </div>
                      </div>
                      <input type="hidden" name="records" value="{{ $records }}">
                    </form>
                  </div>
                  @if(checkPermission($admin_arr->download))
                    <div class="col-md-1 mt-3" style="max-width:55px;">
                      <form role="form" action="{{ route('export-old-monthly-invoices') }}" method="post">
                        @csrf
                        <input type="hidden" name="invoice_month_year" id="InputInvoiceMonthYear" value="" >
                        <button type="submit" title="Download Monthly Invoice" class="btn bg-gradient-danger"><i class="fas fa-file-pdf"></i></button>
                      </form>
                    </div>
                  @endif
                  @if(checkPermission($admin_arr->upload))
                    <div class="col-md-1 mt-3" style="max-width:55px;">
                      <a href="{{ route('import-old-monthly-payment') }}" title="Import Monthly Excel" class="btn bg-gradient-warning"><i class="fas fa-file-excel"></i></a>
                    </div>
                  @endif
                </div>
              </h3>
            </div>
            <div class="card-body table-responsive p-2">
              <table id="oldMonthlyInvoice" class="table table-bordered table-striped" style="width:99%; text-align: center">
                <thead>
                  <tr>
                    <th>Sr</th>
                    <th style="min-width: 60px;">Date</th>
                    <th>Customer Name</th>
                    <th>Address</th>
                    <th>Monthly Fee</th>
                    <th>Balance</th>
                    <th>VAT</th>
                    <th>Amount</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                  @if(count($ompArr)>0)
                    @foreach($ompArr as $okey=>$oval)
                      <tr>
                        <td>{{ $okey+$ompArr->firstItem() }}.</td>
                        <td>{{ $oval->invoice_date }}</td>
                        <td>{{ $oval->customer_name }} </td>
                        <td>{{ $oval->address }}</td>
                        <td>${{ amountFormat2($oval->monthly_fee) }}</td>
                        <td>${{ amountFormat2($oval->balance) }}</td>
                        <td>${{ amountFormat2($oval->vat_amount) }}</td>
                        <td><b>${{ amountFormat2($oval->total_amount) }}</b></td>
                        <td><a href="{{ route('generate-old-monthly-invoice',$oval->id) }}" class="text-danger"><i class="fas fa-file-pdf"></i></a></td>
                      </tr>
                    @endforeach
                  @endif
                </tbody>
              </table>
              <div class="float-left">
                <form action="{{route('old-monthly-payment')}}" method="get">
                  @csrf
                  <div class="input-group input-group-lg mb-3">
                    <div class="input-group-prepend mr-2">
                      <select class="form-control" name="records" onchange="this.form.submit()">
                        <option value="10" {{ $records=='10' ? "selected": "" }}>10</option>
                        <option value="50" {{ $records=='50' ? "selected": "" }}>50</option>
                        <option value="100" {{ $records=='100' ? "selected": "" }}>100</option>
                        <option value="500" {{ $records=='500' ? "selected": "" }}>500</option>
                        <option value="1000" {{ $records=='1000' ? "selected": "" }}>1000</option>
                        <option value="{{ $ompArr->total() }}" {{ $records==$ompArr->total() ? "selected": "" }}>All</option>
                      </select>
                    </div>
                    <span class="pt-1">Showing {{ $ompArr->firstItem() }} to {{ $ompArr->lastItem() }} of {{ $ompArr->total() }} entries</span>
                  </div>
                  <input type="hidden" name="monthlyYear" value="{{ $monthYear }}">
                </form>
              </div>
              <div class="float-right">{{ $ompArr->links() }}</div>
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

  $("#oldMonthlyInvoice").DataTable({
    "paging": false,
    "lengthChange": true,
    "searching": true,
    "order": [],
    "info": false,
    "autoWidth": false,
    "responsive": true,
    "columnDefs": [{
      "targets": 8,
      "orderable": false
    }],
  });
})

$( document ).ready(function() {
  var monthYear = $('#SelectMonthYear').val();    
  $('#InputInvoiceMonthYear').val(monthYear);  
  // $( ".remove" ).click(function() {
  //   $(this).parent().parent().hide();
  // });
});
</script>
@endsection