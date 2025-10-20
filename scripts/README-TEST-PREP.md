# Script de Preparação para Testes

## 📋 Descrição

Este script remove completamente os usuários de teste especificados e todos os seus rastros do sistema, preparando o ambiente para testes do zero.

## 🔒 Segurança

**USUÁRIOS PROTEGIDOS** (nunca serão removidos):
- `flitaiff@gmail.com` (admin)
- `filipe.litaiff@ifrj.edu.br` (admin)

**USUÁRIOS DE TESTE** (serão removidos):
- `filipe.litaiff@gmail.com`
- `pmo@diagnext.com`
- `filipe.barbosa@coppead.ufrj.br`

## 🧹 O Que o Script Remove

Para cada usuário de teste, o script remove:

1. **Histórico de Prompts** (`prompt_history`)
   - Todos os prompts gerados com Claude API
   - Respostas e custos associados

2. **Solicitações de Acesso** (`vertical_access_requests`)
   - Solicitações pendentes, aprovadas ou rejeitadas
   - Dados do formulário de solicitação

3. **Perfis de Usuário** (`user_profiles`)
   - Dados adicionais do perfil
   - Informações jurídicas (OAB, escritório, etc.)

4. **Logs de Auditoria** (`audit_logs`)
   - Registros de ações do usuário
   - Histórico de atividades

5. **Registro do Usuário** (`users`)
   - Conta completa do usuário
   - Dados de login e autenticação

6. **Sessões Ativas**
   - Arquivos de sessão PHP
   - Força logout de todos os usuários

7. **Cache do Sistema**
   - Cache de configurações
   - Cache de dados temporários

## 🚀 Como Usar

### Execução Simples

```bash
cd /home/iflitaiff/projetos/plataforma-sunyata
./scripts/prepare-test-users.sh
```

### O que acontece:

1. **Verificação de Segurança**
   - Lista os admins protegidos
   - Lista os usuários que serão removidos

2. **Confirmação**
   - Solicita confirmação digitando 'SIM'
   - Pode ser cancelado a qualquer momento

3. **Remoção em 8 Etapas**
   - Remove dados de forma ordenada (respeitando foreign keys)
   - Exibe progresso em tempo real
   - Mostra estatísticas de cada etapa

4. **Verificação Final**
   - Confirma que usuários foram removidos
   - Mostra status do sistema
   - Exibe configurações atuais

## 📊 Output Esperado

```
╔═══════════════════════════════════════════════════════════════╗
║    Script de Preparação para Testes - Plataforma Sunyata     ║
╚═══════════════════════════════════════════════════════════════╝

🔒 Verificando usuários ADMINS protegidos...
   ✓ flitaiff@gmail.com (protegido)
   ✓ filipe.litaiff@ifrj.edu.br (protegido)

📋 Usuários de teste que serão REMOVIDOS:
   ✗ ID: 33 | filipe.litaiff@gmail.com | Filipe Litaiff
   ✗ ID: 35 | pmo@diagnext.com | Project Manager Office PMO
   - filipe.barbosa@coppead.ufrj.br (não existe no sistema)

⚠️  ATENÇÃO: Esta ação é IRREVERSÍVEL!
   Todos os dados desses usuários serão PERMANENTEMENTE removidos.

Deseja continuar? (digite 'SIM' para confirmar): SIM

✓ Confirmação recebida. Iniciando remoção...

[1/8] Identificando IDs dos usuários...
      ✓ filipe.litaiff@gmail.com → ID: 33
      ✓ pmo@diagnext.com → ID: 35
      Total de usuários a remover: 2

[2/8] Removendo histórico de prompts da API Claude...
      ✓ 5 registro(s) removido(s)

[3/8] Removendo solicitações de acesso vertical...
      ✓ 3 solicitação(ões) removida(s)

[4/8] Removendo perfis de usuário...
      ✓ 2 perfil(is) removido(s)

[5/8] Removendo logs de auditoria...
      ✓ 12 log(s) removido(s)

[6/8] Removendo registros de usuários...
      ✓ filipe.litaiff@gmail.com removido
      ✓ pmo@diagnext.com removido

[7/8] Limpando sessões ativas...
      ✓ 3 sessão(ões) removida(s)

[8/8] Limpando cache do sistema...
      ✓ Cache limpo

═══════════════════════════════════════════════════════════════
✓ REMOÇÃO CONCLUÍDA COM SUCESSO!
═══════════════════════════════════════════════════════════════

📊 Estatísticas de Remoção:
   • Prompts removidos: 5
   • Solicitações removidas: 3
   • Perfis removidos: 2
   • Logs removidos: 12
   • Usuários removidos: 2
   • Sessões limpas: 3
   • Cache: limpo

🔍 Verificação Final:
   ✓ filipe.litaiff@gmail.com - REMOVIDO
   ✓ pmo@diagnext.com - REMOVIDO
   ✓ filipe.barbosa@coppead.ufrj.br - REMOVIDO

📋 Status do Sistema:
   • Total de usuários no sistema: 17
   • Administradores: 2
   • Solicitações pendentes: 0

⚙️  Configuração Atual:
   • Aprovação Jurídico: HABILITADA

💡 Dica: Para testar acesso imediato, desabilite a aprovação:
   ssh -p 65002 u202164171@82.25.72.226 ...

╔═══════════════════════════════════════════════════════════════╗
║              SISTEMA PRONTO PARA TESTES!                      ║
╚═══════════════════════════════════════════════════════════════╝

📝 Próximos Passos:
   1. Acesse: https://portal.sunyataconsulting.com
   2. Faça login com os usuários de teste via Google OAuth
   3. Complete o onboarding e teste os fluxos

📖 Guia de Testes:
   Ver: ONBOARDING_TEST_GUIDE.md
```

