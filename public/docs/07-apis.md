# 🔌 APIs

> **Status:** ✅ Documentação completa dos endpoints implementados e planejados

Esta seção documenta todos os endpoints da API REST da Plataforma Sunyata.

---

## 📋 Visão Geral

A API da Plataforma Sunyata segue os princípios REST e retorna respostas em formato JSON.

### Características

- ✅ **Autenticação:** Sessão PHP + Google OAuth
- ✅ **Autorização:** Ownership checks em todos os endpoints
- ✅ **CSRF Protection:** Token obrigatório em operações de escrita
- ✅ **Rate Limiting:** Limites por endpoint e usuário
- ✅ **Error Handling:** Códigos HTTP padronizados + mensagens user-friendly

### Base URL

```
https://portal.sunyataconsulting.com/plataforma-sunyata/api/
```

---

## 🔐 Autenticação

Todos os endpoints requerem autenticação via sessão PHP.

### Headers Obrigatórios

```http
Cookie: PHPSESSID=<session_id>
X-CSRF-Token: <csrf_token>  (apenas para POST/PUT/DELETE)
```

### Obter CSRF Token

O token CSRF está disponível em:
```php
$_SESSION['csrf_token']
```

No frontend:
```javascript
const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
```

---

## 📤 POST /api/upload-file.php

Upload de arquivo com processamento automático (extração de texto).

### Request

**Headers:**
```http
Content-Type: multipart/form-data
X-CSRF-Token: <csrf_token>
```

**Body (multipart/form-data):**
```
file: <arquivo>  (PDF ou DOCX, máx 10MB)
```

### Response (200 OK)

```json
{
  "success": true,
  "file_id": 123,
  "filename": "documento.pdf",
  "original_name": "Relatório Anual 2024.pdf",
  "file_size": 2048576,
  "mime_type": "application/pdf",
  "extracted_text": "Conteúdo extraído do documento...",
  "upload_date": "2025-10-22 14:30:00"
}
```

### Errors

| Código | Mensagem | Descrição |
|--------|----------|-----------|
| 400 | `No file uploaded` | Nenhum arquivo foi enviado |
| 400 | `Invalid file type` | Tipo de arquivo não permitido (apenas PDF/DOCX) |
| 413 | `File too large` | Arquivo excede 10MB |
| 429 | `Rate limit exceeded` | Limite de 10 uploads/hora excedido |
| 500 | `Upload failed` | Erro interno no processamento |

### Exemplo de Uso

```javascript
async function uploadFile(file) {
  const formData = new FormData();
  formData.append('file', file);
  
  const response = await fetch('/api/upload-file.php', {
    method: 'POST',
    headers: {
      'X-CSRF-Token': csrfToken
    },
    body: formData
  });
  
  if (!response.ok) {
    const error = await response.json();
    throw new Error(error.message);
  }
  
  return await response.json();
}
```

### Rate Limiting

- **Limite:** 10 uploads por hora por usuário
- **Janela:** Sliding window de 60 minutos
- **Reset:** Automático após 1 hora do primeiro upload

---

## 💬 POST /api/chat.php

Enviar mensagem para o Claude AI e receber resposta (com streaming opcional).

### Request

**Headers:**
```http
Content-Type: application/json
X-CSRF-Token: <csrf_token>
```

**Body (JSON):**
```json
{
  "conversation_id": 456,
  "message": "Analise este documento e resuma os principais pontos",
  "file_ids": [123, 124],
  "stream": true
}
```

**Parâmetros:**
- `conversation_id` (opcional): ID da conversa existente. Se omitido, cria nova conversa.
- `message` (obrigatório): Mensagem do usuário (máx 65.000 caracteres)
- `file_ids` (opcional): Array de IDs de arquivos anexados
- `stream` (opcional): Se `true`, retorna resposta em streaming (padrão: `false`)

### Response (200 OK) - Modo Normal

```json
{
  "success": true,
  "conversation_id": 456,
  "message_id": 789,
  "response": "Aqui está o resumo do documento:\n\n1. Ponto principal 1...",
  "tokens_used": 1250,
  "processing_time": 3.2
}
```

### Response (200 OK) - Modo Streaming

```
Content-Type: text/event-stream

data: {"type":"start","conversation_id":456}

data: {"type":"chunk","content":"Aqui"}

data: {"type":"chunk","content":" está"}

data: {"type":"chunk","content":" o resumo..."}

data: {"type":"end","message_id":789,"tokens_used":1250}
```

### Errors

