<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

require_once '../config/database.php';
require_once '../config/auth.php';

session_start();

// Verificar autenticação para APIs que requerem login
if (!isset($_SESSION['usuario_id']) && $_SERVER['REQUEST_METHOD'] !== 'OPTIONS') {
    http_response_code(401);
    echo json_encode(['error' => 'Não autorizado']);
    exit;
}

$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {
    case 'carregar':
        carregarPresenca();
        break;
    case 'salvar':
        salvarPresenca();
        break;
    case 'toggle':
        togglePresenca();
        break;
    case 'stats':
        getStats();
        break;
    case 'historico':
        getHistorico();
        break;
    case 'ranking':
        getRanking();
        break;
    case 'exportar':
        exportarPresenca();
        break;
    default:
        http_response_code(400);
        echo json_encode(['error' => 'Ação inválida']);
        break;
}

function carregarPresenca() {
    $data = $_GET['data'] ?? date('Y-m-d');
    $unidade_id = $_GET['unidade_id'] ?? '';
    
    $db = getDB();
    
    $sql = "
        SELECT 
            m.id, m.nome, m.data_nascimento,
            u.nome as unidade,
            COALESCE(p.presente, 0) as presente
        FROM membros m
        JOIN unidades u ON m.unidade_id = u.id
        LEFT JOIN presenca p ON m.id = p.membro_id AND p.data = ?
        WHERE m.status = 'ativo'
    ";
    
    $params = [$data];
    
    if ($unidade_id) {
        $sql .= " AND m.unidade_id = ?";
        $params[] = $unidade_id;
    }
    
    $sql .= " ORDER BY u.nome, m.nome";
    
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    
    $membros = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Adicionar idade
    foreach ($membros as &$membro) {
        $membro['idade'] = calcularIdade($membro['data_nascimento']);
    }
    
    echo json_encode(['membros' => $membros]);
}

