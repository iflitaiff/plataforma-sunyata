# Plataforma Sunyata - Claude Code

**IMPORTANTE:** Ao iniciar nova sessão, SEMPRE leia primeiro:
1. `/home/u202164171/domains/sunyataconsulting.com/public_html/plataforma-sunyata/docs/START-HERE.md` - Contexto completo e atualizado
2. Verifique `/home/u202164171/ai-comm/` para novas mensagens via SSH

---

## Comandos Bash Essenciais

### Deploy para Produção
```bash
scp -P 65002 arquivo.php u202164171@82.25.72.226:/home/u202164171/domains/sunyataconsulting.com/public_html/plataforma-sunyata/public/
```

### Banco de Dados (Query Remota)
```bash
ssh -p 65002 u202164171@82.25.72.226 "/usr/bin/mariadb u202164171_sunyata -e 'SELECT * FROM users LIMIT 5;'"
```

### Git (Commit e Push)
```bash
git add . && git commit -m "feat: descrição" && git push origin feature/mvp-admin-canvas
```

### Logs em Produção
```bash
ssh -p 65002 u202164171@82.25.72.226 "tail -50 /home/u202164171/domains/sunyataconsulting.com/public_html/plataforma-sunyata/storage/logs/laravel.log"
```

### Verificar Mensagens Novas
```bash
ssh -p 65002 u202164171@82.25.72.226 "ls -lt /home/u202164171/ai-comm/*.md | head -5"
```

---

## Convenções de Código

**Nomenclatura:** kebab-case para arquivos (ex: `user-service.php`)

**Detalhes completos:** Ver `/home/u202164171/domains/sunyataconsulting.com/public_html/plataforma-sunyata/docs/START-HERE.md` seção "Convenção de Nomenclatura"

**Outros padrões:**
- Decisões arquiteturais: `/home/u202164171/memory/decisions/`
- Bugs conhecidos: `/home/u202164171/memory/bugs/`
- Padrões de código: `/home/u202164171/memory/patterns/`

---

## Links Rápidos

**Documentação oficial (sempre atualizada):**
- Contexto geral: `/home/u202164171/domains/sunyataconsulting.com/public_html/plataforma-sunyata/docs/START-HERE.md`
- Ambiente e credenciais: Ver START-HERE.md seção "Ambiente"
- Memória institucional: `/home/u202164171/memory/`

**Comunicação:**
- Mensagens: `/home/u202164171/ai-comm/` (via SSH)
- Portal: https://portal.sunyataconsulting.com/comm/

---

**Versão:** 2.0  
**Mantido por:** Manus AI  
**Última atualização:** 2025-10-23
