window.addEventListener('load', (number) => {
    Vue.component('nurse-app-form-step1', {
      template: /*html*/`
          <validation-observer
              ref="observer"
              v-slot="{ invalid }"
          >

              <h2 style="color: white; font-size: 24px; margin-bottom: 25px;">Personal Information</h2>
    
              <v-text-field
                v-model="page1Data.street_address"
                label="Street Address *"
                ref="street_address"
                outlined
                autocomplete="street-address"
              ></v-text-field>
  
              <v-text-field
                v-model="page1Data.street_address_line2"
                label="Apt, Suite, Unit, Building (optional)"
                ref="street_address_line2"
                outlined
                autocomplete="address-line2"
              ></v-text-field>

              <v-text-field
                v-model="page1Data.city"
                label="City *"
                ref="city"
                outlined
                autocomplete="address-level2"
              ></v-text-field>
    
              <v-select
                v-model="page1Data.state"
                :items="state_options"
                label="State *"
                ref="state"
                outlined
                autocomplete="address-level1"
              ></v-select>
  
              <v-text-field
                v-model="page1Data.zipcode"
                label="ZIP / Postal Code *"
                ref="zipcode"
                type="number"
                :rules="ziprules"
                maxlength="5"
                outlined
                autocomplete="postal-code"
              ></v-text-field>

              <v-text-field
                v-model="page1Data.dob"
                label="Date of Birth *"
                ref="dob"
                type="number"
                :rules="dobrules"
                outlined
                autocomplete="bday"
                hint="MMDDYYYY"
                maxlength="8"
              ></v-text-field>
  
              <v-text-field
                v-model="page1Data.phone"
                label="Phone Number *"
                ref="phone"
                type="number"
                :rules="phonerules"
                maxlength="10"
                outlined
                autocomplete="tel"
              ></v-text-field>
              
              <v-select
                outlined
                :items="['RN', 'LPN', 'CNA', 'CMA/KMA']"
                label="Please select the position you are applying for *"
                ref="position"
                key="nurse-position"
                v-model="page1Data.position"
              ></v-select>

              <div v-show="page1Data.position == 'Other'">
                <v-textarea
                  v-model="page1Data.explanation"
                  label="Position Explanation *"
                  ref="explanation"
                  hint="Please describe position that is being applied for"
                  outlined
                ></v-textarea>
              </div>

              <v-radio-group
                v-model="page1Data.citizen_of_the_us"
                label="Are you a citizen of the US? *"
                ref="citizen_of_the_us"
              >
                <v-radio
                  label="Yes"
                  value= "1"
                ></v-radio>
                <v-radio
                  label="No"
                  value= "0"
                ></v-radio>
              </v-radio-group>

              <div v-show="page1Data.citizen_of_the_us === '0'">
                <v-radio-group
                  v-model="page1Data.allowed_to_work"
                  label="Are you authorized to work in the US? *"
                  ref="allowed_to_work"
                >
                  <v-radio
                    label="Yes"
                    value= "1"
                  ></v-radio>
                  <v-radio
                    label="No"
                    value= "0"
                  ></v-radio>
                </v-radio-group>
              </div>

              <v-text-field
                v-show="false"
                v-model="page1Data.social_security_number"
                label="Social Security Number or EIN *"
                :rules="socsecrules"
                type="number"
                ref="social_security_number"
                outlined
                maxlength="9"
              ></v-text-field>

              <div
                v-show="false"
                style="display: flex; flex-direction: row; justify-content: space-between; align-items: baseline;"
              >
                <v-text-field
                  label="***-**-****"
                  outlined
                  hint="Social security number securely stored"
                  persistent-hint
                  maxlength="11"
                  readonly
                  disabled
                ></v-text-field>

                <v-btn
									v-show="false"
                  @click="page1Data.soc_sec_saved = false"
                  color="primary"
                  text
                  x-large
                >Replace</v-btn>
              </div>

              <div class="py-4"></div>
              <div style="display: flex; flex-direction: row; justify-content: space-between; align-items: center;">
                <button
                  class="btn btn-primary d-none"
                  @click="formAction(false)"
                >Save Progress</button>
                <div class="flex justify-end">
                  <button
                    class="btn btn-primary"
                    @click="formAction"
                  >Next</button>
                </div>
              </div>
          </validation-observer>`,
  
      watch: {

        page1Data: {

          handler() {

            // social security formatting
            // let socsec = this.page1Data.social_security_number.replace(/\D/g, '');
  
            // if (socsec.length > 3) {
            //   socsec = socsec.slice(0, 3) + '-' + socsec.slice(3);
            // }
            // if (socsec.length > 6) {
            //   socsec = socsec.slice(0, 6) + '-' + socsec.slice(6, 10);
            // }
  
            // this.page1Data.social_security_number = socsec;

            // phone formatting 
            // let phone = this.page1Data.phone.replace(/\D/g, '');

            // if (phone.length > 6) {
            //   phone = '(' + phone.slice(0, 3) + ') ' + phone.slice(3, 6) + '-' + phone.slice(6);
            // } else if (phone.length > 2) {
            //   phone = '(' + phone.slice(0, 3) + ') ' + phone.slice(3);
            // }

            // this.page1Data.phone = phone;
          },
          deep: true,
        },
      },
      computed: {
      },
      created() {},
      props: {
        page1Data: Object,
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
        phonerules: [ v => v.length <= 10 || 'Max 10 digits' ],
        dobrules: [ v => v.length <= 8 || 'Max 8 digits' ],
        socsecrules: [ v => v.length <= 9 || 'Max 9 digits' ],
      }),
      methods: {

        is18YearsOld(birthdate) {

          // const parts = birthdate.split('/');
          const parts = [
            birthdate.slice(0, 2),
            birthdate.slice(2, 4),
            birthdate.slice(4)
          ];
          const year = parts[2];
          const month = parts[0].padStart(2, '0');
          const day = parts[1].padStart(2, '0');
          let formattedBirthdate = `${year}-${month}-${day}`;

          const today = new Date();
          const minDate = new Date();
          minDate.setFullYear(today.getFullYear() - 18);
          const birthDate = new Date(formattedBirthdate);

          return birthDate <= minDate;
        },
        formatDateOfBirth() {
          
          let dob = this.page1Data.dob.replace(/\D/g, '').slice(0, 10);
          if (dob.length > 2) {
            dob = dob.slice(0, 2) + '/' + dob.slice(2);
          }
          if (dob.length > 5) {
            dob = dob.slice(0, 5) + '/' + dob.slice(5, 9);
          }
          this.page1Data.dob = dob;
        },
        formAction(validate = true) {
          
          let formValidation = {};
          if (validate) {
            formValidation = this.validateForm();
          }

          if (formValidation.valid) {

            this.formatSocSecNumber(this.page1Data.social_security_number);

            this.$emit('formAction', {

              page: 1,
              page1Data: this.page1Data,
              progressPage: true,
            });
          } else if (formValidation.message !== '') {
            this.showSnackbar(formValidation.message, 'error', 5000);
          }

          if (!validate) {
            this.$emit('formAction', {

              page: 1,
              page1Data: this.page1Data,
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

          let is18YearsOld = true;
          let correctDOBFormat = true;

          if (this.page1Data.dob === '' || typeof this.page1Data.dob !== 'string') {
            is18YearsOld = false;
          } else if (this.page1Data.dob.length != 8) {
            correctDOBFormat = false;
          } else {
            is18YearsOld = this.is18YearsOld(this.page1Data.dob);
          }

          if (this.page1Data.street_address === '' || this.page1Data.street_address === null) {
            this.$refs.street_address.focus();
            return {
              valid: false,
              message: 'Street address is required'
            }
          } else if (this.page1Data.city === '') {
            this.$refs.city.focus();
            return {
              valid: false,
              message: 'City is required'
            }
          } else if (this.page1Data.state === '') {
            this.$refs.state.focus();
            return {
              valid: false,
              message: 'State is required'
            }
          } else if (this.page1Data.zipcode === '') {
            this.$refs.zipcode.focus();
            return {
              valid: false,
              message: 'ZIP code is required'
            }
          } else if (this.page1Data.zipcode.length != 5) {
            this.$refs.zipcode.focus();
            return {
              valid: false,
              message: 'ZIP code must be 5 digits'
            }
          } else if (this.page1Data.dob === '') {
            this.$refs.dob.focus();
            return {
              valid: false,
              message: 'Date of birth is required'
            }
          } else if (!correctDOBFormat) {
            this.$refs.dob.focus();
            return {
              valid: false,
              message: 'Date of birth must be in the format MMDDYYYY'
            }
          } else if (!is18YearsOld) {
            this.$refs.dob.focus();
            return {
              valid: false,
              message: 'You must be at least 18 years old to apply'
            }
          } else if (this.page1Data.phone === '') {
            this.$refs.phone.focus();
            return {
              valid: false,
              message: 'Phone number is required'
            }
          } else if (this.page1Data.phone.length != 10) {
            this.$refs.phone.focus();
            return {
              valid: false,
              message: 'Phone number must be 10 digits'
            }
          } else if (this.page1Data.position === '') {
            this.$refs.position.focus();
            return {
              valid: false,
              message: 'Position is required'
            }
          }/* else if (this.page1Data.position === 'Other' && this.explanation === '') {
            return {
              valid: false,
              message: 'Position explanation is required'
            }
          }*/ else if (this.page1Data.citizen_of_the_us === '') {
            this.$refs.citizen_of_the_us.$el.focus();
            return {
              valid: false,
              message: 'Citizenship status is required'
            }
          } else if (this.page1Data.citizen_of_the_us === '0' && this.page1Data.allowed_to_work !== '1') {
            this.$refs.allowed_to_work.$el.focus();
            return {
              valid: false,
              message: 'Work authorization status is required'
            }
          } else if (this.page1Data.citizen_of_the_us === '0' && this.allowed_to_work === '0') {
            this.$refs.allowed_to_work.$el.focus();
            return {
              valid: false,
              message: 'You must be authorized to work in the US to apply'
            }
          } /* else if (this.page1Data.social_security_number === '' && this.page1Data.soc_sec_saved === false) {
            this.$refs.social_security_number.focus();
            return {
              valid: false,
              message: 'Social security number is required'
            }
          } else if (this.page1Data.social_security_number.length != 9 && this.page1Data.soc_sec_saved === false) {
            this.$refs.social_security_number.focus();
            return {
              valid: false,
              message: 'Social security number must be 9 digits'
            }
          } */ else {
            return {
              valid: true,
              message: ''
            }
          }
        },
        formatSocSecNumber(socSec) {

          // take nnnnnnnnn and format to nnn-nn-nnnn
          let formattedSocSec = socSec.replace(/\D/g, '');
          if (formattedSocSec.length > 3) {
            formattedSocSec = formattedSocSec.slice(0, 3) + '-' + formattedSocSec.slice(3);
          }
          if (formattedSocSec.length > 6) {
            formattedSocSec = formattedSocSec.slice(0, 6) + '-' + formattedSocSec.slice(6, 10);
          }
          this.page1Data.social_security_number = formattedSocSec;
        },
      },
    })
  })
  
