<?php
require('../fpdf/fpdf.php');
include('../lib/motor.php');
global $tabla;
global $fecha;
$params = array();
$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );

	if(isset($_GET['fi']) && isset($_GET['ff']))
	{
		$fecha=$_GET['fi'];
		$query="select a.nombre nombre, a.cedula, c.nombre cargo, d.nombre  departamento, REPLACE(CONVERT(VARCHAR,CONVERT(MONEY,a.sueldo),1), '.00','') sueldo from empleado a 
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
				else
				{
					$query.= "AND f.usr = (SELECT usuario from usuario where id = {$_SESSION['id']} ) ";
				}

				$query.=" GROUP BY a.nombre,a.cedula,c.nombre,d.nombre, a.sueldo";

		$rs = sqlsrv_query($_SESSION['con'],$query, $params, $options);
		if($rs)
		{
			while($fila=sqlsrv_fetch_array($rs, SQLSRV_FETCH_ASSOC))
			{
				$tabla[]=$fila['nombre'].";".$fila['cedula'].";".$fila['cargo'].";".$fila['departamento'].";".$fila['sueldo'];
			}
			$tabla[]='';
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
		$this->setLeftMargin(50);
		$this->SetY(-45);

		$this->SetFont('Arial','B',9);
		$this->Cell(25,7, "",1,0);
		$this->Cell(80,7, "Preparado por:",1,0,'C');
		$this->Cell(80,7, "Aprobado por:",1,0,'C');
		$this->Cell(80,7, "Revisado por:",1,0,'C');
		$this->Ln();
		
		$this->Cell(25,7, "Firma",1,0);
		$this->SetFont('Arial','',9);
		$this->Cell(80,7, "",1,0,'C');
		$this->Cell(80,7, "",1,0,'C');
		$this->Cell(80,7, "",1,0,'C');
		$this->Ln();
		
		$this->SetFont('Arial','B',9);
		$this->Cell(25,7, "Nombre",1,0);
		$this->Cell(80,7, Manejador::obtenerNombreCompleto($_SESSION['id']),1,0,'C');
		$this->Cell(80,7, Manejador::obtenerEncargado(Manejador::obtenerIdDpto($_SESSION['dpto'])),1,0,'C');
		$this->Cell(80,7, "",1,0,'C');
		$this->Ln();
		
		$this->SetFont('Arial','B',9);
		$this->Cell(25,7, "Rol",1,0);
		$this->Cell(80,7, Manejador::obtenerCargo($_SESSION['id']),1,0,'C');
		$this->Cell(80,7, "Encargado",1,0,'C');
		$this->SetFont('Arial','B',7.5);
		$this->Cell(80,7, "DEPARTAMENTO DE RECURSOS HUMANOS.",1,0,'C');
		$this->Ln();
		
		$this->SetFont('Arial','B',9);
		$this->Cell(25,7, "Fecha",1,0);
		$this->Cell(80,7, date('Y-m-d H:i:s'),1,0,'C');
		$this->Cell(80,7, "",1,0,'C');
		$this->Cell(80,7, "",1,0,'C');
		$this->Ln();
		$this->setLeftMargin(10);
	}
	
	function Body($tabla, $fecha)
	{
		//tabla		
		$this->SetFillColor(255,0,0);
		$this->SetDrawColor(0);
		$this->SetLineWidth(.3);
		$this->setLeftMargin(10);
		$altoCelda = 10;

		$w = array
		(0 => 65,
		 1 => 25,
         2 => 55,
		 3 => 90, 
		 4 => 17.5,
		 5 => 17.5,
		 6 => 15,
		 7 => 15,
		 8 => 20,
		 9 => 20);
		 
		$header= array('Nombre', 'Cedula','Cargo', 'Departamento', 'Pago (RD$)','Sueldo', 'Horas Feriadas', 'Horas Normales', 'Monto Feriado (RD$)', 'Monto Normal (RD$)');
		for($i=0;$i<count($header);$i++)
		{
			$x=$this->GetX();
			$y=$this->GetY();

			if ($i>5) {
				$altoCelda = 5;
			}

			$this->MultiCell($w[$i],$altoCelda,$header[$i],1,'C');
			$this->SetXY($x+$w[$i],$y);
		}
		$this->Ln(10);
		$this->SetFont('Times','B',6);
		$this->SetFillColor(224,235,255);
		$this->SetTextColor(0);
		$total=0;
		$notacion="";
		$pagonormal;
		$horanormal;
		$pagoferiado;
		$horaferiada;
		// Datos
		foreach($tabla as $row)
		{
			$notacion="";
			$columna = explode(";",$row); //separar los datos en posiciones de arreglo 
			if($row == end($tabla))
			{
				$this->SetFont('Times','B',8);
				$this->Cell(235,6,"Total",'LR');
				$this->Cell(17.5,6,number_format($total,2,'.',','),1,0,'C');
				$this->Cell(17.5,6,' ',1,0,'C');
				$this->Cell(15,6,' ',1,0,'C');
				$this->Cell(15,6,' ',1,0,'C');
				$this->Cell(20,6,' ',1,0,'C');
				$this->Cell(20,6,' ',1,0,'C');
				$this->Ln();
				$this->Cell(340,7, "Los montos marcados con '*' superaron el 30% de su sueldo y se les redujo al 30%.",1,0,'L');
				$this->Ln();
				$this->Cell(340,7, "Los montos marcados con '**' superaron el 30% de su sueldo y se les redujo al 30% entre cada solicitante.",1,0,'L');
			}
			else
			{
				$id = Manejador::obtenerIdE($columna[1]);
				$tempN = explode('/*', ManejadorSolicitud::obtenerTotalHoras($id,$_GET['fi'], $_GET['ff'], 0, $_SESSION['id'] ));
				$tempF = explode('/*', ManejadorSolicitud::obtenerTotalHoras($id,$_GET['fi'], $_GET['ff'], 1, $_SESSION['id']  ));
				$horanormal = $tempN[0];
				$pagonormal = $tempN[1];
				$horaferiada = $tempF[0];
				$pagoferiado = $tempF[1];
				$pagoTotal = (float)str_replace(",", "", $pagonormal) + (float)str_replace(",", "", $pagoferiado); //+ floatval($pagoferiado);
				if ($pagoTotal > ((float)str_replace(",", "", $columna[4])*0.30)) {
					$pagoTotal = (float)str_replace(",", "", $columna[4])*0.30;
					$notacion = '*';
				}
				$total+=$pagoTotal;
				
				$this->Cell($w[0],8,$columna[0],1,0,'C');
				$this->SetFont('Times','B',8);
				$this->Cell($w[1],8,$columna[1],1,0,'C');
				$this->SetFont('Times','B',6);
				$this->Cell($w[2],8,$columna[2],1,0,'C');	
				$this->Cell($w[3],8,$columna[3],1,0,'C');
				$this->SetFont('Times','B',8);

				$this->Cell($w[4],8,number_format($pagoTotal,2,'.',',')." {$notacion}",1,0,'C');
				$this->Cell($w[5],8,$columna[4],1,0,'C');	
				$this->Cell($w[6],8,$horaferiada,1,0,'C');
				$this->Cell($w[7],8,$horanormal,1,0,'C');
				$this->Cell($w[8],8,$pagoferiado,1,0,'C');
				$this->Cell($w[9],8,$pagonormal,1,0,'C');	
				$this->SetFont('Times','B',6);					
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
$pdf->setAutoPageBreak(true,52);
$pdf->SetFont('Times','B',8);
$pdf->Body($tabla, $fecha);
$pdf->Output();

?>