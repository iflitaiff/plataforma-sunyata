# 🚀 CONTEXTO PARA PRÓXIMO SPRINT - Sprint 2 (Services Layer)

## ✅ ESTADO ATUAL DO PROJETO (2025-10-21)

### O Que Está FUNCIONANDO
- ✅ **Admin Dashboard completo** (index.php, users.php, access-requests.php, audit-logs.php)
- ✅ **Canvas Templates CRUD** (canvas-templates.php, canvas-edit.php)
  - Lista Canvas
  - Edita Canvas com Monaco Editor (JSON + prompts)
  - 1 Canvas ativo: **juridico-geral**
- ✅ **Database** (5 tabelas criadas via migration 004_mvp_console.sql)
  - canvas_templates (1 registro)
  - conversations (vazio)
  - conversation_messages (vazio)
  - user_files (vazio)
  - conversation_files (vazio)
- ✅ **Sistema de autenticação** (Google OAuth)
- ✅ **Sistema de verticais** (Jurídico, Docência, Pesquisa, IFRJ, Admin)

### O Que FOI CORRIGIDO HOJE
- ✅ **3 bugs potenciais** (users.php, access-requests.php, audit-logs.php)
  - Inicialização correta de `$stats = []`
- ✅ **System prompt melhorado** (Canvas Jurídico)
  - Chain-of-thought
  - Formatação Markdown estruturada
  - Instruções sobre documentos

### O Que FALTA (MVP Incompleto)
- ❌ **Sprint 2:** Services Layer (FileUpload, DocumentProcessor, Conversation, Claude)
- ❌ **Sprint 3:** APIs (/api/upload-file.php, /api/chat.php, /api/export-conversation.php)
- ❌ **Sprint 4:** Frontend Console (interface para usuários usarem Canvas)

**Status:** 1 de 5 funcionalidades core implementadas (20% do MVP)

---

## 🎯 OBJETIVO DO SPRINT 2

**Criar camada de Services para:**
1. Upload e validação de arquivos (PDF, DOCX)
2. Extração de texto de documentos
3. Gerenciamento de conversas (CRUD)
4. Integração com Claude API (com contexto de conversa)

**Duração estimada:** 2-3 dias (8-12h)

---

## 📁 ESTRUTURA DE ARQUIVOS ATUAL

```
plataforma-sunyata/
├── config/
│   ├── config.php (CORRIGIDO - sem redefinição de constantes)
│   ├── secrets.php (credentials)
│   └── database.local.php (local dev)
├── database/
│   └── migrations/
│       └── 004_mvp_console.sql (APLICADO)
├── public/
│   ├── admin/
│   │   ├── index.php ✅
│   │   ├── users.php ✅ (CORRIGIDO)
│   │   ├── access-requests.php ✅ (CORRIGIDO)
│   │   ├── audit-logs.php ✅ (CORRIGIDO)
│   │   ├── canvas-templates.php ✅ (CORRIGIDO)
│   │   └── canvas-edit.php ✅ (CORRIGIDO)
│   └── areas/
│       └── juridico/
│           └── console.php ❌ (SPRINT 4)
├── src/
│   ├── Core/
│   │   ├── Database.php ✅
│   │   └── Settings.php ✅
│   ├── AI/
│   │   └── ClaudeService.php ✅ (precisa generateWithContext())
│   ├── Admin/
│   │   └── UserDeletionService.php ✅
│   └── Services/ ❌ (CRIAR NO SPRINT 2)
│       ├── FileUploadService.php
│       ├── DocumentProcessorService.php
│       └── ConversationService.php
├── logs/ ✅ (CRIADO HOJE)
├── BACKLOG.md ✅ (sugestões pós-MVP do Manus)
├── ANALISE-CRITICA-MANUS.md ✅
└── composer.json
```

---

## 🗄️ SCHEMA DO BANCO DE DADOS (Relevante para Sprint 2)

### Tabela: `canvas_templates`
```sql
id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY
slug VARCHAR(100) NOT NULL UNIQUE
name VARCHAR(255) NOT NULL
vertical VARCHAR(50) NOT NULL
form_config JSON NOT NULL              -- SurveyJS JSON
system_prompt TEXT NOT NULL            -- Claude system prompt
user_prompt_template TEXT NOT NULL    -- Template com {{placeholders}}
max_questions INT UNSIGNED DEFAULT 5
is_active BOOLEAN DEFAULT TRUE
created_at TIMESTAMP
updated_at TIMESTAMP
```

