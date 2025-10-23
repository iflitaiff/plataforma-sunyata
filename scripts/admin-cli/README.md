# Admin CLI Tools

Ferramentas de administração via linha de comando para gerenciamento remoto da Plataforma Sunyata.

## Requisitos

- SSH no servidor
- PHP CLI 8.0+
- Acesso ao diretório do projeto

## Scripts Disponíveis

### 1. Gerenciamento de Sessões (`sessions.php`)

**Listar sessões ativas:**
```bash
php sessions.php list
```

**Encerrar sessão específica:**
```bash
php sessions.php kill abc123def456...
```

**Encerrar todas sessões de um usuário:**
```bash
php sessions.php kill-user 42
```

**Limpar sessões expiradas:**
```bash
php sessions.php clean
```

---

### 2. Estatísticas (`stats.php`)

**Estatísticas gerais:**
```bash
php stats.php
# ou
php stats.php general
```

**Estatísticas de usuários:**
```bash
php stats.php users
```

**Estatísticas por vertical:**
```bash
php stats.php vertical
```

**Estatísticas de uso da API Claude:**
```bash
php stats.php api
```

---

### 3. Gerenciamento de Cache (`cache.php`)

**Limpar cache de Settings:**
```bash
php cache.php clear-settings
```

**Remover sessões expiradas:**
```bash
php cache.php clear-sessions
```

**Remover logs antigos (>90 dias):**
```bash
php cache.php clear-logs
```

**Limpeza completa:**
```bash
php cache.php clear-all
```

---

## Exemplos de Uso Remoto

### Acessar servidor e executar:

```bash
# Login SSH
ssh -p 65002 u202164171@82.25.72.226

# Navegar para scripts
cd /home/u202164171/domains/sunyataconsulting.com/public_html/plataforma-sunyata/scripts/admin-cli

# Executar comandos
php stats.php
php sessions.php list
php cache.php clear-all
```

### Executar comando direto (sem login interativo):

```bash
# Estatísticas gerais
ssh -p 65002 u202164171@82.25.72.226 "php /home/u202164171/domains/sunyataconsulting.com/public_html/plataforma-sunyata/scripts/admin-cli/stats.php"

# Limpar cache
ssh -p 65002 u202164171@82.25.72.226 "php /home/u202164171/domains/sunyataconsulting.com/public_html/plataforma-sunyata/scripts/admin-cli/cache.php clear-all"

# Listar sessões
ssh -p 65002 u202164171@82.25.72.226 "php /home/u202164171/domains/sunyataconsulting.com/public_html/plataforma-sunyata/scripts/admin-cli/sessions.php list"
```

---

## Segurança

- ⚠️ **Apenas use via SSH autenticado**
- ⚠️ **Scripts verificam que estão rodando em CLI** (não via web)
- ⚠️ **Não expor o diretório `/scripts` publicamente**

---

## Logs

Erros são registrados em:
```
/home/u202164171/domains/sunyataconsulting.com/public_html/plataforma-sunyata/logs/php_errors.log
```

---

## Troubleshooting

**Erro: "Permission denied"**
```bash
chmod +x scripts/admin-cli/*.php
```

**Erro: "Database connection error"**
- Verifique `config/secrets.php`
- Teste conexão: `php -r "new PDO('mysql:host=localhost;dbname=u202164171_sunyata', 'u202164171_sunyata', 'senha');"`

**Erro: "Class not found"**
```bash
cd /path/to/project
composer install
```

---

## Manutenção Recomendada

### Diária
- Verificar sessões ativas: `php sessions.php list`

### Semanal
- Estatísticas gerais: `php stats.php`
- Estatísticas API: `php stats.php api`

### Mensal
- Limpar cache completo: `php cache.php clear-all`
- Verificar espaço em disco: `php stats.php general`

---

## Versão

1.0.0 - Outubro 2025
