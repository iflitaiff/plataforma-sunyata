# Requisitos do Projeto - Plataforma Sunyata

## Visão Geral

Sistema educacional/profissional multi-vertical para integração de IA generativa (Claude) em fluxos de trabalho especializados, com foco inicial na área Jurídica.

---

## Objetivos do Projeto

### Objetivo Primário
Criar uma plataforma onde usuários possam interagir com IA (Claude) de forma **contextualizada** e **iterativa**, usando ferramentas especializadas ("Canvas") que coletam informações estruturadas antes de gerar prompts otimizados.

### Objetivos Secundários
1. **Transparência:** Usuários não veem prompts, apenas interagem naturalmente com a IA
2. **Controle de Custos:** Limitar interações, otimizar uso de tokens
3. **Rastreabilidade:** Admin pode auditar todas interações (LGPD compliant)
4. **Escalabilidade:** Arquitetura multi-vertical (Jurídico, Docência, Pesquisa, etc.)
5. **Reutilização:** Documentos em biblioteca pessoal, conversas exportáveis

---

## Requisitos Funcionais

### RF-001: Autenticação e Onboarding
**Prioridade:** Crítica | **Status:** ✅ Implementado

- **RF-001.1:** Sistema deve permitir login via Google OAuth 2.0
- **RF-001.2:** Após primeiro login, usuário deve completar onboarding:
  - Passo 1: Dados pessoais (nome, instituição, cargo)
  - Passo 2: Seleção de vertical (Jurídico, Docência, Pesquisa, IFRJ)
- **RF-001.3:** Algumas verticais requerem aprovação de admin antes de liberar acesso
- **RF-001.4:** Usuário deve ser notificado sobre status da aprovação
- **RF-001.5:** Admin pode aprovar/rejeitar solicitações via dashboard

**Critérios de Aceitação:**
- ✅ Login funcional com redirect para dashboard após OAuth
- ✅ Onboarding só aparece uma vez (flag `onboarding_completed`)
- ✅ Sistema de aprovação configurável via Settings

---

### RF-002: Sistema de Verticais
**Prioridade:** Crítica | **Status:** ✅ Implementado

- **RF-002.1:** Sistema deve suportar múltiplas verticais configuráveis
- **RF-002.2:** Cada vertical pode ter:
  - Nome, slug, ícone, cor
  - Requisito de aprovação (on/off)
  - Limite de usuários
  - Ferramentas/Canvas específicos
- **RF-002.3:** Usuário só acessa conteúdo da vertical selecionada
- **RF-002.4:** Admin pode alterar configurações de verticais via Settings

**Critérios de Aceitação:**
- ✅ Config centralizada em `config/verticals.php`
- ✅ VerticalManager valida acesso antes de renderizar páginas
- ✅ Dashboard mostra ferramentas filtradas por vertical

---

### RF-003: Console Interativa (MVP)
**Prioridade:** Crítica | **Status:** 🚧 Em Desenvolvimento

- **RF-003.1:** Usuário deve ter acesso a "Console" onde gerencia:
  - Biblioteca de arquivos pessoais
  - Histórico de conversas
  - Uso de créditos/tokens (visualização)
- **RF-003.2:** Console deve exibir:
  - **Sidebar:**
    - 📂 Biblioteca (lista de arquivos)
    - 💬 Conversas (ativas, arquivadas)
    - ➕ Botão "Nova Conversa"
  - **Área Principal:**
    - Chat interativo (quando conversa ativa)
    - Formulário Canvas (ao iniciar nova conversa)
    - Dashboard de métricas (quando nenhuma conversa ativa)

**Critérios de Aceitação:**
- 🔲 Layout responsivo (mobile + desktop)
- 🔲 Navegação fluida entre conversas
- 🔲 Indicadores visuais de estado (ativa, concluída, arquivada)

---

### RF-004: Upload e Gestão de Arquivos
**Prioridade:** Alta | **Status:** 🚧 Planejado

- **RF-004.1:** Usuário pode fazer upload de documentos para biblioteca pessoal
- **RF-004.2:** Tipos suportados (MVP):
  - PDF com texto (não escaneado)
  - DOCX
  - TXT
  - JPG/PNG (Claude faz OCR)
- **RF-004.3:** Validações:
  - Tamanho máximo: 10MB por arquivo
  - MIME type verificado server-side
  - Rejeitar PDFs vazios/escaneados (sem texto extraível)
