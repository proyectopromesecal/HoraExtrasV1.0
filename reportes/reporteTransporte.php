<?php 
include("../lib/motor.php");
if(!isset($_SESSION)){
	session_start();
}
$s = new Seguridad();

$domain = $_SERVER['HTTP_HOST'];  
$url = "http://" . $domain . $_SERVER['REQUEST_URI']; 
$includes = $_SESSION['m']->obtenerIncludes($url);
if($s->verificar())
{
	$f=0;
	foreach ($_SESSION['permisos'] as $value) {
		if ($value == 'Viewer' || $value == 'SuperAdmin' || $value == 'Secretaria' || $value == 'Asistente') {
			$f=1;
		}
	}

	if($f)
	{	
		$_SESSION['rutaActual']="Reportes > Transporte";
	}
	else
	{
		header("Location:../index.php");
	}
}
else
{
	header('Location:Login.php');
}
if($_POST)
{
	if(isset($_POST['btnReporte']))
	{

		$f=0;
		foreach ($_SESSION['permisos'] as $value) {
			if ($value == 'Viewer' || $value == 'SuperAdmin') {
				$f=1;
			}
		}

		if($f)
		{
			echo "<script>window.open('transporte.php?fi={$_POST['fechaInicio']}&ff={$_POST['fechaFinal']}&dp={$_POST['slcDepartamento']}')</script>";
		}
		else
		{
			echo "<script>window.open('transporte.php?fi={$_POST['fechaInicio']}&ff={$_POST['fechaFinal']}')</script>";
		}
	}
}
?>
<html>
	<head>
		<title>Reporte de Transporte</title>
		<meta charset='utf-8'>
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<?php echo $includes;?>
		<style type="text/css">
			#contenido{
				position: fixed;
			    top: 120px;
			    bottom: 100px;
			    left: 0;
			    right: 0;
			    overflow: auto;
			}
		</style>
	</head>
	<body>
		<header>
			<?php include("../menu.html");?>
		</header>
		
		<div id="contenido">
			<div class="container-fluid body-content">
				<fieldset class="well bs-component" style="border-radius:8px;border: 3px solid;width:90%;margin: 0 auto;">
					<legend>B&uacute;squeda de Transporte</legend>
					<div class="row text-center">
						<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
							<form method="post" action="" class="form-inline">
								<?php 
									$f=0;
									foreach ($_SESSION['permisos'] as $value) {
										if ($value == 'Viewer' || $value == 'SuperAdmin') {
											$f=1;
										}
									}

									if($f)
									{
										echo "<div class='form-group'>
												<select name='slcDepartamento' class='form-control'>"; Manejador::obtenerDepartamentos();echo"</select> 
										</div>";
									}
								?>
								<div class="form-group">
									<input type="date" name="fechaInicio" required class="form-control"> - <input type="date" name="fechaFinal" required class="form-control">
								</div>
								<div class="form-group">
									<input type="submit" value="Generar Reporte" name="btnReporte" class="btn btn-primary" ></input>	
								</div>	
							</form>	
						</div>
					</div>
				</fieldset>	
			</div>	
		</div>
		<?php include("../footer.html");?>
	</body>
</html>