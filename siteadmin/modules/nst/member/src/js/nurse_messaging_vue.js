Vue.component('nurse-messaging-view', {
    template: /*html*/`
        <v-app>

            <v-container>

                <nst-error-notification
                    v-if="error"
                    :error="error"
                ></nst-error-notification>

                <v-snackbar
                    v-model="snackbar.status"
                    :color="snackbar.color"
                    rounded="pill"
                    :timeout="snackbar.timeout">
                    {{ snackbar.message }}

                    <template v-slot:action="{ attrs }">
                        <v-btn
                        color="white"
                        text
                        v-bind="attrs"
                        @click="snackbar.status = false"
                        >
                        Close
                        </v-btn>
                    </template>
                </v-snackbar>

                <v-card style="padding: 25px; display: flex; flex-direction: column;">

                    <v-card-title>
                        <h2>Messages</h2>
                    </v-card-title>
                    
                    <v-container fluid overflow-y-auto>
                        <div v-if="previous_messages.length > 0">
                            <div v-for="message in previous_messages">
                                <v-row>

                                    <v-col
                                        v-if="!message.sid"
                                        cols="4"
                                        sm="4"
                                        md="4"
                                    ></v-col>

                                    <v-col
                                        cols="8"
                                        sm="8"
                                        md="8"
                                    >
                                        <div v-if="!message.sid">

                                            <v-textarea
                                                v-model="message.message"
                                                background-color="#E3F2FD"
                                                color="black"
                                                solo
                                                dense
                                                readonly
                                                :hint="message.sms_info"
                                                persistent-hint
                                                auto-grow
                                                rows="2"
                                                overflow-y="auto"
                                            ></v-textarea>
                                        </div>

                                        <div v-else>

                                            <v-textarea
                                                v-model="message.message"
                                                background-color="#E8F5E9"
                                                color="black"
                                                solo
                                                dense
                                                readonly
                                                :hint="message.sms_info"
                                                persistent-hint
                                                auto-grow
                                                rows="2"
                                                overflow-y="auto"
                                            ></v-textarea>
                                        </div>
                                    </v-col>
                                </v-row>
                            </div>
                        </div>
                        <div v-else>
                            <h4>No Previous Messages</h4>
                        </div>
                        <v-row style="margin-top: 50px;">
                            <v-text-field
                                elevation="6"
                                filled
                                solo
                                label="Send SMS"
                                counter="140"
                                v-model="message"
                                auto-grow
                            >
                                <template v-slot:append>

                                    <v-btn @click="sendMessage" color="#E3F2FD">
                                        <v-icon left>
                                            fa fa-send
                                        </v-icon>
                                        Send
                                    </v-btn>

                                    <v-btn @click="recieveNewSMS" color="#CFD8DC" style="margin-left: 20px;">
                                        <v-icon left>
                                            fa fa-refresh
                                        </v-icon>
                                        Refresh Messages
                                    </v-btn>
                                </template>
                                
                            </v-text-field>
                        </v-row>

                        <v-btn style="position: fixed; bottom: 50px; right: 50px;" fab bottom right @click="scrollToBottom">
                            <v-icon>mdi-arrow-down</v-icon>
                        </v-btn>

                    </v-container>
                </v-card>

            </v-container>
        </v-app>
    `,
    props: [
        'id'
    ],
    data: function () {
        return{
            error: null,
            snackbar: {
                status: false,
                message: '',
                color: "white",
                timeout: 4000
            },
            message: "",
            previous_messages: [],
        }
    },
    created() {
    },
    mounted() {
        this.recieveNewSMS();
    },
    methods: {
        recieveNewSMS() {
            
            modRequest.request('nst.messages.recieveNewSMS', null, {}, function(response) {
                if (response.success) {            
                    this.getMessages();
                }
            }.bind(this));
        },
        getMessages() {

            data = {
                id: this.id
            }
            
            modRequest.request('nst.messages.getNurseSMSMessages', null, data, function(response) {
                if (response.success) {
                    
                    this.previous_messages = response.messages;

                    this.previous_messages.forEach(message => {

                        if (message.sid) {
                            
                            message.sms_info = message.nurse + " - " + message.date_created;
                            if (message.viewed) {
                                message.sms_info += " - Viewed";
                            } else {
                                message.sms_info += " - New Message";
                            }
                        } else {
                            message.sms_info = message.user + " - " + message.date_created;
                            if (message.sent_successfully) {
                                message.sms_info += " - Sent";
                            } else {
                                message.sms_info += " - Failed to Send";
                            }
                        }
                    });

                }
            }.bind(this));
        },
        sendMessage() {
            
            let data = {

                message: this.message,
                recipients: [ this.id ],
            }
            modRequest.request('nst.messages.sendNurseSMS', {}, data, function (response) {
                if (response.success) {

                    this.message = '';                    
                    this.triggerSnackbar('Success: Nurse Message sent', 'success', 3500);
                }
            }.bind(this), function (response) {
                
                console.log('Failed');
                this.errorMessage = response;
                console.log(response);

                this.snackbar_text = 'Error: Nurse Message failed to send';
                this.show_snackbar = true;
            });

            setTimeout(function() {
                this.recieveNewSMS();
            }.bind(this), 1500);
        },
        triggerSnackbar(message, color, timeout = 1000) {

            this.snackbar.message = message;
            this.snackbar.color = color;
            this.snackbar.timeout = timeout;
            this.snackbar.status = true;
        },
        scrollToBottom() {

            const windowHeight = window.innerHeight;
            const bodyHeight = document.body.scrollHeight;
            const maxScroll = bodyHeight - windowHeight;
            window.scrollTo({ top: maxScroll, behavior: 'smooth' });
        }
    }
});
