# 📋 Guia de Instalação do MySQL Workbench para o Sistema Meritus

## 🛠️ Passo a Passo - MySQL Workbench

### 1. Instalação do MySQL Workbench

#### Windows:
1. Baixe o MySQL Workbench em: https://dev.mysql.com/downloads/workbench/
2. Execute o instalador
3. Siga as instruções (Next → I Agree → Install → Finish)
4. Durante a instalação, anote a senha do usuário root

#### Verificação:
- Abra o MySQL Workbench
- Você verá a tela inicial com conexões disponíveis

---

### 2. Configuração da Conexão

#### Criar Nova Conexão:
1. No MySQL Workbench, clique no `+` (MySQL Connections)
2. Preencha os campos:
   - **Connection Name**: `Meritus Local`
   - **Hostname**: `127.0.0.1` ou `localhost`
   - **Port**: `3306`
   - **Username**: `root`
   - **Password**: Clique em "Store in Keychain" e digite sua senha

#### Testar Conexão:
1. Clique em "Test Connection"
2. Se aparecer "Successfully made connection", clique OK
3. Clique em "OK" para salvar

---

### 3. Criando o Banco de Dados

#### Método 1: Via Interface Gráfica

1. **Conecte-se** ao servidor MySQL (clique na conexão "Meritus Local")
2. Na aba **Query**, clique no ícone de criar nova query (folha em branco)
3. Digite o seguinte comando:

```sql
CREATE DATABASE IF NOT EXISTS meritus 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;
```

4. **Execute** o comando (ícone de raio ⚡ ou Ctrl+Enter)
5. **Verifique** se o banco foi criado:
   - No painel esquerdo (Navigator), clique em "Schemas"
   - Você deve ver `meritus` na lista

#### Método 2: Via Interface Gráfica (Alternativa)
1. Clique com o botão direito em "Schemas"
2. Selecione "Create Schema"
3. Digite `meritus` no nome
4. Clique "Apply"
5. Clique "Finish"

---

### 4. Importando o Script SQL

#### Método A: Copiar e Colar

1. **Abra** o arquivo `database/meritus.sql` em um editor de texto
2. **Selecione** todo o conteúdo (Ctrl+A)
3. **Copie** (Ctrl+C)
4. **Cole** no MySQL Workbench (Ctrl+V)
5. **Execute** tudo (ícone de raio ⚡)

#### Método B: Abrir Arquivo

1. No MySQL Workbench: `File` → `Open Script`
2. Navegue até: `d:/Área de Trabalho/projeto dbv/meritus/database/meritus.sql`
3. Clique em "Open"
4. Execute o script (ícone de raio ⚡)

#### Método C: Import via Command Line (se necessário)

1. Abra o Prompt de Comando como Administrador
2. Navegue até a pasta do MySQL:
   ```bash
   cd "C:\Program Files\MySQL\MySQL Server 8.0\bin"
   ```
3. Execute:
   ```bash
   mysql -u root -p meritus < "d:\Área de Trabalho\projeto dbv\meritus\database\meritus.sql"
   ```
4. Digite a senha quando solicitada

---

### 5. Verificação da Instalação

#### Verificar Tabelas Criadas:
1. No painel esquerdo, expanda `meritus`
2. Você deve ver as tabelas:
   - `usuarios`
   - `membros`
   - `unidades`
   - `presenca`
   - `membros_pontos`
   - `especialidades`
   - E outras...

#### Verificar Dados Iniciais:
1. Clique na tabela `usuarios`
2. Clique na aba "Query"
3. Execute:
   ```sql
   SELECT * FROM usuarios;
   ```
4. Você deve ver os usuários padrão criados

#### Testar Conexão PHP:
1. Abra o arquivo `config/database.php`
2. Verifique se as configurações estão corretas:
   ```php
   private $host = 'localhost';
   private $db_name = 'meritus';
   private $username = 'root';
   private $password = 'SUA_SENHA'; // ← Coloque sua senha aqui
   ```

---

### 6. Configurar Senha no PHP

#### Edite o arquivo `config/database.php`:

```php
<?php
class Database {
    private $host = 'localhost';        // ✅ OK
    private $db_name = 'meritus';       // ✅ OK
    private $username = 'root';         // ✅ OK
    private $password = 'SUA_SENHA';   // ← MUDAR AQUI
    
    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO('mysql:host=' . $this->host . ';dbname=' . $this->db_name, $this->username, $this->password);
            $this->conn->exec('set names utf8');
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $exception) {
            echo 'Connection error: ' . $exception->getMessage();
        }
        return $this->conn;
    }
}
```

---

### 7. Teste Final

#### Acesse o Sistema:
1. Abra o navegador
2. Digite: `http://localhost/meritus/` (ou seu endereço local)
3. Faça login com:
   - **Email**: `admin@meritus.com`
   - **Senha**: `admin123`

#### Se Funcionar:
- ✅ Banco configurado com sucesso!
- ✅ Sistema pronto para uso!

#### Se Der Erro:
- Verifique a senha no `config/database.php`
- Confirme se o serviço MySQL está rodando
- Verifique se as tabelas foram criadas

---

## 🔧 Problemas Comuns e Soluções

### ❌ "Access denied for user 'root'@'localhost'"
**Solução:**
1. Resetar senha do root
2. Verificar se o usuário root tem permissão

### ❌ "Can't connect to MySQL server"
**Solução:**
1. Verifique se o serviço MySQL está rodando
2. Confirme a porta (geralmente 3306)
3. Verifique firewall

### ❌ "Unknown database 'meritus'"
**Solução:**
1. Execute novamente o script SQL
2. Verifique se o banco foi criado

### ❌ "Table doesn't exist"
**Solução:**
1. Verifique se executou o script completo
2. Confirme se não houve erros durante a importação

---

## 📱 Screenshots do Processo

### 1. Tela Inicial do Workbench:
```
MySQL Connections
├── + (criar nova conexão)
├── Local instance MySQL80
└── Meritus Local (sua conexão)
```

### 2. Criando Banco:
```
Query 1
CREATE DATABASE IF NOT EXISTS meritus 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;
```

### 3. Verificando Tabelas:
```
Schemas
├── information_schema
├── meritus ✅
├── mysql
├── performance_schema
└── sys
```

---

## 🎉 Pronto!

Após seguir esses passos, seu sistema Meritus estará 100% funcional com o banco de dados MySQL configurado!

### Próximos Passos:
1. ✅ Testar o login no sistema
2. ✅ Explorar os painéis por cargo
3. ✅ Começar a cadastrar membros
4. ✅ Configurar as unidades

---

## 📞 Ajuda Adicional

Se precisar de mais ajuda:
- Verifique o console do navegador para erros
- Analise os logs do PHP
- Teste a conexão com um script simples

### Script de Teste:
```php
<?php
try {
    $pdo = new PDO('mysql:host=localhost;dbname=meritus', 'root', 'SUA_SENHA');
    echo "✅ Conexão bem-sucedida!";
} catch(PDOException $e) {
    echo "❌ Erro: " . $e->getMessage();
}
?>
```

Salve como `test.php` e acesse no navegador para testar!
