<?php
require_once '../config.php';

validateAdmin();

$database = new Database();
$db = $database->getConnection();

if (!$db) {
    handleError('Erreur de connexion à la base de données');
}

try {
    $query = "SELECT id, username, role FROM users ORDER BY username";
    $stmt = $db->prepare($query);
    $stmt->execute();
    
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    sendJsonResponse([
        'success' => true,
        'users' => $users
    ]);
} catch (Exception $e) {
    error_log($e->getMessage());
    handleError('Erreur lors de la récupération des utilisateurs');
}

?>
