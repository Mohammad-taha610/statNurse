window.addEventListener('load', function () {
    Vue.component('sa-reports-dnr-view', {
        // language=HTML
        template: `
        <v-app>
            <div>
                <v-row>
                    <v-col
                        cols="9">
                        Report flow:
                            <ul>
                                <li>Please select whether you'd like to see DNR lists for nurses or DNR lists for providers using the DNR Type select box to the right.</li>
                                <li>To see the list for a nurse or provider, click the chevron on the right side of the entry and view the contents.</li>
                            </ul>
                    </v-col>
                    <v-col 
                        class="text-right d-flex"
                        cols="3">
                        <v-select
                        class="px-2"
                        :items="dnrTypes"
                        v-model="dnrType"
                        label="DNR Type">
                    </v-select>
                    </v-col>
                </v-row>
            </div>
                <v-card v-if="dnrType == 'Nurses'">
                <v-card-text>
                <v-data-table 
                        v-model:expanded="expanded"
                        :headers="nurseDnrTableHeaders"
                        :items="dnrNurses"
                        item-key="nurse.id"
                        no-data-text="No data found."
                        :expanded.sync="expanded"
                        show-expand>
                        <template v-slot:expanded-item="{ headers, item }">
                            <td :colspan="headers.length">
                                <div class="d-flex" v-if="item.blocked.length > 0"> <!-- keeping for future item specific actions if needed -->
                                    <span >Blocked Providers:</span>
                                    <ul>
                                        <li v-for="(name, key) in item.blockedNames"><span>{{ name }}</span></li>
                                    </ul>
                                </div>
                                <span v-else class="green--text">No blocked providers found.</span>
                            </td>
                        </template>
                </v-data-table>
        </v-card-text>
        <v-card-actions>
            <v-btn
                @click="getDnrNurseReport"
                :disabled="retrievingNurses">
                Refresh
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

                <!-- Providers Table -->

                <v-card v-if="dnrType == 'Providers'">
                <v-card-text>
                <v-data-table 
                        v-model:expanded="expanded"
                        :headers="providerDnrTableHeaders"
                        :items="dnrProviders"
                        item-key="provider.id"
                        no-data-text="No data found."
                        :expanded.sync="expanded"
                        show-expand>
                        <template v-slot:expanded-item="{ headers, item }">
                            <td :colspan="headers.length">
                                <div class="d-flex" v-if="item.blocked.length > 0">  <!-- keeping for future item specific actions if needed -->
                                    <span >Blocked Nurses:</span>
                                    <ul>
                                        <li v-for="(blocked, key) in item.blocked"><span>{{ blocked.first_name }}  {{blocked.last_name}} {{blocked.credentials}} </span></li>
                                    </ul>
                                </div>
                                <span v-else class="green--text">No blocked nurses found.</span>
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
        <v-card-actions>
        <v-btn
        @click="getDnrProvidersReport"
        :disabled="retrievingProviders">
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
            Retrieving provider information...
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
                nurseDnrTableHeaders: [
                    { text: 'Nurse Name', align: 'start', sortable: true, value: 'fullname' },
                    { text: 'Credentials', align: 'start', sortable: true, value: 'nurse.credentials' },
                    { text: 'City', align: 'start', sortable: true, value: 'nurse.city' },
                    { text: 'State', align: 'start', sortable: true, value: 'nurse.state' },
                    { text: '', value: 'data-table-expand' },
                ],
                providerDnrTableHeaders: [
                    { text: 'Provider Name', align: 'start', sortable: true, value: 'company' },
                    { text: 'City', align: 'start', sortable: true, value: 'provider.city' },
                    { text: 'State', align: 'start', sortable: true, value: 'provider.state_abbreviation' },
                    { text: 'Facility Phone Number', align: 'start', sortable: true, value: 'provider.facility_phone_number' },
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
                dnrTypes: ["None", "Nurses", "Providers"],
                dnrType: "None",
                formattedDate: null,
                formattedToday: null,
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
                this.getDnrNurseReport();
                this.getDnrProvidersReport();
            },
            setCurrentDate() {
                const today = new Date();
                const sixtyDaysAgo = new Date();
                sixtyDaysAgo.setDate(today.getDate() - 60);
                this.formattedDate = moment(sixtyDaysAgo).format('YYYY-MM-DD HH:mm:ss');
                this.formattedToday = moment(today).format('YYYY-MM-DD HH:mm:ss');
            },
            expandRow(item) {
                // TODO: return if there is no data to expand
                this.expanded = item === this.expanded[0] ? [] : [item]
            },
            getDnrNurseReport() {
                data = {};
                modRequest.request('sa.payroll.get_dnr_nurse_report', {}, data, response => {
                    if (response.success) {
                        this.triggerSnackbar('DNR Nurse Report retrieved successfully!', '#4CAF50', timeout = 1000);
                        this.dnrNurses = null;
                        if (response.returnArray) {
                            this.dnrNurses = response.returnArray;
                        };
                        this.retrievingNurses = false;
                    } else {
                        this.triggerSnackbar('DNR Nurses Report retrieval failed!', '#F44336', timeout = 4000);
                    }
                }, response => {
                    this.triggerSnackbar('DNR Nurses Report retrieval failed!', '#F44336', timeout = 4000);
                });
            },
            getDnrProvidersReport() {
                data = {};
                modRequest.request('sa.payroll.get_dnr_provider_report', {}, data, response => {
                    if (response.success) {
                        this.triggerSnackbar('DNR Provider Report retrieved successfully!', '#4CAF50', timeout = 1000);
                        this.dnrProviders = null;
                        if (response.returnArray) {
                            this.dnrProviders = response.returnArray;
                        }
                        this.retrievingProviders = false;
                    } else {
                        this.dnrProvidersReport = [];
                        this.triggerSnackbar('DNR Providers retrieval failed!', '#F44336', timeout = 4000);

                    }
                }, response => {
                    this.triggerSnackbar('DNR Providers retrieval failed!', '#F44336', timeout = 4000);
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


