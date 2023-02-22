<?php
session_start();
require_once (__DIR__ . "/" . "../../includes/include_basics_only.php");
require_once (__DIR__ . "/" . "../../includes/classes/ConnectPDO.php");
use includes\classes\ConnectPDO;

if ($_SESSION['s_logado'] != 1 || ($_SESSION['s_nivel'] != 1 && $_SESSION['s_nivel'] != 2)) {
    exit;
}
$conn = ConnectPDO::getInstance();


$isAdmin = $_SESSION['s_nivel'] == 1;
$aliasAreasFilter = ($_SESSION['requester_areas'] ? "ua.AREA" : "o.sistema");
$filtered_areas = $_SESSION['dash_filter_areas'];
$qry_filter_areas = "";

$u_areas = (!empty($filtered_areas) ? $filtered_areas : $_SESSION['s_uareas']);


// /* Controle para limitar os resultados das consultas às áreas do usuário logado quando a opção estiver habilitada */
// $filter_areas_ids = "";
// $filter_areas = "";
// $areas_names = "";
// if (isAreasIsolated($conn) && $_SESSION['s_nivel'] != 1) {
//     /* Visibilidade isolada entre áreas para usuários não admin */
//     $u_areas = $_SESSION['s_uareas'];
//     $filter_areas = " AND o.sistema IN ({$u_areas}) ";
//     $filter_areas_ids = " AND s.sis_id IN ({$u_areas}) ";

//     $array_areas_names = getUserAreasNames($conn, $u_areas);

//     foreach ($array_areas_names as $area_name) {
//         if (strlen($areas_names))
//             $areas_names .= ", ";
//         $areas_names .= $area_name;
//     }
// }

$allAreasInfo = getAreas($conn, 0, 1, null);
$arrayAllAreas = [];
foreach ($allAreasInfo as $sigleArea) {
    $arrayAllAreas[] = $sigleArea['sis_id'];
}
$allAreas = implode(",", $arrayAllAreas);

if ($isAdmin) {
    $u_areas = (!empty($filtered_areas) ? $filtered_areas : $allAreas);

    if (empty($filtered_areas) && !$_SESSION['requester_areas']) {
        /* Padrão, não precisa filtrar por área - todas as áreas de destino */
        $qry_filter_areas = "";

    } else {
        $qry_filter_areas = " AND " . $aliasAreasFilter . " IN ({$u_areas}) ";
    } 
} else {
    $u_areas = (!empty($filtered_areas) ? $filtered_areas : $_SESSION['s_uareas']);
    $qry_filter_areas = " AND " . $aliasAreasFilter . " IN ({$u_areas}) ";
}


$dates = [];
$datesBegin = [];
$datesEnd = [];
$months = [];
$areas = [];
$data = [];

// Meses anteriores
$dates = getMonthRangesUpToNOw('P3M');
$datesBegin = $dates['ini'];
$datesEnd = $dates['end'];
$months = $dates['mLabel'];

/* PRIMEIRO BUSCO AS AREAS ENVOLVIDAS NA CONSULTA */
// $sql = "SELECT s.sis_id, s.sistema FROM sistemas s WHERE s.sis_atende = 1 {$filter_areas_ids}";

if ($_SESSION['requester_areas']) {
    $sql = "SELECT s.sis_id, s.sistema FROM sistemas s WHERE s.sis_id IN ({$u_areas})";
} else {
    $sql = "SELECT s.sis_id, s.sistema FROM sistemas s WHERE s.sis_atende = 1 AND s.sis_id IN ({$u_areas})";
}


$result = $conn->query($sql);
foreach ($result->fetchAll() as $row) {
    $i = 0;
    foreach ($datesBegin as $dateStart) {
        /* Em cada intervalo de tempo busco os totais de cada área */

        $sqlEach = "SELECT count(*) AS total, s.sistema 
                    FROM ocorrencias o, sistemas s, usuarios ua
                    WHERE 
                        -- s.sis_id = o.sistema 
                        -- AND s.sis_id = " . $row['sis_id'] . " 
                        s.sis_id = " . $aliasAreasFilter . " 
                        AND o.aberto_por = ua.user_id 
                        
                        AND " . $aliasAreasFilter . " = " . $row['sis_id'] . " 
                        AND o.oco_real_open_date >= '" .  $dateStart  . "' 
                        AND o.oco_real_open_date <= '" .  $datesEnd[$i]  . "' 
                    GROUP BY s.sistema
                    ";
        
        $resultEach = $conn->query($sqlEach);
        $countResults = $resultEach->rowCount();

        if ($countResults) {
            foreach ($resultEach->fetchAll() as $rowEach) {

                if ($rowEach['sistema']) {
                    $areas[] = $rowEach['sistema'];
                    // $totais[] = (int)$rowEach['total'];
                    $meses[] = $months[$i];
                    // $areasDados[$rowEach['sistema']][] = intval($rowEach['total']);
                    $areasDados[$rowEach['sistema']][] = $rowEach['total'];
                } else {
                    $areas[] = $row['sistema'];
                    $meses[] = $months[$i];
                    $areasDados[$row['sistema']][] = 0;
                }
            }
        } else {
            $areas[] = $row['sistema'];
            $meses[] = $months[$i];
            $areasDados[$row['sistema']][] = 0;
        }

        $i++;
    }
}



/* Ajusto os arrays de labels para não ter repetidos */
$meses = array_unique($meses);
$areas = array_unique($areas);

/* Separo o conteúdo para organizar o JSON */
$data['areas'] = $areas;
$data['months'] = $meses;
$data['totais'] = $areasDados;
$data['chart_title'] = ($_SESSION['requester_areas'] ? TRANS('TICKETS_BY_REQUESTER_AREA_LAST_MONTHS', '', 1) : TRANS('TICKETS_BY_AREA_LAST_MONTHS', '', 1));

// var_dump($areas, $totais, $meses, $areasDados, $data);

echo json_encode($data);

?>
