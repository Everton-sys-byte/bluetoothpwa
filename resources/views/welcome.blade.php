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
            document.addEventListener('DOMContentLoaded', function() {
                document.getElementById('btn-download-apk').style.display = 'none';
            });
        }
    </script>

    <script src="https://js.pusher.com/8.4.0/pusher.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>

<body>
    <div class="container-full d-flex justify-content-center align-items-center w-100 p-0" style="height: 100dvh">
        <div class="row">
            <div class="col-lg-4 col-12 d-flex flex-column bg-sucess">
                <!-- Botão de abrir câmera -->
                <label for="camera-input" class="btn btn-primary">Acessar Câmera</label>
                <input type="file" accept="image/*" capture="environment" id="camera-input" style="display: none;" />

                <a class="btn btn-primary mt-2" id="btn-bluetooth" href="">Procurar beacons (Bluetooth)</a>
                <a class="btn btn-primary mt-2" id="btn-download-apk"
                    href="{{ asset('downloads/Bluetooth.apk') }}">Download
                    .APK (TWA)</a>
                <a class="btn btn-success mt-2" href="beacons://?tenant_id=3&user_email=evertonlook2010@gmail.com"
                    target="_blank">Abrir o app Beacons</a>
            </div>
            <div class="col-lg-8 col-12">
                <div class="card mt-2 mt-lg-0">
                    <div class="card-header">
                        Beacons Encontrados
                    </div>
                    <div class="card-body">
                        <div class="beacons">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>



<script>
    const pusher = new Pusher('{{ config('broadcasting.connections.pusher.key') }}', {
        cluster: 'us2'
    })

    const channel = pusher.subscribe('public')

    // mensagens recebidas (listener)
    channel.bind('beaconScanning', function(data) {
        const beacon = data.scannedBeacon
        console.log(beacon)

        if (!beacon || !beacon.id)
            return

        const id = beacon.id

        const $container = $(".beacons");

        let html = "";

        // mostra informações de forma dinamica
        Object.entries(beacon).forEach(([key, value]) => {
            html += `<strong>${key}:</strong>${value ?? "---"}<br>`;
        })

        html+= "<hr>"
        // substitui ":" por "_" para usar como id válido no DOM
        // tenta selecionar um elemento que tenha esse id
        let $el = $("#" + id.replace(/:/g, "_"))

        // se tem elemento com esse id ele é atualizado
        if ($el.length > 0) {
            $el.html(html); // atualiza se já existir
        }else {
            // cria um novo elemento com ID único baseado no beacon ID se não existir
            $el = $(`<div class="beacon" id="${id.replace(/:/g, "_")}"></div>`).html(html);
            $container.append($el);
        }
    })

    $(document).ready(function() {

        $('#btn-bluetooth').on('click', async function() {
            if (!navigator.bluetooth) {
                alert('Seu navegador não suporta Web Bluetooth.');
                return;
            }

            try {
                const device = await navigator.bluetooth.requestDevice({
                    acceptAllDevices: true,
                    optionalServices: [
                        'battery_service'
                    ] // Pode mudar conforme o dispositivo
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
