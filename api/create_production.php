<?php
require_once '../config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    handleError('Méthode non autorisée', 405);
}

$user = validateUser();

$input = json_decode(file_get_contents('php://input'), true);
$productId = $input['product_id'] ?? 0;
$clientName = $input['client_name'] ?? '';
$initialComment = $input['initial_comment'] ?? '';

if (!$productId || empty($clientName)) {
    handleError('ID du produit et nom du client requis', 400);
}

$database = new Database();
$db = $database->getConnection();

if (!$db) {
    handleError('Erreur de connexion à la base de données');
}

try {
    $db->beginTransaction();
    
    // Créer la fabrication
    $prodQuery = "INSERT INTO productions (product_id, client_name) VALUES (:product_id, :client_name)";
    $prodStmt = $db->prepare($prodQuery);
    $prodStmt->bindParam(':product_id', $productId);
    $prodStmt->bindParam(':client_name', $clientName);
    $prodStmt->execute();
    
    $productionId = $db->lastInsertId();
    
    // Ajouter le commentaire initial si fourni
    if (!empty($initialComment)) {
        $commQuery = "INSERT INTO production_comments (production_id, user_id, comment) VALUES (:production_id, :user_id, :comment)";
        $commStmt = $db->prepare($commQuery);
        $commStmt->bindParam(':production_id', $productionId);
        $commStmt->bindParam(':user_id', $user['user_id']);
        $commStmt->bindParam(':comment', $initialComment);
        $commStmt->execute();
    }
    
    $db->commit();
    
    sendJsonResponse([
        'success' => true,
        'message' => 'Fabrication créée avec succès'
    ]);
} catch (Exception $e) {
    $db->rollback();
    error_log($e->getMessage());
    handleError('Erreur lors de la création de la fabrication');
}

?>
