<?php 
include('lib/motor.php');

$s = new Seguridad();

if(!isset($_SESSION)){
	session_start();
}
if($s->verificar())
{
	if(strcmp($s->verificarTipo(), "Secretaria") ==0  or strcmp($s->verificarTipo(), "SuperAdmin") ==0 or strcmp($s->verificarTipo(), "Asistente") ==0)
	{		
		$_SESSION['rutaActual']="Solicitudes > Pago de Horas Extra";
		#echo '<pre>';
		#print_r($_POST);
		#echo '</pre>';
		
		#echo '<pre>';
		#print_r($_GET);
		#echo '</pre>';	
	}
	else
	{
		header("Location:index.php");
	}
}
else
{
	header('Location:Login.php');
}
if($_POST)
{
	$rsCustom;
	if(isset($_POST['btnBuscar']))
	{
		if ($_POST['slcFiltro']=='fecha2')
		{
			$rsCustom = ManejadorSolicitud::solicitudesAprobadas($_SESSION['dpto'], "",$_POST['slcFecha'], $_SESSION['usuario']);
		}
		else if ($_POST['slcFiltro']=='fecha')
		{	
			$rsCustom = ManejadorSolicitud::solicitudesAprobadas($_SESSION['dpto'], $_POST['slcFecha'],"", $_SESSION['usuario']);
		}
	}
	else if (isset($_POST['btnGenerar']))
	{
		if(isset($_POST['check']))
		{
			$t = count($_POST["check"]);
			if($t>1)
			{
				echo "<script>alert('Para generar el pago seleccione solo 1 solicitud');</script>";	
			}
			else
			{
				foreach($_POST["check"] as $valor)// por cada valor de los checkbox seleccionados en POST
				{
					$valores = explode("-", $valor);
					$idSl = $valores[0];
					header("Location:calculoSolicitud.php?s={$idSl}");
				}			
			}
		}
		else
		{
			echo "<script>alert('Seleccione una solicitud para realizar el calculo);</script>";
		}
	}
}
?>
<html>
	<header>
		<title>Pago Horas Extra</title>
		<link rel="stylesheet" href="css/styles.css" type="text/css" media="screen">
		<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
	</header>
	<body>
		<?php include("menu.html");?>
		<div id='page'>
			<center></br>
				<fieldset style="width:70%;border-radius:8px;"><br>
					<form method="post" action="solicitudespago.php">
						<div style="width:100%;">
							<select name="slcFiltro"><option value='fecha'>Fecha de Creacion</option><option value='fecha2'>Fecha Solicitada</option></select> &nbsp <label style="color:white;">ES</label> &nbsp 
							<input type="date" name="slcFecha" value=''></input>&nbsp 
							<button type="submit" name="btnBuscar" class="submit">Buscar</button>
						</div><br>
						<div class='center' style="height:50%;overflow:auto;">
							<table class='tab_cadre_fixe' style="width:100%;">
								<tr class='tab_bg_2'>
									<th>Seleccion</th><th>No.Solicitud</th><th>Estado</th><th>Fecha de creacion</th><th>Fecha solicitada</th><th>Cantidad de empleados</th>
								</tr>
								<?php 
									$rs;
									if(strstr($_SESSION['tipo'], "SuperAdmin"))
									{
										if(!empty($rsCustom))
										{
											$rs = $rsCustom;
										}	
										else
										{
											$rs =ManejadorSolicitud::solicitudesAprobadas('',"","");
										}
									}
									else
									{
										if(!empty($rsCustom))
										{
											$rs = $rsCustom;
										}
										else
										{
											$rs =ManejadorSolicitud::solicitudesAprobadas($_SESSION['dpto'],"","", $_SESSION['usuario']);
										}
									}
									if($rs)
									{
										while($fila=sqlsrv_fetch_array($rs, SQLSRV_FETCH_ASSOC))
										{
											$enlace="";
											$estado = ManejadorSolicitud::verEstado($fila['id']);
											$cantidad = ManejadorSolicitud::cantidadEmpleados($fila['id']);
											echo "
												<tr class='tab_bg_2'>
													<td align='center'><input type='checkbox' name='check[]' value='{$fila['id']}-{$estado}' readonly></input></td>
													<td>{$fila['noOficioHE']}</td>
													<td>{$estado}</td>
													<td>{$fila['fecha_creacion']->format('d/m/Y')}</td>
													<td>{$fila['fecha']->format('d/m/Y')}</td>
													<td>"; echo $cantidad ."</td>
												</tr>";
										}
									}
									else
									{
										echo "<script language='javascript' type='text/javascript'>alert('Hubo un problema al cargar las solicitudes de la base de datos.')</script>";
									}
								?>
							</table>
						</div>
						<br>	
						<button type="submit" name="btnGenerar" class="submit" style="width:300px;">Generar solicitud de pago</button>
					</form>
				</fieldset>	
			</center>
		</div>
		<?php include("footer.html");?>
	</body>
</html>