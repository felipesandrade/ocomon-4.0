<?php session_start();
 /* Copyright 2020 Flávio Ribeiro

    This file is part of OCOMON.

    OCOMON is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 3 of the License, or
    (at your option) any later version.
    OCOMON is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with Foobar; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

if (!isset($_SESSION['s_logado']) || $_SESSION['s_logado'] == 0) {
    $_SESSION['session_expired'] = 1;
    echo "<script>top.window.location = '../../index.php'</script>";
    exit;
}

require_once __DIR__ . "/" . "../../includes/include_basics_only.php";
require_once __DIR__ . "/" . "../../includes/classes/ConnectPDO.php";

$auth = new AuthNew($_SESSION['s_logado'], $_SESSION['s_nivel'], 2, 2);

use includes\classes\ConnectPDO;
$conn = ConnectPDO::getInstance();


$post = $_POST;

// var_dump($post); exit;


$terms = "";
$criteria = array();
$criterText = "";
$badgeClass = "badge badge-info p-2 mb-1";
$badgeClassEmptySearch = "badge badge-danger p-2 mb-1";


$imgsPath = "../../includes/imgs/";
$config = getConfig($conn);



/* Unidade */
$field_label = TRANS('COL_UNIT');
$post_field_sufix = "unidade";
$sql_column = "inst.inst_cod";
$field_table = "instituicao";
$field_id = "inst_cod";
$field_name = "inst_nome";

if (isset($post['no_empty_' . $post_field_sufix]) && $post['no_empty_' . $post_field_sufix] == 1) {
    $terms .= " AND ( {$sql_column} != '-1' AND {$sql_column} != '0' AND {$sql_column} IS NOT NULL AND {$sql_column} != '') ";
    $criterText = $field_label . ": " . TRANS('SMART_NOT_EMPTY') . "<br />";
    $criteria[] = "<span class='{$badgeClass}'>{$criterText}</span>";

} elseif (isset($post['no_' . $post_field_sufix]) && $post['no_' . $post_field_sufix] == 1) {
    $terms .= " AND ( {$sql_column} = '-1' OR {$sql_column} = '0' OR {$sql_column} IS NULL OR {$sql_column} = '' ) ";
    $criterText = $field_label . ": " . TRANS('SMART_EMPTY') . "<br />";
    $criteria[] = "<span class='{$badgeClass}'>{$criterText}</span>";

} elseif (isset($post[$post_field_sufix]) && !empty($post[$post_field_sufix])) {
    $fieldIn = "";
    foreach ($post[$post_field_sufix] as $field) {
        if (strlen($fieldIn)) $fieldIn .= ",";
        $fieldIn .= $field;
    }
    $terms .= " AND {$sql_column} IN ({$fieldIn}) ";

    $criterText = "";
    $sqlCriter = "SELECT {$field_name} FROM {$field_table} WHERE {$field_id} in ({$fieldIn}) ORDER BY {$field_name}";
    $resCriter = $conn->query($sqlCriter);
    foreach ($resCriter->fetchAll() as $rowCriter) {
        if (strlen($criterText)) $criterText .= ", ";
        $criterText .= $rowCriter[$field_name];
    }
    $criterText = $field_label . ": " . $criterText ."<br />";
    $criteria[] = "<span class='{$badgeClass}'>{$criterText}</span>";
}


/* Etiqueta */
$field_label = TRANS('ASSET_TAG');
$post_field_sufix = "etiqueta";
$sql_column = "c.comp_inv";

if (isset($post['no_empty_' . $post_field_sufix]) && $post['no_empty_' . $post_field_sufix] == 1) {
    $terms .= " AND ( {$sql_column} != '-1' AND {$sql_column} != '0' AND {$sql_column} IS NOT NULL AND {$sql_column} != '' ) ";
    $criterText = $field_label . ": " . TRANS('SMART_NOT_EMPTY') . "<br />";
    $criteria[] = "<span class='{$badgeClass}'>{$criterText}</span>";

} elseif (isset($post['no_' . $post_field_sufix]) && $post['no_' . $post_field_sufix] == 1) {
    $terms .= " AND ( {$sql_column} = '-1' OR {$sql_column} = '0' OR {$sql_column} IS NULL OR {$sql_column} = '' ) ";
    $criterText = $field_label . ": " . TRANS('SMART_EMPTY') . "<br />";
    $criteria[] = "<span class='{$badgeClass}'>{$criterText}</span>";

} elseif (isset($post[$post_field_sufix]) && !empty($post[$post_field_sufix])) {
    
    $tmp = explode(',', $post[$post_field_sufix]);
    // $treatValues = array_map('intval', $tmp);
    $treatValues = array_map('noHtml', $tmp);
    $tagIN = "";
    foreach ($treatValues as $tag) {
        if (strlen($tagIN)) $tagIN .= ", ";
        $tag = trim($tag);
        $tagIN .= "'{$tag}'";
    }
    $terms .= " AND {$sql_column} IN ({$tagIN}) ";
    
    $criterText = $field_label . ": {$tagIN}<br />";
    $criteria[] = "<span class='{$badgeClass}'>{$criterText}</span>";
}


/* Tipo de equipamento */
$field_label = TRANS('COL_TYPE');
$post_field_sufix = "equip_type";
$sql_column = "c.comp_tipo_equip";
$field_table = "tipo_equip";
$field_id = "tipo_cod";
$field_name = "tipo_nome";

