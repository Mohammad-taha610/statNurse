Vue.component('background-check', {
template: /*html*/`
<div>

<div class="row" v-show="background_check.status == 'pending'">

    <h3 style="color: white; font-size: 18px;">Background Check is Processing</h3>

    <p><br>Our team is working to complete your background check process. You will be notified via sms what your next steps will be at the conclusion of this process.<br></p>

</div>

<div class="row" v-show="background_check.status == 'accepted' && !background_check.already_accepted">

    <div style="display: flex; flex-direction: row; justify-content: space-between; align-items: center; width: 100%; margin-bottom: 25px;">

        <h3 style="color: white; font-size: 20px;">Download Background Check Agreement PDF</h3>
        
        <v-btn @click="exportPdf">
            Download
            <v-icon right>
                mdi-cloud-download
            </v-icon>
        </v-btn>  

    </div>

    <v-row style="margin-top: 10px;">
        <iframe
            :src="agreement_url"
            width="100%"
            height="800px"
            v-show="agreement_url"
            frameborder="0" 
        ></iframe>
    </v-row>

    <p style="margin-top: 35px;"><strong>Please input your name as a digital
    signature below indicating your acceptance of this
    agreement *</strong></p>

    <div style="width: 100%;">
        <v-text-field
            v-model="signature"
            label="Signature"
            outlined
            hint="I understand that this is a legal representation of my signature"
            persistent-hint
            style="margin-top: 16px; width: 100%;"
            autocomplete="signature"
        ></v-text-field>
    </div>

    <div style="width: 100%; display: flex; flex-direction: row; justify-content: flex-end;">
        <button
            class="btn btn-primary"
            @click="acceptAgreement"
        >Submit</button>
    </div>

</div>

<div class="row" v-show="background_check.already_accepted">

    <h3 style="color: white; font-size: 18px;">Application Completed!</h3>

    <p><br>Our team is reviewing your completed application! You will be notified via sms what your next steps will be at the conclusion of this process.<br></p>

</div>

</div>
`,
watch: {

    application_id: function() {

        this.getPdf();
    }
},
computed: {},
created() {
},
props: {

    application_id: {

        type: [Number, String],
        default: 0
    },
    background_check: Object,
},
data: () => ({

    agreement_url: '',
    signature: '',
}),
methods: {

    getPdf() {

        data = {
            application_id: this.application_id,
        }
        
        modRequest.request('nurse.application.getBackgroundCheckAgreement', null, data, function(response) {
            if (response.success) {
                this.agreement_url = response.url;
            }
        }.bind(this));
    },
    exportPdf() {

        fetch(this.agreement_url)
        .then(response => response.blob())
        .then(blob => {

            // Create a new object URL for the blob
            const url = window.URL.createObjectURL(blob);

            // Create a link element
            const link = document.createElement('a');

            // Set the href and download attributes for the link
            link.href = url;
            link.download = 'agreement.pdf';

            // Append the link to the body
            document.body.appendChild(link);

            // Click the link to start the download
            link.click();

            // Clean-up by removing the link and revoking the object URL
            document.body.removeChild(link);
            window.URL.revokeObjectURL(url);
        })
        .catch(error => console.error('Error:', error));
    },
    acceptAgreement() {

        if (!this.signature || this.signature == '') {

            this.$emit('showSnackbar', {

                message: 'Please enter your signature',
                color: 'error',
                timeout: 5000,
            });            
            
            return;
        }

        data = {

            application_id: this.application_id,
            signature: this.signature,
        }
        
        modRequest.request('nurse.application.signBackgroundCheckAgreement', null, data, function(response) {
            if (response.success) {
        
                this.background_check.already_accepted = true;
            }
        }.bind(this));
    },
},
});