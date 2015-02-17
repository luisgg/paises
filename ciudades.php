<?php
//session_start();

require_once "config.inc.php";

// Funcion de mi libreria para generar el select 
function WriteCombo($vals, $name, $selected){
    $res = "<select name='$name' method=\"POST\" onChange=document.forms.ActionForm.submit() > \n";
    while(list($val, $opt)=each($vals)){
        $res .= "<option value='$val'". (($val==$selected)?' selected="selected"':''). ">$opt</option>\n";
    }
    $res .= "</select>\n";
    return $res;
}

// Funcion de filtrado de url, copyright w3schools.com  
function test_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Declaramos los valores del select
$values = array('0' => 'No tiene metro', '1' => 'Tiene Metro', '2' => 'Indiferente');
// Declaramos e inicializamos las variables, para despues cargarlas ya filtradas por test_input()
$iniciado = $posicion = "";
$metro = "2";

// Copiamos los valores a variables para evitar errores de Undefined type
// ademas es mas rapido llamar a la variable que la funcion $_GET o $_POST
// lo que hacemos es forzar que nos pasen los datos por $_POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    (isset($_POST['iniciado'])) ? $iniciado = true                           : $iniciado = false ;  
    (isset($_POST['accion']))   ? $accion = test_input($_POST['accion'])     : $accion=0 ;
    (isset($_POST['posicion'])) ? $posicion += test_input($_POST['posicion']): $posicion=0 ;  
    (isset($_POST['metro']))    ? $metro = test_input($_POST['metro'])       : $metro=2 ;
} else {
    (isset($_GET['accion']))   ? $accion = test_input($_GET['accion'])     : $accion="" ;
    (isset($_GET['posicion'])) ? $posicion += test_input($_GET['posicion']): $posicion=0 ;  
    (isset($_GET['metro']))    ? $metro = test_input($_GET['metro'])       : $metro=2 ;
    ($accion=="anterior") ? $posicion = $posicion-$config['rows_per_page'] : $posicion = $posicion;
}

// Iniciamos el programa .
echo "<h3> Ejercicio : Tabla de ciudades </h3> <br>";
// Abrimos la conexion
$conexion = mysqli_connect($config['dbhost'], $config['dblogin'], $config['dbpass'], $config['dbname']);
// Comprobamos la conexion
if (!$conexion)
{
    echo "La conexion con MySQL ha fallado.";
    die ( "Codigo del error de conexion : " . mysqli_connect_error());
}

// Forzamos que coja utf8
mysqli_set_charset($conexion,$config['encoding']);
// Componemos la cadena busqueda con el filtrado del metro
($metro <> 2) ? $filtro = " WHERE tieneMetro =".$metro  : $filtro = "";
// Llamamos a la DB
$sql = "SELECT * FROM ciudades".$filtro;
$resultados = mysqli_query($conexion, $sql);
// Cargamos los valores de definicion de la tabla
if (mysqli_num_rows($resultados) > 0) {
    $numFilas = mysqli_num_rows($resultados);
    $numColumnas = mysqli_num_fields($resultados);
} else {
    echo "0 resultados";
}
?>

<?php 
//<form name="ActionForm" method="post" action="">
echo "<form name=\"ActionForm\" method=\"post\"" .htmlspecialchars($_SERVER['PHP_SELF']) ."\ >";
echo "<td>"."Seleccione si tiene metro : ".WriteCombo($values,"metro","$metro")."</td>"; 
echo "<input type=\"hidden\" name=\"posicion\" value=$posicion >";
?>
<table cellpadding="1" cellspacing="0" border="5" bgcolor="#ababab">
    <tr>
        <td>
<?php
//echo "Seleccione si tiene metro : ".WriteCombo($values,"metro","$metro")."</td>"; 
                    ?>
            <table cellpadding="0" cellspacing="1" border="2" class="datatable" bgcolor="#acacac">
                <tr>
                    <?php
                    // Leemos los nombres de los campos para ponerle titulo
                    for ($contColumnas = 0; $contColumnas < $numColumnas; $contColumnas++) {
                        //Montamos el array de nombres
                        $nombre[$contColumnas] = mysqli_fetch_field_direct($resultados,$contColumnas)->name;
                        echo "<th>".$nombre[$contColumnas]."</th>";
                    }
                    ?>
                </tr>
                <?php  // DATA
                // Montamos la tabla
                for ($contFilas = $posicion ; $contFilas <= $posicion+$config['rows_per_page']-1; $contFilas++) {
                    mysqli_data_seek($resultados,$contFilas);
                    $fila = mysqli_fetch_assoc($resultados); 
                    echo "<tr>";
                    foreach ($nombre as $value) {
                        echo "<th>".$fila[$value]."</th>";
                    }
                    echo "</tr>";
                }
                ?>
            </table>
        </td>
    </tr>
</table><br />
<?php // PAGER
// Chequeamos que estamos posicionados despues de inicio
if ($posicion >= $config['rows_per_page'] ) {
    // si lo esta  lo echoea en modo activo
    echo "<br><a href=\"?accion=anterior&amp;posicion=$posicion&amp;metro=$metro\">PgArriba</a>&nbsp;";
} else {
    // si no lo esta se echoea en modo pasivo
    echo "<br>PgArriba&nbsp;";
}
// Echoeamos la pagina actual 
echo "   [ Pagina Actual -".(($posicion+$config['rows_per_page'])/$config['rows_per_page'])."]   ";  

// Chequemos si estamos en la ultima pagina   
if ($posicion+$config['rows_per_page'] < $numFilas ) {
    $tmpos = $posicion + 2 ;
    echo "<a href=\"?accion=siguiente&amp;posicion=$tmpos&amp;metro=$metro\">PgAbajo</a>&nbsp;";
} else {
    echo "PgAbajo&nbsp;";
}
echo "<br />";
?>
<br />
<table cellpadding="1" cellspacing="0" border="0" bgcolor="#ababab">
    <tr><td>
        <table cellpadding="1" cellspacing="0" border="0" bgcolor="#fcfcfc">
            <tr><td>
                    <?php { } ?>
                </td></tr>
        </table>
        </td></tr>
</table>
</form>

<?php 
mysqli_close($conexion);
?>

</body>
</html>