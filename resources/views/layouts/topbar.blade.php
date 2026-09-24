<style>
  .user-img {
        position: absolute;
        height: 27px;
        width: 27px;
        object-fit: cover;
        left: -7%;
        top: -12%;
  }
  .btn-rounded {
        border-radius: 50px;
  }
  </style>
<nav class="main-header navbar navbar-expand navbar-dark border border-light border-top-0 border-left-0 border-right-0 navbar-light text-sm bg-lightblue">
    <!-- Left navbar links -->
    <ul class="navbar-nav">
        <li class="nav-item">
            <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
        </li>
        <li class="nav-item d-none d-sm-inline-block">
            <a href="{{ url('/') }}" class="nav-link">
                {{-- Checks agent type; falls back to config if helper isn't installed --}}
                @if(request()->userAgent() && !Str::contains(request()->userAgent(), ['iPhone', 'Android', 'Mobile']))
                    {{ config('app.name', 'Laravel') }}
                @else
                    {{ config('app.short_name', 'Lvl') }}
                @endif
                - Admin
            </a>
        </li>
    </ul>

    <!-- Right navbar links -->
    <ul class="navbar-nav ml-auto">
        <!-- Messages Dropdown Menu -->
        <li class="nav-item">
            <div class="btn-group nav-link">
                <button type="button" class="btn btn-rounded badge badge-light dropdown-toggle dropdown-icon" data-toggle="dropdown">
                    <span>
                        {{-- Grabs the logged-in user avatar, falling back to a default if null --}}
                        <img src="{{ auth()->user()->avatar ? asset('storage/' . auth()->user()->avatar) : asset('images/default-avatar.png') }}" class="img-circle elevation-2 user-img" alt="User Image">
                    </span>
                    <span class="ml-3">{{ Str::title(auth()->user()->firstname . ' ' . auth()->user()->lastname) }}</span>
                    <span class="sr-only">Toggle Dropdown</span>
                </button>
                <div class="dropdown-menu" role="menu">
                    <a class="dropdown-item" href="#">
                        <span class="fa fa-user"></span> My Account
                    </a>
                    <div class="dropdown-divider"></div>
                    
                    <!-- Laravel Logout Form (Best Practice for CSRF Security) -->
                    <form method="POST" action="{{ route('logout') }}" id="logout-form" class="d-none">
                        @csrf
                    </form>
                    <a class="dropdown-item" href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                        <span class="fas fa-sign-out-alt"></span> Logout
                    </a>
                </div>
            </div>
        </li>
    </ul>
</nav>
<!-- /.navbar -->
