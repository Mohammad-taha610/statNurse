window.addEventListener('load', function() {
    // Vue.component('vue-timepicker', window.VueTimepicker.default);
    Vue.component('review-shifts', {
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
                                                    dense
                                                ></v-select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <v-card-title>
                                    <v-text-field
                                        style="margin-right: 50px;"
                                        v-model="search"
                                        append-icon="mdi-magnify"
                                        label="Search"
                                        single-line
                                        hide-details
                                    ></v-text-field> 
                                </v-card-title>                     
                                <v-data-table
                                    class="table table-responsive-md"
                                    :headers="headers"
                                    :items="line_items"
                                    :sort-by="['nurse_name', 'date']"
                                    :search="search"
                                    multi-sort
                                >
                                    <template v-slot:item.nurse_name="{ item }">
                                        <a v-bind:href="item.nurse_route" class="blue--text" target="_blank">
                                            {{ item.nurse_name }}
                                        </a>
                                    </template>
                                    <template v-slot:item.rate="{ item }">
                                        <span>{{ item.credentials }}</span>
                                    </template>
                                    <template v-slot:item.bill_rate="{ item }">
                                        <span v-if="item.bill_rate[0].includes('-')" style="color: red;" class="mt block">{{item.bill_rate[0]}}</span>
                                        <span v-else class="mt block">{{item.bill_rate[0]}}</span>

                                        <span v-if="item.bill_rate[1]">
                                            <span class="mt-1 block grey--text" v-if="item.bill_rate[1].includes('-')" style="color: red;">{{item.bill_rate[1]}}</span>
                                            <span class="mt-1 block grey--text" v-else>{{item.bill_rate[1]}}</span>
                                        </span>
                                    </template>
                                    <template v-slot:item.clocked_hours="{ item }">
                                        <span class="mt block" v-if="item.clocked_hours[0] > 0">{{ item.clocked_hours[0] }} hours</span>
                                        <span class="mt block" v-else style="color: red;">{{ item.clocked_hours[0] }} hours</span>

                                        <span v-if="item.clocked_hours[1]">
                                            <span class="mt-1 block grey--text" v-if="item.clocked_hours[1] > 0">{{ item.clocked_hours[1] }} hours</span>
                                            <span class="mt-1 block grey--text" v-else style="color: red;">{{ item.clocked_hours[1] }} hours</span>
                                        </span>
                                    </template>
                                    <template v-slot:item.bonus="{ item }">
                                        <span v-if="item.bonus.includes('-')" style="color: red;">{{ item.bonus }}</span>
                                        <span v-else>{{ item.bonus }}</span>
                                    </template>
                                    <template v-slot:item.travel_pay="{ item }">
                                        <span v-if="item.travel_pay.includes('-')" style="color: red;">{{ item.travel_pay }}</span>
                                        <span v-else>{{ item.travel_pay }}</span>
                                    </template>
                                    <template v-slot:item.bill_total="{ item }">
                                        <span v-if="item.bill_total.includes('-')" style="color: red;">{{ item.bill_total }}</span>
                                        <span v-else>{{ item.bill_total }}</span>
                                    </template>
                                    <template v-slot:item.date="{ item }">
                                        <span>{{ item.date }}</span>
                                    </template>
                                </v-data-table>                                     
                            </div>
                        </div>
                    </v-app>
                </div>
            </div>
        </div>`,
        props: [
            'provider_id'
        ],
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
                        text: 'Credentials',
                        align: 'start',
                        value: 'credentials'
                    },
                    {
                        text: 'Billing Rate',
                        sortable: true,
                        value: 'bill_rate'
                    },
                    {
                        text: 'Clocked Hours',
                        sortable: true,
                        value: 'clocked_hours'
                    },
                    {
                        text: 'Bonus',
                        sortable: true,
                        value: 'bonus'
                    },
                    {
                        text: 'Travel Pay',
                        sortable: true,
                        value: 'travel_pay'
                    },
                    {
                        text: 'Total',
                        sortable: true,
                        value: 'bill_total'
                    },
                    {
                        text: 'Date',
                        sortable: true,
                        value: 'date'
                    },
                ],
                colors: {
                    'Resolved': 'success',
                    'Approved': 'success',
                    'Change Requested': 'warning',
                    'Unresolved': 'red',
                },
                line_items: [
                ],
                search: '',
                pay_periods: [],
                pay_period: '',
                search: '',
                pay_period_loading: true,
                pbj_loading: false
            }
        },
        mounted: function() {
            this.getPayPeriods();
        },
        watch: {
            pay_period: function() {
                this.getPbjReport();
            }
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
            getStatusColorClass(item) {
                return this.colors[item.status] + '--text';
            },
            getPayPeriods() {
                let data = {
                    provider_id: this.provider_id
                };
                modRequest.request('payroll.get_pay_periods', {}, data, function(response) {
                    console.log(response)

                    if(response.success) {
                        for(let i = 0; i < response.periods.length; i++) {
                            let period = response.periods[i];
                            var start = new Date('01-01-1900');
                            var end = new Date('01-01-1900');
                            if(i > 0) {
                                start = new Date(period.start.date);
                                end = new Date(period.end.date);
                            }
                            this.pay_periods.push({
                                start: start,
                                end: end,
                                display: period.display,
                                combined: period.combined
                            })
                        }
                        for (let i = 0; i < this.pay_periods.length; i++) {
                            if(this.pay_periods[i].combined == this.period) {
                                this.pay_period = this.pay_periods[i].combined;
                            }
                        }                        
                        this.pay_period = this.pay_periods[1].combined;
                    } else {
                        console.log('Error');
                        console.log(response);
                    }
                }.bind(this), function(response) {
                    console.log('Failed');
                    console.log(response);
                });

            },
            getPbjReport() {
                const dates = this.getDates(this.pay_period);
                let data = {
                    provider_id: this.provider_id,
                    start: dates[0],
                    end: dates[1]
                };
                modRequest.request('provider.get_pbj_report', {}, data, function(response) {
                    if (response.success) {
                        console.log(response)
                        for (let i = 0; i < response.line_items.length; i++) {
                            response.line_items[i].date = this.formatReturnedDate(response.line_items[i].date);
                            response.line_items[i].bill_total = this.formatMoney(response.line_items[i].bill_total);
                            response.line_items[i].travel_pay = this.formatMoney(response.line_items[i].travel_pay);
                            response.line_items[i].bonus = this.formatMoney(response.line_items[i].bonus);
                            response.line_items[i].date = this.formatDisplayedDate(response.line_items[i].date);
                            for (let j = 0; j < response.line_items[i].bill_rate.length; j++) {
                                response.line_items[i].bill_rate[j] = this.formatMoney(response.line_items[i].bill_rate[j]);
                            }
                        }
                        this.line_items = response.line_items;
                        console.log(this.line_items)
                    } else {
                        console.log("Error");
                        console.log(response);
                    }
                }.bind(this), function(response) {
                    console.log('Failed');
                    console.log(response);
                })
            },
            getDates(date) {
                dateString = date.replace("_", "");
                dateString = dateString.match(/.{1,2}/g) || [];
                start = dateString[0] + dateString[1] + "-" + dateString[2] + "-" + dateString[3];
                end = dateString[4] + dateString[5] + "-" + dateString[6] + "-" + dateString[7];
                
                return [start, end];
            },
            formatReturnedDate(date) {
                date = date.split(" ");
                return date[0];
            },
            formatDisplayedDate(date) {
                dateArray = date.split("-");
                return dateArray[1]+"/"+dateArray[2]+"/"+dateArray[0];
            },
            formatMoney(money) {
                return money.toLocaleString('en-US', { style: 'currency', currency: 'USD' });
            }
        },
    });
});
