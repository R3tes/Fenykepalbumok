<?php
$username = 'aron';
$password = '123';
$connection_string = 'localhost/XE';

$conn= oci_connect($username, $password, $connection_string, 'AL32UTF8');

if (!$conn) {
    $e = oci_error();
    die("Nem sikerült csatlakozni az adatbázishoz: " . $e['message']);
}
?>
