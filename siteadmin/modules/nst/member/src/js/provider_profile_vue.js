window.addEventListener('load', function() {
    Vue.component('provider-profile-view', {
        template:
        `
            <div class="container-fluid">
                <div class="row"> 
                    <div class="col-12 col-sm-9">
                        <div class="page-titles">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="javascript:void(0)">Dashboard</a></li>
                                <li class="breadcrumb-item active"><a href="javascript:void(0)">Profile</a></li>
                            </ol>
                        </div>
                    </div>
                    <div class="col-12 col-sm-3"> 
                    <v-btn
                        text
                        v-if="!editing"
                        color="primary"
                        @click="editing = !editing"
                        class="pull-right"
                    >Edit Information</v-btn>
                    <v-btn
                        text
                        v-if="editing"
                        color="primary"
                        @click="saveProviderInfo"
                        class="pull-right"
                    >Save</v-btn>
                    <v-btn
                        text
                        v-if="editing"
                        @click="resetInfo"
                        class="pull-right"
                    >Cancel</v-btn>
                    </div>
                </div>
                <!-- row -->
                <div class="row">
                    <div class="col-lg-12">
                        <div class="profile card card-body px-3 pt-0 pb-0">
                            <div class="profile-head">
                                <div class="profile-info">
                                    <div class="profile-photo">
                                        <img src="/themes/nst/assets/images/profile/profile.png" class="img-fluid rounded-circle" alt="">
                                    </div>
                                    <div class="profile-details" v-bind="provider">
                                        <div class="profile-name px-3 pt-2">
                                            <h4 class="text-primary mb-0" v-if="!editing">{{ provider.company }}</h4>
                                            <v-text-field type="text" v-if="editing" label="Provider" v-model="provider.company"></v-text-field>
                                            <p v-if="!editing">Provider</p>
                                        </div>
                                        <div class="profile-email px-2 pt-2">
                                            <h4 class="text-muted mb-0" v-if="!editing"><a :href="'mailto:' + provider.email">{{ provider.email }}</a></h4>
                                            <v-text-field type="text" v-if="editing" label="Email" v-model="provider.email"></v-text-field>
                                            <p v-if="!editing">Email</p>
                                        </div>
                                        <div class="profile-email px-2 pt-2">
                                            <h4 class="text-muted mb-0" v-if="!editing"><a href="">{{ provider.phone }}</a></h4>
                                            <v-text-field type="text" v-if="editing" label="Phone" v-model="provider.phone"></v-text-field>
                                            <p v-if="!editing">Phone Number</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-xl-8">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title">Upcoming Shifts</h4>
                            </div>
                            <div class="card-body">
                                <v-data-table
                                    class="table table-responsive-md"
                                    :headers="headers"
                                    :items="upcoming_shifts"
                                    multi-sort
                                >
                                    <template v-slot:item.date="{ item }">
                                        {{ item.date }}
                                    </template>
                                    <template v-slot:item.start_time="{ item }">
                                        {{ item.start_time }}
                                    </template>
                                    <template v-slot:item.end_time="{ item }">
                                        {{ item.end_time }}
                                    </template>
                                    <template v-slot:item.nurse_name="{ item }">
                                        <a v-if="item.nurse_name != 'Unassigned'" v-bind:href="item.nurse_profile" class="blue--text" target="_blank">
                                            {{ item.nurse_name }}
                                        </a>
                                        <span v-else>{{ item.nurse_name }}</span>
                                    </template>
                                    <template v-slot:item.actions="{ item }">
                                        <a
                                            :href="item.shift_route" target="_blank"
                                        >
                                            <v-icon
                                                color="blue"
                                            >
                                                mdi-square-edit-outline
                                        </v-icon>
                                        </a>
                                    </template>
                                </v-data-table>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-4">
                        <div class="card">
                            <div class="card-body">
                                <div class="profile-tab">
                                    <div class="custom-tab-1">
                                        <ul class="nav nav-tabs">
                                            <li class="nav-item"><a href="#app-details" data-toggle="tab" class="nav-link active show">Application Details</a></li>
                                            <li class="nav-item"><a href="#payroll" data-toggle="tab" class="nav-link">Payroll</a></li>
                                        </ul>
                                        <div class="tab-content">
                                            <div id="app-details" class="tab-pane fade active show">
                                                <div class="my-post-content pt-3">
                                                    <div class="profile-personal-info">
                                                        <div class="row mb-2">
                                                            <div class="col-3">
                                                                <h5 class="f-w-500">Name:
                                                                </h5>
                                                            </div>
                                                            <div class="col-9"><span>Jane Doe</span>
                                                            </div>
                                                        </div>
                                                        <div class="row mb-2">
                                                            <div class="col-3">
                                                                <h5 class="f-w-500">Email:
                                                                </h5>
                                                            </div>
                                                            <div class="col-9"><span>example@examplel.com</span>
                                                            </div>
                                                        </div>
                                                        <div class="row mb-2">
                                                            <div class="col-3">
                                                                <h5 class="f-w-500">Availability:</h5>
                                                            </div>
                                                            <div class="col-9"><span>Full Time</span>
                                                            </div>
                                                        </div>
                                                        <div class="row mb-2">
                                                            <div class="col-3">
                                                                <h5 class="f-w-500">Age:
                                                                </h5>
                                                            </div>
                                                            <div class="col-9"><span>27</span>
                                                            </div>
                                                        </div>
                                                        <div class="row mb-2">
                                                            <div class="col-3">
                                                                <h5 class="f-w-500">Location:</h5>
                                                            </div>
                                                            <div class="col-9"><span>Rosemont Avenue Melbourne,
                                                                    Florida</span>
                                                            </div>
                                                        </div>
                                                        <div class="row mb-2">
                                                            <div class="col-3">
                                                                <h5 class="f-w-500">Experience:</h5>
                                                            </div>
                                                            <div class="col-9"><span>7 Years</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div id="payroll" class="tab-pane fade">
                                                <div class="table-responsive pt-3">
                                                    <table class="table">
                                                        <thead>
                                                            <tr>
                                                                <th>Payout Date</th>
                                                                <th>Amount</th>
                                                                <th>Status</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <tr>
                                                                <td>3/31/2021</td>
                                                                <td>$123.45</td>
                                                                <td>Pending</td>
                                                            </tr>
                                                            <tr>
                                                                <td>3/31/2021</td>
                                                                <td>$123.45</td>
                                                                <td>Complete</td>
                                                            </tr>
                                                            <tr>
                                                                <td>3/31/2021</td>
                                                                <td>$123.45</td>
                                                                <td>Complete</td>
                                                            </tr>
                                                            <tr>
                                                                <td>3/31/2021</td>
                                                                <td>$123.45</td>
                                                                <td>Complete</td>
                                                            </tr>
                                                            <tr>
                                                                <td>3/31/2021</td>
                                                                <td>$123.45</td>
                                                                <td>Complete</td>
                                                            </tr>
                                                            <tr>
                                                                <td>3/31/2021</td>
                                                                <td>$123.45</td>
                                                                <td>Complete</td>
                                                            </tr>                                        
                                                        </tbody>
                                                    </table>
                                                </div>
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
        props: [],
        data () {
            return {
                original_provider: {
                    name: '',
                    email: '',
                    phone: '',
                    company: ''
                },
                provider: {
                    name: '',
                    email: '',
                    phone: '',
                    company: ''
                },
                upcoming_shifts: [],
                headers: [
                    {
                        text: 'Date',
                        align: 'start',
                        sortable: true,
                        value: 'date'
                    },
                    {
                        text: 'Start Time',
                        align: 'start',
                        sortable: true,
                        value: 'start_time'
                    },
                    {
                        text: 'End Time',
                        align: 'start',
                        sortable: true,
                        value: 'end_time'
                    },
                    {
                        text: 'Nurse',
                        align: 'start',
                        sortable: true,
                        value: 'nurse_name'
                    },
                    {
                        text: 'Actions',
                        align: 'start',
                        sortable: false,
                        value: 'actions'
                    },
                ],
                editing: false
            };
        },
        mounted () {
            this.loadProfileData();
            this.loadUpcomingProviderShifts();
        },
        methods: {
            loadProfileData() {
                modRequest.request('provider.load_profile_data', {}, {}, function(response) {
                    if(response.success) {
                        let p = response.provider;
                        this.provider.name = p.name;
                        this.provider.company = p.company;
                        this.provider.email = p.email;
                        this.provider.phone = p.phone;
                        this.original_provider.name = p.name;
                        this.original_provider.company = p.company;
                        this.original_provider.email = p.email;
                        this.original_provider.phone = p.phone;
                    } else {
                        console.log('Error');
                        console.log(response);
                    }
                }.bind(this), function(response) {
                    console.log('Failed');
                    console.log(response);
                });
            },
            loadUpcomingProviderShifts() {
                modRequest.request('provider.load_upcoming_provider_shifts', {}, {}, function(response) {
                    if(response.success) {
                        let upcoming_shifts = [];
                        for(let i = 0; i < response.shifts.length; i++) {
                            let shift = response.shifts[i];
                            upcoming_shifts.push({
                                id: shift.id,
                                shift_name: shift.shift_name,
                                shift_route: shift.shift_route,
                                nurse_name: shift.nurse_name,
                                nurse_profile: shift.nurse_profile,
                                start_time: shift.start_time,
                                end_time: shift.end_time,
                                date: shift.date,
                                is_recurrence: shift.is_recurrence
                            })
                        }

                        this.upcoming_shifts = upcoming_shifts;
                    } else {
                        console.log('Error');
                        console.log(response);
                    }
                }.bind(this), function(response) {
                    console.log('Failed');
                    console.log(response);
                });
            },
            saveProviderInfo() {
                let data = {
                    company: this.provider.company,
                    email: this.provider.email,
                    phone: this.provider.phone
                }
                
                modRequest.request('provider.save_provider_info', {}, data, function(response) {
                    if(response.success) {
                        this.original_provider.name = this.provider.name;
                        this.original_provider.company = this.provider.company;
                        this.original_provider.email = this.provider.email;
                        this.original_provider.phone = this.provider.phone;
                        this.editing = false;
                    } else {
                        console.log('Error');
                        console.log(response);
                    }
                }.bind(this), function(response) {
                    console.log('Failed');
                    console.log(response);
                });
            },
            resetInfo() {
                this.provider.name = this.original_provider.name;
                this.provider.company = this.original_provider.company;
                this.provider.email = this.original_provider.email;
                this.provider.phone = this.original_provider.phone;
                this.editing = false;
            }
        }
    });
})