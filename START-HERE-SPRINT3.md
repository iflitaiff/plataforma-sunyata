# 🚀 COMEÇAR AQUI - Sprint 3

## ✅ Status Atual (2025-10-21)
- ✅ Sprint 1: Admin Canvas CRUD completo
- ✅ Sprint 2: Services Layer completo (FileUpload, DocumentProcessor, Conversation, Claude)
- ⏳ Sprint 3: APIs **← VOCÊ ESTÁ AQUI**

---

## 🎯 Sprint 3: APIs (Estimativa: 1 dia / 6-8h)

### Objetivo
Criar endpoints de API REST para conectar o frontend com os Services criados no Sprint 2.

---

## 📋 TAREFAS

### Task 3.1: `/api/upload-file.php` (2h)
**Fazer:**
- Receber upload de arquivo via POST multipart/form-data
- Validar autenticação (usuário logado)
- Chamar FileUploadService::uploadFile()
- Processar arquivo via DocumentProcessorService::processFile()
- Retornar JSON com file_id e extracted_text

**Endpoint:**
```
POST /api/upload-file.php
Content-Type: multipart/form-data

Parameters:
- file: arquivo (PDF ou DOCX)

Response:
{
  "success": true,
  "file_id": 123,
  "filename": "documento.pdf",
  "text_length": 5432
}
```

---

### Task 3.2: `/api/chat.php` (3h)
**Fazer:**
- Receber mensagem do usuário via POST JSON
- Validar autenticação
- Se primeira mensagem: criar conversa via ConversationService
- Buscar histórico de mensagens
- Formatar mensagens para Claude API
- Chamar ClaudeService::generateWithContext()
- Salvar resposta do Claude
- Detectar [RESPOSTA-FINAL] e completar conversa
- Retornar JSON com resposta

**Endpoint:**
```
POST /api/chat.php
Content-Type: application/json

Body:
{
  "conversation_id": 123, // opcional (null se primeira mensagem)
  "canvas_id": 1,
  "message": "Minha resposta aqui",
  "message_type": "answer" // form_submission | answer
}

Response:
{
  "success": true,
  "conversation_id": 123,
  "message_id": 456,
  "response": "Resposta do Claude...",
  "message_type": "question", // question | final_answer
  "is_complete": false
}
```

---

### Task 3.3: `/api/export-conversation.php` (1h)
**Fazer:**
- Receber conversation_id via GET
- Validar autenticação e ownership
- Buscar conversa completa via ConversationService
- Gerar PDF com mPDF
- Retornar arquivo para download

**Endpoint:**
```
GET /api/export-conversation.php?conversation_id=123

Response:
- Content-Type: application/pdf
- Content-Disposition: attachment; filename="conversa-123.pdf"
- PDF binário
```

---

## 🔑 REFERÊNCIAS IMPORTANTES

### Services Disponíveis (Sprint 2)
```php
// Upload de arquivos
$fileService = FileUploadService::getInstance();
$result = $fileService->uploadFile($_FILES['file'], $userId);

// Processar documento
$docService = DocumentProcessorService::getInstance();
$docService->processFile($fileId);

// Conversas
$convService = ConversationService::getInstance();
$convId = $convService->createConversation($userId, $canvasId);
$convService->addMessage($convId, 'user', $content, 'form_submission');
$conversation = $convService->getConversation($convId, $userId);

// Claude API
$claudeService = new ClaudeService();
$response = $claudeService->generateWithContext($systemPrompt, $messages);
```

### Autenticação
```php
session_start();
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Não autenticado']);
    exit;
}
$userId = $_SESSION['user_id'];
```

### Headers JSON
```php
header('Content-Type: application/json');
```

### Error Handling Pattern
```php
try {
    // Lógica da API
    echo json_encode(['success' => true, ...]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Erro ao processar requisição',
        'detail' => $e->getMessage()
    ]);
}
```

---

## 📁 Estrutura a Criar

```
public/
└── api/
    ├── upload-file.php      (Task 3.1)
    ├── chat.php             (Task 3.2)
    └── export-conversation.php (Task 3.3)
```

---

## ⚡ Quick Start

```bash
cd /home/iflitaiff/projetos/plataforma-sunyata
git status
git pull origin feature/mvp-admin-canvas

# Criar diretório de APIs
mkdir -p public/api

# Começar com Task 3.1
# Criar public/api/upload-file.php
```

---

## 🎯 Definition of Done

### upload-file.php
- [ ] Aceita POST multipart/form-data
- [ ] Valida autenticação
- [ ] Usa FileUploadService para salvar arquivo
- [ ] Usa DocumentProcessorService para extrair texto
- [ ] Retorna JSON com file_id
- [ ] Tratamento de erros (arquivo muito grande, tipo inválido)

### chat.php
- [ ] Aceita POST JSON
- [ ] Valida autenticação
- [ ] Cria conversa se for primeira mensagem
- [ ] Busca histórico de mensagens
- [ ] Chama Claude API com contexto
- [ ] Salva resposta do Claude
- [ ] Detecta [RESPOSTA-FINAL] e completa conversa
- [ ] Retorna JSON com resposta

### export-conversation.php
- [ ] Aceita GET com conversation_id
- [ ] Valida autenticação e ownership
- [ ] Busca conversa completa
- [ ] Gera PDF com mPDF
- [ ] Headers corretos (Content-Type, Content-Disposition)
- [ ] Download funciona

---

## 📚 Documentação

- **Sprint 2 completo:** SPRINT-2-COMPLETO.md
- **Context detalhado:** CONTEXTO-PROXIMO-SPRINT.md (ainda válido para APIs)
- **Backlog:** BACKLOG.md (o que NÃO fazer agora)

---

## 🚨 Armadilhas a Evitar

1. **CORS:** Não necessário (mesmo domínio)
2. **CSRF:** Adicionar tokens depois se necessário (backlog)
3. **Rate limiting:** Simples por enquanto (backlog)
4. **Validação complexa:** Manual simples (MVP-first)
5. **Logs estruturados:** error_log() por enquanto (backlog)

---

## 🎉 Após Sprint 3

**Próximo:** Sprint 4 - Frontend Console
- /areas/juridico/console.php (interface usuário)
- /assets/js/console.js (JavaScript para chat)
- Integração SurveyJS

**Meta:** MVP funcional completo em 7 dias!

---

**Última atualização:** 2025-10-21
**Commit anterior:** ceb5b4e (Sprint 2 completo)
**Próximo commit:** APIs funcionando
