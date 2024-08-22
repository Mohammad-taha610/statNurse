window.addEventListener('load', function() {
    Vue.component('invoices-list-view', {
        template:
        /*html*/`
            <div class="container-fluid" id="pay-period-container">
                <div class="row">
                    <div class="col-12">
                        <v-app>
                            <div class="card">
                                <div class="card-header">
                                    <div class="row">
                                        <div class="col-12">
                                            <h4 class="card-title">Invoices</h4>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <v-card>
                                            <v-card-text>
                                                <v-data-table
                                                    class="table table-responsive-md"
                                                    :headers="headers"
                                                    :items="invoices"
                                                    multi-sort
                                                >
                                                    <template v-slot:item.invoice_number="{ item }"> 
                                                        <span>{{ item.invoice_number }}</span>
                                                    </template>
                                                    <template v-slot:item.pay_period="{ item }">
                                                        <span>{{ item.pay_period }}</span>
                                                    </template>
                                                    <template v-slot:item.amount="{ item }">
                                                        <span>{{ item.amount }}</span>
                                                    </template>
                                                    <template v-slot:item.status="{ item }">
                                                        <span>{{ item.status }}</span>
                                                    </template>
                                                    <template v-slot:item.actions="{ item }"> 
                                                        <a
                                                            v-if="item.file_url"
                                                            :href="item.file_url"
                                                            target="_blank"
                                                            ><v-btn
                                                                color="primary" 
                                                            >View Invoice</v-btn></a>
                                                    </template>
                                                </v-data-table>
                                            </v-card-text>
                                        </v-card>
                                    </div>
                                </div>
                            </div>
                        </v-app>
                    </div>
                </div>
            </div>
        `,
        props: [
            'provider_id'
        ],
        data: function() {
            return {
                headers: [
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
                invoices: [],
                pay_periods: [],
            };
        },
        mounted () {
            this.loadInvoices();
        },
        methods: {
            loadInvoices() {
                let data = {
                    provider_id: this.provider_id
                }
                
                modRequest.request('payroll.load_invoices', {}, data, function(response) {
                    this.invoices = [];
                    if(response.success) {
                        console.log(response)
                        for (let i = 0; i < response.invoices.length; i++) {
                            let invoice = response.invoices[i];
                            if (response.invoices[i].amount > 0) {
                                this.invoices.push({
                                    invoice_number: invoice.invoice_number,
                                    file_url: invoice.file_url,
                                    pay_period: this.formatDate(invoice.pay_period),
                                    amount: this.formatMoney(invoice.amount),
                                    status: invoice.status
                                });
                            } else {
                                this.invoices.push({
                                    invoice_number: invoice.invoice_number,
                                    file_url: invoice.file_url,
                                    pay_period: invoice.pay_period,
                                    amount: 0,
                                    status: invoice.status
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
            formatMoney(money) {
                formattedMoney = money.toLocaleString('en-US', {
                    style: 'currency',
                    currency: 'USD'
                });

                return formattedMoney;
            },
            formatDate(date) {
                dateString = date.replace("_", "");
                dateString = dateString.match(/.{1,2}/g) || [];
                start = dateString[2] + "/" + dateString[3] + "/" + dateString[0] + dateString[1];
                end = dateString[6] + "/" + dateString[7] + "/" + dateString[4] + dateString[5];
                
                return start+" - "+end;
            },
        }
    });
});