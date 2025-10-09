# Relatório Técnico: Plataforma Sunyata

**Gerado em:** 2025-10-09
**Versão:** 1.0
**Ambiente:** WSL2 Ubuntu no Windows
**Repositório local:** `/home/iflitaiff/projetos/plataforma-sunyata`

---

## 📋 Sumário Executivo

A **Plataforma Sunyata** é um portal web educacional em PHP para consultoria em IA generativa, hospedado no Hostinger. O sistema oferece autenticação via Google OAuth, dicionário de prompts, ferramentas canvas especializadas por vertical (Direito, Pesquisa & Ensino), e conformidade total com LGPD.

**Status atual:** MVP em desenvolvimento ativo
**Stack:** PHP 8.0+, MySQL, Bootstrap 5, Google OAuth 2.0
**Deploy:** Hostinger Premium (`portal.sunyataconsulting.com`)

---

## 🏗️ Arquitetura do Projeto

### Estrutura de Diretórios

```
plataforma-sunyata/
├── config/                      # Configurações centralizadas
│   ├── config.php              # Configuração principal (URLs, constantes, helpers)
│   ├── auth.php                # Funções de autenticação (require_login, etc)
│   ├── secrets.php             # Credenciais sensíveis (DB, OAuth) [NÃO VERSIONADO]
│   ├── secrets.php.example     # Template para secrets
│   └── database.sql            # Schema do banco MySQL
│
├── public/                      # Document root (acessível via HTTP)
│   ├── index.php               # Landing page / Login Google
│   ├── callback.php            # OAuth callback handler
│   ├── dashboard.php           # Dashboard principal do usuário
│   ├── dicionario.php          # Dicionário de prompts
│   ├── logout.php              # Logout handler
│   │
│   ├── areas/                  # Verticais especializadas (NOVO)
│   │   ├── direito/            # Vertical Jurídico
│   │   │   ├── index.php       # Página índice com 3 cards
│   │   │   ├── canvas-juridico.php    # Gateway protegido para canvas
│   │   │   └── solicitar-acesso.php   # Formulário de solicitação de acesso
│   │   │
│   │   └── pesquisa-ensino/    # Vertical Pesquisa & Ensino
│   │       ├── index.php       # Página índice com 4 cards
│   │       ├── canvas-docente.php     # Gateway protegido
│   │       └── canvas-pesquisa.php    # Gateway protegido
│   │
│   └── ferramentas/            # Ferramentas HTML estáticas
│       ├── canvas-juridico.html
│       ├── canvas-docente.html
│       ├── canvas-pesquisa.html
│       ├── guia-prompts-juridico.html
│       ├── padroes-avancados-juridico.html
│       ├── guia-prompts-jogos.html
│       └── biblioteca-prompts-jogos.html
│
├── src/                         # Código-fonte PHP (orientado a objetos)
│   ├── Auth/
│   │   └── GoogleAuth.php      # Cliente OAuth Google
│   ├── Core/
│   │   ├── Database.php        # Wrapper PDO
│   │   ├── User.php            # Model de usuário
│   │   └── Contract.php        # Model de contratos
│   ├── Compliance/
│   │   ├── ConsentManager.php  # Gestão de consentimentos LGPD
│   │   └── DataRetention.php   # Retenção e anonimização
│   └── views/
│       └── navbar.php          # Navbar compartilhado Bootstrap
│
├── storage/                     # Dados persistentes (protegido)
│   ├── .htaccess               # Bloqueia acesso HTTP
│   └── access-requests-law.jsonl  # Solicitações de acesso (vertical Direito)
│
├── vendor/                      # Dependências Composer (GuzzleHTTP)
├── composer.json               # Gerenciador de dependências
├── composer.lock               # Versões travadas
└── README.md                   # Documentação principal

```

---

## 🔑 Configurações Principais

### 1. `config/config.php` - Configuração Central

**Constantes importantes:**

