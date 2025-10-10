# Plataforma Sunyata

Portal web educacional e de consultoria em IA generativa, desenvolvido em PHP para hospedagem Hostinger.

## 🎯 Visão Geral

A Plataforma Sunyata é um sistema completo para ensino e consultoria de IA generativa, oferecendo:

- **Dicionário de Prompts**: Centenas de templates prontos organizados por vertical
- **Sistema de Cursos**: Conteúdo educacional estruturado (em desenvolvimento)
- **Consultoria**: Serviços personalizados por área de negócio (em desenvolvimento)
- **LGPD Compliant**: Total conformidade com Lei Geral de Proteção de Dados

## 🏗️ Estrutura do Projeto

```
plataforma-sunyata/
├── public/               # Document root (configure no Hostinger)
│   ├── index.php        # Landing page / Login
│   ├── callback.php     # OAuth callback
│   ├── dashboard.php    # Dashboard do usuário
│   ├── dicionario.php   # Dicionário de prompts
│   ├── logout.php       # Logout
│   └── assets/          # CSS, JS, imagens
├── src/
│   ├── Auth/           # Autenticação Google OAuth
│   ├── Core/           # Models (Database, User, Contract)
│   ├── Compliance/     # LGPD (Consents, Data Retention)
│   ├── AI/             # Stubs para futuras integrações
│   └── views/          # Componentes de view (navbar)
├── config/
│   ├── config.php      # Configuração principal
│   ├── secrets.php     # Credenciais (não versionar!)
│   ├── secrets.php.example
│   └── database.sql    # Schema do banco
├── vendor/              # Dependências (gerado pelo Composer)
├── composer.json        # Gerenciamento de dependências
├── composer.lock        # Versões travadas
└── README.md
```

## 🚀 Instalação

### Pré-requisitos

- Hospedagem Hostinger Premium
- PHP 8.0+ com extensões:
  - `ext-curl` (requisições OAuth)
  - `ext-json` (manipulação de dados)
  - `ext-pdo` e `ext-pdo_mysql` (banco de dados)
  - `ext-mbstring` (strings UTF-8)
  - `ext-session` (gerenciamento de sessões)
- MySQL 5.7+ ou MariaDB 10.2+
- Composer 2.0+ (gerenciador de dependências PHP)
- Acesso SSH
- Domínio configurado: `portal.sunyataconsulting.com`

### Passo 1: Upload dos Arquivos

```bash
# Via SSH no Hostinger
cd ~/public_html
git clone <seu-repositorio> plataforma-sunyata
cd plataforma-sunyata
```

### Passo 2: Instalar Dependências

```bash
# Instalar dependências PHP via Composer
composer install --no-dev --optimize-autoloader

# Verificar se as extensões PHP estão ativas
php -m | grep -E 'curl|json|pdo|mbstring|session'
```

