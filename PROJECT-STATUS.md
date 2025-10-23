# Status do Projeto - Plataforma Sunyata

## ✅ Implementado e Funcional (v1.0)

### Sistema Base
- ✅ Autenticação Google OAuth 2.0
- ✅ Gestão de sessões com CSRF protection
- ✅ Sistema de verticais (Jurídico, Docência, Pesquisa, IFRJ)
- ✅ Onboarding em 2 etapas (perfil + vertical)
- ✅ Dashboard por vertical
- ✅ Sistema de aprovação de acesso (configurável via Settings)

### Admin
- ✅ Dashboard administrativo completo
- ✅ Gerenciamento de usuários
- ✅ Aprovação/rejeição de solicitações de acesso
- ✅ Sistema de Settings dinâmico (toggle de configurações)
- ✅ Deleção segura de usuários (LGPD compliant)
- ✅ CLI tools para administração via SSH

### Integração IA
- ✅ Integração com Claude API (Messages API)
- ✅ ClaudeService com histórico transparente
- ✅ Cálculo automático de tokens e custos
- ✅ Canvas Jurídico (ferramenta de análise jurídica)
- ✅ API endpoint `/api/generate-juridico.php`
- ✅ Histórico de prompts (`prompt_history` table)

### Segurança & LGPD
- ✅ Deleção de conta pelo usuário (2 etapas)
- ✅ Anonimização de logs de auditoria
- ✅ UserDeletionService com transações atômicas
- ✅ Página de confirmação pós-deleção

### Infraestrutura
- ✅ Deployment em Hostinger Premium (PHP 8.2)
- ✅ MySQL/MariaDB database
- ✅ Composer autoloading (PSR-4)
- ✅ Bootstrap 5 UI
- ✅ Commits no GitHub (histórico limpo)

---

## 🚧 Em Desenvolvimento (v1.1 - MVP Console)

### Console Interativa
- 🔨 **Página principal:** `/areas/juridico/console.php`
  - Sidebar com biblioteca de arquivos
  - Lista de conversas
  - Chat interativo
  - Status: **NÃO INICIADO**

### Sistema de Upload de Arquivos
- 🔨 **FileUploadService:**
  - Upload para biblioteca pessoal
  - Validação (MIME, tamanho, hash)
  - Armazenamento `/uploads/user_{id}/`
  - Status: **NÃO INICIADO**

- 🔨 **DocumentProcessorService:**
  - Extração de texto (PDF/DOCX/TXT)
  - Integração com `smalot/pdfparser`, `phpoffice/phpword`
  - Estimativa de tokens
  - Status: **NÃO INICIADO**

### Conversas Interativas
- 🔨 **ConversationService:**
  - Criar/gerenciar conversas
  - Adicionar mensagens
  - Detectar tipos de mensagem (marcadores)
  - Status: **NÃO INICIADO**

- 🔨 **API Chat:** `/api/chat.php`
  - Endpoint de conversação
  - Integração com Claude
  - Histórico contextual
  - Status: **NÃO INICIADO**

### Export
- 🔨 **Export de conversas:**
  - Formato TXT (simples)
  - Formato PDF (mPDF)
  - Status: **NÃO INICIADO**

### Database
- 🔨 **Migrations:**
  - `004_conversations_system.sql`
  - Tables: `conversations`, `conversation_messages`, `user_files`, `conversation_files`
  - Status: **NÃO INICIADO**

### Bibliotecas
- 🔨 **Composer packages:**
  - `smalot/pdfparser`
  - `phpoffice/phpword`
  - `mpdf/mpdf`
  - Status: **NÃO INSTALADO**

---

## 📋 Backlog (Fases Futuras)

### Fase 1.5: Sistema de Créditos
- ⏳ Tabela `user_credits`
- ⏳ Admin atribuir/recarregar créditos
- ⏳ Bloqueio quando créditos acabam
- ⏳ Interface de compra/recarga
- ⏳ Dashboard de uso por usuário

### Fase 2: Melhorias de UX
- ⏳ Edição de histórico completo (não só última mensagem)
- ⏳ Preview inline de PDFs (PDF.js)
- ⏳ Drag & drop para upload
- ⏳ Busca de conversas
- ⏳ Tags/categorização de conversas

### Fase 3: RAG Avançado
- ⏳ Vector embeddings (OpenAI/Cohere)
- ⏳ Vector database (Qdrant/ChromaDB)
- ⏳ Envio de trechos relevantes (não documento completo)
- ⏳ Redução de custo ~90%

### Fase 4: Outras Verticais
- ⏳ Canvas Docente (planejamento de aulas)
- ⏳ Canvas Pesquisa (revisão de literatura)
- ⏳ Canvas IFRJ (específico alunos)
- ⏳ Ferramentas customizadas por vertical

### Fase 5: Colaboração
- ⏳ Compartilhamento de conversas
- ⏳ Comentários em conversas
- ⏳ Workspaces para equipes
- ⏳ Permissões granulares

