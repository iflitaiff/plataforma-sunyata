# Instruções para Codex: Sistema comm/

**Versão:** 1.0  
**Data:** 2025-10-23

---

## 👋 Bem-vindo, Codex!

Este documento explica como você deve usar o sistema de comunicação em `/comm/` para interagir com Manus AI e Claude Code.

---

## 📥 Quando Você Recebe uma Mensagem

Filipe vai copiar o conteúdo de um arquivo de `comm/inbox/` e colar para você. A mensagem terá este formato:

```markdown
# Título da Pergunta

**De:** Manus AI (ou Claude Code)
**Para:** Codex
**Data:** 2025-10-23 17:00
**Prioridade:** Normal

---

## Contexto
...

## Pergunta
...

## Anexos
...
```

---

## 📤 Como Você Deve Responder

### 1. Formato da Resposta

Sempre use este formato:

```markdown
# Resposta: [Título da Pergunta Original]

**De:** Codex
**Para:** [Manus AI | Claude Code]
**Data:** YYYY-MM-DD HH:MM
**Ref:** comm/inbox/[nome-do-arquivo-original].md

---

## Resposta

[Sua resposta detalhada aqui]

---

## Análise

[Se aplicável, análise técnica, problemas encontrados, etc.]

---

## Sugestões

[Se aplicável, sugestões de melhoria, refactoring, etc.]

---

## Anexos

[Se você criar arquivos, liste aqui:]
- `arquivo-corrigido.php` - Descrição
- `diagrama.md` - Descrição
```

### 2. Nome do Arquivo de Resposta

**Formato:** `YYYYMMDD-HHMM-resposta-[titulo-original].md`

**Exemplos:**
- Pergunta: `20251023-1700-bug-upload-service.md`
- Resposta: `20251023-1710-resposta-bug-upload-service.md`

### 3. Localização

**SEMPRE salve sua resposta em:** `comm/outbox/[nome-do-arquivo].md`

**Caminho completo:** `/plataforma-sunyata/comm/outbox/20251023-1710-resposta-bug-upload-service.md`

---

## 📎 Trabalhando com Anexos

### Se a Mensagem Tem Anexos:

Filipe vai mencionar a localização: `comm/inbox/YYYYMMDD-HHMM-titulo-anexos/`

Você pode:
1. Pedir para Filipe mostrar o conteúdo dos arquivos
2. Referenciar os arquivos na sua resposta

### Se Você Quer Incluir Anexos na Resposta:

1. **Crie uma pasta:** `comm/outbox/YYYYMMDD-HHMM-resposta-titulo-anexos/`
2. **Salve seus arquivos lá** (código corrigido, diagramas, etc.)
3. **Liste os anexos** na seção "Anexos" da sua resposta

**Exemplo:**

```markdown
## Anexos

Criei os seguintes arquivos em `comm/outbox/20251023-1710-resposta-bug-upload-service-anexos/`:

- `FileUploadService-fixed.php` - Versão corrigida com comentários
- `test-upload.php` - Script de teste
- `CHANGELOG.md` - Resumo das mudanças
```

---

## ✅ Checklist Antes de Responder

- [ ] Li e entendi a pergunta completamente
- [ ] Verifiquei se há anexos mencionados
- [ ] Preparei resposta detalhada e clara
- [ ] Usei o formato correto de resposta
- [ ] Nomeei o arquivo corretamente (`YYYYMMDD-HHMM-resposta-titulo.md`)
- [ ] Salvei em `comm/outbox/`
- [ ] Se criei arquivos, coloquei em pasta `-anexos/` e listei na resposta
- [ ] Referenciei o arquivo original no campo `Ref:`

---

## 📊 Exemplo Completo

### Mensagem Recebida (inbox):

**Arquivo:** `comm/inbox/20251023-1700-refactor-auth.md`

```markdown
# Refatoração do Sistema de Autenticação

**De:** Manus AI
**Para:** Codex
**Data:** 2025-10-23 17:00
**Prioridade:** Normal

---

## Contexto

O GoogleAuth.php está com código duplicado e difícil de testar.

## Pergunta

Como você refatoraria esta classe para melhorar testabilidade e reduzir duplicação?

## Anexos

- `GoogleAuth.php` - Código atual

**Localização:** `comm/inbox/20251023-1700-refactor-auth-anexos/`
```

### Sua Resposta (outbox):

**Arquivo:** `comm/outbox/20251023-1715-resposta-refactor-auth.md`

```markdown
# Resposta: Refatoração do Sistema de Autenticação

**De:** Codex
**Para:** Manus AI
**Data:** 2025-10-23 17:15
**Ref:** comm/inbox/20251023-1700-refactor-auth.md

---

## Resposta

Analisei o GoogleAuth.php e identifiquei 3 áreas principais para refatoração:

1. **Extração de Interface:** Criar `AuthProviderInterface`
2. **Separação de Responsabilidades:** Mover lógica de token para `TokenManager`
3. **Injeção de Dependências:** Remover instanciação direta de Database

---

## Análise

### Problemas Encontrados:

1. Código duplicado nas linhas 45-67 e 89-111
2. Lógica de negócio misturada com lógica de apresentação
3. Difícil de mockar para testes unitários
4. Violação do Single Responsibility Principle

---

## Sugestões

Propus uma arquitetura em 3 camadas (ver anexos):

- `GoogleAuthProvider` - Implementação específica do Google
- `AuthProviderInterface` - Contrato para providers
- `TokenManager` - Gerenciamento de tokens OAuth

---

## Anexos

Criei os seguintes arquivos em `comm/outbox/20251023-1715-resposta-refactor-auth-anexos/`:

- `GoogleAuthProvider.php` - Classe refatorada
- `AuthProviderInterface.php` - Interface proposta
- `TokenManager.php` - Nova classe para tokens
- `REFACTORING-GUIDE.md` - Guia passo-a-passo da migração
- `tests/GoogleAuthProviderTest.php` - Exemplo de teste unitário
```

**Anexos criados em:** `comm/outbox/20251023-1715-resposta-refactor-auth-anexos/`

---

## 🚫 O Que NÃO Fazer

❌ **Não salve em outro lugar** - Sempre use `comm/outbox/`  
❌ **Não mude o formato** - Siga o template fornecido  
❌ **Não esqueça metadados** - Sempre inclua De, Para, Data, Ref  
❌ **Não use nomes aleatórios** - Siga convenção `YYYYMMDD-HHMM-resposta-titulo.md`  
❌ **Não esqueça anexos** - Se criar arquivos, liste-os na resposta

---

## 💡 Dicas

✅ **Seja específico** - Cite linhas de código, nomes de funções, etc.  
✅ **Inclua exemplos** - Código de exemplo ajuda muito  
✅ **Explique o raciocínio** - Não apenas "o que", mas "por quê"  
✅ **Sugira testes** - Se possível, inclua casos de teste  
✅ **Documente decisões** - Explique trade-offs das suas sugestões

---

## 🔗 Links Úteis

- **Documentação do Projeto:** `/docs/START-HERE.md`
- **Guia Completo para Codex:** `/docs/START-HERE-CODEX.md`
- **Convenções de Código:** `/docs/START-HERE.md` (seção "Convenção de Nomenclatura")

---

## ❓ Dúvidas?

Se algo não estiver claro, pergunte ao Filipe antes de responder. É melhor esclarecer do que assumir incorretamente.

---

**Boa sorte, Codex! 🚀**

