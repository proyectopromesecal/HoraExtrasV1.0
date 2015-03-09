<?php
require('../fpdf/fpdf.php');
include('../lib/motor.php');
if(!isset($_SESSION)){
	session_start();
}

$datos;
$data1;
$data2;

/*
echo '<pre>';
echo print_r($_SESSION);
echo '</pre>';
*/

if(isset($_GET['f']) && !empty($_GET['f']))
{
	set_time_limit(0);
	$datos = ManejadorDietaViatico::obtenerReportePunch($_GET['f']);
	$data1 = $datos[0];
	$data2 = $datos[1];
}
else
{
	header('Location:generadorViatico.php');
}

class PDF extends FPDF
{
	// Cabecera de página
	function Header()
	{
		$this->SetLeftMargin(20);
		// Logo
		$this->Image('../imagenes/logo-promosecal.png',21,10,50, 20);
		$this->Image('../imagenes/farmacia-logo.png',140,10,50,20);
		// Arial bold 15
		$this->SetFont('Arial','I',11);
		$this->Ln(15);
		// Movernos a la derecha
		// Título
		$this->SetFont('Arial','B',15);
		$this->Cell(170,25,"Reporte de punch de la solicitud ".ManejadorDietaViatico::obtenerNombre($_GET['f'])." de Dieta y Viaticos",0,0,'C');			
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
	function ImprovedTable($header, $data, $data2)
	{
		$m=new Manejador();
		$this->SetLeftMargin(20);
		// Anchuras de las columnas
		$w = array
		(0 => 65,
		 1 => 35,
		 2 => 35,
		 3 => 35);
		// Datos
		if(empty($data['nombre']))
		{
			$this->Ln();
			$this->SetFillColor(220,235,255);
			$this->SetTextColor(0);
			$this->SetFont('Times','B',10);
			$this->Cell(165,8,"La informacion de los empleados esta incompleta y no se ha podido realizar el calculo.",0,0,'C');
		}
		else
		{
			// Cabeceras
			for($i=0;$i<count($header);$i++)
			{
				$this->Cell($w[$i],10,$header[$i],1,0,'C');
			}
			$this->Ln();
			$this->SetFillColor(220,235,255);
			$this->SetTextColor(0);
			$this->SetFont('Times','',9);
			foreach($data2 as $id)
			{
				$this->Cell($w[0],8,$data["nombre"][$id],1,0,'C');

				$this->Cell($w[1],8,$data["totalbd"][$id],1,0,'C');

				$this->Cell($w[2],8,$data["totalpn"][$id],1,0,'C');
				
				$this->Cell($w[2],8,$data["debe"][$id],1,0,'C');
				
				$this->Ln();		
			}		
			// Línea de cierre
			$this->Cell(array_sum($w),0,'','T');
		}
	}
}
// Creación del objeto de la clase heredada
$header = array('Nombre', 'Total Consumido','Total Entregado', 'Devolucion');
$pdf=new PDF('P', 'mm', 'A4');
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetFont('Times','B',12);
$pdf->ImprovedTable($header, $data1,$data2);
$pdf->Output();
?>