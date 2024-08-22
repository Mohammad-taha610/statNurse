window.addEventListener('load', (number) => {
Vue.component('nurse-app-form-step6', {
template: /*html*/`
<validation-observer
    ref="observer"
    v-slot="{ invalid }"
>

<h2 style="color: white; font-size: 24px; margin-bottom: 25px;">Medical History Confirmation</h2>
    
<div style="display: none; flex-direction: row; justify-content: space-between; align-items: center; margin-bottom: 25px;">

    <h3 style="color: white; font-size: 16px;">Have you had any of the following conditions or diseases?</h3>

    <v-btn @click="page6Data.medical_history_show = !page6Data.medical_history_show">
        {{ page6Data.medical_history_show ? 'Hide Questionnaire' : 'Show Questionnaire' }}
    </v-btn>

</div>

<div v-show="false">

    <v-row>
        <v-col cols="4">
            <v-radio-group
                v-model="page6Data.medical_history.anemia"
                label="Anemia"
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
        </v-col>

        <v-col cols="4">
            <v-radio-group
                v-model="page6Data.medical_history.smallpox"
                label="Smallpox"
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
        </v-col>

        <v-col cols="4">
            <v-radio-group
                v-model="page6Data.medical_history.diabetes"
                label="Diabetes"
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
        </v-col>
        
    </v-row>
    <v-row>

        <v-col cols="4">
            <v-radio-group
                v-model="page6Data.medical_history.diptheria"
                label="Diphtheria"
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
        </v-col>

        <v-col cols="4">
            <v-radio-group
                v-model="page6Data.medical_history.epilepsy"
                label="Epilepsy"
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
        </v-col>

        <v-col cols="4">
            <v-radio-group
                v-model="page6Data.medical_history.heart_disease"
                label="Heart Disease"
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
        </v-col>

    </v-row>
    <v-row>
    
        <v-col cols="4">
            <v-radio-group
                v-model="page6Data.medical_history.kidney_trouble"
                label="Kidney Trouble"
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
        </v-col>
    
        <v-col cols="4">
            <v-radio-group
                v-model="page6Data.medical_history.mononucleosis"
                label="Mononucleosis"
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
        </v-col>

        <v-col cols="4">
            <v-radio-group
                v-model="page6Data.medical_history.scarlet_fever"
                label="Scarlet Fever"
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
        </v-col>

    </v-row>
    <v-row>

        <v-col cols="4">
            <v-radio-group
                v-model="page6Data.medical_history.typhoid"
                label="Typhoid Fever"
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
        </v-col>

        <v-col cols="4">
            <v-radio-group
                v-model="page6Data.medical_history.hypertension"
                label="Hypertension"
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
        </v-col>

        <v-col cols="4">
            <v-radio-group
                v-model="page6Data.medical_history.latex_allergy"
                label="Latex Allergy"
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
        </v-col>

    </v-row>
    <v-row>

        <v-col cols="4">
            <v-radio-group
                v-model="page6Data.medical_history.hernia"
                label="Hernia"
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
        </v-col>

        <v-col cols="4">
            <v-radio-group
                v-model="page6Data.medical_history.depression"
                label="Deperession"
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
        </v-col>

        <v-col cols="4">
            <v-radio-group
                v-model="page6Data.medical_history.measles"
                label="Measles"
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
        </v-col>

    </v-row>
    <v-row>

        <v-col cols="4">
            <v-radio-group
                v-model="page6Data.medical_history.hepatitis"
                label="Hepatitis"
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
        </v-col>

        <v-col cols="4">
            <v-radio-group
                v-model="page6Data.medical_history.mumps"
                label="Mumps"
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
        </v-col>

        <v-col cols="4">
            <v-radio-group
                v-model="page6Data.medical_history.pleurisy"
                label="Pleurisy"
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
        </v-col>

    </v-row>
    <v-row>

        <v-col cols="4">
            <v-radio-group
                v-model="page6Data.medical_history.pneumonia"
                label="Pneumonia"
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
        </v-col>

        <v-col cols="4">
            <v-radio-group
                v-model="page6Data.medical_history.chicken_pox"
                label="Chicken Pox"
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
        </v-col>

        <v-col cols="4">
            <v-radio-group
                v-model="page6Data.medical_history.emphysema"
                label="Emphysema"
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
        </v-col>

    </v-row>
    <v-row>

        <v-col cols="4">
            <v-radio-group
                v-model="page6Data.medical_history.tuberculosis"
                label="Tuberculosis"
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
        </v-col>

        <v-col cols="4">
            <v-radio-group
                v-model="page6Data.medical_history.whopping_cough"
                label="Whopping Cough"
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
        </v-col>

        <v-col cols="4">
            <v-radio-group
                v-model="page6Data.medical_history.rheumatic_fever"
                label="Rheumatic Fever"
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
        </v-col>

    </v-row>
    <v-row style="margin-bottom: 30px;">

        <v-col cols="4">
            <v-radio-group
                v-model="page6Data.medical_history.carpal_tunnel"
                label="Carple Tunnel"
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
        </v-col>

        <v-col cols="4">
            <v-radio-group
                v-model="page6Data.medical_history.sight_hearing_problems"
                label="Sight or Hearing Problems"
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
        </v-col>

        <v-col cols="4">
            <v-radio-group
                v-model="page6Data.medical_history.color_blindness"
                label="Color Blindness"
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
        </v-col>
    </v-row>
</div>
    
<div style="display: none; flex-direction: row; justify-content: space-between; align-items: center; margin-bottom: 25px;">

    <h3 style="color: white; font-size: 12px;">If you have had any of the following please provide approximate dates and a brief description of the occurrance</h3>

    <v-btn @click="page6Data.injury_history_show = !page6Data.injury_history_show">
        {{ page6Data.injury_history_show ? 'Hide Questionnaire' : 'Show Questionnaire' }}
    </v-btn>

</div>

<div v-show="false">

    <ul class="bulleted" style="margin-bottom: 30px;">
        <li>Fractures</li>
        <li>Back Problems or Injuries</li>
        <li>Other Injuries that caused you to miss work more than 10 days</li>
        <li>Surgeries</li>
        <li>Permanent physical restrictions</li>
    </ul>
    
    <v-textarea
        v-model="page6Data.injury_explanation"
        label="Details"
        ref="injury_explanation"
        hint="If none of these apply to you, please type 'none' in the text box."
        persistent-hint
        outlined
    ></v-textarea>

</div>
    
<div style="display: flex; flex-direction: row; justify-content: space-between; align-items: center; margin-bottom: 25px;">

    <h3 style="color: white; font-size: 16px;">Please indicate whether you have recieved the following vaccinations:</h3>

    <v-btn @click="page6Data.vaccination_history_show = !page6Data.vaccination_history_show">
        {{ page6Data.vaccination_history_show ? 'Hide Questionnaire' : 'Show Questionnaire' }}
    </v-btn>

</div>

<div v-show="page6Data.vaccination_history_show">

    <v-row>
        <v-col cols="4">
            <v-radio-group
                v-model="page6Data.routine_vaccinations"
                label="Routine Vaccinations Current *"
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
        </v-col>

        <v-col cols="4">
            <v-radio-group
                v-model="page6Data.hepatitis_b"
                label="Hepatitis B *"
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
        </v-col>
        
        <v-col cols="4">
            <v-radio-group
                v-model="page6Data.hepatitis_a"
                label="Hepatitis A *"
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
        </v-col>
    </v-row>

    <v-row>
        <v-col cols="4">
            <v-radio-group
                v-model="page6Data.covid_19"
                label="Covid-19 *"
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
        </v-col>
        
        <div v-show="page6Data.covid_19 === '0'">
            <v-col cols="4">
                <v-radio-group
                    v-model="page6Data.covid_19_exemption"
                    label="Do you have a Covid-19 exemption? *"
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
            </v-col>
        </div>
    </v-row>
</div>
    
<div style="display: flex; flex-direction: row; justify-content: space-between; align-items: center; margin-bottom: 25px;">

    <h3 style="color: white; font-size: 16px;">Tuberculosis Screening Questionnaire</h3>

    <v-btn @click="page6Data.tuberculosis_screening_show = !page6Data.tuberculosis_screening_show">
        {{ page6Data.tuberculosis_screening_show ? 'Hide Questionnaire' : 'Show Questionnaire' }}
    </v-btn>

</div>

<div v-show="page6Data.tuberculosis_screening_show">

    <v-row>
        <v-col cols="4">
            <v-radio-group
                v-model="page6Data.positive_tb_screening"
                label="Have you had a positive TB skin test in the past? *"
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
        </v-col>

        <v-col cols="8">
            <div
                v-show="page6Data.positive_tb_screening === '1'"
                style="display: flex; justify-content: center; align-items: center; width: 100%; height: 100%;"
            >
                <v-text-field
                    v-model="page6Data.positive_tb_date"
                    label="Date of positive TB screening *"
                    ref="positive_tb_date"
                    outlined
                    type="number"
                    :rules="date_rules"
                    hint="MMDDYYYY"
                    maxlength="8"
                ></v-text-field>
            </div>
        </v-col>
    </v-row>

    <v-row>
        <v-col cols="4">
            <v-radio-group
                v-model="page6Data.xray"
                label="Have you had a chest x-ray in the past? *"
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
        </v-col>

        <v-col cols="8">
            <div
                v-show="page6Data.xray === '1'"
                style="display: flex; justify-content: center; align-items: center; width: 100%; height: 100%;"
            >
                <v-text-field
                    v-model="page6Data.xray_date"
                    label="Date of x-ray *"
                    ref="xray_date"
                    outlined
                    type="number"
                    :rules="date_rules"
                    hint="MMDDYYYY"
                    maxlength="8"
                ></v-text-field>
            </div>
        </v-col>
    </v-row>

</div>

<p>I have answered the questions fully and declare that I have no
known injury, Illness, or ailment other than those previously
noted. I further understand that any misrepresentation, or
omission may be grounds for corrective action up to and
including termination of my contract.</p>

<p><strong>Please input your name as a digital
signature below indicating your acceptance of this
agreement *</strong></p>

<v-text-field
    v-model="page6Data.signature"
    label="Signature *"
    ref="signature"
    outlined
    hint="Please enter your full legal name."
    persistent-hint
    style="margin-top: 16px;"
    autocomplete="signature"
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
        >Next</button>
    </div>
</div>

</validation-observer>
`,

watch: {},
computed: {},
created() {},
props: {
    page6Data: Object,
},
data: () => ({
    date_rules: [ v => v.length <= 8 || 'Max 8 digits' ],
}),
methods: {

    formatDate(field) {

        let date = '';
        if (field == 'positive_tb_date') { date = this.page6Data.positive_tb_date; }
        else if (field == 'xray_date') { date = this.page6Data.xray_date; }

        if (date.length == 2) { date += '/'; }
        else if (date.length == 5) { date += '/'; }
        else if (date.length > 10) { date = date.substring(0, 10); }

        if (field == 'positive_tb_date') { this.page6Data.positive_tb_date = date; }
        else if (field == 'xray_date') { this.page6Data.xray_date = date; }
    },
    formAction(validate = true) {

        let formValidation = {};
        if (validate) {
            formValidation = this.validateForm();
        }

        if (formValidation.valid) {

            this.$emit('formAction', {

                page: 6,
                page6Data: this.page6Data,
                progressPage: true,
            });
        } else if (formValidation.message !== '') {
            this.showSnackbar(formValidation.message, 'error', 5000);
        }

        if (!validate) {
            this.$emit('formAction', {

                page: 6,
                page6Data: this.page6Data,
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

       /*  if (this.page6Data.injury_explanation == '') {
            this.$refs.injury_explanation.focus();
            return {
                valid: false,
                message: "Please provide an explanation for your injury history or type 'none'.",
            };
        } else */ if (this.page6Data.routine_vaccinations != '1' && this.page6Data.routine_vaccinations != '0') {
            return {
                valid: false,
                message: "Please indicate whether you have recieved routine vaccinations.",
            }; 
        } else if (this.page6Data.hepatitis_b != '1' && this.page6Data.hepatitis_b != '0') {
            return {
                valid: false,
                message: "Please indicate whether you have recieved the Hepatitis B vaccination.",
            }; 
        } else if (this.page6Data.hepatitis_a != '1' && this.page6Data.hepatitis_a != '0') {
            return {
                valid: false,
                message: "Please indicate whether you have recieved the Hepatitis A vaccination.",
            }; 
        } else if (this.page6Data.covid_19 != '1' && this.page6Data.covid_19 != '0') {
            return {
                valid: false,
                message: "Please indicate whether you have recieved the Covid-19 vaccination.",
            };         
        } else if (this.page6Data.covid_19 == '0' && this.page6Data.covid_19_exemption != '1' && this.page6Data.covid_19_exemption != '0') {
            return {
                valid: false,
                message: "Please indicate whether you have a Covid-19 exemption.",
            }; 
        } else if (this.page6Data.positive_tb_screening != '1' && this.page6Data.positive_tb_screening != '0') {
            return {
                valid: false,
                message: "Please indicate whether you have had a positive TB screening in the past.",
            };  
        } else if (this.page6Data.positive_tb_screening == '1' && this.page6Data.positive_tb_date.length != 8) {
            this.$refs.positive_tb_date.focus();
            return {
                valid: false,
                message: "Please provide a valid date for your positive TB screening.",
            };
        } else if (this.page6Data.xray != '1' && this.page6Data.xray != '0') {
            console.log("x ray: ", this.page6Data.xray_date)
            return {
                valid: false,
                message: "Please indicate whether you have had a positive chest x-ray in the past.",
            };
        } else if (this.page6Data.xray == '1' && this.page6Data.xray_date.length != 8) {
            this.$refs.xray_date.focus();
            return {
                valid: false,
                message: "Please provide a valid date for your positive chest x-ray.",
            };
        } else if (this.page6Data.signature == '' || this.page6Data.signature == null) {
            this.$refs.signature.focus();
            return {
                valid: false,
                message: "Please provide a signature.",
            };
        } else {
            return {
                valid: true,
                message: '',
            };
        }
    },
    backApplicationStep() {
        this.$emit('backApplicationStep');
    },
},
})});
