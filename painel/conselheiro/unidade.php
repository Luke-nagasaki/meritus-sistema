<?php
session_start();
require_once '../../config/database.php';
require_once '../../config/auth.php';

verificarAutenticacao();
verificarPermissao('conselheiro');

$usuario = getUsuarioLogado();
$unidade_id = $usuario['unidade_id'];

$page_title = 'Minha Unidade - Meritus';
include '../../includes/header.php';
include '../../includes/sidebar.php';
?>

<div class="main-content">
    <div class="content-header">
        <h1>Minha Unidade</h1>
        <div class="header-actions">
            <button class="btn btn-primary" data-modal="modal-atividade">Nova Atividade</button>
            <button class="btn btn-outline" onclick="exportarDados()">Exportar Dados</button>
        </div>
    </div>
    
    <div class="unidade-overview">
        <div class="unidade-header-card">
            <div class="unidade-banner">
                <img src="assets/img/banner.jpg" alt="Banner da Unidade">
                <div class="unidade-info-overlay">
                    <h2><?php echo htmlspecialchars(getNomeUnidade($unidade_id)); ?></h2>
                    <p><?php echo htmlspecialchars(getDescricaoUnidade($unidade_id)); ?></p>
                    <div class="unidade-meta">
                        <span class="meta-item">
                            <strong>Conselheiro:</strong> <?php echo htmlspecialchars($usuario['nome']); ?>
                        </span>
                        <span class="meta-item">
                            <strong>Total de Membros:</strong> <?php echo getTotalMembrosUnidade($unidade_id); ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="tabs-container">
        <div class="tabs-header">
            <button class="tab-btn active" onclick="showTab('membros')">Membros</button>
            <button class="tab-btn" onclick="showTab('atividades')">Atividades</button>
            <button class="tab-btn" onclick="showTab('pontos')">Pontuação</button>
            <button class="tab-btn" onclick="showTab('especialidades')">Especialidades</button>
            <button class="tab-btn" onclick="showTab('presenca')">Presença</button>
        </div>
        
        <div class="tabs-content">
            <!-- Tab Membros -->
            <div id="membros" class="tab-pane active">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Todos os Membros</h3>
                        <div class="card-actions">
                            <input type="text" class="form-control" placeholder="Buscar membro..." id="busca-membros" onkeyup="filtrarMembros()">
                            <button class="btn btn-primary" data-modal="modal-membro">Novo Membro</button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="membros-lista" id="membros-lista">
                            <?php
                            $membros = getMembrosUnidade($unidade_id);
                            foreach ($membros as $membro):
                            ?>
                            <div class="membro-item" data-nome="<?php echo strtolower($membro['nome']); ?>">
                                <div class="membro-avatar">
                                    <img src="assets/img/default-avatar.png" alt="<?php echo htmlspecialchars($membro['nome']); ?>">
                                    <div class="membro-status <?php echo $membro['presente_hoje'] ? 'online' : 'offline'; ?>"></div>
                                </div>
                                <div class="membro-detalhes">
                                    <h4><?php echo htmlspecialchars($membro['nome']); ?></h4>
                                    <div class="membro-info">
                                        <span class="idade"><?php echo calcularIdade($membro['data_nascimento']); ?> anos</span>
                                        <span class="membro-id">ID: <?php echo $membro['id']; ?></span>
                                    </div>
                                    <div class="membro-contato">
                                        <?php if ($membro['email']): ?>
                                        <span class="contato-item">📧 <?php echo htmlspecialchars($membro['email']); ?></span>
                                        <?php endif; ?>
                                        <?php if ($membro['telefone']): ?>
                                        <span class="contato-item">📱 <?php echo htmlspecialchars($membro['telefone']); ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="membro-stats">
                                    <div class="stat-item">
                                        <span class="stat-number"><?php echo $membro['pontos']; ?></span>
                                        <span class="stat-label">Pontos</span>
                                    </div>
                                    <div class="stat-item">
                                        <span class="stat-number"><?php echo $membro['presenca_percentual']; ?>%</span>
                                        <span class="stat-label">Presença</span>
                                    </div>
                                </div>
                                <div class="membro-acoes">
                                    <button class="btn btn-sm btn-outline" onclick="verPerfilMembro(<?php echo $membro['id']; ?>)">Perfil</button>
                                    <button class="btn btn-sm btn-outline" onclick="lancarPontos(<?php echo $membro['id']; ?>)">+Pts</button>
                                    <button class="btn btn-sm btn-outline" onclick="editarMembro(<?php echo $membro['id']; ?>)">Editar</button>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Tab Atividades -->
            <div id="atividades" class="tab-pane">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Atividades da Unidade</h3>
                        <div class="card-actions">
                            <select class="form-control" id="filtro-mes" onchange="filtrarAtividades()">
                                <option value="">Todos os meses</option>
                                <?php for ($i = 1; $i <= 12; $i++): ?>
                                <option value="<?php echo $i; ?>"><?php echo strftime('%B', mktime(0, 0, 0, $i, 1)); ?></option>
                                <?php endfor; ?>
                            </select>
                            <button class="btn btn-primary" data-modal="modal-atividade">Nova Atividade</button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="atividades-lista" id="atividades-lista">
                            <?php
                            $atividades = getAtividadesUnidade($unidade_id);
                            foreach ($atividades as $atividade):
                            ?>
                            <div class="atividade-item" data-mes="<?php echo date('n', strtotime($atividade['data'])); ?>">
                                <div class="atividade-data">
                                    <div class="data-dia"><?php echo date('d', strtotime($atividade['data'])); ?></div>
                                    <div class="data-mes"><?php echo strftime('%b', strtotime($atividade['data'])); ?></div>
                                </div>
                                <div class="atividade-conteudo">
                                    <h4><?php echo htmlspecialchars($atividade['titulo']); ?></h4>
                                    <p><?php echo htmlspecialchars($atividade['descricao']); ?></p>
                                    <div class="atividade-meta">
                                        <span class="meta-item">📍 <?php echo htmlspecialchars($atividade['local']); ?></span>
                                        <span class="meta-item">👥 <?php echo $atividade['participantes']; ?> participantes</span>
                                        <span class="meta-item">⏰ <?php echo date('H:i', strtotime($atividade['data'])); ?></span>
                                    </div>
                                </div>
                                <div class="atividade-acoes">
                                    <button class="btn btn-sm btn-outline" onclick="verAtividade(<?php echo $atividade['id']; ?>)">Ver</button>
                                    <button class="btn btn-sm btn-outline" onclick="editarAtividade(<?php echo $atividade['id']; ?>)">Editar</button>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Tab Pontos -->
            <div id="pontos" class="tab-pane">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Histórico de Pontos</h3>
                        <div class="card-actions">
                            <button class="btn btn-primary" data-modal="modal-pontos">Lançar Pontos</button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="pontos-filtros">
                            <select class="form-control" id="filtro-categoria" onchange="filtrarPontos()">
                                <option value="">Todas as categorias</option>
                                <option value="presenca">Presença</option>
                                <option value="participacao">Participação</option>
                                <option value="comportamento">Comportamento</option>
                                <option value="especialidade">Especialidade</option>
                                <option value="atividade">Atividade</option>
                                <option value="lideranca">Liderança</option>
                            </select>
                            <input type="date" class="form-control" id="filtro-data-pontos" onchange="filtrarPontos()">
                        </div>
                        <div class="pontos-lista" id="pontos-lista">
                            <?php
                            $pontos = getPontosUnidade($unidade_id);
                            foreach ($pontos as $ponto):
                            ?>
                            <div class="ponto-item" data-categoria="<?php echo $ponto['categoria']; ?>" data-data="<?php echo date('Y-m-d', strtotime($ponto['data'])); ?>">
                                <div class="ponto-valor <?php echo $ponto['pontos'] > 0 ? 'positivo' : 'negativo'; ?>">
                                    <?php echo $ponto['pontos'] > 0 ? '+' : ''; ?><?php echo $ponto['pontos']; ?>
                                </div>
                                <div class="ponto-info">
                                    <h4><?php echo htmlspecialchars($ponto['membro_nome']); ?></h4>
                                    <p><?php echo htmlspecialchars($ponto['descricao']); ?></p>
                                    <div class="ponto-meta">
                                        <span class="categoria-badge badge-<?php echo $ponto['categoria']; ?>"><?php echo htmlspecialchars(ucfirst($ponto['categoria'])); ?></span>
                                        <span class="data-ponto"><?php echo formatDateTime($ponto['data']); ?></span>
                                    </div>
                                </div>
                                <div class="ponto-acoes">
                                    <button class="btn btn-sm btn-outline" onclick="editarPonto(<?php echo $ponto['id']; ?>)">Editar</button>
                                    <button class="btn btn-sm btn-outline" onclick="excluirPonto(<?php echo $ponto['id']; ?>)">Excluir</button>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Tab Especialidades -->
            <div id="especialidades" class="tab-pane">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Especialidades</h3>
                        <div class="card-actions">
                            <button class="btn btn-primary" data-modal="modal-especialidade">Nova Especialidade</button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="especialidades-grid">
                            <?php
                            $especialidades = getEspecialidadesUnidade($unidade_id);
                            foreach ($especialidades as $especialidade):
                            ?>
                            <div class="especialidade-card">
                                <div class="especialidade-header">
                                    <h4><?php echo htmlspecialchars($especialidade['nome']); ?></h4>
                                    <span class="status-badge badge-<?php echo $especialidade['status']; ?>">
                                        <?php echo htmlspecialchars(ucfirst($especialidade['status'])); ?>
                                    </span>
                                </div>
                                <div class="especialidade-progresso">
                                    <div class="progress-bar">
                                        <div class="progress-fill" style="width: <?php echo $especialidade['progresso']; ?>%"></div>
                                    </div>
                                    <span class="progress-text"><?php echo $especialidade['progresso']; ?>%</span>
                                </div>
                                <div class="especialidade-membros">
                                    <div class="membro-list">
                                        <?php
                                        $membros_especialidade = getMembrosEspecialidade($especialidade['id']);
                                        foreach ($membros_especialidade as $membro):
                                        ?>
                                        <div class="membro-especialidade">
                                            <img src="assets/img/default-avatar.png" alt="<?php echo htmlspecialchars($membro['nome']); ?>">
                                            <span><?php echo htmlspecialchars($membro['nome']); ?></span>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Tab Presença -->
            <div id="presenca" class="tab-pane">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Controle de Presença</h3>
                        <div class="card-actions">
                            <input type="date" class="form-control" id="data-presenca" value="<?php echo date('Y-m-d'); ?>" onchange="carregarPresenca()">
                            <button class="btn btn-primary" onclick="salvarPresenca()">Salvar Presença</button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="presenca-grid" id="presenca-grid">
                            <!-- Será carregado via JavaScript -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modais -->
