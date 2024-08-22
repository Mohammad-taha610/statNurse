window.addEventListener('load', function () {
    Vue.component('dashboard-view', {
        template:`
        <div class="container-fluid">
            <div class="row">
                <div v-for="item in dashboard_items" v-if="!item.table" class="col-xl-6 col-lg-6 col-sm-6">
                    <div class="widget-stat card">
                        <div class="card-body p-4">
                            <div class="media ai-icon">
                                <span :class="item.color" >
                                    <i :class="item.icon"></i>
                                </span>
                                <div class="media-body">
                                    <p class="mb-1"><a :href="item.route">{{item.name}}</a></p>
                                    <h4 class="mb-0">{{item.value}}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div v-else class="col-xl-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">Upcoming Shifts</h4>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive" id="vue">
                            <v-app>
                                <v-data-table
                                    :headers="headers"
                                    :items="upcoming_shifts"
                                    item-key="name"
                                    class="table table-responsive-md">
                                    <template v-slot:item.actions="{ item }">
                                        <a :href="item.shift_route"><v-icon class="blue--text">mdi-square-edit-outline</v-icon></a>
                                        <v-icon
                                            v-show="!item.too_late && item.status == 'Approved'"
                                            color="red"
                                            @click="cancelApprovedShiftDialog = true; selectedItem = item">
                                            mdi-cancel
                                        </v-icon>
                                    </template>
                                </v-data-table>
                                <v-dialog
                                    v-model="cancelApprovedShiftDialog"
                                    hide-overlay
                                    transition="dialog-bottom-transition"
                                    max-width="500"
                                    :retain-focus="false">
                                    <v-card>
                                        <v-toolbar
                                            color="primary"
                                            dark>
                                            <v-card-title>Confirm Shift Cancellation</v-card-title>
                                        </v-toolbar>

                                        <v-card-text
                                            class="pt-6 px-8">
                                            Nurse: <span style="color: black;">{{ selectedItem.nurse_name }}</span><br>
                                            Date: <span style="color: black;">{{ selectedItem.date }}</span><br>
                                            Start Time: <span style="color: black;">{{ selectedItem.start_time }}</span><br>
                                            End Time: <span style="color: black;">{{ selectedItem.end_time }}</span>
                                        </v-card-text>
                                        
                                        <v-card-actions class="d-flex px-8 pb-8">
                                            <v-btn
                                                color="confirm"
                                                class="pa-2 ml-auto"
                                                @click="cancelApprovedShiftDialog = false">
                                                Do nothing
                                            </v-btn>
                                            <v-btn
                                                color="primary"
                                                class="pa-2"
                                                @click="cancelApprovedShift()">
                                                Confirm Cancel
                                            </v-btn>
                                        </v-card-actions>
                                    </v-card>
                                </v-dialog>
                            </v-app>
                            </div>
                            <v-snackbar
                                v-model="snackbar.status"
                                :timeout="snackbar.timeout"
                                :color="snackbar.color">
                                {{ snackbar.message }}
                                <template v-slot:action="{ attrs }">
                                    <v-btn
                                        color="white"
                                        text
                                        v-bind="attrs"
                                        @click="snackbar.status = false">
                                        Close
                                    </v-btn>
                                </template>
                            </v-snackbar>
                        </div>
                    </div>
                </div>
            </div>
        </div>
       `,
        props: [],
        data: function () {
            return {
                upcoming_shifts: [],
                unclaimed_shifts: 0,
                shift_requests: 0,
                shifts_to_review: 0,
                current_pay_period: '',
                dashboard_items: [],
                headers: [
                    { text: 'Nurse', align: 'start', value: 'nurse_name' },
                    { text: 'Date', value: 'date' },
                    { text: 'Start Time', value: 'start_time' },
                    { text: 'End Time', value: 'end_time' },
                    { text: 'Actions', value: 'actions' }
                ],
                recurrence: [],
                selectedItem: {
                    id: '',
                    nurse_name: '',
                    date: '',
                    start_time: '',
                    end_time: ''
                },
                cancelApprovedShiftDialog: false,
                snackbar: {
                    status: false,
                    timeout: 4000,
                    message: '',
                    color: '#4CAF50' // green/success
                },
            }
        },
        computed: {
            itemColor(item) {
                return {
                    'text-primary': item.textPrimary,
                }
            }
        },
        created() {
            this.loadDashboardData();
        },
        methods: {
            loadDashboardData() {
                modRequest.request('provider.load_dashboard_data', {}, {}, function (response) {
                    if (response.success) {
                        let items = [];
                        let shifts = [];
                        for (let i = 0; i < response.items.length; i++) {
                            let item = response.items[i];
                            items.push({
                                name: item.name,
                                value: item.value,
                                color: item.color,
                                icon: item.icon,
                                route: item.route,
                                table: item.table
                            });
                        }
                        for (let i = 0; i < response.shifts.length; i++) {
                            let shift = response.shifts[i];

                            shifts.push({
                                id: shift.id,
                                too_late: this.checkTime(shift.start),
                                nurse_name: shift.nurse_name,
                                date: shift.date,
                                start_time: shift.start_time,
                                end_time: shift.end_time,
                                status: shift.status,
                                shift_route: shift.shift_route,
                                nurse_route: shift.nurse_route
                            });
                        }
                        this.recurrence = response.recurrence;
                        this.dashboard_items = items;
                        this.upcoming_shifts = shifts;
                    } else {
                        console.log('Error');
                        console.log(response);
                    }
                }.bind(this), function (response) {
                    console.log('Failed');
                    console.log(response);
                });
            },
            cancelApprovedShift() {
                let data = {
                    "shift_id": this.selectedItem.id
                }

                modRequest.request('provider.cancel_approved_shift', null, data, response => {
                        if (response.success) {
                            this.triggerSnackbar('Successfully canceled shift', '#4CAF50')
                            this.cancelApprovedShiftDialog = false;
                            this.loadDashboardData();
                        } else {
                            this.triggerSnackbar(error.message, '#F44336')
                            this.cancelApprovedShiftDialog = false;
                            this.loadDashboardData();
                        }
                    }, error => {
                        this.triggerSnackbar(error.message, '#F44336')
                        this.cancelApprovedShiftDialog = false;
                    });            
            },
            triggerSnackbar(message, color, timeout = 4000) {
                this.snackbar.message = message;
                this.snackbar.color = color;
                this.snackbar.timeout = timeout;
                this.snackbar.status = true;
            },
            checkTime(start) {
                let now = luxon.DateTime.now();
                let shiftDate = luxon.DateTime.fromSQL(start.date, {zone: start.timezone}).toLocal().minus({hours: 2});
                return !(now < shiftDate);
            }

        }
    });
});
