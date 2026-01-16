<?php
require('fpdf/fpdf.php');
include('lib/motor.php');

global $datos;
global $tabla;
$c = new Calculo();
$totalAprx=0;
$tiempo;

if(isset($_GET['s']))
{
	$query="SELECT * FROM solicitudHE
			WHERE id={$_GET['s']}";
	$params = array();
	$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );			
	$rs = sqlsrv_query($_SESSION['con'],$query, $params, $options);
	if($rs)
	{
		while($fila=sqlsrv_fetch_array($rs, SQLSRV_FETCH_ASSOC))
		{
			$tiempo= explode(":",$fila['tiempoEstimado']);
			if($tiempo[0]==0)
			{
				if($tiempo[1]>=30)
				{
					$tiempo = 1;
				}
			}
			else
			{
				$tiempo = $tiempo[0];
			}
			$datos[]=$fila['noOficio'].";".$fila['objetivo'].";".$fila['descripcion'].";".$fila['alcance'].";".$fila['programado'].";".$fila['tiempoEstimado'].";".$fila['fecha']->format('d/m/Y');			
		}
	}
	$rs = ManejadorSolicitud::obtenerEmpleados($_GET['s']);
	if($rs)
	{
		while($fila=sqlsrv_fetch_array($rs, SQLSRV_FETCH_ASSOC))
		{
			$totalAprx += $c->calcularHoraNormal($c->calcularSalarioDiario($fila['sueldo']));
			 
			$tabla[]=$fila['nombre'].";".$fila['cargo'].";".$fila['departamento'].";".$fila['cedula'];
		}	
		$totalAprx *= $tiempo;
	}
}
else
{
	header('Location:generadorReportes.php');
}


class PDF extends FPDF
{
	// Cabecera de página
	function Header()
	{
		// Logo
		$this->Image('imagenes/logo-promosecal.png',12,10,50, 20);
		$this->Image('imagenes/farmacia-logo.png',150,10,50,20);
		// Arial bold 15
		$this->SetFont('Arial','B',14);
		$this->Ln(20);
		// Movernos a la derecha
		// Título
		$this->Cell(190, 15, "Solicitud de trabajo de Horas Extra",0,0, 'C');
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
		$this->Cell(0,10,date('d/m/Y'), 0,0,'C');
		$this->Cell(0,10,'Pagina '.$this->PageNo().'/{nb}',0,0,'C');
	}
	
	function Body($data, $tabla, $total)
	{
		// Datos
		foreach($data as $row)
		{
			$columna = explode(";",$row); //separar los datos en posiciones de arreglo 
			$this->SetFont('Times','B',13);
			$this->Cell(45, 6, utf8_decode("Número de Solicitud: "),0);
			
			$this->SetFont('Times','',12);
			$this->MultiCell(193,6,$columna[0],0);
			
			$this->Ln();
			$this->SetFont('Times','B',13);
			$this->Cell(50, 6, utf8_decode("Objetivo del período de trabajo extraordinario: "),0);
			
			$this->Ln();
			
			$this->SetFont('Times','',12);
			$this->MultiCell(193,6,utf8_decode($columna[1]),0,1);
			
			$this->Ln();
			
			$this->SetFont('Times','B',13);
			$this->Cell(50, 6, utf8_decode("Breve descripción del trabajo extraordinario: "),0);
			
			$this->Ln();
			
			$this->SetFont('Times','',12);
			$this->MultiCell(193,6,utf8_decode($columna[2]),0,1);
			
			$this->Ln();
			
			$this->SetFont('Times','B',13);
			$this->Cell(50, 6, "Alcance de la jornada de trabajo: ",0);
			
			$this->Ln();
			
			$this->SetFont('Times','',12);
			$this->MultiCell(193,6,utf8_decode($columna[3]),0,1);	
			
			$this->Ln();		
			
			$this->SetFont('Times','B',13);
			$this->Cell(50, 6, "Actividad Programada: ",0);	
			
			$this->SetFont('Times','',12);
			$this->MultiCell(193,6,$columna[4],0,1);
			
			$this->Ln();

			$this->SetFont('Times','B',13);
			$this->Cell(80, 6, utf8_decode("Tiempo de ejecución Estimado (Horas): "),0);
			
			
			$this->SetFont('Times','',12);
			$this->MultiCell(193,6,$columna[5],0,1);
			
			$this->Ln();
			
			$this->SetFont('Times','B',13);
			$this->Cell(15, 6, "Fecha: ",0);
			
			
			$this->SetFont('Times','',12);
			$this->MultiCell(193,6,$columna[6],0,1);
			
			$this->Ln();
			
			$this->SetFont('Times','B',13);
			$this->Cell(70, 6, "Total a pagar aproximado (RD$): ",0);
			
			
			$this->SetFont('Times','',12);
			$this->MultiCell(193,6,round($total,2),0,1);
			
			$this->Ln();
		}
		//tabla
		if(!empty($tabla))
		{
			$this->Cell(50, 6, "Empleados que realizaran el trabajo correspondiente:", 0,1);
			$this->Ln();
			
			$this->SetFillColor(255,0,0);
			$this->SetDrawColor(0);
			$this->SetLineWidth(.3);
			
			$w = array
			(0 => 50,
			 1 => 50,
			 2 => 77,
			 3 => 15);
			 
			$header= array('Nombre', 'Cargo', 'Departamento', 'Cedula');
			for($i=0;$i<count($header);$i++)
			{
				$this->Cell($w[$i],10,$header[$i],1,0,'C');
			}
			$this->Ln();
			$this->SetFont('Times','B',6);
			$this->SetFillColor(224,235,255);
			$this->SetTextColor(0);
			$this->SetFont('');
			// Datos
			foreach($tabla as $row)
			{
				$columna = explode(";",$row); //separar los datos en posiciones de arreglo 
				$this->Cell($w[0],6,utf8_decode($columna[0]),1,0,'C');
				$this->Cell($w[1],6,utf8_decode($columna[1]),1,0,'C');
				$this->Cell($w[2],6,utf8_decode($columna[2]),1,0,'C');
				$this->Cell($w[3],6,$columna[3],1,0,'C');				
				$this->Ln();
			}
			$this->Cell(array_sum($w),0,'','T');
		}
		else
		{
			$this->Cell(50, 6, "Este formulario no tiene empleados.", 0,1);
		}
		
	}
}

// Creación del objeto de la clase heredada
$pdf=new PDF('P', 'mm', 'A4');
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetFont('Times','B',12);
$pdf->Body($datos, $tabla, $totalAprx);
$pdf->Output();

?>