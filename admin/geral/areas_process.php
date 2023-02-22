<?php session_start();
/*      Copyright 2020 Flávio Ribeiro

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

use includes\classes\ConnectPDO;

$conn = ConnectPDO::getInstance();

$post = $_POST;

$screenNotification = "";
$exception = "";
$data = [];
$data['success'] = true;
$data['message'] = "";
$data['cod'] = (isset($post['cod']) ? intval($post['cod']) : "");
$data['action'] = $post['action'];
$data['field_id'] = "";
$data['system_areas'] = [1,2];

$data['area'] = (isset($post['area']) ? noHtml($post['area']) : "");
$data['process_tickets'] = (isset($post['process_tickets']) ? ($post['process_tickets'] == "yes" ? 1 : 0) : 0);
$data['email'] = (isset($post['email']) ? noHtml($post['email']) : "");
$data['screen_profile'] = (isset($post['screen_profile']) ? noHtml($post['screen_profile']) : "");
$data['area_active'] = (isset($post['area_active']) ? ($post['area_active'] == "yes" ? 1 : 0) : 0);
$data['wt_profile'] = (isset($post['wt_profile']) ? noHtml($post['wt_profile']) : "");
$data['mod_tickets'] = (isset($post['mod_tickets']) ? ($post['mod_tickets'] == "yes" ? 1 : 0) : 0);
$data['mod_inventory'] = (isset($post['mod_inventory']) ? ($post['mod_inventory'] == "yes" ? 1 : 0) : 0);
$data['area_admins'] = (isset($post['area_admins']) ? noHtml($post['area_admins']) : "");

$data['months'] = (isset($post['months']) && $post['months'] > 0 ? (int)$post['months'] : 12);

$modules = [];
if ($data['mod_tickets'] == 1) $modules[] = 1; /* ocorrencias */
if ($data['mod_inventory'] == 1) $modules[] = 2; /* inventário */




/* Validações */
if ($data['action'] == "new" || $data['action'] == "edit") {

    if (empty($data['area']) || empty($data['email'])) {
        $data['success'] = false; 
        $data['field_id'] = (empty($data['area']) ? 'area' : 'email');
        $data['message'] = message('warning', 'Ooops!', TRANS('MSG_EMPTY_DATA'),'');
        echo json_encode($data);
        return false;
    }

    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $data['success'] = false; 
        $data['field_id'] = "email";
        $data['message'] = message('warning', '', TRANS('WRONG_FORMATTED_URL'), '');
        echo json_encode($data);
        return false;
    }
}

