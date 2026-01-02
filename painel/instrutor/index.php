<?php
session_start();
require_once '../../config/database.php';
require_once '../../config/auth.php';

verificarAutenticacao();
verificarPermissao('instrutor');

$usuario = getUsuarioLogado();

$page_title = 'Painel do Instrutor - Meritus';
include '../../includes/header.php';
include '../../includes/sidebar.php';
?>

<div class="main-content">
    <div class="content-header">
        <h1>Painel do Instrutor</h1>
        <div class="header-actions">
            <button class="btn btn-primary" data-modal="modal-especialidade">Nova Especialidade</button>
            <button class="btn btn-outline" onclick="registrarAula()">Registrar Aula</button>
        </div>
    </div>
    
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-number"><?php echo getTotalEspecialidades(); ?></div>
            <div class="stat-label">Especialidades</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?php echo getAlunosAtivos(); ?></div>
            <div class="stat-label">Alunos Ativos</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?php echo getAulasMes(); ?></div>
            <div class="stat-label">Aulas Este Mês</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?php echo getConclusoesMes(); ?></div>
            <div class="stat-label">Conclusões Este Mês</div>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Minhas Especialidades</h2>
            <div class="card-actions">
                <select class="form-control" id="filtro-status" onchange="filtrarEspecialidades()">
                    <option value="">Todas</option>
                    <option value="andamento">Em Andamento</option>
                    <option value="concluida">Concluídas</option>
                    <option value="planejada">Planejadas</option>
                </select>
            </div>
        </div>
        <div class="card-body">
            <div class="especialidades-grid">
                <?php
                $especialidades = getEspecialidadesInstrutor($usuario['id']);
                foreach ($especialidades as $especialidade):
                ?>
                <div class="especialidade-card" data-status="<?php echo $especialidade['status']; ?>">
                    <div class="especialidade-header">
                        <h3><?php echo htmlspecialchars($especialidade['nome']); ?></h3>
                        <span class="status-badge badge-<?php echo $especialidade['status']; ?>">
                            <?php echo htmlspecialchars(ucfirst($especialidade['status'])); ?>
                        </span>
                    </div>
                    <div class="especialidade-info">
                        <div class="info-item">
                            <span class="info-label">Nível:</span>
                            <span class="info-value"><?php echo htmlspecialchars($especialidade['nivel']); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Alunos:</span>
                            <span class="info-value"><?php echo $especialidade['total_alunos']; ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Progresso:</span>
                            <span class="info-value"><?php echo $especialidade['progresso']; ?>%</span>
                        </div>
                    </div>
                    <div class="especialidade-progresso">
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: <?php echo $especialidade['progresso']; ?>%"></div>
                        </div>
                    </div>
                    <div class="especialidade-acoes">
                        <button class="btn btn-sm btn-outline" onclick="gerenciarEspecialidade(<?php echo $especialidade['id']; ?>)">Gerenciar</button>
                        <button class="btn btn-sm btn-outline" onclick="verAlunos(<?php echo $especialidade['id']; ?>)">Alunos</button>
                        <button class="btn btn-sm btn-outline" onclick="registrarAula(<?php echo $especialidade['id']; ?>)">+ Aula</button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Aulas Recentes</h2>
            <div class="card-actions">
                <button class="btn btn-primary" data-modal="modal-aula">Registrar Aula</button>
            </div>
        </div>
        <div class="card-body">
            <div class="aulas-timeline">
                <?php
                $aulas = getAulasRecentes($usuario['id']);
                foreach ($aulas as $aula):
                ?>
                <div class="timeline-item">
                    <div class="timeline-marker">
                        <div class="timeline-icon">📚</div>
                    </div>
                    <div class="timeline-content">
                        <div class="timeline-header">
                            <h4><?php echo htmlspecialchars($aula['especialidade_nome']); ?></h4>
                            <span class="timeline-data"><?php echo formatDateTime($aula['data']); ?></span>
                        </div>
                        <p><?php echo htmlspecialchars($aula['conteudo']); ?></p>
                        <div class="timeline-meta">
                            <span class="meta-item">📍 <?php echo htmlspecialchars($aula['local']); ?></span>
                            <span class="meta-item">👥 <?php echo $aula['presentes']; ?> presentes</span>
                            <span class="meta-item">⏰ <?php echo $aula['duracao']; ?> min</span>
                        </div>
                        <div class="timeline-acoes">
                            <button class="btn btn-sm btn-outline" onclick="verAula(<?php echo $aula['id']; ?>)">Ver Detalhes</button>
                            <button class="btn btn-sm btn-outline" onclick="editarAula(<?php echo $aula['id']; ?>)">Editar</button>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Alunos em Destaque</h2>
        </div>
        <div class="card-body">
            <div class="alunos-destaque">
                <?php
                $alunos_destaque = getAlunosDestaque($usuario['id']);
                foreach ($alunos_destaque as $aluno):
                ?>
                <div class="aluno-card">
                    <div class="aluno-avatar">
                        <img src="assets/img/default-avatar.png" alt="<?php echo htmlspecialchars($aluno['nome']); ?>">
                        <div class="aluno-ranking">#<?php echo $aluno['posicao']; ?></div>
                    </div>
                    <div class="aluno-info">
                        <h4><?php echo htmlspecialchars($aluno['nome']); ?></h4>
                        <div class="aluno-unidade"><?php echo htmlspecialchars($aluno['unidade']); ?></div>
                        <div class="aluno-especialidades">
                            <span class="especialidade-count"><?php echo $aluno['especialidades_concluidas']; ?> especialidades</span>
                        </div>
                    </div>
                    <div class="aluno-stats">
                        <div class="stat-item">
                            <span class="stat-number"><?php echo $aluno['aulas_assistidas']; ?></span>
                            <span class="stat-label">Aulas</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-number"><?php echo $aluno['presenca_percentual']; ?>%</span>
                            <span class="stat-label">Presença</span>
                        </div>
                    </div>
                    <div class="aluno-acoes">
                        <button class="btn btn-sm btn-outline" onclick="verPerfilAluno(<?php echo $aluno['id']; ?>)">Perfil</button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Calendário de Aulas</h2>
            <div class="card-actions">
                <button class="btn btn-outline" onclick="verCalendario()">Ver Calendário Completo</button>
            </div>
        </div>
        <div class="card-body">
            <div class="calendario-mini" id="calendario-mini">
                <!-- Calendário será renderizado via JavaScript -->
            </div>
        </div>
    </div>
