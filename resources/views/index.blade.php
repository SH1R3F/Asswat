<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://fonts.googleapis.com/css?family=Cairo|Monoton|Righteous&display=swap" rel="stylesheet">
    <title>{{ env('APP_NAME') }}</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/recorderjs/0.1.0/recorder.js"></script>
    <script src="//cdn.jsdelivr.net/npm/alertifyjs@1.12.0/build/alertify.min.js"></script>
    <link rel="stylesheet" href="//cdn.jsdelivr.net/npm/alertifyjs@1.12.0/build/css/alertify.min.css" />
    <script src="https://voicechanger.io/voicemaker/js/jungle.js"></script>
    <script src="{{ asset('js/app.js') }}" defer></script>
</head>

<body>
    <div id="app">
        <router-view />
    </div>
</body>

</html>
