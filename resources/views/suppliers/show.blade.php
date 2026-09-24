<style>
    #uni_modal .modal-footer {
        display: none;
    }
</style>
<div class="container-fluid" id="print_out">
    <div id="transaction-printable-details" class="position-relative">
        <div class="row">
            <fieldset class="w-100">
                <div class="col-12">
                    <dl>
                        <dt class="text-info">Name:</dt>
                        <dd class="pl-3">{{ $supplier->name }}</dd>
                        <dt class="text-info">Address:</dt>
                        <dd class="pl-3">{{ $supplier->address ?? '' }}</dd>
                        <dt class="text-info">Contact Person:</dt>
                        <dd class="pl-3">{{ $supplier->cperson ?? '' }}</dd>
                        <dt class="text-info">Contact #:</dt>
                        <dd class="pl-3">{{ $supplier->contact ?? '' }}</dd>
                        <dt class="text-info">Status:</dt>
                        <dd class="pl-3">
                            @if($supplier->status === \App\Enums\Status::ACTIVE)
                                <span class="badge badge-success rounded-pill">Active</span>
                            @else
                                <span class="badge badge-danger rounded-pill">Inactive</span>
                            @endif
                        </dd>
                    </dl>
                </div>
            </fieldset>
        </div>
    </div>
</div>
<div class="form-group">
    <div class="col-12">
        <div class="d-flex justify-content-end align-items-center">
            <button class="btn btn-dark btn-flat" type="button" data-dismiss="modal">Close</button>
        </div>
    </div>
</div>
