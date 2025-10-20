#!/bin/bash

##############################################################################
# Admin Menu - Interface interativa para administração do sistema
#
# Execute do WSL local, gerencia o servidor remoto
##############################################################################

# Configurações do servidor
SSH_HOST="u202164171@82.25.72.226"
SSH_PORT="65002"
DB_NAME="u202164171_sunyata"
DB_PASS="MiGOq%tMrUP+9Qy@bxR"
BASE_URL="https://portal.sunyataconsulting.com"

# Cores
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
MAGENTA='\033[0;35m'
CYAN='\033[0;36m'
WHITE='\033[1;37m'
GRAY='\033[0;90m'
NC='\033[0m' # No Color
BOLD='\033[1m'

# Função para executar SQL remoto
execute_sql() {
    local sql="$1"
    ssh -p $SSH_PORT $SSH_HOST "/usr/bin/mariadb $DB_NAME -p'$DB_PASS' -e \"$sql\"" 2>/dev/null
}

# Função para executar comando remoto
execute_remote() {
    ssh -p $SSH_PORT $SSH_HOST "$1" 2>/dev/null
}

# Limpar tela
clear_screen() {
    clear
}

# Header do sistema
show_header() {
    clear_screen
    echo -e "${BLUE}╔═══════════════════════════════════════════════════════════════════════╗${NC}"
    echo -e "${BLUE}║${WHITE}           🚀 PLATAFORMA SUNYATA - ADMIN MENU 🚀                      ${BLUE}║${NC}"
    echo -e "${BLUE}╚═══════════════════════════════════════════════════════════════════════╝${NC}"
    echo -e "${GRAY}  Servidor: $SSH_HOST:$SSH_PORT${NC}"
    echo -e "${GRAY}  URL: $BASE_URL${NC}"
    echo ""
}

# Pause para ler output
pause() {
    echo ""
    echo -e "${GRAY}Pressione ENTER para continuar...${NC}"
    read
}

# ============================================================================
# MENU 1: GERENCIAMENTO DE USUÁRIOS
# ============================================================================

menu_users() {
    while true; do
        show_header
        echo -e "${CYAN}👥 GERENCIAMENTO DE USUÁRIOS${NC}"
        echo -e "${BLUE}═══════════════════════════════════════════════════════════════════════${NC}"
        echo ""
        echo "  1) Listar todos os usuários"
        echo "  2) Buscar usuário por email"
        echo "  3) Ver usuários por vertical"
        echo "  4) Ver usuários recentes (últimos 7 dias)"
        echo "  5) Remover usuários de teste"
        echo "  6) Ver estatísticas de usuários"
        echo ""
        echo "  0) Voltar ao menu principal"
        echo ""
        echo -e "${GRAY}═══════════════════════════════════════════════════════════════════════${NC}"
        echo -n "Escolha uma opção: "
        read choice

        case $choice in
            1) users_list_all ;;
            2) users_search ;;
            3) users_by_vertical ;;
            4) users_recent ;;
            5) users_remove_test ;;
            6) users_stats ;;
            0) return ;;
            *) echo -e "${RED}Opção inválida!${NC}"; sleep 1 ;;
        esac
    done
}

users_list_all() {
    show_header
    echo -e "${CYAN}📋 TODOS OS USUÁRIOS${NC}"
    echo ""
    execute_sql "SELECT id, email, name, access_level, selected_vertical, completed_onboarding, DATE_FORMAT(last_login, '%d/%m/%Y %H:%i') as ultimo_login FROM users ORDER BY id;"
    pause
}

users_search() {
    show_header
    echo -e "${CYAN}🔍 BUSCAR USUÁRIO${NC}"
    echo ""
    echo -n "Digite o email (ou parte dele): "
    read email
    echo ""
    execute_sql "SELECT id, email, name, access_level, selected_vertical, completed_onboarding, DATE_FORMAT(created_at, '%d/%m/%Y') as cadastro FROM users WHERE email LIKE '%$email%';"
    pause
}