| Código | Mensagem | Descrição |
|--------|----------|-----------|
| 400 | `Message is required` | Mensagem vazia |
| 400 | `Message too long` | Mensagem excede 65.000 caracteres |
| 403 | `File access denied` | Usuário não tem acesso aos arquivos anexados |
| 404 | `Conversation not found` | Conversa não existe ou não pertence ao usuário |
| 429 | `Rate limit exceeded` | Limite de 30 mensagens/hora excedido |
| 500 | `Claude API error` | Erro na comunicação com Claude API |
| 504 | `Request timeout` | Claude API não respondeu em 30s |

### Exemplo de Uso (Normal)

```javascript
async function sendMessage(conversationId, message, fileIds = []) {
  const response = await fetch('/api/chat.php', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-Token': csrfToken
    },
    body: JSON.stringify({
      conversation_id: conversationId,
      message: message,
      file_ids: fileIds
    })
  });
  
  return await response.json();
}
```

### Exemplo de Uso (Streaming)

```javascript
async function sendMessageStreaming(conversationId, message, onChunk) {
  const response = await fetch('/api/chat.php', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-Token': csrfToken
    },
    body: JSON.stringify({
      conversation_id: conversationId,
      message: message,
      stream: true
    })
  });
  
  const reader = response.body.getReader();
  const decoder = new TextDecoder();
  
  while (true) {
    const {done, value} = await reader.read();
    if (done) break;
    
    const chunk = decoder.decode(value);
    const lines = chunk.split('\n');
    
    for (const line of lines) {
      if (line.startsWith('data: ')) {
        const data = JSON.parse(line.substring(6));
        if (data.type === 'chunk') {
          onChunk(data.content);
        }
      }
    }
  }
}
```

### Rate Limiting

- **Limite:** 30 mensagens por hora por usuário
- **Janela:** Sliding window de 60 minutos
- **Prioridade:** Conversas com arquivos anexados têm prioridade

---

## 📥 GET /api/export-conversation.php

Exportar conversa para PDF.

### Request

**Headers:**
```http
Cookie: PHPSESSID=<session_id>
```

**Query Parameters:**
```
?conversation_id=456
```

### Response (200 OK)

```
Content-Type: application/pdf
Content-Disposition: attachment; filename="conversa-456.pdf"

<binary PDF data>
```

### Errors

| Código | Mensagem | Descrição |
|--------|----------|-----------|
| 400 | `Conversation ID required` | Parâmetro `conversation_id` ausente |
| 403 | `Access denied` | Conversa não pertence ao usuário |
| 404 | `Conversation not found` | Conversa não existe |
| 500 | `PDF generation failed` | Erro ao gerar PDF |

### Exemplo de Uso

```javascript
function exportConversation(conversationId) {
  window.location.href = `/api/export-conversation.php?conversation_id=${conversationId}`;
}
```

---

## 📊 GET /api/conversations.php

Listar conversas do usuário.

### Request

**Headers:**
```http
Cookie: PHPSESSID=<session_id>
```

**Query Parameters (opcionais):**
```
?area=juridico&limit=20&offset=0
```

**Parâmetros:**
- `area` (opcional): Filtrar por área (docencia, pesquisa, juridico)
- `limit` (opcional): Número de resultados (padrão: 20, máx: 100)
- `offset` (opcional): Offset para paginação (padrão: 0)

### Response (200 OK)

```json
{
  "success": true,
  "conversations": [
    {
      "id": 456,
      "title": "Análise de Contrato",
      "area": "juridico",
      "message_count": 12,
      "file_count": 3,
      "created_at": "2025-10-20 10:00:00",
      "updated_at": "2025-10-22 14:30:00",
      "last_message_preview": "Aqui está o resumo do contrato..."
    },
    {
      "id": 457,
      "title": "Pesquisa sobre IA",
      "area": "pesquisa",
      "message_count": 8,
      "file_count": 2,
      "created_at": "2025-10-21 09:00:00",
      "updated_at": "2025-10-21 15:45:00",
      "last_message_preview": "Os principais achados são..."
    }
  ],
  "total": 45,
  "limit": 20,
  "offset": 0
}
```

### Exemplo de Uso

```javascript
async function listConversations(area = null, page = 0) {
  const limit = 20;
  const offset = page * limit;
  
  let url = `/api/conversations.php?limit=${limit}&offset=${offset}`;
  if (area) {
    url += `&area=${area}`;
  }
  
  const response = await fetch(url);
  return await response.json();
}
```

---

