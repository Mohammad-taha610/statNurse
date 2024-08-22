window.addEventListener('load', function () {
    Vue.component('sa-reports-earnings-view', {
        // language=HTML
        template: `
        <v-app>
            <div>
                <v-row>
                    <v-col
                        cols="9">
                        Report flow:
                            <ul>
                                <li>Please select a Start Date and an End Date, then click "Search All" to see all nurse earnings between those two dates.</li>
                                <li>Then, click the "Total Pay" table header if you wish to sort by total pay earned.</li>
                                <li>Additionally, you may select a State and search again to view all nurse earnings between those two dates for the state you choose.</li>
                                <!-- <li>If you'd like to take an action on an entry, simply click the dropdown arrow on the right side of that entry and select an action.</li> -->
                            </ul>
                    </v-col>
                    <v-col 
                    class="text-right d-flex"
                    cols="6">
                        <label for="startDate">Start Date:</label>
                        <input type="date" id="startDate" v-model="startDate" />

                        <label for="endDate">End Date:</label>
                        <input type="date" id="endDate" v-model="endDate" @change="" />
                        
                        <v-select
                        class="px-2"
                        :items="states"
                        v-model="state"
                        label="State">
                        </v-select>
                        <v-card-actions v-if="state">
                            <v-btn
                                v-if="state == 'All'"
                                @click="getEarningNurses"
                                :disabled="retrievingNurses">
                                Search All
                            </v-btn>
                            <v-btn
                                v-else
                                @click="getStateNurses"
                                :disabled="retrievingNurses">
                                Search in {{state}}
                            </v-btn>
                        </v-card-actions>

                    </v-col>
                </v-row>
            </div>
                <v-card v-if="state == 'All'">
                <v-card-text>
                <v-data-table 
                        v-model:expanded="expanded"
                        :headers="nurseEarningsTableHeaders"
                        :items="earningNurses"
                        item-key="id"
                        no-data-text="No data found."
                        :expanded.sync="expanded"
                        show-expand>
                        <template v-slot:expanded-item="{ headers, item }">
                            <td :colspan="headers.length">
                                <div class="d-flex" v-if="item.actions"> <!-- keeping for future item specific actions if needed -->
                                    <span >Issues:</span>
                                    <ul>
                                        <li v-for="(action, key) in item.actions"><span class="red--text">{{ action }}</span></li>
                                    </ul>
                                </div>
                                <span v-else class="green--text">No actions available.</span>
                            </td>
                        </template>
                </v-data-table>
        </v-card-text>
        <v-card-actions>
                            <v-btn
                                v-if="state == 'All'"
                                @click="getEarningNurses"
                                :disabled="retrievingNurses">
                                Refresh All
                            </v-btn>
                        </v-card-actions>
        <v-overlay
            class="text-center"
            v-if="retrievingNurses"
            opacity="0.75"
            absolute>
            <v-progress-circular
                class="mr-2"
                indeterminate
                size="64">
            </v-progress-circular>
            <div 
                class="pa-4"> 
                Retrieving nurse information...
            </div>
        </v-overlay>
        <v-snackbar
            dark
            :color="snackbar.color"
            v-model="snackbar.status"
            :timeout="snackbar.timeout">
            {{ snackbar.message }}

            <template v-slot:action="{ attrs }">
                <v-btn
                color="white"
                text
                v-bind="attrs"
                @click="snackbar.status = false"
                >
                Close
                </v-btn>
            </template>
        </v-snackbar>
    </v-card>

                <v-card v-if="state != 'All'">
                <v-card-text>
                <v-data-table 
                        v-model:expanded="expanded"
                        :headers="nurseEarningsTableHeaders"
                        :items="stateEarningNurses"
                        item-key="id"
                        no-data-text="No data found."
                        :expanded.sync="expanded"
                        show-expand>
                        <template v-slot:expanded-item="{ headers, item }">
                            <td :colspan="headers.length">
                                <div class="d-flex" v-if="item.actions">  <!-- keeping for future item specific actions if needed -->
                                    <span >Issues:</span>
                                    <ul>
                                        <li v-for="(action, key) in item.actions"><span class="red--text">{{ action }}</span></li>
                                    </ul>
                                </div>
                                <span v-else class="green--text">No actions available.</span>
                            </td>
                        </template>
                        <template v-slot:item.status="{ item }">
                            <span :class="{'red--text': item.status == 'Incomplete', 'green--text': item.status == 'Ready' }">{{ item.status }}</span>
                        </template>
                        <template v-slot:item.nursingLicenseFile="{ item }">
                            <span v-if="item.nursingLicenseFile">
                                <v-btn
                                    class="mr-2"
                                    fab
                                    dark
                                    small
                                    color="primary"
                                    @click="openFile(item.shiftsReportFileUrl)">
                                    <v-icon dark>mdi-file</v-icon>
                                </v-btn>
                                {{ item.nursingLicenseFile }}
                            </span>
                        </template>
                        <template v-slot:item.shiftsReportFile="{ item }">
                            <span v-if="item.shiftsReportFile">
                                <v-btn
                                    class="mr-2"
                                    fab
                                    dark
                                    small
                                    color="primary"
                                    @click="openFile(item.shiftsReportFileUrl)">
                                    <v-icon dark>mdi-file</v-icon>
                                </v-btn>
                                {{ item.shiftsReportFile }}
                            </span>
                        </template>
                </v-data-table>
        </v-card-text>
        <v-card-actions v-if="state">
                            <v-btn
                                @click="getStateNurses"
                                :disabled="retrievingNurses">
                                Refresh in {{state}}
                            </v-btn>
                        </v-card-actions>
        <v-overlay
        class="text-center"
        v-if="retrievingProviders"
        opacity="0.75"
        absolute>
        <v-progress-circular
            class="mr-2"
            indeterminate
            size="64">
        </v-progress-circular>
        <div 
            class="pa-4"> 
            Retrieving state nurse information...
        </div>
    </v-overlay>
<v-snackbar
            dark
            :color="snackbar.color"
            v-model="snackbar.status"
            :timeout="snackbar.timeout">
            {{ snackbar.message }}

            <template v-slot:action="{ attrs }">
                <v-btn
                color="white"
                text
                v-bind="attrs"
                @click="snackbar.status = false"
                >
                Close
                </v-btn>
            </template>
        </v-snackbar>
            </v-card>


        </v-app>`,
        props: [
        ],
        data() {
            return {
                nurseEarningsTableHeaders: [
                    { text: 'First Name', align: 'start', sortable: true, value: 'first_name' },
                    { text: 'Last Name ', align: 'start', sortable: true, value: 'last_name' },
                    { text: 'Credentials', align: 'start', sortable: true, value: 'credentials' },
                    { text: 'City', align: 'start', sortable: true, value: 'city' },
                    { text: 'State', align: 'start', sortable: true, value: 'state_abbreviation' },
                    { text: 'Total Pay', align: 'start', sortable: true, value: 'totalPay' },
                    { text: '', value: 'data-table-expand' },
                ],
                nurseReports: [],
                expanded: [],
                snackbar: {
                    status: false,
                    message: [],
                    color: 'success',
                    timeout: 4000
                },
                retrievingNurses: true,
                retrievingProviders: true,
                states: ["All",
                    "KY",
                    "OH",
                    "IN"],
                state: "All",
                formattedDate: null,
                formattedToday: null,
                startDate: moment().subtract(3, 'months').format('YYYY-MM-DD'),
                today: new Date(),
                endDate: moment(this.today).format('YYYY-MM-DD')
            }
        },
        computed: {
            isReady() {
                return this.nurseReports.some(item => item.status === 'Incomplete');
            },

        },
        created: function () {
        },
        mounted: function () {
            this.setupDataTable();
        },
        methods: {
            setupDataTable() {
                this.getEarningNurses();
                this.getStateNurses();
            },
            expandRow(item) {
                // TODO: return if there is no data to expand
                this.expanded = item === this.expanded[0] ? [] : [item]
            },
            getEarningNurses() {
                this.retrievingNurses = true;
                this.loadingEarningNurseData = true;
                let data = {
                    start: this.startDate,
                    end: this.endDate
                }
                modRequest.request('sa.payroll.get_earnings_report', {}, data, response => {
                    if (response.success) {
                        this.triggerSnackbar('Nurses retrieved successfully!', '#4CAF50', timeout = 1000);
                        this.earningNurses = null;
                        if (response.returnArray) {
                            // Apply rounding to the 'totalPay' values
                            this.earningNurses = response.returnArray.map(nurse => {
                                return {
                                    ...nurse,
                                    totalPay: parseFloat(nurse.totalPay).toFixed(2)
                                };
                            });
                        }
                        this.retrievingNurses = false;
                    } else {
                        this.triggerSnackbar('Nurses retrieval failed!', '#F44336', timeout = 4000);
                    }
                }, response => {
                    this.triggerSnackbar('Nurses retrieval failed!', '#F44336', timeout = 4000);
                });
            },
            getStateNurses() {
                // this.retrievingProviders = true;
                this.loadingStateNurseData = true;
                let data = {
                    start: this.startDate,
                    end: this.endDate,
                    state: this.state
                }

                modRequest.request('sa.payroll.get_earnings_report_state', {}, data, response => {
                    if (response.success) {
                        this.stateEarningNurses = null;
                        if (response.returnArray) {
                            // Apply rounding to the 'totalPay' values
                            this.stateEarningNurses = response.returnArray.map(nurse => {
                                return {
                                    ...nurse,
                                    totalPay: parseFloat(nurse.totalPay).toFixed(2)
                                };
                            });
                        }
                        this.retrievingProviders = false;
                        this.triggerSnackbar('State Nurses retrieval failed!', '#4CAF50', timeout = 1000);
                        this.loadingGovernmentReportdata = false;

                    } else {
                        this.nurseReports = [];
                        this.triggerSnackbar('State Nurses retrieval failed!', '#F44336', timeout = 4000);
                        this.loadingGovernmentReportdata = false;
                    }
                }, response => {
                    this.nurseReports = [];
                    this.triggerSnackbar('State Nurses retrieval failed!', '#F44336', timeout = 4000);
                    this.loadingGovernmentReportdata = false;
                });
            },
            triggerSnackbar(message, color, timeout = 4000) {
                this.snackbar.message = message;
                this.snackbar.color = color;
                this.snackbar.timeout = timeout;
                this.snackbar.status = true;
            },
        }
    });
});