if (isset($post['no_empty_' . $post_field_sufix]) && $post['no_empty_' . $post_field_sufix] == 1) {
    $terms .= " AND ( {$sql_column} != '-1' AND {$sql_column} != '0' AND {$sql_column} IS NOT NULL AND {$sql_column} != '') ";
    $criterText = $field_label . ": " . TRANS('SMART_NOT_EMPTY') . "<br />";
    $criteria[] = "<span class='{$badgeClass}'>{$criterText}</span>";

} elseif (isset($post['no_' . $post_field_sufix]) && $post['no_' . $post_field_sufix] == 1) {
    $terms .= " AND ( {$sql_column} = '-1' OR {$sql_column} = '0' OR {$sql_column} IS NULL OR {$sql_column} = '' ) ";
    $criterText = $field_label . ": " . TRANS('SMART_EMPTY') . "<br />";
    $criteria[] = "<span class='{$badgeClass}'>{$criterText}</span>";

} elseif (isset($post[$post_field_sufix]) && !empty($post[$post_field_sufix])) {
    $fieldIn = "";
    foreach ($post[$post_field_sufix] as $field) {
        if (strlen($fieldIn)) $fieldIn .= ",";
        $fieldIn .= $field;
    }
    $terms .= " AND {$sql_column} IN ({$fieldIn}) ";

    $criterText = "";
    $sqlCriter = "SELECT {$field_name} FROM {$field_table} WHERE {$field_id} in ({$fieldIn}) ORDER BY {$field_name}";
    $resCriter = $conn->query($sqlCriter);
    foreach ($resCriter->fetchAll() as $rowCriter) {
        if (strlen($criterText)) $criterText .= ", ";
        $criterText .= $rowCriter[$field_name];
    }
    $criterText = $field_label . ": " . $criterText ."<br />";
    $criteria[] = "<span class='{$badgeClass}'>{$criterText}</span>";
}


/* Fabricante */
$field_label = TRANS('COL_MANUFACTURER');
$post_field_sufix = "manufacturer";
$sql_column = "fab.fab_cod";
$field_table = "fabricantes";
$field_id = "fab_cod";
$field_name = "fab_nome";

if (isset($post['no_empty_' . $post_field_sufix]) && $post['no_empty_' . $post_field_sufix] == 1) {
    $terms .= " AND ( {$sql_column} != '-1' AND {$sql_column} != '0' AND {$sql_column} IS NOT NULL AND {$sql_column} != '') ";
    $criterText = $field_label . ": " . TRANS('SMART_NOT_EMPTY') . "<br />";
    $criteria[] = "<span class='{$badgeClass}'>{$criterText}</span>";

} elseif (isset($post['no_' . $post_field_sufix]) && $post['no_' . $post_field_sufix] == 1) {
    $terms .= " AND ( {$sql_column} = '-1' OR {$sql_column} = '0' OR {$sql_column} IS NULL OR {$sql_column} = '' ) ";
    $criterText = $field_label . ": " . TRANS('SMART_EMPTY') . "<br />";
    $criteria[] = "<span class='{$badgeClass}'>{$criterText}</span>";

} elseif (isset($post[$post_field_sufix]) && !empty($post[$post_field_sufix])) {
    $fieldIn = "";
    foreach ($post[$post_field_sufix] as $field) {
        if (strlen($fieldIn)) $fieldIn .= ",";
        $fieldIn .= $field;
    }
    $terms .= " AND {$sql_column} IN ({$fieldIn}) ";

    $criterText = "";
    $sqlCriter = "SELECT {$field_name} FROM {$field_table} WHERE {$field_id} in ({$fieldIn}) ORDER BY {$field_name}";
    $resCriter = $conn->query($sqlCriter);
    foreach ($resCriter->fetchAll() as $rowCriter) {
        if (strlen($criterText)) $criterText .= ", ";
        $criterText .= $rowCriter[$field_name];
    }
    $criterText = $field_label . ": " . $criterText ."<br />";
    $criteria[] = "<span class='{$badgeClass}'>{$criterText}</span>";
}


/* Modelo */
$field_label = TRANS('COL_MODEL');
$post_field_sufix = "model";
$sql_column = "model.marc_cod";
$field_table = "marcas_comp";
$field_id = "marc_cod";
$field_name = "marc_nome";

if (isset($post['no_empty_' . $post_field_sufix]) && $post['no_empty_' . $post_field_sufix] == 1) {
    $terms .= " AND ( {$sql_column} != '-1' AND {$sql_column} != '0' AND {$sql_column} IS NOT NULL AND {$sql_column} != '') ";
    $criterText = $field_label . ": " . TRANS('SMART_NOT_EMPTY') . "<br />";
    $criteria[] = "<span class='{$badgeClass}'>{$criterText}</span>";

} elseif (isset($post['no_' . $post_field_sufix]) && $post['no_' . $post_field_sufix] == 1) {
    $terms .= " AND ( {$sql_column} = '-1' OR {$sql_column} = '0' OR {$sql_column} IS NULL OR {$sql_column} = '' ) ";
    $criterText = $field_label . ": " . TRANS('SMART_EMPTY') . "<br />";
    $criteria[] = "<span class='{$badgeClass}'>{$criterText}</span>";

} elseif (isset($post[$post_field_sufix]) && !empty($post[$post_field_sufix])) {
    $fieldIn = "";
    foreach ($post[$post_field_sufix] as $field) {
        if (strlen($fieldIn)) $fieldIn .= ",";
        $fieldIn .= $field;
    }
    $terms .= " AND {$sql_column} IN ({$fieldIn}) ";

    $criterText = "";
    $sqlCriter = "SELECT {$field_name} FROM {$field_table} WHERE {$field_id} in ({$fieldIn}) ORDER BY {$field_name}";
    $resCriter = $conn->query($sqlCriter);
    foreach ($resCriter->fetchAll() as $rowCriter) {
        if (strlen($criterText)) $criterText .= ", ";
        $criterText .= $rowCriter[$field_name];
    }
    $criterText = $field_label . ": " . $criterText ."<br />";
    $criteria[] = "<span class='{$badgeClass}'>{$criterText}</span>";
}



