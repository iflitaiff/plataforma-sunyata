# ADR-002: Sistema de Configurações e Gerenciamento Avançado de Usuários

**Data**: 2025-10-14
**Status**: Proposto
**Decisores**: Arquiteto + Product Owner

## Contexto e Problema

### Necessidades Identificadas
1. **Testes de Aprovação Jurídico**: Admin precisa desabilitar temporariamente aprovação obrigatória
2. **Gerenciamento de Usuários**: Admin precisa deletar usuários de teste durante desenvolvimento
3. **Configuração Hardcoded**: Atualmente `'requer_aprovacao' => true` está hardcoded em arrays PHP

### Problemas Atuais
- **Baixa Flexibilidade**: Mudanças requerem deploy de código
- **Dados de Teste Acumulam**: Sem forma fácil de limpar usuários
- **Risco em Produção**: Deletar usuários é operação SQL manual e perigosa

## Decisão

### Componentes a Serem Criados

#### 1. Settings Manager (Nova Camada de Abstração)
```
src/Core/Settings.php
├── Singleton pattern (como Database)
├── Cache em memória (evita queries repetidas)
├── Métodos: get(), set(), toggle()
└── Validação de tipos
```

**Justificativa Arquitetural**:
- Centraliza configurações dinâmicas
- Separa configuração de lógica de negócio
- Permite auditoria de mudanças
- Facilita testes (mock de configurações)

#### 2. User Deletion Service (Nova Feature Admin)
```
src/Admin/UserDeletionService.php
├── Validações de segurança
├── Transação atômica (all-or-nothing)
├── Cascade deletion (LGPD compliant)
├── Audit logging
└── Proteções: não deletar admins/self
```

**Justificativa Arquitetural**:
- Encapsula lógica complexa de deleção
- Garante integridade referencial
- LGPD: anonimiza audit_logs
- Evita SQL injection (prepared statements)

### Mudanças no Banco de Dados

```sql
-- Nova tabela: settings
CREATE TABLE settings (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT NOT NULL,
    data_type ENUM('string', 'integer', 'boolean', 'json') DEFAULT 'string',
    description VARCHAR(255),
    is_public BOOLEAN DEFAULT FALSE,  -- se pode ser lido sem autenticação
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    updated_by INT UNSIGNED NULL,
    FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL
);

-- Índices para performance
CREATE INDEX idx_setting_key ON settings(setting_key);
CREATE INDEX idx_is_public ON settings(is_public);
```

### Mudanças no Código Existente

#### Arquivos que SERÃO modificados:
1. `public/onboarding-step2.php`
   - LER configuração de settings ao invés de hardcode
   - Fallback para `true` se setting não existir (fail-safe)

2. `public/admin/users.php`
   - Adicionar coluna "Ações" com botão deletar
   - Modal de confirmação dupla
   - POST handler para deleção

3. `public/admin/index.php` (Dashboard)
   - Nova seção "Configurações Rápidas"
   - Toggle para Jurídico approval
   - Feedback visual de estado atual

#### Arquivos que NÃO serão modificados:
- `src/Core/Database.php` - já está perfeito
- `src/Core/User.php` - não precisa saber de settings
- `src/Auth/GoogleAuth.php` - autenticação separada de config
- Ferramentas HTML - não são afetadas

## Consequências

### Positivas ✅
1. **Flexibilidade**: Admin muda configurações sem deploy
2. **Segurança**: Deleção controlada, não SQL direto
3. **Auditoria**: Todas mudanças rastreadas
4. **Testabilidade**: Fácil mudar configs em ambiente de teste
5. **Escalabilidade**: Adicionar novas settings é trivial
6. **LGPD**: Deleção completa e compliant

### Negativas ⚠️
1. **Complexidade**: +2 classes, +1 tabela
2. **Performance**: +1 query por request (mitigado por cache)
3. **Migração**: Dados existentes precisam migrar

