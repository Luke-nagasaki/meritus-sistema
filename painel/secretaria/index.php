<?php
session_start();
require_once '../../config/database.php';
require_once '../../config/auth.php';

verificarAutenticacao();
verificarPermissao('secretaria');

$page_title = 'Painel da Secretaria - Meritus';
include '../../includes/header.php';
include '../../includes/sidebar.php';
?>

<div class="main-content">
    <div class="content-header">
        <h1>Painel da Secretaria</h1>
        <div class="header-actions">
            <button class="btn btn-primary" data-modal="modal-membro">Novo Membro</button>
            <button class="btn btn-outline" onclick="registrarPresencaHoje()">Registrar Presença</button>
        </div>
    </div>
    
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-number"><?php echo getTotalMembros(); ?></div>
            <div class="stat-label">Total de Membros</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?php echo getPresentesHoje(); ?></div>
            <div class="stat-label">Presentes Hoje</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?php echo getAusenciasHoje(); ?></div>
            <div class="stat-label">Ausências Hoje</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?php echo getPresencaMedia(); ?>%</div>
            <div class="stat-label">Presença Média</div>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Presença de Hoje</h2>
            <div class="card-actions">
                <select class="form-control" id="filtro-unidade" onchange="filtrarPresenca()">
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
            <div class="presenca-rapida">
                <div class="presenca-header">
                    <h3>Registro Rápido de Presença</h3>
                    <p>Clique nos membros para marcar presença/ausência</p>
                </div>
                <div class="presenca-grid" id="presenca-grid">
                    <?php
                    $membros = getMembrosPresencaHoje();
                    foreach ($membros as $membro):
                    ?>
                    <div class="membro-presenca" data-membro-id="<?php echo $membro['id']; ?>" onclick="togglePresenca(<?php echo $membro['id']; ?>)">
                        <div class="membro-avatar">
                            <img src="assets/img/default-avatar.png" alt="<?php echo htmlspecialchars($membro['nome']); ?>">
                        </div>
                        <div class="membro-info">
                            <div class="membro-nome"><?php echo htmlspecialchars($membro['nome']); ?></div>
                            <div class="membro-unidade"><?php echo htmlspecialchars($membro['unidade']); ?></div>
                        </div>
                        <div class="presenca-status <?php echo $membro['presente'] ? 'presente' : 'ausente'; ?>">
                            <?php echo $membro['presente'] ? '✓' : '✗'; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Membros Recentes</h2>
            <div class="card-actions">
                <a href="membros.php" class="btn btn-outline">Ver Todos</a>
            </div>
        </div>
        <div class="card-body">
            <table class="table">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Unidade</th>
                        <th>Idade</th>
                        <th>Data Cadastro</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $membros_recentes = getMembrosRecentes();
                    foreach ($membros_recentes as $membro):
                    ?>
                    <tr>
                        <td><?php echo htmlspecialchars($membro['nome']); ?></td>
                        <td><?php echo htmlspecialchars($membro['unidade']); ?></td>
                        <td><?php echo calcularIdade($membro['data_nascimento']); ?> anos</td>
                        <td><?php echo formatDate($membro['data_cadastro']); ?></td>
                        <td>
                            <span class="badge badge-<?php echo $membro['status'] === 'ativo' ? 'success' : 'danger'; ?>">
                                <?php echo htmlspecialchars(ucfirst($membro['status'])); ?>
                            </span>
                        </td>
                        <td>
                            <button class="btn btn-sm btn-outline" onclick="editarMembro(<?php echo $membro['id']; ?>)">Editar</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Próximos Aniversários</h2>
        </div>
        <div class="card-body">
            <div class="aniversarios-grid">
                <?php
                $aniversarios = getProximosAniversarios();
                foreach ($aniversarios as $aniversario):
                ?>
                <div class="aniversario-card">
                    <div class="aniversario-avatar">
                        <img src="assets/img/default-avatar.png" alt="<?php echo htmlspecialchars($aniversario['nome']); ?>">
                    </div>
                    <div class="aniversario-info">
                        <div class="aniversario-nome"><?php echo htmlspecialchars($aniversario['nome']); ?></div>
                        <div class="aniversario-data">
                            <?php echo formatDate($aniversario['data_nascimento']); ?> 
                            (<?php echo calcularIdade($aniversario['data_nascimento']) + 1; ?> anos)
                        </div>
                        <div class="aniversario-unidade"><?php echo htmlspecialchars($aniversario['unidade']); ?></div>
                    </div>
                    <div class="aniversario-dias">
                        <?php echo $aniversario['dias_restantes']; ?> dias
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
            <div class="form-group">
                <label for="nome">Nome Completo</label>
                <input type="text" name="nome" id="nome" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="data_nascimento">Data de Nascimento</label>
                <input type="date" name="data_nascimento" id="data_nascimento" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="unidade_id">Unidade</label>
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
                <label for="email">Email (opcional)</label>
                <input type="email" name="email" id="email" class="form-control">
            </div>
            <div class="form-group">
                <label for="telefone">Telefone (opcional)</label>
                <input type="tel" name="telefone" id="telefone" class="form-control">
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Cadastrar</button>
                <button type="button" class="btn btn-outline modal-close">Cancelar</button>
            </div>
        </form>
    </div>
</div>

<?php
// Funções helper
function getTotalMembros() {
    $db = getDB();
    $stmt = $db->query("SELECT COUNT(*) as total FROM membros WHERE status = 'ativo'");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result['total'];
}

