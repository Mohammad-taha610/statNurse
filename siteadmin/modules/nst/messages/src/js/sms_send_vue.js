Vue.component('sms-send-view', {
    template:

        /*html*/`
        <v-app>
        
            <v-container v-show="loading" style="width: 100%; height: 40%; display: flex; justify-content: center; align-items: center">
                <i class="mdi mdi-loading mdi-spin" style="font-size: 64px;"></i>
            </v-container>

            <v-container v-show="!loading">
                <v-row>
                    <v-col>

                        <h3>Nurse/Provider</h3>

                        <v-radio-group v-model="recipient" @change="clearForm">

                            <v-radio label="Nurse" value="nurse"></v-radio>
                            <v-radio label="Provider" value="provider"></v-radio>

                        </v-radio-group>
    
                    </v-col>
                </v-row>
                <v-row v-if="recipient == 'nurse'">
                    <v-col>

                        <h3>Select all by certification</h3>

                        <v-checkbox v-model="credentials.CNA" @change="selectCredentials()" label="CNA" class="d-flex align-items-center"></v-checkbox>
                        <v-checkbox v-model="credentials.CMT" @change="selectCredentials()" label="CMT"></v-checkbox>
                        <v-checkbox v-model="credentials.LPN" @change="selectCredentials()" label="LPN"></v-checkbox>
                        <v-checkbox v-model="credentials.RN" @change="selectCredentials()" label="RN"></v-checkbox>
                    
                    </v-col>
                </v-row>
                <v-row v-else>
                    <v-col>
                        <h3>Select all providers</h3>

                        <v-checkbox v-model="select_all_providers" @change="selectAllProviders()" label="Select All Providers"></v-checkbox>
                    </v-col>
                </v-row>
                <v-row align="start">
                    <v-col>
                        <template v-if="recipient == 'nurse'">
                            <h3>Select Recipients</h3>
                            <v-autocomplete
                                v-model="recipients"
                                :items="nurses"
                                label="Selected Recipients"
                                multiple
                                item-text="full_name"
                                item-value="id"
                                clearable
                            >
                                <template v-slot:selection="{ item, index }">
                                    <v-chip v-if="index < 2">
                                        <span>{{ item.full_name }}</span>
                                    </v-chip>
                                    <span
                                        v-if="index === 2"
                                        class="text-grey text-caption align-self-center"
                                    >
                                        (+{{ recipients.length - 2 }} others)
                                    </span>
                                </template>
                            </v-autocomplete>
                        </template>

                        <template v-else>
                            <h3>Select Recipients</h3>
                            <v-autocomplete
                                v-model="recipients"
                                :items="providers"
                                label="Selected Recipients"
                                multiple
                                item-text="company"
                                item-value="id"
                                clearable
                            >
                                <template v-slot:selection="{ item, index }">
                                    <v-chip v-if="index < 2">
                                        <span>{{ item.company }}</span>
                                    </v-chip>
                                    <span
                                        v-if="index === 2"
                                        class="text-grey text-caption align-self-center"
                                    >
                                        (+{{ recipients.length - 2 }} others)
                                    </span>
                                </template>
                            </v-autocomplete>
                        </template>
                    </v-col>
                </v-row>
                <v-row>
                    <v-col>
                        <h3>Write Message</h3>

                        <v-text-field v-model="message" placeholder="Message Body" clearable counter></v-text-field>
        
                        <v-btn @click="sendSMS()"> <span><i class="fa fa-send"></i> Send Message</span> </v-btn>
                    </v-col>
                </v-row>
                <v-snackbar
                    v-model="show_snackbar"
                    :timeout="3500"
                >
                    {{ snackbar_text }}
            
                    <template v-slot:actions>
                        <v-btn
                            @click="show_snackbar = false"
                        >
                            Close
                        </v-btn>
                    </template>
                </v-snackbar>
            </v-container>
        </v-app>
`,
    props: [],
    data: function () {
        return {

            nurses: [],
            providers: [],
            message: '',
            recipient: "nurse",
            isMass: "group",
            recipients: [],
            credentials: {
                CNA: false,
                CMT: false,
                LPN: false,
                RN: false
            },
            select_all_providers: false,
            loading: true,
            show_snackbar: false,
            snackbar_text: ''
        };
    },
    created() {
    },
    mounted() {
        
        this.loadProviders();
        this.loadNurses();
    },
    computed: {

    },
    methods: {
        loadNurses() {

            this.nurses = [];

            modRequest.request('sa.member.load_nurses', {}, {}, function (response) {
                if (response.success) {

                    this.nurses = response.nurses;
                    this.nurses.forEach(nurse => {
                        nurse.full_name = nurse.first_name + ' ' + nurse.last_name;
                    });
                    this.loading = false;
                } else {
                    console.log('Error');
                    console.log(response);
                    this.loading = false;
                }
            }.bind(this), function (response) {

                console.log('Failed');
                console.log(response);
                this.loading = false;
            });
        },
        loadProviders() {
            
            this.providers = [];

            modRequest.request('sa.member.load_providers', {}, {}, function (response) {
                if (response.success) {
                    
                    this.providers = response.providers;
                } else {
                    throw new Error(response);
                }
            }.bind(this), function (error) {
                console.log('Failed');
                console.log(error);
            });

        },

        sendSMS() {

            if (this.message == '') {

                this.snackbar_text = 'Please add a message to send.';
                this.show_snackbar = true;
                return;
            } else if (this.recipients.length == 0) {

                this.snackbar_text = 'Please select at least one recipient.';
                this.show_snackbar = true;
                return;
            } else {

                this.snackbar_text = 'Message sending...';
                this.show_snackbar = true;
            }

            if (this.recipient == 'nurse') {

                let data = {

                    message: this.message,
                    recipients: this.recipients,
                }
                modRequest.request('nst.messages.sendNurseSMS', {}, data, function (response) {
                    if (response.success) {
                        
                        this.clearForm();
                        this.snackbar_text = 'Success: Nurse Message sent';
                        this.show_snackbar = true;
                    }
                }.bind(this), function (response) {
                    
                    console.log('Failed');
                    this.errorMessage = response;
                    console.log(response);

                    this.snackbar_text = 'Error: Nurse Message failed to send';
                    this.show_snackbar = true;
                });

            } else {

                let data = {
                    message: this.message,
                    recipients: this.recipients,
                }
                modRequest.request('nst.messages.sendProviderSMS', {}, data, function (response) {
                    if (response.success) {

                        this.clearForm();
                        this.snackbar_text = 'Success: Provider Message sent';
                        this.show_snackbar = true;
                    } else {
                        console.error(response);
                    }
                }.bind(this), function (response) {
                    console.log('Error');
                    console.log(response);

                    this.snackbar_text = 'Error: Provider Message failed to send';
                    this.show_snackbar = true;
                });
            }
        },

        clearForm() {
            this.message = '';
            this.recipients = [];
        },
        selectCredentials() {

            let CNAs = this.nurses.filter(nurse => {
                return nurse.credentials.includes('CNA') && this.credentials.CNA;
            });
            let CMTs = this.nurses.filter(nurse => {
                return nurse.credentials.includes('CMT') && this.credentials.CMT;
            });
            let LPNs = this.nurses.filter(nurse => {
                return nurse.credentials.includes('LPN') && this.credentials.LPN;
            });
            let RNs = this.nurses.filter(nurse => {
                return nurse.credentials.includes('RN') && this.credentials.RN;
            });
              
            this.recipients = CNAs.concat(CMTs, LPNs, RNs);
        },
        selectAllProviders() {

            if (this.select_all_providers) {
                this.recipients = this.providers;
            } else {
                this.recipients = [];
            }
        }
    }
});