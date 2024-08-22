Vue.component('nurse-bank-info-view', {
    // language=HTML
    template:
        `
            <v-app>
                <v-container>
                    <nst-overlay :loading="loading"></nst-overlay>
                    <nst-error-notification
                            v-if="error"
                            :error="error"></nst-error-notification>
                    <v-row>
                        <label>Bank Account</label>
                    </v-row>
                    <v-row>
                        <v-text-field
                                label="Bank Name"
                                v-model="payment.bank_name"
                        ></v-text-field>
                    </v-row>
                    <v-row>
                        <v-text-field
                                label="Bank Account Holder Name"
                                v-model="payment.bank_account_holder_name"
                        ></v-text-field>
                    </v-row>
                    <v-row>
                        <v-select
                                label="Bank Account Type"
                                attach
                                :items="['Checking', 'Savings']"
                                v-model="payment.bank_account_type"
                        ></v-select>
                    </v-row>
                    <v-row>
                        <v-text-field
                                v-model="payment.bank_account_number"
                                label="Account Number"
                        ></v-text-field>
                    </v-row>
                    <v-row>
                        <v-text-field
                                v-model="payment.bank_routing_number"
                                label="Routing Number / ABA Number"
                        ></v-text-field>
                    </v-row>
                </v-container>
            </v-app>
        `,
    props: [
        'id'
    ],
    data: function () {
        return {
            error: null,
            payment: {},
            loading: false
        };
    },
    created() {
        this.loadNurseBankInfo();
    },
    mounted() {
        this.$root.$on('saveMemberData', function () {
            this.saveData()
        }.bind(this));
    },
    computed: {},
    methods: {
        validateRoutingNumber(value) {
            if (isNaN(value) || value.length !== 9) {
                return false;
            }

            let sum = (3 * (Number(value.substring(0, 1)) + Number(value.substring(3, 4)) + Number(value.substring(6, 7)))) +
                (7 * (Number(value.substring(1, 2)) + Number(value.substring(4, 5)) + Number(value.substring(7, 8)))) +
                ((Number(value.substring(2, 3)) + Number(value.substring(5, 6)) + Number(value.substring(8, 9))));

            let mod = (sum % 10);
            return mod === 0;
        },
        loadNurseBankInfo() {
            let data = {
                id: this.id,
            };
            this.loading = true;
            modRequest.request('sa.member.load_nurse_direct_deposit_info', {}, data, function (response) {
                if (response.success) {
                    if (response.direct_deposit) {
                        this.payment.bank_name = response.direct_deposit.bank_name;
                        this.payment.bank_account_holder_name = response.direct_deposit.bank_account_holder_name;
                        this.payment.bank_account_type = response.direct_deposit.bank_account_type;
                        this.payment.bank_account_number = response.direct_deposit.bank_account_number;
                        this.payment.bank_routing_number = response.direct_deposit.bank_routing_number;
                    }
                    this.error = null;
                } else {
                    console.log('Error');
                    console.log(response);
                    this.error = {
                        type: 'danger',
                        message: response.message,
                    }
                }
                this.loading = false;
            }.bind(this), function (response) {
                console.log('Failed');
                console.log(response);
            });
        },
        saveData() {
            if (!this.validateRoutingNumber(this.payment.bank_routing_number)) {
                $.growl.error({ title: "Error!", message: "Bank Routing number is not a number, the required length, or is invalid.", size: "large" });
                return;
            }

            let data = {
                id: this.id,
                nurse: this.nurse,
                direct_deposit: {
                    bank_account_holder_name: this.payment.bank_account_holder_name,
                    bank_account_number: this.payment.bank_account_number,
                    bank_routing_number: this.payment.bank_routing_number,
                    bank_account_type: this.payment.bank_account_type,
                    bank_name: this.payment.bank_name
                }
            };

            this.loading = true;
            modRequest.request('sa.member.save_nurse_direct_deposit_info', {}, data, function (response) {
                if (response.success) {
                    //$.growl.notice({ title: "Success!", message: "Changes to nurse bank information was saved.", size: "large" });
                    this.error = null;
                } else {
                    console.log('Error');
                    console.log(response);
                    this.error = {
                        type: 'danger',
                        message: response.message,
                    }
                }
                this.loading = false;
            }.bind(this), function (response) {
                console.log('Failed');
                console.log(response);
            });
        }
    }
});
