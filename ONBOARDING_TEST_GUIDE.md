# Guia de Teste - Fluxo de Onboarding Jurídico

## Problema Resolvido

O usuário ficava em loop ao selecionar a vertical Jurídico durante o onboarding. Agora o fluxo está corrigido com três cenários distintos:

## Arquivos Modificados/Criados

1. **public/aguardando-aprovacao.php** (NOVO)
   - Tela de espera com auto-refresh a cada 30 segundos
   - Verifica automaticamente se a aprovação foi concedida
   - Opções: verificar agora, escolher outra vertical, sair

2. **public/onboarding-juridico.php** (MODIFICADO)
   - Adicionada lógica de branching baseada na configuração
   - Se aprovação desabilitada: acesso imediato
   - Se aprovação habilitada: redireciona para tela de espera

## Fluxos de Teste

### Cenário 1: Usuário seleciona vertical NÃO-Jurídico
**Comportamento Esperado:** Fluxo normal, sem necessidade de aprovação

1. Login no sistema
2. Selecionar vertical (Docência, Pesquisa, etc.) - NÃO Jurídico
3. Preencher formulário de onboarding
4. ✅ Acesso imediato ao dashboard

### Cenário 2: Jurídico com Aprovação DESABILITADA
**Comportamento Esperado:** Acesso imediato após preenchimento

**Preparação:**
```sql
UPDATE settings
SET setting_value = '0', updated_at = NOW()
WHERE setting_key = 'juridico_requires_approval';
```

**Teste:**
1. Login no sistema
2. Selecionar "Jurídico" no onboarding
3. Preencher formulário com dados jurídicos
4. Clicar em "Enviar Solicitação"
5. ✅ Redireciona DIRETAMENTE para dashboard
6. ✅ Vertical Jurídico fica acessível imediatamente

**Verificação no Banco:**
```sql
SELECT id, email, selected_vertical, completed_onboarding
FROM users
WHERE email = 'claudesunyata@gmail.com';
-- Deve mostrar: selected_vertical = 'juridico', completed_onboarding = 1
```

### Cenário 3: Jurídico com Aprovação HABILITADA
**Comportamento Esperado:** Tela de espera com auto-refresh

**Preparação:**
```sql
UPDATE settings
SET setting_value = '1', updated_at = NOW()
WHERE setting_key = 'juridico_requires_approval';
```

**Teste:**
1. Login no sistema
2. Selecionar "Jurídico" no onboarding
3. Preencher formulário com dados jurídicos
4. Clicar em "Enviar Solicitação"
5. ✅ Redireciona para `/aguardando-aprovacao.php`
6. ✅ Vê tela de espera com ícone animado
7. ✅ Mostra tempo de espera
8. ✅ Mensagem: "Verificando status... (atualiza a cada 30 segundos)"
9. ✅ Botões disponíveis:
   - "Verificar Agora" (reload manual)
   - "Escolher Outra Vertical" (volta ao onboarding)
   - "Sair da Sessão" (logout)

**Simulação de Aprovação (em outra aba/sessão admin):**
```sql
-- Encontrar ID da solicitação
SELECT id, user_id, vertical, status
FROM vertical_access_requests
WHERE user_id = 34 AND vertical = 'juridico'
ORDER BY requested_at DESC LIMIT 1;

-- Aprovar
UPDATE vertical_access_requests
SET status = 'approved',
    processed_at = NOW(),
    processed_by = 1
WHERE id = <ID_DA_SOLICITACAO>;

-- Dar acesso ao usuário
UPDATE users
SET selected_vertical = 'juridico',
    completed_onboarding = 1
WHERE id = 34;
```

**Após Aprovação:**
10. ✅ Na próxima atualização (automática em 30s ou manual), usuário é redirecionado para dashboard
11. ✅ Vertical Jurídico fica acessível

## Credenciais de Teste

**Email:** claudesunyata@gmail.com
**Método:** Google OAuth

## Como Resetar Usuário para Novo Teste

```sql
-- Resetar onboarding
UPDATE users
SET selected_vertical = NULL,
    completed_onboarding = 0
WHERE email = 'claudesunyata@gmail.com';

-- Limpar solicitações antigas (opcional)
DELETE FROM vertical_access_requests
WHERE user_id = 34;
```

## Comandos Úteis de Verificação

### Ver status atual do usuário:
```bash
ssh -p 65002 u202164171@82.25.72.226 "/usr/bin/mariadb u202164171_sunyata -p'MiGOq%tMrUP+9Qy@bxR' -e \"SELECT id, email, selected_vertical, completed_onboarding FROM users WHERE email = 'claudesunyata@gmail.com';\""
```

### Ver solicitações pendentes:
```bash
ssh -p 65002 u202164171@82.25.72.226 "/usr/bin/mariadb u202164171_sunyata -p'MiGOq%tMrUP+9Qy@bxR' -e 'SELECT id, user_id, vertical, status, requested_at FROM vertical_access_requests WHERE user_id = 34 ORDER BY requested_at DESC;'"
```

### Ver configuração de aprovação:
```bash
ssh -p 65002 u202164171@82.25.72.226 "/usr/bin/mariadb u202164171_sunyata -p'MiGOq%tMrUP+9Qy@bxR' -e \"SELECT setting_key, setting_value FROM settings WHERE setting_key = 'juridico_requires_approval';\""
```

### Ver logs de erro em tempo real:
```bash
ssh -p 65002 u202164171@82.25.72.226 "tail -f /home/u202164171/domains/sunyataconsulting.com/logs/error.log"
```

## Pontos de Atenção

1. **Auto-refresh funciona via meta tag:** `<meta http-equiv="refresh" content="30">`
2. **Sessão persiste durante espera:** Usuário não precisa fazer login novamente
3. **Múltiplas abas:** Se usuário abrir várias abas, todas vão atualizar
4. **Logout disponível:** Usuário pode sair e voltar depois sem perder a solicitação

## Checklist de Validação

- [ ] Dashboard CSS funcionando (sem quebrar)
- [ ] Cenário 1: Verticais não-Jurídico funcionam normalmente
- [ ] Cenário 2: Jurídico + aprovação OFF = acesso imediato
- [ ] Cenário 3: Jurídico + aprovação ON = tela de espera
- [ ] Auto-refresh a cada 30 segundos funciona
- [ ] Botão "Verificar Agora" funciona
- [ ] Botão "Escolher Outra Vertical" volta ao onboarding
- [ ] Botão "Sair da Sessão" faz logout
- [ ] Após aprovação, usuário é redirecionado automaticamente
- [ ] Tempo de espera é exibido corretamente
- [ ] Não há mais loop de onboarding

## URLs Importantes

- **Portal:** https://portal.sunyataconsulting.com
- **Admin:** https://portal.sunyataconsulting.com/admin/
- **Aguardando Aprovação:** https://portal.sunyataconsulting.com/aguardando-aprovacao.php
- **Onboarding Jurídico:** https://portal.sunyataconsulting.com/onboarding-juridico.php
