# Arquitetura da Plataforma Sunyata

## Visão Geral

Plataforma educacional/profissional para integração de IA (Claude API) em diferentes verticais (Jurídico, Docência, Pesquisa, IFRJ), com sistema de console interativa para conversas contextualizadas usando ferramentas tipo "Canvas".

---

## Stack Tecnológico

### Backend
- **Linguagem:** PHP 8.2+
- **Framework:** Vanilla PHP com PSR-4 autoloading (Composer)
- **Banco de Dados:** MySQL/MariaDB
- **Autenticação:** Google OAuth 2.0
- **AI Provider:** Anthropic Claude API (Messages API)

### Frontend
- **UI Framework:** Bootstrap 5
- **JavaScript:** Vanilla JS (sem frameworks pesados)
- **Icons:** Bootstrap Icons
- **PDF Viewer:** PDF.js (planejado)

### Bibliotecas PHP
- `smalot/pdfparser` - Extração de texto de PDFs
- `phpoffice/phpword` - Processamento de DOCX
- `mpdf/mpdf` - Geração de PDFs para export

### Infraestrutura
- **Servidor:** Hostinger Premium Web Hosting (sem root access)
- **Deploy:** SCP + SSH (porta 65002)
- **Storage:** Filesystem local (`/uploads/`)

---

## Arquitetura de Dados

### Tabelas Principais

#### **users**
Usuários cadastrados via Google OAuth.
- `id`, `email`, `name`, `picture`, `google_id`
- `access_level` (guest, user, admin)
- `selected_vertical` (juridico, docencia, pesquisa, ifrj_alunos)

#### **vertical_access_requests**
Solicitações de acesso às verticais (algumas requerem aprovação admin).
- `user_id`, `vertical`, `status` (pending, approved, rejected)

#### **settings**
Sistema de configuração dinâmica (ex: `juridico_requires_approval`).
- Gerenciado via `src/Core/Settings.php` (singleton)

#### **prompt_history**
Histórico de prompts enviados para Claude (transparente ao usuário).
- `user_id`, `vertical`, `tool_name`, `input_data`, `generated_prompt`, `claude_response`
- Tokens, custo, status

#### **conversations** (NOVO - MVP)
Conversas interativas na Console.
- `user_id`, `vertical`, `tool_name` (ex: canvas_juridico)
- `title`, `status` (active, completed, archived)

#### **conversation_messages** (NOVO - MVP)
Mensagens individuais de cada conversa.
- `conversation_id`, `role` (user, assistant)
- `content`, `message_type` (question, answer, final_response)
- `tokens_input`, `tokens_output`, `cost_usd`

#### **user_files** (NOVO - MVP)
Biblioteca pessoal de documentos do usuário.
- `user_id`, `original_filename`, `stored_filename`, `file_path`
- `mime_type`, `file_size`, `file_hash` (SHA256 para deduplicação)
- `extracted_text`, `tokens_estimated`, `page_count`

#### **conversation_files** (NOVO - MVP)
Vínculo many-to-many entre conversas e arquivos.
- `conversation_id`, `file_id`

---

## Módulos Implementados

### 1. Sistema de Verticais
**Arquivo:** `src/Core/VerticalManager.php`
- Gerenciamento centralizado de verticais (config em `config/verticals.php`)
- Lógica de aprovação dinâmica baseada em Settings
- Validação de acesso

### 2. Autenticação OAuth
**Arquivos:** `public/login.php`, `public/oauth-callback.php`
- Login via Google OAuth 2.0
- Gestão de sessões (`SESSION_NAME` customizado)
- CSRF protection (`csrf_token()` helper)

### 3. Onboarding
**Arquivos:** `public/onboarding-step1.php`, `public/onboarding-step2.php`
- Coleta de dados do perfil
- Seleção de vertical
- Submissão de solicitação de acesso (com aprovação se necessário)

