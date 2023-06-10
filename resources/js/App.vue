<template>
    <Search />
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
           <button type="button" id="pending" @click="getDatas(null, 1, 'pending')"
        :class="{ 'btn btn-block btn-secondary': activeButton !== 'pending', 'btn btn-block btn-secondary active': activeButton === 'pending' }">
      Pending(<span class="pending_data">{{ this.totallCount.allpendingloan }}</span>)
    </button>
        </div>


        <div class=" mb-3">
            <button type="button" id="approved" @click="getDatas(1, 2, 'approved')"
       :class="{ 'btn btn-block btn-secondary': activeButton !== 'approved', 'btn btn-block btn-secondary active': activeButton === 'approved' }">
      Approved(<span id="approved_data">{{ this.totallCount.allapproveloan }}</span>)
    </button>
        </div>

        <div class="mb-3">
       <button type="button" id="disbursement" @click="getDatas(2, 2, 'disbursement')"
        :class="{ 'btn btn-block btn-secondary': activeButton !== 'disbursement', 'btn btn-block btn-secondary active': activeButton === 'disbursement' }">
      Ready for Disbursement(<span id="approved_data">{{ this.totallCount.all_disbursement }}</span>)
    </button>
    </div>

        <div class=" mb-3">
           <button type="button" id="disburse" @click="getDatas(4, 2, 'disburse')"
      :class="{ 'btn btn-block btn-secondary': activeButton !== 'disburse', 'btn btn-block btn-secondary active': activeButton === 'disburse' }">
      Disburse(<span id="disburse_data">{{ this.totallCount.alldisburseloan }}</span>)
    </button>

        </div>

         <div class=" mb-3">
                <button type="button" id="rejected" @click="getDatas(3, 3, 'rejected')"
      :class="{ 'btn btn-block btn-secondary': activeButton !== 'rejected', 'btn btn-block btn-secondary active': activeButton === 'rejected' }">
      Rejected(<span id="rejected_data">{{ this.totallCount.allrejectloan }}</span>)
    </button>
        </div>
 </div>

    <div class="col-md-9">
        <div class="roll_btn">
            <div class="row">
                <div class="col-md-3">
                    <div class="row">
                        <div class="col-md-4"></div>
                        <div class="col-md-8 roll_single_btn">
                            <h4 class="roll_class" id="roll_bm" @click="getBmData()">BM<span id="btn_bm">{{ bmCount }}</span>
                            </h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="row">
                        <div class="col-md-4">

                        </div>
                        <div class="col-md-8 roll_single_btn">
                            <h4 class="roll_class" id="roll_am" @click="getAmData()">AM<span id="btn_am">{{ amCount }}</span>
                            </h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="row">
                        <div class="col-md-4"></div>
                        <div class="col-md-8 roll_single_btn">
                            <h4 class="roll_class" id="roll_rm" @click="getRmData()">RM<span id="btn_rm">{{ rmCount }}</span>
                            </h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="row">
                        <div class="col-md-4"></div>
                        <div class="col-md-8 roll_single_btn">
                            <h4 class="roll_class" id="roll_dm" @click="getDmData()">DM<span id="btn_dm">{{ dmCount }}</span>
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
import './Search.vue'
import Search from './Search.vue';
DataTable.use(DataTablesCore);


export default {
    name: 'datas',

    data() {
        return {
            activeButton: 'pending',
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
            this.getBmData(),
            this.getAmData(),
            this.getRmData(),
            this.getDmData(),
            this.getTottalCount()

    },
    methods: {

        getDatas(ErpStatus,status, activeButton) {
            const params = {
                ErpStatus: ErpStatus,
                status: status

            };
            axios.post('http://127.0.0.1:8000/allpendingloan', params)
                .then(res => {
                    this.datas = res.data;
                    this.length = res.data.length;
                    this.activeButton = activeButton;
                    this.initializeDataTable();
                });
        },

        getTottalCount() {
            axios.get('http://127.0.0.1:8000/allcount').then(res => {
                this.totallCount = res.data
                this.bmCount = this.totallCount.bmpendingloan.length;
                this.amCount = this.totallCount.ampendingloan.length;
                this.rmCount = this.totallCount.rmpendingloan.length;
                this.dmCount = this.totallCount.dmpendingloan.length;
            }

        );
    },
    getBmData(){
        axios.get('http://127.0.0.1:8000/allcount').then(res => {
            this.datas = res.data.bmpendingloan;
            this.initializeDataTable();
        });
    },
    getAmData() {
            axios.get('http://127.0.0.1:8000/allcount').then(res => {
                this.datas = res.data.ampendingloan;
                this.initializeDataTable();
            });
        },
     getRmData() {
            axios.get('http://127.0.0.1:8000/allcount').then(res => {
                this.datas = res.data.rmpendingloan;
                this.initializeDataTable();
            });
        },
    getDmData() {
            axios.get('http://127.0.0.1:8000/allcount').then(res => {
                this.datas = res.data.dmpendingloan;
                this.initializeDataTable();
            });
        },
    initializeDataTable() {
        $(document).ready(function () {
             $('#myTable').DataTable();
        });
    },


    addClasses() {
        document.querySelector('.datatable').classList.add('table', 'table-bordered', 'dataTable', 'no-footer', 'dtr-inline');
    },


},
    components: {
        DataTable,
        Search
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