/* Serial number */
$field_label = TRANS('SERIAL_NUMBER');
$post_field_sufix = "serial_number";
$sql_column = "c.comp_sn";

if (isset($post['no_empty_' . $post_field_sufix]) && $post['no_empty_' . $post_field_sufix] == 1) {
    $terms .= " AND ( {$sql_column} != '-1' AND {$sql_column} != '0' AND {$sql_column} IS NOT NULL AND {$sql_column} != '' ) ";
    $criterText = $field_label . ": " . TRANS('SMART_NOT_EMPTY') . "<br />";
    $criteria[] = "<span class='{$badgeClass}'>{$criterText}</span>";

} elseif (isset($post['no_' . $post_field_sufix]) && $post['no_' . $post_field_sufix] == 1) {
    $terms .= " AND ( {$sql_column} = '-1' OR {$sql_column} = '0' OR {$sql_column} IS NULL OR {$sql_column} = '' ) ";
    $criterText = $field_label . ": " . TRANS('SMART_EMPTY') . "<br />";
    $criteria[] = "<span class='{$badgeClass}'>{$criterText}</span>";

} elseif (isset($post[$post_field_sufix]) && !empty($post[$post_field_sufix])) {
    
    $tmp = explode(',', $post[$post_field_sufix]);
    // $treatValues = array_map('intval', $tmp);
    $treatValues = array_map('noHtml', $tmp);
    $tagIN = "";
    foreach ($treatValues as $tag) {
        if (strlen($tagIN)) $tagIN .= ", ";
        $tag = trim($tag);
        $tagIN .= "'{$tag}'";
    }
    $terms .= " AND {$sql_column} IN ({$tagIN}) ";
    
    $criterText = $field_label . ": {$tagIN}<br />";
    $criteria[] = "<span class='{$badgeClass}'>{$criterText}</span>";
}




/* Departamento */
$field_label = TRANS('DEPARTMENT');
$post_field_sufix = "departamento";
$sql_column = "c.comp_local";
$field_table = "localizacao";
$field_id = "loc_id";
$field_name = "local";

if (isset($post['no_empty_' . $post_field_sufix]) && $post['no_empty_' . $post_field_sufix] == 1) {
    $terms .= " AND ( {$sql_column} != '-1' AND {$sql_column} != '0' AND {$sql_column} IS NOT NULL AND {$sql_column} != '') ";
    $criterText = $field_label . ": " . TRANS('SMART_NOT_EMPTY') . "<br />";
    $criteria[] = "<span class='{$badgeClass}'>{$criterText}</span>";

} elseif (isset($post['no_' . $post_field_sufix]) && $post['no_' . $post_field_sufix] == 1) {
    $terms .= " AND ( {$sql_column} = '-1' OR {$sql_column} = '0' OR {$sql_column} IS NULL OR {$sql_column} = '' ) ";
    $criterText = $field_label . ": " . TRANS('SMART_EMPTY') . "<br />";
    $criteria[] = "<span class='{$badgeClass}'>{$criterText}</span>";

} elseif (isset($post[$post_field_sufix]) && !empty($post[$post_field_sufix])) {
    $fieldIn = "";
    foreach ($post[$post_field_sufix] as $field) {
        if (strlen($fieldIn)) $fieldIn .= ",";
        $fieldIn .= $field;
    }
    $terms .= " AND {$sql_column} IN ({$fieldIn}) ";

    $criterText = "";
    $sqlCriter = "SELECT {$field_name} FROM {$field_table} WHERE {$field_id} in ({$fieldIn}) ORDER BY {$field_name}";
    $resCriter = $conn->query($sqlCriter);
    foreach ($resCriter->fetchAll() as $rowCriter) {
        if (strlen($criterText)) $criterText .= ", ";
        $criterText .= $rowCriter[$field_name];
    }
    $criterText = $field_label . ": " . $criterText ."<br />";
    $criteria[] = "<span class='{$badgeClass}'>{$criterText}</span>";
}




/* Situação */
$field_label = TRANS('STATE');
$post_field_sufix = "condition";
$sql_column = "c.comp_situac";
$field_table = "situacao";
$field_id = "situac_cod";
$field_name = "situac_nome";

