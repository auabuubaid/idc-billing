@extends('users.layout.master')

@section('content')

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6">
          <h1>Report</h1>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{route('dashboard')}}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{route('cabletv-subscribers')}}">Cable TV</a></li>
            <li class="breadcrumb-item active">Report</li>
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
                <form role="form" action="{{route('cabletv-report')}}" method="post" id="cabletv-report-form">
                  @csrf
                  <div class="row">
                    <div class="col-md-4">
                      <div class="form-group">
                        <label for="InputType">Type</label>
                        <div class="form-group clearfix" style="padding-top:8px;">
                          <div class="icheck-primary d-inline" style="padding-right:5px;">
                            <input type="radio" name="type" onchange="openBoxByType(this.value)"  {{ $type=='Y'? "checked": "" }}  value="Y" id="Yearly">
                            <label for="Yearly">Yearly</label>
                          </div>
                          <div class="icheck-primary d-inline" style="padding-right:5px;">
                            <input type="radio" name="type" onchange="openBoxByType(this.value)"  {{ $type=='Q'? "checked": "" }} value="Q" id="Quarterly">
                            <label for="Quarterly">Quarterly</label>
                          </div>
                          <div class="icheck-primary d-inline" style="padding-right:5px;">
                            <input type="radio" name="type" onchange="openBoxByType(this.value)"  {{ $type=='M'? "checked": "" }} value="M" id="Monthly">
                            <label for="Monthly">Monthly</label>
                          </div>
                        </div>
                      </div>
                    </div>
                    <div class="col-md-2" id="yearlyDiv">
                      <div class="form-group">
                        <label for="SelectYear">Year</label>
                        <select name="year" class="form-control select2" id="SelectYear">
                          @for($start=currentYear(); $start>=2017; $start--)
                            <option value="{{ $start }}" {{ $year==$start ? "selected": "" }}>{{ $start }}</option>
                          @endfor
                        </select>
                      </div>
                    </div>
                    <div class="col-md-2" id="quarterlyDiv">
                      <div class="form-group">
                        <label for="SelectQuarter">Quarter</label>
                        <select name="quarter" class="form-control select2" id="SelectQuarter">
                          <option value="{{ '01' }}" {{ $quarter=='01' ? "selected": "" }}>1 Jan - 31 Mar</option>
                          <option value="{{ '04' }}" {{ $quarter=='04' ? "selected": "" }}>1 Apr - 30 Jun</option>
                          <option value="{{ '07' }}" {{ $quarter=='07' ? "selected": "" }}>1 Jul - 30 Sep</option>
                          <option value="{{ '10' }}" {{ $quarter=='10' ? "selected": "" }}>1 Oct - 31 Dec</option>
                        </select>
                      </div>
                    </div>
                    <div class="col-md-2" id="monthlyDiv">
                      <div class="form-group">
                        <label for="SelectMonth">Month</label>
                        <select name="month" class="form-control select2" id="SelectMonth">
                          @for($i=1; $i<=12; $i++)
                            <option value="{{ $i }}" {{ $month==$i ? "selected": "" }}>{{ monthNumberToName($i) }}</option>
                          @endfor
                        </select>
                      </div>
                    </div>
                    <div class="col-md-2">
                      <div class="form-group mt-4">
                        <label for="Submit" style="padding-top:12px;">&nbsp;</label>
                        <button type="submit" class="btn btn-warning"><i class="fas fa-search"></i></button>
                      </div>
                    </div>
                  </div>
                </form>   
              </h3>
            </div>
            <div class="card-body">
              <table id="cabletv-report" class="table table-bordered table-striped" style="width:99%; text-align: center">
                <thead>                  
                  <tr>
                    <th style="min-width:150px;"><span class="text-dark">Location</span></th>
                    <th><span class="text-dark">{{ internetHistoryTypeAbbreviationToFullName('NR')['full'] }}</th>
                    <th><span class="text-dark">{{ internetHistoryTypeAbbreviationToFullName('CL')['full'] }}</th>
                    <th><span class="text-dark">{{ internetHistoryTypeAbbreviationToFullName('TS')['full'] }}</th>
                    <th><span class="text-dark">{{ internetHistoryTypeAbbreviationToFullName('RC')['full'] }}</th>
                    <th><span class="text-dark">Change Owner</th>
                    <th><span class="text-dark">{{ internetHistoryTypeAbbreviationToFullName('MP')['full'] }}</th>
                    <th><span class="text-dark">Total</span></th>
                  </tr>
                </thead>
                <tbody>
                  @if(is_array($reportArr) && count($reportArr)>0)
                    @foreach($reportArr as $rkey=>$rval)
                      <tr>
                        <td>{{ $rkey }}</td>
                        <td>{{ $rval['NR'] }}</td>                    
                        <td>{{ $rval['CL'] }}</td>
                        <td>{{ $rval['TS'] }}</td>
                        <td>{{ $rval['RC'] }}</td>
                        <td>{{ $rval['CH'] }}</td>
                        <td>{{ $rval['MP'] }}</td>
                        <td style="font-weight:bold;">{{ ($rval['NR']+$rval['CL']+$rval['TS']+$rval['RC']+$rval['CH']+$rval['MP']) }}</td>
                      </tr>
                    @endforeach
                  @endif 
                </tbody>
                <tfoot>                  
                  <tr>
                    <th><span class="text-dark">Grand Total</span></th>
                    <th class="text-{{ internetHistoryTypeAbbreviationToFullName('NR')['color'] }}"></th>
                    <th class="text-{{ internetHistoryTypeAbbreviationToFullName('CL')['color'] }}"></th>
                    <th class="text-{{ internetHistoryTypeAbbreviationToFullName('TS')['color'] }}"></th>
                    <th class="text-{{ internetHistoryTypeAbbreviationToFullName('RC')['color'] }}"></th>
                    <th class="text-primary"></th>
                    <th class="text-{{ internetHistoryTypeAbbreviationToFullName('MP')['color'] }}"></th>
                    <th style="font-weight:bold;"></th>
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
  
  var type = "{{ $type }}";
  var year = $('#SelectYear option:selected').text();
  var quarter = $('#SelectQuarter option:selected').text();
  var month = $('#SelectMonth option:selected').text();
  var reportTitle = '';

  if(type=="Q"){
    reportTitle='Quarterly ( '+quarter+'-'+year+') Report';
  }else if(type=="M"){
    reportTitle='Monthly ('+month+'-'+year+') Report';
  }else{
    reportTitle='Yearly('+year+') Report';
  }

  $("#cabletv-report").DataTable({
    "responsive": true,
    "paging": false,
    "searching": false,
    "ordering": false,
    "dom": 'Bfrtip',
    "buttons": [
      {
        "extend":'excel',
        "text": '<i class="fa fa-file-excel"></i>',
        "titleAttr": 'Excel',
        "title": 'Cable TV '+reportTitle,
        "footer": true
      },
      {
        "extend":'pdf',
        "text": '<i class="fa fa-file-pdf"></i>',
        "titleAttr": 'Pdf',
        "title": 'Cable TV '+reportTitle,
        "footer": true,
        // "orientation": 'landscape',
        "customize": function(doc) {
          doc.defaultStyle.alignment = 'center';
        }
      },
      {
        "extend":'print',
        "text": '<i class="fa fa-print"></i>',
        "titleAttr": 'Print',
        "title": 'Cable TV '+reportTitle,
        "footer": true,
        // "autoPrint": false,
        "customize": function(win) {
          $(win.document.body).find( 'table' ).css( 'text-align', 'center' );
        }
      },
    ],
    "footerCallback": function (tfoot, data, start, end, display) {                
      for(var i = 1; i < 8; i++){
        var totalAmount = 0;
        for (var j = 0; j < data.length; j++) {
          totalAmount += parseInt(data[j][i]);
        }
        $(tfoot).find('th').eq(i).text(totalAmount);
      }
    }
  });
})

$( document ).ready(function() {
  
  var type= $("input[name='type']:checked").val();
  openBoxByType(type);

});

function openBoxByType(type)
{
  if(type=="M"){
    $('#quarterlyDiv').hide();
    $('#monthlyDiv').show();
  }else if(type=="Q"){
    $('#monthlyDiv').hide();
    $('#quarterlyDiv').show();
  }else{
    $('#quarterlyDiv').hide();
    $('#monthlyDiv').hide();
  }  
}  
</script>
@endsection