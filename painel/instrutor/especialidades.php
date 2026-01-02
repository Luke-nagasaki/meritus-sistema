<?php
session_start();
require_once '../../config/database.php';
require_once '../../config/auth.php';

verificarAutenticacao();
verificarPermissao('instrutor');

$usuario = getUsuarioLogado();
$especialidade_id = $_GET['id'] ?? null;

$page_title = 'Especialidades - Meritus';
include '../../includes/header.php';
include '../../includes/sidebar.php';
?>

<div class="main-content">
    <div class="content-header">
        <h1>Gestão de Especialidades</h1>
        <div class="header-actions">
            <button class="btn btn-primary" data-modal="modal-especialidade">Nova Especialidade</button>
            <button class="btn btn-outline" onclick="exportarEspecialidades()">Exportar</button>
        </div>
    </div>
    
    <?php if ($especialidade_id): ?>
    <!-- Detalhes de uma especialidade específica -->
    <?php
    $especialidade = getEspecialidadeDetalhes($especialidade_id, $usuario['id']);
    if (!$especialidade) {
        echo '<div class="alert alert-error">Especialidade não encontrada ou você não tem permissão para acessá-la.</div>';
    } else {
    ?>
    <div class="especialidade-header">
        <div class="especialidade-info">
            <h2><?php echo htmlspecialchars($especialidade['nome']); ?></h2>
            <p><?php echo htmlspecialchars($especialidade['descricao']); ?></p>
            <div class="especialidade-meta">
                <span class="meta-item">📚 Nível: <?php echo htmlspecialchars(ucfirst($especialidade['nivel'])); ?></span>
                <span class="meta-item">🏷️ Categoria: <?php echo htmlspecialchars(ucfirst($especialidade['categoria'])); ?></span>
                <span class="meta-item">⏱️ Carga Horária: <?php echo $especialidade['carga_horaria']; ?>h</span>
                <span class="meta-item">👥 Alunos: <?php echo $especialidade['total_alunos']; ?></span>
            </div>
        </div>
        <div class="especialidade-progresso-geral">
            <div class="progresso-circular">
                <div class="progresso-value"><?php echo round($especialidade['progresso_medio']); ?>%</div>
            </div>
            <span class="progresso-label">Progresso Médio</span>
        </div>
    </div>
    
    <div class="tabs-container">
        <div class="tabs-header">
            <button class="tab-btn active" onclick="showTab('alunos')">Alunos</button>
            <button class="tab-btn" onclick="showTab('aulas')">Aulas</button>
            <button class="tab-btn" onclick="showTab('requisitos')">Requisitos</button>
            <button class="tab-btn" onclick="showTab('materiais')">Materiais</button>
        </div>
        
        <div class="tabs-content">
            <!-- Tab Alunos -->
            <div id="alunos" class="tab-pane active">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Alunos Matriculados</h3>
                        <div class="card-actions">
                            <button class="btn btn-primary" data-modal="modal-matricula">Nova Matrícula</button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="alunos-grid">
                            <?php
                            $alunos = getAlunosEspecialidade($especialidade_id);
                            foreach ($alunos as $aluno):
                            ?>
                            <div class="aluno-especialidade-card">
                                <div class="aluno-avatar">
                                    <img src="assets/img/default-avatar.png" alt="<?php echo htmlspecialchars($aluno['nome']); ?>">
                                    <div class="progresso-badge"><?php echo round($aluno['progresso']); ?>%</div>
                                </div>
                                <div class="aluno-info">
                                    <h4><?php echo htmlspecialchars($aluno['nome']); ?></h4>
                                    <div class="aluno-unidade"><?php echo htmlspecialchars($aluno['unidade']); ?></div>
                                    <div class="aluno-status">
                                        <span class="status-badge badge-<?php echo $aluno['status']; ?>">
                                            <?php echo htmlspecialchars(ucfirst($aluno['status'])); ?>
                                        </span>
                                        <span class="data-matricula">Matrícula: <?php echo formatDate($aluno['data_matricula']); ?></span>
                                    </div>
                                </div>
                                <div class="aluno-progresso">
                                    <div class="progress-bar">
                                        <div class="progress-fill" style="width: <?php echo $aluno['progresso']; ?>%"></div>
                                    </div>
                                </div>
                                <div class="aluno-acoes">
                                    <button class="btn btn-sm btn-outline" onclick="verProgresso(<?php echo $aluno['id']; ?>)">Progresso</button>
                                    <button class="btn btn-sm btn-outline" onclick="editarMatricula(<?php echo $aluno['id']; ?>)">Editar</button>
                                    <?php if ($aluno['status'] === 'andamento'): ?>
                                    <button class="btn btn-sm btn-primary" onclick="concluirEspecialidade(<?php echo $aluno['id']; ?>)">Concluir</button>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Tab Aulas -->
            <div id="aulas" class="tab-pane">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Aulas da Especialidade</h3>
                        <div class="card-actions">
                            <button class="btn btn-primary" data-modal="modal-aula">Nova Aula</button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="aulas-lista">
                            <?php
                            $aulas = getAulasEspecialidade($especialidade_id);
                            foreach ($aulas as $aula):
                            ?>
                            <div class="aula-item">
                                <div class="aula-data">
                                    <div class="data-dia"><?php echo date('d', strtotime($aula['data'])); ?></div>
                                    <div class="data-mes"><?php echo strftime('%b', strtotime($aula['data'])); ?></div>
                                </div>
                                <div class="aula-conteudo">
                                    <h4><?php echo htmlspecialchars($aula['conteudo']); ?></h4>
                                    <p><?php echo htmlspecialchars($aula['descricao']); ?></p>
                                    <div class="aula-meta">
                                        <span class="meta-item">📍 <?php echo htmlspecialchars($aula['local']); ?></span>
                                        <span class="meta-item">⏰ <?php echo $aula['duracao']; ?> min</span>
                                        <span class="meta-item">👥 <?php echo $aula['presentes']; ?> presentes</span>
                                    </div>
                                </div>
                                <div class="aula-acoes">
                                    <button class="btn btn-sm btn-outline" onclick="verAula(<?php echo $aula['id']; ?>)">Ver</button>
                                    <button class="btn btn-sm btn-outline" onclick="editarAula(<?php echo $aula['id']; ?>)">Editar</button>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Tab Requisitos -->
            <div id="requisitos" class="tab-pane">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Requisitos da Especialidade</h3>
                        <div class="card-actions">
                            <button class="btn btn-primary" data-modal="modal-requisito">Novo Requisito</button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="requisitos-lista">
                            <?php
                            $requisitos = getRequisitosEspecialidade($especialidade_id);
                            foreach ($requisitos as $requisito):
                            ?>
                            <div class="requisito-item">
                                <div class="requisito-status">
                                    <input type="checkbox" id="req-<?php echo $requisito['id']; ?>" <?php echo $requisito['obrigatorio'] ? 'checked disabled' : ''; ?>>
                                    <label for="req-<?php echo $requisito['id']; ?>"></label>
                                </div>
                                <div class="requisito-info">
                                    <h4><?php echo htmlspecialchars($requisito['descricao']); ?></h4>
                                    <p><?php echo htmlspecialchars($requisito['detalhes']); ?></p>
                                    <div class="requisito-meta">
                                        <span class="meta-item">📊 Peso: <?php echo $requisito['peso']; ?>%</span>
                                        <?php if ($requisito['obrigatorio']): ?>
                                        <span class="meta-item obrigatorio">Obrigatório</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="requisito-acoes">
                                    <button class="btn btn-sm btn-outline" onclick="editarRequisito(<?php echo $requisito['id']; ?>)">Editar</button>
                                    <button class="btn btn-sm btn-outline" onclick="excluirRequisito(<?php echo $requisito['id']; ?>)">Excluir</button>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Tab Materiais -->
            <div id="materiais" class="tab-pane">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Materiais de Apoio</h3>
                        <div class="card-actions">
                            <button class="btn btn-primary" data-modal="modal-material">Novo Material</button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="materiais-grid">
                            <?php
                            $materiais = getMateriaisEspecialidade($especialidade_id);
                            foreach ($materiais as $material):
                            ?>
                            <div class="material-card">
                                <div class="material-icon">
                                    <?php echo getMaterialIcon($material['tipo']); ?>
                                </div>
                                <div class="material-info">
                                    <h4><?php echo htmlspecialchars($material['titulo']); ?></h4>
                                    <p><?php echo htmlspecialchars($material['descricao']); ?></p>
                                    <div class="material-meta">
                                        <span class="meta-item">📁 <?php echo htmlspecialchars(ucfirst($material['tipo'])); ?></span>
                                        <span class="meta-item">📅 <?php echo formatDate($material['data_upload']); ?></span>
                                    </div>
                                </div>
                                <div class="material-acoes">
                                    <button class="btn btn-sm btn-outline" onclick="baixarMaterial(<?php echo $material['id']; ?>)">Baixar</button>
                                    <button class="btn btn-sm btn-outline" onclick="editarMaterial(<?php echo $material['id']; ?>)">Editar</button>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
    }
    ?>
    <?php else: ?>
    <!-- Lista de todas as especialidades -->
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Minhas Especialidades</h2>
            <div class="card-filters">
                <input type="text" class="form-control" placeholder="Buscar especialidade..." id="busca-especialidade" onkeyup="filtrarEspecialidades()">
                <select class="form-control" id="filtro-categoria" onchange="filtrarEspecialidades()">
                    <option value="">Todas as Categorias</option>
                    <option value="arte">Arte e Cultura</option>
                    <option value="natureza">Natureza</option>
                    <option value="saude">Saúde</option>
                    <option value="tecnologia">Tecnologia</option>
                    <option value="lideranca">Liderança</option>
                    <option value="servico">Serviço Comunitário</option>
                </select>
            </div>
        </div>
        <div class="card-body">
            <div class="especialidades-lista">
                <?php
                $especialidades = getEspecialidadesInstrutor($usuario['id']);
                foreach ($especialidades as $especialidade):
                ?>
                <div class="especialidade-lista-item" data-nome="<?php echo strtolower($especialidade['nome']); ?>" data-categoria="<?php echo $especialidade['categoria']; ?>">
                    <div class="especialidade-sumario">
                        <div class="especialidade-icon">
                            <?php echo getCategoriaIcon($especialidade['categoria']); ?>
                        </div>
                        <div class="especialidade-detalhes">
                            <h3><?php echo htmlspecialchars($especialidade['nome']); ?></h3>
                            <p><?php echo htmlspecialchars($especialidade['descricao']); ?></p>
                            <div class="especialidade-meta">
                                <span class="meta-item">📚 <?php echo htmlspecialchars(ucfirst($especialidade['nivel'])); ?></span>
                                <span class="meta-item">🏷️ <?php echo htmlspecialchars(ucfirst($especialidade['categoria'])); ?></span>
                                <span class="meta-item">⏱️ <?php echo $especialidade['carga_horaria']; ?>h</span>
                                <span class="meta-item">👥 <?php echo $especialidade['total_alunos']; ?> alunos</span>
                            </div>
                        </div>
                    </div>
                    <div class="especialidade-progresso">
                        <div class="progresso-info">
                            <span class="progresso-text"><?php echo round($especialidade['progresso']); ?>% concluído</span>
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: <?php echo $especialidade['progresso']; ?>%"></div>
                            </div>
                        </div>
                    </div>
                    <div class="especialidade-acoes">
                        <button class="btn btn-primary" onclick="verEspecialidade(<?php echo $especialidade['id']; ?>)">Gerenciar</button>
                        <button class="btn btn-outline" onclick="editarEspecialidade(<?php echo $especialidade['id']; ?>)">Editar</button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Modais -->
