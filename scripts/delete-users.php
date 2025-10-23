<?php
/**
 * Delete specific users from the platform
 * This script removes users and all their related data (LGPD compliant)
 */

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/config.php';

use Sunyata\Core\Database;

$db = Database::getInstance();

// Users to delete
$emails_to_delete = [
    'editzbryes@gmail.com',
    'leonardo.melo@diagnext.com',
    'pmo@diagnext.com'
];

echo "=== User Deletion Script ===\n\n";

foreach ($emails_to_delete as $email) {
    echo "Processing: $email\n";

    // Find user
    $user = $db->fetchOne("SELECT * FROM users WHERE email = :email", ['email' => $email]);

    if (!$user) {
        echo "  ❌ User not found\n\n";
        continue;
    }

    $user_id = $user['id'];
    echo "  Found user ID: $user_id\n";

    try {
        $db->beginTransaction();

        // Delete related data

        // 1. User profiles
        $deleted = $db->delete('user_profiles', 'user_id = :user_id', ['user_id' => $user_id]);
        echo "  - Deleted $deleted user_profiles\n";

        // 2. Vertical access requests
        $deleted = $db->delete('vertical_access_requests', 'user_id = :user_id', ['user_id' => $user_id]);
        echo "  - Deleted $deleted vertical_access_requests\n";

        // 3. Consents
        $deleted = $db->delete('consents', 'user_id = :user_id', ['user_id' => $user_id]);
        echo "  - Deleted $deleted consents\n";

        // 4. Data requests
        $deleted = $db->delete('data_requests', 'user_id = :user_id', ['user_id' => $user_id]);
        echo "  - Deleted $deleted data_requests\n";

        // 5. Audit logs (keep for compliance, just mark user as deleted)
        $updated = $db->getConnection()->prepare(
            "UPDATE audit_logs SET user_id = NULL WHERE user_id = :user_id"
        );
        $updated->execute(['user_id' => $user_id]);
        echo "  - Anonymized " . $updated->rowCount() . " audit_logs\n";

        // 6. Tool access logs
        $deleted = $db->delete('tool_access_logs', 'user_id = :user_id', ['user_id' => $user_id]);
        echo "  - Deleted $deleted tool_access_logs\n";

        // 7. Contracts (if any)
        $deleted = $db->delete('contracts', 'user_id = :user_id', ['user_id' => $user_id]);
        echo "  - Deleted $deleted contracts\n";

        // 8. Finally, delete the user
        $deleted = $db->delete('users', 'id = :id', ['id' => $user_id]);
        echo "  - Deleted user account\n";

        $db->commit();
        echo "  ✅ User $email deleted successfully\n\n";

    } catch (Exception $e) {
        $db->rollback();
        echo "  ❌ Error deleting user: " . $e->getMessage() . "\n\n";
    }
}

echo "=== Deletion Complete ===\n";
