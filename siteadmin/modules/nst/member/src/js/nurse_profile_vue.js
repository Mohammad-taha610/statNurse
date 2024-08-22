window.addEventListener('load', function () {
    Vue.use(window.VueTheMask);

    Vue.component('nurse-profile', {
        template: /*html*/`
            <div class="container-fluid" >
                <v-app v-model="nurse" class="nurse-profile-page">
                    <div class="modal fade" id="return-modal">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Are you sure?</h5>
                                    <button type="button" class="close" data-dismiss="modal"><span>×</span></button>
                                </div>
                                <div class="modal-body">{{ modal_body }}</div>
                                <div class="modal-footer">
                                    <a class="btn btn-light grey--text" data-dismiss="modal">Cancel</a>
                                    <a v-on:click="toggle" class="btn btn-success white--text" data-dismiss="modal">Save changes</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-xl-8 col-xxl-8">
                            <div class="row">
                                <div class="col-xl-12">
                                    <div class="card profile-card">
                                        <div class="card-header flex-wrap border-0 pb-0">
                                            <h3 class="fs-24 text-black font-w600 mr-auto mb-2 pr-3">Nurse's Profile</h3>
                                            <button
                                                v-bind:class="btn_class"
                                                data-toggle="modal"
                                                data-target="#return-modal"
                                                v-on:click="refreshPage"
                                            >{{ btn_text }}</button>
                                        </div>
                                        <div class="card-body">
                                            <div class="mb-5">
                                                <div class="title mb-4">
                                                    <span class="fs-18 text-black font-w600">General</span>
                                                </div>
                                            </div>
                                            <div class="d-block">
                                                <div class="row bdr-bottom">
                                                    <div class="col-sm-5">
                                                        <div class="form-group bdr-bottom">
                                                            <label>First Name</label>
                                                            <span class="form-control no-border">{{ nurse.first_name }}</span>
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-1">
                                                        <div class="form-group bdr-bottom">
                                                            <label>MI</label>
                                                            <span class="form-control no-border">{{ nurse.middle_initial }}</span>
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-6">
                                                        <div class="form-group bdr-bottom">
                                                            <label>Last Name</label>
                                                            <span class="form-control no-border">{{ nurse.last_name }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row bdr-bottom">
                                                    <template v-if="nurse.birthday">
                                                    <div class="col-sm-6">
                                                        <div class="form-group bdr-bottom">
                                                            <label>Birthday</label>
                                                            <span class="form-control no-border">{{ nurse.birthday }}</span>
                                                        </div>
                                                    </div>
                                                    </template>
                                                    <template v-if="nurse.social_security">
                                                    <div class="col-sm-6">
                                                        <div class="form-group bdr-bottom">
                                                            <label>Social Security #</label>
                                                            <span class="form-control no-border"><input v-mask="'•••-••-####'" v-model="nurse.social_security" placeholder="•••-••-••••" /></span>
                                                        </div>
                                                    </div>
                                                    </template>
                                                    <template v-if="nurse.covid_vaccinated === 'yes'">
                                                    <div class="col-sm-6">
                                                        <div class="form-group bdr-bottom">
                                                            <label>Covid Vaccinated?</label>
                                                            <span class="form-control no-border">Yes</span>
                                                        </div>
                                                    </div>
                                                    </template>
                                                </div>
                                                <template v-if="nurse.covid_vaccinated === 'no' && nurse.covid_exemption">
                                                <div class="row bdr-bottom">
                                                    <div class="col">
                                                        <div class="form-group bdr-bottom">
                                                            <label>Covid Exemption</label>
                                                            <span class="form-control no-border">{{ nurse.covid_exemption }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row bdr-bottom">
                                                    <div class="col">
                                                        <div class="form-group bdr-bottom">
                                                            <label>Covid Vaccinated?</label>
                                                            <span class="form-control no-border">No</span>
                                                        </div>
                                                    </div>
                                                </div>
                                                </template>
                                            </div>
                                            <template v-if="nurse.emergency_contact_one || nurse.emergency_contact_two">
                                            <div class="mb-5 mt-4">
                                                <div class="title mb-4">
                                                    <span class="fs-18 text-black font-w600">Emergency Contacts</span>
                                                </div>
                                            </div>
                                            <div class="d-block">
                                                <template v-if="nurse.emergency_contact_one">
                                                <div class="row bdr-bottom">
                                                    <div class="col-sm-6">
                                                        <div class="form-group bdr-bottom">
                                                            <label>Name</label>
                                                            <span class="form-control no-border">{{ nurse.emergency_contact_one.first_name }} {{ nurse.emergency_contact_one.last_name }}</span>
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-6">
                                                        <div class="form-group bdr-bottom">
                                                            <label>Phone</label>
                                                            <span class="form-control no-border">{{ nurse.emergency_contact_one.phone_number }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                                </template>
                                                <template v-if="nurse.emergency_contact_two">
                                                <div class="row bdr-bottom">
                                                    <div class="col-sm-6">
                                                        <div class="form-group bdr-bottom">
                                                            <label>Name</label>
                                                            <span class="form-control no-border">{{ nurse.emergency_contact_two.first_name }} {{ nurse.emergency_contact_two.last_name }}</span>
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-6">
                                                        <div class="form-group bdr-bottom">
                                                            <label>Phone</label>
                                                            <span class="form-control no-border">{{ nurse.emergency_contact_two.phone_number }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                                </template>
                                            </div>
                                            </template>
                                            <div class="mb-5 mt-4">
                                                <div class="title mb-4">
                                                    <span class="fs-18 text-black font-w600">Files</span>
                                                </div>
                                            </div>
                                            <div class="d-block">
                                                <template>
                                                    <v-row>
                                                        <a v-for="file in files" :href="file.route" target="_blank" class="mt-3 mr-3">
                                                            <template>
                                                                <v-card class="member-file-card">
                                                                    <v-card-text>
                                                                        <div class="member-file-icon">
                                                                            <v-icon color="primary">mdi-file</v-icon>
                                                                        </div>
                                                                        <div class="member-file-tag-container mt-1">
                                                                            <span class="member-file-tag-name">{{ file.tag.name }}</span>
                                                                        </div>
                                                                    </v-card-text>
                                                                </v-card>
                                                            </template>
                                                        </a>
                                                    </v-row>
                                                </template>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-4 col-xxl-4">
                            <div class="row">
                                <div class="col-xl-12">
                                    <div class="card flex-lg-column flex-md-row">
                                        <div class="card-body card-body text-center border-bottom profile-bx">
                                            <div class="profile-image mb-4">
                                                <template v-if="nurse.avatar">
                                                    <img v-bind:src="'url(' + nurse.avatar + ')'" class="rounded-circle">
                                                </template>
                                                <template v-else>
                                                    <img src="/themes/nst/assets/images/profile_placeholder.png" class="rounded-circle">
                                                </template>
                                            </div>
                                            <h4 class="fs-22 text-black mb-1">{{ nurse.first_name + ' ' + nurse.last_name }}</h4>
                                            <p class="mb-4">{{ nurse.credentials }}</p>
                                            <div class="row">
                                                <div class="col-6" v-if="nurse.payment_due">
                                                    <div class="border rounded p-2">
                                                        <h4 class="fs-22 text-black font-w600">{{ nurse.payment_due }}</h4>
                                                        <span class="text-black">Payment Due</span>
                                                    </div>
                                                </div>
                                                <div class="col-6" v-if="nurse.unresolved_pay">
                                                    <div class="border rounded p-2">
                                                        <h4 class="fs-22 text-red font-w600">{{ nurse.unresolved_pay }}</h4>
                                                        <span class="text-red">Unresolved Pay</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-body border-left nurse-contact-info" v-if="nurse.phone || nurse.email">
                                            <div class="d-flex mb-3 align-items-center nurse-phone" v-if="nurse.phone">
                                                <a class="contact-icon mr-3" href="#"><i class="fa fa-phone" aria-hidden="true"></i></a>
                                                <span class="text-black">{{ nurse.phone }}</span>
                                            </div>
                                            <div class="d-flex mb-3 align-items-center nurse-email" v-if="nurse.email">
                                                <a class="contact-icon mr-3" href="#"><i class="las la-envelope"></i></a>
                                                <span class="text-black">{{ nurse.email }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </v-app>
            </div>`,
        props: {
            id: Number,
            filesRoute: String,},
        data() {
            return {
                btn_class: '',
                btn_text: '',
                dialog: false,
                modal_body: '',
                payment_due: '',
                is_blocked: false,
                unresolved_pay: 1,
                first_name: '',
                middle_initial: '',
                last_name: '',
                credentials: '',
                nurse: {},
                files: [],
            }
        },
        created: function () {
            this.payment_due = '4/19/2021'
            this.unresolved_pay = 4000;

            this.loadNurse();
            this.loadNurseFiles();
        },
        methods: {
            toggle: function () {
                let data = {
                    id: this.id
                };
                if (!this.is_blocked) {

                    modRequest.request('provider.block_nurse', {}, data, function (response) {
                        if (response.success) {
                            this.btn_text = 'BLOCKED';
                            this.btn_class = 'btn btn-danger btn-rounded mr-3 mb-2';
                            this.modal_body = 'Do you wish to remove this nurse from your \'Do Not Return\' list?';
                            this.is_blocked = !this.is_blocked;
                        } else {
                            console.log('Error');
                            console.log(response);
                        }
                    }.bind(this), function (response) {
                        console.log('Failed');
                        console.log(response);
                    });
                } else {
                    modRequest.request('provider.unblock_nurse', {}, data, function (response) {
                        if (response.success) {
                            this.btn_text = 'BLOCK NURSE';
                            this.btn_class = 'btn btn-dark btn-rounded mb-2';
                            this.modal_body = 'Do you wish to add this nurse to your \'Do Not Return\' list?';
                            this.is_blocked = !this.is_blocked;
                        } else {
                            console.log('Error');
                            console.log(response);
                        }
                    }.bind(this), function (response) {
                        console.log('Failed');
                        console.log(response);
                    });
                }
            },
            isUndefined: function(obj) {
                return obj === void 0;
            },
            loadNurse: function () {
                const data = {
                    id: this.id,
                };
                modRequest.request('nurse.profile', null, data,
                    function (response) {
                        //console.log(response);
                        if (response.success) {
                            let medical_history = '',
                            r_nurse = '';
                            if (response.nurse.application !== '') {
                                if (!this.isUndefined(response.nurse.application.part_one.medical_history)) {
                                    medical_history = JSON.parse(response.nurse.application.part_one.medical_history);
                                }
                                if (!this.isUndefined(response.nurse.application.part_one.nurse)) {
                                    r_nurse = JSON.parse(response.nurse.application.part_one.nurse);
                                }
                            }
                            let emergency_contact_one = undefined;
                            let emergency_contact_two = undefined;

                            // W.O. 12/15/2022
                            // Moved some logic here for managing emergency contact data objects as there was a bug introduced that does not save them the same as before
                            if (response.nurse.application.part_one !== undefined) {
                                if (typeof response.nurse.application.part_one.emergency_contact_one === 'object'){
                                    emergency_contact_one = response.nurse.application.part_one.emergency_contact_one;
                                } else {
                                    emergency_contact_one = JSON.parse(response.nurse.application.part_one.emergency_contact_one);
                                }
                                
                                if (typeof response.nurse.application.part_one.emergency_contact_two === 'object'){
                                    emergency_contact_two = response.nurse.application.part_one.emergency_contact_two;
                                } else {
                                    emergency_contact_two = JSON.parse(response.nurse.application.part_one.emergency_contact_two);
                                }
                            }

                            this.nurse = {
                                first_name: response.nurse.first_name,
                                middle_initial: response.nurse.middle_initial,
                                last_name: response.nurse.last_name,
                                email: response.nurse.email,
                                phone: response.nurse.phone,
                                credentials: response.nurse.credentials,
                                avatar: response.nurse.avatar,
                                payment_due: response.nurse.payment_due,
                                unresolved_pay: response.nurse.unresolved_pay,
                                emergency_contact_one: emergency_contact_one !== undefined ? emergency_contact_one : '',
                                emergency_contact_two: emergency_contact_two !== undefined ? emergency_contact_two : '',
                                birthday: response.nurse.birthday ? response.nurse.birthday : '',
                                covid_vaccinated: medical_history !== '' && medical_history !== null ? medical_history.covid : '',
                                covid_exemption: medical_history !== '' && medical_history !== null ? medical_history.covid_exemption : '',
                                social_security: r_nurse !== '' && r_nurse !== null ? r_nurse.socialsecurity_number : ''


                            };
                            console.log("NURSE: ", this.nurse);
                            console.log("APPLICATION: ", response.nurse.application);
                            this.is_blocked = response.nurse.is_blocked;

                            if (this.is_blocked) {
                                this.btn_text = 'BLOCKED';
                                this.btn_class = 'btn btn-danger btn-rounded mr-3 mb-2';
                                this.modal_body = 'Do you wish to remove this nurse from your \'Do Not Return\' list?';
                            } else {
                                this.btn_text = 'BLOCK NURSE';
                                this.btn_class = 'btn btn-dark btn-rounded mb-2';
                                this.modal_body = 'Do you wish to add this nurse to your \'Do Not Return\' list?';
                            }
                        }
                    }.bind(this),
                    function (error) {
                        console.log('error');
                    }
                );
            },
            refreshPage() {
                this.loadNurse();
            },
            loadNurseFiles () {

                data = {
                    id: this.id
                }
                
                modRequest.request('provider.get_nurse_files', null, data, function(response) {
                    if (response.success) {
                        this.files = response.files;
                    }
                }.bind(this));
            }
        }
    });
});
