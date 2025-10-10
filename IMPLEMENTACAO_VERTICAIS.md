# Sistema de Verticais - Implementação Completa

## 📋 Resumo

Este documento descreve a implementação completa do **Sistema de Verticais** na Plataforma Sunyata, transformando o portal de acesso universal para um sistema segmentado por áreas de atuação.

**Data de Implementação:** Outubro 2025
**Versão:** 1.0

---

## ✅ O que foi implementado

### FASE 1: Urgente (✅ COMPLETO)

#### ✅ SUBTAREFA 1: Schema do Banco de Dados

**Arquivos criados:**
- `config/migrations/001_vertical_system.sql` - Migration SQL com todas as alterações
- `scripts/apply-migration.php` - Script CLI para aplicar migrations de forma segura
- `config/migrations/README.md` - Documentação completa do sistema de migrations

**Alterações no banco:**

1. **Tabela `users`** - 3 novas colunas:
   ```sql
   selected_vertical ENUM('docencia', 'pesquisa', 'ifrj_alunos', 'juridico', 'vendas', 'marketing', 'licitacoes', 'rh', 'geral')
   completed_onboarding BOOLEAN DEFAULT FALSE
   is_demo BOOLEAN DEFAULT FALSE
   ```

2. **Nova tabela `user_profiles`:**
   - Dados estendidos do usuário (telefone, cargo, organização, etc)
   - Campos específicos para IFRJ (nível de ensino, curso)

3. **Nova tabela `vertical_access_requests`:**
   - Solicitações de acesso a verticais que requerem aprovação (Jurídico)
   - Status: pending, approved, rejected
   - JSON field para dados flexíveis da solicitação

4. **Nova tabela `tool_access_logs`:**
   - Log de cada acesso a ferramentas
   - Para analytics e LGPD
   - Campos: user_id, tool_slug, vertical, IP, user_agent, timestamp

5. **Nova tabela `tool_versions`:**
   - Versionamento de arquivos HTML das ferramentas
   - Para rollback e histórico

6. **View `v_tool_access_stats`:**
   - Estatísticas pré-calculadas para analytics

#### ✅ SUBTAREFA 2: Fluxo de Onboarding

**Arquivos criados:**

1. **`public/onboarding-step1.php`**
   - Coleta dados pessoais/profissionais após primeiro login Google
   - Campos: phone, position (obrigatório), organization, organization_size, area
   - Salva em `user_profiles`

2. **`public/onboarding-step2.php`**
   - Interface visual para escolha de vertical
   - Cards com descrição, ícones e ferramentas de cada vertical
   - Badges: "Em breve", "Requer aprovação"
   - JavaScript para roteamento inteligente

3. **`public/onboarding-save-vertical.php`**
   - Processa verticais diretas (não requerem aprovação/info extra)
   - Atualiza `users.selected_vertical` e `completed_onboarding`
   - Redireciona para área da vertical

4. **`public/onboarding-ifrj.php`**
   - Formulário específico para alunos do IFRJ
   - Campos: ifrj_level (ensino_medio/superior), ifrj_course
   - Salva em `user_profiles` e completa onboarding

5. **`public/onboarding-juridico.php`**
   - Formulário de solicitação para vertical Jurídico
   - Campos: profissao, oab (opcional, com validação), escritorio, motivo
   - Envia email para admin
   - Salva em `vertical_access_requests` com status pending
   - Sugere escolher outra vertical enquanto aguarda

**Integração:**
- ✅ **`public/callback.php`** atualizado para verificar `completed_onboarding`
- Redireciona automaticamente para onboarding se necessário

#### ✅ SUBTAREFA 3: Estrutura de Verticais

**Diretórios criados:**
```
public/areas/
├── docencia/
├── pesquisa/
├── ifrj-alunos/
├── juridico/
├── vendas/
├── marketing/
├── licitacoes/
├── rh/
└── geral/
```

**Arquivos gerados:**

