# 🚀 START HERE - Plataforma Sunyata

**Leia PRIMEIRO ao iniciar nova sessão!**

**Versão:** 3.2 | **Atualizado:** 2025-10-23

> ⚠️ **Nota:** Este arquivo é uma cópia da documentação oficial mantida em https://portal.sunyataconsulting.com/comm/#/docs/START-HERE

---

## 🧠 Por Que Este Documento Existe

Este documento é essencial para **continuidade entre sessões**.

**Problema:** Claude Code e outros AIs têm limite de contexto. Após muitas mensagens, conversas antigas são comprimidas ou perdidas.

**Solução:** Este START-HERE serve como "memória externa" para:
- Recontextualização rápida (5 min)
- Evitar repetir informações básicas
- Manter coerência nas decisões
- Garantir que nada importante se perca

**Quando usar:** SEMPRE ao iniciar nova sessão ou após longo período inativo.

---

## 👥 Equipe

| Membro | Papel | Foco |
|--------|-------|------|
| **Filipe Litaiff** | Product Owner | Visão, prioridades, decisões finais |
| **Claude Code** | Developer | Implementação, features, bugfixes |
| **Codex** | Code Reviewer | Review de PRs, refactoring, qualidade |
| **Manus AI** | Architect | Arquitetura, infra, documentação |

---

## 🌍 Ambiente

### Produção (Hostinger)
```bash
# SSH
ssh -p 65002 u202164171@82.25.72.226
# Senha: .ZK6k9ScB8hpCmW

# Upload
scp -P 65002 arquivo.php u202164171@82.25.72.226:/home/u202164171/domains/sunyataconsulting.com/public_html/plataforma-sunyata/public/

# Banco de dados
ssh -p 65002 u202164171@82.25.72.226 "/usr/bin/mariadb u202164171_sunyata -e 'QUERY;'"
```

**Diretório raiz:** `/home/u202164171/domains/sunyataconsulting.com/public_html/plataforma-sunyata/`

### Local (WSL)
- **Usuário:** `iflitaiff`
- **GitHub:** https://github.com/iflitaiff/plataforma-sunyata
- **Branch:** `feature/mvp-admin-canvas`

### Servidor Config
| Recurso | Valor | Nota |
|---------|-------|------|
| PHP | 8.2.28 | Recursos modernos OK |
| RAM | 1024 MB | Otimizar loops |
| Memory Limit | 1536M | Generoso |
| Max Execution | 360s | 6 minutos |
| Upload Max | 1536M | Arquivos grandes OK |
| **Bloqueado** | `exec`, `shell_exec`, `system` | ⚠️ Sem shell via PHP |

### Usuários de Teste
| Tipo | Email | Senha |
|------|-------|-------|
| **Claude Code** | claudesunyata@gmail.com | Pxh5G2t9w6ogprAuwZWr5me |
| Admin | flitaiff@gmail.com | - |
| Admin | filipe.litaiff@ifrj.edu.br | - |
| Teste | filipe.barbosa@coppead.ufrj.br | - |
| Teste | filipe.litaiff@gmail.com | - |
| Teste | pmo@diagnext.com | - |

---

## 📝 Convenção de Nomenclatura

**Regra:** kebab-case (minúsculas + hífen)

✅ `claude-api-calls.md`  
❌ `claude_api_calls.md` (underscore)  
❌ `ClaudeApiCalls.md` (PascalCase)

**Exceções:**
- `README.md`, `INDEX.md` (uppercase)
- `001-prefixo.md` (memória institucional)
- `2025-10-23.md` (logs diários)

**Detalhes:** Ver `/docs/CONVENTIONS.md` ou https://portal.sunyataconsulting.com/comm/#/memory/decisions/003-naming-convention

---

## 💬 Comunicação

### Para Claude Code
**Sistema:** `/ai-comm/` (via SSH)

```bash
# Ler mensagens novas
ssh -p 65002 u202164171@82.25.72.226 "ls -lt /home/u202164171/ai-comm/*.md | head -5"
cat /home/u202164171/ai-comm/NNNN-*.md
```

### Para Codex

**Sistema Principal:** GitHub Issues e Pull Requests
- Code review via PR comments
- Contexto via arquivos neste repositório

**Sistema Secundário:** `/comm/` (perguntas diretas via Filipe)
- Manus/Claude criam mensagem em `/comm/inbox/`
- Filipe copia e cola para Codex
- Codex responde salvando em `/comm/outbox/`
- **Instruções:** `/comm/INSTRUCTIONS-CODEX.md`
- **Boas-vindas:** `/comm/WELCOME-CODEX.md`

---

## 🔗 Links Úteis

**Para quem tem acesso externo (Claude Code, Manus):**
- **Portal:** https://portal.sunyataconsulting.com/
- **Docs:** https://portal.sunyataconsulting.com/comm/#/docs/
- **Memory:** https://portal.sunyataconsulting.com/comm/#/memory/
- **Logs:** https://portal.sunyataconsulting.com/comm/#/logs/

**Para Codex:**
- Ver `/docs/` neste repositório
- Ver `/memory/` neste repositório (quando disponível)

---

## 🎯 Princípios

1. **Simplicidade** > Sofisticação
2. **Funcionalidade** > Perfeição
3. **Documentar** decisões importantes
4. **Testar** antes de deploy
5. **Comunicar** mudanças significativas

---

## 🛡️ Status Atual do Projeto

### Canvas v2 - Seguro para MVP
**Última atualização:** 2025-10-23

**Status:** ✅ Pronto para produção (até 5 usuários)

**Segurança:**
- 🔴 **Críticos corrigidos (3/3):**
  - XSS via form_config (JSON escape)
  - Upload sem validação (FileUploadService)
  - form_data sem validação (whitelist + limites)
- 🟡 **Importantes corrigidos (2/2):**
  - Access control (revalidação no banco)
  - Logging (error_log em falhas)
- 🟢 **Sugestões implementadas (1/4):**
  - Limites SurveyJS (10k chars, 10MB)

**Pendente (antes de escalar 50+ usuários):**
- Schema validation do form_config
- Rate limiting do submit (30/hora)
- Verificar htmlspecialchars em helpers.php

**Commit:** `55e681c` - fix(security): Apply Codex security review fixes

---

## 📚 Workflow por Papel

### Claude Code
1. Ler este START-HERE
2. Verificar `/ai-comm/` via SSH
3. Consultar memória institucional (portal)
4. Implementar features
5. Fazer commit e push

### Codex
1. Ler este START-HERE
2. Ler `/docs/START-HERE-CODEX.md` (específico para você)
3. Aguardar menção em PR ou Issue
4. Revisar código
5. Comentar no PR

### Manus
1. Monitorar `/ai-comm/`
2. Atualizar documentação
3. Revisar arquitetura
4. Manter memória institucional
5. Sincronizar docs para GitHub (quando necessário)

---

**Mantido por:** Manus AI  
**Fonte oficial:** https://portal.sunyataconsulting.com/comm/#/docs/START-HERE  
**Próxima revisão:** Conforme necessário

