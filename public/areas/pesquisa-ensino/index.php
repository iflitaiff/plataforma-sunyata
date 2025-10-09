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
    <title>Pesquisa & Ensino | Plataforma Sunyata</title>
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
                <h1 class="mb-3">Pesquisa & Ensino</h1>
                <p class="lead text-muted mb-5">Ferramentas para planejamento de aulas e pesquisa acadêmica.</p>
            </div>
        </div>

        <div class="row g-4">
            <!-- Card 1: Canvas Docente -->
            <div class="col-md-6 col-lg-3">
                <div class="card h-100">
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title">Canvas Docente</h5>
                        <p class="card-text flex-grow-1">
                            Ferramenta visual para planejar aulas, organizar conteúdos e estruturar atividades pedagógicas de forma estratégica.
                        </p>
                        <a href="/areas/pesquisa-ensino/canvas-docente.php" class="btn btn-primary">Acessar</a>
                    </div>
                </div>
            </div>

            <!-- Card 2: Canvas Pesquisa -->
            <div class="col-md-6 col-lg-3">
                <div class="card h-100">
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title">Canvas Pesquisa</h5>
                        <p class="card-text flex-grow-1">
                            Canvas para estruturar projetos de pesquisa acadêmica, mapeando objetivos, metodologia e etapas do trabalho científico.
                        </p>
                        <a href="/areas/pesquisa-ensino/canvas-pesquisa.php" class="btn btn-primary">Acessar</a>
                    </div>
                </div>
            </div>

            <!-- Card 3: Guia de Prompts (Jogos Digitais) -->
            <div class="col-md-6 col-lg-3">
                <div class="card h-100">
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title">Guia de Prompts (Jogos Digitais)</h5>
                        <p class="card-text flex-grow-1">
                            Coletânea de prompts especializados para desenvolvimento de jogos digitais com uso de IA generativa.
                        </p>
                        <a href="/public/ferramentas/guia-prompts-jogos.html" class="btn btn-primary" target="_blank" rel="noopener noreferrer">
                            Abrir
                        </a>
                    </div>
                </div>
            </div>

            <!-- Card 4: Biblioteca de Prompts (Jogos) -->
            <div class="col-md-6 col-lg-3">
                <div class="card h-100">
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title">Biblioteca de Prompts (Jogos)</h5>
                        <p class="card-text flex-grow-1">
                            Biblioteca completa de prompts prontos para uso em diferentes etapas de desenvolvimento de jogos digitais.
                        </p>
                        <a href="/public/ferramentas/biblioteca-prompts-jogos.html" class="btn btn-primary" target="_blank" rel="noopener noreferrer">
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
