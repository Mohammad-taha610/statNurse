window.addEventListener('load', function () {
    Vue.component('sa-reports-inactive-view', {
        // language=HTML
        template: `
        <v-app>
            <div>
                <v-row>
                    <v-col
                        cols="9">
                        Report flow:
                            <ul>
                                <li>Please select whether you'd like to see inactive nurses or inactive providers using the Inactive Type select box to the right.</li>
                                <li>Nurses/Providers will appear in this report once they have not logged a shift for 60 days.</li>
                                <li>If you'd like to take an action on an entry, simply click the dropdown arrow on the right side of that entry and select an action.</li>
                            </ul>
                    </v-col>
                    <v-col 
                        class="text-right d-flex"
                        cols="3">
                        <v-select
                        class="px-2"
                        :items="inactiveTypes"
                        v-model="inactiveType"
                        label="Inactive Type">
                    </v-select>
                    </v-col>
                </v-row>
            </div>
                <v-card v-if="inactiveType == 'Nurses'">
                <v-card-text>
                <v-data-table 
                        v-model:expanded="expanded"
                        :headers="nurseInactiveTableHeaders"
                        :items="inactiveNurses"
                        item-key="nurse.id"
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
                @click="getInactiveNurses"
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

                <v-card v-if="inactiveType == 'Providers'">
                <v-card-text>
                <v-data-table 
                        v-model:expanded="expanded"
                        :headers="providerInactiveTableHeaders"
                        :items="inactiveProviders"
                        item-key="provider.id"
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
        <v-card-actions>
        <v-btn
        @click="getInactiveProviders"
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
                nurseInactiveTableHeaders: [
                    { text: 'Nurse Name', align: 'start', sortable: true, value: 'fullname' },
                    { text: 'Credentials', align: 'start', sortable: true, value: 'nurse.credentials' },
                    { text: 'City', align: 'start', sortable: true, value: 'nurse.city' },
                    { text: 'State', align: 'start', sortable: true, value: 'nurse.state' },
                    { text: 'Last Shift Start', align: 'start', sortable: true, value: 'shift.start' },
                    { text: 'Last Shift End', align: 'start', sortable: true, value: 'shift.end' },
                    { text: '', value: 'data-table-expand' },
                ],
                providerInactiveTableHeaders: [
                    { text: 'Provider Name', align: 'start', sortable: true, value: 'company' },
                    { text: 'City', align: 'start', sortable: true, value: 'provider.city' },
                    { text: 'State', align: 'start', sortable: true, value: 'provider.state_abbreviation' },
                    { text: 'Facility Phone Number', align: 'start', sortable: true, value: 'provider.facility_phone_number' },
                    { text: 'Last Shift Start', align: 'start', sortable: true, value: 'shift.start' },
                    { text: 'Last Shift End', align: 'start', sortable: true, value: 'shift.end' },
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
                inactiveTypes: ["None", "Nurses", "Providers"],
                inactiveType: "None",
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
                this.setCurrentDate();
                this.getInactiveNurses();
                this.getInactiveProviders();
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
            getInactiveNurses() {
                this.retrievingNurses = true;
                this.loadingInactiveNurseData = true;
                let data = {
                    start: this.formattedDate,
                    end: this.formattedToday
                }
                modRequest.request('sa.payroll.get_inactive_nurses', {}, data, response => {
                    if (response.success) {
                        this.triggerSnackbar('Inactive Nurses retrieved successfully!', '#4CAF50', timeout = 1000);
                        this.inactiveNurses = null;
                        if (response.returnArray) {
                            this.inactiveNurses = response.returnArray;
                        };
                        this.retrievingNurses = false;
                    } else {
                        this.triggerSnackbar('Inactive Nurses retrieval failed!', '#F44336', timeout = 4000);
                    }
                }, response => {
                    this.triggerSnackbar('Inactive Nurses retrieval failed!', '#F44336', timeout = 4000);
                });
            },
            getInactiveProviders() {
                this.retrievingProviders = true;
                this.loadingInactiveProviderData = true;
                let data = {
                    start: this.formattedDate,
                    end: this.formattedToday

                }

                modRequest.request('sa.payroll.get_inactive_providers', {}, data, response => {
                    if (response.success) {
                        this.inactiveProviders = null;
                        if (response.returnArray) {
                            this.inactiveProviders = response.returnArray;
                        }
                        this.retrievingProviders = false;
                        this.triggerSnackbar('Inactive Providers retrieved successfully!', '#4CAF50', timeout = 1000);
                        this.loadingGovernmentReportdata = false;

                    } else {
                        this.nurseReports = [];
                        this.triggerSnackbar('Inactive Providers retrieval failed!', '#F44336', timeout = 4000);
                        this.loadingGovernmentReportdata = false;
                    }
                }, response => {
                    this.nurseReports = [];
                    this.triggerSnackbar('Inactive Providers retrieval failed!', '#F44336', timeout = 4000);
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


