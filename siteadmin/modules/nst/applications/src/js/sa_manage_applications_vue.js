Vue.component('manage-applications-view', {
template: /*html*/`
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <v-app>
                <div class="card">
                    <div class="card-header">
                        <div style="margin-left: 30px; display: flex; flex-direction: row; justify-content: space-between; align-items: baseline;">

                            <h2 class="card-title">Manage Applications</h2>

                            <div style="display: flex; flex-direction: row;">

                                <v-btn color="success" style="margin-left: 10px;" @click="showSearch = true" v-show="!showSearch">
                                    <v-icon left>mdi-magnify</v-icon>
                                    <span class="v-btn__content">Search</span>
                                </v-btn>

                                <v-btn color="error" style="margin-left: 10px;" @click="applicationSearch" v-show="showSearch">
                                    <v-icon left>mdi-magnify</v-icon>
                                    <span class="v-btn__content">Clear Search</span>
                                </v-btn>

                                <v-btn color="primary" style="margin-left: 10px;" @click="showActive">Show Active</v-btn>

                                <v-btn color="primary" style="margin-left: 10px;" @click="showLicense">In License Verification</v-btn>
                                
                                <v-btn color="primary" style="margin-left: 10px;" @click="showDrugScreen">In Drug Screen</v-btn>
                                
                                <v-btn color="primary" style="margin-left: 10px;" @click="showBackgroundCheck">In Background Check</v-btn>
                                
                                <v-btn color="primary" style="margin-left: 10px;" @click="showCompleted">Process Completed</v-btn>

                                <v-btn color="error" style="margin-left: 10px;" @click="showAll">Show All</v-btn>

                                <v-btn color="success" style="margin-left: 10px;" @click="getApplications">
                                    <v-icon left>mdi-recycle</v-icon>
                                    <span class="v-btn__content">Refresh</span>
                                </v-btn>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <v-card-title v-show="showSearch">

                            <v-text-field
                                style="margin-left: 15px; margin-right: 15px;"
                                v-model="searchFirstName"
                                append-icon="mdi-magnify"
                                label="Search By First Name"
                                single-line
                                hide-details
                            ></v-text-field>

                            <v-text-field
                                style="margin-left: 15px; margin-right: 15px;"
                                v-model="searchLastName"
                                append-icon="mdi-magnify"
                                label="Search By Last Name"
                                single-line
                                hide-details
                            ></v-text-field>

                            <v-text-field
                                style="margin-left: 15px; margin-right: 15px;"
                                v-model="searchPhone"
                                append-icon="mdi-magnify"
                                label="Search By Phone Number"
                                single-line
                                hide-details
                            ></v-text-field>

                            <v-text-field
                                style="margin-left: 15px; margin-right: 15px;"
                                v-model="searchEmail"
                                append-icon="mdi-magnify"
                                label="Search By Email Address"
                                single-line
                                hide-details
                            ></v-text-field>

                            <v-text-field
                                style="margin-left: 15px; margin-right: 15px;"
                                v-model="searchState"
                                append-icon="mdi-magnify"
                                label="Search By State"
                                single-line
                                hide-details
                            ></v-text-field>
                        </v-card-title>

                        <v-data-table
                            class="table table-responsive-md full-width"
                            :headers="headers"
                            :items="filtered_items"
                            :sort-by="['applicant_id']"
			    :sort-desc="true"
                            multi-sort
                            :footer-props="{ itemsPerPageOptions: [20, 50, 100, -1] }"
                        >
                            <template v-slot:item="{ item }" :style="getRowStyle(item)">
                                <tr :style="getRowStyle(item)">
				    <td>
					<a href="item.link" class="blue--text" target="_blank">
					    {{ item.applicant_id }}
					</a>
				    </td>
                                    <td>
                                        <a :href="item.link" class="blue--text" target="_blank">
                                            {{ item.first_name }}
                                        </a>
                                    </td>
                                    <td>
                                        <a :href="item.link" class="blue--text" target="_blank">
                                            {{ item.last_name }}
                                        </a>
                                    </td>
                                    <td>
                                        <a :href="item.link" class="blue--text" target="_blank">
                                            {{ item.email }}
                                        </a>
                                    </td>
                                    <td>
                                        <a :href="item.link" class="blue--text" target="_blank">
                                            {{ item.phone }}
                                        </a>
                                    </td>
				    <td>
					<a :href="item.link" class="blue--text" target="_blank">
					    {{ item.city }}
					</a>
				    </td>
                                    <td>
                                        <a :href="item.link" class="blue--text" target="_blank">
                                            {{ item.state }}
                                        </a>
                                    </td>
                                    <td>
                                        <a :href="item.link" class="blue--text" target="_blank">
                                            {{ item.license_generated }}
                                        </a>
                                    </td>
                                    <td>
                                        <a :href="item.link" class="blue--text" target="_blank">
                                            {{ item.drug_screen_status }}
                                        </a>
                                    </td>
                                    <td>
                                        <a :href="item.link" class="blue--text" target="_blank">
                                            {{ item.background_check_started_date }}
                                        </a>
                                    </td>
                                    <td>
                                        <a :href="item.link" class="blue--text" target="_blank">
                                            {{ item.background_check_generated }}
                                        </a>
                                    </td>
                                    <td>
                                        <a :href="item.link" class="blue--text" target="_blank">
                                            {{ item.completed_at }}
                                        </a>
                                    </td>
                                    <td>
                                        <a :href="item.link" class="blue--text" target="_blank">
                                            {{ item.approved_at }}
                                        </a>
                                    </td>
                                    <td>
                                        <a :href="item.link" class="blue--text" target="_blank">
                                            {{ item.declined_at }}
                                        </a>
                                    </td>
                                </tr>
                            </template>
                        </v-data-table>
                        <v-snackbar
                            v-model="showSnackbar"
                            :timeout="3000"
                        >
                        {{ snackbarText }}
                        <v-btn
                            slot="action"
                            color="error"
                            text
                            @click="showSnackbar = false"
                        >
                            Close
                        </v-btn>
                      </v-snackbar>
                    </div>
                </div>
            </v-app>
        </div>
    </div>
</div>`,
data () {
    return {
        headers: [
	    {
		text: 'ID',
		align: 'start',
		value: 'applicant_id'
	    },
            {
                text: 'First Name',
                align: 'start',
                sortable: true,
                value: 'first_name'
            },
            {
                text: 'Last Name',
                sortable: true,
                value: 'last_name'
            },
            {
                text: 'Email',
                sortable: true,
                value: 'email'
            },
            {
                text: 'Phone',
                sortable: true,
                value: 'phone'
            },
	    {
		text: 'City',
		sortable: true,
		value: 'city'
	    },
            {
                text: 'State',
                sortable: true,
                value: 'state'
            },
            {
                text: 'Nurse License',
                sortable: true,
                value: 'license_generated'
            },
            {
                text: 'Screen Status',
                sortable: true,
                value: 'drug_screen_status'
            },
            {
                text: 'Background Check Started',
                sortable: true,
                value: 'background_check_started_date'
            },
            {
                text: 'Background Check Status',
                sortable: true,
                value: 'background_check_generated'
            },
            {
                text: 'Completed At',
                sortable: true,
                value: 'completed_at'
            },
            {
                text: 'Approved At',
                sortable: true,
                value: 'approved_at'
            },
            {
                text: 'Declined At',
                sortable: true,
                value: 'declined_at'
            },
        ],
        colors: {
            'Resolved': 'success',
            'Approved': 'success',
            'Change Requested': 'warning',
            'Unresolved': 'red',
        },
        searchFirstName: '',
        searchLastName: '',
        searchPhone: '',
        searchEmail: '',
        searchState: '',
        line_items: [],
        all_items: [],
        create_account: '',
        export_url: '',
        confirmDeleteDialog: false,
        userToDelete: null,
        showSearch: false,
        showSnackbar: false,
        snackbarText: '',
    }
},
computed: {

    filtered_items() {

        if (this.line_items) {

            return this.line_items.filter(item => {

                const firstNameMatch = item.first_name.toLowerCase().includes(this.searchFirstName?.toLowerCase() ?? '');
                const lastNameMatch = item.last_name.toLowerCase().includes(this.searchLastName?.toLowerCase() ?? '');
                const phoneMatch = item.phone.replace(/[^\d0-9]/g, '').includes(this.searchPhone?.replace(/[^\d0-9]/g, '') ?? '');
                const emailMatch = item.email.toLowerCase().includes(this.searchEmail?.toLowerCase() ?? '');
                const stateMatch = item.state.toLowerCase().includes(this.searchState?.toLowerCase() ?? '');

                return firstNameMatch && lastNameMatch && phoneMatch && emailMatch && stateMatch;
            });
        } else { return []; }
    }
},
mounted() {

    this.getApplications();
},
watch: {
},
methods: {

    getApplications() {
        
        modRequest.request('nurse.application.loadApplications', null, null, function(response) {
            if (response.success) {
                
                let applicantsList = response.applicants;
                applicantsList.forEach(applicant => {

                    applicant.license_generated = this.generateLicenseStatus(applicant.license_submitted, applicant.license_verified);
                    applicant.drug_screen_status = this.generateDrugScreenStatus(applicant.drug_screen_status, applicant.drug_screen_accepted, applicant.background_check_started_date);
                    applicant.background_check_generated = this.generateBackgroundCheckStatus(applicant.background_check_accepted, applicant.background_check_status, applicant.background_check_started_date, applicant.declined_at, applicant.drug_screen_report);
                });
                this.all_items = applicantsList;
                this.line_items = applicantsList.filter((applicant) => {
                    return applicant.is_active && !applicant.approved_at && !applicant.declined_at;
                });
            }
        }.bind(this));
    },
    generateLicenseStatus(license_submitted, license_verified) {

        if (license_submitted) {
            
            if (license_verified) { return 'Verified'; } else { return 'Needs Verification'; }
        } else { return 'Not Submitted'; }
    },
    generateDrugScreenStatus(drug_screen_status, drug_screen_accepted, declined_at, drug_screen_report) {

        if (drug_screen_status.toLowerCase() === 'pending') {

            return 'Pending...';

        } else if (drug_screen_status.toLowerCase() === 'consider') {

            if (drug_screen_accepted) {
                return 'Completed';
            } else if (declined_at) {
                return '';
            } else {
                return 'Needs Review';
            }

        } else if (drug_screen_status.toLowerCase() === 'completed') {
            
            if (drug_screen_report && drug_screen_report.result.toLowerCase() === 'consider') {
                return 'Needs Review';
            } else {
                return 'Completed';
            }

        } else if (drug_screen_status.toLowerCase() === 'clear') {

            return 'Completed';
        }
    },
    generateBackgroundCheckStatus(background_check_accepted, background_check_status, background_check_started_date, declined_at) {

        if (background_check_started_date) {

            if (background_check_accepted === true) {
                return 'Completed';
            }

            if (background_check_status.toLowerCase() === 'pending') {
                return 'Processing...';
            }

            if (background_check_status.toLowerCase() === 'complete' && !declined_at) {
                return 'Needs Review';
            }
        }

        return '';
    },
    getRowStyle(item) {

        if (!item) { return {}; }
        
        let color = null;
        if (item.background_check_generated === 'Needs Review') {
            color = '#F3E5F5';
        } else if (item.license_generated === 'Needs Verification') {
            color = '#E8EAF6';
        } else if (item.drug_screen_status == 'Needs Review') {
            color = '#E3F2FD';
        } else if (item.completed_at && !item.approved_at && !item.declined_at) {
            color = '#E0F2F1';
        }

        return color ? { backgroundColor: color + '!important'} : {};
    },
    showActive() {
            
        this.line_items = this.all_items.filter((applicant) => {
            return applicant.is_active;
        });
    },
    showLicense() {

        this.line_items = this.all_items.filter((applicant) => {
            return applicant.license_generated !== 'Verified';
        });
    },
    showDrugScreen() {

        this.line_items = this.all_items.filter((applicant) => {
            return applicant.drug_screen_status !== 'Accepted' && applicant.drug_screen_status !== 'Declined' && applicant.license_generated === 'Verified';
        });
    },
    showBackgroundCheck() {

        this.line_items = this.all_items.filter((applicant) => {
            return applicant.background_check_generated !== 'Accepted' && applicant.background_check_generated !== 'Declined' && applicant.drug_screen_status === 'Accepted' && applicant.license_generated === 'Verified';
        });
    },
    showCompleted() {

        this.line_items = this.all_items.filter((applicant) => {
            return applicant.submitted_at && applicant.approved_at === null && applicant.declined_at === null;
        });
    },
    showAll() {
        this.line_items = this.all_items;
    },
    // showConfirmDeleteDialog(user) {

    //     this.userToDelete = user;
    //     this.confirmDeleteDialog = true;
    // },
    // deleteUser() {

    //     data = {                    
    //         member_id: this.userToDelete.member_id
    //     }
        
    //     modRequest.request('sa.member.delete_nst_member_mod', null, data, function(response) {
    //         if (response.success) {

    //             this.confirmDeleteDialog = false;
    //             this.snackbarText = 'Nurse deleted successfully.';
    //             this.showSnackbar = true;

    //             this.getNurses();
    //         } else {
                
    //             this.confirmDeleteDialog = false;
    //             this.snackbarText = 'Error deleting nurse. Please try again.';
    //             this.showSnackbar = true;

    //             console.log("Nurse deletion error message: ", response.error);
    //         }
    //     }.bind(this));
    // },
    applicationSearch() {

        this.searchFirstName = '';
        this.searchLastName = '';
        this.searchPhone = '';
        this.searchEmail = '';
        this.searchState = '';

        this.showSearch = false;
    }
},
});
