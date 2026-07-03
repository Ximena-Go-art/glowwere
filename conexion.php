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