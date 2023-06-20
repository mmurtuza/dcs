<template>
     <div class="container">
            <div class="card card-custom">
                <div class="card-body">
                    <div class="form_value">
                        <div class="form-row">
                            <div class="col-md-2 mb-3">
                                <label class="ml-2" for="division">Division</label>
                                <select v-model="selectedDivision" @change="getRegions" class="form-control">
                                    <option value="">Select</option>
                                    <option v-for="division in divisions" :value="division.division_id">{{ division.division_name }}</option>
                                </select>
                            </div>
                            <div class="col-md-2 mb-3">
                                <label class="ml-2" for="region">Region</label>
                                <select v-model="selectedRegion" @change="getAreas" class="form-control">
                                    <option value=""></option>
                                    <option v-for="region in regions" :value="region.region_id">{{ region.region_id }}-{{
                                        region.region_name }}</option>
                                </select>
                            </div>
                            <div class="col-md-2 mb-3">
                                <label class="ml-2" for="area">Area</label>
                                <select v-model="selectedArea" @change="getBranches" class="form-control">
                                    <option value=""></option>
                                    <option v-for="area in areas" :value="area.area_id">{{ area.area_id }}-{{ area.area_name }}
                                    </option>
                                </select>
                            </div>
                            <div class="col-md-2 mb-3">
                                <label class="ml-2" for="branch">Branch</label>
                                <select v-model="selectedBranch" @change="getPOs" class="form-control">
                                    <option value=""></option>
                                    <option v-for="branch in branches" :value="branch.branch_id">{{ branch.branch_id }}-{{
                                        branch.branch_name }}</option>
                                </select>
                            </div>
                            <div class="col-md-2 mb-3">
                                <label class="ml-2" for="po">PO</label>
                                <select v-model="selectedPO" class="form-control">
                                    <option value=""></option>
                                    <option v-for="po in pos" :value="po.cono">{{ po.cono }}-{{ po.coname }}</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="col-md-2 mb-3">
                                <label class="ml-2" for="dateFrom"></label>

                                <input type="date" v-model="dateFrom" class="form-control" id="dateFrom">
                            </div>
                            <div class="col-md-2 mb-3">
                                <label class="ml-2" for="dateTo"></label>
                                <input type="date" v-model="dateTo" class="form-control" id="dateTo">
                            </div>
                            <div class="col-md-2 mb-3 mt-8">
                                <button @click="searchData" class="btn btn-secondary">Search</button>
                            </div>
                        </div>
                    </div>
                </div>
        </div>
    </div>
    <br>

<div class="card_section">
    <div class="row">
        <div class="col-md-2.5">
            <div class="card" style="width: 18rem; height: 131;">
                <div class="card-body" style="background-color:#90ee90;">
                    <h5 class="card-title">No of Admission</h5>
                    <h5 class="text-center" id="totalAdmission">{{ this.totallCount.pendingadminssioncount }}</h5>
                </div>
            </div>
        </div>
        <div class="col-md-2.5">
            <div class="card" style="width: 18rem; height: 131;">
                <div class="card-body" style="background-color:#90ee90;">
                    <h5 class="card-title">No of Profile Update</h5>
                    <h5 class="text-center" id="totalProfileAdmission">{{this.totallCount.pendingprofileadmission }}</h5>
                </div>
            </div>
        </div>
        <div class="col-md-2.5">
            <div class="card" style="width: 18rem; height: 131;">
                <div class="card-body" style="background-color:#FF77FF">
                    <h6 class=" card-title">No of Loan Application</h6>
                    <h5 class="text-center" id="totalLoan">{{ this.totallCount.pendingloan }}</h5>
                </div>
            </div>
        </div>
        <div class="col-md-2.5">
            <div class="card" style="width: 18rem; height: 131;">
                <div class="card-body" style="background-color:#fed8b1">
                    <h5 class="card-title" style="padding: 0; margin: 0;">
                       Total Disbursed Amount</h5>
                    <h5 class="text-center" style="margin-top:7px;" id="total_disbuse">{{ this.totallCount.disburseamt}}</h5>
                </div>
            </div>
        </div>
        <div class="col-md-2.5">
            <div class="card" style="width: 18rem; height: 131;">
                <div class="card-body" style="background-color:#ffcccb">
                    <h5 class="card-title">Pending for Approval</h5>
                    <h5 class="text-center"><span
                            class="pending_data"></span>{{ this.totallCount.allpendingloan }}</h5>
                </div>
            </div>
        </div>
    </div>
