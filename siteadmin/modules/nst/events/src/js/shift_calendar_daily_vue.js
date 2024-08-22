window.addEventListener('load', function() {

    Vue.component('shift-calendar-daily-view', {
        template:
            `
                <div v-if="weekday" class="daily-calendar-view v-calendar v-calendar-daily theme--light v-calendar-events"> 
                    <v-overlay :value="loading" absolute color="#eee"> 
                        <v-progress-circular
                                active
                                indeterminate
                                :size="100"
                                color="primary"></v-progress-circular>
                    </v-overlay>
                    <div class="v-calendar-daily__head"> 
                        <div :class="'daily-calendar-day-header v-calendar-daily_head-day ' + weekday.timePeriodClass"> 
                            <div class="v-calendar-daily_head-weekday">{{day_labels[weekday.weekday]}}</div>
                            <div class="v-calendar-daily_head-day-label"> 
                                <button type="button" :class="'v-btn v-btn--fab v-btn--has-bg v-btn--round theme--light v-size--default transparent ' + (weekday.timePeriodClass == 'v-present' ? 'primary' : '')"> 
                                    <span class="v-btn__content">{{weekday.day}}</span>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="weekly-calendar-body v-calendar-daily__body"> 
                        <div class="v-calendar-daily__pane" style="height: 480px;"> 
                            <div class="v-calendar-daily__day-container text-center" > 
                                <div style="padding: 2px 30%; overflow-y: auto; overflow-x: hidden; " :class="'v-calendar-daily__day ' + weekday.timePeriodClass"> 
                                    <shift-calendar-event 
                                        v-for="day_event in weekday.events"
                                        :getEventColor="getEventColor"
                                        :day_event="day_event"
                                        :showDayEvent="showDayEvent"
                                        :toggleEventSelected="toggleEventSelected"
                                        :selecting="selecting"
                                        :is_weekly="false"
                                        :isBackend="isBackend"
                                        > 
                                    </shift-calendar-event>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `,
        props: [
            'calendar',
            'events',
            '_week',
            'statuses',
            'nurses',
            'categories',
            'nurse_types',
            'getEventColor',
            'showDayEvent',
            'selectedOpen',
            'selecting',
            'loading',
            'isBackend',
        ],
        data: function() {
            return {
                week: [],
                weekdays: [],
                day_labels: {
                    0: 'Sun',
                    1: 'Mon',
                    2: 'Tue',
                    3: 'Wed',
                    4: 'Thu',
                    5: 'Fri',
                    6: 'Sat'
                },
                showMenu: false,
                menuX: null,
                menuY: null,
            };
        },
        computed: {
            shownEvents : function () {
                return this.events.filter(function(event)  {
                    return this.statuses[event.status].shown && event.count > 0;
                }.bind(this));
            },
            weekday : function() {
                var day = this.calendar.$children[0].days[0];
                day.events = this.dayEvents(day.date);
                var timePeriodClass = 'v-present';
                if(day.future === true) {
                    timePeriodClass = 'v-future';
                }
                if(day.past === true) {
                    timePeriodClass = 'v-past';
                }
                day.timePeriodClass = timePeriodClass;
                return day;
            },
        },
        created () {
            this.week = this._week;
        },
        mounted () {
        },
        methods: {
            toggleEventSelected(e, day_event) {
                e.stopPropagation();
                if(day_event.count > 1) {
                    var children = this.getChildren(day_event);
                    for (let i = 0; i < children.length; i++) {
                        if(children[i].status == day_event.status) {
                            children[i].is_selected = day_event.is_selected;
                        }
                    }
                    if(day_event.is_selected) {
                        day_event.is_open = true;
                    }
                }
            },
            getChildren(event) {
                var children = this.events.filter((e) => { return (e.parent_id == event.dropdown_grouping_id && e.status == 'Open'); });
                return children;
            },
            eventById(id) {
                if(id > 0) {
                    return this.events.find((event) => { return event.id == id });
                }
                return null;
            },
            eventByGroupingId(id) {
                if(id > 0) {
                    return this.events.find((event) => { return event.dropdown_grouping_id == id });
                }
                return null;
            },
            eventByUniqueId(uniqueId) {
                if(uniqueId.length > 0) {
                    return this.events.filter((event) => { return event.unique_id == uniqueId });
                }
                return null;
            },
            dayEvents : function(date) {
                var _dayEvents = this.events.filter(function(event) {
                    var start = this.formatDate(event.start);
                    if((this.statuses[event.status] && this.statuses[event.status].shown)
                        && ((event.parent_id > 0 && this.eventByGroupingId(event.parent_id) != null && this.eventByGroupingId(event.parent_id).is_open ) || event.count > 0)
                    )
                    {
                        return start == date;
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

                    if (a.parent_id == b.id && !a.is_recurrence && !b.is_recurrence) return 1;
                    if (a.id == b.parent_id && !a.is_recurrence && !b.is_recurrence) return -1;
                    if (a.parent_unique_id == b.unique_id && b.is_recurrence) return 1;
                    if (a.unique_id == b.parent_unique_id && a.is_recurrence) return -1;
                    
                    if (a.start < b.start) return -1;
                    if (a.start > b.start) return 1;
                    return 0;
                });
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
            }
        }
    });
});