> **Nota**: O Hostinger geralmente já tem o Composer instalado. Se não tiver, [veja como instalar](https://getcomposer.org/download/).

### Passo 3: Configurar Document Root

No painel do Hostinger:
1. Acesse **Websites** > **Gerenciar**
2. Vá em **Configurações Avançadas** > **Document Root**
3. Defina: `public_html/plataforma-sunyata/public`

### Passo 3: Criar Banco de Dados

1. No painel Hostinger, crie um banco MySQL
2. Anote: nome do banco, usuário, senha
3. Importe o schema:

```bash
mysql -u seu_usuario -p nome_do_banco < config/database.sql
```

### Passo 4: Configurar Google OAuth

#### 4.1. Criar Projeto no Google Cloud

1. Acesse [Google Cloud Console](https://console.cloud.google.com/)
2. Clique em **Select a project** > **New Project**
3. Nome do projeto: "Plataforma Sunyata"
4. Clique em **Create**

#### 4.2. Ativar APIs Necessárias

1. No menu lateral, vá em **APIs & Services** > **Library**
2. Busque por "**Google People API**" e clique em **Enable**
3. Busque por "**OAuth 2.0**" (geralmente já está ativo)

#### 4.3. Configurar Tela de Consentimento OAuth

1. Vá em **APIs & Services** > **OAuth consent screen**
2. Selecione **External** (para permitir qualquer conta Google)
3. Preencha:
   - **App name**: Plataforma Sunyata
   - **User support email**: seu email
   - **Developer contact**: seu email
4. Clique em **Save and Continue**
5. Em **Scopes**, adicione:
   - `.../auth/userinfo.email`
   - `.../auth/userinfo.profile`
   - `openid`
6. Clique em **Save and Continue**
7. Em **Test users**, adicione seu email para testes
8. Clique em **Save and Continue**

#### 4.4. Criar Credenciais OAuth

1. Vá em **APIs & Services** > **Credentials**
2. Clique em **+ CREATE CREDENTIALS** > **OAuth client ID**
3. Selecione **Application type**: Web application
4. Configure:
   - **Name**: Plataforma Sunyata Web Client
   - **Authorized JavaScript origins**:
     - `https://portal.sunyataconsulting.com`
   - **Authorized redirect URIs**:
     - `https://portal.sunyataconsulting.com/callback.php`
5. Clique em **Create**
6. **IMPORTANTE**: Copie e guarde:
   - **Client ID** (formato: `xxxxx.apps.googleusercontent.com`)
   - **Client Secret**

> **⚠️ Segurança**: Nunca compartilhe ou versione o Client Secret!

#### 4.5. Publicar App (Após Testes)

Quando estiver pronto para produção:
1. Volte em **OAuth consent screen**
2. Clique em **PUBLISH APP**
3. Confirme a publicação

Enquanto em modo teste, apenas os emails em "Test users" poderão fazer login.

### Passo 5: Configurar Secrets

```bash
cd config
cp secrets.php.example secrets.php
nano secrets.php
```

Edite com seus dados:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'seu_banco');
define('DB_USER', 'seu_usuario');
define('DB_PASS', 'sua_senha');

define('GOOGLE_CLIENT_ID', 'seu-client-id.apps.googleusercontent.com');
define('GOOGLE_CLIENT_SECRET', 'seu-client-secret');

// Gere uma string aleatória
define('SESSION_SECRET', 'cole_aqui_resultado_de_openssl_rand_-hex_32');
```

Gerar SESSION_SECRET:
```bash
openssl rand -hex 32
```

### Passo 6: Criar Diretório de Logs

```bash
mkdir logs
chmod 755 logs
```

### Passo 7: Configurar SSL

No Hostinger, ative SSL gratuito para o domínio.

### Passo 8: Testar

Acesse: `https://portal.sunyataconsulting.com`

## 🔐 Segurança e LGPD

### Conformidade Implementada

- ✅ Consentimentos explícitos registrados
- ✅ Logs de auditoria completos
- ✅ Direito de acesso aos dados
- ✅ Direito de exclusão (anonimização)
- ✅ Portabilidade de dados
- ✅ Política de retenção (730 dias)
- ✅ Disclaimers legais por vertical

### Direitos do Usuário (Art. 18 LGPD)

Implementados via `src/Compliance/DataRetention.php`:

1. **Acesso**: Exportar todos os dados pessoais
2. **Correção**: Atualizar dados incorretos
3. **Exclusão**: Anonimização de dados
4. **Portabilidade**: Download em JSON

### Contato DPO

Configure em `config/config.php`:
```php
define('DPO_EMAIL', 'dpo@sunyataconsulting.com');
```

## 👤 Níveis de Acesso

| Nível | Acesso |
|-------|--------|
| `guest` | Prompts gratuitos |
| `student` | Prompts free + student |
| `client` | Todos os prompts + consultoria |
| `admin` | Acesso total + gestão |

## 📊 Verticais Disponíveis

1. **Vendas** (`sales`)
2. **Marketing** (`marketing`)
3. **Atendimento** (`customer_service`)
4. **RH** (`hr`)
5. **Geral** (`general`)

Cada vertical possui disclaimers legais específicos.

## 🛠️ Manutenção

### Adicionar Prompts

```sql
INSERT INTO prompt_dictionary (vertical, category, title, prompt_text, description, access_level)
VALUES ('sales', 'prospecting', 'Título', 'Texto do prompt...', 'Descrição', 'free');
```

### Criar Primeiro Admin

Após primeiro login via Google:

```sql
UPDATE users SET access_level = 'admin' WHERE email = 'seu@email.com';
```

### Limpeza Automática (Cron)

Adicione no cron do Hostinger:

```bash
# Diariamente às 3h - Expirar contratos e limpar dados antigos
0 3 * * * php /home/usuario/public_html/plataforma-sunyata/scripts/maintenance.php
```

Crie `scripts/maintenance.php`:

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

// Anonimizar dados antigos
$cleaned = $retention->cleanOldData();
echo "Usuários anonimizados: $cleaned\n";
```

## 🧪 Testes

Testar OAuth:
1. Acesse `https://portal.sunyataconsulting.com`
2. Clique em "Entrar com Google"
3. Autorize a aplicação
4. Verifique redirecionamento para dashboard

Testar LGPD:
1. Faça login
2. Vá em Dashboard
3. Aceite os termos (primeira vez)
4. Verifique registro em `consents` table

## 📝 Customização

### Alterar Cores

Edite `public/assets/css/style.css`:

```css
:root {
    --primary-color: #sua-cor;
}
```

### Adicionar Páginas

1. Crie arquivo em `public/sua-pagina.php`
2. Use template do dashboard
3. Adicione link no navbar (`src/views/navbar.php`)

## 🐛 Troubleshooting

### Erro "Database connection error"

Verifique `config/secrets.php`:
- Hostname correto (geralmente `localhost`)
- Credenciais do banco
- Banco existe e schema foi importado

```bash
# Testar conexão com o banco
php -r "new PDO('mysql:host=localhost;dbname=seu_banco', 'usuario', 'senha');"
```

### Problemas com Google OAuth

#### Erro "redirect_uri_mismatch"

**Causa**: URI de redirecionamento não corresponde ao configurado no Google Console.

**Solução**:
1. Acesse [Google Cloud Console Credentials](https://console.cloud.google.com/apis/credentials)
2. Clique no OAuth Client ID criado
3. Verifique que em **Authorized redirect URIs** está exatamente:
   ```
   https://portal.sunyataconsulting.com/callback.php
   ```
4. **NÃO use** `http://` (precisa ser `https://`)
5. **NÃO use** `www.` no domínio (a menos que seja seu domínio real)
6. Salve e aguarde 5 minutos para propagar

#### Erro "Failed to get access token"

**Possíveis causas**:

1. **Client ID ou Secret incorretos**
   ```bash
   # Verifique em config/secrets.php
   grep GOOGLE_CLIENT config/secrets.php
   ```

2. **SSL não está ativo**
   ```bash
   # Teste se o domínio tem certificado válido
   curl -I https://portal.sunyataconsulting.com
   ```
   Se retornar erro SSL, ative o certificado no painel Hostinger.

3. **App está em modo teste e usuário não está na lista**
   - Vá em **OAuth consent screen** > **Test users**
   - Adicione o email que está tentando fazer login
   - OU publique o app (botão **PUBLISH APP**)

#### Erro "Access blocked: This app's request is invalid"

**Causa**: Scopes do OAuth não foram configurados.

**Solução**:
1. Vá em **OAuth consent screen** > **Edit App**
2. Na aba **Scopes**, adicione:
   - `.../auth/userinfo.email`
   - `.../auth/userinfo.profile`
   - `openid`
3. Salve e teste novamente

#### Erro "Error 400: admin_policy_enforced"

**Causa**: Seu domínio Google Workspace tem restrições de OAuth.

**Solução**:
1. Admin precisa autorizar o app no Console Admin do Workspace
2. OU use uma conta Gmail pessoal para testes

#### Usuário faz login mas cai em loop

**Causa**: Sessões não estão sendo salvas.

**Solução**:
```bash
# Verifique permissões do diretório de logs
chmod 755 logs/

# Teste se sessões funcionam
php -r "session_start(); \$_SESSION['test']=1; echo 'OK';"
```

### Erro "Failed to get user info"

**Causa**: Google People API não está ativada.

**Solução**:
1. Vá em [API Library](https://console.cloud.google.com/apis/library)
2. Busque "Google People API"
3. Clique em **Enable**

### Página em branco

Ative logs de erro temporariamente em `config/config.php`:

```php
ini_set('display_errors', 1);
error_reporting(E_ALL);
```

Verifique `logs/php_errors.log`

### Sessão não persiste

Já está coberto na seção "Usuário faz login mas cai em loop" acima.

### Composer não encontrado no Hostinger

```bash
# Instalar Composer localmente
curl -sS https://getcomposer.org/installer | php
php composer.phar install --no-dev --optimize-autoloader
```

## 📞 Suporte

- **Email**: suporte@sunyataconsulting.com
- **DPO**: dpo@sunyataconsulting.com

## 📄 Licença

© 2025 Sunyata Consulting. Todos os direitos reservados.

## 🚧 Roadmap

- [ ] Sistema de cursos online
- [ ] Integração com APIs de IA (OpenAI, Anthropic)
- [ ] Painel administrativo completo
- [ ] Sistema de pagamentos
- [ ] App mobile
- [ ] Webhooks para automações
- [ ] Analytics dashboard

---

**Desenvolvido com ❤️ para transformar negócios com IA**
