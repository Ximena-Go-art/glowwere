<?php
include "conexion.php";
include_once "paginador.php";
$cnn = conection();

/* ============================
   ELIMINAR (BAJA LÓGICA)
============================ */

if (isset($_GET['ideliminar'])) {

    $idEliminar = intval($_GET['ideliminar']);

    $sql = "UPDATE usuarios
            SET deleted = 1
            WHERE id_usuario = $idEliminar";

    $resp = mysqli_query($cnn, $sql);

    if (!$resp) {

        die("Error: " . mysqli_error($cnn));

    } else {

        registrar_accion($cnn, "Usuarios", "Eliminó el usuario #$idEliminar");

        echo "<script>

            alert('Usuario eliminado correctamente.');

            window.location.href='index.php?seccion=usuarios&accion=listar';

        </script>";

    }

}

/* ============================
   FILTROS
============================ */

$filtro_buscar = isset($_GET['f_buscar']) ? trim($_GET['f_buscar']) : '';
$filtro_rol    = (isset($_GET['f_rol']) && is_numeric($_GET['f_rol'])) ? intval($_GET['f_rol']) : 0;
$filtro_estado = (isset($_GET['f_estado']) && in_array($_GET['f_estado'], ['0','1'])) ? intval($_GET['f_estado']) : -1;

$condiciones = array("u.deleted = 0");

if ($filtro_buscar != '') {
    $buscarSeguro = mysqli_real_escape_string($cnn, $filtro_buscar);
    $condiciones[] = "(u.usuario LIKE '%$buscarSeguro%' OR u.email LIKE '%$buscarSeguro%')";
}
if ($filtro_rol > 0) {
    $condiciones[] = "u.id_rol = $filtro_rol";
}
if ($filtro_estado >= 0) {
    $condiciones[] = "u.actividad_usuario = $filtro_estado";
}

$where = implode(" AND ", $condiciones);

$listaRoles = mysqli_query($cnn, "SELECT id_rol, nombre FROM roles WHERE deleted = 0 ORDER BY nombre ASC");

$sql = "SELECT u.*, r.nombre AS rol
        FROM usuarios u
        LEFT JOIN roles r ON u.id_rol = r.id_rol
        WHERE $where
        ORDER BY id_usuario DESC";

$pag = paginar_consulta($cnn, $sql, 10);
$result = $pag['data'];
$hayFiltros = ($filtro_buscar != '' || $filtro_rol > 0 || $filtro_estado >= 0);
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold m-0">Usuarios</h2>
            <p class="text-muted small">Gestión de usuarios del sistema</p>
        </div>
        <a href="index.php?seccion=usuarios&accion=modificar" class="btn rounded-pill px-4" style="background-color:#F48FB1;border-color:#F48FB1;color:#fff">
            + Nuevo Usuario
        </a>
    </div>

    <!-- FILTROS -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body py-3">
            <form method="GET" action="index.php">
                <input type="hidden" name="seccion" value="usuarios">
                <input type="hidden" name="accion" value="listar">
                <div class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label small fw-bold text-muted">Buscar</label>
                        <input type="text" name="f_buscar" class="form-control" placeholder="Usuario o email..."
                               value="<?= htmlspecialchars($filtro_buscar) ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold text-muted">Rol</label>
                        <select name="f_rol" class="form-select">
                            <option value="0">Todos los roles</option>
                            <?php if($listaRoles){ while($r = mysqli_fetch_assoc($listaRoles)){ ?>
                                <option value="<?= $r['id_rol'] ?>" <?= ($filtro_rol == $r['id_rol']) ? 'selected' : '' ?>><?= htmlspecialchars($r['nombre']) ?></option>
                            <?php } } ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold text-muted">Estado</label>
                        <select name="f_estado" class="form-select">
                            <option value="-1">Todos</option>
                            <option value="1" <?= ($filtro_estado === 1) ? 'selected' : '' ?>>Activo</option>
                            <option value="0" <?= ($filtro_estado === 0) ? 'selected' : '' ?>>Inactivo</option>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end gap-2">
                        <button type="submit" class="btn rounded-pill px-4" style="background-color:#F48FB1;border-color:#F48FB1;color:#fff"><i class="fas fa-filter me-1"></i> Filtrar</button>
                        <?php if($hayFiltros){ ?>
                            <a href="index.php?seccion=usuarios&accion=listar" class="btn rounded-pill px-4" style="border-color:#C5B4E3;color:#C5B4E3">Limpiar</a>
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
                            <th>Usuario</th>
                            <th>Email</th>
                            <th>Rol</th>
                            <th>Fecha Registro</th>
                            <th>Activo</th>
                            <th class="text-end pe-4">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(mysqli_num_rows($result)>0){ while($fila = mysqli_fetch_assoc($result)){ ?>
                        <tr>
                            <td class="ps-4 fw-bold text-muted"><?= $fila['id_usuario'] ?></td>
                            <td class="fw-bold"><?= htmlspecialchars($fila['usuario']) ?></td>
                            <td><?= htmlspecialchars($fila['email']) ?></td>
                            <td><span class="badge bg-light text-dark border"><?= ($fila['rol']!="") ? htmlspecialchars($fila['rol']) : "Sin rol" ?></span></td>
                            <td><?= date("d/m/Y H:i", strtotime($fila['fecha_registro'])) ?></td>
                            <td>
                                <?php if($fila['actividad_usuario']==1){ ?>
                                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill">Activo</span>
                                <?php }else{ ?>
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill">Inactivo</span>
                                <?php } ?>
                            </td>
                            <td class="text-end pe-4">
                                <a href="index.php?seccion=usuarios&accion=modificar&id=<?= $fila['id_usuario'] ?>" 
                                   class="btn btn-sm rounded-pill px-3" title="Editar"
                                   style="border-color:#E6C24D;color:#E6C24D"><i class="fas fa-pen"></i></a>
                                <a href="index.php?seccion=usuarios&accion=listar&ideliminar=<?= $fila['id_usuario'] ?>" 
                                   class="btn btn-sm rounded-pill px-3" title="Eliminar"
                                   style="border-color:#E57373;color:#E57373"
                                   onclick="return confirm('¿Está seguro de eliminar este usuario?');"><i class="fas fa-trash"></i></a>
                            </td>
                        </tr>
                        <?php } }else{ ?>
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">No existen usuarios registrados.</td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php render_paginador($pag); ?>
    </div>
</div>