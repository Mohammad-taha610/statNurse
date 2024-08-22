window.addEventListener('load', function() {

    Vue.component('shift-calendar-monthly-view', {
        template:`
                <div class="monthly-calendar-view v-calendar v-calndar-monthly v-calendar-weekly theme--light v-calendar-events"> 
                    <v-overlay :value="loading" absolute color="#eee"> 
                        <v-progress-circular
                                active
                                indeterminate
                                :size="100"
                                color="primary"></v-progress-circular>
                    </v-overlay>
                    <div class="v-calendar-weekly__head"> 
                        <div v-for="weekday in initialWeekdays" :class="'v-calendar-weekly__head-weekday ' + weekday.outsideClass + ' ' + weekday.timePeriodClass + ' ' + (weekday.is_active ? 'primary--text' : '')"> 
                            {{weekday.abbreviation}}
                        </div> 
                    </div>
                    <div v-if="monthDays" v-for="i in weeks" class="v-calendar-weekly__week"> 
                        <div v-for="j in 7" :class="'v-calendar-weekly__day ' + monthDays[((i-1)*7)+(j-1)].timePeriodClass + ' ' + monthDays[((i-1)*7)+(j-1)].outsideClass">
                            <div class="v-calendar-weekly__day-label"> 
                                <button type="button" v-on:click="viewMonthDay(monthDays[((i-1)*7)+(j-1)].date)" :class="'v-btn v-btn--fab v-btn--has-bg v-btn--round theme--light v-size--small ' + (monthDays[((i-1)*7)+(j-1)].timePeriodClass == 'v-present' ? 'primary' : 'transparent')"> 
                                    <span class="v-btn__content">{{monthDays[((i-1)*7)+(j-1)].day}}</span>
                                </button>
                            </div> 
                            <div v-if="monthDays[((i-1)*7)+(j-1)].counts['Total'] > 0" :data-date="monthDays[((i-1)*7)+(j-1)].date" class="v-event-more monthly-view-shifts-btn-container" >
                                <div class="monthly-view-shifts-btn" v-on:click="viewMonthDay(monthDays[((i-1)*7)+(j-1)].date)">
                                    <div class="month-shift-left"> 
                                        <div class="month-day-shift-count-total"> 
                                            <div class="month-day-shift-count-total-circle">
                                                {{monthDays[((i-1)*7)+(j-1)].counts['Total']}}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="month-shift-right">
                                        <ul class="month-shift-count-list"> 
                                            <li class="month-shift-count-item" v-if="monthDays[((i-1)*7)+(j-1)].counts['Open'] > 0 && statuses['Open'].shown"> 
                                                <v-icon :color="colors['Open']">mdi-checkbox-blank-circle</v-icon>
                                                {{monthDays[((i-1)*7)+(j-1)].counts['Open']}}
                                            </li>
                                            <li class="month-shift-count-item" v-if="monthDays[((i-1)*7)+(j-1)].counts['Pending'] > 0 && statuses['Pending'].shown"> 
                                                <v-icon :color="colors['Pending']">mdi-checkbox-blank-circle</v-icon>
                                                {{monthDays[((i-1)*7)+(j-1)].counts['Pending']}}
                                            </li>
                                            <li class="month-shift-count-item" v-if="monthDays[((i-1)*7)+(j-1)].counts['Assigned'] > 0 && statuses['Assigned'].shown"> 
                                                <v-icon :color="colors['Assigned']">mdi-checkbox-blank-circle</v-icon>
                                                {{monthDays[((i-1)*7)+(j-1)].counts['Assigned']}}
                                            </li>
                                            <li class="month-shift-count-item" v-if="monthDays[((i-1)*7)+(j-1)].counts['Approved'] > 0 && statuses['Approved'].shown"> 
                                                <v-icon :color="colors['Approved']">mdi-checkbox-blank-circle</v-icon>
                                                {{monthDays[((i-1)*7)+(j-1)].counts['Approved']}}
                                            </li>
                                            <li class="month-shift-count-item" v-if="monthDays[((i-1)*7)+(j-1)].counts['Completed'] > 0 && statuses['Completed'].shown"> 
                                                <v-icon :color="colors['Completed']">mdi-checkbox-blank-circle</v-icon>
                                                {{monthDays[((i-1)*7)+(j-1)].counts['Completed']}}
                                            </li>
                                        </ul> 
                                    </div>
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
            'selecting',
            'colors',
            'viewEventsForDay',
            'viewMonthDay',
            'loading'
        ],
        data: function() {
            return {
                week: [],
                weekdays: [],
                monthdays: [],
                day_labels: {
                    0: 'Sun',
                    1: 'Mon',
                    2: 'Tue',
                    3: 'Wed',
                    4: 'Thu',
                    5: 'Fri',
                    6: 'Sat'
                },
                weeks: null
            };
        },
        computed: {
            shownEvents : function () {
                return this.events.filter(function(event)  {
                    return this.statuses[event.status].shown && event.count > 0;
                }.bind(this));
            },
            initialWeekdays : function() {
                if(this.calendar.$children.length && this.calendar.$children[0].todayWeek != undefined) {
                    var days = this.calendar.$children[0].todayWeek;
                    var monthDays = this.calendar.$children[0].days;
                    var month = this.calendar.$children[0].parsedStart.month;

                    // set calendar weeks for showing long or short months
                    if (monthDays.length >= 36) {
                        this.weeks = 6;
                    } else {
                        this.weeks = 5;
                    }

                    for (var i = 0; i < 7; i++) {

                        var timePeriodClass = 'v-present';
                        if (days[i].future === true) {
                            timePeriodClass = 'v-future';
                        }
                        if (days[i].past === true) {
                            timePeriodClass = 'v-past';
                        }
                        if(timePeriodClass !== 'v-past') {
                        }

                        days[i].timePeriodClass = timePeriodClass;
                        days[i].abbreviation = this.day_labels[days[i].weekday];
                        days[i].outsideClass = monthDays[i].month != month ? 'v-outside' : '';
                        days[i].is_active = timePeriodClass == 'v-present';
                    }

                    return days;
                }
                return null;
            },
            monthDays : function() {
                // TODO - Optimize this by only loading event counts instead of actual events
                if(this.calendar.$children.length) {
                    var days = this.calendar.$children[0].days;
                    var month = this.calendar.$children[0].parsedStart.month;
                    for (var i = 0; i < days.length; i++) {

                        var timePeriodClass = 'v-present';
                        if (days[i].future === true) {
                            timePeriodClass = 'v-future';
                        }
                        if (days[i].past === true) {
                            timePeriodClass = 'v-past';
                        }

                        try {
                            days[i].timePeriodClass = timePeriodClass;
                            days[i].abbreviation = this.day_labels[days[i].weekday];
                            days[i].outsideClass = days[i].month != month ? 'v-outside' : '';
                            days[i].is_active = timePeriodClass == 'v-present';
                            var events = this.events.filter((event) => {
                                return event.date == days[i].date
                            })
                            var event = events[0];
                            var total = this.statuses['Open'].shown ? parseInt(event.counts.Open ?? 0) : 0;
                            total += this.statuses['Pending'].shown ? parseInt(event.counts.Pending ?? 0) : 0;
                            total += this.statuses['Assigned'].shown ? parseInt(event.counts.Assigned ?? 0) : 0;
                            total += this.statuses['Approved'].shown ? parseInt(event.counts.Approved ?? 0) : 0;
                            total += this.statuses['Completed'].shown ? parseInt(event.counts.Completed ?? 0) : 0;
                            days[i].counts = {
                                Total: total,
                                Open: parseInt(event.counts.Open ?? 0),
                                Pending: parseInt(event.counts.Pending ?? 0),
                                Assigned: parseInt(event.counts.Assigned ?? 0),
                                Approved: parseInt(event.counts.Approved ?? 0),
                                Completed: parseInt(event.counts.Completed ?? 0)
                            };
                        } catch(e) {
                            days[i].counts = {
                                Total: 0,
                                Open: 0,
                                Pending: 0,
                                Assigned: 0,
                                Approved: 0
                            };
                        }
                    }
                    return days;
                }
                return null;
            },
        },
        created () {
        },
        mounted () {
            this.week = this._week;
            this.monthdays = this.monthDays;
        },
        methods: {
            eventById(id) {
                if(id > 0) {
                    return this.events.filter((event) => { return event.id == id });
                }
                return null;
            },
            eventByUniqueId(uniqueId) {
                if(uniqueId.length > 0) {
                    return this.events.find((event) => { return event.unique_id == uniqueId });
                }
                return null;
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
        }
    });
});