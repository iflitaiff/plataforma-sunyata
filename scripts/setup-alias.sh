#!/bin/bash

##############################################################################
# Setup Alias - Configura alias para acesso rápido ao Admin Menu
##############################################################################

ALIAS_NAME="admin"
SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
PROJECT_DIR="$(dirname "$SCRIPT_DIR")"
BASHRC="$HOME/.bashrc"

echo "╔═══════════════════════════════════════════════════════════════════════╗"
echo "║           Configurar Alias para Admin Menu                           ║"
echo "╚═══════════════════════════════════════════════════════════════════════╝"
echo ""

# Verificar se alias já existe
if grep -q "alias $ALIAS_NAME=" "$BASHRC" 2>/dev/null; then
    echo "⚠️  O alias '$ALIAS_NAME' já existe no $BASHRC"
    echo ""
    echo "Alias atual:"
    grep "alias $ALIAS_NAME=" "$BASHRC"
    echo ""
    echo -n "Deseja substituir? (s/n): "
    read response

    if [ "$response" != "s" ]; then
        echo "Operação cancelada."
        exit 0
    fi

    # Remover alias antigo
    sed -i "/alias $ALIAS_NAME=/d" "$BASHRC"
    echo "✓ Alias antigo removido"
fi

# Adicionar novo alias
echo "" >> "$BASHRC"
echo "# Admin Menu - Plataforma Sunyata" >> "$BASHRC"
echo "alias $ALIAS_NAME='cd $PROJECT_DIR && ./scripts/admin-menu.sh'" >> "$BASHRC"

echo ""
echo "✅ Alias configurado com sucesso!"
echo ""
echo "Comando adicionado ao $BASHRC:"
echo "  alias $ALIAS_NAME='cd $PROJECT_DIR && ./scripts/admin-menu.sh'"
echo ""
echo "Para ativar imediatamente:"
echo "  source ~/.bashrc"
echo ""
echo "Ou feche e abra o terminal novamente."
echo ""
echo "Depois, execute de qualquer lugar:"
echo "  $ALIAS_NAME"
echo ""
