<?php
// config.php - Configuration de la base de données et fonctions utilitaires

// Configuration de la base de données
define('DB_HOST', 'localhost');
define('DB_NAME', 'stock_management');
define('DB_USER', 'stock_user');
define('DB_PASS', 'stock_password');

// Classe de gestion de base de données
class Database {
    private $host = DB_HOST;
    private $db_name = DB_NAME;
    private $username = DB_USER;
    private $password = DB_PASS;
    public $conn;

    public function getConnection() {
        $this->conn = null;
        
        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, 
                                  $this->username, $this->password);
            $this->conn->exec("set names utf8");
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $exception) {
            error_log("Connection error: " . $exception->getMessage());
            return null;
        }
        
        return $this->conn;
    }
}

// Fonction pour envoyer une réponse JSON
function sendJsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
    echo json_encode($data);
    exit;
}

// Fonction pour gérer les erreurs
function handleError($message, $statusCode = 500) {
    sendJsonResponse(['success' => false, 'message' => $message], $statusCode);
}

// Fonction pour valider l'utilisateur connecté via session
function validateUser() {
    session_start();
    if (!isset($_SESSION['user_id'])) {
        handleError('Utilisateur non connecté', 401);
    }
    return $_SESSION;
}

// Fonction pour vérifier les droits admin
function validateAdmin() {
    $user = validateUser();
    if ($user['user_role'] !== 'admin') {
        handleError('Accès refusé - droits administrateur requis', 403);
    }
    return $user;
}

// Gestion des requêtes OPTIONS pour CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
    exit(0);
}

?>
