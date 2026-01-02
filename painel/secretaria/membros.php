<?php
session_start();
require_once '../../config/database.php';
require_once '../../config/auth.php';

verificarAutenticacao();
verificarPermissao('secretaria');

$page_title = 'Membros - Meritus';
include '../../includes/header.php';
include '../../includes/sidebar.php';
?>

<div class="main-content">
    <div class="content-header">
        <h1>Gerenciar Membros</h1>
        <div class="header-actions">
            <button class="btn btn-primary" data-modal="modal-membro">Novo Membro</button>
            <button class="btn btn-outline" onclick="exportarMembros()">Exportar Lista</button>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Lista de Membros</h2>
            <div class="card-filters">
                <div class="filter-group">
                    <input type="text" class="form-control table-search" placeholder="Buscar membro...">
                </div>
                <div class="filter-group">
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
                <div class="filter-group">
                    <select class="form-control" id="filtro-status" onchange="filtrarMembros()">
                        <option value="">Todos os Status</option>
                        <option value="ativo">Ativos</option>
                        <option value="inativo">Inativos</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="card-body">
            <table class="table data-table">
                <thead>
                    <tr>
                        <th data-sort="nome">Nome</th>
                        <th data-sort="unidade">Unidade</th>
                        <th data-sort="idade">Idade</th>
                        <th data-sort="telefone">Telefone</th>
                        <th data-sort="data_cadastro">Cadastro</th>
                        <th data-sort="status">Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $membros = getMembros();
                    foreach ($membros as $membro):
                    ?>
                    <tr data-unidade="<?php echo $membro['unidade_id']; ?>" data-status="<?php echo $membro['status']; ?>">
                        <td>
                            <div class="membro-info">
                                <div class="membro-avatar-small">
                                    <img src="assets/img/default-avatar.png" alt="<?php echo htmlspecialchars($membro['nome']); ?>">
                                </div>
                                <div class="membro-detalhes">
                                    <div class="membro-nome"><?php echo htmlspecialchars($membro['nome']); ?></div>
                                    <div class="membro-email"><?php echo htmlspecialchars($membro['email'] ?? ''); ?></div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge badge-info"><?php echo htmlspecialchars($membro['unidade']); ?></span>
                        </td>
                        <td><?php echo calcularIdade($membro['data_nascimento']); ?> anos</td>
                        <td><?php echo htmlspecialchars($membro['telefone'] ?? 'Não informado'); ?></td>
                        <td><?php echo formatDate($membro['data_cadastro']); ?></td>
                        <td>
                            <span class="badge badge-<?php echo $membro['status'] === 'ativo' ? 'success' : 'danger'; ?>">
                                <?php echo htmlspecialchars(ucfirst($membro['status'])); ?>
                            </span>
                        </td>
                        <td>
                            <div class="action-buttons">
                                <button class="btn btn-sm btn-outline" onclick="editarMembro(<?php echo $membro['id']; ?>)">Editar</button>
                                <button class="btn btn-sm btn-outline" onclick="verHistorico(<?php echo $membro['id']; ?>)">Histórico</button>
                                <button class="btn btn-sm btn-outline" onclick="toggleStatus(<?php echo $membro['id']; ?>)">
                                    <?php echo $membro['status'] === 'ativo' ? 'Desativar' : 'Ativar'; ?>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Estatísticas por Unidade</h2>
        </div>
        <div class="card-body">
            <div class="stats-unidades">
                <?php
                $stats_unidades = getStatsUnidades();
                foreach ($stats_unidades as $stats):
                ?>
                <div class="unidade-stat-card">
                    <h3><?php echo htmlspecialchars($stats['nome']); ?></h3>
                    <div class="stat-numbers">
                        <div class="stat-item">
                            <span class="stat-number"><?php echo $stats['total']; ?></span>
                            <span class="stat-label">Total</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-number"><?php echo $stats['ativos']; ?></span>
                            <span class="stat-label">Ativos</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-number"><?php echo $stats['media_idade']; ?></span>
                            <span class="stat-label">Idade Média</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-number"><?php echo $stats['presenca_media']; ?>%</span>
                            <span class="stat-label">Presença</span>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<!-- Modal Novo Membro -->
