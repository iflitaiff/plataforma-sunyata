#!/bin/bash

##############################################################################
# Script de Preparação para Testes - Plataforma Sunyata
#
# Remove completamente os usuários de teste especificados e seus rastros
# para permitir testes do zero.
#
# USUÁRIOS PROTEGIDOS (NÃO SERÃO TOCADOS):
#   - flitaiff@gmail.com (admin)
#   - filipe.litaiff@ifrj.edu.br (admin)
#
# USUÁRIOS DE TESTE A REMOVER:
#   - filipe.litaiff@gmail.com
#   - pmo@diagnext.com
#   - filipe.barbosa@coppead.ufrj.br
#
##############################################################################

# Cores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configurações do servidor
SSH_HOST="u202164171@82.25.72.226"
SSH_PORT="65002"
DB_NAME="u202164171_sunyata"
DB_PASS="MiGOq%tMrUP+9Qy@bxR"

# Usuários de teste a remover
TEST_USERS=(
    "filipe.litaiff@gmail.com"
    "pmo@diagnext.com"
    "filipe.barbosa@coppead.ufrj.br"
    "claudesunyata@gmail.com"
)

# Função para executar comandos SQL remotos
execute_sql() {
    local sql="$1"
    ssh -p $SSH_PORT $SSH_HOST "/usr/bin/mariadb $DB_NAME -p'$DB_PASS' -e \"$sql\"" 2>/dev/null
}

# Função para executar comandos remotos
execute_remote() {
    ssh -p $SSH_PORT $SSH_HOST "$1"
}

echo -e "${BLUE}╔═══════════════════════════════════════════════════════════════╗${NC}"
echo -e "${BLUE}║    Script de Preparação para Testes - Plataforma Sunyata     ║${NC}"
echo -e "${BLUE}╚═══════════════════════════════════════════════════════════════╝${NC}"
echo ""

# Verificação de segurança - confirmar admins protegidos
echo -e "${YELLOW}🔒 Verificando usuários ADMINS protegidos...${NC}"
ADMINS=$(execute_sql "SELECT email FROM users WHERE access_level = 'admin' AND email IN ('flitaiff@gmail.com', 'filipe.litaiff@ifrj.edu.br');")
echo "$ADMINS" | grep -q "flitaiff@gmail.com" && echo -e "${GREEN}   ✓ flitaiff@gmail.com (protegido)${NC}"
echo "$ADMINS" | grep -q "filipe.litaiff@ifrj.edu.br" && echo -e "${GREEN}   ✓ filipe.litaiff@ifrj.edu.br (protegido)${NC}"
echo ""

# Listar usuários que serão removidos
echo -e "${YELLOW}📋 Usuários de teste que serão REMOVIDOS:${NC}"
for email in "${TEST_USERS[@]}"; do
    USER_INFO=$(execute_sql "SELECT id, email, name FROM users WHERE email = '$email';")
    if echo "$USER_INFO" | grep -q "$email"; then
        USER_ID=$(echo "$USER_INFO" | tail -1 | awk '{print $1}')
        USER_NAME=$(echo "$USER_INFO" | tail -1 | cut -f3-)
        echo -e "${RED}   ✗ ID: $USER_ID | $email | $USER_NAME${NC}"
    else
        echo -e "${BLUE}   - $email (não existe no sistema)${NC}"
    fi
done
echo ""

# Solicitar confirmação
echo -e "${YELLOW}⚠️  ATENÇÃO: Esta ação é IRREVERSÍVEL!${NC}"
echo -e "${YELLOW}   Todos os dados desses usuários serão PERMANENTEMENTE removidos.${NC}"
echo ""

# Verificar se foi passado parâmetro -y ou --yes
if [ "$1" == "-y" ] || [ "$1" == "--yes" ]; then
    CONFIRM="SIM"
    echo "Confirmação automática via parâmetro -y"
else
    read -p "Deseja continuar? (digite 'SIM' para confirmar): " CONFIRM
fi

