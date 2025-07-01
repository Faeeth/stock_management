<?php
require_once '../config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    handleError('Méthode non autorisée', 405);
}

validateAdmin();

$input = json_decode(file_get_contents('php://input'), true);
$name = $input['name'] ?? '';
$image = $input['image'] ?? '';
$saleUrl = $input['sale_url'] ?? '';
$components = $input['components'] ?? [];

if (empty($name) || empty($components)) {
    handleError('Nom du produit et composants requis', 400);
}

$database = new Database();
$db = $database->getConnection();

if (!$db) {
    handleError('Erreur de connexion à la base de données');
}

try {
    $db->beginTransaction();
    
    // Créer le produit
    $productQuery = "INSERT INTO products (name, image, sale_url) VALUES (:name, :image, :sale_url)";
    $productStmt = $db->prepare($productQuery);
    $productStmt->bindParam(':name', $name);
    $productStmt->bindParam(':image', $image);
    $productStmt->bindParam(':sale_url', $saleUrl);
    $productStmt->execute();
    
    $productId = $db->lastInsertId();
    
    // Ajouter les composants
    $compQuery = "INSERT INTO product_components (product_id, component_id, quantity) VALUES (:product_id, :component_id, :quantity)";
    $compStmt = $db->prepare($compQuery);
    
    foreach ($components as $component) {
        $compStmt->bindParam(':product_id', $productId);
        $compStmt->bindParam(':component_id', $component['component_id']);
        $compStmt->bindParam(':quantity', $component['quantity']);
        $compStmt->execute();
    }
    
    $db->commit();
    
    sendJsonResponse([
        'success' => true,
        'message' => 'Produit créé avec succès'
    ]);
} catch (Exception $e) {
    $db->rollback();
    error_log($e->getMessage());
    handleError('Erreur lors de la création du produit');
}

?>
