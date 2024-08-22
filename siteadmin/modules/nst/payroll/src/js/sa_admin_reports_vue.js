window.addEventListener('load', function () {
    Vue.component('vue-timepicker', window.VueTimepicker.default);
    Vue.component('sa-admin-reports-view', {
        // language=HTML
        template: /*html*/`
            <div class="container-fluid" id="pay-period-container">
                <div class="row">
                    <div class="col-12">
                        <v-app>
                            <div class="card">
                                <div class="card-header"
                                        v-show="tab == 0">
                                    <div class="center">
                                        <v-alert
                                            dismissible
                                            prominent
                                            type="warning"
                                            :value="alert"
                                            >{{ alertMessage }}</v-alert>
                                    </div>
                                    <div class="row">
                                        
                                        <div class="col-6">
                                            <h4 class="card-title">Pay Reports</h4>
                                        </div>
                                        <div class="col-6 text-right">
                                            <v-spacer></v-spacer>
                                            <v-btn outlined @click="updateTables">
                                                REFRESH
                                            </v-btn>
                                            <v-btn outlined @click="toggleFilters" id="filter-toggle">
                                                FILTER
                                            </v-btn>
                                        </div>
                                    </div>
                                    <div id="reports_filter" v-if="show_filters">
                                        <div class="row">
                                            <div class="col-2 pt-0">
                                                <v-menu
                                                        ref="menu_start_date"
                                                        v-model="menu_start_date"
                                                        :close-on-content-click="true"
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
                                                            v-if="menu_start_date"
                                                            v-model="start_date"
                                                            no-title
                                                            scrollable
                                                            @change="updateDateRange();updateTables()"
                                                    >
                                                    </v-date-picker>
                                                </v-menu>
                                            </div>
                                            <div class="col-2 pt-0" v-if="use_end_date">
                                                <v-menu
                                                        ref="menu_end_date"
                                                        v-model="menu_end_date"
                                                        :close-on-content-click="true"
                                                        :nudge-right="40"
                                                        transition="scale-transition"
                                                        max-width="290px"
                                                        min-width="290px"
                                                        attach
                                                        :offset-y="y"
                                                        @change="updateDateRange();updateTables()"
                                                >
                                                    <template v-slot:activator="{ on, attrs }">
                                                        <v-text-field
                                                                autocomplete="off"
                                                                v-model="end_date"
                                                                :label="end_date_label"
                                                                prepend-icon="mdi-calendar"
                                                                readonly
                                                                v-bind="attrs"
                                                                v-on="on"
                                                                :rules="rules"
                                                                :disabled="endDateDisabled"
                                                                @change="updateTables()"
                                                        ></v-text-field>
                                                    </template>
                                                    <v-date-picker
                                                            v-if="menu_end_date"
                                                            v-model="end_date"
                                                            no-title
                                                            scrollable
                                                            @change="updateTables"
                                                            :min="start_date"
                                                    >
                                                        <!--       :min="todayDate"
                                                               :max="max_start_date"
                                                       >-->
                                                    </v-date-picker>
                                                </v-menu>
                                            </div>
                                            <div class="col-2 pb-0" id="provider-dropdown-col">
                                                <v-spacer></v-spacer>
                                                <v-autocomplete
                                                        v-model="provider_id"
                                                        prepend-icon="fa-building"
                                                        :items="providers"
                                                        item-text="name"
                                                        item-value="id"
                                                        label="Provider"
                                                        clearable
                                                        @change="updateTables"
                                                ></v-autocomplete>
                                            </div>
                                            <div class="col-2 pb-0 pt-0" id="payment_method-dropdown-col">
                                                <v-spacer></v-spacer>
                                                <v-autocomplete
                                                        v-model="payment_method_filter"
                                                        prepend-icon="fa-building"
                                                        :items="payment_method_items"
                                                        label="Payment Method"
                                                        clearable
                                                        width="300"
                                                        @change="updateTables"
                                                ></v-autocomplete>
                                            </div>
                                            <div class="col-2 pb-0 pt-0" id="payment_status-dropdown-col">
                                                <v-spacer></v-spacer>
                                                <v-autocomplete
                                                        v-model="payment_status_filter"
                                                        prepend-icon="fa-building"
                                                        :items="payment_status_items"
                                                        label="Payment Status"
                                                        clearable
                                                        width="300"
                                                        @change="updateTables"
                                                ></v-autocomplete>
                                            </div>
                                            <div class="col-2" v-if="!use_end_date"></div>
                                            <div class="col-2 text-right">
                                                <span class="pr-3">Use End date</span>
                                                <v-checkbox
                                                        color="primary"
                                                        v-model="use_end_date"
                                                        @change="updateDateRange();updateTables()"
                                                ></v-checkbox>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <v-tabs v-model="tab" id="pay-period-tabs">
                                        <v-tab class="black--text">Nurses</v-tab>
                                        <v-tab class="black--text">Government Reporting</v-tab>
                                        <v-tab class="black--text">Inactive</v-tab>
                                        <v-tab class="black--text">DNR</v-tab>
                                        <v-tab class="black--text">Earnings</v-tab>
                                        <v-tab class="black--text">Shifts</v-tab>
                                        <v-tab class="black--text">Schedule</v-tab>
                                        <v-spacer></v-spacer>
                                        <v-autocomplete
                                                v-show="tab == 0"
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
                                            <div class="table-responsive" ref="for_print">
                                                <v-card>
                                                    <v-card-text>
                                                        <v-row>
                                                            <v-data-table 
                                                                    dense
                                                                    ref="paymentTable"
                                                                    class="table table-responsive-md table-header-no-wrap"
                                                                    :headers="nurse_headers"
                                                                    :items="nurse_shift_payments"
                                                                    :search="search"
                                                                    :custom-filter="paymentFilter"
                                                                    @current-items="setCurrent"
                                                                    multi-sort
                                                            >
                                                                <template v-slot:item.nurse_name="{ item }">
                                                                    <a v-bind:href="item.nurse_route"
                                                                       class="block mt-3 blue--text" target="_blank">
                                                                        {{ item.nurse_name }}
                                                                    </a>
                                                                </template>
                                                                <template v-slot:item.provider_name="{ item }">
                                                                    <span class="block mt-3">{{item.provider_name}}</span>
                                                                </template>
                                                                <template v-slot:item.clocked_hours="{ item }">
                                                                    <span v-if="!item.editing" class="mt-1 block grey--text">{{item.date}}</span>
                                                                    <span v-if="!item.editing">{{item.clocked_hours}}</span>
                                                                    <v-text-field class="mt-3" dense v-else
                                                                                  v-model="item.clocked_hours"></v-text-field>
                                                                </template>
                                                                <template v-slot:item.pay_rate="{ item }">
                                                                    <span class="block mt-3" v-if="!item.editing">{{item.pay_rate}}</span>
                                                                    <v-text-field class="mt-3" dense v-else
                                                                                  v-model="item.pay_rate"></v-text-field>
                                                                </template>
                                                                <template v-slot:item.bill_rate="{ item }">
                                                                    <span class="block mt-3" v-if="!item.editing">{{item.bill_rate}}</span>
                                                                    <v-text-field class="mt-3" dense v-else
                                                                                  v-model="item.bill_rate"></v-text-field>
                                                                </template>
                                                                <template v-slot:item.pay_bonus="{ item }">
                                                                    <span class="block mt-3" v-if="!item.editing">{{item.pay_bonus}}</span>
                                                                    <v-text-field class="mt-3" dense v-else
                                                                                  v-model="item.pay_bonus"></v-text-field>
                                                                </template>
                                                                <template v-slot:item.bill_bonus="{ item }">
                                                                    <!--<span class="block mt-3">{{'$' + item.pay_rate}}</span>-->
                                                                    <span class="block mt-3" v-if="!item.editing">{{item.bill_bonus}}</span>
                                                                    <v-text-field class="mt-3" dense v-else
                                                                                  v-model="item.bill_bonus"></v-text-field>
                                                                </template>
                                                                <template v-slot:item.pay_travel="{ item }">
                                                                    <span class="block mt-3" v-if="!item.editing">{{item.pay_travel}}</span>
                                                                    <v-text-field class="mt-3" dense v-else
                                                                                  v-model="item.pay_travel"></v-text-field>
                                                                </template>
                                                                <template v-slot:item.bill_travel="{ item }">
                                                                    <span class="block mt-3" v-if="!item.editing">{{item.bill_travel}}</span>
                                                                    <v-text-field class="mt-3" dense v-else
                                                                                  v-model="item.bill_travel"></v-text-field>
                                                                </template>
                                                                <template v-slot:item.pay_holiday="{ item }">
                                                                    <span class="block mt-3" v-if="!item.editing">{{item.pay_holiday}}</span>
                                                                    <v-text-field class="mt-3" dense v-else
                                                                                  v-model="item.pay_holiday "></v-text-field>
                                                                </template>
                                                                <template v-slot:item.bill_holiday="{ item }">
                                                                    <span class="block mt-3" v-if="!item.editing">{{item.bill_holiday}}</span>
                                                                    <v-text-field class="mt-3" dense v-else
                                                                                  v-model="item.bill_holiday"></v-text-field>
                                                                </template>
                                                                <template v-slot:item.pay_total="{ item }">
                                                                    <span class="block mt-3">{{'$' + item.pay_total}}</span>
                                                                </template>
                                                                <template v-slot:item.bill_total="{ item }">
                                                                    <span class="block mt-3">{{'$' + item.bill_total}}</span>
                                                                </template>
                                                                <template v-slot:item.payment_method="{ item }">
                                                                    <span v-if="!item.editing" class="block mt-3">{{item.payment_method}}</span>
                                                                    <v-select
                                                                            v-else
                                                                            v-model="item.payment_method"
                                                                            :items="payment_method_items"
                                                                            :menu-props="{ bottom: true, offsetY: true }"
                                                                            label="Payment Method"
                                                                            :disabled="!item.editing"
                                                                    ></v-select>
                                                                </template>
                                                                <template v-slot:item.payment_status="{ item }">
                                                                    <v-tooltip v-if="!item.editing" :disabled="(item.payment_status != 'Corrected') || !item.corrected_comment" bottom>
                                                                        <template v-slot:activator="{ on, attrs }">
                                                                            <span 
                                                                                v-on="on" 
                                                                                v-bind="attrs" 
                                                                                :class="'mt-3 ' + status_classes[item.payment_status]">{{item.payment_status}}</span>
                                                                        </template>
                                                                    </v-tooltip>
                                                                    <span v-if="item.quickbooks_purchase_id || item.quickbooks_bill_id || item.quickbooks_bill_payment_id" 
                                                                    style="background-color: #4caf50; color: white; border-radius: 50%; padding: 3px; font-size: smaller">QB</span>
                                                                    <span class="block">{{item.corrected_comment}}</span>
                                                                    <v-select 
                                                                        v-if="item.editing"
                                                                        v-model="item.payment_status"
                                                                        :items="payment_status_items"
                                                                        :menu-props="{ bottom: true, offsetY: true }"
                                                                        label="Payment Status"
                                                                        :disabled="!item.editing"
                                                                        @change="onStatusChange(item)"
                                                                    ></v-select>
                                                                    <v-dialog 
                                                                            v-model="item.dialog"
                                                                            max-width="450" >
                                                                        <v-card>
                                                                            <v-card-title class="text-h5">
                                                                                Correction Comment
                                                                            </v-card-title>
                                                                            <v-card-text>
                                                                                Please comment on the correction made.
                                                                                <v-textarea v-model="item.corrected_comment"></v-textarea>
                                                                            </v-card-text>
                                                                            <v-card-actions>
                                                                                <v-spacer></v-spacer>
                                                                                <v-btn color="green darken-1" text @click="item.dialog = false">
                                                                                    submit
                                                                                </v-btn>
                                                                            </v-card-actions>
                                                                        </v-card>
                                                                    </v-dialog>
                                                                </template>
                                                                <template v-slot:item.has_unresolved_payments="{ item }">
                                                                    <span class="block mt-3">{{item.has_unresolved_payments}}</span>
                                                                </template>
                                                                <template v-slot:item.actions="{ item }">
                                                                    <v-btn
                                                                            v-show="!item.editing"
                                                                            class="mt-1 mr-1"
                                                                            color="primary"
                                                                            icon
                                                                            @click="editPayReports(item);"
                                                                    ><v-icon color="primary">mdi-pencil</v-icon></v-btn>

                                                                    <v-dialog max-width="300">
                                                                        <template v-slot:activator="{ on, attrs }">
                                                                            <v-btn
                                                                                    v-show="!item.editing"
                                                                                    class="mt-1 mr-1"
                                                                                    icon
                                                                                    color="red"
                                                                                    v-on="on"
                                                                                    v-bind="attrs"
                                                                            ><v-icon color="red">mdi-trash-can</v-icon></v-btn>
                                                                        </template>
                                                                        <template v-slot:default="dialog">
                                                                            <v-card>
                                                                                <v-toolbar color="red" class="text-h4 white--text">
                                                                                    Are you sure?
                                                                                </v-toolbar>
                                                                                <v-card-text class="pt-5">
                                                                                    Are you sure you want to delete this payment? It will delete the corresponding shift aswell.
                                                                                </v-card-text>
                                                                                <v-card-actions class="justify-end">
                                                                                    <v-btn
                                                                                            text
                                                                                            color="grey dark-3"
                                                                                            v-on:click="dialog.value = false">Cancel</v-btn>
                                                                                    <v-btn
                                                                                            color="red"
                                                                                            class="white--text"
                                                                                            @click="deletePayment(item); dialog.value = false;"
                                                                                    >Yes, Delete Payment</v-btn>
                                                                                </v-card-actions>
                                                                            </v-card>
                                                                        </template>
                                                                    </v-dialog>
                                                                    <v-btn 
                                                                            v-show="item.editing" 
                                                                            class="mt-2 mr-1"
                                                                            icon
                                                                            color="primary" 
                                                                            @click="saveNurseReport(item)"
                                                                    ><v-icon color="primary">mdi-content-save</v-icon></v-btn>
                                                                    <v-btn 
                                                                            v-show="item.editing" 
                                                                            class="mt-2 mr-1"
                                                                            icon
                                                                            color="light"
                                                                            @click="item.editing = false;"
                                                                    ><v-icon color="gray">mdi-cancel</v-icon></v-btn>
                                                                </template>
                                                            </v-data-table>
                                                        </v-row>
                                                        <v-row>
                                                            <v-btn
                                                                    color="primary"
                                                                    class="mr-1"
                                                                    :class="{'mr-auto': !markedAsPaid}"
                                                                    @click="markAllAsPaid"
                                                            >Mark All As Paid</v-btn>
                                                            <v-btn
                                                                    color="success"
                                                                    class="mr-1 mr-auto"
                                                                    @click="saveMarkAsPaid"
                                                                    v-if="markedAsPaid"
                                                            >Save</v-btn>
                                                            <sa-create-payment-modal
                                                                    class="ml-auto"
                                                                @paymentCreated="updateTables"
                                                            >
                                                                    <v-icon>mdi-plus</v-icon> Add Payment
                                                            </sa-create-payment-modal>
                                                        </v-row>
                                                    </v-card-text>
                                                </v-card>
                                            </div>
                                            
                                            <div class="row">
                                                <div class="col-xs-12 col-sm-6 d-flex align-content-start flex-wrap">
                                                    <v-btn
                                                        class="ma-1"
                                                        color="success"
                                                        x-large
                                                        @click="generateExcel"
                                                    >View as Excel
                                                    </v-btn>
                                                    <v-btn
                                                        class="ma-1"
                                                        color="success"
                                                        x-large
                                                        @click="sendToQuickbooks"
                                                    >Send to Quickbooks
                                                    </v-btn>
                                                    <v-btn
                                                        class="ma-1"
                                                        color="success"
                                                        x-large
                                                        @click="generateNachaFile"
                                                        >Generate NACHA File
                                                    </v-btn>
                                                    <v-btn
                                                        class="ma-1"
                                                        color="success"
                                                        x-large
                                                        @click="generatePaycardUploadFile"
                                                        :loading="generatingPaycardFile"
                                                        :disabled="generatingPaycardFile"
                                                    >Generate Paycard Upload File
                                                    </v-btn>
                <!--                                    <v-btn-->
                <!--                                            color="success"-->
                <!--                                            x-large-->
                <!--                                            @click="generatePdf"-->
                <!--                                    >View as PDF-->
                                                    <!-- </v-btn> -->
                                                </div>
                                            </div>
                                        </v-tab-item>
                                        <v-tab-item>
                                            <sa-reports-government-reporting-view>
                                            </sa-reports-government-reporting-view>
                                        </v-tab-item>
                                        <v-tab-item>
                                        <sa-reports-inactive-view>
                                        </sa-reports-inactive-view>
                                        </v-tab-item>
                                        <v-tab-item>
                                        <sa-reports-dnr-view>
                                        </sa-reports-dnr-view>
                                        </v-tab-item>
                                        <v-tab-item>
                                        <sa-reports-earnings-view>
                                        </sa-reports-earnings-view>
                                        </v-tab-item>
                                        <v-tab-item>
                                        <sa-reports-shifts-view>
                                        </sa-reports-shifts-view>
                                        </v-tab-item>
                                        <v-tab-item>
                                        <sa-reports-schedule-view>
                                        </sa-reports-schedule-view>
                                        </v-tab-item>
                                    </v-tabs-items>
                                </div>
                            </div>
                            <p class="block red--text mt-1" v-for="message in errorMessages">{{ message }}</p><br>
                        </v-app>
                    </div>
                </div>
            </div>`,
        props: [
            'provider__id',
            'period'
        ],
        data() {
            return {
                markedAsPaid: false,
                show_filters: false,
                tab: null,
                menu_start_date: false,
                menu_end_date: false,
                use_end_date: false,
                start_date: new Date().toLocaleString('sv', { timeZoneName: 'short' }).slice(0, 10),
                start_date_label: 'Date',
                end_date: new Date().toLocaleString('sv', { timeZoneName: 'short' }).slice(0, 10),
                end_date_label: 'End Date',
                rules: [],
                y: true,
                todayDate: new Date(-20).toLocaleString('sv', { timeZoneName: 'short' }).slice(0, 10),
                max_start_date: new Date(+20).toLocaleString('sv', { timeZoneName: 'short' }).slice(0, 10),
                select: 'Direct Deposit',
                status_classes: {
                    'Paid': 'success--text',
                    'Pending': 'warning--text',
                    'Unpaid': 'red--text'
                },
                payment_method_items: ['Direct Deposit', 'Pay Card', 'Paper Check'],
                payment_status_items: ['Paid', 'Pending', 'Unpaid', 'Corrected', 'Under Review'],
                nurse_headers: [
                    {
                        text: 'Nurse Name',
                        align: 'start',
                        sortable: true,
                        value: 'nurse_name'
                    },
                    {
                        text: 'Provider Name',
                        sortable: true,
                        value: 'provider_name'
                    },
                    {
                        text: 'Clocked Hours',
                        sortable: true,
                        value: 'clocked_hours'
                    },
                    {
                        text: 'Pay Rate',
                        sortable: true,
                        value: 'pay_rate'
                    },
                    {
                        text: 'Bill Rate',
                        sortable: true,
                        value: 'bill_rate'
                    },
                    {
                        text: 'Pay Bonus',
                        sortable: true,
                        value: 'pay_bonus'
                    },
                    {
                        text: 'Bill Bonus',
                        sortable: true,
                        value: 'bill_bonus'
                    },
                    {
                        text: 'Pay Travel',
                        sortable: true,
                        value: 'pay_travel'
                    },
                    {
                        text: 'Bill Travel',
                        sortable: true,
                        value: 'bill_travel'
                    },
                    {
                        text: 'Pay Holiday',
                        sortable: true,
                        value: 'pay_holiday'
                    }, {
                        text: 'Bill Holiday',
                        sortable: true,
                        value: 'bill_holiday'
                    },
                    {
                        text: 'Pay Total',
                        sortable: true,
                        value: 'pay_total'
                    },
                    {
                        text: 'Bill Total',
                        sortable: true,
                        value: 'bill_total'
                    },
                    {
                        text: 'Payment Method',
                        sortable: true,
                        value: 'payment_method'
                    }, {
                        text: 'Payment Status',
                        sortable: true,
                        value: 'payment_status'
                    },
                    {
                        width: '150px',
                        text: 'Actions',
                        sortable: false,
                        printable: false,
                        value: 'actions'
                    }
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
                        value: 'pay_rate'
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
                shift_payments: [],
                nurse_shift_payments: [],
                pay_periods: [],
                pay_period: '',
                providers: [],
                provider_id: null,
                payment_method_filter: null,
                payment_status_filter: null,
                search: '',
                searchable_nurses: [],
                time_clock_rules: [],
                errorMessages: [],
                currentItems: null,
                alert: false,
                alertMessage: '',
                filteredItems: [],
                generatingPaycardFile: false,
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
            statusDisabled: function () {
                return false;
            },
            endDateDisabled: function () {
                return !this.use_end_date;
            },
        },
        created: function () {
            this.time_clock_rules = [
                v => !!v || 'This field is required',
            ];
        },
        mounted: function () {
            this.getPayPeriods();
            this.getProviders();
            this.getNursePaymentsForReports();
        },
        methods: {
            toggleFilters() {
                this.show_filters = !this.show_filters;
            },
            generatePdf() {
                let shift_start_date_ = new Date(this.start_date);
                shift_start_date_.setDate(shift_start_date_.getDate() - 1);
                let shift_start_date = shift_start_date_.toISOString().slice(0, 10).replaceAll("-", "");
                let shift_end_date = this.end_date.replaceAll("-", "");
                let shift_date_range = shift_start_date + "_" + shift_end_date;
                let data = {
                    excel_filename: 'report.Xlsx',
                    excel_type: 'Xlsx',
                    nurse_headers: this.nurse_headers,
                    nurse_shift_payments: this.nurse_shift_payments,
                    provider_id: this.provider_id,
                    pay_period: shift_date_range,
                    pdf_file_name: 'report.pdf'
                };
                modRequest.request('sa.payroll.report_to_pdf', {}, data, function (response) {
                    if (response.success) {
                        window.open(response['file_route'], "_blank");
                    } else {
                        console.log('Error');
                        console.log(response);
                    }
                }, function (response) {
                    console.log('Failed');
                    console.log(response);
                });
            },
            generateExcel() {
                // Build array of IDs for excel export
                let paymentIds = '';
                if (this.filteredItems) {
                    paymentIds = this.filteredItems[0].payment_id;
                    for (let i = 1; i < this.filteredItems.length; i++) {
                        paymentIds += ',' + this.filteredItems[i].payment_id;
                    }
                } else {
                    return;
                }

                // Copy nurse_headers array so we can arbitrarily add extra headers to it
                let headers = [...this.nurse_headers];
                // Adding after nurse column
                headers.splice(1, 0, { 'text': 'Credentials', 'value': 'nurse_credentials' });
                // adding after hours column
                headers.splice(4, 0, { 'text': 'Date', 'value': 'date' });

                let data = {
                    excel_filename: 'report.Xlsx',
                    excel_type: 'Xlsx',
                    nurse_headers: headers,
                    nurse_shift_payments: paymentIds,
                    provider_id: this.provider_id,
                    pay_period: 'all',
                };

                modRequest.request('sa.payroll.report_to_excel', {}, data, function (response) {
                    if (response.success) {
                        window.open(response['file_route'], "_blank");
                    } else {
                        console.log('Error');
                        console.log(response);
                    }
                }, function (response) {
                    console.log('Failed');
                    console.log(response);
                });
            },
            saveNurseReport(nurse_shift_payment) {
                let data = {
                    nurse_shift_payment: nurse_shift_payment,
                    clocked_hours: nurse_shift_payment.clocked_hours
                }

                setTimeout(function () {
                    modRequest.request('sa.payroll.save_payment_changes_for_reports', {}, data, function (response) {
                        if (response.success) {
                            this.getSingleNursePaymentsForReports(nurse_shift_payment);
                            nurse_shift_payment.editing = false;
                        } else {
                            console.log('Error');
                            console.log(response);
                        }
                    }.bind(this), function (response) {
                        console.log('Failed');
                        console.log(response);
                    });
                }.bind(this), 100);
            },
            paymentFilter(value, search, item) {
                return (item.nurse_name != null &&
                    search != null &&
                    item.nurse_name.toString().indexOf(search) !== -1);
            },
            updateTables() {
                this.getNursePaymentsForReports();
            },
            updateDateRange() {
                if (this.use_end_date) {
                    if (this.end_date < this.start_date) {
                        this.end_date = this.start_date;
                    }
                } else {
                    this.end_date = this.start_date;
                }
            },
            getStatusColorClass(item) {
                return this.colors[item.status] + '--text';
            },
            getProviders() {
                let data = {};
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
                        this.getSearchableNurses();
                        this.getNursePaymentsForReports();
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
            getSingleNursePaymentsForReports(nurse_shift_payment) {

                let data = {
                    nurse_shift_payment: nurse_shift_payment
                };
                modRequest.request('sa.payroll.get_single_nurse_shift_payments_for_reports', {}, data, function (response) {
                    if (response.success) {
                        nurse_shift_payment.clocked_hours = parseFloat(response.nurse_shift_payment.clocked_hours).toFixed(2);
                        nurse_shift_payment.pay_rate = parseFloat(response.nurse_shift_payment.pay_rate).toFixed(2);
                        nurse_shift_payment.bill_rate = parseFloat(response.nurse_shift_payment.bill_rate).toFixed(2);
                        nurse_shift_payment.pay_bonus = parseFloat(response.nurse_shift_payment.pay_bonus).toFixed(2);
                        nurse_shift_payment.bill_bonus = parseFloat(response.nurse_shift_payment.bill_bonus).toFixed(2);
                        nurse_shift_payment.pay_travel = parseFloat(response.nurse_shift_payment.pay_travel).toFixed(2);
                        nurse_shift_payment.bill_travel = parseFloat(response.nurse_shift_payment.bill_travel).toFixed(2);
                        nurse_shift_payment.pay_holiday = parseFloat(response.nurse_shift_payment.pay_holiday).toFixed(2);
                        nurse_shift_payment.bill_holiday = parseFloat(response.nurse_shift_payment.bill_holiday).toFixed(2);
                        nurse_shift_payment.pay_total = parseFloat(response.nurse_shift_payment.pay_total).toFixed(2);
                        nurse_shift_payment.bill_total = parseFloat(response.nurse_shift_payment.bill_total).toFixed(2);
                        nurse_shift_payment.payment_method = response.nurse_shift_payment.payment_method;
                        nurse_shift_payment.payment_status = response.nurse_shift_payment.payment_status;
                    }
                });
            },
            getNursePaymentsForReports() {
                let shift_start_date = this.start_date.replaceAll("-", "");
                let shift_end_date = this.end_date.replaceAll("-", "");

                let shift_date_range = shift_start_date + "_" + shift_end_date;
                let data = {
                    provider_id: this.provider_id,
                    pay_period: shift_date_range,
                    payment_method: this.payment_method_filter,
                    payment_status: this.payment_status_filter,
                    nurse_name: this.search,
                };
                modRequest.request('sa.payroll.get_nurse_shift_payments_for_reports', {}, data, function (response) {
                    this.nurse_shift_payments = [];
                    if (response.success) {
                        for (var k in response.nurse_shift_payments) {
                            let payment = response.nurse_shift_payments[k];
                            this.nurse_shift_payments.push({
                                date: payment.date,
                                payment_id: payment.payment_id,
                                nurse_name: payment.nurse_name,
                                nurse_route: payment.nurse_route,
                                provider_name: payment.provider_name,
                                clocked_hours: parseFloat(payment.clocked_hours).toFixed(2),
                                pay_rate: parseFloat(payment.pay_rate).toFixed(2),
                                bill_rate: parseFloat(payment.bill_rate).toFixed(2),
                                pay_bonus: parseFloat(payment.pay_bonus).toFixed(2),
                                bill_bonus: parseFloat(payment.bill_bonus).toFixed(2),
                                pay_travel: parseFloat(payment.pay_travel).toFixed(2),
                                bill_travel: parseFloat(payment.bill_travel).toFixed(2),
                                pay_holiday: parseFloat(payment.pay_holiday).toFixed(2),
                                bill_holiday: parseFloat(payment.bill_holiday).toFixed(2),
                                pay_total: parseFloat(payment.pay_total).toFixed(2),
                                bill_total: parseFloat(payment.bill_total).toFixed(2),
                                has_unresolved_payments: payment.has_unresolved_payments,
                                payment_method: payment.payment_method,
                                payment_status: payment.payment_status,
                                editing: false,
                                corrected_comment: payment.corrected_comment,
                                dialog: false,
                                quickbooks_purchase_id: payment.quickbooks_purchase_id,
                                quickbooks_bill_id: payment.quickbooks_bill_id,
                                quickbooks_bill_payment_id: payment.quickbooks_bill_payment_id,
                            });
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
            saveChanges(payment, dialog) {
                let data = {
                    payment_id: payment.payment_id,
                    clock_in_time: payment.clock_in_time_picker,
                    clock_out_time: payment.clock_out_time_picker,
                    hourly_rate: payment.changed_rate,
                    bonus_amount: payment.changed_amount,
                    bonus_description: payment.changed_description
                }
                this.$refs.form.validate();


                setTimeout(function () {
                    if (this.$refs.form.validate()) {
                        modRequest.request('sa.payroll.save_payment_changes', {}, data, function (response) {
                            if (response.success) {
                                this.getNursePaymentsForReports();
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
            editPayReports(nurse_shift_payment) {
                for (let i = 0; i < this.nurse_shift_payments; i++) {
                    this.nurse_shift_payments[i].editing = false;
                }
                nurse_shift_payment.editing = true;
            },
            sendToQuickbooks() {
                if (this.search) {
                    var data = {
                        payments: this.currentItems
                    }
                } else {
                    var data = {
                        payments: this.nurse_shift_payments
                    }
                }

                modRequest.request('sa.quickbooks.get_payments_auth_route', {}, data, function (response) {
                    if (response.success) {
                        window.location.href = response.auth_url;
                    } else {
                        console.log('Error');
                        console.log(response);
                    }
                }.bind(this), function (response) {
                    console.log('Failed');
                    console.log(response);
                });
            },
            generateNachaFile() {
                if (this.search) {
                    var paymentIds = this.currentItems[0].payment_id;
                    for (let i = 1; i < this.currentItems.length; i++) {
                        paymentIds += ',' + this.currentItems[i].payment_id
                    }
                    var data = {
                        payments: String(paymentIds)
                    }
                } else {
                    var paymentIds = this.nurse_shift_payments[0].payment_id;
                    for (let i = 1; i < this.nurse_shift_payments.length; i++) {
                        paymentIds += ',' + this.nurse_shift_payments[i].payment_id
                    }

                    var data = {
                        payments: paymentIds
                    }
                }

                let app = this;

                modRequest.request('sa.payroll.generate_nacha_file', {}, data, function (response) {
                    if (response.success) {
                        if (response.messages) {
                            let messages = response.messages;
                            messages.forEach((e) => {
                                app.alertMessage = e;
                            })
                            app.alert = true;
                        }
                        window.open(response['file_route'], "_blank");
                    } else {
                        console.log('Error');
                        console.log(response);
                    }
                    this.errorMessages = response.messages;
                }.bind(this), function (response) {
                    console.log('Failed');
                    console.log(response);
                });
            },
            saveMarkAsPaid() {
                var paymentIds = this.nurse_shift_payments[0].payment_id;
                for (let i = 1; i < this.nurse_shift_payments.length; i++) {
                    paymentIds += ',' + this.nurse_shift_payments[i].payment_id
                }
                var data = {
                    payments: paymentIds
                };

                modRequest.request('sa.payroll.mark_all_as_paid', {}, data, function (response) {
                    if (response.success) {
                        this.markedAsPaid = false;
                    } else {
                        console.log('Error');
                        console.log(response);
                    }
                }.bind(this), function (response) {
                    console.log('Failed');
                    console.log(response);
                });
            },
            markAllAsPaid() {
                for (var i = 0; i < this.nurse_shift_payments.length; i++) {
                    this.nurse_shift_payments[i].payment_status = 'Paid';
                }
                this.markedAsPaid = true;
            },
            deletePayment(payment) {
                console.log(payment)
                var data = {
                    id: payment.payment_id,
                }

                modRequest.request('sa.payroll.delete_payment', {}, data, function (response) {
                    if (response.success) {
                        this.updateTables();
                    } else {
                        console.log('Error');
                        console.log(response);
                    }
                }.bind(this), function (response) {
                    console.log('Failed');
                    console.log(response);
                });
            },
            setCurrent(e) {
                this.currentItems = e;

                // set filtered items for excel export
                this.filteredItems = this.nurse_shift_payments.filter(shiftPayment => {
                    if (!this.search) {
                        return true;
                    }
                    return (shiftPayment.nurse_name.toString().indexOf(this.search) !== -1);
                })
            },
            onStatusChange(item) {
                if (item.payment_status === 'Corrected') {
                    item.dialog = true;
                }
            },
            generatePaycardUploadFile() {
                this.generatingPaycardFile = true;
                let payments = this.nurse_shift_payments.filter(this.getPayCardsOnlyFilter);
                let data = {
                    ids: payments.map(payment => payment.payment_id)
                }

                modRequest.request('sa.payroll.generate_paycard_file_xlsx', {}, data, response => {
                    if (response.success) {
                        this.generatingPaycardFile = false;
                        if (response.messages) {
                            let messages = response.messages;
                            messages.forEach((e) => {
                                app.alertMessage = e;
                            })
                            app.alert = true;
                        }

                        window.open(response['file_route'], "_blank");

                        // Set status of all effected payments to Pending
                        if (response.fundingFileIds && response.fundingFileIds.length) {
                            this.nurse_shift_payments = this.nurse_shift_payments.map(payment => response.fundingFileIds.includes(payment.payment_id) ? { ...payment, payment_status: 'Pending' } : payment);
                        }
                    } else {
                        this.generatingPaycardFile = false;
                        console.log('Error');
                    }
                    this.errorMessages = response.messages;
                }, response => {
                    this.generatingPaycardFile = false;
                    console.log('Failed');
                });
            },
            getPayCardsOnlyFilter(payment) {
                return payment.payment_method == 'Pay Card';
            }
        }
    });
});
