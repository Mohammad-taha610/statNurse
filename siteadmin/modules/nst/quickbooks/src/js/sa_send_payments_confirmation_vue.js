Vue.component('send-payments-confirmation-view', {
    // language=HTML
    template:
    `
        <v-app>
            <v-container>
                <v-row>
                    <h1 v-if="!finished">Sending</h1>
                    <h1 v-else>Finished Sending</h1>
                </v-row>
                <v-row v-for="message in messages" v-if="messages.length > 0">
                    <span class="block red--text">{{message}}</span>
                </v-row>
            </v-container>
        </v-app>
    `,
    props: [
        'code',
        'state',
        'realm_id',
        'payment_ids'
    ],
    data: function() {
        return {
            finished: false,
            messages: [],
        };
    },
    created() {
        this.sendPaymentsToQuickbooks();
    },
    mounted() {

    },
    computed: {

    },
    methods: {
        sendPaymentsToQuickbooks() {
            var data = {
                code: this.code,
                state: this.state,
                realmId: this.realm_id,
                payment_ids: this.payment_ids
            }
            modRequest.request('sa.quickbooks.send_payments_to_quickbooks', {}, data, function(response) {
                if(response.success) {
                    this.finished = true;
                    this.messages = response.messages;
                    console.log("success");
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