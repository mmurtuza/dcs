@extends('backend.layouts.master')

@section('title','Admin Config')
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
    <!--end::Subheader-->
    <!--begin::Entry-->
    <div class="d-flex flex-column-fluid">
        <!--begin::Container-->
        <div class="container">
            <!--begin::Dashboard-->
            <!--begin::Row-->
            <div class="row">
                <div class="col-md-12">
                    <div class="card card-custom gutter-b">
                        <!--begin::Form-->
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-12">
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
                                    <table style="text-align: center;font-size:13" class="table table-bordered" id="">
                                        <thead>
                                            <tr class="brac-color-pink">
                                                <th>Name</th>
                                                <th>Email</th>
                                                <th>Phone</th>
                                                <th>User Name</th>
                                                <th>User Pin</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>

                                        <tbody>
                                            @foreach ($admins as $row)
                                            <tr>
                                                <td>{{ $row->name }}</td>
                                                <td>{{ $row->email }}</td>
                                                <td>{{ $row->phone }}</td>
                                                <td>{{ $row->username }}</td>
                                                <td>{{ $row->userpin }}</td>
                                                <td><a href="{{url('config/admin-edit',$row->id )}}" class="btn btn-warning" >Edit</a>
                                                    <a href="{{url('config/admin-delete',$row->id )}}" class="btn btn-warning">Delete</a></td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                    <div class="box-footer">
                                        <div class="row">
                                            <div class="col-md-5">
                                            </div>
                                            <div class="col-md-2">
                                                <button type="submit" id="add_btn"
                                                    class="btn btn-secondary btn-block">@lang('actionBtn.addNew')</button>
                                            </div>
                                            <div class="col-md-5">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!--end::Form-->
                    </div>
                    <!--end::Advance Table Widget 4-->
                </div>
                <br>
            </div>
            <!--end::Row-->
            <!--begin::Row-->

            <!--end::Row-->
            <!--end::Dashboard-->
        </div>
        <!--end::Container-->
    </div>
    <!--end::Entry-->
</div>
<!-- The Modal -->
<div id="myModal" class="modal">
    <!-- Modal content -->
    <div class="modal-content">
        <div>
            <span class="close">&times;</span>
        </div>
        <div>
            <form action="{{route('admin_store')}}" method="post">
                @csrf
                <div class="box-body">
                    <div class="form-group">
                        <label for="Name">Name</label>
                        <input type="text" class="form-control" id="Name" name="Name" required>
                    </div>
                    <div class="form-group">
                        <label for="username">User Name</label>
                        <input type="text" class="form-control" id="username" name="username" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="text" class="form-control" id="email" name="email" required>
                    </div>
                    <div class="form-group">
                        <label for="phone">Phone Number</label>
                        <input type="text" class="form-control" id="phone" name="phone" required>
                    </div>
                    <div class="form-group">
                        <label for="userpin">User Pin</label>
                        <input type="text" class="form-control" id="userpin" name="userpin" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="text" class="form-control" id="password" name="password">
                    </div>
                    <input type="hidden" class="form-control" value="0" id="role" name="role">
                    {{-- <div class="form-group">
                        <label for="role">Role</label>
                        <select class="form-control" name="role" id="role" required>
                            <option value="">select</option>
                            <option value="1">Super Admin</option>
                            <option value="0">Admin</option>
                        </select>
                    </div> --}}
                    <input type="hidden" id="status" name="status" value="1">
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
    // Get the modal
    var modal = document.getElementById("myModal");

    // Get the button that opens the modal
    var btn = document.getElementById("add_btn");

    // Get the <span> element that closes the modal
    var span = document.getElementsByClassName("close")[0];

    // When the user clicks the button, open the modal 
    btn.onclick = function () {
        modal.style.display = "block";
    }

    // When the user clicks on <span> (x), close the modal
    span.onclick = function () {
        modal.style.display = "none";
    }

    // When the user clicks anywhere outside of the modal, close it
    // window.onclick = function (event) {
    //     if (event.target == modal) {
    //         modal.style.display = "none";
    //     }
    // }

</script>
@endsection
