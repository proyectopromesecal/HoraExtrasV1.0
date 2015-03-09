<?php
require("lib/PHPMailer/class.phpmailer.php");
include("lib/PHPMailer/class.smtp.php"); 
$mail = new PHPMailer();
$mail->SMTPDebug = 1;
$mail->IsSMTP();
$mail->SMTPAuth = true;
$mail->SMTPSecure = "tls"; 
$mail->Host = "promesevsmx01.promesecal.lan"; // SMTP a utilizar. Por ej. smtp.elserver.com
$mail->Username = "suazo.guillermo"; // Correo completo a utilizar
$mail->Password = "Nios1234"; // Contraseña
$mail->Port = 587; // Puerto a utilizar
$mail->From = "suazo.guillermo@promesecal.gob.do"; // Desde donde enviamos (Para mostrar)
$mail->FromName = "Sistema de Gestion de Soporte";
$mail->AddAddress("cruz.rosmery@promesecal.gob.do"); // Esta es la dirección a donde enviamos
//$mail->AddCC("mr.game01@gmail.com"); // Copia
$mail->AddBCC("suazo.guillermo@promesecal.gob.do"); // Copia oculta
$mail->IsHTML(true); // El correo se envía como HTML
$mail->Subject = "Prueba de SMTP"; // Este es el titulo del email.
$body = "Hola...esto es una prueba. <br />";
$mail->Body = $body; // Mensaje a enviar
$mail->AltBody = "Hola mundo. Esta es la primer línean Acá continuo el mensaje"; // Texto sin html
//$mail->AddAttachment("archivos/ring_logo.jpg", "ring_logo.jpg");
$exito = $mail->Send(); // Envía el correo.
//172.125.10.6
if($exito)
{
	echo "El correo fue enviado correctamente.";
}
else
{
	echo "Hubo un inconveniente. Contacta a un administrador.";
}
?>
<html>
	<head>
		<title></title>
	</head>
	<body>
		
	</body>
</html>