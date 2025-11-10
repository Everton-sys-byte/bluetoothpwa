<!DOCTYPE html>
<html lang="pt-BR">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <title>Bluetooth Scanner</title>

  <link rel="manifest" href="{{ asset('manifest.json') }}">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">

  <script>
    // Registra o service worker (PWA)
    if ('serviceWorker' in navigator) {
      navigator.serviceWorker.register("{{ asset('pwabuilder-sw.js') }}")
        .then(() => console.log("Service Worker registrado"))
        .catch(err => console.error("Erro ao registrar SW:", err));
    }

    // Oculta botão se estiver em modo standalone
    document.addEventListener('DOMContentLoaded', () => {
      const isStandalone = window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone;
      if (isStandalone && document.getElementById('btn-download-apk')) {
        document.getElementById('btn-download-apk').style.display = 'none';
      }
    });
  </script>

  <style>
    body {
      padding: 20px;
      background: #f8f9fa;
    }
    pre {
      background: #222;
      color: #0f0;
      padding: 10px;
      height: 250px;
      overflow-y: auto;
    }
  </style>
</head>

<body>
  <div class="container text-center">
    <h2 class="mb-3">Bluetooth Low Energy Scanner</h2>

    <div class="mb-2">
      <input type="text" id="name" class="form-control mb-2" placeholder="Filtrar por nome exato">
      <input type="text" id="namePrefix" class="form-control mb-2" placeholder="Filtrar por prefixo de nome">
      <div class="form-check text-start mb-3">
        <input class="form-check-input" type="checkbox" id="allAdvertisements">
        <label class="form-check-label" for="allAdvertisements">
          Aceitar todos anúncios
        </label>
      </div>
      <button class="btn btn-primary" onclick="onButtonClick()">Iniciar Scan</button>
    </div>

    <pre id="log"></pre>

    <a class="btn btn-primary mt-2" id="btn-download-apk"
                    href="{{ asset('downloads/Bluetooth.apk') }}">Download
                    .APK (TWA)</a>
  </div>

  <script>
    const logEl = document.getElementById('log');

    function log(msg) {
      console.log(msg);
      logEl.textContent += msg + "\n";
      logEl.scrollTop = logEl.scrollHeight;
    }

    const logDataView = (labelOfDataSource, key, valueDataView) => {
      const hexString = [...new Uint8Array(valueDataView.buffer)].map(b =>
        b.toString(16).padStart(2, '0')).join(' ');
      const textDecoder = new TextDecoder('ascii');
      const asciiString = textDecoder.decode(valueDataView.buffer);
      log(`  ${labelOfDataSource} Data (${key}):\n    HEX: ${hexString}\n    ASCII: ${asciiString}`);
    };

    async function onButtonClick() {
      if (!navigator.bluetooth) {
        alert("Seu navegador não suporta Web Bluetooth API.");
        return;
      }

      try {
        let filters = [];
        const name = document.querySelector('#name').value.trim();
        const namePrefix = document.querySelector('#namePrefix').value.trim();

        if (name) filters.push({ name });
        if (namePrefix) filters.push({ namePrefix });

        let options = {};
        if (document.querySelector('#allAdvertisements').checked) {
          options.acceptAllAdvertisements = true;
        } else if (filters.length > 0) {
          options.filters = filters;
        } else {
          alert("Defina um filtro ou marque 'Aceitar todos anúncios'");
          return;
        }

        log('Solicitando permissão para escanear...');
        const scan = await navigator.bluetooth.requestLEScan(options);

        log(`Scan iniciado:
          acceptAllAdvertisements: ${scan.acceptAllAdvertisements}
          active: ${scan.active}
          filters: ${JSON.stringify(scan.filters)}`);

        navigator.bluetooth.addEventListener('advertisementreceived', event => {
          log(`\n=== Dispositivo detectado ===
          Nome: ${event.device.name}
          ID: ${event.device.id}
          RSSI: ${event.rssi}
          TX Power: ${event.txPower}
          UUIDs: ${event.uuids}`);

          event.manufacturerData.forEach((value, key) => logDataView('Manufacturer', key, value));
          event.serviceData.forEach((value, key) => logDataView('Service', key, value));
        });

        setTimeout(() => {
          scan.stop();
          log(`Scan parado. scan.active = ${scan.active}`);
        }, 10000);

      } catch (error) {
        log('Erro: ' + error);
      }
    }
  </script>
</body>
</html>
