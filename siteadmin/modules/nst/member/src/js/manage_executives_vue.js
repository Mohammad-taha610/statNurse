window.addEventListener('load', function() {
    Vue.component('manage-executives-view', {
        template: /*html*/`
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <v-app>
                        <div class="card">
                            <div class="card-header">
                            <div class="row justify-content-between">
                            <div class="col-12 col-sm-8">
                              <h2 class="card-title">Manage Executives</h2>
                            </div>
                            <div class="col-12 col-sm-4">
                                <div style="display: flex; flex-direction: row; max-width: 150px;">
                                    <v-btn color="primary" :href="create_account" target="_blank">
                                        <v-icon left>mdi-account-plus</v-icon>
                                        <span class="v-btn__content">Add User</span>
                                    </v-btn>
                                </div>
                            </div>
                          </div>
                            </div>
                            <div class="card-body">
                                <v-card-title v-show="showSearch">
                                    <v-text-field
                                        style="margin-left: 15px; margin-right: 15px;"
                                        v-model="searchExecutive"
                                        append-icon="mdi-magnify"
                                        label="Search By Executive"
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
                                    <v-text-field
                                        style="margin-left: 15px; margin-right: 15px;"
                                        v-model="searchUsername"
                                        append-icon="mdi-magnify"
                                        label="Search By Username"
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
                                        style="margin-left: 5px; margin-right: 15px;"
                                        v-model="searchEmail"
                                        append-icon="mdi-magnify"
                                        label="Search By Email Address"
                                        single-line
                                        hide-details
                                    ></v-text-field>
                                </v-card-title>
                                <v-dialog v-model="confirmDeleteDialog" max-width="500">
                                    <v-card>
                                        <v-card-title class="headline">Confirm Deletion</v-card-title>
                                        <v-card-text>Are you sure you want to delete this executive?</v-card-text>
                                        <v-card-actions>
                                        <v-btn color="primary" @click="deleteUser">Yes</v-btn>
                                        <v-btn color="error" @click="confirmDeleteDialog = false">No</v-btn>
                                        </v-card-actions>
                                    </v-card>
                                </v-dialog>
                                <v-data-table
                                    class="table table-responsive-md full-width"
                                    :headers="headers"
                                    :items="line_items"
                                    :sort-by="['company']"
                                    multi-sort
                                    :footer-props="{ itemsPerPageOptions: [20, 50, -1] }"
                                >
                                    <template v-slot:item.company="{ item }">
                                        <a :href="item.user_actions.edit_user" class="blue--text" target="_blank">
                                            {{ item.company }}
                                        </a>
                                    </template>
                                    <template v-slot:item.member_since="{ item }">
                                        <a :href="item.user_actions.edit_user" class="blue--text" target="_blank">
                                            {{ item.member_since }}
                                        </a>
                                    </template>
                                    <template v-slot:item.last_login="{ item }">
                                        <a :href="item.user_actions.edit_user" class="blue--text" target="_blank">
                                            {{ item.last_login }}
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
                                        <v-btn color="success" icon :href="item.user_actions.login_user" target="_blank">
                                            <v-icon>mdi-login</v-icon>
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
                        text: 'Executive Name',
                        align: 'start',
                        sortable: true,
                        value: 'company'
                    },
                    {
                        text: 'Member Since',
                        align: 'start',
                        value: 'member_since'
                    },
                    {
                        text: 'Last Login',
                        sortable: true,
                        value: 'last_login'
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
                searchExecutive: '',
                searchPhone: '',
                searchEmail: '',
                searchState: '',
                searchUsername: '',
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

            // filtered_items() {

            //     if (this.line_items) {

            //         return this.line_items.filter(item => {

            //             const executiveMatch = item.company.toLowerCase().includes(this.searchExecutive?.toLowerCase() ?? '');
            //             const usernameMatch = item.usernames && item.usernames.some(username => username.toLowerCase().includes(this.searchUsername?.toLowerCase() ?? ''));
            //             const phoneMatch = item.phone_numbers && item.phone_numbers.some(phone_number => phone_number.replace(/[^\d0-9]/g, '').includes(this.searchPhone?.replace(/[^\d0-9]/g, '') ?? ''));
            //             const emailMatch = item.emails && item.emails.some(email => email.toLowerCase().includes(this.searchEmail?.toLowerCase() ?? ''));
            //             const stateMatch = item.state.toLowerCase().includes(this.searchState?.toLowerCase() ?? '');

            //             return executiveMatch && usernameMatch && phoneMatch && emailMatch && stateMatch;
            //         });
            //     } else { return []; }
            // }
        },
        mounted() {

            this.getExecutives();
        },
        watch: {
        },
        methods: {

            getExecutives() {

                modRequest.request('sa.member.load_executives', null, null, function(response) {
                    if (response.success) {

                        this.line_items = response.executives;
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
                        this.snackbarText = 'Executive deleted successfully.';
                        this.showSnackbar = true;

                        this.getExecutives();
                    } else {

                        this.confirmDeleteDialog = false;
                        this.snackbarText = 'Error deleting executive. Please try again.';
                        this.showSnackbar = true;

                        console.log("Executive deletion error message: ", response.error);
                    }
                }.bind(this));
            },
            executiveSearch() {

                this.searchExecutive = '';
                this.searchState = '';
                this.searchUsername = '';
                this.searchPhone = '';
                this.searchEmail = '';
                this.showSearch = false;
            }
        },
    });
});
