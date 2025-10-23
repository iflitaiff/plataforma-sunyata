<?php
/**
 * Test Script: API Endpoints Validation
 *
 * Validates syntax and structure of all API endpoints
 */

declare(strict_types=1);

echo "=== API Endpoints Validation ===\n\n";

$endpoints = [
    'upload-file.php' => __DIR__ . '/public/api/upload-file.php',
    'chat.php' => __DIR__ . '/public/api/chat.php',
    'export-conversation.php' => __DIR__ . '/public/api/export-conversation.php'
];

$results = [];
$allPassed = true;

foreach ($endpoints as $name => $path) {
    echo "Testing: {$name}\n";

    // Test 1: File exists
    if (!file_exists($path)) {
        echo "  ❌ File does not exist: {$path}\n";
        $results[$name] = false;
        $allPassed = false;
        continue;
    }
    echo "  ✅ File exists\n";

    // Test 2: Syntax check
    $output = [];
    $returnCode = 0;
    exec("php -l " . escapeshellarg($path) . " 2>&1", $output, $returnCode);

    if ($returnCode !== 0) {
        echo "  ❌ Syntax error:\n";
        foreach ($output as $line) {
            echo "     {$line}\n";
        }
        $results[$name] = false;
        $allPassed = false;
        continue;
    }
    echo "  ✅ Syntax valid\n";

    // Test 3: Check for required Services
    $content = file_get_contents($path);

    $requiredClasses = [
        'upload-file.php' => ['FileUploadService', 'DocumentProcessorService'],
        'chat.php' => ['ConversationService', 'ClaudeService'],
        'export-conversation.php' => ['Database', 'Mpdf']
    ];

    if (isset($requiredClasses[$name])) {
        $allClassesFound = true;
        foreach ($requiredClasses[$name] as $class) {
            if (strpos($content, $class) === false) {
                echo "  ❌ Missing required class: {$class}\n";
                $allClassesFound = false;
            }
        }

        if ($allClassesFound) {
            echo "  ✅ All required classes referenced\n";
        } else {
            $results[$name] = false;
            $allPassed = false;
            continue;
        }
    }

    // Test 4: Check for security measures
    $securityChecks = [
        'session_start()' => 'Session handling',
        'X-CSRF-Token' => 'CSRF protection (for POST)',
        'user_id' => 'Authentication check',
        'http_response_code' => 'HTTP status codes'
    ];

    $securityPassed = true;
    foreach ($securityChecks as $pattern => $description) {
        // Skip CSRF check for GET-only endpoints
        if ($name === 'export-conversation.php' && $pattern === 'X-CSRF-Token') {
            continue;
        }

        if (strpos($content, $pattern) === false) {
            echo "  ⚠️  Missing: {$description} ({$pattern})\n";
            $securityPassed = false;
        }
    }

    if ($securityPassed) {
        echo "  ✅ Security measures in place\n";
    }

    // Test 5: Check error handling
    if (strpos($content, 'try {') !== false && strpos($content, 'catch (Exception $e)') !== false) {
        echo "  ✅ Exception handling implemented\n";
    } else {
        echo "  ⚠️  No exception handling found\n";
    }

    // Test 6: Check JSON response
    if (strpos($content, 'application/json') !== false) {
        echo "  ✅ JSON response header set\n";
    } else {
        // Export endpoint returns PDF, not JSON
        if ($name !== 'export-conversation.php') {
            echo "  ⚠️  JSON response header not found\n";
        }
    }

    $results[$name] = true;
    echo "  ✅ {$name} PASSED\n\n";
}

// Summary
echo "=== Summary ===\n\n";

$passed = 0;
$failed = 0;

foreach ($results as $name => $result) {
    if ($result) {
        echo "✅ {$name}\n";
        $passed++;
    } else {
        echo "❌ {$name}\n";
        $failed++;
    }
}

echo "\n";
echo "Total: " . count($results) . "\n";
echo "Passed: {$passed}\n";
echo "Failed: {$failed}\n\n";

if ($allPassed) {
    echo "🎉 All API endpoints passed validation!\n";
    exit(0);
} else {
    echo "⚠️  Some endpoints have issues. Please review.\n";
    exit(1);
}
