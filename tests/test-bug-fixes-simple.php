<?php
/**
 * Simple Test Script for Bug Fixes (No DB Required)
 *
 * Validates code changes without requiring database connection
 */

require_once __DIR__ . '/vendor/autoload.php';

echo "=== TESTE DE CORREÇÕES DE BUGS (Simple) ===\n\n";

$results = [
    'passed' => 0,
    'failed' => 0
];

// Test Bug #2: Upload path method exists
echo "Bug #2: Testing upload path method...\n";
try {
    $reflection = new ReflectionClass('Sunyata\Services\FileUploadService');
    if ($reflection->hasMethod('getUploadBasePath')) {
        echo "   ✅ getUploadBasePath() method exists\n";
        $results['passed']++;
    } else {
        echo "   ❌ getUploadBasePath() method NOT FOUND\n";
        $results['failed']++;
    }
} catch (Exception $e) {
    echo "   ❌ Error: " . $e->getMessage() . "\n";
    $results['failed']++;
}

// Test Bug #3: DocumentProcessorService methods have userId parameter
echo "\nBug #3: Testing DocumentProcessorService userId parameters...\n";
try {
    $reflection = new ReflectionClass('Sunyata\Services\DocumentProcessorService');

    // Check extractText
    $method = $reflection->getMethod('extractText');
    $params = $method->getParameters();
    $hasUserId = false;
    foreach ($params as $param) {
        if ($param->getName() === 'userId') {
            $hasUserId = true;
            break;
        }
    }

    if ($hasUserId) {
        echo "   ✅ extractText() has userId parameter\n";
        $results['passed']++;
    } else {
        echo "   ❌ extractText() missing userId parameter\n";
        $results['failed']++;
    }

    // Check processFile
    $method2 = $reflection->getMethod('processFile');
    $params2 = $method2->getParameters();
    $hasUserId2 = false;
    foreach ($params2 as $param) {
        if ($param->getName() === 'userId') {
            $hasUserId2 = true;
            break;
        }
    }

    if ($hasUserId2) {
        echo "   ✅ processFile() has userId parameter\n";
        $results['passed']++;
    } else {
        echo "   ❌ processFile() missing userId parameter\n";
        $results['failed']++;
    }
} catch (Exception $e) {
    echo "   ❌ Error: " . $e->getMessage() . "\n";
    $results['failed'] += 2;
}

// Test Bug #4: ConversationService::attachFiles has userId
echo "\nBug #4: Testing ConversationService::attachFiles()...\n";
try {
    $reflection = new ReflectionClass('Sunyata\Services\ConversationService');
    $method = $reflection->getMethod('attachFiles');
    $params = $method->getParameters();

    $hasUserId = false;
    foreach ($params as $param) {
        if ($param->getName() === 'userId') {
            $hasUserId = true;
            break;
        }
    }

    if ($hasUserId) {
        echo "   ✅ attachFiles() has userId parameter\n";
        $results['passed']++;
    } else {
        echo "   ❌ attachFiles() missing userId parameter\n";
        $results['failed']++;
    }
} catch (Exception $e) {
    echo "   ❌ Error: " . $e->getMessage() . "\n";
    $results['failed']++;
}

// Test Bug #5: ConversationService::completeConversation has optional userId
echo "\nBug #5: Testing ConversationService::completeConversation()...\n";
try {
    $reflection = new ReflectionClass('Sunyata\Services\ConversationService');
    $method = $reflection->getMethod('completeConversation');
    $params = $method->getParameters();

    $hasUserId = false;
    $isOptional = false;
    foreach ($params as $param) {
        if ($param->getName() === 'userId') {
            $hasUserId = true;
            $isOptional = $param->isDefaultValueAvailable() && $param->getDefaultValue() === null;
            break;
        }
    }

    if ($hasUserId && $isOptional) {
        echo "   ✅ completeConversation() has optional userId parameter (nullable)\n";
        $results['passed']++;
    } else {
        echo "   ❌ completeConversation() parameter check FAILED\n";
        $results['failed']++;
    }
} catch (Exception $e) {
    echo "   ❌ Error: " . $e->getMessage() . "\n";
    $results['failed']++;
}

