<?php
/**
 * Canvas Jurídico v2 - SurveyJS
 * Versão moderna com SurveyJS + Upload de Arquivos
 */

require_once __DIR__ . '/../../../vendor/autoload.php';
require_once __DIR__ . '/../../../config/config.php';

session_name(SESSION_NAME);
session_start();

use Sunyata\Core\Database;

require_login();

// Verificar acesso à vertical
if (!isset($_SESSION['user']['selected_vertical'])) {
    $_SESSION['error'] = 'Por favor, complete o onboarding primeiro';
    redirect(BASE_URL . '/onboarding-step1.php');
}

$user_vertical = $_SESSION['user']['selected_vertical'];
$is_demo = $_SESSION['user']['is_demo'] ?? false;
$is_admin = ($_SESSION['user']['access_level'] ?? 'guest') === 'admin';

// Verificar se tem acesso (vertical juridico OU usuário demo)
if ($user_vertical !== 'juridico' && !$is_demo && !$is_admin) {
    $_SESSION['error'] = 'Você não tem acesso a esta ferramenta';
    redirect(BASE_URL . '/dashboard.php');
}

// Buscar configuração do canvas no banco
$db = Database::getInstance();
$canvas = $db->fetchOne("
    SELECT id, slug, name, form_config, system_prompt, user_prompt_template, max_questions
    FROM canvas_templates
    WHERE slug = 'juridico-geral' AND is_active = 1
");

if (!$canvas) {
    $_SESSION['error'] = 'Canvas não encontrado';
    redirect(BASE_URL . '/dashboard.php');
}

// Decodificar form_config JSON
$formConfig = json_decode($canvas['form_config'], true);

$pageTitle = $canvas['name'];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= sanitize_output($pageTitle) ?> - <?= APP_NAME ?></title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- SurveyJS CSS -->
    <link href="https://unpkg.com/survey-core/defaultV2.min.css" rel="stylesheet">

    <style>
        body {
            background: linear-gradient(135deg, #1a365d 0%, #2d5a87 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .container-custom {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            padding: 40px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.2);
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #e9ecef;
        }

        .header h1 {
            color: #1a365d;
            margin-bottom: 10px;
        }

        .header p {
            color: #6c757d;
            font-size: 1.1rem;
        }

        #surveyContainer {
            margin-top: 20px;
            width: 100% !important;
            max-width: 100% !important;
        }

        /* Fix SurveyJS container width */
        #surveyContainer .sd-root-modern {
            width: 100% !important;
            max-width: 100% !important;
        }

        #surveyContainer .sd-body {
            width: 100% !important;
            max-width: 100% !important;
        }

        #surveyContainer .sd-page {
            width: 100% !important;
            max-width: 100% !important;
        }

        /* Hide duplicate survey title and description */
        #surveyContainer .sd-title,
        #surveyContainer .sd-description {
            display: none !important;
        }

        /* Fix progress bar to Portuguese */
        #surveyContainer .sd-progress__text {
            font-size: 0 !important;
        }

        #surveyContainer .sd-progress__text::after {
            content: "Respondidas 0 de 7 perguntas";
            font-size: 14px;
        }

        #resultContainer {
            display: none;
            margin-top: 30px;
            padding: 30px;
            background: #f8f9fa;
            border-radius: 10px;
            border-left: 5px solid #28a745;
        }

        #resultContainer h3 {
            color: #28a745;
            margin-bottom: 20px;
        }

        #claudeResponse {
            background: white;
            padding: 20px;
            border-radius: 8px;
            line-height: 1.8;
            white-space: pre-wrap;
            font-family: 'Georgia', serif;
        }

        .loading {
            text-align: center;
            padding: 40px;
        }

        .loading .spinner-border {
            width: 3rem;
            height: 3rem;
            color: #1a365d;
        }

        .btn-voltar {
            margin-top: 20px;
        }

        .alert-custom {
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container-custom">
        <div class="header">
            <h1><?= sanitize_output($canvas['name']) ?></h1>
            <p>Análise jurídica assistida por IA com upload de documentos</p>
        </div>

        <!-- Survey Container -->
        <div id="surveyContainer"></div>

        <!-- Loading State -->
        <div id="loadingContainer" class="loading" style="display: none;">
            <div class="spinner-border" role="status">
                <span class="visually-hidden">Processando...</span>
            </div>
            <p class="mt-3">Analisando com Claude AI... Isso pode levar alguns segundos.</p>
        </div>

        <!-- Result Container -->
        <div id="resultContainer">
            <h3>📋 Análise Jurídica - Claude AI</h3>
            <div id="claudeResponse"></div>
            <button class="btn btn-primary btn-voltar" onclick="window.location.reload()">
                ← Nova Análise
            </button>
            <a href="<?= BASE_URL ?>/areas/juridico/" class="btn btn-secondary btn-voltar">
                Voltar ao Dashboard
            </a>
        </div>

        <!-- Error Container -->
        <div id="errorContainer" style="display: none;">
            <div class="alert alert-danger alert-custom">
                <h4 class="alert-heading">Erro ao processar</h4>
                <p id="errorMessage"></p>
                <hr>
                <button class="btn btn-danger" onclick="window.location.reload()">Tentar Novamente</button>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- SurveyJS -->
    <script src="https://unpkg.com/survey-core/survey.core.min.js"></script>
    <script src="https://unpkg.com/survey-js-ui/survey-js-ui.min.js"></script>

    <script>
        console.log('Script started');

        // Verificar se SurveyJS carregou
        if (typeof Survey === 'undefined') {
            console.error('SurveyJS library not loaded!');
            document.getElementById('surveyContainer').innerHTML = '<div class="alert alert-danger">Erro: Biblioteca SurveyJS não carregou. Verifique sua conexão com internet.</div>';
        } else {
            console.log('SurveyJS loaded OK');

            try {
                // Configuração do formulário SurveyJS do banco
                const surveyJson = <?= json_encode($formConfig) ?>;
                console.log('Survey config loaded:', surveyJson);

                // Criar survey
                const survey = new Survey.Model(surveyJson);
                console.log('Survey model created');

        // Configurar upload de arquivos
        survey.onUploadFiles.add(function (sender, options) {
            const formData = new FormData();
            options.files.forEach(file => {
                formData.append('file', file);
            });

            fetch('<?= BASE_URL ?>/api/canvas/upload-file.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    options.callback('error', [{ file: options.files[0], error: data.error }]);
                } else {
                    options.callback('success', [data.file]);
                }
            })
            .catch(error => {
                options.callback('error', [{ file: options.files[0], error: error.message }]);
            });
        });

        // Aplicar tema
        survey.applyTheme({
            "cssVariables": {
                "--sjs-primary-backcolor": "#1a365d",
                "--sjs-primary-backcolor-dark": "#0f1f3d",
                "--sjs-primary-backcolor-light": "#2d5a87"
            }
        });

        // Renderizar no container
        survey.render(document.getElementById("surveyContainer"));

        // Handler quando completar
        survey.onComplete.add(async function (sender) {
            const formData = sender.data;
            console.log('Form submitted:', formData);

            // Mostrar loading
            document.getElementById('surveyContainer').style.display = 'none';
            document.getElementById('loadingContainer').style.display = 'block';

            try {
                // Enviar para backend
                const response = await fetch('<?= BASE_URL ?>/api/canvas/submit.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        canvas_id: <?= $canvas['id'] ?>,
                        form_data: formData
                    })
                });

                const result = await response.json();

                // Esconder loading
                document.getElementById('loadingContainer').style.display = 'none';

                if (result.success) {
                    // Mostrar resultado
                    document.getElementById('claudeResponse').textContent = result.response;
                    document.getElementById('resultContainer').style.display = 'block';
                } else {
                    // Mostrar erro
                    document.getElementById('errorMessage').textContent = result.error || 'Erro desconhecido';
                    document.getElementById('errorContainer').style.display = 'block';
                }

            } catch (error) {
                console.error('Error:', error);
                document.getElementById('loadingContainer').style.display = 'none';
                document.getElementById('errorMessage').textContent = 'Erro de conexão: ' + error.message;
                document.getElementById('errorContainer').style.display = 'block';
            }
        });

            } catch (error) {
                console.error('Error initializing survey:', error);
                document.getElementById('surveyContainer').innerHTML = '<div class="alert alert-danger">Erro ao inicializar formulário: ' + error.message + '</div>';
            }
        }
    </script>
</body>
</html>
