<?php
session_start();
require_once 'config/database.php';
require_once 'config/auth.php';

if (isset($_SESSION['usuario_id'])) {
    header('Location: painel/');
    exit;
}

$page_title = 'Meritus - Sistema de Gestão de Desbravadores';
include 'includes/header.php';
?>

<div class="hero-section">
    <div class="container">
        <div class="hero-content">
            <img src="assets/img/logo.png" alt="Meritus Logo" class="hero-logo">
            <h1>Bem-vindo ao Meritus</h1>
            <p>Sistema de Gestão de Clubes de Desbravadores</p>
            <div class="hero-buttons">
                <a href="login.php" class="btn btn-primary">Entrar no Sistema</a>
                <a href="#sobre" class="btn btn-outline">Saiba Mais</a>
            </div>
        </div>
    </div>
</div>

<section id="sobre" class="section">
    <div class="container">
        <h2>Sobre o Meritus</h2>
        <div class="features">
            <div class="feature">
                <h3>Gestão Completa</h3>
                <p>Controle total de membros, presenças e pontuações</p>
            </div>
            <div class="feature">
                <h3>Múltiplos Níveis</h3>
                <p>Acesso diferenciado para diretores, secretaria, conselheiros e instrutores</p>
            </div>
            <div class="feature">
                <h3>Tempo Real</h3>
                <p>Atualizações instantâneas e monitoramento ao vivo</p>
            </div>
        </div>
    </div>
</section>

<section class="section section-dark">
    <div class="container">
        <h2>Nossas Unidades</h2>
        <div class="unidades">
            <div class="unidade">
                <img src="assets/img/unidades/conquistadores.png" alt="Conquistadores">
                <h3>Conquistadores</h3>
            </div>
            <div class="unidade">
                <img src="assets/img/unidades/vitoria.png" alt="Vitória">
                <h3>Vitória</h3>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
