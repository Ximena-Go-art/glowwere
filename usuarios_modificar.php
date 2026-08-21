<?php

include "conexion.php";

$cnn = conection();

/* =====================================
   GUARDAR (ALTA O MODIFICACIÓN)
===================================== */

if (isset($_POST['btnGuardar'])) {

    $id_usuario = isset($_POST['id_usuario']) ? intval($_POST['id_usuario']) : 0;

    $usuario = mysqli_real_escape_string($cnn, $_POST['usuario']);
    $email = mysqli_real_escape_string($cnn, $_POST['email']);
    $contrasena = mysqli_real_escape_string($cnn, $_POST['contrasena']);
    $id_rol = intval($_POST['id_rol']);
    $activo = isset($_POST['actividad_usuario']) ? 1 : 0;

    /* ========= ALTA ========= */

    if ($id_usuario == 0) {

        $sql = "INSERT INTO usuarios
                (
                    usuario,
                    email,
                    contraseña,
                    id_rol,
                    actividad_usuario,
                    fecha_registro,
                    deleted
                )
                VALUES
                (
                    '$usuario',
                    '$email',
                    '$contrasena',
                    '$id_rol',
                    '$activo',
                    NOW(),
                    0
                )";

        $resp = mysqli_query($cnn, $sql);

        if ($resp) {

            $nuevoId = mysqli_insert_id($cnn);

            registrar_accion($cnn, "Usuarios", "Registró el usuario '$usuario'");

            echo "<script>

                    alert('Usuario guardado correctamente');

                    window.location.href='index.php?seccion=usuarios&accion=modificar&id=$nuevoId';

                  </script>";

            exit;
        } else {

            die("Error: " . mysqli_error($cnn));

        }

    }

    /* ========= MODIFICACIÓN ========= */

    else {

        $sql = "UPDATE usuarios
                SET

                    usuario='$usuario',
                    email='$email',
                    contraseña='$contrasena',
                    id_rol='$id_rol',
                    actividad_usuario='$activo'

                WHERE id_usuario='$id_usuario'";

        $resp = mysqli_query($cnn, $sql);

        if ($resp) {

            registrar_accion($cnn, "Usuarios", "Modificó el usuario #$id_usuario ($usuario)");

            echo "<script>

                    alert('Usuario modificado correctamente');

                    window.location.href='index.php?seccion=usuarios&accion=listar';

                  </script>";

            exit;

        } else {

            die("Error: " . mysqli_error($cnn));

        }

    }

}


/* =====================================
   CARGAR DATOS
===================================== */

$campos = [];

if (isset($_GET['id'])) {

    $idUsuario = intval($_GET['id']);

    $sql = "SELECT *
            FROM usuarios
            WHERE id_usuario = $idUsuario";

    $result = mysqli_query($cnn, $sql);

    if (mysqli_num_rows($result) > 0) {

        $campos = mysqli_fetch_assoc($result);

    }

}

?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold m-0"><?= isset($campos['id_usuario']) ? 'Editar Usuario' : 'Nuevo Usuario' ?></h2>
            <p class="text-muted small">Completa los datos para <?= isset($campos['id_usuario']) ? 'actualizar' : 'registrar' ?> el usuario</p>
        </div>
        <a href="index.php?seccion=usuarios&accion=listar" class="btn btn-outline-secondary rounded-pill px-4">Volver</a>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4">
                    <form method="POST">
                        <input type="hidden" name="id_usuario" value="<?= isset($campos['id_usuario']) ? $campos['id_usuario'] : '' ?>">

                        <div class="mb-3">
                            <label class="form-label fw-bold small text-muted">Usuario</label>
                            <input type="text" class="form-control form-control-lg rounded-3" name="usuario" required
                                   value="<?= isset($campos['usuario']) ? htmlspecialchars($campos['usuario']) : '' ?>" placeholder="Nombre de usuario">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold small text-muted">Email</label>
                            <input type="email" class="form-control form-control-lg rounded-3" name="email" required
                                   value="<?= isset($campos['email']) ? htmlspecialchars($campos['email']) : '' ?>" placeholder="correo@ejemplo.com">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold small text-muted">Contraseña</label>
                            <input type="password" class="form-control form-control-lg rounded-3" name="contrasena" required
                                   value="<?= isset($campos['contraseña']) ? htmlspecialchars($campos['contraseña']) : '' ?>" placeholder="••••••••">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold small text-muted">Rol</label>
                            <select name="id_rol" class="form-select form-select-lg rounded-3" required>
                                <option value="">Seleccione un rol</option>
                                <?php
                                $sqlRoles = "SELECT * FROM roles WHERE deleted = 0 ORDER BY nombre";
                                $resultadoRoles = mysqli_query($cnn, $sqlRoles);
                                while ($rol = mysqli_fetch_assoc($resultadoRoles)) {
                                    $selected = (isset($campos['id_rol']) && $campos['id_rol'] == $rol['id_rol']) ? 'selected' : '';
                                ?>
                                    <option value="<?= $rol['id_rol'] ?>" <?= $selected ?>><?= htmlspecialchars($rol['nombre']) ?></option>
                                <?php } ?>
                            </select>
                        </div>

                        <div class="mb-4 form-check form-switch">
                            <input type="checkbox" class="form-check-input" name="actividad_usuario" value="1" id="activo"
                                   <?= (isset($campos['actividad_usuario']) && $campos['actividad_usuario'] == 1) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="activo">Usuario Activo</label>
                        </div>

                        <div class="d-grid">
                            <button type="submit" name="btnGuardar" class="btn btn-danger btn-lg rounded-pill shadow-sm">
                                <i class="fas fa-save me-2"></i> Guardar Cambios
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>