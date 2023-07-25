<template>
     <div class="container">
            <div class="card card-custom">
                <div class="card-body">
                    <div class="form_value">
                        <div class="form-row">
                            <div class="col-md-2 mb-3">
                                <label class="ml-2" for="division">Division</label>
                                <h5 v-if="['AM', 'RM', 'DM'].includes(this.role_designation)">{{ this.branch.division_id}} - {{ this.branch.division_name }}</h5>
                                <select v-else v-model="selectedDivision" @change="getRegions" class="form-control" id="division">
                                    <option value="">Select</option>
                                    <option v-for="division in divisions" :key="division.division_id" :value="division.division_id">{{ division.division_id }}-{{ division.division_name }}</option>
                                </select>
                            </div>
                            <div class="col-md-2 mb-3">
                                <label class="ml-2" for="region">Region</label>
                                <h5 v-if="['AM', 'RM'].includes(this.role_designation)">{{  this.branch.region_id }} - {{ this.branch.region_name}}</h5>
                                <select v-else v-model="selectedRegion" @change="getAreas" class="form-control" id="region">
                                    <option value="">Select</option>
                                    <option v-for="region in regions" :key="region.region_id" :value="region.region_id">{{ region.region_id }}-{{region.region_name }}</option>
                                </select>
                            </div>
                            <div class="col-md-2 mb-3">
                                <label class="ml-2" for="area">Area</label>
                                <h5 v-if="this.role_designation ==='AM'">{{ this.branch.area_id }}-{{ this.branch.area_name }}</h5>
                                <select v-else v-model="selectedArea" @change="getBranches" class="form-control" id="area">
                                    <option value="">Select</option>
                                    <option v-for="area in areas" :key="area.area_id" :value="area.area_id">{{ area.area_id }}-{{ area.area_name }}
                                    </option>
                                </select>
                            </div>
                            <div class="col-md-2 mb-3">
                                <label class="ml-2" for="branch">Branch</label>
                                <select v-model="selectedBranch" @change="getPOs" id="branch" class="form-control">
                                    <option value="">Select</option>
                                    <option v-for="branch in branches" :key="branch.branch_id" :value="branch.branch_id">{{ branch.branch_id }}-{{
                                        branch.branch_name }}</option>
                                </select>
                            </div>
                            <div class="col-md-2 mb-3">
                                <label class="ml-2" for="po">PO</label>
                                <select v-model="selectedPO" class="form-control" id="po">
                                    <option value="">Select</option>
                                    <option v-for="po in pos" :key="po.cono" :value="po.cono">{{ po.cono }}-{{ po.coname }}</option>
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
                        Total Disbursed Amount
                    </h5>
                    <h5 class="text-center" style="margin-top:7px;" id="total_disbuse">{{ this.totallCount.disbursment_amnt }}</h5>
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
            <button type="button" id="pending"
            @click="()=>{getDatas(null, 1, 'pending'); this.erpStatus=null; this.roleStatus='1'; getPendingCount() }"
            :class="{ 'btn btn-block btn-secondary': activeButton !== 'pending', 'btn btn-block btn-secondary active': activeButton === 'pending' }"
            >
                Pending(<span class="pending_data">{{ this.totallCount.allpendingloan }}</span>)
            </button>
        </div>


        <div class=" mb-3">
            <button type="button" id="approved" @click="()=>{getDatas(null, 2, 'approved'); this.erpStatus= null; this.roleStatus='2'; getApproveCount() }"
       :class="{ 'btn btn-block btn-secondary': activeButton !== 'approved', 'btn btn-block btn-secondary active': activeButton === 'approved' }">
      Approved(<span id="approved_data">{{ this.totallCount.allapproveloan }}</span>)
    </button>
        </div>

        <div class="mb-3">
       <button type="button" id="disbursement" @click="()=>{getDatas(1, null, 'disbursement'); this.erpStatus= '1'; this.roleStatus=null; getDisbursementCount() }"
        :class="{ 'btn btn-block btn-secondary': activeButton !== 'disbursement', 'btn btn-block btn-secondary active': activeButton === 'disbursement' }">
      Ready for Disbursement(<span id="approved_data">{{ this.totallCount.all_disbursement }}</span>)
    </button>
    </div>

        <div class=" mb-3">
           <button type="button" id="disburse" @click="()=>{getDatas(4, null, 'disburse'); this.erpStatus = '4'; this.roleStatus= null; getDisburseCount() }"
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

        <DataTable
            ref="table"
            :columns="columns"
            :data="this.datas"
            :options="this.options"
            class="display table table-bordered no-footer dtr-inline dataTable"
            width="100%"
        />
    </div>
    <Overlay></Overlay>

