<?php
// obtenemos el arreglo de familias desde el controlador
include "conexion.php";
$cnn = conection();

//--*--  consulta para obtener los datos de las familias que no han sido eliminados,
$sql = "
SELECT id_familia,
    familia,
    descripcion
FROM familias
WHERE deleted = 0
";

//--*-- ejecutamos la consulta y obtenemos el resultado
if (isset($_GET['eliminar']) && is_numeric($_GET['eliminar'])) {
    
    //--*-- obtenemos el id de la familia a eliminar
    $id_familia = intval($_GET['eliminar']);
    $sql_eliminar = "UPDATE familias SET deleted = 1 WHERE id_familia = $id_familia";

    //--*-- ejecutamos la consulta de eliminación
    if (mysqli_query($cnn, $sql_eliminar)) {
        registrar_accion($cnn, "Familias", "Eliminó la familia #$id_familia");
        echo "<script>window.location='index.php?seccion=familias&accion=listar ';</script>";
        exit;
    } else {
        //--*-- mostramos un mensaje de error si la eliminación falla
        echo "<div class='alert alert-danger'>Error al eliminar: " . mysqli_error($cnn) . "</div>";
    }
}
//--*-- ejecutamos la consulta y obtenemos el resultado
$resultado = mysqli_query($cnn, $sql);
?>


<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold m-0">Familias</h2>
            <p class="text-muted small">Gestión de categorías y familias de productos</p>
        </div>
        <a href="index.php?seccion=familias&accion=modificar" class="btn btn-danger rounded-pill px-4">
            + Nueva Familia
        </a>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">ID</th>
                            <th>Familia</th>
                            <th>Descripción</th>
                            <th class="text-end pe-4">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($fila = mysqli_fetch_assoc($resultado)){ ?>
                        <tr>
                            <td class="ps-4 fw-bold text-muted"><?= $fila['id_familia'] ?></td>
                            <td class="fw-bold"><?= htmlspecialchars($fila['familia']) ?></td>
                            <td><?= htmlspecialchars($fila['descripcion']) ?></td>
                            <td class="text-end pe-4">
                                <a href="index.php?seccion=familias&accion=modificar&id=<?= $fila['id_familia'] ?>" 
                                   class="btn btn-outline-warning btn-sm rounded-pill px-3 me-1">
                                   Editar
                                </a>
                                <a href="index.php?seccion=familias&accion=listar&eliminar=<?= $fila['id_familia'] ?>" 
                                   class="btn btn-outline-danger btn-sm rounded-pill px-3"
                                   onclick="return confirm('¿Está seguro de eliminar esta familia?');">
                                   Eliminar
                                </a>
                            </td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>