## 🗑️ DELETE /api/conversation.php

Deletar conversa (soft delete).

### Request

**Headers:**
```http
Content-Type: application/json
X-CSRF-Token: <csrf_token>
```

**Body (JSON):**
```json
{
  "conversation_id": 456
}
```

### Response (200 OK)

```json
{
  "success": true,
  "message": "Conversa deletada com sucesso"
}
```

### Errors

| Código | Mensagem | Descrição |
|--------|----------|-----------|
| 400 | `Conversation ID required` | Parâmetro ausente |
| 403 | `Access denied` | Conversa não pertence ao usuário |
| 404 | `Conversation not found` | Conversa não existe |
| 500 | `Delete failed` | Erro ao deletar |

---

## 🔄 Códigos de Status HTTP

| Código | Significado | Uso |
|--------|-------------|-----|
| 200 | OK | Requisição bem-sucedida |
| 400 | Bad Request | Parâmetros inválidos ou ausentes |
| 401 | Unauthorized | Usuário não autenticado |
| 403 | Forbidden | Usuário não tem permissão (ownership check) |
| 404 | Not Found | Recurso não encontrado |
| 413 | Payload Too Large | Arquivo muito grande |
| 429 | Too Many Requests | Rate limit excedido |
| 500 | Internal Server Error | Erro interno do servidor |
| 504 | Gateway Timeout | Timeout na comunicação com serviço externo |

---

## 📝 Formato de Erro Padrão

Todos os endpoints retornam erros no seguinte formato:

```json
{
  "success": false,
  "error": "Código do erro",
  "message": "Mensagem user-friendly em português",
  "details": {
    "field": "nome_do_campo",
    "constraint": "max_length"
  }
}
```

**Exemplo:**
```json
{
  "success": false,
  "error": "FILE_TOO_LARGE",
  "message": "O arquivo excede o tamanho máximo de 10MB",
  "details": {
    "max_size": 10485760,
    "actual_size": 15728640
  }
}
```

---

## 🔒 Segurança

### CSRF Protection

Todos os endpoints de escrita (POST/PUT/DELETE) exigem token CSRF:

```javascript
// Obter token
const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

// Enviar em header
headers: {
  'X-CSRF-Token': csrfToken
}
```

### Ownership Checks

Todos os endpoints verificam se o recurso pertence ao usuário autenticado:

```php
// Exemplo interno
if ($conversation['user_id'] !== $_SESSION['user_id']) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Acesso negado']);
    exit;
}
```

### Rate Limiting

Limites por endpoint:

| Endpoint | Limite | Janela |
|----------|--------|--------|
| `/api/upload-file.php` | 10 req | 1 hora |
| `/api/chat.php` | 30 req | 1 hora |
| `/api/conversations.php` | 100 req | 1 hora |
| `/api/export-conversation.php` | 20 req | 1 hora |

---

## 🧪 Testando a API

### Com cURL

```bash
# Upload de arquivo
curl -X POST https://portal.sunyataconsulting.com/plataforma-sunyata/api/upload-file.php \
  -H "X-CSRF-Token: YOUR_TOKEN" \
  -H "Cookie: PHPSESSID=YOUR_SESSION" \
  -F "file=@documento.pdf"

# Enviar mensagem
curl -X POST https://portal.sunyataconsulting.com/plataforma-sunyata/api/chat.php \
  -H "Content-Type: application/json" \
  -H "X-CSRF-Token: YOUR_TOKEN" \
  -H "Cookie: PHPSESSID=YOUR_SESSION" \
  -d '{"message":"Olá, Claude!","conversation_id":456}'
```

### Com Postman

1. Criar coleção "Plataforma Sunyata API"
2. Adicionar variável de ambiente `baseUrl` = `https://portal.sunyataconsulting.com/plataforma-sunyata/api`
3. Configurar autenticação:
   - Fazer login no navegador
   - Copiar cookie `PHPSESSID`
   - Copiar CSRF token da página
4. Adicionar headers em todas as requisições:
   - `Cookie: PHPSESSID=...`
   - `X-CSRF-Token: ...` (para POST/PUT/DELETE)

---

## 📚 Próximos Passos

Após ler esta documentação:

1. Veja [Fluxos Principais](05-fluxos.md) para entender como as APIs se integram
2. Consulte [Troubleshooting](12-troubleshooting.md) para problemas comuns
3. Leia [Segurança](08-seguranca.md) para práticas de segurança

---

**Última atualização:** 2025-10-22  
**Versão da API:** v2.0

