<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Simulador de Tecido - Sistema Massa-Mola</title>
<style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }
    
    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
        overflow: hidden;
        color: white;
    }
    
    #canvas {
        display: block;
        cursor: crosshair;
        background: radial-gradient(circle at center, #0f3460 0%, #0a1929 100%);
    }
    
    .controls {
        position: absolute;
        top: 20px;
        left: 20px;
        background: rgba(26, 26, 46, 0.95);
        padding: 20px;
        border-radius: 15px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.5);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.1);
        max-width: 320px;
    }
    
    .controls h2 {
        margin-bottom: 15px;
        color: #00d4ff;
        font-size: 1.3em;
        border-bottom: 2px solid #00d4ff;
        padding-bottom: 8px;
    }
    
    .control-group {
        margin: 15px 0;
    }
    
    .control-group label {
        display: block;
        margin-bottom: 5px;
        color: #a0a0a0;
        font-size: 0.9em;
    }
    
    .control-group input[type="range"] {
        width: 100%;
        height: 6px;
        border-radius: 3px;
        background: #2d2d44;
        outline: none;
        -webkit-appearance: none;
    }
    
    .control-group input[type="range"]::-webkit-slider-thumb {
        -webkit-appearance: none;
        appearance: none;
        width: 18px;
        height: 18px;
        border-radius: 50%;
        background: #00d4ff;
        cursor: pointer;
        box-shadow: 0 0 10px rgba(0, 212, 255, 0.5);
    }
    
    .control-group input[type="range"]::-moz-range-thumb {
        width: 18px;
        height: 18px;
        border-radius: 50%;
        background: #00d4ff;
        cursor: pointer;
        border: none;
        box-shadow: 0 0 10px rgba(0, 212, 255, 0.5);
    }
    
    .value-display {
        display: inline-block;
        float: right;
        color: #00d4ff;
        font-weight: bold;
    }
    
    .button-group {
        display: flex;
        gap: 10px;
        margin-top: 15px;
    }
    
    button {
        flex: 1;
        padding: 10px;
        border: none;
        border-radius: 8px;
        background: linear-gradient(135deg, #00d4ff 0%, #0099cc 100%);
        color: white;
        font-weight: bold;
        cursor: pointer;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(0, 212, 255, 0.3);
    }
    
    button:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(0, 212, 255, 0.5);
    }
    
    button:active {
        transform: translateY(0);
    }
    
    .reset-btn {
        background: linear-gradient(135deg, #ff6b6b 0%, #ee5a6f 100%);
        box-shadow: 0 4px 15px rgba(255, 107, 107, 0.3);
    }
    
    .reset-btn:hover {
        box-shadow: 0 6px 20px rgba(255, 107, 107, 0.5);
    }
    
    .instructions {
        position: absolute;
        bottom: 20px;
        left: 50%;
        transform: translateX(-50%);
        background: rgba(26, 26, 46, 0.95);
        padding: 15px 30px;
        border-radius: 10px;
        text-align: center;
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.1);
    }
    
    .instructions p {
        margin: 5px 0;
        color: #a0a0a0;
        font-size: 0.9em;
    }
    
    .instructions strong {
        color: #00d4ff;
    }
    
    .stats {
        position: absolute;
        top: 20px;
        right: 20px;
        background: rgba(26, 26, 46, 0.95);
        padding: 15px 20px;
        border-radius: 10px;
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.1);
        font-size: 0.9em;
    }
    
    .stats div {
        margin: 5px 0;
        color: #a0a0a0;
    }
    
    .stats span {
        color: #00d4ff;
        font-weight: bold;
    }
</style>
</head>
<body>

<canvas id="canvas"></canvas>

<div class="controls">
    <h2>⚙️ Controles</h2>
    
    <div class="control-group">
        <label>
            Gravidade <span class="value-display" id="gravityVal">0.5</span>
        </label>
        <input type="range" id="gravity" min="0" max="2" step="0.1" value="0.5">
    </div>
    
    <div class="control-group">
        <label>
            Rigidez <span class="value-display" id="stiffnessVal">0.6</span>
        </label>
        <input type="range" id="stiffness" min="0.1" max="1" step="0.05" value="0.6">
    </div>
    
    <div class="control-group">
        <label>
            Amortecimento <span class="value-display" id="dampingVal">0.98</span>
        </label>
        <input type="range" id="damping" min="0.8" max="0.99" step="0.01" value="0.98">
    </div>
    
    <div class="control-group">
        <label>
            Distância de Ruptura <span class="value-display" id="tearVal">40</span>
        </label>
        <input type="range" id="tearDistance" min="20" max="100" step="5" value="40">
    </div>
    
    <div class="control-group">
        <label>
            Força do Mouse <span class="value-display" id="mouseVal">0.5</span>
        </label>
        <input type="range" id="mouseForce" min="0" max="1" step="0.1" value="0.5">
    </div>
    
    <div class="button-group">
        <button class="reset-btn" onclick="resetSimulation()">🔄 Resetar</button>
        <button onclick="togglePause()">⏸️ Pausar</button>
    </div>
