<?php
require_once '../config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    handleError('Méthode non autorisée', 405);
}

validateAdmin();

$input = json_decode(file_get_contents('php://input'), true);
$componentId = $input['component_id'] ?? 0;
$change = $input['change'] ?? 0;

if (!$componentId || !is_numeric($change)) {
    handleError('Paramètres invalides', 400);
}

$database = new Database();
$db = $database->getConnection();

if (!$db) {
    handleError('Erreur de connexion à la base de données');
}

try {
    $db->beginTransaction();
    
    // Vérifier le stock actuel
    $query = "SELECT quantity FROM stocks WHERE component_id = :component_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':component_id', $componentId);
    $stmt->execute();
    
    if ($stmt->rowCount() === 0) {
        $db->rollback();
        handleError('Composant non trouvé dans le stock', 404);
    }
    
    $currentStock = $stmt->fetch(PDO::FETCH_ASSOC);
    $newQuantity = $currentStock['quantity'] + $change;
    
    if ($newQuantity < 0) {
        $db->rollback();
        handleError('Stock insuffisant', 400);
    }
    
    // Mettre à jour le stock
    $updateQuery = "UPDATE stocks SET quantity = :quantity WHERE component_id = :component_id";
    $updateStmt = $db->prepare($updateQuery);
    $updateStmt->bindParam(':quantity', $newQuantity);
    $updateStmt->bindParam(':component_id', $componentId);
    $updateStmt->execute();
    
    $db->commit();
    
    sendJsonResponse([
        'success' => true,
        'message' => 'Stock mis à jour avec succès',
        'new_quantity' => $newQuantity
    ]);
} catch (Exception $e) {
    $db->rollback();
    error_log($e->getMessage());
    handleError('Erreur lors de la mise à jour du stock');
}

?>