1. **Script `scripts/generate-vertical-indexes.php`**
   - Gera automaticamente `index.php` para todas as 9 verticais
   - Verticais disponíveis: interface completa com grid de ferramentas
   - Verticais indisponíveis: página "Em breve" estilizada

2. **Script `scripts/generate-tool-gateways.php`**
   - Gera automaticamente gateways PHP para todas as ferramentas
   - Total: **16 gateways criados**
   - Cada gateway:
     - Verifica login e acesso à vertical
     - Registra acesso em `tool_access_logs`
     - Embeda HTML da ferramenta via `readfile()`
     - Tratamento de erros (404 se ferramenta não existe)

**Mapeamento Ferramenta → Vertical:**
- **Docência:** canvas-docente, canvas-pesquisa, biblioteca-prompts-jogos, guia-prompts-jogos, repositorio-prompts
- **Pesquisa:** canvas-docente, canvas-pesquisa, repositorio-prompts
- **IFRJ-Alunos:** biblioteca-prompts-jogos, guia-prompts-jogos, canvas-pesquisa, repositorio-prompts
- **Jurídico:** canvas-juridico, guia-prompts-juridico, padroes-avancados-juridico, repositorio-prompts

#### ✅ SUBTAREFA 4: Controle de Acesso

**Arquivo atualizado:** `config/auth.php`

**Funções criadas:**

1. **`has_vertical_access(string $vertical): bool`**
   - Verifica se usuário tem acesso a uma vertical específica
   - Considera usuários demo (acesso total)

2. **`has_tool_access(string $tool_slug): bool`**
   - Verifica se usuário tem acesso a uma ferramenta específica
   - Usa mapeamento tool→verticals

3. **`get_user_tools(): array`**
   - Retorna lista de ferramentas disponíveis para o usuário
   - Baseado na vertical selecionada
   - Retorna array com metadados (id, nome, descrição, ícone, url)

**Dashboard atualizado:** `public/dashboard.php`
- Substituída seção hardcoded de ferramentas por sistema dinâmico
- Usa `get_user_tools()` para filtrar ferramentas
- Mostra vertical do usuário e badge "Modo Demo" se aplicável
- Link direto para área da vertical
- Alerta se onboarding pendente

#### ✅ SUBTAREFA 5: Monitoramento Básico

**Arquivo criado:** `public/admin/analytics.php`

**Funcionalidades:**

1. **Estatísticas Gerais** (cards):
   - Total de usuários
   - Usuários que completaram onboarding
   - Acessos últimos 30 dias
   - Total de acessos histórico

2. **Solicitações Pendentes**:
   - Lista de solicitações de acesso ao Jurídico
   - Detalhes expandíveis (profissão, OAB, escritório, motivo)
   - Badge de contagem

3. **Top 10 Ferramentas** (últimos 30 dias):
   - Ordenado por número de acessos
   - Mostra total de acessos e usuários únicos

4. **Acessos por Vertical** (últimos 30 dias):
   - Agrupado por vertical
   - Total de acessos e usuários únicos

5. **Distribuição de Usuários**:
   - Cards com total de usuários por vertical

6. **Últimos 50 Acessos**:
   - Tabela com timestamp, usuário, email, ferramenta, vertical
   - Ordenado por data decrescente

**Segurança:**
- Apenas usuários admin podem acessar
- Redirecionamento automático se não autorizado

---

## 🗂️ Arquivos Criados/Modificados

### Criados (21 arquivos principais)

**Migrations & Scripts:**
- `config/migrations/001_vertical_system.sql`
- `config/migrations/README.md`
- `scripts/apply-migration.php`
- `scripts/generate-vertical-indexes.php`
- `scripts/generate-tool-gateways.php`

**Onboarding:**
- `public/onboarding-step1.php`
- `public/onboarding-step2.php`
- `public/onboarding-save-vertical.php`
- `public/onboarding-ifrj.php`
- `public/onboarding-juridico.php`