- **RF-004.4:** Sistema deve extrair texto automaticamente:
  - PDF: via `smalot/pdfparser`
  - DOCX: via `phpoffice/phpword`
  - TXT: leitura direta
- **RF-004.5:** Sistema deve calcular estimativa de tokens
- **RF-004.6:** Deduplicação via hash SHA256 (mesmo arquivo = não re-upload)
- **RF-004.7:** Limites:
  - Max 5 arquivos por conversa
  - Max 100MB total por usuário
  - Retenção indefinida (usuário deleta manualmente)

**Critérios de Aceitação:**
- 🔲 Upload com progress bar
- 🔲 Preview de metadados (nome, tamanho, páginas, tokens estimados)
- 🔲 Mensagem clara ao rejeitar arquivo (ex: "PDF escaneado detectado")
- 🔲 Botão deletar arquivo (com confirmação)

---

### RF-005: Conversas Interativas com IA
**Prioridade:** Crítica | **Status:** 🚧 Planejado

- **RF-005.1:** Usuário inicia conversa preenchendo Canvas (ex: Canvas Jurídico)
- **RF-005.2:** Sistema gera prompt estruturado baseado nos campos do Canvas
- **RF-005.3:** Claude faz perguntas de contexto (máx 5) com marcadores:
  - `[PERGUNTA-1] Texto da pergunta`
  - `[PERGUNTA-2] ...`
- **RF-005.4:** Usuário pode:
  - Responder cada pergunta sequencialmente
  - Clicar "⚡ Resposta Direta" para pular perguntas
  - Editar última resposta e reenviar
- **RF-005.5:** Quando Claude tem contexto suficiente, envia resposta final:
  - `[RESPOSTA-FINAL] Conteúdo da análise completa`
- **RF-005.6:** Sistema detecta tipo de mensagem via regex (marcadores)
- **RF-005.7:** Interface mostra:
  - Contador de perguntas (ex: "Pergunta 2/5")
  - Badge visual (Pergunta vs Resposta Final)
  - Histórico completo da conversa
  - Arquivos anexados (badges no topo)

**Critérios de Aceitação:**
- 🔲 Limite de 5 perguntas respeitado
- 🔲 Botão "Pular" funcional (gera resposta imediata)
- 🔲 Edição da última mensagem apaga resposta Claude seguinte
- 🔲 Marcadores `[PERGUNTA-N]` e `[RESPOSTA-FINAL]` detectados corretamente
- 🔲 Conversa salva em `conversations` + `conversation_messages`

---

### RF-006: Anexação de Arquivos em Conversas
**Prioridade:** Alta | **Status:** 🚧 Planejado

- **RF-006.1:** Ao iniciar conversa, usuário pode anexar arquivos da biblioteca
- **RF-006.2:** Arquivos anexados têm texto extraído e enviado para Claude
- **RF-006.3:** Claude tem acesso ao conteúdo dos documentos no contexto
- **RF-006.4:** Usuário pode anexar até 5 arquivos por conversa
- **RF-006.5:** Sistema mostra lista de arquivos anexados com:
  - Nome do arquivo
  - Ícone do tipo (PDF, DOCX, etc.)
  - Botão "Remover" (antes de iniciar chat)

**Critérios de Aceitação:**
- 🔲 Modal de seleção de arquivos da biblioteca
- 🔲 Texto extraído aparece no prompt enviado para Claude
- 🔲 Limite de 5 arquivos validado
- 🔲 Mensagem de erro se arquivo sem texto (escaneado)

---

### RF-007: Exportação de Conversas
**Prioridade:** Média | **Status:** 🚧 Planejado

- **RF-007.1:** Usuário pode exportar conversa completa após resposta final
- **RF-007.2:** Formatos suportados (MVP):
  - TXT (plain text, mais simples)
  - PDF (formatado com mPDF)
- **RF-007.3:** Export deve incluir:
  - Título da conversa
  - Data/hora
  - Histórico completo (perguntas + respostas)
  - Lista de arquivos anexados (nomes)
  - Footer com "Gerado via Plataforma Sunyata"
- **RF-007.4:** Arquivo baixado com nome descritivo:
  - Ex: `conversa_123_revisao_contrato_20250121.pdf`

**Critérios de Aceitação:**
- 🔲 Botão "Exportar" aparece após `[RESPOSTA-FINAL]`
- 🔲 Modal com opções TXT/PDF
- 🔲 Download automático após geração
- 🔲 PDF bem formatado (títulos, parágrafos, quebras de linha)

---

