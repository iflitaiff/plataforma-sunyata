# RESUMO TÉCNICO - HTTP 500 em canvas-templates.php

## CONTEXTO DO PROJETO

**Plataforma Sunyata** - Sistema de Canvas interativos com IA (Claude API) para análise jurídica.

**Objetivo do Sprint Atual:** Implementar MVP Admin Canvas - interface de administração para editar Canvas Templates (formulários dinâmicos SurveyJS + prompts para Claude API).

## AMBIENTE

### Desenvolvimento (Local - WSL)
- Path: `/home/iflitaiff/projetos/plataforma-sunyata`
- MySQL: `sunyata_dev` (user: dev/dev123)
- PHP 8.3.6
- Não testável localmente (BASE_URL hardcoded para produção, OAuth aponta para produção)

### Produção (Hostinger Shared Hosting)
- SSH: `u202164171@82.25.72.226` porta `65002`
- Path: `/home/u202164171/domains/sunyataconsulting.com/public_html/plataforma-sunyata`
- Database: `u202164171_sunyata` (MariaDB)
- PHP 8.2.28 (LiteSpeed)
- Configurações: max_execution_time=360s, memory_limit=1536M, upload_max_filesize=1536M
- **Funções desabilitadas:** exec, shell_exec, system

### GitHub
- Repo: https://github.com/iflitaiff/plataforma-sunyata
- Branch atual: `feature/mvp-admin-canvas`
- Último commit: `68c4a56` - fix: Add stats array initialization

### Usuários Admin (para testes)
- `flitaiff@gmail.com` (user_id=7, access_level=admin)
- `filipe.litaiff@ifrj.edu.br` (user_id=1, access_level=admin)

## O PROBLEMA

**Sintoma:** HTTP ERROR 500 ao acessar `https://portal.sunyataconsulting.com/admin/canvas-templates.php` com usuário admin via browser.

**Arquivos afetados:**
- `public/admin/canvas-templates.php` - Erro 500
- `public/admin/canvas-edit.php` - Presumivelmente mesmo erro (não testado)

**Arquivos que FUNCIONAM:**
- `public/admin/index.php` - OK
- `public/admin/users.php` - OK
- `public/admin/access-requests.php` - OK
- `public/admin/info.php` (phpinfo()) - OK

## DIAGNÓSTICO REALIZADO

### Testes Executados

1. **Sintaxe PHP:** ✅ OK
   ```bash
   php -l canvas-templates.php
   # No syntax errors detected
   ```

2. **Execução via CLI:** ✅ Roda sem erro fatal
   ```bash
   php canvas-templates.php
   # Apenas warnings de constantes já definidas, sem fatal error
   ```

3. **Permissões:** ✅ OK
   ```
   -rw-r--r-- 1 u202164171 o1006921199 7052 Oct 21 19:13 canvas-templates.php
   ```

4. **Encoding:** ✅ OK
   ```
   PHP script, UTF-8 Unicode text (mesmo que index.php que funciona)
   ```

5. **Database:** ✅ OK
   ```sql
   SELECT * FROM canvas_templates; -- Retorna 1 registro (ID=1, juridico-geral)
   SELECT * FROM vertical_access_requests; -- Tabela existe, count=0
   ```

6. **Teste de páginas debug criadas:**
   - `ERRO.php` (debug step-by-step) → HTTP 500, página em branco
   - `canvas-debug.php` → HTTP 500
   - `canvas-minimal.php` → Não testado
   - `test-canvas.php` (só queries SQL) → ✅ Funcionou
   - `info.php` (phpinfo) → ✅ Funcionou

### Tentativas de Correção (SEM SUCESSO)

1. **Adicionado bloco stats em canvas-templates.php** (linhas 25-36)
   ```php
   // Stats for admin-header.php (pending requests badge)
   try {
       $result = $db->fetchOne("SELECT COUNT(*) as count FROM vertical_access_requests WHERE status = 'pending'");
       $stats['pending_requests'] = $result['count'] ?? 0;
   } catch (Exception $e) {
       $stats['pending_requests'] = 0;
   }
   ```
   **Motivo:** admin-header.php espera $stats['pending_requests'] definido
   **Resultado:** Ainda HTTP 500

2. **Limpado cache do servidor**
   ```bash
   rm -rf /var/cache/*
   ```
   **Resultado:** Ainda HTTP 500

3. **Criado .htaccess para desabilitar cache e mostrar erros**
   ```apache
   php_flag display_errors On
   php_value error_reporting E_ALL
   ```
   **Resultado:** Quebrou TODAS as páginas (removido depois)

4. **Corrigido config.php - removido redefinição de constantes**
   Problema: linhas 48-57 tentavam redefinir DB_HOST, DB_NAME, etc. que já existiam em secrets.php
   ```php
   // ANTES (bugado):
   define('DB_HOST', DB_HOST); // Tenta redefinir

   // DEPOIS (corrigido):
   // Note: DB_HOST já definido em secrets.php
   ```
   **Resultado:** Ainda HTTP 500

