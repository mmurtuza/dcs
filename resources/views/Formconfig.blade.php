@extends('backend.layouts.master')

@section('title','Form Configuration')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/css/select2.min.css" rel="stylesheet" />
@section('style')
<style>
    #admission_field {
        display: none;
    }

    .displayHide {
        display: none;
    }

    #rca {
        display: none;
    }

    /* The Modal (background) */
    .popup_length {
        display: none;
        position: fixed;
        z-index: 1;
        padding-top: 150px;
        top: 0;
        width: 100%;
        height: 100%;
        overflow: auto;
        background-color: rgb(0, 0, 0);
        background-color: rgba(0, 0, 0, 0.4);
    }

    .popup_bg {
        display: none;
        position: fixed;
        z-index: 1;
        padding-top: 150px;
        top: 0;
        width: 100%;
        height: 100%;
        overflow: auto;
        background-color: rgb(0, 0, 0);
        background-color: rgba(0, 0, 0, 0.4);
    }

    .popup_date {
        display: none;
        position: fixed;
        z-index: 1;
        padding-top: 150px;
        top: 0;
        width: 100%;
        height: 100%;
        overflow: auto;
        background-color: rgb(0, 0, 0);
        background-color: rgba(0, 0, 0, 0.4);
    }

    .popup_content {
        height: 300px;
        width: 650px;
        background: #fff;
        left: 220px;
        padding: 20px;
        border-radius: 5px;
        position: relative;
        overflow: auto;
    }

    .content_style {
        margin: 15px auto;
        display: block;
        width: 100%;
    }

    .close {
        position: absolute;
        top: 5px;
        right: 5px;
        border-radius: 50%;
        cursor: pointer;
    }

    .select2-selection__rendered {
        line-height: 31px !important;
        padding-top: 0px !important;
        padding-left: 16px !important;
    }

    .select2-container .select2-selection--single {
        height: 38px !important;
    }

    .select2-selection__arrow {
        height: 34px !important;
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
                <h5 class="text-dark font-weight-bold mt-2 mb-2 mr-5">@lang('formConfig.header')</h5>
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

                            <!-- {{-- <form action="{{route('store')}}" method="post" onsubmit="return confirm('Are you
                            sureyou want to submit?');" name="registration"> --}} -->
                            <form action="{{route('formConfigstore')}}" method="post">
                                @csrf
                                <div class="box-body">
                                    <!-- <div class="form-group">
                                        <label for="Project" class="form-label">Project</label>
                                        <input type="text" value="015"class="form-control" id="Project" name="Project">
                                    </div> -->
                                    <div class="form-group">
                                        <label for="formID">@lang('formConfig.appForm')</label>
                                        <select id="formID" name="formID" class="form-control">
                                            <option selected disabled>Select</option>
                                            <option value='survey'>Survey</option>
                                            <option value='admission'>Amission</option>
                                            <option value='loan-proposal'>Loan-proposal</option>
                                            <option value='rca'>RCA</option>
                                        </select>
                                    </div>
                                    <div class="form-group" id="loan-select">

                                    </div>

                                    <div id="form_field" class="displayHide">
                                        <table class="table" id="app-form">

                                        </table>
                                        <br>
                                    </div>
                                    <div id="extra-value"></div>

                                </div><!-- /.box-body -->


                                <div class="box-footer">

                                    <div class="row">
                                        <div class="col-md-6">
                                            <button type="reset" onclick="resetForm()" class="btn btn-warning btn-block">@lang('actionBtn.reset')</button>
                                        </div>
                                        <div class="col-md-6">
                                            <button type="button" id="add_btn" class="btn btn-success btn-block">@lang('actionBtn.addNew')</button>
                                            <!-- <input class="btn btn-success" type="button" name="add_btn"
                                                id="add_btn" value="Add More"> -->
                                        </div>
                                    </div>
                                    <div class="submit">
                                        <br><button type="submit" class="btn btn-secondary btn-block">@lang('actionBtn.submit')</button>
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
<!--popup window of number and text  -->
<div class="popup_length" id="length">
    <div class="popup_content">
        <input class="btn btn-danger close btn_block " type="button" id="remove_ln_btn" value="x">
        <form id="form3" method="post" action="">
            <div class="displayHide">
                <input class="form-group popup_datatype" type="text" name="popup_datatype" id="popup_ln_datatype">
                <input class="form-group popup_label" type="text" name="popup_label" id="popup_ln_label">
            </div>
            <h1 class="text-center h_title">@lang('formConfig.lengthTitle')</h1>
            <div class="input_field">
                <table class="table table-bordered">
                    <tr>
                        <th class="text-center max">@lang('formConfig.MaxLength')</th>
                        <th class="text-center min">@lang('formConfig.MaxLength')</th>
                    </tr>
                    <tr>
                        <td><input class="form-control" type="text" name="popup_ln_value[]" id="popup_value" required></td>
                        <td><input class="form-control" type="text" name="popup_ln_value[]" id="popup_value" required></td>

                    </tr>
                </table>
                <center>
                    <input class="btn btn-primary" type="submit" name="" id="submit" value="@lang('actionBtn.submit')">
                </center>
                <p id="msg" class="text-success"></p>
            </div>
        </form>
    </div>
</div>
<!-- popup window for radio, checkbox & dropdown  -->
<div class="popup_bg" id="multiple_data">
    <div class="popup_content">
        <input class="btn btn-danger close btn_block " type="button" id="remove_mul_btn" value="x">
        <form id="form2" method="post" action="">
            <div class="displayHide">
                <input class="form-group popup_datatype" type="text" name="popup_datatype" id="popup_datatype">
                <input class="form-group popup_label" type="text" name="popup_label" id="popup_label">
            </div>
            <h1 class="text-center">@lang('formConfig.multiFieldTitle')</h1>
            <div class="input_field">
                <table class="table table-bordered" id="pop_table_field">
                    <tr>
                        <th class="text-center">@lang('formConfig.caption')</th>
                        <th class="text-center">@lang('formConfig.values')</th>
                        <th class="text-center">@lang('formConfig.addRemoveBtn')</th>
                    </tr>
                    <tr>
                        <td><input class="form-control" type="text" name="popup_caption[]" id="popup_caption" required></td>
                        <td><input class="form-control" type="text" name="popup_value[]" id="popup_value" required></td>
                        <td>
                            <center>
                                <input class="btn btn-success" type="button" name="add_btn" id="pop_add_btn" value="@lang('actionBtn.addNew')">
                            </center>
                        </td>
                    </tr>
                </table>

                <center>
                    <input class="btn btn-primary" type="submit" name="" id="submit" value="@lang('actionBtn.submit')">
                </center>
                <p id="msg" class="text-success"></p>
            </div>
        </form>
    </div>
</div>
<!--popup window of date -->
<div class="popup_date" id="date">
    <div class="popup_content">
        <input class="btn btn-danger close btn_block " type="button" id="remove_date_btn" value="x">
        <form id="form4" method="post" action="">
            <div class="displayHide">
                <input class="form-group popup_datatype" type="text" name="popup_datatype" id="popup_date_datatype">
                <input class="form-group popup_label" type="text" name="popup_label" id="popup_date_label">
            </div>
            <h1 class="text-center">@lang('formConfig.dateTitle')</h1>
            <div class="input_field">
                <table class="table table-bordered">
                    <tr>
                        <th class="text-center">@lang('formConfig.formDate')</th>
                        <th class="text-center">@lang('formConfig.toDate')</th>
                    </tr>
                    <tr>
                        <td><input class="form-control" type="date" name="popup_date_value[]" id="popup_value" required></td>
                        <td><input class="form-control" type="date" name="popup_date_value[]" id="popup_value" required></td>

                    </tr>
                </table>
                <center>
                    <button class="btn btn-primary" type="submit">@lang('actionBtn.submit')</button>
                </center>
                <p id="msg" class="text-success"></p>
            </div>
        </form>
    </div>
</div>
@endsection

@section('script')
<script>
    $(document).on('change', '#formID', function() {
        $('div').remove('#more');
        var app_form = $(this).val();
        if ($(this).val() == "loan-proposal" || $(this).val() == "rca") {
            $("#loan-select").empty();
            document.querySelector('#form_field').style.display = 'none';
            document.querySelector('#extra-value').style.display = 'none';
            $("#loan-select").append(`<label for="loan_product">@lang('formConfig.loanProduct')</label>
                <select id="product_details" name="loan_product" class="form-control">
                    <option selected disabled>Select</option>
                    @foreach($product_details as $product_detail)
                        <option value='{{$product_detail->productcode}}'>{{$product_detail->productname}}</option>
                    @endforeach
                </select>`);
            $("#product_details").select2();
        } else {
            $("#loan-select").empty();
            document.querySelector('#form_field').style.display = 'block';
            document.querySelector('#extra-value').style.display = 'block';
            $.ajax({
                url: "{{url('config/form-config')}}",
                type: 'POST',
                data: {
                    "_token": "{{ csrf_token() }}",
                    app_form: app_form,
                },
                success: function(data) {
                    $("#app-form").empty();
                    $("#extra-value").empty();
                    $(`<tr>
                    <th>@lang('formConfig.table_header1')</th>
                    <th>@lang('formConfig.table_header2')</th>
                    <th>@lang('formConfig.table_header3')</th>
                    <th>@lang('formConfig.table_header4')</th>
                </tr>`).appendTo("#app-form");
                    $.each(data, function(key, value) {
                        if (value.columnType == 0 || value.formName == app_form) {
                            $("#app-form").append(`<tr>  <td><input type="text" value="` + (value.fieldNameEn || value.lebel.english) + `"
                                class="form-control" id="label" name="labelEn[]" readonly></td>
                        <td><input type="text" value="` + (value.fieldNameBn || value.lebel.bangla) + `"
                                class="form-control" id="label" name="labelBn[]" readonly></td>
                        <td><input type="text" value="` + value.dataType + `"
                                class="form-control" id="label" name="dataType[]" readonly></td>
                        <td>
                            <select id="status" name="status[]" class="form-control">
                                <option selected value='1'>True</option>
                                <option value='0'>False</option>
                            </select>
                        
                        
                        <input type="text" value="` + 0 + `" class="form-control displayHide" id="columnType"
                            name="columnType[]">
                            <input type="hidden" name="groupLabelEn[]" value="">
                        <input type="text" value="` + value.id + `" class="form-control displayHide" id="displayorder"
                            name="displayOrder[]">
                    
                    </td>
                    </tr>`)
                        } else {
                            $("#extra-value").append(`<div>
                        <input class="btn btn-danger btn_block  float-right px-4 rounded-circle" type="button" name="remove_btn" id="remove_btn"
                                        
                        value="x"><h6>@lang('formConfig.label')</h6>'
                        <div class="form-row">
                        <div class="col-md-6 mb-3">
                        <label for="label">@lang('formConfig.table_header1')</label>
                        <input type="text" value="` + value.lebel.english + `" class="form-control" id="labelEn" name="labelEn[]" required>
                        </div>
                        <div class="col-md-6 mb-3">
                        <label for="label">@lang('formConfig.table_header2')</label>
                        <input type="text"  value="` + value.lebel.bangla + `" class="form-control" id="labelBn" name="labelBn[]" required>
                        </div>
                        </div>
                        <div class="form-group">
                            <label for="dataType">@lang('formConfig.table_header3')</label>
                                <select  id="dataType" name="dataType[]" class="form-control" required>
                                    <option selected value="` + value.dataType + `">` + value.dataType + `</option>
                                    <option value="text">Text</option>
                                    <option value="number">Number</option>
                                    <option value="radio">Radio</option>
                                    <option value="dropdown">Dropdown</option>
                                    <option value="checkbox">Checkbox</option>
                                    <option value="date">Date</option>
                                    <option value="photo">Photo</option>
                                </select>
                                </div>
                                <div class="form-group displayHide">
                                    <label for="columnType">Column Type</label>                                        
                                    <input type="text" value="1" class="form-control"
                                    id="columnType" name="columnType[]">
                                </div>
                                <input type="hidden" name="groupLabelEn[]" value="">
                                <input type="hidden" name="displayOrder[]" value="">
                                <div class="form-group displayHide">
                                    <label for="status">Show/hide</label>
                                    <input type="text" value="1" class="form-control"
                                    id="status" name="status[]">
                                </div><br></div>`);
                        }
                    });
                }
            });
        }
    });
    // $(document).ready(function () {
    //     // branch-wish-select
    //     $("#product_details").select2();
    // }
    $(document).on('change', '#product_details', function() {
        document.querySelector('#form_field').style.display = 'block';
        document.querySelector('#extra-value').style.display = 'block';
        var app_form = $('#formID').val();
        var loan_product = $(this).val();
        $.ajax({
            url: "{{url('config/form-config')}}",
            type: 'POST',
            data: {
                "_token": "{{ csrf_token() }}",
                app_form: app_form,
                loan_product: loan_product,
            },
            success: function(data) {
                $("#app-form").empty();
                $("#extra-value").empty();
                $(`<tr>
                    <th>@lang('formConfig.table_header1')</th>
                    <th>@lang('formConfig.table_header2')</th>
                    <th>@lang('formConfig.table_header3')</th>
                    <th>@lang('formConfig.table_header4')</th>
                </tr>`).appendTo("#app-form");
                $.each(data, function(key, value) {
                    if (value.formID == "rca") {
                        var rca_group = `<div id="rca_form" class="form-group">
                                <label for="dataType">@lang('formConfig.groupLabel')</label>
                                    <select  id="groupLabelEn" name="groupLabelEn[]" class="form-control">
                                        <option value="` + value.groupLabel + `" selected>` + value.groupLabel + `</option>
                                    </select>
                                </div>`
                        var rca_display_order = `<div class="form-group">
                                <label for="displayOrder">@lang('formConfig.displayOrder')</label>
                                <input type="text" value="` + value.displayOrder + `" class="form-control" id="displayOrder" name="displayOrder[]">
                            </div>`
                    } else {
                        var rca_group = `<input type="hidden" name="groupLabelEn[]" value="">`;
                        var rca_display_order = `<input type="hidden" name="displayOrder[]" value="">`;
                    }
                    if (value.columnType == 0 || value.formName == app_form) {
                        $("#app-form").append(`<tr>  <td><input type="text" value="` + (value.fieldNameEn || value.lebel.english) + `"
                                                        class="form-control" id="label" name="labelEn[]" readonly></td>
                                                <td><input type="text" value="` + (value.fieldNameBn || value.lebel.bangla) + `"
                                                        class="form-control" id="label" name="labelBn[]" readonly></td>
                                                <td><input type="text" value="` + value.dataType + `"
                                                        class="form-control" id="label" name="dataType[]" readonly></td>
                                                <td>
                                                    <select id="status" name="status[]" class="form-control">
                                                        <option selected value='1'>True</option>
                                                        <option value='0'>False</option>
                                                    </select>
                                                
                                                
                                                <input type="text" value="` + 0 + `" class="form-control displayHide" id="columnType"
                                                    name="columnType[]">
                                                    <input type="hidden" name="groupLabelEn[]" value="">
                                                <input type="text" value="` + value.id + `" class="form-control displayHide" id="displayorder"
                                                    name="displayOrder[]">
                                         
                                            </td>
                                            </tr>`)
                    } else {
                        $("#extra-value").append(`<div>
                    <input class="btn btn-danger btn_block  float-right px-4 rounded-circle" type="button" name="remove_btn" id="remove_btn"
                                  
                        value="x">` + rca_group + `<h6>@lang('formConfig.label')</h6>'
                        <div class="form-row">
                        <div class="col-md-6 mb-3">
                        <label for="label">@lang('formConfig.table_header1')</label>
                        <input type="text" value="` + value.lebel.english + `" class="form-control" id="labelEn" name="labelEn[]" required>
                        </div>
                        <div class="col-md-6 mb-3">
                        <label for="label">@lang('formConfig.table_header2')</label>
                        <input type="text"  value="` + value.lebel.bangla + `" class="form-control" id="labelBn" name="labelBn[]" required>
                        </div>
                        </div>
                        <div class="form-group">
                            <label for="dataType">@lang('formConfig.table_header3')</label>
                                <select  id="dataType" name="dataType[]" class="form-control" required>
                                    <option selected value="` + value.dataType + `">` + value.dataType + `</option>
                                    <option value="text">Text</option>
                                    <option value="number">Number</option>
                                    <option value="radio">Radio</option>
                                    <option value="dropdown">Dropdown</option>
                                    <option value="checkbox">Checkbox</option>
                                    <option value="date">Date</option>
                                    <option value="photo">Photo</option>
                                </select>
                            </div>
                            <div class="form-group displayHide">
                                <label for="columnType">Column Type</label>                                        
                                <input type="text" value="1" class="form-control"
                                id="columnType" name="columnType[]">
                            </div>
                            ` + rca_display_order + `
                            <div class="form-group displayHide">
                                <label for="status">Show/hide</label>
                                <input type="text" value="1" class="form-control"
                                id="status" name="status[]">
                            </div><br></div>`);
                    }

                });
            }
        });
    });


    $(document).on('click', '#add_btn', function() {
        if ($('#formID').val()) {
            var app_form = $('#formID').val();
            var rca_group;
            if (app_form == "rca") {
                rca_group = `<div id="rca_form" class="form-group">
                     <label for="dataType">@lang('formConfig.groupLabel')</label>
                        <select  id="groupLabelEn" name="groupLabelEn[]" class="form-control">
                            <option selected disabled>Select</option>
                            <option value="Income">Income</option>
                            <option value="Expense">Expense</option>
                            <option value="Liabilities">Liabilities</option>
                            <option value="Tolerence">Tolerence</option>
                        </select>
                    </div>`
                var rca_display_order = `<div class="form-group">
                                        <label for="displayOrder">@lang('formConfig.displayOrder')</label>
                                        <input type="text" class="form-control" id="displayOrder" name="displayOrder[]">
                                    </div>`
            } else {
                rca_group = `<input type="hidden" name="groupLabelEn[]" value="">`;
                var rca_display_order = `<input type="hidden" name="displayOrder[]" value="">`;

            }


            var html = `<div id="more">
    <input class="btn btn-danger btn_block  float-right px-4 rounded-circle" type="button" name="remove_btn" id="remove_btn"` +
                'value="x">' + rca_group + `<h6 class="mt-10">@lang('formConfig.label')</h6>` +
                `<div class="form-row">
               <div class="col-md-6 mb-3">
                  <label for="label">@lang('formConfig.table_header1')</label>
                  <input type="text" onkeyup="manage(this)" class="form-control" id="labelEn" name="labelEn[]" required>
                 </div>
                <div class="col-md-6 mb-3">
                 <label for="label">@lang('formConfig.table_header2')</label>
                <input type="text"  class="form-control" id="labelBn" name="labelBn[]" required>
                 </div>
                 </div>
                 <div class="form-group">
                     <label for="dataType">@lang('formConfig.table_header3')</label>
                                        <select  id="dataType" name="dataType[]" class="form-control" required>
                                            <option selected disabled>Select</option>
                                            <option value="text">Text</option>
                                            <option value="number">Number</option>
                                            <option value="radio">Radio</option>
                                            <option value="dropdown">Dropdown</option>
                                            <option value="checkbox">Checkbox</option>
                                            <option value="date">Date</option>
                                            <option value="photo">Photo</option>
                                        </select>
                                    </div>
                                    <div class="form-group displayHide">
                                        <label for="columnType">Column Type</label>                                        
                                        <input type="text" value="1" class="form-control"
                                        id="columnType" name="columnType[]">
                                    </div>
                                    ` + rca_display_order + `
                                    <div class="form-group displayHide">
                                        <label for="status">Show/hide</label>
                                        <input type="text" value="1" class="form-control"
                                        id="status" name="status[]">
                                    </div><br></div>`;


            $(".box-body").append(html);
        }
    });
    $(".box-body").on('click', '#remove_btn', function() {
        $(this).parent('div').remove();
    });


    // $('#labelEn').change(function() {
    // $('#popup_label').val($(this).val());
    // });


    // popup form work
    $(document).on('change', '#dataType', function() {
        if ($(this).val() == "text") {
            document.querySelector('.popup_length').style.display = 'flex';
            document.getElementById('popup_ln_datatype').value = $(this).val();
            // var popup_label = document.getElementById('labelEn').value;
            $("input[name^='labelEn']").each(function() {
                document.getElementById('popup_ln_label').value = $(this).val();
            });
            // $("#popup_label").val($("#labelEn").val()); 
            $(".h_title").html("@lang('formConfig.MaxLength')");
            $(".max").html("@lang('formConfig.MaxLength')");
            $(".min").html("@lang('formConfig.MaxLength')");
        }
        if ($(this).val() == "number") {
            document.querySelector('.popup_length').style.display = 'flex';
            document.getElementById('popup_ln_datatype').value = $(this).val();
            // var popup_label = document.getElementById('labelEn').value;
            $("input[name^='labelEn']").each(function() {
                document.getElementById('popup_ln_label').value = $(this).val();
            });
            $(".h_title").html("@lang('formConfig.RangeTitle')");
            $(".max").html("@lang('formConfig.maxRange')");
            $(".min").html("@lang('formConfig.minRange')");
        }
        if ($(this).val() == "radio" || $(this).val() == "dropdown" || $(this).val() == "checkbox") {
            document.querySelector('.popup_bg').style.display = 'flex';
            document.getElementById('popup_datatype').value = $(this).val();
            // var popup_label = document.getElementById('labelEn').value;
            $("input[name^='labelEn']").each(function() {
                document.getElementById('popup_label').value = $(this).val();
            });
            // $("#popup_label").val($("#labelEn").val()); 
        }
        if ($(this).val() == "date") {
            document.querySelector('.popup_date').style.display = 'flex';
            document.getElementById('popup_date_datatype').value = $(this).val();
            // var popup_label = document.getElementById('labelEn').value;
            $("input[name^='labelEn']").each(function() {
                document.getElementById('popup_date_label').value = $(this).val();
            });
        }
    });
    document.querySelector("#remove_ln_btn").addEventListener("click", function() {
        document.querySelector(".popup_length").style.display = "none";
    });
    document.querySelector("#remove_mul_btn").addEventListener("click", function() {
        document.querySelector(".popup_bg").style.display = "none";
    });
    document.querySelector("#remove_date_btn").addEventListener("click", function() {
        document.querySelector(".popup_date").style.display = "none";
        // document.getElementById("status").innerHTML = "";
    });

    $("#pop_add_btn").click(function() {
        $("#pop_table_field").append(`<tr>
        <td><input class="form-control" type="text" name="popup_caption[]"
                                                            id="popup_caption" required></td>
                <td><input class="form-control" type="text" name="popup_value[]" id="popup_value" required></td>                        
                <td>
                    <center>
                        <input class="btn btn-danger" type="button" name="remove" id="remove"
                         value="Remove">
                    </center>
                </td>
            </tr>`);
    });
    $("#pop_table_field").on('click', '#remove', function() {
        $(this).closest('tr').remove();
    });

    $(document).ready(function() {
        $("#form2").on('submit', function(event) {
            let label = $('#popup_label').val();

            let datatype = $('#popup_datatype').val();
            let popup_caption = $('input[name^=popup_caption]').map(function(idx, elem) {
                return $(elem).val();
            }).get()

            let popup_value = $('input[name^=popup_value]').map(function(idx, elem) {
                return $(elem).val();
            }).get()
            event.preventDefault();
            $.ajax({
                type: "POST",
                url: "{{url('config/Formconfig-popup')}}",
                data: {
                    "_token": "{{ csrf_token() }}",
                    label: label,
                    datatype: datatype,
                    popup_caption: popup_caption,
                    popup_value: popup_value,
                },
                success: function(response) {

                    alert('Data insert successfully');
                },
                error: function() {
                    alert('Please give the field name');
                }

            });
            $("#form2")[0].reset();
        });
    });

    // for form3
    $(document).ready(function() {
        $("#form3").on('submit', function(event) {
            let label = $('#popup_ln_label').val();
            let datatype = $('#popup_ln_datatype').val();
            let popup_value = $('input[name^=popup_ln_value]').map(function(idx, elem) {
                return $(elem).val();
            }).get()
            event.preventDefault();
            $.ajax({
                type: "POST",
                url: "{{url('config/Formconfig-popup')}}",
                data: {
                    "_token": "{{ csrf_token() }}",
                    label: label,
                    datatype: datatype,
                    popup_value: popup_value,
                },
                success: function(response) {

                    alert('Data insert successfully');
                },
                error: function() {
                    alert('Please give field name');
                }

            });
            $("#form3")[0].reset();
        });
    });

    // for form4
    $(document).ready(function() {
        $("#form4").on('submit', function(event) {
            let label = $('#popup_date_label').val();
            let datatype = $('#popup_date_datatype').val();
            let popup_value = $('input[name^=popup_date_value]').map(function(idx, elem) {
                return $(elem).val();
            }).get()
            event.preventDefault();
            $.ajax({
                type: "POST",
                url: "{{url('config/Formconfig-popup')}}",
                data: {
                    "_token": "{{ csrf_token() }}",
                    label: label,
                    datatype: datatype,
                    popup_value: popup_value,
                },
                success: function(response) {

                    alert('Data insert successfully');
                },
                error: function() {
                    alert('Please give the field name');
                }

            });
            $("#form4")[0].reset();
        });
    });
</script>

@endsection
<!-- <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/js/select2.min.js"></script> -->