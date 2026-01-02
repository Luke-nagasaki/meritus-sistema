# 🌐 Meritus - Sistema de Gestão de Desbravadores

Sistema completo para gestão de Clubes de Desbravadores com múltiplos níveis de acesso, controle de presença, pontuação e monitoramento em tempo real.

## 🚀 Tecnologias Utilizadas

- **Frontend**: HTML5 + CSS3 + JavaScript (ES6+)
- **Backend**: PHP 8.0+
- **Banco de Dados**: MySQL 8.0+
- **Servidor**: Apache/Nginx com PHP-FPM

## 📁 Estrutura do Projeto

```
meritus/
│
├── index.php                # Página inicial (site público)
├── login.php                # Login geral
├── logout.php               # Logout
│
├── config/
│   ├── database.php         # Conexão MySQL
│   └── auth.php             # Controle de sessão e permissões
│
├── assets/
│   ├── css/
│   │   ├── main.css         # Estilo geral
│   │   ├── painel.css       # Estilo dos painéis
│   │   └── login.css        # Estilo do login
│   ├── js/
│   │   ├── main.js          # Scripts gerais
│   │   ├── painel.js        # Scripts dos painéis
│   │   └── realtime.js      # Atualização em tempo real
│   └── img/
│       ├── logo.png
│       ├── banner.jpg
│       └── unidades/
│           ├── conquistadores.png
│           └── vitoria.png
│
├── includes/
│   ├── header.php           # Cabeçalho padrão
│   ├── footer.php           # Rodapé
│   └── sidebar.php          # Menu lateral
│
├── painel/
│   ├── index.php            # Redirecionamento por cargo
│   ├── diretor/             # Painel do Diretor
│   │   ├── index.php        # Dashboard
│   │   ├── usuarios.php     # Gerenciar usuários
│   │   ├── unidades.php     # Gerenciar unidades
│   │   └── relatorios.php   # Relatórios
│   ├── secretaria/          # Painel da Secretaria
│   │   ├── index.php        # Dashboard
│   │   ├── membros.php      # Cadastro de membros
│   │   └── presenca.php     # Controle de presença
│   ├── conselheiro/         # Painel do Conselheiro
│   │   ├── index.php        # Dashboard
│   │   ├── unidade.php      # Gerenciar unidade
│   │   └── pontos.php       # Pontuação
│   ├── instrutor/           # Painel do Instrutor
│   │   ├── index.php        # Dashboard
│   │   └── especialidades.php # Especialidades
│   └── monitor/             # Painel do Monitor
│       ├── index.php        # Monitoramento em tempo real
│       └── logs.php         # Logs do sistema
│
├── api/
│   ├── presenca.php         # API de presença
│   ├── pontos.php          # API de pontos
│   └── realtime.php        # API em tempo real
│
└── database/
    └── meritus.sql          # Script do banco
```

## 🛠️ Instalação

### 1. Pré-requisitos

- PHP 8.0+ com extensões:
  - PDO MySQL
  - JSON
  - MBString
  - Session
- MySQL 8.0+
- Servidor web (Apache/Nginx)

### 2. Configuração do Banco de Dados

1. Crie o banco de dados:
   ```sql
   CREATE DATABASE meritus CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```

2. Importe o arquivo `database/meritus.sql`:
   ```bash
   mysql -u usuario -p meritus < database/meritus.sql
   ```

### 3. Configuração do Sistema

1. Configure a conexão com o banco em `config/database.php`:
   ```php
   private $host = 'localhost';
   private $db_name = 'meritus';
   private $username = 'root';
   private $password = 'sua_senha';
   ```

2. Ajuste as permissões das pastas:
   ```bash
   chmod 755 assets/
   chmod 644 assets/css/
   chmod 644 assets/js/
   ```

### 4. Acesso Inicial

- **URL**: `http://seu-dominio/meritus/`
- **Usuários Padrão**:
  - **Diretor**: admin@meritus.com / admin123
  - **Secretaria**: secretaria@meritus.com / admin123
  - **Conselheiro**: conselheiro@meritus.com / admin123
  - **Instrutor**: instrutor@meritus.com / admin123
  - **Monitor**: monitor@meritus.com / admin123

## 👥 Cargos e Permissões

### 🧭 Diretor
- Acesso total ao sistema
- Gerenciar usuários e cargos
- Visualizar todas as unidades
- Gerar relatórios gerais
- Controle do sistema

### 📋 Secretaria
- Cadastro de desbravadores
- Controle de presença
- Atualização de dados
- Relatórios simples

### 👩‍🏫 Conselheiro
- Visualiza apenas sua unidade
- Lança pontos
- Avalia comportamento
- Vê ranking da unidade

### 🛠️ Instrutor
- Registra aulas
- Marca especialidades
- Acompanha progresso
- Gerencia materiais

