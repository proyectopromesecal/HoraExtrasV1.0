<?php
require('fpdf/fpdf.php');
include('lib/motor.php');

global $datos;
global $tabla;
global $codigo;
global $he;

if(isset($_GET['s']))
{	
	$query="SELECT * FROM formulario_transporte
			WHERE id={$_GET['s']}";
	$params = array();
	$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );			
	$rs = sqlsrv_query($_SESSION['con'],$query, $params, $options);
	if($rs)
	{
		while($fila=sqlsrv_fetch_array($rs, SQLSRV_FETCH_ASSOC))
		{
			$datos[]=$fila['area'].";".$fila['fecha']->format('d/m/Y');
		}
	}
	$rs = ManejadorTransporte::obtenerEmpleados($_GET['s']);
	if($rs)
	{
		if(sqlsrv_num_rows($rs) >0)
		{
			while($fila=sqlsrv_fetch_array($rs, SQLSRV_FETCH_ASSOC))
			{
				$tabla[]=$fila['nombre'].";".$fila['cargo'].";".$fila['cedula'].";".$fila['pago'];
			}			
		}
	}
}
else
{
	header('Location:formulariotransporte.php');
}
class PDF extends FPDF
{
	// Cabecera de página
	function Header()
	{
		$this->SetLeftMargin(22);
		$this->SetXY(22, 10);
		$codigo = ManejadorTransporte::obtenerNombre($_GET['s']);
		// Arial bold 10
		$this->SetFont('Arial','',10);
		$this->Cell(95, 9,$this->Image('imagenes/logo-promosecal.png',30,10,60,10),1,0,'C');
		$this->Cell(70, 9,"Código:     {$codigo}",1,0, 'C');
		$this->Ln();
		
		$this->SetFont('Arial','',10);
		$this->Cell(65, 8, "Fecha de Emision:   ".date('d/m/Y'),1,0, 'C');
		$this->Cell(100,8,'Página Actual:     '.$this->PageNo().'/{nb}',1,0,'C');
		$this->Ln();
		
		$this->SetFont('Arial','B',10);
		$this->Cell(65, 8, "Titulo del documento:",1,0, 'C');
		
		$this->SetFont('Arial','',10);
		$this->Cell(100, 8, "Formulario para el pago de transporte en horas extraordinarias",1,0, 'C');
		$this->Ln();
		
		$this->Ln(10);
	}
	
	function Body($data, $tabla)
	{
		$this->SetAutoPageBreak(true, 45);
		//$this->SetLeftMargin(22);
		// Datos
		foreach($data as $row)
		{		
			$columna = explode(";",$row); //separar los datos en posiciones de arreglo 
			
			$this->SetFont('Times','B',13);
			$this->Cell(20, 7, "Area: ",0);
			
			$this->SetFont('Times','',12);
			$this->MultiCell(193,7,$columna[0],0,1);
			
			$this->Ln();
			
			$this->SetFont('Times','B',13);
			$this->Cell(20, 7, "Fecha: ",0);
			
			$this->SetFont('Times','',12);
			$this->MultiCell(193,7,$columna[1],0,1);	
			
			$this->Ln();
			
			$this->SetFont('Times','B',13);
			$this->Cell(55, 7, "Solicitud de Hora Extra: ",0);
			
			$this->SetFont('Times','',12);
			$he = ManejadorTransporte::obtenerSolicitudHE($_GET['s']);
			$this->MultiCell(193,7,$he,0,1);
			
			$this->Ln(10);
		}
		//tabla
		if(!empty($tabla))
		{
			//$this->SetLeftMargin(32);
			$this->Cell(50, 6, "Empleados que solicitan el transporte:", 0,1);
			$this->Ln();
			
			$w = array
			(0 => 55,
			 1 => 50,
			 2 => 25,
			 3 => 15);
			 
			$header= array('Nombre', 'Cargo', 'Cedula', 'Pago');
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
				$this->Cell($w[0],6,$columna[0],1,0,'C');
				$this->Cell($w[1],6,$columna[1],1,0,'C');
				$this->Cell($w[2],6,$columna[2],1,0,'C');
				$this->Cell($w[3],6,$columna[3]." RD$",1,0,'C');				
				$this->Ln();
			}
			$this->Cell(array_sum($w),0,'','T');			
		}
		else
		{
			$this->Cell(50, 6, "Este formulario no tiene empleados.", 0,1);
		}
		//$this->SetLeftMargin(32);
	}
}

// Creación del objeto de la clase heredada
$pdf=new PDF('P', 'mm', 'A4');
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetFont('Times','B',12);
$pdf->SetLeftMargin(22);
$pdf->Body($datos, $tabla);
$pdf->Output();
?>