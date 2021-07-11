<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>SMART IPG</title>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,600" rel="stylesheet">

    <!-- Styles -->
    <style>
        html, body {
            background-color: #fff;
            color: #636b6f;
            font-family: 'Nunito', sans-serif;
            font-weight: 200;
            height: 100vh;
            margin: 0;
        }

        .full-height {
            height: 100vh;
        }

        .flex-center {
            align-items: center;
            display: flex;
            justify-content: center;
        }

        .position-ref {
            position: relative;
        }

        .top-right {
            position: absolute;
            right: 10px;
            top: 18px;
        }

        .content {
            text-align: center;
        }

        .title {
            font-size: 96px;
            text-shadow: 1px 1px 1px #000, 3px 3px 5px gray;
        }

        .links > a {
            color: #636b6f;
            padding: 0 25px;
            font-size: 13px;
            font-weight: 600;
            letter-spacing: .1rem;
            text-decoration: none;
            text-transform: uppercase;
        }

        .m-b-md {
            margin-bottom: 30px;
        }

        body {
            min-height: 100vh;
            margin: 0;
            font-family: Avenir, sans-serif;
        }

    </style>
</head>
<body>
<div class="flex-center position-ref full-height">
    <div class="content">
        <div class="title m-b-md">
            SMART IPG
        </div>

        {{--        <div class="links">--}}
        {{--            <a href="https://laravel.com/docs"></a>--}}
        {{--            <a href="https://laracasts.com"></a>--}}
        {{--        </div>--}}
    </div>
</div>
<script>
    var setBackground = function () {
        var svgstring = '<svg id="diagtext" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="100%" height="100%"><style type="text/css">text { fill: lightgray; font-family: Avenir, Arial, Helvetica, sans-serif; }</style><defs><pattern id="twitterhandle" patternUnits="userSpaceOnUse" width="400" height="200"><text y="30" font-size="40" id="name">IPG</text></pattern><pattern xlink:href="#twitterhandle"><text y="120" x="200" font-size="40" id="occupation">SMART</text></pattern><pattern id="combo" xlink:href="#twitterhandle" patternTransform="rotate(-45)"><use xlink:href="#name" /><use xlink:href="#occupation" /></pattern></defs><rect width="100%" height="100%" fill="url(#combo)" /></svg>';
        document.body.style.backgroundImage = "url('data:image/svg+xml;base64," + window.btoa(svgstring) + "')";
    };
    setBackground();
</script>
</body>
</html>