### 🖥️ Monitor
- Monitoramento em tempo real
- Ver usuários online
- Visualizar logs
- Alertas do sistema

## 🌟 Funcionalidades Principais

### 📊 Dashboard Personalizado
- Estatísticas em tempo real
- Gráficos interativos
- Informações relevantes por cargo

### ✅ Controle de Presença
- Registro rápido de presença
- Histórico completo
- Estatísticas e rankings
- Exportação de dados

### ⭐ Sistema de Pontos
- Lançamento por categorias
- Ranking automático
- Histórico detalhado
- Análise de desempenho

### 🎓 Gestão de Especialidades
- Cadastro de especialidades
- Controle de progresso
- Materiais de apoio
- Registro de aulas

### 📱 Monitoramento em Tempo Real
- Usuários online
- Atividades recentes
- Alertas do sistema
- Logs detalhados

### 📈 Relatórios
- Relatórios por período
- Exportação em PDF/Excel
- Análises estatísticas
- Visualizações gráficas

## 🔧 Configurações Adicionais

### Upload de Arquivos
Configure o limite de upload em `php.ini`:
```ini
upload_max_filesize = 10M
post_max_size = 10M
```

### Timezone
Configure o timezone em `php.ini`:
```ini
date.timezone = America/Sao_Paulo
```

### Cache
Para melhor performance, habilite cache:
```ini
opcache.enable=1
opcache.memory_consumption=128
```

## 🎨 Personalização

### Cores e Identidade Visual
- Altere as cores em `assets/css/main.css`
- Substitua as imagens em `assets/img/`
- Ajuste o logo e banner

### Unidades
- Adicione novas unidades pelo painel do Diretor
- Personalize cores e logos
- Configure conselheiros

## 🔒 Segurança

### Recursos Implementados
- Senhas criptografadas (bcrypt)
- Sessões seguras
- Proteção contra CSRF
- SQL Injection prevention
- XSS protection

### Recomendações
- Alterar senhas padrão
- Usar HTTPS
- Configurar backup automático
- Monitorar logs de acesso

## 📱 Responsividade

O sistema é totalmente responsivo e funciona em:
- Desktop (Chrome, Firefox, Safari, Edge)
- Tablets (iPad, Android)
- Smartphones (iOS, Android)

## 🔄 Atualizações em Tempo Real

O sistema utiliza:
- JavaScript Fetch API
- WebSockets (quando disponível)
- Polling como fallback
- Atualização automática a cada 5 segundos

## 📊 APIs Disponíveis

### Presença
- `GET /api/presenca.php?action=carregar`
- `POST /api/presenca.php?action=salvar`
- `GET /api/presenca.php?action=stats`

### Pontos
- `POST /api/pontos.php?action=lancar`
- `GET /api/pontos.php?action=ranking`
- `GET /api/pontos.php?action=analise`

### Tempo Real
- `GET /api/realtime.php?action=usuarios_online`
- `GET /api/realtime.php?action=atividades`
- `GET /api/realtime.php?action=alertas`

## 🛠️ Manutenção

### Backup Automático
```bash
# Script de backup
mysqldump -u usuario -p meritus > backup_$(date +%Y%m%d).sql
```

### Limpeza de Logs
```sql
-- Limpar logs com mais de 90 dias
CALL sp_limpar_logs_antigos(90);
```

### Otimização do Banco
```sql
-- Otimizar tabelas
OPTIMIZE TABLE membros;
OPTIMIZE TABLE presenca;
OPTIMIZE TABLE membros_pontos;
```

## 🐛 Solução de Problemas

### Problemas Comuns

1. **Erro de conexão com banco**
   - Verifique credenciais em `config/database.php`
   - Confirme se o MySQL está rodando

2. **Upload não funciona**
   - Verifique permissões das pastas
   - Confirme configuração `upload_max_filesize`

3. **Sessão expira**
   - Ajuste `session.gc_maxlifetime` em `php.ini`
   - Verifique configuração de cookies

4. **Gráficos não aparecem**
   - Verifique console do navegador
   - Confirme se JavaScript está habilitado

## 📞 Suporte

### Documentação
- Leia este README completo
- Verifique comentários no código
- Analise logs do sistema

### Contato
- Email: contato@meritus.com
- Sistema de mensagens interno

## 📝 Licença

Este projeto é licenciado sob a MIT License.

## 🤝 Contribuição

1. Fork o projeto
2. Crie uma branch (`git checkout -b feature/nova-funcionalidade`)
3. Commit suas mudanças (`git commit -am 'Adiciona nova funcionalidade'`)
4. Push para a branch (`git push origin feature/nova-funcionalidade`)
5. Abra um Pull Request

---

## 🎉 Divirta-se!

O Meritus foi desenvolvido com ❤️ para facilitar a gestão de Clubes de Desbravadores.

**Versão**: 1.0.0  
**Última Atualização**: 02/01/2026
