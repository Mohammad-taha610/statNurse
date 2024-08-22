Vue.component('update-agreement', {
template: /*html*/`
<div>

<h3 style="font-size: 18px; margin-top: 30px;">Upload New Application Agreement</h3>

<v-row style="margin-top: 10px;">
    <v-col cols="12" md="4">

        <file-uploader
            :config="application_agreement_config"
            @fileUploaded="uploadResponse"
        />

        <v-card
            class="member-file-card"
            style="margin-top: 20px;"
            color="#ECEFF1"
            :href="application_agreement.url"
            target="_blank"
            v-show="application_agreement.name"
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
                    <span class="member-file-name" style="color: #212121;">{{ application_agreement.name }}</span>
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
            v-show="application_agreement.name"
        >Save New Agreement</v-btn>

        <div style="margin-top: 250px;" v-show="!application_agreement.name">
    </v-col>
</v-row>

<h3 style="font-size: 18px; margin-top: 30px;">Current Agreement</h3>
<v-row style="margin-top: 10px;">
    <iframe :src="agreement_url" width="100%" height="800px" v-show="agreement_url" frameborder="0"></iframe>
    <h5 v-show="!agreement_url" style="margin: 20px 0 0 20px;">No Agreement Uploaded</h5>
</v-row>
</div>
`,
watch: {},
computed: {},
created() {
    this.getUploadedAgreement();
},
data: () => ({

    application_agreement_config: {

        id: 'application_agreement',
        multiple: false,
        upload_route: '/files/upload',
        chunk_size: 1000000,
        color: '#BBDEFB',
        button_text: 'Upload New Application Agreement',
        fileTag: 'Application Agreement',
    },
    application_agreement: {},
    agreement_url: '',
}),
methods: {
    uploadResponse(uploadInfo) {            
        this.application_agreement = uploadInfo;
    },
    deleteFile() {
        this.application_agreement = {};
    },
    saveUploadedAgreement() {

        // check if file is a pdf

        data = {
            file: this.application_agreement,
        }
        
        modRequest.request('nurse.application.setAgreementPDF', null, data, function(response) {
            if (response.success) {

                this.application_agreement = {};
                this.getUploadedAgreement();
                // snackbar
            }
        }.bind(this));
    },
    getUploadedAgreement() {
        
        modRequest.request('nurse.application.getAgreementPDF', null, {}, function(response) {
            if (response.success) {
                this.agreement_url = response.url;
            }
        }.bind(this));
    },
    // snackbar
},
});