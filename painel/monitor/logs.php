<?php
session_start();
require_once '../../config/database.php';
require_once '../../config/auth.php';

verificarAutenticacao();
verificarPermissao('monitor');

$page_title = 'Logs do Sistema - Meritus';
include '../../includes/header.php';
include '../../includes/sidebar.php';
?>

<div class="main-content">
    <div class="content-header">
        <h1>Logs do Sistema</h1>
        <div class="header-actions">
            <button class="btn btn-primary" onclick="filtrarLogs()">Aplicar Filtros</button>
            <button class="btn btn-outline" onclick="limparLogs()">Limpar Logs Antigos</button>
            <button class="btn btn-outline" onclick="exportarLogs()">Exportar Logs</button>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Filtros de Pesquisa</h2>
        </div>
        <div class="card-body">
            <div class="filtros-grid">
                <div class="form-group">
                    <label for="filtro-data-inicio">Data Início</label>
                    <input type="datetime-local" id="filtro-data-inicio" class="form-control">
                </div>
                <div class="form-group">
                    <label for="filtro-data-fim">Data Fim</label>
                    <input type="datetime-local" id="filtro-data-fim" class="form-control">
                </div>
                <div class="form-group">
                    <label for="filtro-nivel">Nível</label>
                    <select id="filtro-nivel" class="form-control" multiple>
                        <option value="error">Error</option>
                        <option value="warning">Warning</option>
                        <option value="info">Info</option>
                        <option value="debug">Debug</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="filtro-usuario">Usuário</label>
                    <select id="filtro-usuario" class="form-control">
                        <option value="">Todos os Usuários</option>
                        <?php
                        $usuarios = getTodosUsuarios();
                        foreach ($usuarios as $usuario):
                        ?>
                        <option value="<?php echo $usuario['id']; ?>"><?php echo htmlspecialchars($usuario['nome']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="filtro-acao">Ação</label>
                    <input type="text" id="filtro-acao" class="form-control" placeholder="Buscar ação...">
                </div>
                <div class="form-group">
                    <label for="filtro-ip">Endereço IP</label>
                    <input type="text" id="filtro-ip" class="form-control" placeholder="Buscar IP...">
                </div>
            </div>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Estatísticas dos Logs</h2>
            <div class="card-actions">
                <select class="form-control" id="periodo-stats" onchange="atualizarStats()">
                    <option value="24h">Últimas 24 horas</option>
                    <option value="7d">Últimos 7 dias</option>
                    <option value="30d">Últimos 30 dias</option>
                </select>
            </div>
        </div>
        <div class="card-body">
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-number" id="total-logs">0</div>
                    <div class="stat-label">Total de Logs</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number" id="logs-error">0</div>
                    <div class="stat-label">Erros</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number" id="logs-warning">0</div>
                    <div class="stat-label">Avisos</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number" id="logs-info">0</div>
                    <div class="stat-label">Informações</div>
                </div>
            </div>
            
            <div class="graficos-container">
                <div class="grafico-card">
                    <h4>Logs por Nível</h4>
                    <div class="grafico-pizza" id="grafico-niveis">
                        <!-- Gráfico será renderizado via JavaScript -->
                    </div>
                </div>
                <div class="grafico-card">
                    <h4>Logs por Hora</h4>
                    <div class="grafico-barras" id="grafico-horas">
                        <!-- Gráfico será renderizado via JavaScript -->
                    </div>
                </div>
            </div>
            
            <div class="grafico-card">
                <h4>Logs por Dia</h4>
                <div class="grafico-linha" id="grafico-dias">
                    <!-- Gráfico será renderizado via JavaScript -->
                </div>
            </div>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Logs Detalhados</h2>
            <div class="card-actions">
                <span class="result-count" id="result-count">0 resultados</span>
                <button class="btn btn-sm btn-outline" onclick="alternarVisao()">Visão: Tabela</button>
            </div>
        </div>
        <div class="card-body">
            <div id="logs-container">
                <!-- Logs serão carregados via JavaScript -->
            </div>
            
            <div class="paginacao" id="paginacao">
                <!-- Paginação será carregada via JavaScript -->
            </div>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Logs Críticos</h2>
            <div class="card-actions">
                <button class="btn btn-sm btn-outline" onclick="marcarResolvidos()">Marcar como Resolvidos</button>
            </div>
        </div>
        <div class="card-body">
            <div class="logs-criticos" id="logs-criticos">
                <!-- Logs críticos serão carregados via JavaScript -->
            </div>
        </div>
    </div>
</div>

<?php
// Funções helper
function getTodosUsuarios() {
    $db = getDB();
    $stmt = $db->query("SELECT id, nome FROM usuarios WHERE status = 'ativo' ORDER BY nome");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getLogsFiltrados($filtros = [], $pagina = 1, $limite = 50) {
    $db = getDB();
    
    $sql = "
        SELECT 
            l.*,
            u.nome as usuario_nome,
            u.cargo as usuario_cargo
        FROM logs_sistema l
        LEFT JOIN usuarios u ON l.usuario_id = u.id
        WHERE 1=1
    ";
    
    $params = [];
    
    if (!empty($filtros['data_inicio'])) {
        $sql .= " AND l.data >= ?";
        $params[] = $filtros['data_inicio'];
    }
    
    if (!empty($filtros['data_fim'])) {
        $sql .= " AND l.data <= ?";
        $params[] = $filtros['data_fim'];
    }
    
    if (!empty($filtros['niveis'])) {
        $placeholders = str_repeat('?,', count($filtros['niveis']) - 1) . '?';
        $sql .= " AND l.nivel IN ($placeholders)";
        $params = array_merge($params, $filtros['niveis']);
    }
    
    if (!empty($filtros['usuario_id'])) {
        $sql .= " AND l.usuario_id = ?";
        $params[] = $filtros['usuario_id'];
    }
    
    if (!empty($filtros['acao'])) {
        $sql .= " AND l.acao LIKE ?";
        $params[] = '%' . $filtros['acao'] . '%';
    }
    
    if (!empty($filtros['ip'])) {
        $sql .= " AND l.ip LIKE ?";
        $params[] = '%' . $filtros['ip'] . '%';
    }
    
    $sql .= " ORDER BY l.data DESC";
    
    // Adicionar paginação
    $offset = ($pagina - 1) * $limite;
    $sql .= " LIMIT ? OFFSET ?";
    $params[] = $limite;
    $params[] = $offset;
    
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getTotalLogs($filtros = []) {
    $db = getDB();
    
    $sql = "SELECT COUNT(*) as total FROM logs_sistema l WHERE 1=1";
    $params = [];
    
    // Aplicar mesmos filtros da função getLogsFiltrados
    if (!empty($filtros['data_inicio'])) {
        $sql .= " AND l.data >= ?";
        $params[] = $filtros['data_inicio'];
    }
    
    if (!empty($filtros['data_fim'])) {
        $sql .= " AND l.data <= ?";
        $params[] = $filtros['data_fim'];
    }
    
    if (!empty($filtros['niveis'])) {
        $placeholders = str_repeat('?,', count($filtros['niveis']) - 1) . '?';
        $sql .= " AND l.nivel IN ($placeholders)";
        $params = array_merge($params, $filtros['niveis']);
    }
    
    if (!empty($filtros['usuario_id'])) {
        $sql .= " AND l.usuario_id = ?";
        $params[] = $filtros['usuario_id'];
    }
    
    if (!empty($filtros['acao'])) {
        $sql .= " AND l.acao LIKE ?";
        $params[] = '%' . $filtros['acao'] . '%';
    }
    
    if (!empty($filtros['ip'])) {
        $sql .= " AND l.ip LIKE ?";
        $params[] = '%' . $filtros['ip'] . '%';
    }
    
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result['total'];
}

function getLogsStats($periodo = '24h') {
    $db = getDB();
    
    $intervalo = '';
    switch ($periodo) {
        case '24h':
            $intervalo = 'INTERVAL 24 HOUR';
            break;
        case '7d':
            $intervalo = 'INTERVAL 7 DAY';
            break;
        case '30d':
            $intervalo = 'INTERVAL 30 DAY';
            break;
    }
    
    $stmt = $db->prepare("
        SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN nivel = 'error' THEN 1 ELSE 0 END) as errors,
            SUM(CASE WHEN nivel = 'warning' THEN 1 ELSE 0 END) as warnings,
            SUM(CASE WHEN nivel = 'info' THEN 1 ELSE 0 END) as infos,
            SUM(CASE WHEN nivel = 'debug' THEN 1 ELSE 0 END) as debugs
        FROM logs_sistema 
        WHERE data >= DATE_SUB(NOW(), $intervalo)
    ");
    $stmt->execute();
    
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function getLogsCriticos() {
    $db = getDB();
    $stmt = $db->query("
        SELECT 
            l.*,
            u.nome as usuario_nome
        FROM logs_sistema l
        LEFT JOIN usuarios u ON l.usuario_id = u.id
        WHERE l.nivel = 'error' 
        AND l.data >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
        ORDER BY l.data DESC
        LIMIT 20
    ");
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<script>
let paginaAtual = 1;
let visaoTabela = true;
let filtrosAtuais = {};

function carregarLogs(pagina = 1) {
    paginaAtual = pagina;
    
    const filtros = coletarFiltros();
    filtrosAtuais = filtros;
    
    fetch('../api/logs.php?action=carregar&pagina=' + pagina, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(filtros)
    })
    .then(response => response.json())
    .then(data => {
        renderizarLogs(data.logs);
        renderizarPaginacao(data.total, data.limite, pagina);
        atualizarContador(data.total);
    });
}

function coletarFiltros() {
    return {
        data_inicio: document.getElementById('filtro-data-inicio').value,
        data_fim: document.getElementById('filtro-data-fim').value,
        niveis: Array.from(document.getElementById('filtro-nivel').selectedOptions).map(opt => opt.value),
        usuario_id: document.getElementById('filtro-usuario').value,
        acao: document.getElementById('filtro-acao').value,
        ip: document.getElementById('filtro-ip').value
    };
}

function renderizarLogs(logs) {
    const container = document.getElementById('logs-container');
    
    if (visaoTabela) {
        container.innerHTML = `
            <div class="logs-tabela">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Data/Hora</th>
                            <th>Nível</th>
                            <th>Usuário</th>
                            <th>Ação</th>
                            <th>IP</th>
                            <th>Detalhes</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${logs.map(log => `
                            <tr class="log-${log.nivel}">
                                <td>${formatDateTime(log.data)}</td>
                                <td><span class="log-nivel badge-${log.nivel}">${log.nivel.toUpperCase()}</span></td>
                                <td>${log.usuario_nome || 'Sistema'}</td>
                                <td>${log.acao}</td>
                                <td>${log.ip}</td>
                                <td class="log-detalhes">${log.detalhes}</td>
                                <td>
                                    <button class="btn btn-sm btn-outline" onclick="verDetalhes(${log.id})">Ver</button>
                                </td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            </div>
        `;
    } else {
        container.innerHTML = `
            <div class="logs-cards">
                ${logs.map(log => `
                    <div class="log-card log-${log.nivel}">
                        <div class="log-card-header">
                            <div class="log-meta">
                                <span class="log-nivel badge-${log.nivel}">${log.nivel.toUpperCase()}</span>
                                <span class="log-data">${formatDateTime(log.data)}</span>
                            </div>
                            <div class="log-usuario">${log.usuario_nome || 'Sistema'}</div>
                        </div>
                        <div class="log-card-body">
                            <div class="log-acao">${log.acao}</div>
                            <div class="log-detalhes">${log.detalhes}</div>
                            <div class="log-ip">IP: ${log.ip}</div>
                        </div>
                    </div>
                `).join('')}
            </div>
        `;
    }
}

function renderizarPaginacao(total, limite, pagina) {
    const container = document.getElementById('paginacao');
    const totalPaginas = Math.ceil(total / limite);
    
    let html = '<div class="paginacao-info">';
    html += `Mostrando ${((pagina - 1) * limite) + 1} a ${Math.min(pagina * limite, total)} de ${total} registros`;
    html += '</div>';
    
    html += '<div class="paginacao-botoes">';
    
    // Botão anterior
    html += `<button class="btn btn-sm" onclick="carregarLogs(${pagina - 1})" ${pagina === 1 ? 'disabled' : ''}>Anterior</button>`;
    
    // Números das páginas
    const inicio = Math.max(1, pagina - 2);
    const fim = Math.min(totalPaginas, pagina + 2);
    
    for (let i = inicio; i <= fim; i++) {
        html += `<button class="btn btn-sm ${i === pagina ? 'btn-primary' : 'btn-outline'}" onclick="carregarLogs(${i})">${i}</button>`;
    }
    
    // Botão próximo
    html += `<button class="btn btn-sm" onclick="carregarLogs(${pagina + 1})" ${pagina === totalPaginas ? 'disabled' : ''}>Próximo</button>`;
    
    html += '</div>';
    
    container.innerHTML = html;
}

function atualizarContador(total) {
    document.getElementById('result-count').textContent = `${total} resultados`;
}

function filtrarLogs() {
    carregarLogs(1);
}

function alternarVisao() {
    visaoTabela = !visaoTabela;
    event.target.textContent = `Visão: ${visaoTabela ? 'Tabela' : 'Cards'}`;
    carregarLogs(paginaAtual);
}

function atualizarStats() {
    const periodo = document.getElementById('periodo-stats').value;
    
    fetch('../api/logs.php?action=stats&periodo=' + periodo)
        .then(response => response.json())
        .then(data => {
            atualizarCardsStats(data);
            renderizarGraficos(data);
        });
}

function atualizarCardsStats(stats) {
    document.getElementById('total-logs').textContent = stats.total;
    document.getElementById('logs-error').textContent = stats.errors;
    document.getElementById('logs-warning').textContent = stats.warnings;
    document.getElementById('logs-info').textContent = stats.infos;
}

function renderizarGraficos(data) {
    // Gráfico de pizza - Logs por nível
    const niveisContainer = document.getElementById('grafico-niveis');
    const total = data.total;
    
    niveisContainer.innerHTML = `
        <div class="pizza-chart">
            <div class="pizza-slice" style="--percent: ${(data.errors / total) * 100}; --color: #e74c3c;"></div>
            <div class="pizza-slice" style="--percent: ${(data.warnings / total) * 100}; --color: #f39c12;"></div>
            <div class="pizza-slice" style="--percent: ${(data.infos / total) * 100}; --color: #3498db;"></div>
            <div class="pizza-slice" style="--percent: ${(data.debugs / total) * 100}; --color: #95a5a6;"></div>
        </div>
        <div class="pizza-legend">
            <div class="legend-item"><span class="legend-color" style="background: #e74c3c;"></span> Erros (${data.errors})</div>
            <div class="legend-item"><span class="legend-color" style="background: #f39c12;"></span> Avisos (${data.warnings})</div>
            <div class="legend-item"><span class="legend-color" style="background: #3498db;"></span> Infos (${data.infos})</div>
            <div class="legend-item"><span class="legend-color" style="background: #95a5a6;"></span> Debug (${data.debugs})</div>
        </div>
    `;
    
    // Gráfico de barras - Logs por hora (simulado)
    const horasContainer = document.getElementById('grafico-horas');
    const horasData = gerarDadosHoras();
    
    horasContainer.innerHTML = `
        <div class="barras-chart">
            ${horasData.map(hora => `
                <div class="bar-item">
                    <div class="bar" style="height: ${hora.valor}%; background: ${hora.cor};"></div>
                    <div class="bar-label">${hora.hora}h</div>
                </div>
            `).join('')}
        </div>
    `;
}

function gerarDadosHoras() {
    const horas = [];
    const cores = ['#e74c3c', '#f39c12', '#3498db', '#27ae60'];
    
    for (let i = 0; i < 24; i++) {
        horas.push({
            hora: i,
            valor: Math.random() * 100,
            cor: cores[Math.floor(Math.random() * cores.length)]
        });
    }
    
    return horas;
}

function verDetalhes(logId) {
    fetch(`../api/logs.php?action=detalhes&id=${logId}`)
        .then(response => response.json())
        .then(data => {
            // Implementar modal de detalhes
            alert('Funcionalidade em desenvolvimento');
        });
}

function limparLogs() {
    if (confirm('Tem certeza que deseja limpar logs antigos (mais de 30 dias)?')) {
        fetch('../api/logs.php?action=limpar', {
            method: 'POST'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('Logs antigos limpos com sucesso!', 'success');
                carregarLogs();
            } else {
                showAlert(data.message, 'error');
            }
        });
    }
}

function exportarLogs() {
    const filtros = coletarFiltros();
    
    const params = new URLSearchParams();
    params.append('action', 'exportar');
    Object.keys(filtros).forEach(key => {
        if (filtros[key] && (Array.isArray(filtros[key]) ? filtros[key].length > 0 : true)) {
            if (Array.isArray(filtros[key])) {
                filtros[key].forEach(val => params.append(key + '[]', val));
            } else {
                params.append(key, filtros[key]);
            }
        }
    });
    
    window.location.href = '../api/logs.php?' + params.toString();
}

function marcarResolvidos() {
    const checkboxes = document.querySelectorAll('.log-critico-checkbox:checked');
    
    if (checkboxes.length === 0) {
        showAlert('Selecione pelo menos um log para marcar como resolvido', 'warning');
        return;
    }
    
    const ids = Array.from(checkboxes).map(cb => cb.value);
    
    fetch('../api/logs.php?action=marcar_resolvidos', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ ids: ids })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('Logs marcados como resolvidos!', 'success');
            carregarLogsCriticos();
        } else {
            showAlert(data.message, 'error');
        }
    });
}

function carregarLogsCriticos() {
    fetch('../api/logs.php?action=criticos')
        .then(response => response.json())
        .then(data => {
            renderizarLogsCriticos(data);
        });
}

function renderizarLogsCriticos(logs) {
    const container = document.getElementById('logs-criticos');
    
    container.innerHTML = logs.map(log => `
        <div class="log-critico-item">
            <div class="log-critico-header">
                <input type="checkbox" class="log-critico-checkbox" value="${log.id}">
                <span class="log-nivel badge-error">ERROR</span>
                <span class="log-data">${formatDateTime(log.data)}</span>
                <span class="log-usuario">${log.usuario_nome || 'Sistema'}</span>
            </div>
            <div class="log-critico-body">
                <div class="log-acao">${log.acao}</div>
                <div class="log-detalhes">${log.detalhes}</div>
                <div class="log-ip">IP: ${log.ip}</div>
            </div>
        </div>
    `).join('');
}

function formatDateTime(datetime) {
    const date = new Date(datetime);
    return date.toLocaleString('pt-BR');
}

// Carregar dados ao iniciar
document.addEventListener('DOMContentLoaded', function() {
    carregarLogs();
    atualizarStats();
    carregarLogsCriticos();
    
    // Auto-atualizar stats a cada 30 segundos
    setInterval(atualizarStats, 30000);
});
</script>

<style>
.filtros-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.graficos-container {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 30px;
    margin-bottom: 30px;
}

.grafico-card {
    background: white;
    border-radius: 10px;
    padding: 20px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.grafico-card h4 {
    margin: 0 0 20px 0;
    color: #2c3e50;
}

.pizza-chart {
    width: 200px;
    height: 200px;
    border-radius: 50%;
    background: conic-gradient(
        #e74c3c 0deg 72deg,
        #f39c12 72deg 144deg,
        #3498db 144deg 216deg,
        #95a5a6 216deg 360deg
    );
    margin: 0 auto 20px;
}

.pizza-legend {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px;
}

.legend-item {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 0.9rem;
}

.legend-color {
    width: 12px;
    height: 12px;
    border-radius: 2px;
}

.barras-chart {
    display: flex;
    align-items: end;
    height: 150px;
    gap: 5px;
}

.bar-item {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
}

.bar {
    width: 100%;
    background: #3498db;
    border-radius: 2px 2px 0 0;
    margin-bottom: 5px;
}

.bar-label {
    font-size: 0.7rem;
    color: #7f8c8d;
}

.logs-cards {
    display: grid;
    gap: 15px;
}

.log-card {
    background: white;
    border-radius: 10px;
    padding: 20px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    border-left: 4px solid #ecf0f1;
}

.log-card.log-error {
    border-left-color: #e74c3c;
    background: #fdf2f2;
}

.log-card.log-warning {
    border-left-color: #f39c12;
    background: #fef9e7;
}

.log-card.log-info {
    border-left-color: #3498db;
    background: #eef7ff;
}

.log-card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
}

.log-meta {
    display: flex;
    align-items: center;
    gap: 10px;
}

.log-data {
    font-size: 0.8rem;
    color: #7f8c8d;
}

.log-usuario {
    font-weight: 600;
    color: #2c3e50;
}

.log-card-body {
    space-y: 8px;
}

.log-acao {
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 8px;
}

.log-detalhes {
    color: #5d6d7e;
    margin-bottom: 8px;
}

.log-ip {
    font-size: 0.8rem;
    color: #95a5a6;
}

.paginacao {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 20px;
    padding-top: 20px;
    border-top: 1px solid #ecf0f1;
}

.paginacao-info {
    color: #7f8c8d;
    font-size: 0.9rem;
}

.paginacao-botoes {
    display: flex;
    gap: 5px;
}

.result-count {
    background: #3498db;
    color: white;
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 0.8rem;
}

.logs-criticos {
    display: grid;
    gap: 15px;
}

.log-critico-item {
    background: #fdf2f2;
    border: 1px solid #f5c6cb;
    border-radius: 10px;
    padding: 20px;
}

.log-critico-header {
    display: flex;
    align-items: center;
    gap: 15px;
    margin-bottom: 15px;
}

.log-critico-body {
    padding-left: 30px;
}

.log-critico-checkbox {
    width: 18px;
    height: 18px;
}

.log-nivel {
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 0.7rem;
    font-weight: 600;
    text-transform: uppercase;
}

.badge-error {
    background: #e74c3c;
    color: white;
}

.log-detalhes {
    max-width: 300px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
</style>

<?php include '../../includes/footer.php'; ?>
