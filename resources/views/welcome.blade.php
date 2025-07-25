<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">

    <title>Bluetooth</title>

    <link rel="manifest" href="{{ asset('manifest.json') }}" />
    
    {{-- script relacionado a PWA (TWA) --}}
    <script>
        if (typeof navigator.serviceWorker !== 'undefined') {
            navigator.serviceWorker.register("{{ asset('pwabuilder-sw.js') }}")
        }
    </script>
    <script defer>
        // Detecta se está rodando em modo standalone (instalado como app)
        const isStandalone = window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone;

        if (isStandalone) {
            document.addEventListener('DOMContentLoaded', function () {
                document.getElementById('btn-download-apk').style.display = 'none';
            });
        }
    </script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>

<body>
    <div class="container-full d-flex flex-column align-items-center justify-content-center" style="height: 100vh">
        <!-- Botão de abrir câmera -->
        <label for="camera-input" class="btn btn-primary">Acessar Câmera</label>
        <input type="file" accept="image/*" capture="environment" id="camera-input" style="display: none;" />

        <a class="btn btn-primary mt-3" id="btn-bluetooth" href="">Bluetooth</a>
        <a class="btn btn-primary mt-3" id="btn-download-apk" href="{{ asset('downloads/Bluetooth.apk') }}">Download .APK</a>
    </div>
</body>

<script>
    $(document).ready(function () {
        $('#btn-bluetooth').on('click', async function () {
            if (!navigator.bluetooth) {
                alert('Seu navegador não suporta Web Bluetooth.');
                return;
            }

            try {
                const device = await navigator.bluetooth.requestDevice({
                    acceptAllDevices: true,
                    optionalServices: ['battery_service'] // Pode mudar conforme o dispositivo
                });

                console.log('Dispositivo encontrado:', device);
                alert(`Dispositivo: ${device.name || 'Sem nome'}\nID: ${device.id}`);

                const server = await device.gatt.connect();
                console.log('Conectado ao GATT server:', server);

                // Aqui você pode continuar lendo características do serviço, se quiser.
            } catch (error) {
                console.error('Erro ao conectar com Bluetooth:', error);
                alert('Erro: ' + error.message);
            }
        });
    });
</script>

</html>
