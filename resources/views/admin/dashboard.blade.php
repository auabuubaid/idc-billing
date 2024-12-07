@extends('admin.layout.master')

@section('content')

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <div class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6">
          <h1 class="m-0 text-dark">Dashboard</h1>
        </div><!-- /.col -->
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item active">Dashboard</li>
          </ol>
        </div><!-- /.col -->
      </div><!-- /.row -->
    </div><!-- /.container-fluid -->
  </div>
  <!-- /.content-header -->
  <!-- Main content -->
  <section class="content">
    <div class="container-fluid">
      <!-- Small boxes (Stat box) -->
      <div class="row">
        <div class="col-lg-3 col-6">
          <!-- small box -->
          <div class="small-box bg-info">
            <div class="inner">
              <h3>{{ totalActiveWithSuspendInternetSubscribers() }}</h3>
              <p>Total Active (including suspended)<br/>Internet Subscribers</p>
            </div>
            <div class="icon">
              <i class="ion-ios-people"></i>
            </div>
          </div>
        </div>
        <!-- ./col -->
        <div class="col-lg-3 col-6">
          <!-- small box -->
          <div class="small-box bg-success">
            <div class="inner">
              <h3>{{ totalInternetAmountTIllToday()['total_amount'] }} <sup style="font-size: 20px">$</sup></h3>
              <p>Monthly Internet Revenue<br/><small>( {{ totalInternetAmountTIllToday()['startDate'] }} - {{ totalInternetAmountTIllToday()['endDate'] }})</small></p>
            </div>
            <div class="icon">
              <i class="ion ion-cash"></i>
            </div>              
          </div>
        </div>
        <!-- ./col -->
        <div class="col-lg-3 col-6">
          <!-- small box -->
          <div class="small-box bg-primary">
            <div class="inner">
              <h3>{{ totalActiveCableTVSubscribers() }}</h3>
              <p>Total Active<br/>Cable TV Subscribers</p>
            </div>
            <div class="icon">
              <i class="ion-ios-people"></i>
            </div>
          </div>
        </div>
        <!-- ./col -->
        <div class="col-lg-3 col-6">
          <!-- small box -->
          <div class="small-box bg-success">
            <div class="inner">
              <h3>{{ totalCableTVAmountMonthly()['total_amount'] }}<sup style="font-size: 20px">$</sup></h3>
              <p>Monthly Cable TV Revenue<br/><small>( {{ totalCableTVAmountMonthly()['startDate'] }} - {{ totalCableTVAmountMonthly()['endDate'] }})</small></p>
            </div>
            <div class="icon">
              <i class="ion ion-cash"></i>
            </div>              
          </div>
        </div>
        <!-- ./col -->          
      </div>
      <!-- /.row -->
      <!-- Main row -->
      <div class="row">
        <!-- Left col -->
        <section class="col-lg-12">
          <!-- Custom tabs (Charts with tabs)-->
          <div class="card">
            <div class="card-header">
              <h3 class="card-title pt-2">
                <i class="fas fa-chart-bar mr-1"></i>
                Sales
              </h3>
              <div class="card-tools">
                <ul class="nav nav-pills ml-auto">
                  <li class="nav-item">
                    <a class="nav-link active" href="#revenue-monthly" data-toggle="tab">Monthly</a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link" href="#revenue-yearly" data-toggle="tab">Yearly</a>
                  </li>
                </ul>
              </div>
            </div><!-- /.card-header -->
            <div class="card-body">
              <div class="tab-content p-0">
                <!-- Morris chart - Sales -->
                <div class="chart tab-pane active" id="revenue-monthly" style="height: 500px;">
                  <canvas id="pieChartMonthly" height="90" style="height: 90px;"></canvas>
                  <p style="position:absolute; top: 90%; left: 70%; transform: translate(-50%, -50%);">(* Amount in USD)</p>                       
                </div>
                <div class="chart tab-pane" id="revenue-yearly" style="height: 500px;">
                  <canvas id="barChartYearly" height="90" style="height: 90px;"></canvas>     
                  <p style="position:absolute; top: 2%; left: 91%;">(* Amount in USD)</p>                    
                </div>  
              </div>
            </div><!-- /.card-body -->
          </div>
          <!-- /.card -->
        </section>
        <!-- /.col -->
      </div>
      <!-- /.row (main row) -->
    </div><!-- /.container-fluid -->
  </section>
  <!-- /.content -->
