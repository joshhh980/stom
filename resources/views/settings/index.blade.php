@extends('layouts.app')

@section('content')
<style>
    img#cimg {
        height: 15vh;
        width: 15vh;
        object-fit: scale-down;
        border-radius: 100% 100%;
    }
    img#cimg2 {
        height: 50vh;
        width: 100%;
        object-fit: contain;
    }
</style>
<div class="container-fluid p-4">
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show rounded-0" role="alert">
        <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    @endif

<div class="col-lg-12">
    <div class="card card-outline card-primary">
        <div class="card-header">
            <h5 class="card-title">System Information</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('settings.update') }}" id="system-settings-frm" method="POST" enctype="multipart/form-data">
                @csrf
                <div id="msg" class="form-group"></div>
                <div class="form-group">
                    <label for="name" class="control-label">System Name</label>
                    <input type="text" class="form-control form-control-sm" name="name" id="name" value="{{ old('name', $settings['name'] ?? '') }}">
                </div>
                <div class="form-group">
                    <label for="short_name" class="control-label">System Short Name</label>
                    <input type="text" class="form-control form-control-sm" name="short_name" id="short_name" value="{{ old('short_name', $settings['short_name'] ?? '') }}">
                </div>
                <div class="form-group">
                    <label for="customFile" class="control-label">System Logo</label>
                    <div class="custom-file">
                        <input type="file" class="custom-file-input rounded-circle" id="customFile" name="img" onchange="displayImg(this,$(this))">
                        <label class="custom-file-label" for="customFile">Choose file</label>
                    </div>
                </div>
                <div class="form-group d-flex justify-content-center">
                    <img src="{{ asset($settings['logo'] ?? 'assets/logo.png') }}" alt="" id="cimg" class="img-fluid img-thumbnail">
                </div>
                <div class="form-group">
                    <label for="customCover" class="control-label">Cover</label>
                    <div class="custom-file">
                        <input type="file" class="custom-file-input rounded-circle" id="customCover" name="cover" onchange="displayImg2(this,$(this))">
                        <label class="custom-file-label" for="customCover">Choose file</label>
                    </div>
                </div>
                <div class="form-group d-flex justify-content-center">
                    <img src="{{ asset($settings['cover'] ?? 'assets/cover.jpg') }}" alt="" id="cimg2" class="img-fluid img-thumbnail">
                </div>
            </form>
        </div>
        <div class="card-footer">
            <div class="col-md-12">
                <div class="row">
                    <button class="btn btn-sm btn-primary" form="system-settings-frm">Update</button>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
<script>
    function displayImg(input, _this) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function (e) {
                $('#cimg').attr('src', e.target.result);
                _this.siblings('.custom-file-label').html(input.files[0].name);
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    function displayImg2(input, _this) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function (e) {
                _this.siblings('.custom-file-label').html(input.files[0].name);
                $('#cimg2').attr('src', e.target.result);
            };
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>
@endsection
