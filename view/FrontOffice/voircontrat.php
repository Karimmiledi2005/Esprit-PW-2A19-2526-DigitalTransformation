<?php
require_once '../../controller/contratController.php';

$contratController = new ContratController();

if (!isset($_GET['id'])) {
    die("ID du contrat manquant.");
}

$id = $_GET['id'];
$contrat = $contratController->showContrat($id);

if (!$contrat) {
    die("Contrat introuvable.");
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Voir contrat</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f5f7fb;
            margin: 0;
            padding: 0;
        }

        .container {
            width: 90%;
            max-width: 700px;
            margin: 40px auto;
        }

        .card {
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            padding: 30px;
        }

        .card h2 {
            margin-bottom: 25px;
            color: #1c2b4a;
        }

        .form-group {
            margin-bottom: 18px;
        }

        .form-group label {
            display: block;
            margin-bottom: 7px;
            font-weight: 600;
            color: #333;
        }

        .form-control {
            width: 100%;
            padding: 12px 14px;
            border: 1px solid #dcdfe6;
            border-radius: 10px;
            font-size: 15px;
            box-sizing: border-box;
            background: #f9fafc;
            color: #333;
        }

        .btn-group {
            display: flex;
            gap: 12px;
            margin-top: 25px;
        }

        .btn {
            display: inline-block;
            text-decoration: none;
            padding: 12px 18px;
            border-radius: 10px;
            font-weight: 600;
            border: none;
            cursor: pointer;
            transition: 0.3s;
        }

        .btn-primary {
            background: #2d6cdf;
            color: white;
        }

        .btn-primary:hover {
            background: #1f57b8;
        }

        .btn-secondary {
            background: #e9edf5;
            color: #333;
        }

        .btn-secondary:hover {
            background: #dce3ef;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="card">
        <h2>Détail du contrat</h2>

        <div class="form-group">
            <label>ID Contrat</label>
            <input type="text" class="form-control" value="<?php echo htmlspecialchars($contrat['id_contrat']); ?>" readonly>
        </div>

        <div class="form-group">
            <label>Numéro contrat</label>
            <input type="text" class="form-control" value="<?php echo htmlspecialchars($contrat['numero_contrat']); ?>" readonly>
        </div>

        <div class="form-group">
            <label>Type contrat</label>
            <input type="text" class="form-control" value="<?php echo htmlspecialchars($contrat['type_contrat']); ?>" readonly>
        </div>

        <div class="form-group">
            <label>Date début</label>
            <input type="date" class="form-control" value="<?php echo htmlspecialchars($contrat['date_debut']); ?>" readonly>
        </div>

        <div class="form-group">
            <label>Date fin</label>
            <input type="date" class="form-control" value="<?php echo htmlspecialchars($contrat['date_fin']); ?>" readonly>
        </div>

        <div class="form-group">
            <label>Montant prime</label>
            <input type="text" class="form-control" value="<?php echo htmlspecialchars($contrat['montant_prime']); ?> DT" readonly>
        </div>

        <div class="form-group">
            <label>Franchise</label>
            <input type="text" class="form-control" value="<?php echo htmlspecialchars($contrat['franchise']); ?> DT" readonly>
        </div>

        <div class="form-group">
            <label>Statut</label>
            <input type="text" class="form-control" value="<?php echo htmlspecialchars($contrat['statut']); ?>" readonly>
        </div>

        <div class="form-group">
            <label>ID catégorie</label>
            <input type="text" class="form-control" value="<?php echo htmlspecialchars($contrat['id_categorie']); ?>" readonly>
        </div>

        <div class="btn-group">
            <a href="modifierContrat.php?id=<?php echo $contrat['id_contrat']; ?>" class="btn btn-primary">Modifier</a>
            <a href="listContrat.php" class="btn btn-secondary">Retour</a>
        </div>
    </div>
</div>

</body>
</html>