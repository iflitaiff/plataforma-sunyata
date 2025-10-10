<?php
/**
 * User Dashboard
 *
 * @package Sunyata
 */

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/config.php';

session_name(SESSION_NAME);
session_start();

require_login();

use Sunyata\Auth\GoogleAuth;
use Sunyata\Core\User;
use Sunyata\Compliance\ConsentManager;

$auth = new GoogleAuth();
$userModel = new User();
$consentManager = new ConsentManager();

$currentUser = $auth->getCurrentUser();
$contracts = $userModel->getActiveContracts($currentUser['id']);

// Handle consent form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accept_terms'])) {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        $_SESSION['error'] = 'Token de segurança inválido';
        redirect(BASE_URL . '/dashboard.php');
    }

    // Record consents
    $consentManager->recordConsent(
        $currentUser['id'],
        'terms_of_use',
        true,
        $consentManager->getConsentText('terms_of_use')
    );

    $consentManager->recordConsent(
        $currentUser['id'],
        'privacy_policy',
        true,
        $consentManager->getConsentText('privacy_policy')
    );

    if (isset($_POST['data_processing'])) {
        $consentManager->recordConsent(
            $currentUser['id'],
            'data_processing',
            true,
            $consentManager->getConsentText('data_processing')
        );
    }

    if (isset($_POST['marketing'])) {
        $consentManager->recordConsent(
            $currentUser['id'],
            'marketing',
            true,
            $consentManager->getConsentText('marketing')
        );
    }

    unset($_SESSION['needs_consent']);
    $_SESSION['success'] = 'Consentimentos registrados com sucesso!';
    redirect(BASE_URL . '/dashboard.php');
}

