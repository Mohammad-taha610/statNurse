window.addEventListener('load', function () {
    Vue.component('sa-create-shift-modal-view', {
        template: /*html*/`
        <div>
            <v-dialog 
                transition=""  
                max-width="800"
                v-model="createShiftDialog"
            >
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
                <v-card v-show="loading">
                
                    <div class="nst-box"
                        style="height:300px; left: 50%;">
                        <v-progress-circular
                            :size="70"
                            :width="7"
                            color="primary"
                            indeterminate
                            ></v-progress-circular>
                    </div>
                </v-card>
                <v-card v-show="!loading">
                    <v-toolbar
                        color="primary"
                        dark>
                        <v-card-title>
                            <span >Create Shift</span>
                        </v-card-title>
                    </v-toolbar>
                    <v-card-text class="pt-6 px-8">
                        <v-form ref="createShiftModalForm">
                            <div 
                                class="nst-box pt-2"
                                :class="{'apply-shake': datesValidationStatus}"
                            >
                                <div class="col-md-6">
                                    
                                    <v-date-picker
                                        class="NstCustomDatePicker pa-0"
                                        v-model="datesOne"
                                        :allowed-dates="inNextSixtyDays"
                                        first-day-of-week="1"
                                        no-title
                                        multiple
                                    ></v-date-picker>
                                </div>
                                <div class="col-md-6">
                                    
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

                            <v-row>                            
                                <v-col cols="6">
                                    <v-autocomplete
                                        v-model="provider_id"
                                        :items="providers"
                                        item-text="name"
                                        item-value="id"
                                        label="Provider"
                                        attach
                                        prepend-icon="mdi-account"
                                        clearable
                                        @change="getPresetShifts"
                                    ></v-autocomplete>
                                </v-col>
                                
                                <v-col cols="6" md="6">
                                    <v-select
                                        :items="presetShiftTimes"
                                        label="Select timeslot"
                                        return-object
                                        v-model="createShiftObj.selectedTime"
                                        prepend-icon="mdi-calendar"
                                        @change="showCategories"
                                    ></v-select>
                                </v-col>
                            </v-row>

                            <v-row>
                                <v-col cols="6" md="6">

                                    <v-select
                                        v-show="!createShiftObj.selectedTime"
                                        prepend-icon="mdi-timeline-clock"
                                        label="Category"
                                        hint="Select Provider"
                                        persistent-hint
                                        disabled
                                    ></v-select>

                                    <div v-show="createShiftObj.selectedTime">

                                        <v-select
                                            v-show="show_categories"
                                            :items="categories"
                                            label="Category"
                                            v-model="category_id"
                                            prepend-icon="mdi-timeline-clock"
                                        ></v-select>

                                        <v-select
                                            v-show="!show_categories"
                                            prepend-icon="mdi-timeline-clock"
                                            label="Category"
                                            hint="Selected by Preset"
                                            persistent-hint
                                            disabled
                                        ></v-select>

                                    </div>                                    
                                </v-col>

                                <v-col cols="6" md="6">
                                    <v-text-field
                                        v-model="createShiftObj.copies"
                                        label="Number of Copies"
                                        prepend-icon="mdi-book-multiple"
                                        type="number"
                                    ></v-text-field>
                                </v-col>
                            </v-row>

                            <v-row>
                                <v-col cols="12" md="6">
                                    <v-text-field
                                        v-model="bonus.amount"
                                        label="Bonus"
                                        prepend-icon="mdi-currency-usd"
                                        type="number"
                                        min="0"
                                        step="0.01"
                                    ></v-text-field>
                                </v-col>

                                <v-col cols="12" md="6">
                                    <v-text-field
                                            v-model="bonus.description"
                                            label="Bonus Description"
                                            prepend-icon="mdi-currency-usd"
                                            type="text"
                                    ></v-text-field>
                                </v-col>
                            </v-row>

                            <v-row style="min-width: 100%:">                       
                            
                                <v-col cols="6" md="6">
                                    <v-select 
                                        v-model="createShiftObj.premiumRate.value"
                                        label="Incentive"
                                        :items="incentive_options"
                                        item-text="name"
                                        item-value="val"
                                        prepend-icon="mdi-cash-multiple"
                                        persistent-hint> 
                                    </v-select> 
                                </v-col>

                                <v-col cols="6" md="6">
                                    <v-checkbox
                                        v-model="createShiftObj.isCovid"
                                        label="Is Covid"
                                    ></v-checkbox>
                                </v-col>
                            </v-row>

                            <v-row>
                                <v-col cols="12" md="6" v-show="show_categories">

                                    <v-menu
                                        ref="menu1"
                                        v-model="menu1"
                                        :close-on-content-click="false"
                                        :nudge-right="40"
                                        transition="scale-transition"
                                        max-width="290px"
                                        min-width="290px"
                                        attach
                                        :offset-y="y"
                                        top
                                    >
                                        <template v-slot:activator="{ on, attrs }">
                                            <v-text-field
                                                autocomplete="off"
                                                v-model="start_time"
                                                label="Start Time"
                                                prepend-icon="mdi-clock-outline"
                                                v-bind="attrs"
                                                v-on="on"
                                            ></v-text-field>
                                        </template>
                                        <v-time-picker
                                            v-if="menu1"
                                            v-model="start_time"
                                            :allowed-minutes="allowedMinutes"
                                            :allowed-hours="allowedStartHours"
                                            full-width
                                            scrollable
                                        ></v-time-picker>
                                    </v-menu>
                                </v-col>

                                <v-col cols="12" md="6" v-show="!show_categories">

                                    <v-text-field
                                        prepend-icon="mdi-clock-outline"
                                        label="Start Time"
                                        hint="Selected by Preset"
                                        persistent-hint
                                        disabled
                                    ></v-text-field>
                                </v-col>

                                <v-col cols="12" md="6" v-show="show_categories">

                                    <v-menu
                                        ref="menu2"
                                        v-model="menu2"
                                        :close-on-content-click="false"
                                        :nudge-right="40"
                                        transition="scale-transition"
                                        max-width="290px"
                                        min-width="290px"
                                        attach
                                        :offset-y="y"
                                        top
                                    >
                                        <template v-slot:activator="{ on, attrs }">
                                            <v-text-field
                                                autocomplete="off"
                                                v-model="end_time"
                                                label="End Time"
                                                prepend-icon="mdi-clock-outline"
                                                v-bind="attrs"
                                                v-on="on"
                                            ></v-text-field>
                                        </template>
                                        <v-time-picker
                                            v-if="menu2"
                                            v-model="end_time"
                                            :allowed-minutes="allowedMinutes"
                                            :allowed-hours="allowedEndHours"
                                            full-width
                                            scrollable
                                        ></v-time-picker>
                                    </v-menu>
                                </v-col>

                                <v-col cols="12" md="6" v-show="!show_categories">

                                    <v-text-field
                                        prepend-icon="mdi-clock-outline"
                                        label="Start Time"
                                        hint="Selected by Preset"
                                        persistent-hint
                                        disabled
                                    ></v-text-field>
                                </v-col>
                            </v-row>

                            <v-row>                                
                                <v-col cols="12" md="6">
                                    <v-select
                                        v-model="credential"
                                        :items="types"
                                        label="Nurse Type"
                                        hint="Leave blank to accept any type of nurse."
                                        persistent-hint
                                        prepend-icon="mdi-account"
                                        clearable
                                    ></v-select>
                                </v-col>
                            </v-row>

                            <!-- <v-row>
                                <v-col cols="12" md="6"> may add later if assigning nurses is added
                                    <v-select
                                        v-show="createShiftObj.copies < 2"
                                        prepend-icon="mdi-bookmark-multiple"
                                        v-model="status"
                                        label="Shift Status"
                                        :items="statusItems"
                                    ></v-select>

                                    <v-select
                                        v-show="createShiftObj.copies > 1"
                                        prepend-icon="mdi-bookmark-multiple"
                                        label="Shift Status"
                                        :items="['Open']"
                                        v-model="status"
                                        disabled
                                        hint="Too many copies to assign a nurse."
                                        persistent-hint
                                    ></v-select>
                                </v-col>
                            </v-row> -->

                            <!-- <v-row>                            

                                <v-col cols="12" md="6" v-show="createShiftObj.copies < 2 && status == 'Assigned'">
                                    <v-autocomplete
                                        v-model="nurse"
                                        :items="assignable_nurses"
                                        item-text="name"
                                        :label="status == 'Open' ? 'Nurse' : 'Assigned Nurse'"
                                        attach
                                        return-object
                                        prepend-icon="mdi-account"
                                        clearable
                                    ></v-autocomplete>
                                </v-col>

                                <v-col cols="12" md="6" v-show="createShiftObj.copies > 1 && status == 'Assigned'">
                                    <v-autocomplete
                                        label="Assigned Nurse"
                                        prepend-icon="mdi-account"
                                        disabled
                                        hint="Too many copies to assign a nurse."
                                        persistent-hint
                                    ></v-autocomplete>
                                </v-col>
                            </v-row> -->
                        </v-form>
                    </v-card-text>
                    
                    <v-card-actions class="d-flex px-8 pb-8">
                        
                        <v-btn
                            text
                            class="pa-2 ml-auto"
                            @click="createShiftDialog = false"
                        >Close</v-btn>
                        
                        <v-btn
                            color="success"
                            @click="saveShifts"
                            class="pa-2"
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

            loading: false,
            // nurseCredentials: [],
            presetShiftTimes: [],
            // nursesAvailable: [],
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
            createShiftObj: {},
            selectedTime: {},
            createShiftDialog: false,
            snackbar: {
                status: false,
                message: [],
                color: 'success',
                timeout: 5000
            },
            datesValidationStatus: false,
            userType: '',
            providers: [],
            types: ['CNA', 'CMT', 'CMT/LPN/RN', 'LPN/RN'],
            credential: '',
            incentive_options: [
                {
                    name: 'None',
                    val: 1
                },
                {
                    name: '1.5x Pay',
                    val: 1.5
                },
                {
                    name: 'Double Pay',
                    val: 2
                }
            ],
            incentive: 1,
            status: 'Open',
            statusItems: [
                {text: 'Open', value: 'Open'}, 
                {text: 'Pending', value: 'Pending'}, 
                {text: 'Assigned', value: 'Assigned'}, 
                {text: 'Approved', vlue: 'Approved', disabled: !this.can_create_approved}
            ],
            nurse_type: '',
            nurse: null,
            assignable_nurses: [],
            menu1: false,
            menu2: false,
            start_time: null,
            end_time: null,
            y: true,
            category: null,
            categories: [],
            show_categories: true,
            provider_id: null,
            bonus: {
                amount: null,
                description: null,
            },
            category_id: null,
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
            // billRates : function() {
            //     if (!this.createShiftObj.credential) {
            //         return null;
            //     }
            //     return this.createShiftObj.credential.split('/');
            // },
            premiumRateSelected: function() {
                return this.createShiftObj.premiumRate.value > 1.0;
            },
        },
        watch: {
            // Updates the selected nurse on the object for creating when submitting the modal
            // nursesAvailable(newNurses, oldNurses) {
            //     let matched = false;
            //     if(newNurses && this.createShiftObj.nurse) {
            //         newNurses.forEach(nurse => {
            //             if (this.createShiftObj.nurse.text === nurse.text) {
            //                 matched = true;
            //             }
            //         });
                    
            //         if (!matched) {
            //             this.createShiftObj.nurse = null;
            //         }
            //     }
            // },
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
            this.setNextMonth();
            this.loadProviders();
            this.getCategories();
        },
        mounted() {
        },
        methods: {
            inNextSixtyDays(val) {
                let valDate = new Date(val);

                let todayDate = new Date();
                todayDate.setDate(todayDate.getDate() - 1);

                let sixtyDaysFromNowDate = new Date();
                sixtyDaysFromNowDate.setDate(sixtyDaysFromNowDate.getDate() + 60);

                return (valDate >= todayDate && valDate <= sixtyDaysFromNowDate);
            },
            saveShifts() {

                this.loading = true;
                this.datesValidationStatus = false;

                if(!this.createShiftObj.dates.length) {
                    this.datesValidationStatus = true;
                }
                
                let formValidation = this.validateForm();
                if (formValidation['passed']) {

                    let data = {
                        shift: this.createShiftObj
                    }
                    data.shift.provider_id = this.provider_id;
                    data.shift.bonus = this.bonus;
                    if (!this.createShiftObj.selectedTime.start_time) {
                        data.shift.selectedTime.start_time = this.start_time;
                        data.shift.selectedTime.end_time = this.end_time;
                    }
                    data.shift.selectedTime.category_id = this.category_id;
                    data.shift.credential = this.credential;

                    modRequest.request('provider.save.new_shift', {}, data, response => {

                        if(response.success) {

                            this.snackbar.message = [];

                            this.snackbar.message.push("Shift(s) saved successfully!");
                            this.snackbar.color = 'success';
                            this.snackbar.status = true;
                            this.$emit('triggerRefresh');

                            this.resetModal();
                            this.createShiftDialog = false;
                            this.loading = false;

                        } else {

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
                            this.loading = false;
                        }
                    }, response => {
                        
                        this.createShiftDialog = false;
                        console.log('Failed');
                        this.snackbar.message = [];
                        
                        this.snackbar.message.push("Failed to save shift(s)...");
                        this.snackbar.color = 'error';
                        this.snackbar.status = true;
                        this.loading = false;
                    });
                } else {

                    this.snackbar.message = [];
                    this.snackbar.message.push(formValidation['message']);
                    this.snackbar.color = 'error';
                    this.snackbar.status = true;
                    this.loading = false;
                }
            },
            validateForm() {

                let validation = {

                    passed: false,
                    message: ''
                };

                if (this.provider_id == null) {

                    validation.message = 'Please select a provider.';
                    return validation;
                }

                if (this.createShiftObj.dates.length == 0) {

                    validation.message = 'Please select at least one date.';
                    return validation;
                }

                let preselectedTimeslot = this.createShiftObj.selectedTime.category_id;
                if (preselectedTimeslot == 0) {

                    if (!this.start_time) {

                        validation.message = 'Please select a timeslot or enter a custom time.';
                        return validation;
                    } else if (!this.end_time) {

                        validation.message = 'Please select a timeslot or enter a custom time.';
                        return validation;
                    } else if (!this.category_id) {

                        validation.message = 'Please choose a shift category.';
                        return validation;
                    }
                }

                if (this.createShiftObj.copies < 1) {

                    validation.message = 'Please enter a number of copies greater than 0.';
                    return validation;
                }

                validation.passed = true;
                return validation;
            },
            setNextMonth() {
                this.createShiftCalendarData.nextMonthDate = luxon.DateTime.now().plus({months: 1}).toFormat('yyyy-MM');
                this.createShiftCalendarData.currentMonth = luxon.DateTime.now().toFormat('MMMM' );
                this.createShiftCalendarData.nextMonth = luxon.DateTime.now().plus({months: 1}).toFormat('MMMM' );
            },
            resetModal() {

                this.datesValidationStatus = false;
                this.createShiftObj = {...this.createShiftTemplate};
                this.datesOne = [];
                this.datesTwo = [];
                this.provider_id = null;
                this.bonus = {

                    amount: null,
                    description: null,
                };
                this.start_time = null;
                this.end_time = null;
                this.category_id = null;
                this.credential = null;
                this.show_categories = true;
                this.getCategories();
            },
            loadProviders() {

                this.loading = true;
                modRequest.request('sa.shift.load_providers', {}, {}, function(response) {
                    if(response.success) {
                        this.providers = response.providers;
                        this.loading = false;
                    } else {
                        console.log('Error');
                        console.log(response);
                        this.loading = false;
                    }
                }.bind(this), function(response) {
                    console.log('Failed');
                    console.log(response);
                    this.loading = false;
                });
                this.loading = false;
            },
            getPresetShifts() {

                this.loading = true;
                data = {
                    provider_id: this.provider_id,
                }
                
                modRequest.request('provider.get.preset_shift_times', null, data, function(response) {
                    if (response.success) {
                        
                        this.presetShiftTimes = response.presetShiftTimes;
                        let customShiftTime = {

                            text: 'Custom',
                            id: 0,
                            value: 'custom',
                            start_time: null,
                            end_time: null,
                            category_id: 0,
                            human_readable: 'Custom',
                        };
                        this.presetShiftTimes.push(customShiftTime);
                        this.createShiftObj.selectedTime = customShiftTime;
                        this.loading = false;
                    }
                }.bind(this));
                this.loading = false;
            },
            getCategories() {

                this.loading = true;
                modRequest.request('sa.shift.load.categories', null, {},
                    function(response){

                        if(response.data.success){
                            this.categories = response.data.categories;
                            this.loading = false;
                        }
                    }.bind(this),
                    function(error){
                        this.loading = false;
                    }
                );
                this.loading = false;
            },
            showCategories() {

                if (!this.createShiftObj.selectedTime.start_time) {
                    this.show_categories = true;
                } else {
                    this.show_categories = false;
                }

                this.category_id = this.createShiftObj.selectedTime.category_id;
            },
            //
            // May add later
            // 
            // loadAssignableNurses() { 

            //     if(this.status == 'Open') return;

            //     let data = {
            //         // id: this.id,
            //         // recurrence_id: this.recurrence_id,
            //         provider_id: this.provider_id,
            //         start_time: this.createShiftObj.selectedTime.start_time,
            //         end_time: this.createShiftObj.selectedTime.end_time,
            //         start_date: this.start_date,
            //         nurse_type: this.nurse_type
            //     };

            //     console.log('loadAssignableNurses() -> data: ', data);
            //     let assignable_nurses = [];

            //     modRequest.request('sa.shift.load_assignable_nurses', {}, data, function(response) {

            //         if(response.success) {
            //             for(var i = 0; i < response.nurses.length; i++) {
            //                 var nurse = response.nurses[i];
            //                 var assignable_nurse = {
            //                     id: nurse.id,
            //                     name: nurse.name,
            //                     disabled: nurse.disabled,
            //                 };
            //                 if(this.nurse_id == nurse.id) {
            //                     assignable_nurse.disabled = false;
            //                 }
            //                 assignable_nurses.push(assignable_nurse);
            //             }
            //             assignable_nurses.sort((a, b) => a.name.localeCompare(b.name));
            //             this.assignable_nurses = assignable_nurses;
            //             this.is_loaded = true;
            //             this.loading = false;
            //         } else {
            //             console.log('Error');
            //             console.log(response);
            //             this.loading = false;
            //         }
            //     }.bind(this), function(response) {
            //         console.log('Failed');
            //         console.log(response);
            //         this.loading = false;
            //     });
            // },
        },
    });
});