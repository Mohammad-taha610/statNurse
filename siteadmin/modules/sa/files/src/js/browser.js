var currentFile = null;
var offset = 0;
var loading = false;
var loadedAll = false;


$(document).ready( function() {
    $('#folder_selection').change( function() {
        $('#delete').hide();
        $('#folder').val( $('#folder_selection').val() );

        loadedAll = false;

        loadFileTree( $('#folder_selection').val(), true );
    });

    $('.show_search').click( function() {
        $('#search_container').show();
        $('#buttons_container').hide();
    });

    $('.show_create_folder').click( function() {
        $('#create_folder_container').show();
        $('#buttons_container').hide();
    });

    $('.close_search').click( function() {
        $('#search_container').hide();
        $('#buttons_container').show();
    });

    $('.search_now').click( function() {
        var folder_name = $('#folder_selection').val();
        var search_term = $('#search_name').val();
        loadFileTree( folder_name, true, search_term );
    });

    $('.save_folder').click( function() {
        var new_folder_name = $('#new_folder_name').val();

        if (new_folder_name != '') {
            $('#folder').val(new_folder_name);
            $('#folder_selection').append('<option val="' + new_folder_name + '">' + new_folder_name + '</option>');
            $('#folder_selection').val(new_folder_name);
            loadFileTree( new_folder_name, true );
        }

        $('#create_folder_container').hide();
        $('#buttons_container').show();
    });

    $('#select').click( function() {
        if (currentFile === null) {
            alert('You must select a file before continuing.');
        }
        else {
            if ( $('#return').val()=='id') {
                try {
                    window.opener.fileBrowserSelectCallBack(currentFile.id);
                    window.close();
                } catch(e) {
                    alert('fileBrowserSelectCallBack function is not defined on the parent window.');
                }

            } else if ( $('#return').val()=='object') {
                try {
                    var nodeInfo = $('#file-tree').jstree(true).get_node('#' + currentFile.id);
                    window.opener.fileBrowserSelectCallBack($('#' + nodeInfo.id).data());
                    window.close();
                } catch(e) {
                    alert('fileBrowserSelectCallBack function is not defined on the parent window.');
                }
            } else {
                try {
                    var funcNum = getUrlParam( 'CKEditorFuncNum' );
                    var path = currentFile['data-filepath'];
                    window.opener.CKEDITOR.tools.callFunction( funcNum, path );
                    window.close();
                } catch(e) {
                    alert('Something is wrong with CKEditor on the parent window.');
                }
            }
        }
    });

    $('#close').click( function() {
        window.close();
    });

    $('.uploadaliasbutton').click( function() {
        $('#uploadfilebutton').click();
    });

    var folder = $('#folder').val();

    $('#uploadfilebutton').fileupload({
        url: '/siteadmin/files/browse/upload?folder='+folder,
        dataType: 'json',
        maxChunkSize: 1950000,
        dataType: "json",
        add: function(e, data) {
            data.url = '/siteadmin/files/browse/upload?folder=' + $('#folder').val();
            data.submit();
        },
        start: function (e, data) {
            $('#progress').show();
            $('#progress .progress-bar').css(
                'width',
                '0%'
            ).hide();
        },
        done: function (e, data) {
            var progressBar = $('#progress');

            $(progressBar).hide();
            $(progressBar).find('.progress-bar').css(
                'width',
                '0%'
            ).hide();

            $('#no-files-msg').hide();

            $.growl.notice({ title: "File Uploaded", message: "The file was successfully uploaded", size: "large" });

            var tree = $('#file-tree').jstree(true);
            var treeData = createTreeData(data.result.files);

            if(!tree) {
                initTree(treeData);
            } else {
                tree.settings.core.data = treeData;
                tree.refresh();
            }
        },
        progressall: function (e, data) {
            var progressBar = $('#progress');
            var progress = parseInt(data.loaded / data.total * 100, 10);

            $(progressBar).find('.progress-bar').css(
                'width',
                progress + '%'
            ).show();
        },
        fail: function(e, data) {
            var progressBar = $('#progress');

            $.growl.error({ title: "File Upload Error", message: "There was an error uploading the file.", size:"large" });

            $(progressBar).find('.progress-bar').css(
                'width',
                '0%'
            ).hide();
        }
    }).prop('disabled', !$.support.fileInput).parent().addClass($.support.fileInput ? undefined : 'disabled');

    var currentItem = $('.tree-item.current');

    if (currentItem.length > 0) {
        loadFileInfo( $('.tree-item.current') );
        currentFile = $('.tree-item.current');
    }

    $('#file-tree').scroll(function(){
        var scrollHeight = $(this)[0].scrollHeight;
        var scrollTop = $(this).scrollTop();
        var elementHeight = $(this).height();
        var position = scrollHeight - scrollTop - elementHeight;

        if (position < 25 && loading == false) {
            loadFileTree( $('#folder_selection').val() );
        }
    });

    loadFileTree( $('#folder_selection').val(), true );

    $('#delete-btn').click(function() {
        confirm("<strong><span style='color: red;'>WARNING:</span></strong><br><br> Deleting these files will remove <strong>ALL VARIATIONS</strong> available for this file! Deleting will make the files inaccessible. Are you sure you wish to delete the files?", function() {
            var deleteContainer = $('#delete');
            var checkedIds = getCheckedParentNodeIds();

            if(checkedIds.length > 0) {
                modRequest.request('sa.files.delete', null, { files: checkedIds }, function(response) {
                    if(response.data.containsFailedFiles) {
                        $.growl.error({ title: 'File Delete Error', message: 'Some files could not be deleted because they are in use.', size:'large' });
                    } else {
                        $.growl.notice({ title: "Files Deleted", message: 'Files Deleted Successfully', size: 'large' });
                    }

                    $(deleteContainer).hide();

                    loadFileTree( $('#folder_selection').val(), true );
                }, function(e) {
                    $.growl.error({ title: 'File Delete Error', message: 'Oops! Something went wrong deleting selected files. Please try again.', size:'large' });
                });
            } else {
                $(deleteContainer).hide();
            }
        });
    });
});

