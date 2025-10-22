# 📝 Changelog

> Histórico de versões e mudanças da Plataforma Sunyata

---

## [v2.0] - Sprint 2 Completo - 2025-10-22

### 🎉 Resumo

Sprint 2 focado na **Services Layer** - camada de lógica de negócio reutilizável. Todos os 8 bugs críticos identificados na auditoria do Manus AI foram corrigidos.

### ✨ Adicionado

#### Services Layer
- **FileUploadService** - Gerenciamento de upload de arquivos
  - Upload com validação MIME real (`finfo_file`)
  - Estrutura de diretórios environment-aware (Hostinger vs local)
  - Rate limiting (10 uploads/hora por usuário)
  - Sanitização de nome de arquivo (prevenção de path traversal)
  - Validação de tamanho real (não confia no cliente)
  
- **DocumentProcessorService** - Processamento de documentos
  - Extração de texto de PDF (via `pdftotext`)
  - Extração de texto de DOCX (via `ZipArchive` + XML parsing)
  - Ownership checks em todas as operações
  - Tratamento de erros robusto

- **ConversationService** - Gerenciamento de conversas
  - CRUD completo de conversas e mensagens
  - Anexo de arquivos a conversas com ownership validation
  - Geração automática de títulos (com fallback para data/hora)
  - Soft delete de conversas
  - Validação de tamanho de conteúdo (65k caracteres)

- **ClaudeService Enhancement**
  - Novo método `generateWithContext()` para multi-turn conversations
  - Suporte a contexto de múltiplos documentos
  - Streaming de respostas

#### Infraestrutura
- Diretório `/storage/uploads` criado em produção (755)
- Backup automático antes de deploy (`backup-antes-mvp-canvas-*.tar.gz`)

### 🔒 Segurança

Correção de **8 bugs críticos** identificados na auditoria do Manus AI:

- **Bug #2 (Path hardcoded):** CORRIGIDO
  - Implementado `getUploadBasePath()` environment-aware
  - Detecta Hostinger vs local automaticamente
  - Path: `/home/u202164171/.../storage/uploads` em produção

- **Bug #3 (Ownership em extractText):** CORRIGIDO
  - Adicionado parâmetro `userId` em `extractText()`
  - Query com `AND user_id = ?`
  - Mensagem de erro apropriada

- **Bug #4 (Ownership em attachFiles):** CORRIGIDO
  - Verificação de ownership para cada arquivo
  - Log de tentativas não autorizadas
  - Skip de arquivos sem acesso (não bloqueia operação)

- **Bug #5 (Ownership em completeConversation):** CORRIGIDO
  - Parâmetro `$userId` opcional (null para chamadas internas)
  - Verificação condicional de ownership
  - Mantém compatibilidade com chamada em `addMessage()`

- **Bug #6 (Rate limiting):** CORRIGIDO
  - Limite de 10 uploads por hora por usuário
  - Query eficiente com `DATE_SUB(NOW(), INTERVAL 1 HOUR)`
  - Mensagem user-friendly

- **Bug #7 (File size spoofing):** CORRIGIDO
  - Validação de tamanho real com `filesize($filePath)`
  - Usa tamanho real no banco (linha 183)
  - Não confia em `$_FILES['size']`

- **Bug #8 (Path traversal):** CORRIGIDO
  - Sanitização com regex `preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', $originalName)`
  - Limite de 100 caracteres
  - Remove caracteres especiais e path separators

- **Bug #9 (Content length):** CORRIGIDO
  - Validação em `addMessage()` antes do insert
  - Truncamento em 65.000 caracteres (TEXT field limit)
  - Mensagem de nota quando truncado + log

### 🐛 Corrigido

- **Bug #10 (generateTitle vazio):** CORRIGIDO
  - Validação `if (empty($title) || strlen($title) < 3)`
  - Fallback para `'Conversa ' . date('d/m/Y H:i')`
  - Truncamento em 50 caracteres mantido

### 🧪 Testes

- ✅ 9/9 testes passaram (100%)
- ✅ Script de validação: `test-bug-fixes-simple.php`
- ✅ Syntax check: `php -l` em todos os arquivos
- ✅ Verificado em produção: path detection funciona

### 📊 Métricas

| Métrica | Valor |
|---------|-------|
| Bugs corrigidos | 9 (8 críticos + 1 menor) |
| Services criados | 3 |
| Code reviews | 2 (Manus AI) |
| Commits importantes | 15 |
| Qualidade média | 9.6/10 (avaliação Manus) |
| Linhas de código | ~900 linhas |

### ⚠️ Breaking Changes

Assinaturas de métodos foram alteradas para incluir ownership checks:

```php
// ANTES
DocumentProcessorService::extractText($fileId)
DocumentProcessorService::processFile($fileId)

// DEPOIS
DocumentProcessorService::extractText($fileId, $userId)
DocumentProcessorService::processFile($fileId, $userId)
```

```php
// ANTES
ConversationService::attachFiles($conversationId, $fileIds)
ConversationService::completeConversation($conversationId)

// DEPOIS
ConversationService::attachFiles($conversationId, $userId, $fileIds)
ConversationService::completeConversation($conversationId, ?$userId = null)
```

### 📚 Documentação