### 4. Admin Dashboard
**Arquivos:** `public/admin/index.php`, `public/admin/users.php`
- Gestão de configurações (toggle settings)
- Gerenciamento de usuários
- Aprovação de solicitações de acesso
- Deleção segura de usuários (LGPD compliant)

### 5. Sistema de Settings Dinâmico
**Arquivo:** `src/Core/Settings.php`
- Singleton com cache em memória
- Métodos: `get()`, `set()`, `toggle()`
- Usado para configurações de aprovação, limites, etc.

### 6. Integração Claude API
**Arquivo:** `src/AI/ClaudeService.php`
- Chamadas HTTP via cURL para Anthropic Messages API
- Salvamento automático em `prompt_history`
- Cálculo de custo e tokens
- Modelo padrão: `claude-3-5-sonnet-20241022`

### 7. Canvas Jurídico (Ferramenta Inicial)
**Arquivos:** `public/areas/juridico/canvas-juridico.php`, `public/ferramentas/canvas-juridico.html`
- Formulário interativo com 6 campos (Tarefa, Contexto, Materiais, etc.)
- Geração de prompt estruturado
- Chamada para `/api/generate-juridico.php`
- Exibição de resposta do Claude

### 8. Deleção de Conta (LGPD)
**Arquivos:** `public/delete-account.php`, `src/Services/UserDeletionService.php`
- Auto-deleção com confirmação dupla
- Deleção atômica via transações
- Anonimização de logs de auditoria
- Página de confirmação pós-deleção

---

## Módulos Planejados (MVP Console)

### 1. Console Interativa
**Arquivo:** `public/areas/juridico/console.php` (NOVO)

**Funcionalidades:**
- **Sidebar:**
  - 📂 Biblioteca de Arquivos (user_files)
  - 💬 Lista de Conversas (conversations)
  - ➕ Botão Nova Conversa

- **Área Principal:**
  - Chat interativo (estilo WhatsApp)
  - Upload de arquivos (início da conversa)
  - Visualização de arquivos anexados
  - Indicador de tipo de mensagem (pergunta vs resposta final)

- **Fluxo:**
  1. Usuário inicia conversa via Canvas (preenche formulário)
  2. Sistema cria `conversation` + anexa arquivos da biblioteca
  3. Claude faz perguntas de contexto (máx 5, com marcadores `[PERGUNTA-N]`)
  4. Usuário responde ou clica "⚡ Resposta Direta" (skip)
  5. Claude entrega resposta final (marcador `[RESPOSTA-FINAL]`)
  6. Conversa pode ser exportada (TXT/PDF), deletada, ou favoritada

### 2. Sistema de Upload de Arquivos
**Arquivo:** `src/Services/FileUploadService.php` (NOVO)

**Funcionalidades:**
- Upload para biblioteca pessoal
- Validação: tipo MIME, tamanho (10MB max), hash SHA256
- Armazenamento: `/uploads/user_{id}/`
- Limites: 5 arquivos/conversa, 100MB total/usuário

**Tipos suportados:**
- ✅ PDF com texto (`smalot/pdfparser`)
- ✅ DOCX (`phpoffice/phpword`)
- ✅ TXT (nativo)
- ✅ JPG/PNG (Claude faz OCR via Messages API)
- ❌ PDFs escaneados (sem OCR no MVP)

### 3. Processamento de Documentos
**Arquivo:** `src/Services/DocumentProcessorService.php` (NOVO)

**Funcionalidades:**
- Extração de texto de PDFs/DOCX/TXT
- Contagem de páginas
- Estimativa de tokens
- Validação (rejeitar PDFs vazios/escaneados)

### 4. Gerenciamento de Conversas
**Arquivo:** `src/Services/ConversationService.php` (NOVO)

**Funcionalidades:**
- `createConversation()` - Iniciar nova conversa
- `addMessage()` - Adicionar mensagem (user/assistant)
- `getConversationHistory()` - Buscar histórico
- `detectMessageType()` - Regex para detectar `[PERGUNTA-N]` ou `[RESPOSTA-FINAL]`
- `attachFiles()` - Vincular arquivos da biblioteca

