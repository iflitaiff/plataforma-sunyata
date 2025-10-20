# Guia de Deploy - Plataforma Sunyata v1.1

Deploy das features:
- ✅ Sistema de Settings dinâmico
- ✅ Serviço de deleção segura de usuários
- ✅ Integração com Claude API (Canvas Jurídico)
- ✅ Histórico de prompts (transparente)
- ✅ CLI Admin Tools

---

## Pré-requisitos

1. Acesso SSH configurado: `ssh -p 65002 u202164171@82.25.72.226`
2. API Key do Claude: `YOUR_ANTHROPIC_API_KEY_HERE`
3. Backup do banco de dados (recomendado)

---

## Passo 1: Backup do Banco de Dados

```bash
ssh -p 65002 u202164171@82.25.72.226 "mysqldump -u u202164171_sunyata -p u202164171_sunyata > ~/backup_$(date +%Y%m%d_%H%M%S).sql"
```

Senha do banco: `MiGOq%tMrUP+9Qy@bxR`

---

## Passo 2: Aplicar Migração do Banco (prompt_history)

```bash
# Upload da migration
scp -P 65002 config/migrations/003_prompt_history.sql u202164171@82.25.72.226:/home/u202164171/

# Aplicar no banco
ssh -p 65002 u202164171@82.25.72.226 "/usr/bin/mariadb u202164171_sunyata -p < /home/u202164171/003_prompt_history.sql"
```

**Verificar se tabela foi criada:**
```bash
ssh -p 65002 u202164171@82.25.72.226 "/usr/bin/mariadb u202164171_sunyata -e 'DESCRIBE prompt_history;'"
```

---

## Passo 3: Upload dos Novos Arquivos

### 3.1. Criar diretórios necessários

```bash
ssh -p 65002 u202164171@82.25.72.226 "mkdir -p /home/u202164171/domains/sunyataconsulting.com/public_html/plataforma-sunyata/src/Admin"

ssh -p 65002 u202164171@82.25.72.226 "mkdir -p /home/u202164171/domains/sunyataconsulting.com/public_html/plataforma-sunyata/public/api"

ssh -p 65002 u202164171@82.25.72.226 "mkdir -p /home/u202164171/domains/sunyataconsulting.com/public_html/plataforma-sunyata/scripts/admin-cli"
```

### 3.2. Upload via SCP

**Novos arquivos Core:**
```bash
scp -P 65002 src/Core/Settings.php u202164171@82.25.72.226:/home/u202164171/domains/sunyataconsulting.com/public_html/plataforma-sunyata/src/Core/
```

**Novos arquivos Admin:**
```bash
scp -P 65002 src/Admin/UserDeletionService.php u202164171@82.25.72.226:/home/u202164171/domains/sunyataconsulting.com/public_html/plataforma-sunyata/src/Admin/
```

**Novo serviço AI:**
```bash
scp -P 65002 src/AI/ClaudeService.php u202164171@82.25.72.226:/home/u202164171/domains/sunyataconsulting.com/public_html/plataforma-sunyata/src/AI/
```

**API endpoint:**
```bash
scp -P 65002 public/api/generate-juridico.php u202164171@82.25.72.226:/home/u202164171/domains/sunyataconsulting.com/public_html/plataforma-sunyata/public/api/
```

**Admin pages atualizados:**
```bash
scp -P 65002 public/admin/index.php u202164171@82.25.72.226:/home/u202164171/domains/sunyataconsulting.com/public_html/plataforma-sunyata/public/admin/

scp -P 65002 public/admin/users.php u202164171@82.25.72.226:/home/u202164171/domains/sunyataconsulting.com/public_html/plataforma-sunyata/public/admin/
```

**Canvas Jurídico atualizado:**
```bash
scp -P 65002 public/ferramentas/canvas-juridico.html u202164171@82.25.72.226:/home/u202164171/domains/sunyataconsulting.com/public_html/plataforma-sunyata/public/ferramentas/
```

**CLI Admin Tools:**
```bash
scp -P 65002 scripts/admin-cli/*.php u202164171@82.25.72.226:/home/u202164171/domains/sunyataconsulting.com/public_html/plataforma-sunyata/scripts/admin-cli/

scp -P 65002 scripts/admin-cli/README.md u202164171@82.25.72.226:/home/u202164171/domains/sunyataconsulting.com/public_html/plataforma-sunyata/scripts/admin-cli/
```

**Secrets.php (atualizar com API Key):**
```bash
scp -P 65002 config/secrets.php.example u202164171@82.25.72.226:/home/u202164171/tmp/secrets.php.example
```