### RF-008: Gestão de Conversas
**Prioridade:** Média | **Status:** 🚧 Planejado

- **RF-008.1:** Usuário pode visualizar lista de conversas:
  - Ordenadas por data (mais recentes primeiro)
  - Status: ativa, concluída, arquivada
- **RF-008.2:** Usuário pode:
  - Deletar conversa (com confirmação)
  - Arquivar conversa (mover para "Arquivadas")
  - Favoritar conversa (⭐, opcional Fase 2)
  - Renomear conversa (opcional Fase 2)
- **RF-008.3:** Admin pode ver todas conversas de todos usuários (auditoria)
- **RF-008.4:** Histórico de conversa deletada pelo usuário permanece acessível ao admin

**Critérios de Aceitação:**
- 🔲 Lista de conversas com paginação (20 por página)
- 🔲 Botão deletar pede confirmação
- 🔲 Soft delete (flag `deleted_at`, usuário não vê mais)
- 🔲 Admin vê todas conversas incluindo deletadas

---

### RF-009: Admin - Dashboard
**Prioridade:** Crítica | **Status:** ✅ Implementado

- **RF-009.1:** Admin tem acesso a dashboard com:
  - Total de usuários (por vertical)
  - Solicitações de acesso pendentes
  - Estatísticas de uso de IA (tokens, custo)
  - Espaço em disco (uploads)
- **RF-009.2:** Admin pode gerenciar usuários:
  - Listar todos usuários
  - Ver detalhes de perfil
  - Deletar usuário (transação atômica, LGPD)
- **RF-009.3:** Admin pode configurar Settings:
  - Toggle de aprovação por vertical
  - Limites de uso
  - Features flags

**Critérios de Aceitação:**
- ✅ Dashboard responsivo
- ✅ Estatísticas em tempo real (query ao banco)
- ✅ Botões de ação funcionais

---

### RF-010: Sistema de Settings Dinâmico
**Prioridade:** Alta | **Status:** ✅ Implementado

- **RF-010.1:** Settings armazenados em tabela `settings`
- **RF-010.2:** Classe `Settings` (singleton) com cache em memória
- **RF-010.3:** Métodos:
  - `get($key, $default)` - Obter valor
  - `set($key, $value)` - Definir valor
  - `toggle($key)` - Inverter boolean
- **RF-010.4:** Settings gerenciados via admin dashboard

**Critérios de Aceitação:**
- ✅ Settings persistem no banco
- ✅ Cache evita queries repetidas
- ✅ Interface admin para toggle

---

### RF-011: LGPD - Deleção de Dados
**Prioridade:** Crítica | **Status:** ✅ Implementado

- **RF-011.1:** Usuário pode deletar própria conta via dashboard
- **RF-011.2:** Processo de deleção em 2 etapas:
  1. Aviso sobre irreversibilidade
  2. Confirmação digitando "DELETAR"
- **RF-011.3:** Deleção deve ser atômica (transação):
  - user_profiles
  - vertical_access_requests
  - consents
  - tool_access_logs
  - conversations (quando implementado)
  - conversation_messages
  - user_files (arquivos em disco também)
  - Usuário final (users)
- **RF-011.4:** Logs de auditoria devem ser anonimizados (não deletados)
- **RF-011.5:** Admin não pode auto-deletar (proteção)

**Critérios de Aceitação:**
- ✅ Transação rollback em caso de erro
- ✅ Página de confirmação pós-deleção
- ✅ Arquivos em disco deletados

---

### RF-012: Integração Claude API
**Prioridade:** Crítica | **Status:** ✅ Implementado (parcial)

- **RF-012.1:** Sistema deve chamar Claude Messages API
- **RF-012.2:** Modelo padrão: `claude-3-5-sonnet-20241022`
- **RF-012.3:** Salvar histórico transparente:
  - Prompt gerado (não visível ao usuário)
  - Resposta Claude
  - Tokens (input, output, total)
  - Custo estimado (USD)
  - Status (success, error)
- **RF-012.4:** Error handling:
  - Rate limits (retry com backoff)
  - Timeout (120s)
  - Erros de API (exibir mensagem genérica ao usuário)

**Critérios de Aceitação:**
- ✅ ClaudeService funcional
- 🔲 Suporte a histórico de conversas (context messages) - MVP Console
- 🔲 Prompt Caching (futura otimização)

---

## Requisitos Não-Funcionais

### RNF-001: Performance
**Prioridade:** Alta