</div>

<h4 class="col-12">Loan Disbursement Status(<span style="color:red;">Showing Data from <span id="startDateShow"
            style="font-style: italic; text-decoration: underline;">{{ this.totallCount.fromdate }}</span>
        to <span id="endDateShow"
            style="font-style: italic; text-decoration: underline;">{{ this.totallCount.today }}</span></span>)
</h4>

    <div class="col-md-3" style="margin-top:42px;">
        <div class=" mb-3">
           <button type="button" id="pending" @click="()=>{getDatas(null, 1, 'pending'); this.erpStatus=null; this.roleStatus='1'; getPendingCount() }"
        :class="{ 'btn btn-block btn-secondary': activeButton !== 'pending', 'btn btn-block btn-secondary active': activeButton === 'pending' }">
      Pending(<span class="pending_data">{{ this.totallCount.allpendingloan }}</span>)
    </button>
        </div>


        <div class=" mb-3">
            <button type="button" id="approved" @click="()=>{getDatas(1, 2, 'approved'); this.erpStatus= '1'; this.roleStatus='2'; getApproveCount() }"
       :class="{ 'btn btn-block btn-secondary': activeButton !== 'approved', 'btn btn-block btn-secondary active': activeButton === 'approved' }">
      Approved(<span id="approved_data">{{ this.totallCount.allapproveloan }}</span>)
    </button>
        </div>

        <div class="mb-3">
       <button type="button" id="disbursement" @click="()=>{getDatas(2, 2, 'disbursement'); this.erpStatus= '2'; this.roleStatus='2'; getDisbursementCount() }"
        :class="{ 'btn btn-block btn-secondary': activeButton !== 'disbursement', 'btn btn-block btn-secondary active': activeButton === 'disbursement' }">
      Ready for Disbursement(<span id="approved_data">{{ this.totallCount.all_disbursement }}</span>)
    </button>
    </div>

        <div class=" mb-3">
           <button type="button" id="disburse" @click="()=>{getDatas(4, 2, 'disburse'); this.erpStatus = '4'; this.roleStatus= '2'; getDisburseCount() }"
      :class="{ 'btn btn-block btn-secondary': activeButton !== 'disburse', 'btn btn-block btn-secondary active': activeButton === 'disburse' }">
      Disburse(<span id="disburse_data">{{ this.totallCount.alldisburseloan }}</span>)
    </button>

        </div>

         <div class=" mb-3">
                <button type="button" id="rejected" @click="()=>{getDatas(3, 3, 'rejected'); this.erpStatus='3'; this.roleStatus='3'; getRejectedCount() }"
      :class="{ 'btn btn-block btn-secondary': activeButton !== 'rejected', 'btn btn-block btn-secondary active': activeButton === 'rejected' }">
      Rejected(<span id="rejected_data">{{ this.totallCount.allrejectloan }}</span>)
    </button>
        </div>
 </div>
            <!-- pending= erpStatus: null, status: 1
    approved = erpStatus: 1, status: 2
    Ready For Disbusrsment = erpStatus: 2, status: 2
    Disburse= erpStatus: 4, status: 2
    Rejected=  erpStatus: 3, status: 3

    roleid 1 BM
    roleid 2 AM
    roleid 3 RM
    roleid 4 DM -->
    <div class="col-md-9">
        <div class="roll_btn">
            <div class="row">
                <div class="col-md-3">
                    <div class="row">
                        <div class="col-md-4"></div>
                        <div class="col-md-8 roll_single_btn">
                            <h4 class="roll_class" id="roll_bm" @click="getRoleWiseData('1')">BM<span id="btn_bm">{{ bmcount }}</span>
                            </h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="row">
                        <div class="col-md-4">

                        </div>
                        <div class="col-md-8 roll_single_btn">
                            <h4 class="roll_class" id="roll_am" @click="getRoleWiseData('2')">AM<span id="btn_am">{{ amcount }}</span>
                            </h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="row">
                        <div class="col-md-4"></div>
                        <div class="col-md-8 roll_single_btn">
                            <h4 class="roll_class" id="roll_rm" @click="getRoleWiseData('3')">RM<span id="btn_rm">{{ rmcount }}</span>
                            </h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="row">
                        <div class="col-md-4"></div>
                        <div class="col-md-8 roll_single_btn">
                            <h4 class="roll_class" id="roll_dm" @click="getRoleWiseData('4')">DM<span id="btn_dm">{{ dmcount }}</span>
                            </h4>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <DataTable :columns="columns" :data="this.datas" :options="options" class="display" width="100%" />
    </div>

