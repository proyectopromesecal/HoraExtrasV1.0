<?php 
if ($_POST)
{
	$fconfig = "<?php
	if (!defined('DB_HOST')) define('DB_HOST', '{$_POST['txtServidor']}');
	if (!defined('DB_USER')) define('DB_USER', '{$_POST['txtUsuario']}');
	if (!defined('DB_PASS')) define('DB_PASS', '{$_POST['txtPass']}');
	if (!defined('DB_NAME')) define('DB_NAME', '{$_POST['txtDB']}');";
	$archivo = fopen("lib/config.php", "w");
	fwrite($archivo, $fconfig);
	fclose($archivo);

	$con = mysql_connect($_POST['txtServidor'], $_POST['txtUsuario'], $_POST['txtPass']);
	if(mysql_error())
	{
		echo (mysql_error());
	}
	else
	{
		$query="CREATE DATABASE IF NOT EXISTS {$_POST['txtDB']}";
		mysql_query($query);
		header("Location:index.php");
	}

		
}		
	
?>
<html>
	<head>
		<title>Instalador</title>
		<style>
			#label
			{
				color: #000000;
				font-weight: bold;
				display: block;
				float: left;
			}
			
		.input {
			border: 1px solid #006;
			background: #ffc;
		}
		.input:hover {
			border: 1px solid #006;
			background: #C0C0C0;
		}
		.button {
			border: 1px solid #006;
			background: #4169E1;
		}
		.button:hover {
			border: 1px solid #006;
			background: #7B68EE;
		}
		br { clear: left; }
		</style>
	</head>
<body style="background-color:#b0c4de;">
	<form method="post">
	<div align="center">
	<fieldset style="width:30%; border:2px dotted black; background-color: CornflowerBlue  ;">
		<table style="background-color:#6495ED;">
			<tr>
				<td><label id = "label">Servidor:</label></td>
				<td><input type="text" name="txtServidor" class="input"></input></td>
			</tr>
			<tr>
				<td><label id = "label">Usuario:</label></td>
				<td><input type="text" name="txtUsuario" class="input"></input></td>
			</tr>
			<tr>
				<td><label id = "label">Contraseña:</label></td>
				<td><input type="password" name="txtPass" class="input"></input></td>
			</tr>
			<tr>
				<td><label id = "label">Base de Datos:</label></td>
				<td><input type="text" name="txtDB" class="input"></input></td>
			</tr>
			<tr>
				<td colspan=2 align="right"><input type="submit" value="Crear" class="button"></input></td>
			</tr>
		</table>
		</fieldset>
		</div>
	</form>
</body>
</html>