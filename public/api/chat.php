<?php
/**
 * API Endpoint: Chat com IA
 *
 * Envia mensagem para Claude AI com contexto de documentos anexados
 *
 * Method: POST
 * Content-Type: application/json
 * Headers Required: X-CSRF-Token
 *
 * @return JSON
 */

declare(strict_types=1);

require_once __DIR__ . '/../../config/config.php';

use App\Services\ConversationService;
use App\AI\ClaudeService;

// Start session for authentication and CSRF
session_start();

// Set JSON response header
header('Content-Type: application/json');

try {
    // 1. Validate HTTP method
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode([
            'success' => false,
            'error' => 'Method not allowed',
            'message' => 'Only POST requests are accepted'
        ]);
        exit;
    }

    // 2. Validate authentication
    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'error' => 'Unauthorized',
            'message' => 'User not authenticated'
        ]);
        exit;
    }

    // 3. Validate CSRF token
    $csrfToken = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    if (empty($csrfToken) || !isset($_SESSION['csrf_token']) || $csrfToken !== $_SESSION['csrf_token']) {
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'error' => 'CSRF validation failed',
            'message' => 'Invalid or missing CSRF token'
        ]);
        exit;
    }

    // 4. Parse JSON body
    $rawBody = file_get_contents('php://input');
    $data = json_decode($rawBody, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Invalid JSON',
            'message' => 'Request body must be valid JSON'
        ]);
        exit;
    }

    // 5. Validate required fields
    if (!isset($data['message']) || empty(trim($data['message']))) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Missing message',
            'message' => 'The "message" field is required and cannot be empty'
        ]);
        exit;
    }

    $userMessage = trim($data['message']);
    $conversationId = isset($data['conversation_id']) ? (int) $data['conversation_id'] : null;
    $fileIds = isset($data['file_ids']) && is_array($data['file_ids']) ? $data['file_ids'] : [];

    $userId = (int) $_SESSION['user_id'];

    // Get service instances
    $conversationService = ConversationService::getInstance();
    $claudeService = new ClaudeService();

    // 6. Create or validate conversation
    if ($conversationId === null) {
        // Create new conversation
        $createResult = $conversationService->createConversation($userId, 'Nova conversa');

        if (!$createResult['success']) {
            http_response_code(500);
            echo json_encode($createResult);
            exit;
        }

        $conversationId = $createResult['conversation_id'];
    } else {
        // Validate that conversation belongs to user
        // This is done implicitly by ConversationService methods with ownership checks
    }

    // 7. Attach files to conversation (if provided)
    if (!empty($fileIds)) {
        $attachResult = $conversationService->attachFiles($conversationId, $userId, $fileIds);

        if (!$attachResult['success']) {
            // Log but don't fail - some files might not be accessible
            error_log("File attachment warning for conversation {$conversationId}: " . $attachResult['message']);
        }
    }

    // 8. Add user message to conversation
    $addUserMessageResult = $conversationService->addMessage(
        $conversationId,
        'user',
        $userMessage,
        $userId
    );

    if (!$addUserMessageResult['success']) {
        http_response_code(400);
        echo json_encode($addUserMessageResult);
        exit;
    }

    $userMessageId = $addUserMessageResult['message_id'];

    // 9. Generate AI response with context
    $aiResult = $claudeService->generateWithContext($userMessage, $fileIds, $userId);

    if (!$aiResult['success']) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'AI generation failed',
            'message' => $aiResult['message'] ?? 'Failed to generate AI response'
        ]);
        exit;
    }

    $aiResponse = $aiResult['response'];

    // 10. Add AI response to conversation
    $addAiMessageResult = $conversationService->addMessage(
        $conversationId,
        'assistant',
        $aiResponse,
        $userId
    );

    if (!$addAiMessageResult['success']) {
        http_response_code(500);
        echo json_encode($addAiMessageResult);
        exit;
    }

    $aiMessageId = $addAiMessageResult['message_id'];

    // 11. Generate conversation title if it's the first exchange
    $title = null;
    $titleResult = $conversationService->generateTitle($conversationId);
    if ($titleResult['success']) {
        $title = $titleResult['title'];
    }

    // 12. Return success response
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'conversation_id' => $conversationId,
        'user_message_id' => $userMessageId,
        'ai_message_id' => $aiMessageId,
        'response' => $aiResponse,
        'title' => $title
    ]);

} catch (Exception $e) {
    // Log unexpected errors
    error_log('API Error (chat.php): ' . $e->getMessage());
    error_log('Stack trace: ' . $e->getTraceAsString());

    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Internal server error',
        'message' => 'An unexpected error occurred while processing your request'
    ]);
}