users_by_vertical() {
    show_header
    echo -e "${CYAN}📊 USUÁRIOS POR VERTICAL${NC}"
    echo ""
    execute_sql "SELECT selected_vertical as vertical, COUNT(*) as total FROM users WHERE selected_vertical IS NOT NULL GROUP BY selected_vertical ORDER BY total DESC;"
    pause
}

users_recent() {
    show_header
    echo -e "${CYAN}🆕 USUÁRIOS RECENTES (últimos 7 dias)${NC}"
    echo ""
    execute_sql "SELECT id, email, name, access_level, selected_vertical, DATE_FORMAT(created_at, '%d/%m/%Y %H:%i') as cadastro FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) ORDER BY created_at DESC;"
    pause
}

users_remove_test() {
    show_header
    echo -e "${YELLOW}🗑️  REMOVER USUÁRIOS DE TESTE${NC}"
    echo ""
    echo "Usuários que serão removidos:"
    echo "  • filipe.litaiff@gmail.com"
    echo "  • pmo@diagnext.com"
    echo "  • filipe.barbosa@coppead.ufrj.br"
    echo ""
    echo -e "${RED}ATENÇÃO: Esta ação é IRREVERSÍVEL!${NC}"
    echo ""
    echo -n "Deseja continuar? (digite 'SIM'): "
    read confirm

    if [ "$confirm" == "SIM" ]; then
        echo ""
        echo "Executando script de preparação..."
        ./scripts/prepare-test-users.sh -y
    else
        echo -e "${YELLOW}Operação cancelada.${NC}"
        sleep 1
    fi
    pause
}

users_stats() {
    show_header
    echo -e "${CYAN}📊 ESTATÍSTICAS DE USUÁRIOS${NC}"
    echo ""

    echo -e "${BOLD}Totais:${NC}"
    execute_sql "SELECT
        COUNT(*) as total,
        SUM(CASE WHEN access_level = 'admin' THEN 1 ELSE 0 END) as admins,
        SUM(CASE WHEN access_level = 'guest' THEN 1 ELSE 0 END) as guests,
        SUM(CASE WHEN completed_onboarding = 1 THEN 1 ELSE 0 END) as onboarding_completo,
        SUM(CASE WHEN completed_onboarding = 0 THEN 1 ELSE 0 END) as onboarding_pendente
    FROM users;"

    echo ""
    echo -e "${BOLD}Por Vertical:${NC}"
    execute_sql "SELECT
        COALESCE(selected_vertical, 'Sem vertical') as vertical,
        COUNT(*) as total
    FROM users
    GROUP BY selected_vertical
    ORDER BY total DESC;"

    echo ""
    echo -e "${BOLD}Últimos 7 dias:${NC}"
    execute_sql "SELECT
        DATE(created_at) as data,
        COUNT(*) as novos_usuarios
    FROM users
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
    GROUP BY DATE(created_at)
    ORDER BY data DESC;"

    pause
}

# ============================================================================
# MENU 2: SOLICITAÇÕES DE ACESSO
# ============================================================================

menu_requests() {
    while true; do
        show_header
        echo -e "${CYAN}📝 SOLICITAÇÕES DE ACESSO${NC}"
        echo -e "${BLUE}═══════════════════════════════════════════════════════════════════════${NC}"
        echo ""
        echo "  1) Listar solicitações pendentes"
        echo "  2) Listar todas as solicitações"
        echo "  3) Aprovar solicitação"
        echo "  4) Rejeitar solicitação"
        echo "  5) Ver detalhes de solicitação"
        echo ""
        echo "  0) Voltar ao menu principal"
        echo ""
        echo -e "${GRAY}═══════════════════════════════════════════════════════════════════════${NC}"
        echo -n "Escolha uma opção: "
        read choice

        case $choice in
            1) requests_pending ;;
            2) requests_all ;;
            3) requests_approve ;;
            4) requests_reject ;;
            5) requests_details ;;
            0) return ;;
            *) echo -e "${RED}Opção inválida!${NC}"; sleep 1 ;;
        esac
    done
}

