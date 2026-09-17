<style>
    .user-img {
        display: block;
        height: 27px;
        width: 27px;
        object-fit: cover;
        border-radius: 50%;
        flex-shrink: 0;
    }

    .btn-rounded {
        border-radius: 50px;
    }
</style>

<nav class="main-header navbar navbar-expand navbar-dark border border-light border-top-0 border-left-0 border-right-0 navbar-light text-sm bg-lightblue">
    <ul class="navbar-nav">
        <li class="nav-item">
            <a class="nav-link" data-widget="pushmenu" href="#" role="button">
                <i class="fas fa-bars"></i>
            </a>
        </li>
        <li class="nav-item d-none d-sm-inline-block">
            <a href="{{ route('dashboard') }}" class="nav-link">
                {{ config('settings.name', 'SMS') }} - Admin
            </a>
        </li>
    </ul>

    <ul class="navbar-nav ml-auto">
        @php($user = auth()->user())
        <li class="nav-item">
            <div class="btn-group nav-link">
                <button type="button" class="btn btn-rounded badge badge-light dropdown-toggle dropdown-icon" data-toggle="dropdown">
                    <img src="{{ asset($user->avatar ?: 'assets/avatar.png') }}" class="img-circle elevation-2 user-img" alt="User Image">
                    <span class="ml-3">{{ $user->name ?: $user->username }}</span>
                    <span class="sr-only">Toggle Dropdown</span>
                </button>
                <div class="dropdown-menu dropdown-menu-right" role="menu">
                    <a class="dropdown-item" href="{{ Route::has('profile.edit') ? route('profile.edit') : '#' }}">
                        <span class="fa fa-user"></span> My Account
                    </a>
                    <div class="dropdown-divider"></div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="dropdown-item">
                            <span class="fas fa-sign-out-alt"></span> Logout
                        </button>
                    </form>
                </div>
            </div>
        </li>
    </ul>
</nav>
