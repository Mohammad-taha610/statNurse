window.addEventListener('load', function() {
    let todayStr = new Date().toISOString().replace(/T.*$/, '');
    Vue.component('fullcalendar', {
        //If there is something in the Full calendar library that we want to support as an option that we can turn on you have to add it here and in the
        //create calendar method.
        props: {
            customButtons:{
                default:{}
            },
            buttonLocations:{
                //If you add spaces between the commas those spaces will be turned into empty buttons, idk why
                //This is also apparently the way to do it, with the type and default, just getting rid of a nasty looking error message
                type: Object,
                default() {
                    return {
                        start: 'dayGridMonth,listMonth dayGridWeek,listWeek timeGridDay,listDay',
                        center: 'title',
                        end: 'prev,next'
                    }
                }
            },
            eventTimeFormat:{
                type: Object,
                default(){
                    return {
                        hour: '2-digit',
                        minute: '2-digit',
                        hour12: true
                    }
                }
            },
            eventDisplay:{
                default:"block"
            },
            selectable: {
                default:true
            },
            selectCalendarFunction:{
                type: Function,
                default: (selectionInfo) => {alert(selectionInfo.start.toString());}
            },
            eventsCalendarFunction:{
                type: Function,
                default: (fetchInfo, callback) =>
                {
                    modRequest.request('events.fillCalendar', null, {'start':(fetchInfo.start.valueOf()/1000), 'end':(fetchInfo.end.valueOf()/1000), 'category':null},
                        function(response) {
                            if(response.data.success) {
                                user_events = response.data.events;
                                this.dates = user_events;

                                callback(user_events);
                            }
                        }.bind(this),
                        function(error) {
                        }
                    );
                }
            },
            displayEventEnd:{
                default:true
            }
        },
        data: function(){
            return{
                date: new Date(Date.now())
            }
        },
        mounted: function(){
            this.createCalendar();
        },
        template: `
            <div id="calendar">
            </div>`,
        methods: {
            createCalendar: function () {
                let calendarElement = document.getElementById('calendar');

                let calendar = new FullCalendar.Calendar(calendarElement, {
                    //Replace by property after initial run through
                    initialView: 'dayGridMonth',
                    initialDate: todayStr,
                    customButtons: this.customButtons,
                    headerToolbar: this.buttonLocations,
                    eventTimeFormat: this.eventTimeFormat,
                    eventDisplay: this.eventDisplay,
                    displayEventEnd: this.displayEventEnd,
                    selectable: this.selectable,

                    //Functions
                    select: this.selectCalendarFunction,
                    events: this.eventsCalendarFunction
                });

                calendar.render();

                //These probably don't need to be used
                jQuery('#datetimepicker').datetimepicker({
                    format: 'Y/m/d g:i A',
                    formatTime: 'g:i A',
                    validateOnBlur: false,
                });


                jQuery('#datetimepicker2').datetimepicker({
                    datepicker : false,
                    format: 'g:i A',
                    formatTime: 'g:i A',
                    validateOnBlur: false,
                });
            }
        }
    });
    window.calendar_vue = new Vue({
        el: '#vue-context',
        data:{
            fullCalendarConfig:fullCalendarConfig
        }
    });

});