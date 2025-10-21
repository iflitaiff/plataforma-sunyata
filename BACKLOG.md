# 📋 BACKLOG - Melhorias Pós-MVP

> Sugestões técnicas do Manus para implementar **APÓS** validação do MVP com usuários reais.

## 🎯 FILOSOFIA

**Não implementar antes de validar necessidade real.**

Todas as sugestões abaixo são tecnicamente sólidas, mas devem ser priorizadas baseadas em:
- ✅ Problemas reais encontrados em produção
- ✅ Feedback de usuários
- ✅ Métricas de uso
- ❌ Não por "seria legal ter"

---

## 🔴 PRIORIDADE ALTA (Implementar se problema ocorrer)

### 1. Refatoração de admin-header.php para Resiliência

**Problema que resolve:** Se esquecermos de inicializar `$stats` em novo arquivo admin, causa HTTP 500.

**Solução proposta pelo Manus:**
```php
// admin-header.php
if (!isset($stats) || !is_array($stats)) {
    $stats = [];
}

if (!isset($stats['pending_requests'])) {
    $db_temp = Database::getInstance();
    try {
        $result = $db_temp->fetchOne("SELECT COUNT(*) as count FROM vertical_access_requests WHERE status = 'pending'");
        $stats['pending_requests'] = $result['count'] ?? 0;
    } catch (Exception $e) {
        $stats['pending_requests'] = 0;
    }
}
```

**Quando implementar:**
- ✅ Se criarmos 3+ novos arquivos admin e esquecermos `$stats` em algum
- ✅ Se tivermos novo desenvolvedor no time
- ❌ Não agora (somos só nós 3, estamos atentos)

**Esforço:** 30min
**ROI:** Médio (previne bugs futuros)

---

### 2. Convenção de Nomenclatura Documentada

**Problema que resolve:** Evita sobrescrita acidental de variáveis importantes.

**Solução proposta pelo Manus:**

Criar `docs/CODING-STANDARDS.md` com:
- `$pageStats` para dados compartilhados com views
- `$queryResult` / `$dbResult` para queries temporárias
- Prefixos contextuais em loops (`$itemStats`, `$canvasResult`)
- Nunca reutilizar nomes entre escopos

**Quando implementar:**
- ✅ Quando tivermos 3+ desenvolvedores
- ✅ Quando codebase > 10k linhas
- ✅ Quando tivermos bugs recorrentes de variáveis sobrescritas
- ❌ Não agora (time de 1 pessoa + 2 AIs)

**Esforço:** 2h (doc + aplicar em código existente)
**ROI:** Alto para times, baixo para MVP solo

---

## 🟡 PRIORIDADE MÉDIA (Nice to have)

### 3. Sistema de Logs Estruturado

**Problema que resolve:** Dificulta debug de problemas em produção.

**Solução proposta pelo Manus:**

Estrutura completa:
```
logs/
├── errors/      (php-errors.log, database-errors.log, api-errors.log)
├── access/      (admin-access.log, user-access.log)
├── audit/       (user-actions.log, admin-actions.log)
├── performance/ (slow-queries.log, memory-usage.log)
└── archive/     (logs rotacionados)
```

+ Classe `Logger.php` customizada
+ Rotação automática via cron
+ Monitoramento com alertas

**Quando implementar:**
- ✅ Quando tivermos >100 usuários ativos
- ✅ Quando precisarmos analisar comportamento de uso
- ✅ Quando bugs em produção forem difíceis de reproduzir
- ❌ Não agora (Hostinger já tem logs do LiteSpeed)

**Esforço:** 4-6h
**ROI:** Alto em escala, baixo para MVP

---

### 4. Versionamento de Prompts (Tabela + Interface)

**Problema que resolve:** Dificulta iterar e testar versões de prompts.

**Solução proposta pelo Manus:**

- Nova tabela `prompt_versions`
- Interface admin para criar/ativar versões
- Changelog estruturado de mudanças
- A/B testing (10% usuários em versão experimental)

**Quando implementar:**
- ✅ Quando tivermos múltiplos Canvas (>3)
- ✅ Quando iterarmos prompts semanalmente
- ✅ Quando quisermos medir impacto de mudanças
- ❌ Não agora (1 Canvas, Git + comentários bastam)

**Esforço:** 6-8h
**ROI:** Alto quando temos volume, prematuro agora

---

