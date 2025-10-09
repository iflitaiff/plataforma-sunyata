# Guia de Deploy e Teste - Fix solicitar-acesso.php

**Data:** 2025-10-09
**Objetivo:** Fazer deploy da correção e testar no servidor de produção

---

## 📋 INFORMAÇÕES DO AMBIENTE

- **Repositório GitHub:** https://github.com/iflitaiff/plataforma-sunyata
- **Servidor SSH:** u202164171@82.25.72.226
- **Porta SSH:** 65002
- **Portal:** https://portal.sunyataconsulting.com/
- **Branch atual:** main

---

## 🚀 PASSO A PASSO PARA DEPLOY

### 1. Verificar Status Local

Primeiro, veja o que foi modificado:

```bash
cd /home/iflitaiff/projetos/plataforma-sunyata
git status
```

Você verá algo como:
```
On branch main
Changes not staged for commit:
  modified:   config/auth.php
  modified:   config/config.php
  modified:   public/areas/direito/solicitar-acesso.php
Untracked files:
  docs/fix-solicitar-acesso_20251009_180833.md
  docs/guia-deploy-e-teste.md
  docs/session-summary_20251009_153239.md
```

### 2. Fazer Commit das Correções

```bash
# Adicionar os arquivos corrigidos
git add config/auth.php
git add config/config.php
git add public/areas/direito/solicitar-acesso.php

# Adicionar documentação
git add docs/fix-solicitar-acesso_20251009_180833.md
git add docs/guia-deploy-e-teste.md
git add docs/session-summary_20251009_153239.md

# Criar commit com mensagem descritiva
git commit -m "Fix: Resolver erro fatal de redeclaração de função require_login()

- Remove função require_login() duplicada de config/auth.php
- Unifica verificação de sessão em config/config.php (\$_SESSION['user'])
- Remove código de diagnóstico de solicitar-acesso.php
- Adiciona documentação completa do fix

Fixes: Página em branco em /areas/direito/solicitar-acesso.php
Causa: Cannot redeclare require_login() error"
```

### 3. Fazer Push para GitHub

```bash
git push origin main
```

Se pedir autenticação:
- **Username:** seu usuário do GitHub (iflitaiff)
- **Password:** seu Personal Access Token (não a senha da conta!)

### 4. Conectar no Servidor via SSH

```bash
ssh -p 65002 u202164171@82.25.72.226
```

Vai pedir a senha do servidor Hostinger.

### 5. Navegar até o Diretório do Portal

```bash
# Após conectar no servidor
cd public_html/portal
# ou talvez:
cd ~/public_html
# ou:
cd ~/domains/portal.sunyataconsulting.com/public_html

# Para descobrir o caminho correto:
pwd
ls -la
```

**Dica:** Use `ls -la` para ver se há um diretório `.git` (indica que é o repo)

### 6. Fazer Pull das Mudanças

```bash
# Verificar branch atual
git branch

# Ver status antes do pull
git status

# Fazer pull das mudanças do GitHub
git pull origin main
```

**Se der erro de conflito ou arquivos modificados:**
```bash
# Ver quais arquivos foram modificados no servidor
git status

# Fazer backup das mudanças locais (se necessário)
git stash

# Tentar pull novamente
git pull origin main

# Se quiser recuperar as mudanças locais depois:
git stash pop
```

### 7. Limpar OPcache do PHP

O OPcache mantém versões antigas dos arquivos em memória. Você precisa limpá-lo.

#### Opção A: Via script PHP (mais fácil)

Crie um arquivo temporário no servidor:

```bash
# No servidor
cat > clear-cache.php << 'EOF'
<?php
if (function_exists('opcache_reset')) {
    opcache_reset();
    echo "OPcache limpo com sucesso!\n";
} else {
    echo "OPcache não está habilitado.\n";
}

// Também limpar cache de stat
if (function_exists('clearstatcache')) {
    clearstatcache(true);
    echo "Stat cache limpo!\n";
}

echo "Timestamp: " . date('Y-m-d H:i:s') . "\n";
?>
EOF

# Executar o script
php clear-cache.php

# Também acessar via navegador (se possível)
# https://portal.sunyataconsulting.com/clear-cache.php
```

#### Opção B: Via painel Hostinger

1. Acesse o painel Hostinger
2. Vá em **Avançado** → **PHP Configuration**
3. Clique em **Reload PHP** ou **Restart PHP-FPM**

#### Opção C: Via .htaccess (permanente)

