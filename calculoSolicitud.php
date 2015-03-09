<?php 
include('lib/motor.php');
if(!isset($_SESSION)){
	session_start();
}

$s = new Seguridad();

if($s->verificar())
{
	if (strcmp($s->verificarTipo(),"Secretaria") ==0 || strcmp($s->verificarTipo(),"SuperAdmin") ==0 or strcmp($s->verificarTipo(), "Asistente") ==0)
	{
		$idS;
		$m = new Manejador();
		$dpto = $_SESSION['dpto'];
		/*
		echo '<pre>';
		echo print_r($_POST);
		echo '</pre>';
		*/
	}
	else
	{
		header('Location:login.php');
	}
}
else
{
	header('Location:Login.php');
}

if (isset($_GET))
{
	if (isset($_GET['s']) && !empty($_GET['s']) )
	{
		$_SESSION['idS'] = $_GET['s'];
		$query="SELECT empleado.id AS id, horario.id AS horario, solicitudhe.fecha as fecha
				FROM empleado, horario, solicitudes, solicitudhe, solicitudes_autorizadas
				WHERE solicitudhe.id = solicitudes_autorizadas.id_solicitud
				AND tipo = 'HoraExtra'
				AND autorizado = 1
				AND empleado.id = solicitudes.id_empleado
				AND solicitudhe.id = solicitudes.id_solicitud
				AND empleado.id = horario.id_empleado
				AND horario.fecha = solicitudhe.fecha
				AND solicitudhe.id ={$_GET['s']}";
		$params = array();
		$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );
		$rsFormulario = sqlsrv_query($_SESSION['con'],$query, $params, $options);
		if($rsFormulario)
		{
			if(sqlsrv_num_rows($rsFormulario) >0)
			{
				$fecha;
				while($fila=sqlsrv_fetch_array($rsFormulario, SQLSRV_FETCH_ASSOC))
				{
					$seleccionados[] = $fila['id'];
					$horario[] = $fila['horario'];
					$fecha = $fila['fecha'];
				}
				$_SESSION['seleccionados'] = $seleccionados;
				$_SESSION['horarios'] = $horario;
				$_SESSION['fechasolicitud'] = $fecha->format('Y-m-d');
				header('Location:seleccionados.php');				
			}
			else
			{
				echo "<script>alert('Los empleados no cumplen con el tiempo/fecha correspondiente.');</script>";
				echo "<script>window.location='solicitudesPago.php'</script>";
			}
		}
	}
}
?>
<html>
	<head>
		<title>Inicio</title>
	</head>
	<style type='text/css'>
	#txt
	{
		border-width:0;
		background:#F6F8F9;
	}
	</style>
	<link rel="stylesheet" href="css/styles.css" type="text/css" media="screen">
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
	<body>
		<?php include("menu.html");?>
		<div id='page'>
			<form method='post' action='calculoSolicitud.php'>
				<div id='tabcontent' style='width:100%'>
					<div id='ext-gen91' class='x-panel-bwrap'>		
						<table width='100%' class='tab_cadre_pager'>
							<tr>
								<td align="left" class="tab_bg_2"><input class='submit' type='submit' name='btnBuscar' value='Buscar'></input></td>
								<td align="right" class="tab_bg_2"><input class='submit' type='submit' name='btnCalcular' value='Calcular'></input></td>
							</tr>
						</table>
					<br>
					<div class='center' style="overflow: auto; height:60%;" >	
						<table style='width:100%' border='1' class='tab_cadre_fixe'>
							<th width="5%">Seleccionar</th> <th width='23%'>Nombre</th> <th width="8%">Cedula</th>
							<th width="8%">Hora de Entrada</th> <th width="8%">Hora de Salida</th> <th width="7%">Fecha</th> <th width='16%'>Cargo</th>
							<th width='19%'>Departamento</th><th width="20%">Sueldo</th>
							<?php 
								if(isset($rsFormulario))
								{
									if($rsFormulario)
									{
										if(sqlsrv_num_rows($rsFormulario) >0)
										{
											while($fila=sqlsrv_fetch_array($rsFormulario, SQLSRV_FETCH_ASSOC))
											{
												echo("<tr class='tab_bg_2'>
														<td align='center'><input id='id-btn' type='checkbox' name='check[]' value='{$fila['id']}-{$fila['horario']}' readonly></input></td>
														<td><input id='txt' style='width: 100%;' type='text' name='txtNombre' value='{$fila['nombre']}' readonly></input></td>
														<td><input id='txt' style='width: 100%;' type='text' name='txtCedula' value='{$fila['cedula']}' readonly></input></td>
														<td><input id='txt' style='width: 100%;' type='text' name='txtHoraEntrada' value='{$fila['horadeentrada']}' readonly></input></td>
														<td><input id='txt' style='width: 100%;' type='text' name='txtHoraSalida' value='{$fila['horadesalida']}' readonly></input></td>
														<td><input id='txt' style='width: 100%;' type='text' name='txtFecha' value='{$fila['fecha']}' readonly></input></td>
														<td><input id='txt' style='width: 100%;' type='text' name='txtCargo' value='{$fila['cargo']}' readonly></input></td>
														<td><input id='txt' style='width: 100%;' type='text' name='txtDepartamento' value='{$fila['departamento']}' readonly></input></td>
														<td><input id='txt' style='width: 100%;' type='text' name='txtSueldo' value='{$fila['sueldo']} RD$' readonly></input></td>
													</tr>");			
											}										
										}
										else
										{
											echo "El formulario seleccionado no contiene registro de empleados que hayan trabajado en la fecha correspondiente.";
										}
									}
								}
							?>					
						</table>		
					</div>				
				</div>
			</form>			
		</div>
		</div>
		<?php include("footer.html");?>
	</body>
</html>