requests_pending() {
    show_header
    echo -e "${CYAN}⏳ SOLICITAÇÕES PENDENTES${NC}"
    echo ""
    execute_sql "SELECT r.id, u.email, u.name, r.vertical, DATE_FORMAT(r.requested_at, '%d/%m/%Y %H:%i') as solicitado_em FROM vertical_access_requests r JOIN users u ON r.user_id = u.id WHERE r.status = 'pending' ORDER BY r.requested_at DESC;"
    pause
}

requests_all() {
    show_header
    echo -e "${CYAN}📋 TODAS AS SOLICITAÇÕES${NC}"
    echo ""
    execute_sql "SELECT r.id, u.email, r.vertical, r.status, DATE_FORMAT(r.requested_at, '%d/%m/%Y %H:%i') as data FROM vertical_access_requests r JOIN users u ON r.user_id = u.id ORDER BY r.requested_at DESC LIMIT 20;"
    pause
}

requests_approve() {
    show_header
    echo -e "${GREEN}✅ APROVAR SOLICITAÇÃO${NC}"
    echo ""
    echo "Solicitações pendentes:"
    execute_sql "SELECT r.id, u.email, u.name, r.vertical FROM vertical_access_requests r JOIN users u ON r.user_id = u.id WHERE r.status = 'pending';"
    echo ""
    echo -n "Digite o ID da solicitação para aprovar (0 para cancelar): "
    read req_id

    if [ "$req_id" == "0" ]; then
        return
    fi

    # Obter user_id e vertical
    USER_DATA=$(execute_sql "SELECT user_id, vertical FROM vertical_access_requests WHERE id = $req_id;" | tail -1)
    USER_ID=$(echo "$USER_DATA" | awk '{print $1}')
    VERTICAL=$(echo "$USER_DATA" | awk '{print $2}')

    if [ -z "$USER_ID" ]; then
        echo -e "${RED}Solicitação não encontrada!${NC}"
        sleep 2
        return
    fi

    echo ""
    echo "Aprovando solicitação $req_id..."

    # Atualizar solicitação
    execute_sql "UPDATE vertical_access_requests SET status = 'approved', processed_at = NOW(), processed_by = 1 WHERE id = $req_id;"

    # Dar acesso ao usuário
    execute_sql "UPDATE users SET selected_vertical = '$VERTICAL', completed_onboarding = 1 WHERE id = $USER_ID;"

    echo -e "${GREEN}✓ Solicitação aprovada com sucesso!${NC}"
    sleep 2
}

requests_reject() {
    show_header
    echo -e "${RED}❌ REJEITAR SOLICITAÇÃO${NC}"
    echo ""
    echo "Solicitações pendentes:"
    execute_sql "SELECT r.id, u.email, u.name, r.vertical FROM vertical_access_requests r JOIN users u ON r.user_id = u.id WHERE r.status = 'pending';"
    echo ""
    echo -n "Digite o ID da solicitação para rejeitar (0 para cancelar): "
    read req_id

    if [ "$req_id" == "0" ]; then
        return
    fi

    echo -n "Motivo da rejeição (opcional): "
    read notes

    execute_sql "UPDATE vertical_access_requests SET status = 'rejected', processed_at = NOW(), processed_by = 1, notes = '$notes' WHERE id = $req_id;"

    echo -e "${GREEN}✓ Solicitação rejeitada.${NC}"
    sleep 2
}

requests_details() {
    show_header
    echo -e "${CYAN}🔍 DETALHES DA SOLICITAÇÃO${NC}"
    echo ""
    echo -n "Digite o ID da solicitação: "
    read req_id
    echo ""

    execute_sql "SELECT r.*, u.email, u.name FROM vertical_access_requests r JOIN users u ON r.user_id = u.id WHERE r.id = $req_id\\G"
    pause
}

# ============================================================================
# MENU 3: CONFIGURAÇÕES DO SISTEMA
# ============================================================================