### Riscos e Mitigações

| Risco | Probabilidade | Impacto | Mitigação |
|-------|---------------|---------|-----------|
| Admin deleta usuário importante | Média | Alto | Confirmação dupla + bloqueio de admins |
| Setting incorreto quebra site | Baixa | Alto | Valores default + validação de tipo |
| Cache de settings desatualizado | Baixa | Médio | Cache expira a cada request |
| Performance degradada | Baixa | Baixo | Índices + singleton + cache |

## Alternativas Consideradas

### Alternativa 1: Manter Hardcoded
**Pros**: Simples, sem mudanças
**Cons**: Inflexível, requer deploy para cada mudança
**Decisão**: ❌ Rejeitada - não atende necessidade de testes

### Alternativa 2: Arquivo de Config (config/settings.php)
**Pros**: Simples, sem banco
**Cons**: Requer acesso SSH, sem auditoria, sem UI
**Decisão**: ❌ Rejeitada - admin não tem acesso SSH

### Alternativa 3: Soft Delete (flag deleted)
**Pros**: Reversível
**Cons**: Complica queries, não é LGPD compliant
**Decisão**: ❌ Rejeitada - deve ser hard delete por LGPD

### Alternativa 4: Settings no Redis/Cache
**Pros**: Performance excelente
**Cons**: Hostinger pode não ter Redis, complexidade
**Decisão**: ❌ Rejeitada - over-engineering para MVP

## Plano de Implementação

### Fase 1: Infraestrutura (30min)
1. Criar migration `002_admin_improvements.sql`
2. Criar `src/Core/Settings.php`
3. Criar `src/Admin/UserDeletionService.php`
4. Testes unitários básicos (localmente)

### Fase 2: UI Admin (45min)
1. Adicionar toggle em `admin/index.php`
2. Adicionar botão delete em `admin/users.php`
3. Criar modal de confirmação
4. AJAX handlers para feedback instantâneo

### Fase 3: Integração (30min)
1. Atualizar `onboarding-step2.php` para ler settings
2. Adicionar audit logging
3. Testes end-to-end

### Fase 4: Deploy (15min)
1. Backup de banco
2. Executar migration
3. Deploy código
4. Smoke tests em produção
5. Rollback plan pronto

**Total estimado**: 2h

## Checklist de Validação

Antes de considerar completo:
- [ ] Migration executada com sucesso
- [ ] Settings.php retorna valores corretos
- [ ] Toggle Jurídico funciona (on/off)
- [ ] Deletar usuário remove todos registros relacionados
- [ ] Não é possível deletar admin
- [ ] Não é possível deletar self
- [ ] Confirmação dupla funciona
- [ ] Audit logs registram todas ações
- [ ] Performance não degradou (< 50ms por request)
- [ ] Mobile responsivo (toggle e delete funcionam)
- [ ] Rollback testado

## Compatibilidade

### Backwards Compatibility
- ✅ Código antigo continua funcionando
- ✅ Se settings não existir, usa default seguro (true)
- ✅ Sem breaking changes em APIs

### Forward Compatibility
- ✅ Adicionar novos settings é trivial
- ✅ Preparado para multi-tenancy futuro
- ✅ Suporta diferentes tipos de dados

## Documentação Necessária

1. ✅ Este ADR
2. [ ] README: Seção "Configurações Admin"
3. [ ] CHANGELOG: Adicionar v1.0.2-admin
4. [ ] Inline docs em Settings.php
5. [ ] Comentários em migration

## Aprovação

**Proposto por**: Claude (Arquiteto)
**Revisado por**: [Aguardando aprovação do PO]
**Aprovado por**: [ ]
**Data de Aprovação**: [ ]

---

## Próximos Passos

Se aprovado:
1. Implementar Fase 1
2. Code review interno
3. Testes locais
4. Deploy em staging (se existir)
5. Deploy em produção
6. Monitoramento pós-deploy (24h)
