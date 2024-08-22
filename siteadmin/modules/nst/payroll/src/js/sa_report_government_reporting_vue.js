
window.addEventListener('load', function () {
    Vue.component('sa-reports-government-reporting-view', {
        // language=HTML
        template: `
        <v-app>
            <div>
                <v-row>
                    <v-col
                        cols="9">
                        Report flow:
                            <ul>
                                <li>Select your Year and Quarter for reporting on.</li>
                                <li>Click Generate Report For... (if report was generated previously that data will still be there, but you may need to regenerate the report if you have made changes)</li>
                                <li>Report will take several minutes to generate.</li>
                                <li>If report is fully generated and all entries are ready, you may download the entire report by clicking the Download Zip button. If there are any issues with the report files the button will be disabled.</li>
                                <li>If there are any incomplete entries you must address issues wit that entry and regenerate the entire report to resolve those issues and enable the download zip button. </li>
                            </ul>
                    </v-col>
                    <v-col 
                        class="text-right d-flex"
                        cols="3">
                        <v-select
                            class="px-2"
                            :items="years"
                            v-model="selectedYear"
                            label="Year"
                            @change="getReports"
                            :disabled="generatingReport || generatingReportZip">
                        </v-select>
                        <v-select
                            class="px-2"
                            :items="quarters"
                            v-model="selectedQuarter"
                            label="Quarter"
                            @change="getReports"
                            :disabled="generatingReport || generatingReportZip">
                        </v-select>
                    </v-col>
                </v-row>

            </div>
                <v-card>
                    <v-card-text>
                            <v-data-table 
                                    v-model:expanded="expanded"
                                    :headers="governmentReportingTableHeaders"
                                    :items="nurseReports"
                                    item-key="name"
                                    no-data-text="No data available. You may need to generate a report for this quarter."
                                    :loading="loadingGovernmentReportdata || generatingReport || generatingReportZip"
                                    :expanded.sync="expanded"
                                    show-expand>
                                    <template v-slot:expanded-item="{ headers, item }">
                                        <td :colspan="headers.length">
                                            <div class="d-flex" v-if="item.actions">
                                                <span >Issues:</span>
                                                <ul>
                                                    <li v-for="(action, key) in item.actions"><span class="red--text">{{ action }}</span></li>
                                                </ul>
                                            </div>
                                            <span v-else class="green--text">No action needed.</span>
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
                                    <v-form>
                                        <v-input 
                                            name="selectedYear"
                                            v-model="selectedYear"
                                            hidden>
                                        </v-input>

                                        <v-input 
                                            name="selectedQuarter"
                                            v-model="selectedQuarter"
                                            hidden>
                                        </v-input>

                                    </v-form>
                            </v-data-table>
                    </v-card-text>
                    <v-card-actions>
                        <v-btn
                            @click="startReportBatching"
                            :disabled="generatingReport || generatingReportZip">
                            Generate report for {{ selectedYear }} {{ selectedQuarter }}
                        </v-btn>
                        <v-tooltip bottom>
                            <template v-slot:activator="{ on, attrs }">
                                <v-btn
                                    class="ml-2"
                                    @click="generateAndDownloadZip"
                                    :disabled="generatingReport || isReady"
                                    v-bind="attrs"
                                    v-on="on">
                                    Download zip
                                </v-btn>
                            </template>
                            <span>May only download the zip file once ALL nurse licenses and shift reports are generated and all entries are ready.</span>
                        </v-tooltip>
                        <v-btn
                            @click="getReports"
                            :disabled="generatingReport || generatingReportZip">
                            Refresh
                        </v-btn>
                    </v-card-actions>
                    <v-overlay
                        class="text-center"
                        :value="generatingReport || generatingReportZip"
                        opacity="0.75"
                        absolute>
                        <v-progress-circular
                            class="mr-2"
                            indeterminate
                            size="64">
                        </v-progress-circular>
                        <div 
                            v-if="batchingData.totalBatches > 0"
                            class="pa-4"> 
                            Generating quarterly reports can take several minutes...  <br>
                            Generating batch {{ batchingData.currentBatch + 1 }} of {{ batchingData.totalBatches }} out of {{ batchingData.totalNurses }} nurses in this report
                        </div>
                        <div 
                            v-else
                            class="pa-4"> 
                            Retrieving report information...
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
                loadingGovernmentReportdata: false,
                generatingReport: false,
                generatingReportZip: false,
                governmentReportingTableHeaders: [
                    { text: 'Name', align: 'start', sortable: false, value: 'name' },
                    { text: 'Credential', align: 'start', sortable: false, value: 'credential' },
                    { text: 'Last Modified', align: 'start', sortable: true, value: 'lastModified' },
                    { text: 'Nursing License', align: 'start', sortable: true, value: 'nursingLicenseFile' },
                    { text: 'Shifts Report', align: 'start', sortable: true, value: 'shiftsReportFile' },
                    { text: 'Status', align: 'start', sortable: true, value: 'status' },
                    { text: '', value: 'data-table-expand' },
                ],
                nurseReports: [],
                years: [],
                quarters: ["Q1", "Q2", "Q3", "Q4"],
                selectedYear: "",
                selectedQuarter: "",
                expanded: [],
                snackbar: {
                    status: false,
                    message: [],
                    color: 'success',
                    timeout: 4000
                },
                batchingData: {
                    totalBatches: 0,
                    currentBatch: 0,
                    completed: false,
                    totalNurses: 0
                }
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
                this.setCurrentYearlyQuarter();
                this.getReports()
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
            },
            expandRow(item) {
                // TODO: return if there is no data to expand
                this.expanded = item === this.expanded[0] ? [] : [item]
            },
            getReports() {
                this.loadingGovernmentReportdata = true;
                let data = {
                    year: this.selectedYear,
                    quarter: this.selectedQuarter
                }

                modRequest.request('sa.payroll.get.governement.report', {}, data, response => {
                    if (response.success) {
                        this.nurseReports = [];
                        if (response.report) {
                            this.nurseReports = [...Object.values(response.report)];
                        }
                        this.triggerSnackbar('Report retrieved successfully!', '#4CAF50', timeout = 1000);
                        this.loadingGovernmentReportdata = false;

                    } else {
                        this.nurseReports = [];
                        this.triggerSnackbar('Report retrieval failed!', '#F44336', timeout = 4000);
                        this.loadingGovernmentReportdata = false;
                    }
                }, response => {
                    this.nurseReports = [];
                    this.triggerSnackbar('Report retrieval failed!', '#F44336', timeout = 4000);
                    this.loadingGovernmentReportdata = false;
                });
            },
            generateReportWithBatching(batch = 0, batchSize = 100, totalCount = null) {
                let data = {
                    year: this.selectedYear,
                    quarter: this.selectedQuarter,
                    limit: batchSize,
                    offset: batch * batchSize
                }
                this.batchingData.currentBatch = batch;

                modRequest.request('sa.payroll.generate.governement.report.batch', {}, data, response => {
                    if (response.success) {
                        if(response.completed) {
                            this.batchingData.completed = true;
                            this.triggerSnackbar('Report generated successfully!', '#4CAF50', timeout = 1000);
                            this.generatingReport = false;
                            this.getReports();
                        } else {
                            this.generateReportWithBatching(batch + 1, batchSize, response.count);
                        }
                    } else {
                        this.triggerSnackbar('Report batch '+ batch + ' failed.', '#F44336', timeout = 4000);
                    }
                }, response => {
                    this.triggerSnackbar('Report batch '+ batch + ' failed.', '#F44336', timeout = 4000);
                });
            },
            startReportBatching() {
                this.generatingReport = true;
                let data = {
                    year: this.selectedYear,
                    quarter: this.selectedQuarter
                }

                let batchSize = 100;

                this.nurseReports = [];

                modRequest.request('sa.payroll.get.governement.report.batching.data', {}, data, response => {
                    if (response.success) {
                        this.batchingData.totalBatches = response.count % batchSize === 0 ? response.count / batchSize : Math.floor(response.count / batchSize) + 1;
                        this.batchingData.currentBatch = 0;
                        this.batchingData.completed = false;
                        this.batchingData.totalNurses = response.count;

                        this.generateReportWithBatching(0, batchSize, response.count);
                    } else {
                        this.triggerSnackbar('Report generation failed.', '#F44336', timeout = 4000);
                        this.generatingReport = false;
                    }
                }, response => {
                    this.triggerSnackbar('Report generation failed.', '#F44336', timeout = 4000);
                    this.generatingReport = false;
                });
            },
            generateAndDownloadZip() {
                this.generatingReportZip = true;
                let data = {
                    year: this.selectedYear,
                    quarter: this.selectedQuarter
                }
                modRequest.request('sa.payroll.generate.governement.report.zip', {}, data, response => {
                    if (response.success) {
                        window.open(response['fileRoute']);
                        this.triggerSnackbar('Report compressed successfully!', '#4CAF50', timeout = 1000);
                        this.generatingReportZip = false;
                    } else {
                        this.triggerSnackbar('Report compression failed.', '#F44336', timeout = 4000);
                        this.generatingReportZip = false;
                    }
                }, response => {
                    this.triggerSnackbar('Report compression failed.', '#F44336', timeout = 4000);
                    this.generatingReportZip = false;
                });
            },
            openFile(arg) {
                this.triggerSnackbar('File viewing is not yet implemented!', '#2196F3', timeout = 2000);
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

