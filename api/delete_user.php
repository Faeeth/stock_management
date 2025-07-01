<?php
require_once '../config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    handleError('Méthode non autorisée', 405);
}

validateAdmin();

$input = json_decode(file_get_contents('php://input'), true);
$userId = $input['user_id'] ?? 0;

if (!$userId) {
    handleError('ID utilisateur requis', 400);
}

$database = new Database();
$db = $database->getConnection();

if (!$db) {
    handleError('Erreur de connexion à la base de données');
}

try {
    $query = "DELETE FROM users WHERE id = :user_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':user_id', $userId);
    $stmt->execute();
    
    if ($stmt->rowCount() === 0) {
        handleError('Utilisateur non trouvé', 404);
    }
    
    sendJsonResponse([
        'success' => true,
        'message' => 'Utilisateur supprimé avec succès'
    ]);
} catch (Exception $e) {
    error_log($e->getMessage());
    handleError('Erreur lors de la suppression de l\'utilisateur');
}

?>