### 5. API de Chat
**Arquivo:** `public/api/chat.php` (NOVO)

**Endpoint:** `POST /api/chat.php`

**Input:**
```json
{
  "conversation_id": 123,
  "message": "Texto do usuário",
  "file_ids": [5, 7],  // Opcional
  "skip_questions": false
}
```

**Output:**
```json
{
  "success": true,
  "response": "[PERGUNTA-1] Qual o prazo do contrato?",
  "message_type": "question",
  "tokens": {"input": 1500, "output": 200},
  "cost_usd": 0.0075
}
```

**Lógica:**
1. Buscar histórico da conversa
2. Buscar arquivos anexados (extrair texto)
3. Montar prompt com:
   - **System:** Instruções + marcadores + limite de perguntas
   - **Histórico:** Mensagens anteriores (contexto)
   - **Documentos:** Texto extraído dos arquivos
   - **Mensagem atual**
4. Chamar `ClaudeService->generate()`
5. Detectar tipo de resposta via regex
6. Salvar em `conversation_messages`
7. Retornar JSON

### 6. Export de Conversas
**Arquivo:** `public/api/export-conversation.php` (NOVO)

**Endpoint:** `GET /api/export-conversation.php?id=123&format=txt`

**Formatos:**
- **TXT:** Plain text (simples)
- **PDF:** mPDF (layout profissional)

---

## Fluxo de Uso Completo (MVP)

### Cenário: Advogado Analisando Contrato

1. **Login:**
   - Usuário faz login via Google OAuth
   - Sistema cria sessão

2. **Onboarding (primeira vez):**
   - Usuário preenche perfil
   - Seleciona vertical "Jurídico"
   - Se aprovação ativa: aguarda admin aprovar
   - Se aprovação desativada: acesso imediato

3. **Acesso à Console:**
   - Usuário navega para `/areas/juridico/console.php`
   - Vê sidebar com biblioteca vazia e botão "Nova Conversa"

4. **Upload de Documento:**
   - Clica "📂 Biblioteca" → "Upload"
   - Seleciona `contrato_locacao.pdf` (5MB, 20 páginas)
   - Sistema:
     - Valida tipo e tamanho
     - Gera UUID: `a3f7b2c1-...-contrato_locacao.pdf`
     - Move para `/uploads/user_42/a3f7b2c1-...pdf`
     - Extrai texto via `smalot/pdfparser`
     - Estima tokens: ~15,000
     - Insere em `user_files`

5. **Iniciar Conversa:**
   - Clica "➕ Nova Conversa"
   - Abre modal "Canvas Jurídico"
   - Preenche:
     - **Tarefa:** "Revisar cláusulas de rescisão"
     - **Contexto:** "Contrato comercial B2B"
     - **Materiais:** Seleciona `contrato_locacao.pdf` da biblioteca
   - Clica "Iniciar Análise"

6. **Sistema cria conversa:**
   - Insere em `conversations`: `{user_id: 42, tool_name: 'canvas_juridico', status: 'active'}`
   - Vincula arquivo em `conversation_files`
   - Gera prompt inicial com marcadores:

```
System:
Você é advogado sênior especializado...

IMPORTANTE:
- Faça perguntas indexadas: [PERGUNTA-1], [PERGUNTA-2], etc.
- Máximo 5 perguntas.
- Quando tiver contexto suficiente, inicie com [RESPOSTA-FINAL].

User:
Tarefa: Revisar cláusulas de rescisão
Contexto: Contrato comercial B2B

Documento anexado (contrato_locacao.pdf, 20 páginas):
[texto extraído do PDF...]
```

7. **Claude responde:**
```
[PERGUNTA-1] Qual é o prazo de vigência do contrato? Isso impacta as condições de rescisão antecipada.
```