- **RNF-001.1:** Tempo de resposta da UI: < 200ms (exceto chamadas IA)
- **RNF-001.2:** Chamada Claude API: timeout de 120s
- **RNF-001.3:** Upload de arquivo: < 5s para 10MB
- **RNF-001.4:** Queries ao banco: < 100ms (95th percentile)
- **RNF-001.5:** Página deve carregar completa em < 2s (3G)

**Métricas:**
- Monitorar via logs de performance (futuro)
- Audit logs com timestamps

---

### RNF-002: Segurança
**Prioridade:** Crítica

- **RNF-002.1:** HTTPS obrigatório em produção
- **RNF-002.2:** Senhas NUNCA armazenadas (OAuth only)
- **RNF-002.3:** API Keys em `secrets.php` (fora de versionamento)
- **RNF-002.4:** CSRF tokens em todos formulários
- **RNF-002.5:** Prepared statements em 100% das queries
- **RNF-002.6:** Sanitização de outputs (`htmlspecialchars`)
- **RNF-002.7:** Upload validation server-side (MIME type, tamanho)
- **RNF-002.8:** Rate limiting em endpoints de IA (futuro)

**Critérios de Aceitação:**
- ✅ Nenhuma vulnerabilidade OWASP Top 10
- ✅ SSL/TLS válido em produção
- ✅ Headers de segurança (X-Frame-Options, CSP)

---

### RNF-003: Escalabilidade
**Prioridade:** Média

- **RNF-003.1:** Suportar até 1000 usuários simultâneos
- **RNF-003.2:** Banco de dados normalizado (3NF)
- **RNF-003.3:** Índices em foreign keys e campos filtrados
- **RNF-003.4:** Upload de arquivos com streaming (não carregar tudo em memória)
- **RNF-003.5:** Paginação em listas > 50 itens

**Critérios de Aceitação:**
- 🔲 Load test com 100 usuários simultâneos (futuro)
- 🔲 Queries otimizadas (EXPLAIN)

---

### RNF-004: Usabilidade
**Prioridade:** Alta

- **RNF-004.1:** Interface responsiva (mobile, tablet, desktop)
- **RNF-004.2:** Feedback visual em todas ações:
  - Loading spinners
  - Mensagens de sucesso/erro
  - Progress bars (upload)
- **RNF-004.3:** Mensagens de erro claras e acionáveis
- **RNF-004.4:** Navegação intuitiva (max 3 cliques para qualquer funcionalidade)
- **RNF-004.5:** Acessibilidade básica (ARIA labels, contraste de cores)

**Critérios de Aceitação:**
- 🔲 Teste com usuário real (piloto)
- 🔲 Score Lighthouse > 80 (Performance, Accessibility)

---

### RNF-005: Manutenibilidade
**Prioridade:** Alta

- **RNF-005.1:** Código documentado (PHPDoc em classes/métodos)
- **RNF-005.2:** Separação de responsabilidades (Services, Controllers, Views)
- **RNF-005.3:** Naming conventions consistentes
- **RNF-005.4:** Logs de erro estruturados
- **RNF-005.5:** Migrations versionadas (SQL files numerados)

**Critérios de Aceitação:**
- ✅ PSR-4 autoloading
- ✅ Classes com responsabilidade única
- ✅ README com instruções de setup

---

### RNF-006: Disponibilidade
**Prioridade:** Média

- **RNF-006.1:** Uptime > 99% (monitorado via Hostinger)
- **RNF-006.2:** Backups diários do banco (automatizado)
- **RNF-006.3:** Plano de rollback (migrations reversíveis)
- **RNF-006.4:** Error handling gracioso (não quebrar página inteira)

**Critérios de Aceitação:**
- 🔲 Backup automático configurado
- 🔲 Teste de restore de backup

---

### RNF-007: Compliance (LGPD)
**Prioridade:** Crítica

- **RNF-007.1:** Termo de consentimento no onboarding
- **RNF-007.2:** Direito ao esquecimento (deleção de dados)
- **RNF-007.3:** Direito à portabilidade (export conversas)
- **RNF-007.4:** Transparência (usuário sabe que dados são coletados)
- **RNF-007.5:** Logs de auditoria para ações críticas
- **RNF-007.6:** Dados sensíveis não expostos em logs/erros

**Critérios de Aceitação:**
- ✅ Deleção funcional
- 🔲 Export de dados pessoais (JSON) - Fase 2
- 🔲 Política de privacidade acessível

---

## Restrições e Limitações