menu_settings() {
    while true; do
        show_header
        echo -e "${CYAN}⚙️  CONFIGURAÇÕES DO SISTEMA${NC}"
        echo -e "${BLUE}═══════════════════════════════════════════════════════════════════════${NC}"
        echo ""

        # Mostrar configuração atual
        JURIDICO_APPROVAL=$(execute_sql "SELECT setting_value FROM settings WHERE setting_key = 'juridico_requires_approval';" | tail -1)
        if [ "$JURIDICO_APPROVAL" == "1" ]; then
            echo -e "  ${YELLOW}📌 Aprovação Jurídico: HABILITADA${NC}"
        else
            echo -e "  ${GREEN}📌 Aprovação Jurídico: DESABILITADA${NC}"
        fi
        echo ""

        echo "  1) Alternar aprovação Jurídico (ON/OFF)"
        echo "  2) Ver todas as configurações"
        echo "  3) Editar configuração específica"
        echo ""
        echo "  0) Voltar ao menu principal"
        echo ""
        echo -e "${GRAY}═══════════════════════════════════════════════════════════════════════${NC}"
        echo -n "Escolha uma opção: "
        read choice

        case $choice in
            1) settings_toggle_juridico ;;
            2) settings_view_all ;;
            3) settings_edit ;;
            0) return ;;
            *) echo -e "${RED}Opção inválida!${NC}"; sleep 1 ;;
        esac
    done
}

settings_toggle_juridico() {
    CURRENT=$(execute_sql "SELECT setting_value FROM settings WHERE setting_key = 'juridico_requires_approval';" | tail -1)

    if [ "$CURRENT" == "1" ]; then
        NEW_VALUE="0"
        NEW_STATUS="DESABILITADA"
    else
        NEW_VALUE="1"
        NEW_STATUS="HABILITADA"
    fi

    execute_sql "UPDATE settings SET setting_value = '$NEW_VALUE', updated_at = NOW() WHERE setting_key = 'juridico_requires_approval';"

    echo ""
    echo -e "${GREEN}✓ Aprovação Jurídico agora está: $NEW_STATUS${NC}"
    sleep 2
}

settings_view_all() {
    show_header
    echo -e "${CYAN}⚙️  TODAS AS CONFIGURAÇÕES${NC}"
    echo ""
    execute_sql "SELECT setting_key, setting_value, DATE_FORMAT(updated_at, '%d/%m/%Y %H:%i') as atualizado_em FROM settings ORDER BY setting_key;"
    pause
}

settings_edit() {
    show_header
    echo -e "${CYAN}✏️  EDITAR CONFIGURAÇÃO${NC}"
    echo ""
    execute_sql "SELECT setting_key, setting_value FROM settings ORDER BY setting_key;"
    echo ""
    echo -n "Digite a chave da configuração: "
    read key
    echo -n "Digite o novo valor: "
    read value

    execute_sql "UPDATE settings SET setting_value = '$value', updated_at = NOW() WHERE setting_key = '$key';"

    echo ""
    echo -e "${GREEN}✓ Configuração atualizada!${NC}"
    sleep 2
}

# ============================================================================
# MENU 4: MONITORAMENTO E LOGS
# ============================================================================

menu_monitoring() {
    while true; do
        show_header
        echo -e "${CYAN}📊 MONITORAMENTO E LOGS${NC}"
        echo -e "${BLUE}═══════════════════════════════════════════════════════════════════════${NC}"
        echo ""
        echo "  1) Ver últimos logs de erro"
        echo "  2) Monitorar logs em tempo real"
        echo "  3) Ver estatísticas da API Claude"
        echo "  4) Ver custo mensal da API"
        echo "  5) Ver audit logs (últimos 50)"
        echo "  6) Ver sessões ativas"
        echo ""
        echo "  0) Voltar ao menu principal"
        echo ""
        echo -e "${GRAY}═══════════════════════════════════════════════════════════════════════${NC}"
        echo -n "Escolha uma opção: "
        read choice

        case $choice in
            1) monitoring_error_logs ;;
            2) monitoring_live_logs ;;
            3) monitoring_api_stats ;;
            4) monitoring_api_cost ;;
            5) monitoring_audit_logs ;;
            6) monitoring_sessions ;;
            0) return ;;
            *) echo -e "${RED}Opção inválida!${NC}"; sleep 1 ;;
        esac
    done
}

