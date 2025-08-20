<?php
// Script de test pour SQLite
require_once 'config_sqlite.php';

echo "<h1>🔍 Test de connexion SQLite</h1>";

try {
    $db = connectDB();
    
    if ($db) {
        echo "<p style='color: green;'>✅ Connexion SQLite réussie !</p>";
        
        // Test simple
        $stmt = $db->query("SELECT 1 as test");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "<p style='color: green;'>✅ Requête de test réussie : " . $result['test'] . "</p>";
        
        // Vérifier si les tables existent
        $tables = $db->query("SELECT name FROM sqlite_master WHERE type='table'")->fetchAll(PDO::FETCH_COLUMN);
        
        echo "<h2>📋 Tables disponibles :</h2>";
        if (count($tables) > 0) {
            echo "<ul>";
            foreach ($tables as $table) {
                echo "<li>✅ $table</li>";
            }
            echo "</ul>";
        } else {
            echo "<p style='color: orange;'>⚠️ Aucune table trouvée. Lancez l'initialisation.</p>";
        }
        
        // Chemin de la base de données
        $db_path = getDBPath();
        echo "<h2>📁 Informations :</h2>";
        echo "<ul>";
        echo "<li><strong>Chemin de la DB:</strong> $db_path</li>";
        echo "<li><strong>Taille:</strong> " . (file_exists($db_path) ? number_format(filesize($db_path) / 1024, 2) . " KB" : "Fichier inexistant") . "</li>";
        echo "<li><strong>Permissions:</strong> " . (is_readable($db_path) ? "✅ Lisible" : "❌ Non lisible") . " / " . (is_writable($db_path) ? "✅ Écrivable" : "❌ Non écrivable") . "</li>";
        echo "</ul>";
        
    } else {
        echo "<p style='color: red;'>❌ Échec de la connexion SQLite</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Erreur : " . $e->getMessage() . "</p>";
}

echo "<h2>🚀 Actions disponibles :</h2>";
echo "<p><a href='init_sqlite.php' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-right: 10px;'>📋 Initialiser la base de données</a>";
echo "<a href='index_final.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🏠 Accéder à l'application</a></p>";
?>
