<?php
require('../fpdf/fpdf.php');
include('../lib/motor.php');
global $tabla;
	
	if(isset($_GET['fi']) && isset($_GET['ff']))
	{
		$query="SELECT empleado.nombre as nombre, cedula, t_cargo.nombre as cargo, t_departamento.nombre as departamento, ROUND(sum(pago),2) as pago
				FROM empleado, horario, historial_empleado, t_cargo, t_departamento,
				solicitudes, solicitudes_autorizadas, solicitudhe
				WHERE empleado.id = horario.id_empleado
				AND t_cargo.id = empleado.cargo
				AND solicitudes.id_solicitud = solicitudhe.id
				AND solicitudes.id_empleado = empleado.id
				AND solicitudes_autorizadas.id_solicitud = solicitudhe.id
				AND tipo =  'HoraExtra'
				AND autorizado =1
				AND horario.fecha = solicitudhe.fecha
				AND t_departamento.id = empleado.departamento
				and empleado.id = historial_empleado.id_empleado
				and horario.id = historial_empleado.id_horario
				AND horario.fecha 
				BETWEEN '{$_GET['fi']}'
				AND '{$_GET['ff']}'
				GROUP BY empleado.nombre";
		$rs = mysql_query($query);
		if($rs)
		{
			while($fila=mysql_fetch_assoc($rs))
			{
				$tabla[]=$fila['nombre'].";".$fila['cedula'].";".$fila['cargo'].";".$fila['departamento'].";".$fila['pago'];
			}
		}		
		$query="SELECT ROUND(sum(pago),2) as totalpago 
		FROM empleado, horario, historial_empleado, t_cargo, t_departamento, solicitudes, solicitudes_autorizadas, solicitudhe 
		WHERE empleado.id = horario.id_empleado 
		AND t_cargo.id = empleado.cargo 
		AND solicitudes.id_solicitud = solicitudhe.id 
		AND solicitudes.id_empleado = empleado.id 
		AND solicitudes_autorizadas.id_solicitud = solicitudhe.id 
		AND tipo = 'HoraExtra' AND autorizado =1 
		AND horario.fecha = solicitudhe.fecha 
		AND t_departamento.id = empleado.departamento 
		and empleado.id = historial_empleado.id_empleado 
		and horario.id = historial_empleado.id_horario 
		AND horario.fecha BETWEEN '{$_GET['fi']}' AND '{$_GET['ff']}'";
		$rs=mysql_query($query);
		if($rs)
		{
			$fila=mysql_fetch_assoc($rs);
			$tabla[]=$fila['totalpago'];
		}	
		
	}
	else
	{
		header('Location:reporteHoraExtra.php');
	}

class PDF extends FPDF
{
	// Cabecera de página
	function Header()
	{
		// Logo
		$this->Image('../imagenes/logo-promosecal.png',15,10,50, 20);
		$this->Image('../imagenes/farmacia-logo.png',230,10,50,20);
		// Arial bold 15
		$this->SetFont('Arial','B',14);
		$this->Ln(10);
		// Movernos a la derecha
		// Título
		$this->Cell(270, 36, "Reporte de trabajo en horas extraordinarias",0,0, 'C');
		$this->Ln(10);
		$this->SetFont('Arial','',14);
		$this->Cell(268, 36,"Reportar: DESDE: {$_GET['fi']} HASTA: {$_GET['ff']}",0,0,'C');
		// Salto de línea
		$this->Ln(30);
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
		//tabla		
		$this->SetFillColor(255,0,0);
		$this->SetDrawColor(0);
		$this->SetLineWidth(.3);
		$this->setLeftMargin(19);
		
		$w = array
		(0 => 60,
		 1 => 20,
         2 => 40,
		 3 => 60, 
		 4 => 20,
		 5 => 60);
		 
		$header= array('Nombre', 'Cedula','Cargo', 'Departamento', 'Pago (RD$)', 'Firma');
		for($i=0;$i<count($header);$i++)
		{
			$this->Cell($w[$i],10,$header[$i],1,0,'C');
		}
		$this->Ln();
		$this->SetFont('Times','B',6);
		$this->SetFillColor(224,235,255);
		$this->SetTextColor(0);
		// Datos
		foreach($tabla as $row)
		{
			$columna = explode(";",$row); //separar los datos en posiciones de arreglo 
			if($row == end($tabla))
			{
				$this->Cell(180,6,"Total",'LR');
				$this->Cell(15,6,$columna[0],1,0,'C');
			}
			else
			{
				$this->Cell($w[0],8,$columna[0],1,0,'C');
				$this->Cell($w[1],8,$columna[1],1,0,'C');
				$this->Cell($w[2],8,$columna[2],1,0,'C');	
				$this->Cell($w[3],8,$columna[3],1,0,'C');
				$this->Cell($w[4],8,$columna[4],1,0,'C');	
				$this->Cell(60,8,'',1,0,'C');				
			}
			$this->Ln();
		}
		$this->Cell(array_sum($w),0,'','T');
	}
}

// Creación del objeto de la clase heredada
$pdf=new PDF('L', 'mm', 'A4');
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetFont('Times','B',8);
$pdf->Body($tabla);
$pdf->Output();
?>