<?php
require_once '../config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    handleError('Méthode non autorisée', 405);
}

$user = validateUser();

$input = json_decode(file_get_contents('php://input'), true);
$productionId = $input['production_id'] ?? 0;
$comment = $input['comment'] ?? '';

if (!$productionId || empty($comment)) {
    handleError('ID de fabrication et commentaire requis', 400);
}

$database = new Database();
$db = $database->getConnection();

if (!$db) {
    handleError('Erreur de connexion à la base de données');
}

try {
    $query = "INSERT INTO production_comments (production_id, user_id, comment) VALUES (:production_id, :user_id, :comment)";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':production_id', $productionId);
    $stmt->bindParam(':user_id', $user['user_id']);
    $stmt->bindParam(':comment', $comment);
    $stmt->execute();
    
    sendJsonResponse([
        'success' => true,
        'message' => 'Commentaire ajouté avec succès'
    ]);
} catch (Exception $e) {
    error_log($e->getMessage());
    handleError('Erreur lors de l\'ajout du commentaire');
}

?>
