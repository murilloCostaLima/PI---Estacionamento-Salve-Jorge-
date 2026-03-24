<?php

$id = $_GET['id'] ?? null;

if (!$id) {
    header("Location: ../view/ViewPainel.php");
    exit;
}

header("Location: ../view/PainelCliente.php?id=" . (int)$id);
exit;