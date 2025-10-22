# 🔧 Troubleshooting

> Problemas comuns e soluções para a Plataforma Sunyata

---

## 📋 Índice Rápido

- [Autenticação](#autenticação)
- [Upload de Arquivos](#upload-de-arquivos)
- [Chat com IA](#chat-com-ia)
- [Exportação de PDF](#exportação-de-pdf)
- [Performance](#performance)
- [Erros do Servidor](#erros-do-servidor)

---

## 🔐 Autenticação

### Problema: "Erro ao fazer login com Google"

**Sintomas:**
- Redirecionamento para Google funciona
- Após autorizar, retorna erro "Failed to authenticate"

**Causas Possíveis:**
1. GOOGLE_CLIENT_ID ou GOOGLE_CLIENT_SECRET incorretos
2. Redirect URI não configurado no Google Console
3. API Google OAuth não habilitada

**Solução:**

```bash
# 1. Verificar secrets.php
cat config/secrets.php | grep GOOGLE

# 2. Verificar se redirect URI está correto
# Deve ser: https://portal.sunyataconsulting.com/plataforma-sunyata/callback.php

# 3. Verificar logs do servidor
tail -f /var/log/apache2/error.log
```

**Checklist:**
- [ ] GOOGLE_CLIENT_ID está correto
- [ ] GOOGLE_CLIENT_SECRET está correto
- [ ] Redirect URI configurado no Google Console
- [ ] API Google OAuth habilitada no projeto

---

### Problema: "Sessão expira muito rápido"

**Sintomas:**
- Usuário é deslogado após alguns minutos de inatividade

**Causa:**
- Configuração padrão do PHP (`session.gc_maxlifetime` = 1440s = 24min)

**Solução:**

```php
// config/config.php
ini_set('session.gc_maxlifetime', 86400); // 24 horas
session_set_cookie_params(86400); // 24 horas
```

---

## 📤 Upload de Arquivos

### Problema: "Upload falhou - Arquivo muito grande"

**Sintomas:**
- Erro 413 ao fazer upload
- Mensagem: "File too large"

**Causa:**
- Arquivo excede 10MB (limite do FileUploadService)
- OU arquivo excede limites do PHP/Apache

**Solução:**

```bash
# 1. Verificar limites do PHP
php -i | grep -E 'upload_max_filesize|post_max_size'

# 2. Se necessário, aumentar limites (php.ini)
upload_max_filesize = 20M
post_max_size = 20M

# 3. Reiniciar servidor
sudo systemctl restart apache2
```

**Alternativa para usuário:**
- Comprimir arquivo antes de upload
- Dividir documento em partes menores

---

### Problema: "Extração de texto falhou"

**Sintomas:**
- Upload bem-sucedido mas `extracted_text` está vazio
- Erro 500 durante processamento

**Causas Possíveis:**
1. PDF protegido por senha
2. PDF escaneado (imagem, não texto)
3. DOCX corrompido
4. `pdftotext` não instalado

**Solução:**

```bash
# 1. Verificar se pdftotext está instalado
which pdftotext
# Se não: sudo apt-get install poppler-utils

# 2. Testar extração manualmente
pdftotext /path/to/file.pdf -

# 3. Verificar logs
tail -f storage/logs/document-processor.log
```

**Para PDFs escaneados:**
- Usar OCR antes do upload (Google Drive, Adobe Acrobat)
- OU implementar OCR no servidor (Tesseract - futuro)

---

### Problema: "Rate limit excedido"

**Sintomas:**
- Erro 429 ao fazer upload
- Mensagem: "Limite de uploads excedido (10/hora)"

**Causa:**
- Usuário fez 10 uploads na última hora

**Solução:**

```sql
-- Verificar uploads recentes
SELECT COUNT(*) FROM files 
WHERE user_id = 123 
AND upload_date > DATE_SUB(NOW(), INTERVAL 1 HOUR);

-- Se necessário, resetar manualmente (apenas admin)
UPDATE files 
SET upload_date = DATE_SUB(upload_date, INTERVAL 2 HOUR)
WHERE user_id = 123;
```

**Alternativa:**
- Aguardar 1 hora
- Anexar múltiplos arquivos a uma única conversa (não conta como uploads separados)

---

### Problema: "Permissão negada ao salvar arquivo"

**Sintomas:**
- Erro 500 ao fazer upload
- Log: "Permission denied: /storage/uploads/..."

**Causa:**
- Diretório `/storage/uploads` não tem permissões corretas

**Solução:**

```bash
# 1. Verificar permissões
ls -la /home/u202164171/domains/sunyataconsulting.com/storage/uploads

# 2. Corrigir permissões
chmod 755 /home/u202164171/domains/sunyataconsulting.com/storage/uploads

# 3. Verificar ownership
chown u202164171:o1006921199 /home/u202164171/domains/sunyataconsulting.com/storage/uploads
```

---

## 💬 Chat com IA

### Problema: "Claude API timeout"

**Sintomas:**
- Erro 504 ao enviar mensagem
- Mensagem: "Request timeout"

**Causa:**
- Claude API não respondeu em 30s
- Geralmente por mensagem muito longa ou contexto muito grande

**Solução:**

```php
// Aumentar timeout (ClaudeService.php)
$this->client = new GuzzleHttp\Client([
    'timeout' => 60, // Aumentar para 60s
    'connect_timeout' => 10
]);
```

**Alternativa:**
- Reduzir tamanho da mensagem
- Anexar menos documentos
- Dividir pergunta em partes menores

---

### Problema: "Resposta vazia do Claude"

**Sintomas:**
- Chat retorna 200 OK mas resposta está vazia
- Frontend mostra bolha vazia

**Causas Possíveis:**
1. CLAUDE_API_KEY inválida ou expirada
2. Limite de tokens excedido
3. Filtro de conteúdo bloqueou resposta

**Solução:**

```bash
# 1. Verificar API key
cat config/secrets.php | grep CLAUDE_API_KEY

# 2. Testar API key manualmente
curl https://api.anthropic.com/v1/messages \
  -H "x-api-key: $CLAUDE_API_KEY" \
  -H "anthropic-version: 2023-06-01" \
  -H "content-type: application/json" \
  -d '{"model":"claude-3-5-sonnet-20241022","max_tokens":1024,"messages":[{"role":"user","content":"Hello"}]}'

# 3. Verificar logs
tail -f storage/logs/claude-api.log
```

---

### Problema: "Arquivo anexado não aparece no contexto"

**Sintomas:**
- Arquivo foi anexado mas Claude não menciona conteúdo
- Resposta genérica sem referência ao documento

**Causas Possíveis:**
1. Ownership check falhou (arquivo não pertence ao usuário)
2. Texto extraído está vazio
3. Contexto foi truncado

**Solução:**

```sql
-- 1. Verificar se arquivo foi processado
SELECT id, filename, LENGTH(extracted_text) as text_length
FROM files
WHERE id = 123;

-- 2. Verificar ownership
SELECT f.*, c.user_id as conversation_user_id
FROM files f
JOIN conversation_files cf ON f.id = cf.file_id
JOIN conversations c ON cf.conversation_id = c.id
WHERE f.id = 123;
```

**Se `text_length` = 0:**
- Reprocessar arquivo (ver seção "Extração de texto falhou")

---

## 📥 Exportação de PDF

### Problema: "PDF gerado está vazio"

**Sintomas:**
- Download funciona mas PDF não tem conteúdo
- Apenas header aparece

**Causa:**
- Conversa não tem mensagens
- OU erro no template HTML

**Solução:**

```sql
-- Verificar se conversa tem mensagens
SELECT COUNT(*) FROM messages WHERE conversation_id = 456;
```

**Se COUNT = 0:**
- Conversa está vazia, não há nada para exportar

**Se COUNT > 0:**
- Verificar logs de geração de PDF
- Testar template HTML manualmente

---

### Problema: "Erro ao gerar PDF - mPDF"

**Sintomas:**
- Erro 500 ao exportar
- Log: "mPDF error: ..."

**Causas Possíveis:**
1. Biblioteca mPDF não instalada
2. Memória PHP insuficiente
3. Caracteres especiais no conteúdo

**Solução:**

```bash
# 1. Verificar se mPDF está instalado
composer show | grep mpdf

# 2. Se não: instalar
composer require mpdf/mpdf

# 3. Aumentar memória PHP (php.ini)
memory_limit = 256M

# 4. Reiniciar servidor
sudo systemctl restart apache2
```

---

## ⚡ Performance

### Problema: "Dashboard demora muito para carregar"

**Sintomas:**
- Página leva >5s para carregar
- Muitas conversas no banco

**Causa:**
- Query sem índice
- Muitas conversas (>100)

**Solução:**

```sql
-- 1. Adicionar índice
CREATE INDEX idx_conversations_user_created ON conversations(user_id, created_at DESC);

-- 2. Implementar paginação (já implementado em /api/conversations.php)
-- Usar limit=20&offset=0

-- 3. Verificar query plan
EXPLAIN SELECT * FROM conversations WHERE user_id = 123 ORDER BY created_at DESC LIMIT 20;
```

---

### Problema: "Chat está lento (>10s para responder)"

**Sintomas:**
- Demora muito entre enviar mensagem e receber resposta

**Causas Possíveis:**
1. Claude API lenta (normal para respostas longas)
2. Contexto muito grande
3. Histórico muito longo

**Solução:**

```php
// 1. Limitar histórico (ConversationService.php)
private function getConversationHistory(int $conversationId, int $limit = 10): array {
    return $this->db->query(
        "SELECT * FROM messages 
         WHERE conversation_id = ? 
         ORDER BY created_at DESC 
         LIMIT ?",
        [$conversationId, $limit]
    );
}

// 2. Truncar contexto de documentos
private function truncateContext(string $text, int $maxChars = 50000): string {
    if (strlen($text) <= $maxChars) {
        return $text;
    }
    
    $half = $maxChars / 2;
    return substr($text, 0, $half) . "\n\n[...]\n\n" . substr($text, -$half);
}
```

---

## 🚨 Erros do Servidor

### Problema: "Erro 500 - Internal Server Error"

**Sintomas:**
- Página branca
- Erro 500 no console do navegador

**Primeiros Passos:**

```bash
# 1. Verificar logs do Apache
tail -f /var/log/apache2/error.log

# 2. Verificar logs do PHP
tail -f /var/log/php/error.log

# 3. Habilitar display_errors (apenas em dev!)
# php.ini
display_errors = On
error_reporting = E_ALL
```

**Erros Comuns:**

| Erro no Log | Causa | Solução |
|-------------|-------|---------|
| `Class not found` | Autoload não configurado | `composer dump-autoload` |
| `Call to undefined function` | Extensão PHP faltando | `sudo apt-get install php-<extensao>` |
| `Permission denied` | Permissões de arquivo | `chmod 644 <arquivo>` |
| `Connection refused` | Banco de dados offline | `sudo systemctl start mariadb` |

---

### Problema: "Erro 403 - Forbidden"

**Sintomas:**
- Acesso negado a recurso
- Mensagem: "Access denied"

**Causas Possíveis:**
1. Ownership check falhou (recurso não pertence ao usuário)
2. CSRF token inválido
3. Sessão expirada

**Solução:**

```javascript
// 1. Verificar se CSRF token está sendo enviado
console.log(document.querySelector('meta[name="csrf-token"]').content);

// 2. Verificar se sessão está ativa
fetch('/api/check-session.php')
    .then(r => r.json())
    .then(data => console.log(data));

// 3. Fazer logout e login novamente
window.location.href = '/logout.php';
```

---

### Problema: "Erro 404 - Not Found"

**Sintomas:**
- Página ou API não encontrada

**Causas Possíveis:**
1. URL incorreta
2. Arquivo não existe
3. .htaccess não configurado

**Solução:**

```bash
# 1. Verificar se arquivo existe
ls -la public/api/upload-file.php

# 2. Verificar .htaccess
cat public/.htaccess

# 3. Verificar mod_rewrite
apache2ctl -M | grep rewrite
# Se não: sudo a2enmod rewrite && sudo systemctl restart apache2
```

---

## 🔍 Debugging Avançado

### Habilitar Logs Detalhados

```php
// config/config.php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../storage/logs/php-errors.log');
```

### Verificar Variáveis de Ambiente

```php
// debug.php (criar temporariamente)
<?php
echo "PHP Version: " . phpversion() . "\n";
echo "Upload Max: " . ini_get('upload_max_filesize') . "\n";
echo "Post Max: " . ini_get('post_max_size') . "\n";
echo "Memory Limit: " . ini_get('memory_limit') . "\n";
echo "Session Path: " . session_save_path() . "\n";

// Verificar extensões
echo "\nExtensions:\n";
print_r(get_loaded_extensions());
?>
```

### Monitorar Queries SQL

```php
// Database.php - adicionar temporariamente
public function query(string $sql, array $params = []): array {
    error_log("SQL: $sql | Params: " . json_encode($params));
    // ... resto do código
}
```

---

## 📞 Suporte

Se o problema persistir após seguir este guia:

1. **Verificar COMM-BOARD:** [https://portal.sunyataconsulting.com/COMM-BOARD.html](https://portal.sunyataconsulting.com/COMM-BOARD.html)
2. **Abrir issue no GitHub:** [https://github.com/iflitaiff/plataforma-sunyata/issues](https://github.com/iflitaiff/plataforma-sunyata/issues)
3. **Contatar Filipe:** flitaiff@gmail.com

**Ao reportar problema, incluir:**
- [ ] Descrição detalhada do problema
- [ ] Passos para reproduzir
- [ ] Mensagem de erro completa
- [ ] Logs relevantes (Apache, PHP, aplicação)
- [ ] Versão da plataforma (ver [Changelog](11-changelog.md))

---

**Última atualização:** 2025-10-22  
**Mantido por:** Manus AI + Claude Code