</template>

<script>

import axios from 'axios';
import DataTable from 'datatables.net-vue3';
import DataTablesCore from 'datatables.net-bs5';
import 'datatables.net-responsive';
import 'datatables.net-select';
import 'moment';
import Overlay from './Overlay.vue';

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
            selectedDivision: '',
            selectedRegion: '',
            selectedArea: '',
            selectedBranch: '',
            selectedPO: '',
            dateFrom: '',
            dateTo: '',
            role_designation: 'AM',
            divisions: [],
            regions: [],
            areas: [],
            branches: [],
            pos: [],
            totallCount: [],
            branch: [],
            data: [],
            dateFrom: null,
            dateTo: null,
            options: {
                responsive: true,
                processing: true,
                ordering: false,
                searching: false,
                bLengthChange: false,
                select: false,
                createdRow: (row, data, dataIndex) => {
                    $(row).attr('role', 'row');
                }
            },
            columns: [
                {
                    data: 'id',
                    title: 'Details',
                    render: (data) => {
                        return `<a class="btn btn-warning" href='./operation/loan-approval/${data}')">View</a>`;
                    }
                },
                { data: 'orgno', title: 'Vo Code' },
                { data: 'orgmemno', title: 'Member Number' },
                { data: 'propos_amt', title: 'Disb. Amt' },
                { data: 'productname', title: 'Product Name' },
                { data: 'loan_type', title: 'Loan Type' },
                { data: 'branchcode', title: 'Branch Code' },
                { data: 'coname', title: 'Applied By' },
                {
                    data: 'time',
                    title: 'Application Date',
                    render: (data)=>{ return moment(data).format("DD MM YYYY hh:mm:ss")}
                },

            ],
        }
    },

    watch: {
        selectedDivision: 'getRegions',
        selectedRegion: 'getAreas',
        selectedArea: 'getBranches'
    },

    mounted() {
        this.getDatas(null, 1, 'pending'),
        this.getPendingCount(),
        this.getDivisions()

        const currentDate = new Date().toISOString().slice(0, 10);
        this.dateTo = currentDate;
        const dateString = (new Date()).getFullYear() +"-01-01";
        const dateObject = new Date(dateString);
        this.dateFrom = dateObject.toISOString().slice(0, 10);
    },

    methods: {

        getDatas(ErpStatus,status, activeButton) {
            const params = {
                ErpStatus: ErpStatus,
                status: status,
                division: this.selectedDivision,
                region: this.selectedRegion,
                area: this.selectedArea,
                branch: this.selectedBranch,
                po: this.selectedPO,
                dateFrom: this.dateFrom,
                dateTo: this.dateTo
            };

            $("#overlay").fadeIn(300);
            axios.post(`${import.meta.env.VITE_API_URL}/fetchdata`, params)
                .then(res => {
                    this.role_designation = res.data['counts']["role_designation"];
                    this.datas = res.data['data']['data'];
                    this.totallCount = res.data['counts'];
                    this.length = res.data.length;
                    this.activeButton = activeButton;
                    this.branch = res.data['branch'];
                    if(['AM', 'RM', 'DM'].includes(this.role_designation))  this.selectedDivision = this.branch.division_id;
                    if(['AM', 'RM'].includes(this.role_designation)) this.selectedRegion = this.branch.region_id;
                    if(this.role_designation === 'AM')  (this.selectedArea = this.branch.area_id);
                })
                .catch((error)=>{
                    console.error('Error fetching data:', error);
                })
                .finally(() => {
                    $("#overlay").fadeOut(300);
                });
        },

        getTottalCount() {
            axios.get(`${import.meta.env.VITE_API_URL}/allcount`).then(res => {
                this.totallCount = res.data;
            });
        },
        getRoleWiseData(data) {
            const params = {
                activeButton: this.activeButton,
                reciverrole: data,
                erpStatus: this.erpStatus,
                roleStatus: this.roleStatus,
                division: this.selectedDivision,
                region: this.selectedRegion,
                area: this.selectedArea,
                branch: this.selectedBranch,
                po: this.selectedPO,
                dateFrom: this.dateFrom,
                dateTo: this.dateTo
            }
            //console.table(params);
            $("#overlay").fadeIn(300);
            axios.post(`${import.meta.env.VITE_API_URL}/roledata`, params).then(res => {
                this.datas = res.data.data;
                $("#overlay").fadeOut(300);
            })
                .catch((error)=>{
                    $("#overlay").fadeOut(300);
                    console.error('Error fetching regions:', error);
                });
        },
        getPendingCount() {
            const params = {
                activeButton: this.activeButton,
                // reciverrole: data,
                erpStatus: this.erpStatus,
                roleStatus: this.roleStatus,
                division: this.selectedDivision,
                region: this.selectedRegion,
                area: this.selectedArea,
                branch: this.selectedBranch,
                po: this.selectedPO,
                dateFrom: this.dateFrom,
                dateTo: this.dateTo
            }
            axios.post(`${import.meta.env.VITE_API_URL}/rollcounts`, params).then(res => {
                this.amcount = res.data.am_pending_loan;
                this.bmcount = res.data.bm_pending_loan;
                this.rmcount = res.data.rm_pending_loan;
                this.dmcount = res.data.dm_pending_loan;
            }
            );
        },
         getApproveCount() {
             const params = {
                activeButton: this.activeButton,
                // reciverrole: data,
                erpStatus: this.erpStatus,
                roleStatus: this.roleStatus,
                division: this.selectedDivision,
                region: this.selectedRegion,
                area: this.selectedArea,
                branch: this.selectedBranch,
                po: this.selectedPO,
                dateFrom: this.dateFrom,
                dateTo: this.dateTo
            }
            axios.post(`${import.meta.env.VITE_API_URL}/rollcounts`, params).then(res => {
                this.amcount = res.data.am_pending_loan;
                this.bmcount = res.data.bm_pending_loan;
                this.rmcount = res.data.rm_pending_loan;
                this.dmcount = res.data.dm_pending_loan;

            }

            );
        },
         getDisbursementCount() {
             const params = {
                activeButton: this.activeButton,
                // reciverrole: data,
                erpStatus: this.erpStatus,
                roleStatus: this.roleStatus,
                division: this.selectedDivision,
                region: this.selectedRegion,
                area: this.selectedArea,
                branch: this.selectedBranch,
                po: this.selectedPO,
                dateFrom: this.dateFrom,
                dateTo: this.dateTo
            }
            axios.post(`${import.meta.env.VITE_API_URL}/rollcounts`, params).then(res => {
                this.amcount = res.data.am_pending_loan;
                this.bmcount = res.data.bm_pending_loan;
                this.rmcount = res.data.rm_pending_loan;
                this.dmcount = res.data.dm_pending_loan;
            }

            );
        },
         getDisburseCount() {
             const params = {
                activeButton: this.activeButton,
                // reciverrole: data,
                erpStatus: this.erpStatus,
                roleStatus: this.roleStatus,
                division: this.selectedDivision,
                region: this.selectedRegion,
                area: this.selectedArea,
                branch: this.selectedBranch,
                po: this.selectedPO,
                dateFrom: this.dateFrom,
                dateTo: this.dateTo
            }
            axios.post(`${import.meta.env.VITE_API_URL}/rollcounts`, params).then(res => {
                this.amcount = res.data.am_pending_loan;
                this.bmcount = res.data.bm_pending_loan;
                this.rmcount = res.data.rm_pending_loan;
                this.dmcount = res.data.dm_pending_loan;
            }

            );
        },
         getRejectedCount() {
             const params = {
                activeButton: this.activeButton,
                // reciverrole: data,
                erpStatus: this.erpStatus,
                roleStatus: this.roleStatus,
                division: this.selectedDivision,
                region: this.selectedRegion,
                area: this.selectedArea,
                branch: this.selectedBranch,
                po: this.selectedPO,
                dateFrom: this.dateFrom,
                dateTo: this.dateTo
            }
            axios.post(`${import.meta.env.VITE_API_URL}/rollcounts`, params).then(res => {
                this.amcount = res.data.am_pending_loan;
                this.bmcount = res.data.bm_pending_loan;
                this.rmcount = res.data.rm_pending_loan;
                this.dmcount = res.data.dm_pending_loan;
                // console.table(res.data)
            }

            );
        },
        getDivisions() {
            $("#po option:not(:first)").remove();
            $("#branch option:not(:first)").remove();
            $("#area option:not(:first)").remove();
            $("#division").prepend(`<option class="spinner-border text-primary" role="status" selected><span class="sr-only">Loading...</span></option>`)
            axios.get(`${import.meta.env.VITE_API_URL}/alldiv?program_id=1`).then(res => {
                this.divisions = res.data
            });
            $("#division").find(':first-child').remove();
        },

        getRegions() {
            $("#po option:not(:first)").remove();
            $("#branch option:not(:first)").remove();
            $("#region").prepend(`<option class="spinner-border text-primary" role="status" selected><span class="sr-only">Loading...</span></option>`)

            axios.get(`${import.meta.env.VITE_API_URL}/allreg`, {
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
            $("#region").find(':first-child').remove();
        },

        async getAreas() {
            $("#po option:not(:first)").remove();
            $("#branch option:not(:first)").remove();
            $("#area").prepend(`<option class="spinner-border text-primary" role="status" selected><span class="sr-only">Loading...</span></option>`)
            axios.get(`${import.meta.env.VITE_API_URL}/allarea`, {
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
            $("#area").find(':first-child').remove();
        },
        async getBranches() {
            $("#po option:not(:first)").remove();
            $("#branch").prepend(`<option class="spinner-border text-primary" role="status" selected><span class="sr-only">Loading...</span></option>`)

            axios.get(`${import.meta.env.VITE_API_URL}/allbra`, {
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
            $("#branch").find(':first-child').remove();
        },
        async getPOs() {
            $("#po").prepend(`<option class="spinner-border text-primary" role="status" selected><span class="sr-only">Loading...</span></option>`)

            axios.get(`${import.meta.env.VITE_API_URL}/allpo`, {
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
            $("#po").find(':first-child').remove();
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
            $("#overlay").fadeIn(300);
            axios.post(`${import.meta.env.VITE_API_URL}/search`, searchParams)
                .then((response) => {
                    $("#overlay").fadeOut(300);
                    this.datas = response.data.searchDataResult.original;
                    this.totallCount = response.data.counts;
                    this.getPendingCount();
                })
                .catch((error) => {
                    $("#overlay").fadeOut(300);
                    console.error('Error searching admissions:', error);
                });
        },

    },

    components: {
        DataTable, // Add the DataTable component to the components option
        Overlay,
    },
}

</script>
<style lang="scss">
@import 'datatables.net-bs5';
.datatable:not(.table){
    display:block !important;
}

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
