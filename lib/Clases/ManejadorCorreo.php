<?php 
	$domain = $_SERVER['HTTP_HOST'];  
	$url = "http://" . $domain . $_SERVER['REQUEST_URI']; 
	if(strstr($url,"reportes/") or strstr($url,"reloj/"))
	{
		include("../../lib/PHPMailer/class.phpmailer.php");
		include("../../lib/PHPMailer/class.smtp.php"); 
		//echo $ruta;
	}
	else
	{
		include("lib/PHPMailer/class.phpmailer.php");
		include("lib/PHPMailer/class.smtp.php"); 		
	}
	class ManejadorCorreo
	{
		static function enviar($usuario,$pass,$from, $fromName, $adress, $CC, $BCC, $subject, $body, $attachment)
		{
			try
			{
				$mail = new PHPMailer();
				$mail->SMTPDebug = 0;
				$mail->IsSMTP();
				$mail->SMTPAuth = true;
				$mail->SMTPSecure = "tls"; 
				$mail->Host = "promesevsmx01.promesecal.lan"; // SMTP a utilizar. Por ej. smtp.elserver.com
				$mail->Username = $usuario; // Correo completo a utilizar
				$mail->Password = $pass; // Contraseña
				$mail->Port = 587; // Puerto a utilizar
				$mail->From = $from; // Desde donde enviamos (Para mostrar)
				$mail->FromName = $fromName;
				foreach($adress as $destinatario)
				{
					$mail->AddAddress($destinatario); // Esta es la dirección a donde enviamos
				}
				if(!empty($CC))
				{
					$mail->AddCC($CC); // Copia
				}
				if(!empty($BCC))
				{
					$mail->AddBCC($BCC); // Copia oculta
				}
				$mail->IsHTML(true); // El correo se envía como HTML
				$mail->Subject = $subject; // Este es el titulo del email.;
				$mail->Body = $body; // Mensaje a enviar
				//$mail->AltBody = "Hola mundo. Esta es la primer línean Acá continuo el mensaje"; // Texto sin html
				foreach($attachment as $adjunto)
				{
					$temp=explode(",", $adjunto);
					$mail->AddAttachment($temp[0], $temp[1]);
				}
				
				$exito = $mail->Send(); // Envía el correo.
				if($exito)
				{
					return "Mensaje enviado correctamente.";
				}
			} catch (phpmailerException $e) {
			  return $e->errorMessage(); //Pretty error messages from PHPMailer
			} catch (Exception $e) {
			  return $e->getMessage(); //Boring error messages from anything else!
			}
		}	
	}

?>