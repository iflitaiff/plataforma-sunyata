#!/bin/bash
# Setup Local Database for Development
# Run: bash scripts/setup-local-db.sh

set -e

echo "🔧 Setup Local Database - Plataforma Sunyata"
echo "=============================================="
echo ""

# Check if MariaDB is installed
if ! command -v mysql &> /dev/null; then
    echo "❌ MariaDB/MySQL não encontrado"
    echo ""
    echo "Instale com:"
    echo "  sudo apt update"
    echo "  sudo apt install mariadb-server mariadb-client"
    echo "  sudo systemctl start mariadb"
    echo ""
    exit 1
fi

# Check if MariaDB is running
if ! sudo systemctl is-active --quiet mariadb; then
    echo "⚠️  MariaDB não está rodando. Iniciando..."
    sudo systemctl start mariadb
fi

echo "✅ MariaDB instalado e rodando"
echo ""

# Create database
DB_NAME="sunyata_dev"
echo "📦 Criando database: $DB_NAME"

sudo mysql -e "DROP DATABASE IF EXISTS $DB_NAME;" 2>/dev/null || true
sudo mysql -e "CREATE DATABASE $DB_NAME CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
sudo mysql -e "GRANT ALL PRIVILEGES ON $DB_NAME.* TO 'root'@'localhost';"
sudo mysql -e "FLUSH PRIVILEGES;"

echo "✅ Database criado: $DB_NAME"
echo ""

# Import existing schema from production
echo "📥 Importando schema da produção..."
echo ""
echo "Execute manualmente:"
echo "  ssh -p 65002 u202164171@82.25.72.226 \\"
echo "    '/usr/bin/mariadb-dump u202164171_sunyata --no-data' > /tmp/schema.sql"
echo ""
echo "  mysql -u root sunyata_dev < /tmp/schema.sql"
echo ""
echo "Ou aplique as migrations manualmente:"
echo "  mysql -u root sunyata_dev < database/migrations/001_initial_schema.sql"
echo "  mysql -u root sunyata_dev < database/migrations/002_*.sql"
echo "  mysql -u root sunyata_dev < database/migrations/003_*.sql"
echo ""

# Create local config
echo "📝 Criando config/database.local.php"
cat > config/database.local.php <<'EOF'
<?php
/**
 * Local Database Configuration
 * This file is ignored by Git (in .gitignore)
 */

define('DB_HOST', 'localhost');
define('DB_NAME', 'sunyata_dev');
define('DB_USER', 'root');
define('DB_PASS', ''); // Sem senha no desenvolvimento local
define('DB_CHARSET', 'utf8mb4');
EOF

echo "✅ Config local criado"
echo ""

# Update .gitignore
if ! grep -q "database.local.php" .gitignore 2>/dev/null; then
    echo "config/database.local.php" >> .gitignore
    echo "✅ Adicionado database.local.php ao .gitignore"
else
    echo "✅ .gitignore já configurado"
fi

echo ""
echo "✅ Setup completo!"
echo ""
echo "Próximos passos:"
echo "  1. Importar schema da produção (comandos acima)"
echo "  2. Modificar config/config.php para usar database.local.php se existir"
echo "  3. Testar: mysql -u root sunyata_dev -e 'SHOW TABLES;'"
echo ""
