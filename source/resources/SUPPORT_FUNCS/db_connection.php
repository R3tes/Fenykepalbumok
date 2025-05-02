<?php
$username = '12345';
$password = '12345';
$connection_string = 'localhost/XE';

$conn= oci_connect($username, $password, $connection_string);

if (!$conn) {
    $e = oci_error();
    die("Nem sikerült csatlakozni az adatbázishoz: " . $e['message']);
}
?>
