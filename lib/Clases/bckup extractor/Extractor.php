<?php 	
	include_once("ManejadorPunch.php");
	
	class Extractor
	{	
		function extraer($ruta)
		{
			ini_set('memory_limit', '-1');
			$archivos = array();
			$datos = array();
			$fila = array();
			$total=0;
			$dir_handle = @opendir($ruta) or die("Unable to open {$path}");
			echo "Leyendo el directorio... <br>";
			while ($file = readdir($dir_handle))
			{
				if(strpos($file, ".log") == false )
				{
					continue;			
				}
				else
				{
					if($file == "." || $file == ".." || strstr($file,"-") || $file == "Error.Log" || $file == "Punch.Log" || $file == "Sys.Log" || substr($file, 0,4)!="2013")
					{
						continue;
					}
					else
					{
						$archivos[]=$file;
					}
				}
			}
			closedir($dir_handle);
			echo "Leyendo los archivos... <br>";
			
			foreach($archivos as $archivo)
			{
				$archivoTemp = file($ruta.$archivo);
				$lineas = count($archivoTemp);
				for($i=0;$i<$lineas;$i++)
				{
					$datos[] = $archivoTemp[$i];
				}
				$total +=$lineas;
			}
			for($i=0;$i<count($datos);$i++)
			{
				$fila = explode(",",substr($datos[$i], 15, -49));
				$codigo=$fila[0];
				$hora=trim($fila[2]);
				$minutos=trim($fila[3]);
				if($fila[6] <=9)
				{
					$fecha= "200".trim($fila[6])."-".trim($fila[4])."-".trim($fila[5]);
				}
				else
				{
					$fecha= "20".trim($fila[6])."-".trim($fila[4])."-".trim($fila[5]);
				}
				
				if($fila[3]=="")
				{
					$minutos ="00";
				}
				else if ($fila[2] <=9)
				{
					$hora = "0".$hora;
				}
				else if($fila[3] <=9)
				{
					$minutos ="0".trim($fila[3]);
				}
				else if ($fila[2]=="")
				{
					$hora = "00";
				}
				ManejadorPunch::guardar($codigo, $hora.":".$minutos, $fecha);
				//echo $codigo, $hora.":".$minutos, $fecha."<br>";
			}
			echo "Termino de leer... <br>";			
			
			#echo "<pre>";
			#echo var_dump($fila);
			#echo "</pre>";
		}
	}

?>