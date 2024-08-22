window.addEventListener('load', function() {
    Vue.component('create-shift-view', {
        template: `
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title">Schedule a New Shift</h4>
                            </div>
                            <div class="card-body">
                                <v-app>
                                    <v-form ref="form" v-model="valid" lazy-validation>
                                        <v-row>
                                            <v-col cols="12" md="6">
                                                <v-text-field
                                                    v-model="name"
                                                    label="Name"
                                                    prepend-icon="mdi-currency-usd"
                                                    type="text"
                                                ></v-text-field>
                                            </v-col>
                                            <v-col cols="12" md="6">
                                                <v-select
                                                    v-model="category"
                                                    :items="categories"
                                                    label="Category"
                                                    prepend-icon="mdi-account"
                                                    attach
                                                ></v-select>
                                            </v-col>
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
                                                            v-model="start_time"
                                                            label="Start Time"
                                                            prepend-icon="mdi-clock-outline"
                                                            v-bind="attrs"
                                                            v-on="on"
                                                            :rules="time_rules"
                                                        ></v-text-field>
                                                    </template>
                                                    <v-time-picker
                                                        v-if="menu1"
                                                        v-model="start_time"
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
                                                            v-model="end_time"
                                                            label="End Time"
                                                            prepend-icon="mdi-clock-outline"
                                                            v-bind="attrs"
                                                            v-on="on"
                                                            :rules="time_rules"
                                                        ></v-text-field>
                                                    </template>
                                                    <v-time-picker
                                                        v-if="menu2"
                                                        v-model="end_time"
                                                        full-width
                                                    ></v-time-picker>
                                                </v-menu>
                                            </v-col>
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
                                                            v-model="start_date"
                                                            :label="start_date_label"
                                                            prepend-icon="mdi-calendar"
                                                            readonly
                                                            v-bind="attrs"
                                                            v-on="on"
                                                            :rules="rules"
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
                                                <v-menu
                                                    ref="menu4"
                                                    v-model="menu4"
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
                                                            v-model="end_date"
                                                            label="End Date"
                                                            prepend-icon="mdi-calendar"
                                                            readonly
                                                            v-bind="attrs"
                                                            v-on="on"
                                                            :disabled="disable_end_date"
                                                        >
                                                            <v-checkbox true-value="checked" slot="prepend" v-on:change="toggleDate"></v-checkbox>
                                                        </v-text-field>
                                                    </template>
                                                    <v-date-picker
                                                        v-if="menu4"
                                                        v-model="end_date"
                                                        no-title
                                                        scrollable
                                                    >
                                                    </v-date-picker>
                                                </v-menu>
                                            </v-col>
                                            <v-col cols="12" md="6">
                                                <v-select
                                                    v-model="nurse_types"
                                                    :items="types"
                                                    label="Nurse Types"
                                                    multiple
                                                    hint="Leave blank to accept any type of nurse."
                                                    persistent-hint
                                                    prepend-icon="mdi-account"
                                                    attach
                                                ></v-select>
                                            </v-col>
                                            <v-col cols="12" md="6">
                                                <v-text-field
                                                    v-model="bonus_amount"
                                                    label="Bonus Amount"
                                                    prepend-icon="mdi-currency-usd"
                                                    type="number"
                                                    min="0"
                                                    step="0.01"
                                                    :rules="rules"
                                                ></v-text-field>
                                            </v-col>
                                            <v-col cols="12" md="6">
                                                <v-select
                                                    v-model="recurrence_type"
                                                    :items="recurrence_types"
                                                    label="Recurrence Type"
                                                    prepend-icon="mdi-repeat"
                                                    attach
                                                    :rules="rules"
                                                    v-on:change="updateRecurrenceFields"
                                                ></v-select>
                                            </v-col>
                                            <v-col cols="12" md="6">
                                                <v-select
                                                    v-model="recurrence_option"
                                                    :items="recurrence_options"
                                                    label="Recurrence Options"
                                                    prepend-icon="mdi-format-list-checkbox"
                                                    attach
                                                    multiple
                                                    :disabled="disable_options"
                                                ></v-select>
                                            </v-col>
                                            <v-col cols="12" md="6">
                                                <v-menu
                                                    ref="menu5"
                                                    v-model="menu5"
                                                    :close-on-content-click="false"
                                                    :nudge-right="40"
                                                    :return-value.sync="recurrence_end_date"
                                                    transition="scale-transition"
                                                    max-width="290px"
                                                    min-width="290px"
                                                    hint="Leave empty for forever"
                                                    persistent-hint
                                                    attach
                                                    :offset-y="y"
                                                >
                                                    <template v-slot:activator="{ on, attrs }">
        
        <!--                                                This hint only shows up when clicked, should probably show up any time-->
                                                        <v-text-field
                                                            v-model="recurrence_end_date"
                                                            label="Recurrence End Date"
                                                            prepend-icon="mdi-calendar"
                                                            readonly
                                                            v-bind="attrs"
                                                            v-on="on"
                                                            :disabled="disable_recurrence_end_date"
                                                        >
                                                        </v-text-field>
                                                    </template>
                                                    <v-date-picker
                                                        v-if="menu5"
                                                        v-model="recurrence_end_date"
                                                        no-title
                                                        scrollable
                                                    >
                                                        <v-spacer></v-spacer>
                                                        <v-btn
                                                            text
                                                            color="primary"
                                                            v-on:click="menu5 = false"
                                                        >Cancel</v-btn>
                                                        <v-btn
                                                            text
                                                            color="primary"
                                                            v-on:click="$refs.menu5.save(recurrence_end_date)"
                                                        >Save</v-btn>
                                                    </v-date-picker>
                                                </v-menu>
                                            </v-col>
                                            <v-col cols="12" md="6">
                                                <v-text-field
                                                    v-model="recurrence_interval"
                                                    label="Recurrence Frequency"
                                                    prepend-icon="mdi-repeat-once"
                                                    type="number"
                                                    :rules="rules"
                                                    :disabled="disable_recurrence_interval"
                                                ></v-text-field>
                                                </v-menu>
                                            </v-col>
                                            <v-col cols="12">
                                                <v-textarea
                                                    v-model="description"
                                                    :value="description"
                                                    label="Description"
                                                    prepend-icon="mdi-calendar-text"
                                                    :rules="rules"
                                                ></v-textarea>
                                            </v-col>
                                            <v-col cols="12" class="text-center">
                                                <a href="javascript:void(0)" class="btn btn-primary mr-1 rounded" v-on:click="createShift">Create Shift</a>
                                                <a href="javascript:void(0)" class="btn btn-danger rounded" v-on:click="reset">Reset</a>
                                            </v-col>
                                        </v-row>
                                    </v-form>
                                </v-app>
                            </div>
                        </div>
                    </div>
                </div>
            </div>`,
        // props:['submit_url'],
        data () {
            return {
                menu1: false,
                menu2: false,
                menu3: false,
                menu4: false,
                menu5: false,
                valid: false,
                y: true,
                name: null,
                start_time: null,
                end_time: null,
                start_date: null,
                end_date: null,
                category: null,
                categories: [],
                types: ['CNA', 'LPN', 'RN', 'CMT'],
                recurrence_types: ['None', 'Daily', 'Weekly', 'Monthly'],
                recurrence_options: [],
                recurrence_option: [],
                recurrence_type: '',
                recurrence_end_date: null,
                recurrence_interval: 1,
                description: '',
                bonus_amount: null,
                nurse_types: [],
                rules: [],
                time_rules: [],
                shift: null,
                disable_options: true,
                disable_end_date: true,
                disable_recurrence_end_date: true,
                disable_recurrence_interval: true,
                start_date_label: 'Date',
                checked: false
            }
        },
        created: function () {
            this.getShiftData();
            this.updateRecurrenceFields();
        },
        methods: {
            testFunction() {
                console.log('here');
            },
            //Todo: connect this to the backend somehow
            createShift() {
                let params = {
                    "name": this.name,
                    "start_time": this.start_time,
                    "end_time": this.end_time,
                    "start_date": this.start_date,
                    "end_date": this.end_date,
                    "shift_types": this.shift_types,
                    "bonus_amount": this.bonus_amount,
                    "frequency": this.recurrence_type,
                    "recurrence_option": this.recurrence_option,
                    "interval": this.recurrence_interval,
                    "recurrence_end_date": this.recurrence_end_date,
                    "description": this.description,
                    "category_id": this.category,
                    "action_type": "create"
                };
                this.rules = [v => !!v || 'This field is required']
                this.time_rules = [
                    v => !!v || 'This field is required',
                    v => this.validateTime(v) || 'Shift start must be before shift end'
                ];
                this.$refs.form.validate();

                modRequest.request('shift.create_shift', null, {'params': params},
                    function(response) {
                        if(response.data.success) {
                        }
                    }.bind(this),
                    function(error) {
                    }
                );
            },
            getShiftData() {
                modRequest.request('shift.load.categories', null, {},
                    function (response) {
                        if (response.data.success) {
                            this.categories = response.data.categories;
                        }
                    }.bind(this),
                    function (error) {
                    }
                );
            },
            reset() {
                this.$refs.form.reset();
            },
            validateTime(v) {
                if (this.end_date != null) {
                    return Date.parse(this.start_date + ' ' + this.start_time) < Date.parse(this.end_date + ' ' + this.end_time);
                } else {
                    return Date.parse(this.start_date + ' ' + this.start_time) < Date.parse(this.start_date + ' ' + this.end_time);
                }
            },
            updateRecurrenceFields() {
                switch (this.recurrence_type) {
                    case 'Daily':
                        this.disable_options = true;
                        this.disable_recurrence_end_date = false;
                        this.disable_recurrence_interval = false;
                        break;
                    case 'Weekly':
                        this.recurrence_options = ['Sun', 'Mon', 'Tues', 'Wed', 'Thur', 'Fri', 'Sat'];
                        this.disable_options = false;
                        this.disable_recurrence_end_date = false;
                        this.disable_recurrence_interval = false;
                        break;
                    case 'Monthly':
                        this.recurrence_options = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                        this.disable_recurrence_end_date = false;
                        this.disable_recurrence_interval = false;
                        break;
                    case 'Yearly':
                        this.disable_options = true;
                        this.disable_recurrence_end_date = false;
                        this.disable_recurrence_interval = false;
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
            toggleDate() {
                this.checked = !this.checked;
                if (this.checked) {
                    this.disable_end_date = false;
                    this.start_date_label = "Start Date";
                } else {
                    this.disable_end_date = true;
                    this.start_date_label = "Date";
                }
            }
        }
    });
});
