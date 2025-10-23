<?php
/**
 * Console Interativa - Exemplo de Implementação com SurveyJS
 *
 * Este arquivo demonstra como:
 * 1. Carregar JSON do Canvas do banco de dados
 * 2. Renderizar com SurveyJS Form Library
 * 3. Processar upload de arquivos
 * 4. Iniciar conversação com Claude
 */

require_once __DIR__ . '/../../../config/session.php';
require_once __DIR__ . '/../../../config/bootstrap.php';

use App\Core\Database;
use App\Services\VerticalManager;

// Verificar autenticação
if (!isset($_SESSION['user_id'])) {
    header('Location: /auth/login.php');
    exit;
}

$db = Database::getInstance();
$verticalManager = new VerticalManager($db);

// Verificar acesso à vertical Jurídico
if (!$verticalManager->hasVerticalAccess($_SESSION['user_id'], 'juridico')) {
    header('Location: /areas/dashboard.php');
    exit;
}

// Carregar Canvas Jurídico do banco (exemplo simplificado)
// Em produção, viria de canvas_templates table
$canvasId = $_GET['canvas_id'] ?? 'juridico-geral';
$canvasJsonPath = __DIR__ . '/../../../config/canvas-templates/' . $canvasId . '.json';

if (!file_exists($canvasJsonPath)) {
    die("Canvas não encontrado");
}

