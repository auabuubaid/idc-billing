@extends('users.layout.master')

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
              <h3>{{ totalActiveInternetSubscribers() }} | {{ totalActiveWithSuspendInternetSubscribers() }} <a href="{{ route('all-active-internet-subscribers') }}" class="text-white"><i class="fas fa-file-download"></i></a> <sup style="font-size: 14px">Suspend Included</sup></h3>
              <h4>Total Active<br/><small>Internet Subscribers</small></h4>
            </div>
            <div class="icon">
              <i class="ion ion-wifi"></i>
            </div>
          </div>
        </div>
        <!-- ./col -->
        <div class="col-lg-3 col-6">
          <!-- small box -->
          <div class="small-box bg-success">
            <div class="inner">
              <h3><sup style="font-size: 20px">$</sup>{{ totalInternetAmountTIllToday()['total_amount'] }}</h3>
              <h4>Revenue Monthly<br/><small>( {{ totalInternetAmountTIllToday()['startDate'] }} - {{ totalInternetAmountTIllToday()['endDate'] }})</small></h4>
            </div>
            <div class="icon">
              <i class="ion ion-cash"></i>
            </div>
          </div>
        </div>
        <!-- ./col -->
        <div class="col-lg-3 col-6">
          <div class="small-box bg-gray">
            <div class="inner">
              <h3>{{ totalActiveCableTVSubscribers() }}</h3>
              <h4>Total<br/><small> Active CableTV Subscribers </small></h4>
            </div>
            <div class="icon">
              <i class="ion ion-monitor"></i>
            </div>
          </div>
        </div>
        <!-- ./col -->
        <div class="col-lg-3 col-6">
          <div class="small-box bg-purple">
            <div class="inner">
              <h3><sup style="font-size: 20px">$</sup>{{ totalCableTVAmountMonthly()['total_amount'] }}</h3>
              <h4>Revenue Monthly<br/><small>( {{ totalCableTVAmountMonthly()['startDate'] }} - {{ totalCableTVAmountMonthly()['endDate'] }})</small></h4>
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
        <section class="col-lg-6 connectedSortable">
          <!-- Custom tabs (Charts with tabs)-->
          <div class="card">
            <div class="card-header">
              <h3 class="card-title pt-2">
                <i class="fas fa-wifi mr-1"></i>
                Internet
              </h3>
              <div class="card-tools">
                <ul class="nav nav-pills ml-auto">
                  <li class="nav-item">
                      <a class="nav-link active" href="#internet-active-subscribers" data-toggle="tab">Active Subscribers</a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link" href="#internet-summary-report" data-toggle="tab">Summary</a>
                  </li>                    
                  <li class="nav-item">
                    <a class="nav-link" href="#internet-sales-monthly" data-toggle="tab">Monthly</a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link" href="#internet-sales-yearly" data-toggle="tab">Yearly</a>
                  </li>
                </ul>
              </div>
            </div><!-- /.card-header -->
            <div class="card-body">
              <div class="tab-content p-0">
                <!-- Morris chart - Sales -->
                <div class="chart tab-pane active" id="internet-active-subscribers" style="position: relative; height: 281px;">
                  <div class="card-body table-responsive p-0">
                    <table class="table table-head-fixed table-bordered table-striped" style="text-align:center">
                      <thead>                  
                        <tr>
                          <th><span title="Apartment"><i class="fas fa-city"></i> <br>Apartment</span></th>
                          <th><span title="Town House"><i class="fas fa-hospital"></i> <br>Town House</span></th>
                          <th><span title="Villa"><i class="fas fa-hotel"></i> <br>R1-Villa</span></th>
                          <th><span title="Shops"><i class="fas fa-store"></i> <br>Shops</span></th>
                          <th><span title="Secret Garden"><i class="fab fa-fort-awesome"></i> <br>Secret Garden</span></th>
                          <th><span title="Total"><i class="fas fa-grip-lines"></i> <br>Total</span></th>
                        </tr>
                      </thead>
                      <tfoot>
                        <tr>
                          <th class="text-primary">{{ totalSubscribersCountByBuildingType('R1','A') }}</th>
                          <th class="text-primary">{{ totalSubscribersCountByBuildingType('R1','T') }}</th>
                          <th class="text-primary">{{ totalSubscribersCountByBuildingType('R1','V') }}</th>
                          <th class="text-primary">{{ totalSubscribersCountByBuildingType('R1','S') }}</th>
                          <th class="text-primary">{{ totalSubscribersCountByBuildingType('R2','V') }}</th>
                          <th class="text-danger">{{ (totalSubscribersCountByBuildingType('R1','A')+totalSubscribersCountByBuildingType('R1','T')+totalSubscribersCountByBuildingType('R1','V')+totalSubscribersCountByBuildingType('R1','S')+totalSubscribersCountByBuildingType('R2','V')) }}</th>
                        </tr>                          
                      </tfoot>
                    </table>          
                  </div>            
                </div>
                <div class="chart tab-pane" id="internet-summary-report" style="position: relative; height: 281px;">
                  <div class="card-body table-responsive p-0" style="height: 281px;">
                    <table class="table table-head-fixed table-bordered table-striped" style="text-align:center" id="internet-summary">
                      <thead>                  
                        <tr>
                          <th><span class="text-orange" title="Locations"><i class="fas fa-city"></i></span></th>
                          <th><span class="text-{{ internetHistoryTypeAbbreviationToFullName('NR')['color'] }}" title="{{ internetHistoryTypeAbbreviationToFullName('NR')['full'] }}"><i class="{{ internetHistoryTypeAbbreviationToFullName('NR')['icon'] }}"></i></th>
                          <th><span class="text-{{ internetHistoryTypeAbbreviationToFullName('MP')['color'] }}" title="{{ internetHistoryTypeAbbreviationToFullName('MP')['full'] }}"><i class="{{ internetHistoryTypeAbbreviationToFullName('MP')['icon'] }}"></i></th>
                          <th><span class="text-{{ internetHistoryTypeAbbreviationToFullName('CP')['color'] }}" title="{{ internetHistoryTypeAbbreviationToFullName('CP')['full'] }}"><i class="{{ internetHistoryTypeAbbreviationToFullName('CP')['icon'] }}"></i></th>
                          <th><span class="text-{{ internetHistoryTypeAbbreviationToFullName('CL')['color'] }}" title="{{ internetHistoryTypeAbbreviationToFullName('CL')['full'] }}"><i class="{{ internetHistoryTypeAbbreviationToFullName('CL')['icon'] }}"></i></th>
                          <th><span class="text-{{ internetHistoryTypeAbbreviationToFullName('SS')['color'] }}" title="{{ internetHistoryTypeAbbreviationToFullName('SS')['full'] }}"><i class="{{ internetHistoryTypeAbbreviationToFullName('SS')['icon'] }}"></i></th>
                          <th><span class="text-{{ internetHistoryTypeAbbreviationToFullName('RC')['color'] }}" title="{{ internetHistoryTypeAbbreviationToFullName('RC')['full'] }}"><i class="{{ internetHistoryTypeAbbreviationToFullName('RC')['icon'] }}"></i></th>
                          <th><span class="text-{{ internetHistoryTypeAbbreviationToFullName('TS')['color'] }}" title="{{ internetHistoryTypeAbbreviationToFullName('TS')['full'] }}"><i class="{{ internetHistoryTypeAbbreviationToFullName('TS')['icon'] }}"></i></th>
                          <th><span class="text-teal" title="Total"><i class="fas fa-grip-lines"></i></span></th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr>
                          <td>A101</td>
                          <td>{{ totalCustomerCountByBuildingName('A101','NR') }}</td>
                          <td>{{ totalCustomerCountByBuildingName('A101','MP') }}</td>
                          <td>{{ totalCustomerCountByBuildingName('A101','CP') }}</td>
                          <td>{{ totalCustomerCountByBuildingName('A101','CL') }}</td>
                          <td>{{ totalCustomerCountByBuildingName('A101','SS') }}</td>
                          <td>{{ totalCustomerCountByBuildingName('A101','RC') }}</td>
                          <td>{{ totalCustomerCountByBuildingName('A101','TS') }}</td>
                          <td></td>
                        </tr>
                        <tr>
                          <td>A102</td>
                          <td>{{ totalCustomerCountByBuildingName('A102','NR') }}</td>
                          <td>{{ totalCustomerCountByBuildingName('A102','MP') }}</td>
                          <td>{{ totalCustomerCountByBuildingName('A102','CP') }}</td>
                          <td>{{ totalCustomerCountByBuildingName('A102','CL') }}</td>
                          <td>{{ totalCustomerCountByBuildingName('A102','SS') }}</td>
                          <td>{{ totalCustomerCountByBuildingName('A102','RC') }}</td>
                          <td>{{ totalCustomerCountByBuildingName('A102','TS') }}</td>
                          <td></td>
                        </tr>
                        <tr>
                          <td>A103</td>
                          <td>{{ totalCustomerCountByBuildingName('A103','NR') }}</td>
                          <td>{{ totalCustomerCountByBuildingName('A103','MP') }}</td>
                          <td>{{ totalCustomerCountByBuildingName('A103','CP') }}</td>
                          <td>{{ totalCustomerCountByBuildingName('A103','CL') }}</td>
                          <td>{{ totalCustomerCountByBuildingName('A103','SS') }}</td>
                          <td>{{ totalCustomerCountByBuildingName('A103','RC') }}</td>
                          <td>{{ totalCustomerCountByBuildingName('A103','TS') }}</td>
                          <td></td>
                        </tr>
                        <tr>
                          <td>A104</td>
                          <td>{{ totalCustomerCountByBuildingName('A104','NR') }}</td>
                          <td>{{ totalCustomerCountByBuildingName('A104','MP') }}</td>
                          <td>{{ totalCustomerCountByBuildingName('A104','CP') }}</td>
                          <td>{{ totalCustomerCountByBuildingName('A104','CL') }}</td>
                          <td>{{ totalCustomerCountByBuildingName('A104','SS') }}</td>
                          <td>{{ totalCustomerCountByBuildingName('A104','RC') }}</td>
                          <td>{{ totalCustomerCountByBuildingName('A104','TS') }}</td>
                          <td></td>
                        </tr>
                        <tr>
                          <td>A105</td>
                          <td>{{ totalCustomerCountByBuildingName('A105','NR') }}</td>
                          <td>{{ totalCustomerCountByBuildingName('A105','MP') }}</td>
                          <td>{{ totalCustomerCountByBuildingName('A105','CP') }}</td>
                          <td>{{ totalCustomerCountByBuildingName('A105','CL') }}</td>
                          <td>{{ totalCustomerCountByBuildingName('A105','SS') }}</td>
                          <td>{{ totalCustomerCountByBuildingName('A105','RC') }}</td>
                          <td>{{ totalCustomerCountByBuildingName('A105','TS') }}</td>
                          <td></td>
                        </tr>
                        <tr>
                          <td>A106</td>
                          <td>{{ totalCustomerCountByBuildingName('A106','NR') }}</td>
                          <td>{{ totalCustomerCountByBuildingName('A106','MP') }}</td>
                          <td>{{ totalCustomerCountByBuildingName('A106','CP') }}</td>
                          <td>{{ totalCustomerCountByBuildingName('A106','CL') }}</td>
                          <td>{{ totalCustomerCountByBuildingName('A106','SS') }}</td>
                          <td>{{ totalCustomerCountByBuildingName('A106','RC') }}</td>
                          <td>{{ totalCustomerCountByBuildingName('A106','TS') }}</td>
                          <td></td>
                        </tr>
                        <tr>
                          <td>A107</td>
                          <td>{{ totalCustomerCountByBuildingName('A107','NR') }}</td>
                          <td>{{ totalCustomerCountByBuildingName('A107','MP') }}</td>
                          <td>{{ totalCustomerCountByBuildingName('A107','CP') }}</td>
                          <td>{{ totalCustomerCountByBuildingName('A107','CL') }}</td>
                          <td>{{ totalCustomerCountByBuildingName('A107','SS') }}</td>
                          <td>{{ totalCustomerCountByBuildingName('A107','RC') }}</td>
                          <td>{{ totalCustomerCountByBuildingName('A107','TS') }}</td>
                          <td></td>
                        </tr>
                        <tr>
                          <td>A108</td>
                          <td>{{ totalCustomerCountByBuildingName('A108','NR') }}</td>
                          <td>{{ totalCustomerCountByBuildingName('A108','MP') }}</td>
                          <td>{{ totalCustomerCountByBuildingName('A108','CP') }}</td>
                          <td>{{ totalCustomerCountByBuildingName('A108','CL') }}</td>
                          <td>{{ totalCustomerCountByBuildingName('A108','SS') }}</td>
                          <td>{{ totalCustomerCountByBuildingName('A108','RC') }}</td>
                          <td>{{ totalCustomerCountByBuildingName('A108','TS') }}</td>
                          <td></td>
                        </tr>
                        <tr>
                          <td>THouse</td>
                          <td>{{ totalCustomerCountByBuildingType('R1','T','NR') }}</td>
                          <td>{{ totalCustomerCountByBuildingType('R1','T','MP') }}</td>
                          <td>{{ totalCustomerCountByBuildingType('R1','T','CP') }}</td>
                          <td>{{ totalCustomerCountByBuildingType('R1','T','CL') }}</td>
                          <td>{{ totalCustomerCountByBuildingType('R1','T','SS') }}</td>
                          <td>{{ totalCustomerCountByBuildingType('R1','T','RC') }}</td>
                          <td>{{ totalCustomerCountByBuildingType('R1','T','TS') }}</td>
                          <td></td>
                        </tr>
                        <tr>
                          <td>Shops</td>
                          <td>{{ totalCustomerCountByBuildingType('R1','S','NR') }}</td>
                          <td>{{ totalCustomerCountByBuildingType('R1','S','MP') }}</td>
                          <td>{{ totalCustomerCountByBuildingType('R1','S','CP') }}</td>
                          <td>{{ totalCustomerCountByBuildingType('R1','S','CL') }}</td>
                          <td>{{ totalCustomerCountByBuildingType('R1','S','SS') }}</td>
                          <td>{{ totalCustomerCountByBuildingType('R1','S','RC') }}</td>
                          <td>{{ totalCustomerCountByBuildingType('R1','S','TS') }}</td>
                          <td></td>
                        </tr>
                        <tr>
                          <td>R1-Villa</td>
                          <td>{{ totalCustomerCountByBuildingType('R1','V','NR') }}</td>
                          <td>{{ totalCustomerCountByBuildingType('R1','V','MP') }}</td>
                          <td>{{ totalCustomerCountByBuildingType('R1','V','CP') }}</td>
                          <td>{{ totalCustomerCountByBuildingType('R1','V','CL') }}</td>
                          <td>{{ totalCustomerCountByBuildingType('R1','V','SS') }}</td>
                          <td>{{ totalCustomerCountByBuildingType('R1','V','RC') }}</td>
                          <td>{{ totalCustomerCountByBuildingType('R1','V','TS') }}</td>
                          <td></td>
                        </tr>
                        <tr>
                          <td>R2-Villa</td>
                          <td>{{ totalCustomerCountByBuildingType('R2','V','NR') }}</td>
                          <td>{{ totalCustomerCountByBuildingType('R2','V','MP') }}</td>
                          <td>{{ totalCustomerCountByBuildingType('R2','V','CP') }}</td>
                          <td>{{ totalCustomerCountByBuildingType('R2','V','CL') }}</td>
                          <td>{{ totalCustomerCountByBuildingType('R2','V','SS') }}</td>
                          <td>{{ totalCustomerCountByBuildingType('R2','V','RC') }}</td>
                          <td>{{ totalCustomerCountByBuildingType('R2','V','TS') }}</td>
                          <td></td>
                        </tr>
                      </tbody>
                      <tfoot>                  
                        <tr>
                          <th><span class="text-dark" title="Total"><i class="fas fa-grip-lines"></i></span></th>
                          <th class="text-{{ internetHistoryTypeAbbreviationToFullName('NR')['color'] }}">{{ totalCustomerCountByMonth('NR') }}</th>
                          <th class="text-{{ internetHistoryTypeAbbreviationToFullName('MP')['color'] }}">{{ totalCustomerCountByMonth('MP') }}</th>
                          <th class="text-{{ internetHistoryTypeAbbreviationToFullName('CP')['color'] }}">{{ totalCustomerCountByMonth('CP') }}</th>
                          <th class="text-{{ internetHistoryTypeAbbreviationToFullName('CL')['color'] }}">{{ totalCustomerCountByMonth('CL') }}</th>
                          <th class="text-{{ internetHistoryTypeAbbreviationToFullName('SS')['color'] }}">{{ totalCustomerCountByMonth('SS') }}</th>
                          <th class="text-{{ internetHistoryTypeAbbreviationToFullName('RC')['color'] }}">{{ totalCustomerCountByMonth('RC') }}</th>
                          <th class="text-{{ internetHistoryTypeAbbreviationToFullName('TS')['color'] }}">{{ totalCustomerCountByMonth('TS') }}</th>
                          <th>{{ (totalCustomerCountByMonth('NR')+totalCustomerCountByMonth('MP')+totalCustomerCountByMonth('CP')+totalCustomerCountByMonth('CL')+totalCustomerCountByMonth('SS')+totalCustomerCountByMonth('RC')+totalCustomerCountByMonth('TS')) }}</th>
                        </tr>
                      </tfoot>
                    </table>     
                  </div>                   
                </div>                  
                <div class="chart tab-pane" id="internet-sales-monthly" style="position: relative; height: 281px;">
                  <canvas id="pieChartMonthly" height="281" style="height: 281px;"></canvas>                         
                </div>
                <div class="chart tab-pane" id="internet-sales-yearly" style="position: relative; height: 281px;">
                  <canvas id="barChartYearly" height="281" style="height: 281px;"></canvas>                         
                </div>
              </div>
            </div><!-- /.card-body -->
          </div>
          <!-- /.card -->
        </section>
        <!-- /.Left col -->
        <!-- right col (We are only adding the ID to make the widgets sortable)-->
        <section class="col-lg-6 connectedSortable">
          <div class="card">
            <div class="card-header">
              <h3 class="card-title pt-2">
                <i class="fas fa-tv mr-1"></i>
                Cable TV
              </h3>
              <div class="card-tools">
                <ul class="nav nav-pills ml-auto">
                  <li class="nav-item">
                      <a class="nav-link active" href="#cabletv-active-subscribers" data-toggle="tab">Active Subscribers</a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link" href="#cabletv-summary-report" data-toggle="tab">Summary</a>
                  </li>                    
                  <li class="nav-item">
                    <a class="nav-link" href="#cabletv-sales-monthly" data-toggle="tab">Monthly</a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link" href="#cabletv-sales-yearly" data-toggle="tab">Yearly</a>
                  </li>                    
                </ul>
              </div>
            </div>
            <div class="card-body">
              <div class="tab-content p-0">
                <div class="chart tab-pane active" id="cabletv-active-subscribers" style="position: relative; height: 281px;">
                  <div class="card-body table-responsive p-0">
                    <table class="table table-head-fixed table-bordered table-striped" style="text-align:center" id="cabletv-subscribers">
                      <thead>                  
                        <tr>
                          <th><span title="Apartment"><i class="fas fa-city"></i> <br>Apartment</span></th>
                          <th><span title="Town House"><i class="fas fa-hospital"></i> <br>Town House</span></th>
                          <th><span title="Villa"><i class="fas fa-hotel"></i> <br>R1-Villa</span></th>
                          <th><span title="Shops"><i class="fas fa-store"></i> <br>Shops</span></th>
                          <th><span title="Secret Garden"><i class="fab fa-fort-awesome"></i> <br>Secret Garden</span></th>
                          <th><span title="Total"><i class="fas fa-grip-lines"></i> <br>Total</span></th>
                        </tr>
                      </thead>
                      <tfoot>
                        <tr>
                          <th class="text-primary">{{ activeCableTVSubscribersByBuildingType('R1','A') }}</th>
                          <th class="text-primary">{{ activeCableTVSubscribersByBuildingType('R1','T') }}</th>
                          <th class="text-primary">{{ activeCableTVSubscribersByBuildingType('R1','V') }}</th>
                          <th class="text-primary">{{ activeCableTVSubscribersByBuildingType('R1','S') }}</th>
                          <th class="text-primary">{{ activeCableTVSubscribersByBuildingType('R2','V') }}</th>
                          <th class="text-danger">{{ (activeCableTVSubscribersByBuildingType('R1','A')+activeCableTVSubscribersByBuildingType('R1','T')+activeCableTVSubscribersByBuildingType('R1','V')+activeCableTVSubscribersByBuildingType('R1','S')+activeCableTVSubscribersByBuildingType('R2','V')) }}</th>
                        </tr>                          
                      </tfoot>
                    </table>  
                  </div>                       
                </div>
                <div class="chart tab-pane" id="cabletv-summary-report" style="position: relative; height: 281px;">
                  <div class="card-body table-responsive p-0" style="height: 281px;">
                    <table class="table table-head-fixed table-bordered table-striped" style="text-align:center" id="cabletv-summary">
                      <thead>                  
                        <tr>
                          <th><span class="text-orange" title="Locations"><i class="fas fa-city"></i></span></th>
                          <th><span class="text-{{ internetHistoryTypeAbbreviationToFullName('NR')['color'] }}" title="{{ internetHistoryTypeAbbreviationToFullName('NR')['full'] }}"><i class="{{ internetHistoryTypeAbbreviationToFullName('NR')['icon'] }}"></i></th>
                          <th><span class="text-{{ internetHistoryTypeAbbreviationToFullName('CL')['color'] }}" title="{{ internetHistoryTypeAbbreviationToFullName('CL')['full'] }}"><i class="{{ internetHistoryTypeAbbreviationToFullName('CL')['icon'] }}"></i></th>
                          <th><span class="text-{{ internetHistoryTypeAbbreviationToFullName('RC')['color'] }}" title="{{ internetHistoryTypeAbbreviationToFullName('RC')['full'] }}"><i class="{{ internetHistoryTypeAbbreviationToFullName('RC')['icon'] }}"></i></th>
                          <th><span class="text-{{ internetHistoryTypeAbbreviationToFullName('TS')['color'] }}" title="{{ internetHistoryTypeAbbreviationToFullName('TS')['full'] }}"><i class="{{ internetHistoryTypeAbbreviationToFullName('TS')['icon'] }}"></i></th>
                          <th><span class="text-{{ internetHistoryTypeAbbreviationToFullName('MP')['color'] }}" title="{{ internetHistoryTypeAbbreviationToFullName('MP')['full'] }}"><i class="{{ internetHistoryTypeAbbreviationToFullName('MP')['icon'] }}"></i></th>
                          <th><span class="text-teal" title="Total"><i class="fas fa-grip-lines"></i></span></th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr>
                          <td>A101</td>
                          <td>{{ totalCableTVCustomerCountByBuildingName('A101','NR') }}</td>
                          <td>{{ totalCableTVCustomerCountByBuildingName('A101','CL') }}</td>
                          <td>{{ totalCableTVCustomerCountByBuildingName('A101','RC') }}</td>
                          <td>{{ totalCableTVCustomerCountByBuildingName('A101','TS') }}</td>
                          <td>{{ totalCableTVCustomerCountByBuildingName('A101','MP') }}</td>
                          <td></td>
                        </tr>
                        <tr>
                          <td>A102</td>
                          <td>{{ totalCableTVCustomerCountByBuildingName('A102','NR') }}</td>
                          <td>{{ totalCableTVCustomerCountByBuildingName('A102','CL') }}</td>
                          <td>{{ totalCableTVCustomerCountByBuildingName('A102','RC') }}</td>
                          <td>{{ totalCableTVCustomerCountByBuildingName('A102','TS') }}</td>
                          <td>{{ totalCableTVCustomerCountByBuildingName('A102','MP') }}</td>
                          <td></td>
                        </tr>
                        <tr>
                          <td>A103</td>
                          <td>{{ totalCableTVCustomerCountByBuildingName('A103','NR') }}</td>
                          <td>{{ totalCableTVCustomerCountByBuildingName('A103','CL') }}</td>
                          <td>{{ totalCableTVCustomerCountByBuildingName('A103','RC') }}</td>
                          <td>{{ totalCableTVCustomerCountByBuildingName('A103','TS') }}</td>
                          <td>{{ totalCableTVCustomerCountByBuildingName('A103','MP') }}</td>
                          <td></td>
                        </tr>
                        <tr>
                          <td>A104</td>
                          <td>{{ totalCableTVCustomerCountByBuildingName('A104','NR') }}</td>
                          <td>{{ totalCableTVCustomerCountByBuildingName('A104','CL') }}</td>
                          <td>{{ totalCableTVCustomerCountByBuildingName('A104','RC') }}</td>
                          <td>{{ totalCableTVCustomerCountByBuildingName('A104','TS') }}</td>
                          <td>{{ totalCableTVCustomerCountByBuildingName('A104','MP') }}</td>
                          <td></td>
                        </tr>
                        <tr>
                          <td>A105</td>
                          <td>{{ totalCableTVCustomerCountByBuildingName('A105','NR') }}</td>
                          <td>{{ totalCableTVCustomerCountByBuildingName('A105','CL') }}</td>
                          <td>{{ totalCableTVCustomerCountByBuildingName('A105','RC') }}</td>
                          <td>{{ totalCableTVCustomerCountByBuildingName('A105','TS') }}</td>
                          <td>{{ totalCableTVCustomerCountByBuildingName('A105','MP') }}</td>
                          <td></td>
                        </tr>
                        <tr>
                          <td>A106</td>
                          <td>{{ totalCableTVCustomerCountByBuildingName('A106','NR') }}</td>
                          <td>{{ totalCableTVCustomerCountByBuildingName('A106','CL') }}</td>
                          <td>{{ totalCableTVCustomerCountByBuildingName('A106','RC') }}</td>
                          <td>{{ totalCableTVCustomerCountByBuildingName('A106','TS') }}</td>
                          <td>{{ totalCableTVCustomerCountByBuildingName('A106','MP') }}</td>
                          <td></td>
                        </tr>
                        <tr>
                          <td>A107</td>
                          <td>{{ totalCableTVCustomerCountByBuildingName('A107','NR') }}</td>
                          <td>{{ totalCableTVCustomerCountByBuildingName('A107','CL') }}</td>
                          <td>{{ totalCableTVCustomerCountByBuildingName('A107','RC') }}</td>
                          <td>{{ totalCableTVCustomerCountByBuildingName('A107','TS') }}</td>
                          <td>{{ totalCableTVCustomerCountByBuildingName('A107','MP') }}</td>
                          <td></td>
                        </tr>
                        <tr>
                          <td>A108</td>
                          <td>{{ totalCableTVCustomerCountByBuildingName('A108','NR') }}</td>
                          <td>{{ totalCableTVCustomerCountByBuildingName('A108','CL') }}</td>
                          <td>{{ totalCableTVCustomerCountByBuildingName('A108','RC') }}</td>
                          <td>{{ totalCableTVCustomerCountByBuildingName('A108','TS') }}</td>
                          <td>{{ totalCableTVCustomerCountByBuildingName('A108','MP') }}</td>
                          <td></td>
                        </tr>
                        <tr>
                          <td>Town House</td>
                          <td>{{ totalCableTVCustomerCountByBuildingType('R1','T','NR') }}</td>
                          <td>{{ totalCableTVCustomerCountByBuildingType('R1','T','CL') }}</td>
                          <td>{{ totalCableTVCustomerCountByBuildingType('R1','T','RC') }}</td>
                          <td>{{ totalCableTVCustomerCountByBuildingType('R1','T','TS') }}</td>
                          <td>{{ totalCableTVCustomerCountByBuildingType('R1','T','MP') }}</td>
                          <td></td>
                        </tr>
                        <tr>
                          <td>Shops</td>
                          <td>{{ totalCableTVCustomerCountByBuildingType('R1','S','NR') }}</td>
                          <td>{{ totalCableTVCustomerCountByBuildingType('R1','S','CL') }}</td>
                          <td>{{ totalCableTVCustomerCountByBuildingType('R1','S','RC') }}</td>
                          <td>{{ totalCableTVCustomerCountByBuildingType('R1','S','TS') }}</td>
                          <td>{{ totalCableTVCustomerCountByBuildingType('R1','S','MP') }}</td>
                          <td></td>
                        </tr>
                        <tr>
                          <td>R1-Villa</td>
                          <td>{{ totalCableTVCustomerCountByBuildingType('R1','V','NR') }}</td>
                          <td>{{ totalCableTVCustomerCountByBuildingType('R1','V','CL') }}</td>
                          <td>{{ totalCableTVCustomerCountByBuildingType('R1','V','RC') }}</td>
                          <td>{{ totalCableTVCustomerCountByBuildingType('R1','V','TS') }}</td>
                          <td>{{ totalCableTVCustomerCountByBuildingType('R1','V','MP') }}</td>
                          <td></td>
                        </tr>
                        <tr>
                          <td>R2-Villa</td>
                          <td>{{ totalCableTVCustomerCountByBuildingType('R2','V','NR') }}</td>
                          <td>{{ totalCableTVCustomerCountByBuildingType('R2','V','CL') }}</td>
                          <td>{{ totalCableTVCustomerCountByBuildingType('R2','V','RC') }}</td>
                          <td>{{ totalCableTVCustomerCountByBuildingType('R2','V','TS') }}</td>
                          <td>{{ totalCableTVCustomerCountByBuildingType('R2','V','MP') }}</td>
                          <td></td>
                        </tr>
                      </tbody>
                      <tfoot>                  
                        <tr>
                          <th><span class="text-dark" title="Total"><i class="fas fa-grip-lines"></i></span></th>
                          <th class="text-{{ internetHistoryTypeAbbreviationToFullName('NR')['color'] }}">{{ totalCableTVCustomerCountByMonth('NR') }}</th>
                          <th class="text-{{ internetHistoryTypeAbbreviationToFullName('CL')['color'] }}">{{ totalCableTVCustomerCountByMonth('CL') }}</th>
                          <th class="text-{{ internetHistoryTypeAbbreviationToFullName('RC')['color'] }}">{{ totalCableTVCustomerCountByMonth('RC') }}</th>
                          <th class="text-{{ internetHistoryTypeAbbreviationToFullName('TS')['color'] }}">{{ totalCableTVCustomerCountByMonth('TS') }}</th>
                          <th class="text-{{ internetHistoryTypeAbbreviationToFullName('MP')['color'] }}">{{ totalCableTVCustomerCountByMonth('MP') }}</th>
                          <th>{{ (totalCableTVCustomerCountByMonth('NR')+totalCableTVCustomerCountByMonth('CL')+totalCableTVCustomerCountByMonth('RC')+totalCableTVCustomerCountByMonth('TS')+totalCableTVCustomerCountByMonth('MP')) }}</th>
                        </tr>
                      </tfoot>
                    </table>     
                  </div>                   
                </div>
                <div class="chart tab-pane" id="cabletv-sales-monthly" style="position: relative; height: 281px;">
                  <canvas id="cableTVPieChartMonthly" height="281" style="height: 281px;"></canvas>                         
                </div>
                <div class="chart tab-pane" id="cabletv-sales-yearly" style="position: relative; height: 281px;">
                  <canvas id="cableTVBarChartYearly" height="281" style="height: 281px;"></canvas>                         
                </div>                  
              </div>
            </div>
          </div>
        </section>
        <!-- right col -->
      </div>
      <!-- /.row (main row) -->
      <div class="row">
        <!-- Left col -->
        <section class="col-lg-6 connectedSortable">
          <!-- Custom tabs (Charts with tabs)-->
          <div class="card">
            <div class="card-header">
              <h3 class="card-title pt-2">
                <i class="fas fa-wifi mr-1"></i>
                Paid/Not Paid Internet Subscribers
              </h3> 
              <h3 class="card-title float-right">
                <div class="float-left mr-3">
                  <form action="{{ route('internet-monthly-invoice') }}" method="get" target="_blank"> 
                    <select name="addressID" class="form-control select2" id="InternetAddress" onchange="this.form.submit()">
                      <option value="">--Unit No.--</option>
                      @if(count($unitsAddressArr)>0)
                        @foreach($unitsAddressArr as $lkey=>$lval)
                          <optgroup label="{{ buildingNameByLocationID($lkey) }}">
                            @foreach($lval as $uval)
                              <option value="{{ $uval->id }}"> {{ buildingLocationAndUnitNumberByUnitID($uval->id) }}</option>
                            @endforeach
                          </optgroup>    
                        @endforeach    
                      @endif
                    </select>
                    <input type="hidden" name="paid" value="N">
                  </form>
                </div> 
                <div class="float-right">
                  <form action="{{ route('dashboard') }}" method="get"> 
                    <select name="iyear" class="form-control" onchange="this.form.submit()">
                      @for($year=currentYear(); $year>= 2017;  $year--)
                        <option value="{{ $year }}" {{ $iyear==$year ? "selected": "" }}>{{ $year }}</option>
                      @endfor
                    </select>
                    <input type="hidden" name="cyear" value="{{ $cyear }}">
                  </form>
                </div>
              </h3>                
            </div><!-- /.card-header -->
            <div class="card-body table-responsive p-0">
              <table class="table table-head-fixed table-bordered table-striped">
                <thead>                  
                  <tr>
                    <th><span class="text-dark" title="Months">Month Year</span></th>
                    <th><span class="text-dark">Paid</span></th>
                    <th><span class="text-dark">Not Paid</span></th>
                    <th><span class="text-dark">Total</span></th>
                  </tr>
                </thead>
                <tbody>
                  @for($i=1; $i<=12; $i++)
                    <tr>
                      <td>{{ monthNumberToName($i). ', ' .$iyear }}</td>
                      <td class="ipaid">{{ paidNotPaidInternetMonthlyCustomers('Y',internetEndMonthlyPaymentDate(sprintf("%02d", $i).'-'.$iyear)) }}</td> 
                      <td class="inotpaid">{{ paidNotPaidInternetMonthlyCustomers('N',internetEndMonthlyPaymentDate(sprintf("%02d", $i).'-'.$iyear)) }} <a target="_blank" href="{{ route('internet-monthly-invoice',['monthlyYear'=>sprintf('%02d', $i).'-'.$iyear, 'paid'=>'N']) }}"><i class="fas fa-search-plus"></i></a></td>
                      <td class="itotal">{{ paidNotPaidInternetMonthlyCustomers('Y',internetEndMonthlyPaymentDate(sprintf("%02d", $i).'-'.$iyear))+paidNotPaidInternetMonthlyCustomers('N',internetEndMonthlyPaymentDate(sprintf("%02d", $i).'-'.$iyear)) }}</td>
                    </tr>
                  @endfor
                </tbody>
                <tfoot>                  
                  <tr>
                    <th><span class="text-dark">Grand Total</span></th>
                    <th><span id="TotalInternetPaid"></span></th>
                    <th><span id="TotalInternetNotPaid"></span></th>
                    <th><span id="TotalMonthlyInternet"></span></th>
                  </tr>
                </tfoot>
              </table>
            </div><!-- /.card-body -->
          </div>
          <!-- /.card -->
        </section>
        <!-- /.Left col -->
        <!-- right col (We are only adding the ID to make the widgets sortable)-->
        <section class="col-lg-6 connectedSortable">
          <div class="card">
            <div class="card-header">
              <h3 class="card-title pt-2">
                <i class="fas fa-tv mr-1"></i>
                Paid/Not Paid Cable TV Subscribers
              </h3>  
              <h3 class="card-title float-right">
                <div class="float-left mr-3">
                  <form action="{{ route('cabletv-monthly-invoice') }}" method="get" target="_blank"> 
                    <select name="addressID" class="form-control select2" id="CableTVAddress" onchange="this.form.submit()">
                      <option value="">--Unit No.--</option>
                      @if(count($unitsAddressArr)>0)
                        @foreach($unitsAddressArr as $lkey=>$lval)
                          <optgroup label="{{ buildingNameByLocationID($lkey) }}">
                            @foreach($lval as $uval)
                              <option value="{{ $uval->id }}"> {{ buildingLocationAndUnitNumberByUnitID($uval->id) }}</option>
                            @endforeach
                          </optgroup>    
                        @endforeach    
                      @endif
                    </select>
                    <input type="hidden" name="paid" value="N">
                  </form>
                </div> 
                <div class="float-right">
                  <form action="{{ route('dashboard') }}" method="get"> 
                    <select name="cyear" class="form-control" onchange="this.form.submit()">
                      @for($year=currentYear(); $year>= 2017;  $year--)
                        <option value="{{ $year }}" {{ $cyear==$year ? "selected": "" }}>{{ $year }}</option>
                      @endfor
                    </select>
                    <input type="hidden" name="iyear" value="{{ $iyear }}">
                  </form>
                </div>
              </h3>              
            </div>
            <div class="card-body table-responsive p-0">
              <table class="table table-head-fixed table-bordered table-striped">
                <thead>                  
                  <tr>
                    <th><span class="text-dark" title="Months">Month Year</span></th>
                    <th><span class="text-dark">Paid</span></th>
                    <th><span class="text-dark">Not Paid</span></th>
                    <th><span class="text-dark">Total</span></th>
                  </tr>
                </thead>
                <tbody>
                  @for($i=1; $i<=12; $i++)
                    <tr>
                      <td>{{ monthNumberToName($i). ', ' .$cyear }}</td>
                      <td class="cpaid">{{ paidNotPaidCableTVMonthlyCustomers('Y',cableTVGivenMonthStartDate('01-'.sprintf("%02d", $i).'-'.$cyear)) }}</td> 
                      <td class="cnotpaid">{{ paidNotPaidCableTVMonthlyCustomers('N',cableTVGivenMonthStartDate('01-'.sprintf("%02d", $i).'-'.$cyear)) }} <a target="_blank" href="{{ route('cabletv-monthly-invoice',['monthlyYear'=>sprintf('%02d', $i).'-'.$cyear, 'paid'=>'N']) }}"><i class="fas fa-search-plus"></i></a></td>
                      <td class="ctotal">{{ paidNotPaidCableTVMonthlyCustomers('Y',cableTVGivenMonthStartDate('01-'.sprintf("%02d", $i).'-'.$cyear))+paidNotPaidCableTVMonthlyCustomers('N',cableTVGivenMonthStartDate('01-'.sprintf("%02d", $i).'-'.$cyear)) }}</td>
                    </tr>
                  @endfor
                </tbody>
                <tfoot>                  
                  <tr>
                    <th><span class="text-dark">Grand Total</span></th>
                    <th><span id="TotalCableTVPaid"></span></th>
                    <th><span id="TotalCableTVNotPaid"></span></th>
                    <th><span id="TotalMonthlyCableTV"></span></th>
                  </tr>
                </tfoot>
              </table>
            </div><!-- /.card-body -->
          </div>
        </section>
        <!-- right col -->
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
  //Initialize Select2 Elements
  $('.select2').select2();
  //-------------
  //- INTERNET PIE CHART -
  //-------------
  // Get context with jQuery - using jQuery's .get() method.
  
  var monthlyData        = {
    labels: [
        'New Registration', 
        'Monthly Invoice',
        'Change Plan',
        'Change Location', 
        'Suspend Service', 
        'Terminate', 
          
    ],
    datasets: [
      {
        data: [{{ totalMonthlyCustomers('NR') }},{{ totalMonthlyCustomers('MP') }},{{ totalMonthlyCustomers('CP') }},{{ totalMonthlyCustomers('CL') }},{{ totalMonthlyCustomers('SS') }},{{ totalMonthlyCustomers('TS') }}],
        backgroundColor : ['#007bff', '#001f3f', '#28a745', '#f012be', '#fd7e14', '#dc3545'],
      }
    ]
  }

  var pieChartCanvasMonthly = $('#pieChartMonthly').get(0).getContext('2d')
  var pieDataMonthly        = monthlyData;
  var pieOptionsMonthly     = {
    maintainAspectRatio : false,
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
        label               : 'Terminate',
        backgroundColor     : '#dc3545',
        borderColor         : 'rgba(60,141,188,0.8)',
        pointRadius          : false,
        pointColor          : '#dc3545',
        pointStrokeColor    : 'rgba(60,141,188,1)',
        pointHighlightFill  : '#fff',
        pointHighlightStroke: 'rgba(60,141,188,1)',
        data                : [{{ totalYearlyCustomers('TS', 1) }}, {{ totalYearlyCustomers('TS', 2) }}, {{ totalYearlyCustomers('TS', 3) }}, {{ totalYearlyCustomers('TS', 4) }}, {{ totalYearlyCustomers('TS', 5) }}, {{ totalYearlyCustomers('TS', 6) }}, {{ totalYearlyCustomers('TS', 7) }}, {{ totalYearlyCustomers('TS', 8) }}, {{ totalYearlyCustomers('TS', 9) }}, {{ totalYearlyCustomers('TS', 10) }}, {{ totalYearlyCustomers('TS', 11) }}, {{ totalYearlyCustomers('TS', 12) }}]
      },
      {
        label               : 'Suspend Service',
        backgroundColor     : '#fd7e14',
        borderColor         : 'rgba(60,141,188,0.8)',
        pointRadius          : false,
        pointColor          : '#fd7e14',
        pointStrokeColor    : 'rgba(60,141,188,1)',
        pointHighlightFill  : '#fff',
        pointHighlightStroke: 'rgba(60,141,188,1)',
        data                : [{{ totalYearlyCustomers('SS', 1) }}, {{ totalYearlyCustomers('SS', 2) }}, {{ totalYearlyCustomers('SS', 3) }}, {{ totalYearlyCustomers('SS', 4) }}, {{ totalYearlyCustomers('SS', 5) }}, {{ totalYearlyCustomers('SS', 6) }}, {{ totalYearlyCustomers('SS', 7) }}, {{ totalYearlyCustomers('SS', 8) }}, {{ totalYearlyCustomers('SS', 9) }}, {{ totalYearlyCustomers('SS', 10) }}, {{ totalYearlyCustomers('SS', 11) }}, {{ totalYearlyCustomers('SS', 12) }}]
      },
      {
        label               : 'Change Location',
        backgroundColor     : '#f012be',
        borderColor         : 'rgba(60,141,188,0.8)',
        pointRadius          : false,
        pointColor          : '#f012be',
        pointStrokeColor    : 'rgba(60,141,188,1)',
        pointHighlightFill  : '#fff',
        pointHighlightStroke: 'rgba(60,141,188,1)',
        data                : [{{ totalYearlyCustomers('CL', 1) }}, {{ totalYearlyCustomers('CL', 2) }}, {{ totalYearlyCustomers('CL', 3) }}, {{ totalYearlyCustomers('CL', 4) }}, {{ totalYearlyCustomers('CL', 5) }}, {{ totalYearlyCustomers('CL', 6) }}, {{ totalYearlyCustomers('CL', 7) }}, {{ totalYearlyCustomers('CL', 8) }}, {{ totalYearlyCustomers('CL', 9) }}, {{ totalYearlyCustomers('CL', 10) }}, {{ totalYearlyCustomers('CL', 11) }}, {{ totalYearlyCustomers('CL', 12) }}]
      },
      {
        label               : 'Change Plan',
        backgroundColor     : '#28a745',
        borderColor         : 'rgba(210, 214, 222, 1)',
        pointRadius         : false,
        pointColor          : 'rgba(210, 214, 222, 1)',
        pointStrokeColor    : '#28a745',
        pointHighlightFill  : '#fff',
        pointHighlightStroke: 'rgba(220,220,220,1)',
        data                : [{{ totalYearlyCustomers('CP', 1) }}, {{ totalYearlyCustomers('CP', 2) }}, {{ totalYearlyCustomers('CP', 3) }}, {{ totalYearlyCustomers('CP', 4) }}, {{ totalYearlyCustomers('CP', 5) }}, {{ totalYearlyCustomers('CP', 6) }}, {{ totalYearlyCustomers('CP', 7) }}, {{ totalYearlyCustomers('CP', 8) }}, {{ totalYearlyCustomers('CP', 9) }}, {{ totalYearlyCustomers('CP', 10) }}, {{ totalYearlyCustomers('CP', 11) }}, {{ totalYearlyCustomers('CP', 12) }}]
      },
      {
        label               : 'Monthly Invoice',
        backgroundColor     : '#001f3f',
        borderColor         : 'rgba(60,141,188,0.8)',
        pointRadius          : false,
        pointColor          : '#001f3f',
        pointStrokeColor    : 'rgba(60,141,188,1)',
        pointHighlightFill  : '#fff',
        pointHighlightStroke: 'rgba(60,141,188,1)',
        data                : [{{ totalYearlyCustomers('MP', 1) }}, {{ totalYearlyCustomers('MP', 2) }}, {{ totalYearlyCustomers('MP', 3) }}, {{ totalYearlyCustomers('MP', 4) }}, {{ totalYearlyCustomers('MP', 5) }}, {{ totalYearlyCustomers('MP', 6) }}, {{ totalYearlyCustomers('MP', 7) }}, {{ totalYearlyCustomers('MP', 8) }}, {{ totalYearlyCustomers('MP', 9) }}, {{ totalYearlyCustomers('MP', 10) }}, {{ totalYearlyCustomers('MP', 11) }}, {{ totalYearlyCustomers('MP', 12) }}]
      },
      {
        label               : 'New Registration',
        backgroundColor     : '#007bff',
        borderColor         : 'rgba(60,141,188,0.8)',
        pointRadius          : false,
        pointColor          : '#007bff',
        pointStrokeColor    : 'rgba(60,141,188,1)',
        pointHighlightFill  : '#fff',
        pointHighlightStroke: 'rgba(60,141,188,1)',
        data                : [{{ totalYearlyCustomers('NR', 1) }}, {{ totalYearlyCustomers('NR', 2) }}, {{ totalYearlyCustomers('NR', 3) }}, {{ totalYearlyCustomers('NR', 4) }}, {{ totalYearlyCustomers('NR', 5) }}, {{ totalYearlyCustomers('NR', 6) }}, {{ totalYearlyCustomers('NR', 7) }}, {{ totalYearlyCustomers('NR', 8) }}, {{ totalYearlyCustomers('NR', 9) }}, {{ totalYearlyCustomers('NR', 10) }}, {{ totalYearlyCustomers('NR', 11) }}, {{ totalYearlyCustomers('NR', 12) }}]
      },
    ]
  }


  var barChartYearlyCanvas = $('#barChartYearly').get(0).getContext('2d')
  var barChartYearlyData = jQuery.extend(true, {}, yearlyChartData)
  var temp0 = yearlyChartData.datasets[0]
  var temp1 = yearlyChartData.datasets[1]
  var temp2 = yearlyChartData.datasets[2]
  var temp3 = yearlyChartData.datasets[3]
  var temp4 = yearlyChartData.datasets[4]
  var temp5 = yearlyChartData.datasets[5]

  barChartYearlyData.datasets[0] = temp5
  barChartYearlyData.datasets[1] = temp4
  barChartYearlyData.datasets[2] = temp3
  barChartYearlyData.datasets[3] = temp2
  barChartYearlyData.datasets[4] = temp1
  barChartYearlyData.datasets[5] = temp0

  var barChartOptions = {
    responsive              : true,
    maintainAspectRatio     : false,
    datasetFill             : false
  }

  var barChart = new Chart(barChartYearlyCanvas, {
    type: 'bar', 
    data: barChartYearlyData,
    options: barChartOptions
  })

})


