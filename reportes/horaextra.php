<?php
require('../fpdf/fpdf.php');
include('../lib/motor.php');
global $tabla;
$params = array();
$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );

	if(isset($_GET['fi']) && isset($_GET['ff']))
	{
		$query="select a.nombre nombre, a.cedula, c.nombre cargo, d.nombre  departamento,REPLACE(CONVERT(VARCHAR,CONVERT(MONEY,sum(pago)),1), '.00','') as pago, REPLACE(CONVERT(VARCHAR,CONVERT(MONEY,a.sueldo),1), '.00','') sueldo from empleado a 
                inner join horario b on a.id = b.id_empleado and b.fecha BETWEEN '{$_GET['fi']}' AND '{$_GET['ff']}' 
                inner join t_cargo c on a.cargo = c.id
                inner join t_departamento d on a.departamento = d.id
                inner join solicitudes e on a.id = e.id_empleado
                inner join solicitudhe f on e.id_solicitud = f.id and b.fecha = f.fecha
                inner join solicitudes_autorizadas g on f.id = g.id_solicitud and g.tipo = 'HoraExtra' and g.autorizado = 1
                inner join historial_empleado h on a.id = h.id_empleado and b.id = h.id_horario 
                where a.nivel <> 2";

				if (strcmp($_SESSION['tipo'], "Asistente")==0) {
					$query.=" and f.usr in (
                                SELECT a.usuario
                                FROM [horasextra].[dbo].[usuario] a
                                inner join empleado b on a.empleado =  b.id
                                inner join t_departamento c on b.departamento = c.id
                                where b.departamento in (

                                                select b.id from  usuario c
                                                inner join empleado a on c.empleado = a.id
                                                inner join t_departamento b on a.departamento = b.id or a.departamento = b.subDepId 
                                                where c.id = {$_SESSION['id']}                     
                                ))";
				}
				$query.=" GROUP BY a.nombre,a.cedula,c.nombre,d.nombre, a.sueldo";

		$rs = sqlsrv_query($_SESSION['con'],$query, $params, $options);
		if($rs)
		{
			while($fila=sqlsrv_fetch_array($rs, SQLSRV_FETCH_ASSOC))
			{
				$tabla[]=$fila['nombre'].";".$fila['cedula'].";".$fila['cargo'].";".$fila['departamento'].";".$fila['pago'].";".$fila['sueldo'];
			}
		}
		$query="SELECT REPLACE(CONVERT(VARCHAR,CONVERT(MONEY,sum(pago)),1), '.00','') as totalpago from empleado a
				inner join horario b on a.id = b.id_empleado and b.fecha BETWEEN '{$_GET['fi']}' AND '{$_GET['ff']}' 
                inner join t_cargo c on a.cargo = c.id
                inner join t_departamento d on a.departamento = d.id
                inner join solicitudes e on a.id = e.id_empleado
                inner join solicitudhe f on e.id_solicitud = f.id and b.fecha = f.fecha
                inner join solicitudes_autorizadas g on f.id = g.id_solicitud and g.tipo = 'HoraExtra' and g.autorizado = 1
                inner join historial_empleado h on a.id = h.id_empleado and b.id = h.id_horario 
                where a.nivel <> 2 ";

				if (strcmp($_SESSION['tipo'], "Asistente")==0) {
					$query.=" and f.usr in (
                                SELECT a.usuario
                                FROM [horasextra].[dbo].[usuario] a
                                inner join empleado b on a.empleado =  b.id
                                inner join t_departamento c on b.departamento = c.id
                                where b.departamento in (

                                                select b.id from  usuario c
                                                inner join empleado a on c.empleado = a.id
                                                inner join t_departamento b on a.departamento = b.id or a.departamento = b.subDepId 
                                                where c.id = {$_SESSION['id']}))";}
		$rs = sqlsrv_query($_SESSION['con'],$query, $params, $options);
		if($rs)
		{
			$fila=sqlsrv_fetch_array($rs, SQLSRV_FETCH_ASSOC);
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
		$this->setLeftMargin(10);
		// Logo
		$this->Image('../imagenes/logo-promosecal.png',15,10,50, 20);
		$this->Image('../imagenes/farmacia-logo.png',290,10,50,20);
		// Arial bold 15
		$this->SetFont('Arial','B',14);
		$this->Ln(10);
		// Movernos a la derecha
		// Título
		$this->Cell(327, 36, "Reporte de trabajo en horas extraordinarias",0,0, 'C');
		$this->Ln(10);
		$this->SetFont('Arial','',14);
		$this->Cell(325 , 36,"Reportar: DESDE: {$_GET['fi']} HASTA: {$_GET['ff']}",0,0,'C');
		// Salto de línea
		$this->Ln(30);
	}
	
	// Pie de página
	function Footer()
	{
		$this->setLeftMargin(75);
		$this->SetY(-45);

		$this->SetFont('Arial','B',9);
		$this->Cell(25,7, "",1,0);
		$this->Cell(60,7, "Preparado por:",1,0,'C');
		$this->Cell(60,7, "Aprobado por:",1,0,'C');
		$this->Cell(60,7, "Revisado por:",1,0,'C');
		$this->Ln();
		
		$this->Cell(25,7, "Firma",1,0);
		$this->SetFont('Arial','',9);
		$this->Cell(60,7, "",1,0,'C');
		$this->Cell(60,7, "",1,0,'C');
		$this->Cell(60,7, "",1,0,'C');
		$this->Ln();
		
		$this->SetFont('Arial','B',9);
		$this->Cell(25,7, "Nombre",1,0);
		$this->Cell(60,7, Manejador::obtenerNombreCompleto($_SESSION['id']),1,0,'C');
		$this->Cell(60,7, Manejador::obtenerEncargado(Manejador::obtenerIdDpto($_SESSION['dpto'])),1,0,'C');
		$this->Cell(60,7, "",1,0,'C');
		$this->Ln();
		
		$this->SetFont('Arial','B',9);
		$this->Cell(25,7, "Rol",1,0);
		$this->Cell(60,7, Manejador::obtenerCargo($_SESSION['id']),1,0,'C');
		$this->Cell(60,7, "Encargado",1,0,'C');
		$this->SetFont('Arial','B',7.5);
		$this->Cell(60,7, "DEPARTAMENTO DE RECURSOS HUMANOS",1,0,'C');
		$this->Ln();
		
		$this->SetFont('Arial','B',9);
		$this->Cell(25,7, "Fecha",1,0);
		$this->Cell(60,7, date('Y-m-d H:i:s'),1,0,'C');
		$this->Cell(60,7, "",1,0,'C');
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
		$this->setLeftMargin(10);
		
		$w = array
		(0 => 60,
		 1 => 20,
         2 => 40,
		 3 => 80, 
		 4 => 20,
		 5 => 20,
		 6 => 20,
		 7 => 20,
		 8 => 27,
		 9 => 27);
		 
		$header= array('Nombre', 'Cedula','Cargo', 'Departamento', 'Pago (RD$)','Sueldo', 'Horas Feriadas', 'Horas Normales', 'Monto Feriado (RD$)', 'Monto Normal (RD$)');
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
				$this->Cell(200,6,"Total",'LR');
				$this->Cell(20,6,$columna[0],1,0,'C');
				$this->Cell(20,6,' ',1,0,'C');
				$this->Cell(20,6,' ',1,0,'C');
				$this->Cell(20,6,' ',1,0,'C');
				$this->Cell(27,6,' ',1,0,'C');
				$this->Cell(27,6,' ',1,0,'C');
			}
			else
			{
				$id = Manejador::obtenerIdE($columna[1]);
				$pagonormal;
				$horanormal;
				$pagoferiado;
				$horaferiada;
				$tempN = explode('/*', ManejadorSolicitud::obtenerTotalHoras($id,$_GET['fi'], $_GET['ff'], 0 ));
				$tempF = explode('/*', ManejadorSolicitud::obtenerTotalHoras($id,$_GET['fi'], $_GET['ff'], 1 ));
				$horanormal = $tempN[0];
				$pagonormal = $tempN[1];
				$horaferiada = $tempF[0];
				$pagoferiado = $tempF[1];
				
				$this->Cell($w[0],8,$columna[0],1,0,'C');
				$this->Cell($w[1],8,$columna[1],1,0,'C');
				$this->Cell($w[2],8,$columna[2],1,0,'C');	
				$this->Cell($w[3],8,$columna[3],1,0,'C');
				$this->Cell($w[4],8,$columna[4],1,0,'C');
				$this->Cell($w[5],8,$columna[5],1,0,'C');	
				$this->Cell($w[6],8,$horaferiada,1,0,'C');
				$this->Cell($w[7],8,$horanormal,1,0,'C');
				$this->Cell($w[8],8,$pagoferiado,1,0,'C');
				$this->Cell($w[9],8,$pagonormal,1,0,'C');	
			}
			$this->Ln();
		}
		$this->Cell(array_sum($w),0,'','T');
	}
}
// Creación del objeto de la clase heredada
$pdf=new PDF('L', 'mm', 'LEGAL');
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->setAutoPageBreak(true,50);
$pdf->SetFont('Times','B',8);
$pdf->Body($tabla);
$pdf->Output();
?>