@extends('layouts.app')

@section('content')
<style>
    body {
        background-image: url("{{ asset(config('settings.cover', 'assets/cover.jpg')) }}") !important;
        background-size: cover;
        background-position: center center;
        background-repeat: no-repeat;
        background-attachment: fixed;
        min-height: 100vh;
    }
    .login-title { text-shadow: 2px 2px black; }
    .login-page { display: flex; flex-direction: column; justify-content: center; align-items: center; height: 100vh; background-color: transparent !important; }
</style>

<div class="login-page dark-mode">
    <h1 class="text-center py-5 login-title"><b>{{ config('settings.name', 'System Name') }}</b></h1>

    <div class="login-box">
        <div class="card card-outline card-primary">
            <div class="card-header text-center">
                <a href="{{ url('/') }}" class="h1"><b>Login</b></a>
            </div>
            <div class="card-body">
                <p class="login-box-msg">Sign in to start your session</p>

                <!-- Native Session Status (e.g., Password Reset confirmation) -->
                @if (session('status'))
                    <div class="alert alert-success p-2 text-sm mb-2">{{ session('status') }}</div>
                @endif

                <!-- Native Input Errors Display -->
                @if ($errors->any())
                    <div class="alert alert-danger p-2 text-sm">
                        <ul class="mb-0 pl-3">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- Action points directly to native Breeze route -->
                <form method="POST" action="{{ route('login') }}">
                    @csrf {{-- Mandatory Security Token --}}

                    <!-- Username Input Field -->
                    <div class="input-group mb-3">
                        <input type="text" class="form-control" name="username" value="{{ old('username') }}" placeholder="Username" required autofocus autocomplete="username">
                        <div class="input-group-append">
                            <div class="input-group-text"><span class="fas fa-user"></span></div>
                        </div>
                    </div>


                    <!-- Password -->
                    <div class="input-group mb-3">
                        <input type="password" class="form-control" name="password" placeholder="Password" required autocomplete="current-password">
                        <div class="input-group-append">
                            <div class="input-group-text"><span class="fas fa-lock"></span></div>
                        </div>
                    </div>

                    <!-- Remember Me Box -->
                    <div class="row">
                        <div class="col-8">
                            <div class="icheck-primary">
                                <input type="checkbox" id="remember_me" name="remember">
                                <label for="remember_me">Remember Me</label>
                            </div>
                        </div>
                        <div class="col-4">
                            <button type="submit" class="btn btn-primary btn-block">Sign In</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
