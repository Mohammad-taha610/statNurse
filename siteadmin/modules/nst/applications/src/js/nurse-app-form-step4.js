window.addEventListener('load', (number) => {
    Vue.component('nurse-app-form-step4', {
    template: /*html*/`
    <validation-observer
        ref="observer"
        v-slot="{ invalid }"
    >
    
        <h2 style="display: none; color: white; font-size: 24px; margin-bottom: 25px;">Professional References</h2>
    
        <div style="display: none; flex-direction: row; justify-content: space-between; align-items: center; margin-bottom: 25px;">
    
            <h3 style="color: white; font-size: 20px;">Professional Reference 1</h3>
    
            <div>
    
                <v-btn
                    @click="page4Data.reference1.show = !page4Data.reference1.show"
                    style="transition: opacity 0.5s;"
                >
                    {{ page4Data.reference1.show ? 'Hide Reference 1 Info' : 'Show Reference 1 Info' }}
                </v-btn>
    
                <v-btn @click="removeInfo(1)">Remove Info</v-btn>
    
            </div>
    
        </div>
    
        <div v-show="false">
        
            <v-text-field
                v-model="page4Data.reference1.name"
                label="Full Name *"
                outlined
            ></v-text-field>
            
            <v-text-field
                v-model="page4Data.reference1.relationship"
                label="Relationship *"
                outlined
            ></v-text-field>
            
            <v-text-field
                v-model="page4Data.reference1.phone_number"
                label="Phone Number *"
                maxlength="14"
                outlined
            ></v-text-field>
            
            <v-text-field
                v-model="page4Data.reference1.company"
                label="Company *"
                outlined
            ></v-text-field>
        </div>
    
        <div style="display: none; flex-direction: row; justify-content: space-between; align-items: center; margin-bottom: 25px;">
    
            <h3 style="color: white; font-size: 20px;">Professional Reference 2</h3>
    
            <div>
    
                <v-btn
                    @click="page4Data.reference2.show = !page4Data.reference2.show"
                    style="transition: opacity 0.5s;"
                >
                    {{ page4Data.reference2.show ? 'Hide Reference 2 Info' : 'Show Reference 2 Info' }}
                </v-btn>
    
                <v-btn @click="removeInfo(2)">Remove Info</v-btn>
    
            </div>
    
        </div>
    
        <div v-show="page4Data.reference2.show">
        
            <v-text-field
                v-model="page4Data.reference2.name"
                label="Full Name"
                outlined
            ></v-text-field>
            
            <v-text-field
                v-model="page4Data.reference2.relationship"
                label="Relationship"
                outlined
            ></v-text-field>
            
            <v-text-field
                v-model="page4Data.reference2.phone_number"
                label="Phone Number"
                maxlength="14"
                outlined
            ></v-text-field>
            
            <v-text-field
                v-model="page4Data.reference2.company"
                label="Company"
                outlined
            ></v-text-field>
        </div>
    
        <div style="display: none; flex-direction: row; justify-content: space-between; align-items: center; margin-bottom: 25px;">
    
            <h3 style="color: white; font-size: 20px;">Professional Reference 3</h3>
    
            <div>
    
                <v-btn
                    @click="page4Data.reference3.show = !page4Data.reference3.show"
                    style="transition: opacity 0.5s;"
                >
                    {{ page4Data.reference3.show ? 'Hide Reference 3 Info' : 'Show Reference 3 Info' }}
                </v-btn>
    
                <v-btn @click="removeInfo(3)">Remove Info</v-btn>
    
            </div>
    
        </div>
    
        <div v-show="page4Data.reference3.show">
        
            <v-text-field
                v-model="page4Data.reference3.name"
                label="Full Name"
                outlined
            ></v-text-field>
            
            <v-text-field
                v-model="page4Data.reference3.relationship"
                label="Relationship"
                outlined
            ></v-text-field>
            
            <v-text-field
                v-model="page4Data.reference3.phone_number"
                label="Phone Number"
                maxlength="14"
                outlined
            ></v-text-field>
            
            <v-text-field
                v-model="page4Data.reference3.company"
                label="Company"
                outlined
            ></v-text-field>
        </div>

        <h2 style="color: white; font-size: 24px; margin-bottom: 25px;">Licenses / Skills / Certifications</h2>
        
        <v-simple-table>
        <template v-slot:default>
            <thead>
            <tr>
                <th style="padding: 10px 0; width: 75px;"></th>
                <th style="padding: 10px 0; width: 75px;">
                    Long Term Care
                </th>
                <th style="padding: 10px 0; width: 75px;">
                    Hospital
                </th>
                <th style="padding: 10px 0; width: 75px;">
                    Home Health
                </th>
                <th style="padding: 10px 0; width: 75px;">
                    Hospice
                </th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td>RN</td>
                <td>
                    <v-checkbox
                            v-model="page4Data.license_and_certifications.rn_long_term_care"></v-checkbox>
                </td>
                <td>
                    <v-checkbox
                            v-model="page4Data.license_and_certifications.rn_hospital"></v-checkbox>
                </td>
                <td>
                    <v-checkbox
                            v-model="page4Data.license_and_certifications.rn_home_health"></v-checkbox>
                </td>
                <td>
                    <v-checkbox
                            v-model="page4Data.license_and_certifications.rn_hospice"></v-checkbox>
                </td>
            </tr>
        
            <tr style>
                <td>LPN</td>
                <td>
                    <v-checkbox
                            v-model="page4Data.license_and_certifications.lpn_long_term_care"></v-checkbox>
                </td>
                <td>
                    <v-checkbox
                            v-model="page4Data.license_and_certifications.lpn_hospital"></v-checkbox>
                </td>
                <td>
                    <v-checkbox
                            v-model="page4Data.license_and_certifications.lpn_home_health"></v-checkbox>
                </td>
                <td>
                    <v-checkbox
                            v-model="page4Data.license_and_certifications.lpn_hospice"></v-checkbox>
                </td>
            </tr>
        
            <tr>
                <td>CMA/KMA/CMT</td>
                <td>
                    <v-checkbox
                            v-model="page4Data.license_and_certifications.ckc_long_term_care"></v-checkbox>
                </td>
                <td>
                    <v-checkbox
                            v-model="page4Data.license_and_certifications.ckc_hospital"></v-checkbox>
                </td>
                <td>
                    <v-checkbox
                            v-model="page4Data.license_and_certifications.ckc_home_health"></v-checkbox>
                </td>
                <td>
                    <v-checkbox
                            v-model="page4Data.license_and_certifications.ckc_hospice"></v-checkbox>
                </td>
            </tr>
        
            <tr>
                <td>CNA</td>
                <td>
                    <v-checkbox
                            v-model="page4Data.license_and_certifications.cna_long_term_care"></v-checkbox>
                </td>
                <td>
                    <v-checkbox
                            v-model="page4Data.license_and_certifications.cna_hospital"></v-checkbox>
                </td>
                <td>
                    <v-checkbox
                            v-model="page4Data.license_and_certifications.cna_home_health"></v-checkbox>
                </td>
                <td>
                    <v-checkbox
                            v-model="page4Data.license_and_certifications.cna_hospice"></v-checkbox>
                </td>
            </tr>
        
            <tr>
                <td>Sitter</td>
                <td>
                    <v-checkbox
                            v-model="page4Data.license_and_certifications.sitter_long_term_care"></v-checkbox>
                </td>
                <td>
                    <v-checkbox
                            v-model="page4Data.license_and_certifications.sitter_hospital"></v-checkbox>
                </td>
                <td>
                    <v-checkbox
                            v-model="page4Data.license_and_certifications.sitter_home_health"></v-checkbox>
                </td>
                <td>
                    <v-checkbox
                            v-model="page4Data.license_and_certifications.sitter_hospice"></v-checkbox>
                </td>
            </tr>
            </tbody>
        </template>
        </v-simple-table>

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

            page4Data: {

                handler() {
        
                    let phone = this.page4Data.reference1.phone_number.replace(/\D/g, '');            
                    if (phone.length > 6) {
                        phone = '(' + phone.slice(0, 3) + ') ' + phone.slice(3, 6) + '-' + phone.slice(6);
                    } else if (phone.length > 2) {
                        phone = '(' + phone.slice(0, 3) + ') ' + phone.slice(3);
                    }            
                    this.page4Data.reference1.phone_number = phone;
        
                    phone = this.page4Data.reference2.phone_number.replace(/\D/g, '');            
                    if (phone.length > 6) {
                        phone = '(' + phone.slice(0, 3) + ') ' + phone.slice(3, 6) + '-' + phone.slice(6);
                    } else if (phone.length > 2) {
                        phone = '(' + phone.slice(0, 3) + ') ' + phone.slice(3);
                    }            
                    this.page4Data.reference2.phone_number = phone;
        
                    phone = this.page4Data.reference3.phone_number.replace(/\D/g, '');            
                    if (phone.length > 6) {
                        phone = '(' + phone.slice(0, 3) + ') ' + phone.slice(3, 6) + '-' + phone.slice(6);
                    } else if (phone.length > 2) {
                        phone = '(' + phone.slice(0, 3) + ') ' + phone.slice(3);
                    }            
                    this.page4Data.reference3.phone_number = phone;
                },
                deep: true,
            },        },
        computed: {},
        created() {},
        props: {
            page4Data: Object,
        },
        data: () => ({
        }),
        methods: {
    
            removeInfo(index) {
                
                if (index == 1) {
                    
                    this.page4Data.reference1 = {
    
                        show: true,
                        name: '',
                        relationship: '',
                        phone_number: '',
                        company: '',
                    }
                } else if (index == 2) {
                    
                    this.page4Data.reference2 = {
    
                        show: true,
                        name: '',
                        relationship: '',
                        phone_number: '',
                        company: '',
                    }
                } else if (index == 3) {
                    
                    this.page4Data.reference3 = {
    
                        show: true,
                        name: '',
                        relationship: '',
                        phone_number: '',
                        company: '',
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
        
                        page: 4,
                        page4Data: this.page4Data,
                        progressPage: true,
                    });
                } else if (formValidation.message !== '') {
                    this.showSnackbar(formValidation.message, 'error', 5000);
                }

                if (!validate) {
                    this.$emit('formAction', {
        
                        page: 4,
                        page4Data: this.page4Data,
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

                // check if each attribute is false in this.license_and_certifications and if so, return false
                let allFalse = true;
                for (let key in this.page4Data.license_and_certifications) {
                    if (this.page4Data.license_and_certifications[key]) {
                        allFalse = false;
                    }
                }
                
               /*  if (this.page4Data.reference1.name == '') {
                    return {
                        valid: false,
                        message: 'Reference 1 Name is required'
                    };
                } else if (this.page4Data.reference1.relationship == '') {
                    return {
                        valid: false,
                        message: 'Reference 1 Relationship is required'
                    };
                } else if (this.page4Data.reference1.phone_number == '') {
                    return {
                        valid: false,
                        message: 'Reference 1 Phone Number is required'
                    };
                } else if (this.page4Data.reference1.phone_number.length < 14) {
                    return {
                        valid: false,
                        message: 'Reference 1 Phone Number is invalid'
                    };
                } else if (this.page4Data.reference1.company == '') {
                    return {
                        valid: false,
                        message: 'Reference 1 Company is required'
                    };
                } else */ if (allFalse) {
                    return {
                        valid: false,
                        message: 'At least one License / Skill / Certification is required'
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
    })
    })
