<?php
// Script de test pour SQLite
require_once 'config_sqlite.php';

echo "<h1>🔍 Test de connexion SQLite</h1>";

try {
    $db = connectDB();
    
    if (!$db) {
        echo "<p style='color: red;'>❌ Erreur de connexion à la base de données</p>";
        exit;
    }
    
    echo "<p style='color: green;'>✅ Connexion SQLite réussie</p>";
    
    // Informations sur le fichier de base de données
    $db_path = getDBPath();
    echo "<h2>📁 Informations sur la base de données</h2>";
    echo "<p><strong>Chemin :</strong> " . htmlspecialchars($db_path) . "</p>";
    echo "<p><strong>Taille :</strong> " . (file_exists($db_path) ? number_format(filesize($db_path) / 1024, 2) . " KB" : "Fichier inexistant") . "</p>";
    echo "<p><strong>Dernière modification :</strong> " . (file_exists($db_path) ? date('d/m/Y H:i:s', filemtime($db_path)) : "N/A") . "</p>";
    
    // Test des tables
    echo "<h2>📋 Test des tables</h2>";
    
    $tables = ['utilisateurs', 'animaux', 'stocks', 'employes', 'activites', 'alertes'];
    
    foreach ($tables as $table) {
        try {
            $stmt = $db->query("SELECT COUNT(*) as count FROM $table");
            $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
            echo "<p style='color: green;'>✅ Table <strong>$table</strong> : $count enregistrements</p>";
        } catch (Exception $e) {
            echo "<p style='color: red;'>❌ Table <strong>$table</strong> : " . $e->getMessage() . "</p>";
        }
    }
    
    // Test des requêtes spécifiques
    echo "<h2>🔍 Test des requêtes</h2>";
    
    // Test utilisateurs
    try {
        $stmt = $db->query("SELECT nom_complet, email, role FROM utilisateurs LIMIT 3");
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "<p style='color: green;'>✅ Utilisateurs (3 premiers) :</p>";
        echo "<ul>";
        foreach ($users as $user) {
            echo "<li>" . htmlspecialchars($user['nom_complet']) . " (" . htmlspecialchars($user['email']) . ") - " . htmlspecialchars($user['role']) . "</li>";
        }
        echo "</ul>";
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Erreur requête utilisateurs : " . $e->getMessage() . "</p>";
    }
    
    // Test animaux
    try {
        $stmt = $db->query("SELECT nom, espece, statut FROM animaux LIMIT 3");
        $animaux = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "<p style='color: green;'>✅ Animaux (3 premiers) :</p>";
        echo "<ul>";
        foreach ($animaux as $animal) {
            echo "<li>" . htmlspecialchars($animal['nom']) . " (" . htmlspecialchars($animal['espece']) . ") - " . htmlspecialchars($animal['statut']) . "</li>";
        }
        echo "</ul>";
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Erreur requête animaux : " . $e->getMessage() . "</p>";
    }
    
    // Test employés
    try {
        $stmt = $db->query("SELECT nom_complet, poste, statut FROM employes LIMIT 3");
        $employes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "<p style='color: green;'>✅ Employés (3 premiers) :</p>";
        echo "<ul>";
        foreach ($employes as $employe) {
            echo "<li>" . htmlspecialchars($employe['nom_complet']) . " (" . htmlspecialchars($employe['poste']) . ") - " . htmlspecialchars($employe['statut']) . "</li>";
        }
        echo "</ul>";
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Erreur requête employés : " . $e->getMessage() . "</p>";
    }
    
    echo "<h2>🎉 Test terminé avec succès !</h2>";
    echo "<p><a href='index_final.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🏠 Retour à l'accueil</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Erreur : " . $e->getMessage() . "</p>";
}
?>
