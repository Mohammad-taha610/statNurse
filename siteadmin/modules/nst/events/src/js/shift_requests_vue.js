window.addEventListener('load', function() {
    Vue.component('shift-requests-view', {
        template: `
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title">Shift Requests Table</h4>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive" id="vue">
                                <v-app>
                                    <v-card>
                                        <v-card-title>
                                            <v-data-table
                                                class="table table-responsive-md"
                                                :headers="headers"
                                                :items="shift_requests"
                                                multi-sort
                                            >
                                                <template v-slot:item.name="{ item }">
                                                    <a v-bind:href="item.nurse_profile" class="blue--text" target="_blank">
                                                        {{ item.name }}
                                                    </a>
                                                </template>
                                                <template v-slot:item.shift_name="{ item }">
                                                    <a v-bind:href="item.shift_route" class="blue--text" target="_blank">
                                                        {{ item.shift_name }}
                                                    </a>
                                                </template>
                                                <template v-slot:item.start_time="{ item }">
                                                    <span>{{item.start_time}}</span>
                                                </template>
                                                <template v-slot:item.end_time="{ item }">
                                                    <span>{{item.end_time}}</span>
                                                </template>
                                                <template v-slot:item.date="{ item }">
                                                    <span>{{item.date}}</span>
                                                </template>
                                                <template v-slot:item.actions="{ item }">
                                                    <v-dialog max-width="400">
                                                        <template v-slot:activator="{ on, attrs }">
                                                            <v-icon
                                                                v-on="on"
                                                                v-bind="attrs"
                                                                class="mr-2"
                                                                color="success"
                                                            >
                                                                mdi-check
                                                            </v-icon>
                                                        </template>
                                                        <template v-slot:default="dialog">
                                                            <v-card>
                                                                <v-toolbar
                                                                    color="success"
                                                                    class="text-h5 white--text"
                                                                >Approve Shift</v-toolbar>
                                                                <v-card-text
                                                                    class="pt-5"
                                                                >Do you wish to <strong class="success--text">APPROVE</strong> {{item.name}}  for the requested shift?</v-card-text>
                                                                <v-card-actions>
                                                                    <v-spacer></v-spacer>
                                                                    <v-btn
                                                                        color="light"
                                                                        v-on:click="dialog.value = false"
                                                                    >Cancel
                                                                    </v-btn>
                                                                    <v-btn
                                                                        color="success"
                                                                        v-on:click="approveRequest(item)"
                                                                        class="white--text"
                                                                    >Yes, Approve</v-btn>
                                                                </v-card-actions>
                                                            </v-card>
                                                        </template>
                                                    </v-dialog>
                                                    
                                                    <v-dialog max-width="400">
                                                        <template v-slot:activator="{ on, attrs }">
                                                            <v-icon
                                                                v-on="on"
                                                                v-bind="attrs"
                                                                class="mr-2"
                                                                color="red"
                                                            >mdi-window-close</v-icon>
                                                        </template>
                                                        <template v-slot:default="dialog">
                                                            <v-card>
                                                                <v-toolbar
                                                                    color="primary"
                                                                    class="text-h4 white--text"
                                                                >Deny Shift</v-toolbar>
                                                                <v-card-text
                                                                    class="pt-5"
                                                                >Do you wish to <span class="red--text">DENY</span> {{item.name}} for the requested shift?</v-card-text>
                                                                <v-card-actions>
                                                                    <v-spacer></v-spacer>
                                                                    <v-btn
                                                                        color="light"
                                                                        v-on:click="dialog.value = false"
                                                                    >Cancel
                                                                    </v-btn>
                                                                    <v-btn
                                                                        color="red"
                                                                        v-on:click="denyRequest(item)"
                                                                        class="white--text"
                                                                    >Yes, Deny</v-btn>
                                                                </v-card-actions>
                                                            </v-card>
                                                        </template>
                                                    </v-dialog>
                                                    <a
                                                        :href="item.shift_route" target="_blank"
                                                    >
                                                        <v-icon
                                                            color="blue"
                                                        >
                                                            mdi-square-edit-outline
                                                    </v-icon>
                                                    </a>
                                                </template>
                                            </v-data-table>
                                        </v-card-title>
                                    </v-card>
                                </v-app>
                                </div>
                            </div>
                        </div>
                    </div>
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
            </div>`,
        data () {
            return {
                headers: [
                    {
                        text: 'Nurse Name',
                        align: 'start',
                        sortable: true,
                        value: 'name'
                    },
                    {
                        text: 'Shift Name',
                        sortable: true,
                        value: 'shift_name'
                    },
                    {
                        text: 'Start Time',
                        sortable: true,
                        value: 'start_time'
                    },
                    {
                        text: 'End Time',
                        sortable: true,
                        value: 'end_time'
                    },
                    {
                        text: 'Date',
                        sortable: true,
                        value: 'date'
                    },
                    {
                        text: 'Actions',
                        sortable: true,
                        value: 'actions'
                    }
                ],
                shift_requests: [
                ],
                snackbar: {
                    status: false,
                    timeout: 4000,
                    message: '',
                    color: '#4CAF50' // green/success
                },
            }
        },
        created: function() {
            this.getShiftRequests();
        },
        methods: {
            approveRequest: function (item) {
                let data = {
                    id: item.id,
                    is_recurrence: item.is_recurrence
                };

                console.log(data);
                modRequest.request('provider.approve_shift_request', {}, data, response => {
                    if(response.success) {
                        console.log('approved');
                        this.shift_requests.splice(this.shift_requests.indexOf(item), 1);
                        this.triggerSnackbar('Success!', "#4CAF50", 4000);
                    } else {
                        if (response.message) {
                            this.triggerSnackbar(response.message, "#F44336", 4000);
                        }
                        this.getShiftRequests();
                    }
                }, response => {
                    this.triggerSnackbar("Error", "#F44336", 4000);
                });
            },
            denyRequest: function (item) {
                let data = {
                    id: item.id,
                    is_recurrence: item.is_recurrence
                };

                modRequest.request('provider.deny_shift_request', {}, data, response => {
                    if(response.success) {
                        console.log('approved');
                        this.shift_requests.splice(this.shift_requests.indexOf(item), 1);
                        this.triggerSnackbar('Success!', "#4CAF50", 4000);
                    } else {
                        this.triggerSnackbar("Error", "#F44336", 4000);
                        this.getShiftRequests();
                    }
                }, response => {
                    this.triggerSnackbar("Error", "#F44336", 4000);
                });
            },
            getShiftRequests: function () {
                modRequest.request('provider.load_shift_requests', {}, {}, response => {
                    if(response.success) {
                        let shift_requests = [];

                        for(let i = 0; i < response.shifts.length; i++) {
                            let shift = response.shifts[i];
                            shift_requests.push({
                                id: shift.id,
                                is_recurrence: shift.is_recurrence,
                                name: shift.name,
                                shift_name: shift.shift_name,
                                start_time: shift.start_time,
                                end_time: shift.end_time,
                                date: shift.date,
                                nurse_profile: shift.nurse_profile,
                                shift_route: shift.shift_route
                            })
                        }
                        this.shift_requests = shift_requests;
                    } else {
                        console.log('Error');
                        console.log(response);
                    }
                }, response => {
                    console.log('Failed');
                    console.log(response);
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