if (isset($post['no_empty_' . $post_field_sufix]) && $post['no_empty_' . $post_field_sufix] == 1) {
    $terms .= " AND ( {$sql_column} != '-1' AND {$sql_column} != '0' AND {$sql_column} IS NOT NULL AND {$sql_column} != '') ";
    $criterText = $field_label . ": " . TRANS('SMART_NOT_EMPTY') . "<br />";
    $criteria[] = "<span class='{$badgeClass}'>{$criterText}</span>";

} elseif (isset($post['no_' . $post_field_sufix]) && $post['no_' . $post_field_sufix] == 1) {
    $terms .= " AND ( {$sql_column} = '-1' OR {$sql_column} = '0' OR {$sql_column} IS NULL OR {$sql_column} = '' ) ";
    $criterText = $field_label . ": " . TRANS('SMART_EMPTY') . "<br />";
    $criteria[] = "<span class='{$badgeClass}'>{$criterText}</span>";

} elseif (isset($post[$post_field_sufix]) && !empty($post[$post_field_sufix])) {
    $fieldIn = "";
    foreach ($post[$post_field_sufix] as $field) {
        if (strlen($fieldIn)) $fieldIn .= ",";
        $fieldIn .= $field;
    }
    $terms .= " AND {$sql_column} IN ({$fieldIn}) ";

    $criterText = "";
    $sqlCriter = "SELECT {$field_name} FROM {$field_table} WHERE {$field_id} in ({$fieldIn}) ORDER BY {$field_name}";
    $resCriter = $conn->query($sqlCriter);
    foreach ($resCriter->fetchAll() as $rowCriter) {
        if (strlen($criterText)) $criterText .= ", ";
        $criterText .= $rowCriter[$field_name];
    }
    $criterText = $field_label . ": " . $criterText ."<br />";
    $criteria[] = "<span class='{$badgeClass}'>{$criterText}</span>";
}

/* Fornecedores */
$field_label = TRANS('COL_VENDOR');
$post_field_sufix = "supplier";
$sql_column = "c.comp_fornecedor";
$field_table = "fornecedores";
$field_id = "forn_cod";
$field_name = "forn_nome";

if (isset($post['no_empty_' . $post_field_sufix]) && $post['no_empty_' . $post_field_sufix] == 1) {
    $terms .= " AND ( {$sql_column} != '-1' AND {$sql_column} != '0' AND {$sql_column} IS NOT NULL AND {$sql_column} != '') ";
    $criterText = $field_label . ": " . TRANS('SMART_NOT_EMPTY') . "<br />";
    $criteria[] = "<span class='{$badgeClass}'>{$criterText}</span>";

} elseif (isset($post['no_' . $post_field_sufix]) && $post['no_' . $post_field_sufix] == 1) {
    $terms .= " AND ( {$sql_column} = '-1' OR {$sql_column} = '0' OR {$sql_column} IS NULL OR {$sql_column} = '' ) ";
    $criterText = $field_label . ": " . TRANS('SMART_EMPTY') . "<br />";
    $criteria[] = "<span class='{$badgeClass}'>{$criterText}</span>";

} elseif (isset($post[$post_field_sufix]) && !empty($post[$post_field_sufix])) {
    $fieldIn = "";
    foreach ($post[$post_field_sufix] as $field) {
        if (strlen($fieldIn)) $fieldIn .= ",";
        $fieldIn .= $field;
    }
    $terms .= " AND {$sql_column} IN ({$fieldIn}) ";

    $criterText = "";
    $sqlCriter = "SELECT {$field_name} FROM {$field_table} WHERE {$field_id} in ({$fieldIn}) ORDER BY {$field_name}";
    $resCriter = $conn->query($sqlCriter);
    foreach ($resCriter->fetchAll() as $rowCriter) {
        if (strlen($criterText)) $criterText .= ", ";
        $criterText .= $rowCriter[$field_name];
    }
    $criterText = $field_label . ": " . $criterText ."<br />";
    $criteria[] = "<span class='{$badgeClass}'>{$criterText}</span>";
}

/* Centro de Custo */
$field_label = TRANS('COST_CENTER');
$post_field_sufix = "cost_center";
$sql_column = "c.comp_ccusto";
$db_name = DB_CCUSTO;
$field_table = TB_CCUSTO;
$field_id = CCUSTO_ID;
$field_name = CCUSTO_DESC;

if (isset($post['no_empty_' . $post_field_sufix]) && $post['no_empty_' . $post_field_sufix] == 1) {
    $terms .= " AND ( {$sql_column} != '-1' AND {$sql_column} != '0' AND {$sql_column} IS NOT NULL AND {$sql_column} != '') ";
    $criterText = $field_label . ": " . TRANS('SMART_NOT_EMPTY') . "<br />";
    $criteria[] = "<span class='{$badgeClass}'>{$criterText}</span>";

} elseif (isset($post['no_' . $post_field_sufix]) && $post['no_' . $post_field_sufix] == 1) {
    $terms .= " AND ( {$sql_column} = '-1' OR {$sql_column} = '0' OR {$sql_column} IS NULL OR {$sql_column} = '' ) ";
    $criterText = $field_label . ": " . TRANS('SMART_EMPTY') . "<br />";
    $criteria[] = "<span class='{$badgeClass}'>{$criterText}</span>";

} elseif (isset($post[$post_field_sufix]) && !empty($post[$post_field_sufix])) {
    $fieldIn = "";
    foreach ($post[$post_field_sufix] as $field) {
        if (strlen($fieldIn)) $fieldIn .= ",";
        $fieldIn .= $field;
    }
    $terms .= " AND {$sql_column} IN ({$fieldIn}) ";

    $criterText = "";
    $sqlCriter = "SELECT {$field_name} FROM {$db_name}.{$field_table} WHERE {$field_id} in ({$fieldIn}) ORDER BY {$field_name}";
    $resCriter = $conn->query($sqlCriter);
    foreach ($resCriter->fetchAll() as $rowCriter) {
        if (strlen($criterText)) $criterText .= ", ";
        $criterText .= $rowCriter[$field_name];
    }
    $criterText = $field_label . ": " . $criterText ."<br />";
    $criteria[] = "<span class='{$badgeClass}'>{$criterText}</span>";
}