Adicione no `.htaccess` do diretório raiz:

```apache
# Desabilitar cache do PHP para desenvolvimento
php_flag opcache.enable Off
```

**⚠️ AVISO:** Isso diminui performance. Use apenas para teste, depois remova.

### 8. Verificar Permissões dos Arquivos

```bash
# No servidor, no diretório do portal
chmod 644 config/auth.php
chmod 644 config/config.php
chmod 644 public/areas/direito/solicitar-acesso.php

# Se o diretório storage/ não existir, criar:
mkdir -p storage
chmod 755 storage

# Verificar permissões
ls -la config/
ls -la public/areas/direito/
```

---

## 🧪 TESTES NO SERVIDOR

### Teste 1: Verificar Sintaxe PHP

```bash
# No servidor
cd public_html/portal  # ou o diretório correto

php -l config/auth.php
php -l config/config.php
php -l public/areas/direito/solicitar-acesso.php
```

Todos devem retornar: `No syntax errors detected`

### Teste 2: Verificar Logs de Erro

```bash
# Ver últimas linhas do log de erros
tail -50 logs/php_errors.log

# Ou se o log estiver em outro lugar:
tail -50 /home/u202164171/logs/error_log
tail -50 /home/u202164171/public_html/error_log

# Monitorar em tempo real (deixe isso rodando enquanto testa)
tail -f logs/php_errors.log
```

### Teste 3: Testar Carregamento dos Arquivos

Crie um script de teste no servidor:

```bash
# No servidor
cat > test-load-configs.php << 'EOF'
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "1. Carregando config.php...\n";
require_once __DIR__ . '/config/config.php';
echo "2. config.php OK\n";

echo "3. Carregando auth.php...\n";
require_once __DIR__ . '/config/auth.php';
echo "4. auth.php OK\n";

echo "5. Verificando função require_login()...\n";
if (function_exists('require_login')) {
    echo "6. Função require_login() existe!\n";
} else {
    echo "6. ERRO: Função require_login() não existe!\n";
}

echo "\n✅ Teste completo com sucesso!\n";
?>
EOF

# Executar
php test-load-configs.php
```

**Resultado esperado:**
```
1. Carregando config.php...
2. config.php OK
3. Carregando auth.php...
4. auth.php OK
5. Verificando função require_login()...
6. Função require_login() existe!

✅ Teste completo com sucesso!
```

### Teste 4: Testar no Navegador

#### Passo 1: Fazer Login
1. Abra: https://portal.sunyataconsulting.com/
2. Faça login com sua conta Google

#### Passo 2: Acessar a Página Corrigida
1. Acesse: https://portal.sunyataconsulting.com/areas/direito/solicitar-acesso.php
2. **Resultado esperado:** Página carrega normalmente com o formulário

#### Passo 3: Verificar o Source Code
1. Na página, pressione **Ctrl+U** (ou botão direito → "Ver código-fonte")
2. **Verificar que NÃO há:**
   - `<!-- BOOT CHECK: Arquivo carregou -->`
   - `<!-- CHECKPOINT 1: config.php carregado -->`
   - `<!-- CHECKPOINT 2: auth.php carregado -->`
   - Outros comentários de debug

3. **Verificar que há:**
   - `<!DOCTYPE html>`
   - Estrutura HTML completa
   - Navbar
   - Formulário

#### Passo 4: Testar o Formulário
1. Preencha o formulário:
   - **Profissão:** Advogado(a)
   - **OAB:** 123456-SP (formato válido)
   - **Escritório:** Teste
2. Clique em "Enviar Solicitação"
3. **Resultado esperado:** Mensagem de sucesso ou feedback apropriado

### Teste 5: Verificar Arquivo JSONL

```bash
# No servidor
ls -la storage/access-requests-law.jsonl

# Ver conteúdo (últimas 5 linhas)
tail -5 storage/access-requests-law.jsonl

# Verificar formato JSON
tail -1 storage/access-requests-law.jsonl | python3 -m json.tool
# ou
tail -1 storage/access-requests-law.jsonl | php -r 'echo json_encode(json_decode(fgets(STDIN)), JSON_PRETTY_PRINT);'
```

---

## 🐛 TROUBLESHOOTING

### Problema: "Permission denied" ao fazer git pull

**Solução:**
```bash
# Verificar dono dos arquivos
ls -la

# Se necessário, ajustar permissões
chown -R u202164171:u202164171 .
```

### Problema: Página ainda em branco após deploy

