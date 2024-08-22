Vue.component('sa-application-licenses', {
template: /*html*/`
<div>

<h2 style="margin-top: 50px;">Nurse License 1</h2>
<v-row>

    <v-col cols="12" md="2">

        <v-card
            class="member-file-card"
            style="margin-top: 20px;"
            color="#ECEFF1"
            :href="licenses.nurse_license_1.url"
            target="_blank"
            v-show="licenses.nurse_license_1.name"
        >
            <v-card-text>

                <div class="member-file-icon" style="margin-bottom: 20px;">
                    <v-icon color="#4FC3F7">mdi-file</v-icon>
                </div>
                <div class="member-file-name-container">
                    <span class="member-file-name" style="color: #212121;">{{ licenses.nurse_license_1.name }}</span>
                </div>
                <div class="member-file-tag-container mt-1">
                    <span class="member-file-tag-name" style="color: #0277BD;">{{ licenses.nurse_license_1.fileTag }}</span>
                </div>

            </v-card-text>
        </v-card>
    </v-col>

    <v-col cols="12" md="4" style="margin-top: 25px;">

        <v-text-field
            v-model="licenses.nurse_license_1.state"
            label="License 1 State"
            disabled
        ></v-text-field>
        
        <v-text-field
            v-model="licenses.nurse_license_1.license_number"
            label="License 1 Number"
            disabled
        ></v-text-field>
        
        <v-text-field
            v-model="licenses.nurse_license_1.full_name"
            label="License 1 Full Name"
            disabled
        ></v-text-field>
        
    </v-col>
    <v-col cols="12" md="4" style="margin-top: 25px;">

        <v-menu
            v-model="showLicense1Expiration"
            :close-on-content-click="false"
            transition="scale-transition"
            offset-y
            min-width="auto"
        >
            <template v-slot:activator="{ on, attrs }">
                <v-text-field
                    v-model="licenses.nurse_license_1.expiration"
                    label="License 1 Expiration"
                    prepend-icon="mdi-calendar"
                    readonly
                    v-bind="attrs"
                    v-on="on"
                ></v-text-field>
            </template>
            <v-date-picker
                v-model="licenses.nurse_license_1.expiration"
                no-title
                scrollable
            >
                <v-spacer></v-spacer>
                <v-btn
                    text
                    color="primary"
                    @click="showLicense1Expiration = false"
                >Cancel</v-btn>
                <v-btn
                    text
                    color="primary"
                    @click="showLicense1Expiration = false"
                >OK</v-btn>
            </v-date-picker>
        </v-menu>

        <div v-show="licenses.nurse_license_1.accepted == null">

            <v-btn
                color="success"
                dark
                text
                @click="approveLicense(1)"
            >Approve License 1</v-btn>

            <v-dialog v-model="showLicense1ApproveModal" max-width="500px">
                <v-card>
                    <v-card-title class="headline">Approve license 1?</v-card-title>
                    <v-card-actions>
                        <v-spacer></v-spacer>
                        <v-btn color="success" text @click="saveApprovedLicense(1)">Confirm</v-btn>
                        <v-btn color="error" text @click="showLicense1ApproveModal = false">Cancel</v-btn>
                    </v-card-actions>
                </v-card>
            </v-dialog>

            <v-btn
                color="error"
                dark
                text
                @click="rejectLicense(1)"
            >Reject License 1</v-btn>

            <v-dialog v-model="showLicense1RejectModal" max-width="800px">
                <v-card>
                    <v-card-title class="headline">Reject license 1?</v-card-title>

                    <v-card-text>
                        <p v-show="license1RejectMessageReminder">Please provide a reason for the candidate for rejecting this license.</p>

                        <v-text-field
                            solo
                            v-model="license1RejectMessage"
                            hint="Please provide a reason for the candidate for rejecting this license."
                            persistent-hint
                        ></v-text-field>
                    </v-card-text>

                    <v-card-actions>
                        <v-spacer></v-spacer>

                        <v-btn color="success" text @click="saveRejectedLicense(1)">Confirm</v-btn>
                        <v-btn color="error" text @click="showLicense1RejectModal = false">Cancel</v-btn>
                    </v-card-actions>
                </v-card>
            </v-dialog>
        
        </div>

        <div v-show="licenses.nurse_license_1.accepted === false">
            <h1>License 1 Rejected</h1>
        </div>

        <div v-show="licenses.nurse_license_1.accepted === true">
            <h1>License 1 Approved</h1>
        </div>
    </v-col>

</v-row>

<h2 v-show="licenses.nurse_license_2.name" style="margin-top: 50px;">Nurse License 2</h2>
<v-row v-show="licenses.nurse_license_2.name">

    <v-col cols="12" md="2">

        <v-card
            class="member-file-card"
            style="margin-top: 20px;"
            color="#ECEFF1"
            :href="licenses.nurse_license_2.url"
            target="_blank"
            v-show="licenses.nurse_license_2.name"
        >
            <v-card-text>

                <div class="member-file-icon" style="margin-bottom: 20px;">
                    <v-icon color="#4FC3F7">mdi-file</v-icon>
                </div>
                <div class="member-file-name-container">
                    <span class="member-file-name" style="color: #212121;">{{ licenses.nurse_license_2.name }}</span>
                </div>
                <div class="member-file-tag-container mt-1">
                    <span class="member-file-tag-name" style="color: #0277BD;">{{ licenses.nurse_license_2.fileTag }}</span>
                </div>

            </v-card-text>
        </v-card>
    </v-col>

    <v-col cols="12" md="4" style="margin-top: 25px;">

        <v-text-field
            v-model="licenses.nurse_license_2.state"
            label="License 2 State"
            disabled
        ></v-text-field>
        
        <v-text-field
            v-model="licenses.nurse_license_2.license_number"
            label="License 2 Number"
            disabled
        ></v-text-field>
        
        <v-text-field
            v-model="licenses.nurse_license_2.full_name"
            label="License 2 Full Name"
            disabled
        ></v-text-field>
        
    </v-col>
    <v-col cols="12" md="4" style="margin-top: 25px;">

        <v-menu
            v-model="showLicense2Expiration"
            :close-on-content-click="false"
            transition="scale-transition"
            offset-y
            min-width="auto"
        >
            <template v-slot:activator="{ on, attrs }">
                <v-text-field
                    v-model="licenses.nurse_license_2.expiration"
                    label="License 2 Expiration"
                    prepend-icon="mdi-calendar"
                    readonly
                    v-bind="attrs"
                    v-on="on"
                ></v-text-field>
            </template>
            <v-date-picker
                v-model="licenses.nurse_license_2.expiration"
                no-title
                scrollable
            >
                <v-spacer></v-spacer>
                <v-btn
                    text
                    color="primary"
                    @click="showLicense2Expiration = false"
                >Cancel</v-btn>
                <v-btn
                    text
                    color="primary"
                    @click="showLicense2Expiration = false"
                >OK</v-btn>
            </v-date-picker>
        </v-menu>

        <div v-show="licenses.nurse_license_2.accepted == null">

            <v-btn
                color="success"
                dark
                text
                @click="approveLicense(2)"
            >Approve License 2</v-btn>

            <v-dialog v-model="showLicense2ApproveModal" max-width="500px">
                <v-card>
                    <v-card-title class="headline">Approve license 2?</v-card-title>
                    <v-card-actions>
                        <v-spacer></v-spacer>
                        <v-btn color="success" text @click="saveApprovedLicense(2)">Confirm</v-btn>
                        <v-btn color="error" text @click="showLicense2ApproveModal = false">Cancel</v-btn>
                    </v-card-actions>
                </v-card>
            </v-dialog>

            <v-btn
                color="error"
                dark
                text
                @click="rejectLicense(1)"
            >Reject License 2</v-btn>

            <v-dialog v-model="showLicense2RejectModal" max-width="800px">
                <v-card>
                    <v-card-title class="headline">Reject license 2?</v-card-title>

                    <v-card-text>
                        <p v-show="license2RejectMessageReminder">Please provide a reason for the candidate for rejecting this license.</p>

                        <v-text-field
                            solo
                            v-model="license2RejectMessage"
                            hint="Please provide a reason for the candidate for rejecting this license."
                            persistent-hint
                        ></v-text-field>
                    </v-card-text>

                    <v-card-actions>
                        <v-spacer></v-spacer>

                        <v-btn color="success" text @click="saveRejectedLicense(2)">Confirm</v-btn>
                        <v-btn color="error" text @click="showLicense2RejectModal = false">Cancel</v-btn>
                    </v-card-actions>
                </v-card>
            </v-dialog>
        
        </div>

        <div v-show="licenses.nurse_license_2.accepted === false">
            <h1>License 2 Rejected</h1>
        </div>

        <div v-show="licenses.nurse_license_2.accepted === true">
            <h1>License 2 Approved</h1>
        </div>
    </v-col>

</v-row>

<h2 v-show="licenses.nurse_license_3.name" style="margin-top: 50px;">Nurse License 3</h2>
<v-row v-show="licenses.nurse_license_3.name">

    <v-col cols="12" md="2">

        <v-card
            class="member-file-card"
            style="margin-top: 20px;"
            color="#ECEFF1"
            :href="licenses.nurse_license_3.url"
            target="_blank"
            v-show="licenses.nurse_license_3.name"
        >
            <v-card-text>

                <div class="member-file-icon" style="margin-bottom: 20px;">
                    <v-icon color="#4FC3F7">mdi-file</v-icon>
                </div>
                <div class="member-file-name-container">
                    <span class="member-file-name" style="color: #212121;">{{ licenses.nurse_license_3.name }}</span>
                </div>
                <div class="member-file-tag-container mt-1">
                    <span class="member-file-tag-name" style="color: #0277BD;">{{ licenses.nurse_license_3.fileTag }}</span>
                </div>

            </v-card-text>
        </v-card>
    </v-col>

    <v-col cols="12" md="4" style="margin-top: 25px;">

        <v-text-field
            v-model="licenses.nurse_license_3.state"
            label="License 3 State"
            disabled
        ></v-text-field>
        
        <v-text-field
            v-model="licenses.nurse_license_3.license_number"
            label="License 3 Number"
            disabled
        ></v-text-field>
        
        <v-text-field
            v-model="licenses.nurse_license_3.full_name"
            label="License 3 Full Name"
            disabled
        ></v-text-field>
        
    </v-col>
    <v-col cols="12" md="4" style="margin-top: 25px;">

        <v-menu
            v-model="showLicense3Expiration"
            :close-on-content-click="false"
            transition="scale-transition"
            offset-y
            min-width="auto"
        >
            <template v-slot:activator="{ on, attrs }">
                <v-text-field
                    v-model="licenses.nurse_license_3.expiration"
                    label="License 3 Expiration"
                    prepend-icon="mdi-calendar"
                    readonly
                    v-bind="attrs"
                    v-on="on"
                ></v-text-field>
            </template>
            <v-date-picker
                v-model="licenses.nurse_license_3.expiration"
                no-title
                scrollable
            >
                <v-spacer></v-spacer>
                <v-btn
                    text
                    color="primary"
                    @click="showLicense3Expiration = false"
                >Cancel</v-btn>
                <v-btn
                    text
                    color="primary"
                    @click="showLicense3Expiration = false"
                >OK</v-btn>
            </v-date-picker>
        </v-menu>

        <div v-show="licenses.nurse_license_3.accepted == null">

            <v-btn
                color="success"
                dark
                text
                @click="approveLicense(3)"
            >Approve License 3</v-btn>

            <v-dialog v-model="showLicense3ApproveModal" max-width="500px">
                <v-card>
                    <v-card-title class="headline">Approve license 3?</v-card-title>
                    <v-card-actions>
                        <v-spacer></v-spacer>
                        <v-btn color="success" text @click="saveApprovedLicense(3)">Confirm</v-btn>
                        <v-btn color="error" text @click="showLicense3ApproveModal = false">Cancel</v-btn>
                    </v-card-actions>
                </v-card>
            </v-dialog>

            <v-btn
                color="error"
                dark
                text
                @click="rejectLicense(3)"
            >Reject License 3</v-btn>

            <v-dialog v-model="showLicense3RejectModal" max-width="800px">
                <v-card>
                    <v-card-title class="headline">Reject license 3?</v-card-title>

                    <v-card-text>
                        <p v-show="license3RejectMessageReminder">Please provide a reason for the candidate for rejecting this license.</p>

                        <v-text-field
                            solo
                            v-model="license3RejectMessage"
                            hint="Please provide a reason for the candidate for rejecting this license."
                            persistent-hint
                        ></v-text-field>
                    </v-card-text>

                    <v-card-actions>
                        <v-spacer></v-spacer>

                        <v-btn color="success" text @click="saveRejectedLicense(3)">Confirm</v-btn>
                        <v-btn color="error" text @click="showLicense3RejectModal = false">Cancel</v-btn>
                    </v-card-actions>
                </v-card>
            </v-dialog>
        
        </div>

        <div v-show="licenses.nurse_license_3.accepted === false">
            <h1>License 3 Rejected</h1>
        </div>

        <div v-show="licenses.nurse_license_3.accepted === true">
            <h1>License 3 Approved</h1>
        </div>
    </v-col>

</v-row>
</div>
`,
watch: {},
computed: {},
created() {},
props: {

    licenses: Object,
    application_id: Number,
},
data: () => ({

    showLicense1Expiration: false,
    showLicense1ApproveModal: false,
    showLicense1RejectModal: false,
    license1RejectMessage: '',
    license1RejectMessageReminder: false,

    showLicense2Expiration: false,
    showLicense2ApproveModal: false,
    showLicense2RejectModal: false,
    license2RejectMessage: '',
    license2RejectMessageReminder: false,

    showLicense3Expiration: false,
    showLicense3ApproveModal: false,
    showLicense3RejectModal: false,
    license3RejectMessage: '',
    license3RejectMessageReminder: false,
}),
methods: {

    approveLicense(license_number) {

        if (license_number == 1) {

            if (this.licenses.nurse_license_1.expiration) {
                this.showLicense1ApproveModal = true;
            } else {
                this.showSnackbar('Please select an expiration date for license 1', 'error', 5000);
            }
        } else if (license_number == 2) {
            
            if (this.licenses.nurse_license_2.expiration) {
                this.showLicense2ApproveModal = true;
            } else {
                this.showSnackbar('Please select an expiration date for license 2', 'error', 5000);
            }
        } else if (license_number == 3) {

            if (this.licenses.nurse_license_3.expiration) {
                this.showLicense3ApproveModal = true;
            } else {
                this.showSnackbar('Please select an expiration date for license 3', 'error', 5000);
            }
        }
    },
    rejectLicense(license_number) {

        if (license_number == 1) {
            this.showLicense1RejectModal = true;
        } else if (license_number == 2) {
            this.showLicense2RejectModal = true;
        } else if (license_number == 3) {
            this.showLicense3RejectModal = true;
        }
    },
    saveApprovedLicense(license_number) {
        
        data = {
        
            application_id: this.application_id,
            license_number: license_number,

            license1_id: this.licenses.nurse_license_1.id,
            license2_id: this.licenses.nurse_license_2.id,
            license3_id: this.licenses.nurse_license_3.id,

            license1_expiration: this.licenses.nurse_license_1.expiration,
            license2_expiration: this.licenses.nurse_license_2.expiration,
            license3_expiration: this.licenses.nurse_license_3.expiration,
        }
        
        modRequest.request('nurse.application.acceptLicense', null, data, function(response) {
            if (response.success) {
        
                if (license_number == 1) {
                    this.licenses.nurse_license_1.accepted = true;
                } else if (license_number == 2) {
                    this.licenses.nurse_license_2.accepted = true;
                } else if (license_number == 3) {
                    this.licenses.nurse_license_3.accepted = true;
                }
                // close modal
                this.showLicense1ApproveModal = false;
                this.showLicense2ApproveModal = false;
                this.showLicense3ApproveModal = false;
                // show snackbar success if no message
                if (!response.message) {
                    this.showSnackbar('License successfully approved', 'success', 5000);
                } else {
                    this.showSnackbar(response.message, 'error', 5000);
                }
            }
        }.bind(this));
    },
    saveRejectedLicense(license_number) {

        let rejectMessage = '';
        if (license_number == 1) {

            if (this.license1RejectMessage == '') {

                this.license1RejectMessageReminder = true;
                return;
            } else {
                rejectMessage = this.license1RejectMessage;
            }
        } else if (license_number == 2) {

            if (this.license2RejectMessage == '') {

                this.license2RejectMessageReminder = true;
                return;
            } else {
                rejectMessage = this.license2RejectMessage;
            }
        } else if (license_number == 3) {

            if (this.license3RejectMessage == '') {

                this.license3RejectMessageReminder = true;
                return;
            } else {
                rejectMessage = this.license3RejectMessage;
            }
        }

        data = {
            
            application_id: this.application_id,
            message: rejectMessage,
            license_number: license_number,

            license1_id: this.licenses.nurse_license_1.id,
            license2_id: this.licenses.nurse_license_2.id,
            license3_id: this.licenses.nurse_license_3.id,
        }
        
        modRequest.request('nurse.application.rejectLicense', null, data, function(response) {
            if (response.success) {

                if (license_number == 1) {
                    this.licenses.nurse_license_1.accepted = false;
                } else if (license_number == 2) {
                    this.licenses.nurse_license_2.accepted = false;
                } else if (license_number == 3) {
                    this.licenses.nurse_license_3.accepted = false;
                }
                // close modal
                this.showLicense1RejectModal = false;
                this.showLicense2RejectModal = false;
                this.showLicense3RejectModal = false;
                if (!response.message) {
                    this.showSnackbar('License successfully rejected', 'success', 5000);
                } else {
                    this.showSnackbar(response.message, 'error', 5000);
                }                
            }
        }.bind(this));
    },
    showSnackbar(message, color, timeout) {
    
        this.$emit('showSnackbar', {
            message,
            color,
            timeout
        });
    },
},
});