8. **Interface renderiza:**
   - Mensagem do Claude com badge "Pergunta 1/5"
   - Campo de input + botão "Enviar"
   - Botão "⚡ Pular e Gerar Resposta Direta"

9. **Usuário responde:**
```
30 meses
```

10. **Claude faz mais perguntas:**
```
[PERGUNTA-2] Há multa por rescisão antecipada prevista no contrato? Se sim, qual percentual?
```

11. **Usuário responde:**
```
Sim, cláusula 8.3 prevê 20% do valor residual
```

12. **Claude tem contexto suficiente:**
```
[RESPOSTA-FINAL]

# Análise Jurídica - Cláusulas de Rescisão

## Sumário Executivo
O contrato apresenta cláusulas de rescisão adequadas...

## Cláusulas Identificadas
1. Cláusula 8.1: Rescisão por justa causa...
2. Cláusula 8.3: Multa rescisória de 20%...

## Recomendações
1. Revisar proporcionalidade da multa...
...
```

13. **Interface mostra resultado final:**
   - Mensagem completa renderizada
   - Badge "✅ Resposta Final"
   - Botões:
     - 📥 Exportar (TXT/PDF)
     - ✏️ Editar última pergunta e reenviar
     - 🗑️ Deletar conversa
     - ⭐ Favoritar

14. **Usuário exporta:**
   - Clica "Exportar PDF"
   - Sistema gera via mPDF
   - Download: `conversa_123_revisao_clausulas.pdf`

---

## Decisões de Arquitetura

### 1. Marcadores Explícitos
**Decisão:** Claude usa `[PERGUNTA-N]` e `[RESPOSTA-FINAL]` para sinalizar tipo de mensagem.

**Alternativas rejeitadas:**
- ❌ Análise semântica (termina com `?`) - Não confiável
- ❌ IA adicional para classificar - Gasta tokens desnecessariamente

**Vantagens:**
- ✅ Simples, rápido, confiável
- ✅ Regex trivial: `/^\[PERGUNTA-\d+\]/` ou `/^\[RESPOSTA-FINAL\]/`
- ✅ Zero tokens extras

### 2. Biblioteca Híbrida
**Decisão:** Arquivos pertencem ao usuário (biblioteca pessoal) e são vinculados a conversas.

**Alternativas rejeitadas:**
- ❌ Arquivos vinculados apenas a conversa específica - Não reutilizável
- ❌ Upload durante chat - Complexidade desnecessária no MVP

**Vantagens:**
- ✅ Reutilização de documentos em múltiplas conversas
- ✅ Gestão centralizada de arquivos
- ✅ Deduplicação via SHA256 (economiza espaço)

### 3. Extração de Texto (não PDF completo)
**Decisão:** Extrair texto antes de enviar para Claude.

**Alternativas rejeitadas:**
- ❌ Enviar PDF completo - Muito caro (~30k tokens por contrato)
- ❌ RAG com embeddings - Complexo demais para MVP

**Vantagens:**
- ✅ Reduz custo em ~70%
- ✅ Funciona no hosting sem root (bibliotecas PHP puras)
- ✅ Suficiente para 90% dos casos (PDFs com texto)

### 4. Limite de 5 Perguntas
**Decisão:** Claude pode fazer até 5 perguntas de contexto.

**Justificativa:**
- Evita loops infinitos
- Controla custos (cada pergunta = ~500 tokens)
- Usuário pode pular e pedir resposta direta

### 5. Edição Apenas da Última Mensagem
**Decisão:** MVP permite editar apenas última mensagem do usuário.

**Alternativas rejeitadas:**
- ❌ Edição de histórico completo - Complexo (reprocessar thread)

**Vantagens:**
- ✅ Simples de implementar (apagar resposta Claude seguinte + reenviar)
- ✅ Resolve 80% dos casos de correção

### 6. Sistema de Créditos em Fase 1.5
**Decisão:** MVP mostra uso (tokens/custo) mas não bloqueia.