monitoring_error_logs() {
    show_header
    echo -e "${CYAN}📋 ÚLTIMOS LOGS DE ERRO${NC}"
    echo ""
    execute_remote "tail -50 /home/u202164171/domains/sunyataconsulting.com/logs/error.log"
    pause
}

monitoring_live_logs() {
    show_header
    echo -e "${CYAN}📡 MONITORAMENTO EM TEMPO REAL${NC}"
    echo ""
    echo -e "${YELLOW}Pressione Ctrl+C para sair${NC}"
    echo ""
    ssh -p $SSH_PORT $SSH_HOST "tail -f /home/u202164171/domains/sunyataconsulting.com/logs/error.log"
}

monitoring_api_stats() {
    show_header
    echo -e "${CYAN}📊 ESTATÍSTICAS DA API CLAUDE${NC}"
    echo ""

    echo -e "${BOLD}Mês Atual:${NC}"
    execute_sql "SELECT
        COUNT(*) as total_prompts,
        SUM(tokens_total) as total_tokens,
        ROUND(SUM(cost_usd), 4) as custo_total_usd,
        AVG(tokens_total) as media_tokens
    FROM prompt_history
    WHERE status = 'success'
    AND MONTH(created_at) = MONTH(NOW());"

    echo ""
    echo -e "${BOLD}Por Vertical (Mês Atual):${NC}"
    execute_sql "SELECT
        vertical,
        COUNT(*) as prompts,
        SUM(tokens_total) as tokens,
        ROUND(SUM(cost_usd), 4) as custo_usd
    FROM prompt_history
    WHERE status = 'success'
    AND MONTH(created_at) = MONTH(NOW())
    GROUP BY vertical;"

    echo ""
    echo -e "${BOLD}Últimos 7 dias:${NC}"
    execute_sql "SELECT
        DATE(created_at) as data,
        COUNT(*) as prompts,
        ROUND(SUM(cost_usd), 4) as custo_usd
    FROM prompt_history
    WHERE status = 'success'
    AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
    GROUP BY DATE(created_at)
    ORDER BY data DESC;"

    pause
}

monitoring_api_cost() {
    show_header
    echo -e "${CYAN}💰 CUSTO MENSAL DA API CLAUDE${NC}"
    echo ""

    COST=$(execute_sql "SELECT COALESCE(SUM(cost_usd), 0) as total FROM prompt_history WHERE status = 'success' AND MONTH(created_at) = MONTH(NOW());" | tail -1)
    LIMIT="10.00"

    echo -e "${BOLD}Custo do mês atual:${NC} USD $COST"
    echo -e "${BOLD}Limite mensal:${NC} USD $LIMIT"

    PERCENT=$(echo "scale=2; ($COST / $LIMIT) * 100" | bc)
    echo -e "${BOLD}Percentual usado:${NC} $PERCENT%"

    if (( $(echo "$PERCENT > 80" | bc -l) )); then
        echo -e "${RED}⚠️  ATENÇÃO: Limite quase atingido!${NC}"
    elif (( $(echo "$PERCENT > 50" | bc -l) )); then
        echo -e "${YELLOW}⚠️  Aviso: Mais de 50% do limite usado${NC}"
    else
        echo -e "${GREEN}✓ Uso normal${NC}"
    fi

    pause
}

monitoring_audit_logs() {
    show_header
    echo -e "${CYAN}📜 AUDIT LOGS (últimos 50)${NC}"
    echo ""
    execute_sql "SELECT
        a.id,
        u.email,
        a.action,
        a.entity_type,
        DATE_FORMAT(a.created_at, '%d/%m/%Y %H:%i') as data
    FROM audit_logs a
    LEFT JOIN users u ON a.user_id = u.id
    ORDER BY a.created_at DESC
    LIMIT 50;"
    pause
}

