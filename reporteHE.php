<?php
require('fpdf/fpdf.php');
include('lib/motor.php');
if(!isset($_SESSION)){
	session_start();
}
global $temp;
$total;$tb;
$params = array();
$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );

if(isset($_SESSION['idS']))
{
	$query="SELECT empleado.nombre as nombre, t_departamento.nombre as departamento, convert(varchar,horario.fecha, 120) AS fecha,
			convert(varchar, horadeentrada, 108 ) AS horadeentrada, convert( varchar,horadesalida,  108 ) AS horadesalida, t_cargo.nombre as cargo, cedula, convert(varchar,tiempo_extra,108) as tiempo_extra, pago
			FROM empleado, horario, solicitudes, solicitudhe, solicitudes_autorizadas, historial_empleado, t_departamento, t_cargo
			WHERE solicitudhe.id = solicitudes_autorizadas.id_solicitud
			AND t_departamento.id = empleado.departamento
			AND t_cargo.id = empleado.cargo
			AND tipo = 'HoraExtra'
			AND autorizado = 1
			AND empleado.id = solicitudes.id_empleado
			AND solicitudhe.id = solicitudes.id_solicitud
			AND empleado.id = horario.id_empleado
			AND horario.fecha = solicitudhe.fecha
			AND solicitudhe.id ={$_SESSION['idS']}
			AND solicitudhe.usr = '{$_SESSION['usuario']}'
			AND historial_empleado.id_empleado = empleado.id
			AND historial_empleado.id_horario = horario.id
			group by empleado.nombre, t_departamento.nombre, horario.fecha, horadeentrada, horadesalida, t_cargo.nombre, cedula, tiempo_extra, pago";
	$params = array();
	$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );
	$rs = sqlsrv_query($_SESSION['con'],$query, $params, $options);
	
	if($rs)
	{
		while($fila=sqlsrv_fetch_array($rs, SQLSRV_FETCH_ASSOC))
		{
			$temp[] = $fila['nombre'].";".$fila['cedula'].";".$fila['cargo'].";".$fila['departamento'].";".$fila['fecha'].";".$fila['horadeentrada'].";".$fila['horadesalida'].";".$fila['tiempo_extra'].";".$fila['pago'];
		}		
	}
	
	$queryTotal="SELECT ROUND(sum(pago),2) as totalpago
				FROM empleado, horario, solicitudes, solicitudhe, solicitudes_autorizadas, historial_empleado, t_departamento, t_cargo
				WHERE solicitudhe.id = solicitudes_autorizadas.id_solicitud
				AND t_departamento.id = empleado.departamento
				AND t_cargo.id = empleado.cargo
				AND tipo = 'HoraExtra'
				AND autorizado = 1
				AND empleado.id = solicitudes.id_empleado
				AND solicitudhe.id = solicitudes.id_solicitud
				AND empleado.id = horario.id_empleado
				AND horario.fecha = solicitudhe.fecha
				AND solicitudhe.id ={$_SESSION['idS']}
				AND solicitudhe.usr = '{$_SESSION['usuario']}'
				AND historial_empleado.id_empleado = empleado.id
				AND historial_empleado.id_horario = horario.id";
	$rsTotal = sqlsrv_query($_SESSION['con'],$queryTotal, $params, $options);
	$tb = sqlsrv_fetch_array($rsTotal, SQLSRV_FETCH_ASSOC);
	$total = $tb['totalpago'];
}
else
{
	header('Location:seleccionados.php');
}

