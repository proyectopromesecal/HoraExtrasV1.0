<?php include("lib/motor.php");

function obtenerReportePunch($id_viatico)
{
	$m = new Manejador();
	$datos = array();
	$idD = array();
	$lugar =array();
	$fs = array();
	$fe = array();
	$he = array();
	$hs = array();
	$emp = array();
	$totalesbd = array();
	$totalespn = array();
	
	$query="SELECT *
			FROM destinos_viaticos
			WHERE id_viatico = {$id_viatico}";
	$rs = mysql_query($query);
	
	while($fila=mysql_fetch_assoc($rs))
	{
		$idD[] = $fila['id'];
		$fs[] = $fila['fecha_salida'];
		$fe[] = $fila['fecha_entrada'];
		$hs[] = $fila['hora_salida'];
		$he[] = $fila['hora_entrada'];
		$lugar[] = $fila['lugar'];
	}
	
	$query="SELECT id_empleado, desayuno, almuerzo, cena, dormitorio
			FROM viatico_empleado, rango_viaticos, empleado
			WHERE id_formulario = {$id_viatico}
			AND empleado.id = viatico_empleado.id_empleado
			AND empleado.tipo_viatico = rango_viaticos.id";
	$rs = mysql_query($query);
	
	while($fila=mysql_fetch_assoc($rs))
	{
		$empleado = $fila['id_empleado'];
		$emp[]=$fila['id_empleado'];
		$totalesbd[$empleado]=0;
		for($x=0;$x<count($fe);$x++)
		{
			$datos['nombre'][$empleado]=$m->obtenerNombre($empleado);
			$tdes=0; $tal=0; $tcen=0; $tdor =0;
			$total1 =0;
			$dias;
			echo $m->obtenerNombre($empleado);
			//echo "<br>---FOR recorrido {$x} ---<br>";
			$sql="	SELECT empleado.nombre as nombre, horadeentrada, horadesalida
					FROM empleado, horario
					WHERE empleado.id = horario.id_empleado
					AND empleado.id = {$empleado}
					AND horario.fecha = '{$fe[$x]}'";
			$rs2 = mysql_query($sql);
			if(mysql_num_rows($rs2) > 0 )
			{
				while($filaEmp=mysql_fetch_assoc($rs2))
				{		
					echo "<br><br>"." Grupo de fecha: {$fe[$x]}"."<br>";
					$desayuno = false; $almuerzo = false; $cena = false; $dormitorio= false;
					
					$dias = (strtotime($fe[$x])-strtotime($fs[$x]))/86400;
					$dias = abs($dias); $dias = floor($dias);					
					//echo "Dias ".$dias."<br>";
					$horaE = explode(":", $filaEmp['horadeentrada']);
					$horaS = explode(":", $filaEmp['horadesalida']);
					echo $filaEmp['horadeentrada']."   ";
					echo $filaEmp['horadesalida']."<br>";
					if($horaE[0] <= 7)
					{
						$desayuno = true;
						$tdes+=1;
					}
					if ($horaS[0] >= 12)
					{
						$almuerzo = true;
						$tal+=1;
					}
					if ($horaS[0]>=18)
					{
						$cena = true;
						$tcen+=1;
					}

					if($desayuno)
					{
						echo "Desayuno: {$fila['desayuno']}"."<br>";
						$total1 += $fila['desayuno'];
					}

					if ($almuerzo)
					{
						echo "Almuerzo: {$fila['almuerzo']}"."<br>";
						$total1 += $fila['almuerzo'];
					}

					if ($cena)
					{
						echo "Cena: {$fila['cena']}"."<br>";
						$total1 += $fila['cena'];
					}

					$dormitorio = $fila['dormitorio'] * $dias;
					echo "Dormitorio: {$dormitorio} ({$dias} dias)"."<br>";
					$total1 += $dormitorio;		
						
					//$total = 0;
					//$desayuno = false; $almuerzo = false; $cena = false; $dormitorio= false;
				}	
				echo "Total: ".$total1."<br><br>";
				//$totalesbd[$empleado]+=$total1;
				$totalesbd[$empleado]+=$total1;
			}
			else
			{
				//echo $m->obtenerNombre($empleado)." no tiene datos en esta fecha. <br>";
			}
		}				
	}
	
	//echo "<pre>";
	//echo var_dump($datos);
	//echo "</pre>";
	
	$arrFechaE = array();
	$arrFechaS = array();
	$arrHoraE = array();
	$arrHoraS = array();
	$arrLugar = array();
	$arrIDDestinos = array();
	
	$queryCargar="SELECT * FROM destinos_viaticos WHERE id_viatico = {$id_viatico}";
	$rsCargar=mysql_query($queryCargar);
	while($filaC=mysql_fetch_assoc($rsCargar))
	{
		$arrIDDestinos[] =$filaC['id'];
		$arrFechaE[]=$filaC['fecha_entrada'];
		$arrFechaS[]=$filaC['fecha_salida'];
		$arrHoraE[]=$filaC['hora_entrada'];
		$arrHoraS[]=$filaC['hora_salida'];
		$arrLugar[]=$filaC['lugar'];
	}			
	
	$query="SELECT empleado.id AS id, desayuno, almuerzo, cena, dormitorio
			FROM empleado, dietaviatico, viatico_empleado, rango_viaticos
			WHERE empleado.id = viatico_empleado.id_empleado
			AND dietaviatico.id = viatico_empleado.id_formulario
			AND dietaviatico.id ={$id_viatico}
			AND empleado.id = viatico_empleado.id_empleado
			AND empleado.tipo_viatico = rango_viaticos.id";
	$rs = mysql_query($query);
	if($rs)
	{
		
		while($filaEmpt=mysql_fetch_assoc($rs))
		{
			$tdes=0; $tal=0; $tcen=0;
			$total =0;
			$dias;
			$totalespn[$filaEmpt['id']]=0;
			//echo "Empleado: ".$filaEmpt['id'];
			for($f=0;$f<count($arrFechaE);$f++)
			{
				//echo "<br><br>"." Grupo de fecha: {$f}"."<br>";
				$desayuno = false; $almuerzo = false; $cena = false; $dormitorio= false;
				
				$dias = (strtotime($arrFechaE[$f])-strtotime($arrFechaS[$f]))/86400;
				$dias = abs($dias); $dias = floor($dias);					
				//echo "Dias ".$dias."<br>";
				for($x=0;$x<=$dias;$x++)
				{
					$horaE = explode(":", $arrHoraE[$f]);
					$horaS = explode(":", $arrHoraS[$f]);
					if($horaE[0] <= 7)
					{
						$desayuno = true;
						$tdes+=1;
					}
					if ($horaS[0] >= 12)
					{
						$almuerzo = true;
						$tal+=1;
					}
					if ($horaS[0]>=18)
					{
						$cena = true;
						$tcen+=1;
					}

					if($desayuno)
					{
						//echo "Desayuno: {$filaEmpt['desayuno']}"."<br>";
						$total += $filaEmpt['desayuno'];
					}
					if ($almuerzo)
					{
						//echo "Almuerzo: {$filaEmpt['almuerzo']}"."<br>";
						$total += $filaEmpt['almuerzo'];
					}
					if ($cena)
					{
						//echo "Cena: {$filaEmpt['cena']}"."<br>";
						$total += $filaEmpt['cena'];
					}
				}
				$dormitorio = $filaEmpt['dormitorio'] * $dias;
				//echo "Dormitorio: {$dormitorio} ({$dias} dias)"."<br>";
				$total += $dormitorio;
				
				
				//$total = 0;
				//$desayuno = false; $almuerzo = false; $cena = false; $dormitorio= false;
			}							
			//echo "Total: ".$total."<br>";
			$totalespn[$filaEmpt['id']]+= $total;
			//$totalespn[]+= $total;
		}
		$datos['totalpn'] = $totalespn;	
		$datos['totalbd'] = $totalesbd;	
	}
	for($c=0;$c<count($totalesbd);$c++)
	{
		$datos['debe'][$emp[$c]]=($totalespn[$emp[$c]]- $totalesbd[$emp[$c]]);
		//echo $m->obtenerNombre($emp[$c])." Debe devolver: ". ($totalesbd[$emp[$c]] - $totalespn[$emp[$c]]) * -1 ."<br><br>"; 
	}	
	/*
	for($x=0;$x<count($datos['nombre']);$x++)
	{
		if(!empty($datos['nombre'][$x])) echo $datos['nombre'][$x]."<br>";
		if(!empty($datos['fecha'][$x])) echo $datos['fecha'][$x]."<br>";
		if(!empty($datos['horadeentrada'][$x]))echo $datos['horadeentrada'][$x]."<br>";
		if(!empty($datos['horadesalida'][$x]))echo $datos['horadesalida'][$x]."<br>";
		if(!empty($datos['total1'][$x]))echo $datos['total1'][$x]."<br>"."<br>";
		echo (!empty($datos['debe'][$x])) ? $datos['nombre'][$x]." debe ".$datos['debe'][$x] : $datos['nombre'][$x]." no debe.";
		echo "<br><br>";
	}
	*/
	//echo "<pre>";
	///echo var_dump($datos);
	//echo "</pre>";

}
?>
<html>
	<head>
		<title></title>
	</head>
	<body>
		<?php obtenerReportePunch(3);?>
	</body>
</html>