<div id="modal-especialidade" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Nova Especialidade</h3>
            <button class="modal-close">&times;</button>
        </div>
        <form action="../api/especialidades.php" method="POST" class="ajax-form">
            <!-- Formulário de especialidade -->
        </form>
    </div>
</div>

<div id="modal-matricula" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Nova Matrícula</h3>
            <button class="modal-close">&times;</button>
        </div>
        <form action="../api/matriculas.php" method="POST" class="ajax-form">
            <!-- Formulário de matrícula -->
        </form>
    </div>
</div>

<?php
// Funções helper
function getEspecialidadeDetalhes($especialidade_id, $instrutor_id) {
    $db = getDB();
    $stmt = $db->prepare("
        SELECT 
            e.*,
            COUNT(DISTINCT me.membro_id) as total_alunos,
            AVG(me.progresso) as progresso_medio
        FROM especialidades e
        LEFT JOIN membros_especialidades me ON e.id = me.especialidade_id
        WHERE e.id = ? AND e.instrutor_id = ?
        GROUP BY e.id
    ");
    $stmt->execute([$especialidade_id, $instrutor_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function getAlunosEspecialidade($especialidade_id) {
    $db = getDB();
    $stmt = $db->prepare("
        SELECT 
            me.*,
            m.nome,
            u.nome as unidade
        FROM membros_especialidades me
        JOIN membros m ON me.membro_id = m.id
        JOIN unidades u ON m.unidade_id = u.id
        WHERE me.especialidade_id = ?
        ORDER BY me.progresso DESC, m.nome
    ");
    $stmt->execute([$especialidade_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getAulasEspecialidade($especialidade_id) {
    $db = getDB();
    $stmt = $db->prepare("
        SELECT 
            a.*,
            COUNT(ap.membro_id) as presentes
        FROM aulas a
        LEFT JOIN aulas_presencas ap ON a.id = ap.aula_id AND ap.presente = 1
        WHERE a.especialidade_id = ?
        GROUP BY a.id
        ORDER BY a.data DESC
    ");
    $stmt->execute([$especialidade_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getRequisitosEspecialidade($especialidade_id) {
    $db = getDB();
    $stmt = $db->prepare("
        SELECT * FROM especialidades_requisitos 
        WHERE especialidade_id = ? 
        ORDER BY obrigatorio DESC, peso DESC
    ");
    $stmt->execute([$especialidade_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getMateriaisEspecialidade($especialidade_id) {
    $db = getDB();
    $stmt = $db->prepare("
        SELECT * FROM especialidades_materiais 
        WHERE especialidade_id = ? 
        ORDER BY data_upload DESC
    ");
    $stmt->execute([$especialidade_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getEspecialidadesInstrutor($instrutor_id) {
    $db = getDB();
    $stmt = $db->prepare("
        SELECT 
            e.*,
            COUNT(DISTINCT me.membro_id) as total_alunos,
            AVG(me.progresso) as progresso
        FROM especialidades e
        LEFT JOIN membros_especialidades me ON e.id = me.especialidade_id
        WHERE e.instrutor_id = ?
        GROUP BY e.id
        ORDER BY e.nome
    ");
    $stmt->execute([$instrutor_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getCategoriaIcon($categoria) {
    $icons = [
        'arte' => '🎨',
        'natureza' => '🌿',
        'saude' => '🏥',
        'tecnologia' => '💻',
        'lideranca' => '👑',
        'servico' => '🤝'
    ];
    return $icons[$categoria] ?? '📚';
}

function getMaterialIcon($tipo) {
    $icons = [
        'pdf' => '📄',
        'video' => '🎥',
        'imagem' => '🖼️',
        'documento' => '📝',
        'link' => '🔗'
    ];
    return $icons[$tipo] ?? '📁';
}

function formatDate($date) {
    return date('d/m/Y', strtotime($date));
}
?>

<script>
function showTab(tabName) {
    document.querySelectorAll('.tab-pane').forEach(pane => pane.classList.remove('active'));
    document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
    
    document.getElementById(tabName).classList.add('active');
    event.target.classList.add('active');
}

function filtrarEspecialidades() {
    const busca = document.getElementById('busca-especialidade').value.toLowerCase();
    const categoria = document.getElementById('filtro-categoria').value;
    const itens = document.querySelectorAll('.especialidade-lista-item');
    
    itens.forEach(item => {
        const nome = item.getAttribute('data-nome');
        const itemCategoria = item.getAttribute('data-categoria');
        
        const matchBusca = !busca || nome.includes(busca);
        const matchCategoria = !categoria || itemCategoria === categoria;
        
        item.style.display = matchBusca && matchCategoria ? '' : 'none';
    });
}

function verEspecialidade(especialidadeId) {
    window.location.href = `?id=${especialidadeId}`;
}

function editarEspecialidade(especialidadeId) {
    window.location.href = `?edit=${especialidadeId}`;
}

function verProgresso(alunoId) {
    window.location.href = `?aluno=${alunoId}`;
}

function editarMatricula(matriculaId) {
    window.location.href = `?edit_matricula=${matriculaId}`;
}

function concluirEspecialidade(matriculaId) {
    if (confirm('Tem certeza que deseja concluir esta especialidade para este aluno?')) {
        fetch('../api/matriculas.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'concluir',
                id: matriculaId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('Especialidade concluída com sucesso!', 'success');
                setTimeout(() => location.reload(), 1500);
            } else {
                showAlert(data.message, 'error');
            }
        });
    }
}

function verAula(aulaId) {
    window.location.href = `aulas.php?id=${aulaId}`;
}

function editarAula(aulaId) {
    window.location.href = `aulas.php?edit=${aulaId}`;
}

function editarRequisito(requisitoId) {
    window.location.href = `?edit_requisito=${requisitoId}`;
}

function excluirRequisito(requisitoId) {
    if (confirm('Tem certeza que deseja excluir este requisito?')) {
        fetch('../api/requisitos.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'excluir',
                id: requisitoId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('Requisito excluído com sucesso!', 'success');
                setTimeout(() => location.reload(), 1500);
            } else {
                showAlert(data.message, 'error');
            }
        });
    }
}

function baixarMaterial(materialId) {
    window.location.href = `../api/materiais.php?action=download&id=${materialId}`;
}

function editarMaterial(materialId) {
    window.location.href = `?edit_material=${materialId}`;
}

function exportarEspecialidades() {
    window.location.href = '../api/especialidades.php?action=exportar';
}
</script>

<style>
.especialidade-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 30px;
    border-radius: 15px;
    margin-bottom: 30px;
}

.especialidade-info h2 {
    margin: 0 0 10px 0;
    font-size: 2rem;
}

.especialidade-meta {
    display: flex;
    gap: 20px;
    margin-top: 15px;
    flex-wrap: wrap;
}

.meta-item {
    font-size: 0.9rem;
    opacity: 0.9;
}

.progresso-circular {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    background: conic-gradient(#27ae60 0deg, #27ae60 calc(360deg * var(--progresso) / 100), #ecf0f1 calc(360deg * var(--progresso) / 100));
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
}

.progresso-circular::before {
    content: '';
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: white;
    position: absolute;
}

.progresso-value {
    font-size: 1.2rem;
    font-weight: bold;
    color: #2c3e50;
    z-index: 1;
}

.progresso-label {
    text-align: center;
    margin-top: 10px;
    font-size: 0.9rem;
}

.especialidades-lista {
    display: grid;
    gap: 20px;
}

.especialidade-lista-item {
    display: flex;
    align-items: center;
    gap: 20px;
    padding: 25px;
    background: white;
    border-radius: 15px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
}

.especialidade-lista-item:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.15);
}

.especialidade-sumario {
    display: flex;
    align-items: center;
    gap: 20px;
    flex: 1;
}

.especialidade-icon {
    font-size: 2.5rem;
    min-width: 60px;
    text-align: center;
}

.especialidade-detalhes {
    flex: 1;
}

.especialidade-detalhes h3 {
    margin: 0 0 10px 0;
    color: #2c3e50;
}

.especialidade-progresso {
    min-width: 200px;
}

.progresso-info {
    text-align: center;
}

.progresso-text {
    font-size: 0.9rem;
    color: #7f8c8d;
    margin-bottom: 8px;
}

.especialidade-acoes {
    display: flex;
    gap: 10px;
}

.alunos-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
    gap: 20px;
}

.aluno-especialidade-card {
    background: white;
    border-radius: 10px;
    padding: 20px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    display: flex;
    align-items: center;
    gap: 15px;
}

.aluno-avatar {
    position: relative;
}

.aluno-avatar img {
    width: 50px;
    height: 50px;
    border-radius: 50%;
}

.progresso-badge {
    position: absolute;
    top: -5px;
    right: -5px;
    background: #3498db;
    color: white;
    width: 25px;
    height: 25px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.7rem;
    font-weight: bold;
}

.aluno-info {
    flex: 1;
}

.aluno-info h4 {
    margin: 0 0 5px 0;
    color: #2c3e50;
}

.aluno-unidade {
    font-size: 0.8rem;
    color: #7f8c8d;
    margin-bottom: 8px;
}

.aluno-status {
    display: flex;
    align-items: center;
    gap: 10px;
}

.data-matricula {
    font-size: 0.7rem;
    color: #95a5a6;
}

.aluno-progresso {
    min-width: 100px;
}

.aluno-acoes {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.aulas-lista {
    display: grid;
    gap: 20px;
}

.aula-item {
    display: flex;
    gap: 20px;
    padding: 20px;
    background: white;
    border-radius: 10px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}

.aula-data {
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

.aula-conteudo {
    flex: 1;
}

.aula-meta {
    display: flex;
    gap: 15px;
    margin-top: 10px;
    font-size: 0.8rem;
    color: #7f8c8d;
}

.requisitos-lista {
    display: grid;
    gap: 15px;
}

.requisito-item {
    display: flex;
    align-items: center;
    gap: 20px;
    padding: 20px;
    background: white;
    border-radius: 10px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}

.requisito-status input[type="checkbox"] {
    width: 20px;
    height: 20px;
}

.requisito-info {
    flex: 1;
}

.requisito-meta {
    display: flex;
    gap: 15px;
    margin-top: 10px;
    font-size: 0.8rem;
}

.obrigatorio {
    background: #e74c3c;
    color: white;
    padding: 2px 8px;
    border-radius: 12px;
}

.materiais-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
}

.material-card {
    background: white;
    border-radius: 10px;
    padding: 20px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    display: flex;
    align-items: center;
    gap: 15px;
}

.material-icon {
    font-size: 2rem;
    min-width: 40px;
    text-align: center;
}
</style>

<?php include '../../includes/footer.php'; ?>
