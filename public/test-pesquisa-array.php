<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/config.php';

session_name(SESSION_NAME);
session_start();

// Array das ferramentas (copiado do index.php da vertical Pesquisa)
$ferramentas = array (
  0 =>
  array (
    'id' => 'canvas-docente',
    'nome' => 'Canvas Docente',
    'descricao' => 'Planejamento estruturado de aulas e atividades',
    'icone' => '📋',
  ),
  1 =>
  array (
    'id' => 'canvas-pesquisa',
    'nome' => 'Canvas Pesquisa',
    'descricao' => 'Estruturação de projetos de pesquisa acadêmica',
    'icone' => '🔬',
  ),
  2 =>
  array (
    'id' => 'repositorio-prompts',
    'nome' => 'Repositório de Prompts',
    'descricao' => 'Dicionário geral de prompts e técnicas',
    'icone' => '📚',
  ),
);

echo "<h1>Debug: Array de Ferramentas</h1>";
echo "<p>Total de ferramentas: " . count($ferramentas) . "</p>";
echo "<pre>";
print_r($ferramentas);
echo "</pre>";

echo "<hr>";
echo "<h2>Renderização dos Cards</h2>";

foreach ($ferramentas as $index => $ferramenta) {
    echo "<div style='border: 1px solid #ccc; padding: 10px; margin: 10px; display: inline-block;'>";
    echo "<p><strong>Index: {$index}</strong></p>";
    echo "<p>Ícone: {$ferramenta['icone']}</p>";
    echo "<p>Nome: {$ferramenta['nome']}</p>";
    echo "<p>ID: {$ferramenta['id']}</p>";
    echo "</div>";
}