### Fase 6: Infraestrutura (VPS)
- ⏳ Migração para VPS (se necessário)
- ⏳ OCR de PDFs escaneados (Tesseract)
- ⏳ Processamento de formatos complexos
- ⏳ Self-hosted RAG
- ⏳ Docker deployment

---

## 🐛 Issues Conhecidos

### Críticos
- Nenhum no momento

### Médios
- ⚠️ Canvas Jurídico atual não salva conversas (one-shot)
  - **Mitigação:** MVP Console resolverá isso

### Baixos
- ⚠️ Admin dashboard pode ter cache (LiteSpeed)
  - **Mitigação:** `.htaccess` com `CacheLookup off` deployado
- ⚠️ Alguns arquivos de debug em produção
  - **Ação:** Remover `debug-info.php`, `clear-cache.php` pós-testes

---

## 📊 Métricas Atuais

### Código
- **Linhas de código:** ~7.400 adicionadas (Git stats)
- **Arquivos PHP:** ~40
- **Classes principais:** 8 (Database, Settings, ClaudeService, etc.)
- **Endpoints API:** 2 (`/api/generate-juridico.php`, `/api/upload-file.php` planejado)

### Database
- **Tabelas:** 10 (+ 4 planejadas no MVP)
- **Usuários cadastrados:** Variável por ambiente
- **Verticais ativas:** 4 (Jurídico, Docência, Pesquisa, IFRJ)

### Custos IA (Produção)
- **Modelo:** Claude 3.5 Sonnet (20241022)
- **Custo médio/conversa:** ~$0.15 (estimado)
- **Tokens médios/conversa:** ~35k (input + output)
- **Status:** Monitorado via `prompt_history` table

---

## 🎯 Próximos Passos Imediatos

### Sprint MVP Console (Estimativa: 6-8 horas)

#### Dia 1 (2-3h)
1. ✅ Instalar bibliotecas Composer
2. ✅ Criar migration `004_conversations_system.sql`
3. ✅ Aplicar migration localmente
4. ✅ Criar `FileUploadService.php` (esqueleto)
5. ✅ Criar `DocumentProcessorService.php` (esqueleto)
6. ✅ Criar `ConversationService.php` (esqueleto)
7. ✅ Testes unitários básicos (syntax check)

#### Dia 2 (2-3h)
8. ✅ Implementar `/api/upload-file.php`
9. ✅ Implementar `/api/chat.php`
10. ✅ Testar endpoints via cURL
11. ✅ Deploy em ambiente de staging (local)

#### Dia 3 (2-3h)
12. ✅ Criar `/areas/juridico/console.php` (HTML + Bootstrap)
13. ✅ Implementar `/assets/js/console-chat.js`
14. ✅ Integrar Canvas Jurídico com Console
15. ✅ Testes end-to-end manuais

#### Dia 4 (30min)
16. ✅ Implementar `/api/export-conversation.php` (TXT)
17. ✅ Deploy em produção (Hostinger)
18. ✅ Testes em produção
19. ✅ Documentar MVP

---

## 📝 Decisões Técnicas Pendentes

### Resolvidas (Sessão 21/01/2025)
- ✅ **Edição de mensagens:** Apenas última (MVP)
- ✅ **Sistema de créditos:** Fase 1.5
- ✅ **Upload durante chat:** Apenas no início (MVP)
- ✅ **Export:** TXT (mais simples para MVP)
- ✅ **Resposta como entrada:** Fase 2
- ✅ **Limite de perguntas:** 5 máximo
- ✅ **Marcadores Claude:** `[PERGUNTA-N]` e `[RESPOSTA-FINAL]`
- ✅ **Biblioteca de arquivos:** Híbrido (pessoal + vinculado a conversa)

### A Definir (Durante Implementação)
- ⏳ Título auto-gerado para conversas (usar primeiros 50 chars da tarefa?)
- ⏳ Comportamento ao atingir 5 perguntas (forçar resposta final?)
- ⏳ Feedback visual durante processamento (skeleton loader?)
- ⏳ Estratégia de paginação para lista de conversas (infinite scroll ou paginação tradicional?)

---

## 🔗 Links Importantes

- **Repositório:** https://github.com/iflitaiff/plataforma-sunyata
- **Produção:** https://portal.sunyataconsulting.com/
- **Documentação Claude API:** https://docs.anthropic.com/claude/reference
- **Hostinger SSH:** `ssh -p 65002 u202164171@82.25.72.226`

---

## 👥 Equipe

- **Desenvolvimento:** Prof. Filipe Litaiff, PhD + Claude Code (Anthropic)
- **Infraestrutura:** Hostinger Premium Web Hosting
- **AI Provider:** Anthropic Claude API

---

**Última atualização:** 2025-01-21 (pré-implementação MVP Console)
**Próxima revisão:** Após conclusão Sprint MVP Console
