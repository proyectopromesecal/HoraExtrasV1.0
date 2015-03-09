<?php 
include("lib/motor.php");
require('fpdf/fpdf.php');

if($_GET['f'])
{
	global $tdes;
	global $tal;
	global $tcen;
	global $tdor;
	$tdes=0;
	$tal=0;
	$tcen=0;
	$tdor=0;

	
	$arrFechaE = array();
	$arrFechaS = array();
	$arrHoraE = array();
	$arrHoraS = array();
	$arrLugar = array();
	$arrIDDestinos = array();
	$conteo = array();
	$empleados = array();
	$params = array();
	$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );

	$queryCargar="SELECT * FROM destinos_viaticos WHERE id_viatico = {$_GET['f']}";

	$rsCargar=sqlsrv_query($_SESSION['con'],$queryCargar, $params, $options);
	
	while($filaC=sqlsrv_fetch_array($rsCargar, SQLSRV_FETCH_ASSOC))
	{
		$arrIDDestinos[] =$filaC['id'];
		$arrFechaE[]=$filaC['fecha_entrada'];
		$arrFechaS[]=$filaC['fecha_salida'];
		$arrHoraE[]=$filaC['hora_entrada'];
		$arrHoraS[]=$filaC['hora_salida'];
		$arrLugar[]=$filaC['lugar'];
	}	
	
	$dias;
	for($f=0;$f<count($arrFechaE);$f++)
	{
		$fecha = $arrFechaE[$f]->format('Y-m-d');
		$fecha2 = $arrFechaS[$f]->format('Y-m-d');
		
		$dias = (strtotime($arrFechaE[$f]->format('Y-m-d'))-strtotime($arrFechaS[$f]->format('Y-m-d')))/86400;
		$dias = abs($dias); $dias = floor($dias);	
			
		for($x=0;$x<=$dias;$x++)
		{
			$horaE = explode(":", $arrHoraE[$f]->format('H:i:s'));
			$horaS = explode(":", $arrHoraS[$f]->format('H:i:s'));
			
			if($horaE[0] <= 7)
			{
				$tdes+=1;
			}
			if ($horaS[0] >= 12)
			{
				$tal+=1;
			}
			if ($horaS[0]>=18)
			{
				$tcen+=1;
			}
		}
		$tdor= $dias;
		$conteo[] = $fecha.";".$fecha2.";".$tdes.";".$tal.";".$tcen.";".$tdor;
		$tdes=0;$tal=0;$tcen=0;
	}	
	$query="SELECT posicion_viatico.posicion as categoria, desayuno, almuerzo, cena, dormitorio
			FROM empleado, posicion_viatico, dietaviatico, viatico_empleado
			where tipo_viatico = posicion_viatico.id
			AND empleado.id = viatico_empleado.id_empleado
			AND dietaviatico.id = viatico_empleado.id_formulario
			AND dietaviatico.id = {$_GET['f']}
			GROUP BY posicion_viatico.posicion, desayuno, almuerzo, cena, dormitorio";
	$rs=sqlsrv_query($_SESSION['con'],$query, $params, $options);
	if($rs)
	{
		while($fila=sqlsrv_fetch_array($rs, SQLSRV_FETCH_ASSOC))
		{
			$empleados[] = $fila['categoria'].";".$fila['desayuno'].";".$fila['almuerzo'].";".$fila['cena'].";".$fila['dormitorio'];
		}
	}
}
else
{
	header("Location:solicitudesviaticos.php");
}

class PDF extends FPDF
{
	// Cabecera de página
	function Header()
	{
		$nombre = ManejadorDietaViatico::obtenerNombre($_GET['f']);
		// Logo
		$this->Image('imagenes/logo-promosecal.png',15,10,50, 20);
		$this->Image('imagenes/farmacia-logo.png',146,10,50,20);
		// Arial bold 15
		$this->SetFont('Arial','B',14);
		$this->Ln(20);
		// Movernos a la derecha
		// Título
		$this->Cell(190, 15, "Detalle del reporte de viaticos {$nombre}",0,0, 'C');
		// Salto de línea
		$this->Ln(20);
	}