if ($data['action'] == 'new') {


    $sql = "SELECT sis_id FROM sistemas WHERE sistema = '" . $data['area'] . "' ";
    $res = $conn->query($sql);
    if ($res->rowCount()) {
        $data['success'] = false; 
        $data['field_id'] = "area";
        $data['message'] = message('warning', '', TRANS('MSG_RECORD_EXISTS'), '');
        echo json_encode($data);
        return false;
    }


    if (!csrf_verify($post)) {
        $data['success'] = false; 
        $data['message'] = message('warning', 'Ooops!', TRANS('FORM_ALREADY_SENT'),'');
    
        echo json_encode($data);
        return false;
    }

    $sql = "INSERT INTO 
                sistemas 
                (
                    sistema, 
                    sis_status, 
                    sis_email, 
                    sis_atende, 
                    sis_screen, 
                    sis_wt_profile, 
                    sis_months_done 
                ) 
                VALUES 
                (
                    '" . $data['area'] . "', 
                    '" . $data['area_active'] . "', 
                    '" . $data['email'] . "', 
                    '" . $data['process_tickets'] . "', 
                    " . dbField($data['screen_profile']) . ", 
                    '" . $data['wt_profile'] . "',
                    '" . $data['months'] . "'
                )";


    try {
        $conn->exec($sql);
        $areaId = $conn->lastInsertId();
        $data['success'] = true; 
        $data['message'] = TRANS('MSG_SUCCESS_INSERT');

        /* VER */
        // $sql = "UPDATE configusercall SET conf_ownarea_2 = CONCAT(conf_ownarea_2,'".$newArea."') ";
        /* Além disso: A área deve poder abrir chamado para ela mesma (se for do tipo que presta atendimento) */

        /* Módulos de acesso */
        foreach ($modules as $mod) {
            $sql = "INSERT INTO permissoes 
                (
                    perm_area, 
                    perm_modulo
                )
                VALUES 
                (
                    {$areaId},
                    {$mod}
                )
                ";
            try {
                $conn->exec($sql);
            }
            catch (Exception $e) {
                $exception .= "<hr>" . $e->getMessage() . "<hr>" . $sql;
            }
        }

        /* Se for do tipo que presta atendimento, deve poder abrir chamado para ela mesma */
        if ($data['process_tickets'] == 1) {
            $sql = "INSERT INTO areaxarea_abrechamado 
                        (
                            area, area_abrechamado
                        )
                        VALUES
                        (
                            {$areaId}, {$areaId}
                        )
            ";
            try {
                $conn->exec($sql);
            }
            catch (Exception $e) {
                $exception .= "<hr>" . $e->getMessage() . "<hr>" . $sql;
            }
        }


        /* atualizar as configurações de áreas que podem enviar chamados para a área recém criada */
        if (isset($post['areaFrom'])) {
            foreach ($post['areaFrom'] as $key => $value) {
                if ($value == 'yes') {
                    $sql = "INSERT INTO areaxarea_abrechamado 
                            (
                                area, area_abrechamado
                            )
                            VALUES
                            (
                                {$areaId}, {$key}
                            )
                    ";
                    try {
                        $conn->exec($sql);
                    }
                    catch (Exception $e) {
                        $exception .= "<hr>" . $e->getMessage() . "<hr>" . $sql;
                    }
                }
            }
        }

        /* atualizar as configurações de áreas que podem receber chamados da área recém criada */
        if (isset($post['areaTo'])) {
            foreach ($post['areaTo'] as $key => $value) {
                if ($value == 'yes') {
                    $sql = "INSERT INTO areaxarea_abrechamado 
                            (
                                area, area_abrechamado
                            )
                            VALUES
                            (
                                {$key}, {$areaId}
                            )
                    ";
                    try {
                        $conn->exec($sql);
                    }
                    catch (Exception $e) {
                        $exception .= "<hr>" . $e->getMessage() . "<hr>" . $sql;
                    }
                }
            }
        }


        $_SESSION['flash'] = message('success', '', $data['message'] . $exception, '');
        echo json_encode($data);
        return false;
    } catch (Exception $e) {
        $exception .= "<hr>" . $e->getMessage() . "<hr>" . $sql;
        $data['success'] = false; 
        $data['message'] = TRANS('MSG_ERR_SAVE_RECORD');
        $_SESSION['flash'] = message('danger', '', $data['message'] . $exception, '');
        echo json_encode($data);
        return false;
    }

} elseif ($data['action'] == 'edit') {


    $sql = "SELECT sis_id FROM sistemas WHERE sistema = '" . $data['area'] . "' AND sis_id <> '" . $data['cod'] . "' ";
    $res = $conn->query($sql);
    if ($res->rowCount()) {
        $data['success'] = false; 
        $data['field_id'] = "area";
        $data['message'] = message('warning', '', TRANS('MSG_RECORD_EXISTS'), '');
        echo json_encode($data);
        return false;
    }


    if (!csrf_verify($post)) {
        $data['success'] = false; 
        $data['message'] = message('warning', 'Ooops!', TRANS('FORM_ALREADY_SENT'),'');
    
        echo json_encode($data);
        return false;
    }

    $sql = "UPDATE sistemas SET 
                sistema = '" . $data['area'] . "', 
                sis_status = " . $data['area_active'] . ", 
                sis_email = '" . $data['email'] . "', 
                sis_screen = " . dbField($data['screen_profile']) . ",  
                sis_atende = '" . $data['process_tickets'] . "', 
                sis_wt_profile = '" . $data['wt_profile'] . "', 
                sis_months_done = '" . $data['months'] . "' 
            WHERE sis_id = '" . $data['cod'] . "'";


    try {
        $conn->exec($sql);
        $data['success'] = true; 
        $data['message'] = TRANS('MSG_SUCCESS_EDIT');

        $sql = "DELETE FROM permissoes WHERE perm_area = " . $data['cod'] . " ";
        try {
            $conn->exec($sql);

            /* Módulos de acesso */
            foreach ($modules as $mod) {
                $sql = "INSERT INTO permissoes 
                    (
                        perm_area, 
                        perm_modulo
                    )
                    VALUES 
                    (
                        " . $data['cod'] . ", 
                        {$mod}
                    )
                    ";
                try {
                    $conn->exec($sql);
                }
                catch (Exception $e) {
                    $exception .= "<hr>" . $e->getMessage() . "<hr>" . $sql;
                }
            }
        }
        catch (Exception $e) {
            $exception .= "<hr>" . $e->getMessage() . "<hr>" . $sql;
        }

        /* Remove todas as configurações sobre que áreas podem abrir ou receber chamados da área editada */
        $sql = "DELETE FROM 
                    areaxarea_abrechamado 
                WHERE 
                    area = '" . $data['cod'] . "' 
                OR 
                    area_abrechamado = '" . $data['cod'] . "'";
        try {
            $conn->exec($sql);
        
            $duplicateKey = false;

            /* atualizar as configurações de áreas que podem enviar chamados para a área editada */
            if (isset($post['areaFrom']) && $data['process_tickets'] == 1) {
                foreach ($post['areaFrom'] as $key => $value) {
                    if ($value == 'yes') {
                        if ($key == $data['cod']) $duplicateKey = true;
                        $sql = "INSERT INTO areaxarea_abrechamado 
                                (
                                    area, area_abrechamado
                                )
                                VALUES
                                (
                                    " . $data['cod'] . ", {$key}
                                )
                        ";
                        try {
                            $conn->exec($sql);
                        }
                        catch (Exception $e) {
                            $exception .= "<hr>" . $e->getMessage() . "<hr>" . $sql;
                        }
                    }
                }
            }

            /* atualizar as configurações de áreas que podem receber chamados da área editada */
            if (isset($post['areaTo'])) {
                foreach ($post['areaTo'] as $key => $value) {
                    if ($value == 'yes' && ($key != $data['cod'] || !$duplicateKey)) {
                        $sql = "INSERT INTO areaxarea_abrechamado 
                                (
                                    area, area_abrechamado
                                )
                                VALUES
                                (
                                    {$key}, " . $data['cod'] . "
                                )
                        ";
                        try {
                            $conn->exec($sql);
                        }
                        catch (Exception $e) {
                            $exception .= "<hr>" . $e->getMessage() . "<hr>" . $sql;
                        }
                    }
                }
            }            
        
        
        
        } catch (Exception $e) {
            $exception .= "<hr>" . $e->getMessage() . "<hr>" . $sql;
        }


        /* Admins da área */
        $tmp_values = [];
        $users_admins = [];
        if (!empty($data['area_admins'])) {
            
            /* Desmarca todos os users admins até então */
            $sql = "UPDATE usuarios SET user_admin = 0 WHERE AREA = '" . $data['cod'] . "'";
            try {
                $conn->exec($sql);

                /* Atualizando os usuários definidos no formulário de atualização */
                $tmp_values = explode(",", $data['area_admins']);
                $users_admins = array_map('intval', $tmp_values);
    
                foreach ($users_admins as $user) {
                    $sql = "UPDATE usuarios SET user_admin = 1 WHERE user_id = :user_id";
                    try {
                        $res = $conn->prepare($sql);
                        $res->bindParam(':user_id', $user, PDO::PARAM_INT);
                        $res->execute();
                    }
                    catch (Exception $e) {
                        $exception .= "<hr>" . $e->getMessage();
                    }
                }
            }
            catch (Exception $e) {
                $exception .= "<hr>" . $e->getMessage();
            }
            
        } else {
            /* Desmarca todos os users admins até então */
            $sql = "UPDATE usuarios SET user_admin = 0 WHERE AREA = '" . $data['cod'] . "'";
            try {
                $conn->exec($sql);
            }
            catch (Exception $e) {
                $exception .= "<hr>" . $e->getMessage();
            }
        }






        $_SESSION['flash'] = message('success', '', $data['message'] . $exception, '');
        echo json_encode($data);
        return false;
    } catch (Exception $e) {
        $exception .= "<hr>" . $e->getMessage() . "<hr>" . $sql;
        $data['success'] = false; 
        $data['message'] = TRANS('MSG_ERR_DATA_UPDATE');
        $_SESSION['flash'] = message('danger', '', $data['message'] . $exception, '');
        echo json_encode($data);
        return false;
    }

} elseif ($data['action'] == 'delete') {


    $config = getConfig($conn);
    $configAreaByEmail = getConfigValue($conn, 'API_TICKET_BY_MAIL_AREA');
    $configAreaLdapNewUsers = getConfigValue($conn, 'LDAP_AREA_TO_BIND_NEWUSERS');

    /* Confere se não está configurada para receber os alertas de garantia */
    if ($config['conf_wrty_area'] == $data['cod']) {
        $data['success'] = false; 
        $data['message'] = TRANS('MSG_CANT_DEL');
        $_SESSION['flash'] = message('danger', '', $data['message'] . $exception, '');
        echo json_encode($data);
        return false;
    }

    /* Confere se não está configurada para receber chamados abertos por e-mail*/
    if ($configAreaByEmail == $data['cod']) {
        $data['success'] = false; 
        $data['message'] = TRANS('MSG_CANT_DEL');
        $_SESSION['flash'] = message('danger', '', $data['message'] . $exception, '');
        echo json_encode($data);
        return false;
    }

    /* Confere se não está configurada para receber novos usuários autenticados via LDAP*/
    if ($configAreaLdapNewUsers == $data['cod']) {
        $data['success'] = false; 
        $data['message'] = TRANS('MSG_CANT_DEL');
        $_SESSION['flash'] = message('danger', '', $data['message'] . $exception, '');
        echo json_encode($data);
        return false;
    }

    /* Confere se não é área padrão do sistema */
    if (in_array($data['cod'], $data['system_areas'])) {
        $data['success'] = false; 
        $data['message'] = TRANS('MSG_CANT_DEL_SYSTEM_REGISTER');
        $_SESSION['flash'] = message('danger', '', $data['message'] . $exception, '');
        echo json_encode($data);
        return false;
    }

    /* Confere se não está configurada para recebimento de chamados de perfis */
    $sql = "SELECT conf_cod FROM configusercall 
            WHERE 
                conf_opentoarea  = '" . $data['cod'] . "'";
    try {
        $res = $conn->query($sql);
        if ($res->rowCount()) {
            $data['success'] = false; 
            $data['message'] = TRANS('MSG_CANT_DEL');
            $_SESSION['flash'] = message('danger', '', $data['message'] . $exception, '');
            echo json_encode($data);
            return false;
        }
    }
    catch (Exception $e) {
        $exception .= "<hr>" . $e->getMessage();
    }

    /* Confere se não está configurada como padrão para autocadastro */
    $sql = "SELECT conf_ownarea FROM configusercall 
            WHERE 
                conf_ownarea  = '" . $data['cod'] . "' AND
                conf_cod = 1 ";
    try {
        $res = $conn->query($sql);
        if ($res->rowCount()) {
            $data['success'] = false; 
            $data['message'] = TRANS('MSG_CANT_DEL');
            $_SESSION['flash'] = message('danger', '', $data['message'] . $exception, '');
            echo json_encode($data);
            return false;
        }
    }
    catch (Exception $e) {
        $exception .= "<hr>" . $e->getMessage();
    }

    /* Confere na tabela de usuários se a área está associada */
    $sql = "SELECT user_id FROM usuarios WHERE AREA = '" . $data['cod'] . "' ";
    $res = $conn->query($sql);
    if ($res->rowCount()) {
        $data['success'] = false; 
        $data['message'] = TRANS('MSG_CANT_DEL');
        $_SESSION['flash'] = message('danger', '', $data['message'] . $exception, '');
        echo json_encode($data);
        return false;
    }
    /* Confere na tabela de ocorrências se a área está associada */
    $sql = "SELECT numero FROM ocorrencias WHERE sistema = '" . $data['cod'] . "' ";
    $res = $conn->query($sql);
    if ($res->rowCount()) {
        $data['success'] = false; 
        $data['message'] = TRANS('MSG_CANT_DEL');
        $_SESSION['flash'] = message('danger', '', $data['message'] . $exception, '');
        echo json_encode($data);
        return false;
    }
    /* Confere na tabela de areas_x_issues se a área está associada */
    $sql = "SELECT prob_id FROM areas_x_issues WHERE area_id = '" . $data['cod'] . "' ";
    $res = $conn->query($sql);
    if ($res->rowCount()) {
        $data['success'] = false; 
        $data['message'] = TRANS('MSG_CANT_DEL');
        $_SESSION['flash'] = message('danger', '', $data['message'] . $exception, '');
        echo json_encode($data);
        return false;
    }


    /* Sem restrições para excluir a área */
    $sql = "DELETE FROM sistemas WHERE sis_id = '" . $data['cod'] . "'";

    try {
        $conn->exec($sql);
        $data['success'] = true; 
        $data['message'] = TRANS('OK_DEL');

        /* Remove as permissões associadas */
        $sql = "DELETE FROM permissoes WHERE perm_area = '" . $data['cod'] . "'";
        try {
            $conn->exec($sql);
        }
        catch (Exception $e) {
            $exception .= "<hr>" . $e->getMessage() . "<hr>" . $sql;
        }

        $sql = "DELETE FROM 
                    areaxarea_abrechamado 
                WHERE 
                    area = '" . $data['cod'] . "' 
                OR 
                    area_abrechamado = '" . $data['cod'] . "'";
        try {
            $conn->exec($sql);
        }
        catch (Exception $e) {
            $exception .= "<hr>" . $e->getMessage() . "<hr>" . $sql;
        }


        $_SESSION['flash'] = message('success', '', $data['message'] . $exception, '');
        echo json_encode($data);
        return false;
    } catch (Exception $e) {
        $exception .= "<hr>" . $e->getMessage() . "<hr>" . $sql;
        $data['success'] = false; 
        $data['message'] = TRANS('MSG_ERR_DATA_REMOVE');
        $_SESSION['flash'] = message('danger', '', $data['message'] . $exception, '');
        echo json_encode($data);
        return false;
    }
    
}

echo json_encode($data);