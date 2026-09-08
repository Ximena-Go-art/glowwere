<?php
include "conexion.php";
include_once "paginador.php";
$cnn = conection();

/* ============================
   LISTADO DE REGISTROS
============================ */

/* ============================
   ELIMINAR (BAJA LÓGICA)
============================ */

if (isset($_GET['ideliminar'])) {

    $idEliminar = intval($_GET['ideliminar']);

    $sql = "UPDATE registros
            SET deleted = 1
            WHERE id_registros = $idEliminar";

    $resp = mysqli_query($cnn, $sql);

    if (!$resp) {

        die("Error: " . mysqli_error($cnn));

    } else {

        echo "<script>

            alert('Registro eliminado correctamente.');

            window.location.href='index.php?seccion=registros&accion=listar';

        </script>";

        exit;

    }

}

/* ============================
   FILTROS
============================ */

$filtro_usuario = (isset($_GET['f_usuario']) && is_numeric($_GET['f_usuario'])) ? intval($_GET['f_usuario']) : 0;
$filtro_seccion = isset($_GET['f_seccion']) ? trim($_GET['f_seccion']) : '';
$filtro_buscar  = isset($_GET['f_buscar']) ? trim($_GET['f_buscar']) : '';
$filtro_desde   = (isset($_GET['f_desde']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['f_desde'])) ? $_GET['f_desde'] : '';
$filtro_hasta   = (isset($_GET['f_hasta']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['f_hasta'])) ? $_GET['f_hasta'] : '';

$condiciones = array("r.deleted = 0");

if ($filtro_usuario > 0) {
    $condiciones[] = "r.id_usuario = $filtro_usuario";
}

if ($filtro_seccion != '') {
    $seccionSegura = mysqli_real_escape_string($cnn, $filtro_seccion);
    $condiciones[] = "r.seccion = '$seccionSegura'";
}

if ($filtro_buscar != '') {
    $buscarSeguro = mysqli_real_escape_string($cnn, $filtro_buscar);
    $condiciones[] = "(u.usuario LIKE '%$buscarSeguro%' OR r.accion LIKE '%$buscarSeguro%' OR r.link LIKE '%$buscarSeguro%')";
}

if ($filtro_desde != '') {
    $condiciones[] = "r.fecha_hora >= '$filtro_desde 00:00:00'";
}

if ($filtro_hasta != '') {
    $condiciones[] = "r.fecha_hora <= '$filtro_hasta 23:59:59'";
}

$where = implode(" AND ", $condiciones);

/* ============================
   ESTADÍSTICAS GENERALES
============================ */

$sqlStats = "SELECT
                COUNT(*) AS total,
                SUM(CASE WHEN DATE(fecha_hora) = CURDATE() THEN 1 ELSE 0 END) AS hoy,
                SUM(CASE WHEN seccion = 'Ventas' AND accion LIKE 'Registró%' AND DATE(fecha_hora) = CURDATE() THEN 1 ELSE 0 END) AS ventas_hoy,
                COUNT(DISTINCT CASE WHEN id_usuario > 0 THEN id_usuario END) AS usuarios
             FROM registros
             WHERE deleted = 0";

$resStats = mysqli_query($cnn, $sqlStats);
$stats = mysqli_fetch_assoc($resStats);

$totalRegistros  = intval($stats['total']);
$hoyRegistros    = intval($stats['hoy']);
$ventasHoy       = intval($stats['ventas_hoy']);
$usuariosActivos = intval($stats['usuarios']);

/* Combos para los filtros */

$listaUsuarios  = mysqli_query($cnn, "SELECT id_usuario, usuario FROM usuarios WHERE deleted = 0 ORDER BY usuario ASC");
$listaSecciones = mysqli_query($cnn, "SELECT DISTINCT seccion FROM registros WHERE deleted = 0 ORDER BY seccion ASC");

/* ============================
   LISTADO FILTRADO
============================ */

$sql = "SELECT
            r.*,
            u.usuario
        FROM registros r
        LEFT JOIN usuarios u
            ON r.id_usuario = u.id_usuario
        WHERE $where
        ORDER BY id_registros DESC";

$pag = paginar_consulta($cnn, $sql, 15);
$result = $pag['data'];

$hayFiltros = ($filtro_usuario > 0 || $filtro_seccion != '' || $filtro_buscar != '' || $filtro_desde != '' || $filtro_hasta != '');

$coloresSeccion = array(
    'Ventas'              => 'danger',
    'Detalle de Ventas'   => 'danger',
    'Compras'             => 'primary',
    'Detalle de Compras'  => 'primary',
    'Sesiones'            => 'success',
    'Usuarios'            => 'warning',
    'Productos'           => 'info',
    'Familias'            => 'info',
    'Clientes'            => 'secondary',
    'Proveedores'         => 'secondary',
);