/* Nota Fiscal */
$field_label = TRANS('INVOICE_NUMBER');
$post_field_sufix = "invoice_number";
$sql_column = "c.comp_nf";

if (isset($post['no_empty_' . $post_field_sufix]) && $post['no_empty_' . $post_field_sufix] == 1) {
    $terms .= " AND ( {$sql_column} != '-1' AND {$sql_column} != '0' AND {$sql_column} IS NOT NULL AND {$sql_column} != '' ) ";
    $criterText = $field_label . ": " . TRANS('SMART_NOT_EMPTY') . "<br />";
    $criteria[] = "<span class='{$badgeClass}'>{$criterText}</span>";

} elseif (isset($post['no_' . $post_field_sufix]) && $post['no_' . $post_field_sufix] == 1) {
    $terms .= " AND ( {$sql_column} = '-1' OR {$sql_column} = '0' OR {$sql_column} IS NULL OR {$sql_column} = '' ) ";
    $criterText = $field_label . ": " . TRANS('SMART_EMPTY') . "<br />";
    $criteria[] = "<span class='{$badgeClass}'>{$criterText}</span>";

} elseif (isset($post[$post_field_sufix]) && !empty($post[$post_field_sufix])) {
    
    $tmp = explode(',', $post[$post_field_sufix]);
    // $treatValues = array_map('intval', $tmp);
    $treatValues = array_map('noHtml', $tmp);
    $tagIN = "";
    foreach ($treatValues as $tag) {
        if (strlen($tagIN)) $tagIN .= ", ";
        $tag = trim($tag);
        $tagIN .= "'{$tag}'";
    }
    $terms .= " AND {$sql_column} IN ({$tagIN}) ";
    
    $criterText = $field_label . ": {$tagIN}<br />";
    $criteria[] = "<span class='{$badgeClass}'>{$criterText}</span>";
}


/* Assistência */
$field_label = TRANS('ASSISTENCE');
$post_field_sufix = "assistance";
$sql_column = "c.comp_assist";
$field_table = "assistencia";
$field_id = "assist_cod";
$field_name = "assist_desc";

if (isset($post['no_empty_' . $post_field_sufix]) && $post['no_empty_' . $post_field_sufix] == 1) {
    $terms .= " AND ( {$sql_column} != '-1' AND {$sql_column} != '0' AND {$sql_column} IS NOT NULL AND {$sql_column} != '') ";
    $criterText = $field_label . ": " . TRANS('SMART_NOT_EMPTY') . "<br />";
    $criteria[] = "<span class='{$badgeClass}'>{$criterText}</span>";

} elseif (isset($post['no_' . $post_field_sufix]) && $post['no_' . $post_field_sufix] == 1) {
    $terms .= " AND ( {$sql_column} = '-1' OR {$sql_column} = '0' OR {$sql_column} IS NULL OR {$sql_column} = '' ) ";
    $criterText = $field_label . ": " . TRANS('SMART_EMPTY') . "<br />";
    $criteria[] = "<span class='{$badgeClass}'>{$criterText}</span>";

} elseif (isset($post[$post_field_sufix]) && !empty($post[$post_field_sufix])) {
    $fieldIn = "";
    foreach ($post[$post_field_sufix] as $field) {
        if (strlen($fieldIn)) $fieldIn .= ",";
        $fieldIn .= $field;
    }
    $terms .= " AND {$sql_column} IN ({$fieldIn}) ";

    $criterText = "";
    $sqlCriter = "SELECT {$field_name} FROM {$field_table} WHERE {$field_id} in ({$fieldIn}) ORDER BY {$field_name}";
    $resCriter = $conn->query($sqlCriter);
    foreach ($resCriter->fetchAll() as $rowCriter) {
        if (strlen($criterText)) $criterText .= ", ";
        $criterText .= $rowCriter[$field_name];
    }
    $criterText = $field_label . ": " . $criterText ."<br />";
    $criteria[] = "<span class='{$badgeClass}'>{$criterText}</span>";
}


/* Tipo de garantia */
$field_label = TRANS('FIELD_TYPE_WARRANTY');
$post_field_sufix = "warranty_type";
$sql_column = "c.comp_tipo_garant";
$field_table = "tipo_garantia";
$field_id = "tipo_garant_cod";
$field_name = "tipo_garant_nome";

if (isset($post['no_empty_' . $post_field_sufix]) && $post['no_empty_' . $post_field_sufix] == 1) {
    $terms .= " AND ( {$sql_column} != '-1' AND {$sql_column} != '0' AND {$sql_column} IS NOT NULL AND {$sql_column} != '') ";
    $criterText = $field_label . ": " . TRANS('SMART_NOT_EMPTY') . "<br />";
    $criteria[] = "<span class='{$badgeClass}'>{$criterText}</span>";

} elseif (isset($post['no_' . $post_field_sufix]) && $post['no_' . $post_field_sufix] == 1) {
    $terms .= " AND ( {$sql_column} = '-1' OR {$sql_column} = '0' OR {$sql_column} IS NULL OR {$sql_column} = '' ) ";
    $criterText = $field_label . ": " . TRANS('SMART_EMPTY') . "<br />";
    $criteria[] = "<span class='{$badgeClass}'>{$criterText}</span>";

} elseif (isset($post[$post_field_sufix]) && !empty($post[$post_field_sufix])) {
    $fieldIn = "";
    foreach ($post[$post_field_sufix] as $field) {
        if (strlen($fieldIn)) $fieldIn .= ",";
        $fieldIn .= $field;
    }
    $terms .= " AND {$sql_column} IN ({$fieldIn}) ";

    $criterText = "";
    $sqlCriter = "SELECT {$field_name} FROM {$field_table} WHERE {$field_id} in ({$fieldIn}) ORDER BY {$field_name}";
    $resCriter = $conn->query($sqlCriter);
    foreach ($resCriter->fetchAll() as $rowCriter) {
        if (strlen($criterText)) $criterText .= ", ";
        $criterText .= $rowCriter[$field_name];
    }
    $criterText = $field_label . ": " . $criterText ."<br />";
    $criteria[] = "<span class='{$badgeClass}'>{$criterText}</span>";
}

