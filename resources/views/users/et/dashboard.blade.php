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
                <h3>{{ totalInternetCustomers("NR") }}</h3>

                <p>Internet Customers</p>
              </div>
              <div class="icon">
                <i class="ion ion-ios-people"></i>
              </div>
            </div>
          </div>
          <!-- ./col -->
          <div class="col-lg-3 col-6">
            <!-- small box -->
            <div class="small-box bg-success">
              <div class="inner">
                <h3><sup style="font-size: 20px">$</sup>{{ totalInternetAmountMonthly() }}</h3>

                <p>Total Amount</p>
              </div>
              <div class="icon">
                <i class="ion ion-cash"></i>
              </div>
            </div>
          </div>
          <!-- ./col -->
          <div class="col-lg-3 col-6">
            <!-- small box -->
            <div class="small-box bg-gray">
              <div class="inner">
                <h3>{{ totalInternetCustomers("NR") }}</h3>

                <p>Cable TV Customers</p>
              </div>
              <div class="icon">
                <i class="ion ion-ios-people"></i>
              </div>
            </div>
          </div>
          <!-- ./col -->
          <div class="col-lg-3 col-6">
            <!-- small box -->
            <div class="small-box bg-purple">
              <div class="inner">
                <h3><sup style="font-size: 20px">$</sup>{{ totalInternetAmountMonthly() }}</h3>

                <p>Total Amount</p>
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
                       <a class="nav-link active" href="#internet-sales-weekly" data-toggle="tab">Weekly</a>
                    </li>
                    <li class="nav-item">
                      <a class="nav-link" href="#internet-sales-monthly" data-toggle="tab">Monthly</a>
                    </li>
                    <li class="nav-item">
                      <a class="nav-link" href="#internet-sales-yearly" data-toggle="tab">Yearly</a>
                    </li>
                    <li class="nav-item">
                      <a class="nav-link" href="#internet-summary-report" data-toggle="tab">Summary</a>
                    </li>
                  </ul>
                </div>
              </div><!-- /.card-header -->
              <div class="card-body">
                <div class="tab-content p-0">
                  <!-- Morris chart - Sales -->
                  <div class="chart tab-pane active" id="internet-sales-weekly" style="position: relative; height: 281px;">
                      <canvas id="pieChartWeekly" height="281" style="height: 281px;"></canvas>                         
                   </div>
                  <div class="chart tab-pane" id="internet-sales-monthly" style="position: relative; height: 281px;">
                    <canvas id="pieChartMonthly" height="281" style="height: 281px;"></canvas>                         
                  </div>
                  <div class="chart tab-pane" id="internet-sales-yearly" style="position: relative; height: 281px;">
                    <canvas id="barChartYearly" height="281" style="height: 281px;"></canvas>                         
                  </div>
                  <div class="chart tab-pane" id="internet-summary-report" style="position: relative; height: 281px;">
                    <div class="card-body table-responsive p-0" style="height: 281px;">
                      <table class="table table-head-fixed" style="text-align:center" id="internet-summary">
                        <thead>                  
                          <tr>
                            <th><span class="text-orange" title="Locations"><i class="fas fa-city"></i></span></th>
                            <th><span class="text-{{ internetHistoryTypeAbbreviationToFullName('NR')['color'] }}" title="{{ internetHistoryTypeAbbreviationToFullName('NR')['full'] }}"><i class="{{ internetHistoryTypeAbbreviationToFullName('NR')['icon'] }}"></i></th>
                            <th><span class="text-{{ internetHistoryTypeAbbreviationToFullName('MP')['color'] }}" title="{{ internetHistoryTypeAbbreviationToFullName('MP')['full'] }}"><i class="{{ internetHistoryTypeAbbreviationToFullName('MP')['icon'] }}"></i></th>
                            <th><span class="text-{{ internetHistoryTypeAbbreviationToFullName('CP')['color'] }}" title="{{ internetHistoryTypeAbbreviationToFullName('CP')['full'] }}"><i class="{{ internetHistoryTypeAbbreviationToFullName('CP')['icon'] }}"></i></th>
                            <th><span class="text-{{ internetHistoryTypeAbbreviationToFullName('CL')['color'] }}" title="{{ internetHistoryTypeAbbreviationToFullName('CL')['full'] }}"><i class="{{ internetHistoryTypeAbbreviationToFullName('CL')['icon'] }}"></i></th>
                            <th><span class="text-{{ internetHistoryTypeAbbreviationToFullName('SS')['color'] }}" title="{{ internetHistoryTypeAbbreviationToFullName('SS')['full'] }}"><i class="{{ internetHistoryTypeAbbreviationToFullName('SS')['icon'] }}"></i></th>
                            <th><span class="text-{{ internetHistoryTypeAbbreviationToFullName('TS')['color'] }}" title="{{ internetHistoryTypeAbbreviationToFullName('TS')['full'] }}"><i class="{{ internetHistoryTypeAbbreviationToFullName('TS')['icon'] }}"></i></th>
                            <th><span class="text-teal" title="Total"><i class="fas fa-grip-lines"></i></span></th>
                          </tr>
                        </thead>
                        <tbody>
                          <tr>
                            <td><i class="fas fa-building"></i> - A101</td>
                            <td>{{ totalCustomerCountByBuildingName('A101','NR') }}</td>
                            <td>{{ totalCustomerCountByBuildingName('A101','MP') }}</td>
                            <td>{{ totalCustomerCountByBuildingName('A101','CP') }}</td>
                            <td>{{ totalCustomerCountByBuildingName('A101','CL') }}</td>
                            <td>{{ totalCustomerCountByBuildingName('A101','SS') }}</td>
                            <td>{{ totalCustomerCountByBuildingName('A101','TS') }}</td>
                            <td></td>
                          </tr>
                          <tr>
                            <td><i class="fas fa-building"></i> - A102</td>
                            <td>{{ totalCustomerCountByBuildingName('A102','NR') }}</td>
                            <td>{{ totalCustomerCountByBuildingName('A102','MP') }}</td>
                            <td>{{ totalCustomerCountByBuildingName('A102','CP') }}</td>
                            <td>{{ totalCustomerCountByBuildingName('A102','CL') }}</td>
                            <td>{{ totalCustomerCountByBuildingName('A102','SS') }}</td>
                            <td>{{ totalCustomerCountByBuildingName('A102','TS') }}</td>
                            <td></td>
                          </tr>
                          <tr>
                            <td><i class="fas fa-building"></i> - A103</td>
                            <td>{{ totalCustomerCountByBuildingName('A103','NR') }}</td>
                            <td>{{ totalCustomerCountByBuildingName('A103','MP') }}</td>
                            <td>{{ totalCustomerCountByBuildingName('A103','CP') }}</td>
                            <td>{{ totalCustomerCountByBuildingName('A103','CL') }}</td>
                            <td>{{ totalCustomerCountByBuildingName('A103','SS') }}</td>
                            <td>{{ totalCustomerCountByBuildingName('A103','TS') }}</td>
                            <td></td>
                          </tr>
                          <tr>
                            <td><i class="fas fa-building"></i> - A104</td>
                            <td>{{ totalCustomerCountByBuildingName('A104','NR') }}</td>
                            <td>{{ totalCustomerCountByBuildingName('A104','MP') }}</td>
                            <td>{{ totalCustomerCountByBuildingName('A104','CP') }}</td>
                            <td>{{ totalCustomerCountByBuildingName('A104','CL') }}</td>
                            <td>{{ totalCustomerCountByBuildingName('A104','SS') }}</td>
                            <td>{{ totalCustomerCountByBuildingName('A104','TS') }}</td>
                            <td></td>
                          </tr>
                          <tr>
                            <td><i class="fas fa-building"></i> - A105</td>
                            <td>{{ totalCustomerCountByBuildingName('A105','NR') }}</td>
                            <td>{{ totalCustomerCountByBuildingName('A105','MP') }}</td>
                            <td>{{ totalCustomerCountByBuildingName('A105','CP') }}</td>
                            <td>{{ totalCustomerCountByBuildingName('A105','CL') }}</td>
                            <td>{{ totalCustomerCountByBuildingName('A105','SS') }}</td>
                            <td>{{ totalCustomerCountByBuildingName('A105','TS') }}</td>
                            <td></td>
                          </tr>
                          <tr>
                            <td><i class="fas fa-building"></i> - A106</td>
                            <td>{{ totalCustomerCountByBuildingName('A106','NR') }}</td>
                            <td>{{ totalCustomerCountByBuildingName('A106','MP') }}</td>
                            <td>{{ totalCustomerCountByBuildingName('A106','CP') }}</td>
                            <td>{{ totalCustomerCountByBuildingName('A106','CL') }}</td>
                            <td>{{ totalCustomerCountByBuildingName('A106','SS') }}</td>
                            <td>{{ totalCustomerCountByBuildingName('A106','TS') }}</td>
                            <td></td>
                          </tr>
                          <tr>
                            <td><i class="fas fa-building"></i> - A107</td>
                            <td>{{ totalCustomerCountByBuildingName('A107','NR') }}</td>
                            <td>{{ totalCustomerCountByBuildingName('A107','MP') }}</td>
                            <td>{{ totalCustomerCountByBuildingName('A107','CP') }}</td>
                            <td>{{ totalCustomerCountByBuildingName('A107','CL') }}</td>
                            <td>{{ totalCustomerCountByBuildingName('A107','SS') }}</td>
                            <td>{{ totalCustomerCountByBuildingName('A107','TS') }}</td>
                            <td></td>
                          </tr>
                          <tr>
                            <td><i class="fas fa-building"></i> - A108</td>
                            <td>{{ totalCustomerCountByBuildingName('A108','NR') }}</td>
                            <td>{{ totalCustomerCountByBuildingName('A108','MP') }}</td>
                            <td>{{ totalCustomerCountByBuildingName('A108','CP') }}</td>
                            <td>{{ totalCustomerCountByBuildingName('A108','CL') }}</td>
                            <td>{{ totalCustomerCountByBuildingName('A108','SS') }}</td>
                            <td>{{ totalCustomerCountByBuildingName('A108','TS') }}</td>
                            <td></td>
                          </tr>
                          <tr>
                            <td><i class="fas fa-hotel"></i> - Town House</td>
                            <td>{{ totalCustomerCountByBuildingType('R1','T','NR') }}</td>
                            <td>{{ totalCustomerCountByBuildingType('R1','T','MP') }}</td>
                            <td>{{ totalCustomerCountByBuildingType('R1','T','CP') }}</td>
                            <td>{{ totalCustomerCountByBuildingType('R1','T','CL') }}</td>
                            <td>{{ totalCustomerCountByBuildingType('R1','T','SS') }}</td>
                            <td>{{ totalCustomerCountByBuildingType('R1','T','TS') }}</td>
                            <td></td>
                          </tr>
                          <tr>
                            <td><i class="fas fa-store"></i> - Shops</td>
                            <td>{{ totalCustomerCountByBuildingType('R1','S','NR') }}</td>
                            <td>{{ totalCustomerCountByBuildingType('R1','S','MP') }}</td>
                            <td>{{ totalCustomerCountByBuildingType('R1','S','CP') }}</td>
                            <td>{{ totalCustomerCountByBuildingType('R1','S','CL') }}</td>
                            <td>{{ totalCustomerCountByBuildingType('R1','S','SS') }}</td>
                            <td>{{ totalCustomerCountByBuildingType('R1','S','TS') }}</td>
                            <td></td>
                          </tr>
                          <tr>
                            <td><i class="fab fa-fort-awesome"></i> - R1-Villa</td>
                            <td>{{ totalCustomerCountByBuildingType('R1','V','NR') }}</td>
                            <td>{{ totalCustomerCountByBuildingType('R1','V','MP') }}</td>
                            <td>{{ totalCustomerCountByBuildingType('R1','V','CP') }}</td>
                            <td>{{ totalCustomerCountByBuildingType('R1','V','CL') }}</td>
                            <td>{{ totalCustomerCountByBuildingType('R1','V','SS') }}</td>
                            <td>{{ totalCustomerCountByBuildingType('R1','V','TS') }}</td>
                            <td></td>
                          </tr>
                          <tr>
                            <td><i class="fab fa-fort-awesome"></i> - R2-Villa</td>
                            <td>{{ totalCustomerCountByBuildingType('R2','V','NR') }}</td>
                            <td>{{ totalCustomerCountByBuildingType('R2','V','MP') }}</td>
                            <td>{{ totalCustomerCountByBuildingType('R2','V','CP') }}</td>
                            <td>{{ totalCustomerCountByBuildingType('R2','V','CL') }}</td>
                            <td>{{ totalCustomerCountByBuildingType('R2','V','SS') }}</td>
                            <td>{{ totalCustomerCountByBuildingType('R2','V','TS') }}</td>
                            <td></td>
                          </tr>
                        </tbody>
                        <tfoot>                  
                          <tr>
                            <th><span class="text-dark" title="Total"><i class="fas fa-grip-lines"></i></span></th>
                            <th class="text-{{ internetHistoryTypeAbbreviationToFullName('NR')['color'] }}">{{ totalInternetCustomers("NR") }}</th>
                            <th class="text-{{ internetHistoryTypeAbbreviationToFullName('MP')['color'] }}">{{ totalInternetCustomers("MP") }}</th>
                            <th class="text-{{ internetHistoryTypeAbbreviationToFullName('CP')['color'] }}">{{ totalInternetCustomers("CP") }}</th>
                            <th class="text-{{ internetHistoryTypeAbbreviationToFullName('CL')['color'] }}">{{ totalInternetCustomers("CL") }}</th>
                            <th class="text-{{ internetHistoryTypeAbbreviationToFullName('SS')['color'] }}">{{ totalInternetCustomers("SS") }}</th>
                            <th class="text-{{ internetHistoryTypeAbbreviationToFullName('TS')['color'] }}">{{ totalInternetCustomers("TS") }}</th>
                            <th></th>
                          </tr>
                        </tfoot>
                      </table>     
                    </div>                   
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
                       <a class="nav-link active" href="#cabletv-sales-weekly" data-toggle="tab">Weekly</a>
                    </li>
                    <li class="nav-item">
                      <a class="nav-link" href="#cabletv-sales-monthly" data-toggle="tab">Monthly</a>
                    </li>
                    <li class="nav-item">
                      <a class="nav-link" href="#cabletv-sales-yearly" data-toggle="tab">Yearly</a>
                    </li>
                    <li class="nav-item">
                      <a class="nav-link" href="#cabletv-summary-report" data-toggle="tab">Summary</a>
                    </li>
                  </ul>
                </div>
              </div><!-- /.card-header -->
              <div class="card-body">
                <div class="tab-content p-0">
                  <!-- Morris chart - Sales -->
                  <div class="chart tab-pane active" id="cabletv-sales-weekly" style="position: relative; height: 281px;">
                      <canvas id="pieChartWeekly" height="281" style="height: 281px;"></canvas>                         
                   </div>
                  <div class="chart tab-pane" id="cabletv-sales-monthly" style="position: relative; height: 281px;">
                    <canvas id="pieChartMonthly" height="281" style="height: 281px;"></canvas>                         
                  </div>
                  <div class="chart tab-pane" id="cabletv-sales-yearly" style="position: relative; height: 281px;">
                    <canvas id="barChartYearly" height="281" style="height: 281px;"></canvas>                         
                  </div>
                  <div class="chart tab-pane" id="cabletv-summary-report" style="position: relative; height: 281px;">
                    <div class="card-body table-responsive p-0" style="height: 281px;">
                      <table class="table table-head-fixed" style="text-align:center" id="cabletv-summary">
                        <thead>                  
                          <tr>
                            <th><span class="text-orange" title="Locations"><i class="fas fa-city"></i></span></th>
                            <th><span class="text-{{ internetHistoryTypeAbbreviationToFullName('NR')['color'] }}" title="{{ internetHistoryTypeAbbreviationToFullName('NR')['full'] }}"><i class="{{ internetHistoryTypeAbbreviationToFullName('NR')['icon'] }}"></i></th>
                            <th><span class="text-{{ internetHistoryTypeAbbreviationToFullName('MP')['color'] }}" title="{{ internetHistoryTypeAbbreviationToFullName('MP')['full'] }}"><i class="{{ internetHistoryTypeAbbreviationToFullName('MP')['icon'] }}"></i></th>
                            <th><span class="text-{{ internetHistoryTypeAbbreviationToFullName('CP')['color'] }}" title="{{ internetHistoryTypeAbbreviationToFullName('CP')['full'] }}"><i class="{{ internetHistoryTypeAbbreviationToFullName('CP')['icon'] }}"></i></th>
                            <th><span class="text-{{ internetHistoryTypeAbbreviationToFullName('CL')['color'] }}" title="{{ internetHistoryTypeAbbreviationToFullName('CL')['full'] }}"><i class="{{ internetHistoryTypeAbbreviationToFullName('CL')['icon'] }}"></i></th>
                            <th><span class="text-{{ internetHistoryTypeAbbreviationToFullName('SS')['color'] }}" title="{{ internetHistoryTypeAbbreviationToFullName('SS')['full'] }}"><i class="{{ internetHistoryTypeAbbreviationToFullName('SS')['icon'] }}"></i></th>
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
                            <td>{{ totalCustomerCountByBuildingName('A108','TS') }}</td>
                            <td></td>
                          </tr>
                          <tr>
                            <td>Town House</td>
                            <td>{{ totalCustomerCountByBuildingType('R1','T','NR') }}</td>
                            <td>{{ totalCustomerCountByBuildingType('R1','T','MP') }}</td>
                            <td>{{ totalCustomerCountByBuildingType('R1','T','CP') }}</td>
                            <td>{{ totalCustomerCountByBuildingType('R1','T','CL') }}</td>
                            <td>{{ totalCustomerCountByBuildingType('R1','T','SS') }}</td>
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
                            <td>{{ totalCustomerCountByBuildingType('R2','V','TS') }}</td>
                            <td></td>
                          </tr>
                        </tbody>
                        <tfoot>                  
                          <tr>
                            <th><span class="text-dark" title="Total"><i class="fas fa-grip-lines"></i></span></th>
                            <th class="text-{{ internetHistoryTypeAbbreviationToFullName('NR')['color'] }}"></th>
                            <th class="text-{{ internetHistoryTypeAbbreviationToFullName('MP')['color'] }}"></th>
                            <th class="text-{{ internetHistoryTypeAbbreviationToFullName('CP')['color'] }}"></th>
                            <th class="text-{{ internetHistoryTypeAbbreviationToFullName('CL')['color'] }}"></th>
                            <th class="text-{{ internetHistoryTypeAbbreviationToFullName('SS')['color'] }}"></th>
                            <th class="text-{{ internetHistoryTypeAbbreviationToFullName('TS')['color'] }}"></th>
                            <th></th>
                          </tr>
                        </tfoot>
                      </table>     
                    </div>                   
                  </div>
                </div>
              </div><!-- /.card-body -->
            </div>
            <!-- /.card -->
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
    /* ChartJS
     * -------
     * Here we will create a few charts using ChartJS
     */
    
    //-------------
    //- PIE CHART -
    //-------------
    // Get context with jQuery - using jQuery's .get() method.
    var weeklyData        = {
      labels: [
          'New Registration', 
          'Change Plan',
          'Relocation', 
          'Termination', 
          'Suspension', 
          'Monthly Customer', 
      ],
      datasets: [
        {
          data: [{{ totalWeeklyCustomers('NR') }},{{ totalWeeklyCustomers('CP') }},{{ totalWeeklyCustomers('CL') }},{{ totalWeeklyCustomers('TS') }},{{ totalWeeklyCustomers('SS') }},{{ totalWeeklyCustomers('MP') }}],
          backgroundColor : ['#007bff', '#00c0ef', '#f39c12', '#dc3545', '#f56954', '#00a65a'],
        }
      ]
    }
    var pieChartCanvasWeekly = $('#pieChartWeekly').get(0).getContext('2d')
    var pieDataWeekly  = weeklyData;
    var pieOptionsWeekly = {
      maintainAspectRatio : false,
      responsive : true,
    }
    //Create pie or douhnut chart
    // You can switch between pie and douhnut using the method below.
    var pieChartWeekly = new Chart(pieChartCanvasWeekly, {
      type: 'pie',
      data: pieDataWeekly,
      options: pieOptionsWeekly      
    })

    var monthlyData        = {
      labels: [
          'New Registration', 
          'Change Plan',
          'Relocation', 
          'Termination', 
          'Suspension', 
          'Monthly Customer', 
      ],
      datasets: [
        {
          data: [{{ totalMonthlyCustomers('NR') }},{{ totalMonthlyCustomers('CP') }},{{ totalMonthlyCustomers('CL') }},{{ totalMonthlyCustomers('TS') }},{{ totalMonthlyCustomers('SS') }},{{ totalMonthlyCustomers('MP') }}],
          backgroundColor : ['#007bff', '#00c0ef', '#f39c12', '#dc3545', '#f56954', '#00a65a'],
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
          label               : 'Termination',
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
          label               : 'Suspension',
          backgroundColor     : '#f56954',
          borderColor         : 'rgba(60,141,188,0.8)',
          pointRadius          : false,
          pointColor          : '#f56954',
          pointStrokeColor    : 'rgba(60,141,188,1)',
          pointHighlightFill  : '#fff',
          pointHighlightStroke: 'rgba(60,141,188,1)',
          data                : [{{ totalYearlyCustomers('SS', 1) }}, {{ totalYearlyCustomers('SS', 2) }}, {{ totalYearlyCustomers('SS', 3) }}, {{ totalYearlyCustomers('SS', 4) }}, {{ totalYearlyCustomers('SS', 5) }}, {{ totalYearlyCustomers('SS', 6) }}, {{ totalYearlyCustomers('SS', 7) }}, {{ totalYearlyCustomers('SS', 8) }}, {{ totalYearlyCustomers('SS', 9) }}, {{ totalYearlyCustomers('SS', 10) }}, {{ totalYearlyCustomers('SS', 11) }}, {{ totalYearlyCustomers('SS', 12) }}]
        },
        {
          label               : 'Relocation',
          backgroundColor     : '#f39c12',
          borderColor         : 'rgba(60,141,188,0.8)',
          pointRadius          : false,
          pointColor          : '#f39c12',
          pointStrokeColor    : 'rgba(60,141,188,1)',
          pointHighlightFill  : '#fff',
          pointHighlightStroke: 'rgba(60,141,188,1)',
          data                : [{{ totalYearlyCustomers('CL', 1) }}, {{ totalYearlyCustomers('CL', 2) }}, {{ totalYearlyCustomers('CL', 3) }}, {{ totalYearlyCustomers('CL', 4) }}, {{ totalYearlyCustomers('CL', 5) }}, {{ totalYearlyCustomers('CL', 6) }}, {{ totalYearlyCustomers('CL', 7) }}, {{ totalYearlyCustomers('CL', 8) }}, {{ totalYearlyCustomers('CL', 9) }}, {{ totalYearlyCustomers('CL', 10) }}, {{ totalYearlyCustomers('CL', 11) }}, {{ totalYearlyCustomers('CL', 12) }}]
        },
        {
          label               : 'Change Plan',
          backgroundColor     : '#00c0ef',
          borderColor         : 'rgba(210, 214, 222, 1)',
          pointRadius         : false,
          pointColor          : 'rgba(210, 214, 222, 1)',
          pointStrokeColor    : '#00c0ef',
          pointHighlightFill  : '#fff',
          pointHighlightStroke: 'rgba(220,220,220,1)',
          data                : [{{ totalYearlyCustomers('CP', 1) }}, {{ totalYearlyCustomers('CP', 2) }}, {{ totalYearlyCustomers('CP', 3) }}, {{ totalYearlyCustomers('CP', 4) }}, {{ totalYearlyCustomers('CP', 5) }}, {{ totalYearlyCustomers('CP', 6) }}, {{ totalYearlyCustomers('CP', 7) }}, {{ totalYearlyCustomers('CP', 8) }}, {{ totalYearlyCustomers('CP', 9) }}, {{ totalYearlyCustomers('CP', 10) }}, {{ totalYearlyCustomers('CP', 11) }}, {{ totalYearlyCustomers('CP', 12) }}]
        },
        {
          label               : 'Monthly Customer',
          backgroundColor     : '#00a65a',
          borderColor         : 'rgba(60,141,188,0.8)',
          pointRadius          : false,
          pointColor          : '#00a65a',
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

  $(document).ready(function() {
    // $('#internet-summary tfoot th').each(function(i) {
    //   calculateColumn(i+1);
    // });

    $('table tbody tr').each(function () {
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
  });

  // function calculateColumn(index) {
  //   var total = 0;
  //   $('table tr').each(function() {
  //     var value = parseInt($('td', this).eq(index).text());
  //       if (!isNaN(value)) {
  //         total += value;
  //       }
  //   });
  //   $('table tfoot th').eq(index).text(total).css('font-weight','bold');
  // }

</script>

@endsection
