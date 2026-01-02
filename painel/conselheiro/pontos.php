<?php
session_start();
require_once '../../config/database.php';
require_once '../../config/auth.php';

verificarAutenticacao();
verificarPermissao('conselheiro');

$usuario = getUsuarioLogado();
$unidade_id = $usuario['unidade_id'];

$page_title = 'Pontuação - Meritus';
include '../../includes/header.php';
include '../../includes/sidebar.php';
?>

<div class="main-content">
    <div class="content-header">
        <h1>Gestão de Pontos</h1>
        <div class="header-actions">
            <button class="btn btn-primary" data-modal="modal-pontos">Lançar Pontos</button>
            <button class="btn btn-outline" onclick="exportarPontos()">Exportar</button>
        </div>
    </div>
    
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-number"><?php echo getPontosTotalUnidade($unidade_id); ?></div>
            <div class="stat-label">Pontos Totais da Unidade</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?php echo getPontosMes($unidade_id); ?></div>
            <div class="stat-label">Pontos Este Mês</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?php echo getMediaPontosMembros($unidade_id); ?></div>
            <div class="stat-label">Média por Membro</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?php echo getPosicaoRankingUnidade($unidade_id); ?>º</div>
            <div class="stat-label">Posição no Ranking</div>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Ranking de Pontos</h2>
            <div class="card-actions">
                <select class="form-control" id="periodo-ranking" onchange="atualizarRanking()">
                    <option value="semana">Esta Semana</option>
                    <option value="mes" selected>Este Mês</option>
                    <option value="trimestre">Este Trimestre</option>
                    <option value="ano">Este Ano</option>
                    <option value="total">Total</option>
                </select>
                <select class="form-control" id="tipo-ranking" onchange="atualizarRanking()">
                    <option value="membros">Membros</option>
                    <option value="unidades">Unidades</option>
                </select>
            </div>
        </div>
        <div class="card-body">
            <div id="ranking-container">
                <!-- Ranking será carregado via JavaScript -->
            </div>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Lançamento de Pontos</h2>
            <div class="card-actions">
                <button class="btn btn-primary" data-modal="modal-lote">Lançamento em Lote</button>
            </div>
        </div>
        <div class="card-body">
            <div class="pontos-form-rapido">
                <div class="form-rapido-header">
                    <h3>Lançamento Rápido</h3>
                    <p>Selecione um membro e lance pontos rapidamente</p>
                </div>
                <form id="form-pontos-rapido" class="ajax-form">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="membro_id">Membro</label>
                            <select name="membro_id" id="membro_id" class="form-control" required>
                                <option value="">Selecione...</option>
                                <?php
                                $membros = getMembrosUnidade($unidade_id);
                                foreach ($membros as $membro):
                                ?>
                                <option value="<?php echo $membro['id']; ?>"><?php echo htmlspecialchars($membro['nome']); ?> (<?php echo $membro['pontos']; ?> pts)</option>
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
                    </div>
                    <div class="form-group">
                        <label for="descricao">Descrição</label>
                        <textarea name="descricao" id="descricao" class="form-control" rows="2" placeholder="Motivo do lançamento..." required></textarea>
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">Lançar Pontos</button>
                        <button type="button" class="btn btn-outline" onclick="limparForm()">Limpar</button>
                    </div>
                </form>
            </div>
            
            <div class="pontos-recentes">
                <h4>Lançamentos Recentes</h4>
                <div class="pontos-lista" id="pontos-recentes">
                    <?php
                    $pontos_recentes = getPontosRecentesUnidade($unidade_id);
                    foreach ($pontos_recentes as $ponto):
                    ?>
                    <div class="ponto-item">
                        <div class="ponto-valor <?php echo $ponto['pontos'] > 0 ? 'positivo' : 'negativo'; ?>">
                            <?php echo $ponto['pontos'] > 0 ? '+' : ''; ?><?php echo $ponto['pontos']; ?>
                        </div>
                        <div class="ponto-info">
                            <div class="ponto-membro"><?php echo htmlspecialchars($ponto['membro_nome']); ?></div>
                            <div class="ponto-descricao"><?php echo htmlspecialchars($ponto['descricao']); ?></div>
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
    
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Análise de Pontos</h2>
            <div class="card-actions">
                <select class="form-control" id="periodo-analise" onchange="atualizarAnalise()">
                    <option value="7">Últimos 7 dias</option>
                    <option value="30" selected>Últimos 30 dias</option>
                    <option value="90">Últimos 90 dias</option>
                </select>
            </div>
        </div>
        <div class="card-body">
            <div class="analise-grid">
                <div class="analise-card">
                    <h4>Pontos por Categoria</h4>
                    <div class="grafico-categorias" id="grafico-categorias">
                        <!-- Gráfico será renderizado via JavaScript -->
                    </div>
                </div>
                <div class="analise-card">
                    <h4>Evolução de Pontos</h4>
                    <div class="grafico-evolucao" id="grafico-evolucao">
                        <!-- Gráfico será renderizado via JavaScript -->
                    </div>
                </div>
            </div>
            
            <div class="categorias-detalhe">
                <h4>Detalhes por Categoria</h4>
                <div class="categorias-tabela">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Categoria</th>
                                <th>Total de Pontos</th>
                                <th>Lançamentos</th>
                                <th>Média por Lançamento</th>
                                <th>Top Membro</th>
                            </tr>
                        </thead>
                        <tbody id="categorias-tabela">
                            <!-- Dados serão carregados via JavaScript -->
                        </tbody>
                    </table>
                </div>
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
                <label for="modal-membro-id">Membro</label>
                <select name="membro_id" id="modal-membro-id" class="form-control" required>
                    <option value="">Selecione...</option>
                    <?php
                    $membros = getMembrosUnidade($unidade_id);
                    foreach ($membros as $membro):
                    ?>
                    <option value="<?php echo $membro['id']; ?>"><?php echo htmlspecialchars($membro['nome']); ?> (<?php echo $membro['pontos']; ?> pts)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="modal-categoria">Categoria</label>
                <select name="categoria" id="modal-categoria" class="form-control" required>
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
                <label for="modal-pontos">Pontos</label>
                <input type="number" name="pontos" id="modal-pontos" class="form-control" min="1" max="100" required>
            </div>
            <div class="form-group">
                <label for="modal-descricao">Descrição</label>
                <textarea name="descricao" id="modal-descricao" class="form-control" rows="3" required></textarea>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Lançar Pontos</button>
                <button type="button" class="btn btn-outline modal-close">Cancelar</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Lançamento em Lote -->
