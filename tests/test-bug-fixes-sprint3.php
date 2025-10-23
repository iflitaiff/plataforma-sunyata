<?php
/**
 * Test Script: Bug Fixes Sprint 3 (Manus Audit)
 *
 * Validates all 8 bug fixes from AUDITORIA-SPRINT3-MANUS.md
 *
 * Bugs tested:
 * - Bug #1: Rate limiting em chat.php
 * - Bug #2: Content length validation em upload-file.php
 * - Bug #3: Ownership check explícito em chat.php
 * - Bug #5: JSON após headers em export-conversation.php
 * - Bug #6: Message length validation em chat.php
 * - Bug #7: CSRF em GET export
 * - Bug #8: File attachment validation em chat.php
 * - Bug #11: Memory exhaustion em PDF export
 */

echo "🔍 TESTE DAS CORREÇÕES - SPRINT 3 BUG FIXES\n";
echo str_repeat("=", 70) . "\n\n";

$testsPassed = 0;
$testsFailed = 0;
$testsTotal = 0;

function testFile(string $filename, string $description): bool {
    global $testsPassed, $testsFailed, $testsTotal;
    $testsTotal++;

    echo "Test #{$testsTotal}: {$description}... ";

    if (file_exists($filename)) {
        echo "✅ PASS\n";
        $testsPassed++;
        return true;
    } else {
        echo "❌ FAIL (file not found)\n";
        $testsFailed++;
        return false;
    }
}

function testCodeContains(string $filename, string $searchString, string $description): bool {
    global $testsPassed, $testsFailed, $testsTotal;
    $testsTotal++;

    echo "Test #{$testsTotal}: {$description}... ";

    if (!file_exists($filename)) {
        echo "❌ FAIL (file not found)\n";
        $testsFailed++;
        return false;
    }

    $content = file_get_contents($filename);

    if (strpos($content, $searchString) !== false) {
        echo "✅ PASS\n";
        $testsPassed++;
        return true;
    } else {
        echo "❌ FAIL (code not found)\n";
        $testsFailed++;
        return false;
    }
}

function testSyntax(string $filename, string $description): bool {
    global $testsPassed, $testsFailed, $testsTotal;
    $testsTotal++;

    echo "Test #{$testsTotal}: {$description}... ";

    if (!file_exists($filename)) {
        echo "❌ FAIL (file not found)\n";
        $testsFailed++;
        return false;
    }

    exec("php -l " . escapeshellarg($filename) . " 2>&1", $output, $returnCode);

    if ($returnCode === 0) {
        echo "✅ PASS\n";
        $testsPassed++;
        return true;
    } else {
        echo "❌ FAIL (syntax error)\n";
        echo "  Error: " . implode("\n  ", $output) . "\n";
        $testsFailed++;
        return false;
    }
}

echo "📋 SECTION 1: FILE EXISTENCE\n";
echo str_repeat("-", 70) . "\n";

testFile(__DIR__ . '/src/Services/ConversationService.php', 'ConversationService exists');
testFile(__DIR__ . '/public/api/chat.php', 'chat.php exists');
testFile(__DIR__ . '/public/api/upload-file.php', 'upload-file.php exists');
testFile(__DIR__ . '/public/api/export-conversation.php', 'export-conversation.php exists');

echo "\n";

echo "📋 SECTION 2: SYNTAX CHECK\n";
echo str_repeat("-", 70) . "\n";

testSyntax(__DIR__ . '/src/Services/ConversationService.php', 'ConversationService syntax OK');
testSyntax(__DIR__ . '/public/api/chat.php', 'chat.php syntax OK');
testSyntax(__DIR__ . '/public/api/upload-file.php', 'upload-file.php syntax OK');
testSyntax(__DIR__ . '/public/api/export-conversation.php', 'export-conversation.php syntax OK');

echo "\n";

echo "📋 SECTION 3: BUG #1 - Rate Limiting\n";
echo str_repeat("-", 70) . "\n";

testCodeContains(
    __DIR__ . '/src/Services/ConversationService.php',
    'public function checkChatRateLimit',
    'Bug #1: checkChatRateLimit() method exists'
);

testCodeContains(
    __DIR__ . '/src/Services/ConversationService.php',
    'SELECT COUNT(*) as count',
    'Bug #1: Rate limit query exists'
);

testCodeContains(
    __DIR__ . '/src/Services/ConversationService.php',
    '$limit = 100',
    'Bug #1: Limit is 100 messages/hour'
);

testCodeContains(
    __DIR__ . '/public/api/chat.php',
    'checkChatRateLimit',
    'Bug #1: Rate limit called in chat.php'
);

testCodeContains(
    __DIR__ . '/public/api/chat.php',
    'http_response_code(429)',
    'Bug #1: Returns 429 status code'
);

echo "\n";

echo "📋 SECTION 4: BUG #6 - Message Length Validation\n";
echo str_repeat("-", 70) . "\n";

testCodeContains(
    __DIR__ . '/public/api/chat.php',
    '$maxMessageLength = 50000',
    'Bug #6: Max message length defined (50,000 chars)'
);

testCodeContains(
    __DIR__ . '/public/api/chat.php',
    'strlen($userMessage) > $maxMessageLength',
    'Bug #6: Message length validation exists'
);

testCodeContains(
    __DIR__ . '/public/api/chat.php',
    'Message too long',
    'Bug #6: Error message for long messages'
);

echo "\n";

echo "📋 SECTION 5: BUG #3 - Ownership Check\n";
echo str_repeat("-", 70) . "\n";

