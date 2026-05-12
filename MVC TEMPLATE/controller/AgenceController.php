<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../model/PosteModel.php';

$model = new PosteModel($pdo);
$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'create':
            $data = [
                'contenu' => $_POST['contenu'] ?? '',
                'date_publication' => $_POST['date_publication'] ?? '',
                'note' => $_POST['note'] ?? 0,
                'auteur' => $_POST['auteur'] ?? '',
                'nb_likes' => $_POST['nb_likes'] ?? 0,
                'nb_commentaires' => $_POST['nb_commentaires'] ?? 0,
                'id_agence' => $_POST['id_agence'] ?? ''
            ];

            $model->createPoste($data);
            header('Location: ../view/FrontOffice/postes.php?success=create');
            exit;

        case 'update':
            $data = [
                'id_poste' => $_POST['id_poste'] ?? '',
                'contenu' => $_POST['contenu'] ?? '',
                'date_publication' => $_POST['date_publication'] ?? '',
                'note' => $_POST['note'] ?? 0,
                'auteur' => $_POST['auteur'] ?? '',
                'nb_likes' => $_POST['nb_likes'] ?? 0,
                'nb_commentaires' => $_POST['nb_commentaires'] ?? 0,
                'id_agence' => $_POST['id_agence'] ?? ''
            ];

            $model->updatePoste($data);
            header('Location: ../view/FrontOffice/postes.php?success=update');
            exit;

        case 'delete':
            $id = $_POST['id_poste'] ?? 0;
            $model->deletePoste((int)$id);
            header('Location: ../view/FrontOffice/postes.php?success=delete');
            exit;

        default:
            echo "Action invalide";
            exit;
    }
} catch (Throwable $e) {
    echo "Erreur Controller : " . $e->getMessage();
}
?>