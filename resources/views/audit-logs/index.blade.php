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

<div class="card card-outline card-primary">
    <div class="card-header">
        <h3 class="card-title">Audit Logs</h3>
    </div>
    <div class="card-body">
        <div class="container-fluid">
            <table class="table table-hover table-striped">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>User</th>
                        <th>Action</th>
                        <th>Details</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($auditLogs as $log)
                        <tr>
                            <td>{{ $log->created_at?->format('Y-m-d H:i:s') }}</td>
                            <td>{{ $log->user ? trim($log->user->firstname . ' ' . $log->user->lastname) : 'Unauthenticated' }}</td>
                            <td>{{ $log->action }}</td>
                            <td>{{ $log->details }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center">No audit logs found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            {{ $auditLogs->links() }}
        </div>
    </div>
</div>
</div>
@endsection