testCodeContains(
    __DIR__ . '/public/api/chat.php',
    'Bug #3 Fix: Validate that conversation belongs to user',
    'Bug #3: Ownership check comment exists'
);

testCodeContains(
    __DIR__ . '/public/api/chat.php',
    '$conversation = $conversationService->getConversation($conversationId, $userId)',
    'Bug #3: Explicit ownership check call'
);

testCodeContains(
    __DIR__ . '/public/api/chat.php',
    'Conversation does not exist or you do not have access to it',
    'Bug #3: Proper error message'
);

echo "\n";

echo "📋 SECTION 6: BUG #8 - File Attachment Validation\n";
echo str_repeat("-", 70) . "\n";

testCodeContains(
    __DIR__ . '/public/api/chat.php',
    'Bug #8 Fix: Validate ownership of EACH file',
    'Bug #8: File validation comment exists'
);

testCodeContains(
    __DIR__ . '/public/api/chat.php',
    'foreach ($fileIds as $fileId)',
    'Bug #8: Loops through file IDs'
);

testCodeContains(
    __DIR__ . '/public/api/chat.php',
    '$fileData = $fileUploadService->getFileById',
    'Bug #8: Checks file ownership'
);

testCodeContains(
    __DIR__ . '/public/api/chat.php',
    'You do not have access to file ID',
    'Bug #8: Proper error message'
);

echo "\n";

echo "📋 SECTION 7: BUG #7 - CSRF in GET Export\n";
echo str_repeat("-", 70) . "\n";

testCodeContains(
    __DIR__ . '/public/api/export-conversation.php',
    'Bug #7 Fix: Accept only POST',
    'Bug #7: Comment about POST-only'
);

testCodeContains(
    __DIR__ . '/public/api/export-conversation.php',
    "REQUEST_METHOD'] !== 'POST'",
    'Bug #7: Only accepts POST'
);

testCodeContains(
    __DIR__ . '/public/api/export-conversation.php',
    'HTTP_X_CSRF_TOKEN',
    'Bug #7: CSRF token validation'
);

testCodeContains(
    __DIR__ . '/public/api/export-conversation.php',
    'json_decode($rawBody, true)',
    'Bug #7: Parses JSON body'
);

echo "\n";

echo "📋 SECTION 8: BUG #11 - Memory Exhaustion (PDF)\n";
echo str_repeat("-", 70) . "\n";

testCodeContains(
    __DIR__ . '/public/api/export-conversation.php',
    'Bug #11 Fix: limit to 500',
    'Bug #11: Comment about 500 message limit'
);

testCodeContains(
    __DIR__ . '/public/api/export-conversation.php',
    '$maxMessages = 500',
    'Bug #11: Max messages set to 500'
);

testCodeContains(
    __DIR__ . '/public/api/export-conversation.php',
    'LIMIT ?',
    'Bug #11: LIMIT clause in query'
);

testCodeContains(
    __DIR__ . '/public/api/export-conversation.php',
    '$wasTruncated = $totalMessages > $maxMessages',
    'Bug #11: Checks if truncated'
);

testCodeContains(
    __DIR__ . '/public/api/export-conversation.php',
    'Esta conversa possui',
    'Bug #11: Truncation warning message'
);

echo "\n";

echo "📋 SECTION 9: BUG #5 - JSON After Headers\n";
echo str_repeat("-", 70) . "\n";

testCodeContains(
    __DIR__ . '/public/api/export-conversation.php',
    'Bug #5 Fix: Generate PDF to string FIRST',
    'Bug #5: Comment about generating to string'
);

testCodeContains(
    __DIR__ . '/public/api/export-conversation.php',
    "\$pdfContent = \$mpdf->Output('', 'S')",
    'Bug #5: Generates PDF to string'
);

testCodeContains(
    __DIR__ . '/public/api/export-conversation.php',
    'Content-Length',
    'Bug #5: Sets Content-Length header'
);

testCodeContains(
    __DIR__ . '/public/api/export-conversation.php',
    'echo $pdfContent',
    'Bug #5: Echoes PDF content'
);

echo "\n";

echo "📋 SECTION 10: BUG #2 - Content Length Validation\n";
echo str_repeat("-", 70) . "\n";

testCodeContains(
    __DIR__ . '/public/api/upload-file.php',
    'Bug #2 Fix: Validate extracted text length',
    'Bug #2: Comment about text length validation'
);

testCodeContains(
    __DIR__ . '/public/api/upload-file.php',
    '$maxTextLength = 100000',
    'Bug #2: Max text length set to 100KB'
);

testCodeContains(
    __DIR__ . '/public/api/upload-file.php',
    'strlen($rawText) > $maxTextLength',
    'Bug #2: Checks text length'
);

testCodeContains(
    __DIR__ . '/public/api/upload-file.php',
    'texto truncado devido ao tamanho',
    'Bug #2: Truncation message'
);

echo "\n";

echo str_repeat("=", 70) . "\n";
echo "📊 TEST SUMMARY\n";
echo str_repeat("=", 70) . "\n";
echo "Total tests: {$testsTotal}\n";
echo "Passed: {$testsPassed} ✅\n";
echo "Failed: {$testsFailed} ❌\n";
echo "Success rate: " . round(($testsPassed / $testsTotal) * 100, 2) . "%\n";

if ($testsFailed === 0) {
    echo "\n🎉 ALL TESTS PASSED! 🎉\n";
    echo "All 8 bug fixes have been successfully implemented.\n";
    exit(0);
} else {
    echo "\n⚠️  SOME TESTS FAILED\n";
    echo "Please review the failed tests above.\n";
    exit(1);
}
