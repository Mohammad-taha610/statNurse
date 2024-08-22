window.addEventListener('load', function() {
    Vue.component('sa-edit-shift-view', {
        // language=HTML
        template: /*html*/`
            <div class="container-fluid" style="height: 150vh;">
                <div class="row">
                    <div class="col-12">
                        <v-app>
                            <nst-overlay :loading="loading"></nst-overlay>
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
                                                    >
                                                    </v-date-picker>
                                                </v-menu>
                                            </v-col>
                                            <v-col cols="12" md="6">
                                                <v-select
                                                        v-model="category"
                                                        :items="categories"
                                                        item-text="text"
                                                        item-value="value"
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
                                                        hint="Leave blank to accept any type of nurse."
                                                        @change="statusChanged"
                                                        :rules="rules"
                                                        prepend-icon="mdi-account"
                                                        :disabled="statusDisabled"
                                                ></v-select>
                                            </v-col>
                                            <v-col cols="12" md="2">
                                                <v-text-field
                                                        v-model="bonus_amount"
                                                        label="Bonus"
                                                        prepend-icon="mdi-currency-usd"
                                                        type="number"
                                                        min="0"
                                                        step="0.01"
                                                        :disabled="statusDisabled"
                                                ></v-text-field>
                                            </v-col>
                                            <v-col cols="12" md="4">
                                                <v-text-field
                                                        v-model="bonus_description"
                                                        label="Bonus Description"
                                                        type="text"
                                                        :disabled="statusDisabled"
                                                ></v-text-field>
                                            </v-col>
                                        </v-row>
                                        <v-row> 
                                            <v-col cols="12" md="6">
                                                <v-select 
                                                    v-model="is_covid"
                                                    label="COVID Unit"
                                                    :items="['Yes', 'No']"
                                                    :rules="rules"
                                                    prepend-icon="mdi-alert-outline"
                                                    :disabled="statusDisabled"
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
                                                    :disabled="statusDisabled"
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
                                                    :rules="rules"
                                                    @change="statusChanged"
                                                    :disabled="statusDisabled"
                                                ></v-autocomplete>
                                            </v-col>
                                            <v-col cols="6" v-if="status != 'Open' && provider_id && assignable_nurses && (recurrence_type !== '' || is_recurrence)">
                                                <v-autocomplete
                                                    v-model="nurse"
                                                    :items="assignable_nurses"
                                                    item-text="name"
                                                    item-value="id"
                                                    :label="status == 'Open' ? 'Nurse' : 'Assigned Nurse'"
                                                    attach
                                                    return-object
                                                    prepend-icon="mdi-account"
                                                    clearable
                                                    @change="nurseChanged"
                                                ></v-autocomplete>
                                                <template v-if="!has_nurse_set"><span class="text-danger">You must assign a nurse in order to save this shift.</span></template>
                                            </v-col>
                                        </v-row>
                                        <v-row> 
                                            <v-col cols="12" md="6">
                                                <v-select
                                                        prepend-icon="mdi-bookmark-multiple"
                                                        v-model="status"
                                                        label="Shift Status"
                                                        @change="statusChanged"
                                                        :items="statusItems"
                                                ></v-select>
                                            </v-col>
                                        </v-row>
                                        <v-row v-if="status != 'Open' && provider_id && assignable_nurses && (!recurrence_type || recurrence_type == 'None' || is_recurrence)">
                                            <v-col cols="6" v-if="original_nurse && status != 'Open' && (!nurse || nurse.id != original_nurse.id)" class="text-center">
                                                <v-icon>mdi-alert</v-icon>
                                                <span class="subtitle-1">
                                                    Warning - By changing or removing nurse, it will deny the shift from the original nurse and assign a new nurse.
                                                </span>
                                            </v-col>
                                        </v-row>
                                        <v-row v-if="status == 'Open' && !id && !nurse">
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
                                                            v-bind="attrs"
                                                            :disabled="!has_nurse_set"
                                                        >
                                                            Save Shift
                                                        </a>
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
                                                                    color="light"
                                                                    v-on:click="approve_nurse = false; deny_nurse = false; saveShift()"
                                                                >No Action</v-btn>
                                                                <v-btn
                                                                    color="red"
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
                                                                color="warning"
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
                                                                    color="warning"
                                                                    v-on:click="nurse_changed = true; saveShift(); dialog.value = false;"
                                                                    class="white--text"
                                                                >I agree</v-btn>
                                                            </v-card-actions>
                                                        </v-card>
                                                    </template>
                                                </v-dialog>
                                                <a v-else href="javascript:void(0)" class="btn btn-primary mr-1 rounded" v-on:click="saveShift(); isActive = false;" :disabled="!checkIsActive">Save Shift</a>
                                                <a v-if="id > 0 && nurse !== null" href="javascript:void(0)" class="btn btn-warning rounded white--text" v-on:click="cancelShift()">Cancel Shift</a>
                                                <a href="javascript:void(0)" class="btn btn-danger rounded" v-on:click="reset">Reset</a>
                                            </v-col>
                                        </v-row>
                                    </v-form>
                                </div>
                            </div>
                            <v-dialog max-width="500"
                                v-model="expiredDocsDialog.active">
                                <v-card>
                                    <v-toolbar
                                        color="warning"
                                        class="text-h4 white--text">
                                        Nurse Document Expired
                                    </v-toolbar>
                                    <v-card-text
                                        class="pt-5">
                                        The selected nurse has doccuments that are expired or will expire before the shift time(s) selected.<br> {{expiredDocsDialog.message}} <br> Assign {{ nurse ? nurse.name : '' }} anyway?
                                    </v-card-text>
                                    <v-card-actions>
                                        <v-spacer></v-spacer>
                                        <v-btn
                                            color="light"
                                            v-on:click="expiredDocsDialog.active = false"
                                        >Cancel
                                        </v-btn>
                                        <v-btn
                                            color="success"
                                            v-on:click="override_expiring_docs = true; saveShift(); expiredDocsDialog.active = false;"
                                            class="white--text"
                                        >Yes</v-btn>
                                    </v-card-actions>
                                </v-card>
                            </v-dialog>
                        </v-app>
                    </div>
                </div>
            </div>
        `,
        props:[
            'id',
            'source_id',
            'providerid',
            'nurse_id',
            'recurrence_id',
            'recurrence_unique_id',
            'recurrence_source_id',
            'is_recurrence',
            'is_copy',
            '_start_date',
            '_end_date',
            'can_create_approved'
        ],
        data () {
            return {
                isActive: true,
                remove_nurse_loading: false,
                loading: false,
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
                status: 'Open',
                disable_options: true,
                disable_end_date: true,
                disable_recurrence_end_date: true,
                disable_recurrence_interval: true,
                start_date_label: 'Date',
                end_date_enabled: false,
                assignable_nurses: [],
                providers: [],
                provider_id: 0,
                nurse: null,
                original_nurse: null,
                provider: null,
                timespan: 'Days',
                showChangedNurseWarning: false,
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
                approve_nurse: false,
                deny_nurse: false,
                nurse_changed: false,
                number_of_copies: 1,
                is_loaded: false,
                has_nurse_set: false,
                statusItems: [
                    {text: 'Open', value: 'Open'}, 
                    {text: 'Pending', value: 'Pending'}, 
                    {text: 'Assigned', value: 'Assigned'}, 
                    {text: 'Approved', vlue: 'Approved', disabled: !this.can_create_approved}],
                override_expiring_docs: false,
                expiredDocsDialog: {
                    active: false,
                    message: ''
                },
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
                // Easy way to revert this just for NurseStat admins
                return false;
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
            if(this.id > 0) {
                this.loading = true;
            }
            this.getShiftData();
            this.updateRecurrenceFields();
        },
        mounted: function() {
            this.provider_id = this.providerid;
        },
        methods: {
            checkIsActive() {
                return this.isActive;
            },
            nurseChanged() {
                this.has_nurse_set = (this.nurse != null ? this.nurse.id : null) !== null;
                this.isActive = !!this.has_nurse_set;
            },
            statusChanged() {
                if(this.status !== 'Open' && this.provider_id > 0) {
                    this.loading = true;
                    this.loadAssignableNurses();
                }
            },
            saveShift () {
                this.loading = true;
                this.remove_nurse_loading = true;
                if(this.is_recurrence && !this.is_copy) {
                    this.save();
                    return;
                }

                let params = {
                    "id": this.is_copy ? 0 : this.id,
                    "provider_id": this.provider_id,
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
                    "is_covid": this.is_covid,
                    "incentive": this.incentive,
                    "status": this.status,
                    "action_type": this.id > 0 ? "save" : "create",
                    "override_expiring_docs": this.override_expiring_docs,
                };

                if (this.status !== 'Open' && (this.nurse != null ? this.nurse.id : null) === null) {
                    this.loading = false;
                    this.isActive = true;
                    this.has_nurse_set = false;
                    return;
                }

                this.rules = [ v => !!v || 'This field is required' ];
                this.time_rules = [ v => !!v || 'This field is required' ];
                this.copies_rules = [
                    v => !!v || 'This field is required',
                    v => this.validateCopies(v) || 'Must be greater than 0'
                ];

                this.$refs.form.validate();
                setTimeout(function() {
                    if (this.$refs.form.validate()) {
                        modRequest.request('sa.shift.save_shift', null, {'params': params},
                            function (response) {
                                console.log('sa.shift.save_shift response: ', response);
                                if (!response.success) {
                                    this.loading = false;
                                    this.isActive = true;

                                    if(response.errorCode){
                                        this.expiredDocsDialog.active = true;
                                        this.expiredDocsDialog.message = response.error;
                                        console.log(response.errorCode);
                                    } else {
                                        window.location.reload();
                                    }
                                } else {
                                    if(response.errorCode){
                                        this.expiredDocsDialog.active = true;
                                        this.expiredDocsDialog.message = response.error;
                                        console.log(response.errorCode);
                                    } else {
                                        window.location.reload();
                                    }
                                }
                            }.bind(this),
                            function (error) {
                                this.loading = false;
                                this.isActive = true;
                            }.bind(this)
                        );
                    } else {
                        this.loading = false;
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
                    "provider_id": this.provider_id,
                    "approve_nurse": this.approve_nurse,
                    "deny_nurse": this.deny_nurse,
                    "nurse_changed": this.nurse_changed,
                    "is_covid": this.is_covid,
                    "incentive": this.incentive,
                    "status": this.status,
                    "action_type": this.id > 0 ? "save" : "create"
                };

                if (this.status !== 'Open' && (this.nurse != null ? this.nurse.id : null) === null) {
                    this.loading = false;
                    this.isActive = true;
                    return;
                }

                this.rules = [ v => !!v || 'This field is required' ]
                this.time_rules = [ v => !!v || 'This field is required' ];
                this.copies_rules = [
                    v => !!v || 'This field is required',
                    v => this.validateCopies(v) || 'Must be greater than 0'
                ];
                this.$refs.form.validate()
                setTimeout(function() {
                    if (this.$refs.form.validate()) {
                        modRequest.request('sa.shift.save_shift', {}, {'params': params}, function (response) {
                            if (response.success) {
                                window.location.reload();
                                // window.location.href = response.url;
                            } else {
                                this.loading = false;
                                this.isActive = true;
                                console.log('Error');
                                console.log(response);
                            }
                        }, function (response) {
                            this.loading = false;
                            this.isActive = true;
                            console.log('Failed');
                            console.log(response);
                        }.bind(this));
                    } else {
                        this.loading = false;
                        this.isActive = true;
                        window.scrollTo(0, 0);
                    }
                }.bind(this), 100);
            },
            getShiftData () {
                modRequest.request('sa.shift.load.categories', null, {},
                    function(response){
                        if(response.data.success){
                            this.categories = response.data.categories;
                        }
                    }.bind(this),
                    function(error){
                    }
                );

                let data = {
                    id: this.is_copy > 0 ? this.source_id : this.id
                };
                // if(this.is_recurrence) {
                //     this.getRecurrenceData();
                // } else {
                    modRequest.request('sa.shift.load_shift_data', {}, data, function (response) {
                        console.log("load_shift_data response: ", response);
                        if (response.success) {
                            var shift = response.data;

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
                                this.has_nurse_set = true;
                                this.nurse = {
                                    id: shift.nurse_id,
                                    name: shift.nurse_name
                                }
                                this.original_nurse = {
                                    id: shift.nurse_id,
                                    name: shift.nurse_name
                                }
                            } else {
                                this.isActive = false;
                            }
                            this.nurse_type = shift.nurse_type;
                            this.recurrence_type = shift.recurrence_type;
                            this.recurrence_option = shift.recurrence_options;
                            this.recurrence_end_date = shift.recurrence_end_date;
                            this.recurrence_interval = 1;
                            this.status = shift.status;
                            this.provider_id = shift.provider_id;
                            this.is_covid = shift.is_covid ? 'Yes' : 'No';
                            this.incentive = shift.incentive;

                            this.updateRecurrenceFields();
                            this.loadAssignableNurses();
                            this.loading = false;
                        } else {
                            console.log('Error');
                            console.log(response);
                        }
                    }.bind(this), function (response) {
                        console.log('Failed');
                        console.log(response);
                    });
                // }
                this.loadProviders();
            },
            getRecurrenceData() {
                let data = {
                    id: this.id,
                    recurrence_id: this.is_copy > 0 ? this.recurrence_source_id : this.recurrence_id,
                    recurrence_unique_id: this.recurrence_unique_id,
                    start_date: this._start_date,
                    end_date: this._end_date
                };
                modRequest.request('sa.shift.load_recurrence_data', {}, data, function(response) {
                    if(response.success) {
                        var shift = response.data;

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
                        this.incentive = shift.incentive;

                        this.updateRecurrenceFields();
                        this.loadAssignableNurses();
                        this.loading = false;
                    } else {
                        console.log('Error');
                        console.log(response);
                    }
                }.bind(this), function(response) {
                    console.log('Failed');
                    console.log(response);
                });
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
            loadAssignableNurses() {
                if(this.status == 'Open') return;
                var data = {
                    id: this.id,
                    recurrence_id: this.recurrence_id,
                    provider_id: this.provider_id,
                    start_time: this.start_time,
                    end_time: this.end_time,
                    start_date: this.start_date,
                    nurse_type: this.nurse_type
                };
                console.log('loadAssignableNurses() -> data: ', data);
                let assignable_nurses = [];
                modRequest.request('sa.shift.load_assignable_nurses', {}, data, function(response) {
                    if(response.success) {
                        for(var i = 0; i < response.nurses.length; i++) {
                            var nurse = response.nurses[i];
                            var assignable_nurse = {
                                id: nurse.id,
                                name: nurse.name,
                                disabled: nurse.disabled,
                            };
                            if(this.nurse_id == nurse.id) {
                                assignable_nurse.disabled = false;
                            }
                            assignable_nurses.push(assignable_nurse);
                        }
                        assignable_nurses.sort((a, b) => a.name.localeCompare(b.name));
                        this.assignable_nurses = assignable_nurses;
                        this.is_loaded = true;
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
            },
            reset () {
                this.$refs.form.reset();
            },
            validateTime (v) {
                if(this.end_date != null){
                    return Date.parse(this.start_date + ' ' + this.start_time) < Date.parse(this.end_date + ' ' + this.end_time);
                }
                else {
                    return Date.parse(this.start_date + ' ' + this.start_time) < Date.parse(this.start_date + ' ' + this.end_time);
                }
            },
            validateCopies (v) {
                return this.number_of_copies > 0;
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
            },
        }
    });
});
