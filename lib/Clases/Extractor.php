<?php 	
	include_once("ManejadorPunch.php");
	if(!isset($_SESSION)){
		session_start();
	}
	class Extractor
	{	
		function extraer($ruta)
		{
			$archivos = array();
			$total=0;
			$datos = array();
			$fila = array();	
			$fecha = date("Y-m-d", strtotime('yesterday'));
			$filename = explode("-", $fecha);
			$filename = $filename[0].$filename[1].$filename[2];
			$encontrado = false;
			echo "Leyendo el directorio... <br>";
			
			while ($encontrado == false)
			{
				if(file_exists("D:\punch\handpunch\\".$filename.".log"))
				{	
					$_SESSION['fecha1p']= $fecha;
					$archivos[] ="D:\punch\handpunch\\".$filename.".log";
					echo "Se ha incluido a ".$filename.".log \n";
					echo "<br>";
					$fecha = date("Y-m-d",strtotime("$fecha -1 day"));
					$filename = explode("-", $fecha);
					$filename = $filename[0].$filename[1].$filename[2];
					$anterior = false;
					while($anterior == false)
					{
						if(file_exists("D:\punch\handpunch\\".$filename.".log"))
						{
							$archivos[] ="D:\punch\handpunch\\".$filename.".log";
							$anterior = true;
							echo "Se ha incluido a ".$filename.".log \n";
						}
						else
						{
							$fecha = date("Y-m-d",strtotime("$fecha -1 day"));
							$filename = explode("-", $fecha);
							$filename = $filename[0].$filename[1].$filename[2];
						}
					}
					$_SESSION['fecha2p']= $fecha;
					$encontrado = true;
				}
				else
				{
					$fecha = date("Y-m-d",strtotime("$fecha -1 day"));
					$filename = explode("-", $fecha);
					$filename = $filename[0].$filename[1].$filename[2];
				}		
			}
			foreach($archivos as $archivo)
			{
				$archivoTemp = file($archivo);
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
				//echo $codigo." ".$hora.":".$minutos." ".$fecha."<br>";
				ManejadorPunch::guardar($codigo, $hora.":".$minutos, $fecha);
			}
		}
	}
?>