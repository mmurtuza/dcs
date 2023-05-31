@extends('backend.layouts.master')

@section('title','admin Config')

@section('style')
<style>
    /* The Modal (background) */
    .modal {
        display: none;
        /* Hidden by default */
        position: fixed;
        /* Stay in place */
        z-index: 1;
        /* Sit on top */
        padding-top: 150px;
        /* Location of the box */
        left: 0;
        top: 0;
        width: 100%;
        /* Full width */
        height: 100%;
        /* Full height */
        overflow: auto;
        /* Enable scroll if needed */
        background-color: rgb(0, 0, 0);
        /* Fallback color */
        background-color: rgba(0, 0, 0, 0.4);
        /* Black w/ opacity */
    }

    /* Modal Content */
    .modal-content {
        background-color: #fefefe;
        margin: auto;
        padding: 20px;
        border: 1px solid #888;
        width: 50%;
        display: flex;
    }

    /* The Close Button */
    .close {
        color: #aaaaaa;
        font-size: 28px;
        float: right;
        font-weight: bold;
    }

    .close:hover,
    .close:focus {
        color: #000;
        text-decoration: none;
        cursor: pointer;
    }

    .multi_select_box {
        width: 400px;
        margin: 80px;
    }

</style>
@endsection

@section('content')
<div class="content d-flex flex-column flex-column-fluid" id="kt_content">
    <!--begin::Subheader-->
    <div class="subheader py-2 py-lg-4 subheader-solid" id="kt_subheader">
        <div class="container-fluid d-flex align-items-center justify-content-between flex-wrap flex-sm-nowrap">
            <!--begin::Info-->
            <div class="d-flex align-items-center flex-wrap mr-2">
                <!--begin::Page Title-->
                <h5 class="text-dark font-weight-bold mt-2 mb-2 mr-5">Admin Configuration</h5>
            </div>
            <!--end::Info-->
        </div>
    </div>
    <div class="d-flex flex-column-fluid">
        <!--begin::Container-->
        <div class="container">
            <!--begin::Card-->
            <div class="card card-custom">
                {{-- <div class="card-header flex-wrap py-5">
                <div class="card-title">
                <h3 class="card-label">Form </h3>
                </div>
                </div> --}}
                <div class="card-body">
                    <!--begin: Datatable-->
                    <div class="row">
                        <div class="col-md-8 col-xs-12 col-sm-12 offset-md-2">
                            @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul style="margin-bottom: 0rem;">
                                    @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                            @endif
                            @if (Session::has('success'))
                            <div class="alert alert-success" role="success">
                                {{ Session::get('success') }}
                            </div>
                            @endif
                            @if (Session::has('error'))
                            <div class="alert alert-danger" role="success">
                                {{ Session::get('error') }}
                            </div>
                            @endif
                            {{-- <form action="{{route('store')}}" method="post" onsubmit="return confirm('Are you
                            sureyou want to submit?');" name="registration"> --}}
                            <div class="box-body">
                                <form action="{{url('config/admin-update', $adminData->id)}}" method="post">
                                    @csrf
                                    <div class="box-body">
                                        <div class="form-group">
                                            <label for="Name">Name</label>
                                            <input type="text" value="{{$adminData->name}}" class="form-control"
                                                id="Name" name="Name" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="username">User Name</label>
                                            <input type="text" value="{{$adminData->username}}" class="form-control"
                                                id="username" name="username" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="email">Email</label>
                                            <input type="text" value="{{$adminData->email}}" class="form-control"
                                                id="email" name="email" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="phone">Phone Number</label>
                                            <input type="text" value="{{$adminData->phone}}" class="form-control"
                                                id="phone" name="phone" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="userpin">User Pin</label>
                                            <input type="text" value="{{$adminData->userpin}}" class="form-control"
                                                id="userpin" name="userpin" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="password">Password</label>
                                            <input type="text" value="{{$adminData->password}}" class="form-control"
                                                id="password" name="password">
                                        </div>
                                        <div class="form-group">
                                            <label for="role">Role</label>
                                            <select class="form-control" name="role" id="role" required>
                                                @if(!$adminData->role=="1")
                                                    <option value="1">Super admin</option>
                                                @endif
                                                <option selected value="{{$adminData->role}}">
                                                    @if($adminData->role=="1")
                                                        Super admin
                                                    @else
                                                        Admin
                                                    @endif
                                                </option>
                                                @if(!$adminData->role=="0")
                                                    <option value="0">Admin</option>
                                                @endif
                                            </select>
                                        </div>
                                        <input type="hidden" id="status" name="status" value="1">
                                    </div><!-- /.box-body -->
                                    <div class="form-footer">
                                        <div class="row">
                                            <div class="col-md-4">

                                            </div>
                                            <div class="col-md-4">
                                                <button class="btn btn-secondary btn-block">@lang('actionBtn.update')</button>
                                            </div>
                                            <div class="col-md-4">

                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div><!-- /.box-body -->
                        </div>
                    </div>
                    <!--end: Datatable-->
                </div>
            </div>
            <!--end::Card-->
            <!-- popup window  -->


        </div>
        <!--end::Container-->

    </div>
</div>

@endsection


@section('script')
<script>
    // process select and find action start
    $(document).on('change', '#fprocess', function () {
        var process = $(this).val();

        $.ajax({
            url: "{{url('config/process-view')}}",
            type: 'POST',
            data: {
                "_token": "{{ csrf_token() }}",
                process: process,
            },
            success: function (data) {
                $("#faction").empty();
                $.each(data, function (key, value) {
                    $("#faction").append(`<option value="` + value.id + `">` + value
                        .actionname + `</option>`)

                });
            }
        });
    });

    // multiselect dropdown
    $('.multi_select').selectpicker();

</script>
@endsection
