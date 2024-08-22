window.addEventListener('load', (number) => {
Vue.component('nurse-app-form-step7', {
template: /*html*/`
<validation-observer
    ref="observer"
    v-slot="{ invalid }"
>

<h2 style="display: none; color: white; font-size: 24px; margin-bottom: 25px;">Payment Options</h2>

<v-radio-group
		v-show="false"
    v-model="page7Data.pay_type"
    :rules="[() => !!page7Data.pay_type || 'This field is required']"
    label="Would you prefer direct deposit or PayCard? *"
>
    <v-radio
        label="Direct Deposit"
        value="direct_deposit"
    ></v-radio>
    <v-radio
        label="PayCard"
        value="paycard"
    ></v-radio>
</v-radio-group>

<div v-show="page7Data.pay_type == 'direct_deposit'">

    <v-radio-group
        v-model="page7Data.account_type"
        :rules="[() => !!page7Data.account_type || 'This field is required']"
        label="Account type: *"
    >
        <v-radio
            label="Checking"
            value="checking"
        ></v-radio>
        <v-radio
            label="Savings"
            value="savings"
        ></v-radio>
    </v-radio-group>
    
    <v-text-field
        v-model="page7Data.account_number"
        label="Account Number *"
        type="number"
        outlined
    ></v-text-field>

    <v-text-field
        v-model="page7Data.routing_number"
        label="Routing / ABA Number *"
        type="number"
        outlined
    ></v-text-field>

    <v-text-field
        v-model="page7Data.bank_name"
        label="Bank Name *"
        outlined
    ></v-text-field>

</div>

<div v-show="page7Data.pay_type == 'paycard'">
    <p><strong>
        This option grants "next day pay". Our company utilizes Rapid! A 3rd party payment method that will issue you a 
        pre-paid Visa card. Information about Rapid! and the Rapid! paycard will be included in the envelope mailed to 
        you with the card. This card can be used anywhere a visa is accepted.
    </strong></p>
</div>

<v-radio-group
    v-model="page7Data.heard_about_us"
    :rules="[() => !!page7Data.heard_about_us || 'This field is required']"
    label="How did you hear about us? *"
>
    <v-radio
        label="Indeed"
        value="indeed"
    ></v-radio>
    <v-radio
        label="Social Media"
        value="social_media"
    ></v-radio>
    <v-radio
        label="Friend"
        value="friend"
    ></v-radio>
    <v-radio
        label="Other"
        value="other"
    ></v-radio>
</v-radio-group>

<v-textarea
    v-show="page7Data.heard_about_us == 'other'"
    v-model="page7Data.heard_about_us_other"
    label="Other *"
    outlined
></v-textarea>

<v-text-field
    v-model="page7Data.referrer"
    label="If you were referred by a friend, please enter their name here:"
    outlined
></v-text-field>

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
        >Save &amp; Continue</button>
    </div>
</div>
</validation-observer>
`,

watch: {},
computed: {},
created() {},
props: {
    page7Data: Object,
},
data: () => ({

    state_options: [

        'AL', 'AK', 'AZ', 'AR', 'CA', 'CO', 'CT', 'DE', 'FL', 'GA', 
        'HI', 'ID', 'IL', 'IN', 'IA', 'KS', 'KY', 'LA', 'ME', 'MD',
        'MA', 'MI', 'MN', 'MS', 'MO', 'MT', 'NE', 'NV', 'NH', 'NJ',
        'NM', 'NY', 'NC', 'ND', 'OH', 'OK', 'OR', 'PA', 'RI', 'SC',
        'SD', 'TN', 'TX', 'UT', 'VT', 'VA', 'WA', 'WV', 'WI', 'WY'
    ],
}),
methods: {
    
    formAction(validate = true) {

        let formValidation = {};
        if (validate) {
            formValidation = this.validateForm();
        }

        if (formValidation.valid) {

            this.$emit('formAction', {

                page: 7,
                page7Data: this.page7Data,
                progressPage: true,
            });
        } else if (formValidation.message !== '') {
            this.showSnackbar(formValidation.message, 'error', 5000);
        }

        if (!validate) {
            this.$emit('formAction', {

                page: 7,
                page7Data: this.page7Data,
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

       /*  if (this.page7Data.pay_type == '') {
            return { valid: false, message: 'Please select a payment type.' };
        } else if (this.page7Data.pay_type == 'direct_deposit' && this.account_type == '') {
            return { valid: false, message: 'Please select an account type.' };
        } else if (this.page7Data.pay_type == 'direct_deposit' && this.account_number == '') {
            return { valid: false, message: 'Please enter an account number.' };
        } else if (this.page7Data.pay_type == 'direct_deposit' && this.routing_number == '') {
            return { valid: false, message: 'Please enter a routing number.' };
        } else if (this.page7Data.pay_type == 'direct_deposit' && this.bank_name == '') {
            return { valid: false, message: 'Please enter a bank name.' };
        } else */ if (this.page7Data.heard_about_us == '') {
            return { valid: false, message: 'Please select how you heard about us.' };
        } else if (this.page7Data.heard_about_us == 'other' && this.heard_about_us_other == '') {
            return { valid: false, message: 'Please explain how you heard about us.' };
        } else {
            return { valid: true, message: '' };
        }
    },
    backApplicationStep() {            
        this.$emit('backApplicationStep');
    },
},
})});
