@extends('layouts.app')

@section('content')
<div class="card card-outline card-primary">
	<div class="card-header">
		<h3 class="card-title">List of Item</h3>
        <div class="card-tools d-flex justify-content-between">
			<div>
				<a href="javascript:void(0)" id="create_new" class="btn btn-flat btn-primary"><span class="fas fa-plus"></span>  Add New</a>
			</div>
			<div class="ml-2">
				<a href="javascript:void(0)" class="btn btn-flat btn-primary">Export Products</a>
			</div>
			<div class="position-relative d-inline-block ml-2">
				<form action="javascript:void(0)" method="POST" enctype="multipart/form-data">
					<button class="btn btn-flat btn-primary position-relative" type="submit">Update Item Costs</button>
				</form>
			</div>
		</div>
	</div>
	<div class="card-body">
		<div class="container-fluid">
        <div class="container-fluid">
			<table class="table table-hovered table-striped">
				<colgroup>
					<col width="5%">
					<col width="15%">
					<col width="45%">
					<col width="15%">
					<col width="20%">
				</colgroup>
				<thead>
					<tr>
						<th>#</th>
						<th>Date Created</th>
						<th>Name</th>
						<th>Status</th>
						<th>Action</th>
					</tr>
				</thead>
				<tbody>
                    @foreach($items as $item)
					<tr>
						<td class="text-center">{{ $loop->iteration }}</td>
						<td>{{ \Carbon\Carbon::parse($item->date_created)->format('Y-m-d H:i') }}</td>
						<td>{{ $item->name }}</td>
						<td class="text-center">
								@if($item->isActive())
                                    <span class="badge badge-success rounded-pill">Active</span>
                                @else
                                    <span class="badge badge-danger rounded-pill">Inactive</span>
                                @endif
                            </td>
						<td align="center">
							 <button type="button" class="btn btn-flat btn-default btn-sm dropdown-toggle dropdown-icon" data-toggle="dropdown">
			                  		Action
			                    <span class="sr-only">Toggle Dropdown</span>
			                  </button>
			                  <div class="dropdown-menu" role="menu">
			                    <a class="dropdown-item view_data" href="javascript:void(0)" data-id="{{ $item->id }}"><span class="fa fa-eye text-dark"></span> View</a>
			                    <div class="dropdown-divider"></div>
			                    <a class="dropdown-item edit_data" href="javascript:void(0)" data-id="{{ $item->id }}"><span class="fa fa-edit text-primary"></span> Edit</a>
			                    <div class="dropdown-divider"></div>
			                    <a class="dropdown-item delete_data" href="javascript:void(0)" data-id="{{ $item->id }}"><span class="fa fa-trash text-danger"></span> Delete</a>
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
		$('.delete_data').click(function(){
			_conf("Are you sure to delete this Item permanently?","delete_category",[$(this).attr('data-id')])
		})
		$('#create_new').click(function(){
			uni_modal("<i class='fa fa-plus'></i> Add New Item","{{ route('items.create') }}","mid-large")
		})
		$('.edit_data').click(function(){
			uni_modal("<i class='fa fa-edit'></i> Edit Item Details","{{ url('items') }}/"+$(this).attr('data-id')+"/edit","mid-large")
		})
		$('.view_data').click(function(){
			uni_modal("<i class='fa fa-box'></i> Item Details","{{ url('items') }}/"+$(this).attr('data-id'),"")
		})
		$('.table td,.table th').addClass('py-1 px-2 align-middle')
		$('.table').dataTable();
	})
	function delete_category($id){
		start_loader();
		$.ajax({
			url:"{{ url('items') }}/"+$id,
			method:"POST",
			data:{_method: "DELETE", _token: '{{ csrf_token() }}'},
			headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}'},
			dataType:"json",
			error:err=>{
				console.log(err)
				alert_toast("An error occured.",'error');
				end_loader();
			},
			success:function(resp){
				if(typeof resp== 'object' && resp.status == 'success'){
					location.reload();
				}else{
					alert_toast("An error occured.",'error');
					end_loader();
				}
			}
		})
	}
</script>
@endsection
