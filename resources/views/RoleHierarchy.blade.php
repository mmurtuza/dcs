@extends('backend.layouts.master')

@section('title','Role Hierarchy')
@section('style')
<style>
    tr{
        cursor:pointer;
    }
    .selected{
        background-color: #EDEEF7;
        /* color:#fff; */
        font-weight: bold;
    }
     .bgcolor{
        background-color: #FB3199; 
    } 
    .toggle-checkbox:checked {
    @apply: right-0 border-green-400;
    right: 0;
    border-color: #68D391;
  }
  .toggle-checkbox:checked + .toggle-label {
    @apply: bg-green-400;
    background-color: #68D391;
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
                <h5 class="text-dark font-weight-bold mt-2 mb-2 mr-5">@lang('roleHierarchy.header')</h5>
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
                            <form action="{{route('hierarchy-update')}}" method="post">
                                @csrf
                                <div class="data-table">
                                <center>
                                    <h3>@lang('roleHierarchy.intro')</h3>
                                </center>
                                <div>
                                    <table id="table" class="table">
                                        <tr class="bgcolor">
                                            <th style="width:50%; text-align: center;">@lang('roleHierarchy.table_header1')</th>
                                            <th style="width:50%; text-align: center;">@lang('roleHierarchy.table_header2')</th>
                                        </tr>
                                        @foreach($details as $detail)
                                        @if($detail->position == "0" or $detail->position != null)
                                            <tr>
                                                <input type="hidden" value="{{$detail->position}}" id="position" name="position[]">
                                                <td style="width:50%; text-align: center;"><input type="text" value="{{$detail->designation}}" id="designation" name="designation[]"
                                                readonly ></td>
                                                <td style="width:50%; text-align: center;">
                                                <input data-id="{{$detail->id}}" name="status[]" value="{{$detail->designation}}"  class="toggle-class" type="checkbox" data-onstyle="warning" data-offstyle="danger" data-toggle="toggle" data-on="Active" data-off="InActive" {{ $detail->status ? 'checked' : '' }}>
                                                </td>
                                            </tr>
                                        @endif                                        
                                        @endforeach
                                        
                                        @foreach($details as $detail)
                                        @if($detail->position == null and $detail->position != "0")
                                            <tr>
                                                <input type="hidden" value="{{$detail->position}}" id="position" name="position[]">
                                                <td style="width:50%; text-align: center;"><input type="text" value="{{$detail->designation}}" id="designation" name="designation[]"
                                                readonly disabled></td>
                                                <td style="width:50%; text-align: center;">
                                                <input data-id="{{$detail->id}}" name="status[]" value="{{$detail->designation}}"  class="toggle-class" type="checkbox" data-onstyle="warning" data-offstyle="danger" data-toggle="toggle" data-on="Active" data-off="InActive" {{ $detail->status ? 'checked' : '' }}>
                                                </td>
                                            </tr>
                                        @endif                                        
                                        @endforeach
                                    </table><br>
                                   <center>
                                    <p>@lang('roleHierarchy.button')</p>                                    
                                    <button type="reset" onclick="upNdown('up');"><i class="fas fa-arrow-up"></i></button>
                                    <button type="reset" onclick="upNdown('down');"><i class="fas fa-arrow-down"></i></button>
                                    </center>
                                </div>
                                <br><br>
                                <div id="new-form"></div>
                                <div class="box-footer">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <!-- <button type="reset" id="add_btn" class="btn btn-warning btn-block">Add New</button> -->
                                        </div>
                                        <div class="col-md-4">
                                            <button type="submit"  class="btn btn-secondary btn-block">@lang('actionBtn.update')</button>
                                        </div>
                                        <div class="col-md-4">
                                            
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

    @endsection

    @section('script')
    <script>

    // up down script
        var index;
        function getSelectedRow()
        {
            var table = document.getElementById("table");
            for(var i=1; i<table.rows.length; i++)
            {
                table.rows[i].onclick = function()
                {
                    if(typeof index !== "undefined" )
                    {
                        table.rows[index].classList.toggle("selected");
                    }
                    index = this.rowIndex;
                    this.classList.toggle("selected");
                }
            }
        }getSelectedRow();

        function upNdown(direction)
        {
            var rows =document.getElementById("table").rows,
            parent= rows[index].parentNode;
            console.log(rows);

            if(direction ==="up")
            {
                if(index > 1)
                {
                    parent.insertBefore(rows[index], rows[index-1]);
                    index--;
                }
            }

            if(direction ==="down")
            {
                if(index < rows.length -1)
                {
                    parent.insertBefore(rows[index+1], rows[index]);
                    index++;
                }
            }
        }

    //    active/inactive
    $(function() {
    $('.toggle-class').change(function() {
        var status = $(this).prop('checked') == true ? 1 : 0; 
        var role_id = $(this).data('id');
        var convertedIntoArray = [];
        $("table#table tr").each(function() {
            var actualData = $(this).find('#designation');
            if (actualData.length > 0) {
                actualData.each(function() {
                convertedIntoArray.push($(this).val());
                });
            }
        }); 
        console.log(convertedIntoArray);
        $.ajax({
            type: "GET",
            dataType: "json",
            url: "{{url('config/update-status')}}",
            data: {
                'status': status,
                'role_id': role_id,
                'designation': convertedIntoArray
            },
            success: function(data){
              console.log(data)
              location.reload();
            }
        });
    })
  })
    </script>
    @endsection
   