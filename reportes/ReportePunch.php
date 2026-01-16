<?php
require('../fpdf/fpdf.php');
include('../lib/motor.php');

class PDF extends FPDF
{
	// Cabecera de página
	function Header()
	{
		// Logo
		$this->Image('../imagenes/logo-promosecal.png',12,11,50, 20);
		$this->Image('../imagenes/farmacia-logo.png',235,10,50,20);
		// Arial bold 15
		$this->SetFont('Arial','B',14);
		$this->Ln(20);
		// Movernos a la derecha
		// Título
		$this->Cell(275, 15, "Reporte de Entrada y Salida",0,0, 'C');
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
	
	function Body($tabla)
	{
		$this->setLeftMargin(12);
		//tabla
		if(!empty($tabla))
		{			
			$this->SetFillColor(255,0,0);
			$this->SetDrawColor(0);
			$this->SetLineWidth(.3);
			$this->SetFont('Times','B',12);
			$w = array
			(0 => 90,
			 1 => 20,
			 2 => 100,
			 3 => 20,
			 4 => 20,
			 5 => 20);
			 
			$header= array('Nombre', 'Codigo','Departamento', 'Fecha', 'Entrada', 'Salida');
			for($i=0;$i<count($header);$i++)
			{
				$this->Cell($w[$i],10,$header[$i],1,0,'C');
			}
			$this->Ln();
			$this->SetFont('Times','',10);
			$this->SetFillColor(224,235,255);
			// Datos
			foreach($tabla as $row)
			{
				
				$columna = explode(";",$row); //separar los datos en posiciones de arreglo 
				
				if($columna[5] == $columna[6]){
					
					$columna[1] .=  "*";
				}
				
				$this->Cell($w[0],6,utf8_decode($columna[0]),1,0,'C');
				$this->Cell($w[1],6,$columna[1],1,0,'C');
				$this->SetFont('Times','',7);
				$this->Cell($w[2],6,utf8_decode($columna[3]),1,0,'C');
				$this->SetFont('Times','',10);
				$this->Cell($w[3],6,$columna[4],1,0,'C');
				$this->Cell($w[4],6,$columna[5],1,0,'C');
				$this->Cell($w[5],6,$columna[6],1,0,'C');			
				$this->Ln();
				
			}
			$this->Cell(array_sum($w),0,'','T');
		}
		else
		{
			$this->Cell(50, 6, "Este formulario no tiene empleados en la fecha seleccionada.", 0,1);
		}
	}
}
// Creación del objeto de la clase heredada
$pdf=new PDF('L', 'mm', 'A4');
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetFont('Times','B',12);
$pdf->Body($_SESSION['datos']);
$pdf->Output();
?>