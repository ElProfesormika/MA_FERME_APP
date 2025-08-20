<?php
// Inclure la configuration
require_once 'config_infinityfree.php';
require_once 'auth_config.php';

// Initialiser les variables
$error = '';
$success = '';

// Traitement de la connexion
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $mot_de_passe = $_POST['mot_de_passe'] ?? '';
    
    if (empty($email) || empty($mot_de_passe)) {
        $error = 'Veuillez remplir tous les champs.';
    } else {
        $db = connectDB();
        if ($db) {
            try {
                $stmt = $db->prepare("SELECT * FROM utilisateurs WHERE email = ? AND statut = 'actif'");
                $stmt->execute([$email]);
                $utilisateur = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($utilisateur && password_verify($mot_de_passe, $utilisateur['mot_de_passe'])) {
                    // Connexion réussie
                    session_start();
                    $_SESSION['user_id'] = $utilisateur['id'];
                    $_SESSION['user_nom'] = $utilisateur['nom'];
                    $_SESSION['user_prenom'] = $utilisateur['prenom'];
                    $_SESSION['user_email'] = $utilisateur['email'];
                    $_SESSION['user_role'] = $utilisateur['role'];
                    
                    // Mettre à jour la dernière connexion
                    $stmt = $db->prepare("UPDATE utilisateurs SET derniere_connexion = NOW() WHERE id = ?");
                    $stmt->execute([$utilisateur['id']]);
                    
                    // Redirection selon le rôle
                    header("Location: index_final.php");
                    exit;
                } else {
                    $error = 'Email ou mot de passe incorrect.';
                }
            } catch (Exception $e) {
                $error = 'Erreur lors de la connexion.';
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
    <title>Connexion - Ferme d'Élevage</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            padding: 3rem;
            max-width: 450px;
            width: 100%;
            margin: 2rem;
        }
        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        .login-header h1 {
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
        .btn-login {
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
        .btn-login:hover {
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
        .demo-accounts {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 1rem;
            margin-top: 1rem;
        }
        .demo-accounts h6 {
            color: #667eea;
            margin-bottom: 0.5rem;
        }
        .demo-account {
            background: white;
            border-radius: 8px;
            padding: 0.5rem;
            margin-bottom: 0.5rem;
            border-left: 3px solid #667eea;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <h1><i class="fas fa-user-lock"></i> Connexion</h1>
            <p class="text-muted">Accédez à votre espace Ferme d'Élevage</p>
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
                <label for="email" class="form-label">Email</label>
                <div class="input-group">
                    <span class="input-group-text">
                        <i class="fas fa-envelope"></i>
                    </span>
                    <input type="email" class="form-control" id="email" name="email" 
                           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" 
                           placeholder="votre@email.com" required>
                </div>
            </div>

            <div class="mb-3">
                <label for="mot_de_passe" class="form-label">Mot de passe</label>
                <div class="input-group">
                    <span class="input-group-text">
                        <i class="fas fa-lock"></i>
                    </span>
                    <input type="password" class="form-control" id="mot_de_passe" name="mot_de_passe" 
                           placeholder="Votre mot de passe" required>
                </div>
            </div>

            <div class="mb-3">
                <button type="submit" class="btn btn-login">
                    <i class="fas fa-sign-in-alt"></i> Se connecter
                </button>
            </div>
        </form>

        <div class="text-center mt-3">
            <p class="text-muted">Pas encore de compte ?</p>
            <a href="register.php" class="btn btn-outline-primary">
                <i class="fas fa-user-plus"></i> Créer un compte
            </a>
        </div>

        <div class="demo-accounts">
            <h6><i class="fas fa-info-circle"></i> Comptes de démonstration :</h6>
            <div class="demo-account">
                <strong>Admin :</strong> admin@ferme.com / admin123
            </div>
            <div class="demo-account">
                <strong>Manager :</strong> manager@ferme.com / manager123
            </div>
            <div class="demo-account">
                <strong>Employé :</strong> employe@ferme.com / employe123
            </div>
            <div class="demo-account">
                <strong>Observateur :</strong> observateur@ferme.com / observateur123
            </div>
        </div>

        <div class="text-center mt-3">
            <a href="index_final.php" class="text-muted">
                <i class="fas fa-home"></i> Retour à l'accueil
            </a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
