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
    <div class="container-full d-flex justify-content-center align-items-center w-100 px-2" style="height: 100dvh">
        <div class="row">
            <div class="col-lg-4 col-12 d-flex flex-column bg-sucess">
                <!-- Botão de abrir câmera -->
                <label for="camera-input" class="btn btn-primary">Acessar Câmera</label>
                <input type="file" accept="image/*" capture="environment" id="camera-input" style="display: none;" />

                <button class="btn btn-primary mt-2" id="btn-bluetooth">Procurar beacons (Bluetooth)</button>

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
                    <div class="card-body" style="max-height: 400px; overflow: auto;">
                        <div class="beacons">
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12" id="results"></div>
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
            retur
        const id = beacon.i
        const $container = $(".beacons")
        let html = ""
        // mostra informações de forma dinamica
        Object.entries(beacon).forEach(([key, value]) => {
            html += `<strong>${key}:</strong>${value ?? "---"}<br>`;
        })
        html += "<hr>"
        // substitui ":" por "_" para usar como id válido no DOM
        // tenta selecionar um elemento que tenha esse id
        let $el = $("#" + id.replace(/:/g, "_"))
        // se tem elemento com esse id ele é atualizado
        if ($el.length > 0) {
            $el.html(html); // atualiza se já existir
        } else {
            // cria um novo elemento com ID único baseado no beacon ID se não existir
            $el = $(`<div class="beacon" id="${id.replace(/:/g, "_")}"></div>`)
                .html(html);
            $container.append($el);
        }
    })


    const scanButton = document.getElementById('btn-bluetooth');
    const resultsDiv = document.getElementById('results');
    const APPLE_COMPANY_ID = 0x004C; // ID da Apple para iBeacons

    scanButton.addEventListener('click', async () => {
        try {
            console.log('Solicitando dispositivo Bluetooth...');
            resultsDiv.innerHTML = 'Procurando...';

            // Solicita permissão ao usuário para escanear
            const device = await navigator.bluetooth.requestDevice({
                acceptAllDevices: true,
                optionalServices: [] // Necessário para ver advertisements
            });

            console.log(
                `Dispositivo ${device.name || device.id} selecionado. Observando advertisements...`
            );

            // AbortController para parar o scan depois de um tempo
            const abortController = new AbortController();

            // Adiciona o listener para "escutar" os pacotes
            device.addEventListener('advertisementreceived', (event) => {
                handleAdvertisement(event);
            });

            // Inicia o scan
            await device.watchAdvertisements({
                signal: abortController.signal
            });

            // Para o scan após 30 segundos para economizar bateria
            setTimeout(() => {
                abortController.abort();
                console.log('Scan parado.');
                resultsDiv.innerHTML += '<p>Busca finalizada.</p>';
            }, 30000);

        } catch (error) {
            console.error('Ocorreu um erro:', error);
            resultsDiv.innerHTML = `Erro: ${error.message}`;
        }
    });

    function handleAdvertisement(event) {
        const companyData = event.manufacturerData.get(APPLE_COMPANY_ID);

        // Verifica se é um pacote da Apple
        if (!companyData) {
            return;
        }

        // `companyData` é um DataView. Verificamos se tem o tamanho esperado
        // e o tipo de iBeacon (0x0215)
        if (companyData.byteLength < 23 || companyData.getUint16(0, false) !== 0x0215) {
            return;
        }

        // Decodifica os dados do iBeacon
        const uuid = parseUUID(companyData, 2);
        const major = companyData.getUint16(18, false);
        const minor = companyData.getUint16(20, false);
        const txPower = companyData.getInt8(22);
        const rssi = event.rssi;

        const beaconId = `${uuid}-${major}-${minor}`;

        // Exibe os resultados na tela
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
            if (i === 3 || i === 5 || i === 7 || i === 9) {
                uuid += '-';
            }
        }
        return uuid;
    }


    // $(document).ready(function() {

    //     $('#btn-bluetooth').on('click', async function() {
    //         if (!navigator.bluetooth) {
    //             alert('Seu navegador não suporta Web Bluetooth.');
    //             return;
    //         }

    //         try {
    //             const device = await navigator.bluetooth.requestDevice({
    //                 acceptAllDevices: true,
    //                 optionalServices: [
    //                     'battery_service'
    //                 ] // Pode mudar conforme o dispositivo
    //             });

    //             console.log('Dispositivo encontrado:', device);
    //             alert(`Dispositivo: ${device.name || 'Sem nome'}\nID: ${device.id}`);

    //             const server = await device.gatt.connect();
    //             console.log('Conectado ao GATT server:', server);

    //             // Aqui você pode continuar lendo características do serviço, se quiser.
    //         } catch (error) {
    //             console.error('Erro ao conectar com Bluetooth:', error);
    //             alert('Erro: ' + error.message);
    //         }
    //     });
    // });
</script>

</html>
