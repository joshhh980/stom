@php
    $isModal = request()->boolean('modal') || request()->ajax();
    $userType = old('type', $user->type ?? \App\Enums\UserType::STAFF->value);
@endphp

@if(!$isModal)
@extends('layouts.app')
@section('content')
@endif

<?php if(session('success')): ?>
<script>
    alert_toast("{{ session('success') }}", 'success');
</script>
<?php endif; ?>

<div class="card card-outline card-primary">
    <div class="card-body">
        <div class="container-fluid">
            <div id="msg"></div>
            <form action="{{ $user->exists ? route('users.update', $user) : route('users.store') }}" id="manage-user" enctype="multipart/form-data">
                @csrf
                @if($user->exists)
                    @method('PUT')
                @endif
                <input type="hidden" name="id" value="{{ $user->id ?? '' }}">
                <div class="form-group col-6">
                    <label for="name">First Name</label>
                    <input type="text" name="firstname" id="firstname" class="form-control" value="{{ old('firstname', $user->firstname ?? '') }}" required>
                </div>
                <div class="form-group col-6">
                    <label for="name">Last Name</label>
                    <input type="text" name="lastname" id="lastname" class="form-control" value="{{ old('lastname', $user->lastname ?? '') }}" required>
                </div>
                <div class="form-group col-6">
                    <label for="username">Username</label>
                    <input type="text" name="username" id="username" class="form-control" value="{{ old('username', $user->username ?? '') }}" required autocomplete="off">
                </div>
                <div class="form-group col-6">
                    <label for="password">Password</label>
                    <input type="password" name="password" id="password" class="form-control" value="" autocomplete="off" {{ $user->exists ? '' : 'required' }}>
                    @if($user->exists)
                        <small class="text-info"><i>Leave this blank if you dont want to change the password.</i></small>
                    @endif
                </div>
                <div class="form-group col-6">
                    <label for="type">User Type</label>
                    <select name="type" id="type" class="custom-select" required>
                        <option value="{{ \App\Enums\UserType::ADMIN->value }}" {{ old('type', $userType) == \App\Enums\UserType::ADMIN->value ? 'selected' : '' }}>Administrator</option>
                        <option value="{{ \App\Enums\UserType::STAFF->value }}" {{ old('type', $userType) == \App\Enums\UserType::STAFF->value ? 'selected' : '' }}>Staff</option>
                    </select>
                </div>
                <div class="form-group col-6">
                    <label for="customFile" class="control-label">Avatar</label>
                    <div class="custom-file">
                        <input type="file" class="custom-file-input rounded-circle" id="customFile" name="img" onchange="displayImg(this,$(this))">
                        <label class="custom-file-label" for="customFile">Choose file</label>
                    </div>
                </div>
                <div class="form-group col-6 d-flex justify-content-center">
                    <img src="{{ $user->avatar ? asset($user->avatar) : asset('uploads/default-avatar.png') }}" alt="" id="cimg" class="img-fluid img-thumbnail">
                </div>
            </form>
        </div>
    </div>
    <div class="card-footer">
        <div class="col-md-12">
            <div class="row">
                <button class="btn btn-sm btn-primary mr-2" form="manage-user">Save</button>
                <a class="btn btn-sm btn-secondary" href="{{ route('users.index') }}">Cancel</a>
            </div>
        </div>
    </div>
</div>
<style>
    img#cimg {
        height: 15vh;
        width: 15vh;
        object-fit: cover;
        border-radius: 100% 100%;
    }
</style>
<script>
    $(function(){
        $('.select2').select2({ width: 'resolve' });
    });

    function displayImg(input,_this) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function (e) {
                $('#cimg').attr('src', e.target.result);
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    $('#manage-user').submit(function(e){
        e.preventDefault();
        var _this = $(this);
        start_loader();
        $.ajax({
            url: _this.attr('action'),
            data: new FormData($(this)[0]),
            cache: false,
            contentType: false,
            processData: false,
            method: 'POST',
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            success:function(resp){
                if(resp && resp.status === 'success'){
                    location.href = '{{ route('users.index') }}';
                }else{
                    $('#msg').html('<div class="alert alert-danger">' + (resp.message || 'Username already exist') + '</div>');
                    $("html, body").animate({ scrollTop: 0 }, "fast");
                }
                end_loader();
            },
            error:function(xhr){
                let message = 'Username already exist';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }
                $('#msg').html('<div class="alert alert-danger">' + message + '</div>');
                $("html, body").animate({ scrollTop: 0 }, "fast");
                end_loader();
            }
        });
    });
</script>
@if(!$isModal)
@endsection
@endif