**Justificativa:**
- MVP foca em funcionalidade core
- Sistema de billing adiciona complexidade
- Pode ser adicionado depois sem refatoração

---

## Limitações Conhecidas

### Ambiente Hostinger (Premium Web Hosting)
- ❌ Sem root access
- ❌ Sem Tesseract OCR (PDFs escaneados)
- ❌ Sem Poppler CLI tools
- ✅ PHP 8.2+, Composer, cURL, MySQL

**Mitigação:**
- Usar bibliotecas PHP puras
- Rejeitar PDFs escaneados (sem texto)
- Considerar VPS no futuro se necessário

### Custos de API
**Claude 3.5 Sonnet:**
- Input: $3/MTok
- Output: $15/MTok

**Estimativa por conversa:**
- 5 perguntas + resposta final
- ~30k tokens input (histórico + documentos)
- ~5k tokens output (respostas)
- Custo: ~$0.15 por conversa completa

**Mitigação:**
- Limitar perguntas (5 max)
- Extrair texto (não enviar PDFs completos)
- Prompt Caching (futura otimização)

---

## Próximas Fases (Pós-MVP)

### Fase 1.5: Sistema de Créditos
- Tabela `user_credits`
- Admin pode atribuir créditos
- Bloqueio quando créditos acabam
- Interface de compra/recarga

### Fase 2: RAG Avançado
- Embeddings de documentos (OpenAI/Cohere)
- Vector database (Qdrant/ChromaDB)
- Enviar apenas trechos relevantes (reduz tokens 90%)

### Fase 3: Outras Verticais
- Canvas Docente (planejamento de aulas)
- Canvas Pesquisa (revisão de literatura)
- Ferramentas específicas por vertical

### Fase 4: Colaboração
- Compartilhar conversas com outros usuários
- Comentários em conversas
- Workspace para equipes

### Fase 5: VPS Migration (se necessário)
- OCR de PDFs escaneados (Tesseract)
- Processamento de formatos complexos
- RAG self-hosted
- Maior controle de recursos

---

## Padrões de Código

### Estrutura de Diretórios
```
plataforma-sunyata/
├── config/
│   ├── config.php              # Constantes globais
│   ├── secrets.php             # API keys (não versionado)
│   ├── verticals.php           # Config de verticais
│   └── migrations/             # SQL migrations
├── public/
│   ├── areas/                  # Páginas por vertical
│   │   └── juridico/
│   │       ├── index.php
│   │       └── console.php     # NOVO
│   ├── admin/                  # Dashboard admin
│   ├── api/                    # Endpoints REST
│   │   ├── chat.php            # NOVO
│   │   └── upload-file.php     # NOVO
│   ├── ferramentas/            # HTML tools
│   └── assets/
│       └── js/
│           └── console-chat.js # NOVO
├── src/
│   ├── Core/
│   │   ├── Database.php        # Singleton PDO
│   │   ├── Settings.php        # Config dinâmico
│   │   └── VerticalManager.php
│   ├── AI/
│   │   └── ClaudeService.php
│   └── Services/
│       ├── ConversationService.php      # NOVO
│       ├── FileUploadService.php        # NOVO
│       ├── DocumentProcessorService.php # NOVO
│       └── UserDeletionService.php
├── uploads/                    # Storage local
│   └── user_{id}/
└── vendor/                     # Composer dependencies
```

### Convenções
- **Namespaces:** `Sunyata\{Module}\{Class}`
- **Classes:** PascalCase, services como Singletons ou stateless
- **Métodos públicos:** camelCase
- **Constantes:** UPPER_SNAKE_CASE
- **Banco de dados:** snake_case
- **Segurança:** Sempre usar prepared statements, `htmlspecialchars()` em outputs

### Error Handling
- Try-catch em pontos críticos
- `error_log()` para logs
- JSON responses em APIs: `{'success': bool, 'error': string}`
- HTTP status codes apropriados

---

## Segurança