<div id="modal-membro" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Novo Membro</h3>
            <button class="modal-close">&times;</button>
        </div>
        <form action="../api/membros.php" method="POST" class="ajax-form">
            <!-- Formulário de membro -->
        </form>
    </div>
</div>

<div id="modal-atividade" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Nova Atividade</h3>
            <button class="modal-close">&times;</button>
        </div>
        <form action="../api/atividades.php" method="POST" class="ajax-form">
            <!-- Formulário de atividade -->
        </form>
    </div>
</div>

<div id="modal-pontos" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Lançar Pontos</h3>
            <button class="modal-close">&times;</button>
        </div>
        <form action="../api/pontos.php" method="POST" class="ajax-form">
            <!-- Formulário de pontos -->
        </form>
    </div>
</div>

<?php
// Funções helper (reutilizadas do index.php e outras)
function getNomeUnidade($unidade_id) {
    $db = getDB();
    $stmt = $db->prepare("SELECT nome FROM unidades WHERE id = ?");
    $stmt->execute([$unidade_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result['nome'] ?? 'Unidade não encontrada';
}

function getDescricaoUnidade($unidade_id) {
    $db = getDB();
    $stmt = $db->prepare("SELECT descricao FROM unidades WHERE id = ?");
    $stmt->execute([$unidade_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result['descricao'] ?? '';
}

function getTotalMembrosUnidade($unidade_id) {
    $db = getDB();
    $stmt = $db->prepare("SELECT COUNT(*) as total FROM membros WHERE unidade_id = ? AND status = 'ativo'");
    $stmt->execute([$unidade_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result['total'];
}

function getMembrosUnidade($unidade_id) {
    $db = getDB();
    $stmt = $db->prepare("
        SELECT 
            m.*,
            COALESCE(SUM(mp.pontos), 0) as pontos,
            COALESCE(p.presente, 0) as presente_hoje,
            (SELECT AVG(presente) * 100 FROM presenca WHERE membro_id = m.id AND data >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)) as presenca_percentual
        FROM membros m
        LEFT JOIN membros_pontos mp ON m.id = mp.membro_id
        LEFT JOIN presenca p ON m.id = p.membro_id AND p.data = CURDATE()
        WHERE m.unidade_id = ? AND m.status = 'ativo'
        GROUP BY m.id
        ORDER BY pontos DESC, m.nome
    ");
    $stmt->execute([$unidade_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getAtividadesUnidade($unidade_id) {
    $db = getDB();
    $stmt = $db->prepare("
        SELECT a.*, COUNT(ap.membro_id) as participantes 
        FROM atividades a 
        LEFT JOIN atividades_participantes ap ON a.id = ap.atividade_id 
        WHERE a.unidade_id = ? 
        GROUP BY a.id 
        ORDER BY a.data DESC
    ");
    $stmt->execute([$unidade_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getPontosUnidade($unidade_id) {
    $db = getDB();
    $stmt = $db->prepare("
        SELECT mp.*, m.nome as membro_nome 
        FROM membros_pontos mp 
        JOIN membros m ON mp.membro_id = m.id 
        WHERE m.unidade_id = ? 
        ORDER BY mp.data DESC
    ");
    $stmt->execute([$unidade_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getEspecialidadesUnidade($unidade_id) {
    $db = getDB();
    $stmt = $db->prepare("
        SELECT e.*, 
               (SELECT COUNT(*) FROM membros_especialidades me WHERE me.especialidade_id = e.id AND me.status = 'concluida') as concluidas,
               (SELECT COUNT(*) FROM membros_especialidades me WHERE me.especialidade_id = e.id) as total
        FROM especialidades e
        WHERE e.unidade_id = ?
        ORDER BY e.nome
    ");
    $stmt->execute([$unidade_id]);
    $especialidades = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($especialidades as &$especialidade) {
        $especialidade['progresso'] = $especialidade['total'] > 0 ? ($especialidade['concluidas'] / $especialidade['total']) * 100 : 0;
    }
    
    return $especialidades;
}

function getMembrosEspecialidade($especialidade_id) {
    $db = getDB();
    $stmt = $db->prepare("
        SELECT m.* 
        FROM membros m 
        JOIN membros_especialidades me ON m.id = me.membro_id 
        WHERE me.especialidade_id = ? AND me.status = 'andamento'
        ORDER BY m.nome
    ");
    $stmt->execute([$especialidade_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function calcularIdade($data_nascimento) {
    $data = new DateTime($data_nascimento);
    $hoje = new DateTime();
    return $hoje->diff($data)->y;
}

function formatDateTime($datetime) {
    return date('d/m/Y H:i', strtotime($datetime));
}
?>

<script>
function showTab(tabName) {
    // Esconder todas as tabs
    document.querySelectorAll('.tab-pane').forEach(pane => pane.classList.remove('active'));
    document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
    
    // Mostrar a tab selecionada
    document.getElementById(tabName).classList.add('active');
    event.target.classList.add('active');
    
    // Carregar dados específicos da tab se necessário
    if (tabName === 'presenca') {
        carregarPresenca();
    }
}

function filtrarMembros() {
    const busca = document.getElementById('busca-membros').value.toLowerCase();
    const membros = document.querySelectorAll('.membro-item');
    
    membros.forEach(membro => {
        const nome = membro.getAttribute('data-nome');
        membro.style.display = nome.includes(busca) ? '' : 'none';
    });
}

function filtrarAtividades() {
    const mes = document.getElementById('filtro-mes').value;
    const atividades = document.querySelectorAll('.atividade-item');
    
    atividades.forEach(atividade => {
        const atividadeMes = atividade.getAttribute('data-mes');
        atividade.style.display = !mes || atividadeMes === mes ? '' : 'none';
    });
}

function filtrarPontos() {
    const categoria = document.getElementById('filtro-categoria').value;
    const data = document.getElementById('filtro-data-pontos').value;
    const pontos = document.querySelectorAll('.ponto-item');
    
    pontos.forEach(ponto => {
        const pontoCategoria = ponto.getAttribute('data-categoria');
        const pontoData = ponto.getAttribute('data-data');
        
        const matchCategoria = !categoria || pontoCategoria === categoria;
        const matchData = !data || pontoData === data;
        
        ponto.style.display = matchCategoria && matchData ? '' : 'none';
    });
}

function verPerfilMembro(membroId) {
    window.location.href = `?membro=${membroId}`;
}

function lancarPontos(membroId) {
    document.getElementById('membro_id').value = membroId;
    openModal('modal-pontos');
}

function editarMembro(membroId) {
    window.location.href = `?edit_membro=${membroId}`;
}

function verAtividade(atividadeId) {
    window.location.href = `?atividade=${atividadeId}`;
}

function editarAtividade(atividadeId) {
    window.location.href = `?edit_atividade=${atividadeId}`;
}

function editarPonto(pontoId) {
    window.location.href = `?edit_ponto=${pontoId}`;
}

function excluirPonto(pontoId) {
    if (confirm('Tem certeza que deseja excluir este lançamento de pontos?')) {
        fetch('../api/pontos.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'excluir',
                id: pontoId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('Ponto excluído com sucesso!', 'success');
                setTimeout(() => location.reload(), 1500);
            } else {
                showAlert(data.message, 'error');
            }
        });
    }
}

function carregarPresenca() {
    const data = document.getElementById('data-presenca').value;
    
    fetch(`../api/presenca.php?action=carregar&data=${data}&unidade_id=<?php echo $unidade_id; ?>`)
        .then(response => response.json())
        .then(data => {
            renderizarPresenca(data.membros);
        });
}

function renderizarPresenca(membros) {
    const grid = document.getElementById('presenca-grid');
    grid.innerHTML = '';
    
    membros.forEach(membro => {
        const membroDiv = document.createElement('div');
        membroDiv.className = 'presenca-membro';
        membroDiv.innerHTML = `
            <div class="presenca-checkbox">
                <input type="checkbox" id="presenca-${membro.id}" ${membro.presente ? 'checked' : ''}>
                <label for="presenca-${membro.id}"></label>
            </div>
            <div class="presenca-info">
                <img src="assets/img/default-avatar.png" alt="${membro.nome}">
                <div class="presenca-detalhes">
                    <div class="presenca-nome">${membro.nome}</div>
                    <div class="presenca-meta">${membro.idade} anos</div>
                </div>
            </div>
        `;
        grid.appendChild(membroDiv);
    });
}

function salvarPresenca() {
    // Implementar salvamento de presença
    alert('Funcionalidade em desenvolvimento');
}

function exportarDados() {
    window.location.href = `../api/unidades.php?action=exportar&id=<?php echo $unidade_id; ?>`;
}
</script>

<style>
.unidade-overview {
    margin-bottom: 30px;
}

.unidade-header-card {
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
}

.unidade-banner {
    position: relative;
    height: 200px;
}

.unidade-banner img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.unidade-info-overlay {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    background: linear-gradient(transparent, rgba(0,0,0,0.8));
    color: white;
    padding: 30px;
}

.unidade-info-overlay h2 {
    margin: 0 0 10px 0;
    font-size: 2rem;
}

.unidade-meta {
    display: flex;
    gap: 20px;
    margin-top: 15px;
}

.meta-item {
    font-size: 0.9rem;
    opacity: 0.9;
}

.tabs-container {
    background: white;
    border-radius: 15px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
    overflow: hidden;
}

.tabs-header {
    display: flex;
    background: #f8f9fa;
    border-bottom: 1px solid #ecf0f1;
}

.tab-btn {
    flex: 1;
    padding: 15px;
    background: none;
    border: none;
    cursor: pointer;
    font-weight: 600;
    color: #7f8c8d;
    transition: all 0.3s ease;
}

.tab-btn.active {
    background: white;
    color: #3498db;
    border-bottom: 3px solid #3498db;
}

.tabs-content {
    padding: 30px;
}

.tab-pane {
    display: none;
}

.tab-pane.active {
    display: block;
}

.membros-lista {
    display: grid;
    gap: 20px;
}

.membro-item {
    display: flex;
    align-items: center;
    gap: 20px;
    padding: 20px;
    background: #f8f9fa;
    border-radius: 10px;
    transition: all 0.3s ease;
}

.membro-item:hover {
    background: #ecf0f1;
    transform: translateY(-2px);
}

.membro-avatar {
    position: relative;
}

.membro-avatar img {
    width: 60px;
    height: 60px;
    border-radius: 50%;
}

.membro-status {
    position: absolute;
    bottom: 0;
    right: 0;
    width: 15px;
    height: 15px;
    border-radius: 50%;
    border: 2px solid white;
}

.membro-status.online {
    background: #27ae60;
}

.membro-status.offline {
    background: #e74c3c;
}

.membro-detalhes {
    flex: 1;
}

.membro-detalhes h4 {
    margin: 0 0 5px 0;
    color: #2c3e50;
}

.membro-info {
    display: flex;
    gap: 15px;
    margin-bottom: 5px;
    font-size: 0.9rem;
    color: #7f8c8d;
}

.membro-contato {
    display: flex;
    gap: 15px;
    flex-wrap: wrap;
}

.contato-item {
    font-size: 0.8rem;
    color: #7f8c8d;
}

.membro-stats {
    display: flex;
    gap: 20px;
    text-align: center;
}

.membro-acoes {
    display: flex;
    gap: 10px;
}

.atividades-lista {
    display: grid;
    gap: 20px;
}

.atividade-item {
    display: flex;
    gap: 20px;
    padding: 20px;
    background: #f8f9fa;
    border-radius: 10px;
}

.atividade-data {
    text-align: center;
    min-width: 60px;
}

.data-dia {
    font-size: 1.5rem;
    font-weight: bold;
    color: #3498db;
}

.data-mes {
    font-size: 0.8rem;
    color: #7f8c8d;
    text-transform: uppercase;
}

.atividade-conteudo {
    flex: 1;
}

.atividade-conteudo h4 {
    margin: 0 0 10px 0;
    color: #2c3e50;
}

.atividade-meta {
    display: flex;
    gap: 15px;
    margin-top: 10px;
    font-size: 0.8rem;
    color: #7f8c8d;
}

.pontos-filtros {
    display: flex;
    gap: 15px;
    margin-bottom: 20px;
}

.pontos-lista {
    display: grid;
    gap: 15px;
}

.ponto-item {
    display: flex;
    align-items: center;
    gap: 20px;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 10px;
}

.ponto-valor {
    font-size: 1.5rem;
    font-weight: bold;
    min-width: 60px;
    text-align: center;
}

.ponto-valor.positivo {
    color: #27ae60;
}

.ponto-valor.negativo {
    color: #e74c3c;
}

.ponto-info {
    flex: 1;
}

.categoria-badge {
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 0.7rem;
    font-weight: 600;
    text-transform: uppercase;
}

.especialidades-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
}

.especialidade-card {
    background: #f8f9fa;
    border-radius: 10px;
    padding: 20px;
}

.especialidade-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
}

.especialidade-progresso {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 15px;
}

.progress-bar {
    flex: 1;
    height: 8px;
    background: #ecf0f1;
    border-radius: 4px;
    overflow: hidden;
}

.progress-fill {
    height: 100%;
    background: linear-gradient(90deg, #3498db, #2980b9);
    transition: width 0.3s ease;
}

.membro-list {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.membro-especialidade {
    display: flex;
    align-items: center;
    gap: 10px;
}

.membro-especialidade img {
    width: 25px;
    height: 25px;
    border-radius: 50%;
}

.presenca-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 15px;
}

.presenca-membro {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px;
    background: #f8f9fa;
    border-radius: 8px;
}

.presenca-checkbox input[type="checkbox"] {
    width: 18px;
    height: 18px;
}

.presenca-info {
    display: flex;
    align-items: center;
    gap: 10px;
}

.presenca-info img {
    width: 30px;
    height: 30px;
    border-radius: 50%;
}

.presenca-nome {
    font-weight: 600;
    color: #2c3e50;
}

.presenca-meta {
    font-size: 0.8rem;
    color: #7f8c8d;
}
</style>

<?php include '../../includes/footer.php'; ?>
