@extends('backend.layouts.master')

@section('title','Auth-config')
@section('style')
<style>
    .box-footer {
        margin-bottom: 150px;
    }

    .card-body {
        overflow: hidden;
    }

    .view_btn {
        text-decoration: none;
        color: #fff;
        padding: 10px 20px;
        background-color: #FB3199;
        border-radius: 10%;
    }

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
        left: 145px;
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
                <h5 class="text-dark font-weight-bold mt-2 mb-2 mr-5">@lang('authConfig.header')</h5>
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

                            <form action="{{route('auth-update')}}" method="post">
                                @csrf
                                <div class="role-check">
                                    <center>
                                        <h3>@lang('authConfig.intro')</h3>
                                    </center>

                                    <div class="form-group">
                                        <label for="roleId">@lang('authConfig.role')</label>
                                        <select id="roleId" name="roleid" class="form-control">
                                            <option selected disabled>Select</option>
                                            @foreach($roleHierarchy as $row)
                                            {{-- @php
                                        $db = config('database.db');
                                        $role_hierarchy = DB::table($db.'.role_hierarchies')->select('position','designation')->where('position', $auth->roleId)->where('projectcode',session('projectcode'))->first();
                                        @endphp --}}
                                            <option value="{{$row->position}}">{{$row->designation}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <table id="table" class="table">
                                        </table>
                                    </div>
                                    <div class="box-footer">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <button type="reset" id="add_btn" class="btn btn-warning btn-block">@lang('actionBtn.addNew')</button>
                                            </div>
                                            <div class="col-md-6">
                                                <button type="submit" class="btn btn-secondary btn-block">@lang('actionBtn.update')</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>


                        </div>
                        <!--end: Datatable-->
                    </div>
                </div>
                <!--end::Card-->

            </div>
            <!--end::Container-->
        </div>
    </div>
</div>
<!-- The Modal -->
<div id="myModal" class="modal">
    <!-- Modal content -->
    <div class="modal-content">
        <div>
            <span class="close">&times;</span>
        </div>
        <div>
            <form action="{{route('auth-config')}}" method="post">
                @csrf
                <div class="box-body">
                    <div class="form-group">
                        <label for="roleId">@lang('authConfig.role')</label>
                        <select name="roleid" class="form-control">
                            <option selected disabled>Select</option>
                            @foreach($roleHierarchy as $role)
                            <option value="{{$role->position}}">{{$role->designation}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="processId">@lang('authConfig.process')</label>
                        <select id="processId" name="processId" class="form-control">
                            <option selected disabled>Select</option>
                            @foreach($processes as $process)
                            <option value='{{$process->id}}'>{{$process->process}}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="isAuthorized">@lang('authConfig.isAuth')</label>
                        <input type="hidden" id="isAuthorized" name="isAuthorized" value=0>
                        <input type="checkbox" id="isAuthorized" name="isAuthorized" checked>
                    </div>
                </div><!-- /.box-body -->
                <div class="form-footer">
                    <div class="row">
                        <div class="col-md-6">
                            <button type="reset" onclick="resetForm()" class="btn btn-warning btn-block">@lang('actionBtn.reset')</button>
                        </div>
                        <div class="col-md-6">
                            <button type="submit" class="btn btn-secondary btn-block">@lang('actionBtn.submit')</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('script')
<script>
    $(document).on('change', '#roleId', function() {
        var role = $(this).val();

        $.ajax({
            url: "{{url('config/auth-edit')}}",
            type: 'POST',
            data: {
                "_token": "{{ csrf_token() }}",
                role: role
            },
            success: function(data) {
                $("#table").empty();
                $(`<tr class="bgcolor">
                    <th>@lang('authConfig.process')</th>
                    <th>@lang('authConfig.isAuth')</th>
                </tr>`).appendTo("#table");
                if (!$.trim(data) == true) {
                    $("#table").append(`<tr id="table_row">
                                        <td colspan="7"> <p style="text-align:center;">Data not found</p></td
                                        </tr>`)
                }
                $.each(data, function(key, value) {
                    var checkbox;
                    if (value.isAuthorized == 1) {
                        checkbox =
                            `<td>
                                <input type="checkbox" value="` + value.processId + `" checked name="isAuthorized[]">
                                </td>`

                    } else {
                        checkbox =
                            `<td>
                                <input type="checkbox" value="` + value.processId + `"  name="isAuthorized[]">
                                </td>`
                    }
                    $("#table").append(`<tr id="table_row">
                                                <td><input type="text" value="` + value.process + `"
                                                        class="form-control" id="process" name="process[]"></td>
                                                        ` + checkbox + `                          
                                                
                                                </tr>`)

                });
            }
        });
    });

    $("#add_btn").click(function() {
        document.querySelector('.popup_bg').style.display = 'flex';
    });

    $("#remove_btn").click(function() {

        document.querySelector('.popup_bg').style.display = 'none';
    });

    // Get the modal
    var modal = document.getElementById("myModal");

    // Get the button that opens the modal
    var btn = document.getElementById("add_btn");

    // Get the <span> element that closes the modal
    var span = document.getElementsByClassName("close")[0];

    // When the user clicks the button, open the modal 
    btn.onclick = function() {
        modal.style.display = "block";
    }

    // When the user clicks on <span> (x), close the modal
    span.onclick = function() {
        modal.style.display = "none";
    }

    // When the user clicks anywhere outside of the modal, close it
    window.onclick = function(event) {
        if (event.target == modal) {
            modal.style.display = "none";
        }
    }
</script>


@endsection