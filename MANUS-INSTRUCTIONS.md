# 📖 INSTRUÇÕES PARA O MANUS - Quadro de Comunicação Técnica

**Última atualização:** 2025-10-22
**Criado por:** Claude Code
**Para:** Manus AI

---

## 🎯 OBJETIVO

Este documento explica como você (Manus) deve usar o **COMM-BOARD.html** para se comunicar eficientemente com Claude Code e Filipe durante o desenvolvimento do MVP Canvas.

---

## 📍 LOCALIZAÇÃO DO ARQUIVO

**Servidor de Produção (Hostinger):**
```
Path: /home/u202164171/domains/sunyataconsulting.com/public_html/plataforma-sunyata/COMM-BOARD.html
URL: https://portal.sunyataconsulting.com/plataforma-sunyata/COMM-BOARD.html
```

**Repositório Git:**
```
Branch: feature/mvp-admin-canvas
File: COMM-BOARD.html (na raiz do projeto)
```

---

## 🔐 ACESSO

- ✅ Você tem acesso SSH ao servidor
- ✅ Você pode editar o arquivo diretamente via terminal
- ✅ Você pode visualizar via browser (protegido por senha)

---

## 📝 COMO ADICIONAR UMA MENSAGEM

### Opção 1: Via SSH (Recomendado)

```bash
# 1. Conectar ao servidor
ssh -p 65002 u202164171@82.25.72.226

# 2. Navegar para o diretório
cd /home/u202164171/domains/sunyataconsulting.com/public_html/plataforma-sunyata

# 3. Editar o arquivo
nano COMM-BOARD.html

# 4. Scroll até o final (antes do </div> do container e antes do footer)
# Procurar por: "<!-- TEMPLATE PARA PRÓXIMAS MENSAGENS"

# 5. Adicionar sua mensagem usando o template abaixo

# 6. Salvar: Ctrl+O, Enter, Ctrl+X
```

### Opção 2: Via SCP (Editar local e enviar)

```bash
# 1. Baixar arquivo
scp -P 65002 u202164171@82.25.72.226:/home/.../COMM-BOARD.html ./

# 2. Editar localmente

# 3. Enviar de volta
scp -P 65002 ./COMM-BOARD.html u202164171@82.25.72.226:/home/.../
```

---

## 🎨 TEMPLATE DA SUA MENSAGEM

**Copie e cole este template ANTES do `<footer>`:**

```html
<div class="message manus">
    <div class="message-header">
        <div class="author">
            <div class="author-icon">🧠</div>
            <span>Manus AI</span>
        </div>
        <div class="timestamp">2025-10-22 12:00 UTC</div>
    </div>
    <div class="message-content">
        <h3>[REVIEW] Título da Sua Análise</h3>

        <p><strong>Status Geral:</strong> ✅ Aprovado | ⚠️ Atenção Necessária | ❌ Bloqueado</p>

        <h4>✅ Pontos Positivos</h4>
        <ul>
            <li>Item positivo 1</li>
            <li>Item positivo 2</li>
        </ul>

        <h4>🔴 Problemas Críticos</h4>
        <ul>
            <li><strong>[BLOQUEADOR]</strong> Descrição do problema</li>
        </ul>

        <h4>🟡 Problemas Médios</h4>
        <ul>
            <li>Descrição do problema</li>
        </ul>

        <h4>💡 Sugestões</h4>
        <ul>
            <li>Sugestão específica 1</li>
            <li>Sugestão específica 2</li>
        </ul>

        <h4>📋 Action Items</h4>
        <ul>
            <li>[ ] Ação para Claude Code</li>
            <li>[ ] Ação para Filipe</li>
        </ul>
    </div>
    <div class="tags">
        <span class="tag type-review">code review</span>
        <span class="tag priority-high">crítico</span>
    </div>
</div>
```

---

## 🏷️ TAGS DISPONÍVEIS

### Tipo de Mensagem
```html
<span class="tag type-review">code review</span>
<span class="tag type-bug">bug report</span>
<span class="tag type-feature">feature</span>
<span class="tag type-question">pergunta</span>
```

### Prioridade
```html
<span class="tag priority-high">crítico</span>
<span class="tag priority-medium">médio</span>
<span class="tag priority-low">baixo</span>
```

### Outras
```html
<span class="tag">arquitetura</span>
<span class="tag">segurança</span>
<span class="tag">performance</span>
<span class="tag">deployment</span>
```

---

## 📋 QUANDO VOCÊ DEVE POSTAR

### ✅ Situações que Exigem Sua Mensagem

1. **Após Code Review**
   - Claude Code fez commits e marcou como "aguardando review"
   - Poste análise de código, bugs encontrados, sugestões

2. **Bugs/Vulnerabilidades Encontrados**
   - Sempre que identificar problemas de segurança
   - Classificar severidade: 🔴 Crítico | 🟡 Médio | 🟢 Baixo

3. **Sugestões de Arquitetura**
   - Quando tiver melhorias arquiteturais importantes
   - Explicar trade-offs e benefícios

4. **Resposta a Perguntas**
   - Claude Code ou Filipe perguntaram algo específico
   - Forneça análise técnica profunda

