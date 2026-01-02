-- SCRIPT SIMPLIFICADO PARA CONFIGURAÇÃO RÁPIDA
-- Execute este script primeiro no MySQL Workbench

-- 1. Criar o banco de dados
CREATE DATABASE IF NOT EXISTS meritus 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

-- 2. Usar o banco criado
USE meritus;

-- 3. Criar tabelas essenciais primeiro
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    cargo ENUM('diretor', 'secretaria', 'conselheiro', 'instrutor', 'monitor') NOT NULL,
    unidade_id INT NULL,
    status ENUM('ativo', 'inativo') DEFAULT 'ativo',
    data_cadastro DATETIME DEFAULT CURRENT_TIMESTAMP,
    ultimo_login DATETIME NULL
);

CREATE TABLE unidades (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    descricao TEXT,
    cor_unidade VARCHAR(7) DEFAULT '#3498db',
    status ENUM('ativo', 'inativo') DEFAULT 'ativo',
    data_criacao DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE membros (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    data_nascimento DATE NOT NULL,
    unidade_id INT NOT NULL,
    status ENUM('ativo', 'inativo', 'transferido') DEFAULT 'ativo',
    data_cadastro DATE NOT NULL,
    usuario_cadastro_id INT NOT NULL,
    pontos_total INT DEFAULT 0
);

-- 4. Inserir dados básicos
INSERT INTO unidades (nome, descricao, cor_unidade) VALUES
('Conquistadores', 'Unidade dos Conquistadores', '#3498db'),
('Vitória', 'Unidade Vitória', '#e74c3c');

-- Usuários padrão (senha: admin123)
INSERT INTO usuarios (nome, email, senha, cargo, unidade_id) VALUES
('Administrador', 'admin@meritus.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'diretor', NULL),
('Secretária', 'secretaria@meritus.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'secretaria', NULL),
('Conselheiro Conquistadores', 'conselheiro@meritus.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'conselheiro', 1),
('Conselheira Vitória', 'conselheira@meritus.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'conselheiro', 2);

-- 5. Verificar se tudo foi criado
SELECT 'BANCO CRIADO COM SUCESSO!' as mensagem;
SELECT COUNT(*) as total_unidades FROM unidades;
SELECT COUNT(*) as total_usuarios FROM usuarios;