</div>
<!-- /.content-wrapper -->
<!-- ChartJS -->
<script src="{{ asset('assets/plugins/chart.js/Chart.min.js') }}"></script>
<!-- page script -->
<script>
  $(function () {
    /* ChartJS
     * -------
     * Here we will create a few charts using ChartJS
     */
    
    //-------------
    //- PIE CHART -
    //-------------
    // Get context with jQuery - using jQuery's .get() method.
    var monthlyData        = {
      labels: [
          'Internet', 
          'Cable TV',
      ],
      datasets: [
        {
          data: [{{ totalInternetYearlyRevenue(currentMonth()) }},{{ cableTVYearlyRevenue(currentMonth()) }}],
          backgroundColor : ['#ffc107', '#007bff'],
        }
      ]
    }

    var pieChartCanvasMonthly = $('#pieChartMonthly').get(0).getContext('2d')
    var pieDataMonthly        = monthlyData;
    var pieOptionsMonthly     = {
      maintainAspectRatio : true,
      responsive : true,
    }
    //Create pie or douhnut chart
    // You can switch between pie and douhnut using the method below.
    var pieChartMonthly = new Chart(pieChartCanvasMonthly, {
      type: 'pie',
      data: pieDataMonthly,
      options: pieOptionsMonthly      
    })

    //-------------
    //- BAR CHART -
    //-------------

    var yearlyChartData = {
      labels  : ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
      datasets: [
        {
          label               : 'Internet',
          backgroundColor     : '#ffc107',
          borderColor         : 'rgba(60,141,188,0.8)',
          pointRadius          : false,
          pointColor          : '#ffc107',
          pointStrokeColor    : 'rgba(60,141,188,1)',
          pointHighlightFill  : '#fff',
          pointHighlightStroke: 'rgba(60,141,188,1)',
          data                : [{{ totalInternetYearlyRevenue(1) }}, {{ totalInternetYearlyRevenue(2) }}, {{ totalInternetYearlyRevenue(3) }}, {{ totalInternetYearlyRevenue(4) }}, {{ totalInternetYearlyRevenue(5) }}, {{ totalInternetYearlyRevenue(6) }}, {{ totalInternetYearlyRevenue(7) }}, {{ totalInternetYearlyRevenue(8) }}, {{ totalInternetYearlyRevenue(9) }}, {{ totalInternetYearlyRevenue(10) }}, {{ totalInternetYearlyRevenue(11) }}, {{ totalInternetYearlyRevenue(12) }}]
        },
        {
          label               : 'Cable TV',
          backgroundColor     : '#007bff',
          borderColor         : 'rgba(60,141,188,0.8)',
          pointRadius          : false,
          pointColor          : '#007bff',
          pointStrokeColor    : 'rgba(60,141,188,1)',
          pointHighlightFill  : '#fff',
          pointHighlightStroke: 'rgba(60,141,188,1)',
          data                : [{{ cableTVYearlyRevenue(1) }}, {{ cableTVYearlyRevenue(2) }}, {{ cableTVYearlyRevenue(3) }}, {{ cableTVYearlyRevenue(4) }}, {{ cableTVYearlyRevenue(5) }}, {{ cableTVYearlyRevenue(6) }}, {{ cableTVYearlyRevenue(7) }}, {{ cableTVYearlyRevenue(8) }}, {{ cableTVYearlyRevenue(9) }}, {{ cableTVYearlyRevenue(10) }}, {{ cableTVYearlyRevenue(11) }}, {{ cableTVYearlyRevenue(12) }}]
        },        
      ]
    }


    var barChartYearlyCanvas = $('#barChartYearly').get(0).getContext('2d')
    var barChartYearlyData = jQuery.extend(true, {}, yearlyChartData)
    var temp0 = yearlyChartData.datasets[0]
    var temp1 = yearlyChartData.datasets[1]

    barChartYearlyData.datasets[0] = temp1
    barChartYearlyData.datasets[1] = temp0

    var barChartOptions = {
      responsive              : true,
      maintainAspectRatio     : true,
      datasetFill             : false
    }

    var barChart = new Chart(barChartYearlyCanvas, {
      type: 'bar', 
      data: barChartYearlyData,
      options: barChartOptions
    })

  })
</script>
@endsection
