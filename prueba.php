<?php
//$servername = "localhost";
//$username = "paises";
//$password = "paises";
//$dbname = "paises";
require_once "config.inc.php";
// Create connection
//$conn = mysqli_connect($servername, $username, $password, $dbname);
$conn = mysqli_connect($config['dbhost'], $config['dblogin'], $config['dbpass'], $config['dbname']);
// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

$sql = "SELECT * FROM ciudades";
$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) > 0) {
    // output data of each row
    while($row = mysqli_fetch_assoc($result)) {
        echo "id: " . $row["id"]. " - Name: " . $row["ciudad"]. " " . $row["pais"]. "<br>";
    }
} else {
    echo "0 results";
}

mysqli_close($conn);
?>