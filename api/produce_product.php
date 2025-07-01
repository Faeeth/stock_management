<?php
require_once '../config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    handleError('Méthode non autorisée', 405);
}

validateAdmin();

$input = json_decode(file_get_contents('php://input'), true);
$productId = $input['product_id'] ?? 0;

if (!$productId) {
    handleError('ID du produit requis', 400);
}

$database = new Database();
$db = $database->getConnection();

if (!$db) {
    handleError('Erreur de connexion à la base de données');
}

try {
    $db->beginTransaction();
    
    // Récupérer les composants nécessaires
    $compQuery = "SELECT component_id, quantity FROM product_components WHERE product_id = :product_id";
    $compStmt = $db->prepare($compQuery);
    $compStmt->bindParam(':product_id', $productId);
    $compStmt->execute();
    
    $requiredComponents = $compStmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($requiredComponents)) {
        $db->rollback();
        handleError('Aucun composant défini pour ce produit', 400);
    }
    
    // Vérifier la disponibilité des stocks
    foreach ($requiredComponents as $comp) {
        $stockQuery = "SELECT quantity FROM stocks WHERE component_id = :component_id";
        $stockStmt = $db->prepare($stockQuery);
        $stockStmt->bindParam(':component_id', $comp['component_id']);
        $stockStmt->execute();
        
        if ($stockStmt->rowCount() === 0) {
            $db->rollback();
            handleError('Composant manquant dans le stock', 400);
        }
        
        $stock = $stockStmt->fetch(PDO::FETCH_ASSOC);
        if ($stock['quantity'] < $comp['quantity']) {
            $db->rollback();
            handleError('Stock insuffisant pour la production', 400);
        }
    }
    
    // Consommer les composants
    $updateQuery = "UPDATE stocks SET quantity = quantity - :quantity WHERE component_id = :component_id";
    $updateStmt = $db->prepare($updateQuery);
    
    foreach ($requiredComponents as $comp) {
        $updateStmt->bindParam(':quantity', $comp['quantity']);
        $updateStmt->bindParam(':component_id', $comp['component_id']);
        $updateStmt->execute();
    }
    
    $db->commit();
    
    sendJsonResponse([
        'success' => true,
        'message' => 'Production réalisée avec succès'
    ]);
} catch (Exception $e) {
    $db->rollback();
    error_log($e->getMessage());
    handleError('Erreur lors de la production');
}

?>
