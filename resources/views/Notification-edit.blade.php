@extends('backend.layouts.master')

@section('title','notification')

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
                            <form action="{{route('notification-update',[$data->id])}}" method="post">
                                @csrf
                                <div class="box-body">
                                    <div class="form-group">
                                        <label for="roleId">@lang('notification.role')</label>
                                        <select name="roleid" class="form-control">
                                            @foreach($roleHierarchy as $role)
                                            <option value="{{$role->position}}"
                                                {{ $role->position == $data->roleid ? 'selected="selected"' : '' }}>
                                                {{$role->designation}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    @php
                                    $db = config('database.db');
                                    $actionList = DB::table($db.'.action_lists')->where('id',$data->actionid)->where('projectcode',session('projectcode'))->first();
                                    $processData = DB::table($db.'.processes')->where('id',$actionList->process_id)->first();
                                    @endphp
                                    <div class="form-group">
                                        <label for="process">@lang('notification.process')</label>
                                        <select name="process" id="fprocess" class="form-control">
                                            <option>Select</option>
                                            @foreach($process as $row)                                            
                                            <option value="{{$row->id}}" {{ $row->id == $processData->id ? 'selected="selected"' : '' }}>
                                                {{$row->process}}
                                            </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="faction">@lang('notification.action')</label>
                                        <select id="faction" name="actionid" class="form-control">
                                            <option value="{{ $actionList->id }}">{{ $actionList->actionname}}</option>
                                        </select>
                                    </div>
                                    @php
                                        $item = explode(',', $data->recieverlist);
                                    @endphp
                                    <div class="form-group" class="multi_select_box">
                                        <label for="recieverlist">@lang('notification.receiver')</label>
                                        <select multiple name="recieverlist[]" title="Select"
                                            class="multi_select form-control">
                                            @foreach($roleHierarchy as $role)                                            
                                                <option value="{{$role->position}}"
                                                    @foreach($item as $row)
                                                        {{ $role->position == $row ? 'selected="selected"' : '' }}
                                                    @endforeach>  
                                                {{$role->designation}}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <label for="isAouthrized">@lang('notification.notifyThrough')</label><br>
                                        <input type="hidden" id="sms" name="sms" value=0>
                                        <input type="hidden" id="email" name="email" value=0>
                                        <input type="hidden" id="inapp" name="inapp" value=0>
                                        @if($data->sms== 1)
                                        <input type="checkbox" id="sms" name="sms" checked>
                                        <label for="vehicle1"> SMS</label><br>
                                        @else
                                        <input type="checkbox" id="sms" name="sms">
                                        <label for="vehicle1"> SMS</label><br>
                                        @endif
                                        @if($data->email== 1)
                                        <input type="checkbox" id="email" name="email" checked>
                                        <label for="vehicle2"> Email</label><br>
                                        @else
                                        <input type="checkbox" id="email" name="email">
                                        <label for="vehicle2"> Email</label><br>
                                        @endif
                                        @if($data->inApp== 1)
                                        <input type="checkbox" id="inapp" name="inapp" checked>
                                        <label for="vehicle3"> In App</label><br>
                                        @else
                                        <input type="checkbox" id="inapp" name="inapp" >
                                        <label for="vehicle3"> In App</label><br>
                                        @endif
                                    </div>
                                    <div class="form-group">
                                        <label for="msgcontent">@lang('notification.content')</label>
                                        <input type="text" value="{{$data->msgcontent}}" class="form-control" id="msgcontent" name="msgcontent">
                                    </div>
                                </div><!-- /.box-body -->
                                <br>
                                <div class="box-footer">
                                    <div class="row">
                                        <div class="col-md-4">
                                        </div>
                                        <div class="col-md-4">
                                            <button type="submit" class="btn btn-secondary btn-block">@lang('actionBtn.update')</button>
                                        </div>
                                        <div class="col-md-4">

                                        </div>
                                    </div>
                                </div>
                            </form>
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
