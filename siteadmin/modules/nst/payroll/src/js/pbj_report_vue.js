window.addEventListener('load', function() {
    Vue.component('vue-timepicker', window.VueTimepicker.default);
    Vue.component('pbj-report', {
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
                                    <v-btn
                                        color="blue-grey"
                                        class="ma-2 white--text"
                                        @click="exportCsv"
                                    >
                                        Download
                                        <v-icon
                                        right
                                        dark
                                        >
                                        mdi-cloud-download
                                        </v-icon>
                                    </v-btn>  
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
                                        <span v-show="item.bill_rate[0] != '$0.00'" class="mt block">{{ item.bill_rate[0] }}</span>
                                        <span v-show="item.bill_rate[1] != '$0.00'" class="mt-1 block grey--text" v-if="item.bill_rate[1]">{{ item.bill_rate[1] }}</span>
                                    </template>
                                    <template v-slot:item.clocked_hours="{ item }">
                                        <span class="mt block">{{ item.clocked_hours[0] }} hours</span>
                                        <span v-if="item.clocked_hours[1]" class="mt-1 block grey--text">{{ item.clocked_hours[1] }} hours</span>
                                    </template>
                                    <template v-slot:item.bonus="{ item }">
                                        <span>{{ item.bonus }}</span>
                                    </template>
                                    <template v-slot:item.holiday_pay="{ item }">
                                        <span>{{ item.holiday_pay }}</span>
                                    </template>
                                    <template v-slot:item.travel_pay="{ item }">
                                        <span>{{ item.travel_pay }}</span>
                                    </template>
                                    <template v-slot:item.bill_total="{ item }">
                                        <span class="mt block" v-show="item.bill_total[0] != '0.00'">{{ '$' + item.bill_total[0] }}</span>
                                        <span class="mt-1 block grey--text" v-show="item.bill_total[1] && item.bill_total[1] != '0.00'">{{ '$' + item.bill_total[1] }}</span>
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
            'provider_id',
            'period'
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
                        text: 'Holiday Pay',
                        sortable: true,
                        value: 'holiday_pay'
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

                const date = new Date();

                let month = date.getMonth() + 1;
                if (String(month).length < 2) { month = "0" + String(month); }
                let day = date.getDate();
                if (String(day).length < 2) { day = "0" + String(day); }
                let year = date.getFullYear();

                let currentDate = String(month+"/"+day+"/"+year);

                if (month < 4) {

                    let start = String(01+"/"+01+"/"+year);
                    let end = currentDate;
                    this.pay_periods.push({
                        start: start,
                        end: end,
                        display: start+" - "+end,
                        combined: String(year)+"0101_"+String(year)+"0331"
                    });
                    this.pay_period = String(year)+"0101_"+String(year)+"0331";

                    start = String(10+"/"+01+"/"+(year - 1));
                    end = String(12+"/"+31+"/"+(year - 1));
                    this.pay_periods.push({
                        start: start,
                        end: end,
                        display: start+" - "+end,
                        combined: String(year - 1)+"1001_"+String(year - 1)+"1231"
                    });

                    start = String(07+"/"+01+"/"+(year - 1));
                    end = String(09+"/"+30+"/"+(year - 1));
                    this.pay_periods.push({
                        start: start,
                        end: end,
                        display: start+" - "+end,
                        combined: String(year - 1)+"0701_"+String(year - 1)+"0930"
                    });

                    start = String(04+"/"+01+"/"+(year - 1));
                    end = String(06+"/"+30+"/"+(year - 1));
                    this.pay_periods.push({
                        start: start,
                        end: end,
                        display: start+" - "+end,
                        combined: String(year - 1)+"0401_"+String(year - 1)+"0630"
                    });
                    
                    start = String(01+"/"+01+"/"+(year - 1));
                    end = String(03+"/"+31+"/"+(year - 1));
                    this.pay_periods.push({
                        start: start,
                        end: end,
                        display: start+" - "+end,
                        combined: String(year - 1)+"0101_"+String(year - 1)+"0331"
                    });

                } else if (month < 7) {

                    let start = String(04+"/"+01+"/"+year);
                    let end = currentDate;
                    this.pay_periods.push({
                        start: start,
                        end: end,
                        display: start+" - "+end,
                        combined: String(year)+"0401_"+String(year)+"0630"
                    });
                    this.pay_period = String(year)+"0401_"+String(year)+"0630";

                    start = String(01+"/"+01+"/"+(year));
                    end = String(03+"/"+31+"/"+(year));
                    this.pay_periods.push({
                        start: start,
                        end: end,
                        display: start+" - "+end,
                        combined: String(year)+"0101_"+String(year)+"0331"
                    });

                    start = String(10+"/"+01+"/"+(year - 1));
                    end = String(12+"/"+31+"/"+(year - 1));
                    this.pay_periods.push({
                        start: start,
                        end: end,
                        display: start+" - "+end,
                        combined: String(year - 1)+"1001_"+String(year - 1)+"1231"
                    });

                    start = String(07+"/"+01+"/"+(year - 1));
                    end = String(09+"/"+30+"/"+(year - 1));
                    this.pay_periods.push({
                        start: start,
                        end: end,
                        display: start+" - "+end,
                        combined: String(year - 1)+"0701_"+String(year - 1)+"0930"
                    });

                    start = String(04+"/"+01+"/"+(year - 1));
                    end = String(06+"/"+30+"/"+(year - 1));
                    this.pay_periods.push({
                        start: start,
                        end: end,
                        display: start+" - "+end,
                        combined: String(year - 1)+"0401_"+String(year - 1)+"0630"
                    });

                } else if (month < 10) {
                    let start = String(07+"/"+01+"/"+year);
                    let end = currentDate;
                    this.pay_periods.push({
                        start: start,
                        end: end,
                        display: start+" - "+end,
                        combined: String(year)+"0701_"+String(year)+"0930"
                    });
                    this.pay_period = String(year)+"0701_"+String(year)+"0930";

                    start = String(04+"/"+01+"/"+(year));
                    end = String(06+"/"+30+"/"+(year));
                    this.pay_periods.push({
                        start: start,
                        end: end,
                        display: start+" - "+end,
                        combined: String(year)+"0401_"+String(year)+"0630"
                    });

                    start = String(01+"/"+01+"/"+(year));
                    end = String(03+"/"+31+"/"+(year));
                    this.pay_periods.push({
                        start: start,
                        end: end,
                        display: start+" - "+end,
                        combined: String(year)+"0101_"+String(year)+"0331"
                    });

                    start = String(10+"/"+01+"/"+(year - 1));
                    end = String(12+"/"+31+"/"+(year - 1));
                    this.pay_periods.push({
                        start: start,
                        end: end,
                        display: start+" - "+end,
                        combined: String(year - 1)+"1001_"+String(year - 1)+"1231"
                    });

                    start = String(07+"/"+01+"/"+(year - 1));
                    end = String(09+"/"+30+"/"+(year - 1));
                    this.pay_periods.push({
                        start: start,
                        end: end,
                        display: start+" - "+end,
                        combined: String(year - 1)+"0701_"+String(year - 1)+"0930"
                    });
                    
                } else {
                    let start = String(10+"/"+01+"/"+(year));
                    let end = currentDate;
                    this.pay_periods.push({
                        start: start,
                        end: end,
                        display: start+" - "+end,
                        combined: String(year)+"1001_"+String(year)+"1231"
                    });
                    this.pay_period = String(year)+"1001_"+String(year)+"1231";

                    start = String(07+"/"+01+"/"+year);
                    end = String(09+"/"+30+"/"+year);
                    this.pay_periods.push({
                        start: start,
                        end: end,
                        display: start+" - "+end,
                        combined: String(year)+"0701_"+String(year)+"0930"
                    });

                    start = String(04+"/"+01+"/"+year);
                    end = String(06+"/"+30+"/"+year);
                    this.pay_periods.push({
                        start: start,
                        end: end,
                        display: start+" - "+end,
                        combined: String(year)+"0401_"+String(year)+"0630"
                    });

                    start = String(01+"/"+01+"/"+year);
                    end = String(03+"/"+31+"/"+year);
                    this.pay_periods.push({
                        start: start,
                        end: end,
                        display: start+" - "+end,
                        combined: String(year)+"0101_"+String(year)+"0331"
                    });
                    
                    start = String(10+"/"+01+"/"+(year - 1));
                    end = String(12+"/"+31+"/"+(year - 1));
                    this.pay_periods.push({
                        start: start,
                        end: end,
                        display: start+" - "+end,
                        combined: String(year - 1)+"1001_"+String(year - 1)+"1231"
                    });
                }
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
                        for (let i = 0; i < response.line_items.length; i++) {

                            if (response.line_items[i].holiday_pay != 0) {
                                console.log("line item: ", response.line_items[i])
                            }
                            response.line_items[i].date = this.formatReturnedDate(response.line_items[i].date);
                            response.line_items[i].travel_pay = this.fixMissingTravelPay(response.line_items[i]);
                            response.line_items[i].bonus = this.formatMoney(response.line_items[i].bonus);
                            response.line_items[i].holiday_pay = this.formatMoney(response.line_items[i].holiday_pay);
                            response.line_items[i].date = this.formatDisplayedDate(response.line_items[i].date);

                            for (let j = 0; j < response.line_items[i].bill_rate.length; j++) {
                                response.line_items[i].bill_rate[j] = this.formatMoney(response.line_items[i].bill_rate[j]);
                            }

                            for (let j = 0; j < response.line_items[i].bill_total.length; j++) {
                                response.line_items[i].bill_total[j] = response.line_items[i].bill_total[j].toFixed(2);
                            }
                        }
                        this.line_items = response.line_items;
                    } else {
                        console.log("Error");
                        console.log(response);
                    }
                }.bind(this), function(response) {
                    console.log('Failed');
                    console.log(response);
                })
            },
            exportCsv() {

                line_items = [];
                var csv = 'Nurse Name, Credentials, Billing Rate, Clocked Hours, Bonus, Holiday Pay, Travel Pay, Total, Date\n';
                for (let i = 0; i < this.line_items.length; i++) {

                    // if there are two hourly rates, two entries need to be made
                    if (this.line_items[i].bill_rate.length > 1) {

                        let clockedHours1 = this.formatClockedHours(this.line_items[i].clocked_hours[0].toFixed(2));
                        let clockedHours2 = this.formatClockedHours(this.line_items[i].clocked_hours[1].toFixed(2));

                        line1 = this.line_items[i].nurse_name+", "+this.line_items[i].credentials+", "+this.line_items[i].bill_rate[0]+", "+clockedHours1+", "+this.line_items[i].bonus+", "+this.line_items[i].holiday_pay+", "+this.line_items[i].travel_pay+", "+this.formatMoney(this.line_items[i].bill_total[0])+", "+this.line_items[i].date+"\n";

                        line2 = this.line_items[i].nurse_name+", "+this.line_items[i].credentials+", "+this.line_items[i].bill_rate[1]+", "+clockedHours2+", "+this.line_items[i].bonus+", "+this.line_items[i].holiday_pay+", "+this.line_items[i].travel_pay+", "+this.formatMoney(this.line_items[i].bill_total[1])+", "+this.line_items[i].date+"\n";

                        csv += line1;
                        csv += line2;
                    } else {

                        let clockedHours = this.formatClockedHours(parseFloat(this.line_items[i].clocked_hours[0].toFixed(2)));

                        line = this.line_items[i].nurse_name+", "+this.line_items[i].credentials+", "+this.line_items[i].bill_rate+", "+clockedHours+", "+this.line_items[i].bonus+", "+this.line_items[i].holiday_pay+", "+this.line_items[i].travel_pay+", "+this.formatMoney(this.line_items[i].bill_total)+", "+this.line_items[i].date+"\n";

                        csv += line;
                    }
                }
    
                var hiddenElement = document.createElement('a');  
                hiddenElement.href = 'data:text/csv;charset=utf-8,' + encodeURI(csv);  
                hiddenElement.target = '_blank';

                hiddenElement.download = 'PBJ Report '+this.pay_period+'.csv';  
                hiddenElement.click();  
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

                return '$' + Number(money).toFixed(2);
            },
            formatClockedHours(hours) {

                let hoursString = hours.toString();
                let hoursArray = hoursString.split(".");

                if (hoursArray.length == 1) {

                    return hours + ".00";
                } else if (hoursArray[1].length == 1) {

                    return hours + "0";
                } else {

                    return hours;
                }
            },
            fixMissingTravelPay(line_item) {

                if (line_item.bill_rate.length > 1 && line_item.bill_total.length > 1) {

                    let normalPay = parseFloat(line_item.bill_rate[0]) * parseFloat(line_item.clocked_hours[0]);
                    let overtimePay = parseFloat(line_item.bill_rate[1]) * parseFloat(line_item.clocked_hours[1]);
                    let bonus = parseFloat(line_item.bonus);
                    let holidayPay = parseFloat(line_item.holiday_pay);
                    let travelPay = parseFloat(line_item.travel_pay);
                    let storedTotal = parseFloat(line_item.bill_total[0]) + parseFloat(line_item.bill_total[1]);
                    let calculatedTotal = normalPay + overtimePay + bonus + holidayPay + travelPay;
                    let difference = Math.abs(calculatedTotal - storedTotal);

                    if (difference > 0.01) {

                        return this.formatMoney(difference + travelPay);
                    } else {

                        return this.formatMoney(travelPay);
                    }
                } else {

                    let travelPay = parseFloat(line_item.travel_pay);
                    let calculatedTotal = (parseFloat(line_item.bill_rate[0]) * parseFloat(line_item.clocked_hours)) + parseFloat(line_item.bonus) + parseFloat(line_item.holiday_pay) + travelPay;
                    let storedTotal = parseFloat(line_item.bill_total[0]);
                    let difference = Math.abs(calculatedTotal - storedTotal);

                    if (difference > 0.01) {

                        return this.formatMoney(difference + travelPay);
                    } else {

                        return this.formatMoney(travelPay);
                    }
                }
            }
        },
    });
});