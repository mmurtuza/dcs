@extends('backend.layouts.master')

@section('title','notification')

@section('style')
<style>
    /* The Modal (background) */
    .modal {
        display: none;
        position: fixed;
        z-index: 1;
        padding-top: 150px;
        left: 120px;
        top: 0;
        width: 100%;
        height: 100%;
        overflow: auto;
        background-color: rgb(0, 0, 0);
        background-color: rgba(0, 0, 0, 0.4);
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
                <h5 class="text-dark font-weight-bold mt-2 mb-2 mr-5">@lang('notification.header')</h5>
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
                            <!-- <form action="" method="post"> -->
                            <!-- @csrf -->
                            <div class="box-body">
                                <div class="form-group">
                                    <label for="roleid">@lang('notification.role')</label>
                                    <select id="roleid" class="form-control">
                                        <option selected disabled>Select</option>
                                        @foreach($roleHierarchy as $role)
                                        <option value="{{$role->position}}">{{$role->designation}}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="process">@lang('notification.process')</label>
                                    <select id="process" name="process" class="form-control">
                                        <option selected disabled>Select</option>
                                        @foreach($process as $row)
                                        <option value="{{$row->id}}">{{$row->process}}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <table id="table" class="table">
                                    </table>
                                </div>


                            </div><!-- /.box-body -->
                            <br>
                            <div class="box-footer">
                                <div class="row">
                                    <div class="col-md-4">
                                    </div>
                                    <div class="col-md-4">
                                        <button type="submit" id="add_btn" class="btn btn-secondary btn-block">@lang('actionBtn.addNew')</button>
                                    </div>
                                    <div class="col-md-4">

                                    </div>
                                </div>
                            </div>
                            <!-- </form> -->
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
<!-- The Modal -->
<div id="myModal" class="modal">
    <!-- Modal content -->
    <div class="modal-content">
        <div>
            <span class="close">&times;</span>
        </div>
        <div>
            <form action="{{route('notification')}}" method="post">
                @csrf
                <div class="box-body">
                    <div class="form-group">
                        <label for="roleId">@lang('notification.role')</label>
                        <select id="roleId" name="roleid" class="form-control">
                            <option selected disabled>Select</option>
                            @foreach($roleHierarchy as $role)
                            <option value="{{$role->position}}">{{$role->designation}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="process">@lang('notification.process')</label>
                        <select name="process" id="fprocess" class="form-control">
                            <option selected disabled>Select</option>
                            @foreach($process as $row)
                            <option value="{{$row->id}}">{{$row->process}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="faction">@lang('notification.action')</label>
                        <select id="faction" name="actionid" class="form-control">
                            <option>Select</option>
                        </select>
                    </div>
                    <div class="form-group" class="multi_select_box">
                        <label for="recieverlist">@lang('notification.receiver')</label>
                        <select multiple name="recieverlist[]" title="Select" class="multi_select form-control">
                            @foreach($roleHierarchy as $role)
                            <option value="{{$role->position}}">{{$role->designation}}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="isAouthrized">@lang('notification.notifyThrough')</label><br>
                        <input type="hidden" id="sms" name="sms" value=0>
                        <input type="hidden" id="email" name="email" value=0>
                        <input type="hidden" id="inapp" name="inapp" value=0>
                        <input type="checkbox" id="sms" name="sms">
                        <label for="vehicle1"> SMS</label><br>
                        <input type="checkbox" id="email" name="email">
                        <label for="vehicle2"> Email</label><br>
                        <input type="checkbox" id="inapp" name="inapp">
                        <label for="vehicle3"> In App</label><br>
                    </div>
                    <div class="form-group">
                        <label for="msgcontent">@lang('notification.content')</label>
                        <input type="text" class="form-control" id="msgcontent" name="msgcontent">
                    </div>
                </div><!-- /.box-body -->
                <div class="form-footer">
                    <div class="row">
                        <div class="col-md-4">

                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-secondary btn-block">@lang('actionBtn.submit')</button>
                        </div>
                        <div class="col-md-4">

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
    // multiselect dropdown
    $('.multi_select').selectpicker();

    // notification view
    $('#process, #roleid').change(function() {
        if ($('#process').val() && $('#roleid').val()) {
            var process = $('#process').val();
            var roleid = $('#roleid').val();

            $.ajax({
                url: "{{url('config/notification-view')}}",
                type: 'POST',
                data: {
                    "_token": "{{ csrf_token() }}",
                    roleid: roleid,
                    process: process,
                },
                success: function(data) {


                    $("#table").empty();
                    $(`<tr class="bgcolor">
                        <th>@lang('notification.table_header1')</th>
                        <th>@lang('notification.table_header2')</th>
                        <th>@lang('notification.table_header3')</th>
                        <th>@lang('notification.table_header4')</th>
                        <th>@lang('notification.table_header5')</th>
                        <th>@lang('notification.table_header6')</th>
                        <th>@lang('notification.table_header7')</th>
                    </tr>`).appendTo("#table");
                    // data is empty or not
                    if (!$.trim(data) == true) {
                        $("#table").append(`<tr id="table_row">
                                        <td colspan="7"> <p style="text-align:center;">Data not found</p></td
                                        </tr>`)
                    }
                    $.each(data, function(key, value) {

                        var sms;
                        var email;
                        var inApp;
                        if (value.sms == true) {
                            sms =
                                `<td>
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-check" viewBox="0 0 16 16">
  <path d="M10.97 4.97a.75.75 0 0 1 1.07 1.05l-3.99 4.99a.75.75 0 0 1-1.08.02L4.324 8.384a.75.75 0 1 1 1.06-1.06l2.094 2.093 3.473-4.425a.267.267 0 0 1 .02-.022z"/>
</svg>
                                </td>`

                        } else {
                            sms =
                                `<td>
                                </td>`
                        }
                        if (value.email == true) {
                            email =
                                `<td>
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-check" viewBox="0 0 16 16">
  <path d="M10.97 4.97a.75.75 0 0 1 1.07 1.05l-3.99 4.99a.75.75 0 0 1-1.08.02L4.324 8.384a.75.75 0 1 1 1.06-1.06l2.094 2.093 3.473-4.425a.267.267 0 0 1 .02-.022z"/>
</svg>
                                </td>`

                        } else {
                            email =
                                `<td></td>`
                        }
                        if (value.inApp == true) {
                            inApp =
                                `<td>
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-check" viewBox="0 0 16 16">
  <path d="M10.97 4.97a.75.75 0 0 1 1.07 1.05l-3.99 4.99a.75.75 0 0 1-1.08.02L4.324 8.384a.75.75 0 1 1 1.06-1.06l2.094 2.093 3.473-4.425a.267.267 0 0 1 .02-.022z"/>
</svg>
                                </td>`

                        } else {
                            inApp =
                                `<td></td>`
                        }
                        var url = '{{ url("config/notification-delete", "id") }}';
                        url = url.replace('id', value.id);
                        var url1 = '{{ url("config/notification-edit", "id") }}';
                        url1 = url1.replace('id', value.id);
                        $("#table").append(`<tr id="table_row">
                                        <td>` + value.recieverlist + `</td>                                                                                  
                                        <td>` + value.actionname + `</td>
                                            ` + sms + email + inApp + `
                                            <td>` + value.msgcontent + `</td>
                                            <td style="text-align:center;">
                                            <a class="btn btn-warning" style="color:white; margin-bottom:2px;" href="` + url1 + `">Edit</a>
                                            <a class="btn btn-danger" style="color:white;" href="` + url + `">Delete </a></td
                                        </tr>`)

                    });

                }
            });
        }
    });

    // process select and find action start
    $(document).on('change', '#fprocess', function() {
        var process = $(this).val();

        $.ajax({
            url: "{{url('config/process-view')}}",
            type: 'POST',
            data: {
                "_token": "{{ csrf_token() }}",
                process: process,
            },
            success: function(data) {

                $("#faction").empty();

                $.each(data, function(key, value) {
                    $("#faction").append(`<option value="` + value.id + `">` + value.actionname + `</option>`)

                });
            }
        });
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