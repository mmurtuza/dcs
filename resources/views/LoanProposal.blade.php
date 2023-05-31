@extends('backend.layouts.master')

@section('title','Loan proposal')
@section('style')
<style>
    h4{
        margin-top:20px;
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
                <h5 class="text-dark font-weight-bold mt-2 mb-2 mr-5">Loan proposal</h5>
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
                                    <h4>1. Basic information</h4>
                                    <div class="mb-3">
                                        <label for="memberiD" class="form-label">Member ID</label>
                                        <input type="text" class="form-control" name="memberiD" id="memberiD">
                                    </div>
                                    <h4>2. Client Information</h4>
                                    <div class="form-row">
                                        <div class="col-md-6 mb-3">
                                            <label for="loanproduct" class="form-label">1. Loan product *</label>
                                            <select class="form-control" id="loanproduct" name="loanproduct">
                                                <option selected disabled>Choose one...</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="loanduration" class="form-label">2. Loan Duration *</label>
                                            <select class="form-control" id="loanduration" name="loanduration">
                                                <option selected disabled>Choose one...</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-row">
                                        <div class="col-md-6 mb-3">
                                            <label for="investmentsector" class="form-label">3. Investment sector *</label>
                                            <select class="form-control" id="investmentsector" name="investmentsector">
                                                <option selected disabled>Choose one...</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="scheme" class="form-label">4. Scheme *</label>
                                            <select class="form-control" id="scheme" name="scheme">
                                                <option selected disabled>Choose one...</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-row">
                                        <div class="col-md-6 mb-3">
                                            <label for="proposedamount" class="form-label">5. Proposed Amount * </label>
                                            <input type="text" class="form-control" id="proposedamount" name="proposedamount" placeholder="50000">

                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="instalmentamount" class="form-label">6. Instalment Amount *</label>
                                            <select class="form-control" id="instalmentamount" name="instalmentamount">
                                                <option selected disabled>Choose one...</option>
                                                <option value="">Potential</option>
                                                <option value="">Not potential</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="loaninfamily" class="form-label">7. BRAC loan in the family
                                            *</label>
                                        <input type="text" class="form-control" name="loaninfamily" id="loaninfamily">
                                    </div>

                                    <h4>3. Loan recommender</h4>
                                    <div class="mb-3">
                                        <label for="memberID" class="form-label">VO Leader: Drop Down*</label><br>
                                        <label for="memberID" class="form-label">Recommender : Drop Down*</label>
                                    </div>

                                    <h4>4. Guarantor information</h4>
                                    <div class="mb-3">
                                        <label for="guarantorname" class="form-label">4.1 Name</label>
                                        <input type="text" class="form-control" name="guarantorname" id="guarantorname">
                                    </div>
                                    <div class="form-row">
                                        <div class="col-md-6 mb-3">
                                            <label for="guarantorphone" class="form-label">4.2 Mobail Number</label>
                                            <input type="text" class="form-control" name="guarantorphone" id="guarantorphone">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="relationshipclient" class="form-label">4.3 Relationship with client</label>
                                            <select class="form-control" id="relationshipclient" name="relationshipclient">
                                                <option selected disabled>Choose one...</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="guarantornid" class="form-label">4.4 Guarantor’s NID number</label>
                                        <input type="text" class="form-control" name="guarantornid" id="guarantornid">
                                    </div>
                                    <div class="form-row">
                                        <div class="col-md-6 mb-3">
                                            <label for="guarantornidfimg" class="form-label">4.5. Guarantor’s NID
                                                front</label>
                                            <input type="file" class="form-control" name="guarantornidfimg" id="guarantornidfimg">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="guarantornidbimg" class="form-label">4.6 Guarantor’s NID
                                                back</label>
                                            <input type="file" class="form-control" name="guarantornidbimg" id="guarantornidbimg">
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="guarantorimg" class="form-label">4.7 Guarantor’s Image</label>
                                        <input type="file" class="form-control" name="guarantorimg" id="guarantorimg">
                                    </div>

                                    <h4>5. CSI Information</h4>
                                    <div class="form-row">
                                        <div class="col-md-6 mb-3">
                                            <label for="insurancetype" class="form-label">5.1. Insurance type</label>
                                            <select class="form-control" id="insurancetype" name="insurancetype">
                                                <option selected disabled>Choose one...</option>
                                                <option value="">Single</option>
                                                <option value="">Double</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="insureroption" class="form-label">5.2. Insurer Option</label>
                                            <select class="form-control" id="insureroption" name="insureroption">
                                                <option selected disabled>Choose one...</option>
                                                <option value="">Existing</option>
                                                <option value="">New</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-row">
                                        <div class="col-md-6 mb-3">
                                            <label for="2ndinsurer" class="form-label">5.3. 2nd Insurer</label>
                                            <select class="form-control" id="2ndinsurer" name="2ndinsurer">
                                                <option selected disabled>Choose one...</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="newinsurer" class="form-label">5.4. Add New Insurer
                                                Information</label>
                                            <select class="form-control" id="newinsurer" name="newinsurer">
                                                <option selected disabled>Choose one...</option>
                                                <option value="">Gender</option>
                                                <option value="">Relationship</option>
                                            </select>
                                        </div>
                                    </div>

                                    <h4>6. Residence information</h4>
                                    <div class="form-row">
                                        <div class="col-md-6 mb-3">
                                            <label for="residencetype" class="form-label">6.1. Residence type </label>
                                            <input type="text" class="form-control" id="residencetype" name="residencetype" placeholder="50000">

                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="durationofresidence" class="form-label">6.2. Duration of residence
                                            </label>
                                            <input type="text" class="form-control" id="durationofresidence" name="durationofresidence" placeholder="50000">

                                        </div>
                                    </div>
                                    <div class="form-row">
                                        <div class="col-md-6 mb-3">
                                            <label for="houseowner" class="form-label">6.3. House owner knows</label>
                                            <select class="form-control" id="houseowner" name="houseowner">
                                                <option selected disabled>Choose one...</option>
                                                <option value="">Yes</option>
                                                <option value="">No</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="relativesaddress" class="form-label">6.4. Relatives in present
                                                address</label>
                                            <input type="text" class="form-control" id="relativesaddress" name="relativesaddress" placeholder="50000">
                                        </div>
                                    </div>
                                    <div class="form-row">
                                        <div class="col-md-6 mb-3">
                                            <label for="relativesname" class="form-label">6.5. Relatives name </label>
                                            <input type="file" class="form-control" id="relativesname" name="relativesname" placeholder="50000">

                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="relativephone" class="form-label">6.6. Relative phone number
                                            </label>
                                            <input type="file" class="form-control" id="relativephone" name="relativephone" placeholder="50000">
                                        </div>
                                    </div>

                                    <h4>7. Job information</h4>
                                    <div class="form-row">
                                        <div class="col-md-6 mb-3">
                                            <label for="jobtenure" class="form-label">7.1. Job tenure </label>
                                            <input type="text" class="form-control" id="jobtenure" name="jobtenure" placeholder="50000">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="salary" class="form-label">7.2. Salary</label>
                                            <input type="text" class="form-control" id="salary" name="salary" placeholder="50000">
                                        </div>
                                    </div>

                                    <h4>RCA:</h4>

                                    <h5>8. Income information</h5>
                                    <div class="mb-3">
                                        <label for="primaryearner" class="form-label">8.1. Primary earner </label>
                                        <input type="text" class="form-control" id="primaryearner" name="primaryearner" placeholder="50000">
                                    </div>
                                    <div class="form-row">
                                        <div class="col-md-6 mb-3">
                                            <label for="incomemainsourcedaily" class="form-label">8.2. Income from main source
                                                (Household) Daily</label>
                                            <input type="text" class="form-control" id="incomemainsourcedaily" name="incomemainsourcedaily" placeholder="50000">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="incomemainsourcemonthly" class="form-label">8.3. Income from main source
                                                (Household) Monthly </label>
                                            <input type="text" class="form-control" id="incomemainsourcemonthly" name="incomemainsourcemonthly" placeholder="50000">
                                        </div>
                                    </div>
                                    <div class="form-row">
                                        <div class="col-md-6 mb-3">
                                            <label for="incomeothersourcesdaily" class="form-label">8.4. Income from other sources
                                                (Household) Daily</label>
                                            <input type="text" class="form-control" id="incomeothersourcesdaily" name="incomeothersourcesdaily" placeholder="50000">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="incomeothersourcesmonthly" class="form-label">8.5. Income from other sources
                                                (Household) Monthly </label>
                                            <input type="text" class="form-control" id="incomeothersourcesmonthly" name="incomeothersourcesmonthly" placeholder="50000">
                                        </div>
                                    </div>

                                    <h4>9. Expenditure information</h4>
                                    <div class="form-row">
                                        <div class="col-md-6 mb-3">
                                            <label for="houserent" class="form-label">9.1. House rent</label>
                                            <input type="text" class="form-control" id="houserent" name="houserent" placeholder="50000">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="food" class="form-label">9.2. Food</label>
                                            <input type="text" class="form-control" id="food" name="food" placeholder="50000">
                                        </div>
                                    </div>
                                    <div class="form-row">
                                        <div class="col-md-6 mb-3">
                                            <label for="education" class="form-label">9.3. Education</label>
                                            <input type="text" class="form-control" id="education"  name="education" placeholder="50000">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="medicaltreatment" class="form-label">9.4. Medical treatment</label>
                                            <input type="text" class="form-control" id="medicaltreatment" name="medicaltreatment" placeholder="50000">
                                        </div>
                                    </div>
                                    <div class="form-row">
                                        <div class="col-md-6 mb-3">
                                            <label for="festival" class="form-label">9.5. Festival</label>
                                            <input type="text" class="form-control" id="festival" name="festival" placeholder="50000">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="utilitybill" class="form-label">9.6. Utility bill</label>
                                            <input type="text" class="form-control" id="utilitybill" name="utilitybill" placeholder="50000">
                                        </div>
                                    </div>
                                    <div class="form-row">
                                        <div class="col-md-6 mb-3">
                                            <label for="savingother" class="form-label">9.7. Saving in other
                                                MFI/Bank/Brac</label>
                                            <input type="text" class="form-control" id="savingother" name="savingother" placeholder="50000">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="others" class="form-label">9.8. Others</label>
                                            <input type="text" class="form-control" id="others" name="others" placeholder="50000">
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="expenditure" class="form-label">9.9. Total expenditure</label>
                                        <input type="text" class="form-control" id="expenditure" name="expenditure" placeholder="50000">
                                    </div>
                                    <h4>10.	Liability</h4>
                                    <div class="form-row">
                                        <div class="col-md-6 mb-3">
                                            <label for="OtherMFI" class="form-label">10.1. Other MFI monthly instalment</label>
                                            <input type="text" class="form-control" id="OtherMFI" name="OtherMFI"  placeholder="50000">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="debtfromother" class="form-label">10.2.	Debt from other sources</label>
                                            <input type="text" class="form-control" id="debtfromother" name="debtfromother" placeholder="50000">
                                        </div>
                                    </div>
                                    <h4>11.	Final estimation</h4>
                                    <div class="mb-3">
                                        <label for="monthlycash" class="form-label">11.1.	Monthly cash in hand</label>
                                        <input type="text" class="form-control" id="monthlycash" name="monthlycash" placeholder="50000">
                                    </div>
                                    <div class="form-row">
                                        <div class="col-md-6 mb-3">
                                            <label for="proposedloan" class="form-label">11.2.	Instalment of proposed loan</label>
                                            <input type="text" class="form-control" id="proposedloan" placeholder="50000" name="proposedloan">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="tolerancelimit" class="form-label">11.3.	Tolerance limit (%)</label>
                                            <input type="text" class="form-control" id="tolerancelimit" placeholder="50000" name="tolerancelimit">
                                        </div>
                                    </div>

                                </div><!-- /.box-body -->


                                <div class="box-footer">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <button type="reset" onclick="resetForm()"
                                                class="btn btn-warning btn-block">Reset</button>
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
