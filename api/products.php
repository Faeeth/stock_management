<?php
require_once '../config.php';

validateUser();

$database = new Database();
$db = $database->getConnection();

if (!$db) {
    handleError('Erreur de connexion à la base de données');
}

try {
    $query = "SELECT id, name, image, sale_url FROM products ORDER BY name";
    $stmt = $db->prepare($query);
    $stmt->execute();
    
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Pour chaque produit, récupérer ses composants
    foreach ($products as &$product) {
        $compQuery = "SELECT pc.component_id, pc.quantity, c.name as component_name 
                      FROM product_components pc 
                      JOIN components c ON pc.component_id = c.id 
                      WHERE pc.product_id = :product_id";
        $compStmt = $db->prepare($compQuery);
        $compStmt->bindParam(':product_id', $product['id']);
        $compStmt->execute();
        
        $product['components'] = $compStmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    sendJsonResponse([
        'success' => true,
        'products' => $products
    ]);
} catch (Exception $e) {
    error_log($e->getMessage());
    handleError('Erreur lors de la récupération des produits');
}

?>
