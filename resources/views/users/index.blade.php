@extends('layouts.app')

@section('content')
<?php if(session('success')): ?>
<script>
    alert_toast("{{ session('success') }}", 'success');
</script>
<?php endif; ?>

<style>
    .img-avatar {
        width:45px;
        height:45px;
        object-fit:cover;
        object-position:center center;
        border-radius:100%;
    }
</style>
<div class="card card-outline card-primary">
    <div class="card-header">
        <h3 class="card-title">List of System Users</h3>
        <div class="card-tools">
            <a href="javascript:void(0)" id="create_new" class="btn btn-flat btn-primary"><span class="fas fa-plus"></span> Create New</a>
        </div>
    </div>
    <div class="card-body">
        <div class="container-fluid">
            <div class="container-fluid">
                <table class="table table-hover table-striped">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Avatar</th>
                            <th>Name</th>
                            <th>Username</th>
                            <th>User Type</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $user)
                            <tr>
                                <td class="text-center">{{ $loop->iteration }}</td>
                                <td class="text-center">
                                    <img src="{{ $user->avatar ? asset($user->avatar) : asset('uploads/default-avatar.png') }}" class="img-avatar img-thumbnail p-0 border-2" alt="user_avatar">
                                </td>
                                <td>{{ ucwords(trim(($user->firstname ?? '') . ' ' . ($user->lastname ?? ''))) }}</td>
                                <td><p class="m-0 truncate-1">{{ $user->username }}</p></td>
                                <td><p class="m-0">{{ $user->type == \App\Enums\UserType::ADMIN->value ? 'Administrator' : 'Staff' }}</p></td>
                                <td align="center">
                                    <button type="button" class="btn btn-flat btn-default btn-sm dropdown-toggle dropdown-icon" data-toggle="dropdown">
                                        Action
                                        <span class="sr-only">Toggle Dropdown</span>
                                    </button>
                                    <div class="dropdown-menu" role="menu">
                                        <a class="dropdown-item edit_data" href="javascript:void(0)" data-id="{{ $user->id }}"><span class="fa fa-edit text-primary"></span> Edit</a>
                                        <div class="dropdown-divider"></div>
                                        <a class="dropdown-item delete_data" href="javascript:void(0)" data-id="{{ $user->id }}"><span class="fa fa-trash text-danger"></span> Delete</a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<script>
    $(document).ready(function(){
        $('#create_new').click(function(){
            window.location.href = "{{ route('users.create') }}";
        });

        $(document).on('click', '.edit_data', function(){
            let id = $(this).attr('data-id');
            window.location.href = "{{ url('users') }}/" + id + "/edit";
        });

        $('.delete_data').click(function(){
            _conf('Are you sure to delete this User permanently?', 'delete_user', [$(this).attr('data-id')]);
        });
        $('.table td,.table th').addClass('py-1 px-2 align-middle');
        $('.table').dataTable();
    });

    function delete_user($id){
        start_loader();
        $.ajax({
            url: '{{ url("users") }}/' + $id,
            method: 'POST',
            data: {
                _method: 'DELETE',
                _token: '{{ csrf_token() }}'
            },
            dataType: 'json',
            error: err => {
                console.log(err);
                alert_toast('An error occured.', 'error');
                end_loader();
            },
            success: function(resp){
                if (typeof resp == 'object' && resp.status == 'success') {
                    location.reload();
                } else {
                    alert_toast('An error occured.', 'error');
                    end_loader();
                }
            }
        });
    }
</script>
@endsection
