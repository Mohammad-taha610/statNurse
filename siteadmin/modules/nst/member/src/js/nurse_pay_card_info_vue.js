Vue.component('nurse_pay_card_info_view', {
    // language=HTML
    template:`
            <v-app>
                <v-container>
                    <nst-overlay :loading="loading"></nst-overlay>
                    <nst-error-notification
                            v-if="error"
                            :error="error"></nst-error-notification>
                    <v-row>
                        <v-col>
                            <v-text-field
                                    label="Pay Card Account Number"
                                    v-model="payCardAccountNumber"
                                    :disabled="loading"
                            ></v-text-field>
                        </v-col>
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
            payCardAccountNumber: null,
            loading: false
        };
    },
    created() {
        this.loadNursePayCardData();
    },
    mounted() {
        this.$root.$on('saveMemberData', function () {
            this.saveData()
        }.bind(this));
    },
    computed: {},
    methods: {
        loadNursePayCardData() {
            let data = {
                id: this.id,
            };

            this.loading = true;
            modRequest.request('sa.member.load_nurse_pay_card_info', {}, data, response => {
                if (response.success) {
                    if (response.payCardAccountNumber) {
                        this.payCardAccountNumber = response.payCardAccountNumber;
                    }
                    this.error = null;
                } else {
                    console.log('Error');
                    this.error = {
                        type: 'danger',
                        message: response.message,
                    }
                }
                this.loading = false;
            }, response => {
                this.loading = false;
                console.log('Failed');
            });
        },
        saveData() {
            if(!this.payCardAccountNumber) {
                return;
            }

            let data = {
                id: this.id,
                payCardAccountNumber: this.payCardAccountNumber
            };

            this.loading = true;
            modRequest.request('sa.member.save_nurse_pay_card_info', {}, data, response => {
                if (response.success) {
                    this.error = null;
                } else {
                    console.log('Error');
                    this.error = {
                        type: 'danger',
                        message: response.message,
                    }
                }
                this.loading = false;
            }, response => {
                console.log('Failed');
            });
        }
    }
});