function salvarPresenca() {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $data_presenca = $data['data'] ?? date('Y-m-d');
    $presencas = $data['presencas'] ?? [];
    
    $db = getDB();
    
    try {
        $db->beginTransaction();
        
        foreach ($presencas as $membro_id => $presente) {
            // Verificar se já existe registro
            $stmt = $db->prepare("
                SELECT id FROM presenca 
                WHERE membro_id = ? AND data = ?
            ");
            $stmt->execute([$membro_id, $data_presenca]);
            
            if ($stmt->fetch()) {
                // Atualizar
                $stmt = $db->prepare("
                    UPDATE presenca 
                    SET presente = ?, usuario_registro_id = ?, data_registro = NOW()
                    WHERE membro_id = ? AND data = ?
                ");
                $stmt->execute([$presente, $_SESSION['usuario_id'], $membro_id, $data_presenca]);
            } else {
                // Inserir
                $stmt = $db->prepare("
                    INSERT INTO presenca (membro_id, data, presente, usuario_registro_id, data_registro)
                    VALUES (?, ?, ?, ?, NOW())
                ");
                $stmt->execute([$membro_id, $data_presenca, $presente, $_SESSION['usuario_id']]);
            }
        }
        
        $db->commit();
        
        echo json_encode(['success' => true, 'message' => 'Presença salva com sucesso']);
        
    } catch (Exception $e) {
        $db->rollBack();
        http_response_code(500);
        echo json_encode(['error' => 'Erro ao salvar presença: ' . $e->getMessage()]);
    }
}

function togglePresenca() {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $membro_id = $data['membro_id'] ?? 0;
    $presente = $data['presente'] ?? false;
    $data_presenca = $data['data'] ?? date('Y-m-d');
    
    $db = getDB();
    
    try {
        $stmt = $db->prepare("
            INSERT INTO presenca (membro_id, data, presente, usuario_registro_id, data_registro)
            VALUES (?, ?, ?, ?, NOW())
            ON DUPLICATE KEY UPDATE 
            presente = ?, usuario_registro_id = ?, data_registro = NOW()
        ");
        
        $stmt->execute([$membro_id, $data_presenca, $presente, $_SESSION['usuario_id'], $presente, $_SESSION['usuario_id']]);
        
        echo json_encode(['success' => true, 'message' => 'Presença atualizada']);
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Erro ao atualizar presença: ' . $e->getMessage()]);
    }
}

function getStats() {
    $unidade_id = $_GET['unidade_id'] ?? '';
    
    $db = getDB();
    
    $sql = "
        SELECT 
            COUNT(*) as total_membros,
            COUNT(CASE WHEN presente = 1 THEN 1 END) as presentes,
            COUNT(CASE WHEN presente = 0 THEN 1 END) as ausentes,
            ROUND(AVG(presente) * 100, 1) as percentual_presenca
        FROM membros m
        LEFT JOIN presenca p ON m.id = p.membro_id AND p.data = CURDATE()
        WHERE m.status = 'ativo'
    ";
    
    $params = [];
    
    if ($unidade_id) {
        $sql .= " AND m.unidade_id = ?";
        $params[] = $unidade_id;
    }
    
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo json_encode($stats);
}

function getHistorico() {
    $periodo = $_GET['periodo'] ?? '7';
    $unidade_id = $_GET['unidade_id'] ?? '';
    
    $intervalo = '';
    switch ($periodo) {
        case '7':
            $intervalo = 'INTERVAL 7 DAY';
            break;
        case '30':
            $intervalo = 'INTERVAL 30 DAY';
            break;
        case '90':
            $intervalo = 'INTERVAL 90 DAY';
            break;
    }
    
    $db = getDB();
    
    $sql = "
        SELECT 
            data,
            COUNT(*) as total,
            COUNT(CASE WHEN presente = 1 THEN 1 END) as presentes,
            COUNT(CASE WHEN presente = 0 THEN 1 END) as ausentes,
            ROUND(AVG(presente) * 100, 1) as percentual
        FROM presenca p
        JOIN membros m ON p.membro_id = m.id
        WHERE p.data >= DATE_SUB(CURDATE(), $intervalo)
    ";
    
    $params = [];
    
    if ($unidade_id) {
        $sql .= " AND m.unidade_id = ?";
        $params[] = $unidade_id;
    }
    
    $sql .= " GROUP BY p.data ORDER BY p.data DESC";
    
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    
    $historico = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($historico);
}

function getRanking() {
    $periodo = $_GET['periodo'] ?? 'mes';
    $unidade_id = $_GET['unidade_id'] ?? '';
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
                COUNT(CASE WHEN p.presente = 1 THEN 1 END) as presencas,
                COUNT(*) as total_aulas,
                ROUND(AVG(presente) * 100, 1) as percentual
            FROM membros m
            JOIN unidades u ON m.unidade_id = u.id
            LEFT JOIN presenca p ON m.id = p.membro_id AND p.data >= DATE_SUB(NOW(), $intervalo)
            WHERE m.status = 'ativo'
        ";
        
        $params = [];
        
        if ($unidade_id) {
            $sql .= " AND m.unidade_id = ?";
            $params[] = $unidade_id;
        }
        
        $sql .= " GROUP BY m.id ORDER BY percentual DESC, presencas DESC LIMIT 20";
        
    } else {
        $sql = "
            SELECT 
                u.id, u.nome,
                COUNT(CASE WHEN p.presente = 1 THEN 1 END) as presencas,
                COUNT(*) as total_aulas,
                ROUND(AVG(presente) * 100, 1) as percentual
            FROM unidades u
            LEFT JOIN membros m ON u.id = m.unidade_id AND m.status = 'ativo'
            LEFT JOIN presenca p ON m.id = p.membro_id AND p.data >= DATE_SUB(NOW(), $intervalo)
            WHERE u.status = 'ativo'
        ";
        
        $params = [];
        
        if ($unidade_id) {
            $sql .= " AND u.id = ?";
            $params[] = $unidade_id;
        }
        
        $sql .= " GROUP BY u.id ORDER BY percentual DESC, presencas DESC";
    }
    
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    
    $ranking = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($ranking);
}

function exportarPresenca() {
    $data = $_GET['data'] ?? date('Y-m-d');
    $unidade_id = $_GET['unidade_id'] ?? '';
    
    $db = getDB();
    
    $sql = "
        SELECT 
            m.nome,
            u.nome as unidade,
            p.presente,
            p.data_registro
        FROM membros m
        JOIN unidades u ON m.unidade_id = u.id
        LEFT JOIN presenca p ON m.id = p.membro_id AND p.data = ?
        WHERE m.status = 'ativo'
    ";
    
    $params = [$data];
    
    if ($unidade_id) {
        $sql .= " AND m.unidade_id = ?";
        $params[] = $unidade_id;
    }
    
    $sql .= " ORDER BY u.nome, m.nome";
    
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    
    $presencas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Gerar CSV
    $filename = "presenca_" . str_replace('-', '', $data) . ".csv";
    
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    $output = fopen('php://output', 'w');
    
    // Cabeçalho
    fputcsv($output, ['Nome', 'Unidade', 'Presente', 'Data Registro']);
    
    // Dados
    foreach ($presencas as $presenca) {
        fputcsv($output, [
            $presenca['nome'],
            $presenca['unidade'],
            $presenca['presente'] ? 'Sim' : 'Não',
            $presenca['data_registro']
        ]);
    }
    
    fclose($output);
    exit;
}

function calcularIdade($data_nascimento) {
    $data = new DateTime($data_nascimento);
    $hoje = new DateTime();
    return $hoje->diff($data)->y;
}
?>
