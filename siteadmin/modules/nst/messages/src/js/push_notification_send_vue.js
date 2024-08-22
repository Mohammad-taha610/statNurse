Vue.component('push-notification-send-view', {
    // language=HTML
    template:
    `
        <v-app>
            <v-container>
                <v-row>
                    <v-col cols="12">
                        <v-text-field
                            label="Title"
                            hint="Maximum 1024 characters"
                            v-model="title"></v-text-field>
                    </v-col>
                </v-row>
                <v-row>
                    <v-col cols="12">
                        <v-textarea
                            label="Message"
                            hint="Maximum 1024 characters"
                            v-model="message"
                            auto-grow
                            counter="1024"
                        ></v-textarea>
                    </v-col>
                </v-row>
                <v-row>
                    <v-col cols="12">
                        <v-autocomplete
                            v-model="nurse"
                            :loading="isLoading"
                            :search-input.sync="search"
                            :items="nurses"
                            item-text="name"
                            item-value="id"
                            label="Nurse"
                            clearable
                            return-object
                        >
                      </v-autocomplete>
                    </v-col>
                </v-row>
                <v-row>
                    <v-col cols="12">
                        <v-btn
                            color="primary"
                            @click="sendNotification">Send Notification</v-btn>
                    </v-col>
                </v-row>
            </v-container>
            
        </v-app>
    `,
    props: [],
    data: function() {
        return {
            title: '',
            message: '',
            nurses: [],
            nurse: {id:null, name: null, token: null},
            isLoading: false,
            search: null,
        };
    },
    created() {
        this.search = ' ';
    },
    mounted() {

    },
    watch: {
        search(val) {
            this.isLoading = true;

            modRequest.request('nst.messages.searchNursesWithTokens', {}, {term: val}, function(response) {
                if(response.success) {
                    this.nurses = [];
                    if ('nurses' in response) {
                        this.nurses = response.nurses;
                    }
                    this.isLoading = false;
                } else {
                    this.isLoading = false;
                    console.log('Error');
                    console.log(response);
                }
            }.bind(this), function(response) {
                this.isLoading = false;
                console.log('Failed');
                console.log(response);
            });
          },
    },
    computed: {

    },
    methods: {
        loadNurses() {
            var data = {};

            this.nurses = [];
            modRequest.request('nst.messages.loadNursesWithTokens', {}, data, function(response) {
                if(response.success) {
                    for (var i = 0; i < response.nurses.length; i++) {
                        var nurse = response.nurses[i];
                        this.nurses.push({
                            id: nurse.id,
                            name: nurse.name,
                            token: nurse.token
                        })
                    }
                } else {
                    console.log('Error');
                    console.log(response);
                }
            }.bind(this), function(response) {
                console.log('Failed');
                console.log(response);
            });
        },
        sendNotification() {
            var data = {
                title: this.title,
                message: this.message,
                nurse_id: this.nurse.id,
                token: this.nurse.token
            };

            modRequest.request('nst.messages.sendNotificationToNurse', {}, data, function(response) {
                if(response.success) {
                    console.log("woooooo");
                } else {
                    console.log('Error');
                    console.log(response);
                }
            }.bind(this), function(response) {
                console.log('Failed');
                console.log(response);
            });
        }
    }
});