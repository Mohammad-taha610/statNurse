Vue.component('provider-option-presets-view', {
    template: `
            <v-container>
                <v-row>
                    <div id="app" class="w-100">
                        <v-app>
                            <div class="d-flex flex-row mb-6 bg-surface-variant">
                                <v-card class="ma-2 pa-2">
                                    <v-card-title>Credentials Available</v-card-title>
                                    <v-card-text
                                    loading="credentialsLoading">
                                        <div v-show="credentialsLoading">
                                            <v-progress-circular
                                                indeterminate
                                                color="primary"
                                            ></v-progress-circular>
                                            <strong>Loading...</strong>
                                        </div>

                                        <div>
                                            <v-checkbox v-for="cred in nurseCredentials"
                                                v-show="!credentialsLoading"
                                                v-model="cred.value"
                                                :label="cred.name"
                                                :value="cred.val"
                                                hide-details
                                                :disabled="cred.disabled"
                                            ></v-checkbox>
                                        </div>
                                    </v-card-text>
                                </v-card>

                                <v-card 
                                    class="ma-2 pa-2">
                                    <v-card-title>Preset Shift Times Available</v-card-title>
                                    
                                    <v-card-text 
                                    class="overflow-auto"
                                    style="max-height: 400px;"
                                    loading="presetTimesLoading">
                                        <div v-show="presetTimesLoading">
                                            <v-progress-circular
                                                indeterminate
                                                color="primary"
                                            ></v-progress-circular>
                                            <strong>Loading...</strong>
                                        </div>

                                        <template>
                                            <v-simple-table
                                            v-show="presetShiftTimes.length">
                                                <template v-slot:default>
                                                    <thead>
                                                        <tr>
                                                            <th class="text-left">
                                                                Start - End
                                                            </th>
                                                            <th class="text-left">
                                                                Category
                                                            </th>
                                                            <th class="text-left">
                                                                Actions
                                                            </th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr
                                                        v-for="(item, index) in presetShiftTimes"
                                                        :key="item.id"
                                                        >
                                                            <td>{{ item.human_readable }}</td>
                                                            <td>{{ item.category_name ? item.category_name : 'No category selected' }}</td>
                                                            <td>
                                                                <v-btn
                                                                    x-small
                                                                    fab
                                                                    color="primary"
                                                                    @click="editPresetShiftTime(item)">
                                                                    <v-icon small>mdi-pencil</v-icon>

                                                                </v-btn>
                                                                
                                                                <v-dialog
                                                                    v-model="item.deleteDialog"
                                                                    max-width="350"
                                                                    >
                                                                    <template v-slot:activator="{ on, attrs }">
                                                                        <v-btn
                                                                            x-small
                                                                            fab
                                                                            color="error"
                                                                            v-bind="attrs"
                                                                            v-on="on"> 
                                                                            <v-icon small>mdi-trash-can</v-icon>

                                                                        </v-btn>
                                                                    </template>
                                                                    <v-card>
                                                                        <v-card-title class="text-h5">
                                                                        Delete Preset shift time?
                                                                        </v-card-title>
                                                                        <v-card-text>Are you sure you want to delete this preset shift time?</v-card-text>
                                                                        <v-card-actions>
                                                                            <v-spacer></v-spacer>
                                                                            <v-btn
                                                                                color="error"
                                                                                text
                                                                                @click="item.deleteDialog = false"
                                                                            >
                                                                                Cancel
                                                                            </v-btn>
                                                                            <v-btn
                                                                                color="success"
                                                                                text
                                                                                @click="deletePresetShiftTime(item)"
                                                                            >
                                                                                Confirm
                                                                            </v-btn>
                                                                        </v-card-actions>
                                                                    </v-card>
                                                                </v-dialog>
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </template>
                                            </v-simple-table>
                                            <div v-show="!presetShiftTimes.length">No preset shift times set.</div>
                                        </template>

                                    </v-card-text>
                                    <v-spacer></v-spacer>
                                    <v-card-actions>
                                        <v-dialog
                                            v-model="presetTimesdialog"
                                            :persistent="presetTimeSaving"
                                            max-width="600px"
                                            >
                                            <template v-slot:activator="{ on, attrs }">
                                                <v-btn
                                                color="success"
                                                dark
                                                rounded
                                                v-bind="attrs"
                                                v-on="on"
                                                block
                                                @click="setupNewPresetShiftTime"
                                                >
                                                Add New
                                                </v-btn>
                                            </template>
                                            <v-card>
                                                <v-card-title>
                                                    <span v-show="!presetTimeSaving && selectedPresetTimeObj.id == '0'" class="text-h5">Add new preset time</span>
                                                    <span v-show="presetTimeSaving && selectedPresetTimeObj.id == '0'" class="text-h5">Saving preset time...</span>
                                                    <span v-show="!presetTimeSaving && selectedPresetTimeObj.id > '0'" class="text-h5">Editing preset time</span>
                                                    <span v-show="presetTimeSaving && selectedPresetTimeObj.id > '0'" class="text-h5">Updating preset time...</span>
                                                </v-card-title>
                                                <v-card-text>
                                                    <v-container>
                                                        <v-row 
                                                            v-show="!presetTimeSaving">
                                                            <v-form ref="presetShiftTimeForm">
                                                            <v-col cols="12" md="6">
                                                                <v-menu
                                                                    ref="menu1"
                                                                    v-model="menu1"
                                                                    :close-on-content-click="false"
                                                                    transition="scale-transition"
                                                                    max-width="290px"
                                                                    min-width="290px"
                                                                    :offset-y="true"
                                                                    :disabled="presetTimeSaving"
                                                                >
                                                                    <template v-slot:activator="{ on, attrs }">
                                                                        <v-text-field
                                                                            ref="presetShiftTimeStart"
                                                                            v-model="selectedPresetTimeObj.start_time"
                                                                            label="*Start Time"
                                                                            prepend-icon="mdi-clock-outline"
                                                                            v-bind="attrs"
                                                                            v-on="on"
                                                                            :disabled="presetTimeSaving"
                                                                            required
                                                                            :rules="[v => !!v || 'Item is required']"
                                                                        ></v-text-field>
                                                                    </template>
                                                                    <v-time-picker
                                                                        v-if="menu1"
                                                                        v-model="selectedPresetTimeObj.start_time"
                                                                        :allowed-minutes="allowedMinutes"
                                                                        full-width
                                                                        :disabled="presetTimeSaving"
                                                                    ></v-time-picker>
                                                                </v-menu>
                                                            </v-col>
                                                            <v-col cols="12" md="6">
                                                                <v-menu
                                                                    ref="menu2"
                                                                    v-model="menu2"
                                                                    :close-on-content-click="false"
                                                                    transition="scale-transition"
                                                                    max-width="290px"
                                                                    min-width="290px"
                                                                    :offset-y="true"
                                                                    :disabled="presetTimeSaving"
                                                                >
                                                                    <template v-slot:activator="{ on, attrs }">
                                                                        <v-text-field
                                                                            ref="presetShiftTimeEnd"
                                                                            v-model="selectedPresetTimeObj.end_time"
                                                                            label="*End Time"
                                                                            prepend-icon="mdi-clock-outline"
                                                                            v-bind="attrs"
                                                                            v-on="on"
                                                                            :disabled="presetTimeSaving"
                                                                            required
                                                                            :rules="[v => !!v || 'Item is required']"
                                                                        ></v-text-field>
                                                                    </template>
                                                                    <v-time-picker
                                                                        v-if="menu2"
                                                                        v-model="selectedPresetTimeObj.end_time"
                                                                        :allowed-minutes="allowedMinutes"
                                                                        full-width
                                                                        :disabled="presetTimeSaving"
                                                                    ></v-time-picker>
                                                                </v-menu>
                                                            </v-col>
                                                            <v-col cols="12" md="12">
                                                                <v-select
                                                                    ref="presetShiftTimeCategory"
                                                                    :items="presetTimeCategories"
                                                                    v-model="selectedPresetTimeObj.category_id"
                                                                    label="*Category"
                                                                    :disabled="presetTimeSaving"
                                                                    required
                                                                    :rules="[v => !!v || 'Item is required']">
                                                                </v-select>
                                                            </v-col>
                                                            </v-form>
                                                        </v-row>
                                                        <v-row class="d-flex justify-center">
                                                            <div v-show="presetTimeSaving">
                                                                <v-progress-circular
                                                                :size="50"
                                                                color="primary"
                                                                indeterminate
                                                                ></v-progress-circular>
                                                            </div>
                                                        </v-row>
                                                    </v-container>
                                                </v-card-text>
                                                <v-card-actions>
                                                    <v-spacer></v-spacer>
                                                    <v-btn
                                                        color="blue darken-1"
                                                        text
                                                        @click="presetTimesdialog = false"
                                                        :disabled="presetTimeSaving">
                                                        Close
                                                    </v-btn>
                                                    <v-btn
                                                        color="success darken-1"
                                                        text
                                                        @click="savePresetTime"
                                                        :disabled="presetTimeSaving">
                                                        {{selectedPresetTimeObj.id > 0 ? 'Update' : 'Save'}}
                                                    </v-btn>
                                                </v-card-actions>
                                            </v-card>
                                        </v-dialog>
                                    </v-card-actions>
                                </v-card>
                            </div>
                            <div class="ma-2 pa-2">
                                 <div class="form-group">
                                    <label for="title">Shift Break Duration (minutes)</label>
                                    <input class="form-control" name="breakDuration" type="number" placeholder="Break Duration" v-model="breakDuration"/>
                                </div>
                            </div>
                        </v-app>
                    </div>
                </v-row>
                <v-snackbar
                    v-model="messagingSnackbar.status"
                    :color="messagingSnackbar.color"
                    rounded="pill"
                    :timeout="2000">
                    {{ messagingSnackbar.message }}

                    <template v-slot:action="{ attrs }">
                        <v-btn
                        color="white"
                        text
                        v-bind="attrs"
                        @click="messagingSnackbar.status = false"
                        >
                        Close
                        </v-btn>
                    </template>
                </v-snackbar>
            </v-container>
        `,
    props: [
        'provider-id',
        'member-type'
    ],
    data: function () {
        return {
            credentialsLoading: true,
            presetTimesLoading: false,
            presetTimeSaving: false,
            nurseCredentials: [
                {
                    name: "Loading",
                    value: false,
                    id: 0,
                    disabled: true
                }
            ],
            presetTimesdialog: false,
            menu1: false,
            menu2: false,
            presetShiftTimes: [],
            selectedPresetTimeObj: null,
            defaultPresetTimeObj: {
                start_time: "",
                end_time: "",
                human_readable: "",
                id: 0,
                category_name: "",
                category_id: 0,
            },
            presetTimeCategories: [{ 
                text: 'Loding...', 
                value: 10, 
                disabled: false 
            }],
            messagingSnackbar: {
                status: false,
                message: '',
                color: "white"
            },
            breakDuration: 10
        }
    },
    created() {
        this.loadProviderNurseCredentials();
        this.loadShiftCategories();
        this.loadShiftBreakDuration();
        this.$root.$on('saveMemberData', function () {
            this.saveProviderNurseCredentials()
            this.saveBreakDuration();
        }.bind(this));
        this.selectedPresetTimeObj = {...this.defaultPresetTimeObj};
        this.loadPresetShiftTimes();
    },
    mounted() {

    },
    computed: {
        allowedMinutes: function(m) {
            return m => m % 15 === 0;
        },
    },
    methods: {
        loadProviderNurseCredentials() {
            this.credentialsLoading = true;

            let data = {
                provider_id: this.providerId
            };

            modRequest.request('sa.member.load_provider_nurse_credentials', {}, data, response => {
                if (response.success) {
                    if (response.credentials) {
                        this.nurseCredentials = [];

                        response.credentials.forEach(element => {
                            this.nurseCredentials.push({ ...element })
                        });
                    }
                    this.credentialsLoading = false;
                } else {
                    this.credentialsLoading = false;
                }
            }, response => {
                this.credentialsLoading = false;
            });
        },
        loadShiftCategories() {
            modRequest.request('sa.member.load_provider_shift_categories', {}, {}, response => {
                if (response.success) {
                    if (response.categories) {
                        this.presetTimeCategories = [];
                        this.presetTimeCategories = [...response.categories];
                    }
                } else {
                }
            }, response => {
            });
        },
        loadPresetShiftTimes() {
            let data = {
                provider_id: this.providerId
            };

            modRequest.request('sa.member.load_provider_preset_shift_times', {}, data, response => {
                if (response.success) {
                    if (response.presetShiftTimes) {
                        this.presetShiftTimes = null;
                        this.presetShiftTimes = [...response.presetShiftTimes];
                    }
                } else {
                }
            }, response => {
            });
        },
        saveProviderNurseCredentials() {
            this.credentialsLoading = true;

            var data = {
                provider_id: this.providerId,
                credentials: this.nurseCredentials
            };

            modRequest.request('sa.member.save_provider_nurse_credentials', {}, data, response => {
                if (response.success) {
                    this.credentialsLoading = false;
                } else {
                    this.credentialsLoading = false;
                }
            }, response => {
                this.credentialsLoading = false;
            });
        },
        savePresetTime() {
            this.presetTimeSaving = true;

            if(!this.$refs.presetShiftTimeForm.validate()) {
                this.presetTimeSaving = false;
                this.activateSnackbar("Please fill out information for all fields.", 'error');
                return;                
            }

            var data = {
                provider_id: this.providerId,
                preset_time: {...this.selectedPresetTimeObj},
            };

            data['preset_time']['human_readable'] = this.convertTimeToHumanReadable(data['preset_time']['start_time'])  + ' - ' + this.convertTimeToHumanReadable(data['preset_time']['end_time']);
            
            let self = this;

            modRequest.request('sa.member.save_provider_preset_shift_time', {}, data, response => {
                if (response.success) {
                    if(data['preset_time']['id'] == 0){
                        data['preset_time']['human_readable'] = data['preset_time']['human_readable'];
                        data['preset_time']['category_name'] = this.presetTimeCategories.filter( category => category.value == data['preset_time']['category_id'])[0].text;
                        data['preset_time']['id'] = response.presetShiftId;
                        this.presetShiftTimes.push({...data['preset_time']});
                    } else {
                        this.loadPresetShiftTimes();
                    }
                    
                    this.activateSnackbar("Successfully saved", 'success');
                    this.resetPresetShiftTimeForm();
                } else {
                    this.activateSnackbar("Failed to save preset time.\n" + response.message, 'error');
                    self.resetPresetShiftTimeForm();
                }
            }, response => {
                this.activateSnackbar("Failed to save preset time.\n" + response.message, 'error');
                self.resetPresetShiftTimeForm();
            });
        },
        resetPresetShiftTimeForm(){
            this.presetTimeSaving = false;
            this.presetTimesdialog = false;
            this.selectedPresetTimeObj = {...this.defaultPresetTimeObj};
            this.$refs.presetShiftTimeForm.resetValidation();
        },
        deletePresetShiftTime(item) {
            let data = {
                preset_shift_time_id: item.id
            }

            modRequest.request('sa.member.delete_provider_preset_shift_time', {}, data, response => {
                if (response.success) {
                    // 
                    let index = this.presetShiftTimes.indexOf(item);
                    this.presetShiftTimes.splice(index, 1);
                    item.deleteDialog = false;
                    
                    this.activateSnackbar("Successfully deleted preset shift time", 'success');
                } else {
                    this.activateSnackbar("Failed to delete preset shift time.\n" + response.message, 'error');
                    item.deleteDialog = false;
                }
            }, response => {
                this.activateSnackbar("Failed to delete preset shift time.\n" + response.message, 'error');
                item.deleteDialog = false;
            });
        },

        loadShiftBreakDuration() {
            modRequest.request('sa.member.load_provider_shift_break_duration', {}, {
                provider_id: this.providerId
            }, response => {
                console.log(response)
                if (response.success) {
                    console.log(response.break_duration)
                    if (response.break_duration) {
                        this.breakDuration = response.break_duration || 0;
                    }
                } else {
                }
            }, response => {
            });
        },
        setupNewPresetShiftTime(){
            this.selectedPresetTimeObj = {...this.defaultPresetTimeObj};
            this.$nextTick(()=>{
                this.$refs.presetShiftTimeForm.resetValidation();
            });
        },
        editPresetShiftTime(item) {
            this.selectedPresetTimeObj = {...item};
            this.presetTimesdialog = true;
        },
        activateSnackbar(message, color) {
            this.messagingSnackbar.message = message;
            this.messagingSnackbar.color = color;
            this.messagingSnackbar.status = true;
        },
        convertTimeToHumanReadable(time) {
            let hours = time.slice(0, 2);
            let amPm = hours > 11 ? 'PM' : 'AM';
            hours = parseInt(hours) ? hours : 12;

            return hours + time.slice(2, 5) + amPm;
        },
        saveBreakDuration() {
            var data = {
                provider_id: this.providerId,
                break_duration: this.breakDuration
            }
            modRequest.request('sa.member.save_provider_shift_break_duration', {}, data, response => {
                if (response.success) {
                    this.activateSnackbar("Successfully saved", 'success');
                } else {
                    this.activateSnackbar("Failed to save break duration.\n" + response.message, 'error');
                }
            })
        }
    }
});
