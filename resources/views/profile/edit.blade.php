@extends('layouts.app')

@section('content')
<div class="container-fluid p-4">
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show rounded-0" role="alert">
        <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    @endif

    @if($errors->any())
    <div class="alert alert-danger rounded-0">
        <ul class="mb-0 pl-3">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="card card-outline card-primary">
        <div class="card-header"><h4 class="card-title">My Account</h4></div>
        <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
            @csrf
            @method('PATCH')
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6"><div class="form-group">
                        <label for="firstname">First Name</label>
                        <input id="firstname" name="firstname" class="form-control" value="{{ old('firstname', $user->firstname) }}" required>
                    </div></div>
                    <div class="col-md-6"><div class="form-group">
                        <label for="lastname">Last Name</label>
                        <input id="lastname" name="lastname" class="form-control" value="{{ old('lastname', $user->lastname) }}" required>
                    </div></div>
                </div>
                <div class="form-group">
                    <label for="username">Username</label>
                    <input id="username" name="username" class="form-control" value="{{ old('username', $user->username) }}" required autocomplete="username">
                </div>
                <div class="form-group">
                    <label for="password">New Password</label>
                    <input id="password" name="password" type="password" class="form-control" autocomplete="new-password">
                    <small class="form-text text-muted">Leave blank to keep your current password.</small>
                </div>
                <div class="form-group">
                    <label for="password_confirmation">Confirm New Password</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" class="form-control" autocomplete="new-password">
                </div>
                <div class="form-group">
                    <label for="avatar">Avatar</label>
                    <div class="custom-file">
                        <input id="avatar" name="avatar" type="file" class="custom-file-input" accept="image/jpeg,image/png">
                        <label class="custom-file-label" for="avatar">Choose file</label>
                    </div>
                </div>
                <div class="text-center">
                    <img src="{{ $user->avatar ? asset('storage/' . $user->avatar) : asset('images/default-avatar.png') }}" alt="User avatar" class="img-thumbnail rounded-circle" style="width: 120px; height: 120px; object-fit: cover;">
                </div>
            </div>
            <div class="card-footer text-right">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i> Update</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $('#avatar').on('change', function() {
        $(this).next('.custom-file-label').text(this.files[0]?.name || 'Choose file');
    });
</script>
@endpush
