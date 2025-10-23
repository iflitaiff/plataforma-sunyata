#!/bin/bash
# Script para capturar sessão de deploy
# Uso: bash start-deploy-session.sh

SESSION_LOG="/tmp/deploy-session-$(date +%Y%m%d_%H%M%S).log"

echo "=========================================="
echo "🚀 Sessão de Deploy MVP Admin"
echo "=========================================="
echo ""
echo "📝 Tudo que você fizer será salvo em:"
echo "   $SESSION_LOG"
echo ""
echo "💡 Quando terminar, digite: exit"
echo ""
echo "=========================================="
echo ""

# Iniciar script (captura tudo)
script -f -q "$SESSION_LOG"

echo ""
echo "=========================================="
echo "✅ Sessão encerrada!"
echo "📝 Log salvo em: $SESSION_LOG"
echo ""
echo "📋 Para ver o log:"
echo "   cat $SESSION_LOG"
echo ""
echo "📤 Caminho para Claude ler:"
echo "   $SESSION_LOG"
echo "=========================================="
