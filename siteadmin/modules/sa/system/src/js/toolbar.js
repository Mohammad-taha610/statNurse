var toolbar = null;

$(document).ready(function(){
    toolbar = new ToolbarManager;
    toolbar.init();
    toolbar.resizeTabContent();
});

$( window ).resize(function() {
    toolbar.resizeTabContent();
});


var ToolbarManager = function() {

    this.expanded = [];
    this.frame = null;
    this.modules = {};
    this.frame_window = null;

    this.init = function() {

        $("#toggleToolbar").click(this.toggleToolbar);
        $("#resize_phone").click(this.resizeToPhone);
        $("#resize_tablet").click(this.resizeToTablet);
        $("#resize_tablet_hor").click(this.resizeToTabletHorizontal);
        $("#resize_desktop").click(this.resizeToDesktop);
        $("#exitToolbar").click(this.exitToolbar);
        $("#refreshFrame").click(this.refreshFrame);
        $("#toggleToolbarClose").click(this.toggleToolbar);

        var tbinstance = this;

        $('.page_tree_container .tree_body').on('click', '.expand_pages, .collapse_pages', function (e) {
            e.preventDefault();
            tbinstance.expandCollapsePages($(this));
        })

        $('.page_tree_container .tree_body').on('click', '.page_active_toggle', function (e) {
            e.preventDefault();
            var url = '/siteadmin/page_editor/page/' + $(this).data('page_id') + '/active_state_toggle';
            $.post(url);
            if ($(this).hasClass('page_active')) {
                $(this).removeClass('page_active').addClass('page_inactive');
            }
            else {
                $(this).removeClass('page_inactive').addClass('page_active');
            }
        });

        modRequest.request('toolbar.tabs', [], [], function(data) {

            for( var i=0; i<data.length; i++) {

                for( var a=0; a<data[i].files.length; a++) {

                    if (data[i].files[a].type=='css')
                        $('<link>', { rel : 'stylesheet', href : data[i].files[a].file }).appendTo( $('head') );
                    else if (data[i].files[a].type=='js')
                        $('<script />', {type: 'text/javascript', src: data[i].files[a].file }).appendTo('head');
                }


                var Constructor = window[data[i].name];
                tbinstance.addModule(data[i].name, new Constructor());

            }

        });

        this.loadPages(0, 0);
    }

    this.exitToolbar = function()
    {
        //window.top.location.href = "http://staging.elinkdesign.com/siteadmin/";
        window.top.location.href = _siteadmin_url;
    }

    this.addModule = function(name, module) {
        this.modules[ name ] = module;
        module.toolbar = this;
        module.init();
    }

    this.refreshFrame = function()
    {
        parent.frames['frame'].location.reload();
    }

    this.toggleToolbar = function()
    {
        $("#display_views").toggleClass("hide");
        $("#peToolbar").toggleClass("col-fixed-none");
        $("#peToolbarSite").toggleClass("col-offset-none");

        if ($('#toggleToolbar').hasClass("hide")) {
            setTimeout(
                function()
                {
                    $("#toggleToolbar").toggleClass("hide");
                }, 1000);

        }else{
            $("#toggleToolbar").toggleClass("hide");
        }
        /*
         if ($('#toggleToolbar').html()=='<i class="ace-icon fa fa-arrow-circle-left"></i>') {
         $('#toggleToolbar').html('<i class="fa fa-arrow-circle-left"></i>');
         $("#toggleToolbar").toggleClass("hide");
         }
         else {
         //$('#toggleToolbar').html('<i class="fa fa-arrow-circle-right"></i>');

         //$("#toggleToolbar").delay( 2400 ).toggleClass("hide").fadeIn( 400 );
         }*/
    }

    this.resizeToTablet = function() {
        $(".display_icon").removeClass("display_icon_active");
        $("#resize_tablet").addClass("display_icon_active");
        $('#frame').css('width', '768px');
        $('#frame').css('height', '1024px');
        $('#frame').css('margin-top', '0');
        $('#frame').css('margin-left', '80px');
    }

    this.resizeToTabletHorizontal = function() {
        $(".display_icon").removeClass("display_icon_active");
        $("#resize_tablet_hor").addClass("display_icon_active");
        $('#frame').css('width', '1024px');
        $('#frame').css('height', '768px');
        $('#frame').css('margin-top', '0');
        $('#frame').css('margin-left', '80px');
    }

    this.resizeToPhone = function() {
        $(".display_icon").removeClass("display_icon_active");
        $("#resize_iphone6").addClass("display_icon_active");
        $('#frame').css('width', '375px');
        $('#frame').css('height', '667px');
        $('#frame').css('margin-top', '0');
        $('#frame').css('margin-left', '80px');
        $('#frame').css('top', '100');
        $('#frame').css('left', '100');
    }

    this.resizeToDesktop = function() {
        $(".display_icon").removeClass("display_icon_active");
        $("#resize_desktop").addClass("display_icon_active");
        $('#frame').css('width', '100%');
        $('#frame').css('height', '100vh');
        $('#frame').css('margin-top', '0');
        $('#frame').css('margin-left', '0');
        $('#frame').css('top', '0');
        $('#frame').css('left', '0');
    }

    this.updateFrame = function(iframe) {

        this.frame = window.frames["frame"].document;
        this.frame_window = window.frames["frame"];
        if ($('meta[name="cms_toolbar_file"]', window.frames["frame"].document).length>0) {
            var file = $('meta[name="cms_toolbar_file"]', window.frames["frame"].document).attr('content');
            if ( $('script[src="'+file+'"]').length == 0 )
            {
                var loadscript = $('<script />', {type: 'text/javascript', src: file});
                $('head').append(loadscript);
            }

            var className = $('meta[name="cms_toolbar_file"]', window.frames["frame"].document).attr('property');
            var Constructor = window[className];
            this.addModule(className, new Constructor());
        }

        for( mod in this.modules) {

            try {
                this.modules[mod].frameLoadComplete();
            }
            catch(e) {

            }
        }

    }

    this.createTree = function (pages, parent_id, level) {

        var html = '';

        if (parent_id == 0) {
            $('.page_tree_container .tree_body').html('');
        }
        else {
            $('.page_tree_container .row[data-parent_id="' + parent_id + '"][data-level="' + level + '"]').remove();
        }

        if (pages.length == 0) {

            $('.page_tree_container .tree_body').append('<div class="row"><div class="col-md-12">There are no pages available</div></div>');
        }

        for (var i = 0; i < pages.length; i++) {

            var page = pages[i];

            var namePadding = (15 * level) + 12;

            var row = '<div class="row level' + level + '" data-parent_id="' + parent_id + '" data-page_id="' + page.id + '" data-level="' + level + '"><div class="col-xs-12 col-sm-12 col-lg-12 col-md-12 name" >';

            //row += '<div class="drag_handle">||</div>';

            row += '<div class="tree_image"></div>';

            if (page.subpages_count > 0 && page.subpages.length == 0) {
                row += '<a href="#" class="expand_pages expand_collapse_button" data-page_id="' + page.id + '" data-level="' + level + '"><i class="fa fa-plus-square-o"></i></a>';
            } else if (page.subpages_count > 0 && page.subpages.length > 0) {
                row += '<a href="#" class="collapse_pages expand_collapse_button" data-page_id="' + page.id + '" data-level="' + level + '"><i class="fa fa-minus-square-o"></i></a>';
            } else {
                row += '<span class="expand_collapse_button"><i class="fa fa-square-o"></i></span>';
            }

            row += '<a href="' + page.route_full + '" target="frame" class="pagelink" title="Edit Page Content">';
            row += page.name;
            row += '</a>';
            row += '<div class="pagelinkedit"><a href="'+page.edit_modal_url+'" data-samodal="true" data-samodal_type="frame" data-samodal_width="1024"><i class="fa fa-pencil bigger-120"></i></a></div>';

            row += '</div></div>';

            html += row;
        }

        html += '';

        if (parent_id == 0) {
            $('.page_tree_container .tree_body').append(html);
        }
        else {
            var parent_row = $('.page_tree_container .tree_body .row[data-page_id="' + parent_id + '"]');
            parent_row.after(html);
        }

        for (var i = 0; i < pages.length; i++) {

            var page = pages[i];

            if (page.subpages.length > 0) {
                this.createTree(page.subpages, page.id, level + 1);
            }


        }

        $('.pe_tree_loading').hide();
    }

    this.loadPages = function(parent_id, level) {

        $('.pe_tree_loading').show();

        var data = '';

        for (var x in this.expanded) {
            data += '&expanded[]=' + this.expanded[x];
        }

        var tbinstance = this;

        $.ajax({

            dataType: 'json',
            url: '/siteadmin/page_editor/pages/ajax/list?parent=' + parent_id,
            data: data,
            type: 'POST',
            success: function (data) {
                tbinstance.createTree(data.pages, parent_id, level);
                tbinstance.resizeTabContent();
            }
        });

    }

    this.expandCollapsePages = function(element, forceClose, forceReload) {

        var parent_id = $(element).data('page_id');
        var tbinstance = this;

        if ($(element).hasClass('expand_pages') && !forceClose) {

            this.expanded.push(parent_id);
            this.expanded = jQuery.unique(this.expanded);

            if ($('.page_tree_container .row[data-parent_id="' + parent_id + '"]').length > 0 && !forceReload) {
                $('.page_tree_container .row[data-parent_id="' + parent_id + '"]').show();
            }
            else {
                this.loadPages(parent_id, $(element).data('level') + 1);
            }

            $(element).html('<i class="fa fa-minus-square-o"></i>')
            $(element).removeClass('expand_pages');
            $(element).addClass('collapse_pages')

        }
        else {

            $(element).html('<i class="fa fa-plus-square-o"></i>')

            for (var x in this.expanded) {

                if (this.expanded[x] == parent_id) {
                    delete this.expanded[x];
                }

            }

            $('.page_tree_container .row[data-parent_id="' + parent_id + '"]').each(function () {
                $(this).hide();

                tbinstance.expandCollapsePages($('.collapse_pages', this), true);
            })

            $(element).addClass('expand_pages');
            $(element).removeClass('collapse_pages')

        }

    }

    this.addTab = function(id, name, content, clear, callback, stickytab, auto_select) {

        if (clear) {
            this.clearTabs();
        }

        if (typeof(auto_select)=='undefined')
            auto_select = true;

        $('#tabs').append('<li class="'+(stickytab ? 'sticky' : '')+'">\
                                <a href="#tab_'+id+'" id="tab_selector_'+id+'" data-toggle="tab" aria-expanded="true">\
                                '+name+'\
                                </a>\
                            </li>');

        $('.tab-content').append('<div class="tab-pane fade '+(stickytab ? 'sticky' : '')+'" id="tab_'+id+'">'+content+'</div>');

        if (auto_select)
            $('#tab_selector_'+id).click();

        try {
            callback();
        }
        catch(e) {
            console.log(e);
        }
    }

    this.clearTabs = function() {
        $('.tab-pane:not(.sticky)').remove();
        $('#tabs li:not(.sticky)').remove();
        $('#tab_selector_site').click();
    }

    this.showMessage = function(type, header, message) {
        $.growl[type]({ title: header, message: message, size:"large" });
    }

    this.resizeTabContent = function()
    {
        // adjust height of tab content
        $('.tab-content').height($('.tabbable').height() - $('#tabs').height() - 32);
    }

}