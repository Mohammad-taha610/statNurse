var refresh_timers = {};
var resources = {};
var callbacks = [];
var loaded_scripts = [];
var widget_js_objects = {};
var executed_loaded_callbacks = [];

$(document).ready( function() {

    $('.dashboard-widgets-main-container').on('click', '.refresh_widget', function(e) {

        var widget = $(this).closest('.widget-box');
        var widget_id = widget.data('widget');
        var id = widget.attr('id');

        refreshWidget(id, widget_id);
        e.preventDefault()
    });

    $('.dashboard-widgets-main-container').on('click', '.minimize_widget', function(e) {
        var widget = $(this).closest('.widget-box');

        toggleWidget(widget);

        saveSettings();

        e.preventDefault()
    });

    $('.dashboard-widgets-main-container').on('click', '.close_widget', function(e) {
        var widget = $(this).closest('.widget-box');
        widget.remove();

        saveSettings();

        e.preventDefault()
    });

    $('.addWidget').click( function(e) {
        addDashboardWidget('widget-tobemoved', $(this).data('widget_id'), false, true, true);
        setupDragandDrop();
        e.preventDefault()
    });

    if (user_settings.length>0) {
        for (var i in user_settings) {
            addDashboardWidget(user_settings[i].location, user_settings[i].widget_id, user_settings[i].minimized, false, true);
        }
    }
    else
    {
        for (var i in available_widgets) {
            addDashboardWidget('widget-tobemoved', available_widgets[i].id, false, true, true);
        }
    }

    setupDragandDrop();


});

function setupDragandDrop() {

    try {
        $( ".dashboard-widgets-left-col, .dashboard-widgets-right-col, .dashboard-widgets-top-col, .dashboard-widgets-bottom-col" ).sortable("destroy");
    }
    catch(e) {

    }

    $( ".dashboard-widgets-left-col, .dashboard-widgets-right-col, .dashboard-widgets-top-col, .dashboard-widgets-bottom-col" ).sortable({
        connectWith: '.dashboard-widgets-left-col, .dashboard-widgets-right-col, .dashboard-widgets-top-col, .dashboard-widgets-bottom-col',
        update: saveSettings,
        placeholder: "dashboard-hover",
        handle: ".drag_handle",
        cursorAt: { left: 195, top: 5 },
        start: function( event, ui ) {
            $(ui.item).addClass('widget_drag_helper');

            $('.placement').addClass('show_placement');
        },
        stop: function( event, ui ) {
            $(ui.item).removeClass('widget_drag_helper');

            $('.placement').removeClass('show_placement');
        }
    });


}

function toggleWidget(widget) {
    if (!$(widget).data('minimized')) {

        $(widget).data('minimized', true);
        $('.widget-body', widget).slideUp();
        $('.minimize_widget', widget).removeClass('fa-chevron-up').addClass('fa-chevron-down')

    }
    else
    {
        $(widget).data('minimized', false);
        $('.widget-body', widget).slideDown();
        $('.minimize_widget', widget).removeClass('fa-chevron-down').addClass('fa-chevron-up')
    }
}

function loadedScript(script) {
    loaded_scripts.push(script);
    for(var i=0; i<callbacks.length; i++) {

        var execute_callback = true;
        for(var x=0; x<callbacks[i].scripts.length; x++) {
            if ( loaded_scripts.indexOf(callbacks[i].scripts[x]) == -1 ) {
                execute_callback = false;
            }
        }


        if ( execute_callback && executed_loaded_callbacks.indexOf(callbacks[i].element_id) != -1 ) {
            execute_callback = false;
        }

        if (execute_callback && callbacks[i].callback && window[callbacks[i].callback]) {

            executed_loaded_callbacks.push(callbacks[i].element_id);

            window[callbacks[i].callback](callbacks[i].element_id, callbacks[i].response);
        }
        else if (execute_callback && callbacks[i].object && window[callbacks[i].object]) {

            executed_loaded_callbacks.push(callbacks[i].element_id);

            widget_js_objects[callbacks[i].element_id] = new window[callbacks[i].object]();
            widget_js_objects[callbacks[i].element_id].init(callbacks[i].element_id, callbacks[i].response)
        }
    }
}

