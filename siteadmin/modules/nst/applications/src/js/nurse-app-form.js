window.addEventListener('load', (number) => {
  Vue.use(VeeValidate, {
    mode: 'eager',
  })

  VeeValidate.extend('required', {
    validate(value) {
      return {
        required: true,
        valid: ['', null, undefined].indexOf(value) === -1,
      }
    },
    computesRequired: true,
    message: 'The {_field_} is required',
  })

  Vue.use(window.VueTheMask)
  Vue.component('ValidationProvider', VeeValidate.ValidationProvider)
  Vue.component('ValidationObserver', VeeValidate.ValidationObserver)

  VeeValidate.extend('minimum_date', {
    validate: (date_of_birth) => {
      let date = new Date()
      currentYear = date.getFullYear()
      // 0: month 1: day 2: year
      const DOBarray = date_of_birth.split('/')
      if (currentYear - parseInt(DOBarray[2]) > 18) {
        return true
      } else if (currentYear - parseInt(DOBarray[2]) == 18) {
        currentMonth = date.getMonth() + 1
        if (currentMonth > parseInt(DOBarray[0])) {
          return true
        } else if (currentMonth == parseInt(DOBarray[0])) {
          currentDay = date.getDate()
          if (currentDay >= parseInt(DOBarray[1])) {
            return true
          }
        }
      }
      return false
    },
    message: 'Applicants must be 18 years of age or older',
  })

  Vue.component('nurse-app-form', {
    // language=HTML
    template: /*html*/`
  <div class="nurse-app h-100">
    <div class="container h-100" data-app>
      <div class="row justify-content-center h-100 align-items-center">
        <div class="nurse-app-form h-100 col-md-6">
          <div class="nurse-app-content">
            <div class="row no-gutters">
              <div class="col-xl-12">
              
                <v-overlay :absolute="true" :value="loading">

                  <v-progress-circular
                      indeterminate
                      size="64"
                  ></v-progress-circular>
                </v-overlay>


                <div style="display: flex; flex-direction: row; justify-content: space-between; align-items: flex-end;">

                  <v-overlay v-show="existing_user.show">
                    <v-card
                      dark
                      width="500"
                    >
                      <div class="login-header" style="background-color: #E53935; height: 60px; padding: 15px;">
                        <h2 style="color: white; font-size: 18px;">Login</h2>
                      </div>
                      <v-card-text style="margin-top: 20px;">
                        <v-form>
                          <v-text-field
                            v-model="existing_user.username"
                            label="Email Address / Username"
                            outlined
                            autocomplete="username"
                          ></v-text-field>

                          <v-text-field
                            v-model="existing_user.password"
                            label="Password"
                            outlined                            
                            type="password"
                            autocomplete="current-password"
                          ></v-text-field>

                          <div style="display: flex; flex-direction: row; justify-content: flex-end; align-items: flex-end; margin-bottom: 25px;">
                            <v-btn
                              color="#C62828"
                              @click="existing_user.show = false"
                            >Cancel</v-btn>

                            <v-btn
                              color="#C62828"
                              style="margin-left: 15px;"
                              @click="existingUserLogin"
                            >Login</v-btn>
                          </div>
                        </v-form>
                      </v-card-text>
                    </v-card>
                  </v-overlay>
                
                  <div class="text-center py-6">
                    <a href="/member/login"><img class="img-fluid" src="/themes/nst/assets/images/white-logo.png" alt=""></a>
                  </div>
          
                  <div style="margin: 0 20px 20px 0;" v-show="!logged_in">
                    <v-btn
                      @click="existing_user.show = true"
                      x-large
                      color="#C62828"
                    >Login</v-btn>
                  </div>

                  <div
                    v-show="logged_in"
                    style="width: 100%; height: 100%; display: flex; flex-direction: row; justify-content: flex-end; align-items: baseline;"
                  >
                    <p style="color: white; font-size: 20px; margin-right: 20px;">Welcome, {{ portal.login.first_name }} {{ portal.login.last_name }}</p>

                    <v-btn
                      @click="logoutUser"
                      medium
                      color="#757575"
                    >Logout</v-btn>
                  </div>

                </div>

                <ul class="steps min-w-full py-3" style="margin-bottom: 30px;">
                    <li v-for="(stepText,index) in portal.steps" class="step" :class="index<=portal.step ? 'step-primary' : ''">
                        {{stepText}}
                    </li>
                </ul>
                  
                <div style="display: flex; flex-direction: row; justify-content: space-between; align-items: center; margin-bottom: 25px;">
            
                  <h3 style="color: white; font-size: 18px;">Create Application Login</h3>
        
                  <v-btn :disabled="portal.login.completed" @click="portal.login.show = !portal.login.show">

                    <span v-show="portal.login.completed">Completed</span>
                    <span v-show="!portal.login.completed">Incomplete</span>

                    <v-icon v-show="portal.login.show">mdi-menu-up</v-icon>
                    <v-icon v-show="!portal.login.show">mdi-menu-down</v-icon>

                  </v-btn>

                </div>

                <div v-show="portal.login.show" style="margin-bottom: 25px;">                  
                  <create-login
                    @login-user="existingUserLogin"

                    :message="snackbarMessage"
                    :color="snackbarColor"
                    :timeout="snackbarTimeout"
                    @show-snackbar="showSnackbar"
                  ></create-login>
                </div>
                  
                <div style="display: flex; flex-direction: row; justify-content: space-between; align-items: center; margin-bottom: 25px;">
            
                  <h3 style="color: white; font-size: 18px;">Application</h3>
        
                  <v-btn :disabled="portal.application.completed || !logged_in" @click="portal.application.show = !portal.application.show">

                    <span v-show="portal.application.completed">Completed</span>
                    <span v-show="!portal.application.completed">Incomplete</span>

                    <v-icon v-show="portal.application.show">mdi-menu-up</v-icon>
                    <v-icon v-show="!portal.application.show">mdi-menu-down</v-icon>

                  </v-btn>

                </div>

                <div v-show="portal.application.show" style="margin-bottom: 30px;">

                  <ul class="steps min-w-full py-3">
                      <li v-for="(stepText,index) in application.steps" class="step" :class="index<=application.step ? 'step-primary' : ''">
                          {{stepText}}
                      </li>
                  </ul>
                  <form @submit.prevent class="min-w-full px-6 pt-6">
                  <!-- !!!!!!! work on this method to change step disabled back and forth, potentially don't disable and instead validate or don't !!!!!!!! -->
                      <!-- <component
                        :is="application.forms[application.step]"
                        @formAction="formAction"
                        @showSnackbar="showSnackbar"
                        @backApplicationStep="backApplicationStep"
                      ></component> -->
                      <nurse-app-form-step1
                        v-show="application.step === 0"
                        @formAction="formAction"
                        @showSnackbar="showSnackbar"
                        @backApplicationStep="backApplicationStep"
                        :page1Data="portal.application.page1Data"
                      ></nurse-app-form-step1>

                      <nurse-app-form-step2
                        v-show="application.step === 1"
                        @formAction="formAction"
                        @showSnackbar="showSnackbar"
                        @backApplicationStep="backApplicationStep"
                        :page2Data="portal.application.page2Data"
                      ></nurse-app-form-step2>

                      <nurse-app-form-step3
                        v-show="application.step === 2"
                        @formAction="formAction"
                        @showSnackbar="showSnackbar"
                        @backApplicationStep="backApplicationStep"
                        :page3Data="portal.application.page3Data"
                      ></nurse-app-form-step3>

                      <nurse-app-form-step4
                        v-show="application.step === 3"
                        @formAction="formAction"
                        @showSnackbar="showSnackbar"
                        @backApplicationStep="backApplicationStep"
                        :page4Data="portal.application.page4Data"
                      ></nurse-app-form-step4>

                      <nurse-app-form-step5
                        v-show="application.step === 4"
                        @formAction="formAction"
                        @showSnackbar="showSnackbar"
                        @backApplicationStep="backApplicationStep"
                        :page5Data="portal.application.page5Data"
                      ></nurse-app-form-step5>

                      <nurse-app-form-step6
                        v-show="application.step === 5"
                        @formAction="formAction"
                        @showSnackbar="showSnackbar"
                        @backApplicationStep="backApplicationStep"
                        :page6Data="portal.application.page6Data"
                      ></nurse-app-form-step6>

                      <nurse-app-form-step7
                        v-show="application.step === 6"
                        @formAction="formAction"
                        @showSnackbar="showSnackbar"
                        @backApplicationStep="backApplicationStep"
                        :page7Data="portal.application.page7Data"
                      ></nurse-app-form-step7>
                      <!--<div class="py-4"></div>
                      <div class="flex justify-end"> -->
                        <!-- <button class="btn btn-ghost" type="button" v-if="application.step !== 0" @click="application.step--">Back</button> -->
                        <!-- <button
                          class="btn btn-primary"
                          type="submit"
                          v-if="application.step !== application.steps.length - 1"
                          @click="formAction"
                        >Next</button> -->
                        <!-- additional conditional disabled button that doesn't look like shit -->
                        <!-- <button class="btn btn-primary" type="submit" v-if="application.step === application.steps.length - 1" @click="formAction">Submit</button>
                      </div> -->
                  </form>

                </div>
                  
                <div style="display: flex; flex-direction: row; justify-content: space-between; align-items: center; margin-bottom: 25px;">
            
                  <h3 style="color: white; font-size: 18px;">Upload Documents</h3>
        
                  <v-btn :disabled="!portal.application.completed || !logged_in" @click="showStep(2)">

                    <span v-show="portal.documents.completed">Completed</span>
                    <span v-show="!portal.documents.completed">Incomplete</span>

                    <v-icon v-show="portal.documents.show">mdi-menu-up</v-icon>
                    <v-icon v-show="!portal.documents.show">mdi-menu-down</v-icon>

                  </v-btn>

                </div>

                <div v-show="portal.documents.show" style="margin-bottom: 25px;">

                  <ul class="steps min-w-full py-3">
                    <li v-for="(stepText,index) in portal.documents.uploaded_files.steps" class="step" :class="index<=portal.documents.uploaded_files.step ? 'step-primary' : ''">
                      {{stepText}}
                    </li>
                  </ul>

                  <div class="flex row align-center justify-between" style="margin: 20px 0;">
                      
                    <div style="max-width: 85%;">
                      <p style="margin-bottom: 0;"><strong>Prefer to complete this section on your phone?</strong></p>
                      <p style="margin-bottom: 0;">Click the button to the right to recieve an sms with an invitation to the</p>
                      <p style="margin-bottom: 0;">upload portal on mobile. When you are finished, click the refresh button to</p>
                      <p style="margin-bottom: 0;">load your progress.</p>
                    </div>
            
                    <v-btn @click="smsMobilePortal" color="#B71C1C">
                      <v-icon>mdi-message-text-outline</v-icon>
                      &nbspSend Invite
                    </v-btn>
                  </div>

                  <div class="flex row align-center justify-between" style="margin: 20px 0;">
                      
                  <div style="max-width: 85%;">
                    <p style="margin-bottom: 0;"><strong>Finished uploading documents on your phone?</strong></p>
                    <p style="margin-bottom: 0;">Click the button to the right to continue application with uploaded files.</p>
                  </div>
            
                    <v-btn @click="loadFilesProgress(portal.application.application_id)" color="#B71C1C">
                      <v-icon>mdi-chevron-right</v-icon>
                      &nbspRefresh & Continue
                    </v-btn>
                  </div>

                  <upload-documents-view
                    @showSnackbar="showSnackbar"
                    @saveFilesProgress="saveFilesProgress"
                    :uploaded_files="portal.documents.uploaded_files"
                    :application_id="portal.application.application_id"
                  ></upload-documents-view>
                </div>
                  
                <div style="display: flex; flex-direction: row; justify-content: space-between; align-items: center; margin-bottom: 25px;">
            
                  <h3 style="color: white; font-size: 18px;">Drug Screening</h3>
        
                  <v-btn :disabled="!portal.drug_screen.completed || !logged_in" @click="portal.drug_screen.show = !portal.drug_screen.show">

                    <span v-show="portal.drug_screen.completed">Completed</span>
                    <span v-show="!portal.drug_screen.completed">Incomplete</span>

                    <v-icon v-show="portal.drug_screen.show">mdi-menu-up</v-icon>
                    <v-icon v-show="!portal.drug_screen.show">mdi-menu-down</v-icon>

                  </v-btn>

                </div>

                <div v-show="portal.drug_screen.show" style="margin-bottom: 25px;">                

                  <drug-screen
                    @showSnackbar="showSnackbar"
                    :application_id="portal.application.application_id"
                    :drug_screen="portal.drug_screen"
                  ></drug-screen>

                </div>
                  
                <div style="display: flex; flex-direction: row; justify-content: space-between; align-items: center; margin-bottom: 25px;">
            
                  <h3 style="color: white; font-size: 18px;">Background Check</h3>
        
                  <v-btn :disabled="!portal.drug_screen.completed || !logged_in" @click="portal.background_check.show = !portal.background_check.show">

                    <span v-show="portal.background_check.completed">Completed</span>
                    <span v-show="!portal.background_check.completed">Incomplete</span>

                    <v-icon v-show="portal.background_check.show">mdi-menu-up</v-icon>
                    <v-icon v-show="!portal.background_check.show">mdi-menu-down</v-icon>

                  </v-btn>

                </div>

                <div v-show="portal.background_check.show" style="margin-bottom: 25px;">                

                  <background-check
                    @showSnackbar="showSnackbar"
                    :application_id="portal.application.application_id"
                    :background_check="portal.background_check"
                  ></background-check>

                </div>

                <v-snackbar
                  v-model="snackbar"
                  :color="snackbarColor"
                  :timeout="snackbarTimeout"
                  top
                >
                  {{ snackbarMessage }}
                </v-snackbar>

              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>`,

    watch: {
      page() {
        document.body.scrollTop = document.documentElement.scrollTop = 0
      },
    },
    computed: {
    },
    created() {
      
      this.$vuetify.theme.dark = true
      this.checkApplicationSession();
    },
    // props: {
    //   submitAction: {
    //     type: Function,
    //     required: true,
    //   },
    // },

    data: () => ({

      portal: {

        login: {

          show: false,
          completed: false,

          first_name: '',
          middle_name: '',
          last_name: '',
          username: '',
          password: '',
        },
        application: {

          show: false,
          completed: false,
          application_id: 0,

          page1Data: {
            
            street_address: '',
            street_address_line2: '',
            city: '',
            state: '',
            zipcode: '',
            dob: '',
            phone: '',
            position: '',
            explanation: '',
            citizen_of_the_us: '',
            allowed_to_work: '',
            social_security_number: '000000000',
            soc_sec_saved: true,
          },
          page2Data: {
            
            one_year_ltc_experience: '',
            one_year_explanation: '',
            currently_employed: '',

            company1: {
                
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
            },
            company2: {
                
              show: false,
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
            },
            company3: {
                
              show: false,
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
            },
          },
          page3Data: {

            hs_or_ged: '',
            high_school: {
        
              show: true,
              name: '',
              city: '',
              state: '',
              year_graduated: '',
            },
            ged: {
        
              show: true,
              name: '',
              city: '',
              state: '',
              year_graduated: '',
            },
            college: {
        
              show: true,
              name: '',
              year_graduated: '',
              city: '',
              state: '',
              subjects_major_degree: '',
            },
            other: {
        
              show: false,
              name: '',
              year_graduated: '',
              city: '',
              state: '',
              subjects_major_degree: '',
            },
          },
          page4Data: {
    
            reference1: {
    
              show: true,
              name: '',
              relationship: '',
              phone_number: '',
              company: '',
            },
            reference2: {
    
              show: false,
              name: '',
              relationship: '',
              phone_number: '',
              company: '',
            },
            reference3: {
    
              show: false,
              name: '',
              relationship: '',
              phone_number: '',
              company: '',
            },
            license_and_certifications: {
                
              rn_long_term_care: false,
              rn_hospital: false,
              rn_home_health: false,
              rn_hospice: false,
              rn_homecare_sitter: false,
      
              lpn_long_term_care: false,
              lpn_hospital: false,
              lpn_home_health: false,
              lpn_hospice: false,
              lpn_homecare_sitter: false,
      
              ckc_long_term_care: false,
              ckc_hospital: false,
              ckc_home_health: false,
              ckc_hospice: false,
              ckc_homecare_sitter: false,
      
              cna_long_term_care: false,
              cna_hospital: false,
              cna_home_health: false,
              cna_hospice: false,
              cna_homecare_sitter: false,
      
              sitter_long_term_care: false,
              sitter_hospital: false,
              sitter_home_health: false,
              sitter_hospice: false,
              sitter_homecare_sitter: false,
            },
          },
          page5Data: {

            signature: '',
            ip: '',
            timestamp: '',
          },
          page6Data: {

            medical_history_show: true,
            injury_history_show: true,
            vaccination_history_show: true,
            tuberculosis_screening_show: true,

            medical_history: {

              anemia: 'no',
              smallpox: 'no',
              diabetes: 'no',
              diptheria: 'no',
              epilepsy: 'no',
              heart_disease: 'no',
              kidney_trouble: 'no',
              mononucleosis: 'no',
              scarlet_fever: 'no',
              typhoid: 'no',
              hypertension: 'no',
              latex_allergy: 'no',
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
              whopping_cough: 'no',
              rheumatic_fever: 'no',
              carpal_tunnel: 'no',
              sight_hearing_problems: 'no',
              color_blindness: 'no',
            },

            injury_explanation: '',

            routine_vaccinations: 'no',
            hepatitis_b: 'no',
            hepatitis_a: 'no',
            covid_19: 'no',
            covid_19_exemption: 'no',

            positive_tb_screening: 'no',
            positive_tb_date: '',
            xray: 'no',
            xray_date: '',

            signature: '',
          },
          page7Data: {

            pay_type: '',
            account_type: '',
            account_number: '',
            routing_number: '',
            bank_name: '',
        
            heard_about_us: '',
            heard_about_us_other: '',
            referrer: '',
            
            license1: {
                
              state: '',
              license_number: '',
              full_name: '',
            },
            license2: {
                
              show: false,
              state: '',
              license_number: '',
              full_name: '',
            },
            license3: {
                
              show: false,
              state: '',
              license_number: '',
              full_name: '',
            },
          },
        },
        documents: {

          show: false,
          completed: false,
          
          uploaded_files: {
            
            step: 0,
            steps: [
              '','','',
            ],

            nursing_license_1: {

              state: '',
              license_number: '',
              full_name: '',

              url: '',
              name: '',
              fileTag: 'Nurse License 1',
            },
            nursing_license_2: {

              show: false,
              state: '',
              license_number: '',
              full_name: '',

              url: '',
              name: '',
              fileTag: 'Nurse License 2',
            },
            nursing_license_3: {

              show: false,
              state: '',
              license_number: '',
              full_name: '',

              url: '',
              name: '',
              fileTag: 'Nurse License 3',
            },

            drivers_license: {},
            social_security: {},
            tb_skin_test: {},

            cpr_card: {},
            bls_acl_card: {},
            covid_vaccine_card: {},
            
            id_badge_picture: {},
          },
        },
        drug_screen: {

          show: false,
          completed: false,

          status: '',
        },
        background_check: {

          show: false,
          completed: false,

          status: '',
        },
        steps: [
          '', '', '', '', ''
        ],
        step: 0,
      },
      application: {

        forms: [

          'nurse-app-form-step1',
          'nurse-app-form-step2',
          'nurse-app-form-step3',
          'nurse-app-form-step4',
          'nurse-app-form-step5',
          'nurse-app-form-step6',
          'nurse-app-form-step7',
        ],
        steps: [

          '', '', '', '', '', '', ''
        ],
        step: 0,
        step_disabled: true,
      },
      logged_in: false,
      loading: false,
      snackbar: false,
      snackbarMessage: '',
      snackbarColor: '',
      snackbarTimeout: -1, 
      existing_user: {

        show: false,
        username: '',
        password: '',
      },
      smsDisabled: false,
    }),

    methods: {
      
      formAction(form) {
        
        if (form.page === 1) {

          this.page1Data = form;
          let successfulSave = this.saveApplicationProgress(1, form.progressPage);
          
          if (successfulSave) {
            if (form.progressPage) {
              this.application.step++;
            } else { this.showSnackbar({ message: 'Progress saved!', color: 'success', timeout: 3000 }); }
          } else { this.showSnackbar({ message: 'Error saving application progress. Please try again or contact Nursestat', color: 'red', timeout: 3000 }); }
        }
        else if (form.page === 2) {
          
          this.page2Data = form;
          let successfulSave = this.saveApplicationProgress(2, form.progressPage);

          if (successfulSave) {
            if (form.progressPage) {
              this.application.step++;
            } else { this.showSnackbar({ message: 'Progress saved!', color: 'success', timeout: 3000 }); }
          } else { this.showSnackbar({ message: 'Error saving application progress. Please try again or contact Nursestat', color: 'red', timeout: 3000 }); }
        }
        else if (form.page === 3) {
          
          this.page3Data = form;
          let successfulSave = this.saveApplicationProgress(3, form.progressPage);

          if (successfulSave) {
            if (form.progressPage) {
              this.application.step++;
            } else { this.showSnackbar({ message: 'Progress saved!', color: 'success', timeout: 3000 }); }
          } else { this.showSnackbar({ message: 'Error saving application progress. Please try again or contact Nursestat', color: 'red', timeout: 3000 }); }
        }
        else if (form.page === 4) {
          
          this.page4Data = form;
          let successfulSave = this.saveApplicationProgress(4, form.progressPage);

          if (successfulSave) {
            if (form.progressPage) {
              this.application.step++;
            } else { this.showSnackbar({ message: 'Progress saved!', color: 'success', timeout: 3000 }); }
          } else { this.showSnackbar({ message: 'Error saving application progress. Please try again or contact Nursestat', color: 'red', timeout: 3000 }); }
        }
        else if (form.page === 5) {
          
          this.page5Data = form;
          let successfulSave = this.saveApplicationProgress(5, form.progressPage);

          if (successfulSave) {
            if (form.progressPage) {
              this.application.step++;
            } else { this.showSnackbar({ message: 'Progress saved!', color: 'success', timeout: 3000 }); }
          } else { this.showSnackbar({ message: 'Error saving application progress. Please try again or contact Nursestat', color: 'red', timeout: 3000 }); }
        }
        else if (form.page === 6) {

          this.page6Data = form;
          let successfulSave = this.saveApplicationProgress(6, form.progressPage);

          if (successfulSave) {
            if (form.progressPage) {
              this.application.step++;
            } else { this.showSnackbar({ message: 'Progress saved!', color: 'success', timeout: 3000 }); }
          } else { this.showSnackbar({ message: 'Error saving application progress. Please try again or contact Nursestat', color: 'red', timeout: 3000 }); }
        }
        else if (form.page === 7) {
          
          this.page7Data = form;
          let successfulSave = this.saveApplicationProgress(7, form.progressPage);
          
          if (successfulSave) {

            if (form.progressPage) {

              this.portal.application.show = false;
              this.portal.application.completed = true;
              this.portal.documents.show = true;
              this.portal.step = 2;
              this.showSnackbar({ message: 'Application successfully submitted!', color: 'success', timeout: 5000 });
            } else { this.showSnackbar({ message: 'Progress saved!', color: 'success', timeout: 3000 }); }
          } else { this.showSnackbar({ message: 'Error saving application progress. Please try again or contact Nursestat', color: 'red', timeout: 3000 }); }
        }
      },
      backApplicationStep() {
        this.application.step--;
      },
      saveApplicationProgress(pageNum, progressPage) {

        switch (pageNum) {

          case 1:

            data = {

              application_id: this.portal.application.application_id,
              pageNum: pageNum,
              page1Data: this.portal.application.page1Data,
            };
            break;
          case 2:

            data = {

              application_id: this.portal.application.application_id,
              pageNum: pageNum,
              page2Data: this.portal.application.page2Data,
            };
            break;
          case 3:

            data = {

              application_id: this.portal.application.application_id,
              pageNum: pageNum,
              page3Data: this.portal.application.page3Data,
            };
            break;
          case 4:

            data = {

              application_id: this.portal.application.application_id,
              pageNum: pageNum,
              page4Data: this.portal.application.page4Data,
            };
            break;
          case 5:

            data = {

              application_id: this.portal.application.application_id,
              pageNum: pageNum,
              page5Data: this.portal.application.page5Data,
            };
            break;
          case 6:

            data = {

              application_id: this.portal.application.application_id,
              pageNum: pageNum,
              page6Data: this.portal.application.page6Data,
            };
            break;
          case 7:

            data = {

              application_id: this.portal.application.application_id,
              pageNum: pageNum,
              page7Data: this.portal.application.page7Data,
            };
            break;
        }

        data.progressPage = progressPage;
        
        let successfulSave = true;
        modRequest.request('nurse.application.saveApplicationProgress', null, data, function(response) {
          if (!response.success) {
            successfulSave = false;
          }
        }.bind(this));

        return successfulSave;
      },
      showSnackbar(snackbarInfo) {

        this.snackbarMessage = snackbarInfo.message
        this.snackbarColor = snackbarInfo.color
        this.snackbarTimeout = snackbarInfo.timeout
        this.snackbar = true
      },
      existingUserLogin(form) {

        if (form.username && form.password) {

          data = {

            username: form.username,
            password: form.password,
          }
        } else {

          data = {
          
            username: this.existing_user.username,
            password: this.existing_user.password,
          }
        }
        
        modRequest.request('nurse.application.loginApplicant', null, data, function(response) {
          if (response.success) {

            if (response.message) {
    
              this.showSnackbar({
      
                message: response.message,
                color: 'red',
                timeout: 3000,
              });
            } else {
              // set first + last
              this.portal.login.first_name = response.login.first_name;
              this.portal.login.last_name = response.login.last_name;

              this.portal.application.application_id = response.login.application_id;
              this.loadApplicationProgress(response.login.application_id);
      
              this.showSnackbar({
      
                message: 'Login Successful',
                color: 'success',
                timeout: 3000,
              });
            }
          }
        }.bind(this));
      },
      checkApplicationSession() {
        
        let cookies = document.cookie.split(';');
        let cookie = cookies.find(cookie => cookie.includes('rememberme_applicant'));

        if (cookie) {
          modRequest.request('nurse.application.checkSession', null, {}, function(response) {
            if (response.success) {
              if (!response.message) {

                this.portal.login.first_name = response.login.first_name;
                this.portal.login.last_name = response.login.last_name;

                this.portal.application.application_id = response.login.application_id;
                this.loadApplicationProgress(response.login.application_id);
              } else {

                this.showSnackbar({

                  message: response.message,
                  color: 'red',
                  timeout: 3000,
                });
              }
            }
          }.bind(this));
        }
      },
      logoutUser() {
        
        modRequest.request('nurse.application.logoutApplicant', null, {}, function(response) {
          if (response.success) {            
            location.reload();        
            this.loading = false;
          }
        }.bind(this));
      },
      loadApplicationProgress(application_id) {

        this.portal.login.show = false;
        this.portal.login.completed = true;
        this.logged_in = true;
        this.existing_user.show = false;
        this.portal.step = 1;

        this.loading = true;

        data = {
          application_id: application_id,
        }
        
        modRequest.request('nurse.application.loadApplicationProgress', null, data, function(response) {
          if (response.success) {

            this.portal.step = 1;
            this.application.step = response.app_step;
            this.portal.application = response.application;

            if (response.application.completed) {

              this.portal.application.show = false;
              this.portal.application.completed = true;
              this.portal.documents.show = true;
              this.portal.step = 2;
              this.loadFilesProgress(application_id);
            } else {

              this.loading = false;
            }
          }
        }.bind(this));
      },
      loadFilesProgress(application_id) {

        data = {
          application_id: application_id,
        }
        
        modRequest.request('nurse.application.loadFilesProgress', null, data, function(response) {
          if (response.success) {

            this.portal.documents = response;
            if (this.portal.documents.completed) {

              this.portal.drug_screen.show = true;
              this.portal.step = 3;
              this.loadDrugScreenProgress(application_id);

            } else {

              this.portal.documents.show = true;
              this.loading = false;
            }
          } else if (response.message) {

            this.showSnackbar({

              message: response.message,
              color: 'red',
              timeout: 3000,
            });
          }
        }.bind(this));
      },
      saveFilesProgress(form) {

        data = {

          application_id: this.portal.application.application_id,
          files: form.uploaded_files,
        }

        modRequest.request('nurse.application.saveFilesProgress', null, data, function(response) {
          if (response.success) {
            if (data.files.step === 3) {

              this.portal.documents.show = false;
              this.portal.documents.completed = true;
              this.portal.drug_screen.show = true;
              this.portal.step = 3;
              this.showSnackbar({ message: 'Files successfully submitted!', color: 'success', timeout: 5000 });
            }
          }
        }.bind(this));
      },
      smsMobilePortal() {

        if (this.smsDisabled) { return; }
  
        data = {
          application_id: this.portal.application.application_id,
        }
          
        modRequest.request('nurse.application.sendMobileFileUpload', null, data, function(response) {
          if (response.success) {

            // show snackbar
            this.showSnackbar({

              message: 'SMS sent to your phone with a link to the mobile upload portal.',
              color: 'success',
              timeout: 5000
            });

            // disable second sms request for 5 minutes
            this.smsDisabled = true;
            setTimeout(function() { this.smsDisabled = false; }.bind(this), 300000);
          } else if (response.message) {

            this.showSnackbar({

              message: response.message,
              color: 'red',
              timeout: 6000,
            });
          }
        }.bind(this));
      },
      loadDrugScreenProgress(application_id) {
  
        data = {
          application_id: application_id,
        }
        
        modRequest.request('nurse.application.loadDrugScreenProgress', null, data, function(response) {
  
          if (response.success) {
  
            this.portal.drug_screen = response;
            if (this.portal.drug_screen.completed) {
  
              this.portal.background_check.show = true;
              this.portal.step = 4;
              this.loadBackgroundCheckProgress(application_id);
  
            } else {

              this.portal.drug_screen.show = true;
              this.loading = false;
            }
          } else if (response.message) {
  
            this.showSnackbar({
  
              message: response.message,
              color: 'red',
              timeout: 3000,
            });
          }
        }.bind(this));
      },
      loadBackgroundCheckProgress(application_id) {

        data = {
          application_id: application_id,
        }
        
        modRequest.request('nurse.application.loadBackgroundCheckProgress', null, data, function(response) {
          if (response.success) {
                        
            this.portal.background_check = response;
            this.loading = false;
          }
        }.bind(this));
      },
      showStep(step) {

        this.portal.step = step;

        if (step === 1) {

          // this.portal.application.show = true;
          // this.portal.documents.show = false;
          // this.portal.drug_screen.show = false;
          // this.portal.background_check.show = false;

        } else if (step === 2) {

          this.portal.application.show = false;
          this.portal.documents.show = true;

          if (this.portal.documents.completed) {

            this.portal.documents.uploaded_files.step = 0;
            this.portal.drug_screen.show = false;
            this.portal.background_check.show = false;
          }

        } else if (step === 3) {

          // this.portal.application.show = false;
          // this.portal.documents.show = false;
          // this.portal.drug_screen.show = true;
          // this.portal.background_check.show = false;

        } else if (step === 4) {

          // this.portal.application.show = false;
          // this.portal.documents.show = false;
          // this.portal.drug_screen.show = false;
          // this.portal.background_check.show = true;

        } else if (step === 5) {

          // this.portal.application.show = false;
          // this.portal.documents.show = false;
          // this.portal.drug_screen.show = false;
          // this.portal.background_check.show = false;

        } else if (step === 6) {

          // this.portal.application.show = false;
          // this.portal.documents.show = false;
          // this.portal.drug_screen.show = false;
          // this.portal.background_check.show = false;
        }
      },
    },
  })
})
