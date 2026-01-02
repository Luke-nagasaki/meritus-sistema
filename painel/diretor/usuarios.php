<?php
session_start();
require_once '../../config/database.php';
require_once '../../config/auth.php';

verificarAutenticacao();
verificarPermissao('diretor');

$page_title = 'Gerenciar Usuários - Meritus';
include '../../includes/header.php';
include '../../includes/sidebar.php';
?>

<div class="main-content">
    <div class="content-header">
        <h1>Gerenciar Usuários</h1>
        <div class="header-actions">
            <button class="btn btn-primary" data-modal="modal-usuario">Novo Usuário</button>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Lista de Usuários</h2>
            <div class="card-actions">
                <input type="text" class="form-control table-search" placeholder="Buscar usuário...">
            </div>
        </div>
        <div class="card-body">
            <table class="table data-table">
                <thead>
                    <tr>
                        <th data-sort="nome">Nome</th>
                        <th data-sort="email">Email</th>
                        <th data-sort="cargo">Cargo</th>
                        <th data-sort="unidade">Unidade</th>
                        <th data-sort="status">Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $usuarios = getUsuarios();
                    foreach ($usuarios as $usuario):
                    ?>
                    <tr>
                        <td><?php echo htmlspecialchars($usuario['nome']); ?></td>
                        <td><?php echo htmlspecialchars($usuario['email']); ?></td>
                        <td>
                            <span class="badge badge-info"><?php echo htmlspecialchars(ucfirst($usuario['cargo'])); ?></span>
                        </td>
                        <td><?php echo htmlspecialchars($usuario['unidade_nome'] ?? 'N/A'); ?></td>
                        <td>
                            <span class="badge badge-<?php echo $usuario['status'] === 'ativo' ? 'success' : 'danger'; ?>">
                                <?php echo htmlspecialchars(ucfirst($usuario['status'])); ?>
                            </span>
                        </td>
                        <td>
                            <div class="action-buttons">
                                <button class="btn btn-sm btn-outline" onclick="editarUsuario(<?php echo $usuario['id']; ?>)">Editar</button>
                                <button class="btn btn-sm btn-outline" onclick="toggleStatus(<?php echo $usuario['id']; ?>)">
                                    <?php echo $usuario['status'] === 'ativo' ? 'Desativar' : 'Ativar'; ?>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Novo Usuário -->
<div id="modal-usuario" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Novo Usuário</h3>
            <button class="modal-close">&times;</button>
        </div>
        <form action="../api/usuarios.php" method="POST" class="ajax-form">
            <div class="form-group">
                <label for="nome">Nome</label>
                <input type="text" name="nome" id="nome" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" name="email" id="email" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="senha">Senha</label>
                <input type="password" name="senha" id="senha" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="cargo">Cargo</label>
                <select name="cargo" id="cargo" class="form-control" required>
                    <option value="">Selecione...</option>
                    <option value="diretor">Diretor</option>
                    <option value="secretaria">Secretaria</option>
                    <option value="conselheiro">Conselheiro</option>
                    <option value="instrutor">Instrutor</option>
                    <option value="monitor">Monitor</option>
                </select>
            </div>
            <div class="form-group">
                <label for="unidade_id">Unidade</label>
                <select name="unidade_id" id="unidade_id" class="form-control">
                    <option value="">Selecione...</option>
                    <?php
                    $unidades = getUnidades();
                    foreach ($unidades as $unidade):
                    ?>
                    <option value="<?php echo $unidade['id']; ?>"><?php echo htmlspecialchars($unidade['nome']); ?></option>
                    <?php endforeach; ?>
                </select>
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
function getUsuarios() {
    $db = getDB();
    $stmt = $db->query("
        SELECT u.*, un.nome as unidade_nome 
        FROM usuarios u 
        LEFT JOIN unidades un ON u.unidade_id = un.id 
        ORDER BY u.nome
    ");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getUnidades() {
    $db = getDB();
    $stmt = $db->query("SELECT id, nome FROM unidades WHERE status = 'ativo' ORDER BY nome");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<script>
function editarUsuario(id) {
    // Implementar edição de usuário
    alert('Funcionalidade de edição em desenvolvimento');
}

function toggleStatus(id) {
    if (confirm('Tem certeza que deseja alterar o status deste usuário?')) {
        fetch('../api/usuarios.php', {
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

<?php include '../../includes/footer.php'; ?>
