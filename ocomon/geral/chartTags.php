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
$areas_names = "";

$u_areas = (!empty($filtered_areas) ? $filtered_areas : $_SESSION['s_uareas']);

$allAreasInfo = getAreas($conn, 0, 1, null);
$arrayAllAreas = [];
foreach ($allAreasInfo as $sigleArea) {
    $arrayAllAreas[] = $sigleArea['sis_id'];
}
$allAreas = implode(",", $arrayAllAreas);

if ($isAdmin) {
    // $u_areas = (!empty($filtered_areas) ? $filtered_areas : $allAreas);
    $u_areas = (!empty($filtered_areas) ? $filtered_areas : null);

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



// if (isAreasIsolated($conn) && $_SESSION['s_nivel'] != 1) {
//     /* Visibilidade isolada entre áreas para usuários não admin */
//     $area = $_SESSION['s_uareas'];
// } else {
//     $area = null;
//     $areas_names .= TRANS('NONE_FILTER');
// }

if ($isAdmin && empty($filtered_areas)) {
    $u_areas = null;
    $areas_names .= TRANS('NONE_FILTER');
} else {
    $array_areas_names = getUserAreasNames($conn, $u_areas);

    foreach ($array_areas_names as $area_name) {
        if (strlen($areas_names))
            $areas_names .= ", ";
        $areas_names .= $area_name;
    }
}


    


$startDate = date("Y-m-01 00:00:00");
$endDate = date("Y-m-d H:i:s");


$data = array();
$none = true;
$data['message_empty'] = "";

foreach (getTagsList($conn) as $tag) {
    $tagCount = getTagCount($conn, $tag['tag_name'], $startDate, $endDate, $u_areas, $_SESSION['requester_areas']);
    if ($tagCount) {
        $none = false;
        $data[] = ['label' => $tag['tag_name'], 'weight' => $tagCount];
    }
}

if ($none) {
    $data['message_empty'] = message('info', '', TRANS('NO_RECORDS_FOUND'), '', '', true);
}
$data['title'] = TRANS('TAGGING_CLOUD_CURRENT_MONTH');
echo json_encode($data);

?>