5. **Validação de Deployment**
   - Após Claude Code fazer deploy
   - Verificar se está tudo correto em produção

### ❌ Quando NÃO Postar

- ❌ Comentários triviais que não agregam valor
- ❌ Sugestões de overengineering (lembrar: MVP-first)
- ❌ Problemas já resolvidos ou discutidos

---

## 📊 FORMATO DE CODE REVIEW

### Template de Review Completo

```html
<div class="message manus">
    <div class="message-header">
        <div class="author">
            <div class="author-icon">🧠</div>
            <span>Manus AI</span>
        </div>
        <div class="timestamp">2025-10-22 14:30 UTC</div>
    </div>
    <div class="message-content">
        <h3>[REVIEW] Sprint 3 - APIs Implementation</h3>

        <p><strong>Status Geral:</strong> ⚠️ Aprovado com Ressalvas</p>
        <p><strong>Commits analisados:</strong> <code>abc1234</code>, <code>def5678</code></p>
        <p><strong>Arquivos revisados:</strong> 15 arquivos, 800 linhas</p>

        <h4>✅ Pontos Fortes</h4>
        <ul>
            <li><strong>Arquitetura:</strong> Separação de responsabilidades bem definida</li>
            <li><strong>Segurança:</strong> CSRF tokens implementados corretamente</li>
            <li><strong>Error Handling:</strong> Try-catch consistente em todos endpoints</li>
        </ul>

        <h4>🔴 Problemas Críticos (BLOQUEADORES)</h4>
        <ul>
            <li>
                <strong>[BUG-CRIT-001]</strong> SQL Injection em <code>/api/chat.php:45</code>
                <br><strong>Código:</strong> <code>$query = "SELECT * FROM users WHERE id = {$userId}";</code>
                <br><strong>Fix:</strong> Usar prepared statements
                <br><strong>Severidade:</strong> 🔴 CRÍTICO - Exploitável
            </li>
        </ul>

        <h4>🟡 Problemas Médios (Importante, mas não bloqueante)</h4>
        <ul>
            <li>
                <strong>[PERF-MED-001]</strong> N+1 query problem em conversation loading
                <br><strong>Impacto:</strong> Performance degrada com >10 mensagens
                <br><strong>Sugestão:</strong> Usar JOIN ou eager loading
            </li>
        </ul>

        <h4>🟢 Problemas Menores (Nice to have)</h4>
        <ul>
            <li>Falta tratamento de timeout em Claude API call</li>
            <li>Logs poderiam ter mais contexto (user_id, request_id)</li>
        </ul>

        <h4>💡 Sugestões de Melhoria</h4>
        <ol>
            <li>
                <strong>Rate Limiting mais sofisticado:</strong>
                <br>Atual: 10 req/hora global
                <br>Sugestão: Por endpoint (upload: 10/h, chat: 30/h)
            </li>
            <li>
                <strong>Response caching:</strong>
                <br>Claude API responses poderiam ser cached (save $$$)
            </li>
        </ol>

        <h4>📋 Action Items</h4>
        <p><strong>Para Claude Code:</strong></p>
        <ul>
            <li>🔴 [CRÍTICO] Corrigir SQL injection em chat.php (estimativa: 10min)</li>
            <li>🟡 [MÉDIO] Otimizar N+1 query (estimativa: 30min)</li>
            <li>🟢 [OPCIONAL] Adicionar request timeout (estimativa: 15min)</li>
        </ul>

        <p><strong>Para Filipe:</strong></p>
        <ul>
            <li>❓ Aprovar cache de respostas do Claude? (trade-off: economia vs frescor)</li>
        </ul>

        <h4>🎯 Recomendação Final</h4>
        <p>
            ⚠️ <strong>Aprovar após correção do bug crítico.</strong>
            <br>O SQL injection DEVE ser corrigido antes de deployment.
            <br>Problemas médios podem ser tratados em sprint subsequente.
        </p>
    </div>
    <div class="tags">
        <span class="tag type-review">code review</span>
        <span class="tag type-bug">bug found</span>
        <span class="tag priority-high">tem bloqueador</span>
    </div>
</div>
```

---

## 🎯 OBJETIVOS DA SUA PARTICIPAÇÃO

### O Que Esperamos de Você

1. **🔍 Code Review Profundo**
   - Análise de segurança (SQL injection, XSS, CSRF, etc)
   - Problemas de performance
   - Bugs sutis que Claude Code pode ter perdido
   - Violações de princípios SOLID

2. **🏗️ Visão Arquitetural**
   - Identificar acoplamentos problemáticos
   - Sugerir refatorações quando fizer sentido
   - Validar decisões técnicas

3. **⚖️ Balance MVP vs Qualidade**
   - Avisar quando algo é overengineering
   - Avisar quando algo é underkill perigoso
   - Ajudar a encontrar o meio-termo pragmático

4. **🚨 Ser o "Safety Net"**
   - Pegar bugs antes de production
   - Identificar vulnerabilidades de segurança
   - Prevenir decisões técnicas ruins

---

## 🚫 O QUE EVITAR

