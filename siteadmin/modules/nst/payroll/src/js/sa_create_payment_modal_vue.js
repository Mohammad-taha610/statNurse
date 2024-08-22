Vue.component('sa-create-payment-modal', {
    // language=HTML
    template:
    /*html*/`
        <v-dialog v-model="visible" max-width="1000">
            <template v-slot:activator="{ on, attrs }">
                <v-btn
                        v-on="on"
                        v-bind="attrs"
                        color="primary">
                    <slot></slot>
                </v-btn>
            </template>
            <template v-slot:default="dialog">
                <v-card v-if="dialog.value">
                    <v-toolbar
                            color="primary"
                            class="text-h5 white--text"
                    >Add Payment</v-toolbar>
                    <v-card-text class="pt-5">
                        <nst-overlay :loading="loading"></nst-overlay>
                        <div class="row">
                            <div class="col-xs-12 col-md-6 pt-0 pb-0">
                                <v-autocomplete
                                        v-model="provider"
                                        :items="providers"
                                        item-text="name"
                                        item-value="id"
                                        label="Provider"
                                        @change="onProviderChanged(); parent_dialog = dialog"
                                        return-object
                                        clearable
                                ></v-autocomplete>
                            </div>
                            <div class="col-xs-12 col-md-6 pt-0 pb-0">
                                <v-menu
                                        ref="dateMenu"
                                        v-model="dateMenu"
                                        :close-on-content-click="false"
                                        :nudge-right="40"
                                        transition="scale-transition"
                                        max-width="290px"
                                        min-width="290px"
                                        attach
                                        :offset-y="true"
                                >
                                    <template v-slot:activator="{ on, attrs }">
                                        <v-text-field
                                                autocomplete="off"
                                                v-model="form.shift_date"
                                                label="Date"
                                                v-bind="attrs"
                                                v-on="on"
                                        ></v-text-field>
                                    </template>
                                    <v-date-picker
                                            v-if="dateMenu"
                                            v-model="form.shift_date"
                                            no-title
                                            scrollable
                                    >
                                    </v-date-picker>
                                </v-menu>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-xs-12 col-md-6 pt-0 pb-0">
                                <v-autocomplete
                                        v-model="nurse"
                                        :items="nurses"
                                        item-text="name"
                                        item-value="id"
                                        return-object
                                        label="Nurse"
                                        clearable
                                        @change="onNurseChanged"
                                ></v-autocomplete>
                            </div>
                            <div class="col-xs-12 col-md-6 pt-0 pb-0">
                                <v-select
                                    v-model="form.payment_method"
                                    :items="['Direct Deposit', 'Pay Card', 'Paper Check', 'Checkr Pay', 'Checkr Pay DD']"
                                    label="Payment Method"></v-select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-xs-12 col-md-6 pt-0">
                                <label class="payment-clock-label">Clock In</label>
                                <vue-timepicker
                                        :key="timepickerKey1"
                                        v-model="form.clock_in"
                                        input-width="100%"
                                        format="hh:mm A"
                                        input-class="custom-timepicker"
                                        placeholder="12:00 AM"
                                        @change="calculatePayTotal(); calculateBillTotal()"
                                        manual-input
                                ></vue-timepicker>
                            </div>
                            <div class="col-xs-12 col-md-6 pt-0">
                                <label class="payment-clock-label">Clock Out</label>
                                <vue-timepicker
                                        :key="timepickerKey2"
                                        v-model="form.clock_out"
                                        input-width="100%"
                                        format="hh:mm A"
                                        input-class="custom-timepicker"
                                        placeholder="12:00 AM"
                                        @change="calculatePayTotal(); calculateBillTotal()"
                                        manual-input
                                ></vue-timepicker>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-xs-12 col-md-6 pt-8">
                                <v-select
                                        dense
                                        @change="calculatePayTotal"
                                        v-model="form.lunch_minutes"
                                        :items="[0, 15, 30, 60]"
                                        label="Lunch"></v-select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-xs-12 col-md-6 pt-0">
                                <v-text-field
                                        @change="calculatePayTotal"
                                        v-model="form.pay_rate"
                                        label="Pay Rate"></v-text-field>
                            </div>
                            <div class="col-xs-12 col-md-6 pt-0">
                                <v-text-field
                                        @change="calculateBillTotal"
                                        v-model="form.bill_rate"
                                        label="Bill Rate"></v-text-field>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-xs-12 col-md-6 pt-0">
                                <v-text-field
                                        @change="calculatePayTotal(); calculateBillTotal()"
                                        @input="form.bill_bonus = form.pay_bonus"
                                        v-model="form.pay_bonus"
                                        label="Pay Bonus"></v-text-field>
                            </div>
                            <div class="col-xs-12 col-md-6 pt-0">
                                <v-text-field
                                        @change="calculateBillTotal"
                                        v-model="form.bill_bonus"
                                        label="Bill Bonus"></v-text-field>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-xs-12 col-md-6 pt-0">
                                <v-text-field
                                        @change="calculatePayTotal(); calculateBillTotal()"
                                        v-model="form.pay_holiday"
                                        label="Pay Holiday"></v-text-field>
                            </div>
                            <div class="col-xs-12 col-md-6 pt-0">
                                <v-text-field
                                        @change="calculateBillTotal"
                                        v-model="form.bill_holiday"
                                        label="Bill Holiday"></v-text-field>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-xs-12 col-md-6 pt-0">
                                <v-checkbox
                                        @change="calculatePayTotal(); form.bill_travel_checked = form.pay_travel_checked ? form.pay_travel_checked : form.bill_travel_checked; calculateBillTotal()"
                                        v-model="form.pay_travel_checked"
                                        color="primary"
                                ></v-checkbox>
                                <span>Pay Travel </span><span v-if="form.pay_travel_checked">({{'$' + form.pay_travel}})</span>
                            </div>
                            <div class="col-xs-12 col-md-6 pt-0">
                                <v-checkbox
                                        @change="calculateBillTotal"
                                        v-model="form.bill_travel_checked"
                                        color="primary"
                                ></v-checkbox>
                                <span>Bill Travel </span><span v-if="form.bill_travel_checked">({{'$' + form.bill_travel}})</span>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-xs-12 pt-0">
                                <label class="black--text">Clocked Hours:</label>
                                <p>{{form.clocked_hours}}</p>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-xs-12 col-md-6 pt-0">
                                <label class="black--text">Pay Total:</label>
                                <p>{{'$' + form.pay_total}}</p>
                            </div>
                            <div class="col-xs-12 col-md-6 pt-0">
                                <label class="black--text">Bill Total:</label>
                                <p>{{'$' + form.bill_total}}</p>
                            </div>
                        </div>
                        
                    </v-card-text>
                    <v-card-actions>
                        <v-spacer></v-spacer>
                        <v-btn
                            color="light"
                            @click="dialog.value = false">Cancel</v-btn>
                        <v-dialog max-width="300">
                            <template v-slot:activator="{ on, attrs }">
                                <v-btn
                                        v-show="show_modal"
                                        color="primary"
                                        :disabled="save_disabled"
                                        v-on="on"
                                        v-bind="attrs">Save</v-btn>
                            </template>
                            <template v-slot:default="dialog">
                                <v-card>
                                    <v-toolbar color="primary" class="text-h4 white--text">
                                        Are you sure?
                                    </v-toolbar>
                                    <v-card-text class="pt-5">
                                        There is already a shift for this nurse on this date:<br>
                                        Clock In: {{already_clocked_in_time}}<br>
                                        Clock Out: {{already_clocked_out_time}}
                                    </v-card-text>
                                    <v-card-actions class="justify-end">
                                        <v-btn
                                                text
                                                color="grey dark-3"
                                                v-on:click="dialog.value = false">Cancel</v-btn>
                                        <v-btn
                                                color="primary"
                                                class="white--text"
                                                @click="savePayment(); dialog.value = false; parent_dialog.value = false;"
                                        >Yes, Save</v-btn>
                                    </v-card-actions>
                                </v-card>
                            </template>
                        </v-dialog>
                        <v-btn
                                v-if="!show_modal"
                                color="primary"
                                :disabled="save_disabled"
                                @click="savePayment(); dialog.value = false;">Save</v-btn>
                    </v-card-actions>
                </v-card>
            </template>
        </v-dialog>
    `,
    props: [],
    data: function() {
        return {
            parent_dialog: null,
            already_clocked_in_time: '',
            already_clocked_out_time: '',
            has_conflicting_payments: false,
            show_modal: false,
            save_disabled: false,
            timepickerKey1: 0,
            timepickerKey2: 0,
            timepickerKey3: 0,
            timepickerKey4: 0,
            visible: false,
            nurses_loading: false,
            providers_loading: false,
            loading: false,
            dateMenu: false,
            nurse: null,
            provider: null,
            nurses: [],
            providers: [],
            form: {
                nurse_id: '',
                provider_id: '',
                shift_date: '',
                clock_in: {},
                clock_out: {},
                lunch_minutes: 0,
                clock_in_string: '',
                clock_out_string: '',
                lunch_start_string: '',
                lunch_end_string: '',
                clocked_hours: 0,
                pay_rate: null,
                bill_rate: null,
                pay_bonus: null,
                bill_bonus: null,
                pay_travel: null,
                bill_travel: null,
                pay_holiday: null,
                bill_holiday: null,
                pay_travel_checked: false,
                bill_travel_checked: false,
                pay_total: 0.00,
                bill_total: 0.00
            }
        };
    },
    created() {
        Object.assign(this.$data, this.$options.data.apply(this));
        this.loading = true;
        this.nurses_loading = true;
        this.providers_loading = true;
        this.loadProviders();
        this.loadNurses();
    },
    mounted() {
    },
    computed: {

    },
    methods: {
        loadProviders() {
            modRequest.request('sa.shift.load_providers', {}, {}, function(response) {
                if(response.success) {
                    this.providers = response.providers;
                    this.providers_loading = false;
                    this.loading = this.providers_loading || this.nurses_loading;
                } else {
                    console.log('Error');
                    console.log(response);
                }
            }.bind(this), function(response) {
                console.log('Failed');
                console.log(response);
            });
        },
        loadNurses() {
            modRequest.request('sa.shift.load_nurses', {}, {}, function(response) {
                if(response.success) {
                    this.nurses = response.nurses;
                    this.nurses_loading = false;
                    this.loading = this.providers_loading || this.nurses_loading;
                } else {
                    console.log('Error');
                    console.log(response);
                }
            }.bind(this), function(response) {
                console.log('Failed');
                console.log(response);
            });
        },
        onNurseChanged() {
            this.form.nurse_id = this.nurse != null ? this.nurse.id : null;
            this.form.payment_method = this.nurse.payment_method;
            if(this.form.provider_id && this.form.shift_date) {
                this.findConflictingPayments();
            }

            if (this.nurse.credentials === 'CMT') {
                this.form.pay_rate = this.provider.pay_rates.CMT.standard_pay;
                this.form.bill_rate = this.provider.pay_rates.CMT.standard_bill;
            } else if (this.nurse.credentials === 'CNA') {
                this.form.pay_rate = this.provider.pay_rates.CNA.standard_pay;
                this.form.bill_rate = this.provider.pay_rates.CNA.standard_bill;
            } else if (this.nurse.credentials === 'LPN') {
                this.form.pay_rate = this.provider.pay_rates.LPN.standard_pay;
                this.form.bill_rate = this.provider.pay_rates.LPN.standard_bill;
            } else if (this.nurse.credentials === 'RN') {
                this.form.pay_rate = this.provider.pay_rates.RN.standard_pay;
                this.form.bill_rate = this.provider.pay_rates.RN.standard_bill;
            }
            // Don't auto check it.
            // if (this.provider.uses_travel_pay) {
            //     this.form.pay_travel_checked = true;
            //     this.form.bill_travel_checked = true;
            // }
            this.form.pay_bonus = "0.00";
            this.form.bill_bonus = "0.00";
            this.form.pay_travel = "0.00";
            this.form.bill_travel = "0.00";
            this.form.pay_holiday = "0.00";
            this.form.bill_holiday = "0.00";
        },
        onProviderChanged() {
            if(this.form.nurse_id && this.form.shift_date) {
                this.findConflictingPayments();
            }
            this.form.provider_id = this.provider.id;
        },
        calculatePayTotal() {
            this.calculateClockedHours();
            var total = (parseFloat(this.form.clocked_hours ?? 0) * parseFloat(this.form.pay_rate ?? 0)) + parseFloat(this.form.pay_bonus ?? 0) + parseFloat(this.form.pay_travel ?? 0) + parseFloat(this.form.pay_holiday ?? 0);
            this.form.pay_total = total.toFixed(2);
        },
        calculateBillTotal() {
            this.calculateClockedHours();
            var total = (parseFloat(this.form.clocked_hours ?? 0) * parseFloat(this.form.bill_rate ?? 0)) + parseFloat(this.form.bill_bonus ?? 0) + parseFloat(this.form.bill_travel ?? 0) + parseFloat(this.form.bill_holiday ?? 0);
            this.form.bill_total = total.toFixed(2);
        },
        calculateClockedHours() {
            this.form.pay_travel = this.form.pay_travel_checked ? this.form.pay_rate * 2 : 0;
            this.form.bill_travel = this.form.bill_travel_checked ? this.form.bill_rate * 2 : 0;

            this.form.clock_in_string = this.form.clock_in.hh + ':' + this.form.clock_in.mm + ':00';
            this.form.clock_out_string = this.form.clock_out.hh + ':' + this.form.clock_out.mm + ':00';
            var clockIn = new Date('2021-01-01T' + this.form.clock_in_string)
            if(this.form.clock_in.A == 'PM') {
                clockIn.setHours(clockIn.getHours() + 12);
            }
            if(this.form.clock_in.hh == 12) {
                clockIn.setHours(clockIn.getHours() - 12);
            }

            var clockOut = new Date('2021-01-01T' + this.form.clock_out_string)
            if(this.form.clock_out.A == 'PM') {
                clockOut.setHours(clockOut.getHours() + 12);
            }
            if(this.form.clock_out.hh == 12) {
                clockOut.setHours(clockOut.getHours() - 12);
            }
            if(clockOut < clockIn) {
                clockOut.setHours(clockOut.getHours() + 24);
            }

            this.form.clocked_hours = ((clockOut - clockIn - (this.form.lunch_minutes*1000*60)) / (1000 * 60 * 60)).toFixed(2)
        },
        savePayment() {
            var data = {
                form: this.form
            }

            modRequest.request('sa.payroll.create_payment', {}, data, function(response) {
                if(response.success) {
                    this.$emit('paymentCreated');
                } else {
                    console.log('Error');
                    console.log(response);
                }
            }.bind(this), function(response) {
                console.log('Failed');
                console.log(response);
            });
        },
        findConflictingPayments() {
            var data = {
                provider_id: this.form.provider_id,
                nurse_id: this.form.nurse_id,
                date: this.form.shift_date,
            }

            this.save_disabled = true;
            this.show_modal = false;
            modRequest.request('sa.payroll.find_conflicting_payments', {}, data, function(response) {
                if(response.success) {
                    if(response.has_conflicting_payments) {
                        this.show_modal = true;
                        this.already_clocked_in_time = response.already_clocked_in_time;
                        this.already_clocked_out_time = response.already_clocked_out_time;
                    }
                    this.save_disabled = false;
                } else {
                    console.log('Error');
                    console.log(response);
                }
            }.bind(this), function(response) {
                console.log('Failed');
                console.log(response);
            });
        }

    },
    watch: {
        visible(v) {
            if(v) {
                Object.assign(this.$data, this.$options.data.apply(this));
                this.loading = true;
                this.loadProviders();
                this.loadNurses();
            }
        },
    }
});