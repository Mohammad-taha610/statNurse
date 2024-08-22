window.addEventListener('load', function() {
    Vue.component('sa-edit-invoice-view', {
        template:
        `
            <div class="container-fluid"> 
                <div class="row"> 
                    <div class="col-12"> 
                        <v-app> 
                            <div class="card"> 
                                <div class="card-header">
                                    <div class="row">
                                        <div class="col-12">
                                            <h4 class="card-title">{{id > 0 ? 'Edit' : 'Add'}} Invoice</h4>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="row"> 
                                        <div class="col-xs-12 col-md-6"> 
                                            <v-autocomplete
                                                v-model="provider_id"
                                                :items="providers"
                                                item-text="name"
                                                item-value="id"
                                                label="Provider"
                                                clearable
                                                dense
                                            ></v-autocomplete>
                                        </div>
                                    </div> 
                                    <div class="row"> 
                                        <div class="col-xs-12 col-md-6"> 
                                            <v-text-field 
                                                label="Invoice Number"
                                                v-model="invoice_number"
                                                ></v-text-field>
                                        </div>
                                    </div>
                                    <div class="row"> 
                                        <div class="col-xs-12 col-md-6">
                                            <v-text-field 
                                                label="Amount"
                                                placeholder="$"
                                                v-model="amount"
                                            ></v-text-field> 
                                        </div>
                                    </div>
                                    <div class="row"> 
                                        <div class="col-xs-12 col-md-6">
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
                                    <div class="row"> 
                                        <div class="col-xs-12 col-md-6">
                                            <v-select
                                                v-model="status"
                                                :items="statuses"
                                                label="Status"
                                                dense
                                            ></v-select>
                                        </div>
                                    </div>
                                    <div class="row" v-if="file_id"> 
                                        <div class="col-xs-12">
                                            <v-btn 
                                                color="primary" 
                                                :href="file_url"
                                                target="_blank"
                                                >View File ({{file_name}})</v-btn>
                                        </div>
                                    </div>
                                    <div class="row"> 
                                        <div class="col-xs-12 col-md-6">
                                            <file-uploader 
                                                :config="upload_config"
                                                v-on:fileUploaded="handleUploaded($event)"
                                            ></file-uploader>
                                        </div>
                                    </div>
                                </div>
                                <div class="row"> 
                                    <div class="col-xs-12 col-md-6 text-center"> 
                                        <v-btn 
                                            color="info"
                                            v-on:click="saveInvoiceData"
                                            x-large
                                            >Save</v-btn>
                                    </div>
                                </div>
                            </div>
                        </v-app>
                    </div>
                </div>
            </div>
        `,
        props: [
            'id'
        ],
        data: function() {
            return {
                provider_id: null,
                providers: [],
                invoice_number: '',
                pay_periods: [],
                pay_period: '',
                statuses: [],
                status: '',
                amount: 0,
                upload_config: {
                    'id': 'pdf-uploader-1',
                    'multiple': true,
                    'upload_route': '/files/upload',
                    'chunk_size' : 1000000,
                },
                file_id: null,
                file_name: '',
                file_url: '',
            };
        },
        created() {
            this.loadPayPeriods();
        },
        mounted() {
        },
        methods: {
            handleUploaded(e) {
                this.file_id = e.id;
                this.file_name = e.name;
                this.file_url = e.url;
            },
            saveInvoiceData() {
                var data = {
                    id: this.id,
                    provider_id: this.provider_id,
                    invoice_number: this.invoice_number,
                    pay_period: this.pay_period,
                    status: this.status,
                    amount: this.amount,
                    file_id: this.file_id,
                };

                modRequest.request('sa.invoices.save_invoice_data', {}, data, function(response) {
                    if(response.success) {
                        console.log('yeh');
                    } else {
                        console.log('Error');
                        console.log(response);
                    }
                }.bind(this), function(response) {
                    console.log('Failed');
                    console.log(response);
                });
            },
            loadPayPeriods() {
                modRequest.request('sa.payroll.get_pay_periods', {}, {}, function(response) {
                    if(response.success) {
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
                }.bind(this), function(response) {
                    console.log('Failed');
                    console.log(response);
                });
            },
            loadProviders() {
                modRequest.request('sa.member.load_providers', {}, {}, function(response) {
                    if(response.success) {
                        this.providers = [];
                        for (let i = 0; i < response.providers.length; i++) {
                            let provider = response.providers[i];
                            this.providers.push({
                                id: provider.id,
                                name: provider.company
                            })
                        }
                        this.loadInvoiceData();
                    } else {
                        console.log('Error');
                        console.log(response);
                    }
                }.bind(this), function(response) {
                    console.log('Failed');
                    console.log(response);
                });
            },
            loadInvoiceData() {
                var data = {
                    id: this.id
                };

                modRequest.request('sa.invoices.load_invoice_data', {}, data, function(response) {
                    if(response.success) {
                        this.statuses = response.statuses;

                        if(typeof(response.invoice) != "undefined") {
                            var invoice = response.invoice;
                            this.provider_id = invoice.provider_id;
                            this.invoice_number = invoice.invoice_number;
                            this.amount = invoice.amount;
                            this.pay_period = invoice.pay_period;
                            this.status = invoice.status;
                            this.file_id = invoice.file_id;
                            this.file_name = invoice.file_name;
                            this.file_url = invoice.file_url;
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
        }
    });
});