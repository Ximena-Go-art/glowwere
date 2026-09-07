<?php
header('Content-Type: application/json; charset=utf-8');

include __DIR__ . "/../conexion.php";
$cnn = conection();

$q = isset($_GET['q']) ? trim($_GET['q']) : '';

if ($q === '') {
    echo json_encode([]);
    exit;
}

$buscarSeguro = mysqli_real_escape_string($cnn, $q);

$sql = "SELECT id_familia, familia 
        FROM familias 
        WHERE deleted = 0 
        AND familia LIKE '%$buscarSeguro%' 
        ORDER BY familia ASC 
        LIMIT 10";

$result = mysqli_query($cnn, $sql);

$datos = [];
while ($fila = mysqli_fetch_assoc($result)) {
    $datos[] = $fila;
}

echo json_encode($datos);
