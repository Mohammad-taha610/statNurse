window.addEventListener('load', function() {
    Vue.component('manage-nurses-view', {
        template: /*html*/`
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <v-app>
                        <div class="card">
                            <div class="card-header">
                            <div class="row justify-content-between">
                            <div class="col-12 col-sm-8">
                              <h2 class="card-title">Manage Nurses</h2>
                            </div>
                            <div class="col-12 col-sm-4">
                                <div style="display: flex; flex-direction: row; max-width: 150px;">
                                    <v-btn color="primary" :href="create_account" target="_blank">
                                        <v-icon left>mdi-account-plus</v-icon>
                                        <span class="v-btn__content">Add User</span>
                                    </v-btn>
                                    <v-btn color="primary" :href="export_url" target="_blank" style="margin-left: 10px;">
                                        <v-icon left>mdi-file-export</v-icon>
                                        <span class="v-btn__content">Export</span>
                                    </v-btn>
                                    <v-btn color="success" style="margin-left: 10px;" @click="showSearch = true" v-show="!showSearch">
                                        <v-icon left>mdi-magnify</v-icon>
                                        <span class="v-btn__content">Search</span>
                                    </v-btn>
                                    <v-btn color="error" style="margin-left: 10px;" @click="nurseSearch" v-show="showSearch">
                                        <v-icon left>mdi-magnify</v-icon>
                                        <span class="v-btn__content">Clear Search</span>
                                    </v-btn>
                                </div>
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
                                        v-model="searchCity"
                                        append-icon="mdi-magnify"
                                        label="Search By City"
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
                                <v-dialog v-model="confirmDeleteDialog" max-width="500">
                                    <v-card>
                                        <v-card-title class="headline">Confirm Deletion</v-card-title>
                                        <v-card-text>Are you sure you want to delete this nurse?</v-card-text>
                                        <v-card-actions>
                                        <v-btn color="primary" @click="deleteUser">Yes</v-btn>
                                        <v-btn color="error" @click="confirmDeleteDialog = false">No</v-btn>
                                        </v-card-actions>
                                    </v-card>
                                </v-dialog>
                                <v-data-table
                                    class="table table-responsive-md full-width"
                                    :headers="headers"
                                    :items="filtered_items"
                                    :sort-by="['first_name', 'last_name']"
                                    multi-sort
                                    :footer-props="{ itemsPerPageOptions: [20, 50, -1] }"
                                >
                                    <template v-slot:item.first_name="{ item }">
                                        <a :href="item.user_actions.edit_user" class="blue--text" target="_blank">
                                            {{ item.first_name }}
                                        </a>
                                    </template>
                                    <template v-slot:item.last_name="{ item }">
                                        <a :href="item.user_actions.edit_user" class="blue--text" target="_blank">
                                            {{ item.last_name }}
                                        </a>
                                    </template>
                                    <template v-slot:item.date_created="{ item }">
                                        <a :href="item.user_actions.edit_user" class="blue--text" target="_blank">
                                            {{ item.date_created }}
                                        </a>
                                    </template>
                                    <template v-slot:item.last_login="{ item }">
                                        <a :href="item.user_actions.edit_user" class="blue--text" target="_blank">
                                            {{ item.last_login }}
                                        </a>
                                    </template>
																		<template v-slot:item.city="{ item }">
                                        <a :href="item.user_actions.edit_user" class="blue--text" target="_blank">
                                            {{ item.city }}
                                        </a>
                                    </template>
                                    <template v-slot:item.state="{ item }">
                                        <a :href="item.user_actions.edit_user" class="blue--text" target="_blank">
                                            {{ item.state }}
                                        </a>
                                    </template>
                                    <template v-slot:item.user_actions="{ item }">
                                        <v-btn dark color="primary" icon :href="item.user_actions.edit_user" target="_blank">
                                            <v-icon dark>mdi-pencil</v-icon>
                                        </v-btn>
                                        <v-btn color="error" icon @click="showConfirmDeleteDialog(item)">
                                            <v-icon>mdi-delete</v-icon>
                                        </v-btn>
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
                        text: 'Member Since',
                        align: 'start',
                        value: 'date_created'
                    },
                    {
                        text: 'Last Login',
                        sortable: true,
                        value: 'last_login'
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
                        text: 'User Actions',
                        sortable: false,
                        value: 'user_actions'
                    }
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
                searchCity: '',
                searchState: '',
                line_items: [],
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
                        const cityMatch = item.city.toLowerCase().includes(this.searchCity?.toLowerCase() ?? '');
                        const stateMatch = item.state.toLowerCase().includes(this.searchState?.toLowerCase() ?? '');

                        return firstNameMatch && lastNameMatch && phoneMatch && emailMatch && cityMatch && stateMatch;
                    });
                } else { return []; }
            }
        },
        mounted() {

            this.getNurses();
        },
        watch: {
        },
        methods: {

            getNurses() {
                
                modRequest.request('sa.member.load_nurses', null, null, function(response) {
                    if (response.success) {
                        
                        this.line_items = response.nurses;
                        this.create_account = response.links[0].create_account;
                        this.export_url = response.links[0].export;
                    }
                }.bind(this));
            },
            showConfirmDeleteDialog(user) {

                this.userToDelete = user;
                this.confirmDeleteDialog = true;
            },
            deleteUser() {

                data = {                    
                    member_id: this.userToDelete.member_id
                }
                
                modRequest.request('sa.member.delete_nst_member_mod', null, data, function(response) {
                    if (response.success) {

                        this.confirmDeleteDialog = false;
                        this.snackbarText = 'Nurse deleted successfully.';
                        this.showSnackbar = true;

                        this.getNurses();
                    } else {
                        
                        this.confirmDeleteDialog = false;
                        this.snackbarText = 'Error deleting nurse. Please try again.';
                        this.showSnackbar = true;

                        console.log("Nurse deletion error message: ", response.error);
                    }
                }.bind(this));
            },
            nurseSearch() {

                this.searchFirstName = '';
                this.searchLastName = '';
                this.searchPhone = '';
                this.searchEmail = '';
                this.searchCity = '';
                this.searchState = '';

                this.showSearch = false;
            }
        },
    });
});