monitoring_sessions() {
    show_header
    echo -e "${CYAN}🔐 SESSÕES ATIVAS${NC}"
    echo ""
    SESSION_COUNT=$(execute_remote "find /home/u202164171/domains/sunyataconsulting.com/public_html/plataforma-sunyata/var/sessions -type f -name 'sess_*' 2>/dev/null | wc -l")

    echo "Total de sessões ativas: $SESSION_COUNT"
    echo ""

    if [ "$SESSION_COUNT" -gt 0 ]; then
        echo "Arquivos de sessão:"
        execute_remote "ls -lh /home/u202164171/domains/sunyataconsulting.com/public_html/plataforma-sunyata/var/sessions/ | grep sess_"
    else
        echo "Nenhuma sessão ativa."
    fi

    pause
}

# ============================================================================
# MENU 5: MANUTENÇÃO
# ============================================================================

menu_maintenance() {
    while true; do
        show_header
        echo -e "${CYAN}🔧 MANUTENÇÃO DO SISTEMA${NC}"
        echo -e "${BLUE}═══════════════════════════════════════════════════════════════════════${NC}"
        echo ""
        echo "  1) Limpar cache"
        echo "  2) Limpar sessões antigas"
        echo "  3) Otimizar banco de dados"
        echo "  4) Backup do banco de dados"
        echo "  5) Ver espaço em disco"
        echo "  6) Reiniciar PHP-FPM"
        echo ""
        echo "  0) Voltar ao menu principal"
        echo ""
        echo -e "${GRAY}═══════════════════════════════════════════════════════════════════════${NC}"
        echo -n "Escolha uma opção: "
        read choice

        case $choice in
            1) maintenance_clear_cache ;;
            2) maintenance_clear_sessions ;;
            3) maintenance_optimize_db ;;
            4) maintenance_backup_db ;;
            5) maintenance_disk_space ;;
            6) maintenance_restart_php ;;
            0) return ;;
            *) echo -e "${RED}Opção inválida!${NC}"; sleep 1 ;;
        esac
    done
}

maintenance_clear_cache() {
    show_header
    echo -e "${CYAN}🧹 LIMPAR CACHE${NC}"
    echo ""
    execute_remote "rm -rf /home/u202164171/domains/sunyataconsulting.com/public_html/plataforma-sunyata/var/cache/*"
    echo -e "${GREEN}✓ Cache limpo com sucesso!${NC}"
    sleep 2
}

maintenance_clear_sessions() {
    show_header
    echo -e "${CYAN}🧹 LIMPAR SESSÕES ANTIGAS${NC}"
    echo ""
    COUNT=$(execute_remote "find /home/u202164171/domains/sunyataconsulting.com/public_html/plataforma-sunyata/var/sessions -type f -mtime +7 | wc -l")
    echo "Sessões com mais de 7 dias: $COUNT"
    echo ""
    echo -n "Deseja remover? (s/n): "
    read confirm

    if [ "$confirm" == "s" ]; then
        execute_remote "find /home/u202164171/domains/sunyataconsulting.com/public_html/plataforma-sunyata/var/sessions -type f -mtime +7 -delete"
        echo -e "${GREEN}✓ Sessões antigas removidas!${NC}"
    fi
    sleep 2
}

maintenance_optimize_db() {
    show_header
    echo -e "${CYAN}⚡ OTIMIZAR BANCO DE DADOS${NC}"
    echo ""
    echo "Otimizando tabelas..."
    execute_sql "OPTIMIZE TABLE users, vertical_access_requests, prompt_history, audit_logs, settings;"
    echo ""
    echo -e "${GREEN}✓ Banco de dados otimizado!${NC}"
    sleep 2
}

maintenance_backup_db() {
    show_header
    echo -e "${CYAN}💾 BACKUP DO BANCO DE DADOS${NC}"
    echo ""
    BACKUP_FILE="backup_$(date +%Y%m%d_%H%M%S).sql"
    echo "Criando backup: $BACKUP_FILE"
    ssh -p $SSH_PORT $SSH_HOST "/usr/bin/mysqldump -u u202164171_sunyata -p'$DB_PASS' $DB_NAME > /home/u202164171/backups/$BACKUP_FILE"
    echo ""
    echo -e "${GREEN}✓ Backup criado com sucesso!${NC}"
    echo "Arquivo: /home/u202164171/backups/$BACKUP_FILE"
    sleep 2
}

