Vue.component('executive-basic-info-view', {
    // language=HTML
    template:
    `
        <v-container>
            <nst-overlay :loading="loading"></nst-overlay>
            <v-row>
                <v-text-field
                        v-model="executive.name"
                        label="Executive Name"
                ></v-text-field>
            </v-row>
            <v-row>
                <v-text-field
                        v-model="executive.administrator"
                        label="Administrator"
                ></v-text-field>
            </v-row>
            <v-row>
                <v-text-field
                        v-model="executive.director_of_nursing"
                        label="Director of Nursing"
                ></v-text-field>
            </v-row>
            <v-row>
                <v-text-field
                        v-model="executive.scheduler"
                        label="Scheduler"
                ></v-text-field>
            </v-row>
            <v-row>
                <v-text-field
                        v-model="executive.facility_phone"
                        label="Facility Phone Number"
                ></v-text-field>
            </v-row>
            <v-row>
                <v-text-field
                        v-model="executive.primary_email_address"
                        label="Primary Email Address"
                ></v-text-field>
            </v-row>
            <v-row>
                <v-text-field
                        v-model="executive.street_address"
                        label="Street Address"
                ></v-text-field>
            </v-row>
            <v-row>
                <v-text-field
                        v-model="executive.zipcode"
                        label="Zipcode"
                ></v-text-field>
            </v-row>
            <v-row>
                <v-text-field
                        v-model="executive.city"
                        label="City"
                ></v-text-field>
            </v-row>
            <v-row>
                <v-text-field
                        v-model="executive.state_abbreviation"
                        label="State Abbreviation"
                ></v-text-field>
            </v-row>
            <v-row>
                <v-checkbox
                        v-model="executive.requires_covid_vaccine"
                        label="Requires a Covid vaccine?"
                        hide-details
                ></v-checkbox>
            </v-row>
            <v-row>
                <v-checkbox
                        v-model="executive.uses_travel_pay"
                        label="Allows Travel Pay?"
                        hide-details
                ></v-checkbox>
            </v-row>
            <!-- <v-row>
                <v-checkbox
                        v-model="executive.has_covid_pay"
                        label="Has COVID Pay?"
                        hide-details
                ></v-checkbox>
            </v-row> -->
            <v-row>
                <v-checkbox
                    v-model="executive.has_ot_pay"
                    label="Has overtime Pay?"
                    hide-details
                ></v-checkbox>
            </v-row>
        </v-container>
    `,
    props: [
        'tab',
        'id'
    ],
    data: function() {
        return {
            executive: {},
            loading: false
        };
    },
    created() {
        this.loadExecutiveBasicInfo();
    },
    mounted() {
        this.$root.$on('saveMemberData', function() {
            this.saveData()
        }.bind(this));
    },
    computed: {

    },
    methods: {

        loadExecutiveBasicInfo() {
            var data = {
                id: this.id,
            };
            this.loading = true;
            modRequest.request('sa.member.load_executive_basic_info', {}, data, function(response) {
                if(response.success) {
                    this.executive = response.executive;
                    // this.executive.has_covid_pay = response.executive.has_covid_pay ? response.executive.has_covid_pay : false;
                    // this.executive.uses_travel_pay = response.executive.uses_travel_pay ? response.executive.uses_travel_pay : false;
                    // this.executive.requires_covid_vaccine = response.executive.requires_covid_vaccine ? response.executive.requires_covid_vaccine : false;

                } else {
                    console.log('Error');
                    console.log(response);
                }
                this.loading = false;
            }.bind(this), function(response) {
                console.log('Failed');
                console.log(response);
            });
        },
        saveData() {
            var data = {
                id: this.id,
                executive: this.executive
            };

            this.loading = true;
            modRequest.request('sa.member.save_executive_basic_info', {}, data, function(response) {
                if(response.success) {
                    $.growl.notice({ title: "Success!", message: "Changes to executive saved.", size: "large" });
                } else {
                    console.log('Error');
                    console.log(response);
                }
                this.loading = false;
            }.bind(this), function(response) {
                console.log('Failed');
                console.log(response);
            });
        }
    }
});
