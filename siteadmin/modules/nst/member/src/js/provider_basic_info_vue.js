Vue.component('provider-basic-info-view', {
    // language=HTML
    template:
    `
        <v-container>
            <nst-overlay :loading="loading"></nst-overlay>
            <v-row>
                <v-text-field
                        v-model="provider.name"
                        label="Provider Name"
                ></v-text-field>
            </v-row>
            <v-row>
                <v-text-field
                        v-model="provider.administrator"
                        label="Administrator"
                ></v-text-field>
            </v-row>
            <v-row>
                <v-text-field
                        v-model="provider.director_of_nursing"
                        label="Director of Nursing"
                ></v-text-field>
            </v-row>
            <v-row>
                <v-text-field
                        v-model="provider.scheduler"
                        label="Scheduler"
                ></v-text-field>
            </v-row>
            <v-row>
                <v-text-field
                        v-model="provider.facility_phone"
                        label="Facility Phone Number"
                ></v-text-field>
            </v-row>
            <v-row>
                <v-text-field
                        v-model="provider.primary_email_address"
                        label="Primary Email Address"
                ></v-text-field>
            </v-row>
            <v-row>
                <v-text-field
                        v-model="provider.street_address"
                        label="Street Address"
                ></v-text-field>
            </v-row>
            <v-row>
                <v-text-field
                        v-model="provider.zipcode"
                        label="Zipcode"
                ></v-text-field>
            </v-row>
            <v-row>
                <v-text-field
                        v-model="provider.city"
                        label="City"
                ></v-text-field>
            </v-row>
            <v-row>
                <v-text-field
                        v-model="provider.state_abbreviation"
                        label="State Abbreviation"
                ></v-text-field>
            </v-row>
            <v-row>
                <v-checkbox
                        v-model="provider.requires_covid_vaccine"
                        label="Requires a Covid vaccine?"
                        hide-details
                ></v-checkbox>
            </v-row>
            <v-row>
                <v-checkbox
                        v-model="provider.uses_travel_pay"
                        label="Allows Travel Pay?"
                        hide-details
                ></v-checkbox>
            </v-row>
            <!-- <v-row>
                <v-checkbox
                        v-model="provider.has_covid_pay"
                        label="Has COVID Pay?"
                        hide-details
                ></v-checkbox>
            </v-row> -->
            <v-row>
                <v-checkbox
                    v-model="provider.has_ot_pay"
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
            provider: {},
            loading: false
        };
    },
    created() {
        this.loadProviderBasicInfo();
    },
    mounted() {
        this.$root.$on('saveMemberData', function() {
            this.saveData()
        }.bind(this));
    },
    computed: {

    },
    methods: {

        loadProviderBasicInfo() {
            var data = {
                id: this.id,
            };
            this.loading = true;
            modRequest.request('sa.member.load_provider_basic_info', {}, data, function(response) {
                if(response.success) {
                    this.provider = response.provider;
                    // this.provider.has_covid_pay = response.provider.has_covid_pay ? response.provider.has_covid_pay : false;
                    this.provider.uses_travel_pay = response.provider.uses_travel_pay ? response.provider.uses_travel_pay : false;
                    this.provider.requires_covid_vaccine = response.provider.requires_covid_vaccine ? response.provider.requires_covid_vaccine : false;

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
                provider: this.provider
            };

            this.loading = true;
            modRequest.request('sa.member.save_provider_basic_info', {}, data, function(response) {
                if(response.success) {
                    $.growl.notice({ title: "Success!", message: "Changes to provider saved.", size: "large" });
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
