<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? htmlspecialchars($page_title) : 'Meritus'; ?></title>
    <link rel="stylesheet" href="assets/css/main.css">
    <?php if (strpos($_SERVER['REQUEST_URI'], '/painel/') !== false): ?>
    <link rel="stylesheet" href="assets/css/painel.css">
    <?php else: ?>
    <link rel="stylesheet" href="assets/css/login.css">
    <?php endif; ?>
    <link rel="icon" href="assets/img/logo.png" type="image/png">
</head>
<body>
    <?php if (strpos($_SERVER['REQUEST_URI'], '/painel/') !== false): ?>
    <header class="painel-header">
        <div class="header-content">
            <button class="mobile-menu-toggle">☰</button>
            <div class="header-logo">
                <img src="assets/img/logo.png" alt="Meritus" height="30">
            </div>
            <div class="header-user">
                <span>Olá, <?php echo htmlspecialchars($_SESSION['usuario_nome'] ?? ''); ?></span>
                <div class="notification-wrapper">
                    <span class="notification-count" style="display: none;">0</span>
                    <button class="notification-btn">🔔</button>
                </div>
                <a href="logout.php" class="btn btn-outline btn-sm">Sair</a>
            </div>
        </div>
    </header>
    <?php endif; ?>