</div>

<div class="instructions">
    <p><strong>Clique e arraste</strong> para interagir com o tecido</p>
    <p><strong>Arraste rápido</strong> para rasgar o tecido</p>
</div>

<div class="stats">
    <div>Pontos: <span id="pointCount">0</span></div>
    <div>Ligações: <span id="linkCount">0</span></div>
    <div>FPS: <span id="fps">60</span></div>
</div>

<script>
// ==================== CONFIGURAÇÃO ====================
const canvas = document.getElementById("canvas");
const ctx = canvas.getContext("2d");

// Ajusta canvas para tela cheia
function resizeCanvas() {
    canvas.width = window.innerWidth;
    canvas.height = window.innerHeight;
}
resizeCanvas();
window.addEventListener("resize", resizeCanvas);

// Parâmetros ajustáveis
let config = {
    cols: 35,
    rows: 25,
    spacing: 20,
    gravity: 0.5,
    stiffness: 0.6,
    damping: 0.98,
    tearDistance: 40,
    mouseForce: 0.5,
    mouseRadius: 60
};

let paused = false;
let lastTime = performance.now();
let fps = 60;

// ==================== MOUSE ====================
const mouse = {
    x: 0,
    y: 0,
    px: 0,
    py: 0,
    down: false
};

canvas.addEventListener("mousedown", () => mouse.down = true);
canvas.addEventListener("mouseup", () => mouse.down = false);
canvas.addEventListener("mousemove", e => {
    mouse.px = mouse.x;
    mouse.py = mouse.y;
    mouse.x = e.clientX;
    mouse.y = e.clientY;
});

canvas.addEventListener("touchstart", e => {
    e.preventDefault();
    mouse.down = true;
    const touch = e.touches[0];
    mouse.x = touch.clientX;
    mouse.y = touch.clientY;
    mouse.px = mouse.x;
    mouse.py = mouse.y;
});

canvas.addEventListener("touchend", () => mouse.down = false);

canvas.addEventListener("touchmove", e => {
    e.preventDefault();
    const touch = e.touches[0];
    mouse.px = mouse.x;
    mouse.py = mouse.y;
    mouse.x = touch.clientX;
    mouse.y = touch.clientY;
});

// ==================== CLASSE POINT ====================
class Point {
    constructor(x, y, pinned = false) {
        this.x = x;
        this.y = y;
        this.oldx = x;
        this.oldy = y;
        this.pinned = pinned;
        this.links = [];
        this.vx = 0;
        this.vy = 0;
    }
    
    update() {
        if (this.pinned) return;
        
        // Verlet integration
        const vx = (this.x - this.oldx) * config.damping;
        const vy = (this.y - this.oldy) * config.damping;
        
        this.oldx = this.x;
        this.oldy = this.y;
        
        this.x += vx;
        this.y += vy + config.gravity;
        
        // Interação com mouse
        if (mouse.down) {
            const dx = this.x - mouse.x;
            const dy = this.y - mouse.y;
            const dist = Math.sqrt(dx * dx + dy * dy);
            
            if (dist < config.mouseRadius) {
                const force = (1 - dist / config.mouseRadius) * config.mouseForce;
                this.x += (mouse.x - mouse.px) * force;
                this.y += (mouse.y - mouse.py) * force;
            }
        }
        
        // Limites da tela (bordas)
        if (this.x < 0) this.x = 0;
        if (this.x > canvas.width) this.x = canvas.width;
        if (this.y > canvas.height) this.y = canvas.height;
    }
    
    draw() {
        // Desenha as conexões
        for (let link of this.links) {
            link.draw();
        }
        
        // Desenha os pontos fixos
        if (this.pinned) {
            ctx.beginPath();
            ctx.arc(this.x, this.y, 4, 0, Math.PI * 2);
            ctx.fillStyle = "#00d4ff";
            ctx.shadowBlur = 10;
            ctx.shadowColor = "#00d4ff";
            ctx.fill();
            ctx.shadowBlur = 0;
        }
    }
}

// ==================== CLASSE LINK ====================
class Link {
    constructor(p1, p2) {
        this.p1 = p1;
        this.p2 = p2;
        const dx = p2.x - p1.x;
        const dy = p2.y - p1.y;
        this.length = Math.sqrt(dx * dx + dy * dy);
    }
    
    solve() {
        const dx = this.p2.x - this.p1.x;
        const dy = this.p2.y - this.p1.y;
        const dist = Math.sqrt(dx * dx + dy * dy);
        
        // Verifica ruptura
        if (dist > config.tearDistance) {
            this.p1.links = this.p1.links.filter(l => l !== this);
            this.p2.links = this.p2.links.filter(l => l !== this);
            return false;
        }
        
        // Aplica restrição de distância
        const diff = (this.length - dist) / dist * config.stiffness;
        const offsetX = dx * diff * 0.5;
        const offsetY = dy * diff * 0.5;
        
        if (!this.p1.pinned) {
            this.p1.x -= offsetX;
            this.p1.y -= offsetY;
        }
        if (!this.p2.pinned) {
            this.p2.x += offsetX;
            this.p2.y += offsetY;
        }
        
        return true;
    }
    
