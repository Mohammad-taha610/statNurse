window.addEventListener('load', function () {
    Vue.component('sa-reports-stub-view', {
        // language=HTML
        template: `
        <v-app>
            <div>
                <v-row>
                    <v-col
                        cols="9">
                        Report flow:
                            <ul>
                                <li>Once the select boxes below finish loading, select the Pay Period and Nurse.</li>
                                <li>Then, press "Download Pay Summary" to generate and view the PDF Pay Stub.</li>
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
                <v-row >
                <v-col cols="3">
                <v-autocomplete
                        class="px-2"
                        :items="nurses"
                        v-model="nurse"
                        item-text="fullname"
                        item-value="id"
                        label="Select Nurse"
                        :disabled="retrievingNurses"
                        clearable>
                        </v-autocomplete>
                        <v-card-actions v-if="nurse">
                            <v-btn
                                @click="getNursePayStub"
                                :disabled="retrievingNurseShifts">
                                Download Pay Summary 
                            </v-btn>
                        </v-card-actions>
                    </v-col>
                </v-row>
            </div>


        </v-app>`,
        props: [
        ],
        data() {
            return {
                pay_periods: [],
                pay_period: '',
                nurseReports: [],
                expanded: [],
                snackbar: {
                    status: false,
                    message: [],
                    color: 'success',
                    timeout: 4000
                },
                retrievingNurses: true,
                retrievingShifts: true,
                retrievingNurseShifts: false,
                nurses: [],
                nurse: null,
                formattedDate: null,
                formattedToday: null,
                startDate: moment().subtract(3, 'months').format('YYYY-MM-DD'),
                today: new Date(),
                endDate: moment(this.today).format('YYYY-MM-DD'),
                gettingPayPeriods: null,             
                timeComponent: null,
                timeComponents: ['Date', 'Pay Period'],

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
            this.getPayPeriods();

        },
        methods: {
            setupDataTable() {
                this.getAllNurseNames();
                this.getShifts();
            },
            expandRow(item) {
                this.expanded = item === this.expanded[0] ? [] : [item]
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
                            this.shifts = response.returnArray;
                        }
                        this.retrievingShifts = false;
                    } else {
                        this.triggerSnackbar('Shifts retrieval failed!', '#F44336', timeout = 4000);
                    }
                }, response => {
                    this.triggerSnackbar('Shifts retrieval failed!', '#F44336', timeout = 4000);
                });
            },
            triggerSnackbar(message, color, timeout = 4000) {
                this.snackbar.message = message;
                this.snackbar.color = color;
                this.snackbar.timeout = timeout;
                this.snackbar.status = true;
            },
            getNursePayStub(){
                // this.retrievingProviders = true;
                this.loadingNursePayStub = true;
                if(this.timeComponent == 'Pay Period'){
                    let data = {
                        pay_period: this.pay_period,
                        id: this.nurse
                    }
                    modRequest.request('sa.payroll.get_pay_summary_pdf', {}, data, response => {
                        if (response.success) {
                            this.nurseShifts = null;
                            if (response.file_route) {
                                window.open(response.file_route, '_blank');
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
    
                }
                else if (this.timeComponent == 'Date'){
                    //date handling here
                    const [startMonth, startDay, startYear] = this.startDate.split('-');
                    const [endMonth, endDay, endYear] = this.endDate.split('-');

                    let data = {
                        pay_period: `${startMonth}${startDay}${startYear}_${endMonth}${endDay}${endYear}`,
                        id: this.nurse
                    }
                    modRequest.request('sa.payroll.get_pay_summary_pdf', {}, data, response => {
                        if (response.success) {
                            this.nurseShifts = null;
                            if (response.file_route) {
                                window.open(response.file_route, '_blank');
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
    
                }

            },
            triggerSnackbar(message, color, timeout = 4000) {
                this.snackbar.message = message;
                this.snackbar.color = color;
                this.snackbar.timeout = timeout;
                this.snackbar.status = true;
            },
    
        },
    });
});
