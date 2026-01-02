-- Banco de Dados Meritus - Sistema de Gestão de Desbravadores
-- Criado em: 2026-01-02
-- Versão: 1.0

-- Criar banco de dados
CREATE DATABASE IF NOT EXISTS meritus 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE meritus;

-- Tabela de unidades
CREATE TABLE unidades (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    descricao TEXT,
    conselheiro_id INT NULL,
    cor_unidade VARCHAR(7) DEFAULT '#3498db',
    status ENUM('ativo', 'inativo') DEFAULT 'ativo',
    data_criacao DATETIME DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_status (status),
    INDEX idx_conselheiro (conselheiro_id)
);

-- Tabela de usuários
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    cargo ENUM('diretor', 'secretaria', 'conselheiro', 'instrutor', 'monitor') NOT NULL,
    unidade_id INT NULL,
    status ENUM('ativo', 'inativo') DEFAULT 'ativo',
    data_cadastro DATETIME DEFAULT CURRENT_TIMESTAMP,
    ultimo_login DATETIME NULL,
    INDEX idx_email (email),
    INDEX idx_cargo (cargo),
    INDEX idx_status (status),
    INDEX idx_unidade (unidade_id),
    FOREIGN KEY (unidade_id) REFERENCES unidades(id) ON DELETE SET NULL
);

-- Tabela de membros (desbravadores)
CREATE TABLE membros (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    data_nascimento DATE NOT NULL,
    genero ENUM('masculino', 'feminino') NULL,
    email VARCHAR(100) NULL,
    telefone VARCHAR(20) NULL,
    endereco TEXT NULL,
    unidade_id INT NOT NULL,
    status ENUM('ativo', 'inativo', 'transferido') DEFAULT 'ativo',
    data_cadastro DATE NOT NULL,
    usuario_cadastro_id INT NOT NULL,
    pontos_total INT DEFAULT 0,
    INDEX idx_nome (nome),
    INDEX idx_unidade (unidade_id),
    INDEX idx_status (status),
    INDEX idx_data_nascimento (data_nascimento),
    FOREIGN KEY (unidade_id) REFERENCES unidades(id) ON DELETE RESTRICT,
    FOREIGN KEY (usuario_cadastro_id) REFERENCES usuarios(id) ON DELETE RESTRICT
);

-- Tabela de presenças
CREATE TABLE presenca (
    id INT AUTO_INCREMENT PRIMARY KEY,
    membro_id INT NOT NULL,
    data DATE NOT NULL,
    presente BOOLEAN DEFAULT TRUE,
    usuario_registro_id INT NOT NULL,
    data_registro DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_presenca (membro_id, data),
    INDEX idx_data (data),
    INDEX idx_membro (membro_id),
    INDEX idx_presente (presente),
    FOREIGN KEY (membro_id) REFERENCES membros(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_registro_id) REFERENCES usuarios(id) ON DELETE RESTRICT
);

-- Tabela de pontos dos membros
CREATE TABLE membros_pontos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    membro_id INT NOT NULL,
    categoria ENUM('presenca', 'participacao', 'comportamento', 'especialidade', 'atividade', 'lideranca') NOT NULL,
    pontos INT NOT NULL,
    descricao TEXT NOT NULL,
    usuario_lancamento_id INT NOT NULL,
    data DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_membro (membro_id),
    INDEX idx_categoria (categoria),
    INDEX idx_data (data),
    FOREIGN KEY (membro_id) REFERENCES membros(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_lancamento_id) REFERENCES usuarios(id) ON DELETE RESTRICT
);