<div id="modal-lote" class="modal">
    <div class="modal-content modal-large">
        <div class="modal-header">
            <h3>Lançamento em Lote</h3>
            <button class="modal-close">&times;</button>
        </div>
        <form action="../api/pontos.php" method="POST" class="ajax-form">
            <input type="hidden" name="action" value="lote">
            <div class="form-group">
                <label for="lote-categoria">Categoria</label>
                <select name="categoria" id="lote-categoria" class="form-control" required>
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
                <label for="lote-descricao">Descrição</label>
                <textarea name="descricao" id="lote-descricao" class="form-control" rows="2" required></textarea>
            </div>
            <div class="form-group">
                <label>Selecione os Membros</label>
                <div class="membros-selecao">
                    <?php
                    $membros = getMembrosUnidade($unidade_id);
                    foreach ($membros as $membro):
                    ?>
                    <div class="membro-checkbox">
                        <input type="checkbox" name="membros[]" value="<?php echo $membro['id']; ?>" id="membro-<?php echo $membro['id']; ?>">
                        <label for="membro-<?php echo $membro['id']; ?>">
                            <img src="assets/img/default-avatar.png" alt="<?php echo htmlspecialchars($membro['nome']); ?>">
                            <span><?php echo htmlspecialchars($membro['nome']); ?></span>
                            <small><?php echo $membro['pontos']; ?> pts</small>
                        </label>
                        <input type="number" name="pontos_<?php echo $membro['id']; ?>" class="pontos-individuais" placeholder="Pts" min="1" max="100">
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Lançar em Lote</button>
                <button type="button" class="btn btn-outline modal-close">Cancelar</button>
            </div>
        </form>
    </div>
</div>

<?php
// Funções helper
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

