# Scripts de Deploy e Teste

Scripts para automatizar o deploy e testes no servidor de produção.

---

## 📜 Scripts Disponíveis

### 1. `deploy.sh` - Deploy Completo

Faz commit, push para GitHub e deploy no servidor automaticamente.

**Uso:**
```bash
# Com mensagem de commit
./scripts/deploy.sh "Mensagem do commit aqui"

# Sem mensagem (será solicitado)
./scripts/deploy.sh
```

**O que faz:**
1. ✅ Mostra status do Git
2. ✅ Adiciona arquivos modificados
3. ✅ Cria commit
4. ✅ Faz push para GitHub
5. ✅ Conecta no servidor via SSH
6. ✅ Faz pull das mudanças
7. ✅ Limpa OPcache
8. ✅ Verifica sintaxe PHP
9. ✅ Mostra logs recentes

### 2. `test-server.sh` - Teste no Servidor

Testa o estado atual do servidor sem fazer deploy.

**Uso:**
```bash
./scripts/test-server.sh
```

**O que faz:**
1. ✅ Verifica status do Git no servidor
2. ✅ Testa sintaxe PHP
3. ✅ Testa carregamento de configs
4. ✅ Verifica logs de erro
5. ✅ Lista arquivos storage
6. ✅ Mostra última modificação dos arquivos

---

## 🚀 Guia Rápido - Primeiro Deploy

### Passo 1: Configure suas credenciais SSH

Primeiro, teste a conexão SSH manualmente:

```bash
ssh -p 65002 u202164171@82.25.72.226
```

Se funcionar, prossiga. Se não, configure sua chave SSH:

```bash
# Gerar chave SSH (se não tiver)
ssh-keygen -t ed25519 -C "seu-email@exemplo.com"

# Copiar chave para o servidor
ssh-copy-id -p 65002 u202164171@82.25.72.226
```

### Passo 2: Execute o Deploy

```bash
cd /home/iflitaiff/projetos/plataforma-sunyata

# Deploy com mensagem
./scripts/deploy.sh "Fix: Corrigir erro de redeclaração de função"
```

### Passo 3: Teste no Navegador

Abra no navegador:
- https://portal.sunyataconsulting.com/areas/direito/solicitar-acesso.php

Não esqueça de limpar o cache: **Ctrl+Shift+R**

---

## 🐛 Troubleshooting

### Erro: "Permission denied"

```bash
chmod +x scripts/deploy.sh
chmod +x scripts/test-server.sh
```

### Erro: "ssh: connect to host ... port 65002: Connection refused"

Verifique:
1. Você está conectado à internet?
2. O firewall permite conexão na porta 65002?
3. As credenciais SSH estão corretas?

**Teste manual:**
```bash
ssh -p 65002 u202164171@82.25.72.226 "echo 'Conexão OK'"
```

### Erro: Git push pede senha

Se você usa 2FA no GitHub, precisa de um Personal Access Token:

1. Vá em: https://github.com/settings/tokens
2. Gere um novo token com permissões de `repo`
3. Use o token como senha quando pedir

**Ou configure SSH:**
```bash
# Adicionar chave SSH ao GitHub
cat ~/.ssh/id_ed25519.pub
# Copie e cole em: https://github.com/settings/keys
```

### Erro: "cd: no such file or directory" no servidor

O caminho do portal pode ser diferente. Conecte manualmente e descubra:

```bash
ssh -p 65002 u202164171@82.25.72.226
pwd
find ~ -name "public_html" -type d
```

Depois edite o script e atualize a variável `SERVER_PATH`.

---

## 📝 Configuração dos Scripts

Se precisar alterar as configurações, edite as variáveis no início de cada script:

```bash
# No deploy.sh e test-server.sh
SERVER_USER="u202164171"           # Seu usuário SSH
SERVER_HOST="82.25.72.226"         # IP do servidor
SERVER_PORT="65002"                # Porta SSH
SERVER_PATH="public_html/portal"   # Caminho do portal no servidor
```

---

## 🔒 Segurança

⚠️ **NÃO comite estes scripts com senhas hardcoded!**

Se precisar armazenar credenciais, use variáveis de ambiente:

```bash
# No seu .bashrc ou .zshrc
export SUNYATA_SSH_USER="u202164171"
export SUNYATA_SSH_HOST="82.25.72.226"
export SUNYATA_SSH_PORT="65002"
```

Depois use nos scripts:
```bash
SERVER_USER="${SUNYATA_SSH_USER}"
SERVER_HOST="${SUNYATA_SSH_HOST}"
SERVER_PORT="${SUNYATA_SSH_PORT}"
```

---

## 📚 Documentação Adicional

- **Guia completo de deploy:** `docs/guia-deploy-e-teste.md`
- **Fix da página em branco:** `docs/fix-solicitar-acesso_20251009_180833.md`
- **Resumo da sessão:** `docs/session-summary_20251009_153239.md`

---

## 💡 Dicas

### Monitorar logs em tempo real

```bash
ssh -p 65002 u202164171@82.25.72.226 "tail -f public_html/portal/logs/php_errors.log"
```

### Limpar cache manualmente

```bash
ssh -p 65002 u202164171@82.25.72.226 "cd public_html/portal && php -r 'opcache_reset(); echo \"Cache limpo\n\";'"
```

### Ver últimos commits no servidor

```bash
ssh -p 65002 u202164171@82.25.72.226 "cd public_html/portal && git log --oneline -5"
```

---

**Criado em:** 2025-10-09
**Versão:** 1.0
