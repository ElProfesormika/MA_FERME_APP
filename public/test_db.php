<?php
// Test de connexion à la base de données
$config = [
    'host' => 'sql204.infinityfree.com',
    'database' => 'if0_39665291_ferme_ya',
    'username' => 'if0_39665291',
    'password' => 'JPrsDcoxt6DWQ0X',
    'charset' => 'utf8mb4'
];

echo "<h1>Test de connexion à la base de données</h1>";

try {
    // Test 1: Connexion MySQL
    $pdo = new PDO("mysql:host={$config['host']}", $config['username'], $config['password']);
    echo "<p style='color: green;'>✅ Connexion MySQL réussie</p>";
    
    // Test 2: Vérifier si la base de données existe
    $stmt = $pdo->query("SHOW DATABASES LIKE 'if0_39665291_ferme_ya'");
    if ($stmt->rowCount() > 0) {
        echo "<p style='color: green;'>✅ Base de données 'if0_39665291_ferme_ya' existe</p>";
        
        // Test 3: Se connecter à la base de données
        $pdo = new PDO("mysql:host={$config['host']};dbname={$config['database']};charset={$config['charset']}", $config['username'], $config['password']);
        echo "<p style='color: green;'>✅ Connexion à '{$config['database']}' réussie</p>";
        
        // Test 4: Vérifier les tables
        $tables = ['users', 'employes', 'animaux', 'stocks', 'activites', 'alertes'];
        foreach ($tables as $table) {
            $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
            if ($stmt->rowCount() > 0) {
                echo "<p style='color: green;'>✅ Table '$table' existe</p>";
                
                // Compter les enregistrements
                $count = $pdo->query("SELECT COUNT(*) FROM $table")->fetchColumn();
                echo "<p>📊 $table : $count enregistrement(s)</p>";
            } else {
                echo "<p style='color: red;'>❌ Table '$table' n'existe pas</p>";
            }
        }
        
    } else {
        echo "<p style='color: red;'>❌ Base de données 'ferme_db' n'existe pas</p>";
        echo "<p>Créez la base de données en exécutant le script SQL</p>";
    }
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ Erreur : " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<h2>Instructions :</h2>";
echo "<ol>";
echo "<li>Ouvrez <a href='https://{$_SERVER['HTTP_HOST']}/phpmyadmin' target='_blank'>phpMyAdmin</a> (ou celui de votre hébergeur)</li>";
echo "<li>Cliquez sur 'SQL'</li>";
echo "<li>Copiez le contenu du fichier 'database_setup_fixed.sql'</li>";
echo "<li>Collez et exécutez</li>";
echo "</ol>";

echo "<div class='text-center mt-4'>";
echo "    <a href='index_final.php' class='btn btn-primary'>";
echo "        <i class='fas fa-home'></i> Retour au menu principal";
echo "    </a>";
echo "</div>";
?>