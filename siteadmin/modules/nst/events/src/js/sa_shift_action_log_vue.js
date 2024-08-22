window.addEventListener('load', function () {
    Vue.component('sa-shift-action-log', {
        template: `
            <div class="container-fluid sa-shift-action-log" id="pay-period-container">
                <div class="row">
                    <div class="col-12">
                        <v-app>                        
                            <div class="card">
                                <div class="card-header">
                                    <div class="row">                                    
                                        <div class="col-6">
                                            <h4 class="card-title">Shift Action Log</h4>
                                        </div>
                                        <div class="col-6 text-right">
                                            <v-spacer></v-spacer>
                                            <v-btn outlined @click="updateTables">
                                                REFRESH
                                            </v-btn>
                                            <v-btn outlined @click="toggleFilters" id="filter-toggle">
                                                FILTER                                            
                                            </v-btn>
                                        </div>
                                    </div>
                                    <div id="reports_filter" v-if="show_filters">
                                        <div class="row">
                                            <div class="col-2 pt-0">
                                                <v-menu
                                                        ref="menu_start_date"
                                                        v-model="menu_start_date"
                                                        :close-on-content-click="true"
                                                        :nudge-right="40"
                                                        transition="scale-transition"
                                                        max-width="290px"
                                                        min-width="290px"
                                                        attach
                                                        :offset-y="y"
                                                >
                                                    <template v-slot:activator="{ on, attrs }">
                                                        <v-text-field
                                                                autocomplete="off"
                                                                v-model="start_date"
                                                                :label="start_date_label"
                                                                prepend-icon="mdi-calendar"
                                                                readonly
                                                                v-bind="attrs"
                                                                v-on="on"
                                                                :rules="rules"
                                                                :disabled="statusDisabled"
                                                        ></v-text-field>
                                                    </template>
                                                    <v-date-picker
                                                            v-if="menu_start_date"
                                                            v-model="start_date"
                                                            no-title
                                                            scrollable
                                                            @change="updateDateRange();updateTables()"
                                                    >
                                                    </v-date-picker>
                                                </v-menu>
                                            </div>
                                            <div class="col-2 pt-0" v-if="use_end_date">
                                                <v-menu
                                                        ref="menu_end_date"
                                                        v-model="menu_end_date"
                                                        :close-on-content-click="true"
                                                        :nudge-right="40"
                                                        transition="scale-transition"
                                                        max-width="290px"
                                                        min-width="290px"
                                                        attach
                                                        :offset-y="y"
                                                        @change="updateDateRange();updateTables()"
                                                >
                                                    <template v-slot:activator="{ on, attrs }">
                                                        <v-text-field
                                                                autocomplete="off"
                                                                v-model="end_date"
                                                                :label="end_date_label"
                                                                prepend-icon="mdi-calendar"
                                                                readonly
                                                                v-bind="attrs"
                                                                v-on="on"
                                                                :rules="rules"
                                                                :disabled="endDateDisabled"
                                                                @change="updateTables()"
                                                        ></v-text-field>
                                                    </template>
                                                    <v-date-picker
                                                            v-if="menu_end_date"
                                                            v-model="end_date"
                                                            no-title
                                                            scrollable
                                                            @change="updateTables"
                                                            :min="start_date"
                                                    >
                                                        <!--       :min="todayDate"
                                                               :max="max_start_date"
                                                       >-->
                                                    </v-date-picker>
                                                </v-menu>
                                            </div>
                                            <div class="col-2 pb-0" id="provider-dropdown-col">
                                                <v-spacer></v-spacer>
                                                <v-autocomplete
                                                        v-model="provider_name"
                                                        prepend-icon="fa-building"
                                                        :items="providers"
                                                        item-text="name"
                                                        item-value="name"
                                                        label="Location - currently disabled"
                                                        clearable
                                                        :disabled="statusDisabled"
                                                        @change="updateTables"
                                                ></v-autocomplete>
                                            </div>
                                            <div class="col-2 pb-0 pt-0" id="nurse-dropdown-col">
                                                <v-spacer></v-spacer>
                                                <v-autocomplete
                                                        v-model="nurse_name"
                                                        prepend-icon="fa-user"
                                                        :items="nurses"
                                                        item-text="name"
                                                        item-value="name"
                                                        label="Nurse"
                                                        clearable
                                                        @change="updateTables"
                                                ></v-autocomplete>
                                            </div>
                                            <div class="col-2 pb-0 pt-0" id="credentials-dropdown-col">
                                                <v-spacer></v-spacer>
                                                <v-autocomplete
                                                        v-model="search_credential"
                                                        prepend-icon="fa-user"
                                                        :items="credentials_list"
                                                        item-text="credential"
                                                        item-value="credential"
                                                        label="Type"
                                                        clearable
                                                        @change="updateTables"
                                                ></v-autocomplete>
                                            </div>
                                            <div class="col-2" v-if="!use_end_date"></div>
                                            <div class="col-2 text-right">
                                                <span class="pr-3">Use End date</span>
                                                <v-checkbox
                                                        color="primary"
                                                        v-model="use_end_date"
                                                        @change="updateDateRange();updateTables()"
                                                ></v-checkbox>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive" id="vue">
                                    <v-app>
                                        <v-card>
                                            <v-card-title>
                                                <v-text-field
                                                    v-model="search"
                                                    append-icon="mdi-magnify"
                                                    label="Search"
                                                    :loading="isLoading"
                                                    single-line
                                                    hide-details
                                                ></v-text-field>
                                                <v-data-table
                                                    v-if="logs"
                                                    class="table table-responsive-md"
                                                    :headers="headers"
                                                    :items="logs"
                                                    :search="search"
                                                    :items-per-page="15"
                                                    multi-sort
                                                    :footer-props="{
                                                      showFirstLastPage: true,
                                                      firstIcon: 'mdi-arrow-collapse-left',
                                                      lastIcon: 'mdi-arrow-collapse-right',
                                                      prevIcon: 'mdi-minus',
                                                      nextIcon: 'mdi-plus'
                                                    }"
                                                >
                                                    <template v-slot:item.date="{ item }">
                                                        <span>{{item.date}}</span>
                                                    </template>
                                                    <template v-slot:item.context[action]="{ item }">
                                                      <v-chip
                                                        v-if="item.context"
                                                        :color="getColor(item.context['action'])"
                                                        dark
                                                        style="color:white!important;"
                                                      >
                                                        {{ item.context['action'] }}
                                                      </v-chip>
                                                    </template>
                                                </v-data-table>
                                            </v-card-title>
                                        </v-card>
                                    </v-app>
                                    </div>
                                </div>
                            </div>
                        </v-app>    
                    </div>
                </div>
            </div>`,
        computed: {
            filteredLogResults: function () {
                return this.logs.filter(function (log) {

                    if ((log.level === this.filter_level || this.filter_level === null)) {
                        return log
                    }

                }.bind(this))
            },
            statusDisabled: function () {
                return false;
            },
            endDateDisabled: function () {
                return !this.use_end_date;
            },
        },
        data: function() {
            return {
                errorMessage: null,
                filter_search: "",
                headers: [
                    {text: 'Date', value: 'date', width: '15%', align: 'left'},
                    {text: 'Action', value: 'context[action]', width: '15%', align: 'center'},
                    {text: 'Message', value: 'message', width: '70%', align: 'left'}
                ],
                isLoading: true,
                logs: [{"date": "", "logger": "", "level": "", "message": ""}],
                search: '',
                show_filters: false,
                menu_start_date: false,
                menu_end_date: false,
                pay_period: '',
                pay_periods: [],
                todayDate: new Date(-20).toLocaleString( 'sv', { timeZoneName: 'short' } ).slice(0, 10),
                max_start_date: new Date(+20).toLocaleString( 'sv', { timeZoneName: 'short' } ).slice(0, 10),
                y: true,
                use_end_date: false,
                start_date: new Date().toLocaleString( 'sv', { timeZoneName: 'short' } ).slice(0, 10),
                start_date_label: 'Date',
                end_date: new Date().toLocaleString( 'sv', { timeZoneName: 'short' } ).slice(0, 10),
                end_date_label: 'End Date',
                rules: [],
                providers: [],
                provider_name: null,
                nurses: [],
                nurse_name: null,
                disable_provider: true,
                credentials_list: [
                    {"credential": "CNA"},
                    {"credential": "CMT"},
                    {"credential": "LPN"},
                    {"credential": "RN"}
                ],
                search_credential: ""
            }
        },
        methods: {
            getLog: function () {
                this.isLoading = true;
                let data = {
                    "search": this.filter_search,
                    "start_date": this.start_date,
                    "end_date": this.end_date,
                    "provider_name": this.provider_name,
                    "nurse_name": this.nurse_name,
                    "credential": this.search_credential
                };
                modRequest.request('sa.shift.action_log', null, data,
                    function (response) {
                        this.logs = response.logs;
                        this.isLoading = false;
                        this.errorMessage = null;
                    }.bind(this),
                    function (error) {
                        let errorStack = JSON.parse(error.responseText);
                        this.errorMessage = errorStack.error.message;
                        this.isLoading = false;
                    }.bind(this));
            },
            getColor (action) {
                if (typeof action !== 'undefined') {
                    if (action === 'DELETED' || action === 'DECLINED') return 'red'
                    else if (action === 'DENIED' || action === 'CANCELLED') return 'orange'
                    else return 'green'
                }
            },
            toggleFilters() {
                this.show_filters = !this.show_filters;
            },
            updateTables() {
                this.getLog();
                // this.getNursePaymentsForReports();
            },
            getPayPeriods() {
                modRequest.request('sa.payroll.get_pay_periods', {}, {}, function (response) {
                    if (response.success) {
                        for (let i = 0; i < response.periods.length; i++) {
                            let period = response.periods[i];
                            this.pay_periods.push({
                                display: period.display,
                                combined: period.combined
                            })
                        }
                        for (let i = 0; i < this.pay_periods.length; i++) {
                            if (this.pay_periods[i].combined == this.period) {
                                this.pay_period = this.pay_periods[i].combined;
                            }
                        }
                    } else {
                        console.log('Error');
                        console.log(response);
                    }
                }.bind(this), function (response) {
                    console.log('Failed');
                    console.log(response);
                });
            },
            updateDateRange() {
                if (this.use_end_date) {
                    if (this.end_date < this.start_date) {
                        this.end_date = this.start_date;
                    }
                } else {
                    this.end_date = this.start_date;
                }
            },
            getProviders() {
                let data = {};
                modRequest.request('sa.member.load_providers', {}, {}, function (response) {
                    if (response.success) {
                        this.providers = [];
                        for (let i = 0; i < response.providers.length; i++) {
                            let provider = response.providers[i];
                            this.providers.push({
                                id: provider.id,
                                name: provider.company
                            })
                        }
                    } else {
                        console.log('Error');
                        console.log(response);
                    }
                }.bind(this), function (response) {
                    console.log('Failed');
                    console.log(response);
                });
            },
            getNurses() {
                modRequest.request('sa.member.load_nurses', {}, {}, function (response) {
                    if (response.success) {
                        let nurses = [];
                            for (var i = 0; i < response.nurses.length; i++) {
                                var nurse = response.nurses[i];

                                //check for nurse middle name to add to filter search
                                if(nurse.middle_name !== ''){  
                                    var n = {
                                        id: nurse.id,
                                        name: nurse.first_name + ' ' + nurse.middle_name + ' ' + nurse.last_name
                                    };
                                    nurses.push(n);
                            }else{
                                // no middle name
                                    var n = {
                                        id: nurse.id,
                                        name: nurse.first_name + ' ' + nurse.last_name
                                        };
                                    nurses.push(n);
                            }
                        }
                        this.nurses = nurses;
                    } else {
                        console.log('Error');
                        console.log(response);
                    }
                }.bind(this), function (response) {
                    console.log('Failed');
                    console.log(response);
                });
            }

        },
        created: function () {
            this.getNurses();
            this.getProviders();
            this.getLog();
        }
    });
});