<?php
// Limpar OPcache do PHP
if (function_exists('opcache_reset')) {
    opcache_reset();
    echo "✅ OPcache limpo com sucesso!<br>";
} else {
    echo "⚠️ OPcache não está disponível<br>";
}

// Limpar outros caches
if (function_exists('apc_clear_cache')) {
    apc_clear_cache();
    echo "✅ APC cache limpo!<br>";
}

// Informações sobre o cache
if (function_exists('opcache_get_status')) {
    $status = opcache_get_status();
    echo "<h3>Status do OPcache:</h3>";
    echo "<pre>";
    print_r($status);
    echo "</pre>";
}

echo "<hr>";
echo "<p><a href='/areas/pesquisa/'>← Voltar para Pesquisa</a></p>";