/* Garantia */
$field_label = TRANS('WARRANTY_STATUS');
$post_field_sufix = "warranty_status";


if (isset($post['no_empty_' . $post_field_sufix]) && $post['no_empty_' . $post_field_sufix] == 1) {
    $terms .= " AND (c.comp_data_compra IS NOT NULL AND c.comp_garant_meses IS NOT NULL) ";
    $criterText = $field_label . ": " . TRANS('SMART_NOT_EMPTY') . "<br />";
    $criteria[] = "<span class='{$badgeClass}'>{$criterText}</span>";

} elseif (isset($post['no_' . $post_field_sufix]) && $post['no_' . $post_field_sufix] == 1) {
    $terms .= " AND (c.comp_data_compra IS NULL OR c.comp_garant_meses IS NULL) ";
    $criterText = $field_label . ": " . TRANS('SMART_EMPTY') . "<br />";
    $criteria[] = "<span class='{$badgeClass}'>{$criterText}</span>";

} elseif (isset($post[$post_field_sufix]) && !empty($post[$post_field_sufix])) {
    
    $criterText = "";

    if ($post[$post_field_sufix] == 1) {
        $terms .= " AND (date_add(c.comp_data_compra, INTERVAL tmp.tempo_meses month) >= now()) ";
        $criterText .= TRANS('UNDER_WARRANTY');
    } elseif ($post[$post_field_sufix] == 2) {
        $terms .= " AND (date_add(c.comp_data_compra, INTERVAL tmp.tempo_meses month) < now()) ";
        $criterText .= TRANS('SEL_GUARANTEE_EXPIRED');
    }
    
    $criterText = $field_label . ": " . $criterText ."<br />";
    $criteria[] = "<span class='{$badgeClass}'>{$criterText}</span>";
}


/* Data mínima de aquisição */
$field_label = TRANS('SMART_MIN_PURCHASE_DATE');
$post_field_sufix = "purchase_date_from";
$sql_column = "c.comp_data_compra";

if (isset($post['no_empty_' . $post_field_sufix]) && $post['no_empty_' . $post_field_sufix] == 1) {
    $terms .= " AND ( {$sql_column} IS NOT NULL ) ";
    $criterText = $field_label . ": " . TRANS('SMART_NOT_EMPTY') . "<br />";
    $criteria[] = "<span class='{$badgeClass}'>{$criterText}</span>";

} elseif (isset($post['no_' . $post_field_sufix]) && $post['no_' . $post_field_sufix] == 1) {
    $terms .= " AND ( {$sql_column} IS NULL ) ";
    $criterText = $field_label . ": " . TRANS('SMART_EMPTY') . "<br />";
    $criteria[] = "<span class='{$badgeClass}'>{$criterText}</span>";

} elseif (isset($post[$post_field_sufix]) && !empty($post[$post_field_sufix])) {
    
    $date_from = "";
    $date_from = $post[$post_field_sufix] . " 00:00:00";
    $date_from = dateDB($date_from);
    
    $terms .= "  AND ( {$sql_column} >= '{$date_from}' ) ";
    
    $criterText = $field_label . ": " . $post[$post_field_sufix] . "<br />";
    $criteria[] = "<span class='{$badgeClass}'>{$criterText}</span>";
}


/* Data máxima de aquisição */
$field_label = TRANS('SMART_MAX_PURCHASE_DATE');
$post_field_sufix = "purchase_date_to";
$sql_column = "c.comp_data_compra";

if (isset($post['no_empty_' . $post_field_sufix]) && $post['no_empty_' . $post_field_sufix] == 1) {
    $terms .= " AND ( {$sql_column} IS NOT NULL ) ";
    $criterText = $field_label . ": " . TRANS('SMART_NOT_EMPTY') . "<br />";
    $criteria[] = "<span class='{$badgeClass}'>{$criterText}</span>";

} elseif (isset($post['no_' . $post_field_sufix]) && $post['no_' . $post_field_sufix] == 1) {
    $terms .= " AND ( {$sql_column} IS NULL ) ";
    $criterText = $field_label . ": " . TRANS('SMART_EMPTY') . "<br />";
    $criteria[] = "<span class='{$badgeClass}'>{$criterText}</span>";

} elseif (isset($post[$post_field_sufix]) && !empty($post[$post_field_sufix])) {
    
    $date_from = "";
    $date_from = $post[$post_field_sufix] . " 23:59:59";
    $date_from = dateDB($date_from);
    
    $terms .= "  AND ( {$sql_column} <= '{$date_from}' ) ";
    
    $criterText = $field_label . ": " . $post[$post_field_sufix] . "<br />";
    $criteria[] = "<span class='{$badgeClass}'>{$criterText}</span>";
}





if (empty($terms)) {
    $criterText = TRANS('SMART_WITHOUT_SEARCH_CRITERIA') . "<br />";
    $criteria[] = "<span class='{$badgeClassEmptySearch}'>{$criterText}</span>";
}

