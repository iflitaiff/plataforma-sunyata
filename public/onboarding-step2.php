<?php
/**
 * Onboarding - Step 2: Escolha de Vertical
 * Usuário seleciona qual vertical deseja acessar
 */

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/config.php';

session_name(SESSION_NAME);
session_start();

use Sunyata\Core\Database;
use Sunyata\Core\Settings;

require_login();

// Ler configuração dinâmica de aprovação Jurídico
$settings = Settings::getInstance();
$juridico_requires_approval = $settings->get('juridico_requires_approval', true);

// Se já completou onboarding E tem vertical, redireciona
if (isset($_SESSION['user']['completed_onboarding']) && $_SESSION['user']['completed_onboarding']) {
    $vertical = $_SESSION['user']['selected_vertical'] ?? null;
    if ($vertical) {
        redirect(BASE_URL . "/areas/{$vertical}/");
    }
}

// Definição das verticais disponíveis
$verticais = [
    'docencia' => [
        'nome' => 'Docência',
        'icone' => '👨‍🏫',
        'descricao' => 'Ferramentas para planejamento de aulas, criação de conteúdo educacional e gestão pedagógica.',
        'ferramentas' => ['Canvas Docente', 'Canvas Pesquisa', 'Biblioteca de Prompts (Jogos)', 'Guia de Prompts (Jogos)', 'Repositório de Prompts'],
        'disponivel' => true
    ],
    'pesquisa' => [
        'nome' => 'Pesquisa',
        'icone' => '🔬',
        'descricao' => 'Recursos para estruturação de projetos de pesquisa acadêmica e científica.',
        'ferramentas' => ['Canvas Docente', 'Canvas Pesquisa', 'Repositório de Prompts'],
        'disponivel' => true
    ],
    'ifrj_alunos' => [
        'nome' => 'IFRJ - Alunos',
        'icone' => '🎓',
        'descricao' => 'Área exclusiva para alunos do IFRJ com ferramentas de apoio ao aprendizado.',
        'ferramentas' => ['Biblioteca de Prompts (Jogos)', 'Guia de Prompts (Jogos)', 'Canvas Pesquisa', 'Repositório de Prompts'],
        'disponivel' => true,
        'requer_info_extra' => true // IFRJ precisa de nível e curso
    ],
    'juridico' => [
        'nome' => 'Jurídico',
        'icone' => '⚖️',
        'descricao' => 'Ferramentas especializadas para profissionais do Direito.' . ($juridico_requires_approval ? ' <strong>Requer aprovação.</strong>' : ''),
        'ferramentas' => ['Canvas Jurídico', 'Guia de Prompts (Jurídico)', 'Padrões Avançados (Jurídico)', 'Repositório de Prompts'],
        'disponivel' => true,
        'requer_aprovacao' => $juridico_requires_approval // Agora lê de Settings
    ],
    'vendas' => [
        'nome' => 'Vendas',
        'icone' => '📈',
        'descricao' => 'Ferramentas para otimizar processos de vendas e relacionamento com clientes.',
        'ferramentas' => [],
        'disponivel' => false
    ],
    'marketing' => [
        'nome' => 'Marketing',
        'icone' => '📢',
        'descricao' => 'Recursos para criação de conteúdo, campanhas e estratégias de marketing digital.',
        'ferramentas' => [],
        'disponivel' => false
    ],
    'licitacoes' => [
        'nome' => 'Licitações',
        'icone' => '📋',
        'descricao' => 'Ferramentas para elaboração de propostas e gestão de processos licitatórios.',
        'ferramentas' => [],
        'disponivel' => false
    ],
    'rh' => [
        'nome' => 'Recursos Humanos',
        'icone' => '👥',
        'descricao' => 'Soluções para recrutamento, seleção e gestão de pessoas.',
        'ferramentas' => [],
        'disponivel' => false
    ],
    'geral' => [
        'nome' => 'Geral',
        'icone' => '🌐',
        'descricao' => 'Ferramentas de propósito geral para diversas áreas e aplicações.',
        'ferramentas' => [],
        'disponivel' => false
    ]
];

$pageTitle = 'Escolha sua Vertical';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?> - <?= APP_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .onboarding-container {
            min-height: 100vh;
            padding: 3rem 0;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .progress-steps {
            display: flex;
            justify-content: center;
            margin-bottom: 2rem;
        }
        .progress-step {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #e9ecef;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin: 0 10px;
            position: relative;
        }
        .progress-step.active {
            background: #0d6efd;
            color: white;
        }
        .progress-step.completed {
            background: #198754;
            color: white;
        }
        .progress-step::after {
            content: '';
            position: absolute;
            width: 40px;
            height: 2px;
            background: #198754;
            left: 50px;
            top: 50%;
            transform: translateY(-50%);
        }
        .progress-step:last-child::after {
            display: none;
        }
        .vertical-card {
            transition: all 0.3s ease;
            cursor: pointer;
            border: 2px solid transparent;
            height: 100%;
        }
        .vertical-card:hover:not(.disabled) {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
            border-color: #0d6efd;
        }
        .vertical-card.disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
        .vertical-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
        }
        .vertical-badge {
            position: absolute;
            top: 10px;
            right: 10px;
        }
    </style>