**Verticais (index.php):**
- `public/areas/docencia/index.php`
- `public/areas/pesquisa/index.php`
- `public/areas/ifrj-alunos/index.php`
- `public/areas/juridico/index.php`
- `public/areas/vendas/index.php`
- `public/areas/marketing/index.php`
- `public/areas/licitacoes/index.php`
- `public/areas/rh/index.php`
- `public/areas/geral/index.php`

**Admin:**
- `public/admin/analytics.php`

**Gateways de Ferramentas (16 arquivos):**
- Gerados automaticamente via script (ver SUBTAREFA 3)

### Modificados (3 arquivos)

- `config/auth.php` - Adicionadas 3 funções de controle de acesso
- `public/callback.php` - Verificação de onboarding + redirecionamento inteligente
- `public/dashboard.php` - Sistema dinâmico de ferramentas por vertical

---

## 🚀 Próximos Passos (Testar e Deploar)

### 1. Aplicar Migration

```bash
# IMPORTANTE: Fazer backup antes!
mysqldump -u usuario -p plataforma_sunyata > backup_pre_migration.sql

# Aplicar migration
php scripts/apply-migration.php

# Confirmar quando solicitado
```

### 2. Testes Locais

**Teste 1: Onboarding Completo**
- [ ] Login com conta Google nova
- [ ] Verificar redirecionamento para onboarding-step1.php
- [ ] Preencher dados pessoais e avançar
- [ ] Escolher vertical "Docência"
- [ ] Verificar redirecionamento para /areas/docencia/
- [ ] Verificar que ferramentas corretas aparecem

**Teste 2: Onboarding IFRJ**
- [ ] Login com conta nova
- [ ] Completar step 1
- [ ] Escolher "IFRJ - Alunos"
- [ ] Preencher nível e curso
- [ ] Verificar acesso às ferramentas corretas

**Teste 3: Onboarding Jurídico**
- [ ] Login com conta nova
- [ ] Completar step 1
- [ ] Escolher "Jurídico"
- [ ] Preencher formulário de solicitação
- [ ] Verificar mensagem de sucesso
- [ ] Verificar que email foi enviado
- [ ] Verificar que aparece em /admin/analytics.php como pendente

**Teste 4: Controle de Acesso**
- [ ] Usuário da vertical "Pesquisa" NÃO pode acessar ferramentas de "Jurídico"
- [ ] Tentar acessar diretamente URL de gateway restrito
- [ ] Verificar que dashboard mostra apenas ferramentas permitidas

**Teste 5: Demo User**
- [ ] Admin marcar usuário como demo (UPDATE users SET is_demo = TRUE WHERE id = X)
- [ ] Verificar que demo user vê TODAS as ferramentas
- [ ] Verificar badge "Modo Demo" no dashboard

**Teste 6: Analytics**
- [ ] Login como admin
- [ ] Acessar /admin/analytics.php
- [ ] Verificar que estatísticas aparecem corretamente
- [ ] Realizar alguns acessos a ferramentas
- [ ] Atualizar analytics e verificar que logs foram registrados

### 3. Deploy para Produção

```bash
# 1. Fazer backup do banco de produção
ssh usuario@servidor
mysqldump -u usuario -p plataforma_sunyata > backup_producao_$(date +%Y%m%d_%H%M%S).sql

# 2. Fazer upload dos novos arquivos
# (Via FTP, rsync, git pull, etc)

# 3. Aplicar migration em produção
php scripts/apply-migration.php

# 4. Verificar logs de erro
tail -f /var/log/apache2/error.log

# 5. Testar funcionalidades críticas
```

---

## 📝 Notas Importantes

### Usuários Existentes

**Problema:** Usuários que já estavam cadastrados não têm `completed_onboarding = TRUE`.

**Solução Temporária:**
```sql
-- Marcar todos os usuários existentes como tendo completado onboarding
-- E atribuir vertical "geral" por padrão
UPDATE users
SET completed_onboarding = TRUE,
    selected_vertical = 'geral'
WHERE completed_onboarding = FALSE;
```