```php
// URLs
define('BASE_URL', 'https://portal.sunyataconsulting.com');
define('CALLBACK_URL', BASE_URL . '/callback.php');

// Aplicação
define('APP_NAME', 'Plataforma Sunyata');
define('SUPPORT_EMAIL', 'suporte@sunyataconsulting.com');
define('DPO_EMAIL', 'dpo@sunyataconsulting.com');

// LGPD
define('CONSENT_VERSION', '1.0.0');
define('DATA_RETENTION_DAYS', 730);      // 2 anos
define('ANONYMIZATION_AFTER_DAYS', 2555); // 7 anos

// Níveis de acesso
define('ACCESS_LEVELS', [
    'guest' => 0,
    'student' => 10,
    'client' => 20,
    'admin' => 100
]);
```

**Funções helper globais:**
- `require_login()` - Força autenticação (redireciona se não logado)
- `has_access($level)` - Verifica nível de acesso
- `csrf_token()` / `verify_csrf($token)` - Proteção CSRF
- `sanitize_output($string)` - Escape HTML (XSS prevention)
- `redirect($url)` - Helper de redirecionamento
- `json_response($data, $status)` - Resposta JSON padronizada

### 2. `config/auth.php` - Autenticação

**Estrutura da sessão:**

```php
$_SESSION['user'] = [
    'google_id' => '...',
    'email' => '...',
    'name' => '...',
    'picture' => '...',
    'access_level' => 'guest'
];

// Sistema de controle de acesso por vertical
$_SESSION['access']['law'] = true/false;  // Acesso à vertical Direito
$_SESSION['requested']['law'] = true/false; // Já solicitou acesso
```

**Função principal:**
- `require_login()` - Redireciona para `/index.php?m=login_required` se não autenticado

### 3. `config/secrets.php` (não versionado)

**Estrutura esperada:**

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'nome_do_banco');
define('DB_USER', 'usuario_mysql');
define('DB_PASS', 'senha_mysql');

define('GOOGLE_CLIENT_ID', 'xxx.apps.googleusercontent.com');
define('GOOGLE_CLIENT_SECRET', 'GOCSPX-xxx');

