{{-- This tells Laravel to wrap this page inside your header layout --}}
@extends('layouts.app')

{{-- This injects your content into the @yield('content') placeholder inside the layout --}}
@section('content')
<div class="wrapper">
    <!-- Your main content, AdminLTE dashboard components, or text goes here -->
    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <h1>Welcome to the Dashboard</h1>
                <p>This page is securely rendered using your converted Laravel template.</p>
            </div>
        </section>
    </div>
</div>
@endsection
