<?php
    error_reporting(E_ERROR | E_PARSE);
	include_once("../motor.php");
	
	$s = new Seguridad();
	$usr='';
	
	$usuario = $_POST['txtUsuario'].'@promese.promesecal.gob.do';
	$clave = $_POST['txtClave'];
	if (isset($_POST['slcUsuario'])) {
		$usr=$_POST['slcUsuario'];
	}

	if ($s->conectarLdap($usuario, $clave))
	{

		//USUARIO ESPECIFICO
		/*
		if (strcmp("cruz.rosmery@promesecal.lan", $usuario)==0) {
			//ManejadorUsuario::crearSesion('encarnacion.licet@promesecal.lan',$usr);
			header("Location:../../index.php");	

		}
		else
		{
			ManejadorUsuario::crearSesion($usuario,$usr);
			header("Location:../../index.php");		
		
		}
		*/
		ManejadorUsuario::crearSesion($usuario,$usr);
		header("Location:../../index.php");	

	} 
	else 
	{
		
		echo "  <script language='javascript'>
					alert('Usuario o contraseña incorrecto $usuario');
					window.location.href='../../login.php';	
				</script>";		
	}
 ?>
 <html>
	<head>
	</head>
	<body >
	</body>
 </html>


