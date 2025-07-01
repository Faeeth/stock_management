<?php
// index.php - Application de gestion de stock avec protection de connexion
session_start();

// Configuration
require_once 'config.php';

// Vérifier si l'utilisateur est connecté
$isLoggedIn = isset($_SESSION['user_id']);
$currentUser = $isLoggedIn ? $_SESSION : null;
$isAdmin = $isLoggedIn && $_SESSION['user_role'] === 'admin';

// Si déconnexion demandée
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion de Stock</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .login-form, .main-app {
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .login-form {
            max-width: 400px;
            margin: 100px auto;
            padding: 40px;
            text-align: center;
        }

        .login-form h2 {
            color: #333;
            margin-bottom: 30px;
            font-size: 28px;
        }

        .form-group {
            margin-bottom: 20px;
            text-align: left;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #555;
            font-weight: 600;
        }

        .form-group input, .form-group select, .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #e1e1e1;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s;
        }

        .form-group input:focus, .form-group select:focus, .form-group textarea:focus {
            outline: none;
            border-color: #74b9ff;
            box-shadow: 0 0 0 3px rgba(116, 185, 255, 0.1);
        }

        .btn {
            background: linear-gradient(135deg, #4CAF50 0%, #2E7D32 100%);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(76, 175, 80, 0.3);
        }

        .btn-secondary {
            background: linear-gradient(135deg, #81C784 0%, #66BB6A 100%);
        }

        .btn-danger {
            background: linear-gradient(135deg, #fd79a8 0%, #e84393 100%);
        }

        .btn-success {
            background: linear-gradient(135deg, #2E7D32 0%, #1B5E20 100%);
        }

        .header {
            background: linear-gradient(135deg, #2d3436 0%, #636e72 100%);
            color: white;
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header h1 {
            font-size: 24px;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .nav-tabs {
            display: flex;
            background: #f8f9fa;
            border-bottom: 2px solid #e9ecef;
        }

        .nav-tab {
            padding: 15px 25px;
            cursor: pointer;
            border: none;
            background: none;
            font-size: 16px;
            font-weight: 600;
            color: #6c757d;
            transition: all 0.3s;
        }

        .nav-tab.active {
            background: white;
            color: #0984e3;
            border-bottom: 3px solid #74b9ff;
        }

        .nav-tab:hover:not(.active) {
            background: #e9ecef;
            color: #495057;
        }

        .tab-content {
            padding: 30px;
            min-height: 600px;
        }

        .tab-pane {
            display: none;
            animation: fadeIn 0.3s ease-in;
        }

        .tab-pane.active {
            display: block;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .search-bar {
            width: 100%;
            max-width: 400px;
            margin-bottom: 20px;
            padding: 12px 20px;
            border: 2px solid #e1e1e1;
            border-radius: 25px;
            font-size: 16px;
        }

        .search-bar:focus {
            outline: none;
            border-color: #74b9ff;
            box-shadow: 0 0 0 3px rgba(116, 185, 255, 0.1);
        }

        .card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            overflow: hidden;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }

        .card-header {
            background: linear-gradient(135deg, #74b9ff 0%, #0984e3 100%);
            color: white;
            padding: 15px 20px;
            font-weight: 600;
        }

        .card-body {
            padding: 20px;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }

        .stock-item, .product-item {
            border: 2px solid #e1e1e1;
            border-radius: 12px;
            padding: 15px;
            transition: all 0.3s;
        }

        .stock-item:hover, .product-item:hover {
            border-color: #74b9ff;
            box-shadow: 0 4px 15px rgba(116, 185, 255, 0.1);
        }

        .product-status {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-available {
            background: #d4edda;
            color: #155724;
        }

        .status-partial {
            background: #fff3cd;
            color: #856404;
        }

        .status-unavailable {
            background: #f8d7da;
            color: #721c24;
        }

        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 20px;
            border-radius: 8px;
            color: white;
            font-weight: 600;
            z-index: 1000;
            transform: translateX(400px);
            transition: transform 0.3s;
        }

        .notification.show {
            transform: translateX(0);
        }

        .notification.success {
            background: #28a745;
        }

        .notification.error {
            background: #dc3545;
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
        }

        .modal.show {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .modal-content {
            background: white;
            border-radius: 12px;
            padding: 30px;
            max-width: 500px;
            width: 90%;
            max-height: 80vh;
            overflow-y: auto;
        }

        .component-select {
            margin-bottom: 10px;
            padding: 10px;
            border: 2px solid #e1e1e1;
            border-radius: 8px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .component-image {
            width: 40px;
            height: 40px;
            object-fit: cover;
            border-radius: 4px;
        }

        .quantity-low {
            color: #e74c3c;
            font-weight: 600;
        }

        .quantity-medium {
            color: #f39c12;
            font-weight: 600;
        }

        .quantity-high {
            color: #27ae60;
            font-weight: 600;
        }

        .hidden {
            display: none !important;
        }

        .comment {
            background: #f8f9fa;
            border-left: 4px solid #74b9ff;
            padding: 12px;
            margin-bottom: 10px;
            border-radius: 0 8px 8px 0;
        }

        .comment-meta {
            font-size: 12px;
            color: #6c757d;
            margin-bottom: 5px;
        }

        .production-item {
            background: white;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .error-message {
            background: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>
<body>
    <?php if (!$isLoggedIn): ?>
    <!-- Formulaire de connexion -->
    <div class="container">
        <div class="login-form">
            <h2>Connexion</h2>
            <div id="errorMessage" class="error-message hidden"></div>
            <form id="loginForm" onsubmit="return login(event);">
                <div class="form-group">
                    <label for="username">Nom d'utilisateur</label>
                    <input type="text" id="username" name="username" required>
                </div>
                <div class="form-group">
                    <label for="password">Mot de passe</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <button type="submit" class="btn">Se connecter</button>
            </form>
        </div>
    </div>

    <script>
        async function login(event) {
            event.preventDefault();
            
            const username = document.getElementById('username').value;
            const password = document.getElementById('password').value;
            const errorDiv = document.getElementById('errorMessage');
            
            try {
                const response = await fetch('./api/login.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ username, password })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    // Recharger la page pour afficher l'application
                    window.location.reload();
                } else {
                    errorDiv.textContent = data.message || 'Erreur de connexion';
                    errorDiv.classList.remove('hidden');
                }
            } catch (error) {
                console.error('Erreur:', error);
                errorDiv.textContent = 'Erreur de connexion au serveur';
                errorDiv.classList.remove('hidden');
            }
            
            return false;
        }
    </script>

    <?php else: ?>
    <!-- Application principale -->
    <div class="main-app">
        <div class="header">
            <h1>Gestion de Stock</h1>
            <div class="user-info">
                <span>Connecté : <?= htmlspecialchars($currentUser['username']) ?> (<?= $currentUser['user_role'] ?>)</span>
                <a href="?logout=1" class="btn btn-secondary">Déconnexion</a>
            </div>
        </div>

        <div class="nav-tabs">
            <button class="nav-tab active" onclick="showTab('stocks')">Stocks</button>
            <button class="nav-tab" onclick="showTab('products')">Produits</button>
            <button class="nav-tab" onclick="showTab('productions')">Fabrications</button>
            <?php if ($isAdmin): ?>
            <button class="nav-tab" onclick="showTab('admin')">Administration</button>
            <?php endif; ?>
        </div>

        <div class="tab-content">
            <!-- Onglet Stocks -->
            <div id="stocks" class="tab-pane active">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                    <input type="text" id="stockSearch" class="search-bar" placeholder="Rechercher un composant...">
                    <?php if ($isAdmin): ?>
                    <button class="btn" onclick="showAddStockModal()">Ajouter au stock</button>
                    <?php endif; ?>
                </div>
                <div id="stocksList" class="grid"></div>
            </div>

            <!-- Onglet Produits -->
            <div id="products" class="tab-pane">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                    <h3>Liste des produits</h3>
                    <?php if ($isAdmin): ?>
                    <button class="btn" onclick="showAddProductModal()">Créer un produit</button>
                    <?php endif; ?>
                </div>
                <div id="productsList" class="grid"></div>
            </div>

            <!-- Onglet Fabrications -->
            <div id="productions" class="tab-pane">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                    <h3>Fabrications en cours</h3>
                    <button class="btn" onclick="showAddProductionModal()">Nouvelle fabrication</button>
                </div>
                <div id="productionsList"></div>
            </div>

            <!-- Onglet Administration -->
            <?php if ($isAdmin): ?>
            <div id="admin" class="tab-pane">
                <div class="card">
                    <div class="card-header">
                        <h3>Gestion des utilisateurs</h3>
                    </div>
                    <div class="card-body">
                        <button class="btn" onclick="showAddUserModal()">Créer un utilisateur</button>
                        <div id="usersList" style="margin-top: 20px;"></div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Modaux -->
    <!-- Modal Ajout Stock -->
    <div id="addStockModal" class="modal">
        <div class="modal-content">
            <h3>Ajouter au stock</h3>
            <div class="form-group">
                <label for="stockComponentSelect">Composant</label>
                <select id="stockComponentSelect" onchange="updateStockComponentInfo()">
                    <option value="">Sélectionner un composant</option>
                </select>
            </div>
            <div class="form-group">
                <label for="newComponentName">Ou créer un nouveau composant</label>
                <input type="text" id="newComponentName" placeholder="Nom du nouveau composant">
            </div>
            <div class="form-group">
                <label for="componentImage">Image (URL)</label>
                <input type="url" id="componentImage" placeholder="https://...">
            </div>
            <div class="form-group">
                <label for="componentSaleUrl">Lien de vente (URL)</label>
                <input type="url" id="componentSaleUrl" placeholder="https://...">
            </div>
            <div class="form-group">
                <label for="stockQuantity">Quantité à ajouter</label>
                <input type="number" id="stockQuantity" min="1" value="1">
            </div>
            <div style="display: flex; gap: 10px; margin-top: 20px;">
                <button class="btn" onclick="addToStock()">Ajouter</button>
                <button class="btn btn-secondary" onclick="closeModal('addStockModal')">Annuler</button>
            </div>
        </div>
    </div>

    <!-- Modal Création Produit -->
    <div id="addProductModal" class="modal">
        <div class="modal-content">
            <h3>Créer un produit</h3>
            <div class="form-group">
                <label for="productName">Nom du produit</label>
                <input type="text" id="productName" required>
            </div>
            <div class="form-group">
                <label for="productImage">Image (URL)</label>
                <input type="url" id="productImage" placeholder="https://...">
            </div>
            <div class="form-group">
                <label for="productSaleUrl">Lien de vente (URL)</label>
                <input type="url" id="productSaleUrl" placeholder="https://...">
            </div>
            <div class="form-group">
                <label>Composants nécessaires</label>
                <div id="productComponents"></div>
                <button type="button" class="btn btn-secondary" onclick="addProductComponent()">Ajouter un composant</button>
            </div>
            <div style="display: flex; gap: 10px; margin-top: 20px;">
                <button class="btn" onclick="createProduct()">Créer</button>
                <button class="btn btn-secondary" onclick="closeModal('addProductModal')">Annuler</button>
            </div>
        </div>
    </div>

    <!-- Modal Fabrication -->
    <div id="addProductionModal" class="modal">
        <div class="modal-content">
            <h3>Nouvelle fabrication</h3>
            <div class="form-group">
                <label for="productionProduct">Produit</label>
                <select id="productionProduct" required>
                    <option value="">Sélectionner un produit</option>
                </select>
            </div>
            <div class="form-group">
                <label for="clientName">Nom du client</label>
                <input type="text" id="clientName" required>
            </div>
            <div class="form-group">
                <label for="initialComment">Commentaire initial</label>
                <textarea id="initialComment" rows="3"></textarea>
            </div>
            <div style="display: flex; gap: 10px; margin-top: 20px;">
                <button class="btn" onclick="createProduction()">Créer</button>
                <button class="btn btn-secondary" onclick="closeModal('addProductionModal')">Annuler</button>
            </div>
        </div>
    </div>

    <!-- Modal Utilisateur -->
    <div id="addUserModal" class="modal">
        <div class="modal-content">
            <h3>Créer un utilisateur</h3>
            <div class="form-group">
                <label for="newUsername">Nom d'utilisateur</label>
                <input type="text" id="newUsername" required>
            </div>
            <div class="form-group">
                <label for="newPassword">Mot de passe</label>
                <input type="password" id="newPassword" required>
            </div>
            <div class="form-group">
                <label for="userRole">Rôle</label>
                <select id="userRole">
                    <option value="user">Utilisateur</option>
                    <option value="admin">Administrateur</option>
                </select>
            </div>
            <div style="display: flex; gap: 10px; margin-top: 20px;">
                <button class="btn" onclick="createUser()">Créer</button>
                <button class="btn btn-secondary" onclick="closeModal('addUserModal')">Annuler</button>
            </div>
        </div>
    </div>

    <!-- Modal Commentaire -->
    <div id="commentModal" class="modal">
        <div class="modal-content">
            <h3>Ajouter un commentaire</h3>
            <div class="form-group">
                <label for="newComment">Commentaire</label>
                <textarea id="newComment" rows="4" required></textarea>
            </div>
            <div style="display: flex; gap: 10px; margin-top: 20px;">
                <button class="btn" onclick="addComment()">Ajouter</button>
                <button class="btn btn-secondary" onclick="closeModal('commentModal')">Annuler</button>
            </div>
        </div>
    </div>

    <script>
        // Variables globales injectées depuis PHP
        const currentUser = <?= json_encode($currentUser) ?>;
        const isAdmin = <?= $isAdmin ? 'true' : 'false' ?>;
        
        // Variables globales de l'application
        let stocks = [];
        let products = [];
        let components = [];
        let productions = [];
        let users = [];
        let currentProductionId = null;

        // Chargement des données
        async function loadData() {
            await Promise.all([
                loadStocks(),
                loadProducts(),
                loadComponents(),
                loadProductions()
            ]);
        }

        async function loadStocks() {
            try {
                const response = await fetch('./api/stocks.php');
                const data = await response.json();
                
                if (data.success) {
                    stocks = data.stocks;
                    displayStocks();
                }
            } catch (error) {
                showNotification('Erreur lors du chargement des stocks', 'error');
            }
        }

        async function loadProducts() {
            try {
                const response = await fetch('./api/products.php');
                const data = await response.json();
                
                if (data.success) {
                    products = data.products;
                    displayProducts();
                    updateProductSelects();
                }
            } catch (error) {
                showNotification('Erreur lors du chargement des produits', 'error');
            }
        }

        async function loadComponents() {
            try {
                const response = await fetch('./api/components.php');
                const data = await response.json();
                
                if (data.success) {
                    components = data.components;
                    updateComponentSelects();
                }
            } catch (error) {
                showNotification('Erreur lors du chargement des composants', 'error');
            }
        }

        async function loadProductions() {
            try {
                const response = await fetch('./api/productions.php');
                const data = await response.json();
                
                if (data.success) {
                    productions = data.productions;
                    displayProductions();
                }
            } catch (error) {
                showNotification('Erreur lors du chargement des fabrications', 'error');
            }
        }

        async function loadUsers() {
            if (!isAdmin) return;
            
            try {
                const response = await fetch('./api/users.php');
                const data = await response.json();
                
                if (data.success) {
                    users = data.users;
                    displayUsers();
                }
            } catch (error) {
                showNotification('Erreur lors du chargement des utilisateurs', 'error');
            }
        }

        // Affichage des données
        function displayStocks() {
            const stocksList = document.getElementById('stocksList');
            const searchTerm = document.getElementById('stockSearch').value.toLowerCase();
            
            const filteredStocks = stocks.filter(stock => 
                stock.name.toLowerCase().includes(searchTerm)
            );
            
            const sortedStocks = filteredStocks.sort((a, b) => a.quantity - b.quantity);
            
            stocksList.innerHTML = sortedStocks.map(stock => {
                const quantityClass = stock.quantity < 5 ? 'quantity-low' : 
                                    stock.quantity < 20 ? 'quantity-medium' : 'quantity-high';
                
                return `
                    <div class="stock-item">
                        <div style="display: flex; align-items: center; gap: 15px;">
                            <img src="${stock.image || 'https://via.placeholder.com/60'}" 
                                 alt="${stock.name}" class="component-image" style="width: 60px; height: 60px;">
                            <div style="flex: 1;">
                                <h4>${stock.name}</h4>
                                <p class="${quantityClass}">Quantité: ${stock.quantity}</p>
                                ${stock.sale_url ? `<a href="${stock.sale_url}" target="_blank" style="color: #0984e3;">🔗 Lien de vente</a>` : ''}
                            </div>
                            ${isAdmin ? `
                                <div>
                                    <button class="btn btn-danger" onclick="updateStock(${stock.id}, -1)">-1</button>
                                    <button class="btn btn-secondary" onclick="updateStock(${stock.id}, 1)">+1</button>
                                </div>
                            ` : ''}
                        </div>
                    </div>
                `;
            }).join('');
        }

        function displayProducts() {
            const productsList = document.getElementById('productsList');
            
            productsList.innerHTML = products.map(product => {
                const status = getProductStatus(product);
                const statusClass = status === 'available' ? 'status-available' : 
                                  status === 'partial' ? 'status-partial' : 'status-unavailable';
                const statusText = status === 'available' ? 'Disponible' : 
                                 status === 'partial' ? 'Partiel' : 'Indisponible';
                
                return `
                    <div class="product-item">
                        <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 15px;">
                            <img src="${product.image || 'https://via.placeholder.com/80'}" 
                                 alt="${product.name}" style="width: 80px; height: 80px; object-fit: cover; border-radius: 8px;">
                            <div style="flex: 1;">
                                <h4>${product.name}</h4>
                                <span class="product-status ${statusClass}">${statusText}</span>
                                ${product.sale_url ? `<br><a href="${product.sale_url}" target="_blank" style="color: #0984e3;">🔗 Lien de vente</a>` : ''}
                            </div>
                        </div>
                        <div style="margin-bottom: 15px;">
                            <strong>Composants nécessaires:</strong>
                            ${product.components.map(comp => {
                                const stock = stocks.find(s => s.id === comp.component_id);
                                const available = stock ? stock.quantity : 0;
                                const statusColor = available >= comp.quantity ? '#27ae60' : 
                                                  available > 0 ? '#f39c12' : '#e74c3c';
                                
                                return `
                                    <div style="margin: 5px 0; padding: 8px; background: #f8f9fa; border-radius: 4px;">
                                        ${comp.component_name} - Requis: ${comp.quantity}, Disponible: <span style="color: ${statusColor}; font-weight: 600;">${available}</span>
                                    </div>
                                `;
                            }).join('')}
                        </div>
                        ${status === 'available' && isAdmin ? `
                            <button class="btn btn-success" onclick="produceProduct(${product.id})">
                                Produire (consommer les composants)
                            </button>
                        ` : ''}
                    </div>
                `;
            }).join('');
        }

        function displayProductions() {
            const productionsList = document.getElementById('productionsList');
            
            productionsList.innerHTML = productions.map(production => `
                <div class="production-item">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                        <div>
                            <h4>${production.product_name}</h4>
                            <p><strong>Client:</strong> ${production.client_name}</p>
                            <p><small>Créé le: ${new Date(production.created_at).toLocaleDateString()}</small></p>
                        </div>
                        <button class="btn" onclick="showCommentModal(${production.id})">Ajouter un commentaire</button>
                    </div>
                    <div>
                        <strong>Historique des commentaires:</strong>
                        <div style="max-height: 200px; overflow-y: auto; margin-top: 10px;">
                            ${production.comments.map(comment => `
                                <div class="comment">
                                    <div class="comment-meta">
                                        ${comment.username} - ${new Date(comment.created_at).toLocaleString()}
                                    </div>
                                    <div>${comment.comment}</div>
                                </div>
                            `).join('')}
                        </div>
                    </div>
                </div>
            `).join('');
        }

        function displayUsers() {
            const usersList = document.getElementById('usersList');
            
            usersList.innerHTML = users.map(user => `
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 10px; border: 1px solid #e1e1e1; border-radius: 8px; margin-bottom: 10px;">
                    <div>
                        <strong>${user.username}</strong>
                        <span style="margin-left: 10px; padding: 2px 8px; background: ${user.role === 'admin' ? '#dc3545' : '#28a745'}; color: white; border-radius: 12px; font-size: 12px;">
                            ${user.role === 'admin' ? 'Admin' : 'Utilisateur'}
                        </span>
                    </div>
                    <button class="btn btn-danger" onclick="deleteUser(${user.id})">Supprimer</button>
                </div>
            `).join('');
        }

        // Fonctions utilitaires
        function getProductStatus(product) {
            const availableComponents = product.components.filter(comp => {
                const stock = stocks.find(s => s.id === comp.component_id);
                return stock && stock.quantity >= comp.quantity;
            });
            
            if (availableComponents.length === product.components.length) return 'available';
            if (availableComponents.length > 0) return 'partial';
            return 'unavailable';
        }

        function updateComponentSelects() {
            const select = document.getElementById('stockComponentSelect');
            select.innerHTML = '<option value="">Sélectionner un composant</option>' + 
                components.map(comp => `
                    <option value="${comp.id}" data-image="${comp.image}" data-url="${comp.sale_url}">
                        ${comp.name}
                    </option>
                `).join('');
        }

        function updateProductSelects() {
            const select = document.getElementById('productionProduct');
            select.innerHTML = '<option value="">Sélectionner un produit</option>' + 
                products.map(product => `<option value="${product.id}">${product.name}</option>`).join('');
        }

        // Actions
        async function updateStock(componentId, change) {
            try {
                const response = await fetch('./api/update_stock.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ 
                        component_id: componentId, 
                        change: change 
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    await loadStocks();
                    showNotification('Stock mis à jour', 'success');
                } else {
                    showNotification(data.message || 'Erreur lors de la mise à jour', 'error');
                }
            } catch (error) {
                showNotification('Erreur lors de la mise à jour du stock', 'error');
            }
        }

        async function addToStock() {
            const componentId = document.getElementById('stockComponentSelect').value;
            const newComponentName = document.getElementById('newComponentName').value;
            const componentImage = document.getElementById('componentImage').value;
            const componentSaleUrl = document.getElementById('componentSaleUrl').value;
            const quantity = parseInt(document.getElementById('stockQuantity').value);
            
            if (!componentId && !newComponentName) {
                showNotification('Veuillez sélectionner un composant ou créer un nouveau', 'error');
                return;
            }
            
            try {
                const response = await fetch('./api/add_stock.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        component_id: componentId || null,
                        name: newComponentName || null,
                        image: componentImage,
                        sale_url: componentSaleUrl,
                        quantity: quantity
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    await loadData();
                    closeModal('addStockModal');
                    clearStockForm();
                    showNotification('Stock ajouté avec succès', 'success');
                } else {
                    showNotification(data.message || 'Erreur lors de l\'ajout', 'error');
                }
            } catch (error) {
                showNotification('Erreur lors de l\'ajout au stock', 'error');
            }
        }

        async function createProduct() {
            const name = document.getElementById('productName').value;
            const image = document.getElementById('productImage').value;
            const saleUrl = document.getElementById('productSaleUrl').value;
            
            const componentSelects = document.querySelectorAll('#productComponents select');
            const quantityInputs = document.querySelectorAll('#productComponents input[type="number"]');
            
            const components = [];
            componentSelects.forEach((select, index) => {
                if (select.value && quantityInputs[index].value) {
                    components.push({
                        component_id: parseInt(select.value),
                        quantity: parseInt(quantityInputs[index].value)
                    });
                }
            });
            
            if (!name || components.length === 0) {
                showNotification('Veuillez remplir tous les champs obligatoires', 'error');
                return;
            }
            
            try {
                const response = await fetch('./api/create_product.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        name: name,
                        image: image,
                        sale_url: saleUrl,
                        components: components
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    await loadProducts();
                    closeModal('addProductModal');
                    clearProductForm();
                    showNotification('Produit créé avec succès', 'success');
                } else {
                    showNotification(data.message || 'Erreur lors de la création', 'error');
                }
            } catch (error) {
                showNotification('Erreur lors de la création du produit', 'error');
            }
        }

        async function produceProduct(productId) {
            if (!confirm('Confirmer la production ? Cela va consommer les composants du stock.')) {
                return;
            }
            
            try {
                const response = await fetch('./api/produce_product.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ product_id: productId })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    await loadData();
                    showNotification('Production réalisée avec succès', 'success');
                } else {
                    showNotification(data.message || 'Erreur lors de la production', 'error');
                }
            } catch (error) {
                showNotification('Erreur lors de la production', 'error');
            }
        }

        async function createProduction() {
            const productId = document.getElementById('productionProduct').value;
            const clientName = document.getElementById('clientName').value;
            const initialComment = document.getElementById('initialComment').value;
            
            if (!productId || !clientName) {
                showNotification('Veuillez remplir tous les champs obligatoires', 'error');
                return;
            }
            
            try {
                const response = await fetch('./api/create_production.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        product_id: parseInt(productId),
                        client_name: clientName,
                        initial_comment: initialComment
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    await loadProductions();
                    closeModal('addProductionModal');
                    clearProductionForm();
                    showNotification('Fabrication créée avec succès', 'success');
                } else {
                    showNotification(data.message || 'Erreur lors de la création', 'error');
                }
            } catch (error) {
                showNotification('Erreur lors de la création de la fabrication', 'error');
            }
        }

        async function createUser() {
            const username = document.getElementById('newUsername').value;
            const password = document.getElementById('newPassword').value;
            const role = document.getElementById('userRole').value;
            
            if (!username || !password) {
                showNotification('Veuillez remplir tous les champs', 'error');
                return;
            }
            
            try {
                const response = await fetch('./api/create_user.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        username: username,
                        password: password,
                        role: role
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    await loadUsers();
                    closeModal('addUserModal');
                    clearUserForm();
                    showNotification('Utilisateur créé avec succès', 'success');
                } else {
                    showNotification(data.message || 'Erreur lors de la création', 'error');
                }
            } catch (error) {
                showNotification('Erreur lors de la création de l\'utilisateur', 'error');
            }
        }

        async function deleteUser(userId) {
            if (!confirm('Confirmer la suppression de cet utilisateur ?')) {
                return;
            }
            
            try {
                const response = await fetch('./api/delete_user.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ user_id: userId })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    await loadUsers();
                    showNotification('Utilisateur supprimé', 'success');
                } else {
                    showNotification(data.message || 'Erreur lors de la suppression', 'error');
                }
            } catch (error) {
                showNotification('Erreur lors de la suppression', 'error');
            }
        }

        async function addComment() {
            const comment = document.getElementById('newComment').value;
            
            if (!comment || !currentProductionId) {
                showNotification('Veuillez saisir un commentaire', 'error');
                return;
            }
            
            try {
                const response = await fetch('./api/add_comment.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        production_id: currentProductionId,
                        comment: comment
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    await loadProductions();
                    closeModal('commentModal');
                    document.getElementById('newComment').value = '';
                    showNotification('Commentaire ajouté', 'success');
                } else {
                    showNotification(data.message || 'Erreur lors de l\'ajout', 'error');
                }
            } catch (error) {
                showNotification('Erreur lors de l\'ajout du commentaire', 'error');
            }
        }

        // Gestion des onglets
        function showTab(tabName) {
            document.querySelectorAll('.nav-tab').forEach(tab => tab.classList.remove('active'));
            document.querySelectorAll('.tab-pane').forEach(pane => pane.classList.remove('active'));
            
            event.target.classList.add('active');
            document.getElementById(tabName).classList.add('active');
            
            if (tabName === 'admin' && isAdmin) {
                loadUsers();
            }
        }

        // Gestion des modaux
        function showAddStockModal() {
            document.getElementById('addStockModal').classList.add('show');
        }

        function showAddProductModal() {
            document.getElementById('addProductModal').classList.add('show');
        }

        function showAddProductionModal() {
            document.getElementById('addProductionModal').classList.add('show');
        }

        function showAddUserModal() {
            document.getElementById('addUserModal').classList.add('show');
        }

        function showCommentModal(productionId) {
            currentProductionId = productionId;
            document.getElementById('commentModal').classList.add('show');
        }

        function closeModal(modalId) {
            document.getElementById(modalId).classList.remove('show');
        }

        // Gestion des composants de produit
        function addProductComponent() {
            const container = document.getElementById('productComponents');
            const div = document.createElement('div');
            div.style.cssText = 'display: flex; gap: 10px; margin-bottom: 10px; align-items: center;';
            div.innerHTML = `
                <select style="flex: 1;">
                    <option value="">Sélectionner un composant</option>
                    ${components.map(comp => `<option value="${comp.id}">${comp.name}</option>`).join('')}
                </select>
                <input type="number" min="1" value="1" style="width: 80px;" placeholder="Qté">
                <button type="button" class="btn btn-danger" onclick="this.parentElement.remove()">Supprimer</button>
            `;
            container.appendChild(div);
        }

        // Fonctions de nettoyage de formulaires
        function clearStockForm() {
            document.getElementById('stockComponentSelect').value = '';
            document.getElementById('newComponentName').value = '';
            document.getElementById('componentImage').value = '';
            document.getElementById('componentSaleUrl').value = '';
            document.getElementById('stockQuantity').value = '1';
        }

        function clearProductForm() {
            document.getElementById('productName').value = '';
            document.getElementById('productImage').value = '';
            document.getElementById('productSaleUrl').value = '';
            document.getElementById('productComponents').innerHTML = '';
        }

        function clearProductionForm() {
            document.getElementById('productionProduct').value = '';
            document.getElementById('clientName').value = '';
            document.getElementById('initialComment').value = '';
        }

        function clearUserForm() {
            document.getElementById('newUsername').value = '';
            document.getElementById('newPassword').value = '';
            document.getElementById('userRole').value = 'user';
        }

        // Notifications
        function showNotification(message, type) {
            const notification = document.createElement('div');
            notification.className = `notification ${type}`;
            notification.textContent = message;
            document.body.appendChild(notification);
            
            setTimeout(() => notification.classList.add('show'), 100);
            setTimeout(() => {
                notification.classList.remove('show');
                setTimeout(() => document.body.removeChild(notification), 300);
            }, 3000);
        }

        // Event listeners
        document.getElementById('stockSearch').addEventListener('input', displayStocks);
        
        // Fermeture des modaux en cliquant à l'extérieur
        document.querySelectorAll('.modal').forEach(modal => {
            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    modal.classList.remove('show');
                }
            });
        });

        // Initialisation au chargement de la page
        document.addEventListener('DOMContentLoaded', function() {
            loadData();
        });
    </script>
    <?php endif; ?>
</body>
</html>
