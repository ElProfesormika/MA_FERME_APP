<?php
// Inclure la configuration
require_once 'config_sqlite.php';
require_once 'auth_config.php';

// Initialiser les variables
$error = '';
$success = '';

// Traitement de l'inscription
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom_complet = trim($_POST['nom_complet'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $mot_de_passe = $_POST['mot_de_passe'] ?? '';
    $confirmation_mot_de_passe = $_POST['confirmation_mot_de_passe'] ?? '';
    $telephone = trim($_POST['telephone'] ?? '');
    
    // Validation des données
    if (empty($nom_complet) || empty($email) || empty($mot_de_passe)) {
        $error = 'Veuillez remplir tous les champs obligatoires.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Veuillez saisir une adresse email valide.';
    } elseif (strlen($mot_de_passe) < 6) {
        $error = 'Le mot de passe doit contenir au moins 6 caractères.';
    } elseif ($mot_de_passe !== $confirmation_mot_de_passe) {
        $error = 'Les mots de passe ne correspondent pas.';
    } else {
        $db = connectDB();
        if ($db) {
            try {
                // Vérifier si l'email existe déjà
                $stmt = $db->prepare("SELECT COUNT(*) FROM utilisateurs WHERE email = ?");
                $stmt->execute([$email]);
                if ($stmt->fetchColumn() > 0) {
                    $error = 'Cette adresse email est déjà utilisée.';
                } else {
                    // Créer le compte utilisateur
                    $stmt = $db->prepare("
                        INSERT INTO utilisateurs (nom_complet, email, mot_de_passe, telephone, role, statut, date_creation) 
                        VALUES (?, ?, ?, ?, ?, ?, NOW())
                    ");
                    
                    $mot_de_passe_hash = password_hash($mot_de_passe, PASSWORD_DEFAULT);
                    $stmt->execute([
                        $nom_complet,
                        $email,
                        $mot_de_passe_hash,
                        $telephone,
                        'observateur', // Rôle par défaut (plus sécurisé)
                        'actif'
                    ]);
                    
                    $success = 'Compte créé avec succès ! Vous pouvez maintenant vous connecter.';
                    
                    // Vider les champs après succès
                    $nom_complet = $email = $telephone = '';
                }
            } catch (Exception $e) {
                $error = 'Erreur lors de la création du compte.';
            }
        } else {
            $error = 'Erreur de connexion à la base de données.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription - Ferme d'Élevage</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 0;
        }
        .register-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            padding: 3rem;
            max-width: 500px;
            width: 100%;
            margin: 2rem;
        }
        .register-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        .register-header h1 {
            color: #667eea;
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }
        .form-control {
            border-radius: 10px;
            border: 2px solid #e9ecef;
            padding: 12px 15px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        .btn-register {
            background: linear-gradient(45deg, #667eea, #764ba2);
            border: none;
            border-radius: 10px;
            padding: 12px;
            font-size: 1.1rem;
            font-weight: 600;
            color: white;
            width: 100%;
            transition: all 0.3s ease;
        }
        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }
        .input-group-text {
            background: #f8f9fa;
            border: 2px solid #e9ecef;
            border-right: none;
            border-radius: 10px 0 0 10px;
        }
        .input-group .form-control {
            border-left: none;
            border-radius: 0 10px 10px 0;
        }
        .alert {
            border-radius: 10px;
            border: none;
        }
        .password-strength {
            margin-top: 0.5rem;
            font-size: 0.9rem;
        }
        .strength-weak { color: #dc3545; }
        .strength-medium { color: #ffc107; }
        .strength-strong { color: #28a745; }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="register-header">
            <h1><i class="fas fa-user-plus"></i> Inscription</h1>
            <p class="text-muted">Créez votre compte Ferme d'Élevage</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle"></i> <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?= htmlspecialchars($success) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="mb-3">
                <label for="nom_complet" class="form-label">Nom complet *</label>
                <div class="input-group">
                    <span class="input-group-text">
                        <i class="fas fa-user"></i>
                    </span>
                    <input type="text" class="form-control" id="nom_complet" name="nom_complet" 
                           value="<?= htmlspecialchars($nom_complet ?? '') ?>" 
                           placeholder="Votre nom complet" required>
                </div>
            </div>

            <div class="mb-3">
                <label for="email" class="form-label">Email *</label>
                <div class="input-group">
                    <span class="input-group-text">
                        <i class="fas fa-envelope"></i>
                    </span>
                    <input type="email" class="form-control" id="email" name="email" 
                           value="<?= htmlspecialchars($email ?? '') ?>" 
                           placeholder="votre@email.com" required>
                </div>
            </div>

            <div class="mb-3">
                <label for="telephone" class="form-label">Téléphone</label>
                <div class="input-group">
                    <span class="input-group-text">
                        <i class="fas fa-phone"></i>
                    </span>
                    <input type="tel" class="form-control" id="telephone" name="telephone" 
                           value="<?= htmlspecialchars($telephone ?? '') ?>" 
                           placeholder="Votre numéro de téléphone">
                </div>
            </div>

            <div class="mb-3">
                <label for="mot_de_passe" class="form-label">Mot de passe *</label>
                <div class="input-group">
                    <span class="input-group-text">
                        <i class="fas fa-lock"></i>
                    </span>
                    <input type="password" class="form-control" id="mot_de_passe" name="mot_de_passe" 
                           placeholder="Minimum 6 caractères" required 
                           onkeyup="checkPasswordStrength(this.value)">
                </div>
                <div id="password-strength" class="password-strength"></div>
            </div>

            <div class="mb-3">
                <label for="confirmation_mot_de_passe" class="form-label">Confirmation du mot de passe *</label>
                <div class="input-group">
                    <span class="input-group-text">
                        <i class="fas fa-lock"></i>
                    </span>
                    <input type="password" class="form-control" id="confirmation_mot_de_passe" 
                           name="confirmation_mot_de_passe" 
                           placeholder="Confirmez votre mot de passe" required>
                </div>
            </div>

            <div class="mb-3">
                <button type="submit" class="btn btn-register">
                    <i class="fas fa-user-plus"></i> Créer mon compte
                </button>
            </div>
        </form>

        <div class="text-center mt-3">
            <p class="text-muted">Déjà un compte ?</p>
            <a href="login.php" class="btn btn-outline-primary">
                <i class="fas fa-sign-in-alt"></i> Se connecter
            </a>
        </div>

        <div class="text-center mt-3">
            <a href="index_final.php" class="text-muted">
                <i class="fas fa-home"></i> Retour à l'accueil
            </a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function checkPasswordStrength(password) {
            const strengthDiv = document.getElementById('password-strength');
            let strength = 0;
            let message = '';
            let className = '';

            if (password.length >= 6) strength++;
            if (password.length >= 8) strength++;
            if (/[a-z]/.test(password)) strength++;
            if (/[A-Z]/.test(password)) strength++;
            if (/[0-9]/.test(password)) strength++;
            if (/[^A-Za-z0-9]/.test(password)) strength++;

            if (strength < 2) {
                message = 'Mot de passe faible';
                className = 'strength-weak';
            } else if (strength < 4) {
                message = 'Mot de passe moyen';
                className = 'strength-medium';
            } else {
                message = 'Mot de passe fort';
                className = 'strength-strong';
            }

            strengthDiv.textContent = message;
            strengthDiv.className = 'password-strength ' + className;
        }
    </script>
</body>
</html>