-- Tabela de especialidades
CREATE TABLE especialidades (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    descricao TEXT NOT NULL,
    nivel ENUM('basico', 'intermediario', 'avancado', 'mestre') NOT NULL,
    categoria ENUM('arte', 'natureza', 'saude', 'tecnologia', 'lideranca', 'servico') NOT NULL,
    carga_horaria INT NOT NULL,
    instrutor_id INT NOT NULL,
    status ENUM('ativa', 'inativa') DEFAULT 'ativa',
    data_criacao DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_instrutor (instrutor_id),
    INDEX idx_categoria (categoria),
    INDEX idx_nivel (nivel),
    FOREIGN KEY (instrutor_id) REFERENCES usuarios(id) ON DELETE RESTRICT
);

-- Tabela de matrícula em especialidades
CREATE TABLE membros_especialidades (
    id INT AUTO_INCREMENT PRIMARY KEY,
    membro_id INT NOT NULL,
    especialidade_id INT NOT NULL,
    status ENUM('andamento', 'concluida', 'cancelada') DEFAULT 'andamento',
    progresso INT DEFAULT 0,
    data_matricula DATETIME DEFAULT CURRENT_TIMESTAMP,
    data_conclusao DATETIME NULL,
    usuario_matricula_id INT NOT NULL,
    UNIQUE KEY unique_matricula (membro_id, especialidade_id),
    INDEX idx_membro (membro_id),
    INDEX idx_especialidade (especialidade_id),
    INDEX idx_status (status),
    FOREIGN KEY (membro_id) REFERENCES membros(id) ON DELETE CASCADE,
    FOREIGN KEY (especialidade_id) REFERENCES especialidades(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_matricula_id) REFERENCES usuarios(id) ON DELETE RESTRICT
);

-- Tabela de requisitos das especialidades
CREATE TABLE especialidades_requisitos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    especialidade_id INT NOT NULL,
    descricao TEXT NOT NULL,
    detalhes TEXT NULL,
    peso INT DEFAULT 1,
    obrigatorio BOOLEAN DEFAULT TRUE,
    INDEX idx_especialidade (especialidade_id),
    FOREIGN KEY (especialidade_id) REFERENCES especialidades(id) ON DELETE CASCADE
);

-- Tabela de materiais das especialidades
CREATE TABLE especialidades_materiais (
    id INT AUTO_INCREMENT PRIMARY KEY,
    especialidade_id INT NOT NULL,
    titulo VARCHAR(100) NOT NULL,
    descricao TEXT NULL,
    tipo ENUM('pdf', 'video', 'imagem', 'documento', 'link') NOT NULL,
    arquivo_path VARCHAR(255) NULL,
    url_link VARCHAR(255) NULL,
    data_upload DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_especialidade (especialidade_id),
    FOREIGN KEY (especialidade_id) REFERENCES especialidades(id) ON DELETE CASCADE
);

-- Tabela de aulas
CREATE TABLE aulas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    especialidade_id INT NOT NULL,
    instrutor_id INT NOT NULL,
    data DATETIME NOT NULL,
    local VARCHAR(100) NOT NULL,
    duracao INT NOT NULL, -- em minutos
    conteudo TEXT NOT NULL,
    descricao TEXT NULL,
    data_criacao DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_especialidade (especialidade_id),
    INDEX idx_instrutor (instrutor_id),
    INDEX idx_data (data),
    FOREIGN KEY (especialidade_id) REFERENCES especialidades(id) ON DELETE CASCADE,
    FOREIGN KEY (instrutor_id) REFERENCES usuarios(id) ON DELETE RESTRICT
);

-- Tabela de presença em aulas
CREATE TABLE aulas_presencas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    aula_id INT NOT NULL,
    membro_id INT NOT NULL,
    presente BOOLEAN DEFAULT TRUE,
    INDEX idx_aula (aula_id),
    INDEX idx_membro (membro_id),
    UNIQUE KEY unique_presenca_aula (aula_id, membro_id),
    FOREIGN KEY (aula_id) REFERENCES aulas(id) ON DELETE CASCADE,
    FOREIGN KEY (membro_id) REFERENCES membros(id) ON DELETE CASCADE
);

