<?php
/**
 * API: File Upload for Canvas
 * Processa upload de arquivos do SurveyJS
 */

require_once __DIR__ . '/../../../vendor/autoload.php';
require_once __DIR__ . '/../../../config/config.php';

session_name(SESSION_NAME);
session_start();

use Sunyata\Services\FileUploadService;

// Headers
header('Content-Type: application/json; charset=utf-8');

// Verificar autenticação
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Não autenticado']);
    exit;
}

// Verificar método
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método não permitido']);
    exit;
}

// Verificar se tem arquivo
if (!isset($_FILES['file'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Nenhum arquivo enviado']);
    exit;
}

try {
    $fileService = new FileUploadService();

    // Upload do arquivo
    $result = $fileService->uploadFile(
        file: $_FILES['file'],
        userId: $_SESSION['user_id'],
        vertical: $_SESSION['user']['selected_vertical'] ?? 'juridico',
        context: 'canvas_upload'
    );

    if ($result['success']) {
        // Formato esperado pelo SurveyJS
        echo json_encode([
            'file' => [
                'name' => $result['file_name'],
                'type' => $result['mime_type'],
                'content' => $result['file_id'], // SurveyJS armazena ID no form data
            ]
        ]);
    } else {
        http_response_code(400);
        echo json_encode(['error' => $result['error'] ?? 'Erro ao fazer upload']);
    }

} catch (Exception $e) {
    error_log('File upload error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Erro interno do servidor']);
}
