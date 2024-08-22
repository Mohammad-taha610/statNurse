Vue.component('application-profile-view', {
template: /*html*/`
<div style="margin: 50px 100px 100px 100px;">

<v-alert v-show="approved" type="success">Application Approved</v-alert>
<v-alert v-show="declined" type="error">Application Declined</v-alert>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 50px;">

    <template>
        <v-tabs>
            <v-tab @click="section = 'basic_info'">Basic Info</v-tab>
            <v-tab @click="section = 'application'">Application</v-tab>
            <v-tab @click="section = 'licenses'">Licenses</v-tab>
            <v-tab @click="section = 'files'">Files</v-tab>
            <v-tab @click="section = 'drug_screen'">Drug Screen</v-tab>
            <v-tab @click="section = 'background_check'">Background Check</v-tab>
            <v-tab @click="section = 'messaging'">Messaging</v-tab>
        </v-tabs>
    </template>

    <div style="display: flex; justify-content: space-between; align-items: center;">

        <v-btn :disabled="!can_approve || approved || declined" color="success" @click="approveNurseDialog = true">Approve Application</v-btn>
        <v-btn color="success" @click="approveNurseDialog = true" style="margin-left: 20px;">Temporary Approve</v-btn>
        <v-btn :disabled="approved || declined" color="error" @click="denyNurseDialog = true" style="margin-left: 20px;">Decline Application</v-btn>

    </div>

</div>

<sa-application-basic-info
    v-show="section == 'basic_info'"
    :basic_info="basic_info"
    :application_id="application_id"
    @showSnackbar="showSnackbar"
></sa-application-basic-info>

<sa-application-application
    v-show="section == 'application'"
    :application="application"
    :application_id="application_id"
    @showSnackbar="showSnackbar"
></sa-application-application>

<sa-application-licenses
    v-show="section == 'licenses'"
    :licenses="licenses"
    :application_id="application_id"
    @showSnackbar="showSnackbar"
></sa-application-licenses>

<sa-application-files
    v-show="section == 'files'"
    :files="files"
    :application_id="application_id"
    @showSnackbar="showSnackbar"
></sa-application-files>

<sa-drug-screen
    v-show="section == 'drug_screen'"
    :drug_screen="drug_screen"
    :drug_screen_view="drug_screen_view"
    :drug_screen_checkr_uri="drug_screen_checkr_uri"
    :application_id="application_id"
    @showSnackbar="showSnackbar"
></sa-drug-screen>

<sa-background-check
    v-show="section == 'background_check'"
    :background_check="background_check"
    background_check_checkr_uri="background_check_checkr_uri"
    :application_id="application_id"
    @showSnackbar="showSnackbar"
></sa-background-check>

<sa-application-messaging
    v-show="section == 'messaging'"
    :application_id="application_id"
    @showSnackbar="showSnackbar"
></sa-application-messaging>

<v-btn style="position: fixed; bottom: 50px; right: 50px;" fab bottom right @click="scrollToTop">
    <v-icon>mdi-arrow-up</v-icon>
</v-btn>

<v-dialog v-model="approveNurseDialog" max-width="500">
    <v-card>
        <v-card-title class="headline">Confirm Action</v-card-title>

        <v-card-text>
            Are you sure you want to approve application?
        </v-card-text>

        <v-card-actions class="justify-end">
            <v-btn color="green" @click="approveNurse">Confirm</v-btn>
            <v-btn color="black" @click="approveNurseDialog = false" style="margin-left: 10px;">Cancel</v-btn>
        </v-card-actions>
    </v-card>
</v-dialog>

<v-dialog v-model="denyNurseDialog" max-width="500">
    <v-card>
        <v-card-title class="headline">Confirm Action</v-card-title>

        <v-card-text>
          <v-textarea v-model="customDeclineMessage" persistent-hint hint="Message for declined applicant"></v-textarea>
        </v-card-text>

        <v-card-actions class="justify-end">
            <v-btn color="red" @click="denyNurse">decline</v-btn>
            <v-btn color="black" @click="denyNurseDialog = false" style="margin-left: 10px;">Cancel</v-btn>
        </v-card-actions>
    </v-card>
</v-dialog>

<v-snackbar
    v-model="snackbar"
    :color="snackbarColor"
    :timeout="snackbarTimeout"
>{{ snackbarMessage }}</v-snackbar>

</div>
`,
watch: {},
computed: {},
created() {
    this.loadApplicationData();
},
props: {
    application_id: Number,
  },
data: () => ({

    section: 'basic_info',

    snackbar: false,
    snackbarMessage: '',
    snackbarTimeout: -1,
    snackbarColor: '',

    basic_info: {},
    application: {

        one_year_ltc_experience: '',
        one_year_experience_explanation: '',
        currently_employed: '',
        company1: {

            name: '',
            supervisor_name: '',
            address: '',
            city: '',
            state: '',
            zipcode: '',
            phone: '',
            email: '',
            job_title: '',
            start_date: '',
            end_date: '',
            responsibilities: '',
            reason_for_leaving: '',
            may_we_contact_employer: '',
        },
        company2: {

            name: '',
            supervisor_name: '',
            address: '',
            city: '',
            state: '',
            zipcode: '',
            phone: '',
            email: '',
            job_title: '',
            start_date: '',
            end_date: '',
            responsibilities: '',
            reason_for_leaving: '',
            may_we_contact_employer: '',
        },
        company3: {

            name: '',
            supervisor_name: '',
            address: '',
            city: '',
            state: '',
            zipcode: '',
            phone: '',
            email: '',
            job_title: '',
            start_date: '',
            end_date: '',
            responsibilities: '',
            reason_for_leaving: '',
            may_we_contact_employer: '',
        },  
        hs_or_ged: '',
        high_school: {

            name: '',
            city: '',
            state: '',
            year_graduated: '',
        },
        ged: {

            name: '',
            city: '',
            state: '',
            year_graduated: '',
        },
        college: {

            name: '',
            city: '',
            state: '',
            year_graduated: '',
            subjects_major_degree: '',
        },
        other: {

            name: '',
            city: '',
            state: '',
            year_graduated: '',
            subjects_major_degree: '',
        },
        reference1: {

            name: '',
            relationship: '',
            company: '',
            phone: '',
        },
        reference2: {

            name: '',
            relationship: '',
            company: '',
            phone: '',
        },
        reference3: {

            name: '',
            relationship: '',
            company: '',
            phone: '',
        },
        licenses_and_certifications: {},
        medical_history: {},
        injury_explanation: '',
        routine_vaccinations: {},
        hepatitis_b: '',
        hepatitis_a: '',
        covid_19: '',
        covid_19_exemption: '',
        positive_tb_screening: '',
        positive_tb_date: '',
        xray: '',
        xray_date: '',
        pay_type: '',
        account_type: '',
        account_number: '',
        routing_number: '',
        bank_name: '',
        heard_about_us: '',
        heard_about_us_other: '',
        referrer: '',
    },
    licenses: {

        nurse_license_1: {

            url: '',
            name: '',
            fileTag: '',
            state: '',
            license_number: '',
            full_name: '',
            expiration: '',
            accepted: '',
        },
        nurse_license_2: {

            url: '',
            name: '',
            fileTag: '',
            state: '',
            license_number: '',
            full_name: '',
            expiration: '',
            accepted: '',
        },
        nurse_license_3: {

            url: '',
            name: '',
            fileTag: '',
            state: '',
            license_number: '',
            full_name: '',
            expiration: '',
            accepted: '',
        },
    },
    files: {

        driver_license: {

            url: '',
            name: '',
            fileTag: '',
        },
        social_security: {

            url: '',
            name: '',
            fileTag: '',
        },
        tb_skin_test: {

            url: '',
            name: '',
            fileTag: '',
        },

        cpr_card: {

            url: '',
            name: '',
            fileTag: '',
        },
        bls_acl: {

            url: '',
            name: '',
            fileTag: '',
        },
        covid_vaccine: {

            url: '',
            name: '',
            fileTag: '',
        },

        id_badge: {

            url: '',
            name: '',
            fileTag: '',
        },
    },
    drug_screen: {},
    drug_screen_checkr_uri: '',
    background_check: {},
    background_check_checkr_uri: '',

    approveNurseDialog: false,
    denyNurseDialog: false,
    customDeclineMessage: '',

    can_approve: false,
    approved: null,
    declined: null,
}),
methods: {

    loadApplicationData() {

        data = {
            application_id: this.application_id,
        }
        
        modRequest.request('nurse.application.sa.loadApplicationData', null, data, function(response) {
            if (response.success) {

                this.basic_info = response.basic_info;
                if (this.basic_info.citizen_of_us == true) { this.basic_info.authorized_to_work_in_us = true; }

                this.application = response.application;
                if (this.application.company1.may_we_contact_employer == true) { this.application.company1.may_we_contact_employer = 'Yes'; }
                if (this.application.company1.may_we_contact_employer == false) { this.application.company1.may_we_contact_employer = 'No'; }
                if (this.application.company2.may_we_contact_employer == true) { this.application.company2.may_we_contact_employer = 'Yes'; }
                if (this.application.company2.may_we_contact_employer == false) { this.application.company2.may_we_contact_employer = 'No'; }
                if (this.application.company3.may_we_contact_employer == true) { this.application.company3.may_we_contact_employer = 'Yes'; }
                if (this.application.company3.may_we_contact_employer == false) { this.application.company3.may_we_contact_employer = 'No'; }
                
                this.licenses = response.files.licenses;
                this.files = response.files.files;
                this.drug_screen = response.drug_screen;
                this.drug_screen_view = JSON.stringify(response.drug_screen, null, 2);

                let checkr_domain = response.checkr_domain.replace(/\/$/, "");
                checkr_domain = checkr_domain.replace('api', 'dashboard');
                // for staging env additional changes to uri need to be made
                if (checkr_domain.includes('staging')) {

                    checkr_domain = checkr_domain.replace('checkr', 'checkrhq');
                    checkr_domain = checkr_domain.replace('com', 'net');
                }

                this.drug_screen_checkr_uri = checkr_domain + this.drug_screen.uri.replace('/v1', '');

                if (response.background_check) {

                    this.background_check_checkr_uri = checkr_domain + response.background_check.uri.replace('/v1', '');
                    this.background_check = JSON.stringify(response.background_check, null, 2);
                } else {

                    this.background_check_checkr_uri = null;
                    this.background_check = null;
                }

                this.can_approve = response.can_approve;
                if (response.approved != null) {
                    this.approved = true;
                }
                if (response.declined != null) {
                    this.declined = true;
                }
            }
        }.bind(this));
    },
    showSnackbar(snackbarInfo) {

        this.snackbarMessage = snackbarInfo.message
        this.snackbarColor = snackbarInfo.color
        this.snackbarTimeout = snackbarInfo.timeout
        this.snackbar = true
    },
    scrollToTop() {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    },
    approveNurse() {

        // if (!this.can_approve) {

            // this.approveNurseDialog = false;
        //     return;
        // } RESTORE THIS LATER

        this.can_approve = false;
        
        data = {
            application_id: this.application_id,
        }
        
        modRequest.request('nurse.application.newApprove', null, data, function(response) {
            if (response.success) {
                    
                this.showSnackbar({
                    message: 'Application approved successfully',
                    color: 'success',
                    timeout: 3000,
                });
                
                this.approved = true;
                this.approveNurseDialog = false;
            }
        }.bind(this));
    },
    denyNurse() {

        data = {
            
            application_id: this.application_id,
            declineMessage: this.customDeclineMessage,
        }
        
        modRequest.request('nurse.application.newDecline', null, data, function(response) {
            if (response.success) {
                    
                this.showSnackbar({
                    message: 'Application declined successfully',
                    color: 'success',
                    timeout: 3000,
                });
                
                this.declined = true;
                this.denyNurseDialog = false;
            }
        }.bind(this));
    },
},
});