<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">

    <title>Bluetooth</title>

    <link rel="manifest" href="{{ asset('manifest.json') }}" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">

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
    <div class="container-full d-flex justify-content-center align-items-center w-100 px-2" style="height: 100dvh">
        <div class="row">
            <div class="col-lg-4 col-12 d-flex flex-column bg-sucess">
                <!-- Botão de abrir câmera -->
                <label for="camera-input" class="btn btn-primary">Acessar Câmera</label>
                <input type="file" accept="image/*" capture="environment" id="camera-input" style="display: none;" />

                <button class="btn btn-primary mt-2" id="btn-bluetooth">Procurar beacons (Bluetooth)</button>
                <button class="btn btn-primary mt-2" id="btn-lowenergy">Start Low Energy Scan</button>

                <a class="btn btn-primary mt-2" id="btn-download-apk"
                    href="{{ asset('downloads/Bluetooth.apk') }}">Download
                    .APK (TWA)</a>
                <a class="btn btn-primary mt-2" href="{{ route('scan.bluetooth') }}">Scan Bluetooth!</a>
                <a class="btn btn-success mt-2" href="https://bluetooth.evertonportfolio.site/openApp?user=1&beacon=10"
                    target="_blank">Abrir o app Beacons</a>
            </div>
            <div class="col-lg-8 col-12">
                <div class="card mt-2 mt-lg-0">
                    <div class="card-header">
                        Beacons Encontrados
                    </div>
                    <div class="card-body" style="max-height: 400px; overflow: auto;">
                        <div class="beacons">
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12" id="results"></div>
            <video id="preview" style="width:100%;height:auto;" autoplay></video>
            <div id="resultado"></div>
        </div>
    </div>
</body>

<script src="https://rawgit.com/schmich/instascan-builds/master/instascan.min.js"></script>
<script>
    const pusher = new Pusher('{{ config('broadcasting.connections.pusher.key') }}', {
        cluster: 'us2'
    });

    const channel = pusher.subscribe('public');

    // Listener de beacons recebidos via Pusher
    channel.bind('beaconScanning', function(data) {
        const beacon = data.scannedBeacon;
        if (!beacon || !beacon.id) return;

        const id = beacon.id;
        const $container = $(".beacons");
        let html = "";

        Object.entries(beacon).forEach(([key, value]) => {
            html += `<strong>${key}:</strong>${value ?? "---"} ${key == 'distance' ? 'M' : ''}<br>`;
        });
        html += "<hr>";

        let $el = $("#" + id.replace(/:/g, "_"));
        if ($el.length > 0) {
            $el.html(html);
        } else {
            $el = $(`<div class="beacon" id="${id.replace(/:/g, "_")}"></div>`).html(html);
            $container.append($el);
        }
    });

    const scanButton = document.getElementById('btn-bluetooth');
    const resultsDiv = document.getElementById('results'); // <-- corrigido
    const APPLE_COMPANY_ID = 0x004C;

    // ---- Botão principal para scan com requestDevice ----
    scanButton.addEventListener('click', async () => {
        try {
            console.log('Solicitando dispositivo Bluetooth...');
            resultsDiv.innerHTML = 'Procurando...';

            const device = await navigator.bluetooth.requestDevice({
                acceptAllDevices: true,
                optionalServices: []
            });

            resultsDiv.innerHTML =
                `Dispositivo ${device.name || device.id} selecionado.`;

            // OBS: advertisementreceived não dispara aqui
            console.log("Dispositivo selecionado, para anúncios use o botão 'Low Energy Scan'");

        } catch (error) {
            console.error('Ocorreu um erro:', error);
            resultsDiv.innerHTML = `Erro: ${error.message}`;
        }
    });

    // ---- Scan com Low Energy (funciona advertisementreceived) ----
    $('#btn-lowenergy').click(() => {
        startLEScan();
    });

    async function startLEScan() {
        if (!navigator.bluetooth) {
            alert("Seu navegador não suporta Web Bluetooth.");
            return;
        }

        try {
            const scan = await navigator.bluetooth.requestLEScan({
                acceptAllAdvertisements: true
            });

            navigator.bluetooth.addEventListener('advertisementreceived', (event) => {
                const rssi = event.rssi;
                const txPower = event.txPower;

                let output = `<strong>RSSI:</strong> ${rssi} dBm<br>`;
                output += `<strong>TX Power:</strong> ${txPower} dBm<br>`;

                for (const [companyId, dataView] of event.manufacturerData.entries()) {
                    const hex = [...new Uint8Array(dataView.buffer)]
                        .map(b => b.toString(16).padStart(2, '0')).join(' ');
                    output += `<strong>Company ID:</strong> ${companyId}<br>`;
                    output += `<strong>Manufacturer Data:</strong> ${hex}<br><hr>`;
                }

                resultsDiv.innerHTML += output; // <-- corrigido
            });

        } catch (error) {
            console.error('Erro ao iniciar LE Scan:', error);
        }
    }

    // ---- Tratamento de pacotes iBeacon ----
    function handleAdvertisement(event) {
        const companyData = event.manufacturerData.get(APPLE_COMPANY_ID);
        if (!companyData) return; // <-- corrigido

        if (companyData.byteLength < 23 || companyData.getUint16(0, false) !== 0x0215) return;

        const uuid = parseUUID(companyData, 2);
        const major = companyData.getUint16(18, false);
        const minor = companyData.getUint16(20, false);
        const txPower = companyData.getInt8(22);
        const rssi = event.rssi;

        const beaconId = `${uuid}-${major}-${minor}`;

        let beaconDiv = document.getElementById(beaconId);
        if (!beaconDiv) {
            beaconDiv = document.createElement('div');
            beaconDiv.id = beaconId;
            resultsDiv.appendChild(beaconDiv);
        }

        beaconDiv.innerHTML = `
            <strong>iBeacon Encontrado!</strong><br>
            <strong>RSSI:</strong> ${rssi} dBm<br>
            <strong>UUID:</strong> ${uuid}<br>
            <strong>Major:</strong> ${major}<br>
            <strong>Minor:</strong> ${minor}<br>
            <strong>TX Power:</strong> ${txPower} dBm<br>
            <hr>
        `;
    }

    function parseUUID(dataView, offset) {
        let uuid = '';
        for (let i = 0; i < 16; i++) {
            const hex = dataView.getUint8(offset + i).toString(16).padStart(2, '0');
            uuid += hex;
            if (i === 3 || i === 5 || i === 7 || i === 9) uuid += '-';
        }
        return uuid;
    }

    // ---- QRCode (Instascan) ----
    $(document).ready(function() {
        let scanner = new Instascan.Scanner({
            video: document.getElementById('preview')
        });

        scanner.addListener('scan', function(content) {
            $("#resultado").text(content);
            console.log("QRCode:", content);
        });

        Instascan.Camera.getCameras().then(function(cameras) {
            if (cameras.length > 0) {
                scanner.start(cameras[0]);
            } else {
                alert("Nenhuma câmera encontrada!");
            }
        }).catch(function(e) {
            console.error(e);
        });
    });
</script>


</html>
