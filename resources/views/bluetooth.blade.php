<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beacon Scanner Pro - Web Bluetooth</title>
    <style>
        :root {
            --primary: #2563eb;
            --success: #16a34a;
            --danger: #dc2626;
            --warning: #ca8a04;
            --bg: #f8fafc;
        }

        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: var(--bg); color: #1e293b; padding: 20px; }
        .container { max-width: 900px; margin: 0 auto; background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1); }
        
        h1 { margin-top: 0; color: #0f172a; border-bottom: 2px solid #e2e8f0; padding-bottom: 10px; }

        /* Checklist de Diagnóstico */
        .checklist { margin-bottom: 25px; padding: 15px; border-radius: 8px; background: #f1f5f9; border: 1px solid #e2e8f0; }
        .check-item { display: flex; align-items: center; margin-bottom: 8px; font-weight: 500; }
        .status-icon { margin-right: 10px; width: 20px; height: 20px; border-radius: 50%; display: inline-block; }
        .valid { color: var(--success); }
        .invalid { color: var(--danger); }
        
        /* Controles */
        .controls { display: flex; gap: 10px; align-items: center; margin-bottom: 20px; }
        button { 
            background: var(--primary); color: white; border: none; padding: 12px 24px; 
            border-radius: 6px; cursor: pointer; font-weight: 600; transition: opacity 0.2s;
        }
        button:disabled { background: #94a3b8; cursor: not-allowed; }
        button:hover:not(:disabled) { opacity: 0.9; }

        /* Tabela */
        .table-container { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { background: #f8fafc; text-align: left; padding: 12px; border-bottom: 2px solid #e2e8f0; text-transform: uppercase; font-size: 0.8rem; letter-spacing: 0.05em; }
        td { padding: 12px; border-bottom: 1px solid #f1f5f9; font-family: monospace; }
        
        .rssi-cell { font-weight: bold; }
        .signal-bg { width: 100px; height: 8px; background: #e2e8f0; border-radius: 4px; overflow: hidden; display: inline-block; vertical-align: middle; margin-left: 10px; }
        .signal-fill { height: 100%; background: var(--success); transition: width 0.3s ease; }
        
        .badge { padding: 2px 6px; border-radius: 4px; font-size: 0.75rem; background: #e2e8f0; }
    </style>
</head>
<body>

<div class="container">
    <h1>Scanner de Beacons BLE</h1>

    <div class="checklist" id="checklist">
        <div class="check-item" id="check-https">
            <span class="status-icon"></span> Contexto Seguro (HTTPS)
        </div>
        <div class="check-item" id="check-api">
            <span class="status-icon"></span> Web Bluetooth API
        </div>
        <div class="check-item" id="check-flag">
            <span class="status-icon"></span> Experimental Scanning Flag
        </div>
    </div>

    <div class="controls">
        <button id="btnStart" disabled>Iniciar Escaneamento</button>
        <span id="scanStatusText">Verificando requisitos...</span>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Dispositivo / Name</th>
                    <th>ID / MAC (Obfuscated)</th>
                    <th>RSSI (Sinal)</th>
                </tr>
            </thead>
            <tbody id="deviceList">
                <tr>
                    <td colspan="3" style="text-align:center; color:#94a3b8; padding: 40px;">Nenhum beacon detectado. Clique em Iniciar.</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<script>
    const btnStart = document.getElementById('btnStart');
    const scanStatusText = document.getElementById('scanStatusText');
    const deviceListBody = document.getElementById('deviceList');
    
    let devicesFound = new Map();
    let isScanning = false;

    // --- 1. Lógica de Diagnóstico Programático ---
    function runDiagnostics() {
        const tests = {
            https: window.isSecureContext,
            api: 'bluetooth' in navigator,
            flag: typeof navigator.bluetooth?.requestLEScan === 'function'
        };

        updateCheckUI('check-https', tests.https);
        updateCheckUI('check-api', tests.api);
        updateCheckUI('check-flag', tests.flag);

        if (tests.https && tests.api && tests.flag) {
            btnStart.disabled = false;
            scanStatusText.textContent = "Pronto para escanear.";
            scanStatusText.className = "valid";
        } else {
            scanStatusText.textContent = "Requisitos pendentes. Verifique a lista acima.";
            scanStatusText.className = "invalid";
        }
    }

    function updateCheckUI(elementId, passed) {
        const el = document.getElementById(elementId);
        const icon = el.querySelector('.status-icon');
        if (passed) {
            el.classList.add('valid');
            icon.style.backgroundColor = 'var(--success)';
            icon.innerHTML = '✓';
            icon.style.color = 'white';
            icon.style.textAlign = 'center';
            icon.style.fontSize = '12px';
        } else {
            el.classList.add('invalid');
            icon.style.backgroundColor = 'var(--danger)';
            icon.innerHTML = '✕';
            icon.style.color = 'white';
            icon.style.textAlign = 'center';
            icon.style.fontSize = '12px';
        }
    }

    // --- 2. Lógica de Escaneamento ---
    async function startScan() {
        if (isScanning) return;

        try {
            // Solicita permissão e inicia o scan
            // O filtro acceptAllAdvertisements permite ver iBeacons, Eddystone, etc.
            const scan = await navigator.bluetooth.requestLEScan({
                keepRepeatedDevices: true,
                acceptAllAdvertisements: true
            });

            isScanning = true;
            btnStart.disabled = true;
            btnStart.textContent = "Escaneando...";
            scanStatusText.textContent = "Ouvindo pacotes BLE...";

            navigator.bluetooth.addEventListener('advertisementreceived', event => {
                // Atualiza o Map com os dados mais recentes
                devicesFound.set(event.device.id, {
                    name: event.device.name || "Unknown",
                    id: event.device.id,
                    rssi: event.rssi,
                    lastSeen: new Date().toLocaleTimeString()
                });

                renderTable();
            });

        } catch (error) {
            console.error(error);
            alert("Erro ao iniciar scan: " + error.message);
        }
    }

    function renderTable() {
        // Ordenar por RSSI decrescente (ex: -30dBm é maior que -90dBm)
        const sorted = Array.from(devicesFound.values())
            .sort((a, b) => b.rssi - a.rssi);

        deviceListBody.innerHTML = "";

        sorted.forEach(device => {
            const row = document.createElement('tr');
            
            // Cálculo de força do sinal (0 a 100)
            // Geralmente RSSI varia de -100 (ruim) a -30 (excelente)
            const strength = Math.min(Math.max(2 * (device.rssi + 100), 0), 100);

            row.innerHTML = `
                <td>
                    <strong>${device.name}</strong><br>
                    <span class="badge">Visto às: ${device.lastSeen}</span>
                </td>
                <td style="font-size: 0.75rem; color: #64748b;">${device.id}</td>
                <td class="rssi-cell">
                    ${device.rssi} dBm
                    <div class="signal-bg">
                        <div class="signal-fill" style="width: ${strength}%"></div>
                    </div>
                </td>
            `;
            deviceListBody.appendChild(row);
        });
    }

    // Inicialização
    btnStart.addEventListener('click', startScan);
    window.onload = runDiagnostics;
</script>

</body>
</html>