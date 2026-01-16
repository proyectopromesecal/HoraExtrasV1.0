<?php 
include("lib/motor.php");

if(!isset($_SESSION)){
	session_start();
}

$s = new Seguridad();
$domain = $_SERVER['HTTP_HOST'];  
$url = "http://" . $domain . $_SERVER['REQUEST_URI']; 
$includes = $_SESSION['m']->obtenerIncludes($url);

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
		$_SESSION['manejador']= $m;	
		$tr=0;
		
		$tr = ManejadorSolicitud::obtenerTransporte($_SESSION['idS']);
		ManejadorTransporte::agregarEmpleados($tr, $_SESSION['seleccionados']);

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

//echo '<pre>';
//echo print_r($_SESSION['pagoemp']);
//echo '</pre>';

if($_POST)
{
	$guardados="";
	$horarios= array();
	$feriado=false;
	if(isset($_POST['btnVolver']))
	{
		header('Location:solicitudesPago.php');
	}
	else if(isset($_POST['btnGuardar']))
	{
		global $arrayGuardar;
		$arrayGuardar = array();

		$noGuardados ="";
		if(isset($_POST['check'])) 
		{
			foreach($_POST["check"] as $valor)// este for es para obtener los registros seleccionados
			{
				$datos = explode("-", $valor);	
				$acumulado = Manejador::obtenerAcumuladoHE($datos[0], $_SESSION['fechasolicitud']);
				$porciento = Manejador::obtenerPorciento($datos[0]);
				$pago = $_SESSION['pagoemp'][$datos[0]];
				if (Manejador::validarPago($datos[0],$_SESSION['fechasolicitud']))
				{
					$noGuardados .= $m->obtenerNombre($datos[0]). " Ya ha sobrepasado el 30% de su sueldo \\n";
				}
				/*
				else if($pago+$acumulado >$porciento)
				{
					$noGuardados .= $m->obtenerNombre($datos[0])." El monto a pagar sobrepasa el 30% de su sueldo \\n";
				}
				*/
				else
				{
					$arrayGuardar[] = $datos[0];
					$horarios[] = $datos[1];
					$guardados .= $m->obtenerNombre($datos[0])."\\n";						
				}
			}

			if(!empty($arrayGuardar))
			{
				$m->guardarSeleccionados($arrayGuardar,$horarios, $_SESSION['fechasolicitud'], $_SESSION['idS']);
				echo "<script language='javascript' type='text/javascript'>alert('Los Siguientes empleados fueron guardados correctamente:\\n{$guardados}')</script>";
			}
			if (!empty($noGuardados)) {
				echo "<script language='javascript' type='text/javascript'>alert('Los Siguientes empleados NO fueron guardados\\n{$noGuardados}')</script>";
			}	
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
		<title>Solicitudes de Horas Extra</title>
		<meta charset='utf-8'>
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<?php echo $includes;?>
		<style type='text/css'>
			#txt
			{
				border-width:0;
				background:#F6F8F9;
			}
			#contenido{
				position: fixed;
			    top: 120px;
			    bottom: 100px;
			    left: 0;
			    right: 0;
			    overflow: auto;
			}
			legend{
			    width: 100px !important; 
			}
		</style>
		<script language='javascript'>
			function abrirReporte()
			{
				window.open('reporteHE.php','_blank');
			}
		</script>
	</head>
	<body>
		<header>
			<?php include("menu.html");?>
		</header>
	
		<div id='contenido'>
			<div class="container-fluid body-content">
				<form method="post" action="seleccionados.php">
					<div class="row" style="margin-bottom: 10px;">
						<fieldset style="width:80%;border-radius:8px;border: 3px solid;float:none; margin: 0 auto;" class="well bs-component">
							<legend>Opciones</legend>
							<div class="form-group">
								<div class="col-xs-4 col-sm-4 col-md-4 col-lg-4">
									<button class='btn btn-default btn-block' type="submit" name="btnVolver" >Volver</button>
								</div>
								<div class="col-xs-4 col-sm-4 col-md-4 col-lg-4">
									<button class='btn btn-primary btn-block' type="submit" name="btnGuardar">Guardar</button>
								</div>
								<div class="col-xs-4 col-sm-4 col-md-4 col-lg-4">
									<button type="submit" class='btn btn-default btn-info btn-block' onclick="abrirReporte()" name="btnPDF">Imprimir</button>
								</div>								
							</div>
						</fieldset>
					</div>
					<div class="row">
						<fieldset style="overflow: auto;height: 70%;">
								
							<table style='width:100%' class='table table-bordered table-striped' border>			
								<th width="5%"><input type='checkbox' name='chkSlc' id="select_all" style='float:left;'><b style='float:left;'>Selec TODO</b></th> <th width='23%'>Nombre</th> <th width='16%'>Cargo</th> <th width="8%">C&eacute;dula</th> <th width="7%">Fecha</th> 
								<th width="8%">Hora de Entrada</th> <th width="8%">Hora de Salida</th> <th width="6%" <?php if(isset($tiempExtr) && $tiempExtr ==false) echo "style='background: #F54E34; color:#000000;text-shadow: 1px 1px 0px #F54E34;'"?>>Tiempo extra</th>
								<th width="10%">30% Sueldo</th> <th width="20%">Pago</th>
								<?php 
									$m->obtenerSeleccionados($_SESSION['seleccionados'],$_SESSION['horarios'], $_SESSION['fechasolicitud']);
								?>
							</table>
						</fieldset>					
					</div>
				</form>			
			</div>
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