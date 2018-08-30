<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="//aui-cdn.atlassian.com/aui-adg/5.9.12/css/aui.min.css" media="all">
    <link rel="stylesheet" href="{{asset('css/app.css', true)}}" media="all">    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <script>
        var JiraSync = {!!
        json_encode([

            'jwt' => \Request::get('jwt-token'),
            'user' => $user->name,
            'jiraUrl' => $tenant->baseUrl

        ])
    !!};
    </script>
</head>
<body>

<div id="app">
    
</div>

<script id="connect-loader" data-options="sizeToParent:true;">
    (function() {
        var getUrlParam = function (param) {
            var codedParam = (new RegExp(param + '=([^&]*)')).exec(window.location.search)[1];
            return decodeURIComponent(codedParam);
        };

        var baseUrl = getUrlParam('xdm_e') + getUrlParam('cp');
        var options = document.getElementById('connect-loader').getAttribute('data-options');

        var script = document.createElement("script");
        script.src = baseUrl + '/atlassian-connect/all.js';

        if(options) {
            script.setAttribute('data-options', options);
        }

        document.getElementsByTagName("head")[0].appendChild(script);
    })();
</script>

<!-- Scripts -->
<script src="{{ asset('js/app.js', true) }}"></script>

</body>
</html>