<?php
require('fpdf/fpdf.php');
include('lib/motor.php');

global $tablaS;
global $tablaE;
global $tablaD;
$conteo = array();

if(isset($_GET['s']))
{
	//OBTENER Y EMPAQUETAR LA INFORMACION DEL FORMULARIO/SOLICITUD
	$query="SELECT * FROM dietaviatico
			WHERE id={$_GET['s']}";
	$params = array();
	$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );
	$rs=sqlsrv_query($_SESSION['con'],$query, $params, $options);
	if($rs)
	{
		while($fila=sqlsrv_fetch_array($rs, SQLSRV_FETCH_ASSOC))
		{
			$tablaS[]=$fila['no_oficio'].";".$fila['objetivo'].";".$fila['departamento'].";".$fila['fecha_creacion']->format('d/m/Y');			
		}
	}
	
	// OBTENER Y EMPAQUETAR LA INFORMACION DE LOS EMPLEADOS 
	$rs2 = ManejadorDietaViatico::obtenerBeneficiariosReporte($_GET['s']);
	$x=0;
	if($rs2)
	{
		while($fila=sqlsrv_fetch_array($rs2, SQLSRV_FETCH_ASSOC))
		{
			$x+=1;
			$tablaE[]=$fila['nombre'].";".$fila['cargo'].";".$fila['cedula'].";".$fila['total'];
		}	
	}
	
	// OBTENER Y EMPAQUETAR LOS DESTINOS Y FECHAS
	$query2="SELECT id,fecha_entrada, fecha_salida,
			 convert(varchar,hora_entrada, 108) as hora_entrada, convert(varchar,hora_salida, 108) as hora_salida, lugar
			 FROM destinos_viaticos
			 WHERE id_viatico ={$_GET['s']}";
	$rs3=sqlsrv_query($_SESSION['con'],$query2, $params, $options);
	if($rs3)
	{
		while($fila=sqlsrv_fetch_array($rs3, SQLSRV_FETCH_ASSOC))
		{
			$tablaD[]=$fila['fecha_salida']->format('d/m/Y').";".$fila['fecha_entrada']->format('d/m/Y').";".$fila['hora_salida'].";".$fila['hora_entrada'].";".$fila['lugar'];
		}
	}

	//OBTENER Y EMPAQUETAR LOS CONTEOS DE CONCEPTO POR DIA
	$conteo = ManejadorDietaViatico::obtenerDetalleViatico($_GET['s']);
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
		$this->Image('imagenes/farmacia-logo.png',230,10,50,20);
		// Arial bold 15
		$this->SetFont('Arial','B',14);
		$this->Ln(20);
		// Movernos a la derecha
		// Título
		$this->Cell(280, 15, "Formulario Dieta y Viaticos",0,0, 'C');
		// Salto de línea
		$this->Ln(20);
	}

	// Pie de página
	function Footer()
	{
		$usr = explode("@",$_SESSION['usuario']);

		// Posición: a 1,5 cm del final
		$this->SetY(-15);
		// Arial italic 8
		$this->SetFont('Arial','I',8);
		// Número de página
		$this->Cell(1,10,$usr[0]." - ".$_GET['s'], 0,0,'L');
		$this->Cell(0,10,date('d/m/Y H:i:s'), 0,0,'C');
		$this->Cell(0,10,'Pagina '.$this->PageNo().'/{nb}',0,0,'R');
		//$this->Cell(0,10,'test6 '.$this->PageNo().'/{nb}',0,0,'C');
	}
	
	function Body($tablaS, $tablaE, $tablaD, $conteo)
	{
		$this->setLeftMargin(12);

		// Datos del Formulario
		foreach($tablaS as $row)
		{
			$columna = explode(";",$row); //separar los datos en posiciones de arreglo 
			$this->SetFont('Times','B',13);
			$this->Cell(45, 6, "Número de Solicitud: ",0);
			
			$this->SetFont('Times','',12);
			$this->Cell(150,6,$columna[0],0);

			$this->SetFont('Times','B',13);
			$this->Cell(40, 6, "Fecha de creación: ",0);	
			
			$this->SetFont('Times','',12);
			$this->Cell(40,6,$columna[3],0,1);

			$this->Ln();

			$this->SetFont('Times','B',13);
			$this->Cell(72, 6, "Departamento / División / Sección: ",0);
			
			$this->SetFont('Times','',12);
			$this->MultiCell(193,6,utf8_decode($columna[2]),0,1);	
			
			
			$this->Ln();
			$this->SetFont('Times','B',13);
			$this->Cell(45, 6, "Actividad a realizar: ",0);
			
			$this->SetFont('Times','',12);
			$this->MultiCell(193,6,$columna[1],0,1);
			
			$this->Ln();
			
		}

		//Tabla Destinos
		if(!empty($tablaD))
		{
			$this->SetFont('Times','B',13);
			$this->Cell(50, 6, "Lugares/destinos de esta solicitud y cantidad de pagos:", 0,1);
			$this->Ln();
			
			$w = array
			(0 => 25,
			 1 => 25,
			 2 => 25,
			 3 => 25,
			 4 => 70,
			 5 => 25,
			 6 => 25,
			 7 => 25,
			 8 => 25);
			
			$this->SetFont('Times','B',12);
			$headerD= array('Salida', 'Entrada', 'Salida','Entrada', '', 'Desayuno', 'Almuerzo', 'Cena', 'Dormitorio');
			$this->Cell(50,10,'Fechas',1,0,'C');
			$this->Cell(50,10,'Horas',1,0,'C');
			$this->Cell(70,17,'Lugar / Destino',1,0,'C');
			$this->Cell(100,10,'Cantidad por concepto',1,0,'C');
			$this->Ln();
			for($i=0;$i<count($headerD);$i++)
			{
				if ($i==4) {
					$this->Cell($w[$i],7,$headerD[$i],0,0,'C');
				}
				else
				{
					$this->Cell($w[$i],7,$headerD[$i],1,0,'C');
				}
			}

			$this->Ln();

			$this->SetFont('Times','B',8);
			$this->SetFillColor(224,235,255);
			$this->SetTextColor(0);
			$this->SetFont('');
			$indiceConteo=0;
			// Datos
			foreach($tablaD as $row)
			{
				$columna = explode(";",$row); //separar los datos en posiciones de arreglo 
				$this->Cell($w[0],5,$columna[1],1,0,'C');
				$this->Cell($w[1],5,$columna[0],1,0,'C');
				$this->Cell($w[2],5,$columna[3],1,0,'C');
				$this->Cell($w[3],5,$columna[2],1,0,'C');	
				$this->Cell($w[4],5,$columna[4],1,0,'C');

				$columnaConteo = explode(";", $conteo[$indiceConteo]);

				$this->Cell($w[5],5,$columnaConteo[2],1,0,'C');
				$this->Cell($w[6],5,$columnaConteo[3],1,0,'C');
				$this->Cell($w[7],5,$columnaConteo[4],1,0,'C');
				$this->Cell($w[8],5,$columnaConteo[5],1,0,'C');
				$this->Ln();
				$indiceConteo+=1;
			}
			$this->Cell(array_sum($w),0,'','T');
			$this->Ln();
		}
		else
		{
			$this->Cell(50, 6, "Este formulario no tiene lugares agregados.", 0,1);
		}

		$this->Ln(10);

		//Tabla Empleados
		if(!empty($tablaE))
		{
			$this->SetFont('Times','B',13);
			$this->Cell(50, 6, "Beneficiarios y monto total a pagar: ", 0,1);
			$this->Ln();
			
			$w = array
			(0 => 80,
			 1 => 70,
			 2 => 45,
			 3 => 25,
			 4 => 50);
			 
			$headerE= array('Nombre', 'Cargo', 'Cedula', 'Total RD$', 'Firma');
			for($i=0;$i<count($headerE);$i++)
			{
				$this->Cell($w[$i],10,$headerE[$i],1,0,'C');
			}
			$this->Ln();
			$this->SetFont('Times','B',8);
			$this->SetFillColor(224,235,255);
			$this->SetTextColor(0);
			$this->SetFont('');
			$pagoTotal=0;
			$final=false;
			// Datos
			foreach($tablaE as $row)
			{
				$columna = explode(";",$row); //separar los datos en posiciones de arreglo 
				if($row == end($tablaE))
				{
					$final=true;
				}
				$pagoTotal+=str_replace(",", "", $columna[3]);
				$this->Cell($w[0],6,utf8_decode($columna[0]),1,0,'C');
				$this->Cell($w[1],6,$columna[1],1,0,'C');
				$this->Cell($w[2],6,$columna[2],1,0,'C');
				$this->Cell($w[3],6,$columna[3],1,0,'C');		
				$this->Cell($w[4],6,"",1,0,'C');								
				if ($final) {
					$this->Ln();
					$this->SetFont('Times','B',12);
					$this->Cell(270,6,'Total General: '.number_format($pagoTotal,2,'.',','),1,0,'L');
				}
				$this->Ln();
			}
			$this->Cell(array_sum($w),0,'','T');
		}
		else
		{
			$this->Cell(50, 6, "Este formulario no tiene empleados.", 0,1);
		}
		

		$this->Ln(10);
		
		//$this->SetY(250);
		$this->SetLeftMargin(15);
		$this->SetFont('Times','B',12);
		$this->Cell(95,3,"__________________________________", 0,0, 'L');
		$this->Cell(90,3,"__________________________________", 0,0,'L');
		$this->Cell(90,3,"__________________________________", 0,0,'L');
		$this->Ln();
		$this->Cell(85,5,"          Enc. Depto. Solicitante", 0,0, 'L');
		$this->Cell(90,5,"Enc. Depto. Financiero", 0,0,'C');
		$this->Cell(90,5,"Enc. Depto. Administrativo", 0,0,'C');	
		


	function firmas($tablaS)
	{
		$this->setLeftMargin(12);

		foreach ($tablaS as $row) {
			$columna = explode(";", $row);

			$this->SetFont('Times','B',13);
			$this->Cell(45, 6, "Número de Solicitud: ",0);
			
			$this->SetFont('Times','',12);
			$this->Cell(150,6,$columna[0],0);

			$this->SetFont('Times','B',13);
			$this->Cell(40, 6, "Fecha de creación: ",0);	
			
			$this->SetFont('Times','',12);
			$this->Cell(40,6,$columna[3],0,1);

			$this->Ln();

			$this->SetFont('Times','B',13);
			$this->Cell(72, 6, "Departamento / División / Sección: ",0);
			
			$this->SetFont('Times','',12);
			$this->MultiCell(193,6,$columna[2],0,1);	
		}


		$this->SetLeftMargin(15);
		$this->SetY(130);	

		//$this->Image('imagenes/firmaviatico1.png',25,93,50, 40);
		$this->SetFont('Times','B',12);
		$this->Cell(95,3,"__________________________________", 0,0, 'L');
		$this->Cell(90,3,"__________________________________", 0,0,'L');
		$this->Cell(90,3,"__________________________________", 0,0,'L');
		$this->Ln();
		$this->Cell(85,5,"          Enc. Depto. Solicitante", 0,0, 'L');
		$this->Cell(90,5,"Enc. Depto. Financiero", 0,0,'C');
		$this->Cell(90,5,"Enc. Depto. Gestión Humana", 0,0,'C');
	}
		
	}
}

// Creación del objeto de la clase heredada
$pdf=new PDF('L', 'mm', 'A4');
$pdf->AliasNbPages();
$pdf->SetAutoPageBreak(true,15);
$pdf->AddPage();
$pdf->SetFont('Times','B',12);
$pdf->Body($tablaS, $tablaE, $tablaD, $conteo);
//$pdf->AddPage();
//$pdf->firmas($tablaS);
$pdf->Output();

?>