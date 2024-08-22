Vue.component('nurse-contact-info-view', {
    template:
    /*html*/`
    <v-app>
        <nst-overlay :loading="loading"></nst-overlay>
        <nst-error-notification 
                v-if="error" 
                :error="error"></nst-error-notification>
        <p>* Changes not final until saved at the bottom of the page
        <h5 class="primary--text bolder smaller">Emails</h5>
        <div class="col-sx-12">
            <div class="table-responsive dataTables_wrapper">
                <table id="sample-table-1" class="table table-striped table-bordered table-hover" style="margin-bottom: 0px;">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Type</th>
                            <th>Primary</th>
                            <th>Active</th>
                            <td @click="addEmail" style="text-align: right;">
                                <i class="primary--text fa fa-plus bigger-125"></i>
                            </td>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-show="showAddEmail">
                            <td>
                                <v-text-field
                                        v-model="newEmail.name"
                                        label="Email"
                                ></v-text-field>
                            </td> 
                            <td>
                                <v-autocomplete
                                        v-model="newEmail.type"
                                        :items="type"
                                        label="Type"
                                ></v-autocomplete>
                            </td> 
                            <td>
                                <v-autocomplete
                                        v-model="newEmail.primary"
                                        :items="['Yes','No']"
                                        label="Is Primary"
                                ></v-autocomplete>
                            </td> 
                            <td>
                                <v-autocomplete
                                        v-model="newEmail.active"
                                        :items="['Yes','No']"
                                        label="Is Active"
                                ></v-autocomplete>
                            </td> 
                            <td @click="pushEmail" style="text-align: right; margin-right: 5px;"><i class="primary--text fa fa-check bigger-125"></i></td>
                        </tr>
                    </tbody>
                    <tbody v-for="(email, index) in emails">
                        <tr>
                            <td>{{ email.name }}</td>
                            <td>{{ email.type }}</td>
                            <td>{{ email.primary }}</td>
                            <td>{{ email.active }}</td>
                            <td @click="editEmail(index)" style="text-align: right; margin-right: 5px;"><i class="primary--text fa fa-edit bigger-125"></i></td>
                        </tr>
                        <tr v-show="openEditEmail === index">
                            <td>
                                <v-text-field
                                        v-model="email.name"
                                        label="Email"
                                ></v-text-field>
                            </td> 
                            <td>
                                <v-autocomplete
                                        v-model="email.type"
                                        :items="type"
                                        label="Type"
                                ></v-autocomplete>
                            </td> 
                            <td>
                                <v-autocomplete
                                        v-model="email.primary"
                                        :items="['Yes','No']"
                                        label="Is Primary"
                                ></v-autocomplete>
                            </td> 
                            <td>
                                <v-autocomplete
                                        v-model="email.active"
                                        :items="['Yes','No']"
                                        label="Is Active"
                                ></v-autocomplete>
                            </td> 
                            <td style="text-align: right; margin-right: 5px;">
                                <div @click="editEmail(index)">
                                    <i class="primary--text fa fa-check bigger-125"></i>
                                </div><hr>
                                <div @click="deleteEmail(index)">
                                    <i class="primary--text fa fa-trash"></i>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div><br><hr>

        <h5 class="primary--text bolder smaller">Phone Numbers</h5>
        <div class="col-sx-12">
            <div class="table-responsive dataTables_wrapper">
                <table id="sample-table-1" class="table table-striped table-bordered table-hover" style="margin-bottom: 0px;">
                    <thead>
                        <tr>
                            <th>Number</th>
                            <th>Type</th>
                            <th>Primary</th>
                            <th>Active</th>
                            <td @click="addPhone" style="text-align: right;"><i class="primary--text fa fa-plus bigger-125"></i></td>
                        </tr>
                    </thead>
                    <tbody v-for="(phone, index) in phone_numbers">
                        <tr v-show="showAddPhone">
                            <td>
                                <v-text-field
                                        v-model="newPhone.phone"
                                        label="Phone Number"
                                ></v-text-field>
                            </td> 
                            <td>
                                <v-autocomplete
                                        v-model="newPhone.type"
                                        :items="type"
                                        label="Type"
                                ></v-autocomplete>
                            </td> 
                            <td>
                                <v-autocomplete
                                        v-model="newPhone.primary"
                                        :items="['Yes','No']"
                                        label="Is Primary"
                                ></v-autocomplete>
                            </td> 
                            <td>
                                <v-autocomplete
                                        v-model="newPhone.active"
                                        :items="['Yes','No']"
                                        label="Is Active"
                                ></v-autocomplete>
                            </td> 
                            <td @click="pushPhone" style="text-align: right; margin-right: 5px;"><i class="primary--text fa fa-check bigger-125"></i></td>
                        </tr>                    
                        <tr>
                            <td>{{ phone.phone }}</td>
                            <td>{{ phone.type }}</td>
                            <td>{{ phone.primary }}</td>
                            <td>{{ phone.active }}</td>
                            <td @click="editPhone(index)" style="text-align: right; margin-right: 5px;"><i class="primary--text fa fa-edit bigger-125"></i></td>
                        </tr>
                        <tr v-show="openEditPhone === index">
                            <td>
                                <v-text-field
                                        v-model="phone.phone"
                                        label="Phone Number"
                                ></v-text-field>
                            </td> 
                            <td>
                                <v-autocomplete
                                        v-model="phone.type"
                                        :items="type"
                                        label="Type"
                                ></v-autocomplete>
                            </td> 
                            <td>
                                <v-autocomplete
                                        v-model="phone.primary"
                                        :items="['Yes','No']"
                                        label="Is Primary"
                                ></v-autocomplete>
                            </td> 
                            <td>
                                <v-autocomplete
                                        v-model="phone.active"
                                        :items="['Yes','No']"
                                        label="Is Active"
                                ></v-autocomplete>
                            </td> 
                            <td style="text-align: right; margin-right: 5px;">
                                <div @click="editPhone(index)">
                                    <i class="primary--text fa fa-check bigger-125"></i>
                                </div><hr>
                                <div @click="deletePhone(index)">
                                    <i class="primary--text fa fa-trash"></i>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </v-app>
    `,
    props: [
        'id'
    ],
    data: function() {
        return {
            error: null,
            loading: false,
            emails: [],
            phone_numbers: [],
            type: ['Personal', 'Work', 'Secondary', 'Other'],
            showAddEmail: false,
            openEditEmail: '',
            newEmail: {
                name: '',
                type: '',
                primary: '',
                active: ''
            },
            showAddPhone: false,
            openEditPhone: '',
            newPhone: {
                phone: '',
                type: '',
                primary: '',
                active: ''
            }
        };
    },
    created() {
        this.loadNurseContactInfo();
    },
    mounted() {
        this.$root.$on('saveMemberData', function() {
            this.saveNurseContactInfo()
        }.bind(this));
    },
    methods: {
        loadNurseContactInfo() {
            var data = {
                id: this.id,
            };
            this.loading = true;
            modRequest.request('sa.member.load_nurse_contact_info', {}, data, function(response) {
                if(response.success) {
                    this.emails = response.emails;
                    // for (let i = 0; i < this.emails.length; i++) { this.openEditEmail.push(false); }
                    this.phone_numbers = response.phone_numbers;
                    // for (let i = 0; i < this.phone_numbers.length; i++) { this.openEditPhone.push(false); }
                    this.error = null;
                    this.loading = false;
                } else {
                    console.log('Error');
                    console.log(response);
                    $.growl.error({ title: "Error!", message: "Error saving changes. Changes not saved.", size: "large" });
                }
                this.loading = false;
            }.bind(this), function(response) {
                console.log('Failed');
                console.log(response);
            });
        },
        saveNurseContactInfo() {
            var data = {
                id: this.id,
                emails: this.emails,
                phone_numbers: this.phone_numbers
            };
            this.loading = true;
            modRequest.request('sa.member.save_nurse_contact_info', {}, data, function(response) {
                if (response.success) {
                    this.loading = false;
                } else {
                    console.log('Error');
                    console.log(response);
                } this.loading = false;
            }.bind(this), function(response) {
                console.log('Failed');
                console.log(response);
            });
        },
        addEmail() {
            this.showAddEmail = !this.showAddEmail;
        },
        pushEmail() {
            if (this.newEmail.type == '' || this.newEmail.primary == '' || this.newEmail.active == '') {
                $.growl.error({ title: "Error!", message: "All fields must be entered to add email", size: "large" });
                return;
            } else { this.error = null; }

            this.emails.push(this.newEmail);
            this.showAddEmail = false;
        },
        editEmail(index) {
            if (this.openEditEmail === index) {
                this.openEditEmail = -1;
            } else { this.openEditEmail = index; }
        },
        deleteEmail(index) {
            this.emails.splice(index, 1);
            this.openEditEmail = -1;
        },
        addPhone() {
            this.showAddPhone = !this.showAddPhone;
        },
        pushPhone() {

            const phoneLength = this.newPhone.phone.replace(/\D/g, '');
            
            if (this.newPhone.type == '' || this.newPhone.primary == '' || this.newPhone.active == '') {
                $.growl.error({ title: "Error!", message: "All fields must be entered to add phone", size: "large" });
                return;
            } else if (phoneLength.length != 10) {
                $.growl.error({ title: "Error!", message: "Phone number must be 10 digits", size: "large" });
                return;
            } else { this.error = null; }

            this.phone_numbers.push(this.newPhone);
            this.showAddPhone = false;
        },
        editPhone(index) {
            if (this.openEditPhone === index) {
                this.openEditPhone = -1;
            } else { this.openEditPhone = index; }
        },
        deletePhone(index) {
            this.phone_numbers.splice(index, 1);
            this.openEditPhone = -1;
        },
    }
})