function areRequiredScriptsLoaded(scripts) {
    var loaded = true;
    for(var x=0; x<scripts.length; x++) {

        if ( loaded_scripts.indexOf(scripts[x]) == -1 ) {
            loaded = false;
        }

    }
    return loaded;
}

function addScriptLoadedCallback(scripts, callback, id, response) {
    callbacks.push({ "scripts":scripts, "object":null, "callback":callback, "element_id":id, "response":response });
}

function addScriptLoadedObjectCallback(scripts, object, id, response) {
    callbacks.push({ "scripts":scripts, "object":object, "callback":null, "element_id":id, "response":response });
}

function addDashboardWidget(location, widget_id, minimized, moveToDefault, saveSettings) {

    if (minimized==='true')
        minimized = true;
    else
        minimized = false;

    var id = 'widget_'+Math.floor(Math.random() * 10001);

    $('.'+location).append( '\
        <div id="'+id+'" data-priority="5" data-minimized="false" data-save="'+saveSettings+'" data-widget="'+widget_id+'" class="widget-box dashboard-widget transparent">\
            <div class="widget-header  widget-header-flat">\
                <h4>Loading...</h4>\
                <span class="widget-toolbar">\
                    <i class="fa fa-spin fa-circle-o-notch "></i>\
                    \<a href="#">\
                        <i class="fa fa-refresh refresh_widget"></i>\
                    </a>\
                    <a href="#">\
                        <i class="fa fa-arrows drag_handle"></i>\
                    </a>\
                    <a href="#">\
                        <i class="fa fa-chevron-up minimize_widget"></i>\
                    </a>\
                    <a href="#" >\
                        <i class="fa fa-remove close_widget" style="color:red"></i>\
                    </a>\
                </span>\
            </div>\
            <div class="widget-body">\
                <div class="widget-main">\
                </div>\
            </div>\
        </div>');

    if (minimized) {
        toggleWidget( $('#'+id) );
    }

    refreshWidget(id, widget_id, moveToDefault);
}

