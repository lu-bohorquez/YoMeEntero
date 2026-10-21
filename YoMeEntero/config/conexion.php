<?php
$host = 'localhost';
$user = 'root';
$pass = '';
$db   = 'db_colegio_palermo';

$mysqli = new mysqli($host, $user, $pass, $db);

if ($mysqli->connect_error) {
    die('Error de Conexión (' . $mysqli->connect_errno . ') ' . $mysqli->connect_error);
}

$mysqli->set_charset("utf8mb4");
?>