**Registro atual:**
- ID: 1
- Slug: juridico-geral
- Name: Canvas Jurídico Geral
- Vertical: juridico

### Tabela: `conversations`
```sql
id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
user_id INT UNSIGNED NOT NULL         -- FK users(id)
canvas_id INT UNSIGNED NOT NULL       -- FK canvas_templates(id)
title VARCHAR(255) NULL               -- Gerado depois da 1ª pergunta
status ENUM('active','completed','archived') DEFAULT 'active'
created_at TIMESTAMP
updated_at TIMESTAMP
```

### Tabela: `conversation_messages`
```sql
id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
conversation_id BIGINT UNSIGNED NOT NULL  -- FK conversations(id)
role ENUM('user','assistant') NOT NULL
content TEXT NOT NULL
message_type ENUM('question','answer','form_submission','context') NULL
created_at TIMESTAMP
```

**Regras:**
- `message_type = 'form_submission'` → Primeira mensagem (dados do form)
- `message_type = 'question'` → Claude fazendo pergunta ([PERGUNTA-N])
- `message_type = 'answer'` → Usuário respondendo
- `message_type = 'context'` → Informação adicional
- `role = 'assistant'` + detectar `[RESPOSTA-FINAL]` → Conversa completed

### Tabela: `user_files`
```sql
id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
user_id INT UNSIGNED NOT NULL         -- FK users(id)
original_filename VARCHAR(255) NOT NULL
stored_filename VARCHAR(255) NOT NULL  -- Hash único
file_path VARCHAR(500) NOT NULL       -- Caminho completo
mime_type VARCHAR(100) NOT NULL
file_size INT UNSIGNED NOT NULL
extracted_text LONGTEXT NULL          -- Texto extraído do PDF/DOCX
created_at TIMESTAMP
```

### Tabela: `conversation_files`
```sql
conversation_id BIGINT UNSIGNED NOT NULL  -- FK conversations(id)
file_id BIGINT UNSIGNED NOT NULL          -- FK user_files(id)
PRIMARY KEY (conversation_id, file_id)
```

---

## 📦 DEPENDÊNCIAS INSTALADAS (Composer)

```json
{
  "require": {
    "php": ">=8.0",
    "league/oauth2-google": "^4.0",
    "smalot/pdfparser": "^2.7",           // PDF text extraction
    "phpoffice/phpword": "^1.2",          // DOCX reading
    "mpdf/mpdf": "^8.2.4"                 // PDF generation (export)
  }
}
```

---

## 🔑 CREDENCIAIS E AMBIENTE

### Produção (Hostinger)
```bash
SSH: u202164171@82.25.72.226:65002
Path: /home/u202164171/domains/sunyataconsulting.com/public_html/plataforma-sunyata
DB: u202164171_sunyata
PHP: 8.2.28 (LiteSpeed)
Funções desabilitadas: exec, shell_exec, system
```

### Local (WSL)
```bash
Path: /home/iflitaiff/projetos/plataforma-sunyata
DB: sunyata_dev (user: dev, pass: dev123)
PHP: 8.3.6
```

### GitHub
```bash
Repo: https://github.com/iflitaiff/plataforma-sunyata
Branch: feature/mvp-admin-canvas
Último commit: a2ff6c0 (docs: Add Manus feedback analysis)
```

### Usuários Admin (Produção)
```
flitaiff@gmail.com (user_id=7, access_level=admin)
filipe.litaiff@ifrj.edu.br (user_id=1, access_level=admin)
```

---

## 🛠️ SPRINT 2: TAREFAS DETALHADAS

### Task 2.1: FileUploadService.php (4h)

**Localização:** `src/Services/FileUploadService.php`

**Responsabilidades:**
1. Validar upload (tipo, tamanho, MIME real via finfo)
2. Gerar nome único (hash SHA256 do arquivo + timestamp)
3. Mover para diretório de uploads
4. Inserir registro em `user_files`
5. Retornar file_id