function refreshWidget(id, widget_id, moveToDefault) {

    $('#'+id+' .widget-toolbar .fa-spin').show();
    $('#'+id+' .widget-toolbar .refresh_widget').hide();


    var dataForRequest = [];

    if (widget_js_objects[id] && typeof widget_js_objects[id].dataForRequest === "function") {
        dataForRequest = widget_js_objects[id].dataForRequest();
    }

    modRequest.request(widget_id, [], dataForRequest, function(response) {

        if (response.auto_refresh) {

            if (refresh_timers[id])
                clearTimeout(refresh_timers[id]);

            refresh_timers[id] = setTimeout( function() {

                refreshWidget(id, widget_id);

            }, response.auto_refresh_interval)

        }

        if (!resources[id] && response.resources) {
              if (response.resources.css && response.resources.css.length>0) {
                  for(var i=0; i<response.resources.css.length; i++) {
                      if (!CSSExists(response.resources.css[i])) {
                          addCSSFile(response.resources.css[i])
                      }
                  }
              }

            if (response.resources.js && response.resources.js.length>0) {
                for(var i=0; i<response.resources.js.length; i++) {
                    if (!JSExists(response.resources.js[i])) {
                        addJSFile(response.resources.js[i])
                    }
                }
            }
        }

        resources[id] = true;

        $('#'+id+' .widget-main').html(response.html);
        $('#'+id+' .widget-header h4').html(response.display_name);

        if (typeof response.show_header !== 'undefined' && response.show_header===false) {
            $('#'+id+' .widget-header').hide();
        }

        if (typeof response.display_icon !== 'undefined' && response.display_icon) {
            $('#'+id+' .widget-header h4').prepend('<i class="'+response.display_icon+'"></i>');
        }

        $('#'+id+' .widget-toolbar .fa-spin').hide();
        $('#'+id+' .widget-toolbar .refresh_widget').show();

        if (response.default_priority)
            $('#'+id).data('priority', response.default_priority).prop('data-priority', response.default_priority).attr('data-priority', response.default_priority);

        if (response.resources && Array.isArray(response.resources.js)) {

            if (areRequiredScriptsLoaded(response.resources.js)) {

                if (window[response.init_js])
                    window[response.init_js](id, response);

                else if (window[response.js_object]) {

                    if (widget_js_objects[id]) {
                        widget_js_objects[id].refresh(response)
                    }
                    else {
                        widget_js_objects[id] = new window[response.js_object]();
                        widget_js_objects[id].init(id, response)
                    }


                }

            }
            // SCRIPTS ARE NOT LOADED, WAIT FOR LOAD
            else if (response.init_js) {
                addScriptLoadedCallback(response.resources.js, response.init_js, id, response);
            }
            else if (response.js_object) {
                addScriptLoadedObjectCallback(response.resources.js, response.js_object, id, response);
            }

        }
        else
        {
            if (window[response.init_js])
                window[response.init_js](id, response);

            else if (window[response.js_object]) {

                if (widget_js_objects[id]) {
                    widget_js_objects[id].refresh(response)
                }
                else {
                    widget_js_objects[id] = new window[response.js_object]();
                    widget_js_objects[id].init(id, response)
                }

            }
        }


        if (moveToDefault && response.default_location) {

            var existing_widgets = $( '.dashboard-widget', '.'+response.default_location );

            if (existing_widgets.length==0) {

                $('#'+id).appendTo('.'+response.default_location);

            }
            else
            {
                var was_placed = false;
                existing_widgets.each( function() {

                    var priority = $(this).data('priority');

                    if ( priority<response.default_priority ) {
                        $('#'+id).insertBefore($(this));
                        was_placed = true;
                        return false;
                    }

                })

                if (!was_placed) {
                    $('#'+id).appendTo('.'+response.default_location);
                }
            }


            saveSettings();

        }



    }, function(response) {

        $('#'+id+' .widget-main').html('An error occurred while loading this widget.');
        $('#'+id+' .widget-header h4').html('Error');

        $('#'+id+' .widget-toolbar .fa-spin').hide();
        $('#'+id+' .widget-toolbar .refresh_widget').show();

    });
}


function saveSettings() {


    var settings = [];

    $('.dashboard-widget').each( function() {

        if ( $(this).data('save') ) {

            settings.push({ 'widget_id': $(this).data('widget'), 'location': $(this).parent().data('location'), 'minimized': $(this).data('minimized') })

        }

    });

    modRequest.request('sa.dashboard.save_settings', null, settings, function() {

    });

}

function addJSFile(file) {
    var tag = document.createElement('script');
    tag.src = file;
    tag.onload = function() { loadedScript(file) };
    document.getElementsByTagName('head')[0].appendChild(tag);
}

function JSExists(file) {

    var list = document.getElementsByTagName('script');
    var i = list.length, exists = false;
    while (i--) {
        if (list[i].src === file) {
            exists = true;
            break;
        }
    }

    return exists;
}


function addCSSFile(file) {
    var tag = document.createElement('link');
    tag.href = file;
    tag.rel = 'stylesheet';
    document.getElementsByTagName('head')[0].appendChild(tag);
}

function CSSExists(file) {

    var list = document.getElementsByTagName('link');
    var i = list.length, exists = false;
    while (i--) {
        if (list[i].rel=='stylesheet' && list[i].href== file) {
            exists = true;
            break;
        }
    }

    return exists;
}