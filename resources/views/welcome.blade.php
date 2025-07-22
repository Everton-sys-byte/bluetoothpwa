<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">

    <title>Bluetooth</title>

    <link rel="manifest" href="{{ asset('manifest.json') }}" />
    <script>
        if (typeof navigator.serviceWorker !== 'undefined') {
            navigator.serviceWorker.register("{{ asset('pwabuilder-sw.js') }}")
        }
    </script>
</head>

<body>
    <div class="container-full d-flex flex-column align-items-center justify-content-center" style="height: 100vh">
        <a class="btn btn-primary" href="{{ asset('downloads/Bluetooth.apk') }}">Download .APK</a>
    </div>
</body>

</html>
