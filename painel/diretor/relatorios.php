<?php
session_start();
require_once '../../config/database.php';
require_once '../../config/auth.php';

verificarAutenticacao();
verificarPermissao('diretor');

$page_title = 'Relatórios - Meritus';
include '../../includes/header.php';
include '../../includes/sidebar.php';
?>

<div class="main-content">
    <div class="content-header">
        <h1>Relatórios</h1>
        <div class="header-actions">
            <button class="btn btn-primary" data-modal="modal-relatorio">Gerar Relatório</button>
            <button class="btn btn-outline" onclick="exportarDados()">Exportar Dados</button>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Relatórios Disponíveis</h2>
        </div>
        <div class="card-body">
            <div class="relatorios-grid">
                <div class="relatorio-card" onclick="gerarRelatorio('geral')">
                    <div class="relatorio-icon">📊</div>
                    <h3>Relatório Geral</h3>
                    <p>Visão completa do sistema com todos os dados principais</p>
                    <div class="relatorio-stats">
                        <span>Última geração: <?php echo getLastReportDate('geral'); ?></span>
                    </div>
                </div>
                
                <div class="relatorio-card" onclick="gerarRelatorio('presenca')">
                    <div class="relatorio-icon">✅</div>
                    <h3>Relatório de Presença</h3>
                    <p>Estatísticas de presença por unidade e período</p>
                    <div class="relatorio-stats">
                        <span>Última geração: <?php echo getLastReportDate('presenca'); ?></span>
                    </div>
                </div>
                
                <div class="relatorio-card" onclick="gerarRelatorio('pontos')">
                    <div class="relatorio-icon">⭐</div>
                    <h3>Relatório de Pontos</h3>
                    <p>Ranking e pontuação dos membros e unidades</p>
                    <div class="relatorio-stats">
                        <span>Última geração: <?php echo getLastReportDate('pontos'); ?></span>
                    </div>
                </div>
                
                <div class="relatorio-card" onclick="gerarRelatorio('membros')">
                    <div class="relatorio-icon">👥</div>
                    <h3>Relatório de Membros</h3>
                    <p>Cadastro, status e informações dos membros</p>
                    <div class="relatorio-stats">
                        <span>Última geração: <?php echo getLastReportDate('membros'); ?></span>
                    </div>
                </div>
                
                <div class="relatorio-card" onclick="gerarRelatorio('atividades')">
                    <div class="relatorio-icon">🎯</div>
                    <h3>Relatório de Atividades</h3>
                    <p>Registro de todas as atividades realizadas</p>
                    <div class="relatorio-stats">
                        <span>Última geração: <?php echo getLastReportDate('atividades'); ?></span>
                    </div>
                </div>
                
                <div class="relatorio-card" onclick="gerarRelatorio('financeiro')">
                    <div class="relatorio-icon">💰</div>
                    <h3>Relatório Financeiro</h3>
                    <p>Movimentações financeiras e balanço</p>
                    <div class="relatorio-stats">
                        <span>Última geração: <?php echo getLastReportDate('financeiro'); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Relatórios Recentes</h2>
        </div>
        <div class="card-body">
            <table class="table">
                <thead>
                    <tr>
                        <th>Tipo</th>
                        <th>Período</th>
                        <th>Data Geração</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $relatorios = getRelatoriosRecentes();
                    foreach ($relatorios as $relatorio):
                    ?>
                    <tr>
                        <td>
                            <span class="badge badge-info"><?php echo htmlspecialchars(ucfirst($relatorio['tipo'])); ?></span>
                        </td>
                        <td><?php echo formatDate($relatorio['data_inicio']) . ' a ' . formatDate($relatorio['data_fim']); ?></td>
                        <td><?php echo formatDateTime($relatorio['data_geracao']); ?></td>
                        <td>
                            <span class="badge badge-<?php echo $relatorio['status']; ?>">
                                <?php echo htmlspecialchars(ucfirst($relatorio['status'])); ?>
                            </span>
                        </td>
                        <td>
                            <div class="action-buttons">
                                <button class="btn btn-sm btn-outline" onclick="visualizarRelatorio(<?php echo $relatorio['id']; ?>)">Visualizar</button>
                                <button class="btn btn-sm btn-outline" onclick="baixarRelatorio(<?php echo $relatorio['id']; ?>)">Baixar</button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Gerar Relatório -->
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
                    <option value="atividades">Relatório de Atividades</option>
                    <option value="financeiro">Relatório Financeiro</option>
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
            <div class="form-group">
                <label for="formato">Formato</label>
                <select name="formato" id="formato" class="form-control" required>
                    <option value="pdf">PDF</option>
                    <option value="excel">Excel</option>
                    <option value="csv">CSV</option>
                </select>
            </div>
            <div class="form-group">
                <label>
                    <input type="checkbox" name="enviar_email"> Enviar por email
                </label>
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
function getLastReportDate($tipo) {
    $db = getDB();
    $stmt = $db->prepare("SELECT data_geracao FROM relatorios WHERE tipo = ? ORDER BY data_geracao DESC LIMIT 1");
    $stmt->execute([$tipo]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result ? formatDateTime($result['data_geracao']) : 'Nunca gerado';
}

function getRelatoriosRecentes() {
    $db = getDB();
    $stmt = $db->query("
        SELECT * FROM relatorios 
        ORDER BY data_geracao DESC 
        LIMIT 20
    ");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function formatDate($date) {
    return date('d/m/Y', strtotime($date));
}

function formatDateTime($datetime) {
    return date('d/m/Y H:i', strtotime($datetime));
}
?>

<script>
function gerarRelatorio(tipo) {
    document.getElementById('tipo_relatorio').value = tipo;
    openModal('modal-relatorio');
}

function visualizarRelatorio(id) {
    window.open(`../api/relatorios.php?action=visualizar&id=${id}`, '_blank');
}

function baixarRelatorio(id) {
    window.location.href = `../api/relatorios.php?action=baixar&id=${id}`;
}

function exportarDados() {
    if (confirm('Deseja exportar todos os dados do sistema? Isso pode levar alguns minutos.')) {
        window.location.href = '../api/relatorios.php?action=exportar_tudo';
    }
}
</script>

<style>
.relatorios-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.relatorio-card {
    background: white;
    border-radius: 10px;
    padding: 25px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    cursor: pointer;
    transition: all 0.3s ease;
    border-left: 4px solid #3498db;
}

.relatorio-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.15);
}

.relatorio-icon {
    font-size: 2.5rem;
    margin-bottom: 15px;
}

.relatorio-card h3 {
    margin: 0 0 10px 0;
    color: #2c3e50;
}

.relatorio-card p {
    color: #7f8c8d;
    margin-bottom: 15px;
    font-size: 0.9rem;
}

.relatorio-stats {
    font-size: 0.8rem;
    color: #95a5a6;
}

.action-buttons {
    display: flex;
    gap: 5px;
}
</style>

<?php include '../../includes/footer.php'; ?>
