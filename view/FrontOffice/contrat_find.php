<?php
$id = (int)($_GET['id'] ?? 0);
header('Location: contratshow.php?id=' . $id);
exit();
?>
