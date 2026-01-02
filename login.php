<?php
session_start();
require_once 'config/database.php';
require_once 'config/auth.php';

if (isset($_SESSION['usuario_id'])) {
    header('Location: painel/');
    exit;
}

$erro = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'] ?? '';
    $senha = $_POST['senha'] ?? '';
    
    if (login($email, $senha)) {
        header('Location: painel/');
        exit;
    } else {
        $erro = 'Email ou senha incorretos';
    }
}

$page_title = 'Login - Meritus';
include 'includes/header.php';
?>

<div class="login-container">
    <div class="login-card">
        <div class="login-header">
            <img src="assets/img/logo.png" alt="Meritus Logo" class="login-logo">
            <h1>Entrar no Sistema</h1>
        </div>
        
        <?php if ($erro): ?>
            <div class="alert alert-error"><?php echo $erro; ?></div>
        <?php endif; ?>
        
        <form method="POST" class="login-form">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required>
            </div>
            
            <div class="form-group">
                <label for="senha">Senha</label>
                <input type="password" id="senha" name="senha" required>
            </div>
            
            <button type="submit" class="btn btn-primary btn-full">Entrar</button>
        </form>
        
        <div class="login-footer">
            <p>Sistema de Gestão de Desbravadores</p>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
