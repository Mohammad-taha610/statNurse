window.addEventListener('load', function() {
    Vue.component('shift-view', {
        template: `
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <v-app>
                            <div class="card">
                                <div class="card-header">
                                    <h4 v-if="id > 0" class="card-title">Edit Shift</h4>
                                    <h4 v-else class="card-title">Create Shift</h4>
                                </div>
                                <div class="card-body">
                                    <v-form ref="form" v-model="valid">
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
                                                >
                                                    <template v-slot:activator="{ on, attrs }">
                                                        <v-text-field
                                                                autocomplete="off"
                                                                v-model="start_time"
                                                                label="Start Time"
                                                                prepend-icon="mdi-clock-outline"
                                                                v-bind="attrs"
                                                                v-on="on"
                                                                :rules="time_rules"
                                                                :disabled="statusDisabled"
                                                        ></v-text-field>
                                                    </template>
                                                    <v-time-picker
                                                            v-if="menu1"
                                                            v-model="start_time"
                                                            :allowed-minutes="allowedMinutes"
                                                            :allowed-hours="allowedStartHours"
                                                            full-width
                                                    ></v-time-picker>
                                                </v-menu>
                                            </v-col>
                                            <v-col cols="12" md="6">
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
                                                >
                                                    <template v-slot:activator="{ on, attrs }">
                                                        <v-text-field
                                                                autocomplete="off"
                                                                v-model="end_time"
                                                                label="End Time"
                                                                prepend-icon="mdi-clock-outline"
                                                                v-bind="attrs"
                                                                v-on="on"
                                                                :rules="time_rules"
                                                                :disabled="statusDisabled"
                                                        ></v-text-field>
                                                    </template>
                                                    <v-time-picker
                                                            v-if="menu2"
                                                            v-model="end_time"
                                                            :allowed-minutes="allowedMinutes"
                                                            :allowed-hours="allowedEndHours"
                                                            full-width
                                                    ></v-time-picker>
                                                </v-menu>
                                            </v-col>
                                        </v-row>
                                        <v-row>
                                            <v-col cols="12" md="6">
                                                <v-menu
                                                        ref="menu3"
                                                        v-model="menu3"
                                                        :close-on-content-click="false"
                                                        :nudge-right="40"
                                                        transition="scale-transition"
                                                        max-width="290px"
                                                        min-width="290px"
                                                        attach
                                                        :offset-y="y"
                                                >
                                                    <template v-slot:activator="{ on, attrs }">
                                                        <v-text-field
                                                                autocomplete="off"
                                                                v-model="start_date"
                                                                :label="start_date_label"
                                                                prepend-icon="mdi-calendar"
                                                                readonly
                                                                v-bind="attrs"
                                                                v-on="on"
                                                                :rules="rules"
                                                                :disabled="statusDisabled"
                                                        ></v-text-field>
                                                    </template>
                                                    <v-date-picker
                                                            v-if="menu3"
                                                            v-model="start_date"
                                                            no-title
                                                            scrollable
                                                            :min="todayDate"
                                                            :max="max_start_date"
                                                    >
                                                    </v-date-picker>
                                                </v-menu>
                                            </v-col>
                                            <v-col cols="12" md="6">
                                                <v-select
                                                        v-model="category"
                                                        :items="categories"
                                                        label="Category"
                                                        :rules="rules"
                                                        prepend-icon="mdi-account"
                                                        attach
                                                        :disabled="statusDisabled"
                                                ></v-select>
                                            </v-col>
                                        </v-row>
                                        <v-row>
                                            <v-col cols="12" md="6">
                                                <v-select
                                                        v-model="nurse_type"
                                                        :items="types"
                                                        label="Nurse Type"
                                                        persistent-hint
                                                        :rules="rules"
                                                        prepend-icon="mdi-account"
                                                        :disabled="statusDisabled"
                                                        @change="statusChanged"
                                                ></v-select>
                                            </v-col>
                                            <v-col cols="12" md="2">
                                                <v-text-field
                                                        v-model="bonus_amount"
                                                        label="Bonus"
                                                        prepend-icon="mdi-currency-usd"
                                                        type="number"
                                                        min="50.00"
                                                        max="200.00"
                                                        step="0.01"
                                                        :disabled="statusDisabled || !bonus_allowed"
                                                ></v-text-field>
                                            </v-col>
                                            <v-col cols="12" md="4">
                                                <v-text-field
                                                        v-model="bonus_description"
                                                        label="Bonus Description"
                                                        type="text"
                                                        :disabled="statusDisabled || !bonus_allowed"
                                                ></v-text-field>
                                            </v-col>
                                        </v-row>
                                        <v-row> 
                                            <v-col cols="12" md="6">
                                                <v-select 
                                                    v-model="is_covid"
                                                    label="COVID UNIT"
                                                    :items="['Yes', 'No']"
                                                    :rules="rules"
                                                    prepend-icon="mdi-alert-outline"
                                                    :disabled="statusDisabled || !covid_allowed"
                                                    persistent-hint> 
                                                </v-select> 
                                            </v-col>
                                            <v-col cols="12" md="6">
                                                <v-select 
                                                    v-model="incentive"
                                                    label="Incentive"
                                                    :items="incentive_options"
                                                    item-text="name"
                                                    item-value="val"
                                                    :rules="rules"
                                                    prepend-icon="mdi-cash-multiple"
                                                    :disabled="statusDisabled || !bonus_allowed"
                                                    persistent-hint> 
                                                </v-select> 
                                            </v-col>
                                        </v-row>
                                        <transition name="fade">
                                            <v-row>
                                                <v-col cols="12" md="6" v-if="!is_recurrence">
                                                    <v-select
                                                            v-model="recurrence_type"
                                                            :items="recurrence_types"
                                                            label="Recurrence Type"
                                                            prepend-icon="mdi-repeat"
                                                            attach
                                                            :rules="rules"
                                                            v-on:change="updateRecurrenceFields"
                                                            :disabled="statusDisabled"
                                                    ></v-select>
                                                </v-col>
                                                <v-col cols="12" md="6" v-if="!is_recurrence && recurrence_type && recurrence_type == 'Weekly'">
                                                    <v-select
                                                            v-model="recurrence_option"
                                                            :items="recurrence_options"
                                                            item-text="name"
                                                            item-value="val"
                                                            label="Recurrence Options"
                                                            prepend-icon="mdi-format-list-checkbox"
                                                            attach
                                                            multiple
                                                            :disabled="disable_options || statusDisabled"
                                                    ></v-select>
                                                </v-col>
                                                <v-col cols="12" md="6" v-if="!is_recurrence && recurrence_type && recurrence_type == 'Custom'">
                                                    <v-menu
                                                            ref="menu6"
                                                            v-model="menu6"
                                                            :close-on-content-click="false"
                                                            :nudge-right="40"
                                                            transition="scale-transition"
                                                            max-width="290px"
                                                            min-width="290px"
                                                            attach
                                                            :offset-y="y"
                                                    >
                                                        <template v-slot:activator="{ on, attrs }">
                                                            <v-text-field
                                                                    v-model="recurrenceCustomDates"
                                                                    label="Recurrence Custom Dates"
                                                                    prepend-icon="mdi-calendar"
                                                                    v-bind="attrs"
                                                                    v-on="on"
                                                                    :disabled="statusDisabled"
                                                                    placeholder="Default: 30 days"
                                                            >
                                                            </v-text-field>
                                                        </template>
                                                        <v-date-picker
                                                                v-if="menu6"
                                                                v-model="recurrence_custom_dates"
                                                                multiple
                                                                no-title
                                                                scrollable
                                                                :min="recurrenceMin"
                                                                :max="recurrenceMax"
                                                        >
                                                        </v-date-picker>
                                                    </v-menu>
                                                </v-col>
                                            </v-row>
                                        </transition>
                                        <transition name="fade">
                                            <v-row v-if=" !is_recurrence && recurrence_type && recurrence_type != 'None' ">
                                                <v-col cols="12" md="6">
                                                    <v-menu
                                                            ref="menu5"
                                                            v-model="menu5"
                                                            :close-on-content-click="false"
                                                            :nudge-right="40"
                                                            transition="scale-transition"
                                                            max-width="290px"
                                                            min-width="290px"
                                                            attach
                                                            :offset-y="y"
                                                    >
                                                        <template v-slot:activator="{ on, attrs }">
                                                            <v-text-field
                                                                    v-model="recurrence_end_date"
                                                                    label="Recurrence End Date"
                                                                    prepend-icon="mdi-calendar"
                                                                    v-bind="attrs"
                                                                    v-on="on"
                                                                    :disabled="disable_recurrence_end_date || statusDisabled"
                                                                    placeholder="Default: 30 days"
                                                            >
                                                            </v-text-field>
                                                        </template>
                                                        <v-date-picker
                                                                v-if="menu5"
                                                                v-model="recurrence_end_date"
                                                                no-title
                                                                scrollable
                                                                :min="recurrenceMin"
                                                                :max="recurrenceMax"
                                                        >
                                                        </v-date-picker>
                                                    </v-menu>
                                                </v-col>
                                            </v-row>
                                        </transition>
                                        <v-row>
                                            <v-col cols="12">
                                                <v-textarea
                                                        v-model="description"
                                                        :value="description"
                                                        label="Description"
                                                        prepend-icon="mdi-calendar-text"
                                                        :disabled="statusDisabled"
                                                ></v-textarea>
                                            </v-col>
                                        </v-row>
                                        <v-row v-if="(recurrence_type !== '' || is_recurrence)">
                                            <v-col cols="6" v-if="status == 'Open'">
                                                <v-autocomplete
                                                    :disabled="!allow_editing"
                                                    v-model="nurse"
                                                    :items="assignable_nurses"
                                                    item-text="name"
                                                    item-value="id"
                                                    label="Assigned Nurse"
                                                    attach
                                                    return-object
                                                    prepend-icon="mdi-account"
                                                    clearable
                                                ></v-autocomplete>
                                            </v-col>
                                            <v-col cols="6" v-if="status == 'Approved'">
                                                <v-autocomplete
                                                    readonly
                                                    disabled
                                                    v-model="nurse"
                                                    :items="assignable_nurses"
                                                    item-text="name"
                                                    item-value="id"
                                                    label="Assigned Nurse"
                                                    attach
                                                    return-object
                                                    prepend-icon="mdi-account"
                                                    clearable
                                                ></v-autocomplete>
                                            </v-col>                                            
                                            <v-col cols="6" v-else>
                                                <v-autocomplete
                                                    :disabled="!allow_editing"
                                                    v-model="nurse"
                                                    :items="assignable_nurses"
                                                    item-text="name"
                                                    item-value="id"
                                                    label="Nurse"
                                                    return-object
                                                    attach
                                                    prepend-icon="mdi-account"
                                                    clearable
                                                ></v-autocomplete>
                                            </v-col>
                                            <v-col cols="6" v-if="original_nurse && status != 'Open' && (!nurse || nurse.id != original_nurse.id)" class="text-center">
                                                <v-icon>mdi-alert</v-icon>
                                                <span class="subtitle-1">
                                                    Warning - By changing or removing nurse, it will deny the shift from the original nurse and assign a new nurse.
                                                </span>
                                            </v-col>
                                        </v-row>
                                        <v-row v-if="!id && !nurse"> 
                                            <v-col cols="12" md="6">
                                                <v-text-field
                                                        v-model="number_of_copies"
                                                        label="Number of Copies"
                                                        prepend-icon="mdi-book-multiple"
                                                        type="number"
                                                        :rules="copies_rules"
                                                        :disabled="statusDisabled"
                                                ></v-text-field>
                                            </v-col>
                                        </v-row>
                                        <v-row>
                                            <v-col cols="12" class="text-center">
                                                <v-dialog max-width="500" v-if="original_nurse && (status == 'Pending' || (status != 'Open' && (!nurse || nurse.id != original_nurse.id)))">
                                                    <template v-slot:activator="{ on, attrs }">
                                                        <a 
                                                            href="javascript:void(0)" 
                                                            class="btn btn-primary mr-1 rounded white--text" 
                                                            v-on="on"
                                                            v-bind="attrs">Save Shift</a>
                                                    </template>
      
                                                    <template v-slot:default="dialog" >
                                                        <v-card v-if="nurse && nurse.id == original_nurse.id">
                                                            <v-toolbar
                                                                color="success"
                                                                class="text-h4 white--text"
                                                            >Approve Shift</v-toolbar>
                                                            <v-card-text
                                                                class="pt-5"
                                                            >Do you wish to <strong class="success--text">APPROVE</strong> {{nurse.name}}  for the requested shift?</v-card-text>
                                                            <v-card-actions>
                                                                <v-btn
                                                                    color="light"
                                                                    v-on:click="dialog.value = false"
                                                                >Cancel
                                                                </v-btn>
                                                                <v-spacer></v-spacer>
                                                                <v-btn
                                                                    color="dark"
                                                                    v-on:click="approve_nurse = false; deny_nurse = false; saveShift()"
                                                                    class="white--text"
                                                                >No Action</v-btn>
                                                                <v-btn
                                                                    color="danger"
                                                                    v-on:click="approve_nurse = false; deny_nurse = true; saveShift()"
                                                                    class="white--text"
                                                                >Deny</v-btn>
                                                                <v-btn
                                                                    color="success"
                                                                    v-on:click="approve_nurse = true; deny_nurse = false; saveShift()"
                                                                    class="white--text"
                                                                >Approve</v-btn>
                                                            </v-card-actions>
                                                        </v-card>
                                                        <v-card v-else-if="nurse && nurse.id != original_nurse.id">
                                                            <v-toolbar
                                                                color="warning"
                                                                class="text-h4 white--text"
                                                            >Changing Nurse</v-toolbar>
                                                            <v-card-text
                                                                class="pt-5"
                                                            >By completing this action, you will be <strong class="red--text">DENYING</strong> {{original_nurse.name}} from this shift and assigning it to {{nurse.name}}</v-card-text>
                                                            <v-card-actions>
                                                                <v-spacer></v-spacer>
                                                                <v-btn
                                                                    color="light"
                                                                    v-on:click="dialog.value = false"
                                                                >Cancel
                                                                </v-btn>
                                                                <v-btn
                                                                    color="warning"
                                                                    v-on:click="nurse_changed = true; saveShift()"
                                                                    class="white--text"
                                                                >I agree</v-btn>
                                                            </v-card-actions>
                                                        </v-card>
                                                        <v-card v-else>
                                                            <v-toolbar
                                                                color="danger"
                                                                class="text-h4 white--text"
                                                            >Remove Nurse</v-toolbar>
                                                            <v-card-text
                                                                class="pt-5"
                                                            >By completing this action, you will be <strong class="red--text">REMOVING</strong> the nurse from this shift</v-card-text>
                                                            <v-card-actions>
                                                                <v-spacer></v-spacer>
                                                                <v-btn
                                                                    color="light"
                                                                    v-on:click="dialog.value = false"
                                                                >Cancel
                                                                </v-btn>
                                                                <v-btn
                                                                    color="danger"
                                                                    v-on:click="nurse_changed = true; saveShift()"
                                                                    class="white--text"
                                                                >I agree</v-btn>
                                                            </v-card-actions>
                                                        </v-card>
                                                    </template>
                                                </v-dialog>
                                                <a v-else href="javascript:void(0)" class="btn btn-primary mr-1 rounded white--text" v-on:click="saveShift(); isActive = false;" :disabled="!isActive">Save Shift</a>
                                                <a v-if="id > 0 && nurse !== null" href="javascript:void(0)" class="btn btn-warning rounded white--text" v-on:click="cancelShift()">Cancel Shift</a>
                                                <a href="javascript:void(0)" class="btn btn-danger rounded white--text" v-on:click="reset">Reset</a>
                                            </v-col>
                                        </v-row>
                                    </v-form>
                                </div>
                            </div>
                        </v-app>
                    </div>
                </div>
            </div>
        `,
        props:[
            'id',
            'source_id',
            'provider_id',
            'nurse_id',
            'recurrence_id',
            'recurrence_unique_id',
            'recurrence_source_id',
            'is_recurrence',
            'is_copy',
            '_start_date',
            '_end_date'
        ],
        data () {
            return {
                isActive: true,
                allow_editing: true,
                menu1: false,
                menu2: false,
                menu3: false,
                menu4: false,
                menu5: false,
                menu6: false,
                valid: false,
                y: true,
                name: null,
                start_time: null,
                end_time: null,
                start_date: null,
                end_date: null,
                category: null,
                categories: [],
                types: ['CNA', 'CMT', 'CMT/LPN/RN', 'LPN/RN'],
                recurrence_types: ['None', 'Daily', 'Weekly', 'Custom'],
                recurrence_options: [],
                recurrence_option: [],
                recurrence_type: 'None',
                recurrence_end_date: null,
                recurrence_interval: 1,
                recurrence_custom_dates: null,
                description: '',
                bonus_amount: null,
                bonus_description: '',
                nurse_type: '',
                rules: [],
                time_rules: [],
                copies_rules: [],
                shift: null,
                disable_options: true,
                disable_end_date: true,
                disable_recurrence_end_date: true,
                disable_recurrence_interval: true,
                start_date_label: 'Date',
                end_date_enabled: false,
                assignable_nurses: [],
                nurse: null,
                original_nurse: null,
                timespan: 'Days',
                showChangedNurseWarning: false,
                status: '',
                approve_nurse: false,
                deny_nurse: false,
                nurse_changed: false,
                number_of_copies: 1,
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
                is_covid: 'No',
                todayDate: new Date().toLocaleDateString('en-CA'),
                max_start_date: new Date().toLocaleDateString('en-CA'),
                bonus_allowed: false,
                covid_allowed: false
            }
        },
        computed: {
            allowedMinutes: function(m) {
                return m => m % 15 === 0;
            },
            allowedStartHours: function(h) {
                if (this.end_time) {
                    return h => {
                        let startHour = h;
                        let endHour = Number(this.end_time.substring(0,2));
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
                if (this.start_time) {
                    return h => {
                        let endHour = h;
                        let startHour = Number(this.start_time.substring(0,2));
                        // Fancy math to add 24 hours of h is less than endHour
                        endHour += (endHour < startHour) * 24;
                        let difference = Math.abs(endHour - startHour)

                        return difference <= 16 && difference >= 4;
                    }
                } else {
                    return h => {return true;};
                }

            },
            recurrenceMin : function() {
                var date;
                if(this.start_date != null) {
                    date = new Date(this.start_date);
                } else {
                    date = new Date();
                }
                return date.toISOString().slice(0, 10);
            },
            recurrenceMax : function() {
                var date;
                if(this.start_date != null) {
                    date = new Date(this.start_date);
                } else {
                    date = new Date();
                }
                date.setDate(date.getDate() + 30);
                return date.toISOString().slice(0, 10);
            },
            statusDisabled : function() {
                return (this.id != 0 && this.is_copy == false) || !this.allow_editing;
            },
            recurrenceCustomDates: function() {
                if(!this.recurrence_custom_dates) {
                    return '';
                }
                var dates = '';
                for (var i = 0; i < this.recurrence_custom_dates.length; i++) {
                    dates += (i > 0 ? ', ' : '') + '(' + this.recurrence_custom_dates[i].substr(5) + ')';
                }
                return dates;
            }
        },
        created: function() {
            this.getShiftData();
            this.updateRecurrenceFields();
        },
        methods: {
            statusChanged() {
                if(this.status !== 'Open' && this.provider_id > 0) {
                    this.loading = true;
                    this.loadAssignableNurses();
                }
            },
            cancelShift() {
                let params = {
                    "shift_id": this.is_copy === 'true' ? 0 : this.id
                }

                modRequest.request('shift.cancel_shift', null, {'params': params},
                    function (response) {
                        if (response.success) {
                            this.isActive = true;
                            if (response.url) {
                                window.location.href = response.url;
                            }
                        }
                    }.bind(this),
                    function (error) {
                        this.isActive = true;
                    }.bind(this),
                );
            },
            //Todo: connect this to the backend somehow
            saveShift () {
                if(this.is_recurrence && !this.is_copy) {
                    this.saveRecurrence();
                    return;
                }

                let params = {
                    "id": this.is_copy ? 0 : this.id,
                    "name": this.name,
                    "start_time": this.start_time,
                    "end_time": this.end_time,
                    "start_date": this.start_date,
                    "end_date": this.end_date,
                    "end_date_enabled": this.end_date_enabled,
                    "nurse_type": this.nurse_type,
                    "bonus_amount": this.bonus_amount,
                    "bonus_description": this.bonus_description,
                    "recurrence_type": this.recurrence_type,
                    "recurrence_options": this.recurrence_option,
                    "recurrence_interval": this.recurrence_interval,
                    "recurrence_end_date": this.recurrence_end_date,
                    "recurrence_custom_dates": this.recurrence_custom_dates,
                    "description": this.description,
                    "category_id" : this.category,
                    "nurse_id" : this.nurse != null ? this.nurse.id : null,
                    "approve_nurse": this.approve_nurse,
                    "deny_nurse": this.deny_nurse,
                    "nurse_changed": this.nurse_changed,
                    "number_of_copies": this.number_of_copies,
                    "incentive": this.incentive,
                    "is_covid": this.is_covid,
                    "parent_id": this.parent_id,
                    "is_copy": this.is_copy,
                    "action_type": !this.id ? "create" : ""
                };

                this.rules = [ v => !!v || 'This field is required' ]
                this.time_rules = [ v => !!v || 'This field is required' ];
                this.copies_rules = [
                    v => !!v || 'This field is required',
                    v => this.validateCopies(v) || 'Must be greater than 0'
                ];
                this.$refs.form.validate();
                setTimeout(function() {
                    if (this.$refs.form.validate()) {
                        modRequest.request('shift.save_shift', null, {'params': params},
                            function (response) {
                                console.log('shift.save_shift response: ', response);
                                if (!response.success) {
                                    this.isActive = true;
                                }
                                window.location.reload();
                            }.bind(this),
                            function (error) {
                                this.isActive = true;
                            }.bind(this),
                        );
                    } else {
                        this.isActive = true;
                        window.scrollTo(0, 0);
                    }
                }.bind(this), 100);
            },
            saveRecurrence() {
                let data = {
                    id: this.id,
                    recurrence_id: this.recurrence_id,
                    recurrence_unique_id: this.recurrence_unique_id
                };
                let params = {
                    "id": this.id,
                    "name": this.name,
                    "start_time": this.start_time,
                    "end_time": this.end_time,
                    "start_date": this.start_date,
                    "end_date": this.end_date,
                    "end_date_enabled": this.end_date_enabled,
                    "nurse_type": this.nurse_type,
                    "bonus_amount": this.bonus_amount,
                    "bonus_description": this.bonus_description,
                    "description": this.description,
                    "category_id" : this.category,
                    "nurse_id" : this.nurse != null ? this.nurse.id : null,
                    "recurrence_id": this.recurrence_id,
                    "recurrence_unique_id": this.recurrence_unique_id,
                    "approve_nurse": this.approve_nurse,
                    "deny_nurse": this.deny_nurse,
                    "nurse_changed": this.nurse_changed,
                    "is_covid": this.is_covid,
                    "incentive": this.incentive,
                    "parent_id": this.parent_id,
                    "is_copy": this.is_copy,
                    "action_type": this.id === 0 ? "create" : ""
                };

                this.rules = [ v => !!v || 'This field is required' ]
                this.time_rules = [ v => !!v || 'This field is required' ];
                this.copies_rules = [
                    v => !!v || 'This field is required',
                    v => this.validateCopies(v) || 'Must be greater than 0'
                ];

                this.$refs.form.validate();
                setTimeout(function() {
                    if (this.$refs.form.validate()) {
                        modRequest.request('shift.save_shift', {}, {'params': params}, function (response) {
                            if (response.success) {
                                //window.location.href = response.url;
                                window.location.reload();
                            } else {
                                this.isActive = true;
                                console.log('Error');
                                console.log(response);
                            }
                        }, function (response) {
                            this.isActive = true;
                            console.log('Failed');
                            console.log(response);
                        });
                    } else {
                        this.isActive = true;
                        window.scrollTo(0, 0);
                    }
                }.bind(this), 100);
            },
            getShiftData () {
                modRequest.request('shift.load.categories', null, {},
                    function(response){
                        if(response.data.success){
                            this.categories = response.data.categories;
                        }
                    }.bind(this),
                    function(error){
                    }
                );

                let data = {
                    id: this.id //this.is_copy === 'true' ? this.source_id : this.id
                };

                // if(this.is_recurrence === 'true') {
                //     this.getRecurrenceData();
                // } else {
                    modRequest.request('shift.load_shift_data', {}, data, function (response) {
                        this.max_start_date = response.max_start_date;
                        this.bonus_allowed = response.bonus_allowed;
                        this.covid_allowed = response.covid_allowed;
                        if (response.success) {
                            var shift = response.data;
                            this.allow_editing = shift.allow_editing;

                            this.name = shift.name;
                            this.category = shift.category;
                            this.start_time = shift.start_time;
                            this.end_time = shift.end_time;
                            this.start_date = shift.start_date;
                            this.end_date = shift.end_date;
                            this.end_date_enabled = shift.end_date_enabled;
                            this.disable_end_date = !shift.end_date_enabled;
                            this.bonus_amount = shift.bonus_amount;
                            this.bonus_description = shift.bonus_description;
                            this.description = shift.description;
                            if(shift.nurse_id != null) {
                                this.nurse = {
                                    id: shift.nurse_id,
                                    name: shift.nurse_name
                                }
                                this.original_nurse = {
                                    id: shift.nurse_id,
                                    name: shift.nurse_name
                                }
                            }
                            this.nurse_type = shift.nurse_type;
                            this.recurrence_type = shift.recurrence_type;
                            this.recurrence_option = shift.recurrence_options;
                            this.recurrence_end_date = shift.recurrence_end_date;
                            this.recurrence_interval = 1;
                            this.status = shift.status;
                            this.is_covid = shift.is_covid ? 'Yes' : 'No';
                            this.incentive = shift.incentive ? shift.incentive : 1;


                            this.updateRecurrenceFields();
                        } else {
                            console.log('Error');
                            console.log(response);
                        }
                    }.bind(this), function (response) {
                        console.log('Failed');
                        console.log(response);
                    });
                // }
                let hasId = this.id; //this.is_copy ? this.source_id : this.id;
                if (hasId >= 0) {
                    this.loadAssignableNurses();
                }
            },
            loadAssignableNurses() {
                var data = {
                    provider_id: this.provider_id,
                    start_time: this.start_time,
                    end_time: this.end_time,
                    start_date: this.start_date,
                    nurse_type: this.nurse_type
                };
                console.log('loadAssignableNurses() -> data: ', data);
                let assignable_nurses = [];
                this.assignable_nurses = [];
                modRequest.request('provider.load_assignable_nurses', {}, data, function(response) {
                    if(response.success) {
                        if (response.nurses) {
                            for(let i = 0; i < response.nurses.length; i++) {
                                let nurse = response.nurses[i];
                                let assignable_nurse = {
                                    id: nurse.id,
                                    name: nurse.name,
                                    disabled: nurse.disabled
                                };
                                if(this.nurse_id == nurse.id) {
                                    assignable_nurse.disabled = false;
                                }
                                assignable_nurses.push(assignable_nurse);
                            }
                            assignable_nurses.sort((a, b) => a.name.localeCompare(b.name));
                            this.assignable_nurses = assignable_nurses;
                        }
                    } else {
                        console.log('Error');
                        console.log(response);
                    }
                }.bind(this), function(response) {
                    console.log('Failed');
                    console.log(response);
                });
            },
            getRecurrenceData() {
                let data = {
                    id: this.id,
                    recurrence_id: this.is_copy ? this.recurrence_source_id : this.recurrence_id,
                    recurrence_unique_id: this.recurrence_unique_id,
                    start_date: this._start_date,
                    end_date: this._end_date
                };
                modRequest.request('shift.load_recurrence_data', {}, data, function(response) {
                    this.max_start_date = response.max_start_date;
                    this.bonus_allowed = response.bonus_allowed;
                    this.covid_allowed = response.covid_allowed;
                    if(response.success) {
                        var shift = response.data;
                        this.allow_editing = shift.allow_editing;

                        this.name = shift.name;
                        this.category = shift.category;
                        this.start_time = shift.start_time;
                        this.end_time = shift.end_time;
                        this.start_date = shift.start_date;
                        this.end_date = shift.end_date;
                        this.end_date_enabled = shift.end_date_enabled;
                        this.disable_end_date = !shift.end_date_enabled;
                        this.bonus_amount = shift.bonus_amount;
                        this.bonus_description = shift.bonus_description;
                        this.description = shift.description;
                        if(shift.nurse_id != null) {
                            this.nurse = {
                                id: shift.nurse_id,
                                name: shift.nurse_name
                            };
                            this.original_nurse = {
                                id: shift.nurse_id,
                                name: shift.nurse_name
                            };
                        }
                        this.nurse_type = shift.nurse_type;
                        this.status = shift.status;
                        this.is_covid = shift.is_covid ? 'Yes' : 'No';
                        this.incentive = shift.incentive ? shift.incentive : 1;

                        this.updateRecurrenceFields();
                    } else {
                        console.log('Error');
                        console.log(response);
                    }
                }.bind(this), function(response) {
                    console.log('Failed');
                    console.log(response);
                });
            },
            reset () {
                this.$refs.form.reset();
            },
            validateCopies (v) {
                return this.number_of_copies > 0;
            },
            validateTime (v) {
                if(this.end_date != null){
                    return Date.parse(this.start_date + ' ' + this.start_time) < Date.parse(this.end_date + ' ' + this.end_time);
                }
                else {
                    return Date.parse(this.start_date + ' ' + this.start_time) < Date.parse(this.start_date + ' ' + this.end_time);
                }
            },
            repeatForever() {
                this.recurrence_end_date = '';
            },
            updateRecurrenceFields () {
                switch (this.recurrence_type){
                    case 'Daily':
                        this.disable_options = true;
                        this.disable_recurrence_end_date = false;
                        this.disable_recurrence_interval = false;
                        this.nurse = null;
                        this.timespan = 'Days';
                        break;
                    case 'Weekly':
                        this.recurrence_options = [
                            {name:'Sun', val:'SU'},
                            {name:'Mon', val:'MO'},
                            {name:'Tues', val:'TU'},
                            {name:'Wed', val:'WE'},
                            {name:'Thur', val:'TH'},
                            {name:'Fri', val:'FR'},
                            {name:'Sat', val:'SA'}
                        ];
                        this.disable_options = false;
                        this.disable_recurrence_end_date = false;
                        this.disable_recurrence_interval = false;
                        this.nurse = null;
                        this.timespan = 'Weeks';
                        break;
                    case 'Monthly':
                        this.recurrence_options = [
                            {name:'Jan', val:'Jan'},
                            {name:'Feb', val:'Feb'},
                            {name:'Mar', val:'Mar'},
                            {name:'Apr', val:'Apr'},
                            {name:'May', val:'May'},
                            {name:'Jun', val:'Jun'},
                            {name:'Jul', val:'Jul'},
                            {name:'Sep', val:'Sep'},
                            {name:'Oct', val:'Oct'},
                            {name:'Nov', val:'Nov'},
                            {name:'Dec', val:'Dec'}
                        ];
                        this.disable_recurrence_end_date = false;
                        this.disable_recurrence_interval = false;
                        this.nurse = null;
                        this.timespan = 'Months';
                        break;
                    case 'Yearly':
                        this.disable_options = true;
                        this.disable_recurrence_end_date = false;
                        this.disable_recurrence_interval = false;
                        this.nurse = null;
                        this.timespan = 'Years';
                        break;
                    default:
                        this.recurrence_options = [];
                        this.recurrence_option = [];
                        this.disable_options = true;
                        this.disable_recurrence_end_date = true;
                        this.disable_recurrence_interval = true;
                        break;
                }
            },
            toggleDate () {
                if (this.end_date_enabled) {
                    this.disable_end_date = false;
                    this.start_date_label = "Start Date";
                } else {
                    this.disable_end_date = true;
                    this.start_date_label = "Date";
                }
            },
            formatMMDDYYYY(date) {
                var year = date.getFullYear().toString();
                var month = (date.getMonth() + 1).toString();
                var day = date.getDate().toString();

                if (month.length < 2)
                    month = '0' + month;
                if (day.length < 2)
                    day = '0' + day;
                return month + '-' + day + '-' + year;
            },
        },
        watch: {
            start_time: function() {
                this.loadAssignableNurses();
            },
            end_time: function() {
                this.loadAssignableNurses();
            },
            start_date: function() {
                this.loadAssignableNurses();
            }
        }
    });
});