</template>

<script>

import axios from 'axios';
import DataTable from 'datatables.net-vue3';
import DataTablesCore from 'datatables.net-bs5';
import 'datatables.net-responsive';
import 'datatables.net-select';

DataTable.use(DataTablesCore);


export default {

    name: 'datas',
    name: 'divisions',

    data() {
        return {
            amcount: '',
            bmcount: '',
            rmcount: '',
            dmcount: '',
            activeButton: 'pending',
            erpStatus: null,
            roleStatus: '1',
            datas: [],
            length:'',
            pendingCount: [],
            approveCount: [],
            approveLoan: [],
            readyForDisbursement:[],
            totallCount: [],
            pending: '',
            approve: '',
            readyDisburse: '',
            disburse: '',
            rejected: '',
            options: {
                processing: true,
                ordering: false,
                searching: false,
                bLengthChange: false,
                responsive: true,
            },

            selectedDivision: '',
            selectedRegion: '',
            selectedArea: '',
            selectedBranch: '',
            selectedPO: '',
            dateFrom: '',
            dateTo: '',
            divisions: [],
            regions: [],
            areas: [],
            branches: [],
            pos: [],
            totallCount: [],
            data: [],
            dateFrom: null,
            dateTo: null,
            columns: [
                {
                    data: 'id',
                    title: 'Details',
                    render: (data) => {
                        return `<button class="btn btn-warning" onclick="window.open('operation/loan-approval/${data}', '_blank')">View</button>`;
                    }
                },
                { data: 'orgno', title: 'Vo Code' },
                { data: 'orgmemno', title: 'Member Number' },
                { data: 'propos_amt', title: 'Disb. Amt' },
                { data: 'loan_product', title: 'Product Name' },
                { data: 'loan_type', title: 'Loan Type' },
                { data: 'branchcode', title: 'Branch Code' },
                { data: 'assignedpo', title: 'Applied By' },
                { data: 'time', title: 'Application Date' },

            ],
            createdRow: ()=>{
            document.querySelector('.datatable tbody tr').setAttribute('role', 'row');

            }
        }
    },
   
    mounted() {

            this.getDatas(null, 1, 'pending'),
            this.addClasses(),
            this.getTottalCount(),
            this.getPendingCount(),
            this.getDivisions()
        const currentDate = new Date().toISOString().slice(0, 10);
        this.dateTo = currentDate;

        const dateString = "2023-01-01";
        const dateObject = new Date(dateString);
        this.dateFrom = dateObject.toISOString().slice(0, 10);


    },
    methods: {

        getDatas(ErpStatus,status, activeButton) {
            const params = {
                ErpStatus: ErpStatus,
                status: status

            };
            axios.post('http://127.0.0.1:8000/fetchdata', params)
                .then(res => {
                    this.datas = res.data;
                    this.length = res.data.length;
                    this.activeButton = activeButton;
                    this.initializeDataTable();
                }).then(this.getTottalCount);
        },

        getTottalCount() {
            axios.get('http://127.0.0.1:8000/allcount').then(res => {
                this.totallCount = res.data
                console.table(this.totallCount)

            }

        );
    },
     getRoleWiseData(data) {
            const params = {
                activeButton: this.activeButton,
                roleid: data,
                erpStatus: this.erpStatus,
                roleStatus: this.roleStatus
            }
            console.table(params);
            axios.post('http://127.0.0.1:8000/roledata', params).then(res => {
                this.datas = res.data;
            });
        },
         getPendingCount() {
            axios.get('http://127.0.0.1:8000/rollcounts').then(res => {
                this.amcount = res.data.am_pending_loan;
                this.bmcount = res.data.bm_pending_loan;
                this.rmcount = res.data.rm_pending_loan;
                this.dmcount = res.data.dm_pending_loan;
            }

            );
        },
         getApproveCount() {
            axios.get('http://127.0.0.1:8000/rollcounts').then(res => {
                this.amcount = res.data.am_approve_loan;
                this.bmcount = res.data.bm_approve_loan;
                this.rmcount = res.data.rm_approve_loan;
                this.dmcount = res.data.dm_approve_loan;

            }

            );
        },
         getDisbursementCount() {
            axios.get('http://127.0.0.1:8000/rollcounts').then(res => {
                this.amcount = res.data.am_disbursement_loan;
                this.bmcount = res.data.bm_disbursement_loan;
                this.rmcount = res.data.rm_disbursement_loan;
                this.dmcount = res.data.dm_disbursement_loan;


            }

            );
        },
         getDisburseCount() {
            axios.get('http://127.0.0.1:8000/rollcounts').then(res => {
                this.amcount = res.data.am_disburse_loan;
                this.bmcount = res.data.bm_disburse_loan;
                this.rmcount = res.data.rm_disburse_loan;
                this.dmcount = res.data.dm_disburse_loan;


            }

            );
        },
         getRejectedCount() {
            axios.get('http://127.0.0.1:8000/rollcounts').then(res => {
                this.amcount = res.data.am_rejected_loan;
                this.bmcount = res.data.bm_rejected_loan;
                this.rmcount = res.data.rm_rejected_loan;
                this.dmcount = res.data.dm_rejected_loan;

            }

            );
        },
                 getDivisions() {
            axios.get('http://127.0.0.1:8000/alldiv?program_id=1').then(res => {
                this.divisions = res.data
            }

            );
        },

        getRegions() {
            axios.get('http://127.0.0.1:8000/allreg', {
                params: {
                    division_id: this.selectedDivision,
                },
            })
                .then((res) => {
                    this.regions = res.data;
                })
                .catch((error) => {
                    console.error('Error fetching regions:', error);
                });
        },

        async getAreas() {
            axios.get('http://127.0.0.1:8000/allarea', {
                params: {
                    region_id: this.selectedRegion,
                },
            })
                .then((res) => {
                    this.areas = res.data;
                })
                .catch((error) => {
                    console.error('Error fetching regions:', error);
                });
        },
        async getBranches() {
            axios.get('http://127.0.0.1:8000/allbra', {
                params: {
                    area_id: this.selectedArea,
                },
            })
                .then((res) => {
                    this.branches = res.data;
                })
                .catch((error) => {
                    console.error('Error fetching regions:', error);
                });
        },
        async getPOs() {

            axios.get('http://127.0.0.1:8000/allpo', {
                params: {
                    branchcode: this.selectedBranch,

                },

            })
                .then((res) => {
                    this.pos = res.data;
                })
                .catch((error) => {
                    console.error('Error fetching POs:', error);
                });
        },
        searchData() {
            const searchParams = {
                division: this.selectedDivision,
                region: this.selectedRegion,
                area: this.selectedArea,
                branch: this.selectedBranch,
                status: 1,
                po: this.selectedPO,
                dateFrom: this.dateFrom,
                dateTo: this.dateTo
            };
            axios.post('http://127.0.0.1:8000/search', searchParams)
                .then((response) => {
                    this.datas = response.data.searchDataResult;
                    this.totallCount = response.data.counts.original;
                    console.table(this.totallCount.allpendingloan);

                })
                .catch((error) => {
                    console.error('Error searching admissions:', error);
                });
                // this.getTottalCount()
        },



    addClasses() {
        document.querySelector('.datatable').classList.add('table', 'table-bordered', 'dataTable', 'no-footer', 'dtr-inline');
    },
    
},
    components: {
        DataTable,
    }
}

</script>
<style>
.datatable thead tr{
    background-color: #f3eded;
    color: #000;
}

        .user_info {
            margin-bottom: 50px;
        }

        .text_aling {
            text-align: center;
            background: #FB3199;
            color: white;
        }

        .view_btn {
            text-decoration: none;
            color: #fff;
            padding: 7px;
            background-color: #EE9D01;
            border-radius: 15%;
        }

        label {
            margin-left: 2px;
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

        .btn:hover {
            color: rgb(9, 8, 8) !important;
        }

        .roll_single_btn {
            border: groove;
            padding-top: 10px;
            transition: .5s;
            cursor: pointer;
            background-color: #7ed6df;
        }

        .roll_single_btn:hover {
            background-color: #FB3199;
        }

        .roll_btn h4 span {
            margin-left: 20px;
        }

        .active {
            color: #FB3199;
        }

        .card-title {
            text-align: center;

        }
</style>
