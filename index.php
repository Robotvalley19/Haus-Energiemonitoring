<?php
// Live-Daten einlesen (aktuelle Messung)
$liveData = @json_decode(file_get_contents(__DIR__ . "/data.json"), true);

// Historische Daten einlesen (für Diagramme)
$historyData = @json_decode(file_get_contents(__DIR__ . "/history.json"), true) ?? [];

// Geräte aus CSV-Datei einlesen
$geraete = [];
$csvFile = __DIR__ . '/geraete.csv';
if (($handle = fopen($csvFile, 'r')) !== false) {
    $header = fgetcsv($handle, 1000, ';'); // Kopfzeile
    while (($data = fgetcsv($handle, 1000, ';')) !== false) {
        $geraete[] = array_combine($header, $data);
    }
    fclose($handle);
}

// Jahresverbrauch berechnen
$gesamtverbrauch = 0;
foreach($geraete as $g){
    $gesamtverbrauch += floatval($g['Jahresverbrauch_kWh']);
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
<meta charset="UTF-8">
<title>Energiemonitoring</title>

<!-- Lokale Fonts einbinden -->
<style>
@font-face {
    font-family: 'Orbitron';
    src: url('fonts/Orbitron-Regular.ttf') format('truetype');
    font-weight: 400;
}
@font-face {
    font-family: 'Orbitron';
    src: url('fonts/Orbitron-Bold.ttf') format('truetype');
    font-weight: 700;
}

body { font-family: 'Orbitron', sans-serif; background: #0b0c10; color: #00ffcc; margin: 0; padding: 0; }
.container { width: 95%; margin: auto; padding: 20px; }
h1 { text-align: center; font-size: 3em; letter-spacing: 2px; text-shadow: 0 0 15px #00ffcc; margin-bottom: 40px; }
.cards { display:grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap:20px; }
.card { background: rgba(10,20,30,0.8); border:2px solid #00ffcc; border-radius:15px; padding:25px 20px; text-align:center; transition:0.3s; cursor:pointer; }
.card:hover { transform:scale(1.05); box-shadow:0 0 25px #00ffcc; }
.card .icon { font-size:2em; margin-bottom:10px; }
.card .small { font-size:0.9em; color:#66fcf1; margin-bottom:5px; }
.card .value { font-size:2.5em; font-weight:bold; }
h2 { margin-top:50px; text-align:center; font-size:2em; text-shadow:0 0 10px #00ffcc; }
table { width:100%; border-collapse: collapse; margin-top:20px; background: rgba(15,23,42,0.8); border-radius: 10px; overflow: hidden; }
table th, table td { padding:12px; text-align:center; border-bottom:1px solid #1f2833; }
table th { background:#1e293b; color:#00ffcc; text-shadow:0 0 3px #00ffcc; }
table tr:nth-child(even) td { background: rgba(30,41,59,0.5); }
table tr:nth-child(odd) td { background: rgba(30,41,59,0.8); }
table tr:hover td { background: #0f1622; box-shadow: inset 0 0 10px #00ffcc; }
p.small { margin-top:30px; text-align:center; font-size:0.9em; color:#66fcf1; text-shadow:0 0 5px #00ffcc; }
#bg-canvas { position:fixed; top:0; left:0; width:100%; height:100%; z-index:-1; }

/* Modal & Filter */
#chart-modal { display:none; position:fixed; top:0; left:0; width:100%; height:100%; background: rgba(0,0,0,0.7); justify-content:center; align-items:center; z-index:1000; }
#chart-container { background:#0b0c10; padding:20px; border-radius:15px; width:80%; max-width:900px; position:relative; }
#chart-close { color:#00ffcc; float:right; font-size:1.5em; cursor:pointer; }
#chart-filters { text-align:center; margin-bottom:10px; }
#chart-filters button { background:#0b0c10; color:#00ffcc; border:1px solid #00ffcc; border-radius:5px; margin:0 5px; padding:5px 10px; cursor:pointer; }
#chart-filters button.active { background:#00ffcc; color:#0b0c10; }
</style>

<!-- Lokales Chart.js -->
<script src="js/chart.min.js"></script>
</head>
<body>
<canvas id="bg-canvas"></canvas>

<div class="container">
    <h1>Energiemonitor</h1>

    <div class="cards">
        <div class="card"><div class="icon">🔌</div><div class="small">Spannung</div><div class="value"><?= $liveData['voltage'] ?? '-' ?> V</div></div>
        <div class="card"><div class="icon">⚡</div><div class="small">Strom</div><div class="value"><?= $liveData['current'] ?? '-' ?> A</div></div>
        <div class="card"><div class="icon">💡</div><div class="small">Leistung</div><div class="value"><?= $liveData['power'] ?? '-' ?> W</div></div>
        <div class="card"><div class="icon">⬇</div><div class="small">Energie-Netzbezug</div><div class="value"><?= $liveData['grid_import'] ?? '-' ?> kWh</div></div>
        <div class="card"><div class="icon">🌞</div><div class="small">Energie-Einspeisung</div><div class="value"><?= $liveData['grid_export'] ?? '-' ?> kWh</div></div>
    </div>

    <p class="small">Letzte Aktualisierung: <?= $liveData['timestamp'] ?? 'Keine Daten' ?></p>

    <h2>Geraeteuebersicht</h2>
    <table>
        <thead>
            <tr>
                <th>Geraet</th>
                <th>Anzahl</th>
                <th>Leistung [W]</th>
                <th>Std/Tag</th>
                <th>Tage/Woche</th>
                <th>Wochen/Jahr</th>
                <th>Jahresverbrauch [kWh]</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($geraete as $g): ?>
            <tr>
                <td><?= htmlspecialchars($g['Geraet']) ?></td>
                <td><?= htmlspecialchars($g['Anzahl']) ?></td>
                <td><?= htmlspecialchars($g['Leistung_W']) ?></td>
                <td><?= htmlspecialchars($g['Std_Tag']) ?></td>
                <td><?= htmlspecialchars($g['Tage_Woche']) ?></td>
                <td><?= htmlspecialchars($g['Wochen_Jahr']) ?></td>
                <td><?= htmlspecialchars($g['Jahresverbrauch_kWh']) ?></td>
            </tr>
            <?php endforeach; ?>
            <tr style="font-weight:bold; background:#1f2833;">
                <td colspan="6">Gesamt Jahresverbrauch</td>
                <td><?= number_format($gesamtverbrauch, 2) ?> kWh</td>
            </tr>
        </tbody>
    </table>
</div>

<!-- Modal fuer Diagramme -->
<div id="chart-modal">
    <div id="chart-container">
        <span id="chart-close">&times;</span>
        <div id="chart-filters">
            <button data-period="day" class="active">Tag</button>
            <button data-period="week">Woche</button>
            <button data-period="month">Monat</button>
            <button data-period="year">Jahr</button>
        </div>
        <canvas id="chart-canvas"></canvas>
    </div>
</div>

<script>
// Hintergrundanimation
const canvas = document.getElementById('bg-canvas');
const ctx = canvas.getContext('2d');
let width = canvas.width = window.innerWidth;
let height = canvas.height = window.innerHeight;
window.addEventListener('resize', ()=>{width=canvas.width=window.innerWidth;height=canvas.height=window.innerHeight;});
const nodes = [];
for(let i=0;i<100;i++){nodes.push({x:Math.random()*width,y:Math.random()*height,vx:(Math.random()-0.5)*0.5,vy:(Math.random()-0.5)*0.5,r:1+Math.random()*2});}
function animate(){ctx.clearRect(0,0,width,height);ctx.fillStyle='rgba(0,255,204,0.7)';nodes.forEach(n=>{n.x+=n.vx;n.y+=n.vy;if(n.x<0)n.x=width;if(n.x>width)n.x=0;if(n.y<0)n.y=height;if(n.y>height)n.y=0;ctx.beginPath();ctx.arc(n.x,n.y,n.r,0,Math.PI*2);ctx.fill();});requestAnimationFrame(animate);}
animate();

// Chart & Modal
const cards = document.querySelectorAll('.cards .card');
const modal = document.getElementById('chart-modal');
const closeBtn = document.getElementById('chart-close');
const chartCtx = document.getElementById('chart-canvas').getContext('2d');
const filterButtons = document.querySelectorAll('#chart-filters button');
let chartInstance = null;
let selectedPeriod = 'day';
let activeCard = null;

const fieldMap = {
    'spannung': 'voltage',
    'strom': 'current',
    'leistung': 'power',
    'energie-netzbezug': 'grid_import',
    'energie-einspeisung': 'grid_export'
};

filterButtons.forEach(btn=>{
    btn.addEventListener('click', ()=>{
        selectedPeriod = btn.dataset.period;
        filterButtons.forEach(b=>b.classList.remove('active'));
        btn.classList.add('active');
        updateChart();
    });
});

cards.forEach(card=>{
    card.addEventListener('click', ()=>{
        activeCard = card;
        modal.style.display = 'flex';
        updateChart();
    });
});

function updateChart(){
    if(!activeCard) return;
    const cardText = activeCard.querySelector('.small').textContent.toLowerCase();
    const field = fieldMap[cardText];
    if(!field) return;

    const history = <?= json_encode($historyData) ?>;
    const now = new Date();
    let filtered = history.filter(d=>{
        const ts = new Date(d.timestamp);
        switch(selectedPeriod){
            case 'day': return ts >= new Date(now - 1*24*60*60*1000);
            case 'week': return ts >= new Date(now - 7*24*60*60*1000);
            case 'month': return ts >= new Date(now.setMonth(now.getMonth()-1));
            case 'year': return ts >= new Date(now.setFullYear(now.getFullYear()-1));
        }
        return true;
    });

    const labels = filtered.map(d=>d.timestamp);
    const values = filtered.map(d=>d[field] ?? 0);

    if(chartInstance) chartInstance.destroy();
    chartInstance = new Chart(chartCtx,{
        type:'line',
        data:{
            labels: labels,
            datasets:[{
                label: activeCard.querySelector('.small').textContent,
                data: values,
                borderColor:'#00ffcc',
                backgroundColor:'rgba(0,255,204,0.2)',
                tension:0.2,
                fill:true
            }]
        },
        options:{
            scales:{
                x:{title:{display:true,text:'Zeit'}},
                y:{title:{display:true,text:activeCard.querySelector('.small').textContent}}
            }
        }
    });
}

closeBtn.onclick = ()=> modal.style.display='none';
window.onclick = e=>{if(e.target==modal) modal.style.display='none';};

async function loadLiveData(){
    try{
        const resp = await fetch('data.json');
        const data = await resp.json();
        document.querySelectorAll('.card').forEach(card=>{
            const text = card.querySelector('.small').textContent.toLowerCase();
            switch(text){
                case 'spannung': card.querySelector('.value').textContent = data.voltage + ' V'; break;
                case 'strom': card.querySelector('.value').textContent = data.current + ' A'; break;
                case 'leistung': card.querySelector('.value').textContent = data.power + ' W'; break;
                case 'energie-netzbezug': card.querySelector('.value').textContent = data.grid_import + ' kWh'; break;
                case 'energie-einspeisung': card.querySelector('.value').textContent = data.grid_export + ' kWh'; break;
            }
        });
    } catch(e){ console.error('Fehler beim Laden der Live-Daten', e); }
}
setInterval(loadLiveData,10000);
loadLiveData();
</script>
</body>
</html>
