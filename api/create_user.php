<?php
require_once '../config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    handleError('Méthode non autorisée', 405);
}

validateAdmin();

$input = json_decode(file_get_contents('php://input'), true);
$username = $input['username'] ?? '';
$password = $input['password'] ?? '';
$role = $input['role'] ?? 'user';

if (empty($username) || empty($password)) {
    handleError('Nom d\'utilisateur et mot de passe requis', 400);
}

if (!in_array($role, ['user', 'admin'])) {
    handleError('Rôle invalide', 400);
}

$database = new Database();
$db = $database->getConnection();

if (!$db) {
    handleError('Erreur de connexion à la base de données');
}

try {
    // Vérifier si l'utilisateur existe déjà
    $checkQuery = "SELECT id FROM users WHERE username = :username";
    $checkStmt = $db->prepare($checkQuery);
    $checkStmt->bindParam(':username', $username);
    $checkStmt->execute();
    
    if ($checkStmt->rowCount() > 0) {
        handleError('Nom d\'utilisateur déjà utilisé', 400);
    }
    
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    $query = "INSERT INTO users (username, password, role) VALUES (:username, :password, :role)";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':username', $username);
    $stmt->bindParam(':password', $hashedPassword);
    $stmt->bindParam(':role', $role);
    $stmt->execute();
    
    sendJsonResponse([
        'success' => true,
        'message' => 'Utilisateur créé avec succès'
    ]);
} catch (Exception $e) {
    error_log($e->getMessage());
    handleError('Erreur lors de la création de l\'utilisateur');
}

?>
