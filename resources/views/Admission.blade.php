@extends('backend.layouts.master')

@section('title','Admission')
@section('style')
<style>
   h4{
       padding-top:15px;
   }
   #refBy{
       display:none;
   }
   .spouceInformation{
       display:none;
   }
   #business{
       display:none;
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
                <h5 class="text-dark font-weight-bold mt-2 mb-2 mr-5">Member Admission</h5>
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
                            {{-- <form action="{{route('store')}}" method="post" onsubmit="return confirm('Are you sureyou want to submit?');" name="registration"> --}}
                            <form action="{{route('store')}}" method="post" >
                            @csrf
                                <div class="box-body">
                                    <h4>Referral Admission</h4>                                   
                                    <div class="form-group">
                                        <label for="referAdmission">Referral Admission</label> 
                                        <select id="referAdmission" name="referAdmission" class="form-control">
                                            <option selected disabled>Select</option>
                                            <option value="yes">Yes</option>
                                            <option value="no">No</option>
                                        </select>
                                    </div>
                                    <div class="" id="refBy">
                                    <div class="form-group" >
                                            <label for="referBy">Referred by *</label> <br>
                                            <input type="text" class="form-control" id="referBy" name="referBy" >                                        
                                    </div>
                                    </div>

                                    <h4>Basic information</h4>
                                    <div class="form-row">
                                        <div class="col-md-6 mb-3">
                                            <label for="enrolmentid">Enrolment ID *</label> 
                                            <input type="text" class="form-control" id="enrolmentid" name="enrolmentid">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="memeberid">Member ID *</label> 
                                            <input type="text" class="form-control" id="memeberid" name="memeberid">
                                        </div>
                                    </div>

                                    <h4>Client information</h4>
                                    <div class="form-group">
                                        <label for="membercat">Member Category *</label> 
                                        <select id="membercat" name="membercat" class="form-control">
                                            <option>Select</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="appliname">Applicant’s name *</label> 
                                        <input type="text" class="form-control" id="appliname" name="appliname" >                                        
                                    </div>
                                    <div class="form-row">
                                        <div class="col-md-6 mb-3">
                                        <label for="mainidtype">Main ID Type</label>
                                        <select id="mainidtype" name="mainidtype" class="form-control">
                                            <option selected disabled>Select</option>
                                            <option value="Old NID">Old NID (17 Digits)</option>
                                            <option value="Smart NID">Smart NID (10 Digits)</option>
                                            <option value="Birth Certificate">Birth Certificate (17 Digits)</option>
                                        </select>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="mainidnumber">Main ID Number</label> 
                                            <input type="text" class="form-control" id="mainidnumber" name="mainidnumber"  >
                                        </div>
                                    </div>
                                    <div class="form-row">
                                        <div class="col-md-6 mb-3">
                                        <label for="otheridtype">Other ID Type</label>
                                        <select id="otheridtype" name="otheridtype" class="form-control">
                                            <option selected disabled>Select</option>
                                            <option value="Passport">Passport (10 Characters)</option>
                                            <option value="Driving License">Driving License (15 Characters)</option>
                                        </select>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="otheridnumber">Other ID number </label> 
                                            <input type="text" class="form-control" id="otheridnumber" name="otheridnumber"  >
                                        </div>
                                    </div>
                                    <div class="form-row">
                                        <div class="col-md-4 mb-3">
                                            <label for="exdate">Expiry Date </label> 
                                            <input type="date" class="form-control" id="exdate" name="exdate"  >
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label for="placecountry">Place of issuing country </label> 
                                            <input type="date" class="form-control" id="placecountry" name="placecountry"  >
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label for="dateofbirth">Date of birth* </label> 
                                            <input type="date" class="form-control" id="dateofbirth" name="dateofbirth"  >
                                        </div>
                                    </div>
                                    <div class="form-row">
                                        <div class="col-md-6 mb-3">
                                            <label for="mothername">Mother’s name *</label> 
                                            <input type="text" class="form-control" id="mothername" name="mothername"  >
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="fathername">Father’s name *</label> 
                                            <input type="text" class="form-control" id="fathername" name="fathername"  >
                                        </div>
                                    </div>
                                    <div class="form-row">
                                        <div class="col-md-6 mb-3">
                                        <label for="education">Education</label>
                                        <select id="education" name="education" class="form-control">
                                            <option selected disabled>Select</option>
                                            <option value='Pre-primary'>Pre-primary</option>
                                            <option value='Primary'>Primary</option>
                                            <option value='Secondary'>Secondary</option>
                                            <option value='Higher-secondary'>Higher-secondary</option>
                                            <option value='Graduation'>Graduation</option>
                                            <option value='Post-graduation'>Post-graduation</option>
                                            <option value='Others'>Others</option>
                                        </select>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="phonenumber">Phone number *</label> 
                                            <input type="text" class="form-control" id="phonenumber" name="phonenumber"  >
                                        </div>
                                    </div>
                                    <div class="form-row">                                        
                                        <div class="col-md-6 mb-3">
                                            <label for="presentadd">Present address*</label> 
                                            <input type="text" class="form-control" id="presentadd" name="presentadd"  >
                                        </div>
                                        <div class="col-md-6 mb-3">
                                        <label for="presentup">Upazila*</label>
                                        <select id="presentup" name="presentup" class="form-control">
                                            <option>Select</option>                                            
                                        </select>
                                        </div>
                                    </div>
                                    <div class="form-row">                                        
                                        <div class="col-md-6 mb-3">
                                            <label for="permanentadd">Permanent address *</label> 
                                            <input type="text" class="form-control" id="permanentadd" name="permanentadd"  >
                                        </div>
                                        <div class="col-md-6 mb-3">
                                        <label for="permanentup">Upazila*</label>
                                        <select id="permanentup" name="permanentup" class="form-control">
                                            <option>Select</option>                                            
                                        </select>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label for="maritalstatus">Marital status  *</label> 
                                        <select id="maritalstatus" name="maritalstatus" class="form-control">
                                            <option selected disabled>Select</option>
                                            <option value='married' >Married</option>
                                            <option value='unmarried'>Unmarried</option>  
                                            <option value='widower'>Widower</option>
                                            <option value='divorce'>Divorce</option>
                                        </select>                                       
                                    </div>
                                    <div class="spouceInformation">
                                    <div class="form-group">
                                        <label for="spousename">Spouse Name *</label> 
                                        <input type="text" class="form-control" id="spousename" name="spousename"  >                                        
                                    </div>
                                    <div class="form-row">                                        
                                        <div class="col-md-6 mb-3">
                                            <label for="spoucenid">Spouse NID/ Birth Certificate *</label> 
                                            <input type="text" class="form-control" id="spoucenid" name="spoucenid"  >
                                        </div>
                                        <div class="col-md-6 mb-3">
                                        <label for="spoucenidimg">Spouse NID/ Birth Certificate *</label>
                                        <input type="file" class="form-control" id="spoucenidimg" name="spoucenidimg"  >
                                        </div>
                                    </div>                                   
                                    <div class="form-group">
                                        <label for="sppoucedob">Spouse DOB * </label> 
                                        <input type="date" class="form-control" id="sppoucedob" name="sppoucedob" >                                        
                                    </div>
                                    <div class="form-group">
                                        <label for="spoceoccu">Spouse Occupation</label> 
                                        <select id="spoceoccu" name="spoceoccu" class="form-control">
                                            <option selected disabled>Select</option>
                                            <option value='self-employed'>Self-employed</option>
                                            <option value='service'>Service</option>  
                                            <option value='business'>Business</option>
                                            <option value='agriculture'>Agriculture</option>
                                            <option value='potter'>Potter</option>  
                                            <option value='tea garden worker'>Tea Garden Worker</option>
                                            <option value='handloom worker'>Handloom Worker</option>
                                            <option value='fisherman'>Fisherman</option>  
                                            <option value='others'>Others</option>
                                        </select>                                       
                                    </div>
                                    </div>
                                    <div class="form-group" id="business">
                                        <label for="spoucebusiness">Spouse Business Type</label> 
                                        <select id="spoucebusiness" name="spoucebusiness" class="form-control">
                                            <option selected disabled>Select</option>
                                            <option value='proprietorship'>Proprietorship</option>
                                            <option value='partnership'>Partnership</option>  
                                            <option value='private'>Private Ltd. Co</option>
                                            <option value='co-operative'>Co-operative</option> 
                                            <option value='others'>Others</option>
                                        </select>                                       
                                    </div>
                                    <div class="form-group">
                                        <label for="refname">Referrer Name *</label> 
                                        <input type="text" class="form-control" id="refname" name="refname" >                                        
                                    </div> 
                                    <div class="form-row">                                        
                                        <div class="col-md-6 mb-3">
                                            <label for="refidd">Referrer ID *</label> 
                                            <input type="file" class="form-control" id="refidd" name="refidd"  >
                                        </div>
                                        <div class="col-md-6 mb-3">
                                        <label for="refphone">Referrer mobile number</label>
                                        <input type="text" class="form-control" id="refphone" name="refphone"  >
                                        </div>
                                    </div>                                     
                                    

                                    <h4>Family information</h4>
                                    <div class="form-row">                                        
                                        <div class="col-md-6 mb-3">
                                            <label for="familynumber">No. of Family Member(s)</label> 
                                            <input type="text" class="form-control" id="familynumber" name="familynumber"  >
                                        </div>
                                        <div class="col-md-6 mb-3">
                                        <label for="childnumber">No. of Children</label>
                                        <input type="text" class="form-control" id="childnumber" name="childnumber"  >
                                        </div>
                                    </div>  
                                    <div class="form-group">
                                        <label for="nomineename">Nominee Name</label> 
                                        <input type="text" class="form-control" id="nomineename" name="nomineename" >                                        
                                    </div>
                                    <div class="form-row">                                        
                                        <div class="col-md-6 mb-3">
                                            <label for="nomineedob">Nominee Date of Birth</label> 
                                            <input type="date" class="form-control" id="nomineedob" name="nomineedob"  >
                                        </div>
                                        <div class="col-md-6 mb-3">
                                        <label for="nomineenidimg">Nominee NID/ Birth Certificate</label>
                                        <input type="file" class="form-control" id="nomineenidimg" name="nomineenidimg"  >
                                        </div>
                                    </div>  
                                    <div class="form-group">
                                        <label for="relationship">Relationship</label> 
                                        <select id="relationship" name="relationship" class="form-control">
                                            <option selected disabled>Select</option>
                                            <option value="father">Father</option>
                                            <option value="mother">Mother</option>  
                                            <option value="sister">Sister</option>
                                            <option value="brother">Brother</option> 
                                            <option value="husband">Husband</option>
                                            <option value="wife">Wife</option>
                                            <option value="son">Son</option> 
                                            <option value="daughter">Daughter</option>
                                            <option value="others">Others</option>
                                        </select>                                       
                                    </div> 
                                    <h4>Photos</h4> 
                                    <div class="form-row">                                        
                                        <div class="col-md-6 mb-3">
                                            <label for="appicombinpic">Applicant’s combined picture</label> 
                                            <input type="file" class="form-control" id="appicombinpic" name="appicombinpic"  >
                                        </div>
                                        <div class="col-md-6 mb-3">
                                        <label for="refpic">Referrer picture</label>
                                        <input type="file" class="form-control" id="refpic" name="refpic"  >
                                        </div>
                                    </div>  
                                    <div class="form-row">                                        
                                        <div class="col-md-6 mb-3">
                                            <label for="nidfront">NID/ other ID front</label> 
                                            <input type="file" class="form-control" id="nidfront" name="nidfront"  >
                                        </div>
                                        <div class="col-md-6 mb-3">
                                        <label for="nidback">NID/ other ID back</label>
                                        <input type="file" class="form-control" id="nidback" name="nidback"  >
                                        </div>
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
    $(document).ready(function () {
        // reference information
        $(document).on('change', '.form-control', function () {
            if ($(this).val() == "yes") {
                document.querySelector('#refBy').style.display = 'flex';
        }
        })
        $(document).on('change', '.form-control', function () {
            if ($(this).val() == "no") {
                document.querySelector('#refBy').style.display = 'none';
        }
        })

        // spouce Information
        $(document).on('change', '.form-control', function () {
            if ($(this).val() == "married") {
                document.querySelector('.spouceInformation').style.display = 'flex';
        }
        })
        $(document).on('change', '.form-control', function () {
            if ($(this).val() == "no") {
                document.querySelector('#refBy').style.display = 'none';
        }
        })

        // business Information
        $(document).on('change', '.form-control', function () {
            if ($(this).val() == "business") {
                document.querySelector('.spouceInformation').style.display = 'flex';
        }
        })
        $(document).on('change', '.form-control', function () {
            if ($(this).val() == "no") {
                document.querySelector('#refBy').style.display = 'none';
        }
        })


    });

</script>

@endsection