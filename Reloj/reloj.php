<?php 
include '../lib/motor.php';

$domain = $_SERVER['HTTP_HOST'];  
$url = "http://" . $domain . $_SERVER['REQUEST_URI']; 
$includes = $_SESSION['m']->obtenerIncludes($url);

$s = new Seguridad();
if(!isset($_SESSION))
{
	session_start();
}
?>
<html>
	<head>
		<title>Reloj</title>
		<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<?php echo $includes;?>
		<style>
			#contenido{
				position: fixed;
			    top: 100px;
			    bottom: 100px;
			    left: 0;
			    right: 0;
			    overflow: auto;
			}
		</style>
		<script>
			function cron()
			{
				
				var hora = new Date();
				console.log(hora.getHours() + ':' + hora.getMinutes() + ':' + hora.getSeconds());
				if((hora.getHours()==6 && hora.getMinutes()==0 && hora.getSeconds()==0))
				{
					//clearInterval(time);
					window.open('punchsql/index.php?days=1');				
				}
			}
			time=setInterval(cron, 1000); //cada 1 segundo llamará a la función
		</script>
	</head>
	<body>
		<div id="contenido">
			<div class="container-fluid body-content">
				<fieldset style="width:100%;border-radius:8px;border: 3px solid;float:none; margin: 0 auto;" class="well bs-component">
					<div style="margin: 0 auto;width: 100%" class="text-center">
						<a onclick="window.open('punchsql/index.php');" class="btn btn-primary">PULL DATA</a>
					</div>
				</fieldset>
			</div>
		</div>
	</body>
</html>