function getPontosMes($unidade_id) {
    $db = getDB();
    $stmt = $db->prepare("
        SELECT COALESCE(SUM(mp.pontos), 0) as total 
        FROM membros_pontos mp 
        JOIN membros m ON mp.membro_id = m.id 
        WHERE m.unidade_id = ? AND MONTH(mp.data) = MONTH(CURDATE()) AND YEAR(mp.data) = YEAR(CURDATE())
    ");
    $stmt->execute([$unidade_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result['total'];
}

function getMediaPontosMembros($unidade_id) {
    $db = getDB();
    $stmt = $db->prepare("
        SELECT AVG(pontos_totais) as media 
        FROM (
            SELECT COALESCE(SUM(mp.pontos), 0) as pontos_totais 
            FROM membros m 
            LEFT JOIN membros_pontos mp ON m.id = mp.membro_id 
            WHERE m.unidade_id = ? AND m.status = 'ativo'
            GROUP BY m.id
        ) as pontos_membros
    ");
    $stmt->execute([$unidade_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return round($result['media'], 1);
}

function getPosicaoRankingUnidade($unidade_id) {
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
            COALESCE(SUM(mp.pontos), 0) as pontos
        FROM membros m
        LEFT JOIN membros_pontos mp ON m.id = mp.membro_id
        WHERE m.unidade_id = ? AND m.status = 'ativo'
        GROUP BY m.id
        ORDER BY pontos DESC, m.nome
    ");
    $stmt->execute([$unidade_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getPontosRecentesUnidade($unidade_id) {
    $db = getDB();
    $stmt = $db->prepare("
        SELECT mp.*, m.nome as membro_nome 
        FROM membros_pontos mp 
        JOIN membros m ON mp.membro_id = m.id 
        WHERE m.unidade_id = ? 
        ORDER BY mp.data DESC 
        LIMIT 20
    ");
    $stmt->execute([$unidade_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function formatDateTime($datetime) {
    return date('d/m/Y H:i', strtotime($datetime));
}
?>

<script>
function atualizarRanking() {
    const periodo = document.getElementById('periodo-ranking').value;
    const tipo = document.getElementById('tipo-ranking').value;
    
    fetch(`../api/pontos.php?action=ranking&unidade_id=<?php echo $unidade_id; ?>&periodo=${periodo}&tipo=${tipo}`)
        .then(response => response.json())
        .then(data => {
            renderizarRanking(data, tipo);
        });
}

function renderizarRanking(data, tipo) {
    const container = document.getElementById('ranking-container');
    
    if (tipo === 'membros') {
        container.innerHTML = `
            <div class="ranking-membros">
                ${data.map((membro, index) => `
                    <div class="ranking-item">
                        <div class="ranking-posicao">${index + 1}º</div>
                        <div class="ranking-avatar">
                            <img src="assets/img/default-avatar.png" alt="${membro.nome}">
                        </div>
                        <div class="ranking-info">
                            <div class="ranking-nome">${membro.nome}</div>
                            <div class="ranking-progresso">
                                <div class="progress-bar">
                                    <div class="progress-fill" style="width: ${membro.percentual || 0}%"></div>
                                </div>
                                <span class="progress-text">${membro.pontos} pts</span>
                            </div>
                        </div>
                        <div class="ranking-trofeu">
                            ${index < 3 ? ['🥇', '🥈', '🥉'][index] : ''}
                        </div>
                    </div>
                `).join('')}
            </div>
        `;
    } else {
        container.innerHTML = `
            <div class="ranking-unidades">
                ${data.map((unidade, index) => `
                    <div class="ranking-item">
                        <div class="ranking-posicao">${index + 1}º</div>
                        <div class="ranking-info">
                            <div class="ranking-nome">${unidade.nome}</div>
                            <div class="ranking-progresso">
                                <div class="progress-bar">
                                    <div class="progress-fill" style="width: ${unidade.percentual || 0}%"></div>
                                </div>
                                <span class="progress-text">${unidade.pontos} pts</span>
                            </div>
                        </div>
                        <div class="ranking-trofeu">
                            ${index < 3 ? ['🥇', '🥈', '🥉'][index] : ''}
                        </div>
                    </div>
                `).join('')}
            </div>
        `;
    }
}

function atualizarAnalise() {
    const periodo = document.getElementById('periodo-analise').value;
    
    fetch(`../api/pontos.php?action=analise&unidade_id=<?php echo $unidade_id; ?>&periodo=${periodo}`)
        .then(response => response.json())
        .then(data => {
            renderizarGraficos(data);
            renderizarTabelaCategorias(data.categorias);
        });
}

function renderizarGraficos(data) {
    // Renderizar gráfico de categorias
    const categoriasContainer = document.getElementById('grafico-categorias');
    categoriasContainer.innerHTML = `
        <div class="grafico-barras">
            ${data.categorias.map(cat => `
                <div class="bar-item">
                    <div class="bar-label">${cat.nome}</div>
                    <div class="bar-container">
                        <div class="bar-fill" style="width: ${cat.percentual}%; background: ${cat.cor}"></div>
                    </div>
                    <div class="bar-value">${cat.total} pts</div>
                </div>
            `).join('')}
        </div>
    `;
    
    // Renderizar gráfico de evolução
    const evolucaoContainer = document.getElementById('grafico-evolucao');
    evolucaoContainer.innerHTML = `
        <div class="grafico-linha">
            ${data.evolucao.map(ponto => `
                <div class="line-point" style="left: ${ponto.percentual}%; bottom: ${ponto.altura}%">
                    <div class="point-value">${ponto.total}</div>
                    <div class="point-label">${ponto.data}</div>
                </div>
            `).join('')}
        </div>
    `;
}

function renderizarTabelaCategorias(categorias) {
    const tbody = document.getElementById('categorias-tabela');
    tbody.innerHTML = categorias.map(cat => `
        <tr>
            <td><span class="categoria-badge badge-${cat.nome}">${cat.nome}</span></td>
            <td>${cat.total} pts</td>
            <td>${cat.lancamentos}</td>
            <td>${cat.media} pts</td>
            <td>${cat.top_membro}</td>
        </tr>
    `).join('');
}

function limparForm() {
    document.getElementById('form-pontos-rapido').reset();
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

function exportarPontos() {
    const periodo = document.getElementById('periodo-ranking').value;
    window.location.href = `../api/pontos.php?action=exportar&unidade_id=<?php echo $unidade_id; ?>&periodo=${periodo}`;
}

// Carregar dados ao iniciar
document.addEventListener('DOMContentLoaded', function() {
    atualizarRanking();
    atualizarAnalise();
});
</script>

<style>
.pontos-form-rapido {
    background: #f8f9fa;
    padding: 25px;
    border-radius: 10px;
    margin-bottom: 30px;
}

.form-rapido-header h3 {
    margin: 0 0 10px 0;
    color: #2c3e50;
}

.form-rapido-header p {
    margin: 0 0 20px 0;
    color: #7f8c8d;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr 150px;
    gap: 15px;
    margin-bottom: 20px;
}

.pontos-recentes h4 {
    margin: 0 0 20px 0;
    color: #2c3e50;
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
    background: white;
    border-radius: 10px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
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

.ponto-membro {
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 5px;
}

.ponto-descricao {
    color: #5d6d7e;
    margin-bottom: 8px;
}

.ponto-meta {
    display: flex;
    align-items: center;
    gap: 10px;
}

.categoria-badge {
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 0.7rem;
    font-weight: 600;
    text-transform: uppercase;
}

.data-ponto {
    font-size: 0.8rem;
    color: #7f8c8d;
}

.ponto-acoes {
    display: flex;
    gap: 5px;
}

.analise-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 30px;
    margin-bottom: 30px;
}

.analise-card {
    background: white;
    border-radius: 10px;
    padding: 20px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.analise-card h4 {
    margin: 0 0 20px 0;
    color: #2c3e50;
}

.grafico-barras {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.bar-item {
    display: flex;
    align-items: center;
    gap: 10px;
}

.bar-label {
    min-width: 100px;
    font-size: 0.9rem;
    color: #5d6d7e;
}

.bar-container {
    flex: 1;
    height: 20px;
    background: #ecf0f1;
    border-radius: 10px;
    overflow: hidden;
}

.bar-fill {
    height: 100%;
    transition: width 0.3s ease;
}

.bar-value {
    min-width: 50px;
    text-align: right;
    font-weight: 600;
    color: #2c3e50;
}

.grafico-linha {
    position: relative;
    height: 200px;
    background: linear-gradient(to right, #ecf0f1 0%, #ecf0f1 100%);
    border-radius: 10px;
}

.line-point {
    position: absolute;
    width: 8px;
    height: 8px;
    background: #3498db;
    border-radius: 50%;
    transform: translateX(-50%);
}

.point-value {
    position: absolute;
    top: -20px;
    left: 50%;
    transform: translateX(-50%);
    font-size: 0.8rem;
    font-weight: 600;
    color: #2c3e50;
}

.point-label {
    position: absolute;
    bottom: -20px;
    left: 50%;
    transform: translateX(-50%);
    font-size: 0.7rem;
    color: #7f8c8d;
}

.categorias-detalhe h4 {
    margin: 0 0 20px 0;
    color: #2c3e50;
}

.membros-selecao {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 15px;
    max-height: 300px;
    overflow-y: auto;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 10px;
}

.membro-checkbox {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px;
    background: white;
    border-radius: 8px;
}

.membro-checkbox input[type="checkbox"] {
    width: 18px;
    height: 18px;
}

.membro-checkbox label {
    display: flex;
    align-items: center;
    gap: 8px;
    flex: 1;
    cursor: pointer;
}

.membro-checkbox img {
    width: 25px;
    height: 25px;
    border-radius: 50%;
}

.membro-checkbox small {
    color: #7f8c8d;
    font-size: 0.8rem;
}

.pontos-individuais {
    width: 60px;
    padding: 5px;
    border: 1px solid #ddd;
    border-radius: 4px;
    text-align: center;
}

.modal-large {
    max-width: 800px;
}

.ranking-membros,
.ranking-unidades {
    display: grid;
    gap: 15px;
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
</style>

<?php include '../../includes/footer.php'; ?>
