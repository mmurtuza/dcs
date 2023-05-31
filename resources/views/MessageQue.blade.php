@extends('backend.layouts.master')

@section('title','Message')
@section('style')
<style>
 

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
                <h5 class="text-dark font-weight-bold mt-2 mb-2 mr-5">Message</h5>
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
                            <form action="{{route('message-que')}}" method="post">
                                @csrf
                                <div class="box-body">
                                    <div class="form-group">
                                        <label for="pin">Pin</label>
                                        <input type="text" class="form-control" id="pin" name="pin">
                                    </div> 
                                    <div class="form-group">
                                        <label for="message">Message</label>
                                        <textarea class="form-control rounded-0" id="message" name="message" rows="3"></textarea>
                                    </div>
                                    <div class="form-group">
                                        <label for="readstatus">Read Status</label>
                                        <input type="text" class="form-control" id="readstatus" name="readstatus">
                                    </div>
                                    <div class="form-group">
                                        <label for="docreff">Document Reference</label>
                                        <input type="text" class="form-control" id="docreff" name="docreff">
                                    </div>
                                    <div class="form-group">
                                        <label for="doctype">Document Type</label>
                                        <input type="text" class="form-control" id="doctype" name="doctype">
                                    </div>

                                </div><!-- /.box-body -->
                                
                                <div class="box-footer">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <button type="reset" onclick="resetForm()" class="btn btn-warning btn-block">Reset</button>
                                        </div>
                                        <div class="col-md-6">
                                            <button type="submit" class="btn btn-secondary btn-block">Submit</button>
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
        </div>
        <!--end::Container-->
    </div>
</div>

@endsection

@section('script')
<script>
    

</script>


@endsection