**Validações:**
- Tipos aceitos: `application/pdf`, `application/vnd.openxmlformats-officedocument.wordprocessingml.document`
- Tamanho máximo: 10MB (por arquivo)
- Usar `finfo_file()` para validar MIME real (não confiar em extensão)

**Estrutura de diretórios:**
```
/var/uploads/
  └── {year}/
      └── {month}/
          └── {user_id}/
              └── {hash}_original-name.pdf
```

**Métodos públicos:**
```php
public function uploadFile(array $fileData, int $userId): array
// Retorna: ['success' => bool, 'file_id' => int, 'message' => string]

public function getFileById(int $fileId, int $userId): ?array
// Retorna dados do arquivo ou null se não pertencer ao usuário

public function deleteFile(int $fileId, int $userId): bool
```

**Simplificações (conforme Manus):**
- ❌ NÃO usar Respect\Validation → validação manual simples
- ❌ NÃO implementar antivírus scan → adicionar depois se necessário
- ❌ NÃO criar hierarquia de exceções → usar Exception padrão

**Referência existente:** `src/Admin/UserDeletionService.php` (padrão singleton)

---

### Task 2.2: DocumentProcessorService.php (3h)

**Localização:** `src/Services/DocumentProcessorService.php`

**Responsabilidades:**
1. Extrair texto de PDF (usando smalot/pdfparser)
2. Extrair texto de DOCX (usando phpoffice/phpword)
3. Atualizar campo `extracted_text` em `user_files`
4. Tratar erros (PDF corrompido, senha protegido, etc)

**Métodos públicos:**
```php
public function extractText(int $fileId): array
// Retorna: ['success' => bool, 'text' => string, 'message' => string]

public function processFile(int $fileId): bool
// Extrai e salva texto no banco, retorna true/false
```

**Tratamento de erros:**
- PDF protegido por senha → retornar mensagem amigável
- PDF corrompido → log de erro, retornar mensagem
- DOCX com imagens → ignorar imagens, extrair só texto

**Limite de texto extraído:**
- Máximo 100.000 caracteres (limitar contexto enviado ao Claude)
- Se exceder, truncar e adicionar nota

**Simplificações:**
- ❌ NÃO implementar OCR → apenas texto nativo do PDF
- ❌ NÃO processar tabelas/formatação → texto puro

---

### Task 2.3: ConversationService.php (2h)

**Localização:** `src/Services/ConversationService.php`

**Responsabilidades:**
1. Criar nova conversa (com canvas_id e user_id)
2. Adicionar mensagens à conversa
3. Buscar conversa completa (com mensagens e arquivos)
4. Atualizar status da conversa
5. Gerar título automático (após primeira pergunta do Claude)

**Métodos públicos:**
```php
public function createConversation(int $userId, int $canvasId): int
// Retorna conversation_id

public function addMessage(int $conversationId, string $role, string $content, ?string $messageType = null): int
// Retorna message_id

public function getConversation(int $conversationId, int $userId): ?array
// Retorna: ['conversation' => [...], 'messages' => [...], 'files' => [...]]

public function attachFiles(int $conversationId, array $fileIds): bool

public function completeConversation(int $conversationId): bool
// Muda status para 'completed'

public function generateTitle(int $conversationId): string
// Gera título baseado no conteúdo (primeiros 50 chars da tarefa)
```

**Regras de negócio:**
- Usuário só pode acessar próprias conversas
- Mensagens são imutáveis (não deletar/editar)
- Título gerado automaticamente após form_submission

**Simplificações:**
- ❌ NÃO implementar paginação → buscar todas mensagens (por enquanto)
- ❌ NÃO implementar busca/filtros → adicionar depois

---

### Task 2.4: ClaudeService::generateWithContext() (2h)

**Localização:** `src/AI/ClaudeService.php` (arquivo JÁ EXISTE)

**Adicionar método:**
```php
public function generateWithContext(string $systemPrompt, array $messages, int $maxTokens = 4096): array
```

**Parâmetros:**
- `$systemPrompt`: System prompt do Canvas
- `$messages`: Array de mensagens no formato Claude API:
  ```php
  [
    ['role' => 'user', 'content' => 'Tarefa: ...'],
    ['role' => 'assistant', 'content' => '[PERGUNTA-1] ...'],
    ['role' => 'user', 'content' => 'Resposta...'],
    // ...
  ]
  ```

