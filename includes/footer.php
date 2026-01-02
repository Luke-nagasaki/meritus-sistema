<?php if (strpos($_SERVER['REQUEST_URI'], '/painel/') !== false): ?>
<footer class="painel-footer">
    <div class="container">
        <p>&copy; <?php echo date('Y'); ?> Meritus - Sistema de Gestão de Desbravadores</p>
        <p>Todos os direitos reservados</p>
    </div>
</footer>
<?php else: ?>
<footer class="main-footer">
    <div class="container">
        <div class="footer-content">
            <div class="footer-section">
                <img src="assets/img/logo.png" alt="Meritus" height="40">
                <p>Sistema de gestão completa para Clubes de Desbravadores</p>
            </div>
            <div class="footer-section">
                <h4>Links</h4>
                <ul>
                    <li><a href="index.php">Início</a></li>
                    <li><a href="login.php">Entrar</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h4>Contato</h4>
                <p>Email: contato@meritus.com</p>
                <p>Telefone: (00) 0000-0000</p>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; <?php echo date('Y'); ?> Meritus. Todos os direitos reservados.</p>
        </div>
    </div>
</footer>
<?php endif; ?>

<script src="assets/js/main.js"></script>
<?php if (strpos($_SERVER['REQUEST_URI'], '/painel/') !== false): ?>
<script src="assets/js/painel.js"></script>
<script src="assets/js/realtime.js"></script>
<?php endif; ?>
</body>
</html>
