window.addEventListener('load', function () {
    Vue.component('sa-invoices-list-view', {
        // language=HTML
        template:
            `
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-12">
                            <v-app>
                                <div class="card">
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <div class="card">
                                                <v-card-text>
                                                    <div class="row">
                                                        <div class="col-12 col-sm-8"></div>
                                                        <div class="col-12 col-sm-4">
                                                            <div class="row">
                                                                <div class="col-12 pt-8">
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
                                                            <div class="row">
                                                                <div class="col-12 pt-0 pb-0"
                                                                     id="provider-dropdown-col">
                                                                    <v-spacer></v-spacer>
                                                                    <v-autocomplete
                                                                            v-model="provider_id"
                                                                            :items="providers"
                                                                            item-text="name"
                                                                            item-value="id"
                                                                            label="Provider"
                                                                            clearable
                                                                            width="300"
                                                                            @change="updateTables"
                                                                    ></v-autocomplete>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <v-data-table
                                                            class="table table-responsive-md"
                                                            :headers="headers"
                                                            :items="invoices"
                                                            multi-sort
                                                    >
                                                        <template v-slot:item.provider_name="{ item }">
                                                            <span class="block mt-3">{{ item.provider_name }}</span>
                                                        </template>
                                                        <template v-slot:item.invoice_number="{ item }">
                                                            <span class="block mt-3">{{ item.invoice_number }}</span>
                                                        </template>
                                                        <template v-slot:item.pay_period="{ item }">
                                                            <span class="block mt-3">{{ payPeriodDisplay(item.pay_period) }}</span>
                                                        </template>
                                                        <template v-slot:item.amount="{ item }">
                                                            <span class="block mt-3">{{'$' + item.amount }}</span>
                                                        </template>
                                                        <template v-slot:item.status="{ item }">
                                                            <span :class="'block mt-3 ' + status_color_classes[item.status]">{{ item.status }}</span>
                                                        </template>
                                                        <template v-slot:item.actions="{ item }">
                                                            <a
                                                                    v-if="item.file_url"
                                                                    :href="item.file_url"
                                                                    target="_blank"
                                                            >
                                                                <v-btn
                                                                        color="primary"
                                                                        class="mr-1 mt-1"
                                                                >View
                                                                </v-btn>
                                                            </a>
                                                            <v-dialog max-width="400">
                                                                <template v-slot:activator="{ on, attrs }">
                                                                    <v-btn
                                                                            v-on="on"
                                                                            v-bind="attrs"
                                                                            class="mr-1 mt-1 white--text"
                                                                            color="red"
                                                                    >Delete
                                                                    </v-btn>
                                                                </template>
                                                                <template v-slot:default="dialog">
                                                                    <v-card>
                                                                        <v-toolbar
                                                                                color="red"
                                                                                class="text-h4 white--text"
                                                                        >Delete Invoice
                                                                        </v-toolbar>
                                                                        <v-card-text
                                                                                class="pt-5"
                                                                        >Do you wish to <span
                                                                                class="red--text">DELETE</span> this
                                                                            invoice?
                                                                        </v-card-text>
                                                                        <v-card-actions>
                                                                            <v-spacer></v-spacer>
                                                                            <v-btn
                                                                                    color="light"
                                                                                    v-on:click="dialog.value = false"
                                                                            >Cancel
                                                                            </v-btn>
                                                                            <v-btn
                                                                                    color="red"
                                                                                    v-on:click="deleteInvoice(item)"
                                                                                    class="white--text"
                                                                            >Yes, Delete
                                                                            </v-btn>
                                                                        </v-card-actions>
                                                                    </v-card>
                                                                </template>
                                                            </v-dialog>
                                                            <v-btn
                                                                    v-if="item.status == 'Review'"
                                                                    color="success"
                                                                    :href="item.generate_url">Send
                                                            </v-btn>
                                                        </template>
                                                    </v-data-table>
                                                </v-card-text>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </v-app>
                        </div>
                    </div>
                </div>
            `,
        props: [],
        data: function () {
            return {
                status_color_classes: {
                    'Unpaid': 'red--text',
                    'Review': 'warning--text',
                    'Paid': 'success--text'
                },
                headers: [
                    {
                        text: 'Provider',
                        sortable: true,
                        value: 'provider_name'
                    },
                    {
                        text: 'Invoice #',
                        sortable: true,
                        value: 'invoice_number'
                    },
                    {
                        text: 'Pay Period',
                        sortable: true,
                        value: 'pay_period'
                    },
                    {
                        text: 'Amount',
                        sortable: true,
                        value: 'amount'
                    },
                    {
                        text: 'Status',
                        sortable: true,
                        value: 'status'
                    },
                    {
                        text: 'Actions',
                        sortable: false,
                        value: 'actions'
                    }
                ],
                providers: [],
                provider_id: null,
                pay_periods: [],
                invoices: [],
                pay_period: null
            };
        },
        created() {
            this.loadPayPeriods();
            this.loadProviders();
            this.loadInvoices();
        },
        computed: {},
        mounted() {

        },
        methods: {
            updateTables() {
                this.loadInvoices();
            },
            approveInvoice(item) {
                const data = {
                    provider_id: this.provider_id,
                    invoice_id: item.id
                };

                modRequest.request('sa.quickbooks.approve_invoice', {}, data, function (response) {
                    if (response.success) {

                    } else {
                        console.log('Error');
                        console.log(response);
                    }
                }.bind(this), function (response) {
                    console.log('Failed');
                    console.log(response);
                });
            },
            loadInvoices() {
                const data = {
                    provider_id: this.provider_id,
                    pay_period: this.pay_period
                };

                modRequest.request('sa.invoices.load_invoices', {}, data, function (response) {
                    this.invoices = [];
                    if (response.success) {
                        if (!response.invoices) {
                            return;
                        }
                        for (let i = 0; i < response.invoices.length; i++) {
                            const invoice = response.invoices[i];
                            this.invoices.push({
                                id: invoice.id,
                                provider_name: invoice.provider_name,
                                invoice_number: invoice.invoice_number,
                                pay_period: invoice.pay_period,
                                amount: invoice.amount != null ? invoice.amount.toFixed(2) : '',
                                status: invoice.status,
                                edit_route: invoice.edit_route,
                                file_url: invoice.file_url,
                                generate_url: invoice.generate_url,
                            });
                        }
                    } else {
                        console.log('Error');
                        console.log(response);
                    }
                }.bind(this), function (response) {
                    console.log('Failed');
                    console.log(response);
                });
            },
            deleteInvoice(item) {
                const data = {
                    invoice_id: item.id
                };

                modRequest.request('sa.invoices.delete_invoice', {}, data, function (response) {
                    if (response.success) {
                        this.invoices.splice(this.invoices.indexOf(item), 1);
                    } else {
                        console.log('Error');
                        console.log(response);
                    }
                }.bind(this), function (response) {
                    console.log('Failed');
                    console.log(response);
                });
            },
            payPeriodDisplay: function (combined) {
                const period = this.pay_periods.filter((period) => {
                    return period.combined === combined
                })[0];
                if (period && period.display) {
                    return period.display;
                } else {
                    return;
                }
            },
            loadPayPeriods() {
                modRequest.request('sa.payroll.get_pay_periods', {}, {}, function (response) {
                    if (response.success) {
                        for (let i = 0; i < response.periods.length; i++) {
                            let period = response.periods[i];
                            this.pay_periods.push({
                                display: period.display,
                                combined: period.combined
                            })
                        }
                        this.loadProviders();
                    } else {
                        console.log('Error');
                        console.log(response);
                    }
                }.bind(this), function (response) {
                    console.log('Failed');
                    console.log(response);
                });
            },
            loadProviders() {
                let data = {};

                modRequest.request('sa.member.load_providers', {}, {}, function (response) {
                    if (response.success) {
                        this.providers = [];
                        for (let i = 0; i < response.providers.length; i++) {
                            let provider = response.providers[i];
                            this.providers.push({
                                id: provider.id,
                                name: provider.company
                            })
                        }
                    } else {
                        console.log('Error');
                        console.log(response);
                    }
                }.bind(this), function (response) {
                    console.log('Failed');
                    console.log(response);
                });
            },
        }
    });

});
