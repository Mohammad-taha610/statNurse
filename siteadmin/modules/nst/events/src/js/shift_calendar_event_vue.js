window.addEventListener('load', function() {
    Vue.component('shift-calendar-event', {
        template:`
            <v-btn 
                    x-small
                    depressed
                    :color="getEventColor(day_event)"
                    class="white--text w-100 p-1 shift-calendar-weekday-btn"
                    :style="day_event.parent_id > 0 && day_event.count < 1 ? 'width: 90% !important; left: 5% !important;' : ''"
                    v-on:click="showDayEvent($event, day_event)">
                <span style="flex-shrink: 1; text-align: left;" class="day-event-checkbox-container"> 
                    <v-checkbox class="day-event-checkbox" color="white" v-if="selecting" dense v-model="day_event.is_selected" v-on:click="toggleEventSelected($event, day_event)"></v-checkbox>
                </span>
                <span :class="'shift-calendar-event-time ' + (selecting ? 'selecting-events' : '')" >
                    <strong>{{day_event.start_time}}<span v-if="!is_weekly"> - {{day_event.end_time}}</span></strong>
                </span>
                <span :class="'text-left pl-2 shift-calendar-event-name ' + (selecting ? 'selecting-events' : '')" >
                    {{day_event.nurse_name ? day_event.nurse_name : ''}}
                </span>
                <span :class="'text-right pl-2 shift-calendar-event-provider-name ' + (selecting ? 'selecting-events' : '')" v-if="!is_weekly && isBackend">
                    {{day_event.provider_name ? day_event.provider_name : ''}}
                </span>
                <v-icon v-if="day_event.bonus_display != 'None'">mdi-cash</v-icon>
                <v-icon v-if="day_event.covid_display === 'Yes'">mdi-needle</v-icon>
                <span :class="'shift-calendar-event-type ' + (selecting ? 'selecting-events' : '')">
                    {{day_event.nurse_type ? '[' + day_event.nurse_type + ']' : ''}} 
                    {{day_event.count > 1 ? 'x' + day_event.count : ''}} 
                    <v-icon v-if="day_event.count > 1" :style="(day_event.is_open ? 'transform: scale(1, -1)' : '')">mdi-menu-down</v-icon>
                </span>
            </v-btn>
        `,
        props: [
            'getEventColor',
            'day_event',
            'showDayEvent',
            'toggleEventSelected',
            'selecting',
            'is_weekly',
            'isBackend',
        ],
        data: function() {
            return {
            };
        },
        created () {
        },
        mounted () {
        },
        methods: {
        }
    });
});