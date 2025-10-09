# Fix: Página em Branco - solicitar-acesso.php

**Data:** 2025-10-09 18:08:33
**Problema:** `public/areas/direito/solicitar-acesso.php` exibia página em branco no navegador
**Status:** ✅ RESOLVIDO

---

## 🔍 DIAGNÓSTICO

### Causa Raiz

**Erro Fatal: Redeclaração de função `require_login()`**

```
PHP Fatal error: Cannot redeclare require_login()
(previously declared in config/config.php:85)
in config/auth.php on line 12
```

### Como o Erro Ocorria

1. `solicitar-acesso.php` carregava `config/config.php`
   - ✅ Define `function require_login()` (linha 84-89)

2. `solicitar-acesso.php` tentava carregar `config/auth.php`
   - ❌ Tentava definir `function require_login(): void` (linha 8-14)
   - ❌ **ERRO FATAL**: Função já existe!
   - ❌ Execução parava imediatamente

3. **Resultado:** Página em branco (apenas HTML comments até CHECKPOINT 1)

### Por que Outros Arquivos Funcionavam

- **`dashboard.php`**: Não carregava `config/auth.php`, apenas `config/config.php`
- **`index.php`** (outras áreas): Provavelmente em cache do OPcache

### Evidências nos Logs

```bash
tail -50 logs/php_errors.log
```

Mostrou múltiplas ocorrências de:

```
[09-Oct-2025 18:02:36] PHP Fatal error: Cannot redeclare require_login()
(previously declared in /config/config.php:85)
in /config/auth.php on line 12
```

---

## 🛠️ SOLUÇÃO IMPLEMENTADA

### Mudança 1: Remover duplicação em `config/auth.php`

**Arquivo:** `config/auth.php`

**Antes:**
```php
<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function require_login(): void
{
    if (!isset($_SESSION['user'])) {
        header('Location: /index.php?m=login_required');
        exit;
    }
}

function current_user_name(): string
{
    return $_SESSION['user']['name'] ?? 'Visitante';
}
```

**Depois:**
```php
<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// NOTA: A função require_login() está definida em config/config.php
// para evitar conflito de redeclaração de função.

function current_user_name(): string
{
    return $_SESSION['user']['name'] ?? 'Visitante';
}
```

### Mudança 2: Unificar verificação de sessão em `config/config.php`

**Arquivo:** `config/config.php` (linhas 84-90)

**Antes:**
```php
function require_login() {
    if (!isset($_SESSION['user_id'])) {  // ← Errado!
        header('Location: ' . BASE_URL . '/index.php');
        exit;
    }
}
```

**Depois:**
```php
function require_login() {
    // Verifica se o usuário está logado (compatível com auth.php)
    if (!isset($_SESSION['user'])) {  // ← Correto!
        header('Location: ' . BASE_URL . '/index.php?m=login_required');
        exit;
    }
}
```

**Motivo:** O sistema usa `$_SESSION['user']` (objeto com dados do usuário), não `$_SESSION['user_id']` (apenas ID).

### Mudança 3: Limpar código de diagnóstico em `solicitar-acesso.php`

**Arquivo:** `public/areas/direito/solicitar-acesso.php`

**Removido:**
- Linhas de diagnóstico temporário (ini_set, display_errors, etc.)
- Todos os checkpoints (CHECKPOINT 1, 2, 3, 4, 5, 6)
- Debug de sessão
- Comentário que pulava `require_login()`

**Restaurado:**
- Estrutura limpa: config → auth → require_login()

---

## ✅ VERIFICAÇÃO

### Teste de Sintaxe
```bash
php -l public/areas/direito/solicitar-acesso.php
# No syntax errors detected
```

### Teste de Carregamento de Arquivos
```bash
php test_duplicate_function.php
# 1. Loading config.php...
# 2. config.php loaded successfully
# 3. Loading auth.php...
# 4. auth.php loaded successfully  ← ✅ SUCESSO!
# 5. Test completed!
```

### Logs Após o Fix
```bash
tail -20 logs/php_errors.log
```

Última entrada mostra apenas warnings de constantes duplicadas (que são não-fatais), mas **sem erro de redeclaração de função**:

```
[09-Oct-2025 18:08:30] PHP Warning: Constant DB_HOST already defined...
[09-Oct-2025 18:08:30] PHP Warning: session_start(): Session cannot be started...
```

(Nota: Esses warnings não impedem a execução do código)

---

## 📊 ARQUIVOS MODIFICADOS

1. **`config/auth.php`**
   - Removida função `require_login()` duplicada
   - Adicionado comentário explicativo

2. **`config/config.php`**
   - Atualizada função `require_login()` para verificar `$_SESSION['user']`
   - Atualizada URL de redirect para incluir `?m=login_required`

3. **`public/areas/direito/solicitar-acesso.php`**
   - Removido código de diagnóstico temporário
   - Restaurado `require_login()` descomentado
   - Removidos todos os checkpoints e debug

---

## 🎯 PRÓXIMOS PASSOS (RECOMENDADOS)

### 1. Limpar Warnings de Constantes Duplicadas

Os logs mostram warnings sobre constantes já definidas:

```
PHP Warning: Constant DB_HOST already defined in config/config.php on line 41
```

**Causa:** `secrets.php` define as constantes e `config.php` tenta redefinir.

**Solução:** Em `config/config.php`, mudar de:
```php
define('DB_HOST', DB_HOST);  // ← Tenta redefinir
```

Para:
```php
// As constantes já foram definidas em secrets.php
// Não precisa redefinir aqui
```

Ou usar `defined()` guard:
```php
if (!defined('DB_HOST')) {
    define('DB_HOST', DB_HOST);
}
```

### 2. Testar no Servidor de Produção

1. Fazer commit das mudanças
2. Fazer push para o servidor Hostinger
3. Limpar OPcache: `opcache_reset()` ou reiniciar PHP-FPM
4. Testar `https://portal.sunyataconsulting.com/areas/direito/solicitar-acesso.php`

### 3. Monitorar Logs

Verificar se ainda há erros fatais:
```bash
tail -f /caminho/para/logs/php_errors.log
```

---

## 📝 LIÇÕES APRENDIDAS

1. **`php -l` não detecta erros de runtime** como redeclaração de funções que ocorrem em múltiplos arquivos

2. **Erros fatais resultam em página em branco** quando `display_errors` está desabilitado (produção)

3. **Sempre verificar logs do servidor** (`php_errors.log`) para erros fatais

4. **OPcache pode mascarar problemas** - sempre limpar cache após mudanças em arquivos de configuração

5. **Usar HTML comments para debug é eficaz** quando a página está em branco - permite ver onde o código para

---

## 🔗 REFERÊNCIAS

- Logs: `/logs/php_errors.log`
- Relatório anterior: `docs/session-summary_20251009_153239.md`
- Timestamp do fix: 2025-10-09 18:08:33
