# Changelog

Todas as mudanças notáveis neste projeto serão documentadas neste arquivo.

O formato é baseado em [Keep a Changelog](https://keepachangelog.com/pt-BR/1.0.0/),
e este projeto adere ao [Semantic Versioning](https://semver.org/lang/pt-BR/).

## [1.0.0-mvp] - 2025-10-10

### 🎉 Lançamento MVP - Sistema de Verticais Completo

Primeira versão funcional da plataforma com sistema completo de verticais, onboarding e controle de acesso.

### ✨ Adicionado

#### Sistema de Verticais
- Sistema completo de 9 verticais (4 ativas + 5 futuras)
- Controle de acesso por vertical (usuários veem apenas sua vertical)
- Administradores têm acesso a todas as verticais
- Páginas "Em breve" para verticais futuras (Vendas, Marketing, Licitações, RH, Geral)

#### Verticais Ativas
- **Docência**: Canvas Docente, Canvas Pesquisa, Biblioteca Prompts Jogos, Guia Prompts Jogos
- **Pesquisa**: Canvas Docente, Canvas Pesquisa
- **IFRJ-Alunos**: Canvas Pesquisa, Biblioteca Prompts Jogos, Guia Prompts Jogos (com coleta de dados extras: nível de ensino e curso)
- **Jurídico**: Canvas Jurídico, Guia Prompts Jurídico, Padrões Avançados Jurídico (requer aprovação admin)

#### Fluxo de Onboarding
- **Step 1**: Coleta de dados pessoais/profissionais (phone, position, organization, organization_size, area)
- **Step 2**: Seleção de vertical com cards interativos
- **Fluxo IFRJ**: Coleta adicional de nível de ensino (médio/superior) e nome do curso
- **Fluxo Jurídico**: Sistema de solicitação com OAB, escritório e motivo

#### Painel Administrativo (`/admin/`)
- **Dashboard**: Estatísticas de usuários, acessos e solicitações pendentes
- **Gestão de Usuários**: Lista com filtros por nível, vertical e busca por nome/email
- **Solicitações de Acesso**: Aprovação/rejeição de solicitações para verticais restritas
- **Logs de Auditoria**: Visualização completa de ações do sistema com filtros
- Acesso multi-vertical para administradores (badge "Modo Admin" nas verticais)

#### Ferramentas
- 7 ferramentas HTML implementadas e funcionais
- Sistema de gateway PHP para controle de acesso por ferramenta
- Logging de acesso às ferramentas em `tool_access_logs`

#### Database
- Migration completa: `001_vertical_system.sql`
- Tabelas novas:
  - `user_profiles`: Dados adicionais do onboarding
  - `vertical_access_requests`: Solicitações de acesso a verticais
  - `tool_access_logs`: Analytics de uso de ferramentas
  - `tool_versions`: Controle de versões de ferramentas (preparado para futuro)
- Campos adicionados em `users`:
  - `selected_vertical`: Vertical escolhida pelo usuário
  - `completed_onboarding`: Flag de conclusão do onboarding
  - `is_demo`: Flag para usuários demo (acesso a todas verticais)
- ENUM fields atualizados em todas as tabelas para as 9 verticais
- Índices criados para otimização de queries

#### Autenticação e Sessão
- GoogleAuth atualizado para incluir `selected_vertical` e `completed_onboarding` na sessão
- Criação automática de sessão completa no primeiro login
- Session management aprimorado com persistência correta

#### Scripts e Utilitários
- `scripts/apply-migration.php`: Aplicar migrações de banco
- `scripts/backup-database.php`: Backup automático do banco
- `scripts/generate-tool-gateways.php`: Gerador de arquivos gateway para ferramentas
- `public/clear-cache.php`: Limpeza de OPcache e realpath cache

#### Documentação
- README.md completo com instalação, configuração e troubleshooting
- CHANGELOG.md para tracking de versões
- Documentação inline em todos os arquivos PHP

### 🔧 Corrigido

#### Bugs Críticos
- **Column case sensitivity**: Adicionado `PDO::ATTR_CASE => PDO::CASE_LOWER` para consistência (fix para buttons de aprovação não aparecerem)
- **Naming consistency**: Corrigido `ifrj-alunos` (hyphen) para `ifrj_alunos` (underscore) em todos os arquivos
- **Session persistence**: Inicialização correta de `$_SESSION['user']` antes de atualizar valores
- **Canvas Pesquisa duplication**: Fix no bug de `foreach` com referência sem `unset()`
- **Admin tool access**: Adicionado `&& !$is_admin` em todos os 16 gateway files de ferramentas
- **HTTP 500 errors**: Corrigido paths relativos incorretos em arquivos de verticais
- **Database insert bug**: Corrigido uso de `insert()` return value (lastInsertId) ao invés de método inexistente

#### Funcionalidades
- Redirect correto após onboarding IFRJ para `/areas/ifrj_alunos/`
- Validação e sanitização de inputs em todos os formulários
- CSRF protection implementado em todos os POSTs
- Tratamento de erros melhorado com mensagens amigáveis

### 🗑️ Removido
- Diretórios obsoletos: `public/areas/direito/` e `public/areas/pesquisa-ensino/`
- Código legado de sistema de verticais antigo
- Arquivos de teste temporários

### 🔐 Segurança
- PDO prepared statements em todas as queries
- CSRF tokens em todos os formulários
- Session hijacking protection
- Input sanitization com `sanitize_output()`
- SQL injection protection via PDO
- XSS protection via output escaping

### 📊 Base de Dados
**Backup MVP criado**: `backup_mvp_20251010_183852.sql` (57KB)

### 🧪 Testes Realizados
- ✅ Onboarding completo (todos os fluxos)
- ✅ IFRJ onboarding com dados extras
- ✅ Jurídico: solicitação → aprovação → acesso end-to-end
- ✅ Admin pode acessar todas as verticais
- ✅ Admin pode acessar todas as ferramentas
- ✅ Usuários regulares veem apenas sua vertical
- ✅ Tool access control funcional
- ✅ Session management persistente

### 📦 Dependências
- PHP 8.2+
- MySQL 5.7+ / MariaDB 10.2+
- Composer 2.0+
- Extensões: curl, json, pdo, pdo_mysql, mbstring, session

### 🔗 Links
- **GitHub Release**: [v1.0.0-mvp](https://github.com/iflitaiff/plataforma-sunyata/releases/tag/v1.0.0-mvp)
- **Produção**: https://portal.sunyataconsulting.com

---

## [Unreleased]

### Planejado para V1.1
- Repositório de Prompts funcional com busca
- Workspace de prompts salvos pelo usuário
- Notificações por email
- Edição de perfil de usuário

---

**Legenda:**
- `Adicionado` para novas funcionalidades
- `Mudado` para alterações em funcionalidades existentes
- `Depreciado` para funcionalidades que serão removidas
- `Removido` para funcionalidades removidas
- `Corrigido` para correções de bugs
- `Segurança` para correções de vulnerabilidades
