window.addEventListener('load', function () {
    Vue.component('sa-shift-calendar-view', {
        template: /*html*/`    
            <div class="container-fluid calendar-container">
                <v-app>
                    <div class="row">
                        <div class="col-12">
                            <div class="card provider-shift-calendar">
                                <div class="card-body">
                                    <v-row class="fill-height">
                                        <v-col>
                                            <v-sheet class="shift-calendar-sheet">
                                                <v-card-title
                                                        flat
                                                        >
                                                    <v-row>
                                                        <v-col class="d-flex shift-calendar-title">
                                                            <v-btn
                                                                    outlined
                                                                    class="mr-2"
                                                                    color="grey darken-2"
                                                                    v-on:click="setToday"
                                                            >
                                                                Today
                                                            </v-btn>
                                                            <v-spacer class="hidden-md-and-up"></v-spacer>
                                                            <v-menu
                                                                    class="hidden-md-and-up"
                                                                    bottom
                                                                    :offset-y="y"
                                                            >
                                                                <template v-slot:activator="{ on, attrs }">
                                                                    <v-btn
                                                                            class="hidden-md-and-up pull-right"
                                                                            outlined
                                                                            color="grey darken-2"
                                                                            v-bind="attrs"
                                                                            v-on="on"
                                                                    >
                                                                        <span>{{ typeToLabel[type] }}</span>
                                                                        <v-icon right>
                                                                            mdi-menu-down
                                                                        </v-icon>
                                                                    </v-btn>
                                                                </template>
                                                                <v-list>
                                                                    <v-list-item v-on:click="type = 'day'">
                                                                        <v-list-item-title>Day</v-list-item-title>
                                                                    </v-list-item>
                                                                    <v-list-item v-on:click="type = 'week'">
                                                                        <v-list-item-title>Week</v-list-item-title>
                                                                    </v-list-item>
                                                                    <v-list-item v-on:click="type = 'month'">
                                                                        <v-list-item-title>Month</v-list-item-title>
                                                                    </v-list-item>
                                                                </v-list>
                                                            </v-menu>
                                                            <v-btn
                                                                    fab
                                                                    text
                                                                    small
                                                                    class="hidden-sm-and-down"
                                                                    color="grey darken-2"
                                                                    v-on:click="prev"
                                                            >
                                                                <v-icon small>
                                                                    mdi-chevron-left
                                                                </v-icon>
                                                            </v-btn>
                                                            <v-btn
                                                                    fab
                                                                    text
                                                                    small
                                                                    class="hidden-sm-and-down"
                                                                    color="grey darken-2"
                                                                    v-on:click="next"
                                                            >
                                                                <v-icon small>
                                                                    mdi-chevron-right
                                                                </v-icon>
                                                            </v-btn>
                                                            <v-toolbar-title class="hidden-sm-and-down" id="calendar-date-text" v-if="$refs.calendar">
                                                                {{ $refs.calendar.title }}
                                                            </v-toolbar-title>
                                                        </v-col>
                                                        <v-col class="col-12 hidden-md-and-up" style="display: flex; justify-content: space-between;">
                                                            <sa-create-shift-modal-view @triggerRefresh="refresh"></sa-create-shift-modal-view>
                                                            <v-btn
                                                                    outlined
                                                                    class="mr-0"
                                                                    color="grey darken-2"
                                                                    id="shift-calendar-bulk-select-btn"
                                                                    v-on:click="toggleSelecting"
                                                            >
                                                                Bulk Select
                                                            </v-btn>
                                                            <v-btn
                                                                    outlined
                                                                    class="mr-0 shift-calendar-refresh-btn"
                                                                    color="grey darken-2"
                                                                    v-on:click="refresh"
                                                            >
                                                                Refresh
                                                            </v-btn>
                                                        </v-col>
                                                        <v-col class="text-right shift-calendar-buttons">
                                                            <sa-create-shift-modal-view @triggerRefresh="refresh"></sa-create-shift-modal-view>
                                                            <v-btn
                                                                    outlined
                                                                    color="grey darken-2"
                                                                    id="shift-calendar-bulk-select-btn"
                                                                    class="hidden-sm-and-down mr-0"
                                                                    v-on:click="toggleSelecting"
                                                            >
                                                                Bulk Select
                                                            </v-btn>
                                                            <v-dialog max-width="300">
                                                                <template v-slot:activator="{ on, attrs }">
                                                                    <v-btn
                                                                            outlined
                                                                            color="red"
                                                                            id="shift-calendar-mass-delete-btn"
                                                                            v-on="on"
                                                                            v-bind="attrs"
                                                                            :class="selectedEvents.length > 0 ? '' : 'mass-delete-hidden'"
                                                                            :disabled="!canMassDeleteShifts"
                                                                    >
                                                                        Delete Selected
                                                                    </v-btn>
                                                                </template>
                                                                <template v-slot:default="dialog">
                                                                    <v-card>
                                                                        <v-toolbar color="red" class="text-h4 white--text">
                                                                            Are you sure?
                                                                        </v-toolbar>
                                                                        <v-card-text class="pt-5">
                                                                            Do you wish to <strong class="red--text">DELETE</strong> <strong>{{selectedEvents.length}}</strong> shifts? This action cannot be undone.</v-card-text>
                                                                        </v-card-text>
                                                                        <v-card-actions class="justify-end">
                                                                            <v-btn
                                                                                text
                                                                                color="grey dark-3"
                                                                                v-on:click="dialog.value = false">Cancel</v-btn>
                                                                            <v-btn
                                                                                color="red"
                                                                                v-on:click="massDelete(); dialog.value = false;"
                                                                                prepend-icon="mdi-window-close"
                                                                                class="white--text"
                                                                                :disabled="!canMassDeleteShifts"
                                                                            >Yes, Delete</v-btn>
                                                                        </v-card-actions>
                                                                    </v-card>
                                                                </template>
                                                            </v-dialog>
                                                            <v-btn
                                                                    outlined
                                                                    color="grey darken-2"
                                                                    class="shift-calendar-refresh-btn hidden-sm-and-down mr-0"
                                                                    v-on:click="refresh"
                                                            >
                                                                Refresh
                                                            </v-btn>
                                                            <v-menu
                                                                    v-model="filters_menu"
                                                                    :close-on-content-click="false"
                                                                    offset-y>
                                                                <template v-slot:activator="{ on, attrs }">
                                                                    <v-btn 
                                                                            v-on="on"
                                                                            v-bind="attrs"
                                                                            outlined
                                                                            class="shift-calendar-filters-btn mr-0"
                                                                            color="grey darken-2"
                                                                    >
                                                                        Filters
                                                                        <v-icon right>
                                                                            mdi-menu-down
                                                                        </v-icon>
                                                                    </v-btn>
                                                                </template>
                                                                <v-card
                                                                        color="grey lighten-4"
                                                                    >
                                                                    <v-list
                                                                        color="grey lighten-4"
                                                                        class="pt-6 pb-0">
                                                                        <v-list-item>
                                                                            <v-autocomplete
                                                                                v-model="provider"
                                                                                :items="providers"
                                                                                item-text="name"
                                                                                item-value="id"
                                                                                label="Provider"
                                                                                background-color="white"
                                                                                @change="refresh"
                                                                                return-object
                                                                                dense
                                                                                outlined
                                                                                clearable
                                                                            ></v-autocomplete>
                                                                        </v-list-item>
                                                                        <v-list-item>
                                                                            <v-autocomplete
                                                                                v-model="nurse"
                                                                                :items="nurses"
                                                                                item-text="name"
                                                                                item-value="id"
                                                                                label="Nurse"
                                                                                background-color="white"
                                                                                @change="refresh"
                                                                                return-object
                                                                                dense
                                                                                outlined
                                                                                clearable
                                                                            ></v-autocomplete>
                                                                        </v-list-item>
                                                                        <v-list-item>
                                                                            <v-autocomplete
                                                                                v-model="category"
                                                                                :items="categories"
                                                                                item-text="name"
                                                                                item-value="id"
                                                                                label="Category"
                                                                                background-color="white"
                                                                                @change="refresh"
                                                                                return-object
                                                                                dense
                                                                                outlined
                                                                                clearable
                                                                            ></v-autocomplete>
                                                                        </v-list-item>
                                                                        <v-list-item>
                                                                            <v-autocomplete
                                                                                v-model="nurse_type"
                                                                                :items="nurse_types"
                                                                                label="Nurse Type"
                                                                                background-color="white"
                                                                                @change="refresh"
                                                                                dense
                                                                                outlined
                                                                                clearable
                                                                            ></v-autocomplete>
                                                                        </v-list-item>
                                                                    </v-list>
                                                                    
                                                                    <v-card-actions>
                                                                        <v-spacer></v-spacer>
                                                            
                                                                        <v-btn
                                                                                text
                                                                                @click="category = null; nurse = null; provider = null; refresh();"
                                                                        >
                                                                            Clear
                                                                        </v-btn>
                                                                    </v-card-actions>
                                                                </v-card>
                                                            </v-menu>
                                                            <v-menu
                                                                    bottom
                                                                    :offset-y="y"
                                                            >
                                                                <template v-slot:activator="{ on, attrs }">
                                                                    <v-btn
                                                                            class="hidden-sm-and-down"
                                                                            outlined
                                                                            color="grey darken-2"
                                                                            v-bind="attrs"
                                                                            v-on="on"
                                                                    >
                                                                        <span>{{ typeToLabel[type] }}</span>
                                                                        <v-icon right>
                                                                            mdi-menu-down
                                                                        </v-icon>
                                                                    </v-btn>
                                                                </template>
                                                                <v-list>
                                                                    <v-list-item v-on:click="type = 'day'">
                                                                        <v-list-item-title>Day</v-list-item-title>
                                                                    </v-list-item>
                                                                    <v-list-item v-on:click="type = 'week'">
                                                                        <v-list-item-title>Week</v-list-item-title>
                                                                    </v-list-item>
                                                                    <v-list-item v-on:click="type = 'month'">
                                                                        <v-list-item-title>Month</v-list-item-title>
                                                                    </v-list-item>
                                                                </v-list>
                                                            </v-menu>
                                                        </v-col>
                                                        <v-col class="col-12 hidden-md-and-up" style="display: flex;">
                                                            <span v-if="$refs.calendar">
                                                                {{ $refs.calendar.title }}
                                                            </span>
                                                            <v-spacer></v-spacer>
                                                            <v-btn
                                                                    fab
                                                                    text
                                                                    small
                                                                    class="calendar-prev"
                                                                    color="grey darken-2"
                                                                    v-on:click="prev"
                                                            >
                                                                <v-icon small>
                                                                    mdi-chevron-left
                                                                </v-icon>
                                                            </v-btn>
                                                            <v-btn
                                                                    fab
                                                                    text
                                                                    small
                                                                    class="calendar-next"
                                                                    color="grey darken-2"
                                                                    v-on:click="next"
                                                            >
                                                                <v-icon small>
                                                                    mdi-chevron-right
                                                                </v-icon>
                                                            </v-btn>
                                                        </v-col>
                                                    </v-row>
                                                </v-card-title>
                                            </v-sheet>
                                            <v-sheet height="600" v-show="false">
                                                <v-calendar
                                                        ref="calendar"
                                                        v-model="focus"
                                                        color="primary"
                                                        :events="shownEvents"
                                                        :event-color="getEventColor"
                                                        :weekdays="weekday"
                                                        event-overlap-threshold="0"
                                                        :type="type"
                                                        interval-height="20"
                                                        v-on:click:event="showEvent"
                                                        v-on:click:more="viewEventsForDay"
                                                        v-on:click:date="viewDay"
                                                        v-on:change="updateRange"
                                                ></v-calendar>
                                                <v-menu
                                                        v-model="selectedOpen"
                                                        :close-on-content-click="false"
                                                        :activator="selectedElement"
                                                        min-width="350px"
                                                        max-width="500px"
                                                        offset-y
                                                >
                                                    <v-card
                                                            color="grey lighten-4"
                                                            flat
                                                            class="mt-1"
                                                    >
                                                        <v-toolbar
                                                                :color="selectedEvent.color"
                                                                dark
                                                        >
                                                            <v-btn icon :href="selectedEvent.route">
                                                                <v-icon>mdi-pencil</v-icon>
                                                            </v-btn>
                                                            <v-toolbar-title class="white--text" v-html="selectedEvent.name"></v-toolbar-title>
                                                            <v-spacer></v-spacer>
                                                            <v-menu offset-y>
                                                                <template v-slot:activator="{ on, attrs }">
                                                                    <v-btn icon v-bind="attrs" v-on="on">
                                                                        <v-icon>mdi-dots-vertical</v-icon>
                                                                    </v-btn>
                                                                </template>
                                                                <v-list>
                                                                    <v-list-item :href="selectedEvent.route">
                                                                        Edit
                                                                    </v-list-item>
                                                                    <v-list-item :href="selectedEvent.copy_route">
                                                                        Copy
                                                                    </v-list-item>
                                                                    <v-dialog 
                                                                        max-width="300"
                                                                        v-if="canDeleteIndividual(selectedEvent)">
                                                                        <template v-slot:activator="{ on, attrs }">
                                                                            <v-list-item
                                                                                v-on="on"
                                                                                v-bind="attrs"
                                                                                :disabled="!canDeleteIndividual(selectedEvent)">
                                                                                Delete
                                                                            </v-list-item>
                                                                        </template>
                                                                        <template v-slot:default="dialog">
                                                                            <v-card>
                                                                                <v-toolbar color="primary" class="text-h4 white--text">
                                                                                    Are you sure?
                                                                                </v-toolbar>
                                                                                <v-card-text class="pt-5">
                                                                                    Do you wish to <strong class="red--text">DELETE</strong> this shift? This action cannot be undone.</v-card-text>
                                                                                </v-card-text>
                                                                                <v-card-actions class="justify-end">
                                                                                    <v-btn
                                                                                        text
                                                                                        color="grey dark-3"
                                                                                        v-on:click="dialog.value = false">Cancel</v-btn>
                                                                                    <v-btn
                                                                                        color="red"
                                                                                        v-on:click="deleteShift"
                                                                                        prepend-icon="mdi-window-close"
                                                                                        class="white--text"
                                                                                        :disabled="!canDeleteIndividual(selectedEvent)"
                                                                                    >Yes, Delete</v-btn>
                                                                                </v-card-actions>
                                                                            </v-card>
                                                                        </template>
                                                                    </v-dialog>
                                                                </v-list>
                                                            </v-menu>
                                                        </v-toolbar>
                                                        <v-card-text>
                                                            <h2 :class="selectedEvent.text_color">{{selectedEvent.status}}</h2>
                                                            <h3><strong>{{selectedEvent.start_time}} - {{selectedEvent.end_time}}</strong></h3>
                                                            <h4><strong>{{selectedEvent.provider_name}}</strong></h4>
                                                            <h4><strong>{{selectedEvent.shift_type}}</strong></h4>
                                                            <h4 v-if="selectedEvent.nurse_name">
                                                                <span><strong>{{selectedEvent.nurse_name}}</strong></span>
                                                                <a v-if="selectedEvent.nurse_route" :href="selectedEvent.nurse_route"><v-icon>mdi-export</v-icon></a>
                                                            </h4>
                                                            <h5 class="gray--text"><strong>{{selectedEvent.nurse_type}}</strong></h5>
                                                            <span class="block">Bonus: {{selectedEvent.bonus_display}}</span>
                                                            <span class="block">Covid: {{selectedEvent.covid_display}}</span>
                                                            <span class="block">Incentive: {{selectedEvent.incentive_display}}</span>
                                                        </v-card-text>
                                                        <v-card-actions>
                                                            <v-btn
                                                                    text
                                                                    color="dark"
                                                                    v-on:click="selectedOpen = false"
                                                            >Cancel
                                                            </v-btn>
                                                            <v-spacer></v-spacer>
                                                            <template v-if="selectedEvent.status == 'Approved'">
                                                                <v-dialog max-width="300">
                                                                    <template v-slot:activator="{ on, attrs }">
                                                                        <v-btn
                                                                            color="info"
                                                                            v-bind="attrs"
                                                                            v-on="on"
                                                                            >Nurse Call-In 
                                                                        </v-btn>
                                                                    </template>
                                                                    <template v-slot:default="dialog">
                                                                        <v-card>
                                                                            <v-toolbar
                                                                                color="info"
                                                                                class="text-h4 white--text"
                                                                            >CALL-IN</v-toolbar>
                                                                            <v-card-text
                                                                                class="pt-5"
                                                                            >Do you wish to <span class="red--text">CALL-IN</span> {{selectedEvent.nurse_name}} for the Approved shift?</v-card-text>
                                                                            <v-card-actions>
                                                                                <v-spacer></v-spacer>
                                                                                <v-btn
                                                                                    color="light"
                                                                                    v-on:click="dialog.value = false; dayModalOpen = false;"
                                                                                >Cancel
                                                                                </v-btn>
                                                                                <v-btn
                                                                                    color="info"
                                                                                    v-on:click="callInShift(selectedEvent); dayModalOpen = false;"
                                                                                    class="white--text"
                                                                                >Yes, CALL-IN</v-btn>
                                                                            </v-card-actions>
                                                                        </v-card>
                                                                    </template>
                                                                </v-dialog>
                                                            </template>
                                                            <template v-if="selectedEvent.status == 'Pending'">
                                                                <v-dialog max-width="300">
                                                                    <template v-slot:activator="{ on, attrs }">
                                                                        <v-btn
                                                                                text
                                                                                color="red"
                                                                                v-bind="attrs"
                                                                                v-on="on"
                                                                        >Deny</v-btn>
                                                                    </template>
                                                                    <template v-slot:default="dialog">
                                                                        <v-card>
                                                                            <v-toolbar
                                                                                color="red"
                                                                                class="text-h4 white--text"
                                                                            >Deny Shift</v-toolbar>
                                                                            <v-card-text
                                                                                class="pt-5"
                                                                            >Do you wish to <span class="red--text">DENY</span> {{selectedEvent.nurse_name}} for the requested shift?</v-card-text>
                                                                            <v-card-actions>
                                                                                <v-spacer></v-spacer>
                                                                                <v-btn
                                                                                    color="light"
                                                                                    v-on:click="dialog.value = false; dayModalOpen = false;"
                                                                                >Cancel
                                                                                </v-btn>
                                                                                <v-btn
                                                                                    color="red"
                                                                                    v-on:click="denyShift(selectedEvent); dayModalOpen = false;"
                                                                                    class="white--text"
                                                                                >Yes, Deny</v-btn>
                                                                            </v-card-actions>
                                                                        </v-card>
                                                                    </template>
                                                                </v-dialog>
                                                            </template>
                                                            <template v-if="selectedEvent.status == 'Pending'">
                                                                <v-dialog max-width="300">
                                                                    <template v-slot:activator="{ on, attrs }">
                                                                        <v-btn
                                                                                text
                                                                                color="success"
                                                                                v-bind="attrs"
                                                                                v-on="on"
                                                                        >Approve</v-btn>
                                                                    </template>
                                                                    <template v-slot:default="dialog">
                                                                        <v-card>
                                                                            <v-toolbar
                                                                                color="success"
                                                                                class="text-h4 white--text"
                                                                            >Approve Shift</v-toolbar>
                                                                            <v-card-text
                                                                                class="pt-5"
                                                                            >Do you wish to <strong class="success--text">APPROVE</strong> {{selectedEvent.nurse_name}}  for the requested shift?</v-card-text>
                                                                            <v-card-actions>
                                                                                <v-spacer></v-spacer>
                                                                                <v-btn
                                                                                    color="light"
                                                                                    v-on:click="dialog.value = false; dayModalOpen = false;"
                                                                                >Cancel
                                                                                </v-btn>
                                                                                <v-btn
                                                                                    color="success"
                                                                                    v-on:click="approveShift(selectedEvent); dayModalOpen = false;"
                                                                                    class="white--text"
                                                                                >Yes, Approve</v-btn>
                                                                            </v-card-actions>
                                                                        </v-card>
                                                                    </template>
                                                                </v-dialog>
                                                            </template>
                                                        </v-card-actions>
                                                    </v-card>
                                                </v-menu>
                                                <v-menu 
                                                        v-model="dayModalOpen"
                                                        :close-on-content-click="false"
                                                        :activator="modalTarget"
                                                        max-width="350px"
                                                        max-height="500px"> 
                                                    <v-card 
                                                            color="grey lighten-4"
                                                            flat
                                                            class="mt-1"> 
                                                        <v-toolbar
                                                                style="z-index: 3"
                                                                color="blue"
                                                                dark> 
                                                            <v-toolbar-title class="white--text" v-html="selectedDayDisplay"></v-toolbar-title>
                                                            <v-spacer></v-spacer>
                                                            <v-menu 
                                                                offset-y
                                                                style="z-index: 23;"
                                                                :close-on-content-click="false">
                                                                <template v-slot:activator="{ on, attrs }">
                                                                    <v-btn text color="white" v-bind="attrs" v-on="on">
                                                                        Filters
                                                                    </v-btn>
                                                                </template>
                                                                <v-list> 
                                                                    <v-list-item 
                                                                        v-for="status in statuses"
                                                                        dense
                                                                        v-on:click="status.shown = !status.shown"> 
                                                                        <v-icon style="font-size: 20px;" class="mr-2" :color="status.shown ? colors[status.name] : 'grey lighten-1'">mdi-checkbox-blank-circle</v-icon> 
                                                                        <span :class="status.shown ? colors[status.name] + '--text' : 'grey--text text--lighten-1'"> {{status.display_name}}</span>
                                                                    </v-list-item>
                                                                </v-list> 
                                                            </v-menu>
                                                        </v-toolbar>
                                                        <v-card-text style="max-height: 400px; overflow-y: scroll;"> 
                                                            <shift-calendar-event 
                                                                v-for="day_event in dayEvents"
                                                                :getEventColor="getEventColor"
                                                                :day_event="day_event"
                                                                :showDayEvent="showDayEvent"
                                                                :toggleEventSelected="toggleEventSelected"
                                                                :selecting="selecting"
                                                                > 
                                                            </shift-calendar-event>
                                                        </v-card-text>
                                                    </v-card>
                                                </v-menu>
                                            </v-sheet>
                                            <v-sheet height="600" v-if="is_loaded && type == 'month'"> 
                                                <shift-calendar-monthly-view
                                                    :events="month_events"
                                                    :_week="week"
                                                    :calendar="$refs.calendar"
                                                    :statuses="statuses"
                                                    :nurses="nurses"
                                                    :categories="categories"
                                                    :nurse_types="nurse_types"
                                                    :getEventColor="getEventColor"
                                                    :showDayEvent="showDayEvent"
                                                    :selecting="selecting"
                                                    :colors="colors"
                                                    :viewEventsForDay="viewEventsForDay"
                                                    :viewMonthDay="viewMonthDay"
                                                    :loading="loading"
                                                ></shift-calendar-monthly-view>
                                            </v-sheet>
                                            <v-sheet height="600" v-if="type == 'week'"> 
                                                <shift-calendar-weekly-view
                                                    :events="events"
                                                    :_week="week"
                                                    :calendar="$refs.calendar"
                                                    :statuses="statuses"
                                                    :nurses="nurses"
                                                    :categories="categories"
                                                    :nurse_types="nurse_types"
                                                    :getEventColor="getEventColor"
                                                    :showDayEvent="showDayEvent"
                                                    :selecting="selecting"
                                                    :viewMonthDay="viewMonthDay"
                                                    :loading="loading"
                                                ></shift-calendar-weekly-view>
                                            </v-sheet>
                                            <v-sheet height="600" v-if="type == 'day'"> 
                                                <shift-calendar-daily-view
                                                    :events="events"
                                                    :_week="week"
                                                    :calendar="$refs.calendar"
                                                    :statuses="statuses"
                                                    :nurses="nurses"
                                                    :categories="categories"
                                                    :nurse_types="nurse_types"
                                                    :getEventColor="getEventColor"
                                                    :showDayEvent="showDayEvent"
                                                    :selectedOpen="selectedOpen"
                                                    :selecting="selecting"
                                                    :loading="loading"
                                                    :isBackend="true"
                                                ></shift-calendar-daily-view>
                                            </v-sheet>
                                            <v-sheet class="mt-3">
                                                <v-row>
                                                    <v-col cols="12" class="d-md-flex justify-center">
                                                        <v-btn
                                                                v-for="status in statuses"
                                                                :color="status.shown ? status.color : 'grey lighten-1'"
                                                                text
                                                                v-on:click="status.shown = !status.shown"
                                                                class="m-2 calendar-status-filter"
                                                        >{{status.display_name}}</v-btn>
                                                    </v-col>
                                                </v-row>
                                            </v-sheet>
                                        </v-col>
                                    </v-row>
                                </div>
                            </div>
                        </div>
                    </div>
                </v-app>
            </div>`,
        props: [
            'provider_id',
            'nurse_id',
            'category_id',
            'can_delete_past_shifts'
        ],
        data: () => ({
            is_refreshing: false,
            focus: '',
            type: 'month',
            typeToLabel: {
                month: 'Month',
                week: 'Week',
                day: 'Day'
            },
            selectedEvent: {},
            selectedElement: null,
            selectedOpen: false,
            events: [],
            oldcolors: ['blue', 'indigo', 'deep-purple', 'cyan', 'green', 'orange', 'grey darken-1'],
            colors: {
                Open: 'blue',
                Pending: 'warning',
                Assigned: 'pink',
                Approved: 'success',
                Completed: 'gray_dark'
            },
            statuses: {
                'Open': {
                    name: 'Open',
                    display_name: 'Open',
                    color: 'blue',
                    shown: true
                },
                'Pending': {
                    name: 'Pending',
                    display_name: 'Pending',
                    color: 'warning',
                    shown: true
                },
                'Assigned': {
                    name: 'Assigned',
                    display_name: 'Assigned',
                    color: 'pink',
                    shown: true
                },
                'Approved': {
                    name: 'Approved',
                    display_name: 'Approved',
                    color: 'success',
                    shown: true
                },
                'Completed': {
                    name: 'Completed',
                    display_name: 'Completed',
                    color: 'gray_dark',
                    shown: true
                }
            },
            y: true,
            views: ['Month', 'Week', 'Day'],
            disabled_color: 'grey-lighten-1',
            dialog: false,
            providers: [],
            nurses: [],
            categories: [],
            filters_menu: false,
            updateKey: 1,
            dayModalKey: 0,
            selectedDayDisplay: '',
            day_events: [],
            selectedDay: '',
            dayModalOpen: false,
            modalTarget: null,
            nurse_types: ['CNA', 'CMT', 'LPN/RN', 'CMT/LPN/RN'],
            nurse_type: null,
            nurse: null,
            provider: null,
            category: null,
            loading: false,
            week: [],
            selecting: false,
            is_loaded: false,
            month_days: [],
            month_events: [],
            weekday: [1, 2, 3, 4, 5, 6, 0],
        }),
        computed: {
            shownEvents: function () {
                return this.events.filter(function (event) {
                    return this.statuses[event.status].shown && event.count > 0;
                }.bind(this));
            },
            dayEvents: function () {
                var _dayEvents = this.events.filter(function (event) {
                    var start = this.formatDate(event.start);
                    if (
                        start === this.selectedDate &&
                        this.statuses[event.status].shown &&
                        (( event.parent_id > 0 && this.eventByGroupingId(event.parent_id).is_open ) || event.count > 0))
                    {
                        return true;
                    }
                    return false;
                }.bind(this));
                return _dayEvents.sort((a, b) => {
                    if (a.status != 'Open' && b.status == 'Open') return 1;
                    if (a.status == 'Open' && b.status != 'Open') return -1;
                    if (a.status != 'Pending' && b.status == 'Pending') return 1;
                    if (a.status == 'Pending' && b.status != 'Pending') return -1;
                    if (a.status != 'Assigned' && b.status == 'Assigned') return 1;
                    if (a.status == 'Assigned' && b.status != 'Assigned') return -1;
                    if (a.status != 'Approved' && b.status == 'Approved') return 1;
                    if (a.status == 'Approved' && b.status != 'Approved') return -1;
                    if (a.status != 'Completed' && b.status == 'Completed') return 1;
                    if (a.status == 'Completed' && b.status != 'Completed') return -1;

                    if (a.start < b.start) return -1;
                    if (a.start > b.start) return 1;
                    
                    return 0;
                });
            },
            selectedEvents: function () {
                return this.events.filter((e) => {
                    return e.is_selected === true;
                });
            },
            canMassDeleteShifts: function () {
                if (this.can_delete_past_shifts === 1) {
                    return true;
                }

                let now = luxon.DateTime.now();
                if (this.selectedEvents.length) { 
                    return !this.selectedEvents.some(e => luxon.DateTime.fromISO(e.start.toISOString()) < now );
                } else {
                    return true;
                }
            }
        },
        created() {
            this.loadFilters();
            if (this.provider_id) {
                this.provider = {
                    id: this.provider_id
                };
            }
            if (this.nurse_id) {
                this.nurse = {
                    id: this.nurse_id
                };
            }
            if (this.category_id) {
                this.category = {
                    id: this.category_id
                };
            }
        },
        mounted() {
            setTimeout(() => {
                this.is_loaded = true;
            }, 1000);
        },
        methods: {
            massDelete() {
                var selected_events = this.selectedEvents;
                // Don't delete parents unless children are being deleted too.
                var parents = selected_events.filter((e) => {
                    return e.count > 1
                })
                for (let i = 0; i < parents.length; i++) {
                    var canDeleteParent = true;
                    var children = this.getChildren(parents[i]);
                    for (let j = 0; j < children.length; j++) {
                        if (!children[j].is_selected) {
                            canDeleteParent = false;
                        }
                    }
                    if (!canDeleteParent) {
                        selected_events.splice(selected_events.indexOf(parents[i]), 1);
                    }
                }
                var events = [];
                for (let i = 0; i < selected_events.length; i++) {
                    events.push({
                        id: selected_events[i].id,
                        is_recurrence: selected_events[i].is_recurrence,
                        recurrence_id: selected_events[i].recurrence_id,
                        unique_id: selected_events[i].unique_id,
                    });
                }
                var data = {
                    events: events
                }

                modRequest.request('sa.shift.mass_delete_shifts', {}, data, function (response) {
                    if (response.success) {
                        for (let i = 0; i < selected_events.length; i++) {
                            if (selected_events[i].parent_unique_id) {
                                this.eventByUniqueId(selected_events[i].parent_unique_id).count -= 1;
                            } else if (selected_events[i].parent_id) {
                                this.eventById(selected_events[i].parent_id).count -= 1;
                            }
                            this.events.splice(this.events.indexOf(selected_events[i]), 1);

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
            eventById(id) {
                if (id > 0) {
                    return this.events.filter((event) => { return event.id == id });
                }
                return null;
            },
            eventByUniqueId(uniqueId) {
                if (uniqueId.length > 0) {
                    return this.events.filter((event) => { return event.unique_id == uniqueId });
                }
                return null;
            },
            eventByGroupingId(id) {
                if(id > 0) {
                    return this.events.find((event) => { return event.dropdown_grouping_id == id});
                }
                return null;
            },
            getChildren(event) {
                var children = this.events.filter((e) => { return e.parent_id == event.dropdown_grouping_id && e.status == 'Open' });

                return children;
            },
            toggleSelecting() {
                this.selecting = !this.selecting;
                if (!this.selecting) {
                    for (let i = 0; i < this.events.length; i++) {
                        this.events[i].is_selected = false;
                    }
                }
            },
            formatDate(date) {
                var year = date.getFullYear().toString();
                var month = (date.getMonth() + 1).toString();
                var day = date.getDate().toString();

                if (month.length < 2)
                    month = '0' + month;
                if (day.length < 2)
                    day = '0' + day;
                return year + '-' + month + '-' + day;
            },
            viewDay({date}) {
                this.focus = date
                this.type = 'day'
            },
            viewMonthDay(date) {
                this.focus = date
                this.type = 'day'
            },
            getEventColor(event) {
                return event.color
            },
            setToday() {
                this.focus = ''
            },
            prev() {
                this.$refs.calendar.prev();
            },
            next() {
                this.$refs.calendar.next();
            },
            updateKeys() {
                this.dayModalKey += '1';
            },
            refresh() {
                this.is_refreshing = true;
                var data = {
                    start: this.$refs.calendar.lastStart,
                    end: this.$refs.calendar.lastEnd,
                    provider_id: this.provider ? this.provider.id : 0,
                    nurse_id: this.nurse ? this.nurse.id : 0,
                    category_id: this.category ? this.category.id : 0,
                    nurse_type: this.nurse_type,
                    calendar_type: this.type
                };

                this.loadCalendar(data);
            },
            viewEventsForDay(date, nativeEvent) {
                const open = () => {
                    this.selectedDay = date;
                    var selectedDate = new Date(date).toDateString().split(' ');
                    this.selectedDayDisplay = selectedDate[0].toString() + ', ' + selectedDate[1].toString() + ' ' + (parseInt(selectedDate[2]) + 1).toString();
                    this.modalTarget = nativeEvent.target;
                    requestAnimationFrame(() => requestAnimationFrame(() => this.dayModalOpen = true))
                }

                if (this.dayModalOpen) {
                    this.dayModalOpen = false;
                    requestAnimationFrame(() => requestAnimationFrame(() => open()))
                } else {
                    open()
                }

                nativeEvent.stopPropagation();
            },
            showDayEvent(nativeEvent, event) {
                if (((event.parent_id > 0 && !event.dropdown_grouping_id) || event.count == 1)) {
                    const open = () => {
                        this.selectedEvent = event
                        this.selectedElement = nativeEvent.target
                        requestAnimationFrame(() => requestAnimationFrame(() => this.selectedOpen = true))
                    }

                    if (this.selectedOpen) {
                        this.selectedOpen = false
                        requestAnimationFrame(() => requestAnimationFrame(() => open()))
                    } else {
                        open()
                    }
                } else {
                    event.is_open = !event.is_open;
                    return;
                }

                nativeEvent.stopPropagation()

            },
            showEvent({nativeEvent, event}) {
                const open = () => {
                    this.selectedEvent = event
                    this.selectedElement = nativeEvent.target;
                    requestAnimationFrame(() => requestAnimationFrame(() => this.selectedOpen = true))
                }

                if (this.selectedOpen) {
                    this.selectedOpen = false
                    requestAnimationFrame(() => requestAnimationFrame(() => open()))
                } else {
                    open()
                }

                nativeEvent.stopPropagation()
            },
            deleteShift() {
                var data = {
                    id: this.selectedEvent.id,
                    unique_id: this.selectedEvent.unique_id,
                    recurrence_id: this.selectedEvent.recurrence_id
                };

                var requestString = 'sa.shift.delete_shift';

                modRequest.request(requestString, {}, data, function (response) {
                    if (response.success) {
                        if (this.selectedEvent.is_recurrence && this.selectedEvent.count < 1) {
                            this.eventByUniqueId(this.selectedEvent.parent_unique_id).count -= 1;
                        } else if (this.selectedEvent.count < 1) {
                            this.eventById(this.selectedEvent.parent_id).count -= 1;
                        }
                        this.events.splice(this.events.indexOf(this.selectedEvent), 1);
                        this.selectedOpen = false;
                    } else {
                        // console.log('Error');
                        // console.log(response);
                        toastr.error(response.error, 'Error. Please contact developers or support department.');
                        this.selectedOpen = false;
                    }
                }.bind(this), function (response) {
                    console.log('Failed');
                    console.log(response);
                });
            },
            updateRange({start, end}) {
                var data = {
                    start: start,
                    end: end,
                    provider_id: this.provider ? this.provider.id : 0,
                    nurse_id: this.nurse ? this.nurse.id : 0,
                    category_id: this.category ? this.category.id : 0,
                    nurse_type: this.nurse_type,
                    calendar_type: this.type,
                };

                if (!this.is_refreshing) {
                    this.loadCalendar(data);
                }
            },
            rnd(a, b) {
                return Math.floor((b - a + 1) * Math.random()) + a
            },
            loadCalendar(data) {
                var currentColor = 0;
                this.loading = true;
                this.events = [];
                this.week = this.$refs.calendar.renderProps.weekdays;
                this.week_data = this.$refs.calendar.week_data;

                // if(this.type == 'month') {
                //     var start = this.$refs.calendar.$children[0].days[0];
                //     var end = this.$refs.calendar.$children[0].days[this.$refs.calendar.$children[0].days.length - 1];
                //     data.start = start;
                //     data.end = end;
                // }
                modRequest.request('sa.shift.load_calendar', {}, data, function (response) {
                    if (response.success) {
                        this.events = [];
                        this.month_events = [];
                        if (response.calendar_type === 'month') {
                            this.month_events = response.shifts;
                        } else {
                            if (response.shifts !== null) {
                                for (var i = 0; i < response.shifts.length; i++) {
                                    var event = response.shifts[i];
                                    this.events.push({
                                        id: event.id,
                                        route: event.route,
                                        recurrence_id: event.recurrence_id,
                                        shift_route: event.shift_route,
                                        copy_route: event.copy_route,
                                        unique_id: event.unique_id,
                                        start_time: event.start_time_formatted,
                                        end_time: event.end_time_formatted,
                                        status: event.status,
                                        name: event.name,
                                        start: new Date(event.start_date + 'T' + event.start_time),
                                        end: new Date(event.end_date + 'T' + event.end_time),
                                        color: this.colors[event.status],
                                        timed: true,
                                        singleline: false,
                                        nurse_name: event.nurse_name,
                                        nurse_route: event.nurse_route,
                                        nurse_type: event.nurse_type,
                                        text_color: this.colors[event.status] + "--text",
                                        shift_type: event.shift_type,
                                        nurse_type_string: event.nurse_type_string,
                                        provider_id: event.provider_id,
                                        provider_name: event.provider_name,
                                        parent_id: event.parent_id,
                                        parent_unique_id: event.parent_unique_id,
                                        is_recurrence: event.is_recurrence,
                                        count: 1,
                                        is_open: false,
                                        is_selected: false,
                                        covid_display: event.covid_display,
                                        incentive_display: event.incentive_display,
                                        bonus_display: event.bonus_display
                                    });
                                    currentColor++;
                                }
                            }
                            this.setUpShiftCounts();
                        }
                        this.loading = false;
                    } else {
                        console.log('Error');
                        console.log(response);
                    }
                    this.is_refreshing = false;
                    this.loading = false;
                }.bind(this), function (response) {
                    console.log('Failed');
                    console.log(response);
                    this.is_refreshing = false;
                });

            },
            loadFilters() {
                modRequest.request('sa.shift.load_calendar_filters', {}, {}, function (response) {
                    if (response.success) {
                        this.providers = response.providers;
                        this.nurses = response.nurses;
                        this.categories = response.categories;
                    } else {
                        console.log('Error');
                        console.log(response);
                    }
                }.bind(this), function (response) {
                    console.log('Failed');
                    console.log(response);
                });
            },
            approveShift: function (item) {
                var data = {
                    id: item.id,
                    is_recurrence: item.recurrence_id > 0
                };

                modRequest.request('sa.shift.approve_shift_request', {}, data, function (response) {
                    if (response.success) {
                        this.selectedOpen = false;
                        this.refresh();
                        // window.location.reload();
                    } else {
                        console.log('Error');
                        console.log(response);
                    }
                }.bind(this), function (response) {
                    console.log('Failed');
                    console.log(response);
                });
            },
            denyShift: function (item) {
                var data = {
                    id: item.recurrence_id ? item.recurrence_id : item.id,
                    is_recurrence: item.recurrence_id > 0
                };

                modRequest.request('sa.shift.deny_shift_request', {}, data, function (response) {
                    if (response.success) {
                        this.selectedOpen = false;
                        this.refresh();
                        // window.location.reload();

                    } else {
                        console.log('Error');
                        console.log(response);
                    }
                }.bind(this), function (response) {
                    console.log('Failed');
                    console.log(response);
                });
            },
            callInShift: function (item) {
                //This is the function I need to work


                var data = {
                    id: item.recurrence_id ? item.recurrence_id : item.id,
                    is_recurrence: item.recurrence_id > 0
                };

                modRequest.request('sa.shift.call_in_shift', {}, data, function (response) {
                    if (response.success) {
                        this.selectedOpen = false;
                        toastr.success('Success!', 'Shift called-in successfully!');
                        this.refresh();

                    } else {
                        toastr.error(response.message, 'Error');
                        console.log('Error');
                        console.log(response);
                    }
                }.bind(this), function (response) {
                    toastr.error(response.message, 'Error');
                    console.log('Failed');
                    console.log(response);
                });
            },
            setUpShiftCounts() {
                for (let i = 0; i < this.events.length; i++) {
                    if ((this.events[i].parent_id > 0 || (this.is_recurrence && this.parent_unique_id.length > 0)) && this.events[i].status == 'Open') {
                        var shift = this.events[i];
                        let parent = this.events.filter((event) => { return event.id == shift.parent_id })[0];

                        // Creating this grouping_shift to be a sort of master container for all events in the count of events that are still 'Open'
                        // This is to resolve the issue where dropdowns sometimes use the parent event even when it is pending or approved

                        // Check for event marked as grouping_shift
                        let grouping_shift = this.events.filter((event) => { return (event.dropdown_grouping_id == shift.parent_id) })[0];
                        // If there is a parent shift but no grouping shift selected yet, select grouping shift
                        if(parent && !grouping_shift) {
                            if(parent.status == 'Open') {
                                parent.dropdown_grouping_id = shift.parent_id;
                                parent.count += 1;
                                shift.count = 0;
                            } else {
                                shift.dropdown_grouping_id = shift.parent_id
                            }
                        }

                        //If we do find the grouping shift simply add the count and set the current shift count to 0
                        if(grouping_shift != undefined) {
                            grouping_shift.count += 1;
                            shift.count = 0;
                        }
                    }
                }
            },
            canDeleteIndividual(selectedEvent) {
                if (this.can_delete_past_shifts === 1) {
                    return true;
                }

                // Sometimes triggered when shift obj is not present
                // So making sure property key exists is needed
                if (selectedEvent.hasOwnProperty("start")) {
                    let now = luxon.DateTime.now();
                    let eventStart = luxon.DateTime.fromISO(selectedEvent.start.toISOString());
                    
                    return eventStart > now;
                }

                return true;
            }
        },
    });
});
