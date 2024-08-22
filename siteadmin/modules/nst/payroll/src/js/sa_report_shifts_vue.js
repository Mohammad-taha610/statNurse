window.addEventListener('load', function () {
    Vue.component('sa-reports-shifts-view', {
        // language=HTML
        template: `
        <v-app>
            <div>
                <v-row>
                    <v-col
                        cols="9">
                        Report flow:
                            <ul>
                                <li>Please select a Start Date and an End Date, then click "Search All" to see all shifts between those two dates.</li>
                                <li>Additionally, you may select a Nurse and click Search for Nurse to view all shifts between those two dates for the nurse you choose.</li>
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
                        
                        <v-autocomplete
                        class="px-2"
                        :items="nurses"
                        v-model="nurse"
                        item-text="fullname"
                        item-value="id"
                        label="Select Nurse"
                        clearable>
                        </v-autocomplete>
                        <v-card-actions v-if="nurse">
                            <v-btn
                                v-if="nurse == 'All'"
                                @click="getShifts"
                                :disabled="retrievingShifts">
                                Search All
                            </v-btn>
                            <v-btn
                                v-else
                                @click="getNurseShifts"
                                :disabled="retrievingNurseShifts">
                                Search for Nurse
                            </v-btn>
                        </v-card-actions>

                    </v-col>
                </v-row>
            </div>
                <v-card v-if="nurse == 'All'">
                <v-card-text>
                <v-data-table 
                        v-model:expanded="expanded"
                        :headers="shiftsTableHeaders"
                        :items="shifts"
                        item-key="shift_id"
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
                                v-if="nurse == 'All'"
                                @click="getShifts"
                                :disabled="retrievingShifts">
                                Refresh All
                            </v-btn>
                        </v-card-actions>
        <v-overlay
            class="text-center"
            v-if="retrievingShifts"
            opacity="0.75"
            absolute>
            <v-progress-circular
                class="mr-2"
                indeterminate
                size="64">
            </v-progress-circular>
            <div 
                class="pa-4"> 
                Retrieving shift information...
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

                <v-card v-if="nurse != 'All'">
                <v-card-text>
                <v-data-table 
                        v-model:expanded="expanded"
                        :headers="shiftsTableHeaders"
                        :items="nurseShifts"
                        item-key="shift_id"
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
        <v-card-actions v-if="nurse">
                            <v-btn
                                @click="getNurseShifts"
                                :disabled="retrievingNurseShifts">
                                Refresh
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
            Retrieving nurse shift information...
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
                shiftsTableHeaders: [
                    { text: 'Shift Id', align: 'start', sortable: true, value: 'shift_id' },
                    { text: 'Provider', align: 'start', sortable: true, value: 'company' },
                    { text: 'City', align: 'start', sortable: true, value: 'city' },
                    { text: 'Start', align: 'start', sortable: true, value: 'start.date1' },
                    { text: 'End', align: 'start', sortable: true, value: 'end.date1' },
                    { text: 'First Name', align: 'start', sortable: true, value: 'first_name' },
                    { text: 'Last Name', align: 'start', sortable: true, value: 'last_name' },
                    { text: 'Credentials', align: 'start', sortable: true, value: 'credentials' },
                    { text: 'Incentive', align: 'start', sortable: true, value: 'incentive' },
                    { text: 'Bonus Amount', align: 'start', sortable: true, value: 'bonus_amount' },
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
                retrievingShifts: true,
                retrievingNurseShifts: false,
                nurses: ["All",
                
            ],
                nurse: "All",
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
                this.getAllNurseNames();
                this.getShifts();
                // this.getStateNurses();
            },
            expandRow(item) {
                // TODO: return if there is no data to expand
                this.expanded = item === this.expanded[0] ? [] : [item]
            },
            getAllNurseNames(){
                data = 'test';
                modRequest.request('sa.payroll.get_all_nurse_names', {}, data, response => {
                    if (response.success) {
                        this.triggerSnackbar('Nurses names retrieved successfully!', '#4CAF50', timeout = 1000);
                        if (response.returnArray) {
                            console.log('return array', response.returnArray);
                            response.returnArray.forEach(nurse => this.nurses.push(nurse));
                        }
                        this.retrievingNurses = false;
                    } else {
                        this.triggerSnackbar('Nurses Names retrieval failed!', '#F44336', timeout = 4000);
                    }
                }, response => {
                    this.triggerSnackbar('Nurses Names retrieval failed!', '#F44336', timeout = 4000);
                })
            },
            getShifts() {
                this.retrievingShifts = true;
                this.loadingShiftsData = true;
                let data = {
                    start: this.startDate,
                    end: this.endDate
                }
                modRequest.request('sa.payroll.get_shifts_report', {}, data, response => {
                    if (response.success) {
                        this.triggerSnackbar('Shifts retrieved successfully!', '#4CAF50', timeout = 1000);
                        this.shifts = null;
                        if (response.returnArray) {
                            // Apply rounding to the 'totalPay' values
                            this.shifts = response.returnArray;
                            // this.earningNurses = response.returnArray.map(nurse => {
                            //     return {
                            //         ...nurse,
                            //         totalPay: parseFloat(nurse.totalPay).toFixed(2)
                            //     };
                            // });
                        }
                        this.retrievingShifts = false;
                    } else {
                        this.triggerSnackbar('Shifts retrieval failed!', '#F44336', timeout = 4000);
                    }
                }, response => {
                    this.triggerSnackbar('Shifts retrieval failed!', '#F44336', timeout = 4000);
                });
            },
            getNurseShifts() {
                // this.retrievingProviders = true;
                this.loadingNurseShiftsData = true;
                let data = {
                    start: this.startDate,
                    end: this.endDate,
                    nurse: this.nurse
                }

                modRequest.request('sa.payroll.get_shifts_report_nurse', {}, data, response => {
                    if (response.success) {
                        this.nurseShifts = null;
                        if (response.returnArray) {
                            // Apply rounding to the 'totalPay' values
                            this.nurseShifts = response.returnArray;
                            // this.earningNurses = response.returnArray.map(nurse => {
                            //     return {
                            //         ...nurse,
                            //         totalPay: parseFloat(nurse.totalPay).toFixed(2)
                            //     };
                            // });
                        }
                        this.loadingNurseShiftsData = false;
                        this.triggerSnackbar('Nurse Shifts retrieval failed!', '#4CAF50', timeout = 1000);
                        this.loadingGovernmentReportdata = false;

                    } else {
                        this.nurseReports = [];
                        this.triggerSnackbar('Nurse Shifts retrieval failed!', '#F44336', timeout = 4000);
                        this.loadingGovernmentReportdata = false;
                    }
                }, response => {
                    this.nurseReports = [];
                    this.triggerSnackbar('Nurse Shifts retrieval failed!', '#F44336', timeout = 4000);
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
