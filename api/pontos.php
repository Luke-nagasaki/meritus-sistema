<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

require_once '../config/database.php';
require_once '../config/auth.php';

session_start();

if (!isset($_SESSION['usuario_id']) && $_SERVER['REQUEST_METHOD'] !== 'OPTIONS') {
    http_response_code(401);
    echo json_encode(['error' => 'Não autorizado']);
    exit;
}

$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {
    case 'lancar':
        lancarPontos();
        break;
    case 'excluir':
        excluirPontos();
        break;
    case 'ranking':
        getRanking();
        break;
    case 'analise':
        getAnalise();
        break;
    case 'historico':
        getHistorico();
        break;
    case 'exportar':
        exportarPontos();
        break;
    default:
        // Se não for uma ação específica, trata como lançamento de pontos
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            lancarPontos();
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'Ação inválida']);
        }
        break;
}

function lancarPontos() {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $membro_id = $data['membro_id'] ?? 0;
    $categoria = $data['categoria'] ?? '';
    $pontos = $data['pontos'] ?? 0;
    $descricao = $data['descricao'] ?? '';
    
    if (!$membro_id || !$categoria || !$pontos || !$descricao) {
        http_response_code(400);
        echo json_encode(['error' => 'Dados incompletos']);
        return;
    }
    
    $db = getDB();
    
    try {
        $db->beginTransaction();
        
        // Inserir lançamento de pontos
        $stmt = $db->prepare("
            INSERT INTO membros_pontos (membro_id, categoria, pontos, descricao, usuario_lancamento_id, data)
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        
        $stmt->execute([$membro_id, $categoria, $pontos, $descricao, $_SESSION['usuario_id']]);
        
        // Atualizar total de pontos do membro
        $stmt = $db->prepare("
            UPDATE membros m 
            SET pontos_total = (
                SELECT COALESCE(SUM(pontos), 0) 
                FROM membros_pontos 
                WHERE membro_id = ?
            )
            WHERE id = ?
        ");
        
        $stmt->execute([$membro_id, $membro_id]);
        
        // Registrar log
        $stmt = $db->prepare("
            INSERT INTO logs_sistema (usuario_id, acao, detalhes, ip, data, nivel)
            VALUES (?, 'lancar_pontos', ?, ?, NOW(), 'info')
        ");
        
        $stmt->execute([
            $_SESSION['usuario_id'],
            "Lançou {$pontos} pontos para membro {$membro_id} - {$categoria}: {$descricao}",
            $_SERVER['REMOTE_ADDR']
        ]);
        
        $db->commit();
        
        echo json_encode(['success' => true, 'message' => 'Pontos lançados com sucesso']);
        
    } catch (Exception $e) {
        $db->rollBack();
        http_response_code(500);
        echo json_encode(['error' => 'Erro ao lançar pontos: ' . $e->getMessage()]);
    }
}

function excluirPontos() {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $id = $data['id'] ?? 0;
    
    if (!$id) {
        http_response_code(400);
        echo json_encode(['error' => 'ID não informado']);
        return;
    }
    
    $db = getDB();
    
    try {
        $db->beginTransaction();
        
        // Buscar informações antes de excluir
        $stmt = $db->prepare("SELECT * FROM membros_pontos WHERE id = ?");
        $stmt->execute([$id]);
        $ponto = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$ponto) {
            http_response_code(404);
            echo json_encode(['error' => 'Lançamento não encontrado']);
            return;
        }
        
        // Excluir lançamento
        $stmt = $db->prepare("DELETE FROM membros_pontos WHERE id = ?");
        $stmt->execute([$id]);
        
        // Atualizar total de pontos do membro
        $stmt = $db->prepare("
            UPDATE membros m 
            SET pontos_total = (
                SELECT COALESCE(SUM(pontos), 0) 
                FROM membros_pontos 
                WHERE membro_id = ?
            )
            WHERE id = ?
        ");
        
        $stmt->execute([$ponto['membro_id'], $ponto['membro_id']]);
        
        // Registrar log
        $stmt = $db->prepare("
            INSERT INTO logs_sistema (usuario_id, acao, detalhes, ip, data, nivel)
            VALUES (?, 'excluir_pontos', ?, ?, NOW(), 'warning')
        ");
        
        $stmt->execute([
            $_SESSION['usuario_id'],
            "Excluiu lançamento de pontos ID {$id} ({$ponto['pontos']} pts)",
            $_SERVER['REMOTE_ADDR']
        ]);
        
        $db->commit();
        
        echo json_encode(['success' => true, 'message' => 'Lançamento excluído com sucesso']);
        
    } catch (Exception $e) {
        $db->rollBack();
        http_response_code(500);
        echo json_encode(['error' => 'Erro ao excluir pontos: ' . $e->getMessage()]);
    }
}

function getRanking() {
    $unidade_id = $_GET['unidade_id'] ?? '';
    $periodo = $_GET['periodo'] ?? 'mes';
    $tipo = $_GET['tipo'] ?? 'membros';
    
    $intervalo = '';
    switch ($periodo) {
        case 'semana':
            $intervalo = 'INTERVAL 1 WEEK';
            break;
        case 'mes':
            $intervalo = 'INTERVAL 1 MONTH';
            break;
        case 'trimestre':
            $intervalo = 'INTERVAL 3 MONTH';
            break;
        case 'ano':
            $intervalo = 'INTERVAL 1 YEAR';
            break;
        case 'total':
            $intervalo = 'INTERVAL 100 YEAR';
            break;
    }
    
    $db = getDB();
    
    if ($tipo === 'membros') {
        $sql = "
            SELECT 
                m.id, m.nome,
                u.nome as unidade,
                COALESCE(SUM(mp.pontos), 0) as pontos,
                (SELECT MAX(pontos_totais) FROM (
                    SELECT COALESCE(SUM(pontos), 0) as pontos_totais 
                    FROM membros_pontos 
                    WHERE data >= DATE_SUB(NOW(), $intervalo)
                    GROUP BY membro_id
                ) as max_pontos) * 100 / NULLIF(SUM(mp.pontos), 0) as percentual
            FROM membros m
            JOIN unidades u ON m.unidade_id = u.id
            LEFT JOIN membros_pontos mp ON m.id = mp.membro_id 
                AND mp.data >= DATE_SUB(NOW(), $intervalo)
            WHERE m.status = 'ativo'
        ";
        
        $params = [];
        
        if ($unidade_id) {
            $sql .= " AND m.unidade_id = ?";
            $params[] = $unidade_id;
        }
        
        $sql .= " GROUP BY m.id ORDER BY pontos DESC";
        
    } else {
        $sql = "
            SELECT 
                u.id, u.nome,
                COALESCE(SUM(mp.pontos), 0) as pontos,
                (SELECT MAX(pontos_totais) FROM (
                    SELECT COALESCE(SUM(pontos), 0) as pontos_totais 
                    FROM membros_pontos mp
                    JOIN membros m ON mp.membro_id = m.id
                    WHERE mp.data >= DATE_SUB(NOW(), $intervalo)
                    GROUP BY m.unidade_id
                ) as max_pontos) * 100 / NULLIF(SUM(mp.pontos), 0) as percentual
            FROM unidades u
            LEFT JOIN membros m ON u.id = m.unidade_id AND m.status = 'ativo'
            LEFT JOIN membros_pontos mp ON m.id = mp.membro_id 
                AND mp.data >= DATE_SUB(NOW(), $intervalo)
            WHERE u.status = 'ativo'
        ";
        
        $params = [];
        
        if ($unidade_id) {
            $sql .= " AND u.id = ?";
            $params[] = $unidade_id;
        }
        
        $sql .= " GROUP BY u.id ORDER BY pontos DESC";
    }
    
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    
    $ranking = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Calcular percentual corretamente
    $maxPontos = 0;
    foreach ($ranking as $item) {
        if ($item['pontos'] > $maxPontos) {
            $maxPontos = $item['pontos'];
        }
    }
    
    foreach ($ranking as &$item) {
        $item['percentual'] = $maxPontos > 0 ? round(($item['pontos'] / $maxPontos) * 100, 1) : 0;
    }
    
    echo json_encode($ranking);
}

function getAnalise() {
    $unidade_id = $_GET['unidade_id'] ?? '';
    $periodo = $_GET['periodo'] ?? '30';
    
    $intervalo = "INTERVAL {$periodo} DAY";
    
    $db = getDB();
    
    // Análise por categoria
    $sql_categorias = "
        SELECT 
            categoria,
            SUM(pontos) as total,
            COUNT(*) as lancamentos,
            ROUND(AVG(pontos), 1) as media,
            (SELECT m.nome FROM membros m 
             JOIN membros_pontos mp ON m.id = mp.membro_id 
             WHERE mp.categoria = mp_out.categoria 
             AND mp.data >= DATE_SUB(NOW(), $intervalo)
             GROUP BY m.id 
             ORDER BY SUM(mp.pontos) DESC 
             LIMIT 1) as top_membro
        FROM membros_pontos mp_out
        WHERE data >= DATE_SUB(NOW(), $intervalo)
    ";
    
    $params = [];
    
    if ($unidade_id) {
        $sql_categorias .= " AND mp_out.membro_id IN (SELECT id FROM membros WHERE unidade_id = ?)";
        $params[] = $unidade_id;
    }
    
    $sql_categorias .= " GROUP BY categoria ORDER BY total DESC";
    
    $stmt = $db->prepare($sql_categorias);
    $stmt->execute($params);
    $categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Adicionar cores para categorias
    $cores = [
        'presenca' => '#27ae60',
        'participacao' => '#3498db',
        'comportamento' => '#f39c12',
        'especialidade' => '#9b59b6',
        'atividade' => '#e74c3c',
        'lideranca' => '#1abc9c'
    ];
    
    foreach ($categorias as &$cat) {
        $cat['cor'] = $cores[$cat['categoria']] ?? '#95a5a6';
        $cat['nome'] = ucfirst($cat['categoria']);
    }
    
    // Evolução de pontos
    $sql_evolucao = "
        SELECT 
            DATE(data) as data,
            SUM(pontos) as total
        FROM membros_pontos
        WHERE data >= DATE_SUB(NOW(), $intervalo)
    ";
    
    $params_evolucao = [];
    
    if ($unidade_id) {
        $sql_evolucao .= " AND membro_id IN (SELECT id FROM membros WHERE unidade_id = ?)";
        $params_evolucao[] = $unidade_id;
    }
    
    $sql_evolucao .= " GROUP BY DATE(data) ORDER BY DATE(data)";
    
    $stmt = $db->prepare($sql_evolucao);
    $stmt->execute($params_evolucao);
    $evolucao = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Preparar dados para gráfico
    $maxPontos = 0;
    foreach ($evolucao as $ponto) {
        if ($ponto['total'] > $maxPontos) {
            $maxPontos = $ponto['total'];
        }
    }
    
    foreach ($evolucao as &$ponto) {
        $ponto['percentual'] = $maxPontos > 0 ? round(($ponto['total'] / $maxPontos) * 100) : 0;
        $ponto['altura'] = $maxPontos > 0 ? round(($ponto['total'] / $maxPontos) * 80) : 0;
        $ponto['data_formatada'] = date('d/m', strtotime($ponto['data']));
    }
    
    echo json_encode([
        'categorias' => $categorias,
        'evolucao' => $evolucao
    ]);
}

function getHistorico() {
    $membro_id = $_GET['membro_id'] ?? '';
    $categoria = $_GET['categoria'] ?? '';
    $data_inicio = $_GET['data_inicio'] ?? '';
    $data_fim = $_GET['data_fim'] ?? '';
    $limite = $_GET['limite'] ?? 50;
    
    $db = getDB();
    
    $sql = "
        SELECT 
            mp.*,
            m.nome as membro_nome
        FROM membros_pontos mp
        JOIN membros m ON mp.membro_id = m.id
        WHERE 1=1
    ";
    
    $params = [];
    
    if ($membro_id) {
        $sql .= " AND mp.membro_id = ?";
        $params[] = $membro_id;
    }
    
    if ($categoria) {
        $sql .= " AND mp.categoria = ?";
        $params[] = $categoria;
    }
    
    if ($data_inicio) {
        $sql .= " AND mp.data >= ?";
        $params[] = $data_inicio;
    }
    
    if ($data_fim) {
        $sql .= " AND mp.data <= ?";
        $params[] = $data_fim;
    }
    
    $sql .= " ORDER BY mp.data DESC LIMIT ?";
    $params[] = $limite;
    
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    
    $historico = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($historico);
}

function exportarPontos() {
    $unidade_id = $_GET['unidade_id'] ?? '';
    $periodo = $_GET['periodo'] ?? 'mes';
    
    $intervalo = '';
    switch ($periodo) {
        case 'semana':
            $intervalo = 'INTERVAL 1 WEEK';
            break;
        case 'mes':
            $intervalo = 'INTERVAL 1 MONTH';
            break;
        case 'trimestre':
            $intervalo = 'INTERVAL 3 MONTH';
            break;
        case 'ano':
            $intervalo = 'INTERVAL 1 YEAR';
            break;
        case 'total':
            $intervalo = 'INTERVAL 100 YEAR';
            break;
    }
    
    $db = getDB();
    
    $sql = "
        SELECT 
            m.nome,
            u.nome as unidade,
            mp.categoria,
            mp.pontos,
            mp.descricao,
            mp.data,
            us.nome as usuario_lancamento
        FROM membros_pontos mp
        JOIN membros m ON mp.membro_id = m.id
        JOIN unidades u ON m.unidade_id = u.id
        LEFT JOIN usuarios us ON mp.usuario_lancamento_id = us.id
        WHERE mp.data >= DATE_SUB(NOW(), $intervalo)
    ";
    
    $params = [];
    
    if ($unidade_id) {
        $sql .= " AND m.unidade_id = ?";
        $params[] = $unidade_id;
    }
    
    $sql .= " ORDER BY mp.data DESC";
    
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    
    $pontos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Gerar CSV
    $filename = "pontos_" . $periodo . "_" . date('Y-m-d') . ".csv";
    
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    $output = fopen('php://output', 'w');
    
    // Cabeçalho
    fputcsv($output, ['Nome', 'Unidade', 'Categoria', 'Pontos', 'Descrição', 'Data', 'Usuário']);
    
    // Dados
    foreach ($pontos as $ponto) {
        fputcsv($output, [
            $ponto['nome'],
            $ponto['unidade'],
            $ponto['categoria'],
            $ponto['pontos'],
            $ponto['descricao'],
            $ponto['data'],
            $ponto['usuario_lancamento']
        ]);
    }
    
    fclose($output);
    exit;
}
?>
