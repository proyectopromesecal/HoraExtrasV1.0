<?php
require('fpdf/fpdf.php');
include('lib/motor.php');

global $datos;
global $tablaE;
global $tablaD;

if(isset($_GET['s']))
{
	$query="SELECT * FROM dietaviatico
			WHERE id={$_GET['s']}";
	$params = array();
	$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );
	$rs=sqlsrv_query($_SESSION['con'],$query, $params, $options);
	if($rs)
	{
		while($fila=sqlsrv_fetch_array($rs, SQLSRV_FETCH_ASSOC))
		{
			$datos[]=$fila['no_oficio'].";".$fila['objetivo'].";".$fila['descripcion'].";".$fila['departamento'].";".$fila['fecha_creacion']->format('d/m/Y');			
		}
	}
	
	$rs2 = ManejadorDietaViatico::obtenerBeneficiariosReporte($_SESSION['dpto'],$_GET['s']);
	if($rs2)
	{
		while($fila=sqlsrv_fetch_array($rs2, SQLSRV_FETCH_ASSOC))
		{
			$tablaE[]=$fila['nombre'].";".$fila['cargo'].";".$fila['cedula'].";".$fila['concepto'].";".$fila['total'];
		}	
	}
	
	$query2="SELECT destinos_viaticos.id as id,fecha_entrada as fecha_entrada, fecha_salida as fecha_salida,
			 convert(varchar,hora_entrada, 108) as hora_entrada, convert(varchar,hora_salida, 108) as hora_salida, centro_salud.nombre as lugar
			 FROM destinos_viaticos, centro_salud
			 WHERE id_viatico ={$_GET['s']}
			 AND centro_salud.id = destinos_viaticos.lugar";
	$rs3=sqlsrv_query($_SESSION['con'],$query2, $params, $options);
	if($rs3)
	{
		while($fila=sqlsrv_fetch_array($rs3, SQLSRV_FETCH_ASSOC))
		{
			$tablaD[]=$fila['fecha_salida']->format('d/m/Y').";".$fila['fecha_entrada']->format('d/m/Y').";".$fila['hora_salida'].";".$fila['hora_entrada'].";".$fila['lugar'];
		}
	}
}
else
{
	header('Location:solicitudesviaticos.php');
}
class PDF extends FPDF
{
	// Cabecera de página
	function Header()
	{
		// Logo
		$this->Image('imagenes/logo-promosecal.png',13,10,50, 23);
		$this->Image('imagenes/farmacia-logo.png',147,10,50,20);
		// Arial bold 15
		$this->SetFont('Arial','B',14);
		$this->Ln(20);
		// Movernos a la derecha
		// Título
		$this->Cell(190, 15, "Formulario Dieta y Viaticos",0,0, 'C');
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
	
	function Body($data, $tablaE, $tablaD)
	{
		$this->setLeftMargin(12);
		// Datos del Formulario
		foreach($data as $row)
		{
			$columna = explode(";",$row); //separar los datos en posiciones de arreglo 
			$this->SetFont('Times','B',13);
			$this->Cell(45, 6, "Numero de Solicitud: ",0);
			
			$this->SetFont('Times','',12);
			$this->MultiCell(193,6,$columna[0],0);
			
			$this->Ln();
			$this->SetFont('Times','B',13);
			$this->Cell(50, 6, "Objetivo: ",0);
			
			$this->Ln();
			
			$this->SetFont('Times','',12);
			$this->MultiCell(193,6,$columna[1],0,1);
			
			$this->Ln();
			
			$this->SetFont('Times','B',13);
			$this->Cell(50, 6, "Breve descripcion: ",0);
			
			$this->Ln();
			
			$this->SetFont('Times','',12);
			$this->MultiCell(193,6,$columna[2],0,1);
			
			$this->Ln();
			
			$this->SetFont('Times','B',13);
			$this->Cell(50, 6, "Departamento: ",0);
			
			$this->Ln();
			
			$this->SetFont('Times','',12);
			$this->MultiCell(193,6,$columna[3],0,1);	
			
			$this->Ln();		
			
			$this->SetFont('Times','B',13);
			$this->Cell(40, 6, "Fecha de creacion: ",0);	
			
			$this->SetFont('Times','',12);
			$this->MultiCell(193,6,$columna[4],0,1);
			
			$this->Ln();
		}
		//Tabla Empleados
		if(!empty($tablaE))
		{
			$this->Cell(50, 6, "Empleados que realizaran el trabajo correspondiente:", 0,1);
			$this->Ln();
			
			$this->SetFillColor(255,0,0);
			$this->SetDrawColor(0);
			$this->SetLineWidth(.3);
			
			$w = array
			(0 => 60,
			 1 => 50,
			 2 => 45,
			 3 => 15, 
			 4 => 15);
			 
			$headerE= array('Nombre', 'Cargo', 'Categoria', 'Cedula', 'Total');
			for($i=0;$i<count($headerE);$i++)
			{
				$this->Cell($w[$i],10,$headerE[$i],1,0,'C');
			}
			$this->Ln();
			$this->SetFont('Times','B',7);
			$this->SetFillColor(224,235,255);
			$this->SetTextColor(0);
			$this->SetFont('');
			// Datos
			foreach($tablaE as $row)
			{
				$columna = explode(";",$row); //separar los datos en posiciones de arreglo 
				$this->Cell($w[0],6,$columna[0],1,0,'C');
				$this->Cell($w[1],6,$columna[1],1,0,'C');
				$this->Cell($w[2],6,$columna[3],1,0,'C');	
				$this->Cell($w[3],6,$columna[2],1,0,'C');
				$this->Cell($w[4],6,$columna[4],1,0,'C');				
				$this->Ln();
			}
			$this->Cell(array_sum($w),0,'','T');
		}
		else
		{
			$this->Cell(50, 6, "Este formulario no tiene empleados.", 0,1);
		}
		$this->Ln(5);
		
		//Tabla Destinos
		if(!empty($tablaD))
		{
			$this->SetFont('Times','',12);
			$this->Cell(50, 6, "Lugares/destinos de esta solicitud:", 0,1);
			$this->Ln();
			
			$this->SetFillColor(255,0,0);
			$this->SetDrawColor(0);
			$this->SetLineWidth(.3);
			
			$w = array
			(0 => 30,
			 1 => 30,
			 2 => 30,
			 3 => 30,
			 4 => 65);
			 
			$headerD= array('Fecha de Salida', 'Fecha de Entrada', 'Hora de Salida','Hora de Entrada' , 'Lugar/Destino');
			for($i=0;$i<count($headerD);$i++)
			{
				$this->Cell($w[$i],10,$headerD[$i],1,0,'C');
			}
			$this->Ln();
			$this->SetFont('Times','B',7);
			$this->SetFillColor(224,235,255);
			$this->SetTextColor(0);
			$this->SetFont('');
			// Datos
			foreach($tablaD as $row)
			{
				$columna = explode(";",$row); //separar los datos en posiciones de arreglo 
				$this->Cell($w[0],6,$columna[0],1,0,'C');
				$this->Cell($w[1],6,$columna[1],1,0,'C');
				$this->Cell($w[2],6,$columna[3],1,0,'C');
				$this->Cell($w[3],6,$columna[2],1,0,'C');	
				$this->Cell($w[4],6,$columna[4],1,0,'C');	
				$this->Ln();
			}
			$this->Cell(array_sum($w),0,'','T');
		}
		else
		{
			$this->Cell(50, 6, "Este formulario no tiene lugares agregados.", 0,1);
		}
		
		$this->SetY(250);
		$this->SetLeftMargin(15);
		$this->SetFont('Times','B',12);
		$this->Cell(50,10,"__________________________________", 0,0, 'L');
		$this->Cell(130,10,"       _________________________________", 0,0,'R');
		$this->Ln();
		$this->Cell(85,10,"        Firma de Gerencia Financiera", 0);
		$this->Cell(90,10,"Firma del Supervisor Inmediato", 0,0,'R');		
		
	}
}

// Creación del objeto de la clase heredada
$pdf=new PDF('P', 'mm', 'A4');
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetFont('Times','B',12);
$pdf->Body($datos, $tablaE, $tablaD);
$pdf->Output();

?>