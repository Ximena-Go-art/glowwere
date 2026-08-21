<?php

/*function conection() {
  $db_host = "192.185.194.188";
  $db_user = "d26com_usr_general";
  $db_pass = "Isp203040";
  $db_name = "d26com_db_ximena";

$cnn = mysqli_connect($db_host, $db_user, $db_pass, $db_name);*/

function conection() {
  $db_host = "localhost";
    $db_user = "root";
    $db_pass = "";
    $db_name = "d26com_db_ximena";

$cnn = mysqli_connect($db_host, $db_user, $db_pass, $db_name);

if (!$cnn) {
    die('Error de conexión a la base de datos.');
}
return $cnn;
}

function registrar_accion($cnn, $seccion, $accion) {

  if (session_status() != PHP_SESSION_ACTIVE) {
      session_start();
  }

  $id_usuario = isset($_SESSION['id_usuario']) ? intval($_SESSION['id_usuario']) : 0;

  $link = basename($_SERVER['PHP_SELF']);
  if (isset($_SERVER['QUERY_STRING']) && $_SERVER['QUERY_STRING'] != '') {
      $link .= '?' . $_SERVER['QUERY_STRING'];
  }

  $so = 'Desconocido';
  if (isset($_SERVER['HTTP_USER_AGENT'])) {
      $ua = $_SERVER['HTTP_USER_AGENT'];
      if     (stripos($ua, 'Windows') !== false) { $so = 'Windows'; }
      elseif (stripos($ua, 'Android') !== false) { $so = 'Android'; }
      elseif (stripos($ua, 'iPhone')  !== false || stripos($ua, 'iPad') !== false) { $so = 'iOS'; }
      elseif (stripos($ua, 'Mac')     !== false) { $so = 'Mac'; }
      elseif (stripos($ua, 'Linux')   !== false) { $so = 'Linux'; }
  }

  $seccion = mysqli_real_escape_string($cnn, substr($seccion, 0, 100));
  $accion  = mysqli_real_escape_string($cnn, substr($accion, 0, 255));
  $link    = mysqli_real_escape_string($cnn, substr($link, 0, 255));

  return mysqli_query($cnn, "INSERT INTO registros (id_usuario, fecha_hora, seccion, accion, link, `S.O`, deleted)
                             VALUES ($id_usuario, NOW(), '$seccion', '$accion', '$link', '$so', 0)");
}