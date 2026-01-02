<?php
session_start();
require_once '../../config/database.php';
require_once '../../config/auth.php';

verificarAutenticacao();
verificarPermissao('monitor');

$page_title = 'Monitoramento em Tempo Real - Meritus';
include '../../includes/header.php';
include '../../includes/sidebar.php';
?>

<div class="main-content">
    <div class="content-header">
        <h1>Monitoramento em Tempo Real</h1>
        <div class="header-actions">
            <button class="btn btn-primary" onclick="atualizarDados()">Atualizar Agora</button>
            <button class="btn btn-outline" onclick="exportarLogs()">Exportar Logs</button>
        </div>
    </div>
    
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-number" id="usuarios-online">0</div>
            <div class="stat-label">Usuários Online</div>
        </div>
        <div class="stat-card">
            <div class="stat-number" id="acessos-hoje">0</div>
            <div class="stat-label">Acessos Hoje</div>
        </div>
        <div class="stat-card">
            <div class="stat-number" id="atividades-recentes">0</div>
            <div class="stat-label">Atividades Recentes</div>
        </div>
        <div class="stat-card">
            <div class="stat-number" id="status-sistema">✅</div>
            <div class="stat-label">Status do Sistema</div>
        </div>
    </div>
    
    <div class="monitor-grid">
        <div class="monitor-card">
            <div class="card-header">
                <h2 class="card-title">Usuários Online</h2>
                <div class="status-indicator online"></div>
            </div>
            <div class="card-body">
                <div class="usuarios-online-lista" id="usuarios-online-lista">
                    <!-- Será carregado via JavaScript -->
                </div>
            </div>
        </div>
        
        <div class="monitor-card">
            <div class="card-header">
                <h2 class="card-title">Acessos Recentes</h2>
                <div class="card-actions">
                    <select class="form-control" id="filtro-acessos" onchange="filtrarAcessos()">
                        <option value="todos">Todos</option>
                        <option value="login">Logins</option>
                        <option value="logout">Logouts</option>
                        <option value="erro">Erros</option>
                    </select>
                </div>
            </div>
            <div class="card-body">
                <div class="acessos-timeline" id="acessos-timeline">
                    <!-- Será carregado via JavaScript -->
                </div>
            </div>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Atividades do Sistema</h2>
            <div class="card-actions">
                <button class="btn btn-sm btn-outline" onclick="pausarMonitoramento()">Pausar</button>
                <button class="btn btn-sm btn-outline" onclick="limparAtividades()">Limpar</button>
            </div>
        </div>
        <div class="card-body">
            <div class="atividades-monitor" id="atividades-monitor">
                <!-- Será carregado via JavaScript -->
            </div>
        </div>
    </div>
    
    <div class="monitor-grid">
        <div class="monitor-card">
            <div class="card-header">
                <h2 class="card-title">Performance do Banco</h2>
                <div class="status-indicator" id="db-status"></div>
            </div>
            <div class="card-body">
                <div class="db-metrics">
                    <div class="metric-item">
                        <span class="metric-label">Conexões Ativas:</span>
                        <span class="metric-value" id="db-conexoes">0</span>
                    </div>
                    <div class="metric-item">
                        <span class="metric-label">Tempo de Resposta:</span>
                        <span class="metric-value" id="db-tempo">0ms</span>
                    </div>
                    <div class="metric-item">
                        <span class="metric-label">Queries/Segundo:</span>
                        <span class="metric-value" id="db-queries">0</span>
                    </div>
                    <div class="metric-item">
                        <span class="metric-label">Uso de Memória:</span>
                        <span class="metric-value" id="db-memoria">0MB</span>
                    </div>
                </div>
                <div class="db-chart">
                    <canvas id="db-performance-chart"></canvas>
                </div>
            </div>
        </div>
        
        <div class="monitor-card">
            <div class="card-header">
                <h2 class="card-title">Alertas do Sistema</h2>
                <div class="card-actions">
                    <span class="alert-count" id="alert-count">0</span>
                </div>
            </div>
            <div class="card-body">
                <div class="alertas-lista" id="alertas-lista">
                    <!-- Será carregado via JavaScript -->
                </div>
            </div>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Logs do Sistema</h2>
            <div class="card-actions">
                <select class="form-control" id="filtro-logs" onchange="filtrarLogs()">
                    <option value="todos">Todos</option>
                    <option value="error">Erros</option>
                    <option value="warning">Avisos</option>
                    <option value="info">Informações</option>
                    <option value="debug">Debug</option>
                </select>
                <button class="btn btn-sm btn-outline" onclick="baixarLogs()">Baixar Logs</button>
            </div>
        </div>
        <div class="card-body">
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
                        </tr>
                    </thead>
                    <tbody id="logs-tbody">
                        <!-- Será carregado via JavaScript -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php
