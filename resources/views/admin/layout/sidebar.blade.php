<!-- Main Sidebar Container -->
<aside class="main-sidebar sidebar-dark-primary elevation-4">
  <!-- Brand Logo -->
  <a href="{{ route('admin-dashboard') }}" class="brand-link">
    <img src="{{ asset('assets/dist/img/logo.png') }}" alt="World City Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
    <span class="brand-text font-weight-dark">World City</span>
  </a>
  <!-- Sidebar -->
  <div class="sidebar">
    <!-- Sidebar user panel (optional) -->
    <div class="user-panel mt-3 pb-3 mb-3 d-flex">
      <div class="image">
        <img src="{{ asset('assets/dist/img/captain_america.png') }}" class="img-circle elevation-1" alt="User Image">
      </div>
      <div class="info">
        <a href="#" class="d-block">{{ $admin_arr->name }}</a>
      </div>
    </div>
    <!-- Sidebar Menu -->
    <nav class="mt-2">
      <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
        <!-- Add icons to the links using the .nav-icon class with font-awesome or any other icon font library -->
        <li class="nav-item">
          <a href="{{ route('admin-dashboard') }}" class="nav-link {{ in_array(\Request::route()->getName(),['','admin-dashboard']) ? 'active' : '' }}">
            <i class="nav-icon fas fa-tachometer-alt"></i>
            <p>
              Dashboard
            </p>
          </a>
        </li>
        <li class="nav-item">
          <a href="{{ route('buildings-location') }}" class="nav-link {{ in_array(\Request::route()->getName(),['buildings-location','add-building-location','view-building-location','edit-building-location','import-buildings-location']) ? 'active' : '' }}">
            <i class="nav-icon fas fa-map-marked-alt"></i>
            <p>
              Buildings Location
            </p>
          </a>
        </li>
        <li class="nav-item">
          <a href="{{ route('units-address') }}" class="nav-link {{ in_array(\Request::route()->getName(),['units-address','add-unit-address','view-unit-address','edit-unit-address','import-units-address']) ? 'active' : '' }}">
            <i class="nav-icon fas fa-door-open"></i>
            <p>
              Units Address
            </p>
          </a>
        </li>
        <li class="nav-item">
          <a href="{{ route('admin-customers') }}" class="nav-link {{ in_array(\Request::route()->getName(),['admin-customers','view-admin-customer','admin-import-customers','admin-export-customers']) ? 'active' : '' }}">
            <i class="nav-icon fas fa-users"></i>
            <p>
              Customers
            </p>
          </a>
        </li>
        <li class="nav-item">
          <a href="{{ route('old-monthly-payment') }}" class="nav-link {{ in_array(\Request::route()->getName(),['old-monthly-payment','import-old-monthly-payment']) ? 'active' : '' }}">
            <i class="nav-icon fas fa-users"></i>
            <p>
              Old Monthly Payment
            </p>
          </a>
        </li>
        <li class="nav-item nav-item has-treeview menu-{{ in_array(\Request::route()->getName(),['internet-services', 'add-internet-service', 'view-internet-service', 'edit-internet-service', 'cabletv-services', 'add-cabletv-service', 'view-cabletv-service', 'edit-cabletv-service']) ? 'open' : '' }}">
          <a href="#" class="nav-link {{ in_array(\Request::route()->getName(),['internet-services', 'add-internet-service', 'view-internet-service', 'edit-internet-service', 'cabletv-services', 'add-cabletv-service', 'view-cabletv-service', 'edit-cabletv-service']) ? 'active' : '' }}">
            <i class="nav-icon fas fa-sitemap"></i>
            <p>
              IDC Services
              <i class="fas fa-angle-left right"></i>
            </p>
          </a>
          <ul class="nav nav-treeview">
            <li class="nav-item">
              <a href="{{ route('internet-services') }}" class="nav-link {{ in_array(\Request::route()->getName(),['internet-services','add-internet-service','view-internet-service','edit-internet-service']) ? 'active' : '' }}">
                <i class="fas fa-wifi nav-icon"></i>
                <p>Internet Plans</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="{{ route('cabletv-services') }}" class="nav-link {{ in_array(\Request::route()->getName(),['cabletv-services','add-cabletv-service','view-cabletv-service','edit-cabletv-service']) ? 'active' : '' }}">
                <i class="fas fa-tv nav-icon"></i>
                <p>CableTV Plans</p>
              </a>
            </li>
            <!-- <li class="nav-item">
              <a href="/" class="nav-link">
                <i class="fas fa-tv nav-icon"></i>
                <p>IP TV</p>
              </a>
            </li> -->
          </ul>
        </li>
        <li class="nav-item">
          <a href="{{ route('users') }}" class="nav-link {{ in_array(\Request::route()->getName(),['users','add-user','edit-user','view-user']) ? 'active' : '' }}">
            <i class="nav-icon fas fa-users"></i>
            <p>
              Users
            </p>
          </a>
        </li>
        <li class="nav-header">Exit</li>
        <li class="nav-item">
          <a href="{{ route('admin-logout') }}" class="nav-link">
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