define('SESSION_SECRET', 'hex_64_chars'); // openssl rand -hex 32
```

---

## 🗄️ Banco de Dados (MySQL)

### Schema Principal

**Tabelas implementadas:**

1. **`users`** - Usuários autenticados via Google
   ```sql
   - id (PK)
   - google_id (UNIQUE, índice)
   - email (UNIQUE, índice)
   - name
   - picture
   - access_level ENUM('guest', 'student', 'client', 'admin')
   - created_at, updated_at, last_login
   ```

2. **`contracts`** - Contratos de serviços
   ```sql
   - id (PK)
   - user_id (FK → users)
   - type ENUM('course', 'consulting', 'subscription')
   - vertical ENUM('sales', 'marketing', 'customer_service', 'hr', 'general')
   - status ENUM('active', 'inactive', 'suspended', 'expired')
   - start_date, end_date
   - metadata JSON
   ```

3. **`consents`** - Consentimentos LGPD
   ```sql
   - id (PK)
   - user_id (FK → users)
   - consent_type ENUM('terms_of_use', 'privacy_policy', 'data_processing', 'marketing')
   - consent_given BOOLEAN
   - ip_address, user_agent
   - consent_text, consent_version
   - created_at, revoked_at
   ```

4. **`audit_logs`** - Logs de auditoria
   ```sql
   - id (PK BIGINT)
   - user_id (FK → users, nullable)
   - action VARCHAR(255)
   - entity_type, entity_id
   - ip_address, user_agent
   - details JSON
   - created_at
   ```

5. **`data_requests`** - Solicitações LGPD (Art. 18)
   ```sql
   - id (PK)
   - user_id (FK → users)
   - request_type ENUM('access', 'deletion', 'portability', 'correction')
   - status ENUM('pending', 'processing', 'completed', 'rejected')
   - requested_at, processed_at
   - processed_by (FK → users)
   - notes TEXT
   ```

6. **`sessions`** - Controle de sessões
   ```sql
   - id VARCHAR(128) PK
   - user_id (FK → users)
   - ip_address, user_agent
   - last_activity, created_at
   ```

7. **`prompt_dictionary`** - Dicionário de prompts (MVP)
   ```sql
   - id (PK)
   - vertical ENUM('sales', 'marketing', 'customer_service', 'hr', 'general')
   - category VARCHAR(100)
   - title, prompt_text, description
   - tags JSON
   - use_cases TEXT
   - access_level ENUM('free', 'student', 'client', 'premium')
   - created_at, updated_at
   - created_by (FK → users)
   - FULLTEXT INDEX (title, description, prompt_text)
   ```

**Charset:** `utf8mb4_unicode_ci` (suporte completo a emojis e caracteres especiais)

---

## 🔐 Autenticação (Google OAuth 2.0)

### Fluxo de Login

1. **Usuário clica em "Entrar com Google"** (`/index.php`)
2. **Redirecionamento para Google** com scopes:
   - `openid`
   - `email`
   - `profile`
3. **Google redireciona para** `/callback.php?code=xxx`
4. **Backend troca código por tokens** (via `GoogleAuth.php`)
5. **Busca dados do usuário** (Google People API)
6. **Cria/atualiza registro** em `users` table
7. **Inicia sessão** e redireciona para `/dashboard.php`

### Classe `GoogleAuth` (`src/Auth/GoogleAuth.php`)

**Métodos principais:**
- `getAuthUrl()` - Gera URL de autorização Google
- `handleCallback($code)` - Troca código por tokens
- `getUserInfo($accessToken)` - Busca dados do usuário
- `getCurrentUser()` - Retorna usuário da sessão
- `logout()` - Destroi sessão

**Dependência:** GuzzleHTTP (cliente HTTP)

---

## 🎨 Frontend

### Framework CSS
- **Bootstrap 5.3** (CDN)
- Classes utilitárias extensivas
- Grid system responsivo

### Componentes Reutilizáveis

**Navbar (`src/views/navbar.php`):**
- Logo/brand → Dashboard
- Menu principal (visível quando logado):
  - Dashboard
  - Dicionário de Prompts
  - **Direito** → `/areas/direito/`
  - **Pesquisa & Ensino** → `/areas/pesquisa-ensino/`
- Dropdown do usuário:
  - Avatar (foto do Google)
  - Nome do usuário
  - Perfil (placeholder)
  - Privacidade/LGPD (placeholder)
  - Sair (logout)

### Padrão de Páginas PHP

**Template padrão:**
```php
<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/auth.php';

require_login(); // Força autenticação

include __DIR__ . '/../../src/views/navbar.php'; // Navbar Bootstrap
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Título - Plataforma Sunyata</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <!-- Conteúdo aqui -->
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
```

---

## 📦 Verticais Especializadas (NOVO)

### 1. Vertical **Direito** (`/areas/direito/`)

#### Estrutura de Arquivos

**`index.php`** - Página índice com 3 cards:
- Canvas Jurídico → `/areas/direito/canvas-juridico.php`
- Guia de Prompts (Jurídico) → `/public/ferramentas/guia-prompts-juridico.html` (nova aba)
- Padrões Avançados (Jurídico) → `/public/ferramentas/padroes-avancados-juridico.html` (nova aba)

**`canvas-juridico.php`** - Gateway protegido:
```php
// Verifica login
require_login();

// Verifica acesso específico à vertical
if (!isset($_SESSION['access']['law']) || $_SESSION['access']['law'] !== true) {
    header('Location: /areas/direito/solicitar-acesso.php');
    exit;
}

