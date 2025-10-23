# Sistema de Comunicação com Codex

**Versão:** 1.0  
**Data:** 2025-10-23  
**Mantido por:** Manus AI

---

## 🎯 Propósito

Este diretório facilita a comunicação entre **Manus AI / Claude Code** e **Codex** através do Filipe como intermediário.

**Por quê?** Codex não tem acesso SSH ou externo, apenas ao repositório GitHub. Este sistema permite comunicação assíncrona via arquivos versionados.

---

## 📁 Estrutura

```
/comm/
├── inbox/          # Mensagens DE Manus/Claude PARA Codex
├── outbox/         # Respostas DE Codex PARA Manus/Claude
├── archive/        # Conversas finalizadas (movidas após conclusão)
├── README.md       # Este arquivo
└── TEMPLATE.md     # Template para novas mensagens
```

---

## 🔄 Workflow

### 1️⃣ Manus/Claude Criam Mensagem

**Arquivo:** `comm/inbox/YYYYMMDD-HHMM-titulo-kebab-case.md`

**Formato:**
```markdown
# Título da Pergunta

**De:** Manus AI (ou Claude Code)
**Para:** Codex
**Data:** YYYY-MM-DD HH:MM
**Prioridade:** Normal (ou Alta/Baixa)

---

## Contexto

Explicação do contexto...

## Pergunta

O que você precisa saber...

## Anexos

Se houver anexos, criar pasta:
`comm/inbox/YYYYMMDD-HHMM-titulo-kebab-case-anexos/`
```

### 2️⃣ Filipe Recebe Notificação

- Email verde 🟢 via sistema ai-comm
- Assunto: "Nova mensagem para Codex"
- Corpo: Nome do arquivo em `comm/inbox/`

### 3️⃣ Filipe Repassa para Codex

1. Abre arquivo em `comm/inbox/YYYYMMDD-HHMM-titulo.md`
2. Copia conteúdo completo
3. Cola no Codex
4. Instrui: "Responda salvando em `comm/outbox/YYYYMMDD-HHMM-resposta-titulo.md`"

### 4️⃣ Codex Responde

Codex cria arquivo em `comm/outbox/` com formato:

```markdown
# Resposta: Título da Pergunta

**De:** Codex
**Para:** Manus AI (ou Claude Code)
**Data:** YYYY-MM-DD HH:MM
**Ref:** comm/inbox/YYYYMMDD-HHMM-titulo.md

---

## Resposta

Conteúdo da resposta...

## Anexos (se houver)

`comm/outbox/YYYYMMDD-HHMM-resposta-titulo-anexos/`
```

### 5️⃣ Manus/Claude Leem Resposta

```bash
# Via SSH
ssh -p 65002 u202164171@82.25.72.226 \
  "cat /home/u202164171/domains/sunyataconsulting.com/public_html/plataforma-sunyata/comm/outbox/YYYYMMDD-HHMM-resposta-titulo.md"
```

### 6️⃣ Arquivamento (Opcional)

Após conclusão, mover para `comm/archive/`:

```bash
mv comm/inbox/YYYYMMDD-HHMM-titulo.md comm/archive/
mv comm/outbox/YYYYMMDD-HHMM-resposta-titulo.md comm/archive/
```

---

## 📎 Sistema de Anexos

### Para Incluir Anexos na Pergunta:

1. Criar pasta: `comm/inbox/YYYYMMDD-HHMM-titulo-anexos/`
2. Colocar arquivos dentro (código, logs, screenshots, etc.)
3. Mencionar no corpo da mensagem:

```markdown
## Anexos

- `arquivo1.php` - Código com bug
- `error.log` - Log do erro
- `screenshot.png` - Captura de tela
```

### Para Incluir Anexos na Resposta:

Codex cria pasta: `comm/outbox/YYYYMMDD-HHMM-resposta-titulo-anexos/`

---

## 📋 Convenções de Nomenclatura

### Arquivos de Mensagem:
- **Formato:** `YYYYMMDD-HHMM-titulo-kebab-case.md`
- **Exemplo:** `20251023-1700-refactoring-user-service.md`

### Pastas de Anexos:
- **Formato:** `YYYYMMDD-HHMM-titulo-kebab-case-anexos/`
- **Exemplo:** `20251023-1700-refactoring-user-service-anexos/`

### Arquivos de Resposta:
- **Formato:** `YYYYMMDD-HHMM-resposta-titulo-kebab-case.md`
- **Exemplo:** `20251023-1705-resposta-refactoring-user-service.md`

---

## 🎨 Identificação Visual (Emails)

Quando Manus/Claude criam mensagem para Codex, Filipe recebe email com:

- **Cabeçalho verde** 🟢 (cor do Codex)
- **Assunto:** `[ai-comm] Nova mensagem para Codex`
- **Corpo:** Nome do arquivo em `comm/inbox/`

---

## ⚠️ Importante

### Para Manus/Claude:
- ✅ Sempre criar arquivo em `comm/inbox/`
- ✅ Seguir convenção de nomenclatura
- ✅ Avisar Filipe via ai-comm quando criar mensagem
- ✅ Verificar `comm/outbox/` periodicamente para respostas

### Para Codex:
- ✅ Sempre salvar resposta em `comm/outbox/`
- ✅ Usar prefixo `resposta-` no nome do arquivo
- ✅ Referenciar arquivo original no campo `Ref:`
- ✅ Criar pasta `-anexos/` se precisar incluir arquivos

### Para Filipe:
- ✅ Copiar conteúdo completo (incluindo metadados)
- ✅ Instruir Codex sobre onde salvar resposta
- ✅ Verificar se anexos foram incluídos corretamente

---

## 📊 Exemplo Completo

### Mensagem (inbox):

**Arquivo:** `comm/inbox/20251023-1700-bug-upload-service.md`

```markdown
# Bug no FileUploadService

**De:** Manus AI
**Para:** Codex
**Data:** 2025-10-23 17:00
**Prioridade:** Alta

---

## Contexto

O FileUploadService está lançando exceção ao fazer upload de arquivos > 5MB.

## Pergunta

Você consegue identificar o problema no código anexo e sugerir correção?

## Anexos

- `FileUploadService.php` - Código atual
- `error.log` - Log do erro
```

**Anexos:** `comm/inbox/20251023-1700-bug-upload-service-anexos/FileUploadService.php`

### Resposta (outbox):

**Arquivo:** `comm/outbox/20251023-1710-resposta-bug-upload-service.md`

```markdown
# Resposta: Bug no FileUploadService

**De:** Codex
**Para:** Manus AI
**Data:** 2025-10-23 17:10
**Ref:** comm/inbox/20251023-1700-bug-upload-service.md

---

## Resposta

Identifiquei o problema na linha 45 do FileUploadService.php...

## Solução Proposta

[código corrigido]

## Anexos

- `FileUploadService-fixed.php` - Versão corrigida
```

**Anexos:** `comm/outbox/20251023-1710-resposta-bug-upload-service-anexos/FileUploadService-fixed.php`

---

## 🔗 Links Relacionados

- **Sistema ai-comm (servidor):** `/home/u202164171/ai-comm/`
- **Documentação Codex:** `/docs/START-HERE-CODEX.md`
- **Documentação Geral:** `/docs/START-HERE.md`

---

## 📝 Changelog

- **2025-10-23:** Sistema criado (v1.0)

---

**Dúvidas?** Consulte `/docs/START-HERE.md` ou pergunte via ai-comm.

