window.addEventListener('load', (number) => {
Vue.component('nurse-app-form-step3', {
    template: /*html*/`
    <validation-observer
        ref="observer"
        v-slot="{ invalid }"
    >

    <h2 style="color: white; font-size: 24px; margin-bottom: 25px;">Education History</h2>

    <v-radio-group  
        v-model="page3Data.hs_or_ged"
        label="Did you graduate from High School or obtain a GED?"
    >
        <v-radio
            label="High School"
            name="hs_or_ged"
            :value="'high_school'"
        ></v-radio>

        <v-radio
            label="GED"
            name="hs_or_ged"
            :value="'ged'"
        ></v-radio>
    </v-radio-group>
    
    <div v-show="page3Data.hs_or_ged == 'high_school'">

        <div style="display: flex; flex-direction: row; justify-content: space-between; align-items: center; margin-bottom: 25px;">

            <h3 style="color: white; font-size: 20px;">High School Information</h3>

            <div>

                <v-btn
                    @click="page3Data.high_school.show = !page3Data.high_school.show"
                    style="transition: opacity 0.5s;"
                >
                    {{ page3Data.high_school.show ? 'Hide High School Info' : 'Show High School Info' }}
                </v-btn>

                <v-btn @click="removeInfo('high_school')">Remove Info</v-btn>

            </div>

        </div>

        <div v-show="page3Data.high_school.show">
        
            <v-text-field
                v-model="page3Data.high_school.name"
                label="High School Name *"
                outlined
            ></v-text-field>
        
            <v-text-field
                v-model="page3Data.high_school.year_graduated"
                label="Year Graduated *"
                maxlength="4"
                outlined
            ></v-text-field>
        
            <v-text-field
                v-model="page3Data.high_school.city"
                label="High School City *"
                outlined
            ></v-text-field>
     
            <v-select
                v-model="page3Data.high_school.state"
                :items="state_options"
                label="State *"
                outlined
            ></v-select>
        </div>
    </div>

    <div v-show="page3Data.hs_or_ged == 'ged'">

        <div style="display: flex; flex-direction: row; justify-content: space-between; align-items: center; margin-bottom: 25px;">

            <h3 style="color: white; font-size: 20px;">GED Information</h3>

            <div>

                <v-btn
                    @click="page3Data.ged.show = !page3Data.ged.show"
                    style="transition: opacity 0.5s;"
                >
                    {{ page3Data.ged.show ? 'Hide GED Info' : 'Show GED Info' }}
                </v-btn>

                <v-btn @click="removeInfo('ged')">Remove Info</v-btn>

            </div>

        </div>

        <div v-show="page3Data.ged.show">
        
            <v-text-field
                v-model="page3Data.ged.name"
                label="School Name *"
                outlined
            ></v-text-field>
        
            <v-text-field
                v-model="page3Data.ged.year_graduated"
                label="Year Graduated *"
                maxlength="4"
                outlined
            ></v-text-field>
        
            <v-text-field
                v-model="page3Data.ged.city"
                label="School City *"
                outlined
            ></v-text-field>
     
            <v-select
                v-model="page3Data.ged.state"
                :items="state_options"
                label="State *"
                outlined
            ></v-select>
                
        </div>
    </div>

    <div style="display: flex; flex-direction: row; justify-content: space-between; align-items: center; margin-bottom: 25px;">
        <h3 style="color: white; font-size: 20px;">University / College / Tradeschool</h3>

        <div>

            <v-btn
                @click="page3Data.college.show = !page3Data.college.show"
                style="transition: opacity 0.5s;"
            >
                {{ page3Data.college.show ? 'Hide College Info' : 'Show College Info' }}
            </v-btn>

            <v-btn @click="removeInfo('college')">Remove Info</v-btn>

        </div>

    </div>

    <div v-show="page3Data.college.show">

        <v-text-field
            v-model="page3Data.college.name"
            label="School Name"
            outlined
        ></v-text-field>
        
        <v-text-field
            v-model="page3Data.college.year_graduated"
            label="Year Graduated"
            maxlength="4"
            outlined
        ></v-text-field>
        
        <v-text-field
            v-model="page3Data.college.city"
            label="School City"
            outlined
        ></v-text-field>

        <v-select
            v-model="page3Data.college.state"
            :items="state_options"
            label="State"
            outlined
        ></v-select>  
    
        <v-text-field
            v-model="page3Data.college.subjects_major_degree"
            label="Subjects / Major / Degree"
            outlined
        ></v-text-field>

    </div>

    <div style="display: flex; flex-direction: row; justify-content: space-between; align-items: center; margin-bottom: 25px;">

        <h3 style="color: white; font-size: 20px;">Other Education</h3>

        <div>

            <v-btn @click="page3Data.other.show = !page3Data.other.show">
                {{ page3Data.other.show ? 'Hide Other Info' : 'Show Other Info' }}
            </v-btn>

            <v-btn @click="removeInfo('other')">Remove Info</v-btn>

        </div>
        
    </div>

    <div v-show="page3Data.other.show">
    
        <v-text-field
            v-model="page3Data.other.name"
            label="School Name"
            outlined
        ></v-text-field>
        
        <v-text-field
            v-model="page3Data.other.year_graduated"
            label="Year Graduated"
            maxlength="4"
            outlined
        ></v-text-field>
        
        <v-text-field
            v-model="page3Data.other.city"
            label="School City"
            outlined
        ></v-text-field>

        <v-select
            v-model="page3Data.other.state"
            :items="state_options"
            label="State"
            outlined
        ></v-select>
        
        <v-text-field
            v-model="page3Data.other.subjects_major_degree"
            label="Subjects / Major / Degree"
            outlined
        ></v-text-field>

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

watch: {},
computed: {},
created() {},
props: {
    page3Data: Object,
},
data: () => ({

    state_options: [

      'AL', 'AK', 'AZ', 'AR', 'CA', 'CO', 'CT', 'DE', 'FL', 'GA', 
      'HI', 'ID', 'IL', 'IN', 'IA', 'KS', 'KY', 'LA', 'ME', 'MD',
      'MA', 'MI', 'MN', 'MS', 'MO', 'MT', 'NE', 'NV', 'NH', 'NJ',
      'NM', 'NY', 'NC', 'ND', 'OH', 'OK', 'OR', 'PA', 'RI', 'SC',
      'SD', 'TN', 'TX', 'UT', 'VT', 'VA', 'WA', 'WV', 'WI', 'WY'
    ]
}),
methods: {

    removeInfo(type) {

        if (type == 'high_school') {

            this.page3Data.high_school = {

                show: false,
                name: '',
                city: '',
                state: '',
                year_graduated: '',
            }
        } else if (type == 'ged') {

            this.page3Data.ged = {

                show: false,
                name: '',
                city: '',
                state: '',
                year_graduated: '',
            }
        } else if (type == 'college') {
                
            this.page3Data.college = {

                show: false,
                name: '',
                year_graduated: '',
                city: '',
                state: '',
                subjects_major_degree: '',
            }
        } else if (type == 'other') {

            this.page3Data.other = {

                show: true,
                name: '',
                year_graduated: '',
                city: '',
                state: '',
                subjects_major_degree: '',
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

                page: 3,
                page3Data: this.page3Data,
                progressPage: true,
            });
        } else if (formValidation.message !== '') {
            this.showSnackbar(formValidation.message, 'error', 5000);
        }

        if (!validate) {
            
            this.$emit('formAction', {

                page: 3,
                page3Data: this.page3Data,
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

        if (this.page3Data.hs_or_ged == '') {
            return {
                valid: false,
                message: 'Please select if you graduated from high school or obtained a GED.'
            }
        }
        if (this.page3Data.hs_or_ged == 'high_school') {

            if (this.page3Data.high_school.name == '') {
                return {
                    valid: false,
                    message: 'Please enter your high school name.'
                }
            } else if (this.page3Data.high_school.year_graduated == '') {
                return {
                    valid: false,
                    message: 'Please enter the year you graduated from high school.'
                }
            } else if (this.page3Data.high_school.year_graduated.length != 4) {
                return {
                    valid: false,
                    message: 'Please enter a valid year you graduated from high school.'
                }
            } else if (this.page3Data.high_school.city == '') {
                return {
                    valid: false,
                    message: 'Please enter your high school city.'
                }
            } else if (this.page3Data.high_school.state == '') {
                return {
                    valid: false,
                    message: 'Please enter your high school state.'
                }
            } else {
                return {
                    valid: true,
                    message: ''
                }
            }
        } else if (this.page3Data.hs_or_ged == 'ged') {
               
        if (this.page3Data.ged.name == '') {
            return {
                valid: false,
                message: 'Please enter your GED name.'
            }
        } else if (this.page3Data.ged.year_graduated == '') {
                return {
                    valid: false,
                    message: 'Please enter the year you graduated from high school.'
                }
            } else if (this.page3Data.ged.year_graduated.length != 4) {
                return {
                    valid: false,
                    message: 'Please enter a valid year you graduated from high school.'
                }
            } else if (this.page3Data.ged.city == '') {
                return {
                    valid: false,
                    message: 'Please enter your GED city.'
                }
            } else if (this.page3Data.ged.state == '') {
                return {
                    valid: false,
                    message: 'Please enter your GED state.'
                }
            } else {
                return {
                    valid: true,
                    message: ''
                }
            }
        }
    },
    backApplicationStep() {
        this.$emit('backApplicationStep');
    },
},
})
})