// Monthly CableTV Chart
var monthlyCableTVData        = {
  labels: [
      'New Registration', 
      'Change Location',
      'Reconnect', 
      'Terminate', 
      'Monthly Invoice',
        
  ],
  datasets: [
    {
      data: [{{ totalCableTVTabCustomers('M','','NR') }},{{ totalCableTVTabCustomers('M','','CL') }},{{ totalCableTVTabCustomers('M','','RC') }},{{ totalCableTVTabCustomers('M','','TS') }},{{ totalCableTVTabCustomers('M','','MP') }}],
      backgroundColor : ['#007bff', '#f012be', '#6610f2', '#dc3545', '#001f3f'],
    }
  ]
}

var cableTVPieChartCanvasMonthly = $('#cableTVPieChartMonthly').get(0).getContext('2d')
var cableTVPieDataMonthly        = monthlyCableTVData;
var cableTVPieOptionsMonthly     = {
  maintainAspectRatio : false,
  responsive : true,
}
//Create pie or douhnut chart
// You can switch between pie and douhnut using the method below.
var cableTVPieChartMonthly = new Chart(cableTVPieChartCanvasMonthly, {
  type: 'pie',
  data: cableTVPieDataMonthly,
  options: cableTVPieOptionsMonthly      
})

// Yearly CableTV Chart 
var cableTVYearlyChartData = {
    labels  : ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
    datasets: [
      {
        label               : 'Monthly Invoice',
        backgroundColor     : '#001f3f',
        borderColor         : 'rgba(60,141,188,0.8)',
        pointRadius          : false,
        pointColor          : '#001f3f',
        pointStrokeColor    : 'rgba(60,141,188,1)',
        pointHighlightFill  : '#fff',
        pointHighlightStroke: 'rgba(60,141,188,1)',
        data                : [{{ totalCableTVTabCustomers('Y',1,'MP') }}, {{ totalCableTVTabCustomers('Y',2,'MP') }}, {{ totalCableTVTabCustomers('Y',3,'MP') }}, {{ totalCableTVTabCustomers('Y',4,'MP') }}, {{ totalCableTVTabCustomers('Y',5,'MP') }}, {{ totalCableTVTabCustomers('Y',6,'MP') }}, {{ totalCableTVTabCustomers('Y',7,'MP') }}, {{ totalCableTVTabCustomers('Y',8,'MP') }}, {{ totalCableTVTabCustomers('Y',9,'MP') }}, {{ totalCableTVTabCustomers('Y',10,'MP') }}, {{ totalCableTVTabCustomers('Y',11,'MP') }}, {{ totalCableTVTabCustomers('Y',12,'MP') }}]
      },
      {
        label               : 'Terminate',
        backgroundColor     : '#dc3545',
        borderColor         : 'rgba(60,141,188,0.8)',
        pointRadius          : false,
        pointColor          : '#dc3545',
        pointStrokeColor    : 'rgba(60,141,188,1)',
        pointHighlightFill  : '#fff',
        pointHighlightStroke: 'rgba(60,141,188,1)',
        data                : [{{ totalCableTVTabCustomers('Y',1,'TS') }}, {{ totalCableTVTabCustomers('Y',2,'TS') }}, {{ totalCableTVTabCustomers('Y',3,'TS') }}, {{ totalCableTVTabCustomers('Y',4,'TS') }}, {{ totalCableTVTabCustomers('Y',5,'TS') }}, {{ totalCableTVTabCustomers('Y',6,'TS') }}, {{ totalCableTVTabCustomers('Y',7,'TS') }}, {{ totalCableTVTabCustomers('Y',8,'TS') }}, {{ totalCableTVTabCustomers('Y',9,'TS') }}, {{ totalCableTVTabCustomers('Y',10,'TS') }}, {{ totalCableTVTabCustomers('Y',11,'TS') }}, {{ totalCableTVTabCustomers('Y',12,'TS') }}]
      },
      {
        label               : 'Reconnect',
        backgroundColor     : '#6610f2',
        borderColor         : 'rgba(60,141,188,0.8)',
        pointRadius          : false,
        pointColor          : '#6610f2',
        pointStrokeColor    : 'rgba(60,141,188,1)',
        pointHighlightFill  : '#fff',
        pointHighlightStroke: 'rgba(60,141,188,1)',
        data                : [{{ totalCableTVTabCustomers('Y',1,'RC') }}, {{ totalCableTVTabCustomers('Y',2,'RC') }}, {{ totalCableTVTabCustomers('Y',3,'RC') }}, {{ totalCableTVTabCustomers('Y',4,'RC') }}, {{ totalCableTVTabCustomers('Y',5,'RC') }}, {{ totalCableTVTabCustomers('Y',6,'RC') }}, {{ totalCableTVTabCustomers('Y',7,'RC') }}, {{ totalCableTVTabCustomers('Y',8,'RC') }}, {{ totalCableTVTabCustomers('Y',9,'RC') }}, {{ totalCableTVTabCustomers('Y',10,'RC') }}, {{ totalCableTVTabCustomers('Y',11,'RC') }}, {{ totalCableTVTabCustomers('Y',12,'RC') }}]
      },        
      {
        label               : 'Change Location',
        backgroundColor     : '#f012be',
        borderColor         : 'rgba(60,141,188,0.8)',
        pointRadius          : false,
        pointColor          : '#f012be',
        pointStrokeColor    : 'rgba(60,141,188,1)',
        pointHighlightFill  : '#fff',
        pointHighlightStroke: 'rgba(60,141,188,1)',
        data                : [{{ totalCableTVTabCustomers('Y',1,'CL') }}, {{ totalCableTVTabCustomers('Y',2,'CL') }}, {{ totalCableTVTabCustomers('Y',3,'CL') }}, {{ totalCableTVTabCustomers('Y',4,'CL') }}, {{ totalCableTVTabCustomers('Y',5,'CL') }}, {{ totalCableTVTabCustomers('Y',6,'CL') }}, {{ totalCableTVTabCustomers('Y',7,'CL') }}, {{ totalCableTVTabCustomers('Y',8,'CL') }}, {{ totalCableTVTabCustomers('Y',9,'CL') }}, {{ totalCableTVTabCustomers('Y',10,'CL') }}, {{ totalCableTVTabCustomers('Y',11,'CL') }}, {{ totalCableTVTabCustomers('Y',12,'CL') }}]
      },        
      {
        label               : 'New Registration',
        backgroundColor     : '#007bff',
        borderColor         : 'rgba(60,141,188,0.8)',
        pointRadius          : false,
        pointColor          : '#007bff',
        pointStrokeColor    : 'rgba(60,141,188,1)',
        pointHighlightFill  : '#fff',
        pointHighlightStroke: 'rgba(60,141,188,1)',
        data                : [{{ totalCableTVTabCustomers('Y',1,'NR') }}, {{ totalCableTVTabCustomers('Y',2,'NR') }}, {{ totalCableTVTabCustomers('Y',3,'NR') }}, {{ totalCableTVTabCustomers('Y',4,'NR') }}, {{ totalCableTVTabCustomers('Y',5,'NR') }}, {{ totalCableTVTabCustomers('Y',6,'NR') }}, {{ totalCableTVTabCustomers('Y',7,'NR') }}, {{ totalCableTVTabCustomers('Y',8,'NR') }}, {{ totalCableTVTabCustomers('Y',9,'NR') }}, {{ totalCableTVTabCustomers('Y',10,'NR') }}, {{ totalCableTVTabCustomers('Y',11,'NR') }}, {{ totalCableTVTabCustomers('Y',12,'NR') }}]
      },
    ]
  }


  var cableTVBarChartYearlyCanvas = $('#cableTVBarChartYearly').get(0).getContext('2d')
  var cableTVBarChartYearlyData = jQuery.extend(true, {}, cableTVYearlyChartData)
  var tempCableTV0 = cableTVYearlyChartData.datasets[0]
  var tempCableTV1 = cableTVYearlyChartData.datasets[1]
  var tempCableTV2 = cableTVYearlyChartData.datasets[2]
  var tempCableTV3 = cableTVYearlyChartData.datasets[3]
  var tempCableTV4 = cableTVYearlyChartData.datasets[4]

  cableTVBarChartYearlyData.datasets[0] = tempCableTV4
  cableTVBarChartYearlyData.datasets[1] = tempCableTV3
  cableTVBarChartYearlyData.datasets[2] = tempCableTV2
  cableTVBarChartYearlyData.datasets[3] = tempCableTV1
  cableTVBarChartYearlyData.datasets[4] = tempCableTV0

  var cableTVBarChartOptions = {
    responsive              : true,
    maintainAspectRatio     : false,
    datasetFill             : false
  }

  var barChart = new Chart(cableTVBarChartYearlyCanvas, {
    type: 'bar', 
    data: cableTVBarChartYearlyData,
    options: cableTVBarChartOptions
  })

