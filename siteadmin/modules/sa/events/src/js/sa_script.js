window.addEventListener('load', function() {
    var eventListener = new EventListener();
    var $datePickers = $('[data-content="date-picker"] .input-group');
    var $inlineDatePickers = $('[data-content="inline-date-picker"]');
    var $timePickers = $('[data-content="time-picker"] .input-group');
    var $sliders = $('[data-content="slider"]');
    var $recurringToggle = $('input[name="is-recurring"]');
    var $recurrenceRulesInput = $('input[name="recurrence_rules"]');
    var $allDayEventInput = $('input[name="is-all-day-event"]');

    var $frequencyInput = $('input[name="frequency"]');
    var $intervalInput = $('input[name="interval"]');
    var $startDate = $('input[name="start_date"]');
    var $endDate = $('input[name="end_date"]');

    var $timeContainer = $('#time-container');
    var $startTime = $timeContainer.find('input[name="start_time"]');
    var $endTime = $timeContainer.find('input[name="end_time"]');

    var $untilDateInputContainer = $('.repeat-until-date-container');
    var $untilDateTrigger = $('input[name="repeat-until-date"]');
    var $untilDateInput = $untilDateInputContainer.find('input[name="until_date"]');

    var $recurringOptionsModal = $('#recurring-options-modal');
    var $frequencyToggles = $recurringOptionsModal.find('[data-frequency-toggle]');

    var selectedFrequency = $frequencyInput.val();
    var selectedInterval = $intervalInput.val();

    var $rruleDescription = $('.rrule-description');
    var $recurrencePreview = $('.recurrence-events-preview');

    /** ****************************************
     *  Initialize page components
     *
     *  - recurring options modal
     *  - date pickers
     *  - inline date pickers
     *  - time pickers
     *  - sliders
     *  - CKEditor
     ** ****************************************/

    /**
     * Initialize recurring options modal.
     */
    $recurringOptionsModal.modal({
        show: false,
        keyboard: false,
        backdrop: 'static'
    });

    /**
     * Initialize start date picker.
     *
     * @see - https://eonasdan.github.io/bootstrap-datetimepicker
     */
    $startDate.datetimepicker({
        format: 'MM/DD/YYYY',
        icons: {
            time: 'fa fa-time',
            date: 'fa fa-calendar',
            up: 'fa fa-chevron-up',
            down: 'fa fa-chevron-down',
            previous: 'fa fa-chevron-left',
            next: 'fa fa-chevron-right',
            today: 'fa fa-screenshot',
            clear: 'fa fa-trash',
            close: 'fa fa-remove'
        }
    });

    /**
     * Initialize end date picker.
     *
     * @see - https://eonasdan.github.io/bootstrap-datetimepicker
     */
    $endDate.datetimepicker({
        format: 'MM/DD/YYYY',
        icons: {
            time: 'fa fa-time',
            date: 'fa fa-calendar',
            up: 'fa fa-chevron-up',
            down: 'fa fa-chevron-down',
            previous: 'fa fa-chevron-left',
            next: 'fa fa-chevron-right',
            today: 'fa fa-screenshot',
            clear: 'fa fa-trash',
            close: 'fa fa-remove'
        }
    });

    /**
     * Initialize until date picker.
     *
     * @see - https://eonasdan.github.io/bootstrap-datetimepicker
     */
    $untilDateInput.datetimepicker({
        format: 'MM/DD/YYYY',
        icons: {
            time: 'fa fa-time',
            date: 'fa fa-calendar',
            up: 'fa fa-chevron-up',
            down: 'fa fa-chevron-down',
            previous: 'fa fa-chevron-left',
            next: 'fa fa-chevron-right',
            today: 'fa fa-screenshot',
            clear: 'fa fa-trash',
            close: 'fa fa-remove'
        }
    });


    /** ****************************************
     *  Date field handlers
     ** ****************************************/

    /**
     * If the start date's value is after the end date,
     * set the end date's value to the start date.
     */
    $startDate.on('dp.change', function () {
        var startDate = moment($startDate.val());
        var endDate = moment($endDate.val());

        validateDateFields(startDate, endDate);
        $endDate.data("DateTimePicker").minDate($startDate.val());

        if (startDate > endDate) {
            $endDate.val($startDate.val());
        }

        eventListener.publish('rrule/update', null);
    });

    /**
     * If the end date's value is before the start date,
     * set the start date to the end date.
     */
    $endDate.on('dp.change', function () {
        var startDate = Date.parse($startDate.val());
        var endDate = Date.parse($endDate.val());

        validateDateFields(startDate, endDate);

        if (startDate > endDate) {
            $startDate.val($endDate.val());
            $endDate.data("DateTimePicker").minDate($startDate.val());
        }

        eventListener.publish('rrule/update', null);
    });

    /**
     * Validate the date fields.
     *
     * @param startDate
     * @param endDate
     * @returns {boolean}
     */
    function validateDateFields(startDate, endDate) {
        if (!startDate) {
            alert("Please enter a valid start date.");
            return false;
        }

        if (!endDate) {
            alert("Please enter a valid end date.");
            return false;
        }
    }

    /**
     * Reset all time pickers
     *
     * @see - https://eonasdan.github.io/bootstrap-datetimepicker
     */
    $startTime.datetimepicker({
        format: 'LT',
        icons: {
            time: 'fa fa-time',
            date: 'fa fa-calendar',
            up: 'fa fa-chevron-up',
            down: 'fa fa-chevron-down',
            previous: 'fa fa-chevron-left',
            next: 'fa fa-chevron-right',
            today: 'fa fa-screenshot',
            clear: 'fa fa-trash',
            close: 'fa fa-remove'
        }
    });

    $endTime.datetimepicker({
        format: 'LT',
        icons: {
            time: 'fa fa-time',
            date: 'fa fa-calendar',
            up: 'fa fa-chevron-up',
            down: 'fa fa-chevron-down',
            previous: 'fa fa-chevron-left',
            next: 'fa fa-chevron-right',
            today: 'fa fa-screenshot',
            clear: 'fa fa-trash',
            close: 'fa fa-remove'
        }
    });


    $startTime.on('dp.change', function () {
        var m = moment().format("MM-DD-YYYY");
        var startTime = moment(m + " " + $startTime.val());
        var endTime = moment(m + " " + $endTime.val());

        if (startTime >= endTime) {
            if(startTime.hour() == 23) {
                $endTime.val(startTime.hour(23).minute(59).format("LT"));
            } else {
                $endTime.val(startTime.add('1', 'hour').minute(0).format("LT"));
            }
        }
    });

    $endTime.on('dp.change', function () {
        var m = moment().format("MM-DD-YYYY");
        var startTime = moment(m + " " + $startTime.val());
        var endTime = moment(m + " " + $endTime.val());

        if (endTime <= startTime) {
            if(startTime.hour() == 1) {
                $startTime.val(endTime.hour(0).minute(0).format("LT"));
            } else {
                $startTime.val(endTime.add('-1', 'hour').minute(0).format("LT"));
            }
        }
    });

    /**
     * Initialize inline date picker.
     */
    $inlineDatePickers.datetimepicker({
        format: 'MM/DD/YYYY',
        inline: true,
        sideBySide: true,
        icons: {
            time: 'fa fa-time',
            date: 'fa fa-calendar',
            up: 'fa fa-chevron-up',
            down: 'fa fa-chevron-down',
            previous: 'fa fa-chevron-left',
            next: 'fa fa-chevron-right',
            today: 'fa fa-screenshot',
            clear: 'fa fa-trash',
            close: 'fa fa-remove'
        }
    });

    /**
     * Initialize sliders
     */
    $sliders.slider();


    /**
     * Initialize CKEditor
     */
    CKEDITOR.replace('description', {
        scayt_autoStartup: true,
        enterMode: CKEDITOR.ENTER_DIV,
        extraPlugins: 'colorbutton,codesnippet,justify,font,image2,indentblock',
        filebrowserBrowseUrl: '/siteadmin/files/browse',
        filebrowserImageBrowseUrl: '/siteadmin/files/browse',
        filebrowserFlashBrowseUrl: '/siteadmin/files/browse',
        filebrowserWindowWidth: '700',
        filebrowserWindowHeight: '630',
        allowedContent: true

    });


    /** ****************************************
     *  Register event handlers
     ** ****************************************/

    //Register the event
    eventListener.register('rrule/update');

    //Set the event's callback
    eventListener.subscribe('rrule/update', onRRuleUpdate);

    //Execute the event on page load
    if($frequencyInput.length > 0) {
        eventListener.publish('rrule/update', null);
    }

    if ($('input[name="repeat-until-date"]:checked').val() == 1) {
        console.log('until date viewable');
        $untilDateInputContainer.slideDown();
    }

    if ($allDayEventInput.val() === 1) {
        $timeContainer.slideUp();
    }

    function onRRuleUpdate() {
        var rrule;
        $frequencyInput.val(selectedFrequency);
        $intervalInput.val(selectedInterval);
        rrule = parseRRules();
        $recurrenceRulesInput.val(rrule.toString());

        $rruleDescription.text(rrule.toText());
        resetRecurrenceOptions();
        //loadRecurrencePreview(rrule);
    }

    function loadRecurrencePreview(rrule) {
        var timestamps = rrule.all(function (date, i) {
            return i < 10
        });
        var $previewTable = $recurrencePreview.find('table > tbody');
        var date;

        var months = ["January", "February", "March", "April", "May", "June",
            "July", "August", "September", "October", "November", "December"
        ];

        var week_days = ["Sun", "Mon", "Tues", "Wed", "Thurs", "Fri", "Sat"];


        $previewTable.html("");

        for (var x = 0; x < timestamps.length; x++) {
            date = new Date(timestamps[x]);
            $previewTable.append("<tr>" +
                "<td>" + months[date.getMonth() - 1] + "</td>" +
                "<td>" + week_days[date.getDay()] + " " + date.getUTCDate() + "</td>" +
                "<td>" + date.getFullYear() + "</td>" +
                "</tr>");
        }
    }


    /** ****************************************
     *  Recurring options modal handlers
     ** ****************************************/

    /**
     * Recurring options modal toggle.
     */
    $recurringToggle.click(function () {
        if ($(this).val() == 1) {
            $recurringOptionsModal.modal('show');
        } else {
            $recurringOptionsModal.modal('hide');
        }
    });

    /**
     * Recurring options modal close button handler
     */
    /*$recurringOptionsModal.on('click', '[data-action="close-re-modal"]', function () {
        $recurringOptionsModal.modal('hide');

        if ($frequencyInput.val() == 'daily' && $intervalInput.val() == 1) {
            $recurringToggle.each(function () {
                $(this).prop('checked', ($(this).val() == 0));
            });
        }
    });*/

    /**
     * Recurring options modal save handler
     */
    $recurringOptionsModal.on('click', '[data-action="save-re-modal"]', function () {
        $recurringOptionsModal.modal('hide');

        $sliders.each(function(){
             if($(this).parents('.tab-pane').attr('id') != 'recurrence-' + selectedFrequency) {
                $(this).slider('setValue', 1);
             } else {
                 selectedInterval = $(this).val();
             }
         });

        $sliders.parents('.tab-pane:not(#recurrence-' + selectedFrequency + ') .slider').find('+ span.value').text(1);
        eventListener.publish('rrule/update', null);
    });

    /**
     * Frequency toggle handler
     *
     * When a frequency (daily, monthly, yearly, etc) is selected, reset all of the
     * fields for the other frequencies.
     */
    $frequencyToggles.bind('click', function () {
        selectedFrequency = $(this).attr('data-frequency-toggle');
    });

    /**
     * Slider 'onSlide' handler
     *
     * When a slider is moved, update adjacent span tag to contain the value of the slider.
     */
    $sliders.on('slide', function () {
        selectedInterval = $(this).val();
        $(this).parents('.slider').find('+ span.value').text($(this).val());
    });

    /**
     *
     */
    $allDayEventInput.change(function () {
        if ($(this).prop('checked') === true) {
            $timeContainer.slideUp(300, function () {
                $startTime.val("");
                $endTime.val("");
            });
        } else {
            $timeContainer.slideDown(300);
        }

        eventListener.publish('rrule/update', null);
    });

    /**
     *
     */
    $untilDateTrigger.click(function () {
        if ($(this).val() == 1) {
            $untilDateInputContainer.slideDown(300);
        } else {
            $untilDateInputContainer.slideUp(300);
        }
    });

    /**
     * Parse RRule parameters based on the selected frequency (daily, monthly, etc).
     *
     * @returns {RRule}
     */
    function parseRRules() {
        var rruleOptions = {
            interval: parseInt(selectedInterval),
            dtstart: parseStringToDateTime($startDate.val(), $startTime.val()),
            //week starts on sunday
            wkst: RRule.SU
        };

        if ($('input[name="is-recurring"]:checked').val() == 1) {
            if ($('input[name="repeat-until-date"]:checked').val() == 1) {
                if ($untilDateInput.val() != "") {
                    rruleOptions.until = parseStringToDateTime($untilDateInput.val(), "");
                    console.log("repeat date | until not empty");
                } else {
                    alert("You selected \"repeat until date\", but did not select a date.");
                }
            } else {
                console.log('repeat forever');
            }
        } else {
            rruleOptions.until = parseStringToDateTime($startDate.val(), $startTime.val());
            console.log("not recurring");
        }

        switch (selectedFrequency.toLowerCase()) {
            case 'daily':
                rruleOptions = dailyRRule(rruleOptions);
                break;
            case 'weekly':
                rruleOptions = weeklyRRule(rruleOptions);
                break;
            case 'monthly':
                rruleOptions = monthlyRRule(rruleOptions);
                break;
            case 'yearly':
                rruleOptions = yearlyRRule(rruleOptions);
                break;
            default:
                //This shouldn't happen. @todo - throw error
                console.log('Error: unexpected RRule frequency selected.');
                break;
        }

        return new RRule(rruleOptions);
    }

    /**
     * Parse a date string, and a time string into a Date() object.
     *
     * @param {string} date  - A date in the format mm-dd-yyy
     * @param {string} time  - A time in the format h:mm am|pm
     * @returns {Date}
     */
    function parseStringToDateTime(date, time) {
        var s = date + " " + time;
        return new Date(Date.parse(s));
    }

    /**
     * Build RRule for daily frequency.
     *
     * @param {object} options
     * @returns {object}
     */
    function dailyRRule(options) {
        options.freq = RRule.DAILY;
        return options;
    }

    /**
     * Build RRule for weekly frequency.
     *
     * @param {object} options
     * @returns {object}
     */
    function weeklyRRule(options) {
        var rruleWeekDayConversion = {
            'MO': RRule.MO,
            'TU': RRule.TU,
            'WE': RRule.WE,
            'TH': RRule.TH,
            'SA': RRule.SA,
            'FR': RRule.FR,
            'SU': RRule.SU
        };
        var $selectedWeekDayInputs = $recurringOptionsModal.find('input[name="week_days[]"]:checked');
        var selectedWeekDays = [];

        $selectedWeekDayInputs.each(function () {
            selectedWeekDays.push(rruleWeekDayConversion[$(this).val()]);
        });

        if (selectedWeekDays.length > 0) {
            options.byweekday = selectedWeekDays;
        }

        options.freq = RRule.WEEKLY;
        return options;
    }

    /**
     * Build RRule for monthly frequency.
     *
     * @param {object} options
     * @returns {object}
     */
    function monthlyRRule(options) {
        options.freq = RRule.MONTHLY;
        return options;
    }

    /**
     * Build RRule for yearly frequency.
     *
     * @param {object} options
     * @returns {object}
     */
    function yearlyRRule(options) {
        var $selectedMonthInputs = $recurringOptionsModal.find('input[name="months[]"]:checked');
        var selectedMonths = [];

        $selectedMonthInputs.each(function () {
            /**
             * The server end processes months with the index starting at 0, but the RRule JS library
             * handles them in indexes starting at 1. Therefore, this index must be incremented by 1
             * for the RRule text preview to work.
             */
            selectedMonths.push(parseInt($(this).val()) + 1);
        });

        if (selectedMonths.length > 0) {
            options.bymonth = selectedMonths;
        }

        options.freq = RRule.YEARLY;
        return options;
    }

    /**
     * Resets the recurrence fields for all frequency options.
     */
    function resetRecurrenceOptions() {
        var freq = selectedFrequency.toLowerCase();

        //Deselect all checkboxes and radio inputs
        var recurrenceInputSelector = '.tab-pane:not(#recurrence-' + freq + ') input[type="checkbox"]';
        recurrenceInputSelector += ', .tab-pane:not(#recurrence-' + freq + ') input[type="radio"]';
        $recurringOptionsModal.find(recurrenceInputSelector).prop('checked', false);
    }
});


/**
 *
 * @returns {{events: {}, register: register, subscribe: subscribe, publish: publish}}
 * @constructor
 */
function EventListener() {
    var events = {};

    /**
     *
     * @param key
     */
    function register(key) {
        events[key] = [];
    }

    /**
     *
     * @param key
     * @param listener
     */
    function subscribe(key, listener) {
        if (!(key in events)) {
            register(key);
        }

        events[key].push(listener);
    }

    /**
     *
     * @param key
     * @param data
     */
    function publish(key, data) {
        if(events.hasOwnProperty(key)) {
            events[key].forEach(function (callback) {
                callback(data != undefined ? data : {});
            });
        }
    }

    return {
        events: events,
        register: function (key) {
            register(key);
        },
        subscribe: function (key, listener) {
            subscribe(key, listener);
        },
        publish: function (key, data) {
            publish(key, data);
        }
    }
}