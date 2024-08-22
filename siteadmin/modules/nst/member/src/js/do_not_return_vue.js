window.addEventListener('load', function() {
    Vue.component('do-not-return-list', {
        template: `
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title">Do Not Return</h4>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive" id="vue">
                                <v-app>
                                    <v-card>
                                        <v-card-title>
                                            <v-data-table
                                                class="table table-responsive-md"
                                                :headers="headers"
                                                :items="nurses"
                                            >
                                                <template v-slot:item.first_name="{ item }">
                                                    <a :href="item.profile">{{item.first_name}}</a>
                                                </template>
                                                <template v-slot:item.last_name="{ item }">
                                                    <a :href="item.profile">{{item.last_name}}</a>
                                                </template>
                                                <template v-slot:item.profile="{ item }">
                                                    <a :href="item.profile">Profile</a>
                                                </template>
                                                <template v-slot:item.remove="{ item }">
                                                    <v-dialog max-width="400">
                                                        <template v-slot:activator="{ on, attrs }">
                                                            <v-btn 
                                                                v-on="on" 
                                                                v-bind="attrs" 
                                                                color="primary"
                                                            >Unblock</v-btn>
                                                        </template>
                                                        <template v-slot:default="dialog">
                                                            <v-card>
                                                                <v-toolbar
                                                                    color="primary"
                                                                    class="text-h4 white--text"
                                                                >Are you sure?</v-toolbar>
                                                                <v-card-text
                                                                    class="pt-5"
                                                                >Do you wish to remove this nurse from your 'Do Not Return' list?</v-card-text>
                                                                <v-card-actions>
                                                                    <v-spacer></v-spacer>
                                                                    <v-btn
                                                                        color="light"
                                                                        v-on:click="dialog.value = false"
                                                                    >Cancel
                                                                    </v-btn>
                                                                    <v-btn
                                                                        color="primary"
                                                                        v-on:click="unblockNurse(item)"
                                                                    >Yes, Unblock</v-btn>
                                                                </v-card-actions>
                                                            </v-card>
                                                        </template>
                                                    </v-dialog>
                                                </template>
                                            </v-data-table>
                                        </v-card-title>
                                    </v-card>
                                </v-app>
                                </div>
                            </div>
                        </div>
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
                        text: 'Profile',
                        sortable: false,
                        value: 'profile'
                    },
                    {
                        text: 'Unblock',
                        sortable: false,
                        value: 'remove'
                    }
                ],
                nurses: []
            }
        },
        created: function () {
            this.loadNurses();
        },
        methods: {
            loadNurses: function () {
                modRequest.request('provider.do_not_return', null, {},
                    function(response) {
                        if(response.success) {
                            this.nurses = response.nurses;
                        }
                    }.bind(this),
                        function(error) {
                    }
                ); 
            },
            unblockNurse(nurse) {
                var data = {
                    id: nurse.id
                };

                modRequest.request('provider.unblock_nurse', {}, data, function(response) {
                    if(response.success) {
                        this.nurses.splice(this.nurses.indexOf(nurse), 1);
                    } else {
                        console.log('Error');
                        console.log(response);
                    }
                }.bind(this), function(response) {
                    console.log('Failed');
                    console.log(response);
                });

            }
        },
    });
});