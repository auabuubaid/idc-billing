<!-- Main Sidebar Container -->
<aside class="main-sidebar sidebar-dark-primary elevation-4">
  <!-- Brand Logo -->
  <a href="{{route('dashboard')}}" class="brand-link">
    <img src="{{ asset('assets/dist/img/logo.png') }}" alt="World City Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
    <span class="brand-text font-weight-dark">World City</span>
  </a>
  <!-- Sidebar -->
  <div class="sidebar">
    <!-- Digital Clock -->
    <div class="user-panel pb-3 mb-3 d-flex">
      <second class="clock">
        <div class="seconds"></div>
        <div class="minutes"></div>
        <div class="minute"></div>
        <div class="hour"></div>
      </second>
    </div>
    <!-- Sidebar Menu -->
    <nav class="mt-2">
      <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
        <!-- Add icons to the links using the .nav-icon class with font-awesome or any other icon font library -->
        <li class="nav-item">
          <a href="{{ route('dashboard') }}" class="nav-link{{ in_array(\Request::route()->getName(),['','dashboard']) ? ' active' : '' }}">
            <i class="nav-icon fas fa-tachometer-alt"></i>
            <p>
              Dashboard
            </p>
          </a>
        </li>
        @if(in_array($userArr->user_type,['IDC']))
        <li class="nav-item">
          <a href="{{ route('customers') }}" class="nav-link{{ in_array(\Request::route()->getName(),['customers', 'add-customer', 'view-customer', 'edit-customer']) ? ' active' : '' }}">
            <i class="nav-icon fas fa-users"></i>
            <p>
              Customers
            </p>
          </a>
        </li>
        <li class="nav-item nav-item has-treeview menu-{{ in_array(\Request::route()->getName(),['internet-subscribers', 'ppp-subscribers', 'internet-monthly-payment', 'new-internet-connection', 'internet-change-location', 'internet-change-service', 'internet-suspend-service', 'internet-reconnect-service', 'internet-terminate-service', 'view-internet-transaction', 'internet-history', 'internet-report','mptc-report','internet-monthly-invoice','edit-monthly-invoice','import-unpaid-internet','internet-advance-payment','add-internet-advance-payment','exchange-rate', 'add-exchange-rate','edit-exchange-rate','view-exchange-rate']) ? 'open' : '' }}">
          <a href="#" class="nav-link{{ in_array(\Request::route()->getName(),['internet-subscribers', 'ppp-subscribers', 'internet-monthly-payment', 'new-internet-connection', 'internet-change-location', 'internet-change-service', 'internet-suspend-service', 'internet-reconnect-service', 'internet-terminate-service', 'internet-history', 'view-internet-transaction', 'internet-report','mptc-report','internet-monthly-invoice','edit-monthly-invoice','import-unpaid-internet','internet-advance-payment','add-internet-advance-payment','exchange-rate', 'add-exchange-rate','edit-exchange-rate','view-exchange-rate']) ? ' active' : '' }}">
            <i class="nav-icon fas fa-wifi"></i>
            <p>
              Internet
              <i class="fas fa-angle-left right"></i>
            </p>
          </a>
          <ul class="nav nav-treeview">
            <li class="nav-item">
              <a href="{{ route('internet-subscribers') }}" class="nav-link{{ in_array(\Request::route()->getName(),['internet-subscribers']) ? ' active' : '' }}">
                <i class="fas fa-users nav-icon"></i>
                <p>Subscribers</p>
              </a>
            </li>
            @if(checkPermission($userArr->write))
            <li class="nav-item">
              <a href="{{ route('new-internet-connection') }}" class="nav-link{{ in_array(\Request::route()->getName(),['new-internet-connection']) ? ' active' : '' }}">
                <i class="fas fa-user-plus nav-icon"></i>
                <p>New Connection</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="{{ route('internet-change-service') }}" class="nav-link{{ in_array(\Request::route()->getName(),['internet-change-service', 'internet-change-location', 'internet-terminate-service', 'internet-suspend-service','internet-reconnect-service']) ? ' active' : '' }}">
                <i class="fas fa-exchange-alt nav-icon"></i>
                <p>Change Service</p>
              </a>
            </li>
            @endif
            <li class="nav-item">
              <a href="{{ route('internet-monthly-invoice') }}" class="nav-link{{ in_array(\Request::route()->getName(),['internet-monthly-invoice','edit-monthly-invoice','import-unpaid-internet']) ? ' active' : '' }}">
                <i class="far fa-calendar-alt nav-icon"></i>
                <p>Monthly Invoice</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="{{ route('internet-history') }}" class="nav-link{{ in_array(\Request::route()->getName(),['internet-history', 'view-internet-transaction']) ? ' active' : '' }}">
                <i class="fas fa-history nav-icon"></i>
                <p>History</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="{{ route('internet-advance-payment') }}" class="nav-link{{ in_array(\Request::route()->getName(),['internet-advance-payment','add-internet-advance-payment']) ? ' active' : '' }}">
                <i class="fas fa-money-bill-alt nav-icon"></i>
                <p>Advance Payment</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="{{ route('exchange-rate') }}" class="nav-link{{ in_array(\Request::route()->getName(),['exchange-rate', 'add-exchange-rate','edit-exchange-rate','view-exchange-rate']) ? ' active' : '' }}">
                <i class="fas fa-funnel-dollar nav-icon"></i>
                <p>Exchange Rate</p>
              </a>
            </li>
            <li class="nav-item nav-item has-treeview menu-{{ in_array(\Request::route()->getName(),['internet-report','mptc-report']) ? 'open' : '' }}">
              <a href="{{ route('internet-report') }}" class="nav-link">
                <i class="far fa-file-pdf nav-icon"></i>
                <p>
                  Report
                  <i class="fas fa-angle-left right"></i>
                </p>
              </a>
              <ul class="nav nav-treeview">
                <li class="nav-item">
                  <a href="{{ route('internet-report') }}" class="nav-link{{ in_array(\Request::route()->getName(),['internet-report']) ? ' active' : '' }}">
                    <i class="far fa-file-pdf nav-icon"></i>
                    <p>IDC Report</p>
                  </a>
                </li>
                <li class="nav-item">
                  <a href="{{ route('mptc-report') }}" class="nav-link{{ in_array(\Request::route()->getName(),['mptc-report']) ? ' active' : '' }}">
                    <i class="far fa-file-excel nav-icon"></i>
                    <p>MPTC Revenue</p>
                  </a>
                </li>                
              </ul>
            </li>
          </ul>
        </li>
        <li class="nav-item nav-item has-treeview menu-{{ in_array(\Request::route()->getName(),['cabletv-subscribers', 'cabletv-monthly-payment', 'new-cabletv-connection', 'cabletv-change-location', 'cabletv-terminate-service', 'cabletv-reconnect-service', 'cabletv-change-owner', 'view-cabletv-transaction', 'cabletv-history', 'cabletv-report','cabletv-monthly-invoice','edit-cabletv-monthly-invoice','import-unpaid-cabletv','cabletv-advance-payment','edit-cabletv-advance-payment']) ? 'open' : '' }}">
          <a href="#" class="nav-link{{ in_array(\Request::route()->getName(),['cabletv-subscribers', 'cabletv-monthly-payment', 'new-cabletv-connection', 'cabletv-change-location', 'cabletv-terminate-service', 'cabletv-reconnect-service', 'cabletv-change-owner', 'view-cabletv-transaction', 'cabletv-history', 'cabletv-report','cabletv-monthly-invoice','edit-cabletv-monthly-invoice','import-unpaid-cabletv','cabletv-advance-payment','edit-cabletv-advance-payment']) ? ' active' : '' }}">
            <i class="nav-icon fas fa-desktop"></i>
            <p>
              Cable TV
              <i class="fas fa-angle-left right"></i>
            </p>
          </a>
          <ul class="nav nav-treeview">
            <li class="nav-item">
              <a href="{{ route('cabletv-subscribers') }}" class="nav-link{{ in_array(\Request::route()->getName(),['cabletv-subscribers']) ? ' active' : '' }}">
                <i class="fas fa-users nav-icon"></i>
                <p>Subscribers</p>
              </a>
            </li>
            @if(checkPermission($userArr->write))
            <li class="nav-item">
              <a href="{{ route('new-cabletv-connection') }}" class="nav-link{{ in_array(\Request::route()->getName(),['new-cabletv-connection']) ? ' active' : '' }}">
                <i class="fas fa-user-plus nav-icon"></i>
                <p>New Connection</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="{{ route('cabletv-change-location') }}" class="nav-link{{ in_array(\Request::route()->getName(),['cabletv-change-location', 'cabletv-terminate-service', 'cabletv-reconnect-service', 'cabletv-change-owner']) ? ' active' : '' }}">
                <i class="fas fa-exchange-alt nav-icon"></i>
                <p>Change Service</p>
              </a>
            </li>
            @endif
            <li class="nav-item">
              <a href="{{ route('cabletv-monthly-invoice') }}" class="nav-link{{ in_array(\Request::route()->getName(),['cabletv-monthly-invoice','edit-cabletv-monthly-invoice','import-unpaid-cabletv']) ? ' active' : '' }}">
                <i class="far fa-calendar-alt nav-icon"></i>
                <p>Monthly Invoice</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="{{ route('cabletv-history') }}" class="nav-link{{ in_array(\Request::route()->getName(),['cabletv-history', 'view-cabletv-transaction']) ? ' active' : '' }}">
                <i class="fas fa-history nav-icon"></i>
                <p>History</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="{{ route('cabletv-advance-payment') }}" class="nav-link{{ in_array(\Request::route()->getName(),['cabletv-advance-payment','edit-cabletv-advance-payment']) ? ' active' : '' }}">
                <i class="fas fa-money-bill-alt nav-icon"></i>
                <p>Advance Payment</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="{{ route('cabletv-report') }}" class="nav-link{{ in_array(\Request::route()->getName(),['cabletv-report']) ? ' active' : '' }}">
                <i class="far fa-file-pdf nav-icon"></i>
                <p>Report</p>
              </a>
            </li>
          </ul>
        </li>
        @endif
        @if(in_array($userArr->user_type,['N']))
        <li class="nav-item">
          <a href="{{ route('summary-report') }}" class="nav-link{{ in_array(\Request::route()->getName(),['summary-report']) ? ' active' : '' }}">
            <i class="nav-icon fas fa-chart-bar"></i>
            <p>
              Summary Report
            </p>
          </a>
        </li>
        @endif
        <li class="nav-header">Exit</li>
        <li class="nav-item">
          <a href="{{ route('logout') }}" class="nav-link">
            <i class="nav-icon fas fa-sign-out-alt"></i>
            <p>
              Logout
            </p>
          </a>
        </li>        
        </ul>
      </nav>
    <!-- /.sidebar-menu -->
    </div>
  <!-- /.sidebar -->
  </aside>