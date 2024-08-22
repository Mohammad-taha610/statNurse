window.addEventListener('load', () => {
    VeeValidate.extend('required', {
        validate (value) {
            return {
                required: true,
                valid: ['', null, undefined].indexOf(value) === -1
            }
        },
        computesRequired: true,
        message: 'The {_field_} is required'
    })

    Vue.use(window.VueTheMask)
    Vue.component('ValidationProvider', VeeValidate.ValidationProvider)

    Vue.component('nurse-app-form', {
        template: `
            <div class="container my-16 nurse-app-form" data-app>
                <div class="row">
                    <div class="col-md-8 offset-md-2">
                        <!-- Full Name -->
                        <div class="row">
                            <div class="col-md-12">
                                <v-card class="px-10 pt-10 pb-8" elevation="2" v-if="!submitted">
                                    <div class="d-flex align-items-center justify-content-between mt-3 mb-16">
                                        <h1>Nurse Application Part 2</h1>

                                        <div>
                                            <div class="d-flex align-items-center justify-content-between">
                                                <v-dialog v-model="dialogTwo" width="500" v-if="!member">
                                                    <template v-slot:activator="{ on, attrs }">
                                                        <v-btn color="primary" elevation="2" v-bind="attrs" v-on="on">Resume</v-btn>
                                                    </template>

                                                    <v-card>
                                                        <v-card-title class="text-h5 grey lighten-2">
                                                            Resume Form
                                                        </v-card-title>

                                                        <v-card-text>
                                                            <div class="pt-4">
                                                                <v-text-field type="email" label="Email" v-model="registerForm.email"></v-text-field>
                                                                <v-text-field type="password" label="Password" v-model="registerForm.password"></v-text-field>
                                                            </div>
                                                        </v-card-text>

                                                        <v-divider></v-divider>

                                                        <v-card-actions>
                                                            <v-spacer></v-spacer>
                                                            <v-btn
                                                                color="primary"
                                                                text
                                                                @click="saveProgress"
                                                            >
                                                                Login
                                                            </v-btn>
                                                        </v-card-actions>
                                                    </v-card>
                                                </v-dialog>

                                                <v-pagination
                                                    v-model="page"
                                                    :length="5"
                                                ></v-pagination>
                                            </div>
                                        </div>
                                    </div>

                                    <v-card-text>
                                    </v-card-text>
                                </v-card>

                                <v-card class="px-10 pt-10 pb-8" elevation="2" v-else>
                                    <div class="text-center mt-4">
                                        <h2>Application Submitted</h2>

                                        <p class="mt-4">Thank you for taking an interest in NurseStat!</p>

                                        <p>Your application will be reviewed and you will receive an email with further instructions once a decision has been made.</p>
                                    </div>
                                </v-card>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `,

        watch: {
            page () {
                document.body.scrollTop = document.documentElement.scrollTop = 0
            }
        },

        created () {
            const submitted = localStorage.getItem('submittedTwo')

            if (submitted) {
                localStorage.removeItem('submittedTwo')
                localStorage.removeItem('formTwo')
                localStorage.removeItem('page')
            }
        },

        mounted () {
            var canvas = document.querySelector('#canvas')
            var canvasTwo = document.querySelector('#canvas-two')

            this.signaturePad = new SignaturePad(canvas, {
                onEnd: () => this.form.terms.signature = this.signaturePad.toData()
            })

            this.signaturePadTwo = new SignaturePad(canvasTwo, {
                onEnd: () => this.form.tb.signature = this.signaturePadTwo.toData()
            })

            if (authenticatedMember) {
                this.member = authenticatedMember

                const formData = localStorage.getItem('formTwo')

                if (formData) {
                    this.form = JSON.parse(formData)
                }

                if (this.form.terms.signature) {
                    this.signaturePad.fromData(this.form.terms.signature)
                }

                if (this.form.tb.signature) {
                    this.signaturePadTwo.fromData(this.form.tb.signature)
                }
            }
        },

        data: () => ({
            page: 1,
            picker: '',
            dialog: '',
            member: null,
            dialogTwo: '',
            radioGroup: '',
            submitted: false,
            registerForm: {
                email: '',
                password: ''
            },
            checkbox: false,
            signaturePad: null,
            signaturePadTwo: null,
            form: {
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
                },

                tb: {
                    date: '',
                    chest_date: '',
                    signature: '',
                }
            }
        }),

        methods: {
            saveProgress () {
                const data = { application: this.form }

                modRequest.request('nurse.application.storeTwo', null, data, (res) => {
                    const formString = JSON.stringify(this.form)

                    localStorage.setItem('formTwo', formString)
                }, () => console.log('no'))
            },

            onSubmit () {
                this.saveProgress()

                this.submitted = true

                localStorage.setItem('submittedTwo', true)
            }
        }
    })
})
