Vue.component('nurse-sms-unread', {
    template: /*html*/`
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <v-app>

                    <div class="card" v-show="loading">
                        <v-container v-show="loading" style="width: 100%; top: 40vh; display: flex; justify-content: center; align-items: center">
                            <i class="mdi mdi-loading mdi-spin" style="font-size: 64px;"></i>
                        </v-container>
                    </div>

                    <div class="card" v-show="!loading">
                        <div class="card-header">
                        <div class="row justify-content-between">
                        <div class="col-12 col-sm-8">
                          <h2 class="card-title">Unread Messages</h2>
                          <small><em>Viewing a nurse's profile will mark recent messages as read.</em></small>
                        </div>
                        <div class="col-12 col-sm-4">
                            <div style="display: flex; flex-direction: row; max-width: 150px;">
                                <v-btn color="success" style="margin-left: 100px;" @click="showSearch = true" v-show="!showSearch">
                                    <v-icon left>mdi-magnify</v-icon>
                                    <span class="v-btn__content">Search Nurses</span>
                                </v-btn>
                                <v-btn color="error" style="margin-left: 100px;" @click="nurseSearch" v-show="showSearch">
                                    <v-icon left>mdi-magnify</v-icon>
                                    <span class="v-btn__content">Clear Search</span>
                                </v-btn>
                                <v-btn @click="recieveNewSMS" color="#546E7A" style="margin-left: 20px;">
                                    <v-icon left style="color: white;">fa fa-refresh</v-icon>
                                    <span class="v-btn__content" style="color: white;">Refresh Messages</span>
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
                            </v-card-title>

                            <v-data-table
                                class="table table-responsive-md full-width"
                                :headers="headers"
                                :items="filtered_items"
                                :sort-by="['first_name', 'last_name']"
                                multi-sort
                                :footer-props="{ itemsPerPageOptions: [20, 50, -1] }"
                            >
                                <template v-slot:item.first_name="{ item }">
                                    <a :href="item.profile_link" class="blue--text" target="_blank">
                                        {{ item.first_name }}
                                    </a>
                                </template>
                                <template v-slot:item.last_name="{ item }">
                                    <a :href="item.profile_link" class="blue--text" target="_blank">
                                        {{ item.last_name }}
                                    </a>
                                </template>
                                <template v-slot:item.most_recent_message.message_body="{ item }">
                                    <a :href="item.profile_link" class="blue--text" target="_blank">
                                        {{ item.most_recent_message.message_body }}
                                    </a>
                                </template>
                                <template v-slot:item.most_recent_message.date_created="{ item }">
                                    <a :href="item.profile_link" class="blue--text" target="_blank">
                                        {{ item.most_recent_message.date_created }}
                                    </a>
                                </template>
                                <template v-slot:item.unread_messages_count="{ item }">
                                    <a :href="item.profile_link" class="blue--text" target="_blank">
                                        {{ item.unread_messages_count }}
                                    </a>
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
    data: function () {
        return{
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
                    text: 'Last Unread Message',
                    sortable: true,
                    value: 'most_recent_message.message_body'
                },
                {
                    text: 'Sent Time',
                    sortable: true,
                    value: 'most_recent_message.date_created'
                },
                {
                    text: 'Unread Messages',
                    sortable: true,
                    value: 'unread_messages_count'
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
            line_items: [],
            showSearch: false,
            showSnackbar: false,
            snackbarText: '',
            loading: false,
        }
    },
    computed: {

        filtered_items() {

            if (this.line_items) {

                return this.line_items.filter(item => {

                    const firstNameMatch = item.first_name.toLowerCase().includes(this.searchFirstName?.toLowerCase() ?? '');
                    const lastNameMatch = item.last_name.toLowerCase().includes(this.searchLastName?.toLowerCase() ?? '');
                    const phoneMatch = item.phone_number.replace(/[^\d0-9]/g, '').includes(this.searchPhone?.replace(/[^\d0-9]/g, '') ?? '');

                    return firstNameMatch && lastNameMatch && phoneMatch;
                });
            } else { return []; }
        }
    },
    mounted() {

        this.recieveNewSMS();
    },
    methods: {

        recieveNewSMS() {

            this.loading = true;
            
            modRequest.request('nst.messages.recieveNewSMS', null, {}, function(response) {
                if (response.success) {
            
                }
            }.bind(this));

            
            setTimeout(function() {
                this.getUnreadSMSNurses();
            }.bind(this), 1500);
        },
        getUnreadSMSNurses() {
            
            modRequest.request('nst.messages.getNursesWithUnreadSMS', null, null, function(response) {
                if (response.success) {                    
                    this.line_items = response.nurses;
                }
            }.bind(this));

            this.loading = false;
        },
        nurseSearch() {

            this.searchFirstName = '';
            this.searchLastName = '';
            this.searchPhone = '';

            this.showSearch = false;
        }
    }
});
