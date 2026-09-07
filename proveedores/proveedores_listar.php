<?php

include "conexion.php";
include_once "paginador.php";

$cnn = conection();

/* Eliminación lógica */

if (isset($_GET['eliminar']) && is_numeric($_GET['eliminar'])) {

    $id_proveedor = intval($_GET['eliminar']);

    $sql_eliminar = "
    UPDATE proveedores
    SET deleted = 1
    WHERE id_proveedor = $id_proveedor
    ";

    if (mysqli_query($cnn, $sql_eliminar)) {

        registrar_accion($cnn, "Proveedores", "Eliminó el proveedor #$id_proveedor");

        echo "
        <script>
            window.location='index.php?seccion=proveedores&accion=listar';
        </script>";
        exit;
    }
}

/* Filtros */
$filtro_buscar = isset($_GET['f_buscar']) ? trim($_GET['f_buscar']) : '';
$filtro_estado = (isset($_GET['f_estado']) && in_array($_GET['f_estado'], ['0','1'])) ? intval($_GET['f_estado']) : -1;

$condiciones = array("deleted = 0");
if ($filtro_buscar != '') {
    $buscarSeguro = mysqli_real_escape_string($cnn, $filtro_buscar);
    $condiciones[] = "(proveedor LIKE '%$buscarSeguro%' OR documento LIKE '%$buscarSeguro%' OR correo LIKE '%$buscarSeguro%')";
}
if ($filtro_estado >= 0) {
    $condiciones[] = "proveedor_activo = $filtro_estado";
}
$where = implode(" AND ", $condiciones);

/* Ordenamiento */
$orden = isset($_GET['orden']) ? $_GET['orden'] : 'id_proveedor';
$direccion = (isset($_GET['dir']) && $_GET['dir'] === 'DESC') ? 'DESC' : 'ASC';

/* Consulta */

$sql = "SELECT id_proveedor, proveedor, documento, telefono, correo, proveedor_activo
        FROM proveedores WHERE $where ORDER BY $orden $direccion";

$pag = paginar_consulta($cnn, $sql, 10);
$resultado = $pag['data'];
$hayFiltros = ($filtro_buscar != '' || $filtro_estado >= 0);

?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold m-0">Proveedores</h2>
            <p class="text-muted small">Gestión y listado de proveedores registrados</p>
        </div>
        <a href="index.php?seccion=proveedores&accion=modificar" class="btn rounded-pill px-4" style="background-color:#F48FB1;border-color:#F48FB1;color:#fff">
            + Nuevo Proveedor
        </a>
    </div>

    <!-- FILTROS -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body py-3">
            <form method="GET" action="index.php">
                <input type="hidden" name="seccion" value="proveedores">
                <input type="hidden" name="accion" value="listar">
                <div class="row g-3 align-items-end">
                    <div class="col-md-5">
                        <label class="form-label small fw-bold text-muted">Buscar</label>
                        <input type="text" name="f_buscar" class="form-control" placeholder="Proveedor, documento o correo..."
                               value="<?= htmlspecialchars($filtro_buscar) ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold text-muted">Estado</label>
                        <select name="f_estado" class="form-select">
                            <option value="-1">Todos</option>
                            <option value="1" <?= ($filtro_estado === 1) ? 'selected' : '' ?>>Activo</option>
                            <option value="0" <?= ($filtro_estado === 0) ? 'selected' : '' ?>>Inactivo</option>
                        </select>
                    </div>
                    <div class="col-md-4 d-flex align-items-end gap-2">
                        <button type="submit" class="btn rounded-pill px-4" style="background-color:#F48FB1;border-color:#F48FB1;color:#fff"><i class="fas fa-filter me-1"></i> Filtrar</button>
                        <?php if($hayFiltros){ ?>
                            <a href="index.php?seccion=proveedores&accion=listar" class="btn rounded-pill px-4" style="border-color:#C5B4E3;color:#C5B4E3">Limpiar</a>
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
                            <th class="ps-4">
                                <?php $nuevaDir = ($direccion === 'ASC') ? 'DESC' : 'ASC'; $sp = $_GET; $sp['orden'] = 'id_proveedor'; $sp['dir'] = $nuevaDir; unset($sp['pagina']); ?>
                                <a href="index.php?<?= http_build_query($sp) ?>" style="text-decoration:none; color:inherit;">ID <?= $direccion === 'ASC' ? '<i class="fas fa-sort-up"></i>' : '<i class="fas fa-sort-down"></i>' ?></a>
                            </th>
                            <th>Proveedor</th>
                            <th>Documento</th>
                            <th>Teléfono</th>
                            <th>Correo</th>
                            <th>Activo</th>
                            <th class="text-end pe-4">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($fila = mysqli_fetch_assoc($resultado)){ ?>
                        <tr>
                            <td class="ps-4 fw-bold text-muted"><?= $fila['id_proveedor'] ?></td>
                            <td class="fw-bold"><?= htmlspecialchars($fila['proveedor']) ?></td>
                            <td><?= htmlspecialchars($fila['documento']) ?></td>
                            <td><?= htmlspecialchars($fila['telefono']) ?></td>
                            <td><?= htmlspecialchars($fila['correo']) ?></td>
                            <td>
                                <?php if($fila['proveedor_activo']){ ?>
                                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill">Activo</span>
                                <?php }else{ ?>
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill">Inactivo</span>
                                <?php } ?>
                            </td>
                            <td class="text-end pe-4">
                                <a href="index.php?seccion=proveedores&accion=modificar&id=<?= $fila['id_proveedor'] ?>" 
                                   class="btn btn-sm rounded-pill px-3" title="Editar"
                                   style="border-color:#E6C24D;color:#E6C24D">
                                   <i class="fas fa-pen"></i>
                                </a>
                                <a href="index.php?seccion=proveedores&accion=listar&eliminar=<?= $fila['id_proveedor'] ?>" 
                                   class="btn btn-sm rounded-pill px-3" title="Eliminar"
                                   style="border-color:#E57373;color:#E57373"
                                   onclick="return confirm('¿Está seguro de eliminar este proveedor?');">
                                   <i class="fas fa-trash"></i>
                                </a>
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