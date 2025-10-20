# 🚀 Admin Menu - Interface Interativa de Administração

## 📋 Descrição

Interface TUI (Text User Interface) completa para administração da Plataforma Sunyata. Execute do seu terminal local (WSL) e gerencie o servidor remoto de forma interativa e visual.

## ✨ Funcionalidades

### 1. 👥 Gerenciamento de Usuários
- Listar todos os usuários
- Buscar usuário por email
- Ver usuários por vertical
- Ver usuários recentes (últimos 7 dias)
- Remover usuários de teste
- Ver estatísticas completas de usuários

### 2. 📝 Solicitações de Acesso
- Listar solicitações pendentes
- Listar todas as solicitações
- **Aprovar solicitação** (atualiza automaticamente o usuário)
- **Rejeitar solicitação** (com motivo opcional)
- Ver detalhes completos de qualquer solicitação

### 3. ⚙️ Configurações do Sistema
- **Alternar aprovação Jurídico** (ON/OFF) com um clique
- Ver todas as configurações
- Editar qualquer configuração

### 4. 📊 Monitoramento e Logs
- Ver últimos logs de erro
- **Monitorar logs em tempo real** (tail -f)
- Ver estatísticas da API Claude
- Ver custo mensal com alertas
- Ver audit logs (últimos 50)
- Ver sessões ativas

### 5. 🔧 Manutenção do Sistema
- Limpar cache
- Limpar sessões antigas (> 7 dias)
- Otimizar banco de dados
- **Backup do banco de dados** (com timestamp)
- Ver espaço em disco
- Reiniciar PHP-FPM

## 🚀 Como Usar

### Instalação e Execução

```bash
# Navegar para o projeto
cd /home/iflitaiff/projetos/plataforma-sunyata

# Executar o menu
./scripts/admin-menu.sh
```

### Criar Alias (Opcional mas Recomendado)

Para facilitar o acesso, adicione ao seu `~/.bashrc`:

```bash
alias admin-sunyata='cd /home/iflitaiff/projetos/plataforma-sunyata && ./scripts/admin-menu.sh'
```

Depois execute:
```bash
source ~/.bashrc
```

Agora você pode executar de qualquer lugar:
```bash
admin-sunyata
```

## 📸 Capturas de Tela (Conceitual)

### Menu Principal
```
╔═══════════════════════════════════════════════════════════════════════╗
║           🚀 PLATAFORMA SUNYATA - ADMIN MENU 🚀                      ║
╚═══════════════════════════════════════════════════════════════════════╝
  Servidor: u202164171@82.25.72.226:65002
  URL: https://portal.sunyataconsulting.com

📊 DASHBOARD RÁPIDO
───────────────────────────────────────────────────────────────────────
  👥 Usuários: 18  |  📝 Solicitações pendentes: 2  |  💰 Custo mês: USD 0.0030

═══════════════════════════════════════════════════════════════════════

  1) 👥 Gerenciamento de Usuários
  2) 📝 Solicitações de Acesso
  3) ⚙️  Configurações do Sistema
  4) 📊 Monitoramento e Logs
  5) 🔧 Manutenção do Sistema

  9) 🌐 Abrir portal no navegador
  0) 🚪 Sair

═══════════════════════════════════════════════════════════════════════
Escolha uma opção:
```

### Submenu - Solicitações de Acesso
```
╔═══════════════════════════════════════════════════════════════════════╗
║           🚀 PLATAFORMA SUNYATA - ADMIN MENU 🚀                      ║
╚═══════════════════════════════════════════════════════════════════════╝
  Servidor: u202164171@82.25.72.226:65002
  URL: https://portal.sunyataconsulting.com

📝 SOLICITAÇÕES DE ACESSO
═══════════════════════════════════════════════════════════════════════

  1) Listar solicitações pendentes
  2) Listar todas as solicitações
  3) Aprovar solicitação
  4) Rejeitar solicitação
  5) Ver detalhes de solicitação

  0) Voltar ao menu principal

═══════════════════════════════════════════════════════════════════════
Escolha uma opção:
```

## 🎯 Casos de Uso Comuns

### Aprovar Acesso Jurídico

1. Executar menu: `./scripts/admin-menu.sh`
2. Escolher opção `2` (Solicitações de Acesso)
3. Escolher opção `1` (Listar pendentes) - ver qual é o ID
4. Escolher opção `3` (Aprovar)
5. Digitar o ID da solicitação
6. ✅ Pronto! O usuário será atualizado automaticamente

### Alternar Aprovação Jurídico (ON/OFF)

1. Executar menu
2. Escolher opção `3` (Configurações)
3. Escolher opção `1` (Alternar aprovação)
4. ✅ Feito! A configuração muda instantaneamente

### Monitorar Sistema em Tempo Real

1. Executar menu
2. Escolher opção `4` (Monitoramento)
3. Escolher opção `2` (Logs em tempo real)
4. Ver logs atualizando automaticamente
5. Pressionar `Ctrl+C` para sair

### Remover Usuários de Teste

1. Executar menu
2. Escolher opção `1` (Usuários)
3. Escolher opção `5` (Remover teste)
4. Confirmar digitando `SIM`
5. ✅ Todos os usuários de teste são removidos

### Ver Custo da API

1. Executar menu
2. Escolher opção `4` (Monitoramento)
3. Escolher opção `4` (Custo mensal)
4. Ver custo, limite e percentual usado

### Fazer Backup do Banco

