<?php
session_start();
require_once '../../config/database.php';
require_once '../../config/auth.php';

verificarAutenticacao();
verificarPermissao('secretaria');

$page_title = 'Controle de Presença - Meritus';
include '../../includes/header.php';
include '../../includes/sidebar.php';
?>

<div class="main-content">
    <div class="content-header">
        <h1>Controle de Presença</h1>
        <div class="header-actions">
            <button class="btn btn-primary" onclick="salvarPresenca()">Salvar Presença</button>
            <button class="btn btn-outline" onclick="exportarPresenca()">Exportar</button>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Registro de Presença</h2>
            <div class="card-actions">
                <input type="date" id="data-presenca" class="form-control" value="<?php echo date('Y-m-d'); ?>" onchange="carregarPresenca()">
                <select class="form-control" id="filtro-unidade" onchange="filtrarMembros()">
                    <option value="">Todas as Unidades</option>
                    <?php
                    $unidades = getUnidades();
                    foreach ($unidades as $unidade):
                    ?>
                    <option value="<?php echo $unidade['id']; ?>"><?php echo htmlspecialchars($unidade['nome']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="card-body">
            <div class="presenca-container">
                <div class="presenca-stats">
                    <div class="stat-item">
                        <span class="stat-label">Total de Membros:</span>
                        <span class="stat-value" id="total-membros">0</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label">Presentes:</span>
                        <span class="stat-value presente" id="total-presentes">0</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label">Ausentes:</span>
                        <span class="stat-value ausente" id="total-ausentes">0</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label">Percentual:</span>
                        <span class="stat-value" id="percentual-presenca">0%</span>
                    </div>
                </div>
                
                <div class="presenca-grid" id="presenca-grid">
                    <!-- Membros serão carregados via JavaScript -->
                </div>
            </div>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Histórico de Presença</h2>
            <div class="card-actions">
                <select class="form-control" id="periodo-historico" onchange="carregarHistorico()">
                    <option value="7">Últimos 7 dias</option>
                    <option value="30">Últimos 30 dias</option>
                    <option value="90">Últimos 90 dias</option>
                </select>
            </div>
        </div>
        <div class="card-body">
            <div class="historico-chart">
                <canvas id="presenca-chart"></canvas>
            </div>
            
            <div class="historico-table">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Data</th>
                            <th>Presentes</th>
                            <th>Ausentes</th>
                            <th>Total</th>
                            <th>Percentual</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody id="historico-tbody">
                        <!-- Dados serão carregados via JavaScript -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Ranking de Presença</h2>
        </div>
        <div class="card-body">
            <div class="ranking-tabs">
                <button class="tab-btn active" onclick="mostrarRanking('membros')">Membros</button>
                <button class="tab-btn" onclick="mostrarRanking('unidades')">Unidades</button>
            </div>
            
            <div id="ranking-membros" class="ranking-content active">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Posição</th>
                            <th>Nome</th>
                            <th>Unidade</th>
                            <th>Presenças</th>
                            <th>Total</th>
                            <th>Percentual</th>
                        </tr>
                    </thead>
                    <tbody id="ranking-membros-tbody">
                        <!-- Dados serão carregados via JavaScript -->
                    </tbody>
                </table>
            </div>
            
            <div id="ranking-unidades" class="ranking-content">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Posição</th>
                            <th>Unidade</th>
                            <th>Presenças</th>
                            <th>Total</th>
                            <th>Percentual</th>
                        </tr>
                    </thead>
                    <tbody id="ranking-unidades-tbody">
                        <!-- Dados serão carregados via JavaScript -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php
// Funções helper
function getUnidades() {
    $db = getDB();
    $stmt = $db->query("SELECT id, nome FROM unidades WHERE status = 'ativo' ORDER BY nome");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<script>
let presencaData = {};
let membrosData = [];

function carregarPresenca() {
    const data = document.getElementById('data-presenca').value;
    const unidadeId = document.getElementById('filtro-unidade').value;
    
    fetch(`../api/presenca.php?action=carregar&data=${data}&unidade_id=${unidadeId}`)
        .then(response => response.json())
        .then(data => {
            membrosData = data.membros;
            presencaData = {};
            
            // Montar dados de presença
            data.membros.forEach(membro => {
                presencaData[membro.id] = membro.presente || false;
            });
            
            renderizarPresenca();
            atualizarEstatisticas();
        });
}

function renderizarPresenca() {
    const grid = document.getElementById('presenca-grid');
    grid.innerHTML = '';
    
    membrosData.forEach(membro => {
        const presente = presencaData[membro.id] || false;
        
        const membroDiv = document.createElement('div');
        membroDiv.className = `membro-presenca-card ${presente ? 'presente' : 'ausente'}`;
        membroDiv.innerHTML = `
            <div class="membro-checkbox">
                <input type="checkbox" id="membro-${membro.id}" ${presente ? 'checked' : ''} 
                       onchange="togglePresenca(${membro.id})">
                <label for="membro-${membro.id}"></label>
            </div>
            <div class="membro-info">
                <div class="membro-avatar">
                    <img src="assets/img/default-avatar.png" alt="${membro.nome}">
                </div>
                <div class="membro-detalhes">
                    <div class="membro-nome">${membro.nome}</div>
                    <div class="membro-unidade">${membro.unidade}</div>
                </div>
            </div>
            <div class="membro-status">
                <span class="status-badge ${presente ? 'presente' : 'ausente'}">
                    ${presente ? 'Presente' : 'Ausente'}
                </span>
            </div>
        `;
        
        grid.appendChild(membroDiv);
    });
}

function togglePresenca(membroId) {
    presencaData[membroId] = !presencaData[membroId];
    atualizarEstatisticas();
    
    // Atualizar visual
    const checkbox = document.getElementById(`membro-${membroId}`);
    const card = checkbox.closest('.membro-presenca-card');
    const badge = card.querySelector('.status-badge');
    
    if (presencaData[membroId]) {
        card.classList.add('presente');
        card.classList.remove('ausente');
        badge.classList.add('presente');
        badge.classList.remove('ausente');
        badge.textContent = 'Presente';
    } else {
        card.classList.remove('presente');
        card.classList.add('ausente');
        badge.classList.remove('presente');
        badge.classList.add('ausente');
        badge.textContent = 'Ausente';
    }
}

function atualizarEstatisticas() {
    const total = membrosData.length;
    const presentes = Object.values(presencaData).filter(p => p).length;
    const ausentes = total - presentes;
    const percentual = total > 0 ? ((presentes / total) * 100).toFixed(1) : 0;
    
    document.getElementById('total-membros').textContent = total;
    document.getElementById('total-presentes').textContent = presentes;
    document.getElementById('total-ausentes').textContent = ausentes;
    document.getElementById('percentual-presenca').textContent = percentual + '%';
}

function salvarPresenca() {
    const data = document.getElementById('data-presenca').value;
    
    const dados = {
        action: 'salvar',
        data: data,
        presencas: presencaData
    };
    
    fetch('../api/presenca.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(dados)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('Presença salva com sucesso!', 'success');
        } else {
            showAlert(data.message || 'Erro ao salvar presença', 'error');
        }
    });
}