$canvasConfig = json_decode(file_get_contents($canvasJsonPath), true);

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Console Interativa - <?php echo htmlspecialchars($canvasConfig['title']); ?></title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- SurveyJS Form Library (MIT License - FREE) -->
    <link href="https://unpkg.com/survey-core@1.9.123/defaultV2.min.css" rel="stylesheet">
    <script src="https://unpkg.com/survey-core@1.9.123/survey.core.min.js"></script>
    <script src="https://unpkg.com/survey-js-ui@1.9.123/survey-js-ui.min.js"></script>

    <style>
        .console-container {
            display: grid;
            grid-template-columns: 300px 1fr;
            gap: 20px;
            height: calc(100vh - 100px);
            margin-top: 20px;
        }

        .sidebar {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            overflow-y: auto;
        }

        .main-area {
            background: white;
            border-radius: 8px;
            padding: 30px;
            overflow-y: auto;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .file-library h5, .conversations h5 {
            font-size: 14px;
            font-weight: 600;
            text-transform: uppercase;
            color: #6c757d;
            margin-bottom: 15px;
        }

        .file-item, .conversation-item {
            padding: 10px;
            margin-bottom: 8px;
            background: white;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.2s;
            border: 1px solid #dee2e6;
        }

        .file-item:hover, .conversation-item:hover {
            background: #e9ecef;
            transform: translateX(3px);
        }

        .chat-area {
            display: none;
            margin-top: 30px;
        }

        .chat-messages {
            min-height: 300px;
            max-height: 500px;
            overflow-y: auto;
            background: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
        }

        .message {
            margin-bottom: 20px;
            padding: 15px;
            border-radius: 8px;
        }

        .message.user {
            background: #0d6efd;
            color: white;
            margin-left: 20%;
        }

        .message.assistant {
            background: white;
            border: 1px solid #dee2e6;
            margin-right: 20%;
        }

        .message.question {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
        }

        .question-counter {
            display: inline-block;
            background: #ffc107;
            color: #000;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .loading-indicator {
            display: none;
            text-align: center;
            padding: 20px;
        }

        .spinner-border {
            width: 3rem;
            height: 3rem;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-dark bg-primary">
        <div class="container-fluid">
            <span class="navbar-brand">
                <strong>Plataforma Sunyata</strong> - Console Interativa
            </span>
            <a href="/areas/juridico/" class="btn btn-outline-light btn-sm">Voltar</a>
        </div>
    </nav>

    <div class="container-fluid console-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="file-library mb-4">
                <h5>📁 Biblioteca de Arquivos</h5>
                <div id="file-list">
                    <p class="text-muted small">Nenhum arquivo ainda</p>
                </div>
                <button class="btn btn-sm btn-outline-primary w-100 mt-3" id="btn-upload-library">
                    + Adicionar Arquivo
                </button>
            </div>

            <hr>

            <div class="conversations">
                <h5>💬 Conversas</h5>
                <div id="conversation-list">
                    <p class="text-muted small">Nenhuma conversa ainda</p>
                </div>
                <button class="btn btn-sm btn-primary w-100 mt-3" id="btn-new-conversation">
                    + Nova Conversa
                </button>
            </div>
        </div>

        <!-- Main Area -->
        <div class="main-area">
            <h2><?php echo htmlspecialchars($canvasConfig['title']); ?></h2>
            <p class="text-muted"><?php echo htmlspecialchars($canvasConfig['description']); ?></p>

            <!-- SurveyJS Container -->
            <div id="surveyContainer"></div>

            <!-- Chat Area (aparece após submit) -->
            <div class="chat-area" id="chatArea">
                <hr class="my-4">
                <h4>Conversação Interativa</h4>
                <p class="text-muted">A IA pode fazer até 5 perguntas para otimizar o resultado. Você pode responder ou pular.</p>

                <div class="chat-messages" id="chatMessages"></div>

                <div class="loading-indicator" id="loadingIndicator">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Processando...</span>
                    </div>
                    <p class="mt-2 text-muted">Aguarde, processando com IA...</p>
                </div>

                <div class="input-group" id="chatInput" style="display: none;">
                    <input type="text" class="form-control" id="userResponse" placeholder="Digite sua resposta...">
                    <button class="btn btn-primary" id="btnSendResponse">Enviar</button>
                    <button class="btn btn-outline-secondary" id="btnSkipQuestion">Pular Pergunta</button>
                </div>

                <div id="exportArea" style="display: none;">
                    <button class="btn btn-success" id="btnExport">📥 Exportar Conversa</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Configuração SurveyJS
        const surveyJson = <?php echo json_encode($canvasConfig); ?>;

        const survey = new Survey.Model(surveyJson);

        // Upload de arquivos para servidor
        survey.onUploadFiles.add((_, options) => {
            const formData = new FormData();
            options.files.forEach((file) => {
                formData.append('files[]', file);
            });
            formData.append('user_id', <?php echo $_SESSION['user_id']; ?>);
            formData.append('type', 'conversation'); // ou 'library'

            fetch('/api/upload-file.php', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-Token': '<?php echo $_SESSION['csrf_token']; ?>'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Retornar URLs dos arquivos
                    options.callback(
                        options.files.map((file, index) => {
                            return {
                                file: file,
                                content: data.files[index].url // URL do arquivo
                            };
                        })
                    );
                } else {
                    alert('Erro no upload: ' + data.error);
                    options.callback([], data.error);
                }
            })
            .catch(error => {
                console.error('Upload error:', error);
                alert('Erro ao fazer upload dos arquivos');
                options.callback([], error.message);
            });
        });

        // Ao completar o formulário
        survey.onComplete.add((sender) => {
            const formData = sender.data;
            console.log('Dados do formulário:', formData);

            // Iniciar conversa com Claude
            startConversation(formData);
        });

        // Renderizar survey
        survey.render('surveyContainer');

        // Estado da conversa
        let currentConversationId = null;
        let questionCount = 0;
        const maxQuestions = 5;

        // Iniciar conversa com Claude
        async function startConversation(formData) {
            document.getElementById('chatArea').style.display = 'block';
            document.getElementById('loadingIndicator').style.display = 'block';

            try {
                const response = await fetch('/api/chat.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-Token': '<?php echo $_SESSION['csrf_token']; ?>'
                    },
                    body: JSON.stringify({
                        action: 'start',
                        canvas_id: '<?php echo $canvasId; ?>',
                        form_data: formData
                    })
                });

                const data = await response.json();

                if (data.success) {
                    currentConversationId = data.conversation_id;
                    handleClaudeResponse(data.response, data.message_type);
                } else {
                    alert('Erro ao iniciar conversa: ' + data.error);
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Erro ao comunicar com servidor');
            } finally {
                document.getElementById('loadingIndicator').style.display = 'none';
            }
        }

        // Processar resposta do Claude
        function handleClaudeResponse(response, messageType) {
            const messagesDiv = document.getElementById('chatMessages');

            if (messageType === 'question') {
                questionCount++;

                // Exibir pergunta
                const messageHtml = `
                    <div class="message question">
                        <span class="question-counter">PERGUNTA ${questionCount}/${maxQuestions}</span>
                        <div>${escapeHtml(response)}</div>
                    </div>
                `;
                messagesDiv.innerHTML += messageHtml;

                // Mostrar input para resposta
                document.getElementById('chatInput').style.display = 'flex';
                document.getElementById('userResponse').focus();

            } else if (messageType === 'final_response') {
                // Exibir resposta final
                const messageHtml = `
                    <div class="message assistant">
                        <strong>✅ RESPOSTA FINAL</strong>
                        <div class="mt-2">${formatResponse(response)}</div>
                    </div>
                `;
                messagesDiv.innerHTML += messageHtml;

                // Ocultar input, mostrar export
                document.getElementById('chatInput').style.display = 'none';
                document.getElementById('exportArea').style.display = 'block';
            }

            // Scroll para última mensagem
            messagesDiv.scrollTop = messagesDiv.scrollHeight;
        }

        // Enviar resposta do usuário
        document.getElementById('btnSendResponse').addEventListener('click', async () => {
            const userInput = document.getElementById('userResponse').value.trim();

            if (!userInput) {
                alert('Por favor, digite uma resposta');
                return;
            }

            // Exibir mensagem do usuário
            const messagesDiv = document.getElementById('chatMessages');
            messagesDiv.innerHTML += `
                <div class="message user">
                    ${escapeHtml(userInput)}
                </div>
            `;

            // Limpar input e ocultar
            document.getElementById('userResponse').value = '';
            document.getElementById('chatInput').style.display = 'none';
            document.getElementById('loadingIndicator').style.display = 'block';

            try {
                const response = await fetch('/api/chat.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-Token': '<?php echo $_SESSION['csrf_token']; ?>'
                    },
                    body: JSON.stringify({
                        action: 'continue',
                        conversation_id: currentConversationId,
                        user_message: userInput
                    })
                });

                const data = await response.json();

                if (data.success) {
                    handleClaudeResponse(data.response, data.message_type);
                } else {
                    alert('Erro: ' + data.error);
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Erro ao comunicar com servidor');
            } finally {
                document.getElementById('loadingIndicator').style.display = 'none';
            }
        });

        // Pular pergunta
        document.getElementById('btnSkipQuestion').addEventListener('click', async () => {
            document.getElementById('chatInput').style.display = 'none';
            document.getElementById('loadingIndicator').style.display = 'block';

            try {
                const response = await fetch('/api/chat.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-Token': '<?php echo $_SESSION['csrf_token']; ?>'
                    },
                    body: JSON.stringify({
                        action: 'skip',
                        conversation_id: currentConversationId
                    })
                });

                const data = await response.json();

                if (data.success) {
                    handleClaudeResponse(data.response, data.message_type);
                }
            } catch (error) {
                console.error('Error:', error);
            } finally {
                document.getElementById('loadingIndicator').style.display = 'none';
            }
        });

        // Export conversa
        document.getElementById('btnExport').addEventListener('click', () => {
            window.location.href = `/api/export-conversation.php?id=${currentConversationId}&format=txt`;
        });

        // Utilities
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        function formatResponse(text) {
            // Converter markdown básico para HTML
            return text
                .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
                .replace(/\*(.*?)\*/g, '<em>$1</em>')
                .replace(/\n/g, '<br>');
        }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
