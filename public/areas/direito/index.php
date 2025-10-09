<?php
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../config/auth.php';

require_login();

include __DIR__ . '/../../../src/views/navbar.php';
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Direito | Plataforma Sunyata</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .card {
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <div class="row">
            <div class="col-12">
                <h1 class="mb-3">Direito</h1>
                <p class="lead text-muted mb-5">Ferramentas e guias para profissionais do Direito.</p>
            </div>
        </div>

        <div class="row g-4">
            <!-- Card 1: Canvas Jurídico -->
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title">Canvas Jurídico</h5>
                        <p class="card-text flex-grow-1">
                            Ferramenta visual para mapear e estruturar casos jurídicos, facilitando a análise e o planejamento estratégico.
                        </p>
                        <a href="/areas/direito/canvas-juridico.php" class="btn btn-primary">Acessar</a>
                    </div>
                </div>
            </div>

            <!-- Card 2: Guia de Prompts (Jurídico) -->
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title">Guia de Prompts (Jurídico)</h5>
                        <p class="card-text flex-grow-1">
                            Coletânea de prompts especializados para uso de IA em tarefas jurídicas, otimizando pesquisas e redação de peças.
                        </p>
                        <a href="/public/ferramentas/guia-prompts-juridico.html" class="btn btn-primary" target="_blank" rel="noopener noreferrer">
                            Abrir
                        </a>
                    </div>
                </div>
            </div>

            <!-- Card 3: Padrões Avançados (Jurídico) -->
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title">Padrões Avançados (Jurídico)</h5>
                        <p class="card-text flex-grow-1">
                            Técnicas e estratégias avançadas de prompting para análise de casos complexos e argumentação jurídica sofisticada.
                        </p>
                        <a href="/public/ferramentas/padroes-avancados-juridico.html" class="btn btn-primary" target="_blank" rel="noopener noreferrer">
                            Abrir
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-5">
            <div class="col-12 text-center">
                <a href="/dashboard.php" class="btn btn-outline-secondary">Voltar ao Dashboard</a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
