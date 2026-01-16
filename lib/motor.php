<?php
include_once("conexion.php");

if(!isset($_SESSION))
{
	session_start();
	$oCon = new conexion();
	$_SESSION['con']= $oCon->getConexion();
}
include_once("Clases/Empleado.php");
include_once("Clases/Manejador.php");
include_once("Clases/ManejadorUsuario.php");
include_once("Clases/ManejadorSolicitud.php");
include_once("Clases/Usuario.php");
include_once("Clases/SolicitudHE.php");
include_once("Clases/ManejadorPunch.php");
include_once("Clases/Extractor.php");
include_once("Clases/Transporte.php");
include_once("Clases/ManejadorTransporte.php");
include_once("Clases/Correo.php");
//include_once("Clases/ManejadorCorreo.php");
include_once("Clases/SolicitudAutorizada.php");
include_once("Clases/ManejadorSAutorizadas.php");
include_once("Clases/DietaViatico.php");
include_once("Clases/ManejadorDietaViatico.php");
include_once("Clases/DiaFeriado.php");
include_once("Clases/ManejadorDiasFeriados.php");
include_once("Clases/TablaViatico.php");
include_once("Clases/ManejadorTablaViatico.php");
include_once("Clases/Cargo.php");
include_once("Clases/ManejadorCargo.php");
include_once("Clases/Departamento.php");
include_once("Clases/ManejadorDepartamento.php");
include_once("Clases/Region.php");
include_once("Clases/CSP.php");
include_once("Clases/ManejadorRegionCSP.php");
include_once("Clases/GrupoEmpleado.php");
include_once("Clases/seguridad.php");
$_SESSION['m']= new Manejador();
date_default_timezone_set('America/Santo_Domingo');
