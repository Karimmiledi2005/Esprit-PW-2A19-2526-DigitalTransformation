<?php
require_once __DIR__ . '/../../controller/CategorieController.php';

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];

    $categorieC = new CategorieController();
    $categorieC->deleteCategorie($id);
}

header("Location: categories_back.php");
exit;