5. **Re-upload arquivo fresco do local**
   ```bash
   scp canvas-templates.php user@host:/path/
   ```
   **Resultado:** Ainda HTTP 500

## ESTRUTURA DO ARQUIVO PROBLEMÁTICO

### canvas-templates.php (7052 bytes)

```php
<?php
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../config/config.php';

session_name(SESSION_NAME);
session_start();

use Sunyata\Core\Database;

require_login();

// Admin check
if (!isset($_SESSION['user']['access_level']) || $_SESSION['user']['access_level'] !== 'admin') {
    $_SESSION['error'] = 'Acesso negado...';
    redirect(BASE_URL . '/dashboard.php');
}

$db = Database::getInstance();

// Stats for admin-header.php (ADICIONADO PARA FIX)
try {
    $result = $db->fetchOne("SELECT COUNT(*) as count FROM vertical_access_requests WHERE status = 'pending'");
    $stats['pending_requests'] = $result['count'] ?? 0;
} catch (Exception $e) {
    $stats['pending_requests'] = 0;
}

// Buscar todos os Canvas Templates
$canvasTemplates = $db->fetchAll("SELECT id, slug, name, vertical, max_questions, is_active, created_at, updated_at FROM canvas_templates ORDER BY vertical ASC, name ASC");

// Contar conversas por canvas
$canvasStats = [];
foreach ($canvasTemplates as $canvas) {
    $stats = $db->fetchOne("SELECT COUNT(*) as total_conversations FROM conversations WHERE canvas_id = :canvas_id", ['canvas_id' => $canvas['id']]);
    $canvasStats[$canvas['id']] = $stats['total_conversations'] ?? 0;
}

$pageTitle = 'Canvas Templates';

// Include header
include __DIR__ . '/../../src/views/admin-header.php';
?>

<!-- HTML com cards de Canvas -->
<!-- ... -->

<?php include __DIR__ . '/../../src/views/admin-footer.php'; ?>
```

### Comparação com index.php que FUNCIONA

**Diferenças estruturais:**

| Aspecto | index.php (✅ funciona) | canvas-templates.php (❌ erro 500) |
|---------|-------------------------|-------------------------------------|
| Use statements | Database, Settings, ClaudeService | Somente Database |
| $stats definição | Define $stats['pending_requests'] na linha 77 | Define $stats['pending_requests'] na linha 27 (adicionado) |
| Queries antes header | Várias queries (users, stats, API) | 2 queries (canvas_templates, conversations) |
| $pageTitle | Define antes de include header | Define antes de include header |
| Tamanho arquivo | ~15KB | 7KB |

**Similaridades:**
- Ambos incluem mesmo autoload, config
- Ambos usam session_name(SESSION_NAME)
- Ambos usam require_login()
- Ambos verificam admin access
- Ambos incluem admin-header.php

## HIPÓTESES (NÃO CONFIRMADAS)

### Hipótese 1: Loop infinito no foreach
Linha 42-47 de canvas-templates.php:
```php
foreach ($canvasTemplates as $canvas) {
    $stats = $db->fetchOne(...); // SOBRESCREVE $stats anterior!
    $canvasStats[$canvas['id']] = $stats['total_conversations'] ?? 0;
}
```
**Problema:** Variável `$stats` é reutilizada (conflito com `$stats['pending_requests']`)

### Hipótese 2: Admin-header.php falhando
Arquivo: `src/views/admin-header.php` (linhas 12-15):
```php
if (isset($stats['pending_requests'])) {
    $pending_requests = $stats['pending_requests'];
} else {
    $db_temp = Database::getInstance();
    $result = $db_temp->fetchOne("SELECT COUNT(*) as count FROM vertical_access_requests WHERE status = 'pending'");
    $pending_requests = $result['count'] ?? 0;
}
```
**Problema:** Se $stats foi sobrescrito, cai no else e talvez dê erro

### Hipótese 3: Cache do LiteSpeed
O LiteSpeed pode estar cacheando a versão quebrada do arquivo.

### Hipótese 4: Configuração PHP diferente para /admin/
Possível .htaccess ou configuração do LiteSpeed específica para /admin/ que causa o erro.

### Hipótese 5: Erro fatal não logado
PHP em produção pode estar gerando erro fatal que não aparece em nenhum log acessível.

## ARQUIVOS RELEVANTES NO SERVIDOR

```
/home/u202164171/domains/sunyataconsulting.com/public_html/plataforma-sunyata/
├── public/
│   └── admin/
│       ├── canvas-templates.php (❌ HTTP 500)
│       ├── canvas-edit.php (❌ presumivelmente)
│       ├── index.php (✅ OK)
│       ├── users.php (✅ OK)
│       ├── ERRO.php (❌ HTTP 500, página branca)
│       ├── info.php (✅ OK)
│       ├── test-canvas.php (✅ OK - só queries)
│       └── .htaccess (REMOVIDO - estava quebrando)
├── config/
│   ├── config.php (CORRIGIDO - removido redefinição constantes)
│   ├── secrets.php (define DB_*, GOOGLE_*)
│   └── database.local.php (NÃO existe em prod)
├── src/
│   └── views/
│       ├── admin-header.php (usado por TODOS os admin)
│       └── admin-footer.php
└── database/
    └── migrations/
        └── 004_mvp_console.sql (APLICADO com sucesso)
```

