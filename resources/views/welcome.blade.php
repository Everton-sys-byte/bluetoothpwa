<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Bluetooth</title>

    <link rel="manifest" href="{{ asset('manifest.json') }}" />
    <script>
        if (typeof navigator.serviceWorker !== 'undefined') {
            navigator.serviceWorker.register("{{ asset('pwabuilder-sw.js') }}")
        }
    </script>
</head>

<body>
    <p>oi</p>
</body>

</html>
