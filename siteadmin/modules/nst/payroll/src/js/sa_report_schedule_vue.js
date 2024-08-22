window.addEventListener('load', function () {
    Vue.component('sa-reports-schedule-view', {
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
                                <li>Once the results have loaded, you may use the status filters to further filter your results.</li>
                                <li>Additionally, please understand that if too much data is being retrieved, the report will fail. If this happens, simply try again with a different report, or refresh the page. Further optimization is in development.</li>
                                <!-- <li>Tips:  Getting a report for a single nurse will always be more efficient over longer periods of time than all nurses due to the volume of data. If your quarterly report for all nurses is failing, try using pay period reports instead, or quarterly reports for a single nurse.</li> -->
                                
                            </ul>
                    </v-col>
                </v-row>
                <v-row>
                    <v-col 
                    class="text-right d-flex"
                    cols="3">
                    <v-select
                        v-model="timeComponent"
                        :items="timeComponents"
                        label="Time Component"
                        dense
                    ></v-select>
                    </v-col>
                </v-row>
                <v-row>
                    <v-col 
                    v-if="timeComponent == 'Date'"
                    class="text-right d-flex"
                    cols="6">
                        <label for="startDate">Start Date:</label>
                        <input type="date" id="startDate" v-model="startDate" />

                        <label for="endDate">End Date:</label>
                        <input type="date" id="endDate" v-model="endDate" @change="" />
                        
                    </v-col>
                </v-row>
                <v-row>
                    <v-col 
                    v-if="timeComponent == 'Pay Period'"
                    class="text-right d-flex"
                    cols="3">
                    <v-select
                        v-model="pay_period"
                        :items="pay_periods"
                        item-text="display"
                        item-value="combined"
                        label="Pay Period"
                        :disabled="gettingPayPeriods"
                        dense
                    ></v-select>

                    </v-col>
                    </v-row>
                <v-row>
                    <v-col 
                    v-if="timeComponent == 'Year and Quarter'"
                        class="text-right d-flex"
                        cols="3">
                        <v-select
                            class="px-2"
                            :items="years"
                            v-model="selectedYear"
                            label="Year">
                        </v-select>
                        <v-select
                            class="px-2"
                            :items="quarters"
                            v-model="selectedQuarter"
                            label="Quarter">
                        </v-select>
                    </v-col>

                </v-row>
                <v-row>
                    <v-col
                    cols="3">
                <v-autocomplete
                        class="px-2"
                        :items="nurses"
                        v-model="nurse"
                        item-text="fullname"
                        item-value="id"
                        label="Select Nurse"
                        clearable>
                        </v-autocomplete>
    </v-col>
    </v-row>         
    <v-row>
                    <v-col
                    cols="3">
                    
                <v-autocomplete
                        class="px-2"
                        :items="providers"
                        v-model="selectedProviders"
                        item-text="name"
                        item-value="name"
                        label="Filter by Provider"
                        multiple
                        clearable>
                        </v-autocomplete>
    </v-col>
    </v-row>         
    <v-row style="margin-bottom: 30px;">
                    <v-col cols="12" class="d-md-flex ">
                        Filter by Status:
                    <v-btn
                        v-for="status in statuses"
                        :key="status.name"
                        :color="status.shown ? status.color : 'grey lighten-1'"
                        text
                        v-on:click="toggleStatus(status.name)"
                        class="m-2 calendar-status-filter"
                        >{{ status.display_name }}</v-btn>                    </v-col>
                </v-row>
                <v-row style="margin-bottom: 30px;">
                    <v-col cols="12" class="d-md-flex ">
                    Filter by Credential:
                    <v-btn
                        v-for="credential in credentials"
                        :key="credential.name"
                        :color="credential.shown ? credential.color : 'grey lighten-1'"
                        text
                        v-on:click="toggleCredential(credential.name)"
                        class="m-2 calendar-status-filter"
                        >{{ credential.display_name }}</v-btn>                    </v-col>
                </v-row>


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
            </div>
                <v-card v-if="nurse == 'All'">
                <v-card-text>
                <v-data-table 
                        v-model:expanded="expanded"
                        :headers="shiftsTableHeaders"
                        :items="shownShifts"
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
                            <v-btn
                            @click="exportToExcel('All')" 
                            :disabled="retrievingShifts">
                                Export to Excel
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
                        :items="shownNurseShifts"
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
                            <v-btn
                            @click="exportToExcel(nurse)" 
                            :disabled="retrievingNurseShifts">
                                Export to Excel
                            </v-btn>
                        </v-card-actions>
        <v-overlay
        class="text-center"
        v-if="retrievingNurseShifts"
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
                    { text: 'Start', align: 'start', sortable: true, value: 'start.date1' },
                    { text: 'End', align: 'start', sortable: true, value: 'end.date1' },
                    { text: 'Status', align: 'start', sortable: true, value: 'status' },
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
                retrievingShifts: false,
                retrievingNurseShifts: false,
                nurses: ["All",

                ],
                nurse: "All",
                formattedDate: null,
                formattedToday: null,
                startDate: moment().subtract(3, 'months').format('YYYY-MM-DD'),
                today: new Date(),
                endDate: moment(this.today).format('YYYY-MM-DD'),
                startEndDate: null,
                colors: {
                    Open: 'blue',
                    Pending: 'warning',
                    Assigned: 'pink',
                    Approved: 'success',
                    Completed: 'gray_dark'
                },
                timeComponent: null,
                timeComponents: ['Date', 'Pay Period', 'Year and Quarter'],
                statuses: {
                    'Open': {
                        name: 'Open',
                        display_name: 'Open',
                        color: 'blue',
                        shown: true
                    },
                    'Pending': {
                        name: 'Pending',
                        display_name: 'Pending',
                        color: 'warning',
                        shown: true
                    },
                    'Assigned': {
                        name: 'Assigned',
                        display_name: 'Assigned',
                        color: 'pink',
                        shown: true
                    },
                    'Approved': {
                        name: 'Approved',
                        display_name: 'Approved',
                        color: 'success',
                        shown: true
                    },
                    'Completed': {
                        name: 'Completed',
                        display_name: 'Completed',
                        color: 'gray_dark',
                        shown: true
                    }
                },
                years: [],
                quarters: ["Q1", "Q2", "Q3", "Q4"],
                selectedYear: "",
                selectedQuarter: "",
                pay_periods: [],
                pay_period: '',
                shifts: [],
                nurseShifts: [],
                selectedStatus: ['Open', 'Pending', 'Assigned', 'Approved', 'Completed'],
                credentials: {
                    'RN': {
                        name: 'RN',
                        display_name: 'RN',
                        color: 'blue',
                        shown: true
                    },
                    'LPN': {
                        name: 'LPN',
                        display_name: 'LPN',
                        color: 'warning',
                        shown: true
                    },
                    'CNA': {
                        name: 'CNA',
                        display_name: 'CNA',
                        color: 'pink',
                        shown: true
                    },
                    'CMT': {
                        name: 'CMT',
                        display_name: 'CMT',
                        color: 'success',
                        shown: true
                    },
                },
                selectedCredentials: ['RN', 'LPN', 'CNA', 'CMT' ],
                providers: [],
                provider: null,
                selectedProviders: [],
            }
        },
        watch: {
            statuses: {
                deep: true,
                handler: 'updateSelectedStatus'
            },
            credentials: {
                deep: true,
                handler: 'updateSelectedCredential'
            },
            provider: {
                deep: true, 
                handler: 'updateShownShifts',
            },
            shownShifts: function (newValue, oldValue) {
                console.log('shownShifts changed:', newValue);
                // Check if newValue and oldValue are as expected, and verify the data.
            },
        },
        computed: {
            shownShifts: function () {
                return this.shifts.filter(shift => {
                    const statusMatch = this.selectedStatus.some(status => status === shift.status);
                    const credentialsMatch = this.selectedCredentials.length === 0 || this.selectedCredentials.includes(shift.credentials);
                    const providersMatch = this.selectedProviders.length === 0 || this.selectedProviders.includes(shift.company);

                    return statusMatch && credentialsMatch && providersMatch;
                });
            },
            shownNurseShifts: function () {
                return this.nurseShifts.filter(nshift => {
                    const statusMatch = this.selectedStatus.some(status => status === nshift.status);
                    const credentialsMatch = this.selectedCredentials.length === 0 || this.selectedCredentials.includes(nshift.credentials);
                    const providersMatch = this.selectedProviders.length === 0 || this.selectedProviders.includes(nshift.company);

                    return statusMatch && credentialsMatch && providersMatch;
                });
            }

        },
        created: function () {
        },
        mounted: function () {
            this.setupDataTable();
        },
        methods: {
            setupDataTable() {
                this.getAllNurseNames();
                // this.getShifts();
                this.setCurrentYearlyQuarter();
                this.getPayPeriods();
                this.getProviders();
                // this.getStateNurses();
            },
            expandRow(item) {
                // TODO: return if there is no data to expand
                this.expanded = item === this.expanded[0] ? [] : [item]
            },
            updateSelectedStatus() {
                this.selectedStatus = Object.values(this.statuses)
                    .filter(status => status.shown)
                    .map(status => status.name);
            },
            updateSelectedCredential() {
                this.selectedCredentials = Object.values(this.credentials)
                    .filter(credential => credential.shown)
                    .map(credential => credential.name);
            },
            toggleStatus(statusName) {
                this.statuses[statusName].shown = !this.statuses[statusName].shown;
            },
            toggleCredential(credentialName) {
                this.credentials[credentialName].shown = !this.credentials[credentialName].shown;
            },
            getAllNurseNames() {
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
                            this.provider_id = this.provider__id;
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
            setCurrentYearlyQuarter() {
                let now = new luxon.DateTime.now();

                if (now) {
                    this.selectedYear = now.year;
                    let lastQuarter = (now.quarter - 1) < 1 ? 4 : (now.quarter - 1);
                    this.selectedQuarter = 'Q' + lastQuarter;

                    // Setup items for our selects at top of page
                    // Only adding the prior year for now..
                    this.years.push(this.selectedYear);
                    this.years.push(this.selectedYear - 1);
                }
                console.log('year and quarter: ', this.selectedYear, this.selectedQuarter);
            },
            getShifts() {
                // this.retrievingProviders = true;
                this.loadingNurseShiftsData = true;
                let sentStatuses = Object.values(this.statuses).filter(status => status.shown).map(status => status.name);
                console.log('sentStatuses are ', sentStatuses);
                let data = {
                    start: this.startDate,
                    end: this.endDate,
                    status: sentStatuses,
                    timeComponent: this.timeComponent
                }
                console.log('data is', data);
                if (this.timeComponent == 'Date') {

                } else if (this.timeComponent == 'Pay Period') {
                    data.start = this.pay_period;

                } else if (this.timeComponent == 'Year and Quarter') {
                    data.start = this.selectedYear;
                    data.end = this.selectedQuarter;
                }

                modRequest.request('sa.payroll.get_schedule_report', {}, data, response => {
                    if (response.success) {
                        this.shifts = null;
                        if (response.returnArray) {
                            this.shifts = response.returnArray;
                            console.log('shifts are ', this.shifts);
                        }
                        this.loadingNurseShiftsData = false;
                        this.triggerSnackbar('Shifts retrieved successfully!', '#4CAF50', timeout = 1000);
                        this.loadingGovernmentReportdata = false;

                    } else {
                        this.nurseReports = [];
                        this.triggerSnackbar('Shifts retrieval failed!', '#F44336', timeout = 4000);
                        this.loadingGovernmentReportdata = false;
                    }
                }, response => {
                    this.nurseReports = [];
                    this.triggerSnackbar('Shifts retrieval failed!', '#F44336', timeout = 4000);
                    this.loadingGovernmentReportdata = false;
                });
            },
            getNurseShifts() {
                // this.retrievingProviders = true;
                this.loadingNurseShiftsData = true;
                let data = {
                    start: this.startDate,
                    end: this.endDate,
                    nurse: this.nurse,
                    timeComponent: this.timeComponent
                }
                console.log('data is', data);
                if (this.timeComponent == 'Date') {

                } else if (this.timeComponent == 'Pay Period') {
                    data.start = this.pay_period;

                } else if (this.timeComponent == 'Year and Quarter') {
                    data.start = this.selectedYear;
                    data.end = this.selectedQuarter;
                }

                modRequest.request('sa.payroll.get_schedule_report_nurse', {}, data, response => {
                    if (response.success) {
                        this.nurseShifts = null;
                        if (response.returnArray) {
                            this.nurseShifts = response.returnArray;
                            console.log('nurseShifts are ', this.nurseShifts);

                        }
                        this.loadingNurseShiftsData = false;
                        this.triggerSnackbar('Nurse Shifts retrieval successful!', '#4CAF50', timeout = 1000);
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
            getPayPeriods() {
                this.gettingPayPeriods = true;
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

                        this.unresolved_only = this.show_unresolved_only == 1;
                        this.gettingPayPeriods = false;
                    } else {
                        console.log('Error');
                        console.log(response);
                    }
                }.bind(this), function (response) {
                    console.log('Failed');
                    console.log(response);
                });
            },
            exportToExcel(nurseName) {
                // Choose the correct dataset based on the nurseName
                const dataset = nurseName === 'All' ? this.shownShifts : this.shownNurseShifts;
                
                const csvContent = this.convertArrayOfObjectsToCSV(dataset);
                this.downloadCSV(csvContent, `shifts_${nurseName}.csv`);
            },
            convertArrayOfObjectsToCSV(data) {
                const csvRows = [];
                const headers = this.shiftsTableHeaders.map(header => header.text);
          
                // Add the headers as the first row
                csvRows.push(headers.join(','));
          
                // Add each data row
                data.forEach(item => {
                  const values = this.shiftsTableHeaders.map(header => item[header.value]);
                  csvRows.push(values.join(','));
                });
          
                // Combine all rows into a single CSV content string
                return csvRows.join('\n');
              },
              downloadCSV(csvContent, fileName) {
                const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
                if (navigator.msSaveBlob) {
                  // For Internet Explorer
                  navigator.msSaveBlob(blob, fileName);
                } else {
                  // For modern browsers
                  const link = document.createElement('a');
                  if (link.download !== undefined) {
                    const url = URL.createObjectURL(blob);
                    link.setAttribute('href', url);
                    link.setAttribute('download', fileName);
                    link.style.visibility = 'hidden';
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                  }
                }
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
