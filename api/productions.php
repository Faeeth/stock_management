<?php
require_once '../config.php';

validateUser();

$database = new Database();
$db = $database->getConnection();

if (!$db) {
    handleError('Erreur de connexion à la base de données');
}

try {
    $query = "SELECT p.id, p.client_name, p.created_at, pr.name as product_name 
              FROM productions p 
              JOIN products pr ON p.product_id = pr.id 
              WHERE p.status = 'en_cours'
              ORDER BY p.created_at DESC";
    $stmt = $db->prepare($query);
    $stmt->execute();
    
    $productions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Pour chaque production, récupérer les commentaires
    foreach ($productions as &$production) {
        $commQuery = "SELECT pc.comment, pc.created_at, u.username 
                      FROM production_comments pc 
                      JOIN users u ON pc.user_id = u.id 
                      WHERE pc.production_id = :production_id 
                      ORDER BY pc.created_at ASC";
        $commStmt = $db->prepare($commQuery);
        $commStmt->bindParam(':production_id', $production['id']);
        $commStmt->execute();
        
        $production['comments'] = $commStmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    sendJsonResponse([
        'success' => true,
        'productions' => $productions
    ]);
} catch (Exception $e) {
    error_log($e->getMessage());
    handleError('Erreur lors de la récupération des fabrications');
}

?>
