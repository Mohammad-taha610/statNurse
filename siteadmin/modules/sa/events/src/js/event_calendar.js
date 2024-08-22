/**
 * Calendar Object
 */
function EventCalendar() {
    var $document = null;
    var $container = null;
    var $calendar = null;
    var $monthLabel = null;
    var $yearLabel = null;
    var $loadIcon = null;

    var $toNextMonthTrigger = null;
    var $toPreviousMonthTrigger = null;
    var $toCurrentMonthTrigger = null;

    var apiUpdateRoute = null;
    var timestampsCache = [];
    var eventsCache = {};

    /**
     * Initialize calendar object
     * @param {jQuery} $c - Calendar container
     */
    function _construct($c) {
        _cacheDom($c);
        _bindEvents();

        var defaultCalData = {};
        defaultCalData[getTodayEvent()] = [{content: 'TODAY', allDay: true}];

        $calendar.calendario({
            checkUpdate: false,
            caldata: defaultCalData,
            fillEmpty: false,
            startIn: 7
        });

        _updateMonthYearLabels();
        _loadEvents();
    }

    /**
     * Returns an event object to represent 'today'.
     * @returns {string}
     */
    function getTodayEvent() {
        var today = new Date();

        //Parse today's date into m-d-Y
        return ((today.getMonth() + 1) < 10 ? '0' + (today.getMonth() + 1) : (today.getMonth() + 1))
            + '-' + (today.getDate() < 10 ? '0' + today.getDate() : today.getDate())
            + '-' + today.getFullYear();
    }

    /**
     * Displays the next month.
     */
    function renderNextMonth() {
        $calendar.calendario('gotoNextMonth', _updateMonthYearLabels);
        _loadEvents();
    }

    /**
     * Displays previous month.
     */
    function renderPreviousMonth() {
        $calendar.calendario('gotoPreviousMonth', _updateMonthYearLabels);
        _loadEvents();
    }

    /**
     * Displays the month of the current date.
     */
    function renderCurrentMonth() {
        $calendar.calendario('gotoNow', _updateMonthYearLabels);
        _loadEvents();
    }

    /**
     * Cache the components of the calendar that will be regularly used.
     *
     * @param {jQuery} $c - Calendar container
     * @private
     */
    function _cacheDom($c) {
        $document = $(document);
        $container = $c;
        $calendar = $c.find('#calendar');
        $monthLabel = $c.find('.calendar-month');
        $yearLabel = $c.find('.calendar-year');
        $nextLabel = $('#custom-next');
        $prevLabel = $('#custom-prev');
        $loadIcon = $c.find('.loading-icon');
        $toNextMonthTrigger = $c.find('[data-action="to-next-month"]');
        $toPreviousMonthTrigger = $c.find('[data-action="to-previous-month"]');
        $toCurrentMonthTrigger = $c.find('[data-action="to-current-month"]');
        apiUpdateRoute = $container.attr('action');
    }

    /**
     * Binds event handlers to intractable components of the calendar.
     * @private
     */
    function _bindEvents() {
        $toNextMonthTrigger.bind('click', renderNextMonth);
        $toPreviousMonthTrigger.bind('click', renderPreviousMonth);
        $toCurrentMonthTrigger.bind('click', renderCurrentMonth);
    }

    /**
     * Updates the month and year labels on the UI to the newly selected values.
     *
     * @private
     */
    function _updateMonthYearLabels() {
        $monthLabel.html($calendar.calendario('getMonthName'));
        $yearLabel.html($calendar.calendario('getYear'));
        _updateNextPrevLabels();
        _updateListView();
    }
    function _updateNextPrevLabels()
    {
        var currentMonthInt = $calendar.calendario('getMonth');
        var nextMonth = "February";
        var prevMonth = "December";
        switch(currentMonthInt) {
            case 1:
                prevMonth = "December";
                nextMonth = "February";
                break;
            case 2:
                prevMonth = "January";
                nextMonth = "March";
                break;
            case 3:
                prevMonth = "February";
                nextMonth = "April";
                break;
            case 4:
                prevMonth = "March";
                nextMonth = "May";
                break;
            case 5:
                prevMonth = "April";
                nextMonth = "June";
                break;
            case 6:
                prevMonth = "May";
                nextMonth = "July";
                break;
            case 7:
                prevMonth = "June";
                nextMonth = "August";
                break;
            case 8:
                prevMonth = "July";
                nextMonth = "September";
                break;
            case 9:
                prevMonth = "August";
                nextMonth = "October";
                break;
            case 10:
                prevMonth = "September";
                nextMonth = "November";
                break;
            case 11:
                prevMonth = "October";
                nextMonth = "December";
                break;
            case 12:
                prevMonth = "November";
                nextMonth = "January";
                break;
        }
        $nextLabel.html('<span style="float: right;">' + nextMonth + '</span>');
        $prevLabel.html('<span style="float: left;">' + prevMonth + '</span>');
    }

    function _getTimestampOfSelectedMonth() {
        var month = parseInt($calendar.calendario('getMonth'));
        /*
         * Append a leading 0 to any month < 10 (october). Without the leading 0, all IE browsers will fail
         * to parse the timestamp.
         */
        if (month < 10) {
            month = '0' + month;
        }
        var string = $calendar.calendario('getYear') + '-' + month + '-01T00:00:00';
        var date = new Date(Date.UTC($calendar.calendario('getYear'), month, 1, 0, 0, 0, 0));
        //Convert from milliseconds to seconds
        return (date.getTime() / 1000);
    }

    /**
     *
     * @private
     */
    function _loadEvents() {
        var timestamp = _getTimestampOfSelectedMonth();
        if (!_isTimestampCached(timestamp)) {
            _cacheTimestamp(timestamp);
            _makeRequest(timestamp);
        }
        // _updateListView();
    }

    /**
     * Render or hide the loading icon.
     *
     * @param doShow - If true, show the loading icon. If false, hide the loading icon.
     */
    function showLoader(doShow) {
        if (doShow) {
            $loadIcon.addClass('is-loading');
            $loadIcon.fadeIn(300);
        } else {
            $loadIcon.fadeOut(300, function () {
                setTimeout(function () {
                    $loadIcon.removeClass('is-loading');
                }, 400);
            })
        }
    }

    /**
     * Send an AJAX request to fetch the events for a given month.
     *
     * @param {string} timestamp - The timestamp of a month within a specific year.
     * @private
     */
    function _makeRequest(timestamp) {
        $.ajax({
            url: apiUpdateRoute + timestamp,
            type: "POST",
            data: {
                json_str: JSON.stringify({category: category}),
            },
            dataType: "json",
            beforeSend: function () {
                showLoader(true);
            },
            success: function (request) {
                if (request['success'] == 1) {
                    mergeEventsWithCalendar(request['events']);
                }
            },
            error: function () {},
            complete: function () {
                showLoader(false);
            }
        });
    }

    /**
     * Merges a list of events with the events stored in the event cache, then renders them on the calendar.
     *
     * @param events - A list of events.
     */
    function mergeEventsWithCalendar(events) {
        var newEvents = {};
        var date;
        //Merge new events with cached events
        mergeEventsCache(events);
        //Iterate over each event
        for(var x in eventsCache) {
            if(!eventsCache.hasOwnProperty(x)) {
                continue;
            }
            //Iterate over recurrences of an event
            for(var y in eventsCache[x]['recurrences']) {
                if(!eventsCache[x]['recurrences'].hasOwnProperty(y)) {
                    continue;
                }
                var parsedDate = _parseDate(eventsCache[x]['recurrences'][y]['start']['date']);
                date = _formatDateToMonthDayYear(parsedDate);
                if (!(date in newEvents)) {
                    newEvents[date] = [];
                }
                //Convert the event to a format the calendar can understand
                newEvents[date][newEvents[date].length] = {
                    content: '<a href="' + eventsCache[x]['recurrences'][y]['url'] + '">' +
                        eventsCache[x]['name'] +
                        '</a>',
                    allDay:true,
                    repeat: 'INTERVAL',
                    startDate: _formatDateToMonthDayYear(_parseDate(eventsCache[x]['recurrences'][y]['start']['date'])),
                    endDate: (eventsCache[x]['end_date'] != null) ? _formatDateToMonthDayYear(
                        _parseDate(eventsCache[x]['recurrences'][y]['end']['date'])
                    ) : null,
                    link: eventsCache[x]['recurrences'][y]['url'],
                    category: eventsCache[x]['category']
                };
            }
        }
        $calendar.calendario('setData', newEvents, true, _updateListView);
    }

    /**
     * Merge a new list of events with the current event cache.
     *
     * @param events
     */
    function mergeEventsCache(events) {
        for(var y in events) {
            if(!events.hasOwnProperty(y)) {
                continue;
            }
            var recurrences = events[y]['recurrences'];
            events[y]['recurrences'] = [];
            if(!(events[y]['id'] in eventsCache)) {
                eventsCache[events[y]['id']] = $.extend({}, events[y]);
            }
            for(var x in recurrences) {
                if(!recurrences.hasOwnProperty(x)) {
                    continue;
                }
                if(!(recurrences[x]['id'] in eventsCache[events[y]['id']]['recurrences'])) {
                    eventsCache[events[y]['id']]['recurrences'][recurrences[x]['id']] = recurrences[x];
                }
            }
        }
    }

    /**
     * Formats a Date object to the string format 'm-d-Y'
     *
     * @param   {Date}      date
     * @returns {string}
     * @private
     */
    function _formatDateToMonthDayYear(date) {
        var month = '' + (date.getMonth() + 1);
        var day = '' + date.getDate();
        var year = date.getFullYear();
        if (month.length < 2) month = '0' + month;
        if (day.length < 2) day = '0' + day;
        return [month, day, year, 'T00:00:00'].join('-');
    }

    /**
     * Parse date in the following format: YYYY-MM-DD hh:ii:ss.00000 (year, month, day, hours, seconds, milliseconds)
     *
     * @param dateString
     * @returns {Date}
     * @private
     */
    function _parseDate(dateString) {
        var regex = /^(\d{4})-(\d{2})-(\d{2})(?:\s+(\d{2}):(\d{2}):(\d{2}))?.(\d{6})$/;
        var match = regex.exec(dateString);
        var month = parseInt(match[2]),
            date = parseInt(match[3]),
            hours = parseInt(match[4]),
            minutes = parseInt(match[5]),
            seconds = parseInt(match[6]),
            year = parseInt(match[1]);
        if(date < 10) {
            date = '0' + date;
        }
        if(month < 10) {
            month = '0' + month;
        }
        //Month given with index from 1-12. JS parses months from index 0-12.
        month--;
        return new Date(year, month, date, hours, minutes, seconds);
    }
    /**
     * Checks if the given timestamp already exists within the timestamp cache.
     *
     * @param timestamp
     * @returns {boolean}
     * @private
     */
    function _isTimestampCached(timestamp) {
        for (var x = 0; x < timestampsCache.length; x++) {
            if (timestampsCache[x] == timestamp) {
                return true;
            }
        }
        return false;
    }

    /**
     * Adds a new timestamp to the timestamp cache.
     *
     * @param timestamp
     * @private
     */
    function _cacheTimestamp(timestamp) {
        timestampsCache.push(timestamp);
    }
    function _updateListView()
    {
        var month = new Array();
        month[0] = "January";
        month[1] = "February";
        month[2] = "March";
        month[3] = "April";
        month[4] = "May";
        month[5] = "June";
        month[6] = "July";
        month[7] = "August";
        month[8] = "September";
        month[9] = "October";
        month[10] = "November";
        month[11] = "December";
        var listContainer = $('#list');
        // Reset HTML for container
        listContainer.html('<div class="no-events-msg">There are no events to display at this time.</div>\
                            <div class="past-events">\
                            </div>\
                            <div class="upcoming-events">\
                                <h2>Upcoming Events</h2>\
                            </div>');
        $('#list .upcoming-events').hide();
        $('#list .past-events').hide();
        var currentMonth = $calendar.calendario('getMonth');
        var currentYear = $calendar.calendario('getYear');
        for(var y in eventsCache) {
            var eventDate = _parseDate(eventsCache[y].start_date.date);
            var startTime = '';
            var endTime = '';
            if(eventsCache[y].start_time) {
                var parsedStartTime =  _parseDate(eventsCache[y].start_time.date);
                startTime = ((parsedStartTime.getHours() > 12) ? parsedStartTime.getHours() - 12 : parsedStartTime.getHours()) + ':' + ((parsedStartTime.getMinutes() > 9) ? parsedStartTime.getMinutes() : '0' + parsedStartTime.getMinutes()) + ((parsedStartTime.getHours() > 12) ? 'pm' : 'am');
            }
            if(eventsCache[y].end_time) {
                var parsedEndTime =  _parseDate(eventsCache[y].end_time.date);
                endTime = ((parsedEndTime.getHours() > 12) ? parsedEndTime.getHours() - 12 : parsedEndTime.getHours()) + ':' + ((parsedEndTime.getMinutes() > 9) ? parsedEndTime.getMinutes() : '0' + parsedEndTime.getMinutes()) + ((parsedEndTime.getHours() > 12) ? 'pm' : 'am');
            }
            if(parseInt(currentMonth) == (parseInt(eventDate.getMonth()) + 1) && currentYear == eventDate.getFullYear()) {
                var listItemHtml = '<div class="listEvent" data-start="' + eventsCache[y].start_date.date + '">\
                    <a data-toggle="collapse" data-parent="#accordion" class="collapsed" href="#' + eventsCache[y].id + '">\
                        <div class="header">\
                            <div class="date"><span class="day">' + eventDate.getDate() + '</span><span class="month">' + month[eventDate.getMonth()] + '</span></div>\
                            <div class="name">' + eventsCache[y].name + (eventsCache[y].location_name ? ' | ' : '') + eventsCache[y].location_name + (startTime ? ' | ' : '') + startTime + '</div>\
                            <div class="arrow"><i class="fa fa-angle-down" aria-hidden="true"></i></div>\
                        </div>\
                    </a>\
                    <div id="' + eventsCache[y].id + '" class="panel-collapse collapse description"><div class="panel-body">' + eventsCache[y].description + '</div></div>\
                </div>';
                var today = new Date();
                var eventStartDate = new Date(_parseDate(eventsCache[y].start_date.date));
                var eventContainer = '.past-events';
                // Upcoming events
                if(eventStartDate >= today) {
                    var eventContainer = '.upcoming-events';
                    $('#list .upcoming-events').show();
                    $('#list .no-events-msg').hide();
                }
                else {
                    $('#list .past-events').show();
                    $('#list .no-events-msg').hide();
                }
                // Find the position for this new item
                var printed = false;
                if($('#list ' + eventContainer + ' .listEvent').length > 0) {
                    $('#list ' + eventContainer + ' .listEvent').each(function() {
                        if(eventsCache[y].start_date.date < $(this).data('start') && !printed) {
                            $(this).before(listItemHtml);
                            printed = true;
                        }
                    });
                }
                if(!printed) {
                    $('#list ' + eventContainer).append(listItemHtml);
                }

            }
        }

    }
    /**
     * The methods and properties made accessible outside of the object.
     */
    return {
        init: _construct,
        getTodayEvent: getTodayEvent,
        getCalendarInstance: $calendar
    }

}