// echo $terms;

// $sql = $QRY["ocorrencias_full_ini"] . " WHERE 1 = 1 {$terms} ORDER BY numero";
$sql = $QRY["full_detail_ini"];
$sql .= $terms;
$sql .= $QRY["full_detail_fim"];
$sql .= " ORDER BY instituicao, etiqueta";

// dump($sql);

$sqlResult = $conn->query($sql);
$totalFiltered = $sqlResult->rowCount();

$criterios = "";

?>
    
    <div id="table_info"></div>
    <div id="div_criterios" class="row p-4">
        <div class="col-10">
            <?php
            foreach ($criteria as $badge) {
                $criterios .= $badge . "&nbsp;";
            }
            ?> 
        </div>
        
    </div>
    <div class="display-buttons"></div>

    <div class="double-scroll">
        <table id="table_tickets_queue" class="stripe hover order-column row-border" border="0" cellspacing="0" width="100%">
            <thead>
                <tr class="header">
                    <td class='line etiqueta'><?= TRANS('ASSET_TAG'); ?></td>
                    <td class='line unidade'><?= TRANS('COL_UNIT'); ?></td>
                    <td class='line type_of'><?= TRANS('COL_TYPE'); ?></td>
                    <td class='line manufacturer'><?= TRANS('COL_MANUFACTURER'); ?></td>
                    <td class='line model'><?= TRANS('COL_MODEL'); ?></td>
                    <td class='line serial_number'><?= TRANS('SERIAL_NUMBER'); ?></td>
                    <td class='line department'><?= TRANS('DEPARTMENT'); ?></td>
                    <td class='line state'><?= TRANS('STATE'); ?></td>
                    <td class='line supplier'><?= TRANS('COL_VENDOR'); ?></td>
                    <td class='line cost_center'><?= TRANS('COST_CENTER'); ?></td>
                    <td class='line value'><?= TRANS('FIELD_PRICE'); ?></td>
                    <td class='line invoice_number'><?= TRANS('INVOICE_NUMBER'); ?></td>
                    <td class='line assistance'><?= TRANS('ASSISTENCE'); ?></td>
                    <td class='line waranty_type'><?= TRANS('FIELD_TYPE_WARRANTY'); ?></td>
                    <td class='line waranty_expire'><?= TRANS('WARRANTY_EXPIRE'); ?></td>
                    <td class='line purchase_date'><?= TRANS('PURCHASE_DATE'); ?></td>

                    <td class='line motherboard'><?= TRANS('MOTHERBOARD'); ?></td>
                    <td class='line processor'><?= TRANS('PROCESSOR'); ?></td>
                    <td class='line memory'><?= TRANS('CARD_MEMORY'); ?></td>
                    <td class='line hdd'><?= TRANS('MNL_HD'); ?></td>
                    <td class='line network_card'><?= TRANS('CARD_NETWORK'); ?></td>
                    <td class='line modem_card'><?= TRANS('CARD_MODEN'); ?></td>
                    <td class='line video_card'><?= TRANS('CARD_VIDEO'); ?></td>
                    <td class='line sound_card'><?= TRANS('CARD_SOUND'); ?></td>
                    <td class='line cdrom'><?= TRANS('FIELD_CDROM'); ?></td>
                    <td class='line recorder'><?= TRANS('MNL_GRAV'); ?></td>
                    <td class='line dvdrom'><?= TRANS('DVD'); ?></td>
                </tr>
            </thead>
       
<?php