class PDF extends FPDF
{
	// Cabecera de página
	function Header()
	{
		$this->SetLeftMargin(7);
		// Logo
		$this->Image('imagenes/logo-promosecal.png',14,10,50, 20);
		$this->Image('imagenes/farmacia-logo.png',236,10,50,20);
		// Arial bold 15
		$this->SetFont('Arial','I',11);
		$this->Ln(10);
		// Movernos a la derecha
		// Título
		$this->SetFont('Arial','B',15);
		$this->Cell(277,25,$_SESSION['titulo']." ". ManejadorSolicitud::obtenerNoOficio($_SESSION['idS']),0,0,'C');			
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
	
	function ImprovedTable($header, $data, $total)
	{
		$this->SetLeftMargin(7);
		if(!empty($data))
		{
			// Anchuras de las columnas
			$w = array
			(0 => 52,
			 1 => 15,
			 2 => 40,
			 3 => 90,
			 4 => 13,
			 5 => 15, 
			 6 => 14,
			 7 => 14,
			 8 => 14);
			// Cabeceras
			for($i=0;$i<count($header);$i++)
			{
				$this->Cell($w[$i],10,$header[$i],1,0,'C');
			}
			$this->Ln();
			$this->SetFillColor(220,235,255);
			$this->SetTextColor(0);
			$this->SetFont('Times','B',6);
			// Datos
			foreach($data as $row)
			{
				$columna = explode(";",$row); //separar los datos en posiciones de arreglo 
				if($row == end($data))
				{
					$this->Cell($w[0],8,$columna[0],1,'LR');
					$this->Cell($w[1],8,$columna[1],1,'LR');
					$this->Cell($w[2],8,$columna[2],1,'LR');
					$this->Cell($w[3],8,$columna[3],1,'LR');			
					$this->Cell($w[4],8,$columna[4],1,'LR');
					$this->Cell($w[5],8,$columna[5],1,'LR');
					$this->Cell($w[6],8,$columna[6],1,'LR');
					$this->Cell($w[7],8,$columna[7],1,'LR');
					$this->Cell($w[8],8,$columna[8]." RD$",1,'LR');			
					$this->Ln();
					$this->Cell(253,8,"Total",1,'LR');
					$this->Cell(14,8,$total. " RD$",1,'LR');
					$this->Ln();
				}
				else
				{
					$this->Cell($w[0],8,$columna[0],1,'LR');
					$this->Cell($w[1],8,$columna[1],1,'LR');
					$this->Cell($w[2],8,$columna[2],1,'LR');
					$this->Cell($w[3],8,$columna[3],1,'LR');			
					$this->Cell($w[4],8,$columna[4],1,'LR');
					$this->Cell($w[5],8,$columna[5],1,'LR');
					$this->Cell($w[6],8,$columna[6],1,'LR');
					$this->Cell($w[7],8,$columna[7],1,'LR');
					$this->Cell($w[8],8,$columna[8]." RD$",1,'LR');					
					$this->Ln();			
				}				
			}
			// Línea de cierre
			$this->Cell(array_sum($w),0,'','T');			
		}
	}
	
	function Firmas()
	{
		$this->AddPage();
		$this->SetFont('Times','B',16);
		$this->Cell(82,15,"    ESTATUS DE LA ACTIVIDAD", 0);
		$this->Ln();
		$this->SetLeftMargin(15);
		$this->SetFont('Times','B',13);
		$this->Cell(5,6," ",1);
		$this->Cell(40,10,"  COMPLETADA", 0);
		$this->Cell(5,6,"  ",1);
		$this->Cell(107,10,"    INCOMPLETA (indicar brevemente porque)", 0);
		$this->Ln(10);
		$this->Cell(50,10,"____________________________________________________________________________________________________________________", 0,0, 'L');
		$this->Ln(10);
		$this->Cell(50,10,"____________________________________________________________________________________________________________________", 0,0, 'L');
		$this->Ln(15);
		$this->Cell(107,10,"Observaciónes del supervisor:", 0);
		$this->Ln(10);
		$this->Cell(50,10,"____________________________________________________________________________________________________________________", 0,0, 'L');
		$this->Ln(10);
		$this->Cell(50,10,"____________________________________________________________________________________________________________________", 0,0, 'L');
		$this->Ln(53);
		$this->Cell(50,10,"________________________________________", 0,0, 'L');
		$this->Cell(213,10,"       _______________________________________", 0,0,'R');
		$this->Ln();
		$this->Cell(100,10,"                     Firma del Solicitante", 0);
		$this->Cell(155,10,"Firma del Supervisor Inmediato", 0,0,'R');
	}
}
// Creación del objeto de la clase heredada
$header = array('Nombre', 'Cedula','Cargo', 'Departamento','Fecha', 'Entrada', 'Salida', 'Tiempo', 'Pago');
$pdf=new PDF('L', 'mm', 'A4');
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetFont('Times','B',12);
$pdf->ImprovedTable($header, $temp, $total);
$pdf->Firmas();
$pdf->Output();
?>