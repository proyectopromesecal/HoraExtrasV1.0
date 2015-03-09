<?php 
include('lib/motor.php');
if(!isset($_SESSION)){
	session_start();
}

$s = new Seguridad();

if($s->verificar())
{
	switch ($s->verificarTipo())
	{
		case "Viewer": 
		header('Location:reportes/generadorreportes.php');
		break;
		
		case "Administrador":
		header('Location:mantenimientoEmpleados.php');
		break;

		case "Secretaria":
		header('Location:principal.php');
		break;		
		
		case "SuperAdmin":
		header('Location:principal.php');
		break;		
		
		case "Pago":
		header('Location:principal.php');
		break;
		
		case "Autorizador":
		header('Location:principal.php');
		break;

		case "Asistente":
		header('Location:principal.php');
		break;


		case "Soporte":
		header('Location:principal.php');
		break;
	}
}
else
{
	header('Location:Login.php');
}
