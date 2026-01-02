<?php
session_start();
require_once '../../config/database.php';
require_once '../../config/auth.php';

verificarAutenticacao();
verificarPermissao('conselheiro');

$usuario = getUsuarioLogado();
$unidade_id = $usuario['unidade_id'];

$page_title = 'Painel do Conselheiro - Meritus';
include '../../includes/header.php';
include '../../includes/sidebar.php';
?>

<div class="main-content">
    <div class="content-header">
        <h1>Painel do Conselheiro</h1>
        <div class="header-actions">
            <button class="btn btn-primary" data-modal="modal-pontos">Lançar Pontos</button>
            <button class="btn btn-outline" onclick="registrarAtividade()">Registrar Atividade</button>
        </div>
    </div>
    
    <div class="unidade-header">
        <div class="unidade-info">
            <h2><?php echo htmlspecialchars(getNomeUnidade($unidade_id)); ?></h2>
            <p class="unidade-descricao"><?php echo htmlspecialchars(getDescricaoUnidade($unidade_id)); ?></p>
        </div>
        <div class="unidade-stats">
            <div class="stat-item">
                <span class="stat-number"><?php echo getTotalMembrosUnidade($unidade_id); ?></span>
                <span class="stat-label">Membros</span>
            </div>
            <div class="stat-item">
                <span class="stat-number"><?php echo getPresentesHojeUnidade($unidade_id); ?></span>
                <span class="stat-label">Presentes Hoje</span>
            </div>
            <div class="stat-item">
                <span class="stat-number"><?php echo getPontosTotalUnidade($unidade_id); ?></span>
                <span class="stat-label">Pontos Totais</span>
            </div>
        </div>
    </div>
    
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-number"><?php echo getPresencaMediaUnidade($unidade_id); ?>%</div>
            <div class="stat-label">Presença Média</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?php echo getAtividadesMes($unidade_id); ?></div>
            <div class="stat-label">Atividades no Mês</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?php echo getEspecialidadesConcluidas($unidade_id); ?></div>
            <div class="stat-label">Especialidades Concluídas</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?php echo getPosicaoRanking($unidade_id); ?>º</div>
            <div class="stat-label">Posição no Ranking</div>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Meus Membros</h2>
            <div class="card-actions">
                <button class="btn btn-sm btn-outline" onclick="verTodosMembros()">Ver Todos</button>
            </div>
        </div>
        <div class="card-body">
            <div class="membros-grid">
                <?php
                $membros = getMembrosUnidade($unidade_id);
                foreach ($membros as $membro):
                ?>
                <div class="membro-card">
                    <div class="membro-avatar">
                        <img src="assets/img/default-avatar.png" alt="<?php echo htmlspecialchars($membro['nome']); ?>">
                        <div class="membro-status <?php echo $membro['presente_hoje'] ? 'online' : 'offline'; ?>"></div>
                    </div>
                    <div class="membro-info">
                        <h3><?php echo htmlspecialchars($membro['nome']); ?></h3>
                        <div class="membro-detalhes">
                            <span class="idade"><?php echo calcularIdade($membro['data_nascimento']); ?> anos</span>
                            <span class="pontos"><?php echo $membro['pontos']; ?> pts</span>
                        </div>
                    </div>
                    <div class="membro-acoes">
                        <button class="btn btn-sm btn-outline" onclick="lancarPontos(<?php echo $membro['id']; ?>)">+ Pontos</button>
                        <button class="btn btn-sm btn-outline" onclick="verPerfil(<?php echo $membro['id']; ?>)">Perfil</button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Ranking da Unidade</h2>
            <div class="card-actions">
                <select class="form-control" id="periodo-ranking" onchange="atualizarRanking()">
                    <option value="semana">Esta Semana</option>
                    <option value="mes">Este Mês</option>
                    <option value="trimestre">Este Trimestre</option>
                    <option value="ano">Este Ano</option>
                </select>
            </div>
        </div>
        <div class="card-body">
            <div class="ranking-list" id="ranking-list">
                <?php
                $ranking = getRankingUnidade($unidade_id);
                foreach ($ranking as $posicao => $membro):
                ?>
                <div class="ranking-item">
                    <div class="ranking-posicao"><?php echo $posicao + 1; ?>º</div>
                    <div class="ranking-avatar">
                        <img src="assets/img/default-avatar.png" alt="<?php echo htmlspecialchars($membro['nome']); ?>">
                    </div>
                    <div class="ranking-info">
                        <div class="ranking-nome"><?php echo htmlspecialchars($membro['nome']); ?></div>
                        <div class="ranking-progresso">
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: <?php echo $membro['percentual']; ?>%"></div>
                            </div>
                            <span class="progress-text"><?php echo $membro['pontos']; ?> pts</span>
                        </div>
                    </div>
                    <div class="ranking-trofeu">
                        <?php if ($posicao < 3): ?>
                            <span class="trofeu"><?php echo ['🥇', '🥈', '🥉'][$posicao]; ?></span>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Atividades Recentes</h2>
        </div>
        <div class="card-body">
            <div class="atividades-timeline">
                <?php
                $atividades = getAtividadesRecentesUnidade($unidade_id);
                foreach ($atividades as $atividade):
                ?>
                <div class="timeline-item">
                    <div class="timeline-marker">
                        <div class="timeline-icon"><?php echo getAtividadeIcon($atividade['tipo']); ?></div>
                    </div>
                    <div class="timeline-content">
                        <div class="timeline-header">
                            <h4><?php echo htmlspecialchars($atividade['titulo']); ?></h4>
                            <span class="timeline-data"><?php echo formatDateTime($atividade['data']); ?></span>
                        </div>
                        <p><?php echo htmlspecialchars($atividade['descricao']); ?></p>
                        <div class="timeline-participantes">
                            <span class="participantes-count"><?php echo $atividade['participantes']; ?> participantes</span>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<!-- Modal Lançar Pontos -->
