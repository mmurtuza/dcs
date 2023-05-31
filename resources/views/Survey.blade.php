@extends('backend.layouts.master')

@section('title','Survey')
@section('style')
<style>
    .add_more input{
        padding-left:122px;
        padding-right:122px;
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
                <h5 class="text-dark font-weight-bold mt-2 mb-2 mr-5">Survey Information</h5>
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
                            <form action="{{route('store')}}" method="post">
                                @csrf
                                <div class="box-body">
                                    <div class="form-group">
                                        <label for="enrolmentid">Enrolment ID</label>
                                        <input type="text" class="form-control" id="enrolmentid" name="enrolmentid">
                                    </div>
                                    <div class="form-group">
                                        <label for="name">Name *</label>
                                        <input type="text" class="form-control" id="name" name="name">
                                    </div>
                                    <div class="form-row">
                                        <div class="col-md-6 mb-3">
                                            <label for="mainidtype">Main ID Type</label>
                                            <select id="mainidtype" name="mainidtype" class="form-control">
                                                <option>Select</option>
                                                <option>Old NID (17 Digits)</option>
                                                <option>Smart NID (10 Digits)</option>
                                                <option>Birth Certificate (17 Digits)</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="mainidnumber">Main ID Number</label>
                                            <input type="text" class="form-control" id="mainidnumber"
                                                name="mainidnumber">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="phonenumber">Phone Number</label>
                                        <input type="text" class="form-control" id="phonenumber" name="phonenumber">
                                    </div>
                                    <div class="form-row">
                                        <div class="col-md-6 mb-3">
                                            <label for="status">Status *</label>
                                            <select id="status" name="status" class="form-control">
                                                <option>Select</option>
                                                <option value="potential">Potential</option>
                                                <option value="not potential">Not potential</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="level">Level *</label>
                                            <select id="level" name="level" class="form-control">
                                                <option>Select</option>
                                                <option value="high">High</option>
                                                <option value="medium">Medium</option>
                                                <option value="low">Low</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-row">
                                        <div class="col-md-6 mb-3">
                                            <label for="referreredImage">Referrered By</label>
                                            <input type="file" class="form-control" id="referreredImage"
                                                name="referreredImage">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="referreredname">Referrered Name</label>
                                            <input type="text" class="form-control" id="referreredname"
                                                name="referreredname">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="follow-upDate">Follow-up Date</label>
                                        <input type="date" class="form-control" id="follow-upDate" name="follow-upDate">
                                    </div>

                                </div><!-- /.box-body -->


                                <div class="box-footer">

                                    <div class="row">
                                        <div class="col-md-6">
                                            <button type="reset" onclick="resetForm()"
                                                class="btn btn-warning btn-block">Reset</button>
                                        </div>
                                        <div class="col-md-6 add_more">
                                            <input class="btn btn-success btn_block" type="button" name="add_btn" id="add_btn"
                                    value="Add More">
                                        </div>
                                    </div>
                                    <div class="submit">
                                    <br><button type="submit" class="btn btn-secondary btn-block">Submit</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    <!--end: Datatable-->
                </div>
            </div>
            <!--end::Card-->
        </div>
        <!--end::Container-->
    </div>
</div>

@endsection

@section('script')
<script>
    var html = `<input class="btn btn-danger btn_block" type="button" name="remove_btn" id="remove_btn"
                                    value="x">
    <div class="row">
    <div class="col-md-4">
        <label for="field_name">Field Name</label>
        <input class="form-control" id="field_name" type="text" name="field_name[]" required>
    </div>
    <div class="col-md-4">
        <label for="field_name">Input Type</label>
        <select class="form-control" id="test" name="field_type[]">
            <option selected disabled>Choose one...</option>
            <option value="int(10)">Integer</option>
            <option value="varchar(50)">Varchar</option>
            <option value="text">Text</option>
            <option value="string">String</option>
            <option value="timestamp">Date</option>
            <option value="bigint(100)">Big Integer</option>
        </select>
    </div>
    <div class="col-md-4">
        <label for="field_name">Control Type</label>
        <select class="form-control" id="control_type" name="control_type[]">
            <option selected disabled>Choose one...</option>
            <option value="radio">Radio</option>
            <option value="checkbox">Checkbox</option>
            <option value="dropdown<">Dropdown</option>
        </select>
    </div>
</div> <br>`;

$("#add_btn").click(function () {
        $(".box-body").append(html);
    });
$("#pop_table_field").on('click', '#remove', function () {
    $(this).closest('tr').remove();
});

</script>


@endsection