if [ "$CONFIRM" != "SIM" ]; then
    echo -e "${RED}❌ Operação cancelada pelo usuário.${NC}"
    exit 1
fi

echo ""
echo -e "${GREEN}✓ Confirmação recebida. Iniciando remoção...${NC}"
echo ""

# 1. Obter IDs dos usuários a remover
echo -e "${BLUE}[1/12]${NC} Identificando IDs dos usuários..."
USER_IDS=()
for email in "${TEST_USERS[@]}"; do
    USER_ID=$(execute_sql "SELECT id FROM users WHERE email = '$email';" | tail -1)
    if [ ! -z "$USER_ID" ] && [ "$USER_ID" != "id" ]; then
        USER_IDS+=("$USER_ID")
        echo "      ✓ $email → ID: $USER_ID"
    fi
done

if [ ${#USER_IDS[@]} -eq 0 ]; then
    echo -e "${GREEN}✓ Nenhum usuário de teste encontrado no sistema.${NC}"
    exit 0
fi

# Criar lista de IDs para SQL
IDS_LIST=$(IFS=,; echo "${USER_IDS[*]}")
echo "      Total de usuários a remover: ${#USER_IDS[@]}"
echo ""

# 2. Remover consents LGPD (compliance)
echo -e "${BLUE}[2/12]${NC} Removendo consents LGPD..."
CONSENT_COUNT=$(execute_sql "SELECT COUNT(*) FROM consents WHERE user_id IN ($IDS_LIST);" | tail -1)
if [ "$CONSENT_COUNT" -gt 0 ]; then
    execute_sql "DELETE FROM consents WHERE user_id IN ($IDS_LIST);"
    echo "      ✓ $CONSENT_COUNT consent(s) removido(s)"
else
    echo "      - Nenhum consent encontrado"
fi
echo ""

# 3. Remover histórico de prompts (prompt_history)
echo -e "${BLUE}[3/12]${NC} Removendo histórico de prompts da API Claude..."
PROMPT_COUNT=$(execute_sql "SELECT COUNT(*) FROM prompt_history WHERE user_id IN ($IDS_LIST);" | tail -1)
if [ "$PROMPT_COUNT" -gt 0 ]; then
    execute_sql "DELETE FROM prompt_history WHERE user_id IN ($IDS_LIST);"
    echo "      ✓ $PROMPT_COUNT registro(s) removido(s)"
else
    echo "      - Nenhum registro encontrado"
fi
echo ""

# 4. Remover solicitações de acesso vertical
echo -e "${BLUE}[4/12]${NC} Removendo solicitações de acesso vertical..."
ACCESS_COUNT=$(execute_sql "SELECT COUNT(*) FROM vertical_access_requests WHERE user_id IN ($IDS_LIST);" | tail -1)
if [ "$ACCESS_COUNT" -gt 0 ]; then
    execute_sql "DELETE FROM vertical_access_requests WHERE user_id IN ($IDS_LIST);"
    echo "      ✓ $ACCESS_COUNT solicitação(ões) removida(s)"
else
    echo "      - Nenhuma solicitação encontrada"
fi
echo ""

# 5. Remover perfis de usuário
echo -e "${BLUE}[5/12]${NC} Removendo perfis de usuário..."
PROFILE_COUNT=$(execute_sql "SELECT COUNT(*) FROM user_profiles WHERE user_id IN ($IDS_LIST);" | tail -1)
if [ "$PROFILE_COUNT" -gt 0 ]; then
    execute_sql "DELETE FROM user_profiles WHERE user_id IN ($IDS_LIST);"
    echo "      ✓ $PROFILE_COUNT perfil(is) removido(s)"
else
    echo "      - Nenhum perfil encontrado"
fi
echo ""

# 6a. Remover mensagens de conversas do Canvas (conversation_messages)
echo -e "${BLUE}[6a/12]${NC} Removendo mensagens de conversas do Canvas..."
MSG_COUNT=$(execute_sql "
    SELECT COUNT(*) FROM conversation_messages cm
    INNER JOIN conversations c ON cm.conversation_id = c.id
    WHERE c.user_id IN ($IDS_LIST);" | tail -1)
if [ "$MSG_COUNT" -gt 0 ]; then
    execute_sql "
        DELETE cm FROM conversation_messages cm
        INNER JOIN conversations c ON cm.conversation_id = c.id
        WHERE c.user_id IN ($IDS_LIST);"
    echo "      ✓ $MSG_COUNT mensagem(ns) removida(s)"
else
    echo "      - Nenhuma mensagem encontrada"
fi
echo ""

# 6b. Remover links conversa-arquivo (conversation_files)
echo -e "${BLUE}[6b/12]${NC} Removendo links conversa-arquivo..."
LINK_COUNT=$(execute_sql "
    SELECT COUNT(*) FROM conversation_files cf
    INNER JOIN conversations c ON cf.conversation_id = c.id
    WHERE c.user_id IN ($IDS_LIST);" | tail -1)
if [ "$LINK_COUNT" -gt 0 ]; then
    execute_sql "
        DELETE cf FROM conversation_files cf
        INNER JOIN conversations c ON cf.conversation_id = c.id
        WHERE c.user_id IN ($IDS_LIST);"
    echo "      ✓ $LINK_COUNT link(s) removido(s)"
else
    echo "      - Nenhum link encontrado"
fi
echo ""

# 6c. Remover conversas do Canvas (conversations)
echo -e "${BLUE}[6c/12]${NC} Removendo conversas do Canvas..."
CONV_COUNT=$(execute_sql "SELECT COUNT(*) FROM conversations WHERE user_id IN ($IDS_LIST);" | tail -1)
if [ "$CONV_COUNT" -gt 0 ]; then
    execute_sql "DELETE FROM conversations WHERE user_id IN ($IDS_LIST);"
    echo "      ✓ $CONV_COUNT conversa(s) removida(s)"
else
    echo "      - Nenhuma conversa encontrada"
fi
echo ""

# 6d. Remover arquivos físicos de uploads (Canvas v2)
echo -e "${BLUE}[6d/12]${NC} Removendo arquivos físicos de uploads..."
UPLOAD_DIR="/home/u202164171/domains/sunyataconsulting.com/storage/uploads"
TOTAL_FILES_DELETED=0
for user_id in "${USER_IDS[@]}"; do
    FILES_TO_DELETE=$(execute_sql "SELECT filepath FROM user_files WHERE user_id = $user_id;")
    FILE_COUNT=0
    while IFS= read -r filepath; do
        if [ ! -z "$filepath" ] && [ "$filepath" != "filepath" ]; then
            execute_remote "rm -f \"$UPLOAD_DIR/$filepath\" 2>/dev/null"
            ((FILE_COUNT++))
        fi
    done <<< "$FILES_TO_DELETE"
    TOTAL_FILES_DELETED=$((TOTAL_FILES_DELETED + FILE_COUNT))
done
if [ $TOTAL_FILES_DELETED -gt 0 ]; then
    echo "      ✓ $TOTAL_FILES_DELETED arquivo(s) físico(s) removido(s)"
else
    echo "      - Nenhum arquivo físico encontrado"
fi
echo ""

# 6e. Remover metadados de uploads (user_files)
echo -e "${BLUE}[6e/12]${NC} Removendo metadados de uploads (user_files)..."
FILE_COUNT=$(execute_sql "SELECT COUNT(*) FROM user_files WHERE user_id IN ($IDS_LIST);" | tail -1)
if [ "$FILE_COUNT" -gt 0 ]; then
    execute_sql "DELETE FROM user_files WHERE user_id IN ($IDS_LIST);"
    echo "      ✓ $FILE_COUNT registro(s) removido(s) de user_files"
else
    echo "      - Nenhum metadado de arquivo encontrado"
fi
echo ""

# 7. Remover logs de auditoria
echo -e "${BLUE}[7/12]${NC} Removendo logs de auditoria..."
AUDIT_COUNT=$(execute_sql "SELECT COUNT(*) FROM audit_logs WHERE user_id IN ($IDS_LIST);" | tail -1)
if [ "$AUDIT_COUNT" -gt 0 ]; then
    execute_sql "DELETE FROM audit_logs WHERE user_id IN ($IDS_LIST);"
    echo "      ✓ $AUDIT_COUNT log(s) removido(s)"
else
    echo "      - Nenhum log encontrado"
fi
echo ""

# 8. Remover os próprios usuários
echo -e "${BLUE}[8/12]${NC} Removendo registros de usuários..."
for email in "${TEST_USERS[@]}"; do
    RESULT=$(execute_sql "DELETE FROM users WHERE email = '$email' AND access_level != 'admin';" 2>&1)
    if [ $? -eq 0 ]; then
        echo "      ✓ $email removido"
    fi
done
echo ""

# 9. Limpar sessões ativas (TODAS as sessões para garantir)
echo -e "${BLUE}[9/12]${NC} Limpando TODAS as sessões ativas..."
SESSION_DIR="/home/u202164171/domains/sunyataconsulting.com/public_html/plataforma-sunyata/var/sessions"
SESSION_COUNT=$(execute_remote "find $SESSION_DIR -type f -name 'sess_*' 2>/dev/null | wc -l" | tr -d ' ')
if [ "$SESSION_COUNT" -gt 0 ]; then
    execute_remote "rm -f $SESSION_DIR/sess_* 2>/dev/null"
    echo "      ✓ $SESSION_COUNT sessão(ões) removida(s)"
    echo "      ${YELLOW}⚠️  ATENÇÃO: Todas as sessões foram limpas (todos os usuários serão deslogados)${NC}"
else
    echo "      - Nenhuma sessão ativa"
fi
echo ""

# 10. Limpar cache
echo -e "${BLUE}[10/12]${NC} Limpando cache do sistema..."
CACHE_DIR="/home/u202164171/domains/sunyataconsulting.com/public_html/plataforma-sunyata/var/cache"
execute_remote "rm -rf $CACHE_DIR/* 2>/dev/null"
echo "      ✓ Cache limpo"
echo ""

# Verificação final
echo -e "${GREEN}═══════════════════════════════════════════════════════════════${NC}"
echo -e "${GREEN}✓ REMOÇÃO CONCLUÍDA COM SUCESSO!${NC}"
echo -e "${GREEN}═══════════════════════════════════════════════════════════════${NC}"
echo ""

# Estatísticas finais
echo -e "${BLUE}📊 Estatísticas de Remoção:${NC}"
echo "   • Consents LGPD removidos: $CONSENT_COUNT"
echo "   • Prompts removidos: $PROMPT_COUNT"
echo "   • Solicitações removidas: $ACCESS_COUNT"
echo "   • Perfis removidos: $PROFILE_COUNT"
echo "   • Mensagens Canvas removidas: $MSG_COUNT"
echo "   • Links conversa-arquivo removidos: $LINK_COUNT"
echo "   • Conversas Canvas removidas: $CONV_COUNT"
echo "   • Arquivos físicos removidos: $TOTAL_FILES_DELETED"
echo "   • Metadados de arquivos removidos: $FILE_COUNT"
echo "   • Logs removidos: $AUDIT_COUNT"
echo "   • Usuários removidos: ${#USER_IDS[@]}"
echo "   • Sessões limpas: $SESSION_COUNT (TODAS)"
echo "   • Cache: limpo"
echo ""

# Verificar que usuários foram removidos
echo -e "${YELLOW}🔍 Verificação Final:${NC}"
for email in "${TEST_USERS[@]}"; do
    EXISTS=$(execute_sql "SELECT COUNT(*) FROM users WHERE email = '$email';" | tail -1)
    if [ "$EXISTS" -eq 0 ]; then
        echo -e "   ${GREEN}✓ $email - REMOVIDO${NC}"
    else
        echo -e "   ${RED}✗ $email - AINDA EXISTE (verificar!)${NC}"
    fi
done
echo ""

# Status do sistema
echo -e "${BLUE}📋 Status do Sistema:${NC}"
TOTAL_USERS=$(execute_sql "SELECT COUNT(*) FROM users;" | tail -1)
ADMIN_USERS=$(execute_sql "SELECT COUNT(*) FROM users WHERE access_level = 'admin';" | tail -1)
PENDING_REQUESTS=$(execute_sql "SELECT COUNT(*) FROM vertical_access_requests WHERE status = 'pending';" | tail -1)
CANVAS_CONVERSATIONS=$(execute_sql "SELECT COUNT(*) FROM conversations;" | tail -1)
CANVAS_FILES=$(execute_sql "SELECT COUNT(*) FROM user_files;" | tail -1)

echo "   • Total de usuários no sistema: $TOTAL_USERS"
echo "   • Administradores: $ADMIN_USERS"
echo "   • Solicitações pendentes: $PENDING_REQUESTS"
echo "   • Conversas Canvas ativas: $CANVAS_CONVERSATIONS"
echo "   • Arquivos no sistema: $CANVAS_FILES"
echo ""

# Verificar uploads órfãos (arquivos sem usuário)
echo -e "${YELLOW}🔍 Verificando integridade do Canvas v2...${NC}"
ORPHAN_FILES=$(execute_sql "
    SELECT COUNT(*) FROM user_files uf
    LEFT JOIN users u ON uf.user_id = u.id
    WHERE u.id IS NULL;" | tail -1)
if [ "$ORPHAN_FILES" -gt 0 ]; then
    echo -e "   ${YELLOW}⚠️  $ORPHAN_FILES arquivo(s) órfão(s) encontrado(s)${NC}"
    echo "   Execute: DELETE FROM user_files WHERE user_id NOT IN (SELECT id FROM users);"
else
    echo -e "   ${GREEN}✓ Nenhum arquivo órfão encontrado${NC}"
fi
echo ""

# Configuração atual
echo -e "${BLUE}⚙️  Configuração Atual:${NC}"
JURIDICO_APPROVAL=$(execute_sql "SELECT setting_value FROM settings WHERE setting_key = 'juridico_requires_approval';" | tail -1)
if [ "$JURIDICO_APPROVAL" = "1" ]; then
    echo -e "   • Aprovação Jurídico: ${YELLOW}HABILITADA${NC}"
    echo ""
    echo -e "${YELLOW}💡 Dica: Para testar acesso imediato, desabilite a aprovação:${NC}"
    echo "   ssh -p 65002 $SSH_HOST \"/usr/bin/mariadb $DB_NAME -p'$DB_PASS' -e \\\"UPDATE settings SET setting_value = '0' WHERE setting_key = 'juridico_requires_approval';\\\"\""
else
    echo -e "   • Aprovação Jurídico: ${GREEN}DESABILITADA${NC} (acesso imediato)"
fi
echo ""

echo -e "${GREEN}╔═══════════════════════════════════════════════════════════════╗${NC}"
echo -e "${GREEN}║              SISTEMA PRONTO PARA TESTES!                      ║${NC}"
echo -e "${GREEN}╚═══════════════════════════════════════════════════════════════╝${NC}"
echo ""
echo -e "${BLUE}📝 Próximos Passos:${NC}"
echo "   1. Acesse: https://portal.sunyataconsulting.com"
echo "   2. Faça login com os usuários de teste via Google OAuth:"
echo "      • filipe.litaiff@gmail.com"
echo "      • pmo@diagnext.com"
echo "      • filipe.barbosa@coppead.ufrj.br"
echo "   3. Complete o onboarding e teste os fluxos"
echo ""
echo -e "${BLUE}📖 Guia de Testes:${NC}"
echo "   Ver: ONBOARDING_TEST_GUIDE.md"
echo ""