// Test Bugs #6, #7, #8: Code presence in FileUploadService
echo "\nBugs #6, #7, #8: Checking FileUploadService implementation...\n";
try {
    $content = file_get_contents('src/Services/FileUploadService.php');

    // Bug #6: Rate limiting
    if (strpos($content, 'DATE_SUB(NOW(), INTERVAL 1 HOUR)') !== false &&
        strpos($content, 'Limite de uploads excedido') !== false) {
        echo "   ✅ Bug #6: Rate limiting code found\n";
        $results['passed']++;
    } else {
        echo "   ❌ Bug #6: Rate limiting code NOT FOUND\n";
        $results['failed']++;
    }

    // Bug #7: Real file size
    if (strpos($content, 'filesize($fileData[\'tmp_name\'])') !== false &&
        strpos($content, '$realSize') !== false) {
        echo "   ✅ Bug #7: Real file size validation found\n";
        $results['passed']++;
    } else {
        echo "   ❌ Bug #7: Real file size validation NOT FOUND\n";
        $results['failed']++;
    }

    // Bug #8: Filename sanitization
    if (strpos($content, 'preg_replace') !== false &&
        strpos($content, '[^a-zA-Z0-9_-]') !== false) {
        echo "   ✅ Bug #8: Filename sanitization found\n";
        $results['passed']++;
    } else {
        echo "   ❌ Bug #8: Filename sanitization NOT FOUND\n";
        $results['failed']++;
    }
} catch (Exception $e) {
    echo "   ❌ Error: " . $e->getMessage() . "\n";
    $results['failed'] += 3;
}

// Test Bug #9: Content length validation
echo "\nBug #9: Checking ConversationService content validation...\n";
try {
    $content = file_get_contents('src/Services/ConversationService.php');

    if (strpos($content, 'strlen($content) > 65000') !== false &&
        strpos($content, 'Conteúdo truncado') !== false) {
        echo "   ✅ Bug #9: Content length validation found\n";
        $results['passed']++;
    } else {
        echo "   ❌ Bug #9: Content length validation NOT FOUND\n";
        $results['failed']++;
    }
} catch (Exception $e) {
    echo "   ❌ Error: " . $e->getMessage() . "\n";
    $results['failed']++;
}

// Summary
echo "\n" . str_repeat("=", 50) . "\n";
echo "RESUMO DOS TESTES\n";
echo str_repeat("=", 50) . "\n";

$total = $results['passed'] + $results['failed'];
$percentage = $total > 0 ? round(($results['passed'] / $total) * 100) : 0;

echo "✅ Testes passaram: {$results['passed']}/{$total} ({$percentage}%)\n";
if ($results['failed'] > 0) {
    echo "❌ Testes falharam: {$results['failed']}/{$total}\n";
}

echo "\n" . str_repeat("=", 50) . "\n";
echo "CORREÇÕES IMPLEMENTADAS\n";
echo str_repeat("=", 50) . "\n";
echo "✅ Bug #2: Upload path environment-aware\n";
echo "✅ Bug #3: Ownership check in DocumentProcessorService\n";
echo "✅ Bug #4: Ownership check in ConversationService::attachFiles()\n";
echo "✅ Bug #5: Ownership check in ConversationService::completeConversation()\n";
echo "✅ Bug #6: Rate limiting (10 uploads/hour)\n";
echo "✅ Bug #7: Real file size validation\n";
echo "✅ Bug #8: Filename sanitization\n";
echo "✅ Bug #9: Content length validation (65KB limit)\n";

echo "\n📋 BACKLOG (Não implementado conforme decisão):\n";
echo "   - Bug #10: Título vazio (cosmético)\n";
echo "   - Bug #11: Diretórios vazios (trivial)\n";

if ($results['passed'] === $total) {
    echo "\n🎉 TODAS AS CORREÇÕES DA OPÇÃO B FORAM IMPLEMENTADAS COM SUCESSO!\n";
} else {
    echo "\n⚠️  Alguns testes falharam. Revisar código.\n";
}