**Retorno:**
```php
[
  'success' => bool,
  'content' => string,        // Texto da resposta
  'message_type' => string,   // 'question' ou 'final_answer'
  'finish_reason' => string,
  'usage' => [...]
]
```

**Detecção de tipo de mensagem:**
```php
if (preg_match('/^\[PERGUNTA-\d+\]/', $content)) {
    $messageType = 'question';
} elseif (preg_match('/^\[RESPOSTA-FINAL\]/', $content)) {
    $messageType = 'final_answer';
} else {
    $messageType = 'context';
}
```

**Simplificações:**
- ❌ NÃO implementar rate limiting complexo → contador simples no banco
- ❌ NÃO implementar cache → adicionar depois se necessário
- ❌ NÃO implementar retry automático → tratamento de erro simples

**Referência:** Método `generate()` já existe no arquivo

---

## 🚨 ARMADILHAS A EVITAR (Lições do Debugging)

### 1. Variáveis `$stats` em Páginas Admin
**Problema:** Esquecer de inicializar `$stats = []` antes de incluir `admin-header.php`
**Solução:** SEMPRE inicializar no topo, após `$db = Database::getInstance()`

### 2. Sobrescrita de Variáveis em Loops
**Problema:** Reutilizar nome de variável dentro de foreach
```php
// ❌ ERRADO
$stats = [];
foreach ($items as $item) {
    $stats = $db->fetchOne(...); // Sobrescreve!
}

// ✅ CORRETO
$stats = [];
foreach ($items as $item) {
    $itemStats = $db->fetchOne(...);
}
```

### 3. Constantes Já Definidas
**Problema:** `config.php` tentava redefinir constantes de `secrets.php`
**Solução:** NUNCA fazer `define('DB_HOST', DB_HOST)` - isso é redundante e causa erro

### 4. Diretórios Inexistentes
**Problema:** `logs/` não existia, causando erro silencioso
**Solução:** Criar diretórios necessários no setup ou verificar com `is_dir()`

### 5. Cache do LiteSpeed
**Problema:** Alterações em arquivos PHP não apareciam (cache)
**Solução:** Limpar cache ou aguardar TTL (não criar .htaccess com cache disable)

---

## 📋 CHECKLIST DE DECISÃO (Framework dos 3 Agentes)

Antes de implementar QUALQUER funcionalidade, perguntar:

- [ ] Isso permite usuário testar o core value HOJE?
- [ ] Isso previne um bug que JÁ ACONTECEU?
- [ ] Isso resolve uma dor RELATADA por usuários?
- [ ] O esforço é < 10% do esforço de completar MVP?

**Se 2+ respostas SIM → FAZER AGORA**
**Se 0-1 respostas SIM → BACKLOG**

---

## 🎯 DEFINIÇÃO DE PRONTO (Sprint 2)

### Critérios de Aceitação

#### FileUploadService
- [ ] Aceita upload de PDF e DOCX
- [ ] Valida MIME type real (não só extensão)
- [ ] Limita tamanho a 10MB
- [ ] Salva arquivo em estrutura de diretórios organizada
- [ ] Insere registro em `user_files`
- [ ] Retorna file_id

#### DocumentProcessorService
- [ ] Extrai texto de PDF usando smalot/pdfparser
- [ ] Extrai texto de DOCX usando phpoffice/phpword
- [ ] Salva texto extraído em `user_files.extracted_text`
- [ ] Trata erro de PDF protegido/corrompido gracefully

#### ConversationService
- [ ] Cria nova conversa
- [ ] Adiciona mensagens com role e type corretos
- [ ] Busca conversa completa com mensagens e arquivos
- [ ] Anexa arquivos à conversa
- [ ] Gera título automático
- [ ] Completa conversa quando detecta [RESPOSTA-FINAL]

#### ClaudeService
- [ ] Método generateWithContext() criado
- [ ] Envia mensagens com histórico completo
- [ ] Detecta tipo de mensagem ([PERGUNTA-N] vs [RESPOSTA-FINAL])
- [ ] Retorna resposta formatada com message_type
- [ ] Trata erros da API gracefully

