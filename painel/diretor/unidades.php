<?php
session_start();
require_once '../../config/database.php';
require_once '../../config/auth.php';

verificarAutenticacao();
verificarPermissao('diretor');

$page_title = 'Gerenciar Unidades - Meritus';
include '../../includes/header.php';
include '../../includes/sidebar.php';
?>

<div class="main-content">
    <div class="content-header">
        <h1>Gerenciar Unidades</h1>
        <div class="header-actions">
            <button class="btn btn-primary" data-modal="modal-unidade">Nova Unidade</button>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Lista de Unidades</h2>
            <div class="card-actions">
                <input type="text" class="form-control table-search" placeholder="Buscar unidade...">
            </div>
        </div>
        <div class="card-body">
            <div class="unidades-grid">
                <?php
                $unidades = getUnidades();
                foreach ($unidades as $unidade):
                ?>
                <div class="unidade-card">
                    <div class="unidade-header">
                        <h3><?php echo htmlspecialchars($unidade['nome']); ?></h3>
                        <span class="badge badge-<?php echo $unidade['status'] === 'ativo' ? 'success' : 'danger'; ?>">
                            <?php echo htmlspecialchars(ucfirst($unidade['status'])); ?>
                        </span>
                    </div>
                    <div class="unidade-info">
                        <div class="info-item">
                            <span class="info-label">Conselheiro:</span>
                            <span class="info-value"><?php echo htmlspecialchars($unidade['conselheiro'] ?? 'Não definido'); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Membros:</span>
                            <span class="info-value"><?php echo $unidade['total_membros']; ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Presentes hoje:</span>
                            <span class="info-value"><?php echo $unidade['presentes_hoje']; ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Pontuação total:</span>
                            <span class="info-value"><?php echo $unidade['pontos_total']; ?></span>
                        </div>
                    </div>
                    <div class="unidade-actions">
                        <button class="btn btn-sm btn-outline" onclick="editarUnidade(<?php echo $unidade['id']; ?>)">Editar</button>
                        <button class="btn btn-sm btn-outline" onclick="verMembros(<?php echo $unidade['id']; ?>)">Ver Membros</button>
                        <button class="btn btn-sm btn-outline" onclick="toggleStatus(<?php echo $unidade['id']; ?>)">
                            <?php echo $unidade['status'] === 'ativo' ? 'Desativar' : 'Ativar'; ?>
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<!-- Modal Nova Unidade -->
<div id="modal-unidade" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Nova Unidade</h3>
            <button class="modal-close">&times;</button>
        </div>
        <form action="../api/unidades.php" method="POST" class="ajax-form">
            <div class="form-group">
                <label for="nome">Nome da Unidade</label>
                <input type="text" name="nome" id="nome" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="descricao">Descrição</label>
                <textarea name="descricao" id="descricao" class="form-control" rows="3"></textarea>
            </div>
            <div class="form-group">
                <label for="conselheiro_id">Conselheiro</label>
                <select name="conselheiro_id" id="conselheiro_id" class="form-control">
                    <option value="">Selecione...</option>
                    <?php
                    $conselheiros = getConselheiros();
                    foreach ($conselheiros as $conselheiro):
                    ?>
                    <option value="<?php echo $conselheiro['id']; ?>"><?php echo htmlspecialchars($conselheiro['nome']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="cor_unidade">Cor da Unidade</label>
                <input type="color" name="cor_unidade" id="cor_unidade" class="form-control" value="#3498db">
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
function getUnidades() {
    $db = getDB();
    $stmt = $db->query("
        SELECT 
            u.*,
            us.nome as conselheiro,
            COUNT(DISTINCT m.id) as total_membros,
            COUNT(DISTINCT CASE WHEN p.presente = 1 AND p.data = CURDATE() THEN p.membro_id END) as presentes_hoje,
            COALESCE(SUM(mp.pontos), 0) as pontos_total
        FROM unidades u
        LEFT JOIN usuarios us ON u.conselheiro_id = us.id
        LEFT JOIN membros m ON u.id = m.unidade_id AND m.status = 'ativo'
        LEFT JOIN presenca p ON m.id = p.membro_id
        LEFT JOIN membros_pontos mp ON m.id = mp.membro_id
        GROUP BY u.id, us.nome
        ORDER BY u.nome
    ");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getConselheiros() {
    $db = getDB();
    $stmt = $db->query("SELECT id, nome FROM usuarios WHERE cargo = 'conselheiro' AND status = 'ativo' ORDER BY nome");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<script>
function editarUnidade(id) {
    // Implementar edição de unidade
    alert('Funcionalidade de edição em desenvolvimento');
}

function verMembros(unidadeId) {
    window.location.href = '../secretaria/membros.php?unidade_id=' + unidadeId;
}

function toggleStatus(id) {
    if (confirm('Tem certeza que deseja alterar o status desta unidade?')) {
        fetch('../api/unidades.php', {
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
</script>

<style>
.unidades-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
    gap: 20px;
}

.unidade-card {
    background: white;
    border-radius: 10px;
    padding: 20px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    border-left: 4px solid #3498db;
}

.unidade-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
}

.unidade-header h3 {
    margin: 0;
    color: #2c3e50;
}

.unidade-info {
    margin-bottom: 20px;
}

.info-item {
    display: flex;
    justify-content: space-between;
    margin-bottom: 8px;
}

.info-label {
    font-weight: 600;
    color: #7f8c8d;
}

.info-value {
    color: #2c3e50;
}

.unidade-actions {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}
</style>

<?php include '../../includes/footer.php'; ?>
