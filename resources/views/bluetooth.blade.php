<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Scanner de iBeacon (Experimental)</title>
  <meta name="theme-color" content="#2196f3" />
  <meta name="description" content="Scanner experimental de iBeacons via Web Bluetooth API" />
  
  <style>
    body {
      font-family: Arial, sans-serif;
      max-width: 600px;
      margin: 0 auto;
      padding: 20px;
      text-align: center;
      background-color: #f5f5f5;
    }
    h1 {
      color: #333;
    }
    button {
      background-color: #2196f3;
      color: white;
      border: none;
      padding: 12px 24px;
      font-size: 16px;
      border-radius: 6px;
      cursor: pointer;
      margin: 20px 0;
    }
    button:disabled {
      background-color: #ccc;
      cursor: not-allowed;
    }
    #log {
      text-align: left;
      background-color: #fff;
      border: 1px solid #ddd;
      border-radius: 6px;
      padding: 10px;
      margin-top: 20px;
      height: 300px;
      overflow-y: auto;
      font-family: monospace;
      font-size: 12px;
      color: #333;
    }
    .info {
      color: #555;
      font-size: 14px;
    }
  </style>
</head>
<body>
  <h1>📡 Scanner de iBeacon</h1>
  <p class="info">Clique no botão abaixo para escanear dispositivos iBeacon próximos.</p>
  <p class="info">⚠️ Requer Chrome/Edge no Android e conexão HTTPS.</p>

  <button id="scanButton" onclick="scanForBeacons()" disabled>🔍 Escanear iBeacons</button>

  <div id="log">Aguardando início...</div>

  <script>
    const log = document.getElementById('log');
    const scanButton = document.getElementById('scanButton');

    // Habilitar botão após o evento beforeinstallprompt (indica que estamos em um ambiente PWA compatível)
    window.addEventListener('load', () => {
      // Mesmo sem PWA, vamos habilitar o botão no load (mas o Bluetooth precisa de interação)
      scanButton.disabled = false;
      log.textContent = 'Pronto para escanear. Clique no botão.';
    });

    async function scanForBeacons() {
      log.textContent = 'Iniciando escaneamento BLE...\n';

      try {
        // Solicitar dispositivo BLE com escuta de anúncios
        const device = await navigator.bluetooth.requestDevice({
          acceptAllDevices: true,
          optionalServices: [] // Não precisamos de serviços específicos
        });

        device.addEventListener('advertisementreceived', event => {
          const { device: btDevice, rssi, manufacturerData, serviceData } = event;

          const name = btDevice.name || 'Desconhecido';
          log.textContent += `\n🔹 Dispositivo: ${name}`;
          log.textContent += `\n   RSSI: ${rssi} dBm`;

          // Verificar manufacturerData (onde está o iBeacon)
          let foundBeacon = false;
          for (const [companyId, data] of manufacturerData) {
            const dataHex = Array.from(data).map(b => b.toString(16).padStart(2, '0')).join('');
            log.textContent += `\n   Manufacturer ID: 0x${companyId.toString(16).toUpperCase()}`;
            log.textContent += `\n   Dados: ${dataHex}`;

            // iBeacon usa Company ID Apple: 0x004C e começa com 0x0215
            if (companyId === 0x004C && dataHex.length >= 50 && dataHex.substr(2, 6) === '0215') {
              // Estrutura: 4c00 0215 [UUID 32] [Major 4] [Minor 4] [TxPower 2]
              const uuid = [
                dataHex.substr(8, 8),
                dataHex.substr(16, 4),
                dataHex.substr(20, 4),
                dataHex.substr(24, 4),
                dataHex.substr(28, 12)
              ].join('-');
              const major = parseInt(dataHex.substr(36, 4), 16);
              const minor = parseInt(dataHex.substr(40, 4), 16);
              const txPower = parseInt(dataHex.substr(44, 2), 16) - 256; // signed 8-bit

              const distance = estimateDistance(txPower, rssi);

              log.textContent += `\n   ✅ iBeacon Detectado!`;
              log.textContent += `\n      UUID: ${uuid}`;
              log.textContent += `\n      Major: ${major}`;
              log.textContent += `\n      Minor: ${minor}`;
              log.textContent += `\n      Potência (Tx): ${txPower} dBm`;
              log.textContent += `\n      Distância estimada: ${distance.toFixed(2)} m`;
              log.textContent += '\n' + '-'.repeat(40);

              foundBeacon = true;
            }
          }

          if (!foundBeacon) {
            log.textContent += `\n   ❌ Não é um iBeacon reconhecido.\n`;
          }
        });

        // Iniciar escuta de anúncios
        device.addEventListener('gattserverdisconnected', () => {
          log.textContent += '\n❌ Conexão BLE perdida.\n';
        });

        // Conectar para começar a receber anúncios
        await device.gatt.connect();
        log.textContent += '\n🟢 Escaneamento ativo... (pare de escanear recarregando a página)\n';

      } catch (error) {
        log.textContent += `\n❌ Erro: ${error.message}\n`;
        if (error.name === 'NotAllowedError') {
          log.textContent += 'Você precisa permitir o acesso ao Bluetooth e interagir com a página.\n';
        }
      }
    }

    // Função para estimar distância com base em RSSI e TxPower
    function estimateDistance(txPower, rssi) {
      if (rssi === 0) return -1;
      const ratio = rssi / txPower;
      if (ratio < 1.0) {
        return Math.pow(ratio, 10);
      } else {
        return 0.89976 * Math.pow(ratio, 7.7095) + 0.111;
      }
    }
  </script>
</body>
</html>