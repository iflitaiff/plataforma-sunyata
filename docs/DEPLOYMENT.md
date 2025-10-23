# 🚀 Deployment Guide - Plataforma Sunyata

**Versão:** 1.0
**Atualizado:** 2025-10-23

---

## 🌍 Ambientes

### Produção (Hostinger)
- **Host:** 82.25.72.226
- **Porta SSH:** 65002
- **Usuário:** u202164171
- **Diretório:** `/home/u202164171/domains/sunyataconsulting.com/public_html/plataforma-sunyata/`

### Local (WSL)
- **Diretório:** `/home/iflitaiff/projetos/plataforma-sunyata/`
- **Branch principal:** `feature/mvp-admin-canvas`

---

## 📦 Deploy de Arquivos PHP

### Single File Deploy
```bash
# Deploy de arquivo único
scp -P 65002 arquivo.php u202164171@82.25.72.226:/home/u202164171/domains/sunyataconsulting.com/public_html/plataforma-sunyata/public/
```

### Multiple Files Deploy
```bash
# Deploy de diretório completo
scp -P 65002 -r src/Services/ u202164171@82.25.72.226:/home/u202164171/domains/sunyataconsulting.com/public_html/plataforma-sunyata/src/
```

### Full Sync (excluindo vendor)
```bash
rsync -avz --exclude 'vendor' --exclude 'node_modules' \
  -e "ssh -p 65002" \
  ./ u202164171@82.25.72.226:/home/u202164171/domains/sunyataconsulting.com/public_html/plataforma-sunyata/
```

---

## 🗄️ Deploy de Database

### Query Direta
```bash
# Executar query única
ssh -p 65002 u202164171@82.25.72.226 "/usr/bin/mariadb u202164171_sunyata -e 'SELECT * FROM users LIMIT 5;'"
```

### Migration SQL File
```bash
# 1. Fazer upload do arquivo de migration
scp -P 65002 config/migrations/004_*.sql u202164171@82.25.72.226:/home/u202164171/

# 2. Executar migration
ssh -p 65002 u202164171@82.25.72.226 "/usr/bin/mariadb u202164171_sunyata < /home/u202164171/004_*.sql"

# 3. Limpar arquivo temporário
ssh -p 65002 u202164171@82.25.72.226 "rm /home/u202164171/004_*.sql"
```

### Verificar Database
```bash
# Listar tabelas
ssh -p 65002 u202164171@82.25.72.226 "/usr/bin/mariadb u202164171_sunyata -e 'SHOW TABLES;'"

# Ver estrutura de tabela
ssh -p 65002 u202164171@82.25.72.226 "/usr/bin/mariadb u202164171_sunyata -e 'DESCRIBE users;'"
```

---

## 📚 Deploy de Vendor (Composer)

### Método Recomendado (Tar + SCP)
```bash
# 1. Criar arquivo tar localmente
tar -czf vendor.tar.gz vendor/

# 2. Upload para servidor
scp -P 65002 vendor.tar.gz u202164171@82.25.72.226:/home/u202164171/

# 3. Extrair no servidor
ssh -p 65002 u202164171@82.25.72.226 "cd /home/u202164171/domains/sunyataconsulting.com/public_html/plataforma-sunyata && tar -xzf ~/vendor.tar.gz"

# 4. Limpar arquivo tar
ssh -p 65002 u202164171@82.25.72.226 "rm /home/u202164171/vendor.tar.gz"
rm vendor.tar.gz
```

### Alternativa (Composer Remoto)
```bash
# Instalar via SSH (se Composer disponível)
ssh -p 65002 u202164171@82.25.72.226 "cd /home/u202164171/domains/sunyataconsulting.com/public_html/plataforma-sunyata && composer install --no-dev"
```

---

## 🔧 Comandos Úteis de Manutenção

### Clear Cache
```bash
# Limpar cache do servidor
ssh -p 65002 u202164171@82.25.72.226 "cd /home/u202164171/domains/sunyataconsulting.com/public_html/plataforma-sunyata && rm -rf cache/*"
```

### Ver Logs
```bash
# Últimas 50 linhas do log de erros
ssh -p 65002 u202164171@82.25.72.226 "tail -50 /home/u202164171/domains/sunyataconsulting.com/public_html/plataforma-sunyata/storage/logs/laravel.log"

# Seguir logs em tempo real
ssh -p 65002 u202164171@82.25.72.226 "tail -f /home/u202164171/domains/sunyataconsulting.com/public_html/plataforma-sunyata/storage/logs/laravel.log"
```

### Verificar Permissões
```bash
# Listar permissões de diretórios críticos
ssh -p 65002 u202164171@82.25.72.226 "ls -la /home/u202164171/domains/sunyataconsulting.com/public_html/plataforma-sunyata/uploads/"
```

### Verificar Espaço em Disco
```bash
# Ver uso de disco
ssh -p 65002 u202164171@82.25.72.226 "du -sh /home/u202164171/domains/sunyataconsulting.com/public_html/plataforma-sunyata/*"
```

---

## 🔐 Deploy de Secrets

### Atualizar secrets.php
```bash
# NUNCA commitar secrets.php
# Deploy manual via SCP
scp -P 65002 config/secrets.php u202164171@82.25.72.226:/home/u202164171/domains/sunyataconsulting.com/public_html/plataforma-sunyata/config/

# Ou editar diretamente no servidor
ssh -p 65002 u202164171@82.25.72.226 "nano /home/u202164171/domains/sunyataconsulting.com/public_html/plataforma-sunyata/config/secrets.php"
```

---

## 📋 Checklist de Deploy Completo

### 1. Pré-Deploy
- [ ] Testar localmente
- [ ] Commitar mudanças no Git
- [ ] Criar backup do banco de produção
- [ ] Verificar se `secrets.php` está atualizado

### 2. Deploy
- [ ] Upload de arquivos PHP via SCP/rsync
- [ ] Deploy de vendor (se necessário)
- [ ] Executar migrations (se houver)
- [ ] Atualizar configurações

### 3. Pós-Deploy
- [ ] Verificar logs de erro
- [ ] Testar funcionalidades afetadas
- [ ] Limpar cache (se necessário)
- [ ] Monitorar por 10-15 minutos

### 4. Rollback (se necessário)
- [ ] Restaurar arquivos anteriores
- [ ] Reverter migrations
- [ ] Restaurar backup do banco

---

## 🚨 Troubleshooting

### "Permission denied" no upload
```bash
# Verificar permissões do diretório de destino
ssh -p 65002 u202164171@82.25.72.226 "ls -la /home/u202164171/domains/sunyataconsulting.com/public_html/plataforma-sunyata/"
```

### "Connection refused"
```bash
# Verificar se porta SSH está correta (65002, não 22)
ssh -p 65002 u202164171@82.25.72.226
```

### Composer não atualiza vendor
```bash
# Deletar vendor e reinstalar
ssh -p 65002 u202164171@82.25.72.226 "cd /home/u202164171/domains/sunyataconsulting.com/public_html/plataforma-sunyata && rm -rf vendor && composer install"
```

---

## 🔗 Links Úteis

- **cPanel:** https://hostinger.com/cpanel
- **Portal Produção:** https://portal.sunyataconsulting.com/
- **GitHub:** https://github.com/iflitaiff/plataforma-sunyata

---

**Mantido por:** Equipe Sunyata
**Última revisão:** 2025-10-23