// Renderiza HTML do canvas
include navbar;
readfile(__DIR__ . '/../../../public/ferramentas/canvas-juridico.html');
```

**`solicitar-acesso.php`** - Formulário de solicitação:
- **Campos:**
  - Profissão (select obrigatório): "Advogado(a)", "Estudante de Direito", "Outro"
  - OAB (opcional, validado com regex `/^[0-9]{1,6}-[A-Z]{2}$/`)
  - Escritório (opcional)
- **Processamento POST:**
  1. Valida campos
  2. Marca `$_SESSION['requested']['law'] = true`
  3. Salva em JSONL: `storage/access-requests-law.jsonl`
  4. Envia e-mail para `contato@sunyataconsulting.com`
  5. Exibe feedback (success/warning)
- **Proteção:** Se já solicitou, desabilita formulário
- **Diagnóstico temporário ativo:** Exibe "BOOT_OK" e loga erros em `storage/php-errors.log`

#### Sistema de Controle de Acesso

**Variáveis de sessão:**
```php
$_SESSION['access']['law'] = true;      // Acesso concedido (manual por admin)
$_SESSION['requested']['law'] = true;   // Já solicitou acesso
```

**Fluxo:**
1. Usuário logado tenta acessar `/areas/direito/canvas-juridico.php`
2. Gateway verifica `$_SESSION['access']['law']`
3. Se `false/null` → redireciona para formulário
4. Usuário preenche formulário → grava JSONL + envia e-mail
5. Admin analisa e aprova manualmente (setando `$_SESSION['access']['law'] = true`)
6. Usuário pode acessar o canvas

### 2. Vertical **Pesquisa & Ensino** (`/areas/pesquisa-ensino/`)

#### Estrutura de Arquivos

**`index.php`** - Página índice com 4 cards:
- Canvas Docente → `/areas/pesquisa-ensino/canvas-docente.php`
- Canvas Pesquisa → `/areas/pesquisa-ensino/canvas-pesquisa.php`
- Guia de Prompts (Jogos Digitais) → `/public/ferramentas/guia-prompts-jogos.html` (nova aba)
- Biblioteca de Prompts (Jogos) → `/public/ferramentas/biblioteca-prompts-jogos.html` (nova aba)

**`canvas-docente.php`** - Gateway protegido (sem controle de acesso especial):
```php
require_login();
include navbar;
readfile(__DIR__ . '/../../../public/ferramentas/canvas-docente.html');
```

**`canvas-pesquisa.php`** - Gateway protegido (sem controle de acesso especial):
```php
require_login();
include navbar;
readfile(__DIR__ . '/../../../public/ferramentas/canvas-pesquisa.html');
```

**Nota:** Atualmente, a vertical Pesquisa & Ensino não tem sistema de solicitação de acesso como a vertical Direito. Qualquer usuário logado pode acessar.

---

## 📂 Sistema de Storage

### Diretório `storage/`

**Proteção HTTP:**
```apache
# storage/.htaccess
<IfModule mod_authz_core.c>
  Require all denied
</IfModule>
<IfModule !mod_authz_core.c>
  Deny from all
</IfModule>
```

**Arquivos armazenados:**
- `access-requests-law.jsonl` - Solicitações de acesso vertical Direito (formato JSONL)
- `php-errors.log` - Erros PHP (diagnóstico)

**Formato JSONL (JSON Lines):**
```json
{"ts":"2025-10-09T14:32:00-03:00","user":{"name":"João Silva","email":"joao@example.com","sub":"google_id_123"},"profissao":"Advogado(a)","oab":"123456-RJ","escritorio":"Silva & Advogados","ip":"192.168.1.1","ua":"Mozilla/5.0..."}
{"ts":"2025-10-09T15:45:00-03:00","user":{"name":"Maria Santos","email":"maria@example.com","sub":"google_id_456"},"profissao":"Estudante de Direito","oab":null,"escritorio":"PUC-Rio","ip":"192.168.1.2","ua":"Mozilla/5.0..."}
```

**Cada linha é um JSON independente** (fácil para append e processamento linha por linha).

---

## 📧 Sistema de Notificações

### E-mail de Solicitação de Acesso (Vertical Direito)

**Destinatário:** `contato@sunyataconsulting.com`
**Função PHP:** `mail()` nativa

**Estrutura:**
```
To: contato@sunyataconsulting.com
Subject: [Portal Sunyata] Solicitação de acesso: Jurídico
From: Portal Sunyata <contato@sunyataconsulting.com>
Reply-To: email_do_usuario@example.com