### ❌ Anti-Patterns na Comunicação

1. **Overengineering Suggestions**
   ```
   ❌ BAD: "Sugiro implementar Event Sourcing + CQRS + DDD completo"
   ✅ GOOD: "Para MVP, service atual é OK. Considerar Event Sourcing
            se tráfego > 1000 req/min (monitorar no futuro)"
   ```

2. **Críticas Vagas**
   ```
   ❌ BAD: "O código está ruim"
   ✅ GOOD: "Problema em linha 45: SQL injection via concatenação.
            Fix: usar prepared statements (exemplo abaixo)"
   ```

3. **Sugestões Sem Context**
   ```
   ❌ BAD: "Deveria usar Redis"
   ✅ GOOD: "Performance está OK agora (< 100ms). Redis só se tempo de
            resposta > 500ms ou > 1000 usuários simultâneos"
   ```

---

## 📚 REFERÊNCIAS IMPORTANTES

### Documentos do Projeto

1. **BACKLOG.md** - O que NÃO fazer agora (evitar overengineering)
2. **ANALISE-CRITICA-MANUS.md** - Sua autocrítica anterior (ótimo reference!)
3. **DEPLOYMENT-SPRINT2-BUGFIXES.md** - Estado atual em produção
4. **START-HERE-SPRINT3.md** - O que está sendo desenvolvido agora

### Princípios Acordados

1. **MVP-first:** Validação antes de fundação técnica
2. **Pragmatismo:** 80/20 - focar nos 20% críticos
3. **Segurança:** Nunca comprometer (ownership checks, CSRF, etc)
4. **Simplicidade:** Preferir solução simples que funciona

---

## 🤝 EXEMPLO PRÁTICO

### Cenário: Claude Code Postou "Sprint 3 Completo - Aguardando Review"

**Sua resposta deveria ser:**

```html
<div class="message manus">
    <div class="message-header">
        <div class="author">
            <div class="author-icon">🧠</div>
            <span>Manus AI</span>
        </div>
        <div class="timestamp">2025-10-22 15:00 UTC</div>
    </div>
    <div class="message-content">
        <h3>[REVIEW] Sprint 3 - APIs ✅ Aprovado com 1 correção</h3>

        <p><strong>Status:</strong> ✅ Aprovado após fix do bug crítico</p>

        <h4>📊 Análise Geral</h4>
        <ul>
            <li>Arquivos revisados: /api/upload-file.php, /api/chat.php, /api/export-conversation.php</li>
            <li>Linhas analisadas: ~450 linhas</li>
            <li>Tempo de review: 30min</li>
        </ul>

        <h4>✅ Excelente Trabalho</h4>
        <ul>
            <li>Ownership checks implementados corretamente</li>
            <li>Error handling consistente</li>
            <li>Breaking changes das assinaturas foram respeitadas</li>
            <li>Rate limiting funcionando</li>
        </ul>

        <h4>🔴 1 Bug Crítico Encontrado</h4>
        <ul>
            <li>
                <strong>Arquivo:</strong> /api/chat.php linha 67
                <br><strong>Problema:</strong> Falta validação de ownership da conversation
                <br><strong>Fix:</strong> Adicionar check antes de buscar conversation:
                <pre><code>$conv = $convService->getConversation($convId, $userId);
if (!$conv) {
    http_response_code(403);
    echo json_encode(['error' => 'Acesso negado']);
    exit;
}</code></pre>
                <br><strong>Severidade:</strong> 🔴 CRÍTICO
            </li>
        </ul>

        <h4>📋 Action Item</h4>
        <ul>
            <li>🔴 Claude Code: corrigir ownership check em chat.php (10min)</li>
        </ul>

        <h4>🎯 Conclusão</h4>
        <p>Após fix do bug crítico, código está pronto para deploy. Ótimo trabalho no geral! 👏</p>
    </div>
    <div class="tags">
        <span class="tag type-review">code review</span>
        <span class="tag priority-high">1 bug crítico</span>
    </div>
</div>
```

---

## 📞 DÚVIDAS?

Se tiver dúvidas sobre:
- **Formato da mensagem:** Veja os templates acima
- **Quando postar:** Veja seção "Quando Você Deve Postar"
- **Tom da comunicação:** Seja direto, técnico e construtivo
- **Nível de detalhe:** Específico o suficiente para Claude Code implementar

---

## ✅ CHECKLIST ANTES DE POSTAR

- [ ] Timestamp está correto (formato: YYYY-MM-DD HH:MM UTC)
- [ ] Mensagem está ANTES do `<footer>`
- [ ] Tags apropriadas foram incluídas
- [ ] Problemas críticos estão marcados com 🔴
- [ ] Action items estão claros e acionáveis
- [ ] Código de exemplo está em `<code>` ou `<pre><code>`
- [ ] Arquivo salvo e verificado no browser

---

**Boa comunicação! 🤝**

**Lembre-se:**
- Seja o safety net técnico
- Balance qualidade vs pragmatismo
- Ajude a manter MVP-first mindset
- Previna bugs antes de production

---

**Última atualização:** 2025-10-22
**Criado por:** Claude Code
**Versão:** 1.0
