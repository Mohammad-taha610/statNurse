window.addEventListener('load', (number) => {
Vue.component('nurse-app-form-step2', {
template: /*html*/`
<validation-observer
    ref="observer"
    v-slot="{ invalid }"
>

    <h2 style="color: white; font-size: 24px; margin-bottom: 25px;">Professional History</h2>

    <v-radio-group
        v-model="page2Data.one_year_ltc_experience"
        label="Do you have at least 1 year long term care experience? *"
        ref="one_year_ltc_experience"
    >
        <v-radio
            label="Yes"
            value="1"
        ></v-radio>
        <v-radio
            label="No"
            value="0"
        ></v-radio>
    </v-radio-group>

    <div v-show="page2Data.one_year_ltc_experience === '0'">
        <v-textarea
            v-model="page2Data.one_year_experience_explanation"
            ref="one_year_experience_explanation"
            label="If no, please describe your current experience *"
            hint="Please describe current experience"
            outlined
        ></v-textarea>
    </div>

    <v-radio-group
				v-show="false"
        v-model="page2Data.currently_employed"
        label="Are you currently employed? *"
        ref="currently_employed"
    >
        <v-radio
            label="Yes"
            value="1"
        ></v-radio>
        <v-radio
            label="No"
            value="0"
        ></v-radio>
    </v-radio-group>

    <h2 style="display: none; color: white; font-size: 24px; margin-bottom: 25px;">Employment History</h2>
    
    <div style="display: none; flex-direction: row; justify-content: space-between; align-items: center; margin-bottom: 25px;">

        <h3 style="color: white; font-size: 20px;">Add Company 1</h3>

        <div>

            <v-btn @click="page2Data.company1.show = !page2Data.company1.show">
                {{ page2Data.company1.show ? 'Hide Company 1' : 'Show Company 1' }}
            </v-btn>

            <v-btn @click="removeCompany(1)">Remove Info</v-btn>

        </div>
    </div>

    <div v-show="false">

        <v-text-field
            v-model="page2Data.company1.company_name"
            label="Company Name *"
            ref="company_name"
            outlined
        ></v-text-field>
     
        <v-text-field
            v-model="page2Data.company1.supervisor_name"
            label="Supervisor Name *"
            ref="supervisor_name"
            outlined
        ></v-text-field>
        
        <v-text-field
            v-model="page2Data.company1.company_phone"
            label="Phone Number *"
            ref="company_phone"
            maxlength="14"
            hint="(###) ###-####"
            outlined
        ></v-text-field>
        
        <v-text-field
            v-model="page2Data.company1.email"
            label="Email *"
            ref="email"
            outlined
        ></v-text-field>

        <v-text-field
            v-model="page2Data.company1.job_title"
            label="Job Title *"
            ref="job_title"
            outlined
        ></v-text-field>
        
        <v-text-field
            v-model="page2Data.company1.company_address"
            label="Company Address *"
            ref="company_address"
            outlined
        ></v-text-field>
        
        <v-text-field
            v-model="page2Data.company1.company_city"
            label="Company City *"
            ref="company_city"
            outlined
        ></v-text-field>
        
        <v-select
            v-model="page2Data.company1.company_state"
            :items="state_options"
            label="State *"
            ref="company_state"
            outlined
        ></v-select>
  
        <v-text-field
            v-model="page2Data.company1.company_zip"
            label="ZIP / Postal Code *"
            ref="zipcode"
            type="number"
            :rules="ziprules"
            maxlength="5"
            outlined
        ></v-text-field>
        
        <v-text-field
            v-model="page2Data.company1.start_date"
            label="Start Date *"
            ref="start_date"
            hint="MM/DD/YYYY"
            maxlength="10"
            outlined
        ></v-text-field>
        
        <v-text-field
            v-model="page2Data.company1.end_date"
            label="End Date if Not Currently Working"
            ref="end_date"
            hint="MM/DD/YYYY"
            maxlength="10"
            outlined
        ></v-text-field>
        
        <v-text-field
            v-model="page2Data.company1.responsibilities"
            label="Responsibilities *"
            ref="responsibilities"
            outlined
        ></v-text-field>
        
        <v-text-field
            v-model="page2Data.company1.reason_for_leaving"
            label="Reason for Leaving if Not Currently Working"
            ref="reason_for_leaving"
            outlined
        ></v-text-field>

        <v-radio-group
            v-model="page2Data.company1.may_we_contact_employer"
            label="May we contact this employer? *"
            ref="may_we_contact_employer"
        >
            <v-radio
                label="Yes"
                name="page2Data.company1.may_we_contact_employer"
                value="1"
            ></v-radio>

            <v-radio
                label="No"
                name="page2Data.company1.may_we_contact_employer"
                value="0"
            ></v-radio>
        </v-radio-group>
    
    </div>

    <div style="display: none; flex-direction: row; justify-content: space-between; align-items: center; margin-bottom: 25px;">

        <h3 style="color: white; font-size: 20px;">Add Company 2</h3>

        <div>

            <v-btn
                @click="page2Data.company2.show = !page2Data.company2.show">
                {{ page2Data.company2.show ? 'Hide Company 2' : 'Show Company 2' }}
            </v-btn>

            <v-btn @click="removeCompany(2)">Remove Info</v-btn>

        </div>
    </div>

    <div v-show="page2Data.company2.show">

        <v-text-field
            v-model="page2Data.company2.company_name"
            label="Company Name"
            outlined
        ></v-text-field>
        
        <v-text-field
            v-model="page2Data.company2.supervisor_name"
            label="Supervisor Name"
            outlined
        ></v-text-field>
        
        <v-text-field
            v-model="page2Data.company2.company_phone"
            label="Phone Number"
            maxlength="14"
            hint="(###) ###-####"
            outlined
        ></v-text-field>
    
        <v-text-field
            v-model="page2Data.company2.email"
            label="Email"
            outlined
        ></v-text-field>
    
        <v-text-field
            v-model="page2Data.company2.job_title"
            label="Job Title"
            outlined
        ></v-text-field>
    
        <v-text-field
            v-model="page2Data.company2.company_address"
            label="Company Address"
            outlined
        ></v-text-field>
        
        <v-text-field
            v-model="page2Data.company2.company_city"
            label="Company City"
            outlined
        ></v-text-field>
        
        <v-select
            v-model="page2Data.company2.company_state"
            :items="state_options"
            label="State"
            outlined
        ></v-select>
  
        <v-text-field
            v-model="page2Data.company2.company_zip"
            label="ZIP / Postal Code"
            type="number"
            :rules="ziprules"
            maxlength="5"
            outlined
        ></v-text-field>
        
        <v-text-field
            v-model="page2Data.company2.start_date"
            label="Start Date"
            hint="MM/DD/YYYY"
            maxlength="10"
            outlined
        ></v-text-field>
        
        <v-text-field
            v-model="page2Data.company2.end_date"
            label="End Date"
            hint="MM/DD/YYYY"
            maxlength="10"
            outlined
        ></v-text-field>
        
        <v-text-field
            v-model="page2Data.company2.responsibilities"
            label="Responsibilities"
            outlined
        ></v-text-field>
        
        <v-text-field
            v-model="page2Data.company2.reason_for_leaving"
            label="Reason for Leaving"
            outlined
        ></v-text-field>

        <v-radio-group
            v-model="page2Data.company2.may_we_contact_employer"
            label="May we contact this employer?"
        >
            <v-radio
                label="Yes"
                name="page2Data.company2.may_we_contact_employer"
                value="1"
            ></v-radio>

            <v-radio
                label="No"
                name="page2Data.company2.may_we_contact_employer"
                value="0"
            ></v-radio>
        </v-radio-group>
    
    </div>

    <div style="display: none; flex-direction: row; justify-content: space-between; align-items: center; margin-bottom: 25px;">

        <h3 style="color: white; font-size: 20px;">Add Company 3</h3>

        <div>

            <v-btn @click="page2Data.company3.show = !page2Data.company3.show">
                {{ page2Data.company3.show ? 'Hide Company 3' : 'Show Company 3' }}
            </v-btn>

            <v-btn @click="removeCompany(3)">Remove Info</v-btn>

        </div>
    </div>

    <div v-show="page2Data.company3.show">
    
        <v-text-field
            v-model="page2Data.company3.company_name"
            label="Company Name"
            outlined
        ></v-text-field>
        
        <v-text-field
            v-model="page2Data.company3.supervisor_name"
            label="Supervisor Name"
            outlined
        ></v-text-field>
        
        <v-text-field
            v-model="page2Data.company3.company_phone"
            label="Phone Number"
            maxlength="14"
            hint="(###) ###-####"
            outlined
        ></v-text-field>
        
        <v-text-field
            v-model="page2Data.company3.email"
            label="Email"
            outlined
        ></v-text-field>
        
        <v-text-field
            v-model="page2Data.company3.job_title"
            label="Job Title"
            outlined
        ></v-text-field>
        
        <v-text-field
            v-model="page2Data.company3.company_address"
            label="Company Address"
            outlined
        ></v-text-field>
        
        <v-text-field
            v-model="page2Data.company3.company_city"
            label="Company City"
            outlined
        ></v-text-field>
        
        <v-select
            v-model="page2Data.company3.company_state"
            :items="state_options"
            label="State"
            outlined
        ></v-select>
  
        <v-text-field
            v-model="page2Data.company3.company_zip"
            label="ZIP / Postal Code"
            type="number"
            :rules="ziprules"
            maxlength="5"
            outlined
        ></v-text-field>
        
        <v-text-field
            v-model="page2Data.company3.start_date"
            label="Start Date"
            hint="MM/DD/YYYY"
            maxlength="10"
            outlined
        ></v-text-field>
        
        <v-text-field
            v-model="page2Data.company3.end_date"
            label="End Date"
            hint="MM/DD/YYYY"
            maxlength="10"
            outlined
        ></v-text-field>
        
        <v-text-field
            v-model="page2Data.company3.responsibilities"
            label="Responsibilities"
            outlined
        ></v-text-field>
        
        <v-text-field
            v-model="page2Data.company3.reason_for_leaving"
            label="Reason for Leaving"
            outlined
        ></v-text-field>

        <v-radio-group
            v-model="page2Data.company3.may_we_contact_employer"
            label="May we contact this employer?"
        >
            <v-radio
                label="Yes"
                name="page2Data.company3.may_we_contact_employer"
                value="1"
            ></v-radio>

            <v-radio
                label="No"
                name="page2Data.company3.may_we_contact_employer"
                value="0"
            ></v-radio>
        </v-radio-group>
    
    </div>
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

watch: {

    page2Data: {
        
        handler() {
        
            let phone = this.page2Data.company1.company_phone.replace(/\D/g, '');            
            if (phone.length > 6) {
                phone = '(' + phone.slice(0, 3) + ') ' + phone.slice(3, 6) + '-' + phone.slice(6);
            } else if (phone.length > 2) {
                phone = '(' + phone.slice(0, 3) + ') ' + phone.slice(3);
            }            
            this.page2Data.company1.company_phone = phone;

            let start_date = this.page2Data.company1.start_date.replace(/\D/g, '');
            if (start_date.length > 2) {
                start_date = start_date.slice(0, 2) + '/' + start_date.slice(2);
            }
            if (start_date.length > 5) {
                start_date = start_date.slice(0, 5) + '/' + start_date.slice(5, 9);
            }
            this.page2Data.company1.start_date = start_date;

            let end_date = this.page2Data.company1.end_date.replace(/\D/g, '');
            if (end_date.length > 2) {
                end_date = end_date.slice(0, 2) + '/' + end_date.slice(2);
            }
            if (end_date.length > 5) {
                end_date = end_date.slice(0, 5) + '/' + end_date.slice(5, 9);
            }
            this.page2Data.company1.end_date = end_date;
        
            phone = this.page2Data.company2.company_phone.replace(/\D/g, '');            
            if (phone.length > 6) {
                phone = '(' + phone.slice(0, 3) + ') ' + phone.slice(3, 6) + '-' + phone.slice(6);
            } else if (phone.length > 2) {
                phone = '(' + phone.slice(0, 3) + ') ' + phone.slice(3);
            }            
            this.page2Data.company2.company_phone = phone;

            start_date = this.page2Data.company2.start_date.replace(/\D/g, '');
            if (start_date.length > 2) {
                start_date = start_date.slice(0, 2) + '/' + start_date.slice(2);
            }
            if (start_date.length > 5) {
                start_date = start_date.slice(0, 5) + '/' + start_date.slice(5, 9);
            }
            this.page2Data.company2.start_date = start_date;

            end_date = this.page2Data.company2.end_date.replace(/\D/g, '');
            if (end_date.length > 2) {
                end_date = end_date.slice(0, 2) + '/' + end_date.slice(2);
            }
            if (end_date.length > 5) {
                end_date = end_date.slice(0, 5) + '/' + end_date.slice(5, 9);
            }
            this.page2Data.company2.end_date = end_date;
        
            phone = this.page2Data.company3.company_phone.replace(/\D/g, '');            
            if (phone.length > 6) {
                phone = '(' + phone.slice(0, 3) + ') ' + phone.slice(3, 6) + '-' + phone.slice(6);
            } else if (phone.length > 2) {
                phone = '(' + phone.slice(0, 3) + ') ' + phone.slice(3);
            }            
            this.page2Data.company3.company_phone = phone;

            start_date = this.page2Data.company3.start_date.replace(/\D/g, '');
            if (start_date.length > 2) {
                start_date = start_date.slice(0, 2) + '/' + start_date.slice(2);
            }
            if (start_date.length > 5) {
                start_date = start_date.slice(0, 5) + '/' + start_date.slice(5, 9);
            }
            this.page2Data.company3.start_date = start_date;

            end_date = this.page2Data.company3.end_date.replace(/\D/g, '');
            if (end_date.length > 2) {
                end_date = end_date.slice(0, 2) + '/' + end_date.slice(2);
            }
            if (end_date.length > 5) {
                end_date = end_date.slice(0, 5) + '/' + end_date.slice(5, 9);
            }
            this.page2Data.company3.end_date = end_date;
        },
        deep: true,
    },
},
computed: {},
created() {},
props: {
    page2Data: Object,
},
data: () => ({

    state_options: [

      'AL', 'AK', 'AZ', 'AR', 'CA', 'CO', 'CT', 'DE', 'FL', 'GA', 
      'HI', 'ID', 'IL', 'IN', 'IA', 'KS', 'KY', 'LA', 'ME', 'MD',
      'MA', 'MI', 'MN', 'MS', 'MO', 'MT', 'NE', 'NV', 'NH', 'NJ',
      'NM', 'NY', 'NC', 'ND', 'OH', 'OK', 'OR', 'PA', 'RI', 'SC',
      'SD', 'TN', 'TX', 'UT', 'VT', 'VA', 'WA', 'WV', 'WI', 'WY'
    ],
    ziprules: [ v => v.length <= 5 || 'Max 5 digits' ],
}),
methods: {

    removeCompany(companyNumber) {
        
        if (companyNumber == 1) {
            
            this.page2Data.company1 = {
        
                show: true,
                company_name: '',
                supervisor_name: '',
                company_address: '',
                company_city: '',
                company_state: '',
                company_zip: '',
                company_phone: '',
                email: '',
                job_title: '',
                start_date: '',
                end_date: '',
                responsibilities: '',
                reason_for_leaving: '',
                may_we_contact_employer: '',
            }
        } else if (companyNumber == 2) {
            
            this.page2Data.company2 = {
        
                show: true,
                company_name: '',
                supervisor_name: '',
                company_address: '',
                company_city: '',
                company_state: '',
                company_zip: '',
                company_phone: '',
                email: '',
                job_title: '',
                start_date: '',
                end_date: '',
                responsibilities: '',
                reason_for_leaving: '',
                may_we_contact_employer: '',
            }
        } else if (companyNumber == 3) {
            
            this.page2Data.company3 = {
        
                show: true,
                company_name: '',
                supervisor_name: '',
                company_address: '',
                company_city: '',
                company_state: '',
                company_zip: '',
                company_phone: '',
                email: '',
                job_title: '',
                start_date: '',
                end_date: '',
                responsibilities: '',
                reason_for_leaving: '',
                may_we_contact_employer: '',
            }
        }
    },
    formAction(validate = true) {

        let formValidation = {};
        if (validate) {
            formValidation = this.validateForm();
        }

        if (formValidation.valid) {

            this.$emit('formAction', {

                page: 2,
                page2Data: this.page2Data,
                progressPage: true,
            });
        } else if (formValidation.message !== '') {
            this.showSnackbar(formValidation.message, 'error', 5000);
        }

        if (!validate) {
            this.$emit('formAction', {

                page: 2,
                page2Data: this.page2Data,
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
        
        if (this.page2Data.one_year_ltc_experience === '') {
            this.$refs.one_year_ltc_experience.focus();
            return {
                valid: false,
                message: 'Please select if you have at least 1 year long term care experience'
            }
        } else if (this.page2Data.one_year_ltc_experience === '0' && this.page2Data.one_year_experience_explanation === '') {
            this.$refs.one_year_experience_explanation.focus();
            return {
                valid: false,
                message: 'Please describe your current experience'
            }
        } else /* if (this.page2Data.currently_employed === '') {
            this.$refs.currently_employed.focus();
            return {
                valid: false,
                message: 'Please select if you are currently employed'
            }
        } else if (this.page2Data.company1.company_name === '') {
            this.$refs.company_name.focus();
            return {
                valid: false,
                message: 'Please enter company name'
            }
        } else if (this.page2Data.company1.supervisor_name === '') {
            this.$refs.supervisor_name.focus();
            return {
                valid: false,
                message: 'Please enter supervisor name'
            }
        } else if (this.page2Data.company1.company_phone === '') {
            this.$refs.company_phone.focus();
            return {
                valid: false,
                message: 'Please enter a valid phone number'
            }
        } else if (this.page2Data.company1.company_phone.length < 14) {
            this.$refs.company_phone.focus();
            return {
                valid: false,
                message: 'Please enter a valid phone number'
            }
        } else if (this.page2Data.company1.email === '') {
            this.$refs.email.focus();
            return {
                valid: false,
                message: 'Please enter email'
            }
        } else if (this.page2Data.company1.job_title === '') {
            this.$refs.job_title.focus();
            return {
                valid: false,
                message: 'Please enter job title'
            }
        } else if (this.page2Data.company1.company_address === '') {
            this.$refs.company_address.focus();
            return {
                valid: false,
                message: 'Please enter company address'
            }
        } else if (this.page2Data.company1.company_city === '') {
            this.$refs.company_city.focus();
            return {
                valid: false,
                message: 'Please enter company city'
            }
        } else if (this.page2Data.company1.company_state === '') {
            this.$refs.company_state.focus();
            return {
                valid: false,
                message: 'Please enter company state'
            }
        } else if (this.page2Data.company1.company_zip === '') {
            this.$refs.zipcode.focus();
            return {
                valid: false,
                message: 'Please enter company zip code'
            }
        } else if (this.page2Data.company1.company_zip < 5) {
            this.$refs.zipcode.focus();
            return {
                valid: false,
                message: 'Please enter a valid zip code'
            }
        } else if (this.page2Data.company1.start_date === '') {
            this.$refs.start_date.focus();
            return {
                valid: false,
                message: 'Please enter start date'
            }
        } else if (this.page2Data.company1.start_date < 10) {
            this.$refs.start_date.focus();
            return {
                valid: false,
                message: 'Please enter a valid start date'
            }
        } else if (this.page2Data.company1.end_date === '' && this.page2Data.currently_employed === '0') {
            this.$refs.end_date.focus();
            return {
                valid: false,
                message: 'Please enter end date'
            }
        } else if (this.page2Data.company1.end_date < 10 && this.page2Data.currently_employed === '0') {
            this.$refs.end_date.focus();
            return {
                valid: false,
                message: 'Please enter a valid end date'
            }
        } else if (this.page2Data.company1.responsibilities === '') {
            this.$refs.responsibilities.focus();
            return {
                valid: false,
                message: 'Please enter responsibilities'
            }
        } else if (this.page2Data.company1.reason_for_leaving === '' && this.page2Data.currently_employed === '0') {
            this.$refs.reason_for_leaving.focus();
            return {
                valid: false,
                message: 'Please enter reason for leaving'
            }
        } else if (this.page2Data.company1.may_we_contact_employer === '') {
            this.$refs.may_we_contact_employer.focus();
            return {
                valid: false,
                message: 'Please select if we may contact this employer'
            }
        } else */ {
            return {
                valid: true,
                message: ''
            }
        }
    },
    backApplicationStep() {            
        this.$emit('backApplicationStep');
    },
},
})});
