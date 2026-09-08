<?php

include "conexion.php";
include_once "paginador.php";

$cnn = conection();

/* Filtros */
$filtro_buscar = isset($_GET['f_buscar']) ? trim($_GET['f_buscar']) : '';
$condiciones = array("deleted = 0");
if ($filtro_buscar != '') {
    $buscarSeguro = mysqli_real_escape_string($cnn, $filtro_buscar);
    $condiciones[] = "descripcion LIKE '%$buscarSeguro%'";
}
$where = implode(" AND ", $condiciones);

/* Obtener formas de pago activas */

$sql = "SELECT id_formas_pago, descripcion FROM formas_pagos WHERE $where ORDER BY id_formas_pago DESC";

/* Eliminación lógica */

if (isset($_GET['eliminar']) && is_numeric($_GET['eliminar'])) {

    $id_formas_pago = intval($_GET['eliminar']);

    $sql_eliminar = "
    UPDATE formas_pago
    SET deleted = 1
    WHERE id_formas_pago = $id_formas_pago
    ";

    if (mysqli_query($cnn, $sql_eliminar)) {

        registrar_accion($cnn, "Formas de Pago", "Eliminó la forma de pago #$id_formas_pago");

        echo "<script>window.location='index.php?seccion=formas_pago&accion=listar ';</script>";
        exit;

    } else {

        echo "
        <div class='alert alert-danger'>
            Error al eliminar: " . mysqli_error($cnn) . "
        </div>";
    }
}

$pag = paginar_consulta($cnn, $sql, 10);
$resultado = $pag['data'];
$hayFiltros = ($filtro_buscar != '');
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold m-0">Formas de Pago</h2>
            <p class="text-muted small">Métodos de pago disponibles en el sistema</p>
        </div>
        <a href="index.php?seccion=formas_pago&accion=modificar" class="btn rounded-pill px-4" style="background-color:#F48FB1;border-color:#F48FB1;color:#fff">
            + Nueva Forma de Pago
        </a>
    </div>

    <!-- FILTROS -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body py-3">
            <form method="GET" action="index.php">
                <input type="hidden" name="seccion" value="formas_pago">
                <input type="hidden" name="accion" value="listar">
                <div class="row g-3 align-items-end">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-muted">Buscar</label>
                        <input type="text" name="f_buscar" class="form-control" placeholder="Descripción..."
                               value="<?= htmlspecialchars($filtro_buscar) ?>">
                    </div>
                    <div class="col-md-6 d-flex align-items-end gap-2">
                        <button type="submit" class="btn rounded-pill px-4" style="background-color:#F48FB1;border-color:#F48FB1;color:#fff"><i class="fas fa-filter me-1"></i> Filtrar</button>
                        <?php if($hayFiltros){ ?>
                            <a href="index.php?seccion=formas_pago&accion=listar" class="btn rounded-pill px-4" style="border-color:#C5B4E3;color:#C5B4E3">Limpiar</a>
                        <?php } ?>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead style="background-color:#FFF9F5">
                        <tr>
                            <th class="ps-4">ID</th>
                            <th>Descripción</th>
                            <th class="text-end pe-4">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($fila = mysqli_fetch_assoc($resultado)){ ?>
                        <tr>
                            <td class="ps-4 fw-bold text-muted"><?= $fila['id_formas_pago'] ?></td>
                            <td class="fw-bold"><?= htmlspecialchars($fila['descripcion']) ?></td>
                            <td class="text-end pe-4">
                                <a href="index.php?seccion=formas_pago&accion=modificar&id=<?= $fila['id_formas_pago'] ?>" 
                                   class="btn btn-sm rounded-pill px-3" title="Editar"
                                   style="border-color:#E6C24D;color:#E6C24D"><i class="fas fa-pen"></i></a>
                                <a href="index.php?seccion=formas_pago&accion=listar&eliminar=<?= $fila['id_formas_pago'] ?>" 
                                   class="btn btn-sm rounded-pill px-3" title="Eliminar"
                                   style="border-color:#E57373;color:#E57373"
                                   onclick="return confirm('¿Está seguro de eliminar esta forma de pago?');"><i class="fas fa-trash"></i></a>
                            </td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php render_paginador($pag); ?>
    </div>
</div>