<?php
require_once '../config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    handleError('Méthode non autorisée', 405);
}

validateAdmin();

$input = json_decode(file_get_contents('php://input'), true);
$componentId = $input['component_id'] ?? null;
$name = $input['name'] ?? '';
$image = $input['image'] ?? '';
$saleUrl = $input['sale_url'] ?? '';
$quantity = $input['quantity'] ?? 0;

if ((!$componentId && empty($name)) || $quantity <= 0) {
    handleError('Paramètres invalides', 400);
}

$database = new Database();
$db = $database->getConnection();

if (!$db) {
    handleError('Erreur de connexion à la base de données');
}

try {
    $db->beginTransaction();
    
    // Créer un nouveau composant si nécessaire
    if (!$componentId && !empty($name)) {
        $compQuery = "INSERT INTO components (name, image, sale_url) VALUES (:name, :image, :sale_url)";
        $compStmt = $db->prepare($compQuery);
        $compStmt->bindParam(':name', $name);
        $compStmt->bindParam(':image', $image);
        $compStmt->bindParam(':sale_url', $saleUrl);
        $compStmt->execute();
        
        $componentId = $db->lastInsertId();
    }
    
    // Vérifier si le stock existe déjà
    $checkQuery = "SELECT quantity FROM stocks WHERE component_id = :component_id";
    $checkStmt = $db->prepare($checkQuery);
    $checkStmt->bindParam(':component_id', $componentId);
    $checkStmt->execute();
    
    if ($checkStmt->rowCount() > 0) {
        // Mettre à jour le stock existant
        $currentStock = $checkStmt->fetch(PDO::FETCH_ASSOC);
        $newQuantity = $currentStock['quantity'] + $quantity;
        
        $updateQuery = "UPDATE stocks SET quantity = :quantity WHERE component_id = :component_id";
        $updateStmt = $db->prepare($updateQuery);
        $updateStmt->bindParam(':quantity', $newQuantity);
        $updateStmt->bindParam(':component_id', $componentId);
        $updateStmt->execute();
    } else {
        // Créer un nouveau stock
        $insertQuery = "INSERT INTO stocks (component_id, quantity) VALUES (:component_id, :quantity)";
        $insertStmt = $db->prepare($insertQuery);
        $insertStmt->bindParam(':component_id', $componentId);
        $insertStmt->bindParam(':quantity', $quantity);
        $insertStmt->execute();
    }
    
    $db->commit();
    
    sendJsonResponse([
        'success' => true,
        'message' => 'Stock ajouté avec succès'
    ]);
} catch (Exception $e) {
    $db->rollback();
    error_log($e->getMessage());
    handleError('Erreur lors de l\'ajout au stock');
}

?>