// Funções helper
function getUsuariosOnline() {
    $db = getDB();
    $stmt = $db->query("
        SELECT 
            u.id, u.nome, u.cargo, u.unidade_id,
            un.nome as unidade,
            s.ultima_atividade,
            TIMESTAMPDIFF(MINUTE, s.ultima_atividade, NOW()) as minutos_inativo
        FROM usuarios u
        JOIN sessoes s ON u.id = s.usuario_id
        LEFT JOIN unidades un ON u.unidade_id = un.id
        WHERE s.ativa = 1 AND s.ultima_atividade >= DATE_SUB(NOW(), INTERVAL 30 MINUTE)
        ORDER BY s.ultima_atividade DESC
    ");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getAcessosRecentes() {
    $db = getDB();
    $stmt = $db->query("
        SELECT 
            l.*,
            u.nome as usuario_nome
        FROM logs_sistema l
        LEFT JOIN usuarios u ON l.usuario_id = u.id
        WHERE l.data >= DATE_SUB(NOW(), INTERVAL 2 HOUR)
        ORDER BY l.data DESC
        LIMIT 50
    ");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getAtividadesRecentes() {
    $db = getDB();
    $stmt = $db->query("
        SELECT 
            'membro' as tipo,
            m.nome as descricao,
            'Cadastro' as acao,
            m.data_cadastro as data,
            u.nome as usuario
        FROM membros m
        JOIN usuarios u ON m.usuario_cadastro_id = u.id
        WHERE m.data_cadastro >= DATE_SUB(NOW(), INTERVAL 1 HOUR)
        
        UNION ALL
        
        SELECT 
            'ponto' as tipo,
            mp.descricao,
            'Pontos',
            mp.data,
            u.nome
        FROM membros_pontos mp
        JOIN usuarios u ON mp.usuario_lancamento_id = u.id
        WHERE mp.data >= DATE_SUB(NOW(), INTERVAL 1 HOUR)
        
        UNION ALL
        
        SELECT 
            'presenca' as tipo,
            m.nome,
            'Presença',
            p.data,
            u.nome
        FROM presenca p
        JOIN membros m ON p.membro_id = m.id
        JOIN usuarios u ON p.usuario_registro_id = u.id
        WHERE p.data >= DATE_SUB(NOW(), INTERVAL 1 HOUR)
        
        ORDER BY data DESC
        LIMIT 20
    ");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getDatabaseStats() {
    $db = getDB();
    
    // Simular estatísticas do banco
    $stats = [
        'conexoes' => rand(5, 15),
        'tempo_resposta' => rand(10, 50),
        'queries_por_segundo' => rand(50, 200),
        'memoria_uso' => rand(100, 500)
    ];
    
    return $stats;
}

function getAlertasSistema() {
    $db = getDB();
    $stmt = $db->query("
        SELECT * FROM alertas_sistema 
        WHERE status = 'ativo' 
        ORDER BY data_criacao DESC 
        LIMIT 10
    ");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getLogsSistema($filtro = 'todos') {
    $db = getDB();
    
    $sql = "
        SELECT 
            l.*,
            u.nome as usuario_nome
        FROM logs_sistema l
        LEFT JOIN usuarios u ON l.usuario_id = u.id
    ";
    
    $params = [];
    
    if ($filtro !== 'todos') {
        $sql .= " WHERE l.nivel = ?";
        $params[] = $filtro;
    }
    
    $sql .= " ORDER BY l.data DESC LIMIT 100";
    
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<script>
let monitoramentoAtivo = true;
let atualizacaoInterval;

function iniciarMonitoramento() {
    atualizarDados();
    atualizacaoInterval = setInterval(atualizarDados, 5000); // Atualizar a cada 5 segundos
}

function pararMonitoramento() {
    if (atualizacaoInterval) {
        clearInterval(atualizacaoInterval);
    }
}

function atualizarDados() {
    if (!monitoramentoAtivo) return;
    
    // Atualizar usuários online
    fetch('../api/realtime.php?action=usuarios_online')
        .then(response => response.json())
        .then(data => {
            atualizarUsuariosOnline(data);
        });
    
    // Atualizar acessos recentes
    fetch('../api/realtime.php?action=acessos_recentes')
        .then(response => response.json())
        .then(data => {
            atualizarAcessosRecentes(data);
        });
    
    // Atualizar atividades
    fetch('../api/realtime.php?action=atividades')
        .then(response => response.json())
        .then(data => {
            atualizarAtividades(data);
        });
    
    // Atualizar estatísticas do banco
    fetch('../api/realtime.php?action=db_stats')
        .then(response => response.json())
        .then(data => {
            atualizarDBStats(data);
        });
    
    // Atualizar alertas
    fetch('../api/realtime.php?action=alertas')
        .then(response => response.json())
        .then(data => {
            atualizarAlertas(data);
        });
    
    // Atualizar logs
    fetch('../api/realtime.php?action=logs')
        .then(response => response.json())
        .then(data => {
            atualizarLogs(data);
        });
}

function atualizarUsuariosOnline(usuarios) {
    const container = document.getElementById('usuarios-online-lista');
    const contador = document.getElementById('usuarios-online');
    
    contador.textContent = usuarios.length;
    
    container.innerHTML = usuarios.map(usuario => `
        <div class="usuario-online-item">
            <div class="usuario-avatar">
                <img src="assets/img/default-avatar.png" alt="${usuario.nome}">
                <div class="status-dot ${usuario.minutos_inativo < 5 ? 'online' : 'away'}"></div>
            </div>
            <div class="usuario-info">
                <div class="usuario-nome">${usuario.nome}</div>
                <div class="usuario-detalhes">
                    <span class="usuario-cargo">${usuario.cargo}</span>
                    <span class="usuario-unidade">${usuario.unidade || 'N/A'}</span>
                </div>
                <div class="usuario-atividade">
                    Ativo há ${usuario.minutos_inativo} min
                </div>
            </div>
        </div>
    `).join('');
}

function atualizarAcessosRecentes(acessos) {
    const container = document.getElementById('acessos-timeline');
    const contador = document.getElementById('acessos-hoje');
    
    const acessosHoje = acessos.filter(a => {
        const data = new Date(a.data);
        const hoje = new Date();
        return data.toDateString() === hoje.toDateString();
    }).length;
    
    contador.textContent = acessosHoje;
    
    container.innerHTML = acessos.map(acesso => `
        <div class="acesso-item">
            <div class="acesso-icon ${getAcessoIconClass(acesso.acao)}">
                ${getAcessoIcon(acesso.acao)}
            </div>
            <div class="acesso-info">
                <div class="acesso-usuario">${acesso.usuario_nome || 'Sistema'}</div>
                <div class="acesso-acao">${acesso.acao}</div>
                <div class="acesso-data">${formatDateTime(acesso.data)}</div>
            </div>
        </div>
    `).join('');
}

function atualizarAtividades(atividades) {
    const container = document.getElementById('atividades-monitor');
    const contador = document.getElementById('atividades-recentes');
    
    contador.textContent = atividades.length;
    
    container.innerHTML = atividades.map(atividade => `
        <div class="atividade-item">
            <div class="atividade-tipo">${getAtividadeIcon(atividade.tipo)}</div>
            <div class="atividade-conteudo">
                <div class="atividade-descricao">${atividade.descricao}</div>
                <div class="atividade-detalhes">
                    <span class="atividade-acao">${atividade.acao}</span>
                    <span class="atividade-usuario">por ${atividade.usuario}</span>
                    <span class="atividade-data">${formatDateTime(atividade.data)}</span>
                </div>
            </div>
        </div>
    `).join('');
}

function atualizarDBStats(stats) {
    document.getElementById('db-conexoes').textContent = stats.conexoes;
    document.getElementById('db-tempo').textContent = stats.tempo_resposta + 'ms';
    document.getElementById('db-queries').textContent = stats.queries_por_segundo;
    document.getElementById('db-memoria').textContent = stats.memoria_uso + 'MB';
    
    // Atualizar status do banco
    const statusElement = document.getElementById('db-status');
    const statusSistema = document.getElementById('status-sistema');
    
    if (stats.tempo_resposta < 100 && stats.memoria_uso < 1000) {
        statusElement.className = 'status-indicator online';
        statusSistema.textContent = '✅';
    } else {
        statusElement.className = 'status-indicator warning';
        statusSistema.textContent = '⚠️';
    }
}

function atualizarAlertas(alertas) {
    const container = document.getElementById('alertas-lista');
    const contador = document.getElementById('alert-count');
    
    contador.textContent = alertas.length;
    
    container.innerHTML = alertas.map(alerta => `
        <div class="alerta-item ${alerta.tipo}">
            <div class="alerta-icon">${getAlertaIcon(alerta.tipo)}</div>
            <div class="alerta-info">
                <div class="alerta-titulo">${alerta.titulo}</div>
                <div class="alerta-mensagem">${alerta.mensagem}</div>
                <div class="alerta-data">${formatDateTime(alerta.data_criacao)}</div>
            </div>
        </div>
    `).join('');
}

function atualizarLogs(logs) {
    const tbody = document.getElementById('logs-tbody');
    
    tbody.innerHTML = logs.map(log => `
        <tr class="log-${log.nivel}">
            <td>${formatDateTime(log.data)}</td>
            <td><span class="log-nivel badge-${log.nivel}">${log.nivel.toUpperCase()}</span></td>
            <td>${log.usuario_nome || 'Sistema'}</td>
            <td>${log.acao}</td>
            <td>${log.ip}</td>
            <td>${log.detalhes}</td>
        </tr>
    `).join('');
}

function getAcessoIcon(acao) {
    const icons = {
        'login': '🔑',
        'logout': '🚪',
        'erro': '❌',
        'acesso': '📍'
    };
    return icons[acao] || '📝';
}

function getAcessoIconClass(acao) {
    const classes = {
        'login': 'success',
        'logout': 'info',
        'erro': 'error',
        'acesso': 'warning'
    };
    return classes[acao] || 'info';
}

function getAtividadeIcon(tipo) {
    const icons = {
        'membro': '👤',
        'ponto': '⭐',
        'presenca': '✅',
        'especialidade': '🎓'
    };
    return icons[tipo] || '📌';
}

function getAlertaIcon(tipo) {
    const icons = {
        'error': '❌',
        'warning': '⚠️',
        'info': 'ℹ️',
        'success': '✅'
    };
    return icons[tipo] || '📢';
}

function formatDateTime(datetime) {
    const date = new Date(datetime);
    return date.toLocaleString('pt-BR');
}

function pausarMonitoramento() {
    monitoramentoAtivo = !monitoramentoAtivo;
    event.target.textContent = monitoramentoAtivo ? 'Pausar' : 'Retomar';
}

function limparAtividades() {
    document.getElementById('atividades-monitor').innerHTML = '';
}

function filtrarAcessos() {
    const filtro = document.getElementById('filtro-acessos').value;
    // Implementar filtro
}

function filtrarLogs() {
    const filtro = document.getElementById('filtro-logs').value;
    // Implementar filtro
}

function exportarLogs() {
    window.location.href = '../api/logs.php?action=exportar';
}

function baixarLogs() {
    window.location.href = '../api/logs.php?action=baixar';
}

// Iniciar monitoramento ao carregar a página
document.addEventListener('DOMContentLoaded', function() {
    iniciarMonitoramento();
});

// Parar monitoramento ao sair da página
window.addEventListener('beforeunload', function() {
    pararMonitoramento();
});
</script>

<style>
.monitor-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin-bottom: 30px;
}

.monitor-card {
    background: white;
    border-radius: 15px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    overflow: hidden;
}

.monitor-card .card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px;
    background: #f8f9fa;
    border-bottom: 1px solid #ecf0f1;
}

.status-indicator {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    background: #e74c3c;
}

.status-indicator.online {
    background: #27ae60;
}

.status-indicator.warning {
    background: #f39c12;
}

.status-indicator.away {
    background: #95a5a6;
}

.usuarios-online-lista {
    max-height: 400px;
    overflow-y: auto;
    padding: 20px;
}

.usuario-online-item {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 15px;
    border-radius: 10px;
    margin-bottom: 10px;
    background: #f8f9fa;
    transition: all 0.3s ease;
}

.usuario-online-item:hover {
    background: #ecf0f1;
    transform: translateX(5px);
}

.usuario-avatar {
    position: relative;
}

.usuario-avatar img {
    width: 40px;
    height: 40px;
    border-radius: 50%;
}

.status-dot {
    position: absolute;
    bottom: 0;
    right: 0;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    border: 2px solid white;
}

.status-dot.online {
    background: #27ae60;
}

.status-dot.away {
    background: #f39c12;
}

.usuario-info {
    flex: 1;
}

.usuario-nome {
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 5px;
}

.usuario-detalhes {
    display: flex;
    gap: 10px;
    margin-bottom: 5px;
}

.usuario-cargo {
    font-size: 0.8rem;
    color: #3498db;
}

.usuario-unidade {
    font-size: 0.8rem;
    color: #7f8c8d;
}

.usuario-atividade {
    font-size: 0.7rem;
    color: #95a5a6;
}

.acessos-timeline {
    max-height: 400px;
    overflow-y: auto;
    padding: 20px;
}

.acesso-item {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 15px;
    border-radius: 10px;
    margin-bottom: 10px;
    background: #f8f9fa;
}

.acesso-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
}

.acesso-icon.success {
    background: #d4edda;
    color: #27ae60;
}

.acesso-icon.error {
    background: #f8d7da;
    color: #e74c3c;
}

.acesso-icon.warning {
    background: #fff3cd;
    color: #f39c12;
}

.acesso-icon.info {
    background: #d1ecf1;
    color: #3498db;
}

.acesso-info {
    flex: 1;
}

.acesso-usuario {
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 3px;
}

.acesso-acao {
    font-size: 0.9rem;
    color: #5d6d7e;
    margin-bottom: 3px;
}

.acesso-data {
    font-size: 0.8rem;
    color: #95a5a6;
}

.atividades-monitor {
    max-height: 500px;
    overflow-y: auto;
    padding: 20px;
}

.atividade-item {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 15px;
    border-radius: 10px;
    margin-bottom: 10px;
    background: #f8f9fa;
    transition: all 0.3s ease;
}

.atividade-item:hover {
    background: #ecf0f1;
    transform: translateX(5px);
}

.atividade-tipo {
    font-size: 1.5rem;
    min-width: 30px;
    text-align: center;
}

.atividade-conteudo {
    flex: 1;
}

.atividade-descricao {
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 5px;
}

.atividade-detalhes {
    display: flex;
    gap: 10px;
    font-size: 0.8rem;
    color: #7f8c8d;
}

.db-metrics {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 15px;
    margin-bottom: 20px;
}

.metric-item {
    display: flex;
    justify-content: space-between;
    padding: 10px;
    background: #f8f9fa;
    border-radius: 8px;
}

.metric-label {
    color: #7f8c8d;
    font-size: 0.9rem;
}

.metric-value {
    font-weight: 600;
    color: #2c3e50;
}

.db-chart {
    height: 200px;
    background: #f8f9fa;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #7f8c8d;
}

.alertas-lista {
    max-height: 400px;
    overflow-y: auto;
    padding: 20px;
}

.alerta-item {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 15px;
    border-radius: 10px;
    margin-bottom: 10px;
    background: #f8f9fa;
}

.alerta-item.error {
    background: #fdf2f2;
    border-left: 4px solid #e74c3c;
}

.alerta-item.warning {
    background: #fef9e7;
    border-left: 4px solid #f39c12;
}

.alerta-item.info {
    background: #eef7ff;
    border-left: 4px solid #3498db;
}

.alerta-icon {
    font-size: 1.5rem;
    min-width: 30px;
    text-align: center;
}

.alerta-info {
    flex: 1;
}

.alerta-titulo {
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 5px;
}

.alerta-mensagem {
    font-size: 0.9rem;
    color: #5d6d7e;
    margin-bottom: 5px;
}

.alerta-data {
    font-size: 0.8rem;
    color: #95a5a6;
}

.alert-count {
    background: #e74c3c;
    color: white;
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 0.8rem;
    font-weight: 600;
}

.logs-tabela {
    overflow-x: auto;
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

.badge-warning {
    background: #f39c12;
    color: white;
}

.badge-info {
    background: #3498db;
    color: white;
}

.badge-debug {
    background: #95a5a6;
    color: white;
}

.log-error {
    background: #fdf2f2;
}

.log-warning {
    background: #fef9e7;
}

.log-info {
    background: #eef7ff;
}

.log-debug {
    background: #f8f9fa;
}
</style>

<?php include '../../includes/footer.php'; ?>
