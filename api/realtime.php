<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

require_once '../config/database.php';
require_once '../config/auth.php';

session_start();

if (!isset($_SESSION['usuario_id']) && $_SERVER['REQUEST_METHOD'] !== 'OPTIONS') {
    http_response_code(401);
    echo json_encode(['error' => 'Não autorizado']);
    exit;
}

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'usuarios_online':
        getUsuariosOnline();
        break;
    case 'acessos_recentes':
        getAcessosRecentes();
        break;
    case 'atividades':
        getAtividadesRecentes();
        break;
    case 'db_stats':
        getDatabaseStats();
        break;
    case 'alertas':
        getAlertasSistema();
        break;
    case 'logs':
        getLogsSistema();
        break;
    case 'notifications':
        getNotifications();
        break;
    case 'poll':
        getPollUpdates();
        break;
    default:
        http_response_code(400);
        echo json_encode(['error' => 'Ação inválida']);
        break;
}

function getUsuariosOnline() {
    $db = getDB();
    
    $stmt = $db->query("
        SELECT 
            u.id, u.nome, u.cargo, u.unidade_id,
            un.nome as unidade,
            s.ultima_atividade,
            TIMESTAMPDIFF(MINUTE, s.ultima_atividade, NOW()) as minutos_inativo
        FROM usuarios u
        JOIN sessoes s ON u.id = s.usuario_id
        LEFT JOIN unidades un ON u.unidade_id = un.id
        WHERE s.ativa = 1 AND s.ultima_atividade >= DATE_SUB(NOW(), INTERVAL 30 MINUTE)
        ORDER BY s.ultima_atividade DESC
    ");
    
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($usuarios);
}

function getAcessosRecentes() {
    $db = getDB();
    
    $stmt = $db->query("
        SELECT 
            l.*,
            u.nome as usuario_nome
        FROM logs_sistema l
        LEFT JOIN usuarios u ON l.usuario_id = u.id
        WHERE l.acao IN ('login', 'logout', 'erro_acesso')
        AND l.data >= DATE_SUB(NOW(), INTERVAL 2 HOUR)
        ORDER BY l.data DESC
        LIMIT 20
    ");
    
    $acessos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($acessos);
}

function getAtividadesRecentes() {
    $db = getDB();
    
    $stmt = $db->query("
        SELECT 
            'membro' as tipo,
            m.nome as descricao,
            'Cadastro' as acao,
            m.data_cadastro as data,
            u.nome as usuario
        FROM membros m
        JOIN usuarios u ON m.usuario_cadastro_id = u.id
        WHERE m.data_cadastro >= DATE_SUB(NOW(), INTERVAL 1 HOUR)
        
        UNION ALL
        
        SELECT 
            'ponto' as tipo,
            mp.descricao,
            'Pontos',
            mp.data,
            u.nome
        FROM membros_pontos mp
        JOIN usuarios u ON mp.usuario_lancamento_id = u.id
        WHERE mp.data >= DATE_SUB(NOW(), INTERVAL 1 HOUR)
        
        UNION ALL
        
        SELECT 
            'presenca' as tipo,
            m.nome,
            'Presença',
            p.data,
            u.nome
        FROM presenca p
        JOIN membros m ON p.membro_id = m.id
        JOIN usuarios u ON p.usuario_registro_id = u.id
        WHERE p.data >= DATE_SUB(NOW(), INTERVAL 1 HOUR)
        
        UNION ALL
        
        SELECT 
            'especialidade' as tipo,
            e.nome as descricao,
            'Especialidade',
            me.data_matricula as data,
            u.nome
        FROM membros_especialidades me
        JOIN especialidades e ON me.especialidade_id = e.id
        JOIN usuarios u ON me.usuario_matricula_id = u.id
        WHERE me.data_matricula >= DATE_SUB(NOW(), INTERVAL 1 HOUR)
        
        ORDER BY data DESC
        LIMIT 20
    ");
    
    $atividades = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($atividades);
}

function getDatabaseStats() {
    // Simular estatísticas do banco de dados
    // Em um ambiente real, você usaria comandos como SHOW STATUS ou queries específicas
    
    $stats = [
        'conexoes' => rand(5, 15),
        'tempo_resposta' => rand(10, 80),
        'queries_por_segundo' => rand(30, 150),
        'memoria_uso' => rand(80, 400),
        'espaco_usado' => rand(20, 80),
        'backup_status' => (rand(0, 1) === 1) ? 'ok' : 'pendente',
        'ultima_verificacao' => date('Y-m-d H:i:s')
    ];
    
    echo json_encode($stats);
}

function getAlertasSistema() {
    $db = getDB();
    
    // Simular alertas
    $alertas = [];
    
    // Verificar se há erros recentes
    $stmt = $db->query("
        SELECT COUNT(*) as total 
        FROM logs_sistema 
        WHERE nivel = 'error' 
        AND data >= DATE_SUB(NOW(), INTERVAL 1 HOUR)
    ");
    
    $erros = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    if ($erros > 0) {
        $alertas[] = [
            'id' => 1,
            'tipo' => 'error',
            'titulo' => 'Erros Detectados',
            'mensagem' => "Foram encontrados {$erros} erros na última hora",
            'data_criacao' => date('Y-m-d H:i:s'),
            'status' => 'ativo'
        ];
    }
    
    // Verificar se há muitos acessos
    $stmt = $db->query("
        SELECT COUNT(*) as total 
        FROM logs_sistema 
        WHERE acao = 'login' 
        AND data >= DATE_SUB(NOW(), INTERVAL 15 MINUTE)
    ");
    
    $logins = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    if ($logins > 10) {
        $alertas[] = [
            'id' => 2,
            'tipo' => 'warning',
            'titulo' => 'Alta Atividade',
            'mensagem' => "Muitos logins nos últimos 15 minutos: {$logins}",
            'data_criacao' => date('Y-m-d H:i:s'),
            'status' => 'ativo'
        ];
    }
    
    // Verificar espaço em disco (simulado)
    $espaco_uso = rand(70, 95);
    if ($espaco_uso > 85) {
        $alertas[] = [
            'id' => 3,
            'tipo' => 'warning',
            'titulo' => 'Espaço em Disco',
            'mensagem' => "Espaço em disco atingindo {$espaco_uso}% de uso",
            'data_criacao' => date('Y-m-d H:i:s'),
            'status' => 'ativo'
        ];
    }
    
    echo json_encode($alertas);
}

function getLogsSistema() {
    $filtro = $_GET['filtro'] ?? 'todos';
    $limite = $_GET['limite'] ?? 50;
    
    $db = getDB();
    
    $sql = "
        SELECT 
            l.*,
            u.nome as usuario_nome
        FROM logs_sistema l
        LEFT JOIN usuarios u ON l.usuario_id = u.id
    ";
    
    $params = [];
    
    if ($filtro !== 'todos') {
        $sql .= " WHERE l.nivel = ?";
        $params[] = $filtro;
    }
    
    $sql .= " ORDER BY l.data DESC LIMIT ?";
    $params[] = $limite;
    
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    
    $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($logs);
}

function getNotifications() {
    $usuario_id = $_SESSION['usuario_id'];
    
    $db = getDB();
    
    $stmt = $db->prepare("
        SELECT 
            n.*,
            (SELECT COUNT(*) FROM notificacoes_leituras nl WHERE nl.notificacao_id = n.id AND nl.usuario_id = ?) as lida
        FROM notificacoes n
        WHERE n.data_criacao >= DATE_SUB(NOW(), INTERVAL 7 DAY)
        AND (n.usuario_destino_id IS NULL OR n.usuario_destino_id = ?)
        ORDER BY n.data_criacao DESC
        LIMIT 10
    ");
    
    $stmt->execute([$usuario_id, $usuario_id]);
    $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($notifications);
}

function getPollUpdates() {
    $last_update = $_GET['last_update'] ?? date('Y-m-d H:i:s', strtotime('-5 minutes'));
    $usuario_id = $_SESSION['usuario_id'];
    
    $db = getDB();
    
    $updates = [];
    
    // Verificar novas notificações
    $stmt = $db->prepare("
        SELECT COUNT(*) as total 
        FROM notificacoes n
        WHERE n.data_criacao > ?
        AND (n.usuario_destino_id IS NULL OR n.usuario_destino_id = ?)
    ");
    
    $stmt->execute([$last_update, $usuario_id]);
    $novas_notificacoes = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    if ($novas_notificacoes > 0) {
        $updates[] = [
            'type' => 'notifications',
            'count' => $novas_notificacoes,
            'message' => "Você tem {$novas_notificacoes} novas notificações"
        ];
    }
    
    // Verificar novas atividades
    $stmt = $db->prepare("
        SELECT COUNT(*) as total 
        FROM logs_sistema l
        WHERE l.data > ?
        AND l.usuario_id != ?
    ");
    
    $stmt->execute([$last_update, $usuario_id]);
    $novas_atividades = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    if ($novas_atividades > 0) {
        $updates[] = [
            'type' => 'activities',
            'count' => $novas_atividades,
            'message' => "{$novas_atividades} novas atividades no sistema"
        ];
    }
    
    // Verificar alertas
    $stmt = $db->query("
        SELECT COUNT(*) as total 
        FROM alertas_sistema 
        WHERE status = 'ativo' 
        AND data_criacao > ?
    ");
    
    $stmt->execute([$last_update]);
    $novos_alertas = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    if ($novos_alertas > 0) {
        $updates[] = [
            'type' => 'alerts',
            'count' => $novos_alertas,
            'message' => "{$novos_alertas} novos alertas no sistema"
        ];
    }
    
    echo json_encode([
        'updates' => $updates,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
}

// Função auxiliar para registrar logs de API
function logAPI($action, $detalhes) {
    $db = getDB();
    
    $stmt = $db->prepare("
        INSERT INTO logs_sistema (usuario_id, acao, detalhes, ip, data, nivel)
        VALUES (?, 'api_call', ?, ?, NOW(), 'info')
    ");
    
    $stmt->execute([
        $_SESSION['usuario_id'] ?? null,
        "API: {$action} - {$detalhes}",
        $_SERVER['REMOTE_ADDR']
    ]);
}

// Registrar chamadas à API
if ($action) {
    logAPI($action, $_SERVER['REQUEST_METHOD']);
}
?>
