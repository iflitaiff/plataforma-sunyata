#!/bin/bash
# Script de Deploy Automatizado - Plataforma Sunyata
# Uso: ./scripts/deploy.sh [mensagem-do-commit]

set -e  # Parar em caso de erro

# Cores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configurações
REPO_DIR="/home/iflitaiff/projetos/plataforma-sunyata"
SERVER_USER="u202164171"
SERVER_HOST="82.25.72.226"
SERVER_PORT="65002"
SERVER_PATH="public_html/portal"
PORTAL_URL="https://portal.sunyataconsulting.com"

echo -e "${BLUE}╔══════════════════════════════════════════╗${NC}"
echo -e "${BLUE}║   Deploy - Plataforma Sunyata           ║${NC}"
echo -e "${BLUE}╚══════════════════════════════════════════╝${NC}"
echo ""

# Verificar se estamos no diretório correto
if [ ! -d ".git" ]; then
    echo -e "${RED}❌ Erro: Este script deve ser executado na raiz do repositório Git${NC}"
    exit 1
fi

# Verificar se há mudanças para commitar
if [ -z "$(git status --porcelain)" ]; then
    echo -e "${YELLOW}⚠️  Aviso: Não há mudanças para commitar${NC}"
    read -p "Deseja continuar mesmo assim e fazer deploy do último commit? (s/N) " -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[Ss]$ ]]; then
        echo -e "${RED}❌ Deploy cancelado${NC}"
        exit 1
    fi
else
    # Mostrar status
    echo -e "${BLUE}📋 Status atual:${NC}"
    git status --short
    echo ""

    # Pedir mensagem de commit se não foi fornecida
    if [ -z "$1" ]; then
        echo -e "${YELLOW}💬 Digite a mensagem do commit:${NC}"
        read -p "> " COMMIT_MSG
    else
        COMMIT_MSG="$1"
    fi

    # Adicionar arquivos modificados
    echo -e "${BLUE}📦 Adicionando arquivos...${NC}"
    git add -A

    # Fazer commit
    echo -e "${BLUE}💾 Criando commit...${NC}"
    git commit -m "$COMMIT_MSG"

    if [ $? -ne 0 ]; then
        echo -e "${RED}❌ Erro ao criar commit${NC}"
        exit 1
    fi
    echo -e "${GREEN}✅ Commit criado${NC}"
fi

# Fazer push para GitHub
echo -e "${BLUE}🚀 Enviando para GitHub...${NC}"
git push origin main

if [ $? -ne 0 ]; then
    echo -e "${RED}❌ Erro ao fazer push para GitHub${NC}"
    exit 1
fi
echo -e "${GREEN}✅ Push concluído${NC}"
echo ""

# Fazer deploy no servidor
echo -e "${BLUE}🌐 Conectando no servidor...${NC}"
echo -e "${YELLOW}   Servidor: ${SERVER_HOST}:${SERVER_PORT}${NC}"
echo ""

# Criar script remoto temporário
REMOTE_SCRIPT=$(cat <<'EOF'
#!/bin/bash
set -e

echo "📂 Navegando para o diretório do portal..."
cd public_html/portal || cd ~/public_html || cd ~/domains/portal.sunyataconsulting.com/public_html

echo "📥 Fazendo pull das mudanças..."
git pull origin main

echo "🧹 Limpando OPcache..."
php -r "if (function_exists('opcache_reset')) { opcache_reset(); echo '✅ OPcache limpo\n'; } else { echo '⚠️  OPcache não disponível\n'; }"

echo "🔍 Verificando sintaxe PHP..."
php -l config/auth.php
php -l config/config.php
php -l public/areas/direito/solicitar-acesso.php

echo "📋 Verificando logs recentes..."
if [ -f logs/php_errors.log ]; then
    echo "Últimas 5 linhas do log de erros:"
    tail -5 logs/php_errors.log
else
    echo "⚠️  Arquivo de log não encontrado"
fi

echo ""
echo "✅ Deploy concluído no servidor!"
EOF
)

# Executar no servidor
ssh -p ${SERVER_PORT} ${SERVER_USER}@${SERVER_HOST} "${REMOTE_SCRIPT}"

if [ $? -ne 0 ]; then
    echo -e "${RED}❌ Erro ao executar comandos no servidor${NC}"
    exit 1
fi

echo ""
echo -e "${GREEN}╔══════════════════════════════════════════╗${NC}"
echo -e "${GREEN}║   ✅ DEPLOY CONCLUÍDO COM SUCESSO!      ║${NC}"
echo -e "${GREEN}╚══════════════════════════════════════════╝${NC}"
echo ""
echo -e "${BLUE}🔗 Portal:${NC} ${PORTAL_URL}"
echo -e "${BLUE}📄 Página de teste:${NC} ${PORTAL_URL}/areas/direito/solicitar-acesso.php"
echo ""
echo -e "${YELLOW}💡 Dica: Limpe o cache do navegador (Ctrl+Shift+R) antes de testar${NC}"
echo ""
