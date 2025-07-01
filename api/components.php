<?php
require_once '../config.php';

validateUser();

$database = new Database();
$db = $database->getConnection();

if (!$db) {
    handleError('Erreur de connexion à la base de données');
}

try {
    $query = "SELECT id, name, image, sale_url FROM components ORDER BY name";
    $stmt = $db->prepare($query);
    $stmt->execute();
    
    $components = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    sendJsonResponse([
        'success' => true,
        'components' => $components
    ]);
} catch (Exception $e) {
    error_log($e->getMessage());
    handleError('Erreur lors de la récupération des composants');
}

?>
