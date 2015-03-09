<?php
require('../fpdf/fpdf.php');
include('../lib/motor.php');
if(!isset($_SESSION)){
	session_start();
}

global $datos;
global $temp;
/*
echo '<pre>';
echo print_r($_SESSION);
echo '</pre>';
*/

if(isset($_SESSION['datos']))
{
	foreach($_SESSION['datos'] as $campo)
	{
		$temp[]=$campo;
	}
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
		$this->SetLeftMargin(7);
		// Logo
		$this->Image('../imagenes/logo-promosecal.png',10,10,50, 20);
		$this->Image('../imagenes/farmacia-logo.png',238,10,50,20);
		// Arial bold 15
		$this->SetFont('Arial','I',11);
		$this->Ln(10);
		// Movernos a la derecha
		// Título
		$this->SetFont('Arial','B',15);
		$this->Cell(270,25,$_SESSION['titulo'],0,0,'C');			
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
	function ImprovedTable($header, $data)
	{
		$this->SetLeftMargin(7);
		// Anchuras de las columnas
		$w = array
		(0 => 55,
		 1 => 17,
		 2 => 50,
         3 => 50,
		 4 => 25,
		 5 => 21, 
		 6 => 21,
		 7 => 21,
		 8 => 21);
		// Cabeceras
		for($i=0;$i<count($header);$i++)
		{
			$this->Cell($w[$i],10,$header[$i],1,0,'C');
		}
		$this->Ln();
		$this->SetFillColor(220,235,255);
		$this->SetTextColor(0);
		$this->SetFont('Times','B',7);
		// Datos
		foreach($data as $row)
		{
			$columna = explode(";",$row);
			$this->Cell($w[0],8,$columna[0],1,0,'C');
			$this->Cell($w[1],8,$columna[1],1,0,'C');
			$this->Cell($w[2],8,$columna[2],1,0,'C');
			$this->Cell($w[3],8,$columna[3],1,0,'C');			
			$this->Cell($w[4],8,$columna[4],1,0,'C');
			$this->Cell($w[5],8,$columna[5],1,0,'C');
			$this->Cell($w[6],8,$columna[6],1,0,'C');
			$this->Cell($w[7],8,$columna[7],1,0,'C');
			$this->Cell($w[8],8,$columna[8],1,0,'C');				
			$this->Ln();			
		}
		// Línea de cierre
		$this->Cell(array_sum($w),0,'','T');
	}
}
// Creación del objeto de la clase heredada
$header = array('Nombre', 'Cedula','Cargo', 'Departamento','No. Solicitud', 'F.Salida', 'F.Entrada', 'H.Salida', 'H.Entrada');
$pdf=new PDF('L', 'mm', 'A4');
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetFont('Times','B',12);
$pdf->ImprovedTable($header, $temp);
$pdf->Output();
?>