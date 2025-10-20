# ✅ Sistema Pronto para Testes

**Data:** 20/10/2025
**Status:** PRONTO

---

## 🎯 Resumo Executivo

O sistema foi preparado com sucesso para testes com os 3 usuários especificados. Todos os rastros anteriores foram removidos e o ambiente está limpo para novos testes de onboarding.

## 👥 Usuários de Teste

Os seguintes usuários foram **REMOVIDOS COMPLETAMENTE** do sistema e estão prontos para novos testes:

1. ✅ **filipe.litaiff@gmail.com**
2. ✅ **pmo@diagnext.com**
3. ✅ **filipe.barbosa@coppead.ufrj.br**

### Próximo Login

Quando esses usuários fizerem login pela primeira vez via Google OAuth, eles serão:
- Criados como novos usuários (`access_level: guest`)
- Direcionados para o onboarding completo
- Terão que selecionar uma vertical
- Dependendo da vertical e configuração, seguirão o fluxo apropriado

## 🔒 Usuários Protegidos (Admins)

Estes usuários **NÃO FORAM TOCADOS** e permanecem ativos:

1. ✅ **flitaiff@gmail.com** (ID: 7) - Admin
2. ✅ **filipe.litaiff@ifrj.edu.br** (ID: 1) - Admin

## 📊 Status Atual do Sistema

```
Total de usuários:        18
Administradores:          2
Usuários guest:          16
Onboarding completo:     18
Solicitações pendentes:   2
```

## 🗑️ Dados Removidos

Para cada usuário de teste, foram removidos:

| Item                              | Quantidade |
|-----------------------------------|------------|
| Histórico de Prompts (Claude API) | 0          |
| Solicitações de Acesso Vertical   | 0          |
| Perfis de Usuário                 | 0          |
| Logs de Auditoria                 | 0          |
| Registros de Usuários             | 3          |
| Sessões Ativas                    | 0          |
| Cache do Sistema                  | Limpo      |

## ⚙️ Configuração Atual

### Aprovação Jurídico

**Status:** HABILITADA (valor = 1)

Isso significa que:
- Usuários que escolherem "Jurídico" serão direcionados para tela de espera
- Precisarão de aprovação manual via admin dashboard
- A tela atualiza automaticamente a cada 30 segundos

### Para Testar com Acesso Imediato

Se quiser testar o fluxo de acesso imediato (sem aprovação), desabilite temporariamente:

```bash
ssh -p 65002 u202164171@82.25.72.226 "/usr/bin/mariadb u202164171_sunyata -p'MiGOq%tMrUP+9Qy@bxR' -e \"UPDATE settings SET setting_value = '0', updated_at = NOW() WHERE setting_key = 'juridico_requires_approval';\""
```

### Para Reabilitar Aprovação

```bash
ssh -p 65002 u202164171@82.25.72.226 "/usr/bin/mariadb u202164171_sunyata -p'MiGOq%tMrUP+9Qy@bxR' -e \"UPDATE settings SET setting_value = '1', updated_at = NOW() WHERE setting_key = 'juridico_requires_approval';\""
```

## 🧪 Fluxos de Teste Disponíveis

### Teste 1: Vertical Não-Jurídico (Docência, Pesquisa, etc.)

**Passos:**
1. Login com usuário de teste
2. Selecionar vertical não-jurídico
3. Preencher formulário de onboarding
4. ✅ **Resultado Esperado:** Acesso imediato ao dashboard

---

### Teste 2: Jurídico com Aprovação DESABILITADA

**Preparação:**
```bash
# Desabilitar aprovação
ssh -p 65002 u202164171@82.25.72.226 "/usr/bin/mariadb u202164171_sunyata -p'MiGOq%tMrUP+9Qy@bxR' -e \"UPDATE settings SET setting_value = '0' WHERE setting_key = 'juridico_requires_approval';\""
```

**Passos:**
1. Login com usuário de teste
2. Selecionar "Jurídico"
3. Preencher formulário específico do Jurídico
4. Clicar em "Enviar Solicitação"
5. ✅ **Resultado Esperado:** Acesso IMEDIATO ao dashboard
6. ✅ Vertical Jurídico disponível instantaneamente

---

### Teste 3: Jurídico com Aprovação HABILITADA (Estado Atual)

**Preparação:** Já está habilitado!

**Passos:**
1. Login com usuário de teste
2. Selecionar "Jurídico"
3. Preencher formulário específico do Jurídico
4. Clicar em "Enviar Solicitação"
5. ✅ **Resultado Esperado:** Redirecionado para `/aguardando-aprovacao.php`

**Tela de Espera:**
- ⏰ Atualiza automaticamente a cada 30 segundos
- 📊 Mostra tempo de espera
- 🔄 Botão "Verificar Agora" (refresh manual)
- ↩️ Botão "Escolher Outra Vertical" (voltar)
- 🚪 Botão "Sair da Sessão" (logout)

**Aprovar no Admin Dashboard:**
1. Abrir outra aba/navegador
2. Login como admin (flitaiff@gmail.com ou filipe.litaiff@ifrj.edu.br)
3. Acessar: https://portal.sunyataconsulting.com/admin/
4. Ir para "Solicitações de Acesso"
5. Aprovar a solicitação do usuário

**Após Aprovação:**
6. ✅ Usuário é redirecionado automaticamente no próximo refresh
7. ✅ Vertical Jurídico fica disponível

