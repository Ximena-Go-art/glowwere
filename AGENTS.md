# AGENTS.md

Flat procedural PHP app ("Gestión de Ventas" for a cosmetics shop): PHP 8 + mysqli, no framework, no Composer, no tests/lint/build. Verification is manual: run under WAMP at `http://localhost/gestion_de_ventas/` and watch `error_log` at the repo root. UI text, sample data, and git commits are in Spanish.

## Architecture

- Single entrypoint routing: all navigation goes through `index.php?seccion=X&accion=Y`, which includes the file `{X}_{Y}.php`. Unknown combos silently fall back to `inicio.php`.
- Section files are HTML fragments (no `<html>` shell) except `login.php`. Adding a page means creating `{seccion}_{accion}.php` plus a sidebar link hardcoded in `index.php` — NOT `menu.php`, which is unused legacy.
- Session auth is checked only in `index.php` and `login.php`. Opening a section file directly by URL bypasses auth and renders an unstyled fragment.
- Active-menu highlighting in `index.php` compares `$_SESSION['usuario']` (the username string) against section names — it almost never matches; don't assume it works when touching that code.

## Database

- `conexion.php` defines `conection()` (sic), returning a mysqli handle to a REMOTE production MySQL server with hardcoded credentials; a commented-out localhost/WAMP block sits below it. Be deliberate before changing which one is active.
- There is no schema dump for the main tables — infer columns from existing queries. Only the `registros` audit table has DDL (`git show HEAD:registros.sql`; the file is deleted from the working tree).
- Soft deletes everywhere: mutations set `deleted = 1`, and every SELECT must filter `WHERE deleted = 0`.
- All SQL is built by string interpolation (no prepared statements) — that's the existing style across every file.
- `login.php` compares passwords in plain text (`$password == $fila["pass"]`).

## Page conventions

- CRUD comes in pairs per entity: `{entidad}_listar.php` (query + table + delete handler at top) and `{entidad}_modificar.php` (create/edit form; hidden `id_producto`-style field decides INSERT vs UPDATE, save handled on `isset($_POST['btnGuardar'])`).
- Redirects after save/delete are done with `echo "<script>window.location='index.php?...'</script>";` — do NOT use `header()`, which fails silently because `index.php` already emitted HTML before the include.
- Call `registrar_accion($cnn, "Sección", "acción realizada")` (defined in `conexion.php`) after every mutation; it writes to the `registros` audit table using `$_SESSION['id_usuario']`.
- Delete parameter naming is inconsistent: most `*_listar.php` read `$_GET['eliminar']`, but `usuarios_listar.php` and `registros_listar.php` read `$_GET['ideliminar']`. Match the file you're editing.
- Known broken spots (don't copy these patterns): `productos_listar.php`'s SweetAlert confirm redirects with `&ideliminar=` yet the file has no delete handler; `Swal.fire` is used in 5 files (`clientes_modificar`, `compra_detalles_listar`, `familias_modificar`, `productos_listar`, `ventas_detalles_listar`) but no sweetalert2 CDN `<script>` is loaded anywhere, so those calls throw unless the script tag is added to `index.php`.
- Styling: Bootstrap 5.3 + FontAwesome via CDN; money displayed as `number_format($monto, 2, ',', '.')`.