### Técnicas
1. **Hostinger Premium:** Sem root access, sem Tesseract OCR, sem ferramentas CLI customizadas
2. **PHP 8.2:** Versão mínima do servidor
3. **MySQL:** Não pode usar PostgreSQL ou NoSQL
4. **Filesystem local:** Armazenamento de arquivos (não S3 inicialmente)

### Negócio
1. **Custo de API:** Claude API pago por token (~$0.15/conversa)
2. **Limite de storage:** 100MB/usuário inicialmente
3. **MVP scope:** Apenas vertical Jurídico completamente funcional

### Temporais
1. **MVP Console:** 6-8 horas de desenvolvimento
2. **Fase 1.5 (Créditos):** +2 semanas após MVP
3. **VPS migration:** Apenas se necessário (não prioritário)

---

## Casos de Uso Principais

### UC-001: Advogado Analisa Contrato
**Ator:** Usuário (vertical Jurídico)

**Pré-condição:** Usuário autenticado, onboarding completo, vertical aprovada

**Fluxo Principal:**
1. Usuário acessa Console Jurídica
2. Faz upload de `contrato_locacao.pdf` para biblioteca
3. Clica "Nova Conversa"
4. Seleciona "Canvas Jurídico"
5. Preenche formulário:
   - Tarefa: "Revisar cláusulas de rescisão"
   - Contexto: "Contrato B2B, 30 meses"
   - Anexa `contrato_locacao.pdf` da biblioteca
6. Clica "Iniciar Análise"
7. Claude faz 3 perguntas de contexto
8. Usuário responde cada pergunta
9. Claude entrega análise jurídica completa
10. Usuário exporta em PDF
11. Usuário arquiva conversa

**Fluxo Alternativo 1:** Usuário clica "Pular" após primeira pergunta → Claude gera resposta imediata

**Fluxo Alternativo 2:** Usuário edita última resposta → Sistema reprocessa sem a resposta Claude seguinte

**Pós-condição:** Conversa salva em `conversations`, exportável, histórico auditável

---

### UC-002: Admin Aprova Solicitação de Acesso
**Ator:** Admin

**Pré-condição:** Há solicitações pendentes

**Fluxo Principal:**
1. Admin faz login
2. Acessa dashboard `/admin/`
3. Vê card "3 solicitações pendentes"
4. Clica "Ver solicitações"
5. Vê lista com nome, email, vertical, data
6. Clica "Aprovar" em usuário X
7. Sistema atualiza status para `approved`
8. Usuário X recebe acesso à vertical

**Fluxo Alternativo:** Admin clica "Rejeitar" → usuário fica bloqueado

**Pós-condição:** Solicitação processada, log de auditoria criado

---

### UC-003: Usuário Deleta Própria Conta
**Ator:** Usuário (não-admin)

**Pré-condição:** Usuário autenticado

**Fluxo Principal:**
1. Usuário acessa menu do perfil
2. Clica "Deletar Conta"
3. Vê aviso sobre irreversibilidade
4. Clica "Prosseguir com Deleção"
5. Sistema exibe tela de confirmação
6. Usuário digita "DELETAR"
7. Clica "Deletar Minha Conta Permanentemente"
8. Sistema executa deleção atômica
9. Sessão destruída
10. Redirect para página de confirmação

**Fluxo Alternativo:** Usuário é admin → Sistema rejeita (mensagem de erro)

**Pós-condição:** Todos dados do usuário deletados, exceto logs anonimizados

---

## Glossário

- **Vertical:** Área de especialização (Jurídico, Docência, Pesquisa, IFRJ)
- **Canvas:** Ferramenta interativa que coleta informações estruturadas para gerar prompts otimizados
- **Console:** Interface centralizada do usuário para gerenciar conversas, arquivos e uso
- **Marcadores:** Strings especiais usadas pelo Claude para sinalizar tipo de mensagem (`[PERGUNTA-N]`, `[RESPOSTA-FINAL]`)
- **Thread/Conversa:** Sequência de mensagens (user + assistant) sobre um tópico específico
- **Biblioteca:** Coleção de arquivos pessoais do usuário, reutilizáveis em múltiplas conversas
- **Créditos:** Sistema de cotas futuro (Fase 1.5) para controlar uso da API Claude
- **Soft Delete:** Registro marcado como deletado (`deleted_at`) mas não removido fisicamente do banco

---

**Versão:** 1.0
**Data:** 2025-01-21
**Autor:** Prof. Filipe Litaiff + Claude Code
