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
			if ($_SESSION['usuario']== 'mesa.hector@promese.promesecal.gob.do') {
				$permisos = array('Viewer', 'Administrador', 'Autorizador', 'Secretaria');
				$_SESSION['permisos'] = $permisos;
			}
			else
			{
				$permisos = array('Viewer');
				$_SESSION['permisos'] = $permisos;			
			}
			header('Location:reportes/generadorreportes.php');
			break;
		
		case "Administrador":
		if ($_SESSION['usuario']== 'chalas.milbian@promesecal.lan' ) {
			$permisos = array('Viewer', 'Administrador',);
			$_SESSION['permisos'] = $permisos;
		}
		else
		{
			$permisos = array('Administrador');
			$_SESSION['permisos'] = $permisos;		
		}
		header('Location:mantenimientoEmpleados.php');
		break;

		case "Secretaria":
		$permisos = array('Secretaria');
		$_SESSION['permisos'] = $permisos;
		header('Location:principal.php');
		break;		
		
		case "SuperAdmin":
		header('Location:principal.php');
		$_SESSION['permisos'] = array();
		break;		
		
		case "Pago":
		$permisos = array('Pago');
		$_SESSION['permisos'] = $permisos;
		header('Location:principal.php');
		break;
		
		case "Autorizador":
		$permisos = array('Autorizador');
		$_SESSION['permisos'] = $permisos;
		header('Location:autorizacionhe.php');
		break;
		
		case "Especial":
		$permisos = array('Especial');
		$_SESSION['permisos'] = $permisos;
		header('Location:principal.php');
		break;

		case "Asistente":
		$permisos = array('Asistente');
		$_SESSION['permisos'] = $permisos;
		header('Location:principal.php');
		break;

		case "Gerente":
		$permisos = array('Gerente');
		$_SESSION['permisos'] = $permisos;
		header('Location:autorizaciondv.php');
		break;

		case "Soporte":
		$_SESSION['tipo'] = 'SuperAdmin';
		header('Location:principal.php');
		break;
	}
}
else
{
	header('Location:Login.php');
}