maintenance_disk_space() {
    show_header
    echo -e "${CYAN}💾 ESPAÇO EM DISCO${NC}"
    echo ""
    execute_remote "df -h | grep -E '(Filesystem|/home)'"
    echo ""
    echo "Diretório da aplicação:"
    execute_remote "du -sh /home/u202164171/domains/sunyataconsulting.com/public_html/plataforma-sunyata"
    pause
}

maintenance_restart_php() {
    show_header
    echo -e "${CYAN}🔄 REINICIAR PHP-FPM${NC}"
    echo ""
    echo -e "${YELLOW}Nota: Pode requerer permissões especiais no servidor${NC}"
    echo ""
    echo -n "Deseja tentar reiniciar? (s/n): "
    read confirm

    if [ "$confirm" == "s" ]; then
        execute_remote "killall -USR2 php-fpm 2>/dev/null"
        echo ""
        echo -e "${GREEN}✓ Sinal enviado ao PHP-FPM${NC}"
    fi
    sleep 2
}

# ============================================================================
# PREPARAÇÃO PARA TESTES
# ============================================================================

prepare_for_tests() {
    show_header
    echo -e "${YELLOW}🧪 PREPARAR SISTEMA PARA TESTES${NC}"
    echo -e "${BLUE}═══════════════════════════════════════════════════════════════════════${NC}"
    echo ""
    echo "Esta opção irá executar TODAS as ações necessárias para preparar o sistema"
    echo "para testes do zero:"
    echo ""
    echo -e "  ${BOLD}✓${NC} Remover todos os usuários de teste e seus dados"
    echo "  ${BOLD}✓${NC} Remover consents LGPD dos usuários de teste"
    echo "  ${BOLD}✓${NC} Remover histórico de prompts da API"
    echo "  ${BOLD}✓${NC} Remover solicitações de acesso pendentes"
    echo "  ${BOLD}✓${NC} Remover perfis de usuário"
    echo "  ${BOLD}✓${NC} Remover logs de auditoria"
    echo "  ${BOLD}✓${NC} Limpar TODAS as sessões ativas (todos serão deslogados)"
    echo "  ${BOLD}✓${NC} Limpar cache do sistema"
    echo ""
    echo -e "${CYAN}Usuários que serão removidos:${NC}"
    echo "  • filipe.litaiff@gmail.com"
    echo "  • pmo@diagnext.com"
    echo "  • filipe.barbosa@coppead.ufrj.br"
    echo "  • claudesunyata@gmail.com"
    echo ""
    echo -e "${GREEN}Usuários PROTEGIDOS (não serão tocados):${NC}"
    echo "  • flitaiff@gmail.com (admin)"
    echo "  • filipe.litaiff@ifrj.edu.br (admin)"
    echo ""
    echo -e "${RED}⚠️  ATENÇÃO: Esta ação é IRREVERSÍVEL!${NC}"
    echo -e "${RED}   Todos os dados dos usuários de teste serão PERMANENTEMENTE removidos.${NC}"
    echo -e "${RED}   Todas as sessões ativas serão encerradas (todos os usuários serão deslogados).${NC}"
    echo ""
    echo -n "Deseja continuar? (digite 'SIM' para confirmar): "
    read confirm

    if [ "$confirm" != "SIM" ]; then
        echo -e "${YELLOW}Operação cancelada.${NC}"
        sleep 2
        return
    fi

    echo ""
    echo -e "${GREEN}✓ Confirmação recebida. Iniciando preparação para testes...${NC}"
    echo ""
    echo -e "${BLUE}═══════════════════════════════════════════════════════════════════════${NC}"
    echo ""

    # Executar script de preparação
    if [ -f "./scripts/prepare-test-users.sh" ]; then
        ./scripts/prepare-test-users.sh -y
    else
        echo -e "${RED}✗ Erro: Script prepare-test-users.sh não encontrado!${NC}"
        echo "Certifique-se de executar este menu do diretório raiz do projeto."
        sleep 3
        return
    fi

    echo ""
    echo -e "${GREEN}═══════════════════════════════════════════════════════════════════════${NC}"
    echo -e "${GREEN}✓ SISTEMA PRONTO PARA TESTES!${NC}"
    echo -e "${GREEN}═══════════════════════════════════════════════════════════════════════${NC}"
    echo ""
    echo -e "${CYAN}📝 Próximos Passos:${NC}"
    echo "  1. Acesse: $BASE_URL"
    echo "  2. Faça login com Google OAuth usando os usuários de teste"
    echo "  3. Complete o onboarding e teste os fluxos"
    echo ""
    echo -e "${BLUE}💡 Dica:${NC} Use a opção 3 (Configurações) para habilitar/desabilitar"
    echo "   a aprovação da vertical Jurídico durante seus testes."
    echo ""

    pause
}