</head>
<body>
    <div class="onboarding-container">
        <div class="container">
            <!-- Progress Steps -->
            <div class="row">
                <div class="col-12">
                    <div class="progress-steps">
                        <div class="progress-step completed">1</div>
                        <div class="progress-step active">2</div>
                    </div>
                </div>
            </div>

            <!-- Header -->
            <div class="row mb-5">
                <div class="col-12 text-center">
                    <h1 class="text-white mb-3">Escolha sua Vertical</h1>
                    <p class="text-white-50 lead">
                        Selecione a área que melhor se adequa ao seu perfil.<br>
                        <small>Você poderá solicitar acesso a outras verticais posteriormente.</small>
                    </p>
                </div>
            </div>

            <!-- Verticais Grid -->
            <div class="row g-4">
                <?php foreach ($verticais as $slug => $vertical): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="card vertical-card <?= !$vertical['disponivel'] ? 'disabled' : '' ?>"
                             <?= $vertical['disponivel'] ? "data-vertical-slug=\"{$slug}\"" : '' ?>
                             <?= $vertical['disponivel'] ? 'onclick="selectVerticalCard(this)"' : '' ?>>

                            <?php if (!$vertical['disponivel']): ?>
                                <span class="badge bg-secondary vertical-badge">Em breve</span>
                            <?php elseif ($vertical['requer_aprovacao'] ?? false): ?>
                                <span class="badge bg-warning text-dark vertical-badge">Requer aprovação</span>
                            <?php endif; ?>

                            <div class="card-body text-center p-4">
                                <div class="vertical-icon"><?= $vertical['icone'] ?></div>
                                <h4 class="card-title mb-3"><?= $vertical['nome'] ?></h4>
                                <p class="card-text text-muted mb-3">
                                    <?= $vertical['descricao'] ?>
                                </p>

                                <?php if (!empty($vertical['ferramentas'])): ?>
                                    <div class="text-start mt-3">
                                        <small class="text-muted d-block mb-2"><strong>Ferramentas incluídas:</strong></small>
                                        <ul class="small text-muted">
                                            <?php foreach (array_slice($vertical['ferramentas'], 0, 3) as $ferramenta): ?>
                                                <li><?= $ferramenta ?></li>
                                            <?php endforeach; ?>
                                            <?php if (count($vertical['ferramentas']) > 3): ?>
                                                <li><em>e mais <?= count($vertical['ferramentas']) - 3 ?>...</em></li>
                                            <?php endif; ?>
                                        </ul>
                                    </div>
                                <?php endif; ?>

                                <?php if ($vertical['disponivel']): ?>
                                    <button type="button" class="btn btn-primary w-100 mt-3">
                                        <?php if ($vertical['requer_aprovacao'] ?? false): ?>
                                            Solicitar Acesso
                                        <?php else: ?>
                                            Selecionar
                                        <?php endif; ?>
                                    </button>
                                <?php else: ?>
                                    <button type="button" class="btn btn-secondary w-100 mt-3" disabled>
                                        Indisponível
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Info Box -->
            <div class="row mt-5">
                <div class="col-md-8 offset-md-2">
                    <div class="alert alert-light">
                        <h6>ℹ️ Informações Importantes:</h6>
                        <ul class="mb-0 small">
                            <li><strong>Verticais "Em breve":</strong> Estão em desenvolvimento e serão disponibilizadas em breve.</li>
                            <li><strong>Vertical "Jurídico":</strong> Requer aprovação manual. Você receberá um email quando for aprovado.</li>
                            <li><strong>Dúvidas?</strong> Entre em contato: <a href="mailto:<?= SUPPORT_EMAIL ?>"><?= SUPPORT_EMAIL ?></a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Configuração das verticais (gerado pelo PHP)
        const verticaisConfig = <?= json_encode($verticais, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;

        function selectVerticalCard(element) {
            const slug = element.getAttribute('data-vertical-slug');
            if (!slug) return;

            const vertical = verticaisConfig[slug];
            if (!vertical) return;

            // Se requer informações extras (IFRJ)
            if (vertical.requer_info_extra) {
                window.location.href = '<?= BASE_URL ?>/onboarding-ifrj.php';
                return;
            }

            // Se requer aprovação (Jurídico)
            if (vertical.requer_aprovacao) {
                window.location.href = '<?= BASE_URL ?>/onboarding-juridico.php';
                return;
            }

            // Verticais normais: salva escolha direto
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '<?= BASE_URL ?>/onboarding-save-vertical.php';

            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = 'csrf_token';
            csrfInput.value = '<?= csrf_token() ?>';
            form.appendChild(csrfInput);

            const verticalInput = document.createElement('input');
            verticalInput.type = 'hidden';
            verticalInput.name = 'vertical';
            verticalInput.value = slug;
            form.appendChild(verticalInput);

            document.body.appendChild(form);
            form.submit();
        }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
