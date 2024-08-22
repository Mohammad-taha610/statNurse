<script>

    $(document).ready( function() {

        $('table > tbody > tr').each( function() {

            var route = $(':nth-child(3n)', this).html();
            route = route.replace(/(\[.*?\](\{.*?\}){0,})/gi, '<a href="#" class="param" title="$1">{param}</a>');
            route = route.replace(/^\^+|\$+$/gm,'');

            $(':nth-child(3n)', this).html(route);

        })

        $('.param').click( function(e) {
            e.preventDefault();


            $(this).html( $(this).attr('title'));
        })

    })

    function showSARoutes() {

        $('table > tbody > tr').each(function () {

            if ($(':nth-child(5n)', this).text() == 'sa\\application\\saRoute')
                $(this).show();
            else
                $(this).hide();
        })

    }

    function showRoutes() {

        $('table > tbody > tr').each(function () {

            if ($(':nth-child(5n)', this).text() == 'sa\\application\\route')
                $(this).show();
            else
                $(this).hide();

        })

    }

    function showResourceRoutes() {

        $('table > tbody > tr').each(function () {

            if ($(':nth-child(5n)', this).text() == 'sa\\application\\resourceRoute' || $(':nth-child(5n)', this).text() == 'sa\\application\\staticResourceRoute')
                $(this).show();
            else
                $(this).hide();

        })

    }

</script>
<button onclick="showRoutes()" class="btn btn-info">Show Only Routes</button>
<button onclick="showSARoutes()" class="btn btn-info">Show Only SA Routes</button>
<button onclick="showResourceRoutes()" class="btn btn-info">Show Resource Routes</button>
<br />