window.addEventListener('load', function () {
    Vue.component('create-shift-modal-view', {
        template: /*html*/`
        <div>
            <v-dialog 
                transition=""  
                max-width="600"
                v-model="createShiftDialog"
                :persistent="savingNewShift">
                <template v-slot:activator="{ on, attrs }"> 
                    <v-btn
                        outlined
                        class="mr-0"
                        color="grey darken-2"
                        id="shift-calendar-create-shift-btn"
                        v-bind="attrs"
                        v-on="on"
                        @click="resetModal">
                        <v-icon left color="success">mdi-plus-circle</v-icon>Create Shift
                    </v-btn>
                </template>
                <v-card>
                    <v-toolbar
                        color="primary"
                        dark>
                        <v-card-title>
                            <span v-if="!savingNewShift">Create Shift</span>
                            <span v-else>Saving Shifts...</span>
                        </v-card-title>
                    </v-toolbar>
                    <v-card-text
                        class="pt-6 px-8"
                        v-if="!loadingCreateShiftData && !savingNewShift">
                        <v-form ref="createShiftModalForm">
                            <div 
                                class="nst-box pt-2"
                                :class="{'apply-shake': datesValidationStatus}">
                                <div class="pr-0">
                                    <h3
                                        class="text-center" 
                                        :class="{'primary--text': datesValidationStatus}">{{ createShiftCalendarData.currentMonth }}</h3>
                                    <v-date-picker
                                        class="NstCustomDatePicker pa-0"
                                        v-model="datesOne"
                                        :allowed-dates="inNextSixtyDays"
                                        first-day-of-week="1"
                                        no-title
                                        multiple></v-date-picker>
                                </div>
                                <div class="pl-0">
                                    <h3
                                        class="text-center" 
                                        :class="{'primary--text': datesValidationStatus}">{{ createShiftCalendarData.nextMonth }}</h3>
                                    <v-date-picker
                                        class="NstCustomDatePicker pa-0"
                                        v-model="datesTwo"
                                        :allowed-dates="inNextSixtyDays"
                                        first-day-of-week="1"
                                        no-title
                                        multiple
                                        :show-current="createShiftCalendarData.nextMonthDate"></v-date-picker>
                                </div>
                            </div>
                            <p 
                                v-show="datesValidationStatus" 
                                class="primary--text">*Please select at least one date.</p>
                            <v-progress-linear 
                                v-if="loadingNurseCredentials"
                                color="primary"
                                indeterminate
                                reverse
                                ></v-progress-linear>
                            <v-row>
                                <v-col cols="12" md="12">
                                    <v-chip-group
                                        v-show="!loadingNurseCredentials"
                                        v-model="createShiftObj.credential"
                                        mandatory
                                        column
                                        active-class="primary--text"
                                        @change="loadProviderAvailableNurses">
                                        <v-chip
                                            v-for="credential in nurseCredentials"
                                            :value="credential.name"
                                            :key="credential.id">
                                            {{ credential.name }}
                                        </v-chip>
                                    </v-chip-group>
                                </v-col>
                                <v-col cols="6" md="6">
                                    <v-select
                                    :items="presetShiftTimes"
                                    label="Select timeslot"
                                    return-object
                                    v-model="createShiftObj.selectedTime"
                                    prepend-icon="mdi-calendar"
                                    :loading="loadingPresetTimes"
                                    :rules="[v => !!v || 'Item is required']"
                                    ></v-select>
                                    
                                    <v-select
                                    v-if="createShiftObj.selectedTime && createShiftObj.selectedTime.value == 'custom'"
                                    :items="shiftCategories"
                                    label="Shift Category"
                                    v-model="createShiftObj.selectedTime.category_id"
                                    prepend-icon="mdi-timeline-clock"
                                    :rules="[v => !!v || 'Item is required']"
                                    ></v-select>
                                    <v-menu
                                        v-if="createShiftObj.selectedTime && createShiftObj.selectedTime.value == 'custom'"
                                        ref="customStartTime"
                                        v-model="customStartTimeMenu"
                                        :close-on-content-click="false"
                                        transition="scale-transition"
                                        max-width="290px"
                                        min-width="290px"
                                        :offset-y="true">
                                        <template v-slot:activator="{ on, attrs }">
                                            <v-text-field
                                                v-model="createShiftObj.selectedTime.start_time"
                                                label="*Start Time"
                                                prepend-icon="mdi-clock-outline"
                                                v-bind="attrs"
                                                v-on="on"
                                                required
                                                :rules="[v => !!v || 'Item is required']"
                                                transition="slide-y-transition"
                                            ></v-text-field>
                                        </template>
                                        <v-time-picker
                                            v-if="customStartTimeMenu"
                                            v-model="createShiftObj.selectedTime.start_time"
                                            :allowed-minutes="allowedMinutes"
                                            :allowed-hours="allowedStartHours"
                                            full-width
                                        ></v-time-picker>
                                    </v-menu>
                                    <v-menu
                                        v-if="createShiftObj.selectedTime && createShiftObj.selectedTime.value == 'custom'"
                                        ref="customEndTime"
                                        v-model="customEndTimeMenu"
                                        :close-on-content-click="false"
                                        transition="scale-transition"
                                        max-width="290px"
                                        min-width="290px"
                                        :offset-y="true">
                                        <template v-slot:activator="{ on, attrs }">
                                            <v-text-field
                                                v-model="createShiftObj.selectedTime.end_time"
                                                label="*End Time"
                                                prepend-icon="mdi-clock-outline"
                                                v-bind="attrs"
                                                v-on="on"
                                                required
                                                :rules="[v => !!v || 'Item is required']"
                                                transition="slide-y-transition"
                                            ></v-text-field>
                                        </template>
                                        <v-time-picker
                                            v-if="customEndTimeMenu"
                                            v-model="createShiftObj.selectedTime.end_time"
                                            :allowed-minutes="allowedMinutes"
                                            :allowed-hours="allowedEndHours"
                                            full-width
                                        ></v-time-picker>
                                    </v-menu>
                                </v-col>
                                <v-col cols="6" md="6">
                                    <v-select
                                    :items="premiumBillRates"
                                    label="Premium Rate"
                                    return-object
                                    prepend-icon="mdi-currency-usd"
                                    v-model="createShiftObj.premiumRate"
                                    :loading="loadingPremiumRates"
                                    :rules="[v => !!v || 'Item is required']"
                                    ></v-select>
                                </v-col>

                                
                                <v-col cols="6" md="6">
                                    <v-autocomplete
                                        v-model="createShiftObj.nurse"
                                        :items="nursesAvailable"
                                        label="Nurse"
                                        return-object
                                        prepend-icon="mdi-account"
                                        clearable
                                        :loading="loadingNursesAvailable"
                                    ></v-autocomplete>
                                </v-col>
                                
                                <v-col cols="6" md="6">
                                    <v-text-field
                                            v-model="createShiftObj.copies"
                                            label="Number of Copies"
                                            prepend-icon="mdi-book-multiple"
                                            type="number"
                                            :rules="copies_rules"
                                    ></v-text-field>
                                </v-col>
                                
                                <v-col>
                                    <!--<v-checkbox
                                        v-model="createShiftObj.isCovid"
                                        label="Is Covid"></v-checkbox>-->
                                </v-col>
                            </v-row>
                        </v-form>
                    </v-card-text>
                    
                    <div class="nst-box"
                        v-if="loadingCreateShiftData || savingNewShift"
                        style="height:300px;">
                        <v-progress-circular
                            :size="70"
                            :width="7"
                            color="primary"
                            indeterminate
                            ></v-progress-circular>
                    </div>
                    <v-card-actions class="d-flex px-8 pb-8">
                        <div class="pa-0" v-show="userType == 'Admin'">
                            <span class="pa-1">Hourly rates: </span>
                            <div  v-if="createShiftObj.premiumRate && payRates && billRates" class="pa-0">
                                <span v-for="(rate, index) in billRates" 
                                    :class="{ 'primary--text': premiumRateSelected, 'font-weight-bold': premiumRateSelected }" 
                                    class="pa-1">{{rate}} Rate: {{ adjustedPayRate(rate) }}<br></span>
                            </div>
                            <v-progress-circular
                                color="primary"
                                v-else
                                :size="10"
                                :width="1"
                                indeterminate
                                ></v-progress-circular>
                        </div>

                        <v-btn
                            text
                            @click="createShiftDialog = false"
                            class="pa-2 ml-auto"
                            :disabled="savingNewShift"
                            >Close</v-btn>
                        
                        <v-btn
                            color="success"
                            @click="saveShift"
                            class="pa-2"
                            :disabled="savingNewShift"
                            >Submit</v-btn>
                    </v-card-actions>
                </v-card>
            </v-dialog>
            <div class="text-center ma-2">
                <v-snackbar
                    :color="snackbar.color"
                    v-model="snackbar.status"
                    :timeout="snackbar.timeout">
                    <span v-for="(message, index) in snackbar.message">
                    <br v-if="index > 0">{{ message }}
                    </span>

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
            </div>
         </div>`,
        data: () => ({
            nurseCredentials: [],
            presetShiftTimes: [],
            nursesAvailable: [],
            shiftCategories: [],
            premiumBillRates: [
                {
                    text: 'None',
                    value: 1
                },
                {
                    text: '20%',
                    value: 1.20
                },
                {
                    text: '25%',
                    value: 1.25
                },
                {
                    text: '30%',
                    value: 1.30
                },
                {
                    text: '50%',
                    value: 1.50
                },
                {
                    text: '75%',
                    value: 1.75
                }
            ],
            payRates: {
                DEFAULT: {
                    standard_pay: 1,
                    standard_bill: 1
                }
            },
            createShiftTemplate: {
                dates: [],
                credential: '',
                selectedTime: {},
                isCovid: false,
                premiumRate: {
                    text: 'None',
                    value: 1
                },
                nurse: null,
                copies: 1,
            },
            customShiftTime: {
                text: 'Custom',
                id: 0,
                value: 'custom',
                start_time: null,
                end_time: null,
                category_id: 0,
            },
            createShiftCalendarData: {
                nextMonthDate: "",
                currentMonth: "",
                nextMonth: "",
            },
            datesOne: [],
            datesTwo: [],
            createShiftObj: null,
            customStartTimeMenu: false,
            customEndTimeMenu: false,
            createShiftDialog: false,
            loadingNursesAvailable: false,
            loadingPremiumRates: false,
            loadingPresetTimes: false,
            loadingNurseCredentials: false,
            loadingCreateShiftData: false,
            savingNewShift: false,
            snackbar: {
                status: false,
                message: [],
                color: 'success',
                timeout: 5000
            },
            copies_rules: [
                v => !!v || 'This field is required',
                v => v > 0 || 'Must be greater than 0'
            ],
            datesValidationStatus: false,
            userType: '',
        }),
        computed: {
            selectedDates : function() {
                return this.createShiftObj.datesOne.concat(this.createShiftObj.datesTwo);
            },
            allowedMinutes: function(m) {
                return m => m % 15 === 0;
            },
            allowedStartHours: function(h) {
                if (this.createShiftObj.selectedTime.end_time) {
                    return h => {
                        let startHour = h;
                        let endHour = Number(this.createShiftObj.selectedTime.end_time.substring(0,2));
                        // Fancy math to add 24 hours of h is less than endHour
                        endHour += (endHour < startHour) * 24;
                        let difference = Math.abs(startHour - endHour)

                        return difference <= 16 && difference >= 4;
                    }
                } else {
                    return h => {return true;};
                }

            },
            allowedEndHours: function(h) {
                if (this.createShiftObj.selectedTime.start_time) {
                    return h => {
                        let endHour = h;
                        let startHour = Number(this.createShiftObj.selectedTime.start_time.substring(0,2));
                        // Fancy math to add 24 hours of h is less than endHour
                        endHour += (endHour < startHour) * 24;
                        let difference = Math.abs(endHour - startHour)

                        return difference <= 16 && difference >= 4;
                    }
                } else {
                    return h => {return true;};
                }

            },
            billRates : function() {
                if (!this.createShiftObj.credential) {
                    return null;
                }
                return this.createShiftObj.credential.split('/');
            },
            premiumRateSelected: function() {
                return this.createShiftObj.premiumRate.value > 1.0;
            },
        },
        watch: {
            // Updates the selected nurse on the object for creating when submitting the modal
            nursesAvailable(newNurses, oldNurses) {
                let matched = false;
                if(newNurses && this.createShiftObj.nurse) {
                    newNurses.forEach(nurse => {
                        if (this.createShiftObj.nurse.text === nurse.text) {
                            matched = true;
                        }
                    });

                    if (!matched) {
                        this.createShiftObj.nurse = null;
                    }
                }
            },
            datesOne(newDates, oldDates) {
                this.datesValidationStatus = false;
                this.createShiftObj.dates = newDates.concat(this.datesTwo);
            },
            datesTwo(newDates, oldDates) {
                this.datesValidationStatus = false;
                this.createShiftObj.dates = this.datesOne.concat(newDates);
            }
        },
        created() {
            this.createShiftObj = JSON.parse(JSON.stringify(this.createShiftTemplate));
            this.loadCreateShiftData();
            this.setNextMonth();
        },
        mounted() {
        },
        methods: {
	    inNextSixtyDays(val) {
                let valDate = new Date(val);

                let todayDate = new Date();
                todayDate.setDate(todayDate.getDate() - 1);
                valDate.setHours(0, 0, 0, 0);
                todayDate.setHours(0, 0, 0, 0);

                let sixtyDaysFromNowDate = new Date();
                sixtyDaysFromNowDate.setDate(sixtyDaysFromNowDate.getDate() + 60);
                sixtyDaysFromNowDate.setHours(0, 0, 0, 0);

                return (valDate >= todayDate && valDate <= sixtyDaysFromNowDate);
            },
            loadCreateShiftData() {
                this.loadingCreateShiftData = true;
                modRequest.request('provider.get.create_shift_data', {}, {}, response => {
                    if(response.success) {
                        this.userType = response.userType;
                        this.nurseCredentials = {...response.credentials};
                        this.payRates = {...response.payRates};

                        response.shiftCategories.forEach((cat, index) => {
                            this.shiftCategories.push({...cat});
                        })

                        if (response.presetShiftTimes && response.presetShiftTimes.length) {
                            response.presetShiftTimes.forEach((pst, index) => {
                                if(index == 0) {
                                    this.createShiftObj.selectedTime = pst;
                                }
                                this.presetShiftTimes.push({...pst})
                            });
                        }

                        this.presetShiftTimes.push({...this.customShiftTime});

                        if (Object.keys(this.nurseCredentials).length) {
                            this.createShiftObj.credential = this.nurseCredentials[0].name;
                        }

                        if (Object.keys(this.presetShiftTimes).length) {
                            this.createShiftObj.selectedTime = this.presetShiftTimes[0];
                        }
                        this.loadProviderAvailableNurses();

                        this.loadingCreateShiftData = false;
                    } else {
                        this.loadingCreateShiftData = false;
                        console.log('Error');
                    }
                }, response => {
                    this.loadingCreateShiftData = false;
                    console.log('Failed');
                });
            },
            loadProviderAvailableNurses() {
                if (!this.createShiftObj.credential) {
                    return;
                }

                this.loadingNursesAvailable = true;

                let data = {
                    nurse_type: this.createShiftObj.credential,
                }

                modRequest.request('provider.get.available_nurses', {}, data, response => {
                    if(response.success) {
                        this.nursesAvailable = [];

                        if(response.nurses) {
                            this.nursesAvailable = [...response.nurses];
                        }

                        this.loadingNursesAvailable = false;
                    } else {
                        this.loadingNursesAvailable = false;
                        console.log('Error');
                    }
                }, response => {
                    this.loadingNursesAvailable = false;
                    console.log('Failed');
                });
            },
            saveShift() {
                this.datesValidationStatus = false;
                this.savingNewShift = true;

                if(!this.createShiftObj.dates.length) {
                    this.datesValidationStatus = true;
                }

                if (!this.$refs.createShiftModalForm.validate() || this.datesValidationStatus) {
                    this.savingNewShift = false;
                    return;
                }


                let data = {
                    shift: this.createShiftObj
                }

                modRequest.request('provider.save.new_shift', {}, data, response => {
                    if(response.success) {
                        this.snackbar.message = [];

                        this.snackbar.message.push("Shift(s) saved successfully!");
                        this.snackbar.color = 'success';
                        this.snackbar.status = true;
                        this.$emit('triggerRefresh');

                        this.savingNewShift = false;
                        this.createShiftDialog = false;
                    } else {
                        this.savingNewShift = false;
                        this.createShiftDialog = false;
                        this.snackbar.timeout = 7000;
                        this.snackbar.message = [];
                        if(response['succeeded'] && response['succeeded'] > 0) {
                            this.snackbar.message.push(`(${response['succeeded']}) Shifts saved successfully.`);
                        }
                        this.snackbar.message.push(`(${response['failed']}) Shifts failed to save.`);
                        if (response['error'] && response['error'].length) {
                            response['error'].forEach((err, index) => {
                                this.snackbar.message.push(`Error: ${err}`);
                            })
                        }

                        this.snackbar.color = 'error';
                        this.snackbar.status = true;
                    }
                }, response => {
                    this.savingNewShift = false;
                    this.createShiftDialog = false;
                    console.log('Failed');
                    this.snackbar.message = [];

                    this.snackbar.message.push("Failed to save shift(s)...");
                    this.snackbar.color = 'error';
                    this.snackbar.status = true;
                });
            },
            setNextMonth() {
                this.createShiftCalendarData.nextMonthDate = luxon.DateTime.now().plus({months: 1}).toFormat('yyyy-MM');
                this.createShiftCalendarData.currentMonth = luxon.DateTime.now().toFormat('MMMM' );
                this.createShiftCalendarData.nextMonth = luxon.DateTime.now().plus({months: 1}).toFormat('MMMM' );
            },
            adjustedPayRate(rate) {
                if (!this.payRates[rate]) {
                    return 0;
                }

                return this.payRates[rate].standard_bill * this.createShiftObj.premiumRate.value;
            },
            resetModal() {
                if(this.$refs.createShiftModalForm) {
                    this.$refs.createShiftModalForm.resetValidation();
                }

                this.datesValidationStatus = false;
                this.createShiftObj = {...this.createShiftTemplate};
                this.datesOne = [];
                this.datesTwo = [];

                if (this.nurseCredentials.length) {
                    this.createShiftObj.credential = this.nurseCredentials[0].name;
                }

                this.createShiftObj.selectedTime = this.presetShiftTimes[0];

                if  (this.presetShiftTimes.length) {
                    this.presetShiftTimes[this.presetShiftTimes.length -1].start_time = null;
                    this.presetShiftTimes[this.presetShiftTimes.length -1].end_time = null;
                    this.presetShiftTimes[this.presetShiftTimes.length -1].category_id = null;
                }
            },
        },
    });
});
