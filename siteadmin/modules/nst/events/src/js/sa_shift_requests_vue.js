window.addEventListener('load', function() {
    Vue.component('sa-shift-requests-view', {
        template: `
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="table-responsive sa-shift-requests-table">
                                <v-app>
                                    <div class="card">
                                        <v-card-title>
                                            <v-spacer></v-spacer>
                                                <v-menu
                                                        v-model="filters_menu"
                                                        :close-on-content-click="false"
                                                        bottom
                                                        left
                                                        offset-y>
                                                    <template v-slot:activator="{ on, attrs }">
                                                        <v-btn 
                                                                v-on="on"
                                                                v-bind="attrs"
                                                                outlined
                                                                class="mr-4"
                                                                color="grey darken-2"
                                                        >
                                                            Filters
                                                        </v-btn>
                                                    </template>
                                                    <v-card
                                                        class="pt-2"
                                                        >
                                                        <v-list>
                                                            <v-list-item>
                                                                <v-autocomplete
                                                                    v-model="provider_id"
                                                                    :items="providers"
                                                                    item-text="name"
                                                                    item-value="id"
                                                                    label="Provider"
                                                                    background-color="white"
                                                                    dense
                                                                    outlined
                                                                    clearable
                                                                ></v-autocomplete>
                                                            </v-list-item>
                                                            <v-list-item>
                                                                <v-autocomplete
                                                                    v-model="nurse_id"
                                                                    :items="nurses"
                                                                    item-text="name"
                                                                    item-value="id"
                                                                    label="Nurse"
                                                                    background-color="white"
                                                                    dense
                                                                    outlined
                                                                    clearable
                                                                ></v-autocomplete>
                                                            </v-list-item>
                                                        </v-list>
                                                    </v-card>
                                                </v-menu>
                                        </v-card-title>
                                        <v-card-text>
                                            <v-data-table
                                                class="table table-responsive-md"
                                                :headers="headers"
                                                :items="shownRequests"
                                                multi-sort
                                            >
                                                <template v-slot:item.nurse_name="{ item }">
                                                    <a v-bind:href="item.nurse_profile" class="blue--text mt-3 block" target="_blank">
                                                        {{ item.nurse_name }}
                                                    </a>
                                                </template>
                                                <template v-slot:item.provider_name="{ item }">
                                                    <a v-bind:href="item.provider_profile" class="blue--text mt-3 block" target="_blank">
                                                        {{ item.provider_name }}
                                                    </a>
                                                </template>
                                                <template v-slot:item.shift_name="{ item }">
                                                    <a v-bind:href="item.shift_route" class="blue--text mt-3 block" target="_blank">
                                                        {{ item.shift_name }}
                                                    </a>
                                                </template>
                                                <template v-slot:item.start_time="{ item }">
                                                    <span class="block mt-3">{{item.start_time}}</span>
                                                </template>
                                                <template v-slot:item.end_time="{ item }">
                                                    <span class="block mt-3">{{item.end_time}}</span>
                                                </template>
                                                <template v-slot:item.date="{ item }">
                                                    <span class="block mt-3">{{item.date}}</span>
                                                </template>
                                                <template v-slot:item.actions="{ item }">
                                                    <v-dialog max-width="400">
                                                        <template v-slot:activator="{ on, attrs }">
                                                            <v-icon
                                                                v-on="on"
                                                                v-bind="attrs"
                                                                class="mr-2 mt-3"
                                                                color="green"
                                                            >
                                                                mdi-check
                                                            </v-icon>
                                                        </template>
                                                        <template v-slot:default="dialog">
                                                            <v-card>
                                                                <v-toolbar
                                                                    color="green"
                                                                    class="text-h4 white--text"
                                                                >Approve Shift</v-toolbar>
                                                                <v-card-text
                                                                    class="pt-5"
                                                                >Do you wish to <strong class="green--text">APPROVE</strong> {{item.name}}  for the requested shift?</v-card-text>
                                                                <v-card-actions>
                                                                    <v-spacer></v-spacer>
                                                                    <v-btn
                                                                        color="light"
                                                                        v-on:click="dialog.value = false"
                                                                    >Cancel
                                                                    </v-btn>
                                                                    <v-btn
                                                                        color="green"
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
                                                                class="mr-2 mt-3"
                                                                color="red"
                                                            >mdi-window-close</v-icon>
                                                        </template>
                                                        <template v-slot:default="dialog">
                                                            <v-card>
                                                                <v-toolbar
                                                                    color="red"
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
                                                            class="mt-3"
                                                        >
                                                            mdi-square-edit-outline
                                                    </v-icon>
                                                    </a>
                                                </template>
                                            </v-data-table>
                                        </v-card-text>
                                    </div>
                                </v-app>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>`,
        data () {
            return {
                headers: [
                    {
                        text: 'Nurse Name',
                        align: 'start',
                        sortable: true,
                        value: 'nurse_name'
                    },
                    {
                        text: 'Provider Name',
                        align: 'start',
                        sortable: true,
                        value: 'provider_name'
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
                providers: [],
                provider_id: null,
                nurses: [],
                nurse_id: null,
                filters_menu: false
            }
        },
        created: function() {
            this.getShiftRequests();
            this.loadFilters();
        },
        computed: {
            shownRequests : function () {
                return this.shift_requests.filter(function(request)  {
                    let response = (
                        (request.nurse_id == this.nurse_id && request.provider_id == this.provider_id) ||
                        (request.nurse_id == this.nurse_id && !this.provider_id) ||
                        (request.provider_id == this.provider_id && !this.nurse_id )||
                        (!this.nurse_id && !this.provider_id));

                    return response;
                }.bind(this))
            }
        },
        methods: {
            approveRequest: function (item) {
                let data = {
                    id: item.id,
                    is_recurrence: item.is_recurrence
                };

                modRequest.request('sa.shift.approve_shift_request', {}, data, function(response) {
                    if(response.success) {
                        this.shift_requests.splice(this.shift_requests.indexOf(item), 1);
                    } else {
                        console.log('Error');
                        console.log(response);
                    }
                }.bind(this), function(response) {
                    console.log('Failed');
                    console.log(response);
                });
            },
            denyRequest: function (item) {
                let data = {
                    id: item.id,
                    is_recurrence: item.is_recurrence
                };

                modRequest.request('sa.shift.deny_shift_request', {}, data, function(response) {
                    if(response.success) {
                        this.shift_requests.splice(this.shift_requests.indexOf(item), 1);
                    } else {
                        console.log('Error');
                        console.log(response);
                    }
                }.bind(this), function(response) {
                    console.log('Failed');
                    console.log(response);
                });
            },
            getShiftRequests: function () {
                modRequest.request('sa.shift.load_shift_requests', {}, {}, function(response) {
                    if(response.success) {
                        let shift_requests = [];

                        for(let i = 0; i < response.shifts.length; i++) {
                            let shift = response.shifts[i];
                            shift_requests.push({
                                id: shift.id,
                                nurse_id: shift.nurse_id,
                                nurse_name: shift.nurse_name,
                                nurse_profile: shift.nurse_profile,
                                provider_id: shift.provider_id,
                                provider_name: shift.provider_name,
                                provider_profile: shift.provider_profile,
                                is_recurrence: shift.is_recurrence,
                                shift_name: shift.shift_name,
                                start_time: shift.start_time,
                                end_time: shift.end_time,
                                date: shift.date,
                                shift_route: shift.shift_route
                            })
                        }
                        this.shift_requests = shift_requests;
                    } else {
                        console.log('Error');
                        console.log(response);
                    }
                }.bind(this), function(response) {
                    console.log('Failed');
                    console.log(response);
                });
            },
            loadFilters() {
                modRequest.request('sa.shift.load_calendar_filters', {}, {}, function(response) {
                    if(response.success) {
                        this.providers = response.providers;
                        this.nurses = response.nurses;
                    } else {
                        console.log('Error');
                        console.log(response);
                    }
                }.bind(this), function(response) {
                    console.log('Failed');
                    console.log(response);
                });
            }
        }
    });
});
