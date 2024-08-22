Vue.component('emergency-contacts-view', {
    template: /*html*/`
        <v-app>
            <v-container>
                <nst-overlay :loading="loading"></nst-overlay>
                <nst-error-notification
                        v-if="error"
                        :error="error"></nst-error-notification>
                <v-row>
                    <label>Emergency Contact One</label>
                </v-row>
                <v-row>
                    <v-text-field
                            label="First Name"
                            v-model="emergency_contact_one.first_name"
                    ></v-text-field>
                </v-row>
                <v-row>
                    <v-text-field
                            label="Last Name"
                            v-model="emergency_contact_one.last_name"
                    ></v-text-field>
                </v-row>
                <v-row>
                    <v-text-field
                            label="Phone Number"
                            v-model="emergency_contact_one.phone_number"
                    ></v-text-field>
                </v-row>
                <v-row>
                    <v-text-field
                            label="Relationship"
                            v-model="emergency_contact_one.relationship"
                    ></v-text-field>
                </v-row>
                <v-row>
                    <label>Emergency Contact Two</label>
                </v-row>
                <v-row>
                    <v-text-field
                            label="First Name"
                            v-model="emergency_contact_two.first_name"
                    ></v-text-field>
                </v-row>
                <v-row>
                    <v-text-field
                            label="Last Name"
                            v-model="emergency_contact_two.last_name"
                    ></v-text-field>
                </v-row>
                <v-row>
                    <v-text-field
                            label="Phone Number"
                            v-model="emergency_contact_two.phone_number"
                    ></v-text-field>
                </v-row>
                <v-row>
                    <v-text-field
                            label="Relationship"
                            v-model="emergency_contact_two.relationship"
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
            loading: false,
            emergency_contact_one: {},
            emergency_contact_two: {}
        }
    },
    created() {
        this.loadEmergencyContacts();
    },
    mounted() {
        this.$root.$on('saveMemberData', function () {
            this.saveData()
        }.bind(this));
    },
    methods: {
        loadEmergencyContacts() {
            let data = {
                id: this.id,
            };
            this.loading = true;
            modRequest.request('sa.member.load_nurse_emergency_contacts', {}, data, function (response) {
                if (response.success) {
                    if (response.emergency_contact_one) {
                        this.emergency_contact_one.first_name = response.emergency_contact_one.first_name;
                        this.emergency_contact_one.last_name = response.emergency_contact_one.last_name;
                        this.emergency_contact_one.phone_number = response.emergency_contact_one.phone_number;
                        this.emergency_contact_one.relationship = response.emergency_contact_one.relationship;
                    }
                    if (response.emergency_contact_two) {
                        this.emergency_contact_two.first_name = response.emergency_contact_two.first_name;
                        this.emergency_contact_two.last_name = response.emergency_contact_two.last_name;
                        this.emergency_contact_two.phone_number = response.emergency_contact_two.phone_number;
                        this.emergency_contact_two.relationship = response.emergency_contact_two.relationship;
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
                this.loading = false;
            });
        },
        saveData() {

            let data = {
                id: this.id,
                emergency_contact_one: {
                    first_name: this.emergency_contact_one.first_name,
                    last_name: this.emergency_contact_one.last_name,
                    phone_number: this.emergency_contact_one.phone_number,
                    relationship: this.emergency_contact_one.relationship
                },
                emergency_contact_two: {
                    first_name: this.emergency_contact_two.first_name,
                    last_name: this.emergency_contact_two.last_name,
                    phone_number: this.emergency_contact_two.phone_number,
                    relationship: this.emergency_contact_two.relationship
                }
            };

            this.loading = true;
            modRequest.request('sa.member.save_nurse_emergency_contacts', {}, data, function (response) {
                if (response.success) {
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