<div id="modal-pontos" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Lançar Pontos</h3>
            <button class="modal-close">&times;</button>
        </div>
        <form action="../api/pontos.php" method="POST" class="ajax-form">
            <div class="form-group">
                <label for="membro_id">Membro</label>
                <select name="membro_id" id="membro_id" class="form-control" required>
                    <option value="">Selecione...</option>
                    <?php
                    $membros = getMembrosUnidade($unidade_id);
                    foreach ($membros as $membro):
                    ?>
                    <option value="<?php echo $membro['id']; ?>"><?php echo htmlspecialchars($membro['nome']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="categoria">Categoria</label>
                <select name="categoria" id="categoria" class="form-control" required>
                    <option value="">Selecione...</option>
                    <option value="presenca">Presença</option>
                    <option value="participacao">Participação</option>
                    <option value="comportamento">Comportamento</option>
                    <option value="especialidade">Especialidade</option>
                    <option value="atividade">Atividade</option>
                    <option value="lideranca">Liderança</option>
                </select>
            </div>
            <div class="form-group">
                <label for="pontos">Pontos</label>
                <input type="number" name="pontos" id="pontos" class="form-control" min="1" max="100" required>
            </div>
            <div class="form-group">
                <label for="descricao">Descrição</label>
                <textarea name="descricao" id="descricao" class="form-control" rows="3" required></textarea>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Lançar Pontos</button>
                <button type="button" class="btn btn-outline modal-close">Cancelar</button>
            </div>
        </form>
    </div>
</div>

<?php
// Funções helper
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

function getPresentesHojeUnidade($unidade_id) {
    $db = getDB();
    $stmt = $db->prepare("
        SELECT COUNT(*) as total 
        FROM presenca p 
        JOIN membros m ON p.membro_id = m.id 
        WHERE p.data = CURDATE() AND p.presente = 1 AND m.unidade_id = ?
    ");
    $stmt->execute([$unidade_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result['total'];
}

function getPontosTotalUnidade($unidade_id) {
    $db = getDB();
    $stmt = $db->prepare("
        SELECT COALESCE(SUM(mp.pontos), 0) as total 
        FROM membros_pontos mp 
        JOIN membros m ON mp.membro_id = m.id 
        WHERE m.unidade_id = ?
    ");
    $stmt->execute([$unidade_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result['total'];
}

function getPresencaMediaUnidade($unidade_id) {
    $db = getDB();
    $stmt = $db->prepare("
        SELECT AVG(p.presente) * 100 as media 
        FROM presenca p 
        JOIN membros m ON p.membro_id = m.id 
        WHERE p.data >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) AND m.unidade_id = ?
    ");
    $stmt->execute([$unidade_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return round($result['media'], 1);
}

function getAtividadesMes($unidade_id) {
    $db = getDB();
    $stmt = $db->prepare("
        SELECT COUNT(*) as total 
        FROM atividades 
        WHERE unidade_id = ? AND MONTH(data) = MONTH(CURDATE()) AND YEAR(data) = YEAR(CURDATE())
    ");
    $stmt->execute([$unidade_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result['total'];
}

function getEspecialidadesConcluidas($unidade_id) {
    $db = getDB();
    $stmt = $db->prepare("
        SELECT COUNT(*) as total 
        FROM membros_especialidades me 
        JOIN membros m ON me.membro_id = m.id 
        WHERE m.unidade_id = ? AND me.status = 'concluida'
    ");
    $stmt->execute([$unidade_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result['total'];
}

function getPosicaoRanking($unidade_id) {
    $db = getDB();
    $stmt = $db->query("
        SELECT 
            u.id,
            COALESCE(SUM(mp.pontos), 0) as pontos_total,
            RANK() OVER (ORDER BY COALESCE(SUM(mp.pontos), 0) DESC) as posicao
        FROM unidades u
        LEFT JOIN membros m ON u.id = m.unidade_id AND m.status = 'ativo'
        LEFT JOIN membros_pontos mp ON m.id = mp.membro_id
        WHERE u.status = 'ativo'
        GROUP BY u.id
        ORDER BY pontos_total DESC
    ");
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        if ($row['id'] == $unidade_id) {
            return $row['posicao'];
        }
    }
    
    return 0;
}

function getMembrosUnidade($unidade_id) {
    $db = getDB();
    $stmt = $db->prepare("
        SELECT 
            m.*,
            COALESCE(SUM(mp.pontos), 0) as pontos,
            COALESCE(p.presente, 0) as presente_hoje
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

function getRankingUnidade($unidade_id) {
    $db = getDB();
    $stmt = $db->prepare("
        SELECT 
            m.*,
            COALESCE(SUM(mp.pontos), 0) as pontos,
            (COALESCE(SUM(mp.pontos), 0) / (SELECT MAX(pontos_total) FROM (
                SELECT COALESCE(SUM(pontos), 0) as pontos_total 
                FROM membros_pontos 
                GROUP BY membro_id
            ) as max_pontos) * 100) as percentual
        FROM membros m
        LEFT JOIN membros_pontos mp ON m.id = mp.membro_id
        WHERE m.unidade_id = ? AND m.status = 'ativo'
        GROUP BY m.id
        ORDER BY pontos DESC
        LIMIT 10
    ");
    $stmt->execute([$unidade_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getAtividadesRecentesUnidade($unidade_id) {
    $db = getDB();
    $stmt = $db->prepare("
        SELECT * FROM atividades 
        WHERE unidade_id = ? 
        ORDER BY data DESC 
        LIMIT 10
    ");
    $stmt->execute([$unidade_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getAtividadeIcon($tipo) {
    $icons = [
        'reuniao' => '👥',
        'especialidade' => '🎓',
        'atividade' => '🎯',
        'evento' => '🎉',
        'estudo' => '📚'
    ];
    return $icons[$tipo] ?? '📌';
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
function lancarPontos(membroId) {
    document.getElementById('membro_id').value = membroId;
    openModal('modal-pontos');
}

function verPerfil(membroId) {
    window.location.href = `unidade.php?membro=${membroId}`;
}

function verTodosMembros() {
    window.location.href = 'unidade.php';
}

function registrarAtividade() {
    window.location.href = 'unidade.php#atividades';
}

function atualizarRanking() {
    const periodo = document.getElementById('periodo-ranking').value;
    
    fetch(`../api/pontos.php?action=ranking&unidade_id=<?php echo $unidade_id; ?>&periodo=${periodo}`)
        .then(response => response.json())
        .then(data => {
            renderizarRanking(data);
        });
}

function renderizarRanking(data) {
    const rankingList = document.getElementById('ranking-list');
    rankingList.innerHTML = '';
    
    data.forEach((membro, index) => {
        const item = document.createElement('div');
        item.className = 'ranking-item';
        item.innerHTML = `
            <div class="ranking-posicao">${index + 1}º</div>
            <div class="ranking-avatar">
                <img src="assets/img/default-avatar.png" alt="${membro.nome}">
            </div>
            <div class="ranking-info">
                <div class="ranking-nome">${membro.nome}</div>
                <div class="ranking-progresso">
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: ${membro.percentual}%"></div>
                    </div>
                    <span class="progress-text">${membro.pontos} pts</span>
                </div>
            </div>
            <div class="ranking-trofeu">
                ${index < 3 ? ['🥇', '🥈', '🥉'][index] : ''}
            </div>
        `;
        rankingList.appendChild(item);
    });
}
</script>

<style>
.unidade-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 30px;
    border-radius: 15px;
    margin-bottom: 30px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.unidade-info h2 {
    margin: 0 0 10px 0;
    font-size: 2rem;
}

.unidade-descricao {
    margin: 0;
    opacity: 0.9;
}

.unidade-stats {
    display: flex;
    gap: 30px;
}

.stat-item {
    text-align: center;
}

.stat-number {
    display: block;
    font-size: 2rem;
    font-weight: bold;
}

.stat-label {
    font-size: 0.9rem;
    opacity: 0.8;
}

.membros-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 20px;
}

.membro-card {
    background: white;
    border-radius: 10px;
    padding: 20px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    text-align: center;
}

.membro-avatar {
    position: relative;
    margin-bottom: 15px;
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

.membro-info h3 {
    margin: 0 0 10px 0;
    color: #2c3e50;
}

.membro-detalhes {
    display: flex;
    justify-content: space-around;
    margin-bottom: 15px;
    font-size: 0.9rem;
}

.idade {
    color: #7f8c8d;
}

.pontos {
    color: #3498db;
    font-weight: bold;
}

.membro-acoes {
    display: flex;
    gap: 10px;
    justify-content: center;
}

.ranking-list {
    space-y: 15px;
}

.ranking-item {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 15px;
    background: white;
    border-radius: 10px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}

.ranking-posicao {
    font-size: 1.5rem;
    font-weight: bold;
    color: #3498db;
    min-width: 40px;
}

.ranking-avatar img {
    width: 40px;
    height: 40px;
    border-radius: 50%;
}

.ranking-info {
    flex: 1;
}

.ranking-nome {
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 5px;
}

.ranking-progresso {
    display: flex;
    align-items: center;
    gap: 10px;
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

.progress-text {
    font-size: 0.8rem;
    color: #7f8c8d;
    min-width: 40px;
}

.ranking-trofeu {
    font-size: 1.5rem;
}

.atividades-timeline {
    position: relative;
    padding-left: 30px;
}

.atividades-timeline::before {
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

.participantes-count {
    font-size: 0.8rem;
    color: #3498db;
}
</style>

<?php include '../../includes/footer.php'; ?>
