window.addEventListener('load', (number) => {
Vue.component('nurse-app-form-step5', {
template: /*html*/`
<validation-observer
    ref="observer"
    v-slot="{ invalid }"
>

<h2 style="color: white; font-size: 24px; margin-bottom: 25px;">Agreement Acknowledgement</h2>

<div style="display: flex; flex-direction: row; justify-content: space-between; align-items: center; margin-bottom: 25px;">

    <h3 style="color: white; font-size: 20px;">Download Agreements PDF</h3>
    
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

<div style="width: 100%; display: flex; flex-direction: row; align-items: center;">
    <v-checkbox v-model="agree" label="I have read and agree to the above document."></v-checkbox>
</div>

<v-row> <!-- come back to this -->
<v-text-field
    v-model="page5Data.signature"
    label="Signature"
    outlined
    hint="Please enter your full legal name."
    persistent-hint
    style="margin-top: 16px;"
    autocomplete="signature"
></v-text-field>
</v-row>

<div class="py-4"></div>
<div style="display: flex; flex-direction: row; justify-content: space-between; align-items: center;">
    <button
        class="btn btn-primary d-none"
        @click="formAction(false)"
    >Save Progress</button>

    <div class="flex justify-end">
        <button
            class="btn btn-ghost"
            @click="backApplicationStep"
        >Back</button>

        <button
            class="btn btn-primary"
            @click="formAction"
        >Next</button>
    </div>
</div>

</validation-observer>
`,

watch: {},
computed: {},
created() {
    this.getPdf();
},
props: {
    page5Data: Object,
},
data: () => ({

    agreement_url: '',
    scrolledToBottom: false,
    agree: false,
}),
methods: {

    getPdf() {
        
        modRequest.request('nurse.application.getAgreementPDF', null, {}, function(response) {
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
    getIp() {

        // get the ip address of the user or a unique id
        const cookies = document.cookie.split(';');
        for (let i = 0; i < cookies.length; i++) {
            
            const cookie = cookies[i].trim();
            if (cookie.startsWith('machine_uuid=')) {
                return cookie.substring('machine_uuid='.length);
            }
        }
        return null;
    },
    formAction(validate = true) {

        let formValidation = {};
        if (validate) {
            formValidation = this.validateForm();
        }

        if (formValidation.valid) {

            if (!this.page5Data.timestamp) {
                this.page5Data.timestamp = 'update';
            }
            if (!this.page5Data.ip) {
                this.page5Data.ip = this.getIp();
            }

            this.$emit('formAction', {

                page: 5,
                page5Data: {
                    signature: this.page5Data.signature,
                    ip: this.page5Data.ip,
                    timestamp: this.page5Data.timestamp,
                },
                progressPage: true,
            });
        } else if (formValidation.message !== '') {
            this.showSnackbar(formValidation.message, 'error', 5000);
        }

        if (!validate) {
            this.$emit('formAction', {

                page: 5,
                page5Data: {
                    signature: this.page5Data.signature,
                    ip: this.getIp(),
                    timestamp: this.getTimestamp(),
                },
                progressPage: false,
            });
        }
    },
    showSnackbar(message, color, timeout) {
    
        this.$emit('showSnackbar', {
            message,
            color,
            timeout
        });
    },
    validateForm() {

        /*if (scrollTop + clientHeight < scrollHeight) {
            return {
                valid: false,
                message: 'Please read to the bottom of the agreement'
            };
        } else*/ if (this.page5Data.signature == '' || this.page5Data.signature == null) {
            return {
                valid: false,
                message: 'Please input your signature'
            };
        } else if (!this.agree) {

            return {
                valid: false,
                message: 'Please agree to the above document'
            };
        } else {
            return {
                valid: true,
                message: ''
            };
        }
    },
    backApplicationStep() {            
        this.$emit('backApplicationStep');
    },
},
})});
