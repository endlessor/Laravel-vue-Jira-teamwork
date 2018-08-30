<!DOCTYPE html>
<html>
<head>
    <title>Page Title</title>
</head>
<body>

<pre id="container"></pre>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>

<script type="text/javascript">
    var SYNC_QUEUE = {!! json_encode($syncdata, JSON_PRETTY_PRINT) !!};
</script>

<script type="text/javascript">

    function writeln(message) {
        document.getElementById('container').innerHTML += message + '\n';
    }

    var execQueue = [];
    function scheduleSync(ticketData) {
        var span = $(document.createElement('span'));
        var statusSpan = $(document.createElement('span'));

        var statusId = 'status' + execQueue.length;

        statusSpan.attr('id', statusId);
        statusSpan.html('o');

        $(span).append(statusSpan);
        $(span).append('<span> Ticket ' + ticketData.id + ': ' + ticketData.summary + '\n</span>');

        $('#container').append(span);

        execQueue.push(function() {

            $.getJSON({
                url: ticketData.action
            })
                .done(
                    function(res) {
                        console.log(res);
                        $('#' + statusId).html('✔');
                    }
                )
                .fail(
                    function(e) {
                        console.log(e);
                        $('#' + statusId).html('✗');
                    }
                )
                .always(
                    function() {
                        setTimeout(
                            function() {
                                execNextQueueItem();
                            }, 1000);
                    }
                )
            ;

        });
    }

    function execNextQueueItem() {
        if (execQueue.length > 0) {
            (execQueue.shift())();
        }
    }

    for (var projectId = 0; projectId < SYNC_QUEUE.projects.length; projectId ++) {

        var projectData = SYNC_QUEUE.projects[projectId];
        writeln('Syncing ' + projectData.name);

        for (var linkId = 0; linkId < projectData.links.length; linkId ++) {

            var linkData = projectData.links[linkId];
            for (var ticketId = 0; ticketId < linkData.tickets.length; ticketId ++) {

                var ticketData = linkData.tickets[ticketId];
                scheduleSync(ticketData);

            }
        }

        writeln('');
    }

    execNextQueueItem();

</script>

</body>
</html>