foreach ($sqlResult->fetchAll() as $row){
    $nestedData = array(); 
    $showRecord = true;
    

    if ($showRecord) {

        $ccusto_array = getCostCenterInfo($conn, $row['ccusto']);
        $cost_center = (!empty($ccusto_array) ? $ccusto_array['ccusto_name'] . " - " . $ccusto_array['ccusto_cod'] : "");

        $mbArray = getPeripheralInfo($conn, $row['tipo_mb']);
        $procArray = getPeripheralInfo($conn, $row['tipo_proc']);
        $memoArray = getPeripheralInfo($conn, $row['tipo_memo']);
        $hddArray = getPeripheralInfo($conn, $row['tipo_hd']);
        $networkArray = getPeripheralInfo($conn, $row['tipo_rede']);
        $modemArray = getPeripheralInfo($conn, $row['tipo_modem']);
        $videoArray = getPeripheralInfo($conn, $row['tipo_video']);
        $soundArray = getPeripheralInfo($conn, $row['tipo_som']);
        $cdromArray = getPeripheralInfo($conn, $row['tipo_cdrom']);
        $dvdromArray = getPeripheralInfo($conn, $row['tipo_dvd']);
        $recorderArray = getPeripheralInfo($conn, $row['tipo_grav']);
        
        $motherboard = $mbArray['mdit_fabricante']. " " . $mbArray['mdit_desc'] . " " . $mbArray['mdit_desc_capacidade'] . " " . $mbArray['mdit_sufixo'];
        $processor = $procArray['mdit_fabricante']. " " . $procArray['mdit_desc'] . " " . $procArray['mdit_desc_capacidade'] . " " . $procArray['mdit_sufixo'];
        $memory = $memoArray['mdit_fabricante']. " " . $memoArray['mdit_desc'] . " " . $memoArray['mdit_desc_capacidade'] . " " . $memoArray['mdit_sufixo'];
        $hdd = $hddArray['mdit_fabricante']. " " . $hddArray['mdit_desc'] . " " . $hddArray['mdit_desc_capacidade'] . " " . $hddArray['mdit_sufixo'];
        $network = $networkArray['mdit_fabricante']. " " . $networkArray['mdit_desc'] . " " . $networkArray['mdit_desc_capacidade'] . " " . $networkArray['mdit_sufixo'];
        $modem = $modemArray['mdit_fabricante']. " " . $modemArray['mdit_desc'] . " " . $modemArray['mdit_desc_capacidade'] . " " . $modemArray['mdit_sufixo'];
        $video = $videoArray['mdit_fabricante']. " " . $videoArray['mdit_desc'] . " " . $videoArray['mdit_desc_capacidade'] . " " . $videoArray['mdit_sufixo'];
        $sound = $soundArray['mdit_fabricante']. " " . $soundArray['mdit_desc'] . " " . $soundArray['mdit_desc_capacidade'] . " " . $soundArray['mdit_sufixo'];
        $cdrom = $cdromArray['mdit_fabricante']. " " . $cdromArray['mdit_desc'] . " " . $cdromArray['mdit_desc_capacidade'] . " " . $cdromArray['mdit_sufixo'];
        $dvdrom = $dvdromArray['mdit_fabricante']. " " . $dvdromArray['mdit_desc'] . " " . $dvdromArray['mdit_desc_capacidade'] . " " . $dvdromArray['mdit_sufixo'];
        $recorder = $recorderArray['mdit_fabricante']. " " . $recorderArray['mdit_desc'] . " " . $recorderArray['mdit_desc_capacidade'] . " " . $recorderArray['mdit_sufixo'];

        /* Se for uma situação operacional marcada para destaque */
        if ($row['situac_destaque'] == '1') {
            $classHighlight = 'destaque';
        } else {
            $classHighlight = '';
        }

        ?>
        <tr>
            <td class="line <?= $classHighlight; ?>" data-sort="<?= $row['etiqueta']; ?>"><span class="pointer" onClick="openEquipmentInfo('<?= $row['etiqueta']; ?>', <?= $row['cod_inst']; ?>)"><?= "<b>" . $row['etiqueta'] . "</b>"; ?></span></td>
            <td class="line <?= $classHighlight; ?>"><?= $row['instituicao'] ; ?></td>
            <td class="line <?= $classHighlight; ?>"><?= $row['equipamento'] ; ?></td>
            <td class="line <?= $classHighlight; ?>"><?= $row['fab_nome'] ; ?></td>
            <td class="line <?= $classHighlight; ?>"><?= $row['modelo'] ; ?></td>
            <td class="line <?= $classHighlight; ?>"><?= $row['serial'] ; ?></td>
            <td class="line <?= $classHighlight; ?>"><?= $row['local'] ; ?></td>
            <td class="line <?= $classHighlight; ?>"><?= $row['situac_nome'] ; ?></td>
            <td class="line <?= $classHighlight; ?>"><?= $row['fornecedor_nome'] ; ?></td>
            <td class="line <?= $classHighlight; ?>"><?= $cost_center ; ?></td>
            <td class="line <?= $classHighlight; ?>"><?= priceScreen($row['valor']) ; ?></td>
            <td class="line <?= $classHighlight; ?>"><?= $row['nota'] ; ?></td>
            <td class="line <?= $classHighlight; ?>"><?= $row['assistencia'] ; ?></td>
            <td class="line <?= $classHighlight; ?>"><?= $row['tipo_garantia'] ; ?></td>
            <td class="line <?= $classHighlight; ?>" data-sort="<?= $row['vencimento']; ?>"><?= dateScreen($row['vencimento'], 1) ; ?></td>
            <td class="line <?= $classHighlight; ?>" data-sort="<?= $row['data_compra']; ?>"><?= dateScreen($row['data_compra'], 1) ; ?></td>
            
            <td class="line <?= $classHighlight; ?>"><?= $motherboard ; ?></td>
            <td class="line <?= $classHighlight; ?>"><?= $processor ; ?></td>
            <td class="line <?= $classHighlight; ?>"><?= $memory ; ?></td>
            <td class="line <?= $classHighlight; ?>"><?= $hdd ; ?></td>
            <td class="line <?= $classHighlight; ?>"><?= $network ; ?></td>
            <td class="line <?= $classHighlight; ?>"><?= $modem ; ?></td>
            <td class="line <?= $classHighlight; ?>"><?= $video ; ?></td>
            <td class="line <?= $classHighlight; ?>"><?= $sound ; ?></td>
            <td class="line <?= $classHighlight; ?>"><?= $cdrom ; ?></td>
            <td class="line <?= $classHighlight; ?>"><?= $recorder ; ?></td>
            <td class="line <?= $classHighlight; ?>"><?= $dvdrom ; ?></td>
        </tr>
        <?php
    } else {
        $totalFiltered--;
    }
}
?>
        </table>
        <div class="d-none" id="table_info_hidden">
            <div class="row"> <!-- d-none -->
                <div class="col-12"><?= TRANS('WERE_FOUND'); ?> <span class="bold"><?= $totalFiltered; ?></span> <?= TRANS('POSSIBLE_RECORDS_ACORDING_TO_FOLLOW'); ?> <span class="bold"><?= TRANS('SMART_SEARCH_CRITERIA'); ?>:</span></div>
            </div>
            <div class="row p-2 mt-2" id="divCriterios">
                <div class="col-10">
                    <?= $criterios; ?>
                </div>
            </div>

        </div>

    </div>