Depois editar manualmente no servidor:
```bash
ssh -p 65002 u202164171@82.25.72.226 "nano /home/u202164171/domains/sunyataconsulting.com/public_html/plataforma-sunyata/config/secrets.php"
```

Adicionar ao final do arquivo:
```php
// Claude API Configuration
define('CLAUDE_API_KEY', 'YOUR_ANTHROPIC_API_KEY_HERE');
```

---

## Passo 4: Definir Permissões

```bash
ssh -p 65002 u202164171@82.25.72.226 "chmod +x /home/u202164171/domains/sunyataconsulting.com/public_html/plataforma-sunyata/scripts/admin-cli/*.php"

ssh -p 65002 u202164171@82.25.72.226 "chmod 644 /home/u202164171/domains/sunyataconsulting.com/public_html/plataforma-sunyata/src/**/*.php"

ssh -p 65002 u202164171@82.25.72.226 "chmod 644 /home/u202164171/domains/sunyataconsulting.com/public_html/plataforma-sunyata/public/**/*.php"
```

---

## Passo 5: Teste Rápido

### 5.1. Testar CLI Tools

```bash
ssh -p 65002 u202164171@82.25.72.226 "cd /home/u202164171/domains/sunyataconsulting.com/public_html/plataforma-sunyata && php scripts/admin-cli/stats.php"
```

Deve exibir estatísticas sem erros.

### 5.2. Testar Admin Dashboard

Acessar: `https://portal.sunyataconsulting.com/admin/`

Verificar:
- ✅ Toggle "Aprovação Jurídico" aparece
- ✅ Botão de deletar usuários aparece
- ✅ Sem erros PHP

### 5.3. Testar Canvas Jurídico

Acessar: `https://portal.sunyataconsulting.com/areas/juridico/canvas-juridico.php`

1. Preencher campos obrigatórios
2. Clicar em "Gerar Análise Jurídica com IA"
3. Deve mostrar: "⏳ Gerando resposta com IA..."
4. Aguardar resposta do Claude (15-30 segundos)
5. Verificar que a resposta aparece

**Verificar histórico:**
```bash
ssh -p 65002 u202164171@82.25.72.226 "/usr/bin/mariadb u202164171_sunyata -e 'SELECT id, user_id, tool_name, status, tokens_total, cost_usd FROM prompt_history ORDER BY id DESC LIMIT 5;'"
```

---

## Passo 6: Monitoramento Pós-Deploy

### Verificar logs de erro:

```bash
ssh -p 65002 u202164171@82.25.72.226 "tail -n 50 /home/u202164171/domains/sunyataconsulting.com/public_html/plataforma-sunyata/logs/php_errors.log"
```

### Estatísticas de uso da API:

```bash
ssh -p 65002 u202164171@82.25.72.226 "cd /home/u202164171/domains/sunyataconsulting.com/public_html/plataforma-sunyata && php scripts/admin-cli/stats.php api"
```

---

## Rollback (se necessário)

### Reverter banco de dados:

```bash
ssh -p 65002 u202164171@82.25.72.226 "mysql -u u202164171_sunyata -p u202164171_sunyata < ~/backup_TIMESTAMP.sql"
```

### Remover arquivos novos:

```bash
ssh -p 65002 u202164171@82.25.72.226 "rm /home/u202164171/domains/sunyataconsulting.com/public_html/plataforma-sunyata/src/Core/Settings.php"

ssh -p 65002 u202164171@82.25.72.226 "rm -rf /home/u202164171/domains/sunyataconsulting.com/public_html/plataforma-sunyata/src/Admin"

ssh -p 65002 u202164171@82.25.72.226 "rm -rf /home/u202164171/domains/sunyataconsulting.com/public_html/plataforma-sunyata/src/AI"
```

---

## Checklist Final

- [ ] Backup do banco criado
- [ ] Tabela `prompt_history` criada
- [ ] Arquivos Core/Settings.php deployado
- [ ] Arquivos Admin/UserDeletionService.php deployado
- [ ] Arquivos AI/ClaudeService.php deployado
- [ ] API endpoint /api/generate-juridico.php deployado
- [ ] Admin dashboard atualizado
- [ ] Canvas Jurídico atualizado
- [ ] CLI tools deployados e executáveis
- [ ] CLAUDE_API_KEY adicionada ao secrets.php
- [ ] Teste manual do Canvas Jurídico OK
- [ ] Histórico sendo salvo no banco OK
- [ ] Sem erros no log PHP

---

## Contatos de Suporte

- Email: filipe.litaiff@ifrj.edu.br
- GitHub: https://github.com/iflitaiff/plataforma-sunyata

---

## Versão

Deploy v1.1.0 - Outubro 2025