- ✅ AUDITORIA-MANUS-RESPOSTA.md - Resposta detalhada à auditoria
- ✅ DEPLOYMENT-SPRINT2-BUGFIXES.md - Doc completa do deployment
- ✅ test-bug-fixes-simple.php - Script de validação
- ✅ START-HERE-SPRINT3.md - Guia para próximo sprint

### 🚀 Deployment

- **Servidor:** Hostinger (u202164171@82.25.72.226)
- **Branch:** feature/mvp-admin-canvas
- **Commits:** ceb5b4e (Sprint 2) + 587387a (Bug fixes)
- **Backup:** backup-antes-mvp-canvas-20251022_0045.tar.gz

---

## [v1.0] - Sprint 1 Completo - 2025-10-08

### 🎉 Resumo

Sprint 1 focado na **Foundation** - autenticação, onboarding e estrutura base.

### ✨ Adicionado

#### Autenticação
- Login com Google OAuth 2.0
- Gerenciamento de sessões PHP
- Logout com limpeza de sessão

#### Onboarding
- Wizard de 2 etapas para novos usuários
- Seleção de áreas de interesse (Docência, Pesquisa, Jurídico)
- Armazenamento de preferências no banco

#### Verticais
- **Docência** - Canvas para planejamento de aulas
- **Pesquisa** - Canvas para projetos de pesquisa
- **Jurídico** - Canvas para análise de documentos legais

#### Infraestrutura
- Estrutura de diretórios PSR-4
- Autoload com Composer
- Database layer com PDO
- Sistema de configuração (config.php + secrets.php)

### 🗄️ Banco de Dados

Tabelas criadas:
- `users` - Dados dos usuários
- `conversations` - Conversas com IA
- `messages` - Mensagens das conversas
- `files` - Arquivos uploadados
- `conversation_files` - Relação N:N entre conversas e arquivos

### 🎨 Frontend

- Bootstrap 5.3.2 para UI
- Vanilla JavaScript (ES6+)
- Fetch API para AJAX
- Responsive design

### 📊 Métricas

| Métrica | Valor |
|---------|-------|
| Páginas criadas | 8 |
| Tabelas no banco | 5 |
| Commits | 42 |
| Tempo de desenvolvimento | 3 dias |

---

## [v0.1] - Setup Inicial - 2025-09-30

### ✨ Adicionado

- Repositório Git criado
- Estrutura de diretórios inicial
- README.md com visão geral do projeto
- .gitignore configurado

### 🔧 Configuração

- Ambiente de desenvolvimento local (WSL)
- Servidor de produção (Hostinger)
- SSH configurado entre ambientes
- Banco de dados MariaDB criado

---

## 🔮 Próximas Versões

### [v3.0] - Sprint 3 - APIs (Planejado)

#### Endpoints Planejados
- [ ] POST `/api/upload-file.php` - Upload com FileUploadService + DocumentProcessor
- [ ] POST `/api/chat.php` - Chat com ClaudeService::generateWithContext()
- [ ] GET `/api/export-conversation.php` - Export para PDF com mPDF
- [ ] GET `/api/conversations.php` - Listagem de conversas
- [ ] DELETE `/api/conversation.php` - Exclusão de conversa

#### Frontend Console
- [ ] Interface de chat em tempo real
- [ ] Upload de arquivos com drag & drop
- [ ] Visualização de conversas
- [ ] Exportação de PDF

#### Estimativa
- **Tempo:** 6-8h de desenvolvimento
- **Data prevista:** 2025-10-23

### [v4.0] - Melhorias de UX (Futuro)

- [ ] Busca de conversas
- [ ] Filtros avançados
- [ ] Compartilhamento de conversas
- [ ] Temas personalizáveis
- [ ] Atalhos de teclado

### [v5.0] - Otimizações (Futuro)

- [ ] Cache de respostas Claude (Redis)
- [ ] CDN para assets estáticos
- [ ] Lazy loading de mensagens antigas
- [ ] Compressão de uploads
- [ ] Background jobs para processamento

---

## 📋 Convenções de Versionamento

Este projeto segue [Semantic Versioning](https://semver.org/):

- **MAJOR** (v1.0, v2.0): Mudanças incompatíveis na API
- **MINOR** (v1.1, v1.2): Novas funcionalidades compatíveis
- **PATCH** (v1.1.1, v1.1.2): Correções de bugs

### Tags de Mudança

- ✨ **Adicionado** - Novas funcionalidades
- 🔒 **Segurança** - Correções de vulnerabilidades
- 🐛 **Corrigido** - Correções de bugs
- ⚠️ **Breaking Changes** - Mudanças incompatíveis
- 📚 **Documentação** - Mudanças na documentação
- 🧪 **Testes** - Adição ou correção de testes
- 🚀 **Deployment** - Mudanças no processo de deploy
- 📊 **Métricas** - Estatísticas da versão

---

## 🔗 Links Relacionados

- [COMM-BOARD](https://portal.sunyataconsulting.com/COMM-BOARD.html) - Comunicação técnica
- [GitHub](https://github.com/iflitaiff/plataforma-sunyata) - Código-fonte
- [Documentação](https://portal.sunyataconsulting.com/docs/) - Documentação completa

---

**Última atualização:** 2025-10-22  
**Mantido por:** Claude Code + Manus AI