# ============================================================================
# MENU PRINCIPAL
# ============================================================================

main_menu() {
    while true; do
        show_header

        # Mostrar dashboard rápido
        echo -e "${CYAN}📊 DASHBOARD RÁPIDO${NC}"
        echo -e "${GRAY}───────────────────────────────────────────────────────────────────────${NC}"

        # Stats rápidas
        TOTAL_USERS=$(execute_sql "SELECT COUNT(*) FROM users;" | tail -1)
        PENDING_REQUESTS=$(execute_sql "SELECT COUNT(*) FROM vertical_access_requests WHERE status = 'pending';" | tail -1)
        MONTH_COST=$(execute_sql "SELECT COALESCE(ROUND(SUM(cost_usd), 4), 0) FROM prompt_history WHERE MONTH(created_at) = MONTH(NOW());" | tail -1)

        echo -e "  👥 Usuários: ${WHITE}$TOTAL_USERS${NC}  |  📝 Solicitações pendentes: ${YELLOW}$PENDING_REQUESTS${NC}  |  💰 Custo mês: ${GREEN}USD $MONTH_COST${NC}"
        echo ""
        echo -e "${BLUE}═══════════════════════════════════════════════════════════════════════${NC}"
        echo ""
        echo -e "  ${BOLD}1)${NC} 👥 Gerenciamento de Usuários"
        echo -e "  ${BOLD}2)${NC} 📝 Solicitações de Acesso"
        echo -e "  ${BOLD}3)${NC} ⚙️  Configurações do Sistema"
        echo -e "  ${BOLD}4)${NC} 📊 Monitoramento e Logs"
        echo -e "  ${BOLD}5)${NC} 🔧 Manutenção do Sistema"
        echo ""
        echo -e "  ${BOLD}8)${NC} 🧪 Preparar sistema para testes"
        echo -e "  ${BOLD}9)${NC} 🌐 Abrir portal no navegador"
        echo -e "  ${BOLD}0)${NC} 🚪 Sair"
        echo ""
        echo -e "${GRAY}═══════════════════════════════════════════════════════════════════════${NC}"
        echo -n "Escolha uma opção: "
        read choice

        case $choice in
            1) menu_users ;;
            2) menu_requests ;;
            3) menu_settings ;;
            4) menu_monitoring ;;
            5) menu_maintenance ;;
            8) prepare_for_tests ;;
            9) xdg-open "$BASE_URL" 2>/dev/null || echo "Execute: $BASE_URL"; sleep 2 ;;
            0)
                clear_screen
                echo -e "${GREEN}Até logo! 👋${NC}"
                echo ""
                exit 0
                ;;
            *) echo -e "${RED}Opção inválida!${NC}"; sleep 1 ;;
        esac
    done
}

# ============================================================================
# INÍCIO DO PROGRAMA
# ============================================================================

# Verificar conexão SSH
echo "Verificando conexão com o servidor..."
if ! ssh -p $SSH_PORT -o ConnectTimeout=5 $SSH_HOST "echo OK" &>/dev/null; then
    echo -e "${RED}Erro: Não foi possível conectar ao servidor!${NC}"
    echo "Servidor: $SSH_HOST:$SSH_PORT"
    exit 1
fi

# Iniciar menu principal
main_menu
