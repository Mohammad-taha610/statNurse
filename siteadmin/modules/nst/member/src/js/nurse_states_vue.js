Vue.component('nurse-states-view', {
    template: /*html*/`
        <v-app>
            <v-container>
                <nst-overlay :loading="loading"></nst-overlay>

                <nst-error-notification
                    v-if="error"
                    :error="error"></nst-error-notification>

                <span><i>Changes will automatically save and sync.</i></span>

                <v-card
                    class="mx-auto">
                    <v-card-title
                        class="d-flex justify-space-between">
                        <v-btn
                            color="primary"
                            @click="selectCompactLicenseStates">Apply compact license</v-btn>

                        <v-btn
                            color="warning"
                            @click="clearAllStates">Clear all</v-btn>
                    </v-card-title>

                    <v-card-text>
                        <v-chip-group
                            column
                            multiple
                            v-model="chipGroupModel"
                            active-class="primary--text">
                            <v-chip
                                v-for="state in states"
                                v-model="state.selected"
                                filter
                                @change="debouncedSaveStates()">
                                {{state.name}}
                            </v-chip>
                            
                        <v-progress-circular
                            class="ml-auto mr-4"
                            size="30"
                            width="3"
                            v-show="debounceTimeout"
                            indeterminate
                            color="primary"
                            ></v-progress-circular>
                        </v-chip-group>
                    </v-card-text>
                </v-card>
                <span><i>*If no states are selected nurse will NOT have access to work in any states.</i></span>

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

            </v-container>
        </v-app>
    `,
    props: [
        'id'
    ],
    data: function () {
        return{
            error: null,
            loading: false,
            states: [],
            snackbar: {
                status: false,
                message: '',
                color: "white",
                timeout: 4000
            },
            debounceTimeout: null,
            compactLicenseStates: [
                "Alabama",
                "Arizona",
                "Arkansas",
                "Colorado",
                "Delaware",
                "Florida",
                "Georgia",
                "Guam",
                "Idaho",
                "Indiana",
                "Iowa",
                "Kansas",
                "Kentucky",
                "Louisiana",
                "Maine",
                "Maryland",
                "Mississippi",
                "Missouri",
                "Montana",
                "Nebraska",
                "New Hampshire",
                "New Jersey",
                "New Mexico",
                "North Carolina",
                "North Dakota",
                "Ohio",
                "Oklahoma",
                "South Carolina",
                "South Dakota",
                "Tennessee",
                "Texas",
                "Utah",
                "Vermont",
                "Virginia",
                "West Virginia",
                "Wisconsin",
                "Wyoming"
            ],
            chipGroupModel: [],
        }
    },
    created() {
    },
    mounted() {
        this.getNurseStates();
    },
    methods: {
        getNurseStates() {
            this.loading = true;
            modRequest.request('sa.member.get.nurse.states', {}, { nurse_id: this.id }, response => {
                if (response.success) {
                    this.states = [...response.states];
                    this.chipGroupModel = [];
                    this.chipGroupModel = this.states.map((state, key) => {
                        if( state.selected === true ) {
                            return key;
                        }
                    });

                    this.loading = false;
                } else {
                    this.loading = false;
                }
            }, response => {
                this.loading = false;
                this.triggerSnackbar("Failed: " + response.message, 'error');
            });
        },
        triggerSnackbar(message, color, timeout = 1000) {
            this.snackbar.message = message;
            this.snackbar.color = color;
            this.snackbar.timeout = timeout;
            this.snackbar.status = true;
        },
        debouncedSaveStates() {
            this.debounce(() => this.saveStates(), 1500);
        },
        debounce(func, timeout = 2000) {
            clearTimeout(this.debounceTimeout);
            this.debounceTimeout = setTimeout(() => { func.apply(this); }, timeout);
        }, 
        saveStates(){
            let data = {
                states: this.states,
                nurse_id: this.id
            };

            modRequest.request('sa.member.save.nurse.states', {}, data, response => {
                if (response.success) {
                    this.triggerSnackbar("Nurse states have been updated!", '#4CAF50', 1000);
                    this.debounceTimeout = 0;
                    
                } else {
                    this.debounceTimeout = 0;
                    this.getNurseStates();
                    this.triggerSnackbar("Failed to update nurse states: " + response.message, 'error');
                }
            }, response => {
                this.debounceTimeout = 0;
                this.getNurseStates();
                this.triggerSnackbar("Failed to update nurse states: REQUEST FAILED" , 'error');
            });
        },
        selectCompactLicenseStates() {
            this.states.map((state, key) => { 
                if (this.compactLicenseStates.includes(state.name)) {
                    if (!this.chipGroupModel.includes(key)) {
                        this.chipGroupModel.push(key);
                    }
                    
                    this.states[key].selected = true;
                }
            });
            
            this.debounce(() => this.saveStates(), 1500);
        },
        clearAllStates() {
            this.states.map(state => { state.selected = false; });

            this.chipGroupModel = [];

            this.debounce(() => this.saveStates(), 1500);
        }
    }
});
