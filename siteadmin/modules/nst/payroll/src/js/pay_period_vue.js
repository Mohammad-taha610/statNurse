window.addEventListener('load', function() {
    Vue.component('vue-timepicker', window.VueTimepicker.default);
    Vue.component('pay-period-view', {
        template: /*html*/`
            <div class="container-fluid" id="pay-period-container">
                <div class="row">
                    <div class="col-12">
                        <v-app>
                            <div class="card">
                                <div class="card-header">
                                    <div class="row">
                                        <div class="col-12 col-sm-8">
                                            <h4 class="card-title">Pay Period</h4>
                                        </div>
                                        <div class="col-12 col-sm-4">
                                            <div class="row">
                                                <div class="col-12">
                                                    <v-spacer></v-spacer>
                                                    <v-select
                                                        v-model="pay_period"
                                                        :items="pay_periods"
                                                        item-text="display"
                                                        item-value="combined"
                                                        label="Pay Period"
                                                        @change="updateTables"
                                                        dense
                                                    ></v-select>
                                                </div>
                                            </div>
                                            <div class="row text-right">
                                                <div class="col-12">
                                                    <span class="pr-3">Unresolved Payments Only</span>
                                                    <v-checkbox
                                                        color="primary"
                                                        v-model="unresolved_only"
                                                        @change="updateTables"
                                                    ></v-checkbox>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <v-row class="hidden-sm-and-up"> 
                                        <v-col cols="12"> 
                                            <v-autocomplete
                                                v-model="search"
                                                :items="searchable_nurses"
                                                item-text="name"
                                                item-value="name"
                                                label="Search for a Nurse"
                                                clearable
                                                width="300"
                                            ></v-autocomplete>
                                        </v-col>
                                    </v-row>
                                    <v-tabs v-model="tab">
                                        <v-tab class="black--text">Shifts</v-tab>
                                        <v-tab class="black--text">Nurses</v-tab>
                                        <v-spacer class="hidden-xs-only"></v-spacer>
                                        <v-autocomplete
                                            v-model="search"
                                            :items="searchable_nurses"
                                            item-text="name"
                                            item-value="name"
                                            label="Search for a Nurse"
                                            clearable
                                            width="300"
                                            class="hidden-xs-only"
                                        ></v-autocomplete>
                                    </v-tabs>
                                    <v-tabs-items v-model="tab" touchless>
                                        <v-tab-item>
                                            <div class="table-responsive">
                                                <v-card>
                                                    <v-card-text>
                                                        <v-data-table
                                                            class="table table-responsive-md"
                                                            :headers="shift_headers"
                                                            :items="shift_payments"
                                                            :search="search"
                                                            :key="updateKey"
                                                            :custom-filter="paymentFilter"
                                                            multi-sort
                                                        >
                                                            <template v-slot:item.nurse_name="{ item }">
                                                                <a v-bind:href="item.nurse_route" class="blue--text" target="_blank">
                                                                    {{ item.nurse_name }}
                                                                </a>
                                                            </template>
                                                            <template v-slot:item.shift_time="{ item }">
                                                                <span class="mt-1 block grey--text">{{item.date}}</span>
                                                                <span class="mt block">{{item.shift_time}}</span>
                                                            </template>
                                                            <template v-slot:item.clocked_hours="{ item }">
                                                                <span v-if="item.type != 'Bonus'" class="mt-1 block grey--text">{{item.clocked_hours}} hours</span>
                                                                <span v-if="item.type != 'Bonus'"class="mt block">{{item.clock_times}}</span>
                                                            </template>
                                                            <template v-slot:item.rate="{ item }">
                                                                <span v-if="item.type != 'Bonus'">{{'$' + item.rate}}</span>
                                                            </template>
                                                            <template v-slot:item.amount="{ item }">
                                                                <span>{{'$' + item.amount}}</span>
                                                            </template>
                                                            <template v-slot:item.type="{ item }">
                                                                {{item.type}}
                                                                <v-tooltip v-if="item.type == 'Bonus' && item.description" bottom :open-on-click="true" nudge-left="50" >
                                                                    <template v-slot:activator="{on, attrs}">
                                                                        <v-icon v-bind="attrs" v-on="on">mdi-chat</v-icon>
                                                                    </template>
                                                                    <span>{{item.description}}</span>
                                                                </v-tooltip>
                                                            </template>
                                                            <template v-slot:item.status="{ item }">
                                                                <span :class="getStatusColorClass(item)">{{item.status}}</span>
                                                            </template>
                                                            <template v-slot:item.actions="{ item }">
                                                                <v-dialog max-width="400" v-show="item.status == 'Unresolved'">
                                                                    <template v-slot:activator="{ on, attrs }">
                                                                        <v-btn 
                                                                            v-on="on"
                                                                            v-bind="attrs"
                                                                            color="success"
                                                                            v-show="item.status == 'Unresolved'"
                                                                            >Approve</v-btn>
                                                                    </template>
                                                                    <template v-slot:default="dialog">
                                                                        <v-card>
                                                                            <v-toolbar
                                                                                color="success"
                                                                                class="text-h4 white--text"
                                                                            >Approve Payment</v-toolbar>
                                                                            <v-card-text
                                                                                class="pt-5"
                                                                            >Do you wish to <strong class="success--text">APPROVE</strong> payment for this shift?</v-card-text>
                                                                            <v-card-actions>
                                                                                <v-spacer></v-spacer>
                                                                                <v-btn
                                                                                    color="light"
                                                                                    v-on:click="dialog.value = false"
                                                                                >Cancel
                                                                                </v-btn>
                                                                                <v-btn
                                                                                    color="success"
                                                                                    v-on:click="approvePayment(item); dialog.value = false;"
                                                                                    class="white--text"
                                                                                >Approve</v-btn>
                                                                            </v-card-actions>
                                                                        </v-card>
                                                                    </template>
                                                                </v-dialog>
                                                                <v-dialog max-width="400">
                                                                    <template v-slot:activator="{ on, attrs }">
                                                                        <v-btn
                                                                            v-on="on"
                                                                            v-bind="attrs"
                                                                            :color="item.status == 'Unresolved' ? 'primary' : 'grey lighten-3'"
                                                                            >Request Change</v-btn>
                                                                    </template>
                                                                    <template v-slot:default="dialog">
                                                                        <v-card>
                                                                            <v-toolbar
                                                                                color="primary"
                                                                                class="text-h4 white--text"
                                                                            >Request Change</v-toolbar>
                                                                            <v-card-text
                                                                                class="pt-5"
                                                                            >Please enter a description below of which details of this payment should be changed and why.
                                                                            <vue-timepicker 
                                                                                v-model="item.request_clock_in"
                                                                                input-width="100%"
                                                                                format="HH:mm"
                                                                                input-class="custom-timepicker"
                                                                                placeholder="Clock In Time"
                                                                                manual-input
                                                                                ></vue-timepicker>
                                                                            <vue-timepicker 
                                                                                v-model="item.request_clock_out"
                                                                                input-width="100%"
                                                                                format="HH:mm"
                                                                                input-class="custom-timepicker"
                                                                                placeholder="Clock Out Time"
                                                                                manual-input
                                                                                ></vue-timepicker>
                                                                            <v-textarea v-model="item.request_description" label="Description (Required)"> 
                                                                            </v-textarea>
                                                                            </v-card-text>
                                                                            <v-card-actions>
                                                                                <v-spacer></v-spacer>
                                                                                <v-btn
                                                                                    color="light"
                                                                                    v-on:click="dialog.value = false"
                                                                                >Cancel
                                                                                </v-btn>
                                                                                <v-btn
                                                                                    color="primary"
                                                                                    v-on:click="requestChange(item); dialog.value = false;"
                                                                                    class="white--text"
                                                                                >Submit Request</v-btn>
                                                                            </v-card-actions>
                                                                        </v-card>
                                                                    </template>
                                                                </v-dialog>
                                                                <v-dialog max-width="400" v-if="item.status == 'Change Requested' && isInCurrentPayPeriod(item)" >
                                                                    <template v-slot:activator="{ on, attrs }">
                                                                        <v-btn
                                                                            v-on="on"
                                                                            v-bind="attrs"
                                                                            color="warning"
                                                                            >Cancel Request</v-btn>
                                                                    </template>
                                                                    <template v-slot:default="dialog">
                                                                        <v-card>
                                                                            <v-toolbar
                                                                                color="warning"
                                                                                class="text-h4 white--text"
                                                                            >Cancel Request</v-toolbar>
                                                                            <v-card-text
                                                                                class="pt-5"
                                                                            >Are you sure you would like to <strong class="warning--text">CANCEL</strong> this change request?
                                                                            </v-card-text>
                                                                            <v-card-actions>
                                                                                <v-spacer></v-spacer>
                                                                                <v-btn
                                                                                    color="light"
                                                                                    v-on:click="dialog.value = false"
                                                                                >No
                                                                                </v-btn>
                                                                                <v-btn
                                                                                    color="warning"
                                                                                    v-on:click="cancelChangeRequest(item); dialog.value = false;"
                                                                                    class="white--text"
                                                                                >Yes, Cancel Request</v-btn>
                                                                            </v-card-actions>
                                                                        </v-card>
                                                                    </template>
                                                                </v-dialog>
                                                            </template>
                                                        </v-data-table>
                                                    </v-card-text>
                                                </v-card>
                                            </div>
                                        </v-tab-item>
                                        <v-tab-item> 
                                            <div class="table-responsive">
                                                <v-card>
                                                    <v-card-text>
                                                        <v-data-table
                                                            class="table table-responsive-md"
                                                            :headers="nurse_headers"
                                                            :items="nurse_payments"
                                                            :search="search"
                                                            :custom-filter="paymentFilter"
                                                            multi-sort
                                                        >
                                                            <template v-slot:item.nurse_name="{ item }">
                                                                <a v-bind:href="item.nurse_route" class="blue--text" target="_blank">
                                                                    {{ item.nurse_name }}
                                                                </a>
                                                            </template>
                                                            <template v-slot:item.clocked_hours="{ item }">
                                                                <span>{{item.clocked_hours}}</span>
                                                            </template>
                                                            <template v-slot:item.rate="{ item }">
                                                                <span>{{'$' + item.rate}}</span>
                                                            </template>
                                                            <template v-slot:item.amount="{ item }">
                                                                <span>{{'$' + item.amount}}</span>
                                                            </template>
                                                            <template v-slot:item.bonus_amount="{ item }">
                                                                <span>{{'$' + item.bonus_amount}}</span>
                                                            </template>
                                                            <template v-slot:item.has_unresolved_payments="{ item }">
                                                                <span>{{item.has_unresolved_payments}}</span>
                                                            </template>
                                                        </v-data-table>
                                                    </v-card-text>
                                                </v-card>
                                            </div>
                                        </v-tab-item>
                                    </v-tabs-items>
                                </div>
                            </div>
                        </v-app>
                    </div>
                </div>
            </div>`,
        props: [
            'provider_id',
            'show_unresolved_only',
            'period'
        ],
        data () {
            return {
                tab: null,
                nurse_headers: [
                    {
                        text: 'Nurse Name',
                        align: 'start',
                        sortable: true,
                        value: 'nurse_name'
                    },
                    {
                        text: 'Hourly Rate',
                        sortable: true,
                        value: 'rate'
                    },
                    {
                        text: 'Clocked Hours',
                        sortable: true,
                        value: 'clocked_hours'
                    },
                    {
                        text: 'Has Unresolved Payments',
                        sortable: true,
                        value: 'has_unresolved_payments'
                    },
                    {
                        text: 'Bonus Total',
                        sortable: true,
                        value: 'bonus_amount'
                    },
                    {
                        text: 'Payment Total',
                        sortable: true,
                        value: 'amount'
                    },
                ],
                shift_headers: [
                    {
                        text: 'Nurse Name',
                        align: 'start',
                        sortable: true,
                        value: 'nurse_name'
                    },
                    {
                        text: 'Shift Time',
                        sortable: true,
                        value: 'shift_time'
                    },
                    {
                        text: 'Clocked Hours',
                        sortable: true,
                        value: 'clocked_hours'
                    },
                    {
                        text: 'Hourly Rate',
                        sortable: true,
                        value: 'rate'
                    },
                    {
                        text: 'Amount',
                        sortable: true,
                        value: 'amount'
                    },
                    {
                        text: 'Type',
                        sortable: true,
                        value: 'type'
                    },
                    {
                        text: 'Status',
                        sortable: true,
                        value: 'status'
                    },
                    {
                        text: 'Actions',
                        sortable: true,
                        value: 'actions'
                    }
                ],
                colors: {
                    'Resolved': 'success',
                    'Approved': 'success',
                    'Change Requested': 'warning',
                    'Unresolved': 'red',
                },
                shift_payments: [
                ],
                nurse_payments: [
                ],
                pay_periods: [],
                pay_period: '',
                unresolved_only: false,
                search: '',
                searchable_nurses: [],
                updateKey: 0,
            }
        },
        computed: {
        },
        mounted: function() {
            this.getPayPeriods();
            // this.getShiftPayments();
            // this.getNursePayments();
        },
        methods: {
            isInCurrentPayPeriod : function(payment) {
                var paydate = new Date(payment.date);
                if(paydate >= this.pay_periods[1].start && paydate <= this.pay_periods[1].end) {
                    return true;
                }
                return false;
            },
            paymentFilter(value, search, item) {
                return (item.nurse_name != null &&
                        search != null &&
                        item.nurse_name.toString().indexOf(search) !== -1);
            },
            updateTables() {
                this.getShiftPayments();
                this.getNursePayments();
            },
            getStatusColorClass(item) {
                return this.colors[item.status] + '--text';
            },
            getPayPeriods() {
                let data = {
                    provider_id: this.provider_id
                };

                modRequest.request('payroll.get_pay_periods', {}, data, function(response) {
                    if(response.success) {

                        let allStart = new Date(response.periods[2].start.date);
                        let allEnd = new Date(response.periods[1].end.date);
                        let currentCombined = response.periods[1].combined;
                        let pastCombined = response.periods[2].combined;
                        let allCombined = this.getAllCombined(pastCombined, currentCombined);
                        
                        this.pay_periods.push({
                            start: allStart,
                            end: allEnd,
                            display: response.periods[0].display,
                            combined: allCombined
                        });

                        for (let i = 1; i < 3; i++) {
                            let period = response.periods[i];
                            let start = new Date(period.start.date);
                            let end = new Date(period.end.date);

                            this.pay_periods.push({
                                start: start,
                                end: end,
                                display: period.display,
                                combined: period.combined
                            })
                        }

                        // current pay period
                        this.pay_period = this.pay_periods[1].combined;

                        // FOR TESTING ONLY
                        // console.log("A period from the backend: ", response.periods[2], "\npay_periods version of the item: ", this.pay_periods[2])
                        // this.pay_periods.push({
                        //     start: new Date("2022-06-13 05:00:00.000000"),
                        //     end: new Date("2022-06-19 05:00:00.000000"),
                        //     display: 'testing',
                        //     combined: '20220613_20220619'
                        // })
                        // FOR TESTING ONLY

                        this.getSearchableNurses();
                        this.getShiftPayments();
                        this.getNursePayments();
                    } else {
                        console.log('Error');
                        console.log(response);
                    }
                }.bind(this), function(response) {
                    console.log('Failed');
                    console.log(response);
                });
            },
            getSearchableNurses() {
                let data = {
                    provider_id: this.provider_id
                };
                modRequest.request('provider.load_assignable_nurses', {}, data, function(response) {
                    if(response.success) {
                        if(response.nurses){
                            let searchable_nurses= [];
                            for(var i = 0; i < response.nurses.length; i++) {
                                var nurse = response.nurses[i];
                                var searchable_nurse = {
                                    id: nurse.id,
                                    name: nurse.name
                                };
                                searchable_nurses.push(searchable_nurse);
                            }
                            this.searchable_nurses = searchable_nurses;
                        }
                    } else {
                        console.log('Error');
                        console.log(response);
                    }
                }.bind(this), function(response) {
                    console.log('Failed');
                    console.log(response);
                });
            },
            getShiftPayments() {
                let data = {
                    provider_id: this.provider_id,
                    pay_period: this.pay_period,
                    unresolved_only: this.unresolved_only
                };

                modRequest.request('payroll.get_shift_payments', {}, data, function(response) {
                    this.shift_payments = [];
                    if(response.success) {
                        if(response.shift_payments) {
                            for (let i = 0; i < response.shift_payments.length; i++) {
                                let payment = response.shift_payments[i];
                                this.shift_payments.push({
                                    nurse_name: payment.nurse_name,
                                    nurse_route: payment.nurse_route,
                                    rate: parseFloat(payment.bill_rate).toFixed(2),
                                    payment_id: payment.payment_id,
                                    shift_name: payment.shift_name,
                                    shift_time: payment.shift_time,
                                    shift_route: payment.shift_route,
                                    status: payment.status,
                                    clocked_hours: parseFloat(payment.clocked_hours).toFixed(2),
                                    clock_times: payment.clock_times,
                                    date: payment.date,
                                    amount: parseFloat(payment.bill_amount).toFixed(2),
                                    description: payment.description,
                                    type: payment.type,
                                    request_description: '',
                                    request_clock_in: '',
                                    request_clock_out: ''
                                });
                            }
                        }
                    } else {
                        console.log('Error');
                        console.log(response);
                    }
                }.bind(this), function(response) {
                    console.log('Failed');
                    console.log(response);
                });
            },
            getNursePayments() {
                let data = {
                    provider_id: this.provider_id,
                    pay_period: this.pay_period,
                    unresolved_only: this.unresolved_only
                };
                
                modRequest.request('payroll.get_nurse_payments', {}, data, function(response) {
                    this.nurse_payments = [];
                    if(response.success) {
                        for(var k in response.nurse_payments) {
                            let payment = response.nurse_payments[k];
                            this.nurse_payments.push({
                                nurse_name: payment.nurse_name,
                                nurse_route: payment.nurse_route,
                                clocked_hours: parseFloat(payment.clocked_hours).toFixed(2),
                                amount: parseFloat(payment.amount).toFixed(2),
                                bonus_amount: parseFloat(payment.bonus_amount).toFixed(2),
                                rate: parseFloat(payment.rate).toFixed(2),
                                has_unresolved_payments: payment.has_unresolved_payments
                            });
                        }
                    } else {
                        console.log('Error');
                        console.log(response);
                    }
                }.bind(this), function(response) {
                    console.log('Failed');
                    console.log(response);
                });
            },
            approvePayment(payment) {
                let data = {
                    payment_id: payment.payment_id
                }

                modRequest.request('payroll.resolve_payment', {}, data, function(response) {
                    if(response.success) {
                        payment.status = 'Resolved'
                        this.updateKey += 1;
                    } else {
                        console.log('Error');
                        console.log(response);
                    }
                }, function(response) {
                    console.log('Failed');
                    console.log(response);
                });
            },
            requestChange(payment) {
                let data = {
                    payment_id: payment.payment_id,
                    request_description: payment.request_description,
                    request_clock_in: payment.request_clock_in,
                    request_clock_out: payment.request_clock_out
                }

                modRequest.request('payroll.request_change', {}, data, function(response) {
                    if(response.success) {
                        payment.status = 'Change Requested'
                        this.updateKey += 1;
                    } else {
                        console.log('Error');
                        console.log(response);
                    }
                }, function(response) {
                    console.log('Failed');
                    console.log(response);
                });
            },
            resolvePayment(payment) {
                var data = {
                    payment_id: payment.payment_id
                }

                modRequest.request('payroll.resolve_payment', {}, data, function(response) {
                    if(response.success) {
                        payment.status = 'Resolved'
                        this.updateKey += 1;
                    } else {
                        console.log('Error');
                        console.log(response);
                    }
                }, function(response) {
                    console.log('Failed');
                    console.log(response);
                });
            },
            cancelChangeRequest(payment) {
                var data = {
                    payment_id: payment.payment_id
                }

                modRequest.request('payroll.cancel_change_request', {}, data, function(response) {
                    if(response.success) {
                        payment.status = 'Unresolved';
                        this.updateKey += 1;
                    } else {
                        console.log('Error');
                        console.log(response);
                    }
                }, function(response) {
                    console.log('Failed');
                    console.log(response);
                });
            },
            getAllCombined(past, current) {
                let allCombinedStart = past.split("_");
                let allCombinedEnd = current.split("_");

                return allCombinedStart[0] +"_"+ allCombinedEnd[1];
            }
        },
    });
});