</div>

<!-- Modal Nova Especialidade -->
<div id="modal-especialidade" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Nova Especialidade</h3>
            <button class="modal-close">&times;</button>
        </div>
        <form action="../api/especialidades.php" method="POST" class="ajax-form">
            <div class="form-group">
                <label for="nome">Nome da Especialidade</label>
                <input type="text" name="nome" id="nome" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="descricao">Descrição</label>
                <textarea name="descricao" id="descricao" class="form-control" rows="3" required></textarea>
            </div>
            <div class="form-group">
                <label for="nivel">Nível</label>
                <select name="nivel" id="nivel" class="form-control" required>
                    <option value="">Selecione...</option>
                    <option value="basico">Básico</option>
                    <option value="intermediario">Intermediário</option>
                    <option value="avancado">Avançado</option>
                    <option value="mestre">Mestre</option>
                </select>
            </div>
            <div class="form-group">
                <label for="categoria">Categoria</label>
                <select name="categoria" id="categoria" class="form-control" required>
                    <option value="">Selecione...</option>
                    <option value="arte">Arte e Cultura</option>
                    <option value="natureza">Natureza</option>
                    <option value="saude">Saúde</option>
                    <option value="tecnologia">Tecnologia</option>
                    <option value="lideranca">Liderança</option>
                    <option value="servico">Serviço Comunitário</option>
                </select>
            </div>
            <div class="form-group">
                <label for="carga_horaria">Carga Horária (horas)</label>
                <input type="number" name="carga_horaria" id="carga_horaria" class="form-control" min="1" required>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Criar Especialidade</button>
                <button type="button" class="btn btn-outline modal-close">Cancelar</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Registrar Aula -->