function loadFileTree(folder, reset, search_term) {
    if (reset) {
        offset = 0;
        $('#fileinfo').hide();
        loadedAll = false;
    }

    if (loadedAll) {
        return;
    }

    loading = true;

    var tree = $('#file-tree').jstree(true);

    $('#folder').val( $('#folder_selection').val() );

    modRequest.request('sa.files.list', null, {
        'offset': offset,
        'folder': folder,
        'prependpath' : $('#prependpath').val(),
        'search': search_term

    }, function(data) {
        if(data.files.length === 0) {
            loadedAll = true;

            if(reset && tree) {
                tree.settings.core.data = [];
                tree.refresh();
            }

            if(reset) {
                $('#no-files-msg').show();
            }

            return;
        } else {
            $('#no-files-msg').hide();
        }

        if (data.files.length === 0 && tree) {
            tree.settings.core.data = [];
            tree.refresh();

            return;
        }

        var treeData = createTreeData(data.files);

        if(!reset && tree) {
            treeData = tree.settings.core.data.concat(treeData);
        }

        if(!tree) {
            initTree(treeData);
        } else {
            tree.settings.core.data = treeData;
            tree.refresh();
        }

        offset += 30;
    });
}

function loadFileInfo( filename, filesize, filepath ) {
    $('#filename').html( '<strong>File Name: </strong>'+ filename );
    $('#filesize').html( '<strong>File Size: </strong>'+ bytesToSize(filesize) );
    $('#filepath').html( '<strong>File Path: </strong><a target="_blank" href="'+ filepath +'">'+ filepath +'</a>' );
    $('#clipboard-success').hide();
    $('#clipboard').html('<button onclick="setClipboard(\'' + (siteUrl + filepath) + '\')" class="btn btn-success btn-xs">Copy Link</button>');

    var fileInfoContainer = $('#fileinfo');

    if (!filename) {
        $(fileInfoContainer).hide();
        return;
    }
    var parts = filename.split('.');

    var image = [ 'jpeg', 'jpg', 'png', 'gif' ];

    if ( image.indexOf( parts[parts.length-1] ) !== -1) {
        $('#preview').html( '<img width="150" src="'+ filepath +'" />' );
    }

    if ( parseInt(filesize) > 400000 ) {
        $('#warning').show();
    } else {
        $('#warning').hide();
    }

    $(fileInfoContainer).show();
}

