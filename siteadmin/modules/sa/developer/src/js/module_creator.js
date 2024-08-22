$(document).ready( function() {


    $('.add-property').click( function() {

        addProperty();

    })


})

function addProperty(index, name, type) {

    if ( typeof(index) == 'undefined')
        index = $('.property-row').length;

    if ( typeof(name) == 'undefined')
        name = '';

    if ( typeof(type) == 'undefined')
        type = '';


    var html ='<div class="row property-row">\
            <div class="col-md-6">\
                <div class="form-group">\
                    <label class="control-label no-padding-right">Name</label>\
                    <input type="text" placeholder="Property Name" name="entity_property['+index+'][name]" value="'+name+'" class="form-control">\
                </div>\
            </div>\
            <div class="col-md-6">\
                <div class="form-group">\
                    <label class="control-label no-padding-right">Type</label>\
                    <select name="entity_property['+index+'][type]" class="form-control">';

    for(var x in types) {
        html +='<option '+(type==types[x] ? 'selected' : '')+' value="'+types[x]+'">'+types[x]+'</option>';
    }


    html +='</select>\
                </div>\
            </div>'

    $('.property-info').append(html)

}