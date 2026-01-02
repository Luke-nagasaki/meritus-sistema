<?php
session_start();
require_once '../config/database.php';
require_once '../config/auth.php';

verificarAutenticacao();

$usuario = getUsuarioLogado();
$cargo = $usuario['cargo'];

// Redirecionar para o painel específico do cargo
switch ($cargo) {
    case 'diretor':
        header('Location: diretor/index.php');
        break;
    case 'secretaria':
        header('Location: secretaria/index.php');
        break;
    case 'conselheiro':
        header('Location: conselheiro/index.php');
        break;
    case 'instrutor':
        header('Location: instrutor/index.php');
        break;
    case 'monitor':
        header('Location: monitor/index.php');
        break;
    default:
        header('Location: ../login.php');
        break;
}
exit;
?>
