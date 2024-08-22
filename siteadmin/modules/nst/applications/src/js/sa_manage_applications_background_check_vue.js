Vue.component('sa-background-check', {
template: /*html*/`
<div>

    <h2 v-if="!background_check.id">Background Check Not Completed</h2>
    
    <div v-if="background_check.id">

        <h2 style="margin-bottom: 25px;" v-show="!background_check_approved">Background Check Results</h2>
        <h2 style="margin-bottom: 25px;" v-show="background_check_approved">Background Check Results: Approved!</h2>

        <a :href="background_check_checkr_uri" target="_blank">View Background Check in Checkr Dashboard</a>

        <textarea
            v-model="background_check"
            label="Background Check"
            disabled
            style="width: 100%; height: 400px; margin-bottom: 25px;"
            auto-grow
        ></textarea>

        <v-btn
            @click="accept_background_check_dialog = true"
            :disabled="!agreement_url || background_check_approved"
        >Approve Background Check</v-btn>

        <v-dialog v-model="accept_background_check_dialog" max-width="500">
            <v-card>
                <v-card-title class="headline">Confirm Action</v-card-title>
        
                <v-card-text>
                    Are you sure you want to approve background check?
                </v-card-text>
        
                <v-card-actions class="justify-end">
                    <v-btn color="green" @click="acceptBackgroundCheck">Accept</v-btn>
                    <v-btn color="black" @click="accept_background_check_dialog = false" style="margin-left: 10px;">Cancel</v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>

        <h2 style="margin-top: 30px;" v-show="!background_check_approved">Upload Acceptance Agreement</h2>

        <v-row style="margin-top: 10px;" v-show="!background_check_approved">
            <v-col cols="12" md="4">

                <file-uploader
                    :config="background_check_agreement_config"
                    @fileUploaded="uploadResponse"
                />

                <v-card
                    class="member-file-card"
                    style="margin-top: 20px;"
                    color="#ECEFF1"
                    :href="background_check_agreement.url"
                    target="_blank"
                    v-show="background_check_agreement.name"
                >
                    <v-card-text>
                        <div class="member-file-icons" style="width:100%; display: flex; flex-direction: row; justify-content: flex-end; align-items: center;">
                            <v-btn icon>
                                <v-icon color="#212121" @click.prevent="deleteFile">mdi-trash-can-outline</v-icon>
                            </v-btn>
                        </div>
                        <div class="member-file-icon" style="margin-bottom: 20px;">
                            <v-icon color="#4FC3F7">mdi-file</v-icon>
                        </div>
                        <div class="member-file-name-container">
                            <span class="member-file-name" style="color: #212121;">{{ background_check_agreement.name }}</span>
                        </div>
                        <div class="member-file-tag-container mt-1">
                            <span class="member-file-tag-name" style="color: #0277BD;">New Agreement</span>
                        </div>
                    </v-card-text>
                </v-card>

                <v-btn
                    color="#BBDEFB"
                    style="margin-top: 20px;"
                    @click.prevent="saveUploadedAgreement"
                    v-show="background_check_agreement.name"
                >Save New Agreement</v-btn>

                <div style="margin-top: 250px;" v-show="!background_check_agreement.name"></div>
            </v-col>
        </v-row>

        <h2 style="margin-top: 30px;">Current Agreement</h2>
        <v-row style="margin-top: 10px;">
            <iframe :src="agreement_url" width="100%" height="800px" v-show="agreement_url" frameborder="0"></iframe>
            <h5 v-show="!agreement_url" style="margin: 20px 0 0 20px;">No Agreement Uploaded</h5>
        </v-row>

    </div>
</div>
`,
watch: {},
computed: {},
created() {
    this.getUploadedAgreement();
},
props: {

    application_id: Number,
    background_check: [String, Object, null],
    background_check_checkr_uri: String,
},
data: () => ({

    accept_background_check_dialog: false,
    background_check_approved: false,

    background_check_agreement_config: {

        id: 'background_check_agreement',
        multiple: false,
        upload_route: '/files/upload',
        chunk_size: 1000000,
        color: '#BBDEFB',
        button_text: 'Upload New Background Check Agreement',
        fileTag: 'Background Check Agreement',
    },
    background_check_agreement: {},
    agreement_url: '',
}),
methods: {

    acceptBackgroundCheck() {

        data = {
            application_id: this.application_id,
        }
        
        modRequest.request('nurse.application.acceptBackgroundCheck', null, data, function(response) {
            if (response.success) {

                this.$emit('showSnackbar', {

                    message: 'Background Check Accepted',
                    color: 'success',
                    timeout: 3000
                });
                
                this.accept_background_check_dialog = false;
                this.background_check_approved = true;
            }
        }.bind(this));
    },
    uploadResponse(uploadInfo) {            
        this.background_check_agreement = uploadInfo;
    },
    deleteFile() {
        this.background_check_agreement = {};
    },
    saveUploadedAgreement() {

        data = {

            file: this.background_check_agreement,
            application_id: this.application_id,
        }
        
        modRequest.request('nurse.application.setBackgroundCheckAgreement', null, data, function(response) {
            if (response.success) {

                this.background_check_agreement = {};
                this.getUploadedAgreement();
            }
        }.bind(this));
    },
    getUploadedAgreement() {

        data = {
            application_id: this.application_id,
        }
        
        modRequest.request('nurse.application.getBackgroundCheckAgreement', null, data, function(response) {
            if (response.success) {

                this.agreement_url = response.url;
                this.background_check_approved = response.background_check_approved;
            }
        }.bind(this));
    },
},
});