### Autenticação
- Google OAuth 2.0 (não armazenar senhas)
- Sessões com `session_name()` customizado
- CSRF tokens em todos os formulários

### Autorização
- Middleware `require_login()` em páginas protegidas
- Verificação de `access_level` para admin
- Verificação de `selected_vertical` para acesso a áreas

### Upload de Arquivos
- Validação MIME type server-side
- Limite de tamanho (10MB)
- SHA256 hash para deduplicação
- Armazenamento fora de `public_html` (via symlink ou .htaccess)
- Scan de vírus (futuro, se VPS)

### API Keys
- `config/secrets.php` não versionado (`.gitignore`)
- Nunca expor em logs ou respostas HTTP
- Rotação periódica

### LGPD
- Deleção completa de dados (transacional)
- Anonimização de logs de auditoria
- Termo de consentimento no onboarding
- Direito de portabilidade (export conversas)

---

## Monitoramento

### Logs
- `logs/php_errors.log` - Erros PHP
- `audit_logs` table - Ações críticas (login, deleção, mudança de settings)
- `prompt_history` - Todas interações com Claude (custo, tokens)

### Métricas (Admin)
- Total de usuários por vertical
- Solicitações de acesso pendentes
- Uso de API Claude (tokens, custo mensal)
- Espaço em disco (uploads)

---

## Testes

### MVP - Testes Manuais
1. **Upload de arquivo:**
   - Upload PDF válido → sucesso
   - Upload arquivo >10MB → rejeição
   - Upload .exe → rejeição
   - Upload PDF escaneado (sem texto) → rejeição

2. **Criação de conversa:**
   - Preencher Canvas → iniciar conversa
   - Ver primeira pergunta do Claude
   - Responder → ver próxima pergunta
   - Contador de perguntas (1/5, 2/5...)

3. **Resposta direta:**
   - Clicar "⚡ Pular" → ver resposta final imediata
   - Verificar marcador `[RESPOSTA-FINAL]`

4. **Edição de mensagem:**
   - Editar última resposta → reenviar
   - Ver nova resposta Claude
   - Histórico atualizado

5. **Export:**
   - Exportar TXT → verificar conteúdo
   - Exportar PDF → verificar formatação

### Pós-MVP - Testes Automatizados
- Unit tests (PHPUnit) para Services
- Integration tests para APIs
- E2E tests (Cypress/Playwright) para UI

---

## Deploy

### Local Development
```bash
# Setup
composer install
mysql -u root -p < config/migrations/*.sql
cp config/secrets.php.example config/secrets.php
# Editar secrets.php com suas chaves

# Run
php -S localhost:8000 -t public/
```

### Produção (Hostinger)
```bash
# Sync código
rsync -avz --exclude 'vendor' --exclude 'node_modules' \
  -e "ssh -p 65002" \
  ./ u202164171@82.25.72.226:/path/to/plataforma-sunyata/

# Deploy vendor
tar -czf vendor.tar.gz vendor/
scp -P 65002 vendor.tar.gz u202164171@82.25.72.226:/path/
ssh -p 65002 u202164171@82.25.72.226 "cd /path && tar -xzf vendor.tar.gz && rm vendor.tar.gz"

# Migrations
scp -P 65002 config/migrations/004_*.sql u202164171@82.25.72.226:/home/u202164171/
ssh -p 65002 u202164171@82.25.72.226 "/usr/bin/mariadb u202164171_sunyata -p < /home/u202164171/004_*.sql"

# Clear cache (se necessário)
ssh -p 65002 u202164171@82.25.72.226 "cd /path && rm -rf cache/*"
```

---

## Contato

- **Desenvolvedor:** Prof. Filipe Litaiff, PhD
- **Email:** filipe.litaiff@ifrj.edu.br
- **GitHub:** https://github.com/iflitaiff/plataforma-sunyata
- **Website:** https://sunyataconsulting.com/

---

**Última atualização:** 2025-01-21
**Versão:** 1.1.0 (MVP Console em desenvolvimento)
