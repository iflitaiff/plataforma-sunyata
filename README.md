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
│   ├── secrets.php.example
│   └── database.sql    # Schema do banco
└── README.md
```

## 🚀 Instalação

### Pré-requisitos

- Hospedagem Hostinger Premium
- PHP 8.0+
- MySQL 5.7+ ou MariaDB 10.2+
- Acesso SSH
- Domínio configurado: `portal.sunyataconsulting.com`

### Passo 1: Upload dos Arquivos

```bash
# Via SSH no Hostinger
cd ~/public_html
git clone <seu-repositorio> plataforma-sunyata
cd plataforma-sunyata
```

### Passo 2: Configurar Document Root

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

### Passo 4: Configurar Credenciais Google OAuth

1. Acesse [Google Cloud Console](https://console.cloud.google.com/)
2. Crie um projeto novo
3. Ative **Google+ API**
4. Vá em **Credenciais** > **Criar credenciais** > **ID do cliente OAuth**
5. Configure:
   - Tipo: Aplicativo da Web
   - URIs de redirecionamento: `https://portal.sunyataconsulting.com/callback.php`
6. Copie **Client ID** e **Client Secret**

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

### Erro "Failed to get access token"

- Verifique Google Client ID e Secret
- Confirme URI de redirecionamento no Google Console
- Teste se domínio está com SSL ativo

### Página em branco

Ative logs de erro temporariamente em `config/config.php`:

```php
ini_set('display_errors', 1);
error_reporting(E_ALL);
```

Verifique `logs/php_errors.log`

### Sessão não persiste

Verifique permissões:
```bash
chmod 755 logs
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
