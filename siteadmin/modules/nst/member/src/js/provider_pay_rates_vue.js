Vue.component('provider-pay-rates-view', {
    // language=HTML
    template:
        `
            <v-container>
                <v-row>
                    <div id="app" class="w-100">
                        <v-app>
                            <div class="card">
                                <div class="card-body" style="max-width: 800px;">
                                    <nst-overlay :loading="loading"></nst-overlay>
                                    <v-btn 
                                        v-if="!editing"
                                        class="mb-4"
                                        @click="editing = true"
                                        color="primary">Edit</v-btn>
                                    <v-btn
                                        v-else
                                        class="mb-4"
                                        @click="savePayRates"
                                        color="success">Save</v-btn>
                                    <v-data-table
                                            class="table table-responsive-md pay-rates-table"
                                            :headers="headers"
                                            :items="pay_rates"
                                            :hide-default-footer="true"
                                    >
                                        <template v-slot:item.type="{ item }">
                                            <span>{{item.name}}</span>
                                        </template>
                                        <template v-slot:item.standard_pay="{ item }">
                                            <input v-if="editing" class="pay-rate-input" v-model="item.standard_pay">
                                            <span v-else>{{item.standard_pay}}</span>
                                        </template>
                                        <template v-slot:item.covid_pay="{ item }">
                                            <span class="grey--text">{{covidPay(item.standard_pay)}}</span>
                                        </template>
                                        <template v-slot:item.standard_bill="{ item }">
                                            <input v-if="editing" class="pay-rate-input" v-model="item.standard_bill">
                                            <span v-else>{{item.standard_bill}}</span>
                                        </template>
                                        <template v-slot:item.covid_bill="{ item }">
                                            <span class="grey--text">{{covidBill(item.standard_bill)}}</span>
                                        </template>
                                    </v-data-table>
                                    <v-text-field
                                            label="Covid Pay Addition"
                                            v-model="covid_pay_amount"></v-text-field>
                                    <v-text-field
                                            label="Covid Bill Addition"
                                            v-model="covid_bill_amount"></v-text-field>
                                </div>
                            </div>
                        </v-app>
                    </div>
                </v-row>
            </v-container>
        `,
    props: [
        'id'
    ],
    data: function () {
        return {
            editing: false,
            tab: null,
            covid_pay_amount: 0,
            covid_bill_amount: 0,
            deleteDialog: false,
            active_tab: 0,
            headers: [
                {
                    width: '10%',
                    text: 'Nurse Type',
                    sortable: false,
                    value: 'type'
                },
                {
                    width: '22.5%',
                    text: 'Standard Pay',
                    sortable: false,
                    value: 'standard_pay'
                },
                {
                    width: '22.5%',
                    text: 'Covid Pay',
                    sortable: false,
                    value: 'covid_pay'
                },
                {
                    width: '22.5%',
                    text: 'Standard Bill',
                    sortable: false,
                    value: 'standard_bill'
                },
                {
                    width: '22.5%',
                    text: 'Covid Bill',
                    sortable: false,
                    value: 'covid_bill'
                }
            ],
            pay_types: [
                'Pay Rates',
                'Bill Rates'
            ],
            pay_rates: [
                {
                    'name': 'CNA',
                    'standard_pay': 0,
                    'standard_bill': 0,
                },
                {
                    'name': 'CMT',
                    'standard_pay': 0,
                    'standard_bill': 0,
                },
                {
                    'name': 'LPN',
                    'standard_pay': 0,
                    'standard_bill': 0,
                },
                {
                    'name': 'RN',
                    'standard_pay': 0,
                    'standard_bill': 0,
                },
            ],
            tab_pay_rates: [],
            tab_bill_rates: [],
            tab_1_5x_pay: [],
            tab_1_5x_bill: [],
            tab_2x_pay: [],
            tab_2x_bill: [],
            loading: false,
        };
    },
    created() {
        this.loadProviderPayRates();
        this.$root.$on('saveMemberData', function() {
            this.savePayRates()
        }.bind(this));
    },
    mounted() {

    },
    computed: {
    },
    methods: {

        covidPay(standard_pay) {
            return parseInt(standard_pay) + parseInt(this.covid_pay_amount);
        },
        covidBill(standard_bill) {
            return parseInt(standard_bill) + parseInt(this.covid_bill_amount);
        },
        loadProviderPayRates() {
            let data = {
                id: this.id
            };

            this.loading = true;

            modRequest.request('sa.member.load_provider_pay_rates', {}, data, function (response) {
                if (response.success) {
                    this.pay_rates = response.pay_rates;
                    this.covid_pay_amount = response.covid_pay_amount == null ? 0 : response.covid_pay_amount;
                    this.covid_bill_amount = response.covid_bill_amount == null ? 0 : response.covid_bill_amount;
                    this.loading = false;
                } else {
                    console.log('Error');
                    console.log(response);
                }
            }.bind(this), function (response) {
                console.log('Failed');
                console.log(response);
            });
        },
        savePayRates() {
            var data = {
                id: this.id,
                pay_rates: this.pay_rates,
                covid_pay_amount: this.covid_pay_amount,
                covid_bill_amount: this.covid_bill_amount
            };
            this.loading = true;
            modRequest.request('sa.member.save_provider_pay_rates', {}, data, function(response) {
                if(response.success) {
                    this.loading = false;
                    this.editing = false;
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
