Vue.component('sa-drug-screen', {
template: /*html*/`
<div>

    <h1 v-if="!drug_screen.drug_screening_id">Drug Screen Not Completed</h1>

    <a v-if="drug_screen.drug_screening_id" :href="drug_screen_checkr_uri" target="_blank">View Drug Screen in Checkr Dashboard</a>

    <textarea
        v-if="drug_screen.drug_screening_id"
        v-model="drug_screen_view"
        label="Drug Screen"
        disabled
        style="width: 100%; height: 800px; margin-bottom: 25px;"
        auto-grow
    ></textarea>

    <!-- <div>

        <h1 v-if="drug_screen.accepted || drug_screen.result == 'clear'">Drug Screen Accepted</h1>
        <h1 v-else style="margin-bottom: 25px;">Drug Screen Results For Consideration</h1>
        
        <v-row>

            <v-col>
                <v-text-field
                    v-model="drug_screen.result"
                    label="Drug Screen Result"
                    disabled
                    auto-grow
                ></v-text-field>
            </v-col>

            <v-col>
                <v-text-field
                    v-model="drug_screen.disposition"
                    label="Disposition"
                    disabled
                    auto-grow
                ></v-text-field>
            </v-col>

        </v-row>        
        
        <v-row>

            <v-col>
                <v-text-field
                    v-model="drug_screen.id"
                    label="Drug Screen Id"
                    disabled
                    auto-grow
                ></v-text-field>
            </v-col>

            <v-col>
                <v-text-field
                    v-model="drug_screen.mro_notes"
                    label="MRO Notes"
                    disabled
                    auto-grow
                ></v-text-field>
            </v-col>

        </v-row>

        <h2>Analytes</h2>

        <h4 v-if="drug_screen.drug_screening.analytes.length == 0" style="margin-bottom: 25px;">No Analytes</h4>

        <div v-if="drug_screen.drug_screening.analytes.length > 0" style="margin-bottom: 25px;">
            <v-text-field v-for="analyte in drug_screen.drug_screening.analytes" auto-grow>{{ analyte }}</v-text-field>
        </div>

        <h2>Events</h2>

        <h4 v-if="drug_screen.drug_screening.events.length == 0">No Events</h4>

        <div v-if="drug_screen.drug_screening.events.length > 0" style="margin-bottom: 25px;">
            <v-text-field v-for="event in drug_screen.drug_screening.events" auto-grow>{{ event }}</v-text-field>
        </div> -->

        <v-row v-if="drug_screen.result && drug_screen.result != 'clear' && !drug_screen.accepted" style="margin-top: 25px;"> -->
        <!-- <v-row v-if="drug_screen.result" style="margin-top: 25px;"> -->
            <v-btn @click="accept_drug_screen_dialog = true" color="success">Approve Non Clear Drug Screen</v-btn>
        </v-row>

        <v-dialog v-model="accept_drug_screen_dialog" max-width="500">
            <v-card>
                <v-card-title class="headline">Confirm Action</v-card-title>
        
                <v-card-text>
                    Are you sure you want to approve drug screen and move on to background check?
                </v-card-text>
        
                <v-card-actions class="justify-end">
                    <v-btn color="green" @click="acceptDrugScreen">Accept</v-btn>
                    <v-btn color="black" @click="accept_drug_screen_dialog = false" style="margin-left: 10px;">Cancel</v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>

    </div>
</div>
`,
watch: {},
computed: {},
created() {},
props: {

    application_id: Number,
    drug_screen: Object,
    drug_screen_view: String,
    drug_screen_checkr_uri: String,
},
data: () => ({
    accept_drug_screen_dialog: false,
}),
methods: {

    acceptDrugScreen() {

        data = {
            application_id: this.application_id,
        }
        
        modRequest.request('nurse.application.acceptDrugScreen', null, data, function(response) {
            if (response.success) {

                this.$emit('showSnackbar', {

                    message: 'Drug Screen Accepted',
                    color: 'success',
                    timeout: 3000
                });
                this.accept_drug_screen_dialog = false;
            }
        }.bind(this));
    },
},
});