$(document).ready(function() {
  
  $('#internet-summary tbody tr').each(function () {
    var row = $(this);
    var rowTotal = 0;
    $(this).find('td').each(function () {
      var td = $(this);
      if ($.isNumeric(td.text())) {
        rowTotal += parseInt(td.text());
      }
    });
    row.find('td:last').text(rowTotal).css('font-weight','bold').addClass('text-teal');
  });

  $('#cabletv-summary tbody tr').each(function () {
    var row = $(this);
    var rowTotal = 0;
    $(this).find('td').each(function () {
      var td = $(this);
      if ($.isNumeric(td.text())) {
        rowTotal += parseInt(td.text());
      }
    });
    row.find('td:last').text(rowTotal).css('font-weight','bold').addClass('text-teal');
  });

  var isum = 0;
  $('.ipaid').each(function(){
    var tdTxt = $(this).text();
    isum += parseInt(tdTxt);                       
  });
  $('#TotalInternetPaid').text(isum);

  var insum = 0;
  $('.inotpaid').each(function(){
    var tdTxt = $(this).text();
    insum += parseInt(tdTxt);                       
  });
  $('#TotalInternetNotPaid').text(insum);

  var csum = 0;
  $('.cpaid').each(function(){
    var tdTxt = $(this).text();
    csum += parseInt(tdTxt);                       
  });
  $('#TotalCableTVPaid').text(csum);

  var cnsum = 0;
  $('.cnotpaid').each(function(){
    var tdTxt = $(this).text();
    cnsum += parseInt(tdTxt);                       
  });
  $('#TotalCableTVNotPaid').text(cnsum); 
  
  var itsum = 0;
  $('.itotal').each(function(){
    var tdTxt = $(this).text();
    itsum += parseInt(tdTxt);                       
  });
  $('#TotalMonthlyInternet').text(itsum);

  var ctsum = 0;
  $('.ctotal').each(function(){
    var tdTxt = $(this).text();
    ctsum += parseInt(tdTxt);                       
  });
  $('#TotalMonthlyCableTV').text(ctsum);
  
});
</script>
@endsection