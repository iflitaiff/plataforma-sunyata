#!/bin/bash
# Script de Teste no Servidor - Plataforma Sunyata
# Uso: ./scripts/test-server.sh

# Configurações
SERVER_USER="u202164171"
SERVER_HOST="82.25.72.226"
SERVER_PORT="65002"
PORTAL_URL="https://portal.sunyataconsulting.com"

# Cores para output
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

echo -e "${BLUE}╔══════════════════════════════════════════╗${NC}"
echo -e "${BLUE}║   Teste no Servidor                     ║${NC}"
echo -e "${BLUE}╚══════════════════════════════════════════╝${NC}"
echo ""

# Criar script remoto de teste
REMOTE_SCRIPT=$(cat <<'EOF'
#!/bin/bash

echo "📂 Navegando para o diretório..."
cd public_html/portal || cd ~/public_html || cd ~/domains/portal.sunyataconsulting.com/public_html

echo ""
echo "📋 Git Status:"
git status --short

echo ""
echo "🔍 Verificando sintaxe PHP..."
php -l config/auth.php && echo "  ✅ config/auth.php OK"
php -l config/config.php && echo "  ✅ config/config.php OK"
php -l public/areas/direito/solicitar-acesso.php && echo "  ✅ solicitar-acesso.php OK"

echo ""
echo "🧪 Testando carregamento de configs..."
php -r "
error_reporting(E_ALL);
ini_set('display_errors', 0);
try {
    require_once 'config/config.php';
    echo '  ✅ config.php carregado\n';
    require_once 'config/auth.php';
    echo '  ✅ auth.php carregado\n';
    if (function_exists('require_login')) {
        echo '  ✅ função require_login() existe\n';
    } else {
        echo '  ❌ função require_login() não encontrada\n';
    }
} catch (Exception \$e) {
    echo '  ❌ Erro: ' . \$e->getMessage() . '\n';
}
"

echo ""
echo "📋 Últimas 10 linhas do log de erros:"
if [ -f logs/php_errors.log ]; then
    tail -10 logs/php_errors.log | grep -E "Fatal|require_login|solicitar-acesso" || echo "  ✅ Sem erros fatais recentes"
else
    echo "  ⚠️  Log não encontrado"
fi

echo ""
echo "📁 Verificando arquivos storage:"
ls -lah storage/ 2>/dev/null || echo "  ⚠️  Diretório storage não existe"

echo ""
echo "🕒 Última modificação dos arquivos corrigidos:"
ls -lh --time-style=long-iso config/auth.php config/config.php public/areas/direito/solicitar-acesso.php | awk '{print "  " $6, $7, $8}'

echo ""
echo "✅ Teste concluído!"
EOF
)

# Executar no servidor
ssh -p ${SERVER_PORT} ${SERVER_USER}@${SERVER_HOST} "${REMOTE_SCRIPT}"

echo ""
echo -e "${BLUE}🔗 Para testar no navegador:${NC}"
echo -e "   ${PORTAL_URL}/areas/direito/solicitar-acesso.php"
echo ""