## 📁 Scripts e Documentação

### Scripts Disponíveis

| Script | Localização | Uso |
|--------|-------------|-----|
| **Preparação de Testes** | `scripts/prepare-test-users.sh` | Remove usuários de teste e rastros |
| **Estatísticas** | `scripts/admin-cli/stats.php` | Ver estatísticas do sistema |
| **Sessões** | `scripts/admin-cli/sessions.php` | Gerenciar sessões ativas |
| **Custo API** | `scripts/admin-cli/check-api-cost.php` | Monitorar custos Claude API |
| **Cache** | `scripts/admin-cli/cache.php` | Limpar cache do sistema |

### Documentação

| Documento | Descrição |
|-----------|-----------|
| `ONBOARDING_TEST_GUIDE.md` | Guia completo de testes de onboarding |
| `scripts/README-TEST-PREP.md` | Documentação do script de preparação |
| `TEST_READY_STATUS.md` | Este documento |

## 🚀 Como Começar os Testes

### Passo 1: Escolher Cenário

Decida qual fluxo quer testar primeiro:
- Vertical não-jurídico (sem aprovação)
- Jurídico com acesso imediato (aprovação OFF)
- Jurídico com aprovação (aprovação ON - **estado atual**)

### Passo 2: Configurar (se necessário)

Se quiser testar com aprovação desabilitada, execute o comando de desabilitar acima.

### Passo 3: Fazer Login

Acesse: **https://portal.sunyataconsulting.com**

Faça login com um dos usuários de teste:
- filipe.litaiff@gmail.com
- pmo@diagnext.com
- filipe.barbosa@coppead.ufrj.br

### Passo 4: Completar Onboarding

Siga o fluxo de onboarding normalmente e observe os comportamentos.

### Passo 5: Verificar Resultados

Confirme que o fluxo funciona como esperado conforme descrito acima.

## 🔍 Comandos Úteis de Verificação

### Ver status de um usuário específico

```bash
ssh -p 65002 u202164171@82.25.72.226 "/usr/bin/mariadb u202164171_sunyata -p'MiGOq%tMrUP+9Qy@bxR' -e \"SELECT id, email, name, selected_vertical, completed_onboarding FROM users WHERE email = 'filipe.litaiff@gmail.com';\""
```

### Ver todas as solicitações de acesso

```bash
ssh -p 65002 u202164171@82.25.72.226 "/usr/bin/mariadb u202164171_sunyata -p'MiGOq%tMrUP+9Qy@bxR' -e 'SELECT r.id, u.email, r.vertical, r.status, r.requested_at FROM vertical_access_requests r JOIN users u ON r.user_id = u.id ORDER BY r.requested_at DESC LIMIT 10;'"
```

### Ver logs de erro em tempo real

```bash
ssh -p 65002 u202164171@82.25.72.226 "tail -f /home/u202164171/domains/sunyataconsulting.com/logs/error.log"
```

### Ver último erro

```bash
ssh -p 65002 u202164171@82.25.72.226 "tail -20 /home/u202164171/domains/sunyataconsulting.com/logs/error.log"
```

## 🎬 Para Resetar e Testar Novamente

Se precisar remover novamente os usuários de teste e começar do zero:

```bash
cd /home/iflitaiff/projetos/plataforma-sunyata
./scripts/prepare-test-users.sh -y
```

Ou execute sem `-y` para confirmação interativa:

```bash
./scripts/prepare-test-users.sh
```

## ✅ Checklist de Validação

Ao testar, verifique:

- [ ] Dashboard CSS está funcionando (sem quebrar)
- [ ] Login via Google OAuth funciona
- [ ] Onboarding aparece para usuários novos
- [ ] Seleção de vertical funciona
- [ ] **Cenário 1:** Verticais não-jurídico têm acesso imediato
- [ ] **Cenário 2:** Jurídico + aprovação OFF = acesso imediato
- [ ] **Cenário 3:** Jurídico + aprovação ON = tela de espera
- [ ] Tela de espera atualiza a cada 30 segundos
- [ ] Botão "Verificar Agora" funciona
- [ ] Botão "Escolher Outra Vertical" volta ao onboarding
- [ ] Botão "Sair da Sessão" faz logout
- [ ] Admin pode aprovar solicitações
- [ ] Após aprovação, usuário é redirecionado
- [ ] Vertical aprovada fica acessível
- [ ] Não há mais loop de onboarding

## 🎯 Resultado Esperado Final

Após completar todos os testes, você deve ter confirmado que:

1. ✅ O fluxo de onboarding funciona para todas as verticais
2. ✅ O sistema respeita a configuração de aprovação Jurídico
3. ✅ A tela de espera funciona com auto-refresh
4. ✅ Admins conseguem aprovar solicitações
5. ✅ Usuários aprovados têm acesso imediato após aprovação
6. ✅ Não há mais loop de onboarding
7. ✅ Dashboard permanece funcional durante todo o processo

---

## 📞 Suporte

Se encontrar problemas:

1. **Ver logs:** Use os comandos de verificação acima
2. **Verificar banco:** Use as queries SQL fornecidas
3. **Resetar:** Execute o script de preparação novamente
4. **Consultar documentação:** Ver `ONBOARDING_TEST_GUIDE.md`

---

**🎉 SISTEMA PRONTO PARA TESTES! BOA SORTE! 🚀**