function filtrarMembros() {
    carregarPresenca();
}

function carregarHistorico() {
    const periodo = document.getElementById('periodo-historico').value;
    
    fetch(`../api/presenca.php?action=historico&periodo=${periodo}`)
        .then(response => response.json())
        .then(data => {
            renderizarHistorico(data);
            renderizarGrafico(data);
        });
}

function renderizarHistorico(data) {
    const tbody = document.getElementById('historico-tbody');
    tbody.innerHTML = '';
    
    data.forEach(item => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${formatDate(item.data)}</td>
            <td>${item.presentes}</td>
            <td>${item.ausentes}</td>
            <td>${item.total}</td>
            <td>${item.percentual}%</td>
            <td>
                <button class="btn btn-sm btn-outline" onclick="verDetalhes('${item.data}')">Ver Detalhes</button>
            </td>
        `;
        tbody.appendChild(row);
    });
}

function renderizarGrafico(data) {
    // Implementar gráfico com Chart.js ou biblioteca similar
    const ctx = document.getElementById('presenca-chart').getContext('2d');
    
    // Dados para o gráfico
    const labels = data.map(item => formatDate(item.data));
    const presentes = data.map(item => item.presentes);
    const ausentes = data.map(item => item.ausentes);
    
    // Implementar gráfico (exemplo básico)
    console.log('Gráfico:', { labels, presentes, ausentes });
}

function carregarRanking() {
    fetch('../api/presenca.php?action=ranking')
        .then(response => response.json())
        .then(data => {
            renderizarRankingMembros(data.membros);
            renderizarRankingUnidades(data.unidades);
        });
}

function renderizarRankingMembros(membros) {
    const tbody = document.getElementById('ranking-membros-tbody');
    tbody.innerHTML = '';
    
    membros.forEach((membro, index) => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${index + 1}</td>
            <td>${membro.nome}</td>
            <td>${membro.unidade}</td>
            <td>${membro.presencas}</td>
            <td>${membro.total}</td>
            <td>${membro.percentual}%</td>
        `;
        tbody.appendChild(row);
    });
}

