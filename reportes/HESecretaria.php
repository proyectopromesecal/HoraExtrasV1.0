<?php
require('../fpdf/fpdf.php');
include('../lib/motor.php');
global $tabla;
$params = array();
$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );
	
	if(isset($_GET['fi']) && isset($_GET['ff']))
	{
		$query="SELECT empleado.nombre as nombre, cedula, t_cargo.nombre as cargo, t_departamento.nombre as departamento, REPLACE(CONVERT(VARCHAR,CONVERT(MONEY,sum(pago)),1), '.00','') as pago
				FROM empleado, horario, historial_empleado, t_cargo, t_departamento,
				solicitudes, solicitudes_autorizadas, solicitudhe
				WHERE empleado.id = horario.id_empleado
				AND t_cargo.id = empleado.cargo
				AND solicitudes.id_solicitud = solicitudhe.id
				AND solicitudes.id_empleado = empleado.id
				AND solicitudes_autorizadas.id_solicitud = solicitudhe.id
				AND tipo =  'HoraExtra'
				AND autorizado =1
				AND solicitudhe.usr='{$_SESSION['usuario']}'
				AND horario.fecha = solicitudhe.fecha
				AND t_departamento.id = empleado.departamento
				and empleado.id = historial_empleado.id_empleado
				and horario.id = historial_empleado.id_horario
				AND horario.fecha BETWEEN '{$_GET['fi']}' AND '{$_GET['ff']}'
				GROUP BY empleado.nombre, empleado.cedula, t_cargo.nombre, t_departamento.nombre";
		$rs = sqlsrv_query($_SESSION['con'],$query, $params, $options);
		if($rs)
		{
			while($fila=sqlsrv_fetch_array($rs, SQLSRV_FETCH_ASSOC))
			{
				$tabla[]=$fila['nombre'].";".$fila['cedula'].";".$fila['cargo'].";".$fila['departamento'].";".$fila['pago'];
			}
		}

		$query="SELECT REPLACE(CONVERT(VARCHAR,CONVERT(MONEY,sum(pago)),1), '.00','') as totalpago 
				FROM empleado, horario, historial_empleado, t_cargo, t_departamento,
				solicitudes, solicitudes_autorizadas, solicitudhe
				WHERE empleado.id = horario.id_empleado
				AND t_cargo.id = empleado.cargo
				AND solicitudes.id_solicitud = solicitudhe.id
				AND solicitudes.id_empleado = empleado.id
				AND solicitudes_autorizadas.id_solicitud = solicitudhe.id
				AND tipo =  'HoraExtra'
				AND autorizado =1
				AND solicitudhe.usr='{$_SESSION['usuario']}'
				AND horario.fecha = solicitudhe.fecha
				AND t_departamento.id = empleado.departamento
				and empleado.id = historial_empleado.id_empleado
				and horario.id = historial_empleado.id_horario
				AND horario.fecha BETWEEN '{$_GET['fi']}' AND '{$_GET['ff']}'";
		$rs = sqlsrv_query($_SESSION['con'],$query, $params, $options);
		if($rs)
		{
			while($fila=sqlsrv_fetch_array($rs, SQLSRV_FETCH_ASSOC))
			{
				$tabla[]=$fila['totalpago'];
			}
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
		$this->Image('../imagenes/farmacia-logo.png',235,10,50,20);
		// Arial bold 15
		$this->SetFont('Arial','B',14);
		$this->Ln(10);
		// Movernos a la derecha
		// Título
		$this->Cell(275, 36, "Reporte de trabajo en horas extraordinarias",0,0, 'C');
		$this->Ln(10);
		$this->SetFont('Arial','',14);
		$this->Cell(275, 36,"Reportar: DESDE: {$_GET['fi']} HASTA: {$_GET['ff']}",0,0,'C');
		// Salto de línea
		$this->Ln(30);
		$this->setLeftMargin(40);
	}
	
	
	// Pie de página
	function Footer()
	{
		$this->setLeftMargin(40);
		$this->SetY(-40);

		$this->SetFont('Arial','B',8);
		$this->Cell(30,7, "",1,0);
		$this->Cell(60,7, "Preparado por:",1,0,'C');
		$this->Cell(65,7, "Aprobado por:",1,0,'C');
		$this->Cell(60,7, "Revisado por:",1,0,'C');
		$this->Ln();
		
		$this->Cell(30,7, "Firma",1,0);
		$this->SetFont('Arial','',9);
		$this->Cell(60,7, "",1,0,'C');
		$this->Cell(65,7, "",1,0,'C');
		$this->Cell(60,7, "",1,0,'C');
		$this->Ln();
		
		$this->SetFont('Arial','B',9);
		$this->Cell(30,7, "Nombre",1,0);
		$this->Cell(60,7, Manejador::obtenerNombreCompleto($_SESSION['id']),1,0,'C');
		$this->Cell(65,7, Manejador::obtenerEncargado(Manejador::obtenerIdDpto($_SESSION['dpto'])),1,0,'C');
		$this->Cell(60,7, "",1,0,'C');
		$this->Ln();
		
		$this->SetFont('Arial','B',8);
		$this->Cell(30,7, "Rol",1,0);
		$this->Cell(60,7, Manejador::obtenerCargo($_SESSION['id']),1,0,'C');
		$this->Cell(65,7, "Encargado",1,0,'C');
		$this->Cell(60,7, "DEPARTAMENTO DE GESTION HUMANA",1,0,'C');
		$this->Ln();
		
		$this->SetFont('Arial','B',9);
		$this->Cell(30,7, "Fecha",1,0);
		$this->Cell(60,7, date('Y-m-d H:i:s'),1,0,'C');
		$this->Cell(65,7, "",1,0,'C');
		$this->Cell(60,7, "",1,0,'C');
		$this->Ln();
		$this->setLeftMargin(10);
	}
	
	function Body($tabla)
	{
		//tabla		
		$this->SetFillColor(255,0,0);
		$this->SetDrawColor(0);
		$this->SetLineWidth(.3);
		$this->setLeftMargin(40);
		
		$w = array
		(0 => 60,
		 1 => 15,
         2 => 40,
		 3 => 85, 
		 4 => 15);
		 
		$header= array('Nombre', 'Cedula','Cargo', 'Departamento', 'Pago (RD$)');
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
			if ($row == end($tabla)) {
				$this->Cell(200,6,"Total",1,0,'LR');
				$this->Cell(15,6,$columna[0],1,0,'C');
			}
			else
			{

				$this->Cell($w[0],8,$columna[0],1,0,'C');
				$this->Cell($w[1],8,$columna[1],1,0,'C');
				$this->Cell($w[2],8,$columna[2],1,0,'C');	
				$this->Cell($w[3],8,$columna[3],1,0,'C');
				$this->Cell($w[4],8,$columna[4],1,0,'C');			
								
			}
			$this->Ln();
		}
		$this->Cell(array_sum($w),0,'','T');
	}
}

// Creación del objeto de la clase heredada
$pdf=new PDF('L', 'mm', 'A4');
$pdf->AliasNbPages();
$pdf->setAutoPageBreak(true,50);
$pdf->AddPage();
$pdf->SetFont('Times','B',8);
$pdf->Body($tabla);
$pdf->Output();
?>