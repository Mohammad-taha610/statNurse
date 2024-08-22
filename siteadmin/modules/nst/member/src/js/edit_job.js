
window.addEventListener('load', function() {

    $("#submit").click(function (event) {
        event.preventDefault();
        var errors = [];
        $(".errors").html("");
        var kit = $("input[name=name]").val();
        var is_active = $("#is_active").val();
        var description = $("textarea[name=description]").val();

        if (kit == "") {
            errors.push("Please enter Kit name");
        }
        if (is_active == "" || is_active == null) {
            errors.push("Please select Kit status");
        }
        if (description == "" || description == null) {
            errors.push("Please enter description ");
        }

        if (errors.length === 0) {
            $(".errors").html("");
            $(".errors").hide();
            // $("submit").submit();
            $('#btnclick').trigger('click');
        } else {

            $(".errors").show();
            $.each(errors, function (index, value) {
                $(".errors").append(value + "<br>");

            });
        }

    })
};