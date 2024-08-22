window.addEventListener('load', function () {
    Vue.component('vue-timepicker', window.VueTimepicker.default);
    Vue.component('sa-pay-period-view', {
        // language=HTML
        template: /*html*/`
            <div class="container-fluid" id="pay-period-container">
                <div class="row">
                    <div class="col-12">
                        <v-app>
                            <div class="card">
                                <div class="card-header">
                                    <div class="row">
                                        <div class="col-12 col-sm-8">
                                            <h4 class="card-title">Pay Period</h4>
                                        </div>
                                        <div class="col-12 col-sm-4">
                                            <div class="row">
                                                <div class="col-12 pt-8">
                                                    <v-spacer></v-spacer>
                                                    <v-select
                                                        v-model="pay_period"
                                                        :items="pay_periods"
                                                        item-text="display"
                                                        item-value="combined"
                                                        label="Pay Period"
                                                        @change="updateTables('pay_period')"
                                                        dense
                                                    ></v-select>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-12 pt-0 pb-0" id="provider-dropdown-col">
                                                    <v-spacer></v-spacer>
                                                    <v-autocomplete
                                                        v-model="search_date"
                                                        :items="shift_times"
                                                        item-text="day"
                                                        item-value="id"
                                                        label="Day"
                                                        clearable
                                                        width="300"
                                                        @change="updateTables('day')"
                                                    ></v-autocomplete>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-12 pt-0 pb-0" id="provider-dropdown-col">
                                                    <v-spacer></v-spacer>
                                                    <v-autocomplete
                                                        v-model="provider_id"
                                                        :items="providers"
                                                        item-text="name"
                                                        item-value="id"
                                                        label="Provider"
                                                        clearable
                                                        width="300"
                                                        @change="updateTables('provider_id')"
                                                    ></v-autocomplete>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-6 text-left">
                                                    <v-btn
                                                        color="light"
                                                        v-on:click="updateTables"                                                
                                                        >Refresh</v-btn>
                                                </div>
                                                <div class="col-6 text-right">
                                                    <span class="pr-3">Unresolved Payments Only</span>
                                                    <v-checkbox
                                                        color="primary"
                                                        v-model="unresolved_only"
                                                        @change="updateTables('unresolved')"
                                                    ></v-checkbox>
                                                    <br>
                                                    
                                                    <v-tooltip bottom
                                                        :max-width="300">
                                                        <template v-slot:activator="{ on, attrs }">
                                                            <span
                                                                    v-bind="attrs"
                                                                    v-on="on"
                                                                    class="pr-3"
                                                            >Include 0-hour payments</span>
                                                        </template>
                                                        <span>This will show any payment with 0 hours clocked including standard payments that have associated overtime payments. This should be used with the <strong>Unresolved Payments Only</strong> setting</span>
                                                    </v-tooltip>
                                                    <v-checkbox
                                                            color="primary"
                                                            v-model="includeZeroClockedHours"
                                                            @change="updateTables('unresolved')"
                                                    ></v-checkbox>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <v-tabs v-model="tab" id="pay-period-tabs">
                                        <v-tab class="black--text">Shifts</v-tab>
                                        <v-tab class="black--text">Nurses</v-tab>
                                        <v-spacer></v-spacer>
                                        <v-autocomplete
                                            v-model="search"
                                            :items="searchable_nurses"
                                            item-text="name"
                                            item-value="name"
                                            label="Search for a Nurse"
                                            clearable
                                            width="300"
                                        ></v-autocomplete>
                                    </v-tabs>
                                    <v-tabs-items v-model="tab" touchless>
                                        <v-tab-item>
                                            <div class="table-responsive">
                                                <v-card>
                                                    <v-card-text>                                                  
                                                        <div v-if="loading" class="center">
                                                            <h2>Loading data</h2>
                                                            <v-progress-circular
                                                            :size="100"
                                                            :width="15"
                                                            :value=loadingPercent
                                                            color="green"
                                                            >{{ this.loadingPercent }}</v-progress-circular>
                                                        </div>
                                                        <v-data-table v-else
                                                            class="table table-responsive-md"
                                                            :headers="shift_headers"
                                                            :items="shift_payments"
                                                            :search="search"
                                                            :custom-filter="paymentFilter"
                                                            multi-sort
                                                        >
                                                            <template v-slot:item.nurse_name="{ item }">
                                                                <a v-bind:href="item.nurse_route" class="blue--text mt-3 block" target="_blank">
                                                                    {{ item.nurse_name }}
                                                                </a>
                                                            </template>
                                                            <template v-slot:item.shift_time="{ item }">
                                                                <span class="mt-1 block grey--text">{{item.date}}</span>
                                                                <span class="mt block">{{item.shift_time}}</span>
                                                            </template>
                                                            <template v-slot:item.clocked_hours="{ item }">
                                                                <span class="mt-1 block grey--text" v-if="item.type != 'Bonus'">{{item.clocked_hours}} hours</span>
                                                                <span class="mt block" v-if="item.type != 'Bonus'">{{item.clock_times}}</span>
                                                            </template>
                                                            <template v-slot:item.rate="{ item }">
                                                                <span class="mt-3 block" v-if="item.type != 'Bonus'">{{'$' + item.rate}}</span>
                                                            </template>
                                                            <template v-slot:item.amount="{ item }">
                                                                <span class="mt-3 block">{{'$' + item.amount}}</span>
                                                            </template>
                                                            <template v-slot:item.type="{ item }">
                                                                <span class="mt-3 d-inline-block">{{item.type}}
                                                                <v-tooltip v-if="item.type == 'Bonus' && item.description" bottom :open-on-click="true" nudge-left="50" >
                                                                    <template v-slot:activator="{on, attrs}">
                                                                        <v-icon v-bind="attrs" v-on="on">mdi-chat</v-icon>
                                                                    </template>
                                                                    <span>{{item.description}}</span>
                                                                </v-tooltip>
                                                                </span>
                                                            </template>
                                                           
                                                         
                                                            

                                                            
                                              <template v-slot:item.status="{ item }">
                                                          

                                                         <v-tooltip v-if="item.status === 'Resolved'"  bottom  nudge-left="50">
                                                            <template v-slot:activator="{ on, attrs }">
                                                                <span v-bind="attrs" v-on="on" :class="getStatusColorClass(item) + ' mt-3 block'">
                                                                {{item.status}}
                                                                <i v-if="item.clock_in_type == 'manual'" class="fas fa-map"></i>
                                                                </span>
                                                            </template>
                                                             
                                                            <span v-show="item.resolved_by !== ''">Resolved by: {{item.resolved_by}}</span>
                                                            <span v-show="item.resolved_by === ''">Previously Resolved. No data available</span>

                                                        </v-tooltip>
                                        <span v-else :class="getStatusColorClass(item) + ' mt-3 block'">
                                                                {{item.status}}
                                                                <i v-if="item.clock_in_type == 'manual'" class="fas fa-map"></i>
                                                                </span>
                                                    </template>
                                                            <template v-slot:item.actions="{ item }">
                                                                <v-dialog max-width="1000"
                                                                    v-if="can_edit_items == 1">
                                                                    <template v-slot:activator="{ on, attrs }">
                                                                        <v-btn
                                                                            v-on="on"
                                                                            v-bind="attrs"
                                                                            :color="item.status == 'Change Requested' ? 'warning' : 'light'"
                                                                            class="mt-1 ml-1" 
                                                                            @click="getPaymentsForEdit(item)"
                                                                            :disabled="can_edit_items == 0"
                                                                            >Edit</v-btn>
                                                                    </template>
                                                                    <template v-slot:default="dialog">
                                                                        <v-card
                                                                            :max-height="item.type != 'Bonus' ? '800px' : '300px'"
                                                                            style="overflow-y: auto;">
                                                                            <v-toolbar
                                                                                :color="item.status == 'Change Requested' ? 'warning' : 'info'"
                                                                                class="text-h4 white--text"
                                                                            >Edit Clock Times</v-toolbar>
                                                                            <v-card-text
                                                                                v-if="item.type != 'Bonus'"
                                                                                class="pt-6 pay-period-clock-times"
                                                                                height="100%">
                                                                                <v-row > 
                                                                                    <v-col cols="6" class="pt-0"> 
                                                                                        <span class="black--text">Request:</span>
                                                                                        <p>Clock In: {{item.request_clock_in}}
                                                                                        <br>Clock Out: {{item.request_clock_out}}
                                                                                        <br>Description: {{item.request_description}}</p>
                                                                                    </v-col>
                                                                                    <v-col cols="6" v-if="shiftForEdit && shiftForEdit.provider" class="black--text">
                                                                                        <span>Facility and staff information:</span>
                                                                                        <p>Facility: <span class="font-weight-light">{{shiftForEdit.provider.company}}</span>
                                                                                        <br>Nurse: <span class="font-weight-light">{{shiftForEdit.nurse.first_name}} {{shiftForEdit.nurse.last_name}} ({{shiftForEdit.nurse.credentials}})</span>
                                                                                        </p>                                                                                        
                                                                                    </v-col>
                                                                                </v-row>
                                                                                <v-row>
                                                                                    <v-col cols="6">
                                                                                        
                                                                                        
                                                                                    </v-col>
                                                                                </v-row>
                                                                                <v-form ref="form">
                                                                                    <v-row>
                                                                                        <v-col cols="12" md="6">
                                                                                            <v-row class="black--text">
                                                                                                Clock In Time:
                                                                                            </v-row>
                                                                                            <v-row v-if="item.type != 'Bonus'">
                                                                                                <v-col cols="12" class="pt-0" v-if="shiftForEdit">
                                                                                                    <vue-timepicker
                                                                                                            v-model="shiftForEdit.clock_in_time_picker"
                                                                                                            input-width="100%"
                                                                                                            format="hh:mm A"
                                                                                                            input-class="custom-timepicker"
                                                                                                            placeholder="Clock In Time"
                                                                                                            manual-input
                                                                                                    ></vue-timepicker>
                                                                                                    <span class="font-italic font-weight-light"> Scheduled to begin: {{shiftForEditStartFormatted}}</span>
                                                                                                </v-col>
                                                                                            </v-row>
<!--                                                                                        </v-col>-->
<!--                                                                                        <v-col cols="12" md="6">-->
                                                                                            <v-row class="black--text pt-2">
                                                                                                Clock Out Time:
                                                                                            </v-row>
                                                                                            <v-row>
                                                                                                <v-col cols="12" v-if="item.type != 'Bonus' && shiftForEdit" class="pt-0">
                                                                                                    <vue-timepicker
                                                                                                            v-model="shiftForEdit.clock_out_time_picker"
                                                                                                            input-width="100%"
                                                                                                            format="hh:mm A"
                                                                                                            input-class="custom-timepicker"
                                                                                                            placeholder="Clock Out Time"
                                                                                                            manual-input
                                                                                                    ></vue-timepicker>
                                                                                                    <span class="font-italic font-weight-light"> Scheduled to end: {{shiftForEditEndFormatted}}</span>
                                                                                                </v-col>
                                                                                            </v-row>
<!--                                                                                        </v-col>-->
<!--                                                                                    </v-row>-->
<!--                                                                                    <v-row>-->
<!--                                                                                        <v-col cols="12" md="6">-->
                                                                                            <v-row v-if="item.type != 'Bonus'" class="black--text pt-2 pb-8">
                                                                                                <span>Lunch (leave empty to retain from shift):</span>
                                                                                                <v-col cols="12" class="pt-0" v-if="shiftForEdit">
                                                                                                    <v-select
                                                                                                            dense
                                                                                                            clearable
                                                                                                            v-model="shiftForEdit.lunch_override"
                                                                                                            :items="[0, 15, 30, 60]"
                                                                                                            ></v-select>
                                                                                                </v-col>
                                                                                            </v-row>
                                                                                        </v-col>
                                                                                        <v-col v-if="shiftForEdit && 'is_covid' in shiftForEdit">
                                                                                            <v-checkbox v-model="shiftForEdit.is_covid" :label="'Covid pay: ' + (shiftForEdit.is_covid ? 'Yes' : 'No')"></v-checkbox> 
<!--                                                                                            standard hours-->
                                                                                            <h4 class="black--text">Hours in pay period counting towards overtime:</h4>
                                                                                            <p class="black--text" v-if="shiftForEdit.current_period_standard_hours">
                                                                                            Standard hours: {{shiftForEdit.current_period_standard_hours}}
                                                                                            <span v-for="payment in shiftForEdit.current_period_standard_shift_payments">
                                                                                                <br>{{payment.date}}: {{payment.clocked_hours}}
                                                                                            </span>
                                                                                            </p>
                                                                                            <p v-else class="black--text">No standard hours in this pay period</p>
                                                                                            <v-divider></v-divider>
<!--                                                                                            overtime hours-->
                                                                                            <p class="black--text" v-if="shiftForEdit.current_period_overtime_hours">
                                                                                                Overtime hours: {{shiftForEdit.current_period_overtime_hours}}
                                                                                                <span v-for="payment in shiftForEdit.current_period_overtime_shift_payments">
                                                                                                    <br>{{payment.date}}: {{payment.clocked_hours}}
                                                                                                </span>
                                                                                            </p>
                                                                                            <p v-else class="black--text">No overtime hours in this pay period so far</p>
                                                                                                            
                                                                                        </v-col>
                                                                                    </v-row>
                                                                                    <v-row>
                                                                                        <!--standard payment-->
                                                                                        <v-col v-if="standardPaymentForEdit">
                                                                                            <div class="flex flex-wrap justify-conter" v-if="standardPaymentForEdit" style="display: flex!important; justify-content: space-around;">
                                                                                                <div class="border p-4 w-1/2 text-center">
                                                                                                    <p class="black--text text-center">
                                                                                                        Standard payment:
                                                                                                    </p>
                                                                                                    <div class="flex items-center mb-4">
                                                                                                        <div class="flex items-center mb-4">
                                                                                                            <h1 class="text-xl">{{ Number(standardPaymentForEdit.clocked_hours).toFixed(2) }} hrs</h1>
                                                                                                        </div>
                                                                                                        <input type="range" min="0" step="0.01" :max="totalHoursForedit" v-model="standardPaymentForEdit.clocked_hours">
                                                                                                    </div>
                                                                                                </div>
                                                                                                <div class="border p-4 w-1/2 text-center">
                                                                                                    <p class="black--text text-center">
                                                                                                        Holiday payment hours:
                                                                                                    </p>
                                                                                                    <div class="flex items-center mb-4">
                                                                                                        <!-- <div class="flex items-center mb-4">
                                                                                                            <h1 class="text-xl">{{ holidayHours }} hrs</h1>
                                                                                                        </div>
                                                                                                        <input type="range" min="0" step="0.01" :max="1" v-model="standardPaymentForEdit.holiday_percentage"> -->
                                                                                                        <div style="max-width: 125px;">
                                                                                                            <v-text-field
                                                                                                                label="Holiday Hours"
                                                                                                                type="input"
                                                                                                                v-model="holidayHoursDisplay"
                                                                                                            ></v-text-field>
                                                                                                        </div>
                                                                                                    </div>
                                                                                                </div>
                                                                                            </div>
                                                                                            <v-expansion-panels>
                                                                                                <v-expansion-panel>
                                                                                                    <v-expansion-panel-header>
                                                                                                        <span class="black--text">Payment data</span>
                                                                                                    </v-expansion-panel-header>
                                                                                                    <v-expansion-panel-content>                                                                                                        
                                                                                                        <v-card class="pa-4 mt-4">
                                                                                                            <v-text-field v-model="standardPaymentForEdit.pay_bonus" type="number" label="Pay Bonus" min="0"></v-text-field>
                                                                                                            <v-text-field v-model="standardPaymentForEdit.pay_rate" type="number" label="Pay Rate" min="0"></v-text-field>
                                                                                                            <v-text-field v-model="standardPaymentForEdit.pay_travel" type="number" label="Pay Travel" min="0"></v-text-field>
                                                                                                            <v-text-field v-model="standardPaymentForEdit.pay_holiday" type="number" label="Pay Holiday" min="0" readonly></v-text-field>
                                                                                                            <span class="font-weight-bold">Pay total: </span><span class="font-weight-light">{{standardPaymentForEdit.pay_total}}</span>
                                                                                                        </v-card>
                                                                                                        <br>
                                                                                                        <p class="black--text">
                                                                                                            Status: <span :class="standardPaymentForEdit.status === 'Unresolved' ? 'red--text' : 'green--text'">
                                                                                                                {{standardPaymentForEdit.status}}
                                                                                                            </span>    
                                                                                                            <br>Payment method: <span class="font-weight-light">{{standardPaymentForEdit.payment_method}}</span>
                                                                                                            <br>Payment Status: <span class="font-weight-light">{{standardPaymentForEdit.payment_status}}</span>
                                                                                                        </p>                                                                                                        
                                                                                                    </v-expansion-panel-content>
                                                                                                </v-expansion-panel>
                                                                                            </v-expansion-panels>
                                                                                        </v-col>
                                                                                        <v-col v-else class="red--text">Something went wrong</v-col>
                                                                                        <v-divider vertical></v-divider>
                                                                                        <!--overtime payment-->
                                                                                        <v-col v-if="overtimePaymentForEdit">
                                                                                            <p class="black--text text-center">
                                                                                                Overtime payment:
                                                                                            </p>
                                                                                            <div class="border p-4 w-1/2 text-center" v-if="overtimePaymentForEdit">
                                                                                                <div class="flex items-center mb-4">
                                                                                                    <h1 class="text-xl">{{ Number(overtimePaymentForEdit.clocked_hours).toFixed(2) }} hrs</h1>
                                                                                                </div>
                                                                                                <input type="range" min="0" step="0.01" :max="totalHoursForedit" v-model="overtimePaymentForEdit.clocked_hours">
                                                                                            </div>
                                                                                            <v-expansion-panels>
                                                                                                <v-expansion-panel>
                                                                                                    <v-expansion-panel-header>
                                                                                                        <span class="black--text">Payment data</span>
                                                                                                    </v-expansion-panel-header>
                                                                                                    <v-expansion-panel-content>
                                                                                                        <v-card class="pa-4 mt-4">
                                                                                                            <v-text-field v-model="overtimePaymentForEdit.pay_bonus" type="number" label="Pay bonus" min="0"></v-text-field>
                                                                                                            <v-text-field v-model="overtimePaymentForEdit.pay_rate" type="number" label="Pay rate" min="0"></v-text-field>
                                                                                                            <v-text-field v-model="overtimePaymentForEdit.pay_travel" type="number" label="Pay travel" min="0"></v-text-field>
                                                                                                            <span class="font-weight-bold">Pay total: </span><span class="font-weight-light">{{overtimePaymentForEdit.pay_total}}</span>
                                                                                                        </v-card>
                                                                                                        <br>
                                                                                                        <p class="black--text">
                                                                                                            Status: <span :class="overtimePaymentForEdit.status === 'Unresolved' ? 'red--text' : 'green--text'">
                                                                                                                {{overtimePaymentForEdit.status}}
                                                                                                            </span>
                                                                                                            <br>Payment method: <span class="font-weight-light">{{overtimePaymentForEdit.payment_method}}</span>
                                                                                                            <br>Payment Status: <span class="font-weight-light">{{overtimePaymentForEdit.payment_status}}</span>
                                                                                                        </p>
                                                                                                    </v-expansion-panel-content>
                                                                                                </v-expansion-panel>
                                                                                            </v-expansion-panels>                                                                                            
                                                                                            <div v-if="overtimePaymentForEdit && overtimePaymentForEdit.id === 0" class="orange--text font-weight-bold pt-4">Note: This overtime payment is not generated until you hit the save button</div>
                                                                                        </v-col>
                                                                                        <v-col v-else>
                                                                                            <p class="orange--text font-weight-bold">No overtime payment</p>
                                                                                            <v-btn 
                                                                                                    elevation="2" 
                                                                                                    v-on:click="createOvertimePaymentForEdit()"
                                                                                            >Generate Overtime Payment</v-btn>
                                                                                        </v-col>
                                                                                    </v-row>                                                                                    
                                                                                </v-form>
                                                                            </v-card-text>
                                                                            <v-card-text 
                                                                                class="pb-0"
                                                                                v-else > 
                                                                                <v-form ref="form">
                                                                                    <v-row class="pt-4"> 
                                                                                        <v-col cols="12">                                                                                         
                                                                                            <v-text-field 
                                                                                                v-model="item.changed_amount" 
                                                                                                label="Bonus Amount"
                                                                                            ></v-text-field>
                                                                                        </v-col>
                                                                                        <v-col cols="12"> 
                                                                                            <v-text-field 
                                                                                                v-model="item.changed_description" 
                                                                                                label="Bonus Description"
                                                                                            ></v-text-field>
                                                                                        </v-col>
                                                                                    </v-row>
                                                                                </v-form>
                                                                            </v-card-text>
                                                                            <v-card-actions>
                                                                                <v-spacer></v-spacer>
                                                                                <v-btn
                                                                                    color="light"
                                                                                    v-on:click="dialog.value = false"
                                                                                >Close
                                                                                </v-btn>
                                                                                <v-btn
                                                                                    :color="item.status == 'Change Requested' ? 'warning' : 'info'"
                                                                                    v-on:click="saveChanges(item, dialog)"
                                                                                    class="white--text"
                                                                                >Save</v-btn>
                                                                            </v-card-actions>
                                                                        </v-card>
                                                                    </template>
                                                                </v-dialog>
                                                                <v-dialog max-width="400" max-height="600" v-if="item.status == 'Change Requested' || item.status == 'Unresolved'">
                                                                    <template v-slot:activator="{ on, attrs }">
                                                                        <v-btn
                                                                            v-on="on"
                                                                            v-bind="attrs"
                                                                            color="success"
                                                                            class="mt-1 ml-1" 
                                                                            >Mark Resolved</v-btn>
                                                                    </template>
                                                                    <template v-slot:default="dialog">
                                                                        <v-card>
                                                                            <v-toolbar
                                                                                color="success"
                                                                                class="text-h4 white--text"
                                                                            >Resolve Payment</v-toolbar>
                                                                            <v-card-text
                                                                                class="pt-5"> 
                                                                                Are you sure you want to mark this payment as <strong class="success--text">RESOLVED</strong>?
                                                                            </v-card-text>
                                                                            <v-card-text v-if="item.clock_in_type == 'manual'">
                                                                                This shift was clocked in manually. Please contact the supervisor/provider for {{item.nurse_name}} to confirm this override.
                                                                                <div class="pt-3" v-if="item.supervisor_name != '' && item.supervisor_code != ''">
                                                                                    <p>Supervisor: {{item.supervisor_name}}</p>
                                                                                    <img class="w-100" v-bind:src="item.supervisor_signature"/>
                                                                                    <p>Code: {{item.supervisor_code}}</p>
                                                                                </div>
                                                                            </v-card-text>
                                                                            <v-card-actions>
                                                                                <v-spacer></v-spacer>
                                                                                <v-btn
                                                                                    color="light"
                                                                                    v-on:click="dialog.value = false"
                                                                                >Close
                                                                                </v-btn>
                                                                                <v-btn
                                                                                    v-if="item.clock_in_type == 'manual'"
                                                                                    color="success"
                                                                                    v-on:click="resolvePayment(item)"
                                                                                    class="white--text"
                                                                                >Confirm Override and Resolve</v-btn>
                                                                                <v-btn
                                                                                    v-else
                                                                                    color="success"
                                                                                    v-on:click="resolvePayment(item)"
                                                                                    class="white--text"
                                                                                >Yes, Mark Resolved</v-btn>
                                                                            </v-card-actions>
                                                                        </v-card>
                                                                    </template>
                                                                </v-dialog>
                                                                <v-dialog max-width="400" max-height="600">
                                                                    <template v-slot:activator="{ on, attrs }">
                                                                        <v-btn
                                                                            v-on="on"
                                                                            v-bind="attrs"
                                                                            color="primary"
                                                                            class="mt-1 ml-1" 
                                                                            >Info</v-btn>
                                                                    </template>
                                                                    <template v-slot:default="dialog">
                                                                        <v-card>
                                                                            <v-toolbar
                                                                                color="primary"
                                                                                class="text-h4 white--text"
                                                                            >Extra Shift Info</v-toolbar>
                                                                            <v-card-text class="mt-5" v-if="item.clock_in_type == 'manual'">
                                                                                This shift was clocked in <strong class="error--text">MANUALLY</strong>
                                                                                Please contact the supervisor whose name and signature are below
                                                                                <div class="pt-3" v-if="item.supervisor_name != '' && item.supervisor_code != ''">
                                                                                    <p><strong>Supervisor</strong>: {{item.supervisor_name}}</p>
                                                                                    <img class="w-100" v-bind:src="item.supervisor_signature"/>
                                                                                </div>
                                                                                
                                                                            </v-card-text>
                                                                            <a target="_blank" v-bind:href="item.timeslip" class="px-6">
                                                                                    View time slip                                                                         
                                                                            </a>
                                                                            <v-card-actions>
                                                                                <v-spacer></v-spacer>
                                                                                <v-btn
                                                                                    color="light"
                                                                                    v-on:click="dialog.value = false"
                                                                                >Close
                                                                                </v-btn>
                                                                            </v-card-actions>
                                                                        </v-card>
                                                                    </template>
                                                                </v-dialog>
                                                            </template>
                                                        </v-data-table>
                                                    </v-card-text>
                                                </v-card>
                                            </div>
                                        </v-tab-item>
                                        <v-tab-item> 
                                            <div class="table-responsive">
                                                <v-card>
                                                    <v-card-text>
                                                        <div v-if="loading" class="center">
                                                            <h2>Loading data</h2>
                                                            <v-progress-circular
                                                            :size="100"
                                                            :width="15"
                                                            :value=loadingPercent
                                                            color="green"
                                                            >{{ this.loadingPercent }}</v-progress-circular>
                                                        </div>
                                                        <v-data-table v-else
                                                            class="table table-responsive-md"
                                                            :headers="nurse_headers"
                                                            :items="nurse_payments"
                                                            :search="search"
                                                            :custom-filter="paymentFilter"
                                                            multi-sort
                                                        >
                                                            <template v-slot:item.nurse_name="{ item }">
                                                                <a v-bind:href="item.nurse_route"  class="block mt-3 blue--text" target="_blank">
                                                                    {{ item.nurse_name }}
                                                                </a>
                                                            </template>
                                                            <template v-slot:item.clocked_hours="{ item }">
                                                                <span class="block mt-3">{{item.clocked_hours}}</span>
                                                            </template>
                                                            <template v-slot:item.rate="{ item }">
                                                                <span class="block mt-3">{{'$' + item.rate}}</span>
                                                            </template>
                                                            <template v-slot:item.amount="{ item }">
                                                                <span class="block mt-3">{{'$' + item.amount}}</span>
                                                            </template>
                                                            <template v-slot:item.bonus_amount="{ item }">
                                                                <span class="block mt-3">{{'$' + item.bonus_amount}}</span>
                                                            </template>
                                                            <template v-slot:item.has_unresolved_payments="{ item }">
                                                                <span class="block mt-3">{{item.has_unresolved_payments}}</span>
                                                            </template>
                                                        </v-data-table>
                                                    </v-card-text>
                                                </v-card>
                                            </div>
                                        </v-tab-item>
                                    </v-tabs-items>
                                </div>
                            </div>
                        </v-app>
                    </div>
                </div>
            </div>`,
        props: [
            'provider__id',
            'show_unresolved_only',
            'period',
            'can_edit_items'
        ],
        data() {
            return {
                tab: null,
                nurse_headers: [
                    {
                        text: 'Nurse Name',
                        align: 'start',
                        sortable: true,
                        value: 'nurse_name'
                    },
                    {
                        text: 'Hourly Rate',
                        sortable: true,
                        value: 'rate'
                    },
                    {
                        text: 'Clocked Hours',
                        sortable: true,
                        value: 'clocked_hours'
                    },
                    {
                        text: 'Has Unresolved Payments',
                        sortable: true,
                        value: 'has_unresolved_payments'
                    },
                    {
                        text: 'Bonus Total',
                        sortable: true,
                        value: 'bonus_amount'
                    },
                    {
                        text: 'Payment Total',
                        sortable: true,
                        value: 'amount'
                    },
                ],
                shift_headers: [
                    {
                        text: 'Nurse Name',
                        align: 'start',
                        sortable: true,
                        value: 'nurse_name'
                    },
                    {
                        text: 'Shift Time',
                        sortable: true,
                        value: 'shift_time'
                    },
                    {
                        text: 'Clocked Hours',
                        sortable: true,
                        value: 'clocked_hours'
                    },
                    {
                        text: 'Hourly Rate',
                        sortable: true,
                        value: 'rate'
                    },
                    {
                        text: 'Amount',
                        sortable: true,
                        value: 'amount'
                    },
                    {
                        text: 'Type',
                        sortable: true,
                        value: 'type'
                    },
                    {
                        text: 'Status',
                        sortable: true,
                        value: 'status'
                    },
                    {
                        text: 'Actions',
                        sortable: true,
                        value: 'actions'
                    }
                ],
                colors: {
                    'Resolved': 'success',
                    'Approved': 'success',
                    'Change Requested': 'warning',
                    'Unresolved': 'red'
                },
                shift_payments: [
                ],
                shift_times: [
                ],
                search_date: '',
                nurse_payments: [
                ],
                pay_periods: [],
                pay_period: '',
                providers: [],
                provider_id: null,
                unresolved_only: false,
                search: '',
                searchable_nurses: [],
                time_clock_rules: [],
                loading: true,
                loadingPercent: 0,
                standardPaymentForEdit: null,
                overtimePaymentForEdit: null,
                shiftForEdit: null,
                totalHoursForedit: 0,
                includeZeroClockedHours: false,
                holidayHoursDisplay: 0,
                initialHolidayHours: 0,
            }
        },
        computed: {
            hourOptions: function () {
                var options = [];
                for (let i = 1; i < 13; i++) {
                    if (i < 10) {
                        options.push('0' + i.toString());
                    } else {
                        options.push(i.toString());
                    }
                }
                return options;
            },
            minuteOptions: function () {
                var options = [];
                for (let i = 1; i < 60; i++) {
                    if (i < 10) {
                        options.push('0' + i.toString());
                    } else {
                        options.push(i.toString());
                    }
                }
                return options;
            },
            shiftForEditStartFormatted: function () {
                if (this.shiftForEdit) {
                    return moment.utc(this.shiftForEdit.start.date).local().format('MMM Do YYYY hh:mm:ss a');
                } else {
                    return null;
                }
            },
            shiftForEditEndFormatted: function () {
                if (this.shiftForEdit) {
                    return moment.utc(this.shiftForEdit.end.date).local().format('MMM Do YYYY hh:mm:ss a');
                } else {
                    return null;
                }
            },
        },
        watch: {
            loadingPercent: function (val) {
                if (val >= 100) {
                    setTimeout(() => this.loading = false, 450);
                }
            },
            standardPaymentForEdit: {
                handler(newVal, oldVal) {

                    if (newVal == oldVal) { return; }
                    
                    if (!this.standardPaymentForEdit) {
                        return;
                    }

                    if (this.overtimePaymentForEdit) {
                        this.overtimePaymentForEdit.clocked_hours = this.totalHoursForedit - this.standardPaymentForEdit.clocked_hours;
                    } else {
                        this.standardPaymentForEdit.clocked_hours = this.totalHoursForedit;
                    }

                    if (this.standardPaymentForEdit.clocked_hours <= 0) {
                        this.standardPaymentForEdit.pay_total = 0.00;
                        this.standardPaymentForEdit.bill_total = 0.00;
                    } else {

                        let payRate = parseFloat(this.standardPaymentForEdit.pay_rate);
                        let billRate = parseFloat(this.standardPaymentForEdit.bill_rate);
                        let clockedHours = parseFloat(this.standardPaymentForEdit.clocked_hours);

                        let holidayPay = (payRate * 0.5 * this.standardPaymentForEdit.holiday_hours);
                        let standardPay = (payRate * clockedHours);

                        let holidayBill = (billRate * 0.5 * this.standardPaymentForEdit.holiday_hours);
                        let standardBill = (billRate * clockedHours);

                        let bonuses = parseFloat(this.standardPaymentForEdit.pay_bonus) + parseFloat(this.standardPaymentForEdit.pay_travel);

                        this.standardPaymentForEdit.pay_total = (holidayPay + standardPay + bonuses).toFixed(2);
                        this.standardPaymentForEdit.bill_total = (holidayBill + standardBill + bonuses).toFixed(2);

                        let nonHolidayPay = payRate * clockedHours;
                        this.standardPaymentForEdit.pay_holiday = (this.standardPaymentForEdit.pay_total - bonuses - nonHolidayPay).toFixed(2);

                        let nonHolidayBill = billRate * clockedHours;
                        this.standardPaymentForEdit.bill_holiday = (this.standardPaymentForEdit.bill_total - bonuses - nonHolidayBill).toFixed(2);
                    }
                },
                deep: true
            },
            overtimePaymentForEdit: {
                handler() {
                    if (!this.overtimePaymentForEdit) {
                        return;
                    }

                    if (this.standardPaymentForEdit && this.overtimePaymentForEdit) {
                        this.standardPaymentForEdit.clocked_hours = this.totalHoursForedit - this.overtimePaymentForEdit.clocked_hours;
                    } else {
                        this.overtimePaymentForEdit.clocked_hours = this.totalHoursForedit;
                    }

                    if (!'clocked_hours' in this.overtimePaymentForEdit || this.overtimePaymentForEdit.clocked_hours <= 0) {
                        this.overtimePaymentForEdit.pay_total = 0.00;
                        this.overtimePaymentForEdit.bill_total = 0.00;
                    } else {
                        this.overtimePaymentForEdit.pay_total = (+(+this.overtimePaymentForEdit.pay_rate * +this.overtimePaymentForEdit.clocked_hours) + +this.overtimePaymentForEdit.pay_bonus + +this.overtimePaymentForEdit.pay_travel).toFixed(2);
                        this.overtimePaymentForEdit.bill_total = (+(+this.overtimePaymentForEdit.bill_rate * +this.overtimePaymentForEdit.clocked_hours) + +this.overtimePaymentForEdit.bill_bonus + +this.overtimePaymentForEdit.bill_travel).toFixed(2);
                    }
                },
                deep: true
            },
            shiftForEdit: {
                handler() {
                    if (this.shiftForEdit) {
                        let clockInPickerTime = this.shiftForEdit.clock_in_time_picker;
                        if (clockInPickerTime.indexOf('AM') && clockInPickerTime.substring(0, 2) === '12') {
                            clockInPickerTime = clockInPickerTime.replace('12', '00');
                        }
                        clockInPickerTime = clockInPickerTime.indexOf('AM') >= 0 ? clockInPickerTime.substring(0, 2) + ':' + clockInPickerTime.substring(3, 5) : (+clockInPickerTime.substring(0, 2) + 12) + ':' + clockInPickerTime.substring(3, 5);

                        let clockoutPickerTime = this.shiftForEdit.clock_out_time_picker;
                        if (clockoutPickerTime.indexOf('AM') && clockoutPickerTime.substring(0, 2) === '12') {
                            clockoutPickerTime = clockoutPickerTime.replace('12', '00');
                        }
                        clockoutPickerTime = clockoutPickerTime.indexOf('AM') >= 0 ? clockoutPickerTime.substring(0, 2) + ':' + clockoutPickerTime.substring(3, 5) : (+clockoutPickerTime.substring(0, 2) + 12) + ':' + clockoutPickerTime.substring(3, 5);

                        const clockInDate = moment.utc(this.shiftForEdit.clock_in_time.date).local().hours(clockInPickerTime.substring(0, 2)).minutes(clockInPickerTime.substring(3, 5));
                        const clockOutDate = moment.utc(this.shiftForEdit.clock_out_time.date).local().hours(clockoutPickerTime.substring(0, 2)).minutes(clockoutPickerTime.substring(3, 5));
                        const lunchOverrideTime = this.getLunchOverrideTime(this.shiftForEdit.lunch_override);

                        this.totalHoursForedit = moment(clockOutDate).diff(moment(clockInDate), 'hours', true).toFixed(2) - +lunchOverrideTime;

                        if (this.overtimePaymentForEdit) {
                            this.overtimePaymentForEdit.clocked_hours = this.totalHoursForedit - this.standardPaymentForEdit.clocked_hours;
                        } else {
                            this.standardPaymentForEdit.clocked_hours = this.totalHoursForedit;
                        }
                    }
                },
                deep: true
            },
            holidayHoursDisplay: {
                handler(newVal, oldVal) {

                    // Set a tolerance for comparing floating-point numbers
                    const tolerance = 0.01;

                    // Check if the absolute difference is within the tolerance
                    if (Math.abs(newVal - oldVal) <= tolerance) {
                        return;
                    }

                    if (newVal !== oldVal) {

                        if (this.holidayHoursDisplay > this.standardPaymentForEdit.clocked_hours) {

                            this.holidayHoursDisplay = this.standardPaymentForEdit.clocked_hours;
                            this.standardPaymentForEdit.holiday_hours = this.standardPaymentForEdit.clocked_hours;
                        } else {

                            if (parseFloat(this.holidayHoursDisplay) == parseFloat(this.initialHolidayHours.toFixed(2))) {

                                this.holidayHoursDisplay = parseFloat(this.holidayHoursDisplay);
                                this.holidayHoursDisplay = parseFloat(this.initialHolidayHours.toFixed(2));
                                this.standardPaymentForEdit.holiday_hours = this.initialHolidayHours;
                            } else if (this.holidayHoursDisplay !== undefined && this.holidayHoursDisplay.toString().split('.')[1]?.length >= 3) {

                                this.holidayHoursDisplay = parseFloat(this.holidayHoursDisplay);
                                this.holidayHoursDisplay = this.holidayHoursDisplay.toFixed(2);
                                this.standardPaymentForEdit.holiday_hours = parseFloat(this.holidayHoursDisplay);
                            } else {

                                this.holidayHoursDisplay = parseFloat(this.holidayHoursDisplay);
                                this.standardPaymentForEdit.holiday_hours = this.holidayHoursDisplay;
                            }
                        }

                        if (isNaN(this.holidayHoursDisplay) || this.holidayHoursDisplay == '' || this.holidayHoursDisplay == null) {

                            this.holidayHoursDisplay = 0;
                            this.standardPaymentForEdit.holiday_hours = 0;
                        }
                    }
                }
            }
        },
        created: function () {
            this.time_clock_rules = [
                v => !!v || 'This field is required',
            ];
        },
        mounted: function () {
            this.getPayPeriods();
            this.getProviders();
        },
        methods: {
            getPaymentsForEdit(item) {
                let data = {
                    shift_id: item.shift_id
                }
                this.totalHoursForedit = item.clocked_hours;
                this.getRelatedPaymentsByShift(data);
            },
            getRelatedPaymentsByShift(data) {
                this.standardPaymentForEdit = null;
                this.overtimePaymentForEdit = null;
                this.shiftForEdit = null;
                modRequest.request('sa.payroll.get_payments_by_shift', {}, data, function (response) {
                    if (response.success) {
                        this.standardPaymentForEdit = response.standard_payment;
                        this.overtimePaymentForEdit = response.overtime_payment;
                        this.shiftForEdit = response.shift_for_edit;
                        this.initialHolidayHours = this.standardPaymentForEdit.holiday_hours;
                        this.holidayHoursDisplay = parseFloat(this.standardPaymentForEdit.holiday_hours.toFixed(2));

                        const clockOutDate = this.shiftForEdit.clock_out_time.date;
                        const clockInDate = this.shiftForEdit.clock_in_time.date;
                        const lunchOverrideTime = this.getLunchOverrideTime(this.shiftForEdit.lunch_override);

                        this.totalHoursForedit = moment(clockOutDate).diff(moment(clockInDate), 'hours', true).toFixed(2) - +lunchOverrideTime;
                    } else {
                        console.log('Error');
                        console.log(response);
                    }
                }.bind(this), function (response) {
                    console.log('Failed');
                    console.log(response);
                });
            },
            getLunchOverrideTime(lunchOverrideValue) {
                if (!lunchOverrideValue) {
                    return 0.00;
                }

                switch (lunchOverrideValue) {
                    case 15:
                        return 0.25;
                        break;
                    case 30:
                        return 0.50;
                        break;
                    case 60:
                        return 1.00;
                        break;
                    default:
                        return 0.00;
                }
            },
            createOvertimePaymentForEdit() {
                let tempPayment = JSON.parse(JSON.stringify(this.standardPaymentForEdit));
                // tempPayment.bill_bonus TODO
                tempPayment.bill_rate = tempPayment.bill_rate * 1.5;
                // tempPayment.bill_travel TODO
                tempPayment.bill_total = (+(+tempPayment.bill_rate * +tempPayment.clocked_hours) + +tempPayment.bill_bonus + +tempPayment.bill_travel).toFixed(2);
                tempPayment.clocked_hours = 0.00;
                tempPayment.date_created = null;
                tempPayment.date_deleted = null;
                tempPayment.date_updated = null;
                tempPayment.description = '';
                tempPayment.id = 0;
                tempPayment.invoice_description = '';
                tempPayment.is_deleted = null;
                // tempPayment.pay_bonus = TODO
                tempPayment.pay_rate = tempPayment.pay_rate * 1.5;
                // tempPayment.pay_travel = TODO
                tempPayment.pay_total = (+(+tempPayment.pay_rate * +tempPayment.clocked_hours) + +tempPayment.pay_bonus + +tempPayment.pay_travel).toFixed(2);
                // tempPayment.payment_method = TODO
                tempPayment.payment_status = 'Custom';
                // tempPayment.quickbooks_bill_id = TODO
                // tempPayment.quickbooks_bill_payment_id = TODO
                tempPayment.request_clock_in = '';
                tempPayment.request_clock_out = '';
                tempPayment.request_description = '';
                tempPayment.status = 'Unresolved';
                tempPayment.type = 'Overtime';
                tempPayment.update_log = [];

                this.overtimePaymentForEdit = tempPayment;
            },
            paymentFilter(value, search, item) {
                return (item.nurse_name != null &&
                    search != null &&
                    item.nurse_name.toString().indexOf(search) !== -1);
            },
            updateTables($context) {
                this.loadingPercent = 0;
                this.loading = true;
                this.getShiftPayments($context);
                this.getNursePayments($context);
            },
            getStatusColorClass(item) {
                return this.colors[item.status] + '--text';
            },
            getProviders() {
                let data = {

                };
                modRequest.request('sa.member.load_providers', {}, {}, function (response) {
                    if (response.success) {
                        this.providers = [];
                        for (let i = 0; i < response.providers.length; i++) {
                            let provider = response.providers[i];
                            this.providers.push({
                                id: provider.id,
                                name: provider.company
                            })
                            this.provider_id = this.provider__id;
                        }
                    } else {
                        console.log('Error');
                        console.log(response);
                    }
                }.bind(this), function (response) {
                    console.log('Failed');
                    console.log(response);
                });
            },
            getPayPeriods() {
                modRequest.request('sa.payroll.get_pay_periods', {}, {}, function (response) {
                    if (response.success) {
                        for (let i = 0; i < response.periods.length; i++) {
                            let period = response.periods[i];
                            this.pay_periods.push({
                                display: period.display,
                                combined: period.combined
                            })
                        }
                        for (let i = 0; i < this.pay_periods.length; i++) {
                            if (this.pay_periods[i].combined == this.period) {
                                this.pay_period = this.pay_periods[i].combined;
                            }
                        }

                        this.unresolved_only = this.show_unresolved_only == 1;
                        this.getSearchableNurses();
                        this.getShiftPayments();
                        this.getNursePayments();
                    } else {
                        console.log('Error');
                        console.log(response);
                    }
                }.bind(this), function (response) {
                    console.log('Failed');
                    console.log(response);
                });
            },
            getSearchableNurses() {
                modRequest.request('sa.member.load_nurses', {}, {}, function (response) {
                    if (response.success) {
                        let searchable_nurses = [];
                        for (var i = 0; i < response.nurses.length; i++) {
                            var nurse = response.nurses[i];
                            var searchable_nurse = {
                                id: nurse.id,
                                name: nurse.first_name + ' ' + nurse.last_name
                            };
                            searchable_nurses.push(searchable_nurse);
                        }
                        this.searchable_nurses = searchable_nurses;
                    } else {
                        console.log('Error');
                        console.log(response);
                    }
                }.bind(this), function (response) {
                    console.log('Failed');
                    console.log(response);
                });
            },
            getShiftPayments($context) {
                let data = {
                    provider_id: this.provider_id,
                    pay_period: this.pay_period,
                    unresolved_only: this.unresolved_only,
                    date: this.search_date,
                    get_zero_hour_payments: this.includeZeroClockedHours,

                };

                if ($context == 'pay_period') {
                    this.shift_times = [];
                    this.search_date = '';
                }

                modRequest.request('sa.payroll.get_shift_payments', {}, data, function (response) {
                    this.shift_payments = [];
                    if (response.success) {
                        if (response.shift_payments) {
                            for (let i = 0; i < response.shift_payments.length; i++) {
                                let payment = response.shift_payments[i];
                                this.shift_payments.push({
                                    nurse_name: payment.nurse_name,
                                    nurse_route: payment.nurse_route,
                                    rate: parseFloat(payment.rate).toFixed(2),
                                    changed_rate: parseFloat(payment.rate).toFixed(2),
                                    payment_id: payment.payment_id,
                                    shift_name: payment.shift_name,
                                    shift_time: payment.shift_time,
                                    shift_route: payment.shift_route,
                                    status: payment.status,
                                    clocked_hours: parseFloat(payment.clocked_hours).toFixed(2),
                                    clock_times: payment.clock_times,
                                    request_description: payment.request_description,
                                    request_clock_in: payment.request_clock_in,
                                    clock_in_time_picker: payment.clock_in,
                                    request_clock_out: payment.request_clock_out,
                                    clock_out_time_picker: payment.clock_out,
                                    date: payment.date,
                                    amount: parseFloat(payment.amount).toFixed(2),
                                    changed_amount: parseFloat(payment.amount).toFixed(2),
                                    description: payment.description,
                                    changed_description: payment.description,
                                    type: this.paymentType(payment.type, payment.pay_holiday, payment.bill_holiday),
                                    shift_id: payment.shift_id,
                                    resolved_by: payment.resolved_by,
                                    supervisor_name: payment.supervisor_name,
                                    supervisor_code: payment.supervisor_code,
                                    supervisor_signature: payment.supervisor_signature,
                                    clock_in_type: payment.clock_in_type,
                                    timeslip: payment.timeslip
                                });
                                if ($context == 'pay_period' || !$context) {
                                    this.shift_times.push(payment.date);
                                }
                            }
                            this.shift_times.sort();
                            this.loadingPercent += 50;
                        } else {
                            this.loadingPercent += 50;
                        }
                    } else {
                        console.log('Error');
                        console.log(response);
                        this.loadingPercent += 50;
                    }
                }.bind(this), function (response) {
                    console.log('Failed');
                    console.log(response);
                    this.loadingPercent += 50;
                });
            },
            getNursePayments($context) {
                let data = {
                    provider_id: this.provider_id,
                    pay_period: this.pay_period,
                    unresolved_only: this.unresolved_only
                };
                modRequest.request('sa.payroll.get_nurse_payments', {}, data, function (response) {
                    this.nurse_payments = [];
                    if (response.success) {
                        for (var k in response.nurse_payments) {
                            let payment = response.nurse_payments[k];
                            this.nurse_payments.push({
                                nurse_name: payment.nurse_name,
                                nurse_route: payment.nurse_route,
                                clocked_hours: parseFloat(payment.clocked_hours).toFixed(2),
                                amount: parseFloat(payment.amount).toFixed(2),
                                bonus_amount: parseFloat(payment.bonus_amount).toFixed(2),
                                rate: parseFloat(payment.rate).toFixed(2),
                                has_unresolved_payments: payment.has_unresolved_payments
                            });
                        }
                        this.loadingPercent += 50;
                    } else {
                        console.log('Error');
                        console.log(response);
                        this.loadingPercent += 50;
                    }
                }.bind(this), function (response) {
                    console.log('Failed');
                    console.log(response);
                    this.loadingPercent += 50;
                });
            },
            saveChanges(payment, dialog) {

                if (this.holidayHoursDisplay.toString().endsWith('.')) {
                    
                    this.holidayHoursDisplay = this.holidayHoursDisplay.slice(0, -1);
                    this.holidayHoursDisplay = parseFloat(this.holidayHoursDisplay);
                }

                var data = {
                    shift: this.shiftForEdit,
                    standard_payment: this.standardPaymentForEdit,
                    overtime_payment: this.overtimePaymentForEdit
                }
                this.$refs.form.validate();


                setTimeout(function () {
                    if (this.$refs.form.validate()) {
                        modRequest.request('sa.payroll.save_payment_changes', {}, data, function (response) {
                            if (response.success) {
                                this.getShiftPayments();
                                this.getNursePayments();
                                dialog.value = false;
                            } else {
                                console.log('Error');
                                console.log(response);
                            }
                        }.bind(this), function (response) {
                            console.log('Failed');
                            console.log(response);
                        });
                    } else {
                        window.scrollTo(0, 0);
                    }
                }.bind(this), 100);
            },
            resolvePayment(payment) {
                let data = {
                    payment_id: payment.payment_id
                }

                modRequest.request('sa.payroll.resolve_payment', {}, data, function (response) {
                    if (response.success) {
                        payment.status = 'Resolved'
                    } else {
                        console.log('Error');
                        console.log(response);
                    }
                }, function (response) {
                    console.log('Failed');
                    console.log(response);
                });
            },
            paymentType(type, pay_holiday, bill_holiday) {

                if (pay_holiday != 0 && bill_holiday != 0) {

                    return type + ' & Holiday';
                } else { return type; }
            },
            fixHolidayHours() {

                if (!this.standardPaymentForEdit) {
                  return 0;
                }

                const standardHours = parseFloat(this.standardPaymentForEdit.clocked_hours);
                let tempHolidayHours = (this.standardPaymentForEdit.holiday_percentage * standardHours);
                
                if (tempHolidayHours.toString().split('.')[1]?.length >= 3) {
                    this.holidayHours = tempHolidayHours.toFixed(2);
                } else {
                    this.holidayHours = tempHolidayHours;
                }

                if (this.holidayHours > standardHours) {
                    this.holidayHours = standardHours;
                }
            }
        }
    });
});
