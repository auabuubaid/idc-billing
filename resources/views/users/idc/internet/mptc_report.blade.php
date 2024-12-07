@extends('users.layout.master')

@section('content')

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6">
          <h1>MPTC Revenue</h1>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{route('internet-subscribers')}}">Internet</a></li>
            <li class="breadcrumb-item active">MPTC Revenue</li>
          </ol>
        </div>
      </div>
    </div>
    <!-- /.container-fluid -->
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
                <form role="form" action="{{route('mptc-report')}}" method="get">
                  <div class="row">
                    <div class="col-md-2">
                      <div class="form-group">
                        <label for="SelectYear">Year</label>
                        <select name="year" class="form-control select2" id="SelectYear" onchange="this.form.submit()">
                          @for($start=currentYear(); $start>=2017; $start--)
                            <option value="{{ $start }}" {{ $year==$start ? "selected": "" }}>{{ $start }}</option>
                          @endfor
                        </select>
                      </div>
                    </div>
                    <div class="col-md-2">
                      <div class="form-group">
                        <label for="SelectQuarter">Quarter</label>
                        <select name="quarter" class="form-control select2" id="SelectQuarter" onchange="this.form.submit()">
                          <option value="1"{{ $quarter=='1' ? "selected": "" }}>Jan To Mar</option>
                          <option value="2"{{ $quarter=='2' ? "selected": "" }}>Apr To Jun</option>
                          <option value="3"{{ $quarter=='3' ? "selected": "" }}>Jul To Sep</option>
                          <option value="4"{{ $quarter=='4' ? "selected": "" }}>Oct To Dec</option>
                        </select>
                      </div>
                    </div>
                    <div class="col-md-4"></div>
                    @if(checkPermission($userArr->download))
                      <div class="col-md-4 mt-4">
                        <a href="{{route('mptc-service-declaration',['year'=>$year,'quarter'=>$quarter])}}" title="Quarterly Declaration" class="btn bg-gradient-primary float-right ml-2"><i class="fas fa-file-excel"></i> Declaration</a>
                        <a href="{{route('mptc-income-statement',['year'=>$year,'quarter'=>$quarter])}}" title="Quarterly Income Statement" class="btn bg-gradient-primary float-right"><i class="fas fa-file-excel"></i> Statement</a>
                      </div>
                    @endif
                  </div>
                </form>                   
              </h3>
            </div>
            <div class="card-body">
              <table id="mptc-report" class="table table-bordered table-striped" style="width:99%; text-align: center">
                <thead>
                  <tr>
                    <th colspan="8"><h3>Monthly Income Statement</h3><h3 id="quarter"></h3></th>
                  </tr>                  
                  <tr>
                    <th><span class="text-dark">#</span></th>
                    <th><span class="text-dark">Invoice</th>
                    <th><span class="text-dark">Date</th>
                    <th><span class="text-dark">Company</th>
                    <th><span class="text-dark">Description</th>
                    <th><span class="text-dark">Service Fee</th>
                    <th><span class="text-dark">VAT</th>
                    <th><span class="text-dark">Total</span></th>
                  </tr>
                </thead>
                <tbody>
                  @if(is_array($mReportArr) && count($mReportArr)>0)
                    @foreach($mReportArr as $mkey=>$mval)
                      <tr>                        
                        <td>{{ $mkey+1 }}</td>                    
                        <td>{{ $mval['invoice_number'] }}</td>
                        <td>{{ $mval['invoice_date'] }}</td>
                        <td>World City Co., Ltd</td>
                        <td>Internet Service Fee</td>
                        <td>${{ reportAmountFormat($mval['total_amount']/1.1) }}</td>
                        <td>${{ reportAmountFormat($mval['total_amount']/11) }}</td>
                        <td>${{ reportAmountFormat($mval['total_amount']) }}</td>
                      </tr>
                    @endforeach
                  @endif 
                </tbody>
                <tfoot>
                  <tr>
                    <th colspan="5"><span class="text-danger"> Grand Total</span></th>
                    <th class="text-dark"></th>
                    <th class="text-dark"></th>
                    <th class="text-dark"></th>
                  </tr>
                </tfoot>
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

  $('#quarter').text('From '+$('#SelectQuarter option:selected').text()+' '+$('#SelectYear option:selected').text());

  $("#mptc-report").DataTable({
    "responsive": true,
    "paging": false,
    "searching": false,
    "ordering": false,
    "footerCallback": function (tfoot, data, start, end, display) { 
        var col = 0;
        for(var i = 5; i < 8; i++){
          var totalAmount = 0;          
          for (var j = 0; j < data.length; j++) { 
            totalAmount += parseFloat((data[j][i]).replace(/[,$]/g,''));
          }
          col++;
          var amount= new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(totalAmount);
          $(tfoot).find('th').eq(col).text(amount);
        }
      }
  });
})

</script>
@endsection