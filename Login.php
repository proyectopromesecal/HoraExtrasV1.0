<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
		<title>Login</title>
		<link rel="stylesheet" href="css/styles.css" type="text/css" media="screen">
	</head>
	<body>
		<div id="contenulogin">
			<div id="logo-login"></div>
			<div id="boxlogin">
				<form method="post" action="lib/Clases/comprobar.php" name="login-form" class="login-form">
					<fieldset>
						<legend>Identificacion</legend>
						<div class='row'>
							<span class='label'>
								<label>Usuario:</label>
							</span>
							<span class='formw'>
								<input type="text" name ="txtUsuario" id="login_name" size="20" >
							</span>
						</div>
						
						</br>
						
						<div class='row'>
							<span class='label'>
								<label>Contraseña:</label>
							</span>
							<span class='formw'>
								<input type="password" name ="txtClave" id="login_password" size="20">
							</span></br></br>
							<span style="float: right;">
								<input type="submit" name="btnGuardar" value="Entrar" class="submit">
							</span>
						</div>

					</fieldset>
				</form>	
			</div>	
		</div>		
	</body>
</html>