<?php
session_start();
require_once '../../config/database.php';
require_once '../../config/auth.php';

verificarAutenticacao();
verificarPermissao('diretor');

$page_title = 'Painel do Diretor - Meritus';
include '../../includes/header.php';
include '../../includes/sidebar.php';
?>

<div class="main-content">
    <div class="content-header">
        <h1>Painel do Diretor</h1>
        <div class="header-actions">
            <button class="btn btn-primary" data-modal="modal-relatorio">Gerar Relatório</button>
        </div>
    </div>
    
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-number"><?php echo getTotalUsuarios(); ?></div>
            <div class="stat-label">Total de Usuários</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?php echo getTotalMembros(); ?></div>
            <div class="stat-label">Total de Membros</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?php echo getTotalUnidades(); ?></div>
            <div class="stat-label">Unidades</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?php echo getPresencaHoje(); ?></div>
            <div class="stat-label">Presentes Hoje</div>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Atividades Recentes</h2>
        </div>
        <div class="card-body">
            <table class="table data-table">
                <thead>
                    <tr>
                        <th>Data</th>
                        <th>Atividade</th>
                        <th>Usuário</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $atividades = getAtividadesRecentes();
                    foreach ($atividades as $atividade):
                    ?>
                    <tr>
                        <td><?php echo formatDate($atividade['data']); ?></td>
                        <td><?php echo htmlspecialchars($atividade['descricao']); ?></td>
                        <td><?php echo htmlspecialchars($atividade['usuario']); ?></td>
                        <td>
                            <span class="badge badge-<?php echo $atividade['status']; ?>">
                                <?php echo htmlspecialchars($atividade['status']); ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Visão Geral das Unidades</h2>
        </div>
        <div class="card-body">
            <div class="unidades-overview">
                <?php
                $unidades = getUnidadesOverview();
                foreach ($unidades as $unidade):
                ?>
                <div class="unidade-card">
                    <h3><?php echo htmlspecialchars($unidade['nome']); ?></h3>
                    <div class="unidade-stats">
                        <div class="stat">
                            <span class="stat-number"><?php echo $unidade['membros']; ?></span>
                            <span class="stat-label">Membros</span>
                        </div>
                        <div class="stat">
                            <span class="stat-number"><?php echo $unidade['presentes']; ?></span>
                            <span class="stat-label">Presentes</span>
                        </div>
                        <div class="stat">
                            <span class="stat-number"><?php echo $unidade['pontos']; ?></span>
                            <span class="stat-label">Pontos</span>
                        </div>
                    </div>
                    <div class="unidade-actions">
                        <a href="unidades.php?id=<?php echo $unidade['id']; ?>" class="btn btn-sm btn-outline">Gerenciar</a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<!-- Modal Relatório -->
<div id="modal-relatorio" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Gerar Relatório</h3>
            <button class="modal-close">&times;</button>
        </div>
        <form action="../api/relatorios.php" method="POST" class="ajax-form">
            <div class="form-group">
                <label for="tipo_relatorio">Tipo de Relatório</label>
                <select name="tipo_relatorio" id="tipo_relatorio" class="form-control" required>
                    <option value="">Selecione...</option>
                    <option value="geral">Relatório Geral</option>
                    <option value="presenca">Relatório de Presença</option>
                    <option value="pontos">Relatório de Pontos</option>
                    <option value="membros">Relatório de Membros</option>
                </select>
            </div>
            <div class="form-group">
                <label for="data_inicio">Data Início</label>
                <input type="date" name="data_inicio" id="data_inicio" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="data_fim">Data Fim</label>
                <input type="date" name="data_fim" id="data_fim" class="form-control" required>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Gerar Relatório</button>
                <button type="button" class="btn btn-outline modal-close">Cancelar</button>
            </div>
        </form>
    </div>
</div>

<?php
// Funções helper
function getTotalUsuarios() {
    $db = getDB();
    $stmt = $db->query("SELECT COUNT(*) as total FROM usuarios WHERE status = 'ativo'");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result['total'];
}

function getTotalMembros() {
    $db = getDB();
    $stmt = $db->query("SELECT COUNT(*) as total FROM membros WHERE status = 'ativo'");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result['total'];
}

function getTotalUnidades() {
    $db = getDB();
    $stmt = $db->query("SELECT COUNT(*) as total FROM unidades WHERE status = 'ativo'");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result['total'];
}

function getPresencaHoje() {
    $db = getDB();
    $stmt = $db->prepare("SELECT COUNT(*) as total FROM presenca WHERE data = CURDATE() AND presente = 1");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result['total'];
}

function getAtividadesRecentes() {
    $db = getDB();
    $stmt = $db->query("
        SELECT a.*, u.nome as usuario 
        FROM atividades a 
        JOIN usuarios u ON a.usuario_id = u.id 
        ORDER BY a.data DESC 
        LIMIT 10
    ");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getUnidadesOverview() {
    $db = getDB();
    $stmt = $db->query("
        SELECT 
            u.id, u.nome,
            COUNT(DISTINCT m.id) as membros,
            COUNT(DISTINCT CASE WHEN p.presente = 1 AND p.data = CURDATE() THEN p.membro_id END) as presentes,
            COALESCE(SUM(mp.pontos), 0) as pontos
        FROM unidades u
        LEFT JOIN membros m ON u.id = m.unidade_id AND m.status = 'ativo'
        LEFT JOIN presenca p ON m.id = p.membro_id
        LEFT JOIN membros_pontos mp ON m.id = mp.membro_id
        WHERE u.status = 'ativo'
        GROUP BY u.id, u.nome
        ORDER BY u.nome
    ");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function formatDate($date) {
    return date('d/m/Y H:i', strtotime($date));
}

include '../../includes/footer.php';
?>
