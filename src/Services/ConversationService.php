<?php
/**
 * Conversation Service
 *
 * Manages conversations, messages, and file attachments for Canvas interactions
 *
 * @package Sunyata\Services
 */

namespace Sunyata\Services;

use Sunyata\Core\Database;
use Exception;

class ConversationService {
    private static $instance = null;
    private $db;

    private function __construct() {
        $this->db = Database::getInstance();
    }

    public static function getInstance(): self {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Create a new conversation
     *
     * @param int $userId User ID
     * @param int $canvasId Canvas Template ID
     * @return int Conversation ID
     * @throws Exception If creation fails
     */
    public function createConversation(int $userId, int $canvasId): int {
        try {
            $conversationId = $this->db->insert('conversations', [
                'user_id' => $userId,
                'canvas_id' => $canvasId,
                'status' => 'active',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            return (int)$conversationId;

        } catch (Exception $e) {
            error_log('ConversationService::createConversation error: ' . $e->getMessage());
            throw new Exception('Erro ao criar conversa');
        }
    }

    /**
     * Add a message to a conversation
     *
     * @param int $conversationId Conversation ID
     * @param string $role 'user' or 'assistant'
     * @param string $content Message content
     * @param string|null $messageType Optional message type (question, answer, form_submission, context)
     * @return int Message ID
     * @throws Exception If adding message fails
     */
    public function addMessage(
        int $conversationId,
        string $role,
        string $content,
        ?string $messageType = null
    ): int {
        try {
            // Validate role
            if (!in_array($role, ['user', 'assistant'])) {
                throw new Exception('Invalid role. Must be "user" or "assistant"');
            }

            // Validate message type if provided
            $validTypes = ['question', 'answer', 'form_submission', 'context'];
            if ($messageType !== null && !in_array($messageType, $validTypes)) {
                throw new Exception('Invalid message type');
            }

            // Insert message
            $messageId = $this->db->insert('conversation_messages', [
                'conversation_id' => $conversationId,
                'role' => $role,
                'content' => $content,
                'message_type' => $messageType,
                'created_at' => date('Y-m-d H:i:s')
            ]);

            // Update conversation updated_at
            $this->db->update(
                'conversations',
                ['updated_at' => date('Y-m-d H:i:s')],
                'id = :id',
                ['id' => $conversationId]
            );

            // Check if this message contains [RESPOSTA-FINAL] marker
            if ($role === 'assistant' && strpos($content, '[RESPOSTA-FINAL]') !== false) {
                $this->completeConversation($conversationId);
            }

            return (int)$messageId;

        } catch (Exception $e) {
            error_log('ConversationService::addMessage error: ' . $e->getMessage());
            throw new Exception('Erro ao adicionar mensagem');
        }
    }

    /**
     * Get complete conversation with messages and attached files
     *
     * @param int $conversationId Conversation ID
     * @param int $userId User ID (security check - user must own conversation)
     * @return array|null Conversation data or null if not found/not owned
     */
    public function getConversation(int $conversationId, int $userId): ?array {
        try {
            // Get conversation (with ownership check)
            $conversation = $this->db->fetchOne(
                "SELECT * FROM conversations WHERE id = :id AND user_id = :user_id",
                [
                    'id' => $conversationId,
                    'user_id' => $userId
                ]
            );

            if (!$conversation) {
                return null;
            }

            // Get all messages
            $messages = $this->db->fetchAll(
                "SELECT * FROM conversation_messages
                 WHERE conversation_id = :conversation_id
                 ORDER BY created_at ASC",
                ['conversation_id' => $conversationId]
            );

            // Get attached files
            $files = $this->db->fetchAll(
                "SELECT uf.*
                 FROM user_files uf
                 INNER JOIN conversation_files cf ON uf.id = cf.file_id
                 WHERE cf.conversation_id = :conversation_id
                 ORDER BY uf.created_at ASC",
                ['conversation_id' => $conversationId]
            );

            return [
                'conversation' => $conversation,
                'messages' => $messages,
                'files' => $files
            ];

        } catch (Exception $e) {
            error_log('ConversationService::getConversation error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get all conversations for a user
     *
     * @param int $userId User ID
     * @param string|null $status Filter by status (optional)
     * @return array Array of conversations
     */
    public function getUserConversations(int $userId, ?string $status = null): array {
        try {
            $sql = "SELECT c.*, ct.name as canvas_name, ct.slug as canvas_slug
                    FROM conversations c
                    INNER JOIN canvas_templates ct ON c.canvas_id = ct.id
                    WHERE c.user_id = :user_id";

            $params = ['user_id' => $userId];

            if ($status !== null) {
                $sql .= " AND c.status = :status";
                $params['status'] = $status;
            }

            $sql .= " ORDER BY c.updated_at DESC";

            return $this->db->fetchAll($sql, $params);

        } catch (Exception $e) {
            error_log('ConversationService::getUserConversations error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Attach files to a conversation
     *
     * @param int $conversationId Conversation ID
     * @param array $fileIds Array of file IDs
     * @return bool True if attached successfully
     */
    public function attachFiles(int $conversationId, array $fileIds): bool {
        try {
            foreach ($fileIds as $fileId) {
                // Check if already attached (to avoid duplicates)
                $exists = $this->db->fetchOne(
                    "SELECT 1 FROM conversation_files
                     WHERE conversation_id = :conversation_id AND file_id = :file_id",
                    [
                        'conversation_id' => $conversationId,
                        'file_id' => $fileId
                    ]
                );

                if (!$exists) {
                    $this->db->insert('conversation_files', [
                        'conversation_id' => $conversationId,
                        'file_id' => $fileId
                    ]);
                }
            }

            return true;

        } catch (Exception $e) {
            error_log('ConversationService::attachFiles error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Complete a conversation (change status to 'completed')
     *
     * @param int $conversationId Conversation ID
     * @return bool True if completed successfully
     */
    public function completeConversation(int $conversationId): bool {
        try {
            $updated = $this->db->update(
                'conversations',
                [
                    'status' => 'completed',
                    'updated_at' => date('Y-m-d H:i:s')
                ],
                'id = :id',
                ['id' => $conversationId]
            );

            return $updated > 0;

        } catch (Exception $e) {
            error_log('ConversationService::completeConversation error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Archive a conversation (change status to 'archived')
     *
     * @param int $conversationId Conversation ID
     * @param int $userId User ID (security check)
     * @return bool True if archived successfully
     */
    public function archiveConversation(int $conversationId, int $userId): bool {
        try {
            // Verify ownership
            $conversation = $this->db->fetchOne(
                "SELECT id FROM conversations WHERE id = :id AND user_id = :user_id",
                [
                    'id' => $conversationId,
                    'user_id' => $userId
                ]
            );

            if (!$conversation) {
                return false;
            }

            $updated = $this->db->update(
                'conversations',
                [
                    'status' => 'archived',
                    'updated_at' => date('Y-m-d H:i:s')
                ],
                'id = :id',
                ['id' => $conversationId]
            );

            return $updated > 0;

        } catch (Exception $e) {
            error_log('ConversationService::archiveConversation error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Generate automatic title for conversation based on content
     *
     * @param int $conversationId Conversation ID
     * @return string Generated title
     */
    public function generateTitle(int $conversationId): string {
        try {
            // Get first user message (form submission)
            $firstMessage = $this->db->fetchOne(
                "SELECT content FROM conversation_messages
                 WHERE conversation_id = :conversation_id
                 AND role = 'user'
                 ORDER BY created_at ASC
                 LIMIT 1",
                ['conversation_id' => $conversationId]
            );

            if (!$firstMessage) {
                return 'Nova Conversa';
            }

            // Extract a meaningful title (first 50 chars of content)
            $content = $firstMessage['content'];

            // Try to extract something meaningful from JSON form data if present
            if (strpos($content, '{') === 0) {
                $data = json_decode($content, true);
                if ($data && isset($data['descricao_caso'])) {
                    $content = $data['descricao_caso'];
                } elseif ($data && isset($data['descricao'])) {
                    $content = $data['descricao'];
                } elseif ($data) {
                    // Get first non-empty value
                    foreach ($data as $value) {
                        if (is_string($value) && strlen($value) > 10) {
                            $content = $value;
                            break;
                        }
                    }
                }
            }

            // Clean and truncate
            $title = strip_tags($content);
            $title = preg_replace('/\s+/', ' ', $title);
            $title = trim($title);

            if (strlen($title) > 50) {
                $title = substr($title, 0, 47) . '...';
            }

            // Update conversation with generated title
            $this->db->update(
                'conversations',
                ['title' => $title],
                'id = :id',
                ['id' => $conversationId]
            );

            return $title;

        } catch (Exception $e) {
            error_log('ConversationService::generateTitle error: ' . $e->getMessage());
            return 'Nova Conversa';
        }
    }
}