**Checklist:**
1. ✅ Git pull foi feito com sucesso?
2. ✅ OPcache foi limpo?
3. ✅ Logs de erro mostram o erro antigo ou novo?
4. ✅ Cache do navegador foi limpo? (Ctrl+Shift+R)

**Comandos:**
```bash
# Forçar timestamp dos arquivos
touch config/auth.php config/config.php public/areas/direito/solicitar-acesso.php

# Limpar OPcache novamente
php clear-cache.php

# Verificar se os arquivos foram realmente atualizados
head -20 config/auth.php
# Deve mostrar o comentário: "// NOTA: A função require_login() está definida..."
```

### Problema: Erro 500 Internal Server Error

**Solução:**
```bash
# Verificar logs
tail -50 logs/php_errors.log
tail -50 /home/u202164171/logs/error_log

# Verificar sintaxe
php -l config/auth.php
php -l config/config.php

# Verificar permissões
chmod 644 config/*.php
chmod 644 public/areas/direito/*.php
```

### Problema: "Function require_login not found"

**Causa:** A função foi removida do auth.php mas config.php não foi carregado.

**Solução:**
```bash
# Verificar se config.php tem a função
grep -n "function require_login" config/config.php

# Deve retornar algo como:
# 84:function require_login() {
```

### Problema: Ainda redireciona para login mesmo estando logado

**Causa:** Verificação de sessão incorreta.

**Solução:**
```bash
# Verificar qual variável de sessão está sendo usada
grep -n "SESSION\['user" config/config.php

# Deve verificar $_SESSION['user'], não $_SESSION['user_id']
```

---

## 📱 COMANDOS RÁPIDOS (COPIAR E COLAR)

### Deploy Completo (Local → GitHub → Servidor)

```bash
# ==== NO SEU COMPUTADOR (WSL) ====
cd /home/iflitaiff/projetos/plataforma-sunyata

# Verificar mudanças
git status

# Commit
git add config/auth.php config/config.php public/areas/direito/solicitar-acesso.php
git add docs/*.md
git commit -m "Fix: Resolver erro fatal de redeclaração de função require_login()"

# Push
git push origin main

# ==== NO SERVIDOR (via SSH) ====
ssh -p 65002 u202164171@82.25.72.226
cd public_html/portal  # ou o caminho correto

# Pull
git pull origin main

# Limpar cache
php -r "opcache_reset(); echo 'OPcache limpo!';"

# Verificar logs
tail -20 logs/php_errors.log

# Testar sintaxe
php -l config/auth.php && php -l config/config.php && php -l public/areas/direito/solicitar-acesso.php

# Se tudo OK:
echo "✅ Deploy concluído!"
```

### Teste Rápido Via Navegador

```bash
# Abrir no navegador (pode usar wsl-open ou copiar URL)
echo "Teste: https://portal.sunyataconsulting.com/areas/direito/solicitar-acesso.php"
```

---

## 📊 CHECKLIST DE DEPLOY

Use este checklist para garantir que tudo foi feito:

- [ ] Commit feito no repositório local
- [ ] Push para GitHub concluído
- [ ] SSH conectado no servidor
- [ ] Git pull executado no servidor
- [ ] OPcache limpo no servidor
- [ ] Permissões dos arquivos verificadas
- [ ] Logs de erro verificados (sem erro fatal)
- [ ] Teste de sintaxe PHP OK
- [ ] Página carrega no navegador (não está em branco)
- [ ] Formulário funciona corretamente
- [ ] JSONL é criado/atualizado
- [ ] Cache do navegador limpo (Ctrl+Shift+R)
- [ ] Teste com usuário logado
- [ ] Teste sem estar logado (deve redirecionar)

---

## 🎯 RESULTADO ESPERADO

Após todos os passos:

✅ **Página carrega normalmente**
✅ **Sem código de debug visível**
✅ **Formulário funcional**
✅ **Logs sem erros fatais**
✅ **JSONL armazena requisições**

---

## 📞 SE PRECISAR DE AJUDA

1. **Verificar logs completos:**
   ```bash
   cat logs/php_errors.log | grep "solicitar-acesso"
   ```

2. **Verificar diferenças entre local e servidor:**
   ```bash
   # No servidor
   md5sum config/auth.php

   # No local
   md5sum config/auth.php

   # Devem ser iguais!
   ```

3. **Criar issue no GitHub** com os logs de erro se problema persistir

---

**Boa sorte com o deploy! 🚀**
