<?php
// Script de test simple pour Railway
// Inclure la configuration AVANT tout output

// Inclure la configuration
require_once 'config_infinityfree.php';

// Maintenant on peut afficher
echo "<h1>🔍 Test de connexion Railway</h1>";

// Afficher les variables d'environnement
echo "<h2>Variables d'environnement :</h2>";
echo "<ul>";
foreach ($_ENV as $key => $value) {
    if (strpos($key, 'MYSQL') !== false || strpos($key, 'RAILWAY') !== false) {
        echo "<li><strong>$key:</strong> " . (strpos($key, 'PASSWORD') !== false ? '***' : $value) . "</li>";
    }
}
echo "</ul>";

// Test de connexion
echo "<h2>Test de connexion :</h2>";

$db = connectDB();
if ($db) {
    echo "<p style='color: green;'>✅ Connexion réussie !</p>";
    
    // Test simple
    try {
        $stmt = $db->query("SELECT 1 as test");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "<p style='color: green;'>✅ Requête de test réussie : " . $result['test'] . "</p>";
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Erreur requête : " . $e->getMessage() . "</p>";
    }
} else {
    echo "<p style='color: red;'>❌ Échec de la connexion</p>";
}

echo "<h2>Configuration utilisée :</h2>";
$config = getDBConfig();
echo "<ul>";
echo "<li><strong>Host:</strong> " . $config['host'] . "</li>";
echo "<li><strong>Database:</strong> " . $config['database'] . "</li>";
echo "<li><strong>User:</strong> " . $config['username'] . "</li>";
echo "<li><strong>Password:</strong> " . (strlen($config['password']) > 0 ? '***' : 'Non défini') . "</li>";
echo "</ul>";
?>