function renderizarRankingUnidades(unidades) {
    const tbody = document.getElementById('ranking-unidades-tbody');
    tbody.innerHTML = '';
    
    unidades.forEach((unidade, index) => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${index + 1}</td>
            <td>${unidade.nome}</td>
            <td>${unidade.presencas}</td>
            <td>${unidade.total}</td>
            <td>${unidade.percentual}%</td>
        `;
        tbody.appendChild(row);
    });
}

function mostrarRanking(tipo) {
    document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
    document.querySelectorAll('.ranking-content').forEach(content => content.classList.remove('active'));
    
    event.target.classList.add('active');
    document.getElementById(`ranking-${tipo}`).classList.add('active');
}

function verDetalhes(data) {
    // Implementar visualização detalhada de presença de uma data específica
    alert('Funcionalidade em desenvolvimento');
}

function exportarPresenca() {
    const data = document.getElementById('data-presenca').value;
    window.location.href = `../api/presenca.php?action=exportar&data=${data}`;
}

function formatDate(dateStr) {
    const date = new Date(dateStr);
    return date.toLocaleDateString('pt-BR');
}

// Carregar dados ao iniciar
document.addEventListener('DOMContentLoaded', function() {
    carregarPresenca();
    carregarHistorico();
    carregarRanking();
});
</script>

<style>
.presenca-container {
    margin-top: 20px;
}

.presenca-stats {
    display: flex;
    justify-content: space-around;
    background: #f8f9fa;
    padding: 20px;
    border-radius: 10px;
    margin-bottom: 30px;
}

.stat-item {
    text-align: center;
}

.stat-label {
    display: block;
    font-size: 0.9rem;
    color: #7f8c8d;
    margin-bottom: 5px;
}

.stat-value {
    display: block;
    font-size: 1.8rem;
    font-weight: bold;
    color: #2c3e50;
}

.stat-value.presente {
    color: #27ae60;
}

.stat-value.ausente {
    color: #e74c3c;
}

.presenca-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 15px;
}

.membro-presenca-card {
    background: white;
    border: 2px solid #ecf0f1;
    border-radius: 10px;
    padding: 15px;
    display: flex;
    align-items: center;
    gap: 15px;
    transition: all 0.3s ease;
}

.membro-presenca-card.presente {
    border-color: #27ae60;
    background: #f0f9f0;
}

.membro-presenca-card.ausente {
    border-color: #e74c3c;
    background: #fdf2f2;
}

.membro-checkbox {
    position: relative;
}

.membro-checkbox input[type="checkbox"] {
    width: 20px;
    height: 20px;
    cursor: pointer;
}

.membro-checkbox label {
    position: absolute;
    top: 0;
    left: 0;
    width: 20px;
    height: 20px;
    cursor: pointer;
}

.membro-info {
    display: flex;
    align-items: center;
    gap: 10px;
    flex: 1;
}

.membro-avatar img {
    width: 40px;
    height: 40px;
    border-radius: 50%;
}

.membro-nome {
    font-weight: 600;
    color: #2c3e50;
}

.membro-unidade {
    font-size: 0.8rem;
    color: #7f8c8d;
}

.status-badge {
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 0.8rem;
    font-weight: 600;
}

.status-badge.presente {
    background: #27ae60;
    color: white;
}

.status-badge.ausente {
    background: #e74c3c;
    color: white;
}

.historico-chart {
    height: 300px;
    margin-bottom: 30px;
    background: white;
    border-radius: 10px;
    padding: 20px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.ranking-tabs {
    display: flex;
    border-bottom: 1px solid #ecf0f1;
    margin-bottom: 20px;
}

.tab-btn {
    background: none;
    border: none;
    padding: 10px 20px;
    cursor: pointer;
    border-bottom: 2px solid transparent;
}

.tab-btn.active {
    border-bottom-color: #3498db;
    color: #3498db;
}

.ranking-content {
    display: none;
}

.ranking-content.active {
    display: block;
}
</style>

<?php include '../../includes/footer.php'; ?>