## TABELAS CRIADAS (migration 004_mvp_console.sql)

```sql
✅ canvas_templates (1 registro: juridico-geral)
✅ conversations (0 registros)
✅ conversation_messages (0 registros)
✅ user_files (0 registros)
✅ conversation_files (0 registros)
```

## LOGS E DEBUGGING

### Erros conhecidos (warnings, não fatais):
```
PHP Warning: Constant DB_HOST already defined in config.php on line 48
PHP Warning: Constant DB_NAME already defined in config.php on line 49
...
```
**Status:** CORRIGIDO em config.php (mas erro 500 persiste)

### Logs de erro não acessíveis
Tentamos acessar:
- `/home/u202164171/domains/sunyataconsulting.com/logs/error.log` - não existe
- `/home/u202164171/logs/error_log` - não existe
- `find /home/u202164171 -name 'error*.log'` - nenhum encontrado

**Conclusão:** Hostinger não dá acesso aos logs de erro do PHP via SSH.

## O QUE PRECISA SER INVESTIGADO

1. **Por que ERRO.php (debug extremamente simples) também dá HTTP 500 em branco?**
   - Se até phpinfo() funciona mas ERRO.php não, o problema pode estar no autoload ou config.php

2. **Existe algum .htaccess oculto ou configuração LiteSpeed específica para /admin/?**
   - Verificar via painel Hostinger

3. **Há alguma whitelist/blacklist de arquivos PHP no LiteSpeed?**
   - canvas-templates.php pode estar bloqueado por algum motivo

4. **O problema está no nome do arquivo?**
   - Tentar renomear para outro nome (ex: `canvas-list.php`)

5. **Loop infinito ou timeout?**
   - max_execution_time é 360s, mas talvez haja timeout menor no LiteSpeed

6. **Bug no admin-header.php específico para esta página?**
   - Testar criar página admin SEM incluir header/footer

## PRÓXIMOS PASSOS SUGERIDOS

### Teste 1: Página admin mínima SEM header
```php
<?php
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../config/config.php';
session_name(SESSION_NAME);
session_start();
use Sunyata\Core\Database;
require_login();

echo "<!DOCTYPE html><html><body>";
echo "<h1>Teste Mínimo Admin</h1>";
echo "<p>Se você vê isto, o problema está no header ou no código depois dele.</p>";
echo "</body></html>";
```
Salvar como `minimal-test.php` e testar.

### Teste 2: Renomear canvas-templates.php
```bash
mv canvas-templates.php canvas-list.php
```
Testar se `canvas-list.php` funciona (pode ser filtro de nome de arquivo).

### Teste 3: Corrigir bug de sobrescrita de $stats
No canvas-templates.php linha 42-47:
```php
// ANTES (bug potencial):
foreach ($canvasTemplates as $canvas) {
    $stats = $db->fetchOne(...); // SOBRESCREVE $stats!
}

// DEPOIS (corrigido):
foreach ($canvasTemplates as $canvas) {
    $statsResult = $db->fetchOne(...); // Usa nome diferente
    $canvasStats[$canvas['id']] = $statsResult['total_conversations'] ?? 0;
}
```

### Teste 4: Acessar logs via painel Hostinger
- Login no painel Hostinger
- Procurar seção "Error Logs" ou "PHP Logs"
- Verificar logs em tempo real ao acessar a página

### Teste 5: Habilitar display_errors via ini_set no arquivo
Adicionar no topo de canvas-templates.php (TEMPORÁRIO):
```php
<?php
ini_set('display_errors', '1');
error_reporting(E_ALL);
// resto do código...
```

## RESUMO EXECUTIVO

**Problema:** Arquivo `canvas-templates.php` retorna HTTP 500 em produção, mas roda sem erro fatal via CLI.

**Sintoma único:** Página em branco (sem mensagem de erro, sem HTML).

**Contexto:** Outros arquivos admin no mesmo diretório funcionam perfeitamente (index.php, users.php).

**Tentativas frustradas:**
- ✅ Sintaxe OK
- ✅ Permissões OK
- ✅ Database OK
- ✅ Corrigido redefinição constantes
- ✅ Limpado cache
- ✅ Re-upload arquivo
- ❌ Ainda HTTP 500

**Suspeita principal:** Bug de sobrescrita da variável `$stats` dentro do foreach (linha 44) que interfere com `$stats['pending_requests']` esperado pelo admin-header.php.

**Bloqueador crítico:** Não temos acesso aos logs de erro do PHP no servidor Hostinger para ver o erro real.

---

**Para a próxima IA:** Por favor, investigue com acesso ao ambiente de produção e painel Hostinger para acessar logs reais de erro PHP.
