@extends('admin.layout.app')

@section('title', 'Profile')

@section('content')

    <!-- Main Content -->
    <div class="main-content">
        <section class="section">
            <div class="section-body">
                <div class="row mt-sm-4">
                    <div class="col-12 col-md-12 col-lg-12">
                        <div class="card">
                            <div class="padding-20">
                                <ul class="nav nav-tabs" id="myTab2" role="tablist">
                                    <li class="nav-item"></li>
                                    <li class="nav-item">
                                        <a class="nav-link active" id="profile-tab2" data-toggle="tab" href="#settings"
                                            role="tab" aria-selected="true">Settings</a>
                                    </li>
                                </ul>

                                <div class="tab-content tab-bordered" id="myTab3Content">
                                    <div class="tab-pane fade active show" id="settings" role="tabpanel"
                                        aria-labelledby="profile-tab2">

                                        <form method="post" action="{{ url('admin/update-profile') }}"
                                            enctype="multipart/form-data">
                                            @csrf
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="form-group col-md-6 col-12">
                                                        <label>Name <span style="color: red;">*</span></label>
                                                        <input type="text" name="name" value="{{ $data->name }}"
                                                            class="form-control" placeholder="Enter Name" required>
                                                        @error('name')
                                                            <div class="text-danger">Please fill in the Name</div>
                                                        @enderror
                                                    </div>

                                                    <div class="form-group col-md-6 col-12">
                                                        <label>Email <span style="color: red;">*</span></label>
                                                        <input type="email" name="email" value="{{ $data->email }}"
                                                            class="form-control" placeholder="Enter Email" required>
                                                        @error('email')
                                                            <div class="text-danger">Please fill in the email</div>
                                                        @enderror
                                                    </div>
                                                </div>

                                                <div class="row">
                                                    <div class="form-group col-md-6 col-12">
                                                        <label>Profile Image</label>
                                                        <div class="custom-file">
                                                            <input type="file" name="image" class="custom-file-input" id="customFile">
                                                            <label class="custom-file-label" for="customFile">Choose file</label>
                                                            <div class="mt-2">
                                                                <img alt="image"
                                                                    src="{{ Auth::guard('admin')->check() ? asset(Auth::guard('admin')->user()->image) : (Auth::guard('subadmin')->check() ? asset(Auth::guard('subadmin')->user()->image) : asset('path/to/default/image.png')) }}"
                                                                    width="100">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
												{{-- Password Change Section --}}
												<h4 style="margin-top: 50px; padding-top: 20px;">Change Password</h4>
												<div class="row">
    <div class="form-group col-md-4 col-12">
        <label>Old Password</label>
        <div style="position: relative;">
            <input type="password" id="old_password" name="old_password" class="form-control" placeholder="Enter Old Password">
            <i class="fa fa-eye toggle-password" data-target="#old_password" style="position:absolute; right:15px; top:50%; transform:translateY(-50%); cursor:pointer;"></i>
        </div>
        @error('old_password')
            <small class="text-danger d-block">{{ $message }}</small>
        @enderror
    </div>

    <div class="form-group col-md-4 col-12">
        <label>New Password</label>
        <div style="position: relative;">
            <input type="password" id="new_password" name="new_password" class="form-control" placeholder="Enter New Password">
            <i class="fa fa-eye toggle-password" data-target="#new_password" style="position:absolute; right:15px; top:50%; transform:translateY(-50%); cursor:pointer;"></i>
        </div>
        @error('new_password')
            <small class="text-danger d-block">{{ $message }}</small>
        @enderror
    </div>

    <div class="form-group col-md-4 col-12">
        <label>Confirm Password</label>
        <div style="position: relative;">
            <input type="password" id="confirm_password" name="confirm_password" class="form-control" placeholder="Confirm Password">
            <i class="fa fa-eye toggle-password" data-target="#confirm_password" style="position:absolute; right:15px; top:50%; transform:translateY(-50%); cursor:pointer;"></i>
        </div>
        @error('confirm_password')
            <small class="text-danger d-block">{{ $message }}</small>
        @enderror
    </div>
</div>


												{{-- End Password Change Section --}}
                                                <div class="card-footer text-right p-0">
                                                    <button type="submit" class="btn btn-primary">Save Changes</button>
                                                </div>
                                            </div>
                                        </form>

                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection

