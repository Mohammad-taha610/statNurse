Vue.component('upload-documents-mobile-view', {
template: /*html*/`
<div class="nurse-app h-100">
    <div class="container h-100" data-app>
        <div class="row justify-content-center h-100 align-items-center">
            <div class="nurse-app-form h-100 col-md-6">
                <div class="nurse-app-content">
                    <div class="row no-gutters">
                        <div class="col-xl-12">

                            <div style="display: flex; flex-direction: row; justify-content: space-between; align-items: flex-end;">
                                
                                <div class="text-center py-6">
                                    <a href="/member/login"><img class="img-fluid" src="/themes/nst/assets/images/white-logo.png" alt=""></a>
                                </div>             
                            </div>                   

                            <h3 style="color: white; font-size: 18px; margin-top: 30px;">License Number</h3>
                            <p style="color: #BDBDBD; font-size: 14px;">Add at least one, but up to three nurse state licenses.</p>

                            <v-select
                                v-model="uploaded_files.nursing_license_1.state"
                                :items="state_options"
                                label="State *"
                                outlined
                            ></v-select>

                            <v-text-field
                                v-model="uploaded_files.nursing_license_1.license_number"
                                label="License Number *"
                                outlined
                            ></v-text-field>    

                            <v-text-field
                                v-model="uploaded_files.nursing_license_1.full_name"
                                label="Full Name as it appears on license *"
                                outlined
                            ></v-text-field>

                            <h3 style="color: white; font-size: 18px; margin-top: 30px;">Upload Picture of Nurse License 1</h3>
                            
                            <v-row style="margin-top: 30px;">
                                <v-col cols="12" md="4">
                        
                                    <file-uploader
                                        :config="upload_configs.nursing_license_1"
                                        @fileUploaded="uploadResponse"
                                    />
                        
                                    <v-card
                                        class="member-file-card"
                                        style="margin-top: 20px;"
                                        color="#313131"
                                        :href="uploaded_files.nursing_license_1.url"
                                        target="_blank"
                                        v-show="uploaded_files.nursing_license_1.name"
                                    >
                                        <v-card-text>
                                            <div class="member-file-icons" style="width:100%; display: flex; flex-direction: row; justify-content: flex-end; align-items: center;">
                                                <v-btn icon>
                                                    <v-icon color="#C62828" @click.prevent="deleteFile('Nurse License 1')">mdi-trash-can-outline</v-icon>
                                                </v-btn>
                                            </div>
                                            <div class="member-file-icon" style="margin-bottom: 20px;">
                                                <v-icon color="#757575">mdi-file</v-icon>
                                            </div>
                                            <div class="member-file-name-container">
                                                <span class="member-file-name" style="color: #E0E0E0;">{{ uploaded_files.nursing_license_1.name }}</span>
                                            </div>
                                            <div class="member-file-tag-container mt-1">
                                                <span class="member-file-tag-name" style="color: #757575;">{{ uploaded_files.nursing_license_1.fileTag }}</span>
                                            </div>
                                        </v-card-text>
                                    </v-card>
                                </v-col>
                            </v-row>
                        
                            <div style="display: flex; flex-direction: row; justify-content: flex-start; align-items: center;">
                                <v-btn
                                    @click="uploaded_files.nursing_license_2.show = !uploaded_files.nursing_license_2.show"
                                    style="margin: 30px 0;"
                                ><v-icon>{{ uploaded_files.nursing_license_2.show ? 'mdi-minus' : 'mdi-plus' }}</v-icon>
                                </v-btn>
                                <span style="color: white; font-size: 18px; margin-left: 30px;">Add another license</span>
                            </div>
                        
                            <div v-show="uploaded_files.nursing_license_2.show">
                        
                                <v-select
                                    v-model="uploaded_files.nursing_license_2.state"
                                    :items="state_options"
                                    label="State"
                                    outlined
                                ></v-select>
                        
                                <v-text-field
                                    v-model="uploaded_files.nursing_license_2.license_number"
                                    label="License Number"
                                    outlined
                                ></v-text-field>
                        
                                <v-text-field
                                    v-model="uploaded_files.nursing_license_2.full_name"
                                    label="Full Name as it appears on license"
                                    outlined
                                ></v-text-field>
                            
                                <v-row style="margin-top: 30px;">
                                    <v-col cols="12" md="4">
                            
                                        <file-uploader
                                            :config="upload_configs.nursing_license_2"
                                            @fileUploaded="uploadResponse"
                                        />
                            
                                        <v-card
                                            class="member-file-card"
                                            style="margin-top: 20px;"
                                            color="#313131"
                                            :href="uploaded_files.nursing_license_2.url"
                                            target="_blank"
                                            v-show="uploaded_files.nursing_license_2.name"
                                        >
                                            <v-card-text>
                                                <div class="member-file-icons" style="width:100%; display: flex; flex-direction: row; justify-content: flex-end; align-items: center;">
                                                    <v-btn icon>
                                                        <v-icon color="#C62828" @click.prevent="deleteFile('Nurse License 2')">mdi-trash-can-outline</v-icon>
                                                    </v-btn>
                                                </div>
                                                <div class="member-file-icon" style="margin-bottom: 20px;">
                                                    <v-icon color="#757575">mdi-file</v-icon>
                                                </div>
                                                <div class="member-file-name-container">
                                                    <span class="member-file-name" style="color: #E0E0E0;">{{ uploaded_files.nursing_license_2.name }}</span>
                                                </div>
                                                <div class="member-file-tag-container mt-1">
                                                    <span class="member-file-tag-name" style="color: #757575;">{{ uploaded_files.nursing_license_2.fileTag }}</span>
                                                </div>
                                            </v-card-text>
                                        </v-card>
                                    </v-col>
                                </v-row>
                            </div>
                        
                            <div style="display: flex; flex-direction: row; justify-content: flex-start; align-items: center;">
                                <v-btn
                                    @click="uploaded_files.nursing_license_3.show = !uploaded_files.nursing_license_3.show"
                                    style="margin: 30px 0;"
                                ><v-icon>{{ uploaded_files.nursing_license_3.show ? 'mdi-minus' : 'mdi-plus' }}</v-icon>
                                </v-btn>
                                <span style="color: white; font-size: 18px; margin-left: 30px;">Add another license</span>
                            </div>
                        
                            <div v-show="uploaded_files.nursing_license_3.show">
                        
                                <v-select
                                    v-model="uploaded_files.nursing_license_3.state"
                                    :items="state_options"
                                    label="State"
                                    outlined
                                ></v-select>
                        
                                <v-text-field
                                    v-model="uploaded_files.nursing_license_3.license_number"
                                    label="License Number"
                                    outlined
                                ></v-text-field>
                        
                                <v-text-field
                                    v-model="uploaded_files.nursing_license_3.full_name"
                                    label="Full Name as it appears on license"
                                    outlined
                                ></v-text-field>
                            
                                <v-row style="margin-top: 30px;">
                                    <v-col cols="12" md="4">
                            
                                        <file-uploader
                                            :config="upload_configs.nursing_license_3"
                                            @fileUploaded="uploadResponse"
                                        />
                            
                                        <v-card
                                            class="member-file-card"
                                            style="margin-top: 20px;"
                                            color="#313131"
                                            :href="uploaded_files.nursing_license_3.url"
                                            target="_blank"
                                            v-show="uploaded_files.nursing_license_3.name"
                                        >
                                            <v-card-text>
                                                <div class="member-file-icons" style="width:100%; display: flex; flex-direction: row; justify-content: flex-end; align-items: center;">
                                                    <v-btn icon>
                                                        <v-icon color="#C62828" @click.prevent="deleteFile('Nurse License 3')">mdi-trash-can-outline</v-icon>
                                                    </v-btn>
                                                </div>
                                                <div class="member-file-icon" style="margin-bottom: 20px;">
                                                    <v-icon color="#757575">mdi-file</v-icon>
                                                </div>
                                                <div class="member-file-name-container">
                                                    <span class="member-file-name" style="color: #E0E0E0;">{{ uploaded_files.nursing_license_3.name }}</span>
                                                </div>
                                                <div class="member-file-tag-container mt-1">
                                                    <span class="member-file-tag-name" style="color: #757575;">{{ uploaded_files.nursing_license_3.fileTag }}</span>
                                                </div>
                                            </v-card-text>
                                        </v-card>
                                    </v-col>
                                </v-row>
                            </div>
                            
                            <h3 style="color: #FFFFFF; font-size: 24px;">Required Uploads</h3>
                            <p style="color: #BDBDBD; font-size: 14px;">These documents are required for application submission.</p>

                            <v-row>
                                <v-col cols="12" md="4">

                                    <file-uploader
                                        :config="upload_configs.drivers_license"
                                        @fileUploaded="uploadResponse"
                                    />

                                    <v-card
                                        class="member-file-card"
                                        style="margin-top: 20px;"
                                        color="#313131"
                                        :href="uploaded_files.drivers_license.url"
                                        target="_blank"
                                        v-show="uploaded_files.drivers_license.name"
                                    >
                                        <v-card-text>
                                            <div class="member-file-icons" style="width:100%; display: flex; flex-direction: row; justify-content: flex-end; align-items: center;">
                                                <v-btn icon>
                                                    <v-icon color="#C62828" @click.prevent="deleteFile('Driver License')">mdi-trash-can-outline</v-icon>
                                                </v-btn>
                                            </div>
                                            <div class="member-file-icon" style="margin-bottom: 20px;">
                                                <v-icon color="#757575">mdi-file</v-icon>
                                            </div>
                                            <div class="member-file-name-container">
                                                <span class="member-file-name" style="color: #E0E0E0;">{{ uploaded_files.drivers_license.name }}</span>
                                            </div>
                                            <div class="member-file-tag-container mt-1">
                                                <span class="member-file-tag-name" style="color: #757575;">{{ uploaded_files.drivers_license.fileTag }}</span>
                                            </div>
                                        </v-card-text>
                                    </v-card>
                                </v-col>

                                <v-col cols="12" md="4">

                                    <file-uploader
                                        :config="upload_configs.social_security"
                                        @fileUploaded="uploadResponse"
                                    />

                                    <v-card
                                        class="member-file-card"
                                        style="margin-top: 20px;"
                                        color="#313131"
                                        :href="uploaded_files.social_security.url"
                                        target="_blank"
                                        v-show="uploaded_files.social_security.name"
                                    >
                                        <v-card-text>
                                            <div class="member-file-icons" style="width:100%; display: flex; flex-direction: row; justify-content: flex-end; align-items: center;">
                                                <v-btn icon>
                                                    <v-icon color="#C62828" @click.prevent="deleteFile('Social Security')">mdi-trash-can-outline</v-icon>
                                                </v-btn>
                                            </div>
                                            <div class="member-file-icon" style="margin-bottom: 20px;">
                                                <v-icon color="#757575">mdi-file</v-icon>
                                            </div>
                                            <div class="member-file-name-container">
                                                <span class="member-file-name" style="color: #E0E0E0;">{{ uploaded_files.social_security.name }}</span>
                                            </div>
                                            <div class="member-file-tag-container mt-1">
                                                <span class="member-file-tag-name" style="color: #757575;">{{ uploaded_files.social_security.fileTag }}</span>
                                            </div>
                                        </v-card-text>
                                    </v-card>
                                </v-col>

                                <v-col cols="12" md="4">

                                    <file-uploader
                                        :config="upload_configs.tb_skin_test"
                                        @fileUploaded="uploadResponse"
                                    />

                                    <v-card
                                        class="member-file-card"
                                        style="margin-top: 20px;"
                                        color="#313131"
                                        :href="uploaded_files.tb_skin_test.url"
                                        target="_blank"
                                        v-show="uploaded_files.tb_skin_test.name"
                                    >
                                        <v-card-text>
                                            <div class="member-file-icons" style="width:100%; display: flex; flex-direction: row; justify-content: flex-end; align-items: center;">
                                                <v-btn icon>
                                                    <v-icon color="#C62828" @click.prevent="deleteFile('TB Skin Test')">mdi-trash-can-outline</v-icon>
                                                </v-btn>
                                            </div>
                                            <div class="member-file-icon" style="margin-bottom: 20px;">
                                                <v-icon color="#757575">mdi-file</v-icon>
                                            </div>
                                            <div class="member-file-name-container">
                                                <span class="member-file-name" style="color: #E0E0E0;">{{ uploaded_files.tb_skin_test.name }}</span>
                                            </div>
                                            <div class="member-file-tag-container mt-1">
                                                <span class="member-file-tag-name" style="color: #757575;">{{ uploaded_files.tb_skin_test.fileTag }}</span>
                                            </div>
                                        </v-card-text>
                                    </v-card>
                                </v-col>
                            </v-row>

                            <h3 style="color: white; font-size: 18px; margin-top: 30px;">Optional Uploads</h3>
                            <p style="color: #BDBDBD; font-size: 14px;">These documents are not required for application submission but may be required at a later time.</p>
                        
                            <v-row>
                                <v-col cols="12" md="4">
                        
                                    <file-uploader
                                        :config="upload_configs.cpr_card"
                                        @fileUploaded="uploadResponse"
                                    />
                        
                                    <v-card
                                        class="member-file-card"
                                        style="margin-top: 20px;"
                                        color="#313131"
                                        :href="uploaded_files.cpr_card.url"
                                        target="_blank"
                                        v-show="uploaded_files.cpr_card.name"
                                    >
                                        <v-card-text>
                                            <div class="member-file-icons" style="width:100%; display: flex; flex-direction: row; justify-content: flex-end; align-items: center;">
                                                <v-btn icon>
                                                    <v-icon color="#C62828" @click.prevent="deleteFile('CPR Card')">mdi-trash-can-outline</v-icon>
                                                </v-btn>
                                            </div>
                                            <div class="member-file-icon" style="margin-bottom: 20px;">
                                                <v-icon color="#757575">mdi-file</v-icon>
                                            </div>
                                            <div class="member-file-name-container">
                                                <span class="member-file-name" style="color: #E0E0E0;">{{ uploaded_files.cpr_card.name }}</span>
                                            </div>
                                            <div class="member-file-tag-container mt-1">
                                                <span class="member-file-tag-name" style="color: #757575;">{{ uploaded_files.cpr_card.fileTag }}</span>
                                            </div>
                                        </v-card-text>
                                    </v-card>
                                </v-col>
                        
                                <v-col cols="12" md="4">
                        
                                    <file-uploader
                                        :config="upload_configs.bls_acl_card"
                                        @fileUploaded="uploadResponse"
                                    />
                        
                                    <v-card
                                        class="member-file-card"
                                        style="margin-top: 20px;"
                                        color="#313131"
                                        :href="uploaded_files.bls_acl_card.url"
                                        target="_blank"
                                        v-show="uploaded_files.bls_acl_card.name"
                                    >
                                        <v-card-text>
                                            <div class="member-file-icons" style="width:100%; display: flex; flex-direction: row; justify-content: flex-end; align-items: center;">
                                                <v-btn icon>
                                                    <v-icon color="#C62828" @click.prevent="deleteFile('BLS ACL')">mdi-trash-can-outline</v-icon>
                                                </v-btn>
                                            </div>
                                            <div class="member-file-icon" style="margin-bottom: 20px;">
                                                <v-icon color="#757575">mdi-file</v-icon>
                                            </div>
                                            <div class="member-file-name-container">
                                                <span class="member-file-name" style="color: #E0E0E0;">{{ uploaded_files.bls_acl_card.name }}</span>
                                            </div>
                                            <div class="member-file-tag-container mt-1">
                                                <span class="member-file-tag-name" style="color: #757575;">{{ uploaded_files.bls_acl_card.fileTag }}</span>
                                            </div>
                                        </v-card-text>
                                    </v-card>
                                </v-col>
                                
                                <v-col cols="12" md="4">
                        
                                    <file-uploader
                                        :config="upload_configs.covid_vaccine_card"
                                        @fileUploaded="uploadResponse"
                                    />
                        
                                    <v-card
                                        class="member-file-card"
                                        style="margin-top: 20px;"
                                        color="#313131"
                                        :href="uploaded_files.covid_vaccine_card.url"
                                        target="_blank"
                                        v-show="uploaded_files.covid_vaccine_card.name"
                                    >
                                        <v-card-text>
                                            <div class="member-file-icons" style="width:100%; display: flex; flex-direction: row; justify-content: flex-end; align-items: center;">
                                                <v-btn icon @click.prevent="deleteFile('Covid Vaccine')">
                                                    <v-icon color="#C62828">mdi-trash-can-outline</v-icon>
                                                </v-btn>
                                            </div>
                                            <div class="member-file-icon" style="margin-bottom: 20px;">
                                                <v-icon color="#757575">mdi-file</v-icon>
                                            </div>
                                            <div class="member-file-name-container">
                                                <span class="member-file-name" style="color: #E0E0E0;">{{ uploaded_files.covid_vaccine_card.name }}</span>
                                            </div>
                                            <div class="member-file-tag-container mt-1">
                                                <span class="member-file-tag-name" style="color: #757575;">{{ uploaded_files.covid_vaccine_card.fileTag }}</span>
                                            </div>
                                        </v-card-text>
                                    </v-card>
                                </v-col>
                            </v-row>

                            <h3 style="color: #FFFFFF; font-size: 24px; margin-top: 30px;">Photo ID Upload:</h3>
                            <h4 style="color: #EEEEEE; font-size: 16px; margin-top: 15px;">Photo Basics</h4>
                            <ul>
                                <li style="color: #BDBDBD; font-size: 14px;">Submit a color photo, taken in last 6 months</li>
                                <li style="color: #BDBDBD; font-size: 14px;">Use a clear image of your face. Do not use filters commonly used on social media</li>
                                <li style="color: #BDBDBD; font-size: 14px;">Have someone else take your photo. No selfies</li>
                                <li style="color: #BDBDBD; font-size: 14px;">Take off your eyeglasses for your photo</li>
                                <li style="color: #BDBDBD; font-size: 14px;">Use a plain white or off-white background</li>
                            </ul>
                            <h4 style="color: #EEEEEE; font-size: 16px; margin-top: 15px;">Resolution, Print Size, and Quality</h4>
                            <ul>
                                <li style="color: #BDBDBD; font-size: 14px;">Submit a high resolution photo that is not blurry, grainy, or pixelated</li>
                                <li style="color: #BDBDBD; font-size: 14px;">The correct size of a passport photo is:</li>
                                <li style="color: #BDBDBD; font-size: 14px;">&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp2 x 2 inches (51 x 51 mm)</li>
                                <li style="color: #BDBDBD; font-size: 14px;">&nbsp&nbsp&nbsp&nbsp&nbsp&nbspHead must be between 1 -1 3/8 inches (25 - 35 mm) from the bottom of the chin to the top of the head</li>
                                <li style="color: #BDBDBD; font-size: 14px;">Printed on matte or glossy photo quality paper</li>
                                <li style="color: #BDBDBD; font-size: 14px;">Do not digitally change the photo</li>
                                <li style="color: #BDBDBD; font-size: 14px;">You cannot submit a damaged photo with holes, creases, or smudges</li>
                            </ul>
                            <h4 style="color: #EEEEEE; font-size: 16px; margin-top: 15px;">Pose and Expression</h4>
                            <ul>
                                <li style="color: #BDBDBD; font-size: 14px;">Have a neutral facial expression or a natural smile, with both eyes open</li>
                                <li style="color: #BDBDBD; font-size: 14px;">Face the camera directly with full face in view</li>
                            </ul>
                            <h4 style="color: #EEEEEE; font-size: 16px; margin-top: 15px;">Attire</h4>
                            <ul>    
                                <li style="color: #BDBDBD; font-size: 14px;">Taken in clothing normally worn on a daily basis</li>
                                <li style="color: #BDBDBD; font-size: 14px;">You cannot wear a uniform, clothing that looks like a uniform, or camouflage attire</li>
                                <li style="color: #BDBDBD; font-size: 14px;">You cannot wear a hat or head covering</li>
                                <li style="color: #BDBDBD; font-size: 14px;">&nbsp&nbsp&nbsp&nbsp&nbsp&nbspIf you wear a hat or head covering for religious purposes, submit a signed statement that verifies that the hat or head covering in your photo is part of traditional religious attire worn continuously in public</li>
                                <li style="color: #BDBDBD; font-size: 14px;">&nbsp&nbsp&nbsp&nbsp&nbsp&nbspIf you wear a hat or head covering for medical purposes, submit a signed doctor's statement verifying the hat or head covering in your photo is used daily for medical purposes</li>
                            </ul>  

                            <v-row style="margin-top: 30px;">
                                <v-col cols="12" md="4">
                        
                                    <file-uploader
                                        :config="upload_configs.id_badge_picture"
                                        @fileUploaded="uploadResponse"
                                    />
                        
                                    <v-card
                                        class="member-file-card"
                                        style="margin-top: 20px;"
                                        color="#313131"
                                        :href="uploaded_files.id_badge_picture.url"
                                        target="_blank"
                                        v-show="uploaded_files.id_badge_picture.name"
                                    >
                                        <v-card-text>
                                            <div class="member-file-icons" style="width:100%; display: flex; flex-direction: row; justify-content: flex-end; align-items: center;">
                                                <v-btn icon>
                                                    <v-icon color="#C62828" @click.prevent="deleteFile('ID Badge')">mdi-trash-can-outline</v-icon>
                                                </v-btn>
                                            </div>
                                            <div class="member-file-icon" style="margin-bottom: 20px;">
                                                <v-icon color="#757575">mdi-file</v-icon>
                                            </div>
                                            <div class="member-file-name-container">
                                                <span class="member-file-name" style="color: #E0E0E0;">{{ uploaded_files.id_badge_picture.name }}</span>
                                            </div>
                                            <div class="member-file-tag-container mt-1">
                                                <span class="member-file-tag-name" style="color: #757575;">{{ uploaded_files.id_badge_picture.fileTag }}</span>
                                            </div>
                                        </v-card-text>
                                    </v-card>
                                </v-col>
                            </v-row>

                            <v-snackbar
                                v-model="snackbar"
                                :color="snackbarColor"
                                :timeout="snackbarTimeout"
                            >{{ snackbarMessage }}</v-snackbar>
                        
                            <div class="py-4"></div>
                            <div style="display: flex; flex-direction: row; justify-content: space-between; align-items: center;">

                                <button
                                    class="btn btn-primary"
                                    @click="saveFiles('save')"
                                >Save Progress</button>
                        
                                <div class="flex justify-end">
                        
                                    <button
                                        class="btn btn-primary"
                                        @click="saveFiles('submit')"
                                    >Submit</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
`,
watch: {

},
computed: {},
created() {
    this.$vuetify.theme.dark = true
    this.loadFiles();
},
props: {

    application_id: Number,
},
data: () => ({

    upload_configs: {

        nursing_license_1: {

            id: 'Nurse License 1',
            multiple: false,
            upload_route: '/files/upload',
            chunk_size: 1000000,
            color: '#C62828',
            button_text: 'Upload Nursing License 1',
            fileTag: 'Nurse License 1'
        },
        nursing_license_2: {

            id: 'Nurse License 2',
            multiple: false,
            upload_route: '/files/upload',
            chunk_size: 1000000,
            color: '#C62828',
            button_text: 'Upload Nursing License 2',
            fileTag: 'Nurse License 2'
        },
        nursing_license_3: {

            id: 'Nurse License 3',
            multiple: false,
            upload_route: '/files/upload',
            chunk_size: 1000000,
            color: '#C62828',
            button_text: 'Upload Nursing License 3',
            fileTag: 'Nurse License 3'
        },

        drivers_license: {

            id: 'Driver License',
            multiple: false,
            upload_route: '/files/upload',
            chunk_size: 1000000,
            color: '#C62828',
            button_text: 'Drivers License',
            fileTag: 'Driver License'
        },
        social_security: {

            id: 'Social Security',
            multiple: false,
            upload_route: '/files/upload',
            chunk_size: 1000000,
            color: '#C62828',
            button_text: 'Social Security',
            fileTag: 'Social Security'
        },
        tb_skin_test: {

            id: 'TB Skin Test',
            multiple: false,
            upload_route: '/files/upload',
            chunk_size: 1000000,
            color: '#C62828',
            button_text: 'TB Skin Test',
            fileTag: 'TB Skin Test'
        },

        cpr_card: {

            id: 'CPR Card',
            multiple: false,
            upload_route: '/files/upload',
            chunk_size: 1000000,
            color: '#C62828',
            button_text: 'CPR Card',
            fileTag: 'CPR Card'
        },
        bls_acl_card: {

            id: 'BLS ACL',
            multiple: false,
            upload_route: '/files/upload',
            chunk_size: 1000000,
            color: '#C62828',
            button_text: 'BLS ACL Card',
            fileTag: 'BLS ACL'
        },
        covid_vaccine_card: {

            id: 'Covid Vaccine',
            multiple: false,
            upload_route: '/files/upload',
            chunk_size: 1000000,
            color: '#C62828',
            button_text: 'Covid Vaccine Card',
            fileTag: 'Covid Vaccine'
        },
        
        id_badge_picture: {

            id: 'ID Badge',
            multiple: false,
            upload_route: '/files/upload',
            chunk_size: 1000000,
            color: '#C62828',
            button_text: 'Upload ID Badge Picture',
            fileTag: 'ID Badge'
        },
    },

    state_options: [

      'Compact', 'AL', 'AK', 'AZ', 'AR', 'CA', 'CO', 'CT', 'DE', 'FL', 'GA', 
      'HI', 'ID', 'IL', 'IN', 'IA', 'KS', 'KY', 'LA', 'ME', 'MD',
      'MA', 'MI', 'MN', 'MS', 'MO', 'MT', 'NE', 'NV', 'NH', 'NJ',
      'NM', 'NY', 'NC', 'ND', 'OH', 'OK', 'OR', 'PA', 'RI', 'SC',
      'SD', 'TN', 'TX', 'UT', 'VT', 'VA', 'WA', 'WV', 'WI', 'WY'
    ],
    
    uploaded_files: {

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

    snackbar: false,
    snackbarMessage: '',
    snackbarColor: '',
    snackbarTimeout: -1,
}),
methods: {

    saveFiles(submitOrSave) {

        data = {

            application_id: this.application_id,
            files: this.uploaded_files,
        }
        
        modRequest.request('nurse.application.saveFilesProgress', null, data, function(response) {

            if (response.success) {

                if (submitOrSave == 'save') { 
                    this.showSnackbar({ message: 'Files successfully saved!', color: 'success', timeout: 5000 });
                } else if (submitOrSave == 'submit') {
                    this.showSnackbar({ message: 'Files successfully submitted! Press refresh button to resume progress in portal', color: 'success', timeout: 5000 });
                }        
            }
        }.bind(this));
    },
    loadFiles() {

        data = {
            application_id: this.application_id,
        }
        
        modRequest.request('nurse.application.loadFilesProgress', null, data, function(response) {
            if (response.success) {
                this.uploaded_files = response.uploaded_files;
            } else if (response.message) {

                this.showSnackbar({

                    message: response.message,
                    color: 'red',
                    timeout: 3000,
                });
            }
        }.bind(this));
    },
    uploadResponse(uploadInfo) {

        if (uploadInfo.fileTag == 'Nurse License 1') {
            
            this.uploaded_files.nursing_license_1.date = uploadInfo.file.date;
            this.uploaded_files.nursing_license_1.id = uploadInfo.file.id;
            this.uploaded_files.nursing_license_1.is_completed_file = uploadInfo.file.is_completed_file;
            this.uploaded_files.nursing_license_1.name = uploadInfo.file.name;
            this.uploaded_files.nursing_license_1.size = uploadInfo.file.size;
            this.uploaded_files.nursing_license_1.url = uploadInfo.file.url;
        } else if (uploadInfo.fileTag == 'Nurse License 2') {

            this.uploaded_files.nursing_license_2.date = uploadInfo.file.date;
            this.uploaded_files.nursing_license_2.id = uploadInfo.file.id;
            this.uploaded_files.nursing_license_2.is_completed_file = uploadInfo.file.is_completed_file;
            this.uploaded_files.nursing_license_2.name = uploadInfo.file.name;
            this.uploaded_files.nursing_license_2.size = uploadInfo.file.size;
            this.uploaded_files.nursing_license_2.url = uploadInfo.file.url;
        } else if (uploadInfo.fileTag == 'Nurse License 3') {

            this.uploaded_files.nursing_license_3.date = uploadInfo.file.date;
            this.uploaded_files.nursing_license_3.id = uploadInfo.file.id;
            this.uploaded_files.nursing_license_3.is_completed_file = uploadInfo.file.is_completed_file;
            this.uploaded_files.nursing_license_3.name = uploadInfo.file.name;
            this.uploaded_files.nursing_license_3.size = uploadInfo.file.size;
            this.uploaded_files.nursing_license_3.url = uploadInfo.file.url;
        }
        
        else if (uploadInfo.fileTag == 'Driver License') { this.uploaded_files.drivers_license = uploadInfo; }
        else if (uploadInfo.fileTag == 'Social Security') { this.uploaded_files.social_security = uploadInfo; }
        else if (uploadInfo.fileTag == 'TB Skin Test') { this.uploaded_files.tb_skin_test = uploadInfo; }

        else if (uploadInfo.fileTag == 'CPR Card') { this.uploaded_files.cpr_card = uploadInfo; }
        else if (uploadInfo.fileTag == 'BLS ACL') { this.uploaded_files.bls_acl_card = uploadInfo; }
        else if (uploadInfo.fileTag == 'Covid Vaccine') { this.uploaded_files.covid_vaccine_card = uploadInfo; }
        
        else if (uploadInfo.fileTag == 'ID Badge') { this.uploaded_files.id_badge_picture = uploadInfo; }
    },
    deleteFile(filetag) {

        for (let key in this.uploaded_files) {

            if (this.uploaded_files.hasOwnProperty(key)) {

                let file = this.uploaded_files[key];
                if (file.fileTag === filetag) {
                    
                    this.uploaded_files[key] = {};
                    break;
                }
            }
        }
    },
    showSnackbar(snackbarInfo) {

        this.snackbarMessage = snackbarInfo.message
        this.snackbarColor = snackbarInfo.color
        this.snackbarTimeout = snackbarInfo.timeout
        this.snackbar = true
    },
},
});