?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold m-0">Registros del Sistema</h2>
            <p class="text-muted small">Bitácora de actividades del sistema</p>
        </div>
    </div>

    <!-- TARJETAS DE ESTADÍSTICAS -->
    <div class="row g-3 mb-4">

        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 me-3" style="width:48px;height:48px;background-color:#FCE4EC;color:#F48FB1">
                        <i class="fas fa-clipboard-list"></i>
                    </div>
                    <div>
                        <div class="small fw-bold" style="color:#4a454a">ACCIONES REGISTRADAS</div>
                        <div class="fs-4 fw-bold"><?= $totalRegistros ?></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 me-3" style="width:48px;height:48px;background-color:#F3E5F5;color:#C5B4E3">
                        <i class="fas fa-calendar-day"></i>
                    </div>
                    <div>
                        <div class="small fw-bold" style="color:#4a454a">MOVIMIENTOS DE HOY</div>
                        <div class="fs-4 fw-bold"><?= $hoyRegistros ?></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 me-3" style="width:48px;height:48px;background-color:#E8F5E9;color:#4CAF50">
                        <i class="fas fa-cart-shopping"></i>
                    </div>
                    <div>
                        <div class="small fw-bold" style="color:#4a454a">VENTAS DE HOY</div>
                        <div class="fs-4 fw-bold"><?= $ventasHoy ?></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 me-3" style="width:48px;height:48px;background-color:#FFF8E1;color:#E6C24D">
                        <i class="fas fa-users"></i>
                    </div>
                    <div>
                        <div class="small fw-bold" style="color:#4a454a">USUARIOS CON ACTIVIDAD</div>
                        <div class="fs-4 fw-bold"><?= $usuariosActivos ?></div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- FILTROS -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4">
            <form method="GET" action="index.php">
                <input type="hidden" name="seccion" value="registros">
                <input type="hidden" name="accion" value="listar">

                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label small fw-bold text-muted">Usuario</label>
                        <select name="f_usuario" class="form-select">
                            <option value="0">Todos los usuarios</option>
                            <?php if($listaUsuarios){ while($u = mysqli_fetch_assoc($listaUsuarios)){ ?>
                                <option value="<?= $u['id_usuario'] ?>" <?= ($filtro_usuario == $u['id_usuario']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($u['usuario']) ?>
                                </option>
                            <?php } } ?>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label small fw-bold text-muted">Sección</label>
                        <select name="f_seccion" class="form-select">
                            <option value="">Todas las secciones</option>
                            <?php if($listaSecciones){ while($s = mysqli_fetch_assoc($listaSecciones)){ ?>
                                <option value="<?= htmlspecialchars($s['seccion']) ?>" <?= ($filtro_seccion == $s['seccion']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($s['seccion']) ?>
                                </option>
                            <?php } } ?>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label small fw-bold text-muted">Buscar</label>
                        <input type="text" name="f_buscar" class="form-control" placeholder="Acción, usuario o link..."
                               value="<?= htmlspecialchars($filtro_buscar) ?>">
                    </div>
                </div>

                <div class="row g-3 mt-0">
                    <div class="col-md-4">
                        <label class="form-label small fw-bold text-muted">Desde</label>
                        <input type="date" name="f_desde" class="form-control" value="<?= $filtro_desde ?>">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label small fw-bold text-muted">Hasta</label>
                        <input type="date" name="f_hasta" class="form-control" value="<?= $filtro_hasta ?>">
                    </div>

                    <div class="col-md-4 d-flex align-items-end gap-2">
                        <button type="submit" class="btn rounded-pill px-4" style="background-color:#F48FB1;border-color:#F48FB1;color:#fff">
                            <i class="fas fa-filter me-1"></i> Filtrar
                        </button>
                        <?php if($hayFiltros){ ?>
                            <a href="index.php?seccion=registros&accion=listar" class="btn rounded-pill px-4"
                               style="border-color:#C5B4E3;color:#C5B4E3">
                                Limpiar
                            </a>
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
                            <th>Fecha y Hora</th>
                            <th>Usuario</th>
                            <th>Sección</th>
                            <th>Acción</th>
                            <th>Link</th>
                            <th>S.O.</th>
                            <th class="text-end pe-4">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(mysqli_num_rows($result)>0){ while($fila = mysqli_fetch_assoc($result)){ 
                            $color = isset($coloresSeccion[$fila['seccion']]) ? $coloresSeccion[$fila['seccion']] : 'dark';
                        ?>
                        <tr>
                            <td class="ps-4 fw-bold text-muted"><?= $fila['id_registros'] ?></td>
                            <td><?= date("d/m/Y H:i:s", strtotime($fila['fecha_hora'])) ?></td>
                            <td class="fw-bold"><?= htmlspecialchars($fila['usuario'] ?? 'Sistema') ?></td>
                            <td><span class="badge bg-<?= $color ?>-subtle text-<?= $color ?> border border-<?= $color ?>-subtle rounded-pill"><?= htmlspecialchars($fila['seccion']) ?></span></td>
                            <td><?= htmlspecialchars($fila['accion']) ?></td>
                            <td><small class="text-muted"><?= htmlspecialchars($fila['link']) ?></small></td>
                            <td><?= htmlspecialchars($fila['S_O']) ?></td>
                            <td class="text-end pe-4">
                                <a href="index.php?seccion=registros&accion=listar&ideliminar=<?= $fila['id_registros'] ?>" 
                                   class="btn btn-sm rounded-pill px-3" title="Eliminar"
                                   style="border-color:#E57373;color:#E57373"
                                   onclick="return confirm('¿Está seguro de eliminar este registro?');"><i class="fas fa-trash"></i></a>
                            </td>
                        </tr>
                        <?php } }else{ ?>
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">No existen registros.</td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php render_paginador($pag); ?>
    </div>
</div>