	// Pie de página
	function Footer()
	{
		// Posición: a 1,5 cm del final
		$this->SetY(-15);
		// Arial italic 8
		$this->SetFont('Arial','I',8);
		// Número de página
		$this->Cell(0,10,date('Y-m-d'), 0,0,'C');
		$this->Cell(0,10,'Pagina '.$this->PageNo().'/{nb}',0,0,'C');
	}
	
	function Body($tabla, $emps)
	{
		$this->setLeftMargin(15);
		if(!empty($emps))
		{
			$this->Cell(50, 6, "Pago correspondiente por categoria:", 0,1);
			$this->Ln();
			
			$this->SetFillColor(255,0,0);
			$this->SetDrawColor(0);
			$this->SetLineWidth(.3);
			
			$w = array
			(0 => 40,
			 1 => 20,
			 2 => 20,
			 3 => 20,
			 4 => 22);
			 
			$header= array('Categoria','Desayuno', 'Almuerzo', 'Cena', 'Dormitorio');
			
			for($i=0;$i<count($header);$i++)
			{
				$this->Cell($w[$i],10,$header[$i],1,0,'C');
			}
			$this->Ln();
			$this->SetFont('Times','B',7);
			$this->SetFillColor(224,235,255);
			$this->SetTextColor(0);
			$this->SetFont('');
			// Datos
			foreach($emps as $row)
			{
				$columna = explode(";",$row); //separar los datos en posiciones de arreglo 
				$this->Cell($w[0],6,$columna[0],1,0,'C');
				$this->Cell($w[1],6,$columna[1],1,0,'C');
				$this->Cell($w[2],6,$columna[2],1,0,'C');
				$this->Cell($w[3],6,$columna[3],1,0,'C');	
				$this->Cell($w[4],6,$columna[4],1,0,'C');			
				$this->Ln();
			}
			$this->Cell(array_sum($w),0,'','T');
		}
		else
		{
			$this->Cell(50, 6, "Este formulario no tiene Empleados.", 0,1);
		}
		//tabla
		if(!empty($tabla))
		{
			$this->SetFont('Times','B',12);
			$this->SetFillColor(224,235,255);
			$this->SetTextColor(0);
			$this->Ln(10);
			$this->Cell(50, 6, "Pagos realizados por fechas:", 0,1);
			$this->Ln();
			
			$this->SetFillColor(255,0,0);
			$this->SetDrawColor(0);
			$this->SetLineWidth(.3);
			
			$w = array
			(0 => 30,
			 1 => 30,
			 2 => 20,
			 3 => 20,
			 4 => 20,
			 5 => 22);
			 
			$header= array('Fecha entrada', 'Fecha Salida','Desayuno', 'Almuerzo', 'Cena', 'Dormitorio');
			for($i=0;$i<count($header);$i++)
			{
				$this->Cell($w[$i],10,$header[$i],1,0,'C');
			}
			$this->Ln();
			$this->SetFont('Times','B',7);
			$this->SetFillColor(224,235,255);
			$this->SetTextColor(0);
			$this->SetFont('');
			// Datos
			foreach($tabla as $row)
			{
				$columna = explode(";",$row); //separar los datos en posiciones de arreglo 
				$this->Cell($w[0],6,$columna[0],1,0,'C');
				$this->Cell($w[1],6,$columna[1],1,0,'C');
				$this->Cell($w[2],6,$columna[2],1,0,'C');
				$this->Cell($w[3],6,$columna[3],1,0,'C');	
				$this->Cell($w[4],6,$columna[4],1,0,'C');
				$this->Cell($w[5],6,$columna[5],1,0,'C');				
				$this->Ln();
			}
			$this->Cell(array_sum($w),0,'','T');
		}
		else
		{
			$this->Cell(50, 6, "Este formulario no tiene datos.", 0,1);
		}
		
	}
}

// Creación del objeto de la clase heredada
$pdf=new PDF('P', 'mm', 'A4');
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetFont('Times','B',12);
$pdf->Body($conteo, $empleados);
$pdf->Output();


?>