function getPresentesHoje() {
    $db = getDB();
    $stmt = $db->prepare("SELECT COUNT(*) as total FROM presenca WHERE data = CURDATE() AND presente = 1");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result['total'];
}

function getAusenciasHoje() {
    $db = getDB();
    $stmt = $db->prepare("SELECT COUNT(*) as total FROM presenca WHERE data = CURDATE() AND presente = 0");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result['total'];
}

function getPresencaMedia() {
    $db = getDB();
    $stmt = $db->query("
        SELECT AVG(presente) * 100 as media 
        FROM presenca 
        WHERE data >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
    ");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return round($result['media'], 1);
}

function getUnidades() {
    $db = getDB();
    $stmt = $db->query("SELECT id, nome FROM unidades WHERE status = 'ativo' ORDER BY nome");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getMembrosPresencaHoje() {
    $db = getDB();
    $stmt = $db->query("
        SELECT 
            m.id, m.nome, m.data_nascimento,
            u.nome as unidade,
            COALESCE(p.presente, 0) as presente
        FROM membros m
        JOIN unidades u ON m.unidade_id = u.id
        LEFT JOIN presenca p ON m.id = p.membro_id AND p.data = CURDATE()
        WHERE m.status = 'ativo'
        ORDER BY u.nome, m.nome
    ");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getMembrosRecentes() {
    $db = getDB();
    $stmt = $db->query("
        SELECT m.*, u.nome as unidade 
        FROM membros m
        JOIN unidades u ON m.unidade_id = u.id
        WHERE m.status = 'ativo'
        ORDER BY m.data_cadastro DESC
        LIMIT 10
    ");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getProximosAniversarios() {
    $db = getDB();
    $stmt = $db->query("
        SELECT 
            m.*, u.nome as unidade,
            DATEDIFF(DATE_ADD(m.data_nascimento, INTERVAL YEAR(CURDATE()) - YEAR(m.data_nascimento) + IF(DAYOFYEAR(CURDATE()) > DAYOFYEAR(m.data_nascimento), 1, 0) YEAR), CURDATE()) as dias_restantes
        FROM membros m
        JOIN unidades u ON m.unidade_id = u.id
        WHERE m.status = 'ativo'
        ORDER BY dias_restantes ASC
        LIMIT 8
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
function togglePresenca(membroId) {
    const element = document.querySelector(`[data-membro-id="${membroId}"]`);
    const statusElement = element.querySelector('.presenca-status');
    const isPresente = statusElement.classList.contains('presente');
    
    fetch('../api/presenca.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            action: 'toggle',
            membro_id: membroId,
            presente: !isPresente
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            statusElement.classList.toggle('presente');
            statusElement.classList.toggle('ausente');
            statusElement.textContent = isPresente ? '✗' : '✓';
            
            // Atualizar estatísticas
            atualizarEstatisticas();
        } else {
            showAlert(data.message, 'error');
        }
    });
}

function filtrarPresenca() {
    const unidadeId = document.getElementById('filtro-unidade').value;
    const membros = document.querySelectorAll('.membro-presenca');
    
    membros.forEach(membro => {
        if (unidadeId === '') {
            membro.style.display = 'block';
        } else {
            // Implementar filtro por unidade
            membro.style.display = 'block';
        }
    });
}

function registrarPresencaHoje() {
    window.location.href = 'presenca.php';
}

function editarMembro(id) {
    window.location.href = 'membros.php?edit=' + id;
}

function atualizarEstatisticas() {
    // Implementar atualização das estatísticas em tempo real
    fetch('../api/presenca.php?action=stats')
        .then(response => response.json())
        .then(data => {
            // Atualizar os números no dashboard
        });
}
</script>

<style>
.presenca-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 15px;
    margin-top: 20px;
}

.membro-presenca {
    background: white;
    border: 2px solid #ecf0f1;
    border-radius: 10px;
    padding: 15px;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s ease;
}

.membro-presenca:hover {
    border-color: #3498db;
    transform: translateY(-2px);
}

.membro-presenca.presente {
    border-color: #27ae60;
    background: #f0f9f0;
}

.membro-presenca.ausente {
    border-color: #e74c3c;
    background: #fdf2f2;
}

.membro-avatar img {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    margin-bottom: 10px;
}

.membro-nome {
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 5px;
}

.membro-unidade {
    font-size: 0.8rem;
    color: #7f8c8d;
    margin-bottom: 10px;
}

.presenca-status {
    font-size: 1.5rem;
    font-weight: bold;
}

.presenca-status.presente {
    color: #27ae60;
}

.presenca-status.ausente {
    color: #e74c3c;
}

.aniversarios-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 15px;
}

.aniversario-card {
    display: flex;
    align-items: center;
    background: white;
    border-radius: 10px;
    padding: 15px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}

.aniversario-avatar img {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    margin-right: 15px;
}

.aniversario-info {
    flex: 1;
}

.aniversario-nome {
    font-weight: 600;
    color: #2c3e50;
}

.aniversario-data {
    font-size: 0.8rem;
    color: #7f8c8d;
}

.aniversario-unidade {
    font-size: 0.8rem;
    color: #3498db;
}

.aniversario-dias {
    font-weight: bold;
    color: #e74c3c;
    font-size: 0.9rem;
}
</style>

<?php include '../../includes/footer.php'; ?>
