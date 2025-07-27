<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title')</title>
    <style>
        body {
            font-size: 16px;
            margin: 5px;
        }

        h1 {
            font-size: 60px;
            color: white;
            text-shadow: 1px 0 5px #289ADC;
            letter-spacing: -4px;
            margin-left: 10px
        }

        .content {
            margin: 10px;
        }
    </style>
</head>

<body>
    <h1>@yield('title')</h1>
    <div class="content">
        @yield('content')
    </div>
</body>

</html>