1. Executar menu
2. Escolher opção `5` (Manutenção)
3. Escolher opção `4` (Backup)
4. ✅ Backup criado em `/home/u202164171/backups/`

## 🔧 Requisitos

- **Bash** (já disponível no Ubuntu/WSL)
- **SSH configurado** (conexão ao servidor)
- **bc** (para cálculos matemáticos) - instalar se necessário:
  ```bash
  sudo apt-get install bc
  ```

## 🎨 Cores e Formatação

O menu usa cores ANSI para melhor visualização:
- 🔵 **Azul**: Headers e separadores
- 🟢 **Verde**: Sucesso e confirmações
- 🟡 **Amarelo**: Avisos e informações importantes
- 🔴 **Vermelho**: Erros e ações críticas
- ⚪ **Branco/Bold**: Destaques e títulos
- ⚫ **Cinza**: Informações secundárias

## ⌨️ Navegação

- Digite o **número** da opção desejada
- Pressione **ENTER** para confirmar
- Digite **0** para voltar/sair
- Pressione **Ctrl+C** para sair imediatamente (em logs ao vivo)

## 🔐 Segurança

- ✅ Credenciais SSH e banco armazenadas no script (arquivo local)
- ✅ Não expõe credenciais ao executar comandos
- ✅ Confirmação obrigatória para ações destrutivas
- ✅ Proteção automática de admins ao remover usuários

## 📊 Dashboard Rápido

O menu principal sempre mostra:
- **Total de usuários** no sistema
- **Solicitações pendentes** de aprovação
- **Custo mensal** da API Claude

## 🚨 Troubleshooting

### Erro: "Não foi possível conectar ao servidor"

**Causa:** SSH não configurado ou servidor inacessível

**Solução:**
```bash
# Testar conexão
ssh -p 65002 u202164171@82.25.72.226 "echo OK"

# Se pedir senha, configure chave SSH
ssh-copy-id -p 65002 u202164171@82.25.72.226
```

### Erro: "bc: command not found"

**Causa:** Utilitário `bc` não instalado

**Solução:**
```bash
sudo apt-get update
sudo apt-get install bc
```

### Caracteres estranhos no terminal

**Causa:** Terminal não suporta cores ANSI

**Solução:** Use um terminal moderno (Windows Terminal, iTerm2, etc.)

### Menu não aparece corretamente

**Causa:** Tamanho do terminal muito pequeno

**Solução:** Redimensione o terminal para pelo menos 80x24

## 🆚 Comparação: Menu vs Scripts Individuais

| Tarefa | Scripts Individuais | Admin Menu |
|--------|---------------------|------------|
| Listar usuários | `ssh + comando SQL` | 2 cliques |
| Aprovar solicitação | `ssh + 2 comandos SQL` | 3 cliques |
| Alternar configuração | `ssh + comando SQL` | 2 cliques |
| Ver logs | `ssh + tail` | 2 cliques |
| Backup | `ssh + mysqldump` | 3 cliques |
| Remover usuários teste | `./script.sh` + confirmar | 3 cliques |

**Resultado:** O Admin Menu reduz tarefas complexas de múltiplos comandos para poucos cliques! 🚀

## 💡 Dicas e Truques

### Uso Rápido com Alias

Depois de configurar o alias, você pode:

```bash
# De qualquer lugar
admin-sunyata

# Ou criar aliases específicos
alias admin-users='admin-sunyata' # abre direto no menu de usuários
```

### Atalhos de Teclado

Embora o menu use números, você pode criar funções no bash:

```bash
# Adicionar ao ~/.bashrc
admin-approve() {
    echo "2\n1" | admin-sunyata  # Menu 2, opção 1
}

admin-toggle() {
    echo "3\n1" | admin-sunyata  # Menu 3, opção 1
}
```

### Monitoramento Contínuo

Deixe uma janela aberta com logs em tempo real enquanto testa:

```bash
# Terminal 1: Testes
# Abrir navegador e fazer login

# Terminal 2: Monitoramento
admin-sunyata
# Escolher 4 (Monitoramento)
# Escolher 2 (Logs em tempo real)
```

## 📚 Documentação Relacionada

- `scripts/prepare-test-users.sh` - Script de preparação de testes (acessível pelo menu)
- `scripts/admin-cli/` - Scripts CLI individuais (substituídos pelo menu)
- `ONBOARDING_TEST_GUIDE.md` - Guia de testes de onboarding
- `TEST_READY_STATUS.md` - Status do sistema para testes

## 🔄 Atualizações Futuras

Possíveis melhorias planejadas:

- [ ] Exportar relatórios em CSV
- [ ] Gráficos ASCII de uso da API
- [ ] Notificações push para solicitações pendentes
- [ ] Suporte a múltiplos ambientes (dev/staging/prod)
- [ ] Modo batch para automação
- [ ] Histórico de comandos executados

## 🤝 Contribuindo

Se quiser adicionar funcionalidades:

1. Editar `scripts/admin-menu.sh`
2. Adicionar nova função seguindo o padrão
3. Adicionar item no menu apropriado
4. Testar
5. Documentar neste README

## 📞 Suporte

Se encontrar problemas:

1. Verificar conexão SSH
2. Verificar permissões do script (chmod +x)
3. Verificar logs de erro do servidor
4. Consultar seção Troubleshooting acima

---

**🎉 Aproveite a administração simplificada! 🚀**

*Interface criada para facilitar a vida do administrador do sistema.*