$needsConsent = isset($_SESSION['needs_consent']) || isset($_GET['consent']);
$pageTitle = 'Dashboard';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?> - <?= APP_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>
<body>
    <?php include __DIR__ . '/../src/views/navbar.php'; ?>

    <div class="container my-5">
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?= sanitize_output($_SESSION['success']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?= sanitize_output($_SESSION['error']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <?php if ($needsConsent): ?>
            <!-- Consent Form -->
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="card shadow">
                        <div class="card-body p-5">
                            <h2 class="card-title mb-4">Termos e Consentimentos</h2>
                            <p class="text-muted">Para continuar, precisamos do seu consentimento:</p>

                            <form method="POST" action="">
                                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

                                <div class="mb-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="accept_terms" id="terms" required>
                                        <label class="form-check-label" for="terms">
                                            <strong>Li e aceito os Termos de Uso e Política de Privacidade</strong> (obrigatório)
                                        </label>
                                        <div class="small text-muted mt-2">
                                            <?= nl2br(sanitize_output($consentManager->getConsentText('terms_of_use'))) ?>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="data_processing" id="data_processing" checked>
                                        <label class="form-check-label" for="data_processing">
                                            <strong>Processamento de dados para personalização</strong> (opcional)
                                        </label>
                                        <div class="small text-muted mt-2">
                                            <?= nl2br(sanitize_output($consentManager->getConsentText('data_processing'))) ?>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="marketing" id="marketing">
                                        <label class="form-check-label" for="marketing">
                                            <strong>Comunicações de marketing</strong> (opcional)
                                        </label>
                                        <div class="small text-muted mt-2">
                                            <?= nl2br(sanitize_output($consentManager->getConsentText('marketing'))) ?>
                                        </div>
                                    </div>
                                </div>

                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary btn-lg">Aceitar e Continuar</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <!-- Dashboard Content -->
            <div class="row mb-4">
                <div class="col">
                    <h1 class="mb-3">Bem-vindo, <?= sanitize_output($currentUser['name']) ?>!</h1>
                    <p class="text-muted">Nível de acesso: <span class="badge bg-primary"><?= sanitize_output(ucfirst($currentUser['access_level'])) ?></span></p>
                </div>
            </div>

            <!-- Vertical Info -->
            <?php
            $user_vertical = $_SESSION['user']['selected_vertical'] ?? null;
            $is_demo = $_SESSION['user']['is_demo'] ?? false;
            $is_admin = ($currentUser['access_level'] === 'admin');
            $completed_onboarding = $_SESSION['user']['completed_onboarding'] ?? false;

            // Nomes amigáveis e ícones das verticais
            $verticals_info = [
                'docencia' => ['nome' => 'Docência', 'icone' => '👩‍🏫', 'disponivel' => true],
                'pesquisa' => ['nome' => 'Pesquisa', 'icone' => '🔬', 'disponivel' => true],
                'ifrj_alunos' => ['nome' => 'IFRJ - Alunos', 'icone' => '🎓', 'disponivel' => true],
                'juridico' => ['nome' => 'Jurídico', 'icone' => '⚖️', 'disponivel' => true],
                'vendas' => ['nome' => 'Vendas', 'icone' => '📈', 'disponivel' => false],
                'marketing' => ['nome' => 'Marketing', 'icone' => '📢', 'disponivel' => false],
                'licitacoes' => ['nome' => 'Licitações', 'icone' => '📋', 'disponivel' => false],
                'rh' => ['nome' => 'Recursos Humanos', 'icone' => '👥', 'disponivel' => false],
                'geral' => ['nome' => 'Geral', 'icone' => '🌐', 'disponivel' => false]
            ];
            ?>

            <?php if ($is_admin): ?>
                <!-- Admin: Vertical Selector -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="alert alert-primary">
                            <strong>👑 Modo Administrador:</strong> Você tem acesso a todas as verticais.
                            <span class="float-end">
                                <a href="<?= BASE_URL ?>/admin/" class="btn btn-sm btn-dark">
                                    🛠️ Painel Admin
                                </a>
                            </span>
                        </div>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-12">
                        <h3 class="mb-3">Selecione uma Vertical para Explorar</h3>
                    </div>
                </div>

                <div class="row g-4 mb-5">
                    <?php foreach ($verticals_info as $slug => $info): ?>
                        <div class="col-md-6 col-lg-3">
                            <a href="<?= BASE_URL ?>/areas/<?= $slug ?>/" class="text-decoration-none">
                                <div class="card h-100 <?= $info['disponivel'] ? 'border-primary' : 'border-secondary' ?>">
                                    <div class="card-body text-center">
                                        <div style="font-size: 3rem;" class="mb-2"><?= $info['icone'] ?></div>
                                        <h5 class="card-title"><?= $info['nome'] ?></h5>
                                        <?php if (!$info['disponivel']): ?>
                                            <span class="badge bg-warning text-dark">Em breve</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php elseif ($user_vertical): ?>
                <!-- Regular User: Show Their Vertical -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="alert alert-info">
                            <strong>📍 Sua Vertical:</strong> <?= $verticals_info[$user_vertical]['nome'] ?? ucfirst($user_vertical) ?>
                            <?php if ($is_demo): ?>
                                <span class="badge bg-warning ms-2">Modo Demo</span>
                            <?php endif; ?>
                            <span class="float-end">
                                <a href="<?= BASE_URL ?>/areas/<?= $user_vertical ?>/" class="btn btn-sm btn-primary">
                                    Ir para Área da Vertical →
                                </a>
                            </span>
                        </div>
                    </div>
                </div>
            <?php elseif (!$completed_onboarding): ?>
                <!-- No Vertical: Prompt Onboarding -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="alert alert-warning">
                            <strong>⚠️ Onboarding Pendente:</strong> Complete seu perfil para acessar as ferramentas.
                            <a href="<?= BASE_URL ?>/onboarding-step1.php" class="btn btn-sm btn-warning ms-3">
                                Completar Agora
                            </a>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (!$is_admin): ?>
                <!-- Ferramentas Section (apenas para usuários regulares) -->
                <div class="row mb-4">
                    <div class="col-12">
                        <h3 class="mb-3">Suas Ferramentas</h3>
                    </div>
                </div>

                <?php
                $user_tools = function_exists('get_user_tools') ? get_user_tools() : [];
                ?>

                <?php if (empty($user_tools)): ?>
                    <div class="row mb-5">
                        <div class="col-12">
                            <div class="alert alert-info text-center">
                                <h4>👋 Nenhuma ferramenta disponível ainda</h4>
                                <p>Complete o onboarding para ter acesso às ferramentas da plataforma.</p>
                                <?php if (!$completed_onboarding): ?>
                                    <a href="<?= BASE_URL ?>/onboarding-step1.php" class="btn btn-primary">
                                        Começar Onboarding
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="row g-4 mb-5">
                        <?php foreach ($user_tools as $tool): ?>
                            <div class="col-md-6 col-lg-3">
                                <div class="card h-100 border-primary">
                                    <div class="card-body">
                                        <div class="text-center mb-3">
                                            <span style="font-size: 40px;"><?= $tool['icone'] ?></span>
                                        </div>
                                        <h5 class="card-title text-center mb-3"><?= sanitize_output($tool['nome']) ?></h5>
                                        <p class="card-text text-muted text-center"><?= sanitize_output($tool['descricao']) ?></p>
                                        <div class="d-grid">
                                            <a href="<?= $tool['url'] ?>" class="btn btn-primary">Acessar</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>

            <!-- Other Services Section -->
            <div class="row mb-4">
                <div class="col-12">
                    <h3 class="mb-3">Outros Serviços</h3>
                </div>
            </div>

            <div class="row g-4 mb-5">
                <div class="col-md-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <div class="flex-shrink-0">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="currentColor" class="bi bi-mortarboard text-info" viewBox="0 0 16 16">
                                        <path d="M8.211 2.047a.5.5 0 0 0-.422 0l-7.5 3.5a.5.5 0 0 0 .025.917l7.5 3a.5.5 0 0 0 .372 0L14 7.14V13a1 1 0 0 0-1 1v2h3v-2a1 1 0 0 0-1-1V6.739l.686-.275a.5.5 0 0 0 .025-.917z"/>
                                        <path d="M4.176 9.032a.5.5 0 0 0-.656.327l-.5 1.7a.5.5 0 0 0 .294.605l4.5 1.8a.5.5 0 0 0 .372 0l4.5-1.8a.5.5 0 0 0 .294-.605l-.5-1.7a.5.5 0 0 0-.656-.327L8 10.466z"/>
                                    </svg>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h5 class="card-title mb-0">Cursos</h5>
                                </div>
                            </div>
                            <p class="card-text">Aprenda IA generativa do básico ao avançado</p>
                            <a href="#" class="btn btn-outline-secondary disabled">Em breve</a>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <div class="flex-shrink-0">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="currentColor" class="bi bi-person-workspace text-success" viewBox="0 0 16 16">
                                        <path d="M4 16s-1 0-1-1 1-4 5-4 5 3 5 4-1 1-1 1zm4-5.95a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5"/>
                                        <path d="M2 1a2 2 0 0 0-2 2v9.5A1.5 1.5 0 0 0 1.5 14h.653a5.4 5.4 0 0 1 1.066-2H1V3a1 1 0 0 1 1-1h12a1 1 0 0 1 1 1v9h-2.219c.554.654.89 1.373 1.066 2h.653a1.5 1.5 0 0 0 1.5-1.5V3a2 2 0 0 0-2-2z"/>
                                    </svg>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h5 class="card-title mb-0">Consultoria</h5>
                                </div>
                            </div>
                            <p class="card-text">Consultoria personalizada para sua empresa</p>
                            <a href="#" class="btn btn-outline-secondary disabled">Em breve</a>
                        </div>
                    </div>
                </div>
            </div>

            <?php if (!empty($contracts)): ?>
            <div class="row">
                <div class="col-12">
                    <h3 class="mb-3">Seus Contratos Ativos</h3>
                    <div class="card">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Tipo</th>
                                            <th>Vertical</th>
                                            <th>Início</th>
                                            <th>Término</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($contracts as $contract): ?>
                                        <tr>
                                            <td><?= sanitize_output(ucfirst($contract['type'])) ?></td>
                                            <td><?= sanitize_output(VERTICALS[$contract['vertical']]) ?></td>
                                            <td><?= date('d/m/Y', strtotime($contract['start_date'])) ?></td>
                                            <td><?= $contract['end_date'] ? date('d/m/Y', strtotime($contract['end_date'])) : 'Indeterminado' ?></td>
                                            <td><span class="badge bg-success">Ativo</span></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
