<aside class="sidebar">
    <div class="sidebar-header">
        <img src="assets/img/logo.png" alt="Meritus" class="sidebar-logo">
        <h3>Meritus</h3>
    </div>
    
    <div class="sidebar-user">
        <div class="user-avatar">
            <img src="assets/img/default-avatar.png" alt="Usuário" width="40" height="40">
        </div>
        <div class="user-info">
            <div class="user-name"><?php echo htmlspecialchars($_SESSION['usuario_nome'] ?? ''); ?></div>
            <div class="user-role"><?php echo htmlspecialchars(ucfirst($_SESSION['usuario_cargo'] ?? '')); ?></div>
        </div>
    </div>
    
    <nav class="sidebar-nav">
        <ul class="sidebar-menu">
            <li>
                <a href="index.php">
                    <span class="menu-icon">🏠</span>
                    Início
                </a>
            </li>
            
            <?php if ($_SESSION['usuario_cargo'] === 'diretor'): ?>
            <li class="menu-section">
                <span class="menu-section-title">Diretoria</span>
            </li>
            <li>
                <a href="diretor/usuarios.php">
                    <span class="menu-icon">👥</span>
                    Usuários
                </a>
            </li>
            <li>
                <a href="diretor/unidades.php">
                    <span class="menu-icon">🏢</span>
                    Unidades
                </a>
            </li>
            <li>
                <a href="diretor/relatorios.php">
                    <span class="menu-icon">📊</span>
                    Relatórios
                </a>
            </li>
            <?php endif; ?>
            
            <?php if ($_SESSION['usuario_cargo'] === 'secretaria'): ?>
            <li class="menu-section">
                <span class="menu-section-title">Secretaria</span>
            </li>
            <li>
                <a href="secretaria/membros.php">
                    <span class="menu-icon">👤</span>
                    Membros
                </a>
            </li>
            <li>
                <a href="secretaria/presenca.php">
                    <span class="menu-icon">✅</span>
                    Presença
                </a>
            </li>
            <?php endif; ?>
            
            <?php if ($_SESSION['usuario_cargo'] === 'conselheiro'): ?>
            <li class="menu-section">
                <span class="menu-section-title">Conselho</span>
            </li>
            <li>
                <a href="conselheiro/unidade.php">
                    <span class="menu-icon">🏢</span>
                    Minha Unidade
                </a>
            </li>
            <li>
                <a href="conselheiro/pontos.php">
                    <span class="menu-icon">⭐</span>
                    Pontuação
                </a>
            </li>
            <?php endif; ?>
            
            <?php if ($_SESSION['usuario_cargo'] === 'instrutor'): ?>
            <li class="menu-section">
                <span class="menu-section-title">Instrução</span>
            </li>
            <li>
                <a href="instrutor/especialidades.php">
                    <span class="menu-icon">🎓</span>
                    Especialidades
                </a>
            </li>
            <?php endif; ?>
            
            <?php if ($_SESSION['usuario_cargo'] === 'monitor'): ?>
            <li class="menu-section">
                <span class="menu-section-title">Monitoramento</span>
            </li>
            <li>
                <a href="monitor/index.php">
                    <span class="menu-icon">📡</span>
                    Tempo Real
                </a>
            </li>
            <li>
                <a href="monitor/logs.php">
                    <span class="menu-icon">📋</span>
                    Logs
                </a>
            </li>
            <?php endif; ?>
            
            <li class="menu-section">
                <span class="menu-section-title">Sistema</span>
            </li>
            <li>
                <a href="../logout.php">
                    <span class="menu-icon">🚪</span>
                    Sair
                </a>
            </li>
        </ul>
    </nav>
</aside>
