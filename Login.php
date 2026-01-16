<?php
include("lib/motor.php");
if(!isset($_SESSION)){
	session_start();
}

$domain = $_SERVER['HTTP_HOST'];  
$url = "http://" . $domain . $_SERVER['REQUEST_URI']; 
$includes = $_SESSION['m']->obtenerIncludes($url);
?>
<html>
	<head>
		<title>Login</title>
		<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<?php echo $includes;?>
		<style>
			#contenido{
				position: fixed;
				top :100px;
			    left: 0;
			    right: 0;
			    overflow: auto;
			}
			#login-box{
				background: url("/pics/loginhe.png") no-repeat;
				background-size: 100% 100%;
			}
			.form-group input{
				width: 50%;
			}
		</style>
	</head>
	<body>

		<div id="contenido">
			<div class="container-fluid body-content">	
				<fieldset id="login-box" style="width:50%;float:none; margin: 0 auto;" class="well bs-component">
					<form method="post" action="lib/Clases/comprobar.php" name="login-form">
						<br><br>
						<div class='row'>
							<div class="form-group">
								<label>Usuario:</label>
								<input type="text" name ="txtUsuario" class="form-control" placeholder="Nombre de usuario">
							</div>
						</div>					
						<div class='row'>
							<div class="form-group">
								<label>Contraseña:</label>
								<input type="password" name ="txtClave" class="form-control" placeholder="Clave">
							</div>
						</div>
						<input type="submit" name="btnGuardar" value="Entrar" class="btn btn-primary ">
					</form>
				</fieldset>
			</div>	
		</div>

		<?php include("footer.html");?>	

	
	</body>
</html>