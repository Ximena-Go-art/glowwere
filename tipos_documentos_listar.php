<?php
include "conexion.php";
$cnn = conection();

/* Consulta */

$sql = "
SELECT
    id_tipo_documento,
    descripcion
FROM tipos_documentos
WHERE deleted = 0
";

/* Eliminación lógica */

if (isset($_GET['eliminar']) && is_numeric($_GET['eliminar'])) {

    $id_tipo_documento = intval($_GET['eliminar']);

    $sql_eliminar = "
    UPDATE tipos_documentos
    SET deleted = 1
    WHERE id_tipo_documento = $id_tipo_documento
    ";

    if (mysqli_query($cnn, $sql_eliminar)) {

        registrar_accion($cnn, "Tipos de Documentos", "Eliminó el tipo de documento #$id_tipo_documento");

        echo "<script>window.location='index.php?seccion=tipos_documentos&accion=listar ';</script>";
        exit;

    } else {

        echo "<div class='alert alert-danger'>
                Error al eliminar: " . mysqli_error($cnn) . "
              </div>";
    }
}

$resultado = mysqli_query($cnn, $sql);

?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold m-0">Tipos de Documentos</h2>
            <p class="text-muted small">Tipos de documentos fiscales y comerciales</p>
        </div>
        <a href="index.php?seccion=tipos_documentos&accion=modificar" class="btn btn-danger rounded-pill px-4">
            + Nuevo Tipo
        </a>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">ID</th>
                            <th>Descripción</th>
                            <th class="text-end pe-4">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($fila = mysqli_fetch_assoc($resultado)){ ?>
                        <tr>
                            <td class="ps-4 fw-bold text-muted"><?= $fila['id_tipo_documento'] ?></td>
                            <td class="fw-bold"><?= htmlspecialchars($fila['descripcion']) ?></td>
                            <td class="text-end pe-4">
                                <a href="index.php?seccion=tipos_documentos&accion=modificar&id=<?= $fila['id_tipo_documento'] ?>" 
                                   class="btn btn-outline-warning btn-sm rounded-pill px-3 me-1">Editar</a>
                                <a href="index.php?seccion=tipos_documentos&accion=listar&eliminar=<?= $fila['id_tipo_documento'] ?>" 
                                   class="btn btn-outline-danger btn-sm rounded-pill px-3"
                                   onclick="return confirm('¿Está seguro de eliminar este tipo de documento?');">Eliminar</a>
                            </td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>