<?php 
include('lib/motor.php');

if(!isset($_SESSION)){
	session_start();
}

$domain = $_SERVER['HTTP_HOST'];  
$url = "http://" . $domain . $_SERVER['REQUEST_URI']; 
$includes = $_SESSION['m']->obtenerIncludes($url);
$s = new Seguridad();

if($s->verificar())
{
	if(strcmp($s->verificarTipo(), "Secretaria") ==0  or strcmp($s->verificarTipo(), "SuperAdmin") ==0 or strcmp($s->verificarTipo(), "Asistente") ==0)
	{
		$_SESSION['rutaActual']="Solicitudes > Listado de formularios de dieta y viaticos ";
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

	if (isset($_POST['check'])){
		if (isset($_POST['btnEditar']))
		{
			$t = count($_POST["check"]);
			if($t>1)
			{
				echo "<script>alert('Para editar seleccione solo 1 solicitud');</script>";	
			}
			else
			{
				foreach($_POST["check"] as $valor)// por cada valor de los checkbox seleccionados en POST
				{
					header("Location:dietayviaticos.php?edit={$valor}");
				}			
			}
		}
		else if (isset($_POST['btnEliminar']))
		{
			$t = count($_POST["check"]);
			if($t>1)
			{
				echo "<script>alert('Para eliminar seleccione solo 1 solicitud');</script>";	
			}
			else
			{
				foreach($_POST["check"] as $valor)
				{
					header("Location:dietayviaticos.php?del={$valor}");						
				}			
			}		
		}
		else if (isset($_POST['btnImprimir']))
		{
			if(isset($_POST['check']))
			{
				$t = count($_POST["check"]);
				if($t>1)
				{
					echo "<script>alert('Para imprimir seleccione solo un formulario');</script>";	
				}
				else
				{
					foreach($_POST["check"] as $valor)// por cada valor de los checkbox seleccionados en POST
					{
						echo "<script language='javascript'>window.open('DietaViaticoPDF.php?s={$valor}','_blank');</script>";
					}			
				}	
			}
			else
			{
				echo "<script>alert('Seleccione un formulario para imprimir);</script>";
			}		
		}
		else if (isset($_POST['btnTransporte']))
		{
			$t = count($_POST["check"]);
			if($t != 1)
			{
				echo "<script>alert('Para asignar transporte seleccione solo 1 solicitud');</script>";	
			}
			else
			{
				if (!empty($_POST["check"])) {
					foreach($_POST["check"] as $valor)
					{
						header("Location:dietayviaticos.php?asign={$valor}");						
					}
				}
			}		
		}
	}
	else
	{
		if(isset($_POST['btnBuscar']))
		{
			if ($_POST['slcFiltro']=='fecha')
			{	
				$rsCustom = ManejadorDietaViatico::obtenerFormularios( $_POST['slcFecha'],"");
			}
		}
		else if (isset($_POST['btnNuevo']))
		{
			header("Location:dietayviaticos.php");	
		}		
	}
}
?>
<html>
	<header>
		<title>Dieta y viaticos</title>
		<meta charset='utf-8'>
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<?php echo $includes;?>
		<style>
			#contenido{
				position: fixed;
			    top: 120px;
			    bottom: 100px;
			    left: 0;
			    right: 0;
			    overflow: auto;
			}
		</style>
	</header>
	<body>
		<?php include("menu.html");?>
		<div id='contenido'>
			<div class="container-fluid body-content">
				<fieldset style="width:90%;border-radius:8px;border: 3px solid;float:none; margin: 0 auto;" class="well bs-component">
					<form method="post" action="solicitudesViaticos.php">
						<div class="row">
							<div class="row" style="margin: 10px 0px;">
								<div class="col-xs-3 col-sm-3 col-md-3 col-lg-3">
									<button type="submit" class="btn btn-default btn-block" name="btnImprimir" title="Imprimir"><img src='pics/print.png'></button>
								</div>
								<div class="col-xs-3 col-sm-3 col-md-3 col-lg-3">
									<button type="submit" class="btn btn-info btn-block" name="btnNuevo" title="Agregar nueva solicitud"><img src='pics/add.png'></button>
								</div>
								<div class="col-xs-2 col-sm-2 col-md-2 col-lg-2">
									<button type="submit" class="btn btn-warning btn-block" name="btnEditar" title="Editar"><img src='pics/edit.png'></button>
								</div>
								<div class="col-xs-2 col-sm-2 col-md-2 col-lg-2">
									<button type="submit" class="btn btn-danger btn-block" name="btnEliminar" title="Eliminar"><img src='pics/delete.png' width="16" height="16"></button>
								</div>	
								<div class="col-xs-2 col-sm-2 col-md-2 col-lg-2">
									<button type="submit" class="btn btn-info btn-block"  name="btnTransporte" title="Asignar Transporte"><img src='pics/tr.png' width="16" height="16"></button>
								</div>								
							</div>
						</div>
						<div class="row">
							<div class="col-xs-4 col-sm-4 col-md-4 col-lg-4">
								<div class="form-group">
									<select name="slcFiltro" class="form-control"><option value='fecha'>Fecha de Creacion</option></select>
								</div>
							</div>
							<div class="col-xs-4 col-sm-4 col-md-4 col-lg-4">
								<div class="form-group">
									<input type="date" name="slcFecha" value='' class="form-control">
								</div>
							</div>
							<div class="col-xs-4 col-sm-4 col-md-4 col-lg-4">
								<button type="submit" name="btnBuscar" class="btn btn-primary btn-block ">Buscar</button>
							</div>
						</div>
						<br>
						<div class="row" style="width: 100%;margin: 0 auto; height: 60%;overflow: auto;">
							<table class='table table-hover table-striped'>
								<tr>
									<th>Seleccion</th><th>No.Solicitud</th><th>Fecha de creacion</th><th>Cantidad de empleados</th><th>Transporte</th>
								</tr>
								<?php 
									$rs;
									if(strstr($_SESSION['tipo'], "SuperAdmin"))
									{
										$rs = ManejadorDietaViatico::obtenerFormularios("","");
									}
									else
									{
										if(!empty($rsCustom))
										{
											$rs = $rsCustom;
										}
										else
										{
											$rs =ManejadorDietaViatico::obtenerFormularios("","");
										}							
									}
									
									if($rs)
									{
										while($fila=sqlsrv_fetch_array($rs, SQLSRV_FETCH_ASSOC))
										{
											$cantidad = ManejadorDietaViatico::cantidadEmpleados($fila['id']);
											if ($fila['transporte']==1) {
												$msjTransporte = "Solicitado";
											}
											else
											{
												$msjTransporte = "No requiere";
											}
											echo "
												<tr>
													<td align='center'><input type='checkbox' name='check[]' value='{$fila['id']}' readonly></input></td>
													<td>{$fila['no_oficio']}</td>
													<td>{$fila['fecha_creacion']->format('d/m/Y')}</td>
													<td>"; echo $cantidad ."</td>
													<td align='center'>{$msjTransporte}</td>
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
					</form>
				</fieldset>	
			</div>
		</div>
		<?php include("footer.html");?>
	</body>
</html>