**Ou forçar todos a refazer onboarding:**
```sql
-- Deixar como está (completed_onboarding = FALSE)
-- Todos terão que escolher vertical no próximo login
```

### Configuração de Email

O sistema envia emails quando há solicitação de acesso ao Jurídico. Verifique que:
- Função `mail()` está configurada no servidor
- Ou configure SMTP no PHP

### Verticais "Em Breve"

As seguintes verticais mostram página "Em breve":
- Vendas
- Marketing
- Licitações
- RH
- Geral

Para ativar, alterar `'disponivel' => true` em:
- `public/onboarding-step2.php`
- Adicionar ferramentas ao mapeamento em `config/auth.php::get_user_tools()`

### Ferramentas HTML

O sistema espera que os arquivos HTML das ferramentas existam em:
```
public/ferramentas/
├── canvas-docente.html
├── canvas-pesquisa.html
├── canvas-juridico.html
├── biblioteca-prompts-jogos.html
├── guia-prompts-jogos.html
├── guia-prompts-juridico.html
└── padroes-avancados-juridico.html
```

Se algum arquivo não existir, o gateway mostrará erro 404 amigável.

---

## 🔐 Segurança

### CSRF Protection
- Todos os formulários usam `csrf_token()`
- Validação via `verify_csrf()`

### SQL Injection
- Todas as queries usam prepared statements via `Database` class

### Access Control
- Verificação de login em todas as páginas: `require_login()`
- Verificação de vertical em gateways
- Admin pages verificam `access_level === 'admin'`

### Audit Logs
- Todos os acessos a ferramentas são registrados
- Ações de onboarding são auditadas
- Conformidade com LGPD

---

## 📊 Métricas Pós-Deploy

**Acompanhar:**
1. Taxa de conclusão do onboarding
2. Distribuição de usuários por vertical
3. Ferramentas mais acessadas
4. Solicitações de acesso ao Jurídico
5. Taxa de rejeição/aprovação de solicitações

**Acesso via:** `https://portal.sunyataconsulting.com/admin/analytics.php`

---

## 🆘 Troubleshooting

### Erro: "Ferramenta não encontrada"
- Verificar se arquivo HTML existe em `public/ferramentas/`
- Verificar permissões (644 para arquivos)

### Erro: "Você não tem acesso a esta vertical"
- Verificar `users.selected_vertical` no banco
- Verificar se onboarding foi completado
- Verificar mapeamento em `config/auth.php`

### Onboarding não redireciona
- Verificar `users.completed_onboarding` no banco
- Verificar `callback.php` linha 42-44
- Verificar sessão: `$_SESSION['user']['completed_onboarding']`

### Analytics não mostra dados
- Verificar se `tool_access_logs` tem registros
- Verificar se gateways estão registrando acessos
- Verificar se view `v_tool_access_stats` foi criada

---

## 🎯 FASE 2 (Próximas Implementações)

**Não implementado ainda (escopo futuro):**

1. **Interface para aprovar/rejeitar solicitações**
   - Página admin para gerenciar `vertical_access_requests`
   - Enviar email ao usuário quando aprovado/rejeitado

2. **Refinamento do Jurídico**
   - Validação de OAB via API da OAB
   - Diferentes níveis de acesso dentro do Jurídico

3. **Admin Interface para Analytics**
   - Gráficos interativos (Chart.js)
   - Exportação de relatórios
   - Filtros por período

4. **Notificações**
   - Email quando vertical liberada
   - Email quando nova ferramenta adicionada
   - Notificações in-app

---

## ✅ Conclusão

**Status:** ✅ FASE 1 COMPLETA

Todas as 5 subtarefas foram implementadas com sucesso. O sistema está pronto para testes locais e deploy para produção.

**Próximo passo:** Aplicar migration no banco de dados e iniciar testes.

---

**Documento gerado em:** Outubro 2025
**Versão:** 1.0
**Autor:** Claude Code + Equipe Sunyata
