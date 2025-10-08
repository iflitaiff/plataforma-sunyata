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

            <!-- Ferramentas Section -->
            <div class="row mb-4">
                <div class="col-12">
                    <h3 class="mb-3">Ferramentas</h3>
                </div>
            </div>

            <div class="row g-4 mb-5">
                <div class="col-md-3">
                    <div class="card h-100 border-primary">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <div class="flex-shrink-0">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="currentColor" class="bi bi-book text-primary" viewBox="0 0 16 16">
                                        <path d="M1 2.828c.885-.37 2.154-.769 3.388-.893 1.33-.134 2.458.063 3.112.752v9.746c-.935-.53-2.12-.603-3.213-.493-1.18.12-2.37.461-3.287.811zm7.5-.141c.654-.689 1.782-.886 3.112-.752 1.234.124 2.503.523 3.388.893v9.923c-.918-.35-2.107-.692-3.287-.81-1.094-.111-2.278-.039-3.213.492zM8 1.783C7.015.936 5.587.81 4.287.94c-1.514.153-3.042.672-3.994 1.105A.5.5 0 0 0 0 2.5v11a.5.5 0 0 0 .707.455c.882-.4 2.303-.881 3.68-1.02 1.409-.142 2.59.087 3.223.877a.5.5 0 0 0 .78 0c.633-.79 1.814-1.019 3.222-.877 1.378.139 2.8.62 3.681 1.02A.5.5 0 0 0 16 13.5v-11a.5.5 0 0 0-.293-.455c-.952-.433-2.48-.952-3.994-1.105C10.413.809 8.985.936 8 1.783"/>
                                    </svg>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h5 class="card-title mb-0">Dicionário de Prompts</h5>
                                </div>
                            </div>
                            <p class="card-text">Acesse centenas de templates prontos para uso</p>
                            <a href="<?= BASE_URL ?>/dicionario.php" class="btn btn-primary">Acessar</a>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card h-100 border-primary">
                        <div class="card-body">
                            <div class="text-center mb-3">
                                <span style="font-size: 32px;">📚</span>
                            </div>
                            <h5 class="card-title mb-3">Canvas de Delimitação - Docentes</h5>
                            <p class="card-text">Crie prompts estruturados para atividades docentes</p>
                            <a href="<?= BASE_URL ?>/ferramentas/canvas-docente.html" class="btn btn-primary">Acessar</a>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card h-100 border-primary">
                        <div class="card-body">
                            <div class="text-center mb-3">
                                <span style="font-size: 32px;">⚖️</span>
                            </div>
                            <h5 class="card-title mb-3">Canvas de Delimitação - Jurídico</h5>
                            <p class="card-text">Transforme demandas jurídicas em instruções precisas</p>
                            <a href="<?= BASE_URL ?>/ferramentas/canvas-juridico.html" class="btn btn-primary">Acessar</a>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card h-100 border-primary">
                        <div class="card-body">
                            <div class="text-center mb-3">
                                <span style="font-size: 32px;">🔬</span>
                            </div>
                            <h5 class="card-title mb-3">Canvas de Delimitação - Pesquisa</h5>
                            <p class="card-text">Estruture tarefas de pesquisa acadêmica</p>
                            <a href="<?= BASE_URL ?>/ferramentas/canvas-pesquisa.html" class="btn btn-primary">Acessar</a>
                        </div>
                    </div>
                </div>
            </div>

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
