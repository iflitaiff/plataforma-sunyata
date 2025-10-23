# Instruções para Codex - Sistema comm/

**Versão:** 2.0 (Simplificada)  
**Data:** 2025-10-23

---

## 🎯 O Que Fazer

Quando Filipe te mostrar uma mensagem de `/comm/inbox/`, você deve:

1. **Ler a mensagem**
2. **Responder criando arquivo** em `/comm/outbox/`
3. **Commit e push**

---

## 📝 Formato da Resposta

**Nome do arquivo:**
```
comm/outbox/YYYYMMDD-HHMM-resposta-[titulo-original].md
```

**Exemplo:**
- Mensagem: `comm/inbox/20251023-1730-refactor-auth.md`
- Resposta: `comm/outbox/20251023-1735-resposta-refactor-auth.md`

---

## 📄 Template da Resposta

```markdown
# Resposta: [Título da Pergunta]

**De:** Codex  
**Para:** [Quem Perguntou]  
**Data:** YYYY-MM-DD HH:MM  
**Ref:** comm/inbox/[arquivo-original].md

---

## Resposta

[Sua resposta aqui]

## Análise

[Análise técnica, se aplicável]

## Sugestões

[Sugestões de código, refactoring, etc.]

## Anexos

[Se houver arquivos de código/exemplo]

**Localização:** comm/outbox/YYYYMMDD-HHMM-resposta-[titulo]-anexos/
```

---

## 📎 Anexos (Opcional)

Se precisar incluir código/arquivos:

1. **Crie pasta:** `comm/outbox/YYYYMMDD-HHMM-resposta-[titulo]-anexos/`
2. **Coloque arquivos** dentro
3. **Mencione na resposta**

---

## 🔄 Comandos Git

Após criar a resposta:

```bash
git add comm/outbox/
git commit -m "docs(comm): resposta para [titulo]"
git push origin feature/mvp-admin-canvas
```

---

## ✅ Checklist

Antes de finalizar:

- [ ] Arquivo criado em `/comm/outbox/`
- [ ] Nome segue padrão `YYYYMMDD-HHMM-resposta-*.md`
- [ ] Resposta usa template acima
- [ ] Anexos em pasta separada (se houver)
- [ ] Commit e push realizados

---

## 💡 Exemplo Completo

### Mensagem Recebida:
`comm/inbox/20251023-1730-refactor-auth.md`

### Sua Resposta:
`comm/outbox/20251023-1735-resposta-refactor-auth.md`

### Conteúdo:
```markdown
# Resposta: Refatoração do GoogleAuth

**De:** Codex  
**Para:** Claude Code  
**Data:** 2025-10-23 17:35  
**Ref:** comm/inbox/20251023-1730-refactor-auth.md

---

## Resposta

Identifiquei 3 pontos de melhoria no GoogleAuth.php.

## Análise

1. Código duplicado nas linhas 45-60
2. Falta de tratamento de erro
3. Variáveis com nomes não descritivos

## Sugestões

Ver arquivo refatorado nos anexos.

## Anexos

- `GoogleAuth-refactored.php` - Versão melhorada
- `CHANGELOG.md` - Resumo das mudanças

**Localização:** comm/outbox/20251023-1735-resposta-refactor-auth-anexos/
```

### Anexos:
```
comm/outbox/20251023-1735-resposta-refactor-auth-anexos/
├── GoogleAuth-refactored.php
└── CHANGELOG.md
```

### Git:
```bash
git add comm/outbox/
git commit -m "docs(comm): resposta para refactor-auth"
git push origin feature/mvp-admin-canvas
```

---

**Pronto!** Filipe será notificado e avisará quem perguntou.

