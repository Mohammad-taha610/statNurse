window.addEventListener('load', function() {
    Vue.component('nurse-search-view', {
        template: `
            <div class="container-fluid" id="nurse-list">
                <v-app>
                    <div class="d-flex align-items-center flex-wrap search-job bg-white rounded py-3 px-md-3 px-0 mb-4">
                        <div class="col-lg-3 border-right">
                            <h4 class="black--text">Search</h4>
                        </div>
                        <div class="col-lg-9 d-md-flex">
                            <div class="d-md-flex">
                                <input class="form-control input-rounded mr-5 mb-md-0 mb-3" v-model="search" type="search" placeholder="Search By Name" name="name">
<!--                                <a href="javascript:void(0);" data-toggle="collapse" data-target="#filter" id="filter-toggle" class="bg-light btn btn-rounded primary&#45;&#45;text mr-3">-->
<!--                                    <i class="las la-filter scale5 mr-3"></i>-->
<!--                                    FILTER-->
<!--                                </a>-->
                                <button class="btn btn-primary btn-rounded" @click="updateFilters"><i class="las la-search scale5 mr-3"></i>SEARCH</button>
                            </div>
                        </div>
                    </div>
<!--                    <div id="filter" class="collapse" data-parent="#filter-toggle" >-->
<!--                        <div class="row">-->
<!--                            <div class="col-12 col-md-4">-->
<!--                                <div class="form-group">-->
<!--                                    <v-select-->
<!--                                        v-model="nurse_filters.worked_with"-->
<!--                                        :items="['All', 'Yes', 'No']"-->
<!--                                        label="Worked With"-->
<!--                                        hint="Worked With"-->
<!--                                        @change="updateFilters"-->
<!--                                        background-color="white"-->
<!--                                        solo-->
<!--                                        class="rounded"-->
<!--                                        flat-->
<!--                                        persistent-hint-->
<!--                                    ></v-select>-->
<!--                                </div>-->
<!--                            </div>-->
<!--                            <div class="col-12 col-md-4">-->
<!--                                <div class="form-group">-->
<!--                                    <v-select-->
<!--                                        v-model="nurse_filters.unresolved_pay"-->
<!--                                        :items="['All', 'Yes', 'No']"-->
<!--                                        label="Unresolved Pay"-->
<!--                                        hint="Unresolved Pay"-->
<!--                                        @change="updateFilters"-->
<!--                                        background-color="white"-->
<!--                                        class="rounded"-->
<!--                                        solo-->
<!--                                        flat-->
<!--                                        persistent-hint-->
<!--                                    ></v-select>-->
<!--                                </div>-->
<!--                            </div>-->
<!--                            <div class="col-12 col-md-4">-->
<!--                                <div class="form-group">-->
<!--                                    <v-select-->
<!--                                        v-model="nurse_filters.blocked"-->
<!--                                        :items="['All', 'Yes', 'No']"-->
<!--                                        label="On DO NOT RETURN List"-->
<!--                                        hint="On DO NOT RETURN List"-->
<!--                                        @change="updateFilters"-->
<!--                                        background-color="white"-->
<!--                                        class="rounded"-->
<!--                                        solo-->
<!--                                        flat-->
<!--                                        persistent-hint-->
<!--                                    ></v-select>-->
<!--                                </div>-->
<!--                            </div>-->
<!--                        </div>-->
<!--                    </div>-->
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title">Nurses</h4>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <v-card>
                                            <v-card-title>
                                                <v-data-table
                                                    class="table table-responsive-md"
                                                    :headers="headers"
                                                    :items="nurses"
                                                    :search="search"
                                                    :custom-filter="nurseListFilter"
                                                    multi-sort
                                                >
                                                    <template v-slot:item.first_name="{ item }">
                                                        <a class="blue--text" :href="item.profile">{{ item.first_name }}</a>
                                                    </template>
                                                    <template v-slot:item.last_name="{ item }">
                                                        <a class="blue--text" :href="item.profile">{{ item.last_name }}</a>
                                                    </template>
                                                    <template v-slot:item.credentials="{ item }">
                                                        {{item.credentials}}
                                                    </template>
                                                </v-data-table>
                                            </v-card-title>
                                        </v-card>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </v-app>
            </div>`,
        props: [
            'provider_id', 'search_term'
        ],
        data () {
            return {
                headers: [
                    {
                        text: 'First Name',
                        align: 'start',
                        sortable: true,
                        value: 'first_name'
                    },
                    {
                        text: 'Last Name',
                        sortable: true,
                        value: 'last_name'
                    },
                    {
                        text: 'Credentials',
                        sortable: true,
                        value: 'credentials'
                    }
                ],
                nurses: [],
                search: '',
                nurse_filters: {
                    worked_with: 'All',
                    unresolved_pay: 'All',
                    blocked: 'All'
                }
            }
        }, 
        mounted: function () {
            this.loadNurses();
            this.search = this.search_term;
        },
        methods: {
            updateFilters() {
                this.loadNurses();
            },
            nurseListFilter(value, search, item) {
                let items = search.split(' ');

                let filterResult = false;
                for(let i = 0; i < items.length; i++) {
                    if(!filterResult) {
                        filterResult =
                            item.first_name != null &&
                            item.last_name != null &&
                            (
                                item.first_name.toString().toLowerCase().indexOf(items[i].toLowerCase()) !== -1 ||
                                item.last_name.toString().toLowerCase().indexOf(items[i].toLowerCase()) !== -1
                            );
                    }
                }

                return filterResult;
            },
            loadNurses: function () {
                let data = {
                    provider_id: this.provider_id,
                    filters: this.nurse_filters
                }
                modRequest.request('nurses.list', {}, data, function(response) {
                    if(response.success) {
                        this.nurses = [];
                        for (let i = 0; i < response.nurses.length; i++) {
                            let nurse = response.nurses[i];
                            this.nurses.push({
                                first_name: nurse.first_name,
                                last_name: nurse.last_name,
                                profile: nurse.profile,
                                credentials: nurse.credentials
                            });
                        }
                        console.log(this.nurses);
                    } else {
                        console.log('Error');
                        console.log(response);
                    }
                }.bind(this), function(response) {
                    console.log('Failed');
                    console.log(response);
                });
            }
        },
    });
});