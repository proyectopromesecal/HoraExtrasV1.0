<?php

include('../../lib/motor.php');
include ('Lib/PHPExcel.php');
include ('Lib/PHPExcel/Writer/Excel2007.php');
ini_set('include_path', ini_get('include_path').'Lib/Classes/');
global $tabla;
$params = array();
$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );

		if(isset($_GET['fi']) && isset($_GET['ff']))
		{
                $query="select a.nombre nombre, a.cedula, c.nombre cargo, d.nombre  departamento, sum(pago) as pago, a.sueldo as sueldo from empleado a 
            inner join horario b on a.id = b.id_empleado and b.fecha BETWEEN '{$_GET['fi']}' AND '{$_GET['ff']}' 
            inner join t_cargo c on a.cargo = c.id
            inner join t_departamento d on a.departamento = d.id
            inner join solicitudes e on a.id = e.id_empleado
            inner join solicitudhe f on e.id_solicitud = f.id and b.fecha = f.fecha
            inner join solicitudes_autorizadas g on f.id = g.id_solicitud and g.tipo = 'HoraExtra' and g.autorizado = 1
            inner join historial_empleado h on a.id = h.id_empleado and b.id = h.id_horario 
            where a.nivel <> 2";

                                               if (!strcmp($_SESSION['tipo'], "Viewer")==0) {
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
		}
		else
		{
						echo "<script>alert('error, espeficique la fecha');</script>";
		}


Class Excel{
	
	private $i;
	
	
	function create($tabla){
		
		$objPHPExcel = new PHPExcel();

		// Set properties
		$objPHPExcel->getProperties()->setCreator("Julio Lizardo");
		$objPHPExcel->getProperties()->setLastModifiedBy("Julio Lizardo");
		$objPHPExcel->getProperties()->setTitle("ReporteHE");
		$objPHPExcel->getProperties()->setSubject("ReporteHE");
		//$objPHPExcel->getProperties()->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.");
		
		
		$objPHPExcel->setActiveSheetIndex(0);
		
		 //indice de los datos en excel
		$i=1;
		
		foreach($tabla as $row){
			
			$columna = explode(";",$row); //separar los datos en posiciones de arreglo 
			
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

				
				
				$objPHPExcel->getActiveSheet()->setCellValue('A'.$i , $columna[0]);
				$objPHPExcel->getActiveSheet()->SetCellValue('B'.$i, $columna[1]);
				$objPHPExcel->getActiveSheet()->getStyle('B'.$i)->getNumberFormat()->setFormatCode('000-0000000-0');
				$objPHPExcel->getActiveSheet()->SetCellValue('C'.$i, $columna[2]);
				$objPHPExcel->getActiveSheet()->SetCellValue('D'.$i, $columna[3]);
				$objPHPExcel->getActiveSheet()->SetCellValue('E'.$i, $columna[4]);
				$objPHPExcel->getActiveSheet()->SetCellValue('F'.$i, $columna[5]);
				$objPHPExcel->getActiveSheet()->SetCellValue('G'.$i, $horaferiada);
				$objPHPExcel->getActiveSheet()->SetCellValue('H'.$i, $horanormal);
				$objPHPExcel->getActiveSheet()->SetCellValue('I'.$i, $pagoferiado);
				$objPHPExcel->getActiveSheet()->SetCellValue('J'.$i, $pagonormal);
				
				$i++;
				/*$this->Cell($w[0],8,$columna[0],1,0,'C');
				$this->Cell($w[1],8,$columna[1],1,0,'C');
				$this->Cell($w[2],8,$columna[2],1,0,'C');	
				$this->Cell($w[3],8,$columna[3],1,0,'C');
				$this->Cell($w[4],8,$columna[4],1,0,'C');
				$this->Cell($w[5],8,$columna[5],1,0,'C');	
				$this->Cell($w[6],8,$horaferiada,1,0,'C');
				$this->Cell($w[7],8,$horanormal,1,0,'C');
				$this->Cell($w[8],8,$pagoferiado,1,0,'C');
				$this->Cell($w[9],8,$pagonormal,1,0,'C');	*/
			

		}
		
		$objPHPExcel->getActiveSheet()->setTitle('Consolidado');
		
		//Crear El Objeto
		
		/*$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
		$objWriter->save('Consolidado.xlsx');*/
		
		$objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
		$objWriter->save(str_replace('.php', '.xlsx', __FILE__));

		
	}
}

$excel = new Excel();
$excel->create($tabla);