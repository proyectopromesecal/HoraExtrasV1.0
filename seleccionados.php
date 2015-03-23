<?php 
include("lib/motor.php");

if(!isset($_SESSION)){
	session_start();
}
$s = new Seguridad();
if($s->verificar())
{
	if(strcmp($s->verificarTipo(), "Secretaria") ==0  or strcmp($s->verificarTipo(), "SuperAdmin") ==0 or strcmp($s->verificarTipo(), "Asistente") ==0)
	{
		$m = new Manejador();
		$c = new Calculo();
		$mensaje="";
		global $feriado;
		$feriado = false;	
		$_SESSION['titulo']="Reporte de Horas Extra";	
		//echo "<script>alert('Solicitud {$_SESSION['idS']}');</script>";	
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

///echo '<pre>';
//echo print_r($_SESSION['datos']);
//echo '</pre>';

if($_POST)
{
	$guardados="";
	$horarios= array();
	$id;
	$hor;
	$tiempExtr;
	$feriado=false;
	if(isset($_POST['btnVolver']))
	{
		if(strcmp($s->verificarTipo(), "Secretaria")==0)
		{
			header('Location:solicitudesPago.php');
		}
		else
		{
			header('Location:index.php');
		}
	}
	else if(isset($_POST['btnGuardar']))
	{
		global $arrayGuardar;
		if(isset($_POST['check'])) 
		{
			foreach($_POST["check"] as $valor)// este for es para obtener los registros seleccionados
			{
				$seleccionados[] = $valor;
			}
			
			$feriado = $m->esFeriado($_SESSION['fechasolicitud']);
			
			foreach($seleccionados as $keys)
			{
				$datos = explode("-", $keys);
				$id= $datos[0];
				$hor = $datos[1];	
				
				if($feriado)
				{
					$arrayGuardar[] = $id;
					$horarios[] = $hor;
					$guardados .= $m->obtenerNombre($id)."\\n";	
				}
				else
				{
					if(($m->obtenerHoras($id, $hor) >8 ) or($m->obtenerHoras($id, $hor) ==8 && $m->obtenerMinutos($id, $hor) >=30))
					{
						$arrayGuardar[] = $id;
						$horarios[] = $hor;
						$guardados .= $m->obtenerNombre($id)."\\n";					
					}
					else 
					{
						$mensaje.="{$m->obtenerNombre($id)}\\n";
						$tiempExtr = false;						
					}				
				}
			}
			if(!empty($arrayGuardar))
			{
				$tr=0;
				$tr = ManejadorSolicitud::obtenerTransporte($_SESSION['idS']);
				$m->guardarSeleccionados($arrayGuardar,$horarios, $_SESSION['fechasolicitud'], $_SESSION['idS']);
				ManejadorTransporte::agregarEmpleados($tr, $arrayGuardar);
				echo "<script language='javascript' type='text/javascript'>alert('Los Siguientes empleados fueron guardados correctamente:\\n{$guardados}')</script>";
			}	
		
			if(!empty($mensaje))
			{
				echo "<script language='javascript' type='text/javascript'>alert('Los Siguientes empleados no cumplen con el tiempo extra requerido:\\n{$mensaje}')</script>";
			}
			//header('Location:index.php');
		}
		else
		{
			$mensaje= "Seleccione los registros para guardarlos. ";
		}
	}
	//var_dump($idH);
	//echo count($arrayGuardar). " y el array de horarios:". count($idH);
	//echo "<br>". var_dump($_POST['check']);
}
?>
<html>
	<head>
		<title>Seleccionados</title>
		<style type='text/css'>
		#txt
		{
			border-width:0;
			background:#F6F8F9;
		}
		</style>
		<script language='javascript'>
		function abrirReporte()
		{
			window.open('reporteHE.php','_blank');
		}
		</script>
		<link rel="stylesheet" href="css/styles.css" type="text/css" media="screen">
		<script src="js/jquery-2.1.1.js"></script>
		<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
	</head>
	<body>
		<?php include("menu.html");?>
		<div id='page'>
			<form method="post" action="seleccionados.php">	
				<div id='tabcontent' style='width:100%;' >
					<div id='ext-gen91' class='x-panel-bwrap'>
						<table width="100%" class='tab_cadre_pager' >					
							<tr>
								<td class="tab_bg_2 b">
									<input class='submit' onclick="abrirReporte()" name="btnPDF" value="Imprimir" style="width:100px;"> &nbsp &nbsp &nbsp
									<input class='submit' type="submit" name="btnGuardar" value="Guardar"> &nbsp &nbsp &nbsp
									<input class='submit' type="submit" name="btnVolver" value="Volver" >&nbsp &nbsp &nbsp
								</td>
							</tr>
						</table>				
					</div></br>

					<div class='center' style="overflow: auto;height: 65%;">
						<input type='checkbox' name='chkSlc' id="select_all" style='float:left;'><b style='float:left;'>SELECCIONAR TODO</b>	
						<table style='width:100%' class='tab_cadre_fixe' border>			
							<th width="5%">Seleccionar</th> <th width='23%'>Nombre</th> <th width='16%'>Cargo</th> <th width="8%">Cedula</th> <th width="7%">Fecha</th> 
							<th width="8%">Hora de Entrada</th> <th width="8%">Hora de Salida</th> <th width="6%" <?php if(isset($tiempExtr) && $tiempExtr ==false) echo "style='background: #F54E34; color:#000000;text-shadow: 1px 1px 0px #F54E34;'"?>>Tiempo extra</th>
							<th width="10%">30% Sueldo</th> <th width="20%">Pago</th>
							<?php 
								$m->obtenerSeleccionados($_SESSION['seleccionados'],$_SESSION['horarios'], $_SESSION['fechasolicitud']);
							?>
						</table>
					</div>					
				</div>
			</form>
		</div>
		<script>
			$(function() {
				$('#select_all').click(function() {
				    var c = this.checked;
				    $(":checkbox[name = 'check[]']").prop('checked',c);
				});
			});
		</script>
		<?php include("footer.html");?>
	</body>
</html>