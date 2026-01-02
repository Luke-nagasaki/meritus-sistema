<?php
function login($email, $senha) {
    $db = getDB();
    
    $query = "SELECT id, nome, email, senha, cargo, unidade_id FROM usuarios WHERE email = :email AND status = 'ativo'";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (password_verify($senha, $row['senha'])) {
            $_SESSION['usuario_id'] = $row['id'];
            $_SESSION['usuario_nome'] = $row['nome'];
            $_SESSION['usuario_email'] = $row['email'];
            $_SESSION['usuario_cargo'] = $row['cargo'];
            $_SESSION['usuario_unidade_id'] = $row['unidade_id'];
            
            return true;
        }
    }
    
    return false;
}

function logout() {
    session_destroy();
    unset($_SESSION);
}

function verificarAutenticacao() {
    if (!isset($_SESSION['usuario_id'])) {
        header('Location: ../login.php');
        exit;
    }
}

function verificarPermissao($cargo_permitido) {
    if (!isset($_SESSION['usuario_cargo']) || $_SESSION['usuario_cargo'] !== $cargo_permitido) {
        header('Location: ../login.php');
        exit;
    }
}

function getUsuarioLogado() {
    if (isset($_SESSION['usuario_id'])) {
        return [
            'id' => $_SESSION['usuario_id'],
            'nome' => $_SESSION['usuario_nome'],
            'email' => $_SESSION['usuario_email'],
            'cargo' => $_SESSION['usuario_cargo'],
            'unidade_id' => $_SESSION['usuario_unidade_id']
        ];
    }
    return null;
}
?>
