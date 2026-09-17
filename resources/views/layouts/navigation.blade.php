<!-- Main Sidebar Container -->
<aside class="main-sidebar sidebar-dark-primary elevation-4 sidebar-no-expand">
    <!-- Brand Logo -->
    <a href="{{ route('dashboard') }}" class="brand-link bg-primary text-sm">
        <img src="{{ asset(config('settings.logo', 'assets/logo.png')) }}" 
             alt="Store Logo" 
             class="brand-image img-circle elevation-3 bg-black" 
             style="width: 1.8rem; height: 1.8rem; max-height: unset">
        <span class="brand-text font-weight-light">{{ config('settings.short_name', 'SMS') }}</span>
    </a>

    <!-- Sidebar Wrapper -->
    <div class="sidebar os-host os-theme-light os-host-overflow os-host-overflow-y os-host-resize-disabled os-host-transition os-host-scrollbar-horizontal-hidden">
        <div class="os-resize-observer-host observed"><div class="os-resize-observer" style="left: 0px; right: auto;"></div></div>
        <div class="os-size-auto-observer observed" style="height: calc(100% + 1px); float: left;"><div class="os-resize-observer"></div></div>
        <div class="os-content-glue" style="margin: 0px -8px; width: 249px; height: 646px;"></div>
        
        <div class="os-padding">
            <div class="os-viewport os-viewport-native-scrollbars-invisible" style="overflow-y: scroll;">
                <div class="os-content" style="padding: 0px 8px; height: 100%; width: 100%;">
                    <div class="clearfix"></div>

                    <!-- Sidebar Menu -->
                    <nav class="mt-4">
                        <ul class="nav nav-pills nav-sidebar flex-column text-sm nav-compact nav-flat nav-child-indent nav-collapse-hide-child" data-widget="treeview" role="menu" data-accordion="false">
                            
                            <!-- Dashboard -->
                            <li class="nav-item">
                                <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-tachometer-alt"></i>
                                    <p>Dashboard</p>
                                </a>
                            </li>

                            <!-- Purchase Order -->
                            <li class="nav-item">
                                <a href="{{ route('purchase-orders.index') }}" class="nav-link {{ request()->routeIs('purchase-orders.*') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-th-list"></i>
                                    <p>Purchase Order</p>
                                </a>
                            </li>

                            <!-- Back Order -->
                            <li class="nav-item">
                                <a href="{{ route('back-orders.index') }}" class="nav-link {{ request()->routeIs('back-orders.*') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-exchange-alt"></i>
                                    <p>Back Order</p>
                                </a>
                            </li>

                            <!-- Receiving -->
                            <li class="nav-item">
                                <a href="{{ route('receivings.index') }}" class="nav-link {{ request()->routeIs('receivings.*') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-boxes"></i>
                                    <p>Receiving</p>
                                </a>
                            </li>

                            <!-- Stocks -->
                            <li class="nav-item">
                                <a href="{{ route('stocks.index') }}" class="nav-link {{ request()->routeIs('stocks.*') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-table"></i>
                                    <p>Stocks</p>
                                </a>
                            </li>

                            <!-- Sale List -->
                            <li class="nav-item">
                                <a href="{{ route('sales.index') }}" class="nav-link {{ request()->routeIs('sales.*') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-file-invoice-dollar"></i>
                                    <p>Sale List</p>
                                </a>
                            </li>

                            <!-- Maintenance Check (Admin Only) -->
                            @if(auth()->user() && auth()->user()->type == \App\Enums\UserType::ADMIN)
                                <li class="nav-header">Maintenance</li>
                                
                                <!-- Supplier List -->
                                <li class="nav-item">
                                    <a href="{{ route('suppliers.index') }}" class="nav-link {{ request()->routeIs('suppliers.*') ? 'active' : '' }}">
                                        <i class="nav-icon fas fa-truck-loading"></i>
                                        <p>Supplier List</p>
                                    </a>
                                </li>

                                <!-- Item List -->
                                <li class="nav-item">
                                    <a href="{{ route('items.index') }}" class="nav-link {{ request()->routeIs('items.*') ? 'active' : '' }}">
                                        <i class="nav-icon fas fa-boxes"></i>
                                        <p>Item List</p>
                                    </a>
                                </li>

                                <!-- User List -->
                                <li class="nav-item">
                                    <a href="{{ route('users.index') }}" class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}">
                                        <i class="nav-icon fas fa-users"></i>
                                        <p>User List</p>
                                    </a>
                                </li>

                                <!-- Settings -->
                                <li class="nav-item">
                                    <a href="{{ route('settings.index') }}" class="nav-link {{ request()->routeIs('settings.*') ? 'active' : '' }}">
                                        <i class="nav-icon fas fa-cogs"></i>
                                        <p>Settings</p>
                                    </a>
                                </li>
                            @endif

                        </ul>
                    </nav>
                </div>
            </div>
        </div>

        <!-- Layout Scrollbars -->
        <div class="os-scrollbar os-scrollbar-horizontal os-scrollbar-unusable os-scrollbar-auto-hidden"><div class="os-scrollbar-track"><div class="os-scrollbar-handle"></div></div></div>
        <div class="os-scrollbar os-scrollbar-vertical os-scrollbar-auto-hidden"><div class="os-scrollbar-track"><div class="os-scrollbar-handle"></div></div></div>
        <div class="os-scrollbar-corner"></div>
    </div>
</aside>