Nova solicitação de acesso à vertical Jurídico:

Nome: João Silva
E-mail: joao@example.com
Google ID: google_id_123
Profissão: Advogado(a)
OAB: 123456-RJ
Escritório: Silva & Advogados
IP: 192.168.1.1
User Agent: Mozilla/5.0...
Timestamp: 2025-10-09T14:32:00-03:00
```

**Sanitização:** Campos `oab` e `escritorio` têm `\r` e `\n` removidos antes de incluir no e-mail (prevenção de header injection).

**Feedback ao usuário:**
- `mail() === true` → Alert success: "Solicitação recebida. Você receberá um e-mail quando for aprovada."
- `mail() === false` → Alert warning: "Recebemos sua solicitação, mas houve falha no envio de e-mail. Nossa equipe será notificada pelos registros internos."

---

## 🔒 Conformidade LGPD

### Consentimentos Implementados

**Tipos de consentimento:**
1. **terms_of_use** (obrigatório)
2. **privacy_policy** (obrigatório)
3. **data_processing** (opcional, padrão: aceito)
4. **marketing** (opcional, padrão: recusado)

**Fluxo no dashboard:**
- Primeiro login → exibe modal de consentimento
- Usuário aceita → registra em `consents` table
- Todos os consentimentos têm:
  - IP do usuário
  - User-Agent
  - Timestamp
  - Versão do texto consentido

### Classes de Compliance

**`ConsentManager.php`:**
- `recordConsent($userId, $type, $given, $text)` - Registra consentimento
- `getConsentText($type)` - Retorna texto do consentimento
- `hasConsent($userId, $type)` - Verifica se usuário consentiu
- `revokeConsent($userId, $type)` - Revoga consentimento

**`DataRetention.php`:**
- `cleanOldData()` - Anonimiza dados antigos (>730 dias)
- `exportUserData($userId)` - Exporta todos os dados do usuário (portabilidade)
- `anonymizeUser($userId)` - Anonimiza usuário (direito ao esquecimento)

### Auditoria

**Toda ação importante é logada em `audit_logs`:**
- Login/logout
- Mudança de nível de acesso
- Criação/expiração de contratos
- Consentimentos dados/revogados
- Solicitações LGPD (acesso, exclusão, portabilidade)

---

## 🚀 Deploy (Hostinger)

### Configuração do Servidor

**Document Root:** `/public_html/plataforma-sunyata/public`
**PHP:** 8.0+ com extensões:
- `ext-curl` (OAuth)
- `ext-json` (dados)
- `ext-pdo`, `ext-pdo_mysql` (banco)
- `ext-mbstring` (strings UTF-8)
- `ext-session` (sessões)

**Composer:** Instalado globalmente ou localmente
```bash
composer install --no-dev --optimize-autoloader
```

**SSL:** Certificado gratuito Let's Encrypt via painel Hostinger

### Variáveis de Ambiente (via `secrets.php`)

**Nunca versionadas:**
- Credenciais MySQL
- Client ID/Secret do Google OAuth
- SESSION_SECRET (gerado com `openssl rand -hex 32`)

---

## 🐛 Debugging Ativo

### Diagnóstico Temporário em `solicitar-acesso.php`

**Linhas adicionadas no topo:**
```php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
ini_set('log_errors', '1');
error_reporting(E_ALL);
ini_set('error_log', __DIR__ . '/../../../storage/php-errors.log');
echo "BOOT_OK\n"; flush();
```

**Objetivo:** Identificar erros de execução durante testes.
**IMPORTANTE:** Remover antes de produção (expor erros é vulnerabilidade de segurança).

---

## 📝 Padrões de Código

### Segurança

1. **Escape de saída:** Sempre usar `sanitize_output()` para dados exibidos em HTML
2. **CSRF Protection:** Todos os formulários POST devem incluir `csrf_token()`
3. **SQL Injection:** Usar PDO com prepared statements (já implementado em `Database.php`)
4. **XSS Prevention:** `htmlspecialchars()` com `ENT_QUOTES` e `UTF-8`
5. **Path Traversal:** Validar caminhos de arquivo antes de `readfile()` ou `include`

### Estrutura de Controle de Acesso

**Páginas públicas:**
- `/index.php` (landing/login)

**Páginas protegidas (requerem login):**
- `/dashboard.php`
- `/dicionario.php`
- `/areas/direito/*`
- `/areas/pesquisa-ensino/*`

**Páginas com controle granular:**
- `/areas/direito/canvas-juridico.php` (requer `$_SESSION['access']['law']`)

### Nomenclatura

- **Variáveis:** `$camelCase`
- **Funções:** `snake_case()`
- **Classes:** `PascalCase`
- **Constantes:** `SCREAMING_SNAKE_CASE`
- **Arquivos:** `kebab-case.php` ou `PascalCase.php` (classes)

---

## 🔄 Fluxos de Usuário

### 1. Novo Usuário (Primeiro Acesso)

```
1. Acessa portal.sunyataconsulting.com
2. Clica "Entrar com Google"
3. Autoriza app no Google
4. Redirecionado para /callback.php
5. Cria registro em users (access_level = 'guest')
6. Inicia sessão
7. Redirecionado para /dashboard.php
8. Modal de consentimento LGPD aparece
9. Aceita termos
10. Registra em consents table
11. Dashboard liberado
```

### 2. Usuário Solicita Acesso à Vertical Direito

```
1. Logado, acessa menu "Direito"
2. Vai para /areas/direito/
3. Clica em "Canvas Jurídico"
4. Redirecionado para /areas/direito/solicitar-acesso.php
5. Preenche formulário (profissão, OAB, escritório)
6. Submete → valida campos
7. Salva em storage/access-requests-law.jsonl
8. Envia e-mail para contato@sunyataconsulting.com
9. Marca $_SESSION['requested']['law'] = true
10. Exibe mensagem: "Solicitação recebida"
11. [ADMIN ANALISA MANUALMENTE E APROVA]
12. Admin seta $_SESSION['access']['law'] = true (ou via DB)
13. Usuário pode acessar canvas-juridico.php
```

### 3. Usuário Acessa Canvas de Pesquisa

```
1. Logado, acessa menu "Pesquisa & Ensino"
2. Vai para /areas/pesquisa-ensino/
3. Clica em "Canvas Pesquisa"
4. Redirecionado para /areas/pesquisa-ensino/canvas-pesquisa.php
5. Gateway verifica login (require_login())
6. Renderiza canvas-pesquisa.html com navbar
7. Usuário utiliza ferramenta
```

---

## 🧩 Dependências (Composer)

**`composer.json`:**
```json
{
  "require": {
    "php": ">=8.0",
    "guzzlehttp/guzzle": "^7.5"
  },
  "autoload": {
    "psr-4": {
      "Sunyata\\": "src/"
    }
  }
}
```

**GuzzleHTTP:** Cliente HTTP para comunicação com APIs Google OAuth.

**Instalação:**
```bash
composer install --no-dev --optimize-autoloader
```

---

## 📊 Estado Atual do Projeto

### ✅ Funcionalidades Implementadas

1. **Autenticação Google OAuth 2.0**
   - Login/logout funcionais
   - Integração com Google People API
   - Sessões seguras (HttpOnly, Secure, SameSite)

2. **Dashboard Principal**
   - Cards de ferramentas
   - Links para dicionário e canvas
   - Display de contratos ativos

3. **Sistema de Consentimentos LGPD**
   - Modal de consentimento no primeiro acesso
   - Registro em banco com IP/UA/timestamp
   - Versioning de textos

4. **Vertical Direito (COMPLETO)**
   - Página índice com 3 cards
   - Gateway protegido para canvas-juridico.php
   - Sistema de solicitação de acesso:
     - Formulário com validação
     - Gravação em JSONL
     - Envio de e-mail
     - Controle de estado (já solicitou)
   - Item no navbar

5. **Vertical Pesquisa & Ensino (COMPLETO)**
   - Página índice com 4 cards
   - Gateways protegidos para canvas-docente e canvas-pesquisa
   - Item no navbar

6. **Proteção de Storage**
   - `.htaccess` bloqueando acesso HTTP
   - Arquivos JSONL fora do document root

7. **Navbar Responsivo**
   - Bootstrap 5
   - Dropdown do usuário com avatar
   - Itens de menu para verticais

### 🚧 Funcionalidades Pendentes/Placeholders

1. **Dicionário de Prompts**
   - Schema de DB implementado
   - Interface não desenvolvida
   - Link existe no navbar

2. **Sistema de Cursos**
   - Card no dashboard (desabilitado)
   - Texto: "Em breve"

3. **Consultoria**
   - Card no dashboard (desabilitado)
   - Texto: "Em breve"

4. **Painel Administrativo**
   - Não implementado
   - Necessário para:
     - Aprovar solicitações de acesso
     - Gerenciar usuários
     - Adicionar prompts ao dicionário
     - Ver logs de auditoria

5. **Perfil do Usuário**
   - Link no dropdown do navbar (placeholder)
   - Sem página implementada

6. **Privacidade/LGPD (Interface)**
   - Link no dropdown do navbar (placeholder)
   - Backend implementado (`DataRetention.php`)
   - Falta interface para:
     - Exportar dados
     - Solicitar exclusão
     - Ver histórico de consentimentos

### ⚠️ Issues Conhecidos

1. **Diagnóstico ativo em produção**
   - `solicitar-acesso.php` tem `display_errors = 1` e `echo "BOOT_OK"`
   - **AÇÃO NECESSÁRIA:** Remover antes de deploy final

2. **Aprovação de acesso manual**
   - Sistema de solicitação funciona, mas aprovação é via sessão
   - **MELHORIA:** Criar admin panel para aprovar e persistir no DB

3. **E-mail via `mail()`**
   - Função nativa do PHP pode falhar em alguns servidores
   - **MELHORIA:** Usar PHPMailer ou SMTP direto

4. **Sem validação de domínio de e-mail**
   - Qualquer conta Google pode fazer login (demo mode ativo)
   - **MELHORIA:** Restringir por domínio ou lista de aprovados

---

## 🔧 Tarefas de Manutenção

### Limpeza Periódica (Cron Job)

**Criar:** `scripts/maintenance.php`
```php
<?php
require_once __DIR__ . '/../config/config.php';

use Sunyata\Core\Contract;
use Sunyata\Compliance\DataRetention;

$contract = new Contract();
$retention = new DataRetention();

// Expirar contratos vencidos
$expired = $contract->expireOutdated();
echo "Contratos expirados: $expired\n";

// Anonimizar dados antigos (>730 dias)
$cleaned = $retention->cleanOldData();
echo "Usuários anonimizados: $cleaned\n";
```

**Adicionar no cron (Hostinger):**
```bash
# Diariamente às 3h
0 3 * * * php /home/usuario/public_html/plataforma-sunyata/scripts/maintenance.php
```

### Criar Primeiro Admin

**Após primeiro login via Google:**
```sql
-- Substituir pelo seu email
UPDATE users SET access_level = 'admin' WHERE email = 'admin@sunyataconsulting.com';
```

---

## 📚 Referências e Recursos

### Documentação Externa

- **Google OAuth 2.0:** https://developers.google.com/identity/protocols/oauth2
- **Google People API:** https://developers.google.com/people
- **Bootstrap 5:** https://getbootstrap.com/docs/5.3/
- **LGPD (Lei 13.709/2018):** http://www.planalto.gov.br/ccivil_03/_ato2015-2018/2018/lei/l13709.htm
- **Composer:** https://getcomposer.org/doc/

### Contatos

- **Suporte Técnico:** suporte@sunyataconsulting.com
- **DPO (Encarregado de Dados):** dpo@sunyataconsulting.com
- **Solicitações de Acesso:** contato@sunyataconsulting.com

---

## 🎯 Próximos Passos Sugeridos

### Curto Prazo (Essencial)

1. ⚠️ **Remover código de debug** de `solicitar-acesso.php`
2. 🔐 **Implementar admin panel** para aprovar solicitações de acesso
3. 📧 **Testar sistema de e-mail** em produção (configurar SMTP se necessário)
4. 🧪 **Testes de ponta a ponta** em ambiente de staging

### Médio Prazo (Melhorias)

1. 📖 **Implementar interface do Dicionário de Prompts**
2. 👤 **Criar página de Perfil do Usuário**
3. 🔒 **Interface de Privacidade/LGPD** (exportar/deletar dados)
4. 📊 **Dashboard administrativo** (usuários, logs, solicitações)
5. 🎨 **Melhorar CSS customizado** (atualmente 100% Bootstrap)

### Longo Prazo (Roadmap)

1. 🎓 **Sistema de cursos online**
2. 💳 **Integração com gateway de pagamento**
3. 🤖 **Integração com APIs de IA** (OpenAI, Anthropic, etc.)
4. 📱 **App mobile** (React Native ou Flutter)
5. 📈 **Analytics dashboard** (métricas de uso)

---

## 💡 Dicas para Nova IA

### Ao Fazer Modificações

1. **Sempre ler antes de editar:**
   - Use `Read` para ver o arquivo completo antes de modificar
   - Identifique o contexto ao redor do código

2. **Teste sintaxe PHP:**
   ```bash
   php -l caminho/do/arquivo.php
   ```

3. **Use caminhos absolutos relativos:**
   - Sempre: `__DIR__ . '/../../config/config.php'`
   - Nunca: caminhos fixos como `/home/usuario/...`

4. **Siga padrões de segurança:**
   - Escape saída: `sanitize_output()`
   - CSRF: `csrf_token()` e `verify_csrf()`
   - SQL: PDO prepared statements
   - Validação: Sempre validar input do usuário

5. **Estrutura de página padrão:**
   ```php
   <?php
   require_once __DIR__ . '/../../config/config.php';
   require_once __DIR__ . '/../../config/auth.php';
   require_login();
   include __DIR__ . '/../../src/views/navbar.php';
   ?>
   <!-- HTML aqui -->
   ```

### Ao Criar Novas Verticais

**Template de nova vertical:**

1. Criar diretório: `public/areas/nome-vertical/`
2. Criar `index.php` com cards das ferramentas
3. Criar gateways PHP para cada ferramenta (ex: `canvas-xyz.php`)
4. Adicionar item no navbar (`src/views/navbar.php`)
5. Se precisar controle de acesso:
   - Criar formulário `solicitar-acesso.php`
   - Usar sessão: `$_SESSION['access']['vertical_name']`
   - Gravar solicitações em: `storage/access-requests-vertical.jsonl`

### Git Workflow (se versionado)

```bash
# Antes de modificar
git status
git pull

# Após modificações
git add .
git commit -m "feat: descrição clara da mudança"
git push

# Nunca versionar:
- config/secrets.php
- storage/*.jsonl
- storage/*.log
- vendor/ (regenerar com composer)
```

---

## 📖 Glossário

- **Canvas:** Ferramenta visual interativa para estruturar prompts
- **Gateway:** Arquivo PHP que valida acesso antes de renderizar conteúdo
- **Vertical:** Área de especialização (ex: Direito, Pesquisa & Ensino)
- **JSONL:** JSON Lines - formato de texto com um JSON por linha
- **OAuth:** Protocolo de autorização usado para login via Google
- **LGPD:** Lei Geral de Proteção de Dados (equivalente brasileiro da GDPR)
- **DPO:** Data Protection Officer (Encarregado de Dados)
- **MVP:** Minimum Viable Product (produto mínimo viável)

---

**Fim do Relatório**

*Documento vivo - atualizar conforme projeto evolui.*