<div id="modal-membro" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Novo Membro</h3>
            <button class="modal-close">&times;</button>
        </div>
        <form action="../api/membros.php" method="POST" class="ajax-form">
            <div class="form-row">
                <div class="form-group">
                    <label for="nome">Nome Completo*</label>
                    <input type="text" name="nome" id="nome" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="data_nascimento">Data de Nascimento*</label>
                    <input type="date" name="data_nascimento" id="data_nascimento" class="form-control" required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="unidade_id">Unidade*</label>
                    <select name="unidade_id" id="unidade_id" class="form-control" required>
                        <option value="">Selecione...</option>
                        <?php
                        $unidades = getUnidades();
                        foreach ($unidades as $unidade):
                        ?>
                        <option value="<?php echo $unidade['id']; ?>"><?php echo htmlspecialchars($unidade['nome']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="genero">Gênero</label>
                    <select name="genero" id="genero" class="form-control">
                        <option value="">Selecione...</option>
                        <option value="masculino">Masculino</option>
                        <option value="feminino">Feminino</option>
                    </select>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" name="email" id="email" class="form-control">
                </div>
                <div class="form-group">
                    <label for="telefone">Telefone</label>
                    <input type="tel" name="telefone" id="telefone" class="form-control">
                </div>
            </div>
            <div class="form-group">
                <label for="endereco">Endereço</label>
                <input type="text" name="endereco" id="endereco" class="form-control">
            </div>
            <div class="form-group">
                <label for="responsavel">Nome do Responsável</label>
                <input type="text" name="responsavel" id="responsavel" class="form-control">
            </div>
            <div class="form-group">
                <label for="observacoes">Observações</label>
                <textarea name="observacoes" id="observacoes" class="form-control" rows="3"></textarea>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Cadastrar</button>
                <button type="button" class="btn btn-outline modal-close">Cancelar</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Histórico -->
<div id="modal-historico" class="modal">
    <div class="modal-content modal-large">
        <div class="modal-header">
            <h3>Histórico do Membro</h3>
            <button class="modal-close">&times;</button>
        </div>
        <div class="modal-body">
            <div id="historico-content">
                <!-- Conteúdo será carregado via AJAX -->
            </div>
        </div>
    </div>
</div>

<?php
// Funções helper
function getMembros() {
    $db = getDB();
    $unidade_id = $_GET['unidade_id'] ?? '';
    $status = $_GET['status'] ?? '';
    
    $sql = "
        SELECT m.*, u.nome as unidade 
        FROM membros m
        JOIN unidades u ON m.unidade_id = u.id
        WHERE 1=1
    ";
    
    $params = [];
    
    if ($unidade_id) {
        $sql .= " AND m.unidade_id = ?";
        $params[] = $unidade_id;
    }
    
    if ($status) {
        $sql .= " AND m.status = ?";
        $params[] = $status;
    }
    
    $sql .= " ORDER BY m.nome";
    
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getUnidades() {
    $db = getDB();
    $stmt = $db->query("SELECT id, nome FROM unidades WHERE status = 'ativo' ORDER BY nome");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getStatsUnidades() {
    $db = getDB();
    $stmt = $db->query("
        SELECT 
            u.id, u.nome,
            COUNT(m.id) as total,
            COUNT(CASE WHEN m.status = 'ativo' THEN 1 END) as ativos,
            AVG(TIMESTAMPDIFF(YEAR, m.data_nascimento, CURDATE())) as media_idade,
            (SELECT AVG(presente) * 100 FROM presenca pr 
             JOIN membros pm ON pr.membro_id = pm.id 
             WHERE pm.unidade_id = u.id AND pr.data >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)) as presenca_media
        FROM unidades u
        LEFT JOIN membros m ON u.id = m.unidade_id
        WHERE u.status = 'ativo'
        GROUP BY u.id, u.nome
        ORDER BY u.nome
    ");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function calcularIdade($data_nascimento) {
    $data = new DateTime($data_nascimento);
    $hoje = new DateTime();
    return $hoje->diff($data)->y;
}

function formatDate($date) {
    return date('d/m/Y', strtotime($date));
}
?>

<script>
function editarMembro(id) {
    window.location.href = '?edit=' + id;
}

function verHistorico(membroId) {
    fetch('../api/membros.php?action=historico&id=' + membroId)
        .then(response => response.json())
        .then(data => {
            const content = document.getElementById('historico-content');
            content.innerHTML = formatHistorico(data);
            openModal('modal-historico');
        });
}

function formatHistorico(data) {
    let html = '<div class="historico-tabs">';
    html += '<button class="tab-btn active" onclick="showTab(\'presencas\')">Presenças</button>';
    html += '<button class="tab-btn" onclick="showTab(\'pontos\')">Pontos</button>';
    html += '<button class="tab-btn" onclick="showTab(\'atividades\')">Atividades</button>';
    html += '</div>';
    
    html += '<div class="tab-content">';
    html += '<div id="presencas" class="tab-pane active">';
    html += '<h4>Histórico de Presenças</h4>';
    html += '<table class="table"><thead><tr><th>Data</th><th>Status</th></tr></thead><tbody>';
    
    if (data.presencas && data.presencas.length > 0) {
        data.presencas.forEach(p => {
            html += `<tr><td>${formatDate(p.data)}</td><td>${p.presente ? 'Presente' : 'Ausente'}</td></tr>`;
        });
    } else {
        html += '<tr><td colspan="2">Nenhum registro encontrado</td></tr>';
    }
    
    html += '</tbody></table></div>';
    
    html += '<div id="pontos" class="tab-pane">';
    html += '<h4>Histórico de Pontos</h4>';
    html += '<p>Em desenvolvimento...</p>';
    html += '</div>';
    
    html += '<div id="atividades" class="tab-pane">';
    html += '<h4>Histórico de Atividades</h4>';
    html += '<p>Em desenvolvimento...</p>';
    html += '</div>';
    
    html += '</div>';
    return html;
}

function showTab(tabName) {
    document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
    document.querySelectorAll('.tab-pane').forEach(pane => pane.classList.remove('active'));
    
    event.target.classList.add('active');
    document.getElementById(tabName).classList.add('active');
}

function toggleStatus(id) {
    if (confirm('Tem certeza que deseja alterar o status deste membro?')) {
        fetch('../api/membros.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'toggle_status',
                id: id
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert(data.message, 'success');
                setTimeout(() => location.reload(), 1500);
            } else {
                showAlert(data.message, 'error');
            }
        });
    }
}

function filtrarMembros() {
    const unidadeId = document.getElementById('filtro-unidade').value;
    const status = document.getElementById('filtro-status').value;
    const rows = document.querySelectorAll('tbody tr');
    
    rows.forEach(row => {
        const rowUnidade = row.getAttribute('data-unidade');
        const rowStatus = row.getAttribute('data-status');
        
        const matchUnidade = !unidadeId || rowUnidade === unidadeId;
        const matchStatus = !status || rowStatus === status;
        
        row.style.display = matchUnidade && matchStatus ? '' : 'none';
    });
}

function exportarMembros() {
    const unidadeId = document.getElementById('filtro-unidade').value;
    const status = document.getElementById('filtro-status').value;
    
    let url = '../api/membros.php?action=exportar';
    if (unidadeId) url += '&unidade_id=' + unidadeId;
    if (status) url += '&status=' + status;
    
    window.location.href = url;
}
</script>

<style>
.card-filters {
    display: flex;
    gap: 10px;
    align-items: center;
}

.filter-group {
    min-width: 150px;
}

.membro-info {
    display: flex;
    align-items: center;
    gap: 10px;
}

.membro-avatar-small img {
    width: 30px;
    height: 30px;
    border-radius: 50%;
}

.membro-nome {
    font-weight: 600;
    color: #2c3e50;
}

.membro-email {
    font-size: 0.8rem;
    color: #7f8c8d;
}

.stats-unidades {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
}

.unidade-stat-card {
    background: white;
    border-radius: 10px;
    padding: 20px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.unidade-stat-card h3 {
    margin: 0 0 15px 0;
    color: #2c3e50;
}

.stat-numbers {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 15px;
}

.stat-item {
    text-align: center;
}

.stat-number {
    display: block;
    font-size: 1.5rem;
    font-weight: bold;
    color: #3498db;
}

.stat-label {
    font-size: 0.8rem;
    color: #7f8c8d;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 15px;
}

.modal-large {
    max-width: 800px;
}

.historico-tabs {
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

.tab-pane {
    display: none;
}

.tab-pane.active {
    display: block;
}
</style>

<?php include '../../includes/footer.php'; ?>