-- Tabela de atividades
CREATE TABLE atividades (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(100) NOT NULL,
    descricao TEXT NOT NULL,
    tipo ENUM('reuniao', 'especialidade', 'atividade', 'evento', 'estudo') NOT NULL,
    data DATETIME NOT NULL,
    local VARCHAR(100) NOT NULL,
    unidade_id INT NULL,
    usuario_criacao_id INT NOT NULL,
    data_criacao DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_data (data),
    INDEX idx_tipo (tipo),
    INDEX idx_unidade (unidade_id),
    FOREIGN KEY (unidade_id) REFERENCES unidades(id) ON DELETE SET NULL,
    FOREIGN KEY (usuario_criacao_id) REFERENCES usuarios(id) ON DELETE RESTRICT
);

-- Tabela de participantes em atividades
CREATE TABLE atividades_participantes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    atividade_id INT NOT NULL,
    membro_id INT NOT NULL,
    presente BOOLEAN DEFAULT TRUE,
    INDEX idx_atividade (atividade_id),
    INDEX idx_membro (membro_id),
    UNIQUE KEY unique_participante (atividade_id, membro_id),
    FOREIGN KEY (atividade_id) REFERENCES atividades(id) ON DELETE CASCADE,
    FOREIGN KEY (membro_id) REFERENCES membros(id) ON DELETE CASCADE
);

-- Tabela de sessões (para controle de login)
CREATE TABLE sessoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    session_id VARCHAR(255) NOT NULL UNIQUE,
    ip VARCHAR(45) NOT NULL,
    user_agent TEXT NULL,
    ativa BOOLEAN DEFAULT TRUE,
    data_login DATETIME DEFAULT CURRENT_TIMESTAMP,
    ultima_atividade DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_usuario (usuario_id),
    INDEX idx_session_id (session_id),
    INDEX idx_ativa (ativa),
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- Tabela de logs do sistema
CREATE TABLE logs_sistema (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NULL,
    acao VARCHAR(100) NOT NULL,
    detalhes TEXT NULL,
    ip VARCHAR(45) NOT NULL,
    data DATETIME DEFAULT CURRENT_TIMESTAMP,
    nivel ENUM('debug', 'info', 'warning', 'error') DEFAULT 'info',
    INDEX idx_usuario (usuario_id),
    INDEX idx_acao (acao),
    INDEX idx_data (data),
    INDEX idx_nivel (nivel),
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
);

-- Tabela de alertas do sistema
CREATE TABLE alertas_sistema (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tipo ENUM('error', 'warning', 'info', 'success') NOT NULL,
    titulo VARCHAR(100) NOT NULL,
    mensagem TEXT NOT NULL,
    status ENUM('ativo', 'resolvido') DEFAULT 'ativo',
    data_criacao DATETIME DEFAULT CURRENT_TIMESTAMP,
    data_resolucao DATETIME NULL,
    usuario_resolucao_id INT NULL,
    INDEX idx_tipo (tipo),
    INDEX idx_status (status),
    INDEX idx_data (data_criacao),
    FOREIGN KEY (usuario_resolucao_id) REFERENCES usuarios(id) ON DELETE SET NULL
);

-- Tabela de notificações
CREATE TABLE notificacoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(100) NOT NULL,
    mensagem TEXT NOT NULL,
    tipo ENUM('info', 'success', 'warning', 'error') DEFAULT 'info',
    usuario_destino_id INT NULL, -- NULL para todos
    usuario_criacao_id INT NOT NULL,
    data_criacao DATETIME DEFAULT CURRENT_TIMESTAMP,
    lida BOOLEAN DEFAULT FALSE,
    INDEX idx_usuario_destino (usuario_destino_id),
    INDEX idx_tipo (tipo),
    INDEX idx_lida (lida),
    INDEX idx_data (data_criacao),
    FOREIGN KEY (usuario_destino_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_criacao_id) REFERENCES usuarios(id) ON DELETE RESTRICT
);

