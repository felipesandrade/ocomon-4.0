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

/* Controle para limitar os resultados das consultas às áreas do usuário logado quando a opção estiver habilitada */
// $filter_areas = "";
// $areas_names = "";
// if (isAreasIsolated($conn) && $_SESSION['s_nivel'] != 1) {
//     /* Visibilidade isolada entre áreas para usuários não admin */
//     $u_areas = (!empty($filtered_areas) ? $filtered_areas : $_SESSION['s_uareas']);

//     $filter_areas = " AND " . $aliasAreasFilter . " IN ({$u_areas}) ";

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

$sql = "SELECT s.status as status, count(o.status) AS quantidade 
        FROM status s, ocorrencias o, usuarios ua 
        WHERE 
            s.stat_id = o.status AND s.stat_painel NOT IN (3) 
            AND o.aberto_por = ua.user_id 
            {$qry_filter_areas}
        GROUP BY status ORDER BY quantidade DESC";

$sql = $conn->query($sql);

$data = array();

foreach ($sql->fetchAll(PDO::FETCH_ASSOC) as $row) {
    $data[] = $row;
}
$data[]['chart_title'] = TRANS('TICKETS_BY_STATUS', '', 1);
// IMPORTANT, output to json
echo json_encode($data);

?>