    draw() {
        const dx = this.p2.x - this.p1.x;
        const dy = this.p2.y - this.p1.y;
        const dist = Math.sqrt(dx * dx + dy * dy);
        
        // Cor baseada na tensão
        const tension = dist / this.length;
        let color;
        if (tension > 1.5) {
            color = `rgba(255, 100, 100, 0.8)`;
        } else if (tension > 1.2) {
            color = `rgba(255, 200, 100, 0.7)`;
        } else {
            color = `rgba(100, 200, 255, 0.6)`;
        }
        
        ctx.beginPath();
        ctx.moveTo(this.p1.x, this.p1.y);
        ctx.lineTo(this.p2.x, this.p2.y);
        ctx.strokeStyle = color;
        ctx.lineWidth = 1.5;
        ctx.stroke();
    }
}

// ==================== CRIAÇÃO DA MALHA ====================
let points = [];
let allLinks = [];

function createCloth() {
    points = [];
    allLinks = [];
    
    const startX = (canvas.width - (config.cols - 1) * config.spacing) / 2;
    const startY = 100;
    
    // Cria pontos
    for (let y = 0; y < config.rows; y++) {
        for (let x = 0; x < config.cols; x++) {
            const pinned = y === 0 && (x % 5 === 0 || x === config.cols - 1);
            const point = new Point(
                startX + x * config.spacing,
                startY + y * config.spacing,
                pinned
            );
            points.push(point);
        }
    }
    
    // Cria links horizontais e verticais
    for (let y = 0; y < config.rows; y++) {
        for (let x = 0; x < config.cols; x++) {
            const index = y * config.cols + x;
            const point = points[index];
            
            // Link horizontal
            if (x < config.cols - 1) {
                const link = new Link(point, points[index + 1]);
                point.links.push(link);
                allLinks.push(link);
            }
            
            // Link vertical
            if (y < config.rows - 1) {
                const link = new Link(point, points[index + config.cols]);
                point.links.push(link);
                allLinks.push(link);
            }
        }
    }
}

// ==================== CONTROLES UI ====================
document.getElementById("gravity").addEventListener("input", e => {
    config.gravity = parseFloat(e.target.value);
    document.getElementById("gravityVal").textContent = config.gravity.toFixed(1);
});

document.getElementById("stiffness").addEventListener("input", e => {
    config.stiffness = parseFloat(e.target.value);
    document.getElementById("stiffnessVal").textContent = config.stiffness.toFixed(2);
});

document.getElementById("damping").addEventListener("input", e => {
    config.damping = parseFloat(e.target.value);
    document.getElementById("dampingVal").textContent = config.damping.toFixed(2);
});

document.getElementById("tearDistance").addEventListener("input", e => {
    config.tearDistance = parseFloat(e.target.value);
    document.getElementById("tearVal").textContent = config.tearDistance.toFixed(0);
});

document.getElementById("mouseForce").addEventListener("input", e => {
    config.mouseForce = parseFloat(e.target.value);
    document.getElementById("mouseVal").textContent = config.mouseForce.toFixed(1);
});

function resetSimulation() {
    createCloth();
}

function togglePause() {
    paused = !paused;
    const btn = event.target;
    btn.textContent = paused ? "▶️ Continuar" : "⏸️ Pausar";
}

// ==================== LOOP DE ANIMAÇÃO ====================
function updateStats() {
    const linkCount = allLinks.filter(link => {
        return link.p1.links.includes(link) || link.p2.links.includes(link);
    }).length;
    
    document.getElementById("pointCount").textContent = points.length;
    document.getElementById("linkCount").textContent = linkCount;
    document.getElementById("fps").textContent = Math.round(fps);
}

function animate() {
    const currentTime = performance.now();
    const deltaTime = currentTime - lastTime;
    fps = fps * 0.9 + (1000 / deltaTime) * 0.1;
    lastTime = currentTime;
    
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    
    if (!paused) {
        // Múltiplas iterações para melhor estabilidade
        for (let iteration = 0; iteration < 5; iteration++) {
            // Remove links quebrados
            allLinks = allLinks.filter(link => link.solve());
        }
        
        // Atualiza pontos
        for (let point of points) {
            point.update();
        }
    }
    
    // Desenha tudo
    for (let point of points) {
        point.draw();
    }
    
    // Desenha cursor do mouse
    if (mouse.down) {
        ctx.beginPath();
        ctx.arc(mouse.x, mouse.y, config.mouseRadius, 0, Math.PI * 2);
        ctx.strokeStyle = "rgba(0, 212, 255, 0.3)";
        ctx.lineWidth = 2;
        ctx.stroke();
    }
    
    // Atualiza estatísticas a cada 30 frames
    if (Math.round(fps) % 30 === 0) {
        updateStats();
    }
    
    requestAnimationFrame(animate);
}

// ==================== INICIALIZAÇÃO ====================
createCloth();
updateStats();
animate();
</script>
</body>
</html>