### Testes Manuais
- [ ] Upload de PDF válido funciona
- [ ] Upload de DOCX válido funciona
- [ ] Upload de arquivo >10MB é rejeitado
- [ ] Upload de .exe é rejeitado
- [ ] Texto é extraído de PDF
- [ ] Texto é extraído de DOCX
- [ ] Conversa é criada e mensagens são salvas
- [ ] Claude responde com [PERGUNTA-1]
- [ ] Resposta do usuário é salva
- [ ] Claude responde com [RESPOSTA-FINAL] após contexto suficiente

---

## 🔗 REFERÊNCIAS IMPORTANTES

### Documentação
- **SurveyJS:** https://surveyjs.io/form-library/documentation/overview
- **Claude API:** https://docs.anthropic.com/en/api/messages
- **smalot/pdfparser:** https://github.com/smalot/pdfparser
- **phpoffice/phpword:** https://phpword.readthedocs.io/

### Arquivos Importantes para Ler
- `src/Core/Database.php` - Padrão singleton, métodos fetchOne/fetchAll/insert/update
- `src/AI/ClaudeService.php` - Método generate() existente
- `src/Admin/UserDeletionService.php` - Exemplo de Service com singleton
- `database/migrations/004_mvp_console.sql` - Schema completo

### Backlog (NÃO Implementar Agora)
- `BACKLOG.md` - 8 melhorias pós-MVP (~29h)
- PHPStan, CI/CD, logs estruturados, versionamento de prompts, etc.

---

## 💬 CONTEXTO DA COLABORAÇÃO (3 Agentes)

### Claude Code (Eu)
- Implementação rápida e pragmática
- Debugging quando necessário
- Commits + deploy

### Manus
- Code review e arquitetura
- Diagnóstico profundo de bugs
- Já está alinhado com MVP-first

### Usuário (Filipe)
- Define prioridades
- Testa em produção
- Valida funcionalidades

**Dinâmica:** MVP-first, fundação técnica depois de validar com usuários.

---

## 📝 ÚLTIMAS AÇÕES REALIZADAS (Hoje)

```bash
# Commits
68c4a56 - fix: Add stats array initialization for admin-header compatibility
5aeab7d - fix: Initialize $stats array in 3 admin files
a2ff6c0 - docs: Add Manus feedback analysis and post-MVP backlog

# Arquivos modificados
- public/admin/users.php (CORRIGIDO)
- public/admin/access-requests.php (CORRIGIDO)
- public/admin/audit-logs.php (CORRIGIDO)
- public/admin/canvas-templates.php (CORRIGIDO)
- public/admin/canvas-edit.php (CORRIGIDO)

# System prompt melhorado (no banco)
- Canvas juridico-geral: Chain-of-thought + formatação Markdown

# Criados
- BACKLOG.md
- ANALISE-CRITICA-MANUS.md
- FEEDBACK-MANUS-SUCESSO.md
- logs/ (diretório)
```

---

## ⚡ COMO COMEÇAR O SPRINT 2

### Passo 1: Verificar ambiente
```bash
cd /home/iflitaiff/projetos/plataforma-sunyata
git status
git pull origin feature/mvp-admin-canvas
```

### Passo 2: Criar diretório Services
```bash
mkdir -p src/Services
```

### Passo 3: Criar FileUploadService.php (começar aqui!)
```php
<?php
namespace Sunyata\Services;

use Sunyata\Core\Database;
use Exception;

class FileUploadService {
    private static $instance = null;
    private $db;

    private function __construct() {
        $this->db = Database::getInstance();
    }

    public static function getInstance(): self {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    // TODO: Implementar uploadFile()
}
```

### Passo 4: Iterar até completar todas as tasks

---

## 🎯 OBJETIVO FINAL

**Ao terminar Sprint 2, teremos:**
- ✅ Upload de arquivos funcionando
- ✅ Extração de texto de PDF/DOCX
- ✅ CRUD de conversas
- ✅ Claude respondendo com contexto e histórico

**Então iremos para Sprint 3 (APIs) e Sprint 4 (Frontend).**

**MVP completo em 7 dias!** 🚀

---

**FIM DO CONTEXTO - Pronto para Sprint 2!**
