Vue.component('nurse-basic-info-view', {
    template:
    /*html*/`
        <v-app>
            <v-container>
                <nst-overlay :loading="loading"></nst-overlay>
                <nst-error-notification 
                        v-if="error" 
                        :error="error"></nst-error-notification>
                <v-row>
                    <label>Personal Information</label>
                </v-row>
                <v-row>
                    <v-text-field
                            v-model="nurse.first_name"
                            label="First Name"
                    ></v-text-field>
                </v-row>
                <v-row>
                    <v-text-field
                            v-model="nurse.middle_name"
                            label="Middle Name"
                    ></v-text-field>
                </v-row>
                <v-row>
                    <v-text-field
                            v-model="nurse.last_name"
                            label="Last Name"
                    ></v-text-field>
                </v-row>
                <v-row>
                    <v-text-field
                            v-model="nurse.phone_number"
                            label="Phone Number"
                    ></v-text-field>
                </v-row>
                <v-row>
                    <v-text-field
                            v-model="nurse.ssn"
                            :type="ssn_hidden ? 'text' : 'password'"
                            :append-icon="ssn_hidden ? 'mdi-eye' : 'mdi-eye-off'"
                            @click:append="ssn_hidden = !ssn_hidden"
                            label="Social Security Number"
                    ></v-text-field>
                </v-row>
                <v-row>
                    <span class="block mt-3">Receive SMS Notifications: </span>
                    <v-switch class="mt-1 ms-3" v-model="nurse.receives_sms"></v-switch>
                </v-row>
                <v-row>
                    <span class="block mt-3">Receive Push Notifications: </span>
                    <v-switch class="mt-1 ms-3" v-model="nurse.receives_push_notification"></v-switch>
                </v-row>

                <v-row>
                    <v-text-field
                            v-model="nurse.email_address"
                            label="Email Address"
                    ></v-text-field>
                </v-row>
                <v-row>
                    <v-menu
                            v-model="birthday_menu"
                            :close-on-content-click="false"
                            min-width="290px"
                            max-width="290px"
                            :nudge-right="40"
                            transition="scale-transition"
                            :offset-y="true"
                    >
                        <template v-slot:activator="{ on, attrs }">
                            <v-text-field
                                autocomplete="off"
                                v-model="nurse.birthday"
                                v-on="on"
                                v-bind="attrs"
                                prepend-icon="mdi-calendar"
                                label="Birthday"
                            ></v-text-field>
                        </template>
                        <v-date-picker
                            v-if="birthday_menu"
                            v-model="nurse.birthday"
                            no-title
                            scrollable>
                        </v-date-picker>
                    </v-menu>
                </v-row>
                <v-row>
                    <v-menu
                            v-model="skin_test_menu"
                            :close-on-content-click="false"
                            min-width="290px"
                            max-width="290px"
                            :nudge-right="40"
                            transition="scale-transition"
                            :offset-y="true"
                    >
                        <template v-slot:activator="{ on, attrs }">
                            <v-text-field
                                autocomplete="off"
                                v-model="nurse.skin_test_expiration"
                                v-on="on"
                                v-bind="attrs"
                                prepend-icon="mdi-calendar"
                                label="Skin Test Expiration Date"
                            ></v-text-field>
                        </template>
                        <v-date-picker
                            v-if="skin_test_menu"
                            v-model="nurse.skin_test_expiration"
                            no-title
                            scrollable>
                        </v-date-picker>
                    </v-menu>
                </v-row>
                <v-row>
                    <v-menu
                            v-model="license_menu"
                            :close-on-content-click="false"
                            min-width="290px"
                            max-width="290px"
                            :nudge-right="40"
                            transition="scale-transition"
                            :offset-y="true"
                    >
                        <template v-slot:activator="{ on, attrs }">
                            <v-text-field
                                    autocomplete="off"
                                    v-model="nurse.license_expiration"
                                    v-on="on"
                                    v-bind="attrs"
                                    prepend-icon="mdi-calendar"
                                    label="License Expiration Date"
                            ></v-text-field>
                        </template>
                        <v-date-picker
                                v-if="license_menu"
                                v-model="nurse.license_expiration"
                                no-title
                                scrollable>
                        </v-date-picker>
                    </v-menu>
                </v-row>
                <v-row>
                    <v-menu
                            v-model="cpr_menu"
                            :close-on-content-click="false"
                            min-width="290px"
                            max-width="290px"
                            :nudge-right="40"
                            transition="scale-transition"
                            :offset-y="true"
                    >
                        <template v-slot:activator="{ on, attrs }">
                            <v-text-field
                                    autocomplete="off"
                                    v-model="nurse.cpr_expiration"
                                    v-on="on"
                                    v-bind="attrs"
                                    prepend-icon="mdi-calendar"
                                    label="CPR Expiration Date"
                            ></v-text-field>
                        </template>
                        <v-date-picker
                                v-if="cpr_menu"
                                v-model="nurse.cpr_expiration"
                                no-title
                                scrollable>
                        </v-date-picker>
                    </v-menu>
                </v-row>
                <v-row>
                    <v-radio-group
                            row
                            class="w-100"
                            v-model="nurse.credentials">
                        <v-radio
                                label="CNA"
                                value="CNA"></v-radio>
                        <v-radio
                                label="CMT"
                                value="CMT"></v-radio>
                        <v-radio
                                label="LPN"
                                value="LPN"></v-radio>
                        <v-radio
                                label="RN"
                                value="RN"></v-radio>
                    </v-radio-group>
                </v-row>
                
                <v-row>
                    <label>Address Information</label>
                </v-row>
                <v-row>
                    <v-text-field
                            v-model="nurse.street_address"
                            label="Street Address"
                    ></v-text-field>
                </v-row>
                <v-row>
                    <v-text-field
                            v-model="nurse.street_address_2"
                            label="Street Address 2"
                    ></v-text-field>
                </v-row>
                <v-row>
                    <v-text-field
                            v-model="nurse.zipcode"
                            label="Zipcode"
                    ></v-text-field>
                </v-row>
                <v-row>
                    <v-text-field
                            v-model="nurse.city"
                            label="City"
                    ></v-text-field>
                </v-row>
                <v-row>
                    <v-autocomplete
                            v-model="nurse.state"
                            :items="states"
                            label="State"
                            clearable
                            width="300"
                    ></v-autocomplete>
                </v-row>
                 <v-row>
                    <label>Device Configuration</label>
                </v-row>
                <v-row>
                    <v-text-field
                            v-model="nurse.app_version"
                            label="Current App Version"
                            readonly
                            disabled
                    ></v-text-field>
                </v-row>
            </v-container>
        </v-app>
    `,
    props: [
        'id'
    ],
    data: function() {
        return {
            error: null,
            states: [],
            nurse: {},
            loading: false,
            birthday_menu: false,
            skin_test_menu: false,
            license_menu: false,
            cpr_menu: false,
            ssn_hidden: false
        };
    },
    created() {
        this.loadNurseBasicInfo();
    },
    mounted() {
        this.$root.$on('saveMemberData', function() {
            this.saveData()
        }.bind(this));
    },
    computed: {

    },
    methods: {
        loadNurseBasicInfo() {
            var data = {
                id: this.id,
            };
            this.loading = true;
            modRequest.request('sa.member.load_nurse_basic_info', {}, data, function(response) {
                if(response.success) {
                    this.nurse = response.nurse;
                    this.states = [];
                    for (let i = 0; i < response.states.length; i++) {
                        this.states.push(response.states[i].name);
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
            }.bind(this), function(response) {
                console.log('Failed');
                console.log(response);
            });
        },
        saveData() {
            var data = {
                id: this.id,
                nurse: this.nurse
            };

            this.loading = true;
            modRequest.request('sa.member.save_nurse_basic_info', {}, data, function(response) {
                if(response.success) {
                    $.growl.notice({ title: "Success!", message: "Changes to nurse saved.", size: "large" });
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
            }.bind(this), function(response) {
                console.log('Failed');
                console.log(response);
            });
        }
    }
});