function getUrlParam( paramName ) {
    var reParam = new RegExp( '(?:[\?&]|&)' + paramName + '=([^&]+)', 'i' ) ;
    var match = window.location.search.match(reParam) ;

    return ( match && match.length > 1 ) ? match[ 1 ] : null ;
}

function bytesToSize(bytes) {
    var sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
    if (bytes === 0) return '0 Byte';
    var i = parseInt(Math.floor(Math.log(bytes) / Math.log(1024)));
    return Math.round(bytes / Math.pow(1024, i), 2) + ' ' + sizes[i];
}

function createTreeData(files) {
    var treeData = [];

    for(var i = 0; i < files.length; i++) {
        var file = files[i];
        var treeItem = buildFileDataForDisplay(files[i]);

        if(file.file_variations.length > 0) {
            treeItem.children = [];

            for(var j = 0; j < file.file_variations.length; j++) {
                var childNode = buildFileDataForDisplay(file.file_variations[j]);
                treeItem.children.push(childNode);
            }
        }

        treeData.push(treeItem);
    }

    return treeData;
}

function buildFileDataForDisplay(file) {
    return {
        id: file.id,
        text: file.filename,
        icon: "blue fa " + file.icon,
        state: { opened: false },
        li_attr: {
            'data-filename': file.filename,
            'data-filesize': file.file_size,
            'data-filepath': file.filepath,
            'data-id': file.id
        }
    };
}

function initTree(treeData) {
    var fileTree = $('#file-tree');

    $(fileTree).jstree({
        core : {
            data : treeData
        },
        plugins: [ "checkbox" ],
        "checkbox": {
            tie_selection: false,
            whole_node: false
        }
    });

    $(fileTree).on('select_node.jstree', function(event, node) {
        var nodeAttr = node.node.li_attr;

        loadFileInfo(nodeAttr["data-filename"], nodeAttr["data-filesize"], nodeAttr["data-filepath"]);

        currentFile = nodeAttr;
    });

    $(fileTree).on('check_node.jstree', function(e, data) {
        var parentNodeId = data.node.parent;

        if(parentNodeId.length > 0) {
            $('#file-tree').jstree('check_node', '#' + parentNodeId);
        }

        handleCheckedNodes();
    });

    $(fileTree).on('uncheck_node.jstree', function(e, data) {
        var tree = $(fileTree).jstree(true);
        var parentNodeId = data.node.parent;

        if(parentNodeId !== '#') {
            var parentNodeObj = tree.get_node('#' + parentNodeId);
            var children = parentNodeObj.children;

            if(children.length > 0) {
                for(var i = 0; i < children.length; i++) {
                    $('#file-tree').jstree('uncheck_node', '#' + children[i]);
                }
            }
        }

        handleCheckedNodes();
    });

    $(fileTree).on('ready.jstree refresh.jstree', function(e, data) {
        loading = false;
    });
}

function handleCheckedNodes() {
    var deleteContainer = $('#delete');
    var checkedIds = getCheckedParentNodeIds();

    if(checkedIds.length > 0) {
        $(deleteContainer).show();
        $(deleteContainer).find('#files-selected').html(checkedIds.length + ' file' + (checkedIds.length > 1 ? 's' : '') + ' selected');
    } else {
        $(deleteContainer).hide();
    }
}

function getCheckedParentNodeIds() {
    var checkedParentNodeIds = [];
    var tree = $("#file-tree").jstree(true);
    var checked = tree.get_checked();

    if(checked.length > 0) {
        for(var i = 0; i < checked.length; i++) {
            var nodeObj = tree.get_node(checked[i]);

            if(tree.get_parent(nodeObj) === '#') {
                checkedParentNodeIds.push(checked[i]);
            }
        }
    }

    return checkedParentNodeIds;
}

function setClipboard(value) {
    var tempInput = document.createElement("input");
    tempInput.style = "position: absolute; left: -1000px; top: -1000px";
    tempInput.value = value;
    document.body.appendChild(tempInput);
    tempInput.select();
    document.execCommand("copy");
    document.body.removeChild(tempInput);

    $('#clipboard-success').fadeIn().next().delay(500).fadeOut();
}