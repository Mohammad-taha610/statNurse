window.addEventListener('load', number => {
    VeeValidate.extend('required', {
        validate(value) {
            return {
                required: true,
                valid: ['', null, undefined].indexOf(value) === -1
            }
        },
        computesRequired: true,
        message: 'The {_field_} is required'
    });

    Vue.use(window.VueTheMask)
    Vue.component('ValidationProvider', VeeValidate.ValidationProvider)
    Vue.component('ValidationObserver', VeeValidate.ValidationObserver)

    VeeValidate.extend('minimum_date', {
        validate: date_of_birth => {
            let date = new Date();
            currentYear = date.getFullYear();
            // 0: month 1: day 2: year
            const DOBarray = date_of_birth.split("/");
            if ((currentYear - parseInt(DOBarray[2])) > 18) {
                return true;
            } else if ((currentYear - parseInt(DOBarray[2])) == 18) {
                currentMonth = date.getMonth() + 1;
                if (currentMonth > parseInt(DOBarray[0])) {
                    return true;
                } else if (currentMonth == parseInt(DOBarray[0])) {
                    currentDay = date.getDate();
                    if (currentDay >= parseInt(DOBarray[1])) {
                        return true;
                    }
                }
            }
            return false;
        },
        message: 'Applicants must be 18 years of age or older'
    })

    Vue.component('nst-line-break', {
        template:
            `
            <div class="row">
                <div class="col-lg-12">
                    <hr>
                </div>
            </div>
        `,
        data() {
            return {}
        }
    })

    Vue.component('nurse-app-form', {
        // language=HTML
        template: `
            <div class="container my-16 nurse-app-form scroll-y" data-app>
                <div class="row">
                    <div class="col-lg-10 offset-lg-1 col-md-12 scroll-y">
                        <!-- Full Name -->
                        <div class="row scroll-y">
                            <div class="col-lg-12 scroll-y">
                                <v-card class="px-10 pt-10 pb-8 scroll-y" elevation="2" v-if="!submitted">
                                    <a href="#" class="brand-logo">
                                        <img class="brand-title" src="/themes/nst/assets/images/logo_black.png" alt=""
                                             style="display: block; margin: 0 auto 15px;">
                                    </a>
                                    <div class="row">
                                        <div class="text-center pt-4 col-lg-12"> 
                                            <strong>FOR THIS TYPE OF EMPLOYMENT STATE LAW REQUIRES A CRIMINAL RECORD CHECK AS A CONDITION OF EMPLOYMENT.</strong> 
                                        </div>
                                        <div class="col-lg-12 offset-0">
                                            <hr class="mb-5 pb-1">
                                        </div>
                                    </div>
                                    <div class="application-header align-items-center justify-content-between mt-3 mb-4">
                                        <h1>Nurse Application</h1>
                                    </div>

                                    <v-card-text>

                                        <div v-show="page == 1" id="page-1">
                                            <div class="row">
                                                <div class="col-lg-3">
                                                    <div class="nurse-form-flush">
                                                        <strong>Full Name *</strong>
                                                    </div>
                                                </div>

                                                <div class="col-lg-9">
                                                    <div class="row">
                                                        <div class="col-lg-6">
                                                            <validation-provider name="first name field"
                                                                                 rules="required" v-slot="{ errors }">
                                                                <v-text-field label="First Name"
                                                                              v-model="form.nurse.first_name"></v-text-field>
                                                                <span class="required">{{ errors[0] }}</span>
                                                            </validation-provider>
                                                        </div>

                                                        <div class="col-lg-6">
                                                            <v-text-field label="Middle Name"
                                                                          v-model="form.nurse.middle_name"></v-text-field>
                                                        </div>
                                                    </div>

                                                    <div class="row">
                                                        <div class="col-lg-12">
                                                            <validation-provider name="last name field" rules="required"
                                                                                 v-slot="{ errors }">
                                                                <v-text-field label="Last Name"
                                                                              v-model="form.nurse.last_name"></v-text-field>
                                                                <span class="required">{{ errors[0] }}</span>
                                                            </validation-provider>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Address -->
                                            <div class="row">
                                                <div class="col-lg-3">
                                                    <div class="nurse-form-flush">
                                                        <strong>Address *</strong>
                                                    </div>
                                                </div>

                                                <div class="col-lg-9">
                                                    <div class="row">
                                                        <div class="col-lg-12">
                                                            <validation-provider name="street address field"
                                                                                 rules="required" v-slot="{ errors }">
                                                                <v-text-field label="Street Address"
                                                                              v-model="form.nurse.street_address"></v-text-field>
                                                                <span class="required">{{ errors[0] }}</span>
                                                            </validation-provider>
                                                        </div>
                                                    </div>

                                                    <div class="row">
                                                        <div class="col-lg-8">
                                                            <v-text-field label="Street Address Line 2"
                                                                          v-model="form.nurse.street_address_two"></v-text-field>
                                                        </div>
                                                        <div class="col-lg-4">
                                                            <v-text-field label="Apartment Number"
                                                                          v-model="form.nurse.apartment_number"></v-text-field>
                                                        </div>
                                                    </div>

                                                    <div class="row">
                                                        <div class="col-lg-6">
                                                            <validation-provider name="city field"
                                                                                 rules="required" v-slot="{ errors }">
                                                                <v-text-field label="City"
                                                                              v-model="form.nurse.city"></v-text-field>
                                                                <span class="required">{{ errors[0] }}</span>
                                                            </validation-provider>
                                                        </div>

                                                        <div class="col-lg-6">
                                                            <validation-provider name="state / province field"
                                                                                 rules="required" v-slot="{ errors }">
                                                                <v-select
                                                                        :items="states"
                                                                        label="State"
                                                                        v-model="form.nurse.state"
                                                                ></v-select>
                                                                <span class="required">{{ errors[0] }}</span>
                                                            </validation-provider>
                                                        </div>
                                                    </div>

                                                    <div class="row">
                                                        <div class="col-lg-6">
                                                            <validation-provider name="postal / zip code field"
                                                                                 rules="required|min:5"
                                                                                 v-slot="{ errors }">
                                                                <v-text-field label="Postal / Zip Code" v-mask="'#####'"
                                                                              v-model="form.nurse.zip_code"></v-text-field>
                                                                <span class="required">{{ errors[0] }}</span>
                                                            </validation-provider>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Date of Birth -->
                                            <div class="row">
                                                <div class="col-lg-3">
                                                    <strong>Date of Birth *</strong>
                                                </div>

                                                <div class="col-lg-9">
                                                    <div class="row">
                                                        <div class="col">
                                                            <validation-observer>
                                                                <validation-provider name="date of birth field"
                                                                                    rules="required|min:10|minimum_date:@is18YearsOld"
                                                                                    v-model="is18YearsOld"
                                                                                    v-slot="{ errors }">
                                                                    <v-text-field label="Date" v-mask="'##/##/####'"
                                                                                v-model="form.nurse.date_of_birth"></v-text-field>
                                                                    <span class="required">{{ errors[0] }}</span>
                                                                </validation-provider>
                                                            </Validation-observer>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Email -->
                                            <div class="row">
                                                <div class="col-lg-3">
                                                    <div class="nurse-form-flush">
                                                        <strong>Email Address *</strong>
                                                    </div>
                                                </div>

                                                <div class="col-lg-9">
                                                    <div class="row">
                                                        <div class="col-lg-12">
                                                            <validation-provider name="email field"
                                                                                 rules="required|email"
                                                                                 v-slot="{ errors }">
                                                                <v-text-field type="email" label="Email"
                                                                              v-model="form.nurse.email"></v-text-field>
                                                                <span class="required">{{ errors[0] }}</span>
                                                            </validation-provider>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-lg-3">
                                                    <div class="nurse-form-flush">
                                                        <strong>Phone Number *</strong>
                                                    </div>
                                                </div>

                                                <div class="col-lg-9">
                                                    <div class="row">
                                                        <div class="col-lg-12">
                                                            <validation-provider name="phone number field"
                                                                                 rules="required|min:14"
                                                                                 v-slot="{ errors }">
                                                                <v-text-field label="Phone Number"
                                                                              v-mask="'(###) ###-####'"
                                                                              v-model="form.nurse.phone_number"></v-text-field>
                                                                <span class="required">{{ errors[0] }}</span>
                                                            </validation-provider>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-lg-3">
                                                    <div class="nurse-form-flush">
                                                        <strong>Applying for Position *</strong>
                                                    </div>
                                                </div>

                                                <div class="col-lg-9">
                                                    <div class="row">
                                                        <div class="col-lg-12">
                                                            <validation-provider name="position field" rules="required"
                                                                                 v-slot="{ errors }">
                                                                <v-select
                                                                        attach
                                                                        :items="['RN', 'LPN', 'CNA', 'CMA/KMA', 'Homecare/Sitter', 'Other']"
                                                                        label="Position"
                                                                        key="nurse-position"
                                                                        v-model="form.nurse.position"
                                                                ></v-select>
                                                                <span class="required">{{ errors[0] }}</span>
                                                            </validation-provider>
                                                            <div v-show="form.nurse.position == 'Other'">
                                                                <textarea v-model="form.nurse.other_position_explanation" class="md-textarea form-control" placeholder="Please describe position that is being applied for"></textarea>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-lg-9 offset-lg-3">
                                                    <hr class="mb-5 pb-1">
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <div class="row">
                                                        <div class="col-lg-3">
                                                            <strong>Are you a citizen of the US? *</strong>
                                                        </div>

                                                        <div class="col-lg-9">
                                                            <validation-provider name="citizenship field"
                                                                                 rules="required" v-slot="{ errors }">
                                                                <v-select
                                                                        attach
                                                                        :items="['Yes', 'No']"
                                                                        label="Are you a citizen of the US?"
                                                                        v-model="form.nurse.citizen_of_the_us"
                                                                        @change="checkUSCitizen"
                                                                ></v-select>
                                                                <span class="required">{{ errors[0] }}</span>
                                                            </validation-provider>
                                                        </div>
                                                    </div>

                                                    <div class="row">
                                                        <div class="col-lg-3">
                                                            <strong>If no, are you authorized to work in the US?
                                                                *</strong>
                                                        </div>

                                                        <div class="col-lg-9">
                                                            <validation-provider name="work authorization field"
                                                                                 rules="required|is:Yes"
                                                                                 v-slot="{ errors }">
                                                                <template v-if="is_us_citizen">
                                                                    <v-select
                                                                            attach
                                                                            :items="['Yes', 'No']"
                                                                            label="Authorized to Work?"
                                                                            v-model="form.nurse.authorized_to_work_in_the_us"
                                                                            disabled
                                                                    ></v-select>
                                                                </template>
                                                                <template v-else>
                                                                    <v-select
                                                                            attach
                                                                            :items="['Yes', 'No']"
                                                                            label="Authorized to Work?"
                                                                            v-model="form.nurse.authorized_to_work_in_the_us"
                                                                    ></v-select>
                                                                </template>
                                                                <span class="required">{{ errors[0] }}</span>
                                                            </validation-provider>
                                                        </div>
                                                    </div>

                                                    <div class="row">
                                                        <div class="col-lg-3">
                                                            <strong>Social Security Number *</strong>
                                                        </div>
                                                        <div class="col-lg-9">
                                                            <validation-provider name="social security number"
                                                                                 rules="required|min:11"
                                                                                 v-slot="{ errors }">
                                                                <v-text-field :label="soc_sec_label"
                                                                              v-mask="'###-##-####'"
                                                                              v-model="form.nurse.socialsecurity_number"
                                                                              @click="fixSocialInputLabel"></v-text-field>
                                                            </validation-provider>
                                                        </div>
                                                    </div>

                                                </div>
                                            </div>
                                        </div>

                                        <div class="print-break-page">
                                            <!-- Currently Employed -->
                                            <div class="row">
                                                <div class="col-xs-12 col-lg-3">
                                                    <div class="nurse-form-flush">
                                                        <strong>Do you have at least 1 year long term care
                                                            experience?</strong>
                                                    </div>
                                                </div>

                                                <div class="col-xs-12 col-lg-9">
                                                    <div class="row">
                                                        <div class="col-lg-12">
                                                            <v-radio-group
                                                                    v-model="form.employment.one_year_experience"
                                                                    mandatory>
                                                                <v-radio
                                                                        label="Yes"
                                                                        name="one_year_experience"
                                                                        :value="'Yes'"
                                                                        required="required"
                                                                ></v-radio>

                                                                <v-radio
                                                                        label="No"
                                                                        name="one_year_experience"
                                                                        :value="'No'"
                                                                ></v-radio>
                                                            </v-radio-group>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-xs-12 col-lg-12">
                                                    <div class="row">
                                                        <div class="col-xs-12 col-lg-3">
                                                            <div class="nurse-form-flush">
                                                                <strong>If no, please describe your current
                                                                    experience</strong>
                                                            </div>
                                                        </div>
                                                        <div class="col-xs-12 col-lg-9">
                                                            <validation-provider name="current experience field"
                                                                                 rules="required|max:150"
                                                                                 v-slot="{ errors }">
                                                                <v-text-field label="Current experience"
                                                                              v-model="form.employment.less_than_one_year_experience"></v-text-field>
                                                                <span class="required">{{ errors[0] }}</span>
                                                            </validation-provider>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-lg-3">
                                                    <div class="nurse-form-flush">
                                                        <strong>Are you currently employeed?</strong>
                                                    </div>
                                                </div>

                                                <div class="col-lg-9">
                                                    <div class="row">
                                                        <div class="col-lg-12">
                                                            <v-radio-group v-model="form.employment.currently_employed">
                                                                <v-radio
                                                                        label="Yes"
                                                                        :value="'Yes'"
                                                                ></v-radio>

                                                                <v-radio
                                                                        label="No"
                                                                        :value="'No'"
                                                                ></v-radio>
                                                            </v-radio-group>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <nst-line-break></nst-line-break>

                                            <!-- Employment Details -->
                                            <div class="row">
                                                <div class="col-lg-3">
                                                    <div class="nurse-form-flush">
                                                        <strong>Employment Details</strong>
                                                    </div>
                                                </div>

                                                <div class="col-lg-9" >
                                                    <div class="row">
                                                        <div class="col-lg-6">
                                                            <v-text-field label="Company"
                                                                          v-model="form.employment_details_one.company"></v-text-field>
                                                        </div>

                                                        <div class="col-lg-6">
                                                            <v-text-field label="Supervisor"
                                                                          v-model="form.employment_details_one.supervisor"></v-text-field>
                                                        </div>
                                                    </div>

                                                    <div class="row">
                                                        <div class="col-lg-6">
                                                            <v-text-field label="Phone Number"
                                                                          v-model="form.employment_details_one.phone_number"
                                                                          v-mask="'(###) ###-####'"></v-text-field>
                                                        </div>

                                                        <div class="col-lg-6">
                                                            <v-text-field label="Email"
                                                                          v-model="form.employment_details_one.email"></v-text-field>
                                                        </div>
                                                    </div>

                                                    <div class="row">
                                                        <div class="col-lg-6">
                                                            <v-text-field label="Job Title"
                                                                          v-model="form.employment_details_one.job_title"></v-text-field>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- Company Address -->
                                            <div class="row">
                                                <div class="col-lg-3">
                                                    <div class="nurse-form-flush">
                                                        <strong>Company Address</strong>
                                                    </div>
                                                </div>

                                                <div class="col-lg-9">
                                                    <div class="row">
                                                        <div class="col-lg-6">
                                                            <v-text-field label="City"
                                                                          v-model="form.employment_details_one.city"></v-text-field>
                                                        </div>

                                                        <div class="col-lg-6">
                                                            <v-select
                                                                    :items="states"
                                                                    label="State"
                                                                    v-model="form.employment_details_one.state"
                                                            ></v-select>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- Start Date -->
                                            <div class="row">
                                                <div class="col-lg-3">
                                                    <strong>Employment Start Date </strong>
                                                </div>

                                                <div class="col-lg-9">
                                                    <div class="row">
                                                        <div class="col">
                                                            <validation-provider name="employment start date field"
                                                                                 rules="" v-slot="{ errors }">
                                                                <v-text-field label="Date"
                                                                              v-mask="'##/##/####'"
                                                                              v-model="form.employment_details_one.start_date"></v-text-field>
                                                                <span class="">{{ errors[0] }}</span>
                                                            </validation-provider>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- End Date -->
                                            <div class="row">
                                                <div class="col-lg-3">
                                                    <strong>Employment End Date</strong>
                                                </div>

                                                <div class="col-lg-9">
                                                    <div class="row">
                                                        <div class="col">
                                                            <v-text-field label="Date"
                                                                          v-mask="'##/##/####'"
                                                                          v-model="form.employment_details_one.end_date"></v-text-field>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- Responsibilities -->
                                            <div class="row">
                                                <div class="col-lg-3">
                                                    <div class="nurse-form-flush">
                                                        <strong>Responsibilities</strong>
                                                    </div>
                                                </div>

                                                <div class="col-lg-9">
                                                    <div class="row">
                                                        <div class="col-lg-12">
                                                            <v-textarea
                                                                    name="input-7-1"
                                                                    label="Responsibilities"
                                                                    auto-grow
                                                                    rows="1"
                                                                    v-model="form.employment_details_one.responsibilities"
                                                            ></v-textarea>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- Reason for leaving -->
                                            <div class="row">
                                                <div class="col-lg-3">
                                                    <div class="nurse-form-flush">
                                                        <strong>Reason for Leaving</strong>
                                                    </div>
                                                </div>

                                                <div class="col-lg-9">
                                                    <div class="row">
                                                        <div class="col-lg-12">
                                                            <v-textarea
                                                                    name="input-7-1"
                                                                    label="Reason for Leaving"
                                                                    auto-grow
                                                                    rows="1"
                                                                    v-model="form.employment_details_one.reason_for_leaving"
                                                            ></v-textarea>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- Contact Employer -->
                                            <div class="row">
                                                <div class="col-lg-3">
                                                    <div class="nurse-form-flush">
                                                        <strong>May we contact this employer?</strong>
                                                    </div>
                                                </div>

                                                <div class="col-lg-9">
                                                    <div class="row">
                                                        <div class="col-lg-12">
                                                            <v-radio-group
                                                                    v-model="form.employment_details_one.can_contact_employer">
                                                                <v-radio
                                                                        label="Yes"
                                                                        :value="'Yes'"
                                                                ></v-radio>

                                                                <v-radio
                                                                        label="No"
                                                                        :value="'No'"
                                                                ></v-radio>
                                                            </v-radio-group>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <nst-line-break></nst-line-break>

                                            <!-- Employment Details -->
                                            <div class="row">
                                                <div class="col-lg-3">
                                                    <div class="nurse-form-flush">
                                                        <strong>Employment Details 2</strong>
                                                    </div>
                                                </div>

                                                <div class="col-lg-9">
                                                    <div class="row">
                                                        <div class="col-lg-6">
                                                            <v-text-field label="Company"
                                                                          v-model="form.employment_details_two.company"></v-text-field>
                                                        </div>

                                                        <div class="col-lg-6">
                                                            <v-text-field label="Supervisor"
                                                                          v-model="form.employment_details_two.supervisor"></v-text-field>
                                                        </div>
                                                    </div>

                                                    <div class="row">
                                                        <div class="col-lg-6">
                                                            <v-text-field label="Phone Number"
                                                                          v-model="form.employment_details_two.phone_number"
                                                                          v-mask="'(###) ###-####'"></v-text-field>
                                                        </div>

                                                        <div class="col-lg-6">
                                                            <v-text-field label="Email"
                                                                          v-model="form.employment_details_two.email"></v-text-field>
                                                        </div>
                                                    </div>

                                                    <div class="row">
                                                        <div class="col-lg-6">
                                                            <v-text-field label="Job Title"
                                                                          v-model="form.employment_details_two.job_title"></v-text-field>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- Company Address -->
                                            <div class="row">
                                                <div class="col-lg-3">
                                                    <div class="nurse-form-flush">
                                                        <strong>Company Address</strong>
                                                    </div>
                                                </div>

                                                <div class="col-lg-9">
                                                    <div class="row">
                                                        <div class="col-lg-6">
                                                            <v-text-field label="City"
                                                                          v-model="form.employment_details_two.city"></v-text-field>
                                                        </div>

                                                        <div class="col-lg-6">
                                                            <v-select
                                                                    :items="states"
                                                                    label="State"
                                                                    v-model="form.employment_details_two.state"
                                                            ></v-select>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- Start Date -->
                                            <div class="row">
                                                <div class="col-lg-3">
                                                    <strong>Employment Start Date *</strong>
                                                </div>

                                                <div class="col-lg-9">
                                                    <div class="row">
                                                        <div class="col">
                                                            <validation-provider name="employment start date field"
                                                                                 rules="required|min:10"
                                                                                 v-slot="{ errors }">
                                                                <v-text-field label="Date"
                                                                              v-mask="'##/##/####'"
                                                                              v-model="form.employment_details_two.start_date"></v-text-field>
                                                            </validation-provider>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- End Date -->
                                            <div class="row">
                                                <div class="col-lg-3">
                                                    <strong>Employment End Date *</strong>
                                                </div>

                                                <div class="col-lg-9">
                                                    <div class="row">
                                                        <div class="col">
                                                            <validation-provider name="employment end date field"
                                                                                 rules="required|min:10"
                                                                                 v-slot="{ errors }">
                                                                <v-text-field label="Date"
                                                                              v-mask="'##/##/####'"
                                                                              v-model="form.employment_details_two.end_date"></v-text-field>
                                                            </validation-provider>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- Responsibilities -->
                                            <div class="row">
                                                <div class="col-lg-3">
                                                    <div class="nurse-form-flush">
                                                        <strong>Responsibilities</strong>
                                                    </div>
                                                </div>

                                                <div class="col-lg-9">
                                                    <div class="row">
                                                        <div class="col-lg-12">
                                                            <v-textarea
                                                                    name="input-7-1"
                                                                    label="Responsibilities"
                                                                    auto-grow
                                                                    rows="1"
                                                                    v-model="form.employment_details_two.responsibilities"
                                                            ></v-textarea>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- Reason for leaving -->
                                            <div class="row">
                                                <div class="col-lg-3">
                                                    <div class="nurse-form-flush">
                                                        <strong>Reason for Leaving</strong>
                                                    </div>
                                                </div>

                                                <div class="col-lg-9">
                                                    <div class="row">
                                                        <div class="col-lg-12">
                                                            <v-textarea
                                                                    name="input-7-1"
                                                                    label="Reason for Leaving"
                                                                    auto-grow
                                                                    rows="1"
                                                                    v-model="form.employment_details_two.reason_for_leaving"
                                                            ></v-textarea>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- Contact Employer -->
                                            <div class="row">
                                                <div class="col-lg-3">
                                                    <div class="nurse-form-flush">
                                                        <strong>May we contact this employer?</strong>
                                                    </div>
                                                </div>

                                                <div class="col-lg-9">
                                                    <div class="row">
                                                        <div class="col-lg-12">
                                                            <v-radio-group
                                                                    v-model="form.employment_details_two.can_contact_employer">
                                                                <v-radio
                                                                        label="Yes"
                                                                        :value="'Yes'"
                                                                ></v-radio>

                                                                <v-radio
                                                                        label="No"
                                                                        :value="'No'"
                                                                ></v-radio>
                                                            </v-radio-group>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <nst-line-break></nst-line-break>

                                            <!-- Employment Details -->
                                            <div class="row">
                                                <div class="col-lg-3">
                                                    <div class="nurse-form-flush">
                                                        <strong>Employment Details 3</strong>
                                                    </div>
                                                </div>

                                                <div class="col-lg-9">
                                                    <div class="row">
                                                        <div class="col-lg-6">
                                                            <v-text-field label="Company"
                                                                          v-model="form.employment_details_three.company"></v-text-field>
                                                        </div>

                                                        <div class="col-lg-6">
                                                            <v-text-field label="Supervisor"
                                                                          v-model="form.employment_details_three.supervisor"></v-text-field>
                                                        </div>
                                                    </div>

                                                    <div class="row">
                                                        <div class="col-lg-6">
                                                            <v-text-field label="Phone Number"
                                                                          v-model="form.employment_details_three.phone_number"
                                                                          v-mask="'(###) ###-####'"></v-text-field>
                                                        </div>

                                                        <div class="col-lg-6">
                                                            <v-text-field label="Email"
                                                                          v-model="form.employment_details_three.email"></v-text-field>
                                                        </div>
                                                    </div>

                                                    <div class="row">
                                                        <div class="col-lg-6">
                                                            <v-text-field label="Job Title"
                                                                          v-model="form.employment_details_three.job_title"></v-text-field>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- Company Address -->
                                            <div class="row">
                                                <div class="col-lg-3">
                                                    <div class="nurse-form-flush">
                                                        <strong>Company Address</strong>
                                                    </div>
                                                </div>

                                                <div class="col-lg-9">
                                                    <div class="row">
                                                        <div class="col-lg-6">
                                                            <v-text-field label="City"
                                                                          v-model="form.employment_details_three.city"></v-text-field>
                                                        </div>

                                                        <div class="col-lg-6">
                                                            <v-select
                                                                    :items="states"
                                                                    label="State"
                                                                    v-model="form.employment_details_three.state"
                                                            ></v-select>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- Start Date -->
                                            <div class="row">
                                                <div class="col-lg-3">
                                                    <strong>Employment Start Date *</strong>
                                                </div>

                                                <div class="col-lg-9">
                                                    <div class="row">
                                                        <div class="col">
                                                            <validation-provider name="employment start date field"
                                                                                 rules="required|min:10"
                                                                                 v-slot="{ errors }">
                                                                <v-text-field label="Date"
                                                                              v-mask="'##/##/####'"
                                                                              v-model="form.employment_details_three.start_date"></v-text-field>
                                                            </validation-provider>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- End Date -->
                                            <div class="row">
                                                <div class="col-lg-3">
                                                    <strong>Employment End Date *</strong>
                                                </div>

                                                <div class="col-lg-9">
                                                    <div class="row">
                                                        <div class="col">
                                                            <validation-provider name="employment start date field"
                                                                                 rules="required|min:10"
                                                                                 v-slot="{ errors }">
                                                                <v-text-field label="Date"
                                                                              v-mask="'##/##/####'"
                                                                              v-model="form.employment_details_three.end_date"></v-text-field>
                                                            </validation-provider>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- Responsibilities -->
                                            <div class="row">
                                                <div class="col-lg-3">
                                                    <div class="nurse-form-flush">
                                                        <strong>Responsibilities</strong>
                                                    </div>
                                                </div>

                                                <div class="col-lg-9">
                                                    <div class="row">
                                                        <div class="col-lg-12">
                                                            <v-textarea
                                                                    name="input-7-1"
                                                                    label="Responsibilities"
                                                                    auto-grow
                                                                    rows="1"
                                                                    v-model="form.employment_details_three.responsibilities"
                                                            ></v-textarea>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- Reason for leaving -->
                                            <div class="row">
                                                <div class="col-lg-3">
                                                    <div class="nurse-form-flush">
                                                        <strong>Reason for Leaving</strong>
                                                    </div>
                                                </div>

                                                <div class="col-lg-9">
                                                    <div class="row">
                                                        <div class="col-lg-12">
                                                            <v-textarea
                                                                    name="input-7-1"
                                                                    label="Reason for Leaving"
                                                                    auto-grow
                                                                    rows="1"
                                                                    v-model="form.employment_details_three.reason_for_leaving"
                                                            ></v-textarea>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- Contact Employer -->
                                            <div class="row">
                                                <div class="col-lg-3">
                                                    <div class="nurse-form-flush">
                                                        <strong>May we contact this employer?</strong>
                                                    </div>
                                                </div>

                                                <div class="col-lg-9">
                                                    <div class="row">
                                                        <div class="col-lg-12">
                                                            <v-radio-group
                                                                    v-model="form.employment_details_three.can_contact_employer">
                                                                <v-radio
                                                                        label="Yes"
                                                                        :value="'Yes'"
                                                                ></v-radio>

                                                                <v-radio
                                                                        label="No"
                                                                        :value="'No'"
                                                                ></v-radio>
                                                            </v-radio-group>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                        </div>

                                        <div class="print-break-page">
                                            <!-- High School -->
                                            <div class="row">
                                                <div class="col-lg-3">
                                                    <div class="nurse-form-flush">
                                                        <strong>High School or GED *</strong>
                                                    </div>
                                                </div>
                                                <div class="col-lg-9">
                                                    <div class="row">
                                                        <div class="col-lg-6">
                                                            <validation-provider name="school name field"
                                                                                 rules="required" v-slot="{ errors }">
                                                                <v-text-field label="School Name *"
                                                                              v-model="form.highschool.name"></v-text-field>
                                                                <span class="required">{{ errors[0] }}</span>
                                                            </validation-provider>
                                                        </div>

                                                        <div class="col-lg-6">
                                                            <validation-provider name="year of completion field"
                                                                                 rules="required|min:4"
                                                                                 v-slot="{ errors }">
                                                                <v-text-field label="Year of Completion *"
                                                                              v-model="form.highschool.ged_date_of_completion"
                                                                              v-mask="'####'"></v-text-field>
                                                                <span class="required">{{ errors[0] }}</span>
                                                            </validation-provider>
                                                        </div>
                                                    </div>

                                                    <div class="row">
                                                        <div class="col-lg-6">
                                                            <v-radio-group
                                                                    v-model="form.highschool.ged" mandatory>
                                                                <v-radio
                                                                        label="GED"
                                                                        name="ged_or_diploma"
                                                                        :value="'GED'"
                                                                ></v-radio>

                                                                <v-radio
                                                                        label="Diploma"
                                                                        name="ged_or_diploma"
                                                                        :value="'Diploma'"
                                                                ></v-radio>
                                                            </v-radio-group>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-lg-3">
                                                    <div class="nurse-form-flush">
                                                        <strong>Address</strong>
                                                    </div>
                                                </div>

                                                <div class="col-lg-9">
                                                    <div class="row">
                                                        <div class="col-lg-6">
                                                            <validation-provider name="city field"
                                                                                 rules="required" v-slot="{ errors }">
                                                                <v-text-field label="City *"
                                                                              v-model="form.highschool.city"></v-text-field>
                                                                <span class="required">{{ errors[0] }}</span>
                                                            </validation-provider>
                                                        </div>

                                                        <div class="col-lg-6">
                                                            <validation-provider name="state field"
                                                                                 rules="required" v-slot="{ errors }">
                                                                <v-select
                                                                        :items="states"
                                                                        label="State *"
                                                                        v-model="form.highschool.state"
                                                                ></v-select>
                                                                <span class="required">{{ errors[0] }}</span>
                                                            </validation-provider>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <hr>
                                                </div>
                                            </div>

                                            <!-- College -->
                                            <div class="row">
                                                <div class="col-lg-3">
                                                    <div class="nurse-form-flush">
                                                        <strong>University / College / Tradeschool</strong>
                                                    </div>
                                                </div>

                                                <div class="col-lg-9">
                                                    <div class="row">
                                                        <div class="col-lg-6">
                                                            <v-text-field label="School Name"
                                                                          v-model="form.college.name"></v-text-field>
                                                        </div>

                                                        <div class="col-lg-6">
                                                            <v-text-field label="Date of Completion"
                                                                          v-model="form.college.date_of_completion"
                                                                          v-mask="'##/##/####'"></v-text-field>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-lg-3">
                                                    <div class="nurse-form-flush">
                                                        <strong>Address</strong>
                                                    </div>
                                                </div>

                                                <div class="col-lg-9">
                                                    <div class="row">
                                                        <div class="col-lg-6">
                                                            <v-text-field label="City"
                                                                          v-model="form.college.city"></v-text-field>
                                                        </div>

                                                        <div class="col-lg-6">
                                                            <v-select
                                                                    :items="states"
                                                                    label="State"
                                                                    v-model="form.college.state"
                                                            ></v-select>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-lg-3">
                                                    <div class="nurse-form-flush">
                                                        <strong>Subjects / Major / Degree</strong>
                                                    </div>
                                                </div>

                                                <div class="col-lg-9">
                                                    <div class="row">
                                                        <div class="col-lg-12">
                                                            <v-textarea
                                                                    name="input-7-1"
                                                                    label="Subjects / Major / Degree"
                                                                    auto-grow
                                                                    rows="1"
                                                                    v-model="form.college.major"
                                                            ></v-textarea>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <hr>
                                                </div>
                                            </div>

                                            <!-- Other Education -->
                                            <div class="row">
                                                <div class="col-lg-3">
                                                    <div class="nurse-form-flush">
                                                        <strong>Other</strong>
                                                    </div>
                                                </div>
                                                <div class="col-lg-9">
                                                    <div class="row">
                                                        <div class="col-lg-6">
                                                            <v-text-field label="School Name"
                                                                          v-model="form.other_education.name"></v-text-field>
                                                        </div>

                                                        <div class="col-lg-6">
                                                            <v-text-field label="Date of Completion"
                                                                          v-model="form.other_education.date_of_completion"
                                                                          v-mask="'##/##/####'"></v-text-field>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-lg-3">
                                                    <div class="nurse-form-flush">
                                                        <strong>Address</strong>
                                                    </div>
                                                </div>

                                                <div class="col-lg-9">
                                                    <div class="row">
                                                        <div class="col-lg-6">
                                                            <v-text-field label="City"
                                                                          v-model="form.other_education.city"></v-text-field>
                                                        </div>

                                                        <div class="col-lg-6">
                                                            <v-select
                                                                :items="states"
                                                                label="State"
                                                                v-model="form.other_education.state"
                                                            ></v-select>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-lg-3">
                                                    <div class="nurse-form-flush">
                                                        <strong>Subjects / Major</strong>
                                                    </div>
                                                </div>

                                                <div class="col-lg-9">
                                                    <div class="row">
                                                        <div class="col-lg-12">
                                                            <v-textarea
                                                                    name="input-7-1"
                                                                    label="Subject / Major"
                                                                    auto-grow
                                                                    rows="1"
                                                                    v-model="form.other_education.major"
                                                            ></v-textarea>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="print-break-page">
                                            <!-- References -->
                                            <div class="row">
                                                <div class="col-lg-3">
                                                    <div class="nurse-form-flush">
                                                        <strong>Professional Reference *</strong>
                                                    </div>
                                                </div>

                                                <div class="col-lg-9">
                                                    <div class="row">
                                                        <div class="col-lg-6">
                                                            <validation-provider name="first name field"
                                                                                 rules="required" v-slot="{ errors }">
                                                                <v-text-field label="Full Name"
                                                                              v-model="form.professional_reference_one.full_name"></v-text-field>
                                                                <span class="required">{{ errors[0] }}</span>
                                                            </validation-provider>
                                                        </div>

                                                        <div class="col-lg-6">
                                                            <validation-provider name="relationship field"
                                                                                 rules="required" v-slot="{ errors }">
                                                                <v-text-field label="Relationship"
                                                                              v-model="form.professional_reference_one.relationship"></v-text-field>
                                                                <span class="required">{{ errors[0] }}</span>
                                                            </validation-provider>
                                                        </div>
                                                    </div>

                                                    <div class="row">
                                                        <div class="col-lg-6">
                                                            <validation-provider name="phone number field"
                                                                                 rules="required|min:14"
                                                                                 v-slot="{ errors }">
                                                                <v-text-field label="Phone Number"
                                                                              v-model="form.professional_reference_one.phone_number"
                                                                              v-mask="'(###) ###-####'"></v-text-field>
                                                                <span class="required">{{ errors[0] }}</span>
                                                            </validation-provider>
                                                        </div>

                                                        <div class="col-lg-6">
                                                            <validation-provider name="company field"
                                                                                 rules="required" v-slot="{ errors }">
                                                                <v-text-field label="Company"
                                                                              v-model="form.professional_reference_one.company"></v-text-field>
                                                                <span class="required">{{ errors[0] }}</span>
                                                            </validation-provider>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-lg-3">
                                                    <div class="nurse-form-flush">
                                                        <strong>Professional Reference</strong>
                                                    </div>
                                                </div>

                                                <div class="col-lg-9">
                                                    <div class="row">
                                                        <div class="col-lg-6">
                                                            <v-text-field label="Full Name"
                                                                          v-model="form.professional_reference_two.full_name"></v-text-field>
                                                        </div>

                                                        <div class="col-lg-6">
                                                            <v-text-field label="Relationship"
                                                                          v-model="form.professional_reference_two.relationship"></v-text-field>
                                                        </div>
                                                    </div>

                                                    <div class="row">
                                                        <div class="col-lg-6">
                                                            <v-text-field label="Phone Number"
                                                                          v-model="form.professional_reference_two.phone_number"
                                                                          v-mask="'(###) ###-####'"></v-text-field>
                                                        </div>

                                                        <div class="col-lg-6">
                                                            <v-text-field label="Company"
                                                                          v-model="form.professional_reference_two.company"></v-text-field>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-lg-3">
                                                    <div class="nurse-form-flush">
                                                        <strong>License / Skills / Certifications *</strong>
                                                    </div>
                                                </div>

                                                <div class="col-lg-9">
                                                    <v-simple-table>
                                                        <template v-slot:default>
                                                            <thead>
                                                            <tr>
                                                                <th class="text-left"
                                                                    style="font-size: 14px!important"></th>
                                                                <th class="text-left" style="font-size: 14px!important">
                                                                    Long Term Care
                                                                </th>
                                                                <th class="text-left" style="font-size: 14px!important">
                                                                    Hospital
                                                                </th>
                                                                <th class="text-left" style="font-size: 14px!important">
                                                                    Home / Health
                                                                </th>
                                                                <th class="text-left" style="font-size: 14px!important">
                                                                    Hospice
                                                                </th>
                                                                <th class="text-left" style="font-size: 14px!important">
                                                                    Homecare / Sitter
                                                                </th>
                                                            </tr>
                                                            </thead>
                                                            <tbody>
                                                            <tr>
                                                                <td>RN</td>
                                                                <td>
                                                                    <v-checkbox
                                                                            v-model="form.license_and_certifications.rn_long_term_care"></v-checkbox>
                                                                </td>
                                                                <td>
                                                                    <v-checkbox
                                                                            v-model="form.license_and_certifications.rn_hospital"></v-checkbox>
                                                                </td>
                                                                <td>
                                                                    <v-checkbox
                                                                            v-model="form.license_and_certifications.rn_home_health"></v-checkbox>
                                                                </td>
                                                                <td>
                                                                    <v-checkbox
                                                                            v-model="form.license_and_certifications.rn_hospice"></v-checkbox>
                                                                </td>
                                                                <td>
                                                                    <v-checkbox
                                                                            v-model="form.license_and_certifications.rn_homecare_sitter"></v-checkbox>
                                                                </td>
                                                            </tr>

                                                            <tr>
                                                                <td>LPN</td>
                                                                <td>
                                                                    <v-checkbox
                                                                            v-model="form.license_and_certifications.lpn_long_term_care"></v-checkbox>
                                                                </td>
                                                                <td>
                                                                    <v-checkbox
                                                                            v-model="form.license_and_certifications.lpn_hospital"></v-checkbox>
                                                                </td>
                                                                <td>
                                                                    <v-checkbox
                                                                            v-model="form.license_and_certifications.lpn_home_health"></v-checkbox>
                                                                </td>
                                                                <td>
                                                                    <v-checkbox
                                                                            v-model="form.license_and_certifications.lpn_hospice"></v-checkbox>
                                                                </td>
                                                                <td>
                                                                    <v-checkbox
                                                                            v-model="form.license_and_certifications.lpn_homecare_sitter"></v-checkbox>
                                                                </td>
                                                            </tr>

                                                            <tr>
                                                                <td>CMA/KMA/CMT</td>
                                                                <td>
                                                                    <v-checkbox
                                                                            v-model="form.license_and_certifications.ckc_long_term_care"></v-checkbox>
                                                                </td>
                                                                <td>
                                                                    <v-checkbox
                                                                            v-model="form.license_and_certifications.ckc_hospital"></v-checkbox>
                                                                </td>
                                                                <td>
                                                                    <v-checkbox
                                                                            v-model="form.license_and_certifications.ckc_home_health"></v-checkbox>
                                                                </td>
                                                                <td>
                                                                    <v-checkbox
                                                                            v-model="form.license_and_certifications.ckc_hospice"></v-checkbox>
                                                                </td>
                                                                <td>
                                                                    <v-checkbox
                                                                            v-model="form.license_and_certifications.ckc_homecare_sitter"></v-checkbox>
                                                                </td>
                                                            </tr>

                                                            <tr>
                                                                <td>CNA</td>
                                                                <td>
                                                                    <v-checkbox
                                                                            v-model="form.license_and_certifications.cna_long_term_care"></v-checkbox>
                                                                </td>
                                                                <td>
                                                                    <v-checkbox
                                                                            v-model="form.license_and_certifications.cna_hospital"></v-checkbox>
                                                                </td>
                                                                <td>
                                                                    <v-checkbox
                                                                            v-model="form.license_and_certifications.cna_home_health"></v-checkbox>
                                                                </td>
                                                                <td>
                                                                    <v-checkbox
                                                                            v-model="form.license_and_certifications.cna_hospice"></v-checkbox>
                                                                </td>
                                                                <td>
                                                                    <v-checkbox
                                                                            v-model="form.license_and_certifications.cna_homecare_sitter"></v-checkbox>
                                                                </td>
                                                            </tr>

                                                            <tr>
                                                                <td>Sitter</td>
                                                                <td>
                                                                    <v-checkbox
                                                                            v-model="form.license_and_certifications.sitter_long_term_care"></v-checkbox>
                                                                </td>
                                                                <td>
                                                                    <v-checkbox
                                                                            v-model="form.license_and_certifications.sitter_hospital"></v-checkbox>
                                                                </td>
                                                                <td>
                                                                    <v-checkbox
                                                                            v-model="form.license_and_certifications.sitter_home_health"></v-checkbox>
                                                                </td>
                                                                <td>
                                                                    <v-checkbox
                                                                            v-model="form.license_and_certifications.sitter_hospice"></v-checkbox>
                                                                </td>
                                                                <td>
                                                                    <v-checkbox
                                                                            v-model="form.license_and_certifications.sitter_homecare_sitter"></v-checkbox>
                                                                </td>
                                                            </tr>
                                                            </tbody>
                                                        </template>
                                                    </v-simple-table>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="print-break-page">

                                            <div class="row">
                                                <div class="col-lg-3">
                                                    <div class="nurse-form-flush">
                                                        <strong>Do you have any mental or physical conditions that would
                                                            inhibit or restrict your ability to perform the essential
                                                            functions of your job?</strong>
                                                    </div>
                                                </div>

                                                <div class="col-lg-9">
                                                    <div class="row">
                                                        <div class="col-lg-12">
                                                            <v-radio-group
                                                                    v-model="form.employment.mental_physical_disabilities">
                                                                <v-radio
                                                                        label="Yes"
                                                                        name="mental_physical_disabilities_radio"
                                                                        :value="'Yes'"
                                                                        required="true"
                                                                ></v-radio>

                                                                <v-radio
                                                                        label="No"
                                                                        name="mental_physical_disabilities_radio"
                                                                        :value="'No'"
                                                                ></v-radio>
                                                            </v-radio-group>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-lg-3">
                                                    <div class="nurse-form-flush">
                                                        <strong>If yes please describe *</strong>
                                                    </div>
                                                </div>

                                                <div class="col-lg-9">
                                                    <div class="row">
                                                        <div class="col-lg-12">
                                                            <validation-provider name="first name field"
                                                                                 rules="required" v-slot="{ errors }">
                                                                <v-text-field label="Description"
                                                                              v-model="form.employment.mental_physical_disabilities_explained"></v-text-field>
                                                                <span class="required">{{ errors[0] }}</span>
                                                            </validation-provider>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>


                                            <div class="row">
                                                <div class="col-lg-3">
                                                    <strong>Criminal Record *</strong>
                                                </div>

                                                <div class="col-lg-9">
                                                    <div class="row">
                                                        <div class="col-lg-12">
                                                            <p>Do you have any criminal convictions or are you currently
                                                                the subject of any police investigation in the USA or
                                                                abroad? *</p>

                                                            <v-radio-group
                                                                    v-model="form.criminal_record.convictions_or_under_investigation">
                                                                <v-radio
                                                                        label="Yes"
                                                                        :value="'Yes'"
                                                                ></v-radio>

                                                                <v-radio
                                                                        label="No"
                                                                        :value="'No'"
                                                                ></v-radio>
                                                            </v-radio-group>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-lg-3"></div>

                                                <div class="col-lg-9">
                                                    <div class="row">
                                                        <div class="col-lg-12">
                                                            <p>Has your License or Certification ever been under
                                                                investigation? *</p>

                                                            <v-radio-group
                                                                    v-model="form.criminal_record.license_or_certification_investigation">
                                                                <v-radio
                                                                        label="Yes"
                                                                        :value="'Yes'"
                                                                ></v-radio>

                                                                <v-radio
                                                                        label="No"
                                                                        :value="'No'"
                                                                ></v-radio>
                                                            </v-radio-group>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-lg-3"></div>

                                                <div class="col-lg-9">
                                                    <div class="row">
                                                        <div class="col-lg-12">
                                                            <p>Has your license or certification ever been revoked or
                                                                under suspension? *</p>

                                                            <v-radio-group
                                                                    v-model="form.criminal_record.license_or_certification_revoked_suspended">
                                                                <v-radio
                                                                        label="Yes"
                                                                        :value="'Yes'"
                                                                ></v-radio>

                                                                <v-radio
                                                                        label="No"
                                                                        :value="'No'"
                                                                ></v-radio>
                                                            </v-radio-group>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-lg-3">
                                                    <div class="nurse-form-flush">
                                                        <strong>If you have answered YES to any of the questions above,
                                                            please give further details.</strong>
                                                    </div>
                                                </div>

                                                <div class="col-lg-9">
                                                    <div class="row">
                                                        <div class="col-lg-12">
                                                            <v-textarea
                                                                    name="input-7-1"
                                                                    label="Details"
                                                                    value=""
                                                                    auto-grow
                                                                    rows="5"
                                                                    outlined
                                                                    v-model="form.criminal_record.explanation"
                                                            ></v-textarea>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <div class="row">
                                                        <div class="col-lg-3"></div>

                                                        <div class="col-lg-9">
                                                            <p>I certify to NurseStat LLC that the information are true
                                                                and complete to the best of my knowledge. I understand
                                                                that false or misleading information in my application
                                                                or interview may result in my refusal or termination of
                                                                contract. *</p>

                                                            <v-checkbox
                                                                    v-model="checkbox"
                                                                    label="I Agree"
                                                            ></v-checkbox>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-lg-3">
                                                    <div class="nurse-form-flush">
                                                        <strong>How did you hear about us? *</strong>
                                                    </div>
                                                </div>

                                                <div class="col-lg-9">
                                                    <div class="row">
                                                        <div class="col-lg-12">
                                                            <v-select
                                                                    attach
                                                                    :items="['Indeed', 'Monster Jobs', 'Social Media', 'Friend', 'Other']"
                                                                    label="How did you hear about us?"
                                                                    v-model="form.nurse_stat_info.found_by"
                                                            ></v-select>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-lg-9 offset-3">
                                                        <div class="row">
                                                            <div class="col-lg-12">
                                                                <v-text-field label="Please explain:"
                                                                              v-model="form.nurse_stat_info.found_by_other_details"></v-text-field>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-lg-3">
                                                    <div class="nurse-form-flush">
                                                        <strong>If you were referred by one of our staff please provide
                                                            their name</strong>
                                                    </div>
                                                </div>

                                                <div class="col-lg-9">
                                                    <div class="row">
                                                        <div class="col-lg-12">
                                                            <v-text-field label="Staff Name"
                                                                          v-model="form.nurse_stat_info.referred_staff_member"></v-text-field>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                        </div>

                                        <div class="print-break-page">

                                            <!-- Address -->
                                            <div class="row">
                                                <div class="col-lg-3">
                                                    <div class="nurse-form-flush">
                                                        <strong>Emergency Contact Name *</strong>
                                                    </div>
                                                </div>

                                                <div class="col-lg-9">
                                                    <div class="row">
                                                        <div class="col-lg-6">
                                                            <validation-provider name="first name field"
                                                                                 rules="required" v-slot="{ errors }">
                                                                <v-text-field label="First Name"
                                                                              v-model="form.emergency_contact_one.first_name"></v-text-field>
                                                                <span class="required">{{ errors[0] }}</span>
                                                            </validation-provider>
                                                        </div>

                                                        <div class="col-lg-6">
                                                            <validation-provider name="last name field" rules="required"
                                                                                 v-slot="{ errors }">
                                                                <v-text-field label="Last Name"
                                                                              v-model="form.emergency_contact_one.last_name"></v-text-field>
                                                                <span class="required">{{ errors[0] }}</span>
                                                            </validation-provider>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-lg-3">
                                                    <div class="nurse-form-flush">
                                                        <strong>Emergency Contact Relationship *</strong>
                                                    </div>
                                                </div>

                                                <div class="col-lg-9">
                                                    <div class="row">
                                                        <div class="col-lg-6">
                                                            <validation-provider name="relationship field"
                                                                                 rules="required" v-slot="{ errors }">
                                                                <v-text-field label="Relationship"
                                                                              v-model="form.emergency_contact_one.relationship"></v-text-field>
                                                                <span class="required">{{ errors[0] }}</span>
                                                            </validation-provider>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-lg-3">
                                                    <div class="nurse-form-flush">
                                                        <strong>Phone Number *</strong>
                                                    </div>
                                                </div>

                                                <div class="col-lg-9">
                                                    <div class="row">
                                                        <div class="col-lg-12">
                                                            <validation-provider name="phone number field"
                                                                                 rules="required|min:14"
                                                                                 v-slot="{ errors }">
                                                                <v-text-field label="Phone Number"
                                                                              v-mask="'(###) ###-####'"
                                                                              v-model="form.emergency_contact_one.phone_number"></v-text-field>
                                                                <span class="required">{{ errors[0] }}</span>
                                                            </validation-provider>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-lg-9 offset-lg-3">
                                                    <hr class="mb-5 pb-1">
                                                </div>
                                            </div>

                                            <!-- Address -->
                                            <div class="row">
                                                <div class="col-lg-3">
                                                    <div class="nurse-form-flush">
                                                        <strong>Emergency Contact Name</strong>
                                                    </div>
                                                </div>

                                                <div class="col-lg-9">
                                                    <div class="row">
                                                        <div class="col-lg-6">
                                                            <v-text-field label="First Name"
                                                                          v-model="form.emergency_contact_two.first_name"></v-text-field>
                                                        </div>

                                                        <div class="col-lg-6">
                                                            <v-text-field label="Last Name"
                                                                          v-model="form.emergency_contact_two.last_name"></v-text-field>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-lg-3">
                                                    <div class="nurse-form-flush">
                                                        <strong>Emergency Contact Relationship</strong>
                                                    </div>
                                                </div>

                                                <div class="col-lg-9">
                                                    <div class="row">
                                                        <div class="col-lg-6">
                                                            <v-text-field label="Relationship"
                                                                          v-model="form.emergency_contact_two.relationship"></v-text-field>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-lg-3">
                                                    <div class="nurse-form-flush">
                                                        <strong>Phone Number</strong>
                                                    </div>
                                                </div>

                                                <div class="col-lg-9">
                                                    <div class="row">
                                                        <div class="col-lg-12">
                                                            <v-text-field label="Phone Number"
                                                                          v-mask="'(###) ###-####'"
                                                                          v-model="form.emergency_contact_two.phone_number"></v-text-field>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="print-break-page">
                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <p><strong>EDUCATION AND TRAINING</strong>. Applicant states that
                                                        he/she has obtained education and training in the healthcare
                                                        field and is duly licensed and authorized to practice nursing.
                                                    </p>

                                                    <p><strong>EMPLOYEE AT WILL</strong>. Applicant acknowledges
                                                        NurseStat LLC employs Applicant ''at will'' and no employment
                                                        promises have been made for any duration of time. Specifically,
                                                        Applicant understands he/she may quit employment at any time
                                                        with NurseStat LLC, with or without notice. Similarly, Applicant
                                                        understands he/she may be discharged by NurseStat LLC at any
                                                        time, without notice, for any lawful reason. Contracts of
                                                        employment can only be made by a written agreement between
                                                        Applicant and NurseStat LLC and require the approval and
                                                        signature of a manger of NurseStat LLC or authorized
                                                        representative. Further, should Facility decide to end
                                                        Applicant's assignment prior to completion date, NurseStat LLC
                                                        may propose a new assignment as long as Applicant is in good
                                                        standing with NurseStat LLC</p>

                                                    <p><strong>NONDISCLOSURE AND LIMITED NONCOMPETE</strong>. Applicant
                                                        agrees not to disclose any NurseStat LLC trade secrets or any
                                                        confidential or proprietary information of NurseStat LLC,
                                                        NurseStat LLC employees, Facilities, or patients of Facilities.
                                                        Applicant further agrees not to compete either as a direct
                                                        competitor or with a competing company at the Facility
                                                        assignment where Applicant has been placed by NurseStat LLC for
                                                        a term of six months after Applicant's final day of work at
                                                        Facility.</p>

                                                    <p><strong>NONSOLICITATION OF CORPORATION EMPLOYEES</strong>.
                                                        Applicant agrees not to solicit NurseStat LLC employees to work
                                                        for any competing company while on assignment with a NurseStat
                                                        LLC facility, and for a period of six months thereafter.</p>

                                                    <p><strong>NONSOLICITATION OF CORPORATION EMPLOYEES</strong>.
                                                        Applicant agrees not to solicit NurseStat LLC employees to work
                                                        for any competing company while on assignment with a NurseStat
                                                        LLC facility, and for a period of six months thereafter.</p>

                                                    <p><strong>DRUG SCREENS</strong>. Prior to placement and throughout
                                                        employment with NurseStat LLC, Applicant consents to a urine,
                                                        blood or breath sample for the purposes of an alcohol, drug,
                                                        intoxicant, or substance abuse screening test. Applicant also
                                                        gives permission for the release of the test results for
                                                        determining the fitness of employment or continued employment.
                                                        Applicant willutilize clinics that are approved by NurseStat LLC
                                                    </p>

                                                    <p><strong>BACKGROUND CHECKS</strong>. Before the Applicant is
                                                        placed and throughout employment with NurseStat LLC, NurseStat
                                                        LLC may, upon a facility's request, conduct background checks of
                                                        any kind from any location for any purpose NurseStat LLC
                                                        considers reasonable. Applicant also gives permission for
                                                        release of the results for determining fitness of employment
                                                        and/or continued employment.</p>

                                                    <p><strong>EMPLOYMENT AND MEDICAL INFORMATION RELEASE</strong>. I
                                                        authorize NurseStat LLC to release any and all confidential
                                                        employment and medical information contained in my employment
                                                        file to any medical facility or entity with whom NurseStat LLC
                                                        has a staffing agreement, and to any other governmental or
                                                        regulatory agency at such agency's request. For all other
                                                        purposes, NurseStat LLC shall keep my employment and medical
                                                        records confidential and shall advise any medical facility or
                                                        other entity to whom records have been provided to also keep
                                                        such records confidential. I hereby release and hold NurseStat
                                                        LLC harmless for any result(s) that may arise with regard to the
                                                        release of this confidential information by NurseStat LLC.</p>

                                                    <p><strong>REIMBURSEMENTS</strong>. Applicant agrees to adhere to
                                                        all rules. and policies regarding reimbursements, including but
                                                        not limited to submitting expenses within 90 days of incurring
                                                        expense. Further, Applicant acknowledges NurseStat LLC rules and
                                                        regulations regarding reimbursements may be modified at any time
                                                        with or without notice for any reason.</p>

                                                    <p><strong>RECORDING 0F TIME WORKED</strong>. Applicant agrees to
                                                        abide by NurseStat LLC procedures. For reporting time worked,
                                                        including hospital supervisor approval for shift time worked and
                                                        missed lunch periods. The NurseStat LLC workweek begins at 7:00
                                                        AM on Monday and concludes at 6:59 AM on the following Monday.
                                                        Applicant's time sheet must reach NurseStat LLC each Tuesday by
                                                        10 AM Eastern Standard Time in order to be paid in the current
                                                        week. Late submissions may be paid the following week.</p>

                                                    <p><strong>LUNCH BREAK POLICY</strong>. Applicant will clock in and
                                                        out for a minimum of thirty (30) minutes and up to a maximum of
                                                        one (1) hour for meal periods, unless otherwise specified by
                                                        facility policy. If the facility requests applicant to work
                                                        their lunch period due to patient care and safety, Applicant
                                                        agrees to obtain two supervisor signatures of approval from
                                                        Facility Healthcare Professional Managers for each applicable
                                                        shift.</p>

                                                    <p><strong>PERSONAL PROPERTY</strong>. NurseStat LLC is not
                                                        responsible for the theft, loss, destruction, or damage to the
                                                        personal property of its employees.</p>

                                                    <p><strong>TERMINATION</strong>. Applicant understands if he/she
                                                        leaves his/her assignment early for any reason or is terminated
                                                        by NurseStat LLC, any fees due to NurseStat LLC or fees incurred
                                                        by NurseStat as a result of this situation shall be deducted
                                                        from their paycheck.</p>

                                                    <p><strong>CONFIDENTIALITY OF AGREEMENT</strong>. NurseStat LLC and
                                                        Applicant will maintain the confidentiality and exclusivity of
                                                        this Agreement.</p>

                                                    <p><strong>AGREEMENT REVIEW</strong>. NurseStat LLC and Applicant
                                                        agree each party has fully read and reviewed this agreement.
                                                        Should any ambiguities arise, the interpretation of the
                                                        ambiguity will not automatically be construed infavor of the
                                                        Applicant.</p>

                                                    <p><strong>EQUAL OPPORTUNITY EMPLOYER</strong>. NurseStat LLC is an
                                                        equal opportunity employer in the State of Kentucky and in good
                                                        standing with the Kentucky Secretary of State. NurseStat LLC
                                                        does not discriminate in respect to hiring, firing,
                                                        compensation, and all other terms and conditions of privileges
                                                        of employment on the basis of race, color, national origin,
                                                        ancestry, sex, age, pregnancy or related medical conditions,
                                                        marital status, religious creed, or disability.</p>

                                                    <h2 class="pb-3">Agreement between applicant and NurseStat LLC</h2>

                                                    <h3>HIPPA and Privacy Standards</h3>

                                                    <p>I certify that I will comply with the specific policies and
                                                        procedures of HIPPA and Privacy of Protected Information for
                                                        each client of NurseStat LLC to which I am assigned. I also
                                                        certify that I understand and will adhere to all of
                                                        organizations privacy policies and procedures. I understand that
                                                        failure to follow these privacy policies and procedures will
                                                        result in disciplinary action which could include termination of
                                                        my employment with NurseStat LLC, termination of current
                                                        assignment, restriction as well as potential personal civil
                                                        and/or criminal action.</p>

                                                    <h4>Competing Agency Agreement</h4>

                                                    <p>NurseStat LLC understands that many nurses will work for multiple
                                                        agencies at the same time. While we understand this, it must be
                                                        understood that a nurse working on behalf of NurseStat LLC
                                                        cannot also be working for another agency at the same facility.
                                                        This is a conflict of interest for the agency. It is acceptable
                                                        for the nurse to work for facilities that do not contract with
                                                        NurseStat LLC.</p>

                                                    <p>Violation of this agreement will result in a lost profit penalty
                                                        payment of $1000 to be paid by the contracted nurse to
                                                        NurseStat. Payable upon proof of violation of this agreement by
                                                        NurseStat LLC</p>

                                                    <h4>Contract Labor Agreement</h4>

                                                    <p>This Agreement constitutes an understanding that the individual
                                                        listed below is being employed as contract labor and is not
                                                        guaranteed any hours of work or minimum work time, does not
                                                        qualify for any company benefits and will not be eligible for
                                                        unemployment compensation based on hours worked under contract
                                                        with NurseStat LLC.</p>

                                                    <p>This agreement also clarifies to both parties that the individual
                                                        is operating as an independent entity and will; be responsible
                                                        for remitting their own taxes as such. NurseStat LLC will not be
                                                        responsible for withholding or remitting any taxes on behalf of
                                                        the individual. At the end of the year NurseStat associates will
                                                        be issued a 1099-m to report for tax purposes. </p>

                                                    <h4>Healthcare Professional Conduct Expectations</h4>

                                                    <p>NurseStat LLC requires you to adhere to the following
                                                        Professional Conduct Expectations while on assignment. Failure
                                                        to meet these expectations could lead to your termination from
                                                        NurseStat LLC. Please represent our company in a professional
                                                        manner.</p>

                                                    <p><em>Please:</em></p>

                                                    <ul class="bulleted">
                                                        <li>Do not discuss any elements of your compensation with anyone
                                                            employed at the host facility.
                                                        </li>
                                                        <li>Do not discuss any previous assignments worked for NurseStat
                                                            LLC with anyone employed at the host facility.
                                                        </li>
                                                        <li>Do not recruit any Healthcare Professionals at the host
                                                            facility, whether temporary or permanent employees.
                                                        </li>
                                                        <li>Communicate with the management, staff and patients of the
                                                            host facility in a respectful manner at all times.
                                                        </li>
                                                        <li>Honor all terms of this agreement letter, including but not
                                                            limited to beginning and ending assignment dates and travel
                                                            arrangements if applicable.
                                                        </li>
                                                        <li>Honor the policies and procedures of NurseStat LLC and the
                                                            host facility.
                                                        </li>
                                                    </ul>

                                                    <p>I certify that I have read, understand and intend to comply with
                                                        the Primary Applicant Agreement and Professional Conduct
                                                        Expectations and the facts contained in this application are
                                                        true and accurate. I understand any misrepresentation or
                                                        omission of facts is cause for dismissal. I authorize the
                                                        employer to investigate any and all statements contained herein
                                                        and request the persons, firms, and/or corporations named above
                                                        to answer any and all questions relating to this application. I
                                                        release all parties from all liability, including but not
                                                        limited to, the employer and any person, firm or corporation who
                                                        provides information concerning my prior education, employment
                                                        or character</p>

                                                    <p class="mb-0"><strong>Please input your name as a digital
                                                        signature below indicating your acceptance of this
                                                        agreement *</strong></p>
                                                </div>

                                                <div class="col-xl-8 col-lg-12">
                                                    <v-text-field
                                                            label="Signature *"
                                                            v-model="form.terms.signature"
                                                    ></v-text-field>
                                                </div>

                                                <div class="col-lg-6">
                                                    <div class="row">
                                                        <v-menu v-model="menu"
                                                                :close-on-content-click="false"
                                                                :nudge-right="40"
                                                                transition="scale-transition"
                                                                offset-y
                                                                min-width="auto"
                                                        >
                                                            <template v-slot:activator="{ on, attrs }">
                                                                <v-text-field v-model="form.terms.date"
                                                                              label="Select Date"
                                                                              prepend-icon="mdi-calendar"
                                                                              readonly
                                                                              v-bind="attrs"
                                                                              v-on="on"
                                                                ></v-text-field>
                                                            </template>
                                                            <v-date-picker v-model="form.terms.date"
                                                                           no-title
                                                                           :allowed-dates="justToday"
                                                                           @input="menu = false"
                                                            >
                                                            </v-date-picker>
                                                        </v-menu>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="print-break-page">
                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <h3>Medical History Questionnaire</h3>

                                                    <p>Have you had any of the following conditions or diseases?</p>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <v-simple-table>
                                                        <template v-slot:default>
                                                            <thead>
                                                            <tr>
                                                                <th class="text-left"
                                                                    style="font-size: 14px!important"></th>
                                                                <th class="text-left" style="font-size: 14px!important">
                                                                    Yes
                                                                </th>
                                                                <th class="text-left" style="font-size: 14px!important">
                                                                    No
                                                                </th>
                                                            </tr>
                                                            </thead>
                                                            <tbody>
                                                            <tr>
                                                                <td>Anemia</td>

                                                                <td>
                                                                    <v-radio-group
                                                                            v-model="form.medical_history.anemia">
                                                                        <v-radio value="yes"></v-radio>
                                                                    </v-radio-group>
                                                                </td>

                                                                <td>
                                                                    <v-radio-group
                                                                            v-model="form.medical_history.anemia">
                                                                        <v-radio value="no"></v-radio>
                                                                    </v-radio-group>
                                                                </td>
                                                            </tr>

                                                            <tr>
                                                                <td>Smallpox</td>

                                                                <td>
                                                                    <v-radio-group
                                                                            v-model="form.medical_history.smallpox">
                                                                        <v-radio value="yes"></v-radio>
                                                                    </v-radio-group>
                                                                </td>

                                                                <td>
                                                                    <v-radio-group
                                                                            v-model="form.medical_history.smallpox">
                                                                        <v-radio value="no"></v-radio>
                                                                    </v-radio-group>
                                                                </td>
                                                            </tr>

                                                            <tr>
                                                                <td>Diabetes</td>

                                                                <td>
                                                                    <v-radio-group
                                                                            v-model="form.medical_history.diabetes">
                                                                        <v-radio value="yes"></v-radio>
                                                                    </v-radio-group>
                                                                </td>

                                                                <td>
                                                                    <v-radio-group
                                                                            v-model="form.medical_history.diabetes">
                                                                        <v-radio value="no"></v-radio>
                                                                    </v-radio-group>
                                                                </td>
                                                            </tr>

                                                            <tr>
                                                                <td>Diphtheria</td>

                                                                <td>
                                                                    <v-radio-group
                                                                            v-model="form.medical_history.diphtheria">
                                                                        <v-radio value="yes"></v-radio>
                                                                    </v-radio-group>
                                                                </td>

                                                                <td>
                                                                    <v-radio-group
                                                                            v-model="form.medical_history.diphtheria">
                                                                        <v-radio value="no"></v-radio>
                                                                    </v-radio-group>
                                                                </td>
                                                            </tr>

                                                            <tr>
                                                                <td>Epilepsy</td>

                                                                <td>
                                                                    <v-radio-group
                                                                            v-model="form.medical_history.epilepsy">
                                                                        <v-radio value="yes"></v-radio>
                                                                    </v-radio-group>
                                                                </td>

                                                                <td>
                                                                    <v-radio-group
                                                                            v-model="form.medical_history.epilepsy">
                                                                        <v-radio value="no"></v-radio>
                                                                    </v-radio-group>
                                                                </td>
                                                            </tr>

                                                            <tr>
                                                                <td>Heart Disease</td>

                                                                <td>
                                                                    <v-radio-group
                                                                            v-model="form.medical_history.heart_disease">
                                                                        <v-radio value="yes"></v-radio>
                                                                    </v-radio-group>
                                                                </td>

                                                                <td>
                                                                    <v-radio-group
                                                                            v-model="form.medical_history.heart_disease">
                                                                        <v-radio value="no"></v-radio>
                                                                    </v-radio-group>
                                                                </td>
                                                            </tr>

                                                            <tr>
                                                                <td>Kidney Trouble</td>

                                                                <td>
                                                                    <v-radio-group
                                                                            v-model="form.medical_history.kidney_trouble">
                                                                        <v-radio value="yes"></v-radio>
                                                                    </v-radio-group>
                                                                </td>

                                                                <td>
                                                                    <v-radio-group
                                                                            v-model="form.medical_history.kidney_trouble">
                                                                        <v-radio value="no"></v-radio>
                                                                    </v-radio-group>
                                                                </td>
                                                            </tr>

                                                            <tr>
                                                                <td>Mononucleosis</td>

                                                                <td>
                                                                    <v-radio-group
                                                                            v-model="form.medical_history.mononucleosis">
                                                                        <v-radio value="yes"></v-radio>
                                                                    </v-radio-group>
                                                                </td>

                                                                <td>
                                                                    <v-radio-group
                                                                            v-model="form.medical_history.mononucleosis">
                                                                        <v-radio value="no"></v-radio>
                                                                    </v-radio-group>
                                                                </td>
                                                            </tr>

                                                            <tr>
                                                                <td>Scarlet Fever</td>

                                                                <td>
                                                                    <v-radio-group
                                                                            v-model="form.medical_history.scarlet_fever">
                                                                        <v-radio value="yes"></v-radio>
                                                                    </v-radio-group>
                                                                </td>

                                                                <td>
                                                                    <v-radio-group
                                                                            v-model="form.medical_history.scarlet_fever">
                                                                        <v-radio value="no"></v-radio>
                                                                    </v-radio-group>
                                                                </td>
                                                            </tr>

                                                            <tr>
                                                                <td>Typhoid Fever</td>

                                                                <td>
                                                                    <v-radio-group
                                                                            v-model="form.medical_history.typhoid_fever">
                                                                        <v-radio value="yes"></v-radio>
                                                                    </v-radio-group>
                                                                </td>

                                                                <td>
                                                                    <v-radio-group
                                                                            v-model="form.medical_history.typhoid_fever">
                                                                        <v-radio value="no"></v-radio>
                                                                    </v-radio-group>
                                                                </td>
                                                            </tr>

                                                            <tr>
                                                                <td>Hypertension</td>

                                                                <td>
                                                                    <v-radio-group
                                                                            v-model="form.medical_history.hypertension">
                                                                        <v-radio value="yes"></v-radio>
                                                                    </v-radio-group>
                                                                </td>

                                                                <td>
                                                                    <v-radio-group
                                                                            v-model="form.medical_history.hypertension">
                                                                        <v-radio value="no"></v-radio>
                                                                    </v-radio-group>
                                                                </td>
                                                            </tr>

                                                            <tr>
                                                                <td>Latex Allergies</td>

                                                                <td>
                                                                    <v-radio-group
                                                                            v-model="form.medical_history.latex_allergies">
                                                                        <v-radio value="yes"></v-radio>
                                                                    </v-radio-group>
                                                                </td>

                                                                <td>
                                                                    <v-radio-group
                                                                            v-model="form.medical_history.latex_allergies">
                                                                        <v-radio value="no"></v-radio>
                                                                    </v-radio-group>
                                                                </td>
                                                            </tr>

                                                            <tr>
                                                                <td>Hernia</td>

                                                                <td>
                                                                    <v-radio-group
                                                                            v-model="form.medical_history.hernia">
                                                                        <v-radio value="yes"></v-radio>
                                                                    </v-radio-group>
                                                                </td>

                                                                <td>
                                                                    <v-radio-group
                                                                            v-model="form.medical_history.hernia">
                                                                        <v-radio value="no"></v-radio>
                                                                    </v-radio-group>
                                                                </td>
                                                            </tr>

                                                            <tr>
                                                                <td>Depression</td>

                                                                <td>
                                                                    <v-radio-group
                                                                            v-model="form.medical_history.depression">
                                                                        <v-radio value="yes"></v-radio>
                                                                    </v-radio-group>
                                                                </td>

                                                                <td>
                                                                    <v-radio-group
                                                                            v-model="form.medical_history.depression">
                                                                        <v-radio value="no"></v-radio>
                                                                    </v-radio-group>
                                                                </td>
                                                            </tr>

                                                            <tr>
                                                                <td>Measles</td>

                                                                <td>
                                                                    <v-radio-group
                                                                            v-model="form.medical_history.measles">
                                                                        <v-radio value="yes"></v-radio>
                                                                    </v-radio-group>
                                                                </td>

                                                                <td>
                                                                    <v-radio-group
                                                                            v-model="form.medical_history.measles">
                                                                        <v-radio value="no"></v-radio>
                                                                    </v-radio-group>
                                                                </td>
                                                            </tr>

                                                            <tr>
                                                                <td>Hepatitis</td>

                                                                <td>
                                                                    <v-radio-group
                                                                            v-model="form.medical_history.hepatitis">
                                                                        <v-radio value="yes"></v-radio>
                                                                    </v-radio-group>
                                                                </td>

                                                                <td>
                                                                    <v-radio-group
                                                                            v-model="form.medical_history.hepatitis">
                                                                        <v-radio value="no"></v-radio>
                                                                    </v-radio-group>
                                                                </td>
                                                            </tr>

                                                            <tr>
                                                                <td>Mumps</td>

                                                                <td>
                                                                    <v-radio-group v-model="form.medical_history.mumps">
                                                                        <v-radio value="yes"></v-radio>
                                                                    </v-radio-group>
                                                                </td>

                                                                <td>
                                                                    <v-radio-group v-model="form.medical_history.mumps">
                                                                        <v-radio value="no"></v-radio>
                                                                    </v-radio-group>
                                                                </td>
                                                            </tr>

                                                            <tr>
                                                                <td>Pleurisy</td>

                                                                <td>
                                                                    <v-radio-group
                                                                            v-model="form.medical_history.pleurisy">
                                                                        <v-radio value="yes"></v-radio>
                                                                    </v-radio-group>
                                                                </td>

                                                                <td>
                                                                    <v-radio-group
                                                                            v-model="form.medical_history.pleurisy">
                                                                        <v-radio value="no"></v-radio>
                                                                    </v-radio-group>
                                                                </td>
                                                            </tr>

                                                            <tr>
                                                                <td>Pneumonia</td>

                                                                <td>
                                                                    <v-radio-group
                                                                            v-model="form.medical_history.pneumonia">
                                                                        <v-radio value="yes"></v-radio>
                                                                    </v-radio-group>
                                                                </td>

                                                                <td>
                                                                    <v-radio-group
                                                                            v-model="form.medical_history.pneumonia">
                                                                        <v-radio value="no"></v-radio>
                                                                    </v-radio-group>
                                                                </td>
                                                            </tr>

                                                            <tr>
                                                                <td>Chicken Pox</td>

                                                                <td>
                                                                    <v-radio-group
                                                                            v-model="form.medical_history.chicken_pox">
                                                                        <v-radio value="yes"></v-radio>
                                                                    </v-radio-group>
                                                                </td>

                                                                <td>
                                                                    <v-radio-group
                                                                            v-model="form.medical_history.chicken_pox">
                                                                        <v-radio value="no"></v-radio>
                                                                    </v-radio-group>
                                                                </td>
                                                            </tr>

                                                            <tr>
                                                                <td>Emphysema</td>

                                                                <td>
                                                                    <v-radio-group
                                                                            v-model="form.medical_history.emphysema">
                                                                        <v-radio value="yes"></v-radio>
                                                                    </v-radio-group>
                                                                </td>

                                                                <td>
                                                                    <v-radio-group
                                                                            v-model="form.medical_history.emphysema">
                                                                        <v-radio value="no"></v-radio>
                                                                    </v-radio-group>
                                                                </td>
                                                            </tr>

                                                            <tr>
                                                                <td>Tuberculosis</td>

                                                                <td>
                                                                    <v-radio-group
                                                                            v-model="form.medical_history.tuberculosis">
                                                                        <v-radio value="yes"></v-radio>
                                                                    </v-radio-group>
                                                                </td>

                                                                <td>
                                                                    <v-radio-group
                                                                            v-model="form.medical_history.tuberculosis">
                                                                        <v-radio value="no"></v-radio>
                                                                    </v-radio-group>
                                                                </td>
                                                            </tr>

                                                            <tr>
                                                                <td>Whooping Cough</td>

                                                                <td>
                                                                    <v-radio-group
                                                                            v-model="form.medical_history.whooping_cough">
                                                                        <v-radio value="yes"></v-radio>
                                                                    </v-radio-group>
                                                                </td>

                                                                <td>
                                                                    <v-radio-group
                                                                            v-model="form.medical_history.whooping_cough">
                                                                        <v-radio value="no"></v-radio>
                                                                    </v-radio-group>
                                                                </td>
                                                            </tr>

                                                            <tr>
                                                                <td>Rheumatic Fever</td>

                                                                <td>
                                                                    <v-radio-group
                                                                            v-model="form.medical_history.rheumatic_fever">
                                                                        <v-radio value="yes"></v-radio>
                                                                    </v-radio-group>
                                                                </td>

                                                                <td>
                                                                    <v-radio-group
                                                                            v-model="form.medical_history.rheumatic_fever">
                                                                        <v-radio value="no"></v-radio>
                                                                    </v-radio-group>
                                                                </td>
                                                            </tr>

                                                            <tr>
                                                                <td>Carpal Tunnel</td>

                                                                <td>
                                                                    <v-radio-group
                                                                            v-model="form.medical_history.carpal_tunnel">
                                                                        <v-radio value="yes"></v-radio>
                                                                    </v-radio-group>
                                                                </td>

                                                                <td>
                                                                    <v-radio-group
                                                                            v-model="form.medical_history.carpal_tunnel">
                                                                        <v-radio value="no"></v-radio>
                                                                    </v-radio-group>
                                                                </td>
                                                            </tr>

                                                            <tr>
                                                                <td>Sight or Hearing problems</td>

                                                                <td>
                                                                    <v-radio-group
                                                                            v-model="form.medical_history.sight_hearing_problems">
                                                                        <v-radio value="yes"></v-radio>
                                                                    </v-radio-group>
                                                                </td>

                                                                <td>
                                                                    <v-radio-group
                                                                            v-model="form.medical_history.sight_hearing_problems">
                                                                        <v-radio value="no"></v-radio>
                                                                    </v-radio-group>
                                                                </td>
                                                            </tr>

                                                            <tr>
                                                                <td>including colorblindness</td>

                                                                <td>
                                                                    <v-radio-group
                                                                            v-model="form.medical_history.including_colorblindness">
                                                                        <v-radio value="yes"></v-radio>
                                                                    </v-radio-group>
                                                                </td>

                                                                <td>
                                                                    <v-radio-group
                                                                            v-model="form.medical_history.including_colorblindness">
                                                                        <v-radio value="no"></v-radio>
                                                                    </v-radio-group>
                                                                </td>
                                                            </tr>
                                                            </tbody>
                                                        </template>
                                                    </v-simple-table>
                                                </div>

                                                <div class="col-lg-12">
                                                    <p>If you have had any of the following please provide approximate
                                                        dates and a brief description of the occurrance</p>

                                                    <ul class="bulleted">
                                                        <li>Fractures</li>
                                                        <li>Back Problems or Injuries</li>
                                                        <li>Other Injuries that caused you to miss work more than 10
                                                            days
                                                        </li>
                                                        <li>Surgeries</li>
                                                        <li>Permanent physical restrictions</li>
                                                    </ul>

                                                    <p class="pt-4">If none of these apply please enter None in the
                                                        box</p>
                                                </div>

                                                <div class="col-lg-6">
                                                    <v-textarea
                                                            name="input-7-1"
                                                            label="Details"
                                                            value=""
                                                            auto-grow
                                                            rows="5"
                                                            outlined
                                                            v-model="form.medical_history.explanation"
                                                    ></v-textarea>
                                                </div>

                                                <div class="col-lg-12">
                                                    <div class="row">
                                                        <div class="col-lg-12">
                                                            <p>
                                                                <strong>Have you been Vaccinated for *</strong>
                                                            </p>

                                                            <v-simple-table>
                                                                <template v-slot:default>
                                                                    <thead>
                                                                    <tr>
                                                                        <th class="text-left"
                                                                            style="font-size: 14px!important"></th>
                                                                        <th class="text-left"
                                                                            style="font-size: 14px!important">Yes
                                                                        </th>
                                                                        <th class="text-left"
                                                                            style="font-size: 14px!important">No
                                                                        </th>
                                                                    </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                    <tr>
                                                                        <td>Routine Vaccinations Current</td>

                                                                        <td>
                                                                            <v-radio-group
                                                                                    v-model="form.medical_history.routine_vaccinations_current">
                                                                                <v-radio value="yes"></v-radio>
                                                                            </v-radio-group>
                                                                        </td>

                                                                        <td>
                                                                            <v-radio-group
                                                                                    v-model="form.medical_history.routine_vaccinations_current">
                                                                                <v-radio value="no"></v-radio>
                                                                            </v-radio-group>
                                                                        </td>
                                                                    </tr>

                                                                    <tr>
                                                                        <td>Hepatitis B</td>

                                                                        <td>
                                                                            <v-radio-group
                                                                                    v-model="form.medical_history.hepatitis_b">
                                                                                <v-radio value="yes"></v-radio>
                                                                            </v-radio-group>
                                                                        </td>

                                                                        <td>
                                                                            <v-radio-group
                                                                                    v-model="form.medical_history.hepatitis_b">
                                                                                <v-radio value="no"></v-radio>
                                                                            </v-radio-group>
                                                                        </td>
                                                                    </tr>

                                                                    <tr>
                                                                        <td>Hepatitis A</td>

                                                                        <td>
                                                                            <v-radio-group
                                                                                    v-model="form.medical_history.hepatitis_a">
                                                                                <v-radio value="yes"></v-radio>
                                                                            </v-radio-group>
                                                                        </td>

                                                                        <td>
                                                                            <v-radio-group
                                                                                    v-model="form.medical_history.hepatitis_a">
                                                                                <v-radio value="no"></v-radio>
                                                                            </v-radio-group>
                                                                        </td>
                                                                    </tr>

                                                                    <tr>
                                                                        <td>COVID-19</td>

                                                                        <td>
                                                                            <v-radio-group
                                                                                    v-model="form.medical_history.covid">
                                                                                <v-radio value="yes"></v-radio>
                                                                            </v-radio-group>
                                                                        </td>

                                                                        <td>
                                                                            <v-radio-group
                                                                                    v-on:click="form.medical_history.covid = no"
                                                                                    v-model="form.medical_history.covid">
                                                                                <v-radio value="no"></v-radio>
                                                                            </v-radio-group>
                                                                        </td>
                                                                    </tr>

                                                                    <transition name="fade">
                                                                        <template>
                                                                            <tr>
                                                                                <td>Do you have a COVID-19 exemption?
                                                                                </td>

                                                                                <td>
                                                                                    <v-radio-group
                                                                                            v-model="form.medical_history.covid_exemption">
                                                                                        <v-radio value="yes"></v-radio>
                                                                                    </v-radio-group>
                                                                                </td>

                                                                                <td>
                                                                                    <v-radio-group
                                                                                            v-model="form.medical_history.covid_exemption">
                                                                                        <v-radio value="no"></v-radio>
                                                                                    </v-radio-group>
                                                                                </td>
                                                                            </tr>
                                                                        </template>
                                                                    </transition>

                                                                    </tbody>
                                                                </template>
                                                            </v-simple-table>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <h3>Tuberculosis Screening Questionnaire</h3>
                                                </div>
                                            </div>

                                            <!-- References -->
                                            <div class="row">
                                                <div class="col-lg-3">
                                                    <div class="nurse-form-flush">
                                                        <strong>Have you had a positive TB skin test in the past? if so
                                                            please list date</strong>
                                                    </div>
                                                </div>

                                                <div class="col-lg-9">
                                                    <div class="row">
                                                        <div class="col-lg-6">
                                                            <v-text-field label="Date" v-mask="'##/##/####'"
                                                                          v-model="form.tb.date"></v-text-field>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-lg-3">
                                                    <div class="nurse-form-flush">
                                                        <strong>If you have had a Chest Xray in the past please list
                                                            date</strong>
                                                    </div>
                                                </div>

                                                <div class="col-lg-9">
                                                    <div class="row">
                                                        <div class="col-lg-6">
                                                            <v-text-field label="Date"
                                                                          v-mask="'##/##/####'"
                                                                          v-model="form.tb.chest_date"></v-text-field>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <p>I have answered the questions fully and declare that I have no
                                                        known injury, Illness, or ailment other than those previously
                                                        noted. I further understand that any misrepresentation, or
                                                        omission may be grounds for corrective action up to and
                                                        including termination of my contract</p>

                                                    <p class="mb-0"><strong>Please input your name as a digital
                                                        signature below indicating your acceptance of this
                                                        agreement *</strong></p>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <div class="row">
                                                        <div class="col-lg-12">
                                                            <strong>Signature</strong>
                                                        </div>

                                                        <div class="col-xl-8 col-lg-12">
                                                            <v-text-field
                                                                    label="Signature *"
                                                                    v-model="form.tb.signature"
                                                            ></v-text-field>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="print-break-page">
                                            <div class="row">
                                                <div class="col-12 col-lg-8">
                                                    <h3>Direct Deposit Information</h3>
                                                </div>
                                            </div>
                                            <!-- Line 1 -->
                                            <div class="row">

                                                <!-- Account Holder Name -->
                                                <div class="col-12 col-lg-8">
                                                    <div class="col">
                                                        <div class="nurse-form-flush">
                                                            <strong>Account Holder Name</strong>
                                                        </div>
                                                    </div>


                                                    <div class="col">
                                                        <validation-provider
                                                                name="bank account holder name"
                                                                v-slot="{ errors }">
                                                            <v-text-field label="Account Holder Name"
                                                                          v-model="form.direct_deposit.bank_account_holder_name"></v-text-field>
                                                            <span>{{ errors[0] }}</span>
                                                        </validation-provider>
                                                    </div>
                                                </div>

                                                <!-- Account Type -->
                                                <div class="col-12 col-lg-4">
                                                    <div class="col">
                                                        <div class="nurse-form-flush">
                                                            <strong>Account Type</strong>
                                                        </div>
                                                    </div>
                                                    <div class="col">
                                                        <validation-provider name="bank account type"
                                                                             v-slot="{ errors }">
                                                            <v-select
                                                                    attach
                                                                    :items="['Checking', 'Savings']"
                                                                    v-model="form.direct_deposit.bank_account_type"
                                                            ></v-select>
                                                            <span>{{ errors[0] }}</span>
                                                        </validation-provider>
                                                    </div>
                                                </div>
                                            </div> <!-- End: Line 1 -->

                                            <!-- Line 2 -->
                                            <div class="row">

                                                <div class="col-12 col-lg-6">
                                                    <div class="col">
                                                        <div class="nurse-form-flush">
                                                            <strong>Account Number</strong>
                                                        </div>
                                                    </div>


                                                    <div class="col">
                                                        <validation-provider
                                                                name="bank account number"
                                                                v-slot="{ errors }"
                                                                vid="form.direct_deposit.bank_account_number">
                                                            <v-text-field label="Account Number" type="number"
                                                                          v-model="form.direct_deposit.bank_account_number"></v-text-field>
                                                            <span>{{ errors[0] }}</span>
                                                        </validation-provider>
                                                    </div>
                                                </div>

                                                <div class="col-12 col-lg-6">
                                                    <div class="col">
                                                        <div class="nurse-form-flush">
                                                            <strong>Confirm Account Number</strong>
                                                        </div>
                                                    </div>


                                                    <div class="col">
                                                        <validation-provider
                                                                name="bank account number"
                                                                v-slot="{ errors }">
                                                            <v-text-field label="Confirm Account Number" type="number"
                                                                          v-model="form.direct_deposit.bank_account_number_confirmation"></v-text-field>
                                                            <span>{{ errors[0] }}</span>
                                                        </validation-provider>
                                                    </div>
                                                </div>
                                            </div>  <!-- End: Line 2 -->

                                            <!-- Line 3 -->
                                            <div class="row">

                                                <div class="col col-lg-6">
                                                    <div class="col">
                                                        <div class="nurse-form-flush">
                                                            <strong>Routing Number / ABA Number</strong>
                                                        </div>
                                                    </div>


                                                    <div class="col">
                                                        <validation-provider
                                                                name="bank account routing / ABA number"
                                                                v-slot="{ errors }">
                                                            <v-text-field label="Routing Number / ABA Number"
                                                                          v-mask="'#########'"
                                                                          v-model="form.direct_deposit.bank_routing_number"></v-text-field>
                                                            <span>{{ errors[0] }}</span>
                                                        </validation-provider>
                                                    </div>
                                                </div>

                                                <div class="col-12 col-lg-6">
                                                    <div class="col">
                                                        <div class="nurse-form-flush">
                                                            <strong>Bank Name</strong>
                                                        </div>
                                                    </div>


                                                    <div class="col">
                                                        <validation-provider
                                                                name="bank name"
                                                                v-slot="{ errors }">
                                                            <v-text-field label="Bank Name"
                                                                          v-model="form.direct_deposit.bank_name"></v-text-field>
                                                            <span>{{ errors[0] }}</span>
                                                        </validation-provider>
                                                    </div>
                                                </div>

                                            </div>  <!-- End: Line 3 -->

                                        </div>

                                        <div class="print-break-page">
                                            <div class="row">
                                                <div class="col-12 col-lg-8">
                                                    <h3>Upload Documents</h3>

                                                    <p>
                                                        Here you may upload any of the following documents:<br>
                                                        Drivers License, Social Security Card, CPR Test, BLS Test, TB
                                                        Test, Nursing License, and your i9.
                                                    </p>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-12 col-lg-8">
                                                    <div class="col"><br>
                                                        <div class="row justify-content-between">
                                                            <h3>Required Uploads</h3>
                                                        </div>
                                                        <div class="row justify-content-between">
                                                            <p>
                                                                These documents are required for application submission.
                                                            </p>
                                                        </div>
                                                        <div class="row justify-content-between">
                                                            <div class="nurse-form-flush">
                                                                <nurse-app-document-upload
                                                                        :files="form.drivers_license"
                                                                        v-bind:uploads_allowed="true"
                                                                        @uploaded="onFilesUploaded"
                                                                        @deletedFile="onFilesDeleted"
                                                                        :button_text="'Drivers License'"
                                                                        :tag="'Drivers License'"
                                                                        :fileTag="'drivers_license'"
                                                                ></nurse-app-document-upload>
                                                            </div>
                                                            <div class="nurse-form-flush">
                                                                <nurse-app-document-upload
                                                                        :files="form.social_security_card"
                                                                        v-bind:uploads_allowed="true"
                                                                        @uploaded="onFilesUploaded"
                                                                        @deletedFile="onFilesDeleted"
                                                                        :button_text="'Social Security Card'"
                                                                        :tag="'Social Security Card'"
                                                                        :fileTag="'social_security_card'"
                                                                ></nurse-app-document-upload>
                                                            </div>
                                                        </div>
                                                        <div class="row justify-content-between">
                                                            <div class="nurse-form-flush">
                                                                <nurse-app-document-upload
                                                                        :files="form.tb_skin_test"
                                                                        v-bind:uploads_allowed="true"
                                                                        @uploaded="onFilesUploaded"
                                                                        @deletedFile="onFilesDeleted"
                                                                        :button_text="'TB Skin Test'"
                                                                        :tag="'TB Skin Test'"
                                                                        :fileTag="'tb_skin_test'"
                                                                ></nurse-app-document-upload>
                                                            </div><br><br>
                                                        </div>  
                                                    </div>
                                                    <div class="col"><br>                                                      
                                                        <div class="row justify-content-between">
                                                            <h3>Optional Uploads</h3>
                                                        </div>
                                                        <div class="row justify-content-between">
                                                            <p>
                                                                These documents are not required for application submission but may
                                                                be required at a later time.
                                                            </p>
                                                        </div>
                                                        <div class="row justify-content-between">
                                                            <div class="nurse-form-flush">
                                                                <nurse-app-document-upload
                                                                        :files="form.nursing_license"
                                                                        v-bind:uploads_allowed="true"
                                                                        @uploaded="onFilesUploaded"
                                                                        @deletedFile="onFilesDeleted"
                                                                        :button_text="'Nursing License'"
                                                                        :tag="'Nursing License'"
                                                                        :fileTag="'nursing_license'"
                                                                ></nurse-app-document-upload>
                                                            </div>
                                                            <div class="nurse-form-flush">
                                                                <nurse-app-document-upload
                                                                        :files="form.cpr_card"
                                                                        v-bind:uploads_allowed="true"
                                                                        @uploaded="onFilesUploaded"
                                                                        @deletedFile="onFilesDeleted"
                                                                        :button_text="'CPR Card'"
                                                                        :tag="'CPR Card'"
                                                                        :fileTag="'cpr_card'"
                                                                ></nurse-app-document-upload>
                                                            </div>
                                                        </div>
                                                        <div class="row justify-content-between">
                                                            <div class="nurse-form-flush">
                                                                <nurse-app-document-upload
                                                                        :files="form.bls_acls_card"
                                                                        v-bind:uploads_allowed="true"
                                                                        @uploaded="onFilesUploaded"
                                                                        @deletedFile="onFilesDeleted"
                                                                        :button_text="'BLS/ACLS Card'"
                                                                        :tag="'BLS Card'"
                                                                        :fileTag="'bls_acls_card'"
                                                                ></nurse-app-document-upload>
                                                            </div>
                                                            <div class="nurse-form-flush">
                                                                <nurse-app-document-upload
                                                                        :files="form.covid_vaccine_card"
                                                                        v-bind:uploads_allowed="true"
                                                                        @uploaded="onFilesUploaded"
                                                                        @deletedFile="onFilesDeleted"
                                                                        :button_text="'Covid Vaccine Card'"
                                                                        :tag="'Covid Vaccine Card'"
                                                                        :fileTag="'covid_vaccine_card'"
                                                                ></nurse-app-document-upload>
                                                            </div>
                                                        </div>
                                                    
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </v-card-text>
                                </v-card>

                                <v-card class="px-10 pt-10 pb-8" elevation="2" v-else>
                                    <div class="text-center mt-4">
                                        <h2>Application Submitted</h2>
                                        <p class="mt-4">We will review your application and contact you shortly.</p>
                                        <hr>
                                        <p class="m-4">Drug Screen Locations</p>
                                        <div class="row">
                                            <div class="col-lg-6">
                                                <p><b><u>Danville</u></b></p>
                                                <p>
                                                    <u>Guardian Support Services</u><br>
                                                    380 Whirl Away Dr #1<br>
                                                    Danville, KY 40422<br>
                                                    859-236-6002
                                                </p>
                                            </div>
                                            <div class="col-lg-6">
                                                <p><b><u>Richmond</u></b></p>
                                                <p>
                                                    <u>Baptist Health Occ</u><br>
                                                    648 University Shopping Center<br>
                                                    Richmond, KY 40475<br>
                                                    859-623-0535
                                                </p>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-6">
                                                <p><b><u>Lexington</u></b></p>
                                                <p>
                                                    <u>Baptist Health Occ</u><br>
                                                    1051 Newtown Pike #100<br>
                                                    Lexington, KY 40511<br>
                                                    859-253-0076<br><br>
                                                    <u>Baptist Health Urgent Care</u><br>
                                                    610 Brannon Rd Suite 100<br>
                                                    Nicholasville, KY 40356<br>
                                                    859-260-5540
                                                </p>
                                            </div>
                                            <div class="col-lg-6">
                                                <p><b><u>Elizabethtown</u></b></p>
                                                <p>
                                                    <u>WorkWell (SM)</u><br>
                                                    Occupational Health Service<br>
                                                    400 Ring Rd #148<br>
                                                    Elizabethtown, KY 42701<br>
                                                    270-706-5621
                                                </p>
                                                <p><b><u>Lebanon</u></b></p>
                                                <p>
                                                    <u>Industrial Choice Healthcare, PLLC</u><br>
                                                    108 Cemetery Rd<br>
                                                    Lebanon, KY 40033<br>
                                                    270-692-2569
                                                </p>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-6">
                                                <p><b><u>Shelbyville</u></b></p>
                                                <p>
                                                    <u>Baptist Health Occ</u><br>
                                                    101 Stonecrest Rd #1<br>
                                                    Shelbyville, KY 40065<br>
                                                    502-633-2233
                                                </p>
                                            </div>
                                            <div class="col-lg-6">
                                                <p><b><u>London</u></b></p>
                                                <p>
                                                    <u>Select Lab</u><br>
                                                    140 East 5th St<br>
                                                    London, KY 40741<br>
                                                    606-864-9731
                                                </p>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-6">
                                                <p><b><u>Louisville</u></b></p>
                                                <p>
                                                    <u>Baptist Health Occ</u><br>
                                                    11630 Commonwealth Dr #300<br>
                                                    Louisville, KY 40299<br>
                                                    502-267-6292
                                                </p>
                                                <p>
                                                    <u>Baptist Health Occ</u><br>
                                                    3303 Fern Valley Rd<br>
                                                    Louisville, KY 40299<br>
                                                    502-964-4889
                                                </p>
                                                <p>
                                                    <u>Baptist Health Occ</u><br>
                                                    7092 Distribution Dr<br>
                                                    Louisville, KY 40258<br>
                                                    502-935-9970
                                                </p>
                                            </div>
                                            <div class="col-lg-6">
                                                <p><b><u>Hazard</u></b></p>
                                                <p>
                                                    <u>Little Flower Clinic</u><br>
                                                    279 East Main St<br>
                                                    Hazard, KY 41701<br>
                                                    606-487-9505
                                                </p>
                                            </div>
                                        </div>
                                        <p>Ask for a <u>Quick Screen 10-Panel</u> for NurseStat.</p>
                                        <p>Send us a picture of your receipt (text).</p>
                                    </div>
                                </v-card>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `,

        watch: {
            page() {
                document.body.scrollTop = document.documentElement.scrollTop = 0;
            }
        },
        computed: {
        },
        created() {
            this.member = authenticatedMember;

            const submitted = localStorage.getItem('submitted');

            if (submitted) {
                // this.submitted = !! submitted
                localStorage.removeItem('submitted');
                localStorage.removeItem('form');
                localStorage.removeItem('page');
            }

            const email = localStorage.getItem('email');

            // const page = localStorage.getItem('page');
            // if (page) {
            //     this.page = Number(page);
            //     this.paginationPage = Number(page);
            // }

            if (email) {
                this.registerForm.email = email;
                this.loginForm.email = email;
            }

            socSec = {
                return: false,
                member_id: this.member?.id || '0'
            }

            const form = localStorage.getItem('form');
            if (form) {
                this.form = JSON.parse(form);
                modRequest.request('nurse.application.socialSecurityNumber', null, socSec, (res) => {
                    if (res.success) {
                        if (res.exists) { this.soc_sec_label = "Saved (***-**-****)"; }
                    } else {
                        toastr.error('Error', res.errorMsg);
                    }
                }, (res) => {
                    toastr.error('Error', 'Something went wrong, please try again.');
                })
            }

            this.form.terms.date = (new Date(Date.now() - (new Date()).getTimezoneOffset() * 60000)).toISOString().substr(0, 10);

            this.organizeLoadedFiles();
        },

        data: () => ({
            is_loading: false,
            is_us_citizen: false,
            paginationPage: 1,
            page: 1,
            picker: '',
            dialog: '',
            member: null,
            dialogTwo: '',
            radioGroup: '',
            submitted: false,
            savingSubmit: false,
            states: ['Alabama', 'Alaska', 'Arizona', 'Arkansas', 'California', 'Colorado', 'Connecticut', 'Delaware', 'District of Columbia', 'Florida', 'Georgia', 'Guam', 'Hawaii', 'Idaho', 'Illinois', 'Indiana', 'Iowa', 'Kansas', 'Kentucky', 'Louisiana', 'Maine', 'Marshall Islands', 'Maryland', 'Massachusetts', 'Michigan', 'Minnesota', 'Mississippi', 'Missouri', 'Montana', 'Nebraska', 'Nevada', 'New Hampshire', 'New Jersey', 'New Mexico', 'New York', 'North Carolina', 'North Dakota', 'Ohio', 'Oklahoma', 'Oregon', 'Pennsylvania', 'Rhode Island', 'South Carolina', 'South Dakota', 'Tennessee', 'Texas', 'Utah', 'Vermont', 'Virginia', 'Washington', 'West Virginia', 'Wisconsin', 'Wyoming'],
            loginForm: {
                email: '',
                password: ''
            },
            registerForm: {
                email: '',
                password: ''
            },
            checkbox: false,
            tableOfContents: false,
            date: (new Date(Date.now() - (new Date()).getTimezoneOffset() * 60000)).toISOString().substr(0, 10),
            menu: false,
            is18YearsOld: true,
            form: {
                // Page 1
                nurse: {
                    first_name: '',
                    middle_name: '',
                    last_name: '',
                    street_address: '',
                    street_address_two: '',
                    apartment_number: '',
                    city: '',
                    state: '',
                    zip_code: '',
                    date_of_birth: '',
                    email: '',
                    phone_number: '',
                    position: '',
                    other_position_explanation: '',
                    citizen_of_the_us: '',
                    authorized_to_work_in_the_us: '',
                    socialsecurity_number: '',
                },

                // Page 2
                employment_details_one: {
                    shown: true,
                    company: '',
                    supervisor: '',
                    phone_number: '',
                    email: '',
                    job_title: '',
                    start_date: '',
                    end_date: '',
                    responsibilities: '',
                    reason_for_leaving: '',
                    can_contact_employer: null,
                },

                employment_details_two: {
                    shown: false,
                    company: '',
                    supervisor: '',
                    phone_number: '',
                    email: '',
                    job_title: '',
                    start_date: '',
                    end_date: '',
                    responsibilities: '',
                    reason_for_leaving: '',
                    can_contact_employer: null,
                },

                employment_details_three: {
                    shown: false,
                    company: '',
                    supervisor: '',
                    phone_number: '',
                    email: '',
                    job_title: '',
                    start_date: '',
                    end_date: '',
                    responsibilities: '',
                    reason_for_leaving: '',
                    can_contact_employer: null,
                },

                // Page 3
                highschool: {
                    shown: false,
                    name: '',
                    hs_date_of_completion: '',
                    ged: '',
                    ged_date_of_completion: '',
                    city: '',
                    state: '',
                },

                college: {
                    shown: false,
                    name: '',
                    date_of_completion: '',
                    city: '',
                    state: '',
                    major: '',
                },

                other_education: {
                    shown: false,
                    name: '',
                    date_of_completion: '',
                    city: '',
                    state: '',
                    major: '',
                },

                // Page 4
                professional_reference_one: {
                    full_name: '',
                    relationship: '',
                    phone_number: '',
                    company: '',
                },

                professional_reference_two: {
                    full_name: '',
                    relationship: '',
                    phone_number: '',
                    company: '',
                },

                license_and_certifications: {
                    rn_long_term_care: '',
                    rn_hospital: '',
                    rn_home_health: '',
                    rn_hospice: '',
                    rn_homecare_sitter: '',

                    lpn_long_term_care: '',
                    lpn_hospital: '',
                    lpn_home_health: '',
                    lpn_hospice: '',
                    lpn_homecare_sitter: '',

                    ckc_long_term_care: '',
                    ckc_hospital: '',
                    ckc_home_health: '',
                    ckc_hospice: '',
                    ckc_homecare_sitter: '',

                    cna_long_term_care: '',
                    cna_hospital: '',
                    cna_home_health: '',
                    cna_hospice: '',
                    cna_homecare_sitter: '',

                    sitter_long_term_care: '',
                    sitter_hospital: '',
                    sitter_home_health: '',
                    sitter_hospice: '',
                    sitter_homecare_sitter: '',
                },

                // Page 5
                employment: {
                    currently_employed: '',
                    can_contact_employer: '',
                    one_year_experience: '',
                    mental_physical_disabilities: '',
                    mental_physical_disabilities_explained: '',
                },

                criminal_record: {
                    convictions_or_under_investigation: '',
                    license_or_certification_investigation: '',
                    license_or_certification_revoked_suspended: '',
                    explanation: '',
                },

                nurse_stat_info: {
                    found_by: '',
                    found_by_other_details: '',
                    referred_staff_member: '',
                    signature: '',
                },

                associate: {
                    first_name: '',
                    last_name: '',
                    phone_number: '',
                },

                emergency_contact_one: {
                    first_name: '',
                    last_name: '',
                    relationship: '',
                    phone_number: '',
                },

                emergency_contact_two: {
                    first_name: '',
                    last_name: '',
                    relationship: '',
                    phone_number: '',
                },

                terms: {
                    signature: '',
                    date: (new Date(Date.now() - (new Date()).getTimezoneOffset() * 60000)).toISOString().substr(0, 10),
                    applicant_first_name: '',
                    applicant_last_name: ''
                },

                medical_history: {
                    anemia: 'no',
                    smallpox: 'no',
                    diabetes: 'no',
                    diphtheria: 'no',
                    epilepsy: 'no',
                    heart_disease: 'no',
                    kidney_trouble: 'no',
                    mononucleosis: 'no',
                    scarlet_fever: 'no',
                    typhoid_fever: 'no',
                    hypertension: 'no',
                    latex_allergies: 'no',
                    hernia: 'no',
                    depression: 'no',
                    measles: 'no',
                    hepatitis: 'no',
                    mumps: 'no',
                    pleurisy: 'no',
                    pneumonia: 'no',
                    chicken_pox: 'no',
                    emphysema: 'no',
                    tuberculosis: 'no',
                    whooping_cough: 'no',
                    rheumatic_fever: 'no',
                    carpal_tunnel: 'no',
                    sight_hearing_problems: 'no',
                    including_colorblindness: 'no',
                    explanation: '',
                    routine_vaccinations_current: 'no',
                    hepatitis_a: 'no',
                    hepatitis_b: 'no',
                    covid: 'no',
                    covid_exemption: 'no'
                },

                // Page 9 - Banking Information
                direct_deposit: {
                    bank_account_holder_name: '',
                    bank_account_type: '',
                    bank_account_number: '',
                    bank_account_number_confirmation: '',
                    bank_name: '',
                    bank_routing_number: ''
                },

                tb: {
                    date: '',
                    chest_date: '',
                    signature: '',
                },

                // Page 10 - Document Uploads
                files: [],
                drivers_license: [],
                social_security_card: [],
                tb_skin_test: [],
                nursing_license: [],
                cpr_card: [],
                bls_acls_card: [],
                covid_vaccine_card: []
            },

            soc_sec_label: 'Social Security Number'
        }),

        methods: {
            // copy files array over from vue child component - nurse-app-document-upload
            onFilesUploaded(newFile, fileTag) {

                if (fileTag == 'drivers_license') {
                    this.form.drivers_license.push(newFile)
                } else if (fileTag == 'social_security_card') {
                    this.form.social_security_card.push(newFile)
                } else if (fileTag == 'tb_skin_test') {
                    this.form.tb_skin_test.push(newFile)
                } else if (fileTag == 'nursing_license') {
                    this.form.nursing_license.push(newFile)
                } else if (fileTag == 'cpr_card') {
                    this.form.cpr_card.push(newFile)
                } else if (fileTag == 'bls_acls_card') {
                    this.form.bls_acls_card.push(newFile)
                } else if (fileTag == 'covid_vaccine_card') {
                    this.form.covid_vaccine_card.push(newFile)
                }

                this.form.files = this.form.drivers_license.concat(this.form.social_security_card, this.form.tb_skin_test, this.form.nursing_license, this.form.cpr_card, this.form.bls_acls_card, this.form.covid_vaccine_card);
            },

            onFilesDeleted(file, fileTag) {
                // clearing files aray
                console.log(fileTag);
                if (fileTag == 'drivers_license') {
                    this.form.drivers_license.pop()
                } else if (fileTag == 'social_security_card') {
                    this.form.social_security_card.pop()
                } else if (fileTag == 'tb_skin_test') {
                    this.form.tb_skin_test.pop()
                } else if (fileTag == 'nursing_license') {
                    this.form.nursing_license.pop()
                } else if (fileTag == 'cpr_card') {
                    this.form.cpr_card.pop()
                } else if (fileTag == 'bls_acls_card') {
                    this.form.bls_acls_card.pop()
                } else if (fileTag == 'covid_vaccine_card') {
                    this.form.covid_vaccine_card.pop()
                }

                this.form.files = this.form.drivers_license.concat(this.form.social_security_card, this.form.tb_skin_test, this.form.nursing_license, this.form.cpr_card, this.form.bls_acls_card, this.form.covid_vaccine_card);
            },

            checkUSCitizen() {
                switch (this.form.nurse.citizen_of_the_us) {
                    case 'Yes':
                        this.is_us_citizen = true;
                        this.form.nurse.authorized_to_work_in_the_us = "Yes";
                        break;
                    case 'No':
                        this.is_us_citizen = false;
                        break;
                }
            },

            onLogin() {
                modRequest.request('nurse.application.login', null, this.loginForm, (res) => {
                    if (res.success) {
                        toastr.success('Success', 'Resuming progress...')
                        this.form = res.form;
                        this.form.nurse.socialsecurity_number = '';
                        this.form.terms.date = this.reverseFormatCalenderDate(this.form.terms.date);
                        const formString = JSON.stringify(this.form)
                        localStorage.setItem('form', formString)
                        location.reload()
                    } else {
                        toastr.error('Error', res.errorMsg);
                    }
                }, (res) => {
                    toastr.error('Error', 'Something went wrong, please try again.');
                })
                localStorage.setItem('email', this.loginForm.email)
            },

            saveProgress() {
                // validate before showing dialog
                //this.dialog = false

                this.form.terms.date = this.formatCalenderDate(this.form.terms.date);

                localStorage.setItem('email', this.registerForm.email)

                const registrationInfo = { ...this.registerForm }

                registrationInfo.first_name = this.form.nurse.first_name
                registrationInfo.last_name = this.form.nurse.last_name
                registrationInfo.phone = this.form.nurse.phone_number
                registrationInfo.email2 = registrationInfo.email
                registrationInfo.password2 = registrationInfo.password
                registrationInfo.socialsecurity = this.form.nurse.socialsecurity_number

                var data = { registration_info: registrationInfo, application: this.form, is_submitting: false }
                this.is_loading = true;
                if (this.member) {
                    data.member_id = this.member.id;
                }

                modRequest.request('nurse.application.store', null, data, (res) => {
                    if (res.data.success) {
                        this.form.terms.date = this.reverseFormatCalenderDate(this.form.terms.date);
                        this.form.nurse.socialsecurity_number = '';
                        const formString = JSON.stringify(this.form)
                        localStorage.setItem('form', formString)
                        toastr.success('Success', 'Progress Saved');
                        location.reload();
                    } else {
                        this.is_loading = false;
                        toastr.warning('Missing information ', res.data.error);
                    }
                }, (res) => {
                    this.is_loading = false;
                    toastr.error('Error', 'Something went wrong, please try again.');
                })
                this.is_loading = false;
            },

            saveSubmit() {
                this.form.terms.date = this.formatCalenderDate(this.form.terms.date);
                const registrationInfo = { ...this.registerForm }

                registrationInfo.first_name = this.form.nurse.first_name
                registrationInfo.last_name = this.form.nurse.last_name
                registrationInfo.phone = this.form.nurse.phone_number
                registrationInfo.email2 = registrationInfo.email
                registrationInfo.password2 = registrationInfo.password
                registrationInfo.socialsecurity = this.form.nurse.socialsecurity_number

                const data = { registration_info: registrationInfo, application: this.form, is_submitting: true }

                modRequest.request('nurse.application.store', null, data, (res) => {
                    if (res.data.success) {
                        const formString = JSON.stringify(this.form)

                        localStorage.setItem('form', formString)

                        // this.submitted = true

                        this.dialog = false

                        localStorage.setItem('submitted', true)

                        // send submission confirmation email
                        modRequest.request('nurse.application.submission_email', null, data, (res) => {
                            //
                        }, () => console.log('submission_email did not send'));
                        /** modRequest new page */
                        modRequest.request('nurse.application.submitted_page', null, null, () => {
                            this.form.terms.date = this.reverseFormatCalenderDate(this.form.terms.date);
                            location.reload();
                        }, () => {
                            console.log('Error navigating to submission success page');
                            toastr.error('Error navigating to submission success page');
                            this.form.terms.date = this.reverseFormatCalenderDate(this.form.terms.date);
                            return;
                        });
                    } else {
                        toastr.warning('Error submitting', res.data.error);
                    }
                }, (res) => {
                    toastr.error('Error', 'Something went wrong, please try again.');
                })
                this.form.terms.date = this.reverseFormatCalenderDate(this.form.terms.date);
            },

            onSubmit() {
                if (!this.member) {
                    // Show modal
                    this.dialog = true
                    this.savingSubmit = true
                    return
                }

                // validate all pages
                let errorValidator = { hasError: false, message: '' };
                for (let ap = 1; ap < 10; ap++) {
                    if (!this.validatePage(ap, errorValidator)) {
                        if (errorValidator.hasError) {
                            toastr.error('Required Form Fields Missing:<br>' + errorValidator.message,
                                'Please complete the required fields on page ' + ap + ' to continue<br>'
                            );
                            return;
                        }
                    }
                }

                // this.submitted = true

                // localStorage.setItem('submitted', true)

                this.form.terms.date = this.formatCalenderDate(this.form.terms.date);
                const data = { application: this.form, is_submitting: true }

                modRequest.request('nurse.application.store', null, data, (res) => {
                    const formString = JSON.stringify(this.form)

                    localStorage.setItem('form', formString)
                }, () => {
                    console.log('no');
                    toastr.error('Error saving submission form. Please try again.');
                    this.form.terms.date = this.reverseFormatCalenderDate(this.form.terms.date);
                    return;
                });

                modRequest.request('nurse.application.submission_email', null, data, (res) => {
                    console.log('submission_email: ', res);
                }, () => {
                    console.log('submission_email - no');
                    toastr.error('Error sending application submitted email. Please try again.');
                    this.form.terms.date = this.reverseFormatCalenderDate(this.form.terms.date);
                    return;
                });

                /** modRequest new page */
                modRequest.request('nurse.application.submitted_page', null, null, () => {
                    this.form.terms.date = this.reverseFormatCalenderDate(this.form.terms.date);
                    location.reload();
                }, () => {
                    console.log('Error navigating to submission success page');
                    toastr.error('Error navigating to submission success page');
                    this.form.terms.date = this.reverseFormatCalenderDate(this.form.terms.date);
                    return;
                });

            },

            removeGroup(type) {
                switch (type) {
                    case 'highschool':
                        this.form.highschool = {
                            shown: false,
                            name: '',
                            hs_date_of_completion: '',
                            ged: '',
                            ged_date_of_completion: '',
                            city: '',
                            state: '',
                        };
                        break;
                    case 'college':
                        this.form.college = {
                            shown: false,
                            name: '',
                            date_of_completion: '',
                            city: '',
                            state: '',
                            major: '',
                        };
                        break;
                    case 'other':
                        this.form.other_education = {
                            shown: false,
                            name: '',
                            date_of_completion: '',
                            city: '',
                            state: '',
                            major: '',
                        };
                        break;
                }
            },

            removeEmploymentDetails(group) {
                switch (group) {
                    case 1:
                        this.form.employment_details_one = {
                            shown: false,
                            company: '',
                            supervisor: '',
                            phone_number: '',
                            email: '',
                            job_title: '',
                            start_date: '',
                            end_date: '',
                            responsibilities: '',
                            reason_for_leaving: '',
                            can_contact_employer: null,
                        }
                        break;
                    case 2:
                        this.form.employment_details_two = {
                            shown: false,
                            company: '',
                            supervisor: '',
                            phone_number: '',
                            email: '',
                            job_title: '',
                            start_date: '',
                            end_date: '',
                            responsibilities: '',
                            reason_for_leaving: '',
                            can_contact_employer: null,
                        }
                        break;
                    case 3:
                        this.form.employment_details_three = {
                            shown: false,
                            company: '',
                            supervisor: '',
                            phone_number: '',
                            email: '',
                            job_title: '',
                            start_date: '',
                            end_date: '',
                            responsibilities: '',
                            reason_for_leaving: '',
                            can_contact_employer: null,
                        }
                        break;
                }
            },
            expandAllEmploymentDetails() {
                this.form.employment_details_one.shown = true;
                this.form.employment_details_two.shown = true;
                this.form.employment_details_three.shown = true;
            },
            onNext() {
                let errorValidator = { hasError: false, message: '' };
                if (!this.validatePage(this.page, errorValidator)) {
                    if (errorValidator.hasError) {
                        toastr.error('Required Form Fields Missing:<br>' + errorValidator.message,
                            'Please complete the required fields on the current page to continue<br>'
                        )
                    }
                } else {
                    this.page = this.page + 1;
                    this.paginationPage = this.page;
                }
            },
            onPrev() {
                this.page = this.page - 1;
                this.paginationPage = this.page;
            },
            onPaginationInput(paginationPage) {
                // This runs when the pagination is interacted with

                let errorValidator = { hasError: false, message: '' };

                // validate current page if we are trying to go to next page
                if (paginationPage === this.page + 1) {
                    if (this.validatePage(this.page, errorValidator)) {
                        this.page = paginationPage;
                    } else {
                        this.paginationPage = this.page;
                        if (errorValidator.hasError) {
                            toastr.error('Required Form Fields Missing:<br>' + errorValidator.message,
                                'Please complete the required fields on the current page to continue<br>')
                        }
                    }
                    //     no validation needed to go back to a page
                } else if (paginationPage <= this.page) {
                    this.page = paginationPage;
                    //    validate a series of pages if we are trying to skip more than one page using the paginator
                } else if (paginationPage >= this.page + 1) {
                    var lastUnfinishedPage = null;
                    while (paginationPage > this.page) {
                        if (!this.validatePage(paginationPage - 1, errorValidator)) {
                            lastUnfinishedPage = paginationPage - 1
                        }
                        paginationPage--;
                    }

                    if (lastUnfinishedPage) {
                        this.paginationPage = this.page;
                        if (errorValidator.hasError) {
                            toastr.error('Required Form Fields Missing:<br>' + errorValidator.message, 'Please complete the required fields on page '
                                + lastUnfinishedPage
                                + ' to continue <br>')
                        }
                    } else {
                        this.page = this.paginationPage;
                    }
                }
            },
            validatePage(pageNum, errorValidator) {
                // full validation on a single page
                // some of the logic here is page-specific
                // returns true if no validation issues were discovered
                var validationResult = true;
                if (pageNum === 1) {
                    if (this.validateDOBForPage1() == false) {
                        errorValidator['message'] += 'Must be 18 years old to apply<br>';
                        errorValidator.hasError = true;
                        validationResult = false;
                    }
                    if (this.soc_sec_label === 'Social Security Number' && this.form.nurse.socialsecurity_number.length !== 11) {
                        errorValidator['message'] += 'Please enter complete Social Security number<br>';
                        errorValidator.hasError = true;
                        validationResult = false;
                    }
                } else if (pageNum === 3) {
                    if (this.form.highschool.shown === false) {
                        errorValidator['message'] += 'Highschool or GED<br>'
                        errorValidator.hasError = true;
                        validationResult = false;
                    }
                } else if (pageNum === 4) {
                    let selection = false;
                    let keys = Object.keys(this.form.license_and_certifications);

                    keys.forEach((e) => {
                        if (this.form.license_and_certifications[e]) {
                            selection = true;
                        }
                        console.log(this.form.license_and_certifications[e]);
                    });

                    if (!selection) {
                        errorValidator['message'] += 'Please select at least one License / Skill / Certification<br>'
                        errorValidator.hasError = true;
                        validationResult = false;
                    }
                } else if (pageNum === 5) {
                    if (this.form.employment.mental_physical_disabilities === '') {
                        errorValidator['message'] += 'Do you have mental/physical disabilities?<br>'
                        errorValidator.hasError = true;
                        validationResult = false;
                    }
                    if (this.form.employment.mental_physical_disabilities === 'Yes' && this.form.employment.mental_physical_disabilities_explained === '') {
                        errorValidator['message'] += 'Please describe mental/physical disabilities<br>'
                        errorValidator.hasError = true;
                        validationResult = false;
                    }
                    if (this.form.nurse_stat_info.found_by === '') {
                        errorValidator['message'] += 'How did you hear about us?<br>'
                        errorValidator.hasError = true;
                        validationResult = false;
                    }
                    if (this.form.nurse_stat_info.found_by === 'Other' && this.form.nurse_stat_info.found_by_other_details === '') {
                        errorValidator['message'] += 'Please explain how you heard about us<br>'
                        errorValidator.hasError = true;
                        validationResult = false;
                    }
                    if (this.form.criminal_record.convictions_or_under_investigation === '') {
                        errorValidator['message'] += 'Please answer all questions in Criminal Record section<br>'
                        errorValidator.hasError = true;
                        validationResult = false;
                    }
                    if (this.form.criminal_record.license_or_certification_investigation === '') {
                        errorValidator['message'] += 'Please answer all questions in Criminal Record section<br>'
                        errorValidator.hasError = true;
                        validationResult = false;
                    }
                    if (this.form.criminal_record.license_or_certification_revoked_suspended === '') {
                        errorValidator['message'] += 'Please answer all questions in Criminal Record section<br>'
                        errorValidator.hasError = true;
                        validationResult = false;
                    }
                } else if (pageNum === 7) {
                    console.log(this.form.terms);
                    if (this.form.terms.signature === '') {
                        errorValidator['message'] += 'Please input your name as a digital signature to continue<br>'
                        errorValidator.hasError = true;
                        validationResult = false;
                    }
                    if (this.form.terms.date !== this.date) {
                        errorValidator['message'] += 'Please date the signature on the current date<br>'
                        errorValidator.hasError = true;
                        validationResult = false;
                    }
                } else if (pageNum === 8) {
                    if (this.form.tb.signature === '') {
                        errorValidator['message'] += 'Please input your name as a digital signature to continue<br>'
                        errorValidator.hasError = true;
                        validationResult = false;
                    }
                } else if (pageNum === 9) {
                    if (this.form.direct_deposit.bank_account_number !== this.form.direct_deposit.bank_account_number_confirmation) {
                        errorValidator['message'] += 'Account number and Account number confirmation do not match<br>'
                        errorValidator.hasError = true;
                        validationResult = false;
                    }
                } else if (pageNum === 10) {
                    if (this.form.drivers_license.length < 1) {
                        errorValidator['message'] += 'Please upload drivers license<br>'
                        errorValidator.hasError = true;
                        validationResult = false;
                    }
                    if (this.form.social_security_card.length < 1) {
                        errorValidator['message'] += 'Please upload social security card<br>'
                        errorValidator.hasError = true;
                        validationResult = false;
                    }
                    if (this.form.tb_skin_test.length < 1) {
                        errorValidator['message'] += 'Please upload TB skin test<br>'
                        errorValidator.hasError = true;
                        validationResult = false;
                    }
                }
                // Check if there are any required fields to validate and return none
                if (document.getElementById("page-" + pageNum).getElementsByClassName("required") == null) {
                    return validationResult;
                }
                // Get all the inputs on the page that are required
                var requiredFields = document.getElementById("page-" + pageNum).getElementsByClassName("required");
                //Loop through required fields for validation
                [...requiredFields].forEach((e) => {
                    // get the input element
                    var element = e.previousElementSibling.querySelector('input');
                    var value = element.value;
                    // if no value in the input element look for a hidden input element
                    if (value == null || value === '') {
                        element = e.previousElementSibling.querySelector('input[type="hidden"]');
                        if (element && element.value) {
                            value = element.value;
                        }
                    }
                    // Get the label for this input - This is used when notifying the user what required input they missed
                    var labelElement = e.previousElementSibling.querySelector('label');
                    if (labelElement && labelElement.textContent) {
                        var label = e.previousElementSibling.querySelector('label').textContent;
                    } else {
                        // When the undefined happens
                        var label = 'Undefined';
                    }
                    // run the value through some additional situation validation
                    if (value && !this.validateValue(label, value)) {
                        errorValidator.message += label + ' - incomplete <br>';
                        errorValidator.hasError = true;
                        validationResult = false;
                    }

                    if (value == null || value === '') {
                        errorValidator.message += label + ' - empty <br>';
                        errorValidator.hasError = true;
                        validationResult = false;
                    }
                });

                return validationResult;
            },
            validateValue(label, value) {
                switch (label) {
                    case 'Authorized to Work?':
                        if (value === 'No') {
                            toastr.error('Invalid field', 'If you are not authorized to work you cannot submit this form.');
                            return false;
                        }
                        return true;
                        break;
                    case 'Social Security Number':
                        if (!this.validateSSN(value)) {
                            return false;
                        }
                        return true;
                        break;
                    case 'Email':
                        if (!this.validateEmail(value)) {
                            return false;
                        }
                        return true;
                        break;
                    case 'Phone Number':
                        if (!this.validatePhone(value)) {
                            return false;
                        }
                        return true;
                        break;
                    case 'Date *':
                    case 'Date':
                        if (!this.validateDate(value)) {
                            return false;
                        }
                        return true;
                        break;
                    case 'Year of Completion *':
                        if (!this.validateYear(value)) {
                            return false;
                        }
                        return true;
                        break;
                    case 'Routing Number / ABA Number':
                        let response = this.validateRoutingNumber(value);
                        if (!response['success']) {
                            toastr.error('Routing number ' + response['error'], 'Additional information');
                            return false;
                        }
                        return true;
                        break;
                    default:
                        break;
                }
                return true;
            },
            // Validates that the input string is a valid date formatted as "mm/dd/yyyy"
            isValidDate(dateString) {
                // First check for the pattern
                if (!/^\d{1,2}\/\d{1,2}\/\d{4}$/.test(dateString))
                    return false;

                // Parse the date parts to integers
                let parts = dateString.split("/");
                let day = parseInt(parts[1], 10);
                let month = parseInt(parts[0], 10);
                let year = parseInt(parts[2], 10);

                // Check the ranges of month and year
                if (year < 1000 || year > 3000 || month === 0 || month > 12)
                    return false;

                let monthLength = [31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];

                // Adjust for leap years
                if (year % 400 === 0 || (year % 100 !== 0 && year % 4 === 0))
                    monthLength[1] = 29;

                // Check the range of the day
                return day > 0 && day <= monthLength[month - 1];
            },
            validateSSN(ssnValue) {
                return String(ssnValue)
                    .toLowerCase()
                    .match(
                        /^(?!000|666)[0-8][0-9]{2}-(?!00)[0-9]{2}-(?!0000)[0-9]{4}$/
                    );
            },
            validateEmail(email) {
                return String(email)
                    .toLowerCase()
                    .match(
                        /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/
                    );
            },
            validatePhone(phone) {
                return String(phone)
                    .toLowerCase()
                    .match(
                        /^(\()?\d{3}(\))?(-|\s)?\d{3}(-|\s)\d{4}$/
                    );
            },
            validateDate(date) {
                return String(date)
                    .toLowerCase()
                    .match(
                        /^\d{2}(\/)?\d{2}(\/)\d{4}$/
                    ) && this.isValidDate(date);
            },
            validateYear(year) {
                return String(year)
                    .toLowerCase()
                    .match(
                        /\d{4}$/
                    );
            },
            validateRoutingNumber(value) {
                let response = { 'success': false };
                if (isNaN(value) || value.length != 9) {
                    response['error'] = 'Is not a number, or not the required length';
                    return response;
                }

                let sum = (3 * (Number(value.substring(0, 1)) + Number(value.substring(3, 4)) + Number(value.substring(6, 7)))) +
                    (7 * (Number(value.substring(1, 2)) + Number(value.substring(4, 5)) + Number(value.substring(7, 8)))) +
                    (1 * (Number(value.substring(2, 3)) + Number(value.substring(5, 6)) + Number(value.substring(8, 9))));

                let mod = (sum % 10);
                if (mod == 0) {
                    response['success'] = true;
                    return response;
                } else {
                    response['error'] = 'Is not a valid routing number';
                    return response;
                }
            },
            validateDOBForPage1() {
                let DOB = this.form.nurse.date_of_birth;
                let date = new Date();
                currentYear = date.getFullYear();
                // 0: month 1: day 2: year
                const DOBarray = DOB.split("/");
                if ((currentYear - parseInt(DOBarray[2])) > 18) {
                    return true;
                } else if ((currentYear - parseInt(DOBarray[2])) == 18) {
                    currentMonth = date.getMonth() + 1;
                    if (currentMonth > parseInt(DOBarray[0])) {
                        return true;
                    } else if (currentMonth == parseInt(DOBarray[0])) {
                        currentDay = date.getDate();
                        if (currentDay >= parseInt(DOBarray[1])) {
                            return true;
                        }
                    }
                }
                return false;
            },
            formatCalenderDate(date) {

                dateArray = date.split("-");

                year = dateArray[0];
                month = dateArray[1];
                day = dateArray[2];

                correctDateFormat = month + "/" + day + "/" + year;

                return correctDateFormat;
            },
            reverseFormatCalenderDate(date) {

                dateArray = date.split("/");

                year = dateArray[2];
                month = dateArray[0];
                day = dateArray[1];

                correctDateFormat = year + "-" + month + "-" + day;

                return correctDateFormat;
            },
            organizeLoadedFiles() {
                if (this.form.files != "") {
                    var localFiles = this.form.files;
                } else {
                    var localFiles = [];
                }
                this.form.files = [];
                this.form.drivers_license = [];
                this.form.social_security_card = [];
                this.form.tb_skin_test = [];
                this.form.nursing_license = [];
                this.form.cpr_card = [];
                this.form.bls_acls_card = [];
                this.form.covid_vaccine_card = [];

                for (let i = 0; i < localFiles?.length; i++) {
                    if (localFiles[i].fileTag == "drivers_license") { this.form.drivers_license.push(localFiles[i]) }
                    if (localFiles[i].fileTag == "social_security_card") { this.form.social_security_card.push(localFiles[i]) }
                    if (localFiles[i].fileTag == "tb_skin_test") { this.form.tb_skin_test.push(localFiles[i]) }
                    if (localFiles[i].fileTag == "nursing_license") { this.form.nursing_license.push(localFiles[i]) }
                    if (localFiles[i].fileTag == "cpr_card") { this.form.cpr_card.push(localFiles[i]) }
                    if (localFiles[i].fileTag == "bls_acls_card") { this.form.bls_acls_card.push(localFiles[i]) }
                    if (localFiles[i].fileTag == "covid_vaccine_card") { this.form.covid_vaccine_card.push(localFiles[i]) }
                }
            },
            fixSocialInputLabel() {
                this.soc_sec_label = 'Social Security Number';
            },
            getTodayDate() {
                let today = new Date();
                const dd = String(today.getDate()).padStart(2, '0');
                const mm = String(today.getMonth() + 1).padStart(2, '0');
                const yyyy = today.getFullYear();

                today = yyyy + '-' + mm + '-' + dd;
                return today;
            },
            justToday(val) {
                return (val == this.getTodayDate());
            },
        }
    })
})
