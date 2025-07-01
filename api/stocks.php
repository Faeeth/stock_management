<?php
require_once '../config.php';

validateUser();

$database = new Database();
$db = $database->getConnection();

if (!$db) {
    handleError('Erreur de connexion à la base de données');
}

try {
    $query = "SELECT s.id, c.name, c.image, c.sale_url, s.quantity 
              FROM stocks s 
              JOIN components c ON s.component_id = c.id 
              ORDER BY s.quantity ASC";
    $stmt = $db->prepare($query);
    $stmt->execute();
    
    $stocks = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    sendJsonResponse([
        'success' => true,
        'stocks' => $stocks
    ]);
} catch (Exception $e) {
    error_log($e->getMessage());
    handleError('Erreur lors de la récupération des stocks');
}
?>