<div id="modal-aula" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Registrar Aula</h3>
            <button class="modal-close">&times;</button>
        </div>
        <form action="../api/aulas.php" method="POST" class="ajax-form">
            <div class="form-group">
                <label for="especialidade_id">Especialidade</label>
                <select name="especialidade_id" id="especialidade_id" class="form-control" required>
                    <option value="">Selecione...</option>
                    <?php
                    $especialidades = getEspecialidadesInstrutor($usuario['id']);
                    foreach ($especialidades as $especialidade):
                    ?>
                    <option value="<?php echo $especialidade['id']; ?>"><?php echo htmlspecialchars($especialidade['nome']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="data_aula">Data</label>
                    <input type="datetime-local" name="data_aula" id="data_aula" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="duracao">Duração (min)</label>
                    <input type="number" name="duracao" id="duracao" class="form-control" min="15" max="240" required>
                </div>
            </div>
            <div class="form-group">
                <label for="local">Local</label>
                <input type="text" name="local" id="local" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="conteudo">Conteúdo da Aula</label>
                <textarea name="conteudo" id="conteudo" class="form-control" rows="4" required></textarea>
            </div>
            <div class="form-group">
                <label>Alunos Presentes</label>
                <div class="alunos-selecao">
                    <?php
                    $alunos = getTodosAlunos();
                    foreach ($alunos as $aluno):
                    ?>
                    <div class="aluno-checkbox">
                        <input type="checkbox" name="alunos[]" value="<?php echo $aluno['id']; ?>" id="aluno-<?php echo $aluno['id']; ?>">
                        <label for="aluno-<?php echo $aluno['id']; ?>">
                            <img src="assets/img/default-avatar.png" alt="<?php echo htmlspecialchars($aluno['nome']); ?>">
                            <span><?php echo htmlspecialchars($aluno['nome']); ?></span>
                            <small><?php echo htmlspecialchars($aluno['unidade']); ?></small>
                        </label>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Registrar Aula</button>
                <button type="button" class="btn btn-outline modal-close">Cancelar</button>
            </div>
        </form>
    </div>
</div>

<?php
// Funções helper
function getTotalEspecialidades() {
    $db = getDB();
    $instrutor_id = $_SESSION['usuario_id'];
    $stmt = $db->prepare("SELECT COUNT(*) as total FROM especialidades WHERE instrutor_id = ?");
    $stmt->execute([$instrutor_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result['total'];
}

function getAlunosAtivos() {
    $db = getDB();
    $instrutor_id = $_SESSION['usuario_id'];
    $stmt = $db->prepare("
        SELECT COUNT(DISTINCT me.membro_id) as total 
        FROM membros_especialidades me 
        JOIN especialidades e ON me.especialidade_id = e.id 
        WHERE e.instrutor_id = ? AND me.status = 'andamento'
    ");
    $stmt->execute([$instrutor_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result['total'];
}

function getAulasMes() {
    $db = getDB();
    $instrutor_id = $_SESSION['usuario_id'];
    $stmt = $db->prepare("
        SELECT COUNT(*) as total 
        FROM aulas 
        WHERE instrutor_id = ? AND MONTH(data) = MONTH(CURDATE()) AND YEAR(data) = YEAR(CURDATE())
    ");
    $stmt->execute([$instrutor_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result['total'];
}

function getConclusoesMes() {
    $db = getDB();
    $instrutor_id = $_SESSION['usuario_id'];
    $stmt = $db->prepare("
        SELECT COUNT(*) as total 
        FROM membros_especialidades me 
        JOIN especialidades e ON me.especialidade_id = e.id 
        WHERE e.instrutor_id = ? AND me.status = 'concluida' 
        AND MONTH(me.data_conclusao) = MONTH(CURDATE()) AND YEAR(me.data_conclusao) = YEAR(CURDATE())
    ");
    $stmt->execute([$instrutor_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result['total'];
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

function getAulasRecentes($instrutor_id) {
    $db = getDB();
    $stmt = $db->prepare("
        SELECT 
            a.*,
            e.nome as especialidade_nome,
            COUNT(ap.membro_id) as presentes
        FROM aulas a
        JOIN especialidades e ON a.especialidade_id = e.id
        LEFT JOIN aulas_presencas ap ON a.id = ap.aula_id AND ap.presente = 1
        WHERE a.instrutor_id = ?
        GROUP BY a.id
        ORDER BY a.data DESC
        LIMIT 10
    ");
    $stmt->execute([$instrutor_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getAlunosDestaque($instrutor_id) {
    $db = getDB();
    $stmt = $db->prepare("
        SELECT 
            m.*,
            u.nome as unidade,
            COUNT(DISTINCT me.especialidade_id) as especialidades_concluidas,
            COUNT(DISTINCT a.id) as aulas_assistidas,
            (COUNT(DISTINCT a.id) / (SELECT COUNT(*) FROM aulas WHERE instrutor_id = ?)) * 100 as presenca_percentual,
            RANK() OVER (ORDER BY COUNT(DISTINCT me.especialidade_id) DESC) as posicao
        FROM membros m
        JOIN unidades u ON m.unidade_id = u.id
        LEFT JOIN membros_especialidades me ON m.id = me.membro_id AND me.status = 'concluida'
        LEFT JOIN aulas_presencas ap ON m.id = ap.membro_id AND ap.presente = 1
        LEFT JOIN aulas a ON ap.aula_id = a.id AND a.instrutor_id = ?
        WHERE m.status = 'ativo'
        GROUP BY m.id
        ORDER BY especialidades_concluidas DESC, presenca_percentual DESC
        LIMIT 6
    ");
    $stmt->execute([$instrutor_id, $instrutor_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getTodosAlunos() {
    $db = getDB();
    $stmt = $db->query("
        SELECT m.*, u.nome as unidade 
        FROM membros m 
        JOIN unidades u ON m.unidade_id = u.id 
        WHERE m.status = 'ativo' 
        ORDER BY u.nome, m.nome
    ");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function formatDateTime($datetime) {
    return date('d/m/Y H:i', strtotime($datetime));
}
?>

<script>
function filtrarEspecialidades() {
    const status = document.getElementById('filtro-status').value;
    const cards = document.querySelectorAll('.especialidade-card');
    
    cards.forEach(card => {
        const cardStatus = card.getAttribute('data-status');
        card.style.display = !status || cardStatus === status ? '' : 'none';
    });
}

function gerenciarEspecialidade(especialidadeId) {
    window.location.href = `especialidades.php?id=${especialidadeId}`;
}

function verAlunos(especialidadeId) {
    window.location.href = `especialidades.php?id=${especialidadeId}#alunos`;
}

function registrarAula(especialidadeId) {
    if (especialidadeId) {
        document.getElementById('especialidade_id').value = especialidadeId;
    }
    openModal('modal-aula');
}

function verAula(aulaId) {
    window.location.href = `aulas.php?id=${aulaId}`;
}

function editarAula(aulaId) {
    window.location.href = `aulas.php?edit=${aulaId}`;
}

function verPerfilAluno(alunoId) {
    window.location.href = `../secretaria/membros.php?view=${alunoId}`;
}

function verCalendario() {
    window.location.href = 'calendario.php';
}

function renderizarCalendarioMini() {
    const calendario = document.getElementById('calendario-mini');
    const hoje = new Date();
    const ano = hoje.getFullYear();
    const mes = hoje.getMonth();
    
    // Implementar calendário mini
    calendario.innerHTML = `
        <div class="calendario-header">
            <h4>${hoje.toLocaleDateString('pt-BR', { month: 'long', year: 'numeric' })}</h4>
        </div>
        <div class="calendario-grid">
            <!-- Dias do mês serão renderizados aqui -->
        </div>
    `;
}

// Inicializar calendário ao carregar
document.addEventListener('DOMContentLoaded', function() {
    renderizarCalendarioMini();
});
</script>

<style>
.especialidades-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
}

.especialidade-card {
    background: white;
    border-radius: 15px;
    padding: 25px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    border-left: 4px solid #3498db;
    transition: all 0.3s ease;
}

.especialidade-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.15);
}

.especialidade-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
}

.especialidade-header h3 {
    margin: 0;
    color: #2c3e50;
}

.status-badge {
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
    text-transform: uppercase;
}

.badge-andamento {
    background: #f39c12;
    color: white;
}

.badge-concluida {
    background: #27ae60;
    color: white;
}

.badge-planejada {
    background: #3498db;
    color: white;
}

.especialidade-info {
    margin-bottom: 15px;
}

.info-item {
    display: flex;
    justify-content: space-between;
    margin-bottom: 8px;
}

.info-label {
    color: #7f8c8d;
    font-size: 0.9rem;
}

.info-value {
    color: #2c3e50;
    font-weight: 600;
}

.especialidade-progresso {
    margin-bottom: 20px;
}

.progress-bar {
    width: 100%;
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

.especialidade-acoes {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.aulas-timeline {
    position: relative;
    padding-left: 30px;
}

.aulas-timeline::before {
    content: '';
    position: absolute;
    left: 15px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #ecf0f1;
}

.timeline-item {
    position: relative;
    margin-bottom: 30px;
}

.timeline-marker {
    position: absolute;
    left: -30px;
    top: 0;
}

.timeline-icon {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    background: #3498db;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.8rem;
}

.timeline-content {
    background: white;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}

.timeline-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
}

.timeline-header h4 {
    margin: 0;
    color: #2c3e50;
}

.timeline-data {
    font-size: 0.8rem;
    color: #7f8c8d;
}

.timeline-content p {
    margin: 0 0 10px 0;
    color: #5d6d7e;
}

.timeline-meta {
    display: flex;
    gap: 15px;
    margin-bottom: 15px;
    font-size: 0.8rem;
    color: #7f8c8d;
}

.timeline-acoes {
    display: flex;
    gap: 10px;
}

.alunos-destaque {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 20px;
}

.aluno-card {
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

.aluno-ranking {
    position: absolute;
    top: -5px;
    right: -5px;
    background: #e74c3c;
    color: white;
    width: 20px;
    height: 20px;
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
    margin-bottom: 5px;
}

.especialidade-count {
    font-size: 0.8rem;
    color: #3498db;
}

.aluno-stats {
    display: flex;
    flex-direction: column;
    gap: 5px;
    text-align: center;
}

.aluno-stats .stat-number {
    font-size: 1.2rem;
    font-weight: bold;
    color: #2c3e50;
}

.aluno-stats .stat-label {
    font-size: 0.7rem;
    color: #7f8c8d;
}

.calendario-mini {
    background: white;
    border-radius: 10px;
    padding: 20px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.calendario-header {
    text-align: center;
    margin-bottom: 20px;
}

.calendario-header h4 {
    margin: 0;
    color: #2c3e50;
}

.calendario-grid {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 5px;
}

.alunos-selecao {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 10px;
    max-height: 200px;
    overflow-y: auto;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 10px;
}

.aluno-checkbox {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px;
    background: white;
    border-radius: 6px;
}

.aluno-checkbox input[type="checkbox"] {
    width: 16px;
    height: 16px;
}

.aluno-checkbox label {
    display: flex;
    align-items: center;
    gap: 8px;
    flex: 1;
    cursor: pointer;
}

.aluno-checkbox img {
    width: 20px;
    height: 20px;
    border-radius: 50%;
}

.aluno-checkbox small {
    color: #7f8c8d;
    font-size: 0.7rem;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 15px;
}
</style>

<?php include '../../includes/footer.php'; ?>
