<?php
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../config/auth.php';

require_login();

// Variáveis de feedback
$feedback = null;
$feedback_type = null;
// Processar POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validar campos
    $profissao = $_POST['profissao'] ?? '';
    $oab = trim($_POST['oab'] ?? '');
    $escritorio = trim($_POST['escritorio'] ?? '');

    $errors = [];

    // Validar profissão
    $profissoes_validas = ['Advogado(a)', 'Estudante de Direito', 'Outro'];
    if (!in_array($profissao, $profissoes_validas)) {
        $errors[] = 'Profissão inválida.';
    }

    // Validar OAB se preenchido
    if (!empty($oab) && !preg_match('/^[0-9]{1,6}-[A-Z]{2}$/', $oab)) {
        $errors[] = 'Formato de OAB inválido. Use o formato 123456-UF (ex: 123456-RJ).';
    }

    // Se não houver erros, processar a solicitação
    if (empty($errors)) {
        // Marcar que o usuário já solicitou acesso
        $_SESSION['requested']['law'] = true;

        // Montar payload
        $payload = [
            'ts' => date('c'),
            'user' => [
                'name' => $_SESSION['user']['name'] ?? null,
                'email' => $_SESSION['user']['email'] ?? null,
                'sub' => $_SESSION['user']['google_id'] ?? null
            ],
            'profissao' => $profissao,
            'oab' => !empty($oab) ? $oab : null,
            'escritorio' => !empty($escritorio) ? $escritorio : null,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? null,
            'ua' => $_SERVER['HTTP_USER_AGENT'] ?? null
        ];

        // Salvar em JSONL
        $storeFile = __DIR__ . '/../../../storage/access-requests-law.jsonl';
        $storeDir = dirname($storeFile);
        if (!is_dir($storeDir)) {
            mkdir($storeDir, 0775, true);
        }

        file_put_contents(
            $storeFile,
            json_encode($payload, JSON_UNESCAPED_UNICODE) . PHP_EOL,
            FILE_APPEND | LOCK_EX
        );

        // Sanitizar campos para e-mail (remover quebras de linha)
        $oab_sanitized = str_replace(["\r", "\n"], '', $oab);
        $escritorio_sanitized = str_replace(["\r", "\n"], '', $escritorio);

        // Montar e-mail
        $to = 'contato@sunyataconsulting.com';
        $subject = '[Portal Sunyata] Solicitação de acesso: Jurídico';

        $body = "Nova solicitação de acesso à vertical Jurídico:\n\n";
        $body .= "Nome: " . ($payload['user']['name'] ?? 'N/A') . "\n";
        $body .= "E-mail: " . ($payload['user']['email'] ?? 'N/A') . "\n";
        $body .= "Google ID: " . ($payload['user']['sub'] ?? 'N/A') . "\n";
        $body .= "Profissão: " . $profissao . "\n";
        $body .= "OAB: " . ($oab_sanitized ?: 'N/A') . "\n";
        $body .= "Escritório: " . ($escritorio_sanitized ?: 'N/A') . "\n";
        $body .= "IP: " . ($payload['ip'] ?? 'N/A') . "\n";
        $body .= "User Agent: " . ($payload['ua'] ?? 'N/A') . "\n";
        $body .= "Timestamp: " . $payload['ts'] . "\n";

        $headers = [
            'Content-Type: text/plain; charset=UTF-8',
            'From: Portal Sunyata <contato@sunyataconsulting.com>'
        ];

        if (!empty($_SESSION['user']['email'])) {
            $headers[] = 'Reply-To: ' . $_SESSION['user']['email'];
        }

        // Enviar e-mail
        $mail_sent = mail($to, $subject, $body, implode("\r\n", $headers));

        // Feedback
        if ($mail_sent) {
            $feedback = 'Solicitação recebida. Você receberá um e-mail quando for aprovada.';
            $feedback_type = 'success';
        } else {
            $feedback = 'Recebemos sua solicitação, mas houve falha no envio de e-mail. Nossa equipe será notificada pelos registros internos.';
            $feedback_type = 'warning';
        }
    } else {
        $feedback = implode('<br>', $errors);
        $feedback_type = 'danger';
    }
}

// Incluir navbar
include __DIR__ . '/../../../src/views/navbar.php';
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitar Acesso - Jurídico | Plataforma Sunyata</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">Solicitar Acesso - Vertical Jurídico</h4>
                    </div>
                    <div class="card-body">
                        <?php if ($feedback): ?>
                            <div class="alert alert-<?php echo htmlspecialchars($feedback_type); ?>" role="alert">
                                <?php echo $feedback; ?>
                            </div>
                        <?php endif; ?>

                        <?php if (isset($_SESSION['requested']['law']) && $_SESSION['requested']['law'] === true): ?>
                            <div class="alert alert-info" role="alert">
                                <strong>Solicitação enviada.</strong> Sua solicitação de acesso está em análise. Você será notificado por e-mail quando for aprovada.
                            </div>
                            <p class="text-muted">
                                Se você não recebeu confirmação ou precisa de acesso urgente, entre em contato conosco em
                                <a href="mailto:contato@sunyataconsulting.com">contato@sunyataconsulting.com</a>.
                            </p>
                        <?php else: ?>
                            <p class="mb-4">
                                Para acessar as ferramentas da vertical Jurídico, preencha o formulário abaixo.
                                Sua solicitação será analisada e você receberá uma notificação por e-mail quando for aprovada.
                            </p>

                            <form method="POST" action="">
                                <div class="mb-3">
                                    <label for="profissao" class="form-label">Profissão <span class="text-danger">*</span></label>
                                    <select class="form-select" id="profissao" name="profissao" required>
                                        <option value="">Selecione...</option>
                                        <option value="Advogado(a)">Advogado(a)</option>
                                        <option value="Estudante de Direito">Estudante de Direito</option>
                                        <option value="Outro">Outro</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label for="oab" class="form-label">Número OAB</label>
                                    <input type="text" class="form-control" id="oab" name="oab"
                                           placeholder="123456-RJ" pattern="[0-9]{1,6}-[A-Z]{2}"
                                           title="Formato: 123456-UF (ex: 123456-RJ)">
                                    <small class="form-text text-muted">Opcional. Formato: 123456-UF (ex: 123456-RJ)</small>
                                </div>

                                <div class="mb-3">
                                    <label for="escritorio" class="form-label">Escritório/Instituição</label>
                                    <input type="text" class="form-control" id="escritorio" name="escritorio"
                                           placeholder="Nome do escritório ou instituição">
                                    <small class="form-text text-muted">Opcional.</small>
                                </div>

                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-primary btn-lg">Enviar Solicitação</button>
                                </div>
                            </form>
                        <?php endif; ?>

                        <hr class="my-4">
                        <div class="text-center">
                            <a href="/dashboard.php" class="btn btn-outline-secondary">Voltar ao Dashboard</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
