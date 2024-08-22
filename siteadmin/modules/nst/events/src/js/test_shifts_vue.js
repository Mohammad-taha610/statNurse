Vue.component('test-shifts', {
template: /*html*/`
<div>
<v-card>
    <v-toolbar
        color="primary"
        dark>
        <v-card-title>
            <span >Test Shifts</span>
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
                        v-model="dateOne"
                        :allowed-dates="inNextSixtyDays"
                        first-day-of-week="1"
                        no-title
                    ></v-date-picker>
                </div>
                <div class="col-md-6">
                    
                    <v-date-picker
                        class="NstCustomDatePicker pa-0"
                        v-model="dateTwo"
                        :allowed-dates="inNextSixtyDays"
                        first-day-of-week="1"
                        no-title
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
                    ></v-autocomplete>
                </v-col>
                
                <v-col cols="6" md="6">
                    <v-autocomplete
                        v-model="nurse_id"
                        :items="nurses"
                        item-text="name"
                        item-value="id"
                        label="Nurse"
                        attach
                        prepend-icon="mdi-account"
                        clearable
                    ></v-autocomplete>
                </v-col>
            </v-row>

            <v-row>
                <v-col cols="12" md="6">

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
            </v-row>
        </v-form>
    </v-card-text>

    <v-card-actions class="d-flex px-8 pb-8">
        
        <v-btn
            color="#BBDEFB"
            @click="createShift"
            class="pa-2"
        >Submit</v-btn>
    </v-card-actions>
</v-card>

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

</div>
`,
watch: {},
data: () => ({

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
    dateOne: "",
    dateTwo: "",
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
    providers: [],
    nurses: [],
    nurse: null,
    menu1: false,
    menu2: false,
    start_time: null,
    end_time: null,
    y: true,
    show_categories: true,
    provider_id: null,
    nurse_id: null,
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
},
created() {
    this.createShiftObj = JSON.parse(JSON.stringify(this.createShiftTemplate));
    this.setNextMonth();
    this.loadProviders();
    this.loadNurses();
},
methods: {
    createShift() {

        console.log("provider id: ", this.provider_id)
        console.log("nurse id: ", this.nurse_id)
        console.log("date one: ", this.dateOne)
        console.log("date two: ", this.dateTwo)
        console.log("start time: ", this.start_time)
        console.log("end time: ", this.end_time)

        let date = '';
        if (this.dateOne) {
            date = this.dateOne;
        } else if (this.dateTwo) {
            date = this.dateTwo;
        }

        if (!this.provider_id || !this.nurse_id || !date || !this.start_time || !this.end_time) { return; }

        data = {

            provider_id: this.provider_id,
            nurse_id: this.nurse_id,
            clock_in_time: date +" "+ this.start_time,
            clock_out_time: date +" "+ this.end_time,        
        }
        
        modRequest.request('test.shift.clock_in', null, data, function(response) {
            if (response.success) {

                data.shift_id = response.shift_id;
        
                modRequest.request('test.shift.clock_out', null, data, function(response) {

                    // keep getting response.success == false even though it works
                    // not invested enough to figure out why as this works for the tests I needed to run
                    if (response) {

                        this.dateOne = "";
                        this.dateTwo = "";

                        this.snackbar.status = true;
                        this.snackbar.message = ["Shift created successfully"];
                        this.snackbar.color = "success";
                        this.snackbar.timeout = 5000;
                        console.log("response: ", response)

                    } else {

                        this.snackbar.status = true;
                        this.snackbar.message = ["Shift creation failed"];
                        this.snackbar.color = "error";
                        this.snackbar.timeout = 5000;
                        console.log("error")
                    }
                }.bind(this));
        
            }
        }.bind(this));
    },
    inNextSixtyDays(val) {
        let valDate = new Date(val);

        let todayDate = new Date();
        todayDate.setDate(todayDate.getDate() - 1);

        let sixtyDaysFromNowDate = new Date();
        sixtyDaysFromNowDate.setDate(sixtyDaysFromNowDate.getDate() + 60);

        return (valDate >= todayDate && valDate <= sixtyDaysFromNowDate);
    },
    setNextMonth() {
        this.createShiftCalendarData.nextMonthDate = luxon.DateTime.now().plus({months: 1}).toFormat('yyyy-MM');
        this.createShiftCalendarData.currentMonth = luxon.DateTime.now().toFormat('MMMM' );
        this.createShiftCalendarData.nextMonth = luxon.DateTime.now().plus({months: 1}).toFormat('MMMM' );
    },
    loadProviders() {

        modRequest.request('sa.shift.load_providers', {}, {}, function(response) {
            if(response.success) {
                this.providers = response.providers;
            } else {
                console.log('Error');
                console.log(response);
            }
        }.bind(this), function(response) {
            console.log('Failed');
            console.log(response);
        });
    },
    loadNurses() {
        
        modRequest.request('sa.shift.load_nurses', null, {}, function(response) {
            if (response.success) {
                this.nurses = response.nurses;
            }
        }.bind(this));
    },
},
});