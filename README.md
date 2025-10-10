# Plataforma Sunyata

Portal web educacional e de consultoria em IA generativa com sistema de verticais e controle de acesso, desenvolvido em PHP para hospedagem Hostinger.

[![Version](https://img.shields.io/badge/version-1.0.0--mvp-blue.svg)](https://github.com/iflitaiff/plataforma-sunyata/releases/tag/v1.0.0-mvp)
[![PHP](https://img.shields.io/badge/PHP-8.2%2B-purple.svg)](https://php.net)
[![License](https://img.shields.io/badge/license-Proprietary-red.svg)](LICENSE)

## 🎯 Visão Geral

A Plataforma Sunyata é um sistema completo de ensino e consultoria em IA generativa, organizado por verticais de negócio. Cada usuário tem acesso a uma vertical específica com ferramentas especializadas (Canvas, Guias de Prompts, etc.).

### ✨ Funcionalidades Principais

- **Sistema de Verticais**: 4 verticais ativas (Docência, Pesquisa, IFRJ-Alunos, Jurídico) + 5 futuras
- **Onboarding Inteligente**: Fluxo guiado com coleta de dados específicos por vertical
- **Controle de Acesso**: Usuários veem apenas sua vertical; admins acessam todas
- **Aprovação de Acesso**: Sistema de solicitação e aprovação para verticais restritas (Jurídico)
- **Painel Administrativo**: Gestão de usuários, aprovações e auditoria
- **Ferramentas Canvas**: Canvas Docente, Pesquisa, Jurídico para criação estruturada de prompts
- **Guias e Bibliotecas**: Guias de prompts, bibliotecas temáticas e padrões avançados
- **LGPD Compliant**: Total conformidade com Lei Geral de Proteção de Dados
- **Google OAuth**: Autenticação segura via conta Google

## 🏗️ Arquitetura

### Estrutura de Diretórios

```
plataforma-sunyata/
├── public/                      # Document root (configure no Hostinger)
│   ├── index.php               # Landing page
│   ├── callback.php            # OAuth callback
│   ├── dashboard.php           # Dashboard principal
│   ├── logout.php              # Logout
│   ├── dicionario.php          # Repositório de prompts (futuro)
│   │
│   ├── onboarding-step1.php    # Onboarding: dados pessoais
│   ├── onboarding-step2.php    # Onboarding: seleção de vertical
│   ├── onboarding-ifrj.php     # Onboarding específico IFRJ
│   ├── onboarding-juridico.php # Solicitação de acesso Jurídico
│   │
│   ├── admin/                  # Painel administrativo
│   │   ├── index.php           # Dashboard admin
│   │   ├── users.php           # Gestão de usuários
│   │   ├── access-requests.php # Aprovação de solicitações
│   │   └── audit-logs.php      # Logs de auditoria
│   │
│   ├── areas/                  # Verticais
│   │   ├── docencia/          # Vertical Docência
│   │   │   ├── index.php      # Landing da vertical
│   │   │   ├── canvas-docente.php
│   │   │   ├── canvas-pesquisa.php
│   │   │   ├── biblioteca-prompts-jogos.php
│   │   │   ├── guia-prompts-jogos.php
│   │   │   └── repositorio-prompts.php
│   │   ├── pesquisa/          # Vertical Pesquisa
│   │   ├── ifrj_alunos/       # Vertical IFRJ-Alunos
│   │   ├── juridico/          # Vertical Jurídico
│   │   ├── vendas/            # Futuras verticais (em breve)
│   │   ├── marketing/
│   │   ├── licitacoes/
│   │   ├── rh/
│   │   └── geral/
│   │
│   ├── ferramentas/           # HTML tools
│   │   ├── canvas-docente.html
│   │   ├── canvas-pesquisa.html
│   │   ├── canvas-juridico.html
│   │   ├── guia-prompts-jogos.html
│   │   ├── guia-prompts-juridico.html
│   │   ├── biblioteca-prompts-jogos.html
│   │   └── padroes-avancados-juridico.html
│   │
│   └── assets/                # CSS, JS, imagens
│
├── src/
│   ├── Auth/                  # Autenticação
│   │   └── GoogleAuth.php     # Google OAuth 2.0
│   ├── Core/                  # Models
│   │   ├── Database.php       # Singleton PDO
│   │   ├── User.php           # Model de usuário
│   │   └── Contract.php       # Contratos (futuro)
│   ├── Compliance/            # LGPD
│   │   ├── ConsentManager.php # Gestão de consentimentos
│   │   └── DataRetention.php  # Retenção e anonimização
│   └── views/                 # Componentes
│       └── navbar.php         # Navbar global
│
├── config/
│   ├── config.php             # Configuração principal
│   ├── auth.php               # Credenciais (não versionar!)
│   ├── auth.php.example       # Template de credenciais
│   ├── database.sql           # Schema original (referência)
│   └── migrations/            # Migrações de banco
│       ├── 001_vertical_system.sql
│       └── README.md
│
├── scripts/                   # Scripts de manutenção
│   ├── apply-migration.php    # Executar migrações
│   ├── backup-database.php    # Backup do banco
│   └── generate-tool-gateways.php
│
├── storage/                   # Arquivos temporários
├── vendor/                    # Dependências Composer
├── composer.json
├── composer.lock
├── CHANGELOG.md
└── README.md
```

## 📊 Verticais Implementadas

| Vertical | Status | Ferramentas | Requer Aprovação |
|----------|--------|-------------|------------------|
| **Docência** | ✅ Ativa | Canvas Docente, Canvas Pesquisa, Biblioteca/Guia Jogos | Não |
| **Pesquisa** | ✅ Ativa | Canvas Docente, Canvas Pesquisa | Não |
| **IFRJ-Alunos** | ✅ Ativa | Canvas Pesquisa, Biblioteca/Guia Jogos | Não (requer dados IFRJ) |
| **Jurídico** | ✅ Ativa | Canvas Jurídico, Guia/Padrões Jurídico | **Sim** (aprovação admin) |
| Vendas | 🚧 Em breve | - | Não |
| Marketing | 🚧 Em breve | - | Não |
| Licitações | 🚧 Em breve | - | Não |
| RH | 🚧 Em breve | - | Não |
| Geral | 🚧 Em breve | - | Não |

## 🚀 Instalação

### Pré-requisitos

- **Hospedagem**: Hostinger Premium ou similar com SSH
- **PHP**: 8.0+ com extensões:
  - `ext-curl` (requisições OAuth)
  - `ext-json` (manipulação de dados)
  - `ext-pdo` e `ext-pdo_mysql` (banco de dados)
  - `ext-mbstring` (strings UTF-8)
  - `ext-session` (gerenciamento de sessões)
- **Banco**: MySQL 5.7+ ou MariaDB 10.2+
- **Composer**: 2.0+ (gerenciador de dependências PHP)
- **Domínio**: Configurado com SSL ativo

### 1. Upload dos Arquivos

```bash
# Via SSH
cd ~/domains/seu-dominio.com/public_html
git clone https://github.com/iflitaiff/plataforma-sunyata.git
cd plataforma-sunyata
```

### 2. Instalar Dependências

```bash
composer install --no-dev --optimize-autoloader

# Verificar extensões PHP
php -m | grep -E 'curl|json|pdo|mbstring|session'
```

### 3. Configurar Document Root

No painel de hospedagem, configure o document root para:
```
/domains/seu-dominio.com/public_html/plataforma-sunyata/public
```

### 4. Criar Banco de Dados

1. Crie um banco MySQL no painel
2. Execute a migração:

```bash
mysql -u usuario -p banco < config/migrations/001_vertical_system.sql
```

Ou use o script:
```bash
php scripts/apply-migration.php config/migrations/001_vertical_system.sql
```

### 5. Configurar Google OAuth

#### 5.1. Criar Projeto Google Cloud

1. Acesse [Google Cloud Console](https://console.cloud.google.com/)
2. Crie novo projeto: "Plataforma Sunyata"

#### 5.2. Ativar APIs

- Google People API
- OAuth 2.0 (geralmente já ativo)

#### 5.3. Configurar OAuth Consent Screen

1. **OAuth consent screen** > **External**
2. Preencha nome do app, emails
3. **Scopes**:
   - `.../auth/userinfo.email`
   - `.../auth/userinfo.profile`
   - `openid`
4. **Test users**: Adicione emails de teste

#### 5.4. Criar Credenciais

1. **Credentials** > **Create Credentials** > **OAuth client ID**
2. **Application type**: Web application
3. **Authorized redirect URIs**:
   ```
   https://seu-dominio.com/callback.php
   ```
4. Copie **Client ID** e **Client Secret**

### 6. Configurar Credenciais

```bash
cd config
cp auth.php.example auth.php
nano auth.php
```

Preencha com suas credenciais:

```php
<?php
// Database
define('DB_HOST', 'localhost');
define('DB_NAME', 'nome_do_banco');
define('DB_USER', 'usuario_banco');
define('DB_PASS', 'senha_banco');

// Google OAuth
define('GOOGLE_CLIENT_ID', 'seu-client-id.apps.googleusercontent.com');
define('GOOGLE_CLIENT_SECRET', 'seu-client-secret');

// Session (gere com: openssl rand -hex 32)
define('SESSION_SECRET', 'string_aleatoria_64_caracteres');
```

### 7. Criar Primeiro Administrador

Após fazer o primeiro login:

```sql
UPDATE users
SET access_level = 'admin'
WHERE email = 'seu@email.com';
```

### 8. Testar

Acesse: `https://seu-dominio.com`

- Faça login com Google
- Complete o onboarding
- Teste acesso às ferramentas

## 👤 Níveis de Acesso

| Nível | Descrição | Acesso |
|-------|-----------|--------|
| `guest` | Usuário padrão | Uma vertical após onboarding |
| `student` | Aluno/estudante | Uma vertical (IFRJ requer dados extras) |
| `client` | Cliente/consultoria | Uma vertical com prioridade |
| `admin` | Administrador | **Todas as verticais** + painel admin |

## 🔐 Sistema de Aprovação (Jurídico)

1. **Usuário solicita acesso**: Preenche formulário com OAB, escritório, motivo
2. **Request criado**: Status "pending" em `vertical_access_requests`
3. **Admin aprova/rejeita**: Via painel administrativo
4. **Usuário notificado**: Acesso liberado ou negado
5. **Acesso concedido**: `users.selected_vertical` atualizado para 'juridico'

## 🛠️ Painel Administrativo

Acesse: `https://seu-dominio.com/admin/`

**Funcionalidades:**
- 📊 Dashboard com estatísticas
- 👥 Gestão de usuários (filtros por nível, vertical, busca)
- 🔑 Aprovação de solicitações de acesso
- 📝 Logs de auditoria completos
- 🎯 Acesso a todas as verticais

## 🔒 Segurança e LGPD

### Conformidade Implementada

- ✅ Consentimentos explícitos registrados em `consents`
- ✅ Logs de auditoria em `audit_logs` (todas as ações)
- ✅ Anonimização de dados via `data_requests`
- ✅ Portabilidade de dados (JSON export)
- ✅ CSRF protection em todos os formulários
- ✅ Session management seguro
- ✅ PDO prepared statements (SQL injection protection)

### Tabelas de Conformidade

- `consents`: Registros de aceite de termos
- `audit_logs`: Log de todas as ações do usuário
- `data_requests`: Solicitações LGPD (acesso, exclusão, portabilidade)
- `user_profiles`: Dados adicionais do onboarding

## 📝 Manutenção

### Backup do Banco

```bash
# Manual
mysqldump -u usuario -p banco > backup_$(date +%Y%m%d).sql

# Via script
php scripts/backup-database.php
```

### Limpar Cache

```bash
# Acessar via navegador
https://seu-dominio.com/clear-cache.php

# Ou via CLI
php public/clear-cache.php
```

### Adicionar Novo Admin

```sql
UPDATE users
SET access_level = 'admin'
WHERE email = 'novo-admin@email.com';
```

### Reset de Usuário para Teste

```sql
-- Reset onboarding
UPDATE users
SET selected_vertical = NULL, completed_onboarding = FALSE
WHERE email = 'teste@email.com';

DELETE FROM user_profiles WHERE user_id = (SELECT id FROM users WHERE email = 'teste@email.com');
```

## 🐛 Troubleshooting

### Erro: "Database connection error"

```bash
# Verificar credenciais em config/auth.php
# Testar conexão
php -r "new PDO('mysql:host=localhost;dbname=seu_banco', 'usuario', 'senha');"
```

### Erro: "redirect_uri_mismatch"

- Verifique que a URI no Google Console é **exatamente**:
  ```
  https://seu-dominio.com/callback.php
  ```
- Use `https://` (não `http://`)
- Não use `www.` a menos que seja seu domínio real

### Erro: "Access blocked: This app's request is invalid"

- Adicione scopes no OAuth consent screen
- Publique o app ou adicione email em test users

### Buttons de aprovação não aparecem

Já corrigido em v1.0.0-mvp (PDO CASE_LOWER)

### Ferramentas mostram 404

Verifique que os arquivos HTML existem em `/public/ferramentas/`

## 🚧 Roadmap

### V1.1 (Próxima Release)
- [ ] Repositório de Prompts funcional com busca
- [ ] Workspace de prompts salvos pelo usuário
- [ ] Notificações por email (aprovação concedida, etc.)
- [ ] Edição de perfil de usuário

### V2.0 (Futuro)
- [ ] Implementar 5 verticais futuras com ferramentas
- [ ] Sistema de upload de ferramentas via admin
- [ ] Versionamento de ferramentas (tool_versions)
- [ ] Analytics dashboard (ferramentas mais usadas)
- [ ] Integração com APIs de IA (OpenAI, Anthropic)
- [ ] Sistema de pagamentos/assinaturas

## 📞 Suporte

- **Email**: suporte@sunyataconsulting.com
- **DPO**: dpo@sunyataconsulting.com
- **Issues**: [GitHub Issues](https://github.com/iflitaiff/plataforma-sunyata/issues)

## 📄 Licença

© 2025 Sunyata Consulting. Todos os direitos reservados.

## 🙏 Créditos

Desenvolvido com assistência de [Claude Code](https://claude.com/claude-code) da Anthropic.

---

**v1.0.0-mvp** - Sistema de Verticais MVP - Outubro 2025