## ⚙️ Configurações Manuais Opcionais

### Desabilitar aprovação Jurídico (para testar acesso imediato)

```bash
ssh -p 65002 u202164171@82.25.72.226 "/usr/bin/mariadb u202164171_sunyata -p'MiGOq%tMrUP+9Qy@bxR' -e \"UPDATE settings SET setting_value = '0', updated_at = NOW() WHERE setting_key = 'juridico_requires_approval';\""
```

### Habilitar aprovação Jurídico (para testar tela de espera)

```bash
ssh -p 65002 u202164171@82.25.72.226 "/usr/bin/mariadb u202164171_sunyata -p'MiGOq%tMrUP+9Qy@bxR' -e \"UPDATE settings SET setting_value = '1', updated_at = NOW() WHERE setting_key = 'juridico_requires_approval';\""
```

## 🔍 Comandos Úteis de Verificação

### Ver todos os usuários

```bash
ssh -p 65002 u202164171@82.25.72.226 "/usr/bin/mariadb u202164171_sunyata -p'MiGOq%tMrUP+9Qy@bxR' -e 'SELECT id, email, name, role, selected_vertical, completed_onboarding FROM users ORDER BY id;'"
```

### Ver solicitações pendentes

```bash
ssh -p 65002 u202164171@82.25.72.226 "/usr/bin/mariadb u202164171_sunyata -p'MiGOq%tMrUP+9Qy@bxR' -e 'SELECT id, user_id, vertical, status, requested_at FROM vertical_access_requests WHERE status = \"pending\";'"
```

### Ver sessões ativas

```bash
ssh -p 65002 u202164171@82.25.72.226 "ls -lh /home/u202164171/domains/sunyataconsulting.com/public_html/plataforma-sunyata/var/sessions/"
```

## 🧪 Cenários de Teste

Após executar o script, você pode testar:

### Teste 1: Vertical Não-Jurídico
1. Login com qualquer usuário de teste
2. Escolher vertical (Docência, Pesquisa, etc.)
3. Preencher formulário
4. ✅ Deve ter acesso imediato

### Teste 2: Jurídico com Aprovação OFF
1. Desabilitar aprovação (comando acima)
2. Login com usuário de teste
3. Escolher "Jurídico"
4. Preencher formulário
5. ✅ Deve ter acesso imediato ao dashboard

### Teste 3: Jurídico com Aprovação ON
1. Habilitar aprovação (comando acima)
2. Login com usuário de teste
3. Escolher "Jurídico"
4. Preencher formulário
5. ✅ Deve ir para tela de espera
6. ✅ Tela atualiza a cada 30 segundos
7. Admin aprova no painel
8. ✅ Usuário é redirecionado automaticamente

## 🚨 Troubleshooting

### Script não encontra usuários

**Causa:** Usuários já foram removidos ou não existem
**Solução:** Verifique manualmente no banco com comando acima

### Erro de permissão

**Causa:** Script não tem permissão de execução
**Solução:** `chmod +x scripts/prepare-test-users.sh`

### Erro de conexão SSH

**Causa:** Credenciais SSH incorretas ou servidor inacessível
**Solução:** Teste conexão: `ssh -p 65002 u202164171@82.25.72.226 "echo OK"`

### Usuário não pode ser removido (admin)

**Causa:** Script protege admins automaticamente
**Solução:** Verificado. Apenas usuários com `role != 'admin'` são removidos

## 📝 Notas Importantes

1. **Backup**: O script não faz backup. Os dados são removidos permanentemente.
2. **Confirmação**: Sempre solicita confirmação antes de remover.
3. **Logs**: Toda ação é exibida em tempo real.
4. **Segurança**: Admins são protegidos automaticamente.
5. **Idempotência**: Pode ser executado múltiplas vezes sem problemas.

## 📚 Documentação Relacionada

- `ONBOARDING_TEST_GUIDE.md` - Guia completo de testes de onboarding
- `scripts/admin-cli/` - Ferramentas CLI de administração
- `docs/DEPLOYMENT.md` - Guia de deployment