-- Tabela de leituras de notificações
CREATE TABLE notificacoes_leituras (
    id INT AUTO_INCREMENT PRIMARY KEY,
    notificacao_id INT NOT NULL,
    usuario_id INT NOT NULL,
    data_leitura DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_leitura (notificacao_id, usuario_id),
    INDEX idx_notificacao (notificacao_id),
    INDEX idx_usuario (usuario_id),
    FOREIGN KEY (notificacao_id) REFERENCES notificacoes(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- Tabela de configurações do sistema
CREATE TABLE configuracoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    chave VARCHAR(100) NOT NULL UNIQUE,
    valor TEXT NOT NULL,
    descricao TEXT NULL,
    tipo ENUM('texto', 'numero', 'boolean', 'json') DEFAULT 'texto',
    categoria VARCHAR(50) DEFAULT 'geral',
    atualizado_por INT NULL,
    data_atualizacao DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_chave (chave),
    INDEX idx_categoria (categoria),
    FOREIGN KEY (atualizado_por) REFERENCES usuarios(id) ON DELETE SET NULL
);

-- Inserir dados iniciais

-- Unidades
INSERT INTO unidades (nome, descricao, cor_unidade) VALUES
('Conquistadores', 'Unidade dos Conquistadores do Clube Meritus', '#3498db'),
('Vitória', 'Unidade Vitória do Clube Meritus', '#e74c3c');

-- Usuários padrão (senha: admin123)
INSERT INTO usuarios (nome, email, senha, cargo, unidade_id) VALUES
('Administrador', 'admin@meritus.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'diretor', NULL),
('Secretária', 'secretaria@meritus.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'secretaria', NULL),
('Conselheiro Conquistadores', 'conselheiro@meritus.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'conselheiro', 1),
('Conselheira Vitória', 'conselheira@meritus.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'conselheiro', 2),
('Instrutor', 'instrutor@meritus.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'instrutor', NULL),
('Monitor', 'monitor@meritus.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'monitor', NULL);

-- Atualizar conselheiros das unidades
UPDATE unidades SET conselheiro_id = 3 WHERE id = 1;
UPDATE unidades SET conselheiro_id = 4 WHERE id = 2;

-- Especialidades básicas
INSERT INTO especialidades (nome, descricao, nivel, categoria, carga_horaria, instrutor_id) VALUES
('Artes e Habilidades Manuais', 'Desenvolvimento de habilidades manuais e artísticas', 'basico', 'arte', 20, 5),
('Natureza e Vida ao Ar Livre', 'Estudo da natureza e atividades ao ar livre', 'basico', 'natureza', 25, 5),
('Saúde e Fitness', 'Cuidados com a saúde e atividades físicas', 'basico', 'saude', 15, 5),
('Tecnologia Básica', 'Introdução à tecnologia e computação', 'basico', 'tecnologia', 10, 5),
('Liderança Júnior', 'Fundamentos de liderança', 'basico', 'lideranca', 15, 5),
('Serviço Comunitário', 'Trabalhos voluntários e serviço à comunidade', 'basico', 'servico', 20, 5);

-- Configurações do sistema
INSERT INTO configuracoes (chave, valor, descricao, tipo, categoria) VALUES
('nome_sistema', 'Meritus', 'Nome do sistema', 'texto', 'geral'),
('versao', '1.0.0', 'Versão do sistema', 'texto', 'geral'),
('email_contato', 'contato@meritus.com', 'Email de contato', 'texto', 'geral'),
('limite_upload', '10MB', 'Tamanho máximo de upload', 'texto', 'uploads'),
('backup_automatico', 'true', 'Backup automático habilitado', 'boolean', 'backup'),
('manutencao', 'false', 'Sistema em modo de manutenção', 'boolean', 'geral'),
('notificar_presenca', 'true', 'Notificar sobre presença', 'boolean', 'notificacoes'),
('meta_presenca_mensal', '75', 'Meta de presença mensal (%)', 'numero', 'presenca');

-- Criar views para facilitar consultas

-- View de estatísticas das unidades
CREATE VIEW vw_unidades_stats AS
SELECT 
    u.id,
    u.nome,
    u.descricao,
    COUNT(DISTINCT m.id) as total_membros,
    COUNT(DISTINCT CASE WHEN m.status = 'ativo' THEN m.id END) as membros_ativos,
    COUNT(DISTINCT CASE WHEN p.presente = 1 AND p.data = CURDATE() THEN p.membro_id END) as presentes_hoje,
    COALESCE(SUM(mp.pontos), 0) as pontos_total,
    us.nome as conselheiro
FROM unidades u
LEFT JOIN usuarios us ON u.conselheiro_id = us.id
LEFT JOIN membros m ON u.id = m.unidade_id
LEFT JOIN presenca p ON m.id = p.membro_id AND p.data = CURDATE()
LEFT JOIN membros_pontos mp ON m.id = mp.membro_id
WHERE u.status = 'ativo'
GROUP BY u.id, u.nome, u.descricao, us.nome;

-- View de ranking de membros
CREATE VIEW vw_membros_ranking AS
SELECT 
    m.id,
    m.nome,
    u.nome as unidade,
    COALESCE(SUM(mp.pontos), 0) as pontos_total,
    COUNT(DISTINCT CASE WHEN p.presente = 1 THEN p.data END) as total_presencas,
    COUNT(DISTINCT p.data) as total_aulas,
    ROUND(COUNT(DISTINCT CASE WHEN p.presente = 1 THEN p.data END) * 100.0 / COUNT(DISTINCT p.data), 1) as percentual_presenca,
    COUNT(DISTINCT CASE WHEN me.status = 'concluida' THEN me.especialidade_id END) as especialidades_concluidas
FROM membros m
JOIN unidades u ON m.unidade_id = u.id
LEFT JOIN membros_pontos mp ON m.id = mp.membro_id
LEFT JOIN presenca p ON m.id = p.membro_id
LEFT JOIN membros_especialidades me ON m.id = me.membro_id
WHERE m.status = 'ativo'
GROUP BY m.id, m.nome, u.nome
ORDER BY pontos_total DESC, percentual_presenca DESC;

-- View de atividades recentes
CREATE VIEW vw_atividades_recentes AS
SELECT 
    'membro' as tipo,
    m.nome as descricao,
            'Cadastro' as acao,
            m.data_cadastro as data,
            u.nome as usuario,
            m.unidade_id
        FROM membros m
        JOIN usuarios u ON m.usuario_cadastro_id = u.id
        
        UNION ALL
        
        SELECT 
            'ponto' as tipo,
            mp.descricao,
            'Pontos',
            mp.data,
            u.nome,
            m.unidade_id
        FROM membros_pontos mp
        JOIN usuarios u ON mp.usuario_lancamento_id = u.id
        JOIN membros m ON mp.membro_id = m.id
        
        UNION ALL
        
        SELECT 
            'presenca' as tipo,
            m.nome,
            'Presença',
            p.data,
            u.nome,
            m.unidade_id
        FROM presenca p
        JOIN membros m ON p.membro_id = m.id
        JOIN usuarios u ON p.usuario_registro_id = u.id
        
        UNION ALL
        
        SELECT 
            'especialidade' as tipo,
            e.nome as descricao,
            'Matrícula',
            me.data_matricula as data,
            u.nome,
            m.unidade_id
        FROM membros_especialidades me
        JOIN especialidades e ON me.especialidade_id = e.id
        JOIN membros m ON me.membro_id = m.id
        JOIN usuarios u ON me.usuario_matricula_id = u.id
        
        ORDER BY data DESC;

-- Procedimentos armazenados úteis

DELIMITER //

-- Procedimento para calcular pontos totais de um membro
CREATE PROCEDURE sp_calcular_pontos_membro(IN membro_id INT)
BEGIN
    UPDATE membros 
    SET pontos_total = (
        SELECT COALESCE(SUM(pontos), 0) 
        FROM membros_pontos 
        WHERE membro_id = membro_id
    )
    WHERE id = membro_id;
END //

-- Procedimento para limpar logs antigos
CREATE PROCEDURE sp_limpar_logs_antigos(IN dias INT)
BEGIN
    DELETE FROM logs_sistema 
    WHERE data < DATE_SUB(NOW(), INTERVAL dias DAY);
    
    SELECT ROW_COUNT() as logs_removidos;
END //

-- Procedimento para backup das configurações
CREATE PROCEDURE sp_backup_configuracoes()
BEGIN
    SELECT 
        chave,
        valor,
        descricao,
        tipo,
        categoria,
        data_atualizacao
    FROM configuracoes
    ORDER BY categoria, chave;
END //

-- Função para calcular idade
CREATE FUNCTION fn_calcular_idade(data_nascimento DATE) RETURNS INT
DETERMINISTIC
BEGIN
    RETURN TIMESTAMPDIFF(YEAR, data_nascimento, CURDATE());
END //

DELIMITER ;

-- Triggers para manter consistência

-- Trigger para atualizar pontos totais ao inserir pontos
DELIMITER //
CREATE TRIGGER tr_atualizar_pontos_inserir
AFTER INSERT ON membros_pontos
FOR EACH ROW
BEGIN
    CALL sp_calcular_pontos_membro(NEW.membro_id);
END //
DELIMITER ;

-- Trigger para atualizar pontos totais ao atualizar pontos
DELIMITER //
CREATE TRIGGER tr_atualizar_pontos_atualizar
AFTER UPDATE ON membros_pontos
FOR EACH ROW
BEGIN
    CALL sp_calcular_pontos_membro(NEW.membro_id);
END //
DELIMITER ;

-- Trigger para atualizar pontos totais ao excluir pontos
DELIMITER //
CREATE TRIGGER tr_atualizar_pontos_excluir
AFTER DELETE ON membros_pontos
FOR EACH ROW
BEGIN
    CALL sp_calcular_pontos_membro(OLD.membro_id);
END //
DELIMITER ;

-- Trigger para registrar log de alterações em membros
DELIMITER //
CREATE TRIGGER tr_log_membro_inserir
AFTER INSERT ON membros
FOR EACH ROW
BEGIN
    INSERT INTO logs_sistema (usuario_id, acao, detalhes, ip, nivel)
    VALUES (NEW.usuario_cadastro_id, 'cadastrar_membro', 
            CONCAT('Cadastrou membro: ', NEW.nome), 
            '127.0.0.1', 'info');
END //
DELIMITER ;

-- Trigger para registrar log de alterações em usuários
DELIMITER //
CREATE TRIGGER tr_log_usuario_inserir
AFTER INSERT ON usuarios
FOR EACH ROW
BEGIN
    INSERT INTO logs_sistema (usuario_id, acao, detalhes, ip, nivel)
    VALUES (NEW.id, 'cadastrar_usuario', 
            CONCAT('Cadastrou usuário: ', NEW.nome), 
            '127.0.0.1', 'info');
END //
DELIMITER ;

-- Índices adicionais para performance
CREATE INDEX idx_presenca_data_unidade ON presenca(data) 
WHERE membro_id IN (SELECT id FROM membros WHERE unidade_id IS NOT NULL);

CREATE INDEX idx_pontos_data_categoria ON membros_pontos(data, categoria);

CREATE INDEX idx_logs_data_nivel ON logs_sistema(data, nivel);

CREATE INDEX idx_atividades_data_tipo ON atividades(data, tipo);

-- Finalizar
SELECT 'Banco de dados Meritus criado com sucesso!' as mensagem;
