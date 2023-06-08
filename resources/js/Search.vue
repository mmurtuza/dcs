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
                                <option v-for="po in pos" :value="po.cono">{{ po.cono}}-{{ po.coname }}</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">

                        <!-- <label for="dateFrom" class="form-label">Date Range</label> -->
                        <div class="col-md-2 mb-3">
                            <label class="ml-2" for="dateFrom"></label>

                            <input type="date" v-model="dateFrom" class="form-control" id="dateFrom">
                        </div>
                        <div class="col-md-2 mb-3">
                            <label class="ml-2" for="dateTo"></label>
                            <input type="date" v-model="dateTo" class="form-control" id="dateTo">
                        </div>
                        <div class="col-md-2 mb-3 mt-8">
                            <button @click="searchAdmission" class="btn btn-secondary">Search</button>
                        </div>
                    </div>
                </div>
            </div>
    </div>
</div>
</template>

<script>
import axios from 'axios';
export default {
    name: 'divisions',
    data() {
        return {
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
            totallCount:[],
            dateFrom: null,
            dateTo: null,
        };
    },
     mounted() {

        this.getDivisions()
        //this.getRegions()
        //this.getApproveLoan()

        const currentDate = new Date().toISOString().slice(0, 10);
        this.dateTo = currentDate;

        const dateString = "2023-01-01";
        const dateObject = new Date(dateString);
        this.dateFrom = dateObject.toISOString().slice(0, 10);


    },
    methods: {
         getDivisions() {
            axios.get('http://127.0.0.1:8000/alldiv?program_id=1').then(res => {
                this.divisions = res.data
                //console.log(this.divisions)
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
                    console.error('Error fetching regions:', error); //allarea
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
                   console.log(res.data)
                })
                .catch((error) => {
                    console.error('Error fetching POs:', error);
                });
                 console.log(this.selectedBranch)

        },


        searchAdmission() {
            const searchParams = {
                division: this.selectedDivision,
                region: this.selectedRegion,
                area: this.selectedArea,
                branch: this.selectedBranch,
                po: this.selectedPO,
                dateFrom: this.dateFrom,
                dateTo: this.dateTo
            };
             axios.post('http://127.0.0.1:8000/search', searchParams)
                .then((response) => {
                    // Handle the response from the backend
                   // console.log(response.data);
                    data= response.data;
                    // console.log(data);
                    // Process the search results
                })
                .catch((error) => {
                    console.error('Error searching admissions:', error);
                    // Handle the error
                });
        }
    }
};

</script>