## 🟢 PRIORIDADE BAIXA (Post-scale)

### 5. PHPStan + Análise Estática

**Problema que resolve:** Previne bugs antes de deploy.

**Solução proposta pelo Manus:**

- PHPStan level 6
- Strict rules
- Git pre-commit hooks
- GitHub Actions CI/CD

**Quando implementar:**
- ✅ Quando tivermos 2+ desenvolvedores
- ✅ Quando codebase > 5k linhas
- ✅ Quando tivermos bugs recorrentes que análise estática detectaria
- ❌ Não agora (solo dev, commits diretos)

**Esforço:** 4-6h (setup + correções)
**ROI:** Alto para times, baixo para solo

---

### 6. Script de Validação Customizado

**Problema que resolve:** Detecta padrões problemáticos específicos do projeto.

**Solução proposta pelo Manus:**

`scripts/validate-admin-pages.php` que verifica:
- Arquivos com `admin-header.php` têm `$stats = []`
- Variáveis não são sobrescritas em loops
- Arrays são inicializados antes de uso
- Diretórios referenciados existem

**Quando implementar:**
- ✅ Quando tivermos padrões recorrentes de bugs
- ✅ Quando onboarding de novos devs
- ❌ Não agora (PHPStan já faz parte disso)

**Esforço:** 3-4h
**ROI:** Médio (PHPStan resolve 80% disso)

---

### 7. Template/Boilerplate para Páginas Admin

**Problema que resolve:** Reduz boilerplate e garante padrão consistente.

**Solução proposta pelo Manus:**

- Arquivo template base
- Script generator (opcional)
- Snippet para VSCode

**Quando implementar:**
- ✅ Quando criarmos 5+ novas páginas admin
- ✅ Quando tivermos desenvolvedores júnior
- ❌ Não agora (copiar página existente é rápido)

**Esforço:** 2h
**ROI:** Baixo (copy-paste funciona)

---

### 8. Code Review Checklist

**Problema que resolve:** Garante qualidade antes de merge.

**Solução proposta pelo Manus:**

Template de PR com checklist:
- [ ] `$stats = []` inicializado
- [ ] Não sobrescreve variáveis em loops
- [ ] Prepared statements em queries
- [ ] CSRF tokens em forms POST
- [ ] Output sanitizado

**Quando implementar:**
- ✅ Quando tivermos PRs de múltiplos devs
- ✅ Quando tivermos histórico de bugs recorrentes
- ❌ Não agora (commits diretos, somos cautelosos)

**Esforço:** 1h
**ROI:** Alto em times, baixo solo

---

## 📊 RESUMO DE PRIORIZAÇÃO

| Item | Esforço | ROI Agora | ROI em Escala | Implementar Quando |
|------|---------|-----------|---------------|---------------------|
| Refatorar admin-header | 30min | Baixo | Alto | 3+ novos arquivos admin |
| Convenção nomenclatura | 2h | Baixo | Alto | 3+ desenvolvedores |
| Sistema de logs | 6h | Baixo | Alto | >100 usuários |
| Versionamento prompts | 8h | Baixo | Alto | >3 Canvas ativos |
| PHPStan + CI/CD | 6h | Baixo | Alto | 2+ desenvolvedores |
| Script validação | 4h | Baixo | Médio | Padrões recorrentes |
| Template admin | 2h | Baixo | Médio | 5+ novas páginas |
| Checklist PR | 1h | Baixo | Alto | PRs de múltiplos devs |

**TOTAL SE IMPLEMENTAR TUDO: ~29 horas**

---

## 🎯 DECISÃO ESTRATÉGICA

**AGORA (MVP):** Foco em completar funcionalidades core (Sprints 2-4)
**DEPOIS:** Implementar itens deste backlog **conforme necessidade comprovada**

**Regra de Ouro:**
> Se não está resolvendo um problema que **já aconteceu** ou que usuários **já reclamaram**, vai para o backlog.

---

## 📝 CHANGELOG

### 2025-10-21
- ✅ Corrigidos bugs potenciais em 3 arquivos (users.php, access-requests.php, audit-logs.php)
- ✅ Melhorado system_prompt do Canvas Jurídico (chain-of-thought, formatação estruturada)
- 📋 Documentadas 8 melhorias para implementar pós-MVP

---

**Última atualização:** 2025-10-21
**Próxima revisão:** Após validação do MVP com usuários (Sprint 4 completo)
