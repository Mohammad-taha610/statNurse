window.addEventListener('load', () => {

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
    Vue.use(window.VueTheMask)
    Vue.component('deprecated-nurse-app', {
        template: `
            <div class="container mb-16 nurse-app-form">
                <div class="row">
                    <div class="col-md-12">
                        <div class="d-flex mb-8" style="justify-content: space-between">
                            <v-pagination
                                v-model="page"
                                :length="10"
                            ></v-pagination>

                            <div v-if="!form.approved_at && !form.declined_at">
                                <v-btn color="error" @click="onDecline">Decline</v-btn>

                                <v-btn color="success" @click="onApprove">Approve</v-btn>
                            </div>

                            <div class="alert alert-success" v-if="approvedMessage">{{ approvedMessage }}</div>
                            <div class="alert alert-success" v-if="declinedMessage">{{ declinedMessage }}</div>
                        </div>
                        <div class="d-flex mb-8 justify-center">
                            <div v-if="application_status">
                                <template v-if="application_status === 'approved'">
                                    <v-alert
                                        type="success"
                                    >This application was <b>Approved</b></v-alert>
                                </template>
                                <template v-else-if="application_status === 'declined'">
                                    <v-alert
                                        type="error"
                                    >This application was <b>Declined</b></v-alert>
                                </template>
                                <template v-else-if="application_status === 'saved'">
                                    <v-alert
                                        type="info"
                                    >This application was <b>Saved</b></v-alert>
                                </template>
                                <template v-else-if="application_status === 'submitted'">
                                    <v-alert
                                        type="warning"
                                    >This application was <b>Submitted</b></v-alert>
                                </template>
                            </div>
                        </div>
                        <div v-show="page == 1">
                            <div class="row">
                                <div class="col-lg-3">
                                    <div class="nurse-form-flush">
                                        <strong>Full Name *</strong>
                                    </div>
                                </div>

                                <div class="col-lg-9">
                                    <div class="row">
                                        <div class="col-lg-6">
                                            <v-text-field label="First Name"
                                                          disabled
                                                          v-model="form.nurse.first_name"></v-text-field>
                                        </div>

                                        <div class="col-lg-6">
                                            <v-text-field label="Middle Name"
                                                          disabled
                                                          v-model="form.nurse.middle_name"></v-text-field>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-lg-12">
                                                <v-text-field label="Last Name"
                                                              disabled
                                                              v-model="form.nurse.last_name"></v-text-field>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Address -->
                            <div class="row">
                                <div class="col-lg-3">
                                    <div class="nurse-form-flush">
                                        <strong>Address</strong>
                                    </div>
                                </div>

                                <div class="col-lg-9">
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <v-text-field label="Street Address"
                                                          disabled
                                                          v-model="form.nurse.street_address"></v-text-field>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-lg-12">
                                            <v-text-field label="Street Address Line 2"
                                                          disabled
                                                          v-model="form.nurse.street_address_two"></v-text-field>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-lg-6">
                                            <v-text-field label="City"
                                                          disabled
                                                          v-model="form.nurse.city"></v-text-field>
                                        </div>

                                        <div class="col-lg-6">
                                            <v-text-field label="State / Province"
                                                          disabled
                                                          v-model="form.nurse.state"></v-text-field>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-lg-6">
                                            <v-text-field label="Postal / Zip Code"
                                                          disabled
                                                          v-model="form.nurse.zip_code"></v-text-field>
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
                                            <v-text-field label="Date" 
                                                          disabled
                                                          v-mask="'##/##/####'" 
                                                          v-model="form.nurse.date_of_birth"></v-text-field>
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
                                                <v-text-field type="email" 
                                                          disabled
                                                          label="Email"
                                                          v-model="form.nurse.email"></v-text-field>
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
                                                <v-text-field label="Phone Number"
                                                              disabled
                                                              v-mask="'(###) ###-####'"
                                                              v-model="form.nurse.phone_number"></v-text-field>
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
                                                <v-select
                                                        disabled
                                                        attach
                                                        :items="['RN', 'LPN', 'CNA', 'CMA/KMA', 'Homecare/Sitter', 'Other']"
                                                        label="Position"
                                                        key="nurse-position"
                                                        v-model="form.nurse.position"
                                                ></v-select>
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
                                                <v-select
                                                        disabled
                                                        attach
                                                        :items="['Yes', 'No']"
                                                        label="Are you a citizen of the US?"
                                                        v-model="form.nurse.citizen_of_the_us"
                                                ></v-select>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-lg-3">
                                            <strong>If no, are you authorized to work in the US?
                                                *</strong>
                                        </div>

                                        <div class="col-lg-9">
                                                <v-select
                                                        disabled
                                                        attach
                                                        :items="['Yes', 'No']"
                                                        label="Authorized to Work?"
                                                        v-model="form.nurse.authorized_to_work_in_the_us"
                                                ></v-select>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-lg-3">
                                            <strong>Social Security Number *</strong>
                                        </div>

                                        <div class="col-lg-9">
                                                <v-text-field label="Social Security Number"
                                                              disabled
                                                              v-mask="'###-##-####'"
                                                              v-model="form.nurse.socialsecurity_number"></v-text-field>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>

                        <div v-show="page == 2">
                            <!-- Currently Employed -->
                            <div class="row">
                                <div class="col-xs-12 col-lg-3">
                                    <div class="nurse-form-flush">
                                        <strong>Do you have at least 1 year long term care experience?</strong>
                                    </div>
                                </div>

                                <div class="col-xs-12 col-lg-9">
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <v-radio-group 
                                                          disabled v-model="form.employment.one_year_experience">
                                                <v-radio
                                                          disabled
                                                        label="Yes"
                                                        :value="'Yes'"
                                                ></v-radio>

                                                <v-radio
                                                          disabled
                                                        label="No"
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
                                                <strong>If no, please describe your current experience</strong>
                                            </div>
                                        </div>
                                        <div class="col-xs-12 col-lg-9">
                                                <v-text-field label="Current experience" disabled
                                                              v-model="form.employment.less_than_one_year_experience"></v-text-field>
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
                                                          disabled
                                                        label="Yes"
                                                        :value="'Yes'"
                                                ></v-radio>

                                                <v-radio
                                                          disabled
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

                                <div class="col-lg-9" v-if="form.employment_details_one.shown">
                                    <div class="row">
                                        <div class="col-lg-6">
                                            <v-text-field label="Company"
                                                          disabled
                                                          v-model="form.employment_details_one.company"></v-text-field>
                                        </div>

                                        <div class="col-lg-6">
                                            <v-text-field label="Supervisor"
                                                          disabled
                                                          v-model="form.employment_details_one.supervisor"></v-text-field>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-lg-6">
                                            <v-text-field label="Phone Number"
                                                          disabled
                                                          v-model="form.employment_details_one.phone_number"
                                                          v-mask="'(###) ###-####'"></v-text-field>
                                        </div>

                                        <div class="col-lg-6">
                                            <v-text-field label="Email"
                                                          disabled
                                                          v-model="form.employment_details_one.email"></v-text-field>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-lg-6">
                                            <v-text-field label="Job Title"
                                                          disabled
                                                          v-model="form.employment_details_one.job_title"></v-text-field>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Company Address -->
                            <div v-if="form.employment_details_one.shown" class="row">
                                <div class="col-lg-3">
                                    <div class="nurse-form-flush">
                                        <strong>Company Address</strong>
                                    </div>
                                </div>

                                <div class="col-lg-9">
                                    <div class="row">
                                        <div class="col-lg-6">
                                            <v-text-field label="City"
                                                          disabled
                                                          v-model="form.employment_details_one.city"></v-text-field>
                                        </div>

                                        <div class="col-lg-6">
                                            <v-text-field
                                                    label="State"
                                                          disabled
                                                    v-model="form.employment_details_one.state"
                                            ></v-text-field>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Start Date -->
                            <div v-if="form.employment_details_one.shown" class="row">
                                <div class="col-lg-3">
                                    <strong>Employment Start Date *</strong>
                                </div>

                                <div class="col-lg-9">
                                    <div class="row">
                                        <div class="col">
                                            <v-text-field label="Date"
                                                          disabled
                                                          v-mask="'##/##/####'"
                                                          v-model="form.employment_details_one.start_date"></v-text-field>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- End Date -->
                            <div v-if="form.employment_details_one.shown" class="row">
                                <div class="col-lg-3">
                                    <strong>Employment End Date</strong>
                                </div>

                                <div class="col-lg-9">
                                    <div class="row">
                                        <div class="col">
                                            <v-text-field label="Date"
                                                          disabled
                                                          v-mask="'##/##/####'"
                                                          v-model="form.employment_details_one.end_date"></v-text-field>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Responsibilities -->
                            <div v-if="form.employment_details_one.shown" class="row">
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
                                                    disabled
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
                            <div v-if="form.employment_details_one.shown" class="row">
                                <div class="col-lg-3">
                                    <div class="nurse-form-flush">
                                        <strong>Reason for Leaving</strong>
                                    </div>
                                </div>

                                <div class="col-lg-9">
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <v-textarea
                                                    disabled
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
                            <div v-if="form.employment_details_one.shown" class="row">
                                <div class="col-lg-3">
                                    <div class="nurse-form-flush">
                                        <strong>May we contact this employer?</strong>
                                    </div>
                                </div>

                                <div class="col-lg-9">
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <v-radio-group
                                                    disabled
                                                    v-model="form.employment_details_one.can_contact_employer">
                                                <v-radio
                                                    disabled
                                                        label="Yes"
                                                        :value="'Yes'"
                                                ></v-radio>

                                                <v-radio
                                                    disabled
                                                        label="No"
                                                        :value="'No'"
                                                ></v-radio>
                                            </v-radio-group>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <nst-line-break
                                    v-if="form.employment_details_one.shown || form.employment_details_two.shown"></nst-line-break>

                            <!-- Employment Details -->
                            <div v-if="form.employment_details_one.shown" class="row">
                                <div class="col-lg-3">
                                    <div class="nurse-form-flush">
                                        <strong>Employment Details 2</strong>
                                    </div>
                                </div>

                                <div v-if="form.employment_details_two.shown" class="col-lg-9">
                                    <div class="row">
                                        <div class="col-lg-6">
                                            <v-text-field label="Company"
                                                    disabled
                                                          v-model="form.employment_details_two.company"></v-text-field>
                                        </div>

                                        <div class="col-lg-6">
                                            <v-text-field label="Supervisor"
                                                    disabled
                                                          v-model="form.employment_details_two.supervisor"></v-text-field>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-lg-6">
                                            <v-text-field label="Phone Number"
                                                    disabled
                                                          v-model="form.employment_details_two.phone_number"
                                                          v-mask="'(###) ###-####'"></v-text-field>
                                        </div>

                                        <div class="col-lg-6">
                                            <v-text-field label="Email"
                                                    disabled
                                                          v-model="form.employment_details_two.email"></v-text-field>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-lg-6">
                                            <v-text-field label="Job Title"
                                                    disabled
                                                          v-model="form.employment_details_two.job_title"></v-text-field>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Company Address -->
                            <div v-if="form.employment_details_two.shown" class="row">
                                <div class="col-lg-3">
                                    <div class="nurse-form-flush">
                                        <strong>Company Address</strong>
                                    </div>
                                </div>

                                <div class="col-lg-9">
                                    <div class="row">
                                        <div class="col-lg-6">
                                            <v-text-field label="City"
                                                    disabled
                                                          v-model="form.employment_details_two.city"></v-text-field>
                                        </div>

                                        <div class="col-lg-6">
                                            <v-text-field
                                                    label="State"
                                                    disabled
                                                    v-model="form.employment_details_two.state"
                                            ></v-text-field>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Start Date -->
                            <div v-if="form.employment_details_two.shown" class="row">
                                <div class="col-lg-3">
                                    <strong>Employment Start Date *</strong>
                                </div>

                                <div class="col-lg-9">
                                    <div class="row">
                                        <div class="col">
                                            <v-text-field label="Date"
                                                    disabled
                                                          v-mask="'##/##/####'"
                                                          v-model="form.employment_details_two.start_date"></v-text-field>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- End Date -->
                            <div v-if="form.employment_details_two.shown" class="row">
                                <div class="col-lg-3">
                                    <strong>Employment End Date *</strong>
                                </div>

                                <div class="col-lg-9">
                                    <div class="row">
                                        <div class="col">
                                            <v-text-field label="Date"
                                                    disabled
                                                          v-mask="'##/##/####'"
                                                          v-model="form.employment_details_two.end_date"></v-text-field>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Responsibilities -->
                            <div v-if="form.employment_details_two.shown" class="row">
                                <div class="col-lg-3">
                                    <div class="nurse-form-flush">
                                        <strong>Responsibilities</strong>
                                    </div>
                                </div>

                                <div class="col-lg-9">
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <v-textarea
                                                    disabled
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
                            <div v-if="form.employment_details_two.shown" class="row">
                                <div class="col-lg-3">
                                    <div class="nurse-form-flush">
                                        <strong>Reason for Leaving</strong>
                                    </div>
                                </div>

                                <div class="col-lg-9">
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <v-textarea
                                                    disabled
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
                            <div v-if="form.employment_details_two.shown" class="row">
                                <div class="col-lg-3">
                                    <div class="nurse-form-flush">
                                        <strong>May we contact this employer?</strong>
                                    </div>
                                </div>

                                <div class="col-lg-9">
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <v-radio-group
                                                    disabled
                                                    v-model="form.employment_details_two.can_contact_employer">
                                                <v-radio
                                                    disabled
                                                        label="Yes"
                                                        :value="'Yes'"
                                                ></v-radio>

                                                <v-radio
                                                    disabled
                                                        label="No"
                                                        :value="'No'"
                                                ></v-radio>
                                            </v-radio-group>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <nst-line-break
                                    v-if="form.employment_details_two.shown || form.employment_details_three.shown"></nst-line-break>

                            <!-- Employment Details -->
                            <div v-if="form.employment_details_two.shown" class="row">
                                <div class="col-lg-3">
                                    <div class="nurse-form-flush">
                                        <strong>Employment Details 3</strong>
                                    </div>
                                </div>

                                <div v-if="form.employment_details_three.shown" class="col-lg-9">
                                    <div class="row">
                                        <div class="col-lg-6">
                                            <v-text-field label="Company"
                                                    disabled
                                                          v-model="form.employment_details_three.company"></v-text-field>
                                        </div>

                                        <div class="col-lg-6">
                                            <v-text-field label="Supervisor"
                                                    disabled
                                                          v-model="form.employment_details_three.supervisor"></v-text-field>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-lg-6">
                                            <v-text-field label="Phone Number"
                                                    disabled
                                                          v-model="form.employment_details_three.phone_number"
                                                          v-mask="'(###) ###-####'"></v-text-field>
                                        </div>

                                        <div class="col-lg-6">
                                            <v-text-field label="Email"
                                                    disabled
                                                          v-model="form.employment_details_three.email"></v-text-field>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-lg-6">
                                            <v-text-field label="Job Title"
                                                    disabled
                                                          v-model="form.employment_details_three.job_title"></v-text-field>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Company Address -->
                            <div v-if="form.employment_details_three.shown" class="row">
                                <div class="col-lg-3">
                                    <div class="nurse-form-flush">
                                        <strong>Company Address</strong>
                                    </div>
                                </div>

                                <div class="col-lg-9">
                                    <div class="row">
                                        <div class="col-lg-6">
                                            <v-text-field label="City"
                                                    disabled
                                                          v-model="form.employment_details_three.city"></v-text-field>
                                        </div>

                                        <div class="col-lg-6">
                                            <v-text-field
                                                    label="State"
                                                    disabled
                                                    v-model="form.employment_details_three.state"
                                            ></v-text-field>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Start Date -->
                            <div v-if="form.employment_details_three.shown" class="row">
                                <div class="col-lg-3">
                                    <strong>Employment Start Date *</strong>
                                </div>

                                <div class="col-lg-9">
                                    <div class="row">
                                        <div class="col">
                                            <v-text-field label="Date"
                                                    disabled
                                                          v-mask="'##/##/####'"
                                                          v-model="form.employment_details_three.start_date"></v-text-field>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- End Date -->
                            <div v-if="form.employment_details_three.shown" class="row">
                                <div class="col-lg-3">
                                    <strong>Employment End Date *</strong>
                                </div>

                                <div class="col-lg-9">
                                    <div class="row">
                                        <div class="col">
                                            <v-text-field label="Date" 
                                                    disabled
                                                          v-mask="'##/##/####'"
                                                          v-model="form.employment_details_three.end_date"></v-text-field>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Responsibilities -->
                            <div v-if="form.employment_details_three.shown" class="row">
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
                                                    disabled
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
                            <div v-if="form.employment_details_three.shown" class="row">
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
                                                    disabled
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
                            <div v-if="form.employment_details_three.shown" class="row">
                                <div class="col-lg-3">
                                    <div class="nurse-form-flush">
                                        <strong>May we contact this employer?</strong>
                                    </div>
                                </div>

                                <div class="col-lg-9">
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <v-radio-group
                                                    disabled
                                                    v-model="form.employment_details_three.can_contact_employer">
                                                <v-radio
                                                    disabled
                                                        label="Yes"
                                                        :value="'Yes'"
                                                ></v-radio>

                                                <v-radio
                                                    disabled
                                                        label="No"
                                                        :value="'No'"
                                                ></v-radio>
                                            </v-radio-group>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>

                        <div v-show="page == 3">
                            <!-- High School -->
                            <div class="row">
                                <div class="col-lg-3">
                                    <div class="nurse-form-flush">
                                        <strong>High School or GED</strong>
                                    </div>
                                </div>
                                <div v-if="form.highschool.shown" class="col-lg-9">
                                    <div class="row">
                                        <div class="col-lg-6">
                                            <v-text-field label="School Name"
                                                    disabled
                                                          v-model="form.highschool.name"></v-text-field>
                                        </div>

                                        <div class="col-lg-6">
                                            <v-text-field label="Finish Date"
                                                    disabled
                                                          v-model="form.highschool.hs_date_of_completion" 
                                                          v-mask="'##/##/####'"></v-text-field>
                                        </div>
                                    </div>

                                    <div v-if="form.highschool.shown" class="row">
                                        <div class="col-lg-6">
                                            <v-text-field label="GED"
                                                    disabled
                                                          v-model="form.highschool.ged"></v-text-field>
                                        </div>

                                        <div class="col-lg-6">
                                            <v-text-field label="Date of Completion"
                                                    disabled
                                                          v-model="form.highschool.ged_date_of_completion" 
                                                          v-mask="'##/##/####'"></v-text-field>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div v-if="form.highschool.shown" class="row">
                                <div class="col-lg-3">
                                    <div class="nurse-form-flush">
                                        <strong>Address</strong>
                                    </div>
                                </div>

                                <div class="col-lg-9">
                                    <div class="row">
                                        <div class="col-lg-6">
                                            <v-text-field label="City"
                                                    disabled
                                                          v-model="form.highschool.city"></v-text-field>
                                        </div>

                                        <div class="col-lg-6">
                                            <v-text-field
                                                    label="State"
                                                    disabled
                                                    v-model="form.highschool.state"
                                            ></v-text-field>
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


                                <div v-if="form.college.shown" class="col-lg-9">
                                    <div class="row">
                                        <div class="col-lg-6">
                                            <v-text-field label="School Name"
                                                    disabled
                                                          v-model="form.college.name"></v-text-field>
                                        </div>

                                        <div class="col-lg-6">
                                            <v-text-field label="Date of Completion"
                                                    disabled
                                                          v-model="form.college.date_of_completion" 
                                                          v-mask="'##/##/####'"></v-text-field>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div v-if="form.college.shown" class="row">
                                <div class="col-lg-3">
                                    <div class="nurse-form-flush">
                                        <strong>Address</strong>
                                    </div>
                                </div>

                                <div class="col-lg-9">
                                    <div class="row">
                                        <div class="col-lg-6">
                                            <v-text-field label="City"
                                                    disabled
                                                          v-model="form.college.city"></v-text-field>
                                        </div>

                                        <div class="col-lg-6">
                                            <v-text-field
                                                    label="State"
                                                    disabled
                                                    v-model="form.college.state"
                                            ></v-text-field>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div v-if="form.college.shown" class="row">
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
                                                    disabled
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

                                <div v-if="!form.other_education.shown">
                                    <v-btn
                                            color="primary"
                                            @click="form.other_education.shown = true">
                                        <v-icon>mdi-plus</v-icon>
                                    </v-btn>
                                </div>
                                <div v-if="form.other_education.shown" class="col-lg-9">
                                    <div class="row">
                                        <div class="col-lg-6">
                                            <v-text-field label="School Name"
                                                    disabled
                                                          v-model="form.other_education.name"></v-text-field>
                                        </div>

                                        <div class="col-lg-6">
                                            <v-text-field label="Date of Completion"
                                                    disabled
                                                          v-model="form.other_education.date_of_completion" 
                                                          v-mask="'##/##/####'"></v-text-field>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div v-if="form.other_education.shown" class="row">
                                <div class="col-lg-3">
                                    <div class="nurse-form-flush">
                                        <strong>Address</strong>
                                    </div>
                                </div>

                                <div class="col-lg-9">
                                    <div class="row">
                                        <div class="col-lg-6">
                                            <v-text-field label="City"
                                                    disabled
                                                          v-model="form.other_education.city"></v-text-field>
                                        </div>

                                        <div class="col-lg-6">
                                            <v-text-field
                                                    label="State"
                                                    disabled
                                                    v-model="form.other_education.state"
                                            ></v-text-field>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div v-if="form.other_education.shown" class="row">
                                <div class="col-lg-3">
                                    <div class="nurse-form-flush">
                                        <strong>Subjects / Major</strong>
                                    </div>
                                </div>

                                <div class="col-lg-9">
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <v-textarea
                                                    disabled
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

                        <div v-show="page == 4">
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
                                                <v-text-field label="Full Name"
                                                    disabled
                                                              v-model="form.professional_reference_one.full_name"></v-text-field>
                                        </div>

                                        <div class="col-lg-6">
                                                <v-text-field label="Relationship"
                                                    disabled
                                                              v-model="form.professional_reference_one.relationship"></v-text-field>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-lg-6">
                                                <v-text-field label="Phone Number"
                                                    disabled
                                                              v-model="form.professional_reference_one.phone_number"
                                                              v-mask="'(###) ###-####'"></v-text-field>
                                        </div>

                                        <div class="col-lg-6">
                                                <v-text-field label="Company"
                                                    disabled
                                                              v-model="form.professional_reference_one.company"></v-text-field>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-lg-3">
                                    <div class="nurse-form-flush">
                                        <strong>Professional Reference *</strong>
                                    </div>
                                </div>

                                <div class="col-lg-9">
                                    <div class="row">
                                        <div class="col-lg-6">
                                                <v-text-field label="Full Name"
                                                    disabled
                                                              v-model="form.professional_reference_two.full_name"></v-text-field>
                                        </div>

                                        <div class="col-lg-6">
                                                <v-text-field label="Relationship"
                                                    disabled
                                                              v-model="form.professional_reference_two.relationship"></v-text-field>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-lg-6">
                                                <v-text-field label="Phone Number"
                                                    disabled
                                                              v-model="form.professional_reference_two.phone_number"
                                                              v-mask="'(###) ###-####'"></v-text-field>
                                        </div>

                                        <div class="col-lg-6">
                                                <v-text-field label="Company"
                                                    disabled
                                                              v-model="form.professional_reference_two.company"></v-text-field>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-lg-3">
                                    <div class="nurse-form-flush">
                                        <strong>License / Skills / Certifications</strong>
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
                                                    <v-checkbox disabled
                                                            v-model="form.license_and_certifications.rn_long_term_care"></v-checkbox>
                                                </td>
                                                <td>
                                                    <v-checkbox disabled
                                                            v-model="form.license_and_certifications.rn_hospital"></v-checkbox>
                                                </td>
                                                <td>
                                                    <v-checkbox disabled
                                                            v-model="form.license_and_certifications.rn_home_health"></v-checkbox>
                                                </td>
                                                <td>
                                                    <v-checkbox disabled
                                                            v-model="form.license_and_certifications.rn_hospice"></v-checkbox>
                                                </td>
                                                <td>
                                                    <v-checkbox disabled
                                                            v-model="form.license_and_certifications.rn_homecare_sitter"></v-checkbox>
                                                </td>
                                            </tr>

                                            <tr>
                                                <td>LPN</td>
                                                <td>
                                                    <v-checkbox disabled
                                                            v-model="form.license_and_certifications.lpn_long_term_care"></v-checkbox>
                                                </td>
                                                <td>
                                                    <v-checkbox disabled
                                                            v-model="form.license_and_certifications.lpn_hospital"></v-checkbox>
                                                </td>
                                                <td>
                                                    <v-checkbox disabled
                                                            v-model="form.license_and_certifications.lpn_home_health"></v-checkbox>
                                                </td>
                                                <td>
                                                    <v-checkbox disabled
                                                            v-model="form.license_and_certifications.lpn_hospice"></v-checkbox>
                                                </td>
                                                <td>
                                                    <v-checkbox disabled
                                                            v-model="form.license_and_certifications.lpn_homecare_sitter"></v-checkbox>
                                                </td>
                                            </tr>

                                            <tr>
                                                <td>CMA/KMA/CMT</td>
                                                <td>
                                                    <v-checkbox disabled
                                                            v-model="form.license_and_certifications.ckc_long_term_care"></v-checkbox>
                                                </td>
                                                <td>
                                                    <v-checkbox disabled
                                                            v-model="form.license_and_certifications.ckc_hospital"></v-checkbox>
                                                </td>
                                                <td>
                                                    <v-checkbox disabled
                                                            v-model="form.license_and_certifications.ckc_home_health"></v-checkbox>
                                                </td>
                                                <td>
                                                    <v-checkbox disabled
                                                            v-model="form.license_and_certifications.ckc_hospice"></v-checkbox>
                                                </td>
                                                <td>
                                                    <v-checkbox disabled
                                                            v-model="form.license_and_certifications.ckc_homecare_sitter"></v-checkbox>
                                                </td>
                                            </tr>

                                            <tr>
                                                <td>CNA</td>
                                                <td>
                                                    <v-checkbox disabled
                                                            v-model="form.license_and_certifications.cna_long_term_care"></v-checkbox>
                                                </td>
                                                <td>
                                                    <v-checkbox disabled
                                                            v-model="form.license_and_certifications.cna_hospital"></v-checkbox>
                                                </td>
                                                <td>
                                                    <v-checkbox disabled
                                                            v-model="form.license_and_certifications.cna_home_health"></v-checkbox>
                                                </td>
                                                <td>
                                                    <v-checkbox disabled
                                                            v-model="form.license_and_certifications.cna_hospice"></v-checkbox>
                                                </td>
                                                <td>
                                                    <v-checkbox disabled
                                                            v-model="form.license_and_certifications.cna_homecare_sitter"></v-checkbox>
                                                </td>
                                            </tr>

                                            <tr>
                                                <td>Sitter</td>
                                                <td>
                                                    <v-checkbox disabled
                                                            v-model="form.license_and_certifications.sitter_long_term_care"></v-checkbox>
                                                </td>
                                                <td>
                                                    <v-checkbox disabled
                                                            v-model="form.license_and_certifications.sitter_hospital"></v-checkbox>
                                                </td>
                                                <td>
                                                    <v-checkbox disabled
                                                            v-model="form.license_and_certifications.sitter_home_health"></v-checkbox>
                                                </td>
                                                <td>
                                                    <v-checkbox disabled
                                                            v-model="form.license_and_certifications.sitter_hospice"></v-checkbox>
                                                </td>
                                                <td>
                                                    <v-checkbox disabled
                                                            v-model="form.license_and_certifications.sitter_homecare_sitter"></v-checkbox>
                                                </td>
                                            </tr>
                                            </tbody>
                                        </template>
                                    </v-simple-table>
                                </div>
                            </div>
                        </div>

                        <div v-show="page == 5">

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
                                                    disabled
                                                    v-model="form.employment.mental_physical_disabilities">
                                                <v-radio
                                                    disabled
                                                        label="Yes"
                                                        :value="'Yes'"
                                                ></v-radio>

                                                <v-radio
                                                    disabled
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
                                        <strong>If yes please describe</strong>
                                    </div>
                                </div>

                                <div class="col-lg-9">
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <v-text-field label="Description"
                                                    disabled
                                                          v-model="form.employment.mental_physical_disabilities_explained"></v-text-field>
                                        </div>
                                    </div>
                                </div>
                            </div>


                            <div class="row">
                                <div class="col-lg-3">
                                    <strong>Criminal Record</strong>
                                </div>

                                <div class="col-lg-9">
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <p>Do you have any criminal convictions or are you currently
                                                the subject of any police investigation in the USA or
                                                abroad? *</p>

                                            <v-radio-group
                                                    disabled
                                                    v-model="form.criminal_record.convictions_or_under_investigation">
                                                <v-radio
                                                    disabled
                                                        label="Yes"
                                                        :value="'Yes'"
                                                ></v-radio>

                                                <v-radio
                                                    disabled
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
                                                    disabled
                                                    v-model="form.criminal_record.license_or_certification_investigation">
                                                <v-radio
                                                    disabled
                                                        label="Yes"
                                                        :value="'Yes'"
                                                ></v-radio>

                                                <v-radio
                                                    disabled
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
                                                    disabled
                                                    v-model="form.criminal_record.license_or_certification_revoked_suspended">
                                                <v-radio
                                                    disabled
                                                        label="Yes"
                                                        :value="'Yes'"
                                                ></v-radio>

                                                <v-radio
                                                    disabled
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
                                                    disabled
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

                                            <v-checkbox disabled
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
                                        <strong>How did you hear about us?</strong>
                                    </div>
                                </div>

                                <div class="col-lg-9">
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <v-select
                                                    disabled
                                                    attach
                                                    :items="['Indeed', 'Monster Jobs', 'Social Media', 'Friend', 'Other']"
                                                    label="How did you hear about us?"
                                                    v-model="form.nurse_stat_info.found_by"
                                            ></v-select>
                                        </div>
                                    </div>
                                </div>
                                <div class="row" v-if="form.nurse_stat_info.found_by == 'Other'">
                                    <div class="col-lg-9 offset-3">
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <v-text-field 
                                                    disabled
                                                    label="Please explain:" v-model="form.nurse_stat_info.found_by_other_details"></v-text-field>
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
                                                    disabled
                                                          v-model="form.nurse_stat_info.referred_staff_member"></v-text-field>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                        </div>

                        <div v-show="page == 6">

                            <!-- Address -->
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="nurse-form-flush">
                                        <strong>Emergency Contact Name</strong>
                                    </div>
                                </div>

                                <div class="col-md-9">
                                    <div class="row">
                                        <div class="col-md-6">
                                                <v-text-field label="First Name"
                                                              disabled 
                                                              v-model="form.emergency_contact_one.first_name"></v-text-field>
                                        </div>

                                        <div class="col-md-6">
                                                <v-text-field label="Last Name"
                                                              disabled 
                                                              v-model="form.emergency_contact_one.last_name"></v-text-field>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-3">
                                    <div class="nurse-form-flush">
                                        <strong>Emergency Contact Relationship</strong>
                                    </div>
                                </div>

                                <div class="col-md-9">
                                    <div class="row">
                                        <div class="col-md-6">
                                                <v-text-field label="Relationship"
                                                              disabled 
                                                              v-model="form.emergency_contact_one.relationship"></v-text-field>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-3">
                                    <div class="nurse-form-flush">
                                        <strong>Phone Number *</strong>
                                    </div>
                                </div>

                                <div class="col-md-9">
                                    <div class="row">
                                        <div class="col-md-12">
                                                <v-text-field label="Phone Number"
                                                              disabled 
                                                              v-mask="'(###) ###-####'"
                                                              v-model="form.emergency_contact_one.phone_number"></v-text-field>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-9 offset-md-3">
                                    <hr class="mb-5 pb-1">
                                </div>
                            </div>

                            <!-- Address -->
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="nurse-form-flush">
                                        <strong>Emergency Contact Name</strong>
                                    </div>
                                </div>

                                <div class="col-md-9">
                                    <div class="row">
                                        <div class="col-md-6">
                                                <v-text-field label="First Name"
                                                              disabled 
                                                              v-model="form.emergency_contact_two.first_name"></v-text-field>
                                        </div>

                                        <div class="col-md-6">
                                                <v-text-field label="Last Name"
                                                              disabled 
                                                              v-model="form.emergency_contact_two.last_name"></v-text-field>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-3">
                                    <div class="nurse-form-flush">
                                        <strong>Emergency Contact Relationship</strong>
                                    </div>
                                </div>

                                <div class="col-md-9">
                                    <div class="row">
                                        <div class="col-md-6">
                                                <v-text-field label="Relationship"
                                                              disabled 
                                                              v-model="form.emergency_contact_two.relationship"></v-text-field>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-3">
                                    <div class="nurse-form-flush">
                                        <strong>Phone Number</strong>
                                    </div>
                                </div>

                                <div class="col-md-9">
                                    <div class="row">
                                        <div class="col-md-12">
                                                <v-text-field label="Phone Number"
                                                              disabled 
                                                              v-mask="'(###) ###-####'"
                                                              v-model="form.emergency_contact_two.phone_number"></v-text-field>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div v-show="page == 7">
                            <div class="row">
                                <div class="col-md-12">
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

                                    <p class="mb-0"><strong>Please input your name as a digital signature below indicating your acceptance of this
                                                        agreement *</strong></p>
                                </div>

                                <div class="col-xl-8 col-md-12">
                                    <v-text-field
                                        disabled
                                        label="Signature *"
                                        v-model="form.terms.signature"
                                    ></v-text-field>
                                </div>

                                <div class="col-md-12">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <v-text-field label="Date" 
                                                          disabled 
                                                          v-mask="'##/##/####'"
                                                          v-model="form.terms.date"></v-text-field>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div v-show="page == 8">
                            <div class="row">
                                <div class="col-md-12">
                                    <h3>Medical History Questionnaire</h3>

                                    <p>Have you had any of the following conditions or diseases?</p>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
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
                                                        <v-radio disabled value="yes"></v-radio>
                                                    </v-radio-group>
                                                </td>

                                                <td>
                                                    <v-radio-group
                                                            v-model="form.medical_history.anemia">
                                                        <v-radio disabled value="no"></v-radio>
                                                    </v-radio-group>
                                                </td>
                                            </tr>

                                            <tr>
                                                <td>Smallpox</td>

                                                <td>
                                                    <v-radio-group
                                                            v-model="form.medical_history.smallpox">
                                                        <v-radio disabled value="yes"></v-radio>
                                                    </v-radio-group>
                                                </td>

                                                <td>
                                                    <v-radio-group
                                                            v-model="form.medical_history.smallpox">
                                                        <v-radio disabled value="no"></v-radio>
                                                    </v-radio-group>
                                                </td>
                                            </tr>

                                            <tr>
                                                <td>Diabetes</td>

                                                <td>
                                                    <v-radio-group
                                                            v-model="form.medical_history.diabetes">
                                                        <v-radio disabled value="yes"></v-radio>
                                                    </v-radio-group>
                                                </td>

                                                <td>
                                                    <v-radio-group
                                                            v-model="form.medical_history.diabetes">
                                                        <v-radio disabled value="no"></v-radio>
                                                    </v-radio-group>
                                                </td>
                                            </tr>

                                            <tr>
                                                <td>Diphtheria</td>

                                                <td>
                                                    <v-radio-group
                                                            v-model="form.medical_history.diphtheria">
                                                        <v-radio disabled value="yes"></v-radio>
                                                    </v-radio-group>
                                                </td>

                                                <td>
                                                    <v-radio-group
                                                            v-model="form.medical_history.diphtheria">
                                                        <v-radio disabled value="no"></v-radio>
                                                    </v-radio-group>
                                                </td>
                                            </tr>

                                            <tr>
                                                <td>Epilepsy</td>

                                                <td>
                                                    <v-radio-group
                                                            v-model="form.medical_history.epilepsy">
                                                        <v-radio disabled value="yes"></v-radio>
                                                    </v-radio-group>
                                                </td>

                                                <td>
                                                    <v-radio-group
                                                            v-model="form.medical_history.epilepsy">
                                                        <v-radio disabled value="no"></v-radio>
                                                    </v-radio-group>
                                                </td>
                                            </tr>

                                            <tr>
                                                <td>Heart Disease</td>

                                                <td>
                                                    <v-radio-group
                                                            v-model="form.medical_history.heart_disease">
                                                        <v-radio disabled value="yes"></v-radio>
                                                    </v-radio-group>
                                                </td>

                                                <td>
                                                    <v-radio-group
                                                            v-model="form.medical_history.heart_disease">
                                                        <v-radio disabled value="no"></v-radio>
                                                    </v-radio-group>
                                                </td>
                                            </tr>

                                            <tr>
                                                <td>Kidney Trouble</td>

                                                <td>
                                                    <v-radio-group
                                                            v-model="form.medical_history.kidney_trouble">
                                                        <v-radio disabled value="yes"></v-radio>
                                                    </v-radio-group>
                                                </td>

                                                <td>
                                                    <v-radio-group
                                                            v-model="form.medical_history.kidney_trouble">
                                                        <v-radio disabled value="no"></v-radio>
                                                    </v-radio-group>
                                                </td>
                                            </tr>

                                            <tr>
                                                <td>Mononucleosis</td>

                                                <td>
                                                    <v-radio-group
                                                            v-model="form.medical_history.mononucleosis">
                                                        <v-radio disabled value="yes"></v-radio>
                                                    </v-radio-group>
                                                </td>

                                                <td>
                                                    <v-radio-group
                                                            v-model="form.medical_history.mononucleosis">
                                                        <v-radio disabled value="no"></v-radio>
                                                    </v-radio-group>
                                                </td>
                                            </tr>

                                            <tr>
                                                <td>Scarlet Fever</td>

                                                <td>
                                                    <v-radio-group
                                                            v-model="form.medical_history.scarlet_fever">
                                                        <v-radio disabled value="yes"></v-radio>
                                                    </v-radio-group>
                                                </td>

                                                <td>
                                                    <v-radio-group
                                                            v-model="form.medical_history.scarlet_fever">
                                                        <v-radio disabled value="no"></v-radio>
                                                    </v-radio-group>
                                                </td>
                                            </tr>

                                            <tr>
                                                <td>Typhoid Fever</td>

                                                <td>
                                                    <v-radio-group
                                                            v-model="form.medical_history.typhoid_fever">
                                                        <v-radio disabled value="yes"></v-radio>
                                                    </v-radio-group>
                                                </td>

                                                <td>
                                                    <v-radio-group
                                                            v-model="form.medical_history.typhoid_fever">
                                                        <v-radio disabled value="no"></v-radio>
                                                    </v-radio-group>
                                                </td>
                                            </tr>

                                            <tr>
                                                <td>Hypertension</td>

                                                <td>
                                                    <v-radio-group
                                                            v-model="form.medical_history.hypertension">
                                                        <v-radio disabled value="yes"></v-radio>
                                                    </v-radio-group>
                                                </td>

                                                <td>
                                                    <v-radio-group
                                                            v-model="form.medical_history.hypertension">
                                                        <v-radio disabled value="no"></v-radio>
                                                    </v-radio-group>
                                                </td>
                                            </tr>

                                            <tr>
                                                <td>Latex Allergies</td>

                                                <td>
                                                    <v-radio-group
                                                            v-model="form.medical_history.latex_allergies">
                                                        <v-radio disabled value="yes"></v-radio>
                                                    </v-radio-group>
                                                </td>

                                                <td>
                                                    <v-radio-group
                                                            v-model="form.medical_history.latex_allergies">
                                                        <v-radio disabled value="no"></v-radio>
                                                    </v-radio-group>
                                                </td>
                                            </tr>

                                            <tr>
                                                <td>Hernia</td>

                                                <td>
                                                    <v-radio-group
                                                            v-model="form.medical_history.hernia">
                                                        <v-radio disabled value="yes"></v-radio>
                                                    </v-radio-group>
                                                </td>

                                                <td>
                                                    <v-radio-group
                                                            v-model="form.medical_history.hernia">
                                                        <v-radio disabled value="no"></v-radio>
                                                    </v-radio-group>
                                                </td>
                                            </tr>

                                            <tr>
                                                <td>Depression</td>

                                                <td>
                                                    <v-radio-group
                                                            v-model="form.medical_history.depression">
                                                        <v-radio disabled value="yes"></v-radio>
                                                    </v-radio-group>
                                                </td>

                                                <td>
                                                    <v-radio-group
                                                            v-model="form.medical_history.depression">
                                                        <v-radio disabled value="no"></v-radio>
                                                    </v-radio-group>
                                                </td>
                                            </tr>

                                            <tr>
                                                <td>Measles</td>

                                                <td>
                                                    <v-radio-group
                                                            v-model="form.medical_history.measles">
                                                        <v-radio disabled value="yes"></v-radio>
                                                    </v-radio-group>
                                                </td>

                                                <td>
                                                    <v-radio-group
                                                            v-model="form.medical_history.measles">
                                                        <v-radio disabled value="no"></v-radio>
                                                    </v-radio-group>
                                                </td>
                                            </tr>

                                            <tr>
                                                <td>Hepatitis</td>

                                                <td>
                                                    <v-radio-group
                                                            v-model="form.medical_history.hepatitis">
                                                        <v-radio disabled value="yes"></v-radio>
                                                    </v-radio-group>
                                                </td>

                                                <td>
                                                    <v-radio-group
                                                            v-model="form.medical_history.hepatitis">
                                                        <v-radio disabled value="no"></v-radio>
                                                    </v-radio-group>
                                                </td>
                                            </tr>

                                            <tr>
                                                <td>Mumps</td>

                                                <td>
                                                    <v-radio-group v-model="form.medical_history.mumps">
                                                        <v-radio disabled value="yes"></v-radio>
                                                    </v-radio-group>
                                                </td>

                                                <td>
                                                    <v-radio-group v-model="form.medical_history.mumps">
                                                        <v-radio disabled value="no"></v-radio>
                                                    </v-radio-group>
                                                </td>
                                            </tr>

                                            <tr>
                                                <td>Pleurisy</td>

                                                <td>
                                                    <v-radio-group
                                                            v-model="form.medical_history.pleurisy">
                                                        <v-radio disabled value="yes"></v-radio>
                                                    </v-radio-group>
                                                </td>

                                                <td>
                                                    <v-radio-group
                                                            v-model="form.medical_history.pleurisy">
                                                        <v-radio disabled value="no"></v-radio>
                                                    </v-radio-group>
                                                </td>
                                            </tr>

                                            <tr>
                                                <td>Pneumonia</td>

                                                <td>
                                                    <v-radio-group
                                                            v-model="form.medical_history.pneumonia">
                                                        <v-radio disabled value="yes"></v-radio>
                                                    </v-radio-group>
                                                </td>

                                                <td>
                                                    <v-radio-group
                                                            v-model="form.medical_history.pneumonia">
                                                        <v-radio disabled value="no"></v-radio>
                                                    </v-radio-group>
                                                </td>
                                            </tr>

                                            <tr>
                                                <td>Chicken Pox</td>

                                                <td>
                                                    <v-radio-group
                                                            v-model="form.medical_history.chicken_pox">
                                                        <v-radio disabled value="yes"></v-radio>
                                                    </v-radio-group>
                                                </td>

                                                <td>
                                                    <v-radio-group
                                                            v-model="form.medical_history.chicken_pox">
                                                        <v-radio disabled value="no"></v-radio>
                                                    </v-radio-group>
                                                </td>
                                            </tr>

                                            <tr>
                                                <td>Emphysema</td>

                                                <td>
                                                    <v-radio-group
                                                            v-model="form.medical_history.emphysema">
                                                        <v-radio disabled value="yes"></v-radio>
                                                    </v-radio-group>
                                                </td>

                                                <td>
                                                    <v-radio-group
                                                            v-model="form.medical_history.emphysema">
                                                        <v-radio disabled value="no"></v-radio>
                                                    </v-radio-group>
                                                </td>
                                            </tr>

                                            <tr>
                                                <td>Tuberculosis</td>

                                                <td>
                                                    <v-radio-group
                                                            v-model="form.medical_history.tuberculosis">
                                                        <v-radio disabled value="yes"></v-radio>
                                                    </v-radio-group>
                                                </td>

                                                <td>
                                                    <v-radio-group
                                                            v-model="form.medical_history.tuberculosis">
                                                        <v-radio disabled value="no"></v-radio>
                                                    </v-radio-group>
                                                </td>
                                            </tr>

                                            <tr>
                                                <td>Whooping Cough</td>

                                                <td>
                                                    <v-radio-group
                                                            v-model="form.medical_history.whooping_cough">
                                                        <v-radio disabled value="yes"></v-radio>
                                                    </v-radio-group>
                                                </td>

                                                <td>
                                                    <v-radio-group
                                                            v-model="form.medical_history.whooping_cough">
                                                        <v-radio disabled value="no"></v-radio>
                                                    </v-radio-group>
                                                </td>
                                            </tr>

                                            <tr>
                                                <td>Rheumatic Fever</td>

                                                <td>
                                                    <v-radio-group
                                                            v-model="form.medical_history.rheumatic_fever">
                                                        <v-radio disabled value="yes"></v-radio>
                                                    </v-radio-group>
                                                </td>

                                                <td>
                                                    <v-radio-group
                                                            v-model="form.medical_history.rheumatic_fever">
                                                        <v-radio disabled value="no"></v-radio>
                                                    </v-radio-group>
                                                </td>
                                            </tr>

                                            <tr>
                                                <td>Carpal Tunnel</td>

                                                <td>
                                                    <v-radio-group
                                                            v-model="form.medical_history.carpal_tunnel">
                                                        <v-radio disabled value="yes"></v-radio>
                                                    </v-radio-group>
                                                </td>

                                                <td>
                                                    <v-radio-group
                                                            v-model="form.medical_history.carpal_tunnel">
                                                        <v-radio disabled value="no"></v-radio>
                                                    </v-radio-group>
                                                </td>
                                            </tr>

                                            <tr>
                                                <td>Sight or Hearing problems</td>

                                                <td>
                                                    <v-radio-group
                                                            v-model="form.medical_history.sight_hearing_problems">
                                                        <v-radio disabled value="yes"></v-radio>
                                                    </v-radio-group>
                                                </td>

                                                <td>
                                                    <v-radio-group
                                                            v-model="form.medical_history.sight_hearing_problems">
                                                        <v-radio disabled value="no"></v-radio>
                                                    </v-radio-group>
                                                </td>
                                            </tr>

                                            <tr>
                                                <td>including colorblindness</td>

                                                <td>
                                                    <v-radio-group
                                                            v-model="form.medical_history.including_colorblindness">
                                                        <v-radio disabled value="yes"></v-radio>
                                                    </v-radio-group>
                                                </td>

                                                <td>
                                                    <v-radio-group
                                                            v-model="form.medical_history.including_colorblindness">
                                                        <v-radio disabled value="no"></v-radio>
                                                    </v-radio-group>
                                                </td>
                                            </tr>
                                            </tbody>
                                        </template>
                                    </v-simple-table>
                                </div>

                                <div class="col-md-12">
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

                                <div class="col-md-6">
                                    <v-textarea
                                            disabled
                                            name="input-7-1"
                                            label="Details"
                                            value=""
                                            auto-grow
                                            rows="5"
                                            outlined
                                            v-model="form.medical_history.explanation"
                                    ></v-textarea>
                                </div>

                                <div class="col-md-12">
                                    <div class="row">
                                        <div class="col-md-6">
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
                                                            <v-radio-group v-model="form.medical_history.routine_vaccinations_current">
                                                                <v-radio disabled value="yes"></v-radio>
                                                            </v-radio-group>
                                                        </td>

                                                        <td>
                                                            <v-radio-group v-model="form.medical_history.routine_vaccinations_current">
                                                                <v-radio disabled value="no"></v-radio>
                                                            </v-radio-group>
                                                        </td>
                                                    </tr>

                                                    <tr>
                                                        <td>Hepatitis B</td>

                                                        <td>
                                                            <v-radio-group v-model="form.medical_history.hepatitis_b">
                                                                <v-radio disabled value="yes"></v-radio>
                                                            </v-radio-group>
                                                        </td>

                                                        <td>
                                                            <v-radio-group v-model="form.medical_history.hepatitis_b">
                                                                <v-radio disabled value="no"></v-radio>
                                                            </v-radio-group>
                                                        </td>
                                                    </tr>

                                                    <tr>
                                                        <td>Hepatitis A</td>

                                                        <td>
                                                            <v-radio-group v-model="form.medical_history.hepatitis_a">
                                                                <v-radio disabled value="yes"></v-radio>
                                                            </v-radio-group>
                                                        </td>

                                                        <td>
                                                            <v-radio-group v-model="form.medical_history.hepatitis_a">
                                                                <v-radio disabled value="no"></v-radio>
                                                            </v-radio-group>
                                                        </td>
                                                    </tr>

                                                    <tr>
                                                        <td>COVID-19</td>

                                                        <td>
                                                            <v-radio-group
                                                                    v-model="form.medical_history.covid">
                                                                <v-radio disabled value="yes"></v-radio>
                                                            </v-radio-group>
                                                        </td>

                                                        <td>
                                                            <v-radio-group
                                                                    v-on:click="form.medical_history.covid = no"
                                                                    v-model="form.medical_history.covid">
                                                                <v-radio disabled value="no"></v-radio>
                                                            </v-radio-group>
                                                        </td>
                                                    </tr>

                                                    <transition name="fade">
                                                        <template
                                                                v-if="form.medical_history.covid === 'no'">
                                                            <tr>
                                                                <td>Do you have a COVID-19 exemption?
                                                                </td>

                                                                <td>
                                                                    <v-radio-group
                                                                            v-model="form.medical_history.covid_exemption">
                                                                        <v-radio disabled value="yes"></v-radio>
                                                                    </v-radio-group>
                                                                </td>

                                                                <td>
                                                                    <v-radio-group
                                                                            v-model="form.medical_history.covid_exemption">
                                                                        <v-radio disabled value="no"></v-radio>
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
                                <div class="col-md-12">
                                    <h3>Tuberculosis Screening Questionnaire</h3>
                                </div>
                            </div>

                            <!-- References -->
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="nurse-form-flush">
                                        <strong>Have you had a positive TB skin test in the past? if so
                                            please list date</strong>
                                    </div>
                                </div>

                                <div class="col-md-9">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <v-text-field label="Date" v-mask="'##/##/####'"
                                                          disabled
                                                          v-model="form.tb.date"></v-text-field>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-3">
                                    <div class="nurse-form-flush">
                                        <strong>If you have had a Chest Xray in the past please list
                                            date</strong>
                                    </div>
                                </div>

                                <div class="col-md-9">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <v-text-field label="Date"
                                                          disabled
                                                          v-mask="'##/##/####'"
                                                          v-model="form.tb.chest_date"></v-text-field>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <p>I have answered the questions fully and declare that I have no
                                        known injury, Illness, or ailment other than those previously
                                        noted. I further understand that any misrepresentation, or
                                        omission may be grounds for corrective action up to and
                                        including termination of my contract</p>
                                        
                                    <p class="mb-0"><strong>Please input your name as a digital signature below indicating your acceptance of this agreement *</strong></p>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <strong>Signature</strong>
                                        </div>

                                        <div class="col-xl-8 col-md-12">
                                            <v-text-field
                                                disabled
                                                label="Signature *"
                                                v-model="form.tb.signature"
                                            ></v-text-field>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div v-show="page == 9">
                            <div class="row">
                                <div class="col-12 col-md-8">
                                    <h3>Direct Deposit Information</h3>
                                </div>
                            </div>
                            <!-- Line 1 -->
                            <div class="row">

                                <!-- Account Holder Name -->
                                <div class="col-12 col-md-8">
                                    <div class="col">
                                        <div class="nurse-form-flush">
                                            <strong>Account Holder Name *</strong>
                                        </div>
                                    </div>


                                    <div class="col">
                                            <v-text-field label="Account Holder Name"
                                                          disabled
                                                          v-model="form.direct_deposit.bank_account_holder_name"></v-text-field>
                                    </div>
                                </div>

                                <!-- Account Type -->
                                <div class="col-12 col-md-4">
                                    <div class="col">
                                        <div class="nurse-form-flush">
                                            <strong>Account Type *</strong>
                                        </div>
                                    </div>
                                    <div class="col">
                                            <v-select
                                                    disabled
                                                    attach
                                                    :items="['Checking', 'Savings']"
                                                    v-model="form.direct_deposit.bank_account_type"
                                            ></v-select>
                                    </div>
                                </div>
                            </div> <!-- End: Line 1 -->

                            <!-- Line 2 -->
                            <div class="row">

                                <div class="col-12 col-md-6">
                                    <div class="col">
                                        <div class="nurse-form-flush">
                                            <strong>Account Number *</strong>
                                        </div>
                                    </div>


                                    <div class="col">
                                            <v-text-field label="Account Number"
                                                          disabled
                                                          v-model="form.direct_deposit.bank_account_number"></v-text-field>
                                    </div>
                                </div>

                                <div class="col-12 col-md-6">
                                    <div class="col">
                                        <div class="nurse-form-flush">
                                            <strong>Confirm Account Number *</strong>
                                        </div>
                                    </div>


                                    <div class="col">
                                            <v-text-field label="Confirm Account Number"
                                                          disabled
                                                          v-model="form.direct_deposit.bank_account_number_confirmation"></v-text-field>
                                    </div>
                                </div>
                            </div>  <!-- End: Line 2 -->

                            <!-- Line 3 -->
                            <div class="row">

                                <div class="col col-md-6">
                                    <div class="col">
                                        <div class="nurse-form-flush">
                                            <strong>Routing Number / ABA Number *</strong>
                                        </div>
                                    </div>


                                    <div class="col">
                                            <v-text-field label="Routing Number / ABA Number"
                                                          disabled
                                                          v-model="form.direct_deposit.bank_routing_number"></v-text-field>
                                    </div>
                                </div>
                                
                                <div class="col-12 col-md-6">
                                    <div class="col">
                                        <div class="nurse-form-flush">
                                            <strong>Bank Name *</strong>
                                        </div>
                                    </div>


                                    <div class="col">
                                            <v-text-field label="Bank Name"
                                                          disabled
                                                          v-model="form.direct_deposit.bank_name"></v-text-field>
                                    </div>
                                </div>

                            </div>  <!-- End: Line 3 -->

                        </div>
                        
                        <div v-show="page == 10">
                            <div class="row">
                                <div class="col-12 col-md-8">
                                    <h3>Uploaded Documents</h3>
                                </div>
                            </div>
                            <!-- Line 1 -->
                            <div class="row">

                                <!-- Files -->
                                <div class="col-12 col-md-8">
                                    <div class="row">
                                        <template v-if="!form.files.length" class="col">
                                            No files were uploaded during the application process.
                                        </template>
                                        <template v-else class="col">
                                            <a v-for="file in form.files" :href="file.route" target="_blank" class="mt-3 mr-3">
                                                <v-card class="member-file-card">
                                                    <v-card-text>
                                                        <div class="member-file-icons">
                                                            <v-menu
                                                                    bottom
                                                            >
                                                                <template v-slot:activator="{ on, attrs }">
                                                                    <v-btn
                                                                        v-on="on"
                                                                        v-bind="attrs"
                                                                        icon
                                                                        @click.prevent
                                                                    >
                                                                        <v-icon color="primary">mdi-tag</v-icon>
                                                                    </v-btn>
                                                                </template>
                                                                <v-list dense>
                                                                    <v-list-item
                                                                        v-for="tag in tags"
                                                                        @click="changeTag(file, tag)"
                                                                        :key="tag"
                                                                    >
                                                                        <v-list-item-title
                                                                                class=""
                                                                                :class="'member-file-tag ' + (tag == file.tag ? 'selected-tag' : '')">
                                                                            {{tag}}
                                                                        </v-list-item-title>
                                                                    </v-list-item>
                                                                </v-list>
                                                            </v-menu>
                                                        </div>
                                                        <div class="member-file-icon">
                                                            <v-icon color="primary">mdi-file</v-icon>
                                                        </div>
                                                        <div class="member-file-name-container">
                                                            <span class="member-file-name">{{file.filename}}</span>
                                                        </div>
                                                        <div class="member-file-tag-container mt-1">
                                                            <span class="member-file-tag-name">{{file.tag}}</span>
                                                        </div>
                                                    </v-card-text>
                                                </v-card>
                                            </a>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </div>
                            
                        <div class="mt-16">
                            <div class="d-flex">
                                <v-pagination
                                    v-model="page"
                                    :length="10"
                                ></v-pagination>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `,

        created () {
            this.application_status = application_status;
            this.form = form;
            this.form.nurse = JSON.parse(this.form.nurse) ?? {};
            this.form.employment_details_one = JSON.parse(this.form.employment_details_one) ?? {};
            this.form.employment_details_two = JSON.parse(this.form.employment_details_two) ?? {};
            this.form.employment_details_three = JSON.parse(this.form.employment_details_three) ?? {};
            this.form.highschool = JSON.parse(this.form.highschool) ?? {};
            this.form.college = JSON.parse(this.form.college) ?? {};
            this.form.other_education = JSON.parse(this.form.other_education) ?? {};
            this.form.professional_reference_one = JSON.parse(this.form.professional_reference_one) ?? {};
            this.form.professional_reference_two = JSON.parse(this.form.professional_reference_two) ?? {};
            this.form.employment = JSON.parse(this.form.employment) ?? {};
            this.form.criminal_record = JSON.parse(this.form.criminal_record) ?? {};
            this.form.nurse_stat_info = JSON.parse(this.form.nurse_stat_info) ?? {};
            this.form.license_and_certifications = JSON.parse(this.form.license_and_certifications) ?? {};
            this.form.emergency_contact_one = JSON.parse(this.form.emergency_contact_one) ?? {};
            this.form.emergency_contact_two = JSON.parse(this.form.emergency_contact_two) ?? {};
            this.form.direct_deposit = JSON.parse(this.form.direct_deposit) ?? {};
            this.form.medical_history = JSON.parse(this.form.medical_history) ?? {};
            this.form.terms = JSON.parse(this.form.terms) ?? {};
            this.form.tb = JSON.parse(this.form.tb) ?? {};
            this.form.files = JSON.parse(this.form.files) ?? {};
        },

        data: () => ({
            page: 1,
            picker: '',
            dialog: '',
            member: null,
            dialogTwo: '',
            radioGroup: '',
            loginForm: {
                email: '',
                password: ''
            },
            registerForm: {
                email: '',
                password: ''
            },
            checkbox: true,
            approvedMessage: '',
            declinedMessage: '',
            form: {
                approved_at: null,
                declined_at: null,
                nurse: {
                    first_name: '',
                    middle_name: '',
                    last_name: '',
                    street_address: '',
                    street_address_two: '',
                    city: '',
                    state: '',
                    zip_code: '',
                    date_of_birth: '',
                    email: '',
                    phone_number: '',
                    position: '',
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
                    less_than_one_year_experience: '',
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
                    date: '',
                    applicant_first_name: '',
                    applicant_last_name: ''
                },

                medical_history: {
                    anemia: '',
                    smallpox: '',
                    diabetes: '',
                    diphtheria: '',
                    epilepsy: '',
                    heart_disease: '',
                    kidney_trouble: '',
                    mononucleosis: '',
                    scarlet_fever: '',
                    typhoid_fever: '',
                    hypertension: '',
                    latex_allergies: '',
                    hernia: '',
                    depression: '',
                    measles: '',
                    hepatitis: '',
                    mumps: '',
                    pleurisy: '',
                    pneumonia: '',
                    chicken_pox: '',
                    emphysema: '',
                    tuberculosis: '',
                    whooping_cough: '',
                    rheumatic_fever: '',
                    carpal_tunnel: '',
                    sight_hearing_problems: '',
                    including_colorblindness: '',
                    explanation: '',
                    routine_vaccinations_current: '',
                    hepatitis_a: '',
                    hepatitis_b: '',
                    covid: '',
                    covid_exemption: ''
                },

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

                files: []
            },

            tags: [
                "TB Skin Test",
                "Nursing License",
                "Background Check",
                "Drug Screen",
                "CPR Card",
                "BLS Card",
                "Drivers License",
                "Social Security Card",
                "Nursing License",
                "i9",
                "Covid Vaccine Card",
                "Covid Vaccine Exemption"
            ],

        }),

        methods: {
            onDecline () {
                let data = {
                    id: this.form.id,
                    email: this.form.nurse.email,
                    data: {
                        declined_at: this.getTodayDateTime()
                    }
                }

                modRequest.request('nurse.application.update', null, data, (res) => {
                    this.form.declined_at = data.data.declined_at
                    this.declinedMessage = 'This applicant has been successfully declined.'
                }, () => {
                    this.declinedMessage = 'Something went wrong declining applicant.'
                })
                modRequest.request('nurse.application.declined_applicant_email', null, data, () => {
                }, () => {
                    this.declinedMessage = 'Application declined email not sent.'
                })
            },

            onApprove () {

                const date = new Date;
                const id = this.form.id;

                let data = {
                    id: this.form.id,
                    email: this.form.nurse.email,
                    data: {
                        approved_at: date.getMonth() + 1 + '/' + date.getDate() + '/' + date.getFullYear() + ' ' + date.getHours() + ':' + date.getMinutes() + ':' + date.getSeconds()
                    },
                    form: this.form
                }

                modRequest.request('nurse.application.approve', null, data, (res) => {
                    this.form.approved_at = data.data.approved_at;
                    this.approvedMessage = 'This applicant has been approved! An email has been sent to ' + this.form.nurse.email + ' with further instructions.'

                }, (res) => {
                    console.log('Error: ', res);
                })
            },
            tagExists(tagName) {
                return this.tags.includes(tagName);
            },
            changeTag(file, tag) {
                if (file.tag !== tag) {
                    this.changes_exist = true;
                    this.show_warning = true;
                    file.tag = tag;
                }
            },
            createTag(file) {
                let newTag = {
                    id: 0,
                    name: this.new_tag_name,
                    description: this.new_tag_description
                };
                this.tags.push(newTag);
                file.tag = newTag;
                this.new_tag_name = '';
                this.new_tag_description = '';
                this.show_warning = true;
                this.changes_exist = true;
            },
            getTodayDateTime() {
                let today = new Date();
                const dd = String(today.getDate()).padStart(2, '0');
                const mm = String(today.getMonth() + 1).padStart(2, '0');
                const yyyy = today.getFullYear();
                const hr = String(today.getHours()).padStart(2, '0'); 
                const min = String(today.getMinutes()).padStart(2, '0');
                const sec = String(today.getSeconds()).padStart(2, '0');
                
                today = yyyy + '-' + mm + '-' + dd + ' ' + hr + ':' + min + ':' + sec;
                return today;
            },
        },
        watch: {
            file_id: function (oldId, newId) {
                var i9File = {
                    id: this.file_id,
                    name: this.name,
                    url: this.response.files.url,
                    file: this.response.files
                };
                console.log('watch')
                this.handleUploaded(i9File);
                // this.$emit('fileUploaded', data);
            }
        }
    })
})
