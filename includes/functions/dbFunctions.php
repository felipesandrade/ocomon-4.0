<?php
/* 
Copyright 2021 Flávio Ribeiro

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

/**
 * getConfig
 * Retorna o array com as informações de configuração do sistema
 * @param PDO $conn
 * @return array
 */
function getConfig ($conn): array
{
    $sql = "SELECT * FROM config ";
    try {
        $res = $conn->query($sql);
        if ($res->rowCount())
            return $res->fetch();
        return [];
    }
    catch (Exception $e) {
        return [];
    }
}

/**
 * getConfigValue
 * Retorna o valor da chave de configuração informada - Configurações estendidas
 * @param \PDO $conn
 * @param string $key
 * @return null | string
 */
function getConfigValue (\PDO $conn, string $key): ?string
{
    $sql = "SELECT key_value FROM config_keys WHERE key_name = :key_name ";
    try {
        $res = $conn->prepare($sql);
        $res->bindParam(':key_name', $key);
        $res->execute();
        
        if ($res->rowCount()) {
            return $res->fetch()['key_value'];
        }
        return null;
    }
    catch (Exception $e) {
        return null;
    }
}

/**
 * getConfigValues
 * Retorna um array com todas as chaves e valores das Configurações estendidas
 * @param \PDO $conn
 * @return array
 */
function getConfigValues (\PDO $conn): array
{
    $return = [];
    $notReturn = [];
    
    /* Essas chaves não serão retornadas */
    $notReturn[] = 'API_TICKET_BY_MAIL_TOKEN';
    $notReturn[] = 'MAIL_GET_PASSWORD';

    $sql = "SELECT key_name, key_value FROM config_keys ";
    try {
        $res = $conn->prepare($sql);
        $res->execute();
        if ($res->rowCount()) {
            foreach ($res->fetchAll() as $row) {
                if (!in_array($row['key_name'], $notReturn))
                    $return[$row['key_name']] = $row['key_value'];
            }
        }
        return $return;
    }
    catch (Exception $e) {
        return $return;
    }
}


/**
 * saveNewTags
 * Checa se há novas tags em um array informado - se existirem novas tags serão gravadas
 * @param \PDO $conn
 * @param array $tags
 * @return bool
 */
function saveNewTags (\PDO $conn, array $tags): bool
{
    if (!is_array($tags)){
        return false;
    }

    $tags = filter_var_array($tags, FILTER_SANITIZE_STRIPPED);
    
    foreach ($tags as $tag) {
        $sql = "SELECT tag_name FROM input_tags WHERE tag_name = :tag ";
        try {
            $res = $conn->prepare($sql);
            $res->bindParam(':tag', $tag);
            $res->execute();
            if (!$res->rowCount()) {
                $sqlIns = "INSERT INTO input_tags (tag_name) VALUES (:tag)";
                try {
                    $resInsert = $conn->prepare($sqlIns);
                    $resInsert->bindParam(':tag', $tag);
                    $resInsert->execute();
                }
                catch (Exception $e) {
                    return false;
                }
            }
        }
        catch (Exception $e) {
            return false;
        }
    }
    return true;
}


/**
 * getTagsList
 * Retorna a listagem de tags existentes ou uma tag específica na tabela de referência
 * @param \PDO $conn
 * @param int $id
 * @return array
 */
function getTagsList(\PDO $conn, ?int $id = null): array
{
    $data = [];
    $terms = "";
    if ($id) {
        $terms = " WHERE id = :id ";
    }

    $sql = "SELECT id, tag_name FROM input_tags {$terms} ORDER BY tag_name";
    try {
        $res = $conn->prepare($sql);
        if ($id) {
            $res->bindParam(':id', $id, PDO::PARAM_INT);
        }
        $res->execute();
        if ($res->rowCount()) {
            foreach ($res->fetchAll() as $tag) {
                $data[] = $tag;
            }
            if ($id)
                return $data[0];
            return $data;
        }
        return [];

    }
    catch (Exception $e) {
        return [];
    }
}


/**
 * getTagCount
 * Retorna a quantidade de vezes que a tag informada está sendo utilizada nos chamados
 * @param \PDO $conn
 * @param string $tag
 * 
 * @return int
 */
function getTagCount(\PDO $conn, string $tag, ?string $startDate = null, ?string $endDate = null, ?string $area = null, bool $requesterArea = false): int
{

    $terms = "";
    $aliasAreas = ($requesterArea ? "ua.AREA" : "o.sistema");
    
    if ($startDate) {
        $terms .= " AND o.data_abertura >= :startDate ";
    }
    if ($endDate) {
        $terms .= " AND o.data_abertura <= :endDate ";
    }

    if ($area && !empty($area) && $area != -1) {
        // $terms .= " AND o.sistema IN ({$area})";
        $terms .= " AND {$aliasAreas} IN ({$area})";
    }
    
    $sql = "SELECT count(*) total 
            FROM 
                ocorrencias o, sistemas s, usuarios ua  
            WHERE 
                o.sistema = s.sis_id AND o.aberto_por = ua.user_id AND 
                MATCH(oco_tag) AGAINST ('\"$tag\"' IN BOOLEAN MODE) 
                {$terms}    
            ";
    try {
        $res = $conn->prepare($sql);
        // $res->bindParam(':tag', $tag);
        if ($startDate) 
            $res->bindParam(":startDate", $startDate);
        if ($endDate) 
            $res->bindParam(":endDate", $endDate);
        // if ($area && !empty($area) && $area != -1)
        //     $res->bindParam(":area", $area);

        $res->execute();
        if ($res->rowCount()) {
            return $res->fetch()['total'];
        }
        return 0;
    }
    catch (Exception $e) {
        echo $sql . "<hr/>" . $e->getMessage();
        // exit;
        return 0;
    }
}


/**
 * getScreenInfo
 * Retorna o array com as informações do perfil de tela de abertura
 * [conf_cod], [conf_name], [conf_user_opencall - permite autocadastro], [conf_custom_areas], 
 * [conf_ownarea - area para usuários que se autocadastram], [conf_ownarea_2], [conf_opentoarea]
 * [conf_screen_area], []... [conf_screen_msg]
 * @param \PDO $conn
 * @param int $screenId
 * @return array
 */
function getScreenInfo (\PDO $conn, int $screenId): array
{
    $sql = "SELECT 
                *
            FROM 
                configusercall
            WHERE 
                conf_cod = :screenID ";
    try {
        $res = $conn->prepare($sql);
        $res->bindParam(':screenID', $screenId);
        $res->execute();

        if ($res->rowCount())
            return $res->fetch();
        return [];
    }
    catch (Exception $e) {
        return [];
    }
}


/**
 * getScreenProfiles
 * Retorna a listagem de perfis de tela de abertura
 * Indices: conf_cod, conf_name, etc..
 *
 * @param \PDO $conn
 * 
 * @return array
 */
function getScreenProfiles (\PDO $conn): array
{
    $sql = "SELECT * FROM configusercall ORDER BY conf_name";
    try {
        $res = $conn->prepare($sql);
        $res->execute();
        
        if ($res->rowCount()) {
            foreach ($res->fetchAll() as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return [];
    }
    catch (Exception $e) {
        return [];
    }
}


/**
 * getScreenRequiredInfo
 * Retorna um array com os valores de obrigariedade para os campos do perfil de tela de abertura
 * Indices: nome do campo, valor (0|1)
 *
 * @param \PDO $conn
 * @param int $screenId
 * 
 * @return array
 */
function getScreenRequiredInfo (\PDO $conn, int $screenId): array
{
    
    $fields = [];
    
    $sql = "SELECT 
                *
            FROM 
                screen_field_required
            WHERE 
                profile_id = :screenID ";
    try {
        $res = $conn->prepare($sql);
        $res->bindParam(':screenID', $screenId);
        $res->execute();

        if ($res->rowCount()) {
            foreach ($res->fetchAll() as $row) {
                $fields[$row['field_name']] = $row['field_required'];
            }
            return $fields;
        }
        return [];
    }
    catch (Exception $e) {
        return [];
    }
}


/**
 * pass
 * Retorna se a combinação de usuário e senha(ou hash:versão 4x) é válida
 * @param \PDO $conn
 * @param string $user
 * @param string $pass (deve vir com md5)
 * @return bool
 */
function pass(\PDO $conn, string $user, string $pass): bool
{
    $user = filter_var($user, FILTER_SANITIZE_STRIPPED);
    $pass = filter_var($pass, FILTER_SANITIZE_STRIPPED);

    $sql = "SELECT 
                `user_id`, `password`, `hash`
            FROM 
                usuarios 
            WHERE 
                login = :user AND
                nivel < 4
            ";
    try {
        $res = $conn->prepare($sql);
        $res->bindParam(':user', $user);

        $res->execute();
        if ($res->rowCount()) {

            $row = $res->fetch();
            if (!empty($row['hash'])) {
                /* usuário possui hash de senha */
                return password_verify($pass, $row['hash']);
            }

            if ($pass === $row['password'] && !empty($pass)) {
                return true;
            }
            return false;

        }
        return false;
    }
    catch (Exception $e) {
        return false;
    }

    return false;
}


/**
 * Valida usuário e senha quanto a configuração de autenticação for para LDAP
 * É utilizada quando o tipo de autenticação de autenticação configurado em AUTH_TYPE for LDAP
 */
function passLdap (string $username, string $pass, array $ldapConfig): bool
{
    if (empty($username) || empty($pass)) {
        return false;
    }

    $username = filter_var($username, FILTER_SANITIZE_STRIPPED);
    $pass = filter_var($pass, FILTER_SANITIZE_STRIPPED);

    $ldapConn = ldap_connect($ldapConfig['LDAP_HOST'], $ldapConfig['LDAP_PORT']);
    if (!$ldapConn) {
        // echo ldap_error($ldapConn);
        return false;
    }

    ldap_set_option($ldapConn, LDAP_OPT_PROTOCOL_VERSION, 3);

    $username = $username . "@" . $ldapConfig['LDAP_DOMAIN'];

    if (@ldap_bind($ldapConn, $username, $pass)) {
        // echo ldap_error($ldapConn);
        return true;
    }
    return false;
}


function getUserLdapData(string $username, string $pass, array $ldapConfig): array
{
    $ldapConn = ldap_connect($ldapConfig['LDAP_HOST'], $ldapConfig['LDAP_PORT']);
    if (!$ldapConn) {
        return false;
    }
    
    ldap_set_option($ldapConn, LDAP_OPT_PROTOCOL_VERSION, 3);

    $usernameAtDomain = $username . "@" . $ldapConfig['LDAP_DOMAIN'];

    if (@ldap_bind($ldapConn, $usernameAtDomain, $pass)) {
        // echo ldap_error($ldapConn);

        /* (&(objectClass=user)(objectCategory=person)(!(userAccountControl:1.2.840.113556.1.4.803:=2))) */
        $search_filter = "(|(sAMAccountName=" . $username .")(uid=" . $username ."))";
        $results = ldap_search($ldapConn, $ldapConfig['LDAP_BASEDN'], $search_filter);
        
        $datas = ldap_get_entries($ldapConn, $results);

        if (!($datas['count']) || !$datas) {
            return [];
        }

        $data = [];
        $data['username'] = $username;
        $data['password'] = $pass;
        
        $data['LDAP_FIELD_FULLNAME'] = (isset($datas[0][$ldapConfig['LDAP_FIELD_FULLNAME']][0]) ? $datas[0][$ldapConfig['LDAP_FIELD_FULLNAME']][0] : "");
        $data['LDAP_FIELD_EMAIL'] = (isset($datas[0][$ldapConfig['LDAP_FIELD_EMAIL']][0]) ? $datas[0][$ldapConfig['LDAP_FIELD_EMAIL']][0] : "");
        $data['LDAP_FIELD_PHONE'] = (isset($datas[0][$ldapConfig['LDAP_FIELD_PHONE']][0]) ? $datas[0][$ldapConfig['LDAP_FIELD_PHONE']][0] : "");

        return $data;
        
    }
    return [];
}


/**
 * isLocalUser
 * Retorna se existe um usuário com o nome de login informado
 *
 * @param \PDO $conn
 * @param string $user
 * 
 * @return bool
 */
function isLocalUser (\PDO $conn, string $user): bool
{
    $user = filter_var($user, FILTER_SANITIZE_STRIPPED);

    $sql = "SELECT user_id FROM usuarios WHERE login = :user ";
    try {
        $res = $conn->prepare($sql);
        $res->bindParam(':user', $user);
        $res->execute();
        if ($res->rowCount()) {
            return true;
        }
        return false;
    }
    catch (Exception $e) {
        return false;
    }

    return false;
}


/**
 * getUsers
 * Retorna um array com a listagem dos usuários ou do usuário específico caso o id seja informado
 * @param \PDO $conn
 * @param null|int $id
 * @param null|array $level
 * @return array
 */
function getUsers (\PDO $conn, ?int $id = null, ?array $level = null): array
{
    $return = [];
    $in = "";
    if ($level) {
        $in = implode(',', array_map('intval', $level));
        // VERSION 2. For strings: apply PDO::quote() function to all elements
        // $in = implode(',', array_map([$conn, 'quote'], $level));
    }
    $terms = ($id ? "WHERE user_id = :id " : '');
    $terms = (empty($terms) && $level ? "WHERE nivel IN ({$in})" : $terms);

    $sql = "SELECT * FROM usuarios {$terms} ORDER BY nome, login";
    try {
        $res = $conn->prepare($sql);
        if (!empty($terms)) {
            if ($id)
                $res->bindParam(':id', $id); 
        }
        $res->execute();
        /* $res->debugDumpParams() */
        if ($res->rowCount()) {
            foreach ($res->fetchAll() as $row) {
                $data[] = $row;
            }
            if ($id)
                return $data[0];
            return $data;
        }
        return $return;
    }
    catch (Exception $e) {
        return $return;
    }
}


/**
 * getUsersByPrimaryArea
 * Retorna um array com a listagem dos usuários da área informada
 * @param \PDO $conn
 * @param null|int $area
 * @param null|array $level
 * @return array
 */
function getUsersByPrimaryArea (\PDO $conn, ?int $area = null, ?array $level = null): array
{
    $return = [];
    $in = "";
    if ($level) {
        $in = implode(',', array_map('intval', $level));
    }
    $terms = ($area ? "AND u.AREA = :area " : '');
    $terms = (empty($terms) && $level ? "AND nivel IN ({$in})" : $terms);

    $sql = "SELECT u.user_id, u.nome FROM usuarios u, sistemas a 
            WHERE u.AREA = a.sis_id 
            {$terms} ORDER BY nome";
    try {
        $res = $conn->prepare($sql);
        if (!empty($terms)) {
            if ($area)
                $res->bindParam(':area', $area); 
        }
        $res->execute();
        /* $res->debugDumpParams() */
        if ($res->rowCount()) {
            foreach ($res->fetchAll() as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return $return;
    }
    catch (Exception $e) {
        return $return;
    }
}

/**
 * getUserInfo
 * Retorna o array com as informações do usuário e da área de atendimento que ele está vinculado
 * [user_id], [login], [nome], [email], [fone], [nivel], [area_id], [user_admin], [last_logon], 
 * [area_nome], [area_status], [area_email], [area_atende], [sis_screen], [sis_wt_profile],
 * [language]
 * @param \PDO $conn: conexao PDO
 * @param int $userId: id do usuário
 * @param string $userName: login do usuário - se for informado, o filtro será por ele
 * @return array
 */
function getUserInfo (\PDO $conn, int $userId, string $userName = ''): array
{
    $terms = (empty($userName) ? " user_id = :userId " : " login = :userName ");
    $sql = "SELECT 
                u.user_id, 
                u.login, u.nome, 
                u.email, u.fone, 
                u.password, u.hash, 
                u.nivel, u.AREA as area_id, 
                u.user_admin, u.last_logon, 
                a.sistema as area_nome, 
                a.sis_status as area_status, 
                a.sis_email as area_email, 
                a.sis_atende as area_atende, a.sis_screen, 
                a.sis_wt_profile, 
                p.upref_lang as language
            FROM 
                sistemas a, usuarios u LEFT JOIN
                uprefs p ON u.user_id = p.upref_uid
            WHERE 
                u.AREA = a.sis_id 
                AND 
                {$terms} ";
    try {
        $res = $conn->prepare($sql);

        if (!empty($userName)) {
            $res->bindParam(':userName', $userName); 
        } else
            $res->bindParam(':userId', $userId); 

        $res->execute();

        if ($res->rowCount())
            return $res->fetch();
        return [];
    }
    catch (Exception $e) {
        return [];
    }
}

/**
 * getUserAreas
 * Retorna uma string com as áreas SECUNDÁRIAS associadas ao usuário
 * @param \PDO $conn: conexao PDO
 * @param int $userId: id do usuário
 * @return string
 *
 */
function getUserAreas (\PDO $conn, int $userId): string
{
    $areas = "";
    $sql = "SELECT uarea_sid FROM usuarios_areas WHERE uarea_uid = '{$userId}' ";
    
    try {
        $res = $conn->query($sql);
        if ($res->rowCount()) {
            foreach ($res->fetchAll() as $row) {
                if (strlen($areas) > 0)
                    $areas .= ",";
                $areas .= $row['uarea_sid'];
            }
            return $areas;
        }
        return $areas;
    }
    catch (Exception $e) {
        return $areas;
    }
}



/**
 * unique_multidim_array
 * Retorna o array multidimensional sem duplicados baseado na chave fornecida
 * @param mixed $array
 * @param mixed $key
 * 
 * @return array | null
 */
function unique_multidim_array($array, $key): ?array
{
    $temp_array = array();
    $i = 0;
    $key_array = array();

    foreach ($array as $val) {
        if (!in_array($val[$key], $key_array)) {
            $key_array[$i] = $val[$key];
            $temp_array[$i] = $val;
        }
        $i++;
    }
    return $temp_array;
} 



/**
 * getUsersByArea
 * Retorna todos os usuários de uma determinada área - sendo primária ou secundária
 * Também retorna a quantidade de chamados vinculados (sob responsabilidade) a cada usuário
 * @param \PDO $conn: conexao PDO
 * @param int | null  $area: id do usuário
 * @return array | null
 *
 */
function getUsersByArea (\PDO $conn, ?int $area, ?bool $getTotalTickets = true): ?array
{

    if (!$area) {
        return [];
    }
    
    $primaryUsers = [];
    $secondaryUsers = [];
    $totalTickets = [];
    
    /* Checando com a área sendo primária */
    $sql = "SELECT 
                u.user_id, u.nome, '0' total 
            FROM 
                sistemas a, usuarios u
            WHERE 
                a.sis_id = u.AREA AND
                a.sis_id = :area 
            ORDER BY 
                u.nome
            ";
    try {
        $res = $conn->prepare($sql);
        $res->bindParam(":area", $area, PDO::PARAM_INT);
        $res->execute();
        if ($res->rowCount()) {
            foreach ($res->fetchAll() as $row) {
                $primaryUsers[] = $row;
            }
        }
    }
    catch (Exception $e) {
        // return $e->getMessage();
        return [];
    }

    /* Checando com a área sendo secundária */
    $sql = "SELECT 
                u.user_id, u.nome, '0' total 
            FROM 
                usuarios as u, usuarios_areas as ua 
            WHERE
                u.user_id = ua.uarea_uid AND 
                ua.uarea_sid = :area 
            ORDER BY 
                u.nome
            ";
    try {
        $res = $conn->prepare($sql);
        $res->bindParam(":area", $area, PDO::PARAM_INT);
        $res->execute();
        if ($res->rowCount()) {
            foreach ($res->fetchAll() as $row) {
                $secondaryUsers[] = $row;
            }
        }
    }
    catch (Exception $e) {
        // return $e->getMessage();
        return [];
    }


    /* Quantidade de chamados sob responsabilidade */
    if ($getTotalTickets) {
        $sql = "SELECT 
                    u.user_id, u.nome, count(*) total 
                FROM 
                    ocorrencias o, status s, usuarios u, sistemas a 
                WHERE 
                    o.status = s.stat_id AND 
                    s.stat_painel = 1 AND 
                    o.operador = u.user_id AND 
                    o.oco_scheduled = 0 AND 
                    a.sis_id = u.AREA AND 
                    a.sis_id = :area 
                GROUP BY 
                    user_id, nome
                ";
        try {
            $res = $conn->prepare($sql);
            $res->bindParam(":area", $area, PDO::PARAM_INT);
            $res->execute();
            if ($res->rowCount()) {
                foreach ($res->fetchAll() as $row) {
                    $totalTickets[] = $row;
                }
            }
        }
        catch (Exception $e) {
            // $exception .= "<hr>" . $e->getMessage();
            return [];
        }
    }

    
    if ($getTotalTickets) {
        $output = array_merge($totalTickets, $primaryUsers, $secondaryUsers);
    } else {
        $output = array_merge($primaryUsers, $secondaryUsers);
    }
    
    $output = unique_multidim_array($output, 'user_id');
    
    $keys = array_column($output, 'nome');
    array_multisort($keys, SORT_ASC, $output);

    return $output;
}


/**
 * getUserAreasNames
 * Retorna um array com os nomes das áreas cujos ids são informados em string
 * @param PDO $conn
 * @param mixed $areasIds
 * @return array
 */
function getUserAreasNames(\PDO $conn, string $areasIds): array
{
    $names = [];
    $sql = "SELECT sistema FROM sistemas WHERE sis_id IN ({$areasIds}) ORDER BY sistema";
    try {
        $res = $conn->query($sql);
        foreach ($res->fetchAll() as $row) {
            $names[] = $row['sistema'];
        }
        return $names;
    }
    catch (Exception $e) {
        return [];
    }
}


/**
 * getTableCompat
 * Para manter a compatibilidade com versões antigas
 * Faz o teste com a nomenclatura da tabela areaxarea_abrechamado
 * Em versões antigas essa tabela era areaXarea_abrechamado
 * @param PDO $conn
 * @return string
 */
function getTableCompat(\PDO $conn): string
{
    $table = "areaxarea_abrechamado";
    $sqlTest = "SELECT * FROM {$table}";
    try {
        $conn->query($sqlTest);
        return $table;
    } catch (Exception $e) {
        $table = "areaXarea_abrechamado";
        return $table;
    }
}


/**
 * getAreasToOpen
 * Retorna um array com as informacoes das areas possiveis de receberem chamados do usuario logado
 * sis_id , sistema
 * @param PDO $conn
 * @return array
 */
function getAreasToOpen(\PDO $conn, string $userAreas): array
{
    if (empty($userAreas))
        return [];
    $userAreas = filter_var($userAreas, FILTER_SANITIZE_STRIPPED);
    
    $table = getTableCompat($conn);
    $sql = "SELECT s.sis_id, s.sistema 
            FROM sistemas s, {$table} a 
            WHERE
                s.sis_status = 1  AND
                s.sis_atende = 1  AND 
                s.sis_id = a.area AND 
                a.area_abrechamado IN (:userAreas) 
            GROUP BY 
                sis_id, sistema 
            ORDER BY sistema";

    try {
        $res = $conn->prepare($sql);
        $res->bindParam(':userAreas', $userAreas, PDO::PARAM_STR);
        $res->execute();

        if ($res->rowCount()) {
            foreach ($res->fetchAll() as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return [];
    }
    catch (Exception $e) {
        return [];
    }
}


/**
 * getIssuesByArea
 * Retorna um array com as informacoes dos tipos de problemas - listagem
 * keys: prob_id | problema | prob_area | prob_sla | prob_tipo_1, prob_tipo_2 | prob_tipo_3 | prob_descricao
 * @param PDO $conn
 * @param bool $all
 * @param int|null $areaId
 * @param int|null $showHidden : se estiver marcado como "0" não exibirá os tipos de problemas marcados como ocultos para a área
 * @return array
 */
function getIssuesByArea(\PDO $conn, bool $all = false, ?int $areaId = null, ?int $showHidden = 1): array
{
    $areaId = (isset($areaId) && filter_var($areaId, FILTER_VALIDATE_INT) ? $areaId : "");
    
    $terms = "";
    if (!empty($areaId)) {
        $terms = " (prob_area = :areaId OR prob_area IS NULL OR prob_area = '-1') AND prob_active = 1 ";
        if (!$showHidden) {
            $terms .= " AND (FIND_IN_SET('{$areaId}', prob_not_area) < 1 OR FIND_IN_SET('{$areaId}', prob_not_area) IS NULL ) ";
        }
    } else {
        if ($all) {
            $terms = " 1 = 1 ";
        } else {
            $terms = " (prob_area IS NULL OR prob_area = '-1') AND prob_active = 1 ";
        }
    }
    $sql = "SELECT 
                MIN(prob_id) as prob_id, MIN(prob_area) as prob_area, MIN(prob_descricao) as prob_descricao, problema  
            FROM 
                problemas
            WHERE 
                {$terms}
            GROUP BY 
                problema
            ORDER BY
                problema";
    try {
        $res = $conn->prepare($sql);
        
        if (!empty($areaId)) {
            $res->bindParam(':areaId', $areaId, PDO::PARAM_INT);
        }
        $res->execute();

        if ($res->rowCount()) {
            foreach ($res->fetchAll() as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return [];
    }
    catch (Exception $e) {
        return [];
    }
}



/**
 * getIssuesByArea4
 * Retorna array com as informações dos tipos de problemas
 * Função específica para a nossa versão de relacionamento NxN entre areas e tipos de problemas
 * keys: prob_id | problema | prob_area | prob_sla | prob_tipo_1, prob_tipo_2 | prob_tipo_3 | prob_descricao
 *
 * @param \PDO $conn
 * @param bool $all
 * @param int|null $areaId
 * @param int|null $showHidden : para exibir ou não os tipos de problemas marcados como exceção para a área informada
 * 
 * @return array
 */
function getIssuesByArea4(
        \PDO $conn, 
        bool $all = false, 
        ?int $areaId = null, 
        ?int $showHidden = 1, 
        ?string $areasFromUser = null
): array
{
    $areaId = (isset($areaId) && $areaId != '-1' && filter_var($areaId, FILTER_VALIDATE_INT) ? $areaId : "");
    $areasToOpen = [];
    
    $terms = "";
    if (!empty($areaId)) {
        $terms = " (a.area_id = :areaId OR a.area_id IS NULL) AND p.prob_active = 1 ";
        if (!$showHidden) {
            $terms .= " AND (FIND_IN_SET('{$areaId}', prob_not_area) < 1 OR FIND_IN_SET('{$areaId}', prob_not_area) IS NULL ) ";
        }
    } else {
        if ($all) {
            $terms = " 1 = 1 ";
        } else {
            
            if ($areasFromUser) {
                $areasToOpen = getAreasToOpen($conn, $areasFromUser);
                if (count($areasToOpen)) {
                    $areas_ids = [];
                    foreach ($areasToOpen as $area) {
                        $areas_ids[] = $area['sis_id'];
                    }
                    $areas_ids = implode(',', $areas_ids);
                    
                    $terms = " (a.area_id IS NULL OR  a.area_id IN ({$areas_ids}) ) AND p.prob_active = 1 ";
                } else
                    $terms = " (a.area_id IS NULL) AND p.prob_active = 1 ";
            } else
                $terms = " (a.area_id IS NULL) AND p.prob_active = 1 ";
        }
    }

    /* -- a.prob_id, a.area_id as prob_area, p.prob_descricao, p.problema */
    /* -- a.prob_id, a.area_id, p.prob_descricao, p.problema */
    $sql = "SELECT 
                
                a.prob_id, p.prob_descricao, p.problema
            FROM 
                problemas p, areas_x_issues a
            WHERE 
                p.prob_id = a.prob_id AND 
                {$terms}

            GROUP BY 
                
                a.prob_id, p.prob_descricao, p.problema
            
            ORDER BY
                problema";
    try {
        $res = $conn->prepare($sql);
        
        if (!empty($areaId)) {
            $res->bindParam(':areaId', $areaId, PDO::PARAM_INT);
        }
        $res->execute();

        if ($res->rowCount()) {
            foreach ($res->fetchAll() as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return [];
    }
    catch (Exception $e) {
        echo $sql . "<hr>" . $e->getMessage();
        return [];
    }
}

/**
 * hiddenAreasByIssue
 * Retorna a listagem de areas que possuem o tipo de problema como oculto para utilização em chamados
 * @param \PDO $conn
 * @param int $issueId
 * 
 * @return array
 */
function hiddenAreasByIssue(\PDO $conn, int $issueId): array
{
    $areasArray = [];
    $data = [];
    $sql = "SELECT prob_not_area FROM problemas WHERE prob_id = :issueId AND prob_not_area IS NOT NULL ";
    try {
        $res = $conn->prepare($sql);
        $res->bindParam(':issueId', $issueId, PDO::PARAM_INT);
        $res->execute();
        if ($res->rowCount()) {
            $areasArray = explode(',', $res->fetch()['prob_not_area']);

            foreach ($areasArray as $areaId) {
                $data[] = getAreaInfo($conn, $areaId);
            }
            return $data;
            
            // return $areasArray;
        }
        return [];
    }
    catch (Exception $e) {
        // $exception .= "<hr>" . $e->getMessage();
        return [];
    }
}


/**
 * getIssueDetailed
 * Retorna um array com as informacoes de Sla e categorias do tipo de problema informado - 
 * nomenclauras parecidas também são buscadas
 * keys: prob_id | problema | 
 * @param PDO $conn
 * @param int $id
 * @param ?int $areaId
 * @return array
 */
function getIssueDetailed(\PDO $conn, int $id, ?int $areaId = null): array
{
    $areaId = (isset($areaId) && $areaId != '-1' && filter_var($areaId, FILTER_VALIDATE_INT) ? $areaId : "");
    $termsIssueName = "";
    $terms = "";
    
    if (empty($id))
        return [] ;

    $sqlName = "SELECT problema FROM problemas WHERE prob_id = :id ";
    try {
        $resName = $conn->prepare($sqlName);
        $resName->bindParam(":id", $id, PDO::PARAM_INT);
        $resName->execute();
        if ($resName->rowCount()) {
            // $issueName = "%" . $resName->fetch()['problema'] . "%";
            $issueName = $resName->fetch()['problema'];
            // $termsIssueName = " WHERE lower(p.problema) LIKE (lower(:issueName)) ";
            $termsIssueName = " AND lower(p.problema) LIKE (lower(:issueName)) ";
        } else {
            return [];
        }
    }
    catch (Exception $e) {
        echo $e->getMessage();
        return [];
    }
   
    if (!empty($areaId)) {
        // $terms = "AND (p.prob_area = :areaId OR (p.prob_area is null OR p.prob_area = -1))";
        $terms = " AND (ai.area_id = :areaId OR ai.area_id IS NULL) ";
    }
    
    // -- LEFT JOIN sistemas as s on p.prob_area = s.sis_id 
    $sql = "SELECT 
                p.prob_id, p.problema, sl.slas_desc, 
                pt1.probt1_desc, pt2.probt2_desc, pt3.probt3_desc
            FROM areas_x_issues ai, problemas as p 
            
            LEFT JOIN sla_solucao as sl on sl.slas_cod = p.prob_sla 
            LEFT JOIN prob_tipo_1 as pt1 on pt1.probt1_cod = p.prob_tipo_1 
            LEFT JOIN prob_tipo_2 as pt2 on pt2.probt2_cod = p.prob_tipo_2 
            LEFT JOIN prob_tipo_3 as pt3 on pt3.probt3_cod = p.prob_tipo_3 

            WHERE p.prob_id = ai.prob_id 
                {$termsIssueName} {$terms} 

            GROUP BY
                prob_id, problema, slas_desc, probt1_desc, probt2_desc, probt3_desc

            ORDER BY p.problema";
    try {
        $res = $conn->prepare($sql);
        
        if ((!empty($areaId))) {
            $res->bindParam(':areaId', $areaId, PDO::PARAM_INT);
        }
        $res->bindParam(':issueName', $issueName, PDO::PARAM_STR);
        $res->execute();

        if ($res->rowCount()) {
            foreach ($res->fetchAll() as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return [];
    }
    catch (Exception $e) {
        echo $sql . "<hr>" . $e->getMessage();
        return [];
    }
}


/**
 * Retorna as informações do tipo de problema informado
 * @param PDO $conn
 * @param int $id
 * @return array
 */
function getIssueById(\PDO $conn, int $id):array
{
    $sql = "SELECT * FROM problemas WHERE prob_id =:id ";
    try {
        $res = $conn->prepare($sql);
        $res->bindParam(':id',$id);
        $res->execute();
        if ($res->rowCount()) {
            return $res->fetch();
        }
        return [];
    }
    catch (Exception $e) {
        return [];
    }
}


/**
 * Retorna se a área informada possui o tipo de problema com a nomenclatura do id informado
 * @param PDO $conn
 * @param int $areaId
 * @param int $probID
 * @return bool
 */
function areaHasIssueName(\PDO $conn, int $areaId, int $probId):bool
{
    $issueName = "";
    $sql = "SELECT problema FROM problemas WHERE prob_id =:probId ";
    try {
        $res = $conn->prepare($sql);
        $res->bindParam(':probId',$probId);
        $res->execute();
        if ($res->rowCount()) {
            $issueName = $res->fetch()['problema'];

            // $sql = "SELECT * FROM problemas WHERE problema = '{$issueName}' AND prob_area = :areaId ";
            $sql = "SELECT * FROM 
                        problemas p, areas_x_issues ai 
                    WHERE 
                        p.problema = '{$issueName}' AND ai.area_id = :areaId AND 
                        p.prob_id = ai.prob_id ";
            
            
            $res = $conn->prepare($sql);
            $res->bindParam(':areaId', $areaId);
            $res->execute();
            if ($res->rowCount()) {
                return true;
            }
            return false;
        }
        return false;
    }
    catch (Exception $e) {
        return false;
    }
}



/**
 * Retorna se o tipo de problema informado (de acordo com sua nomenclatura) existe desvinculado de áreas de atendimento
 * @param PDO $conn
 * @param int $probID
 * @return bool
 */
function issueFreeFromArea(\PDO $conn, int $probId):bool
{
    $issueName = "";
    $sql = "SELECT problema FROM problemas WHERE prob_id =:probId ";
    try {
        $res = $conn->prepare($sql);
        $res->bindParam(':probId',$probId);
        $res->execute();
        if ($res->rowCount()) {
            $issueName = $res->fetch()['problema'];

            $sql = "SELECT * FROM problemas p, areas_x_issues ai 
                    WHERE 
                        p.problema = '{$issueName}' AND (ai.area_id IS NULL)
                        AND p.prob_id = ai.prob_id ";
            $res = $conn->prepare($sql);
            $res->execute();
            if ($res->rowCount()) {
                return true;
            }
            return false;
        }
        return false;
    }
    catch (Exception $e) {
        return false;
    }
}



/**
 * Retorna se um tipo de problema possui roteiros relacionados
 * @param PDO $conn
 * @param int $id
 * @return string
 */
function issueDescription(\PDO $conn, int $id):string
{
    $sql = "SELECT prob_descricao FROM problemas WHERE prob_id =:id ";
    try {
        $res = $conn->prepare($sql);
        $res->bindParam(':id',$id);
        $res->execute();
        if ($res->rowCount()) {
            return $res->fetch()['prob_descricao'];
        }
        return '';
    }
    catch (Exception $e) {
        return '';
    }
}



/**
 * getAreasByIssue
 * Retorna um array com as informações das áreas de atendimento
 * vinculadas ao tipo de problema informado (via id) - Nova arquitetura NxN para 
 * areas x tipos de problemas: areas_x_issues
 *
 * @param \PDO $conn
 * @param int $id : id do tipo de problema
 * 
 * @return array
 */
function getAreasByIssue (\PDO $conn, int $id, ?string $labelAll = "Todas"): array
{
    $data = [];

    /* Só retornará registro se existir com area_id = null */
    $sqlAllAreas = "SELECT 
                       area_id as sis_id, 
                       '{$labelAll}' as sistema
                    FROM areas_x_issues
                    WHERE 
                        area_id IS NULL AND 
                        prob_id = :id ";
    try {
        $res = $conn->prepare($sqlAllAreas);
        $res->bindParam(':id', $id);
        $res->execute();

        if ($res->rowCount()) {
            foreach ($res->fetchAll() as $row) {
                $data[] = $row;
            }
            return $data;
            
        }
    }
    catch (Exception $e) {
        // $exception .= "<hr>" . $e->getMessage();
        return [];
    }


    $sql = "SELECT * FROM sistemas s, areas_x_issues ap 
            WHERE 
                (s.sis_id = ap.area_id OR ap.area_id IS NULL) AND 
                ap.prob_id = :id 
            ORDER BY s.sistema";
    try {
        $res = $conn->prepare($sql);
        $res->bindParam(':id',$id);
        $res->execute();
        if ($res->rowCount()) {
            foreach ($res->fetchAll() as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return [];
    }
    catch (Exception $e) {
        return [];
    }
}



/**
 * Retorna o departamento de uma tag (com unidade) informada
 * @param PDO $conn
 * @param int $unit
 * @param int $tag
 * @return null|int
 */
function getDepartmentByUnitAndTag(\PDO $conn, int $unit, int $tag):?int
{

    $sql = "SELECT comp_local FROM equipamentos WHERE comp_inst = :unit AND comp_inv = :tag ";
    try {
        $res = $conn->prepare($sql);
        $res->bindParam(':unit',$unit);
        $res->bindParam(':tag',$tag);
        $res->execute();
        if ($res->rowCount()) {
            return $res->fetch()['comp_local'];
        }
        return null;
    }
    catch (Exception $e) {
        return null;
    }
}


/**
 * Retorna se um tipo de problema possui roteiros relacionados
 * @param PDO $conn
 * @param int $id
 * @return bool
 */
function issueHasScript(\PDO $conn, int $id):bool
{
    $sql = "SELECT prscpt_id FROM prob_x_script WHERE prscpt_prob_id = :id ";
    try {
        $res = $conn->prepare($sql);
        $res->bindParam(':id', $id);
        $res->execute();
        if ($res->rowCount()) {
            return true;
        }
        return false;
    }
    catch (Exception $e) {
        return false;
    }
}


/**
 * Retorna se um tipo de problema possui roteiros para usuário final
 * @param PDO $conn
 * @param int $id
 * @return bool
 */
function issueHasEnduserScript(\PDO $conn, int $id):bool
{
    $sql = "SELECT script.scpt_enduser FROM problemas as p 
            LEFT JOIN prob_x_script as sc on sc.prscpt_prob_id = p.prob_id 
            LEFT JOIN scripts as script on script.scpt_id = sc.prscpt_scpt_id 
            WHERE p.prob_id = :id ";
    try {
        $res = $conn->prepare($sql);
        $res->bindParam(':id', $id);
        $res->execute();
        if ($res->rowCount()) {
            foreach ($res->fetchAll() as $row) {
                if ($row['scpt_enduser'] == 1)
                    return true;
            }
            return false;
        }
        return false;
    }
    catch (Exception $e) {
        return false;
    }
}


/**
 * getOpenerLevel
 * Retorna o código do nível do usuário que abriu o chamado
 * @param \PDO $conn
 * @param int $ticket
 * @return int
 */
function getOpenerLevel (\PDO $conn, int $ticket): int
{
    $sql = "SELECT u.nivel FROM usuarios u, ocorrencias o WHERE o.numero = :ticket AND o.aberto_por = u.user_id ";
    $result = $conn->prepare($sql);
    $result->bindParam(':ticket', $ticket);
    $result->execute();

    return $result->fetch()['nivel'];
}


/**
 * getOpenerEmail
 * Retorna o endereço de e-mail do usuário que abriu o chamado
 * @param \PDO $conn
 * @param int $ticket
 * @return string
 */
function getOpenerEmail (\PDO $conn, int $ticket): string
{
    $sql = "SELECT u.email FROM usuarios u, ocorrencias o WHERE o.numero = :ticket AND o.aberto_por = u.user_id ";
    $result = $conn->prepare($sql);
    $result->bindParam(':ticket', $ticket);
    $result->execute();

    return $result->fetch()['email'];
}


/**
 * isAreasIsolated
 * Retorna se a configuração atual está marcada para isolamento de visibilidade entre áreas
 * @param PDO $conn
 * @return bool
 */
function isAreasIsolated($conn): bool
{
    $config = getConfig($conn);
    if ($config['conf_isolate_areas'] == 1)
        return true;
    return false;
}


/**
 * isFather
 * Testa se o ticket informado pode ser um chamado pai
 * @param \PDO $conn
 * @param int $ticket
 * @return array
 */
function getTicketData (\PDO $conn, int $ticket): array
{
    $sql = "SELECT * 
            FROM ocorrencias  
            WHERE 
                numero = :ticket";
    try {
        $res = $conn->prepare($sql);
        $res->bindParam(':ticket', $ticket, PDO::PARAM_INT);
        $res->execute();
        
        if ($res->rowCount()) {
            return $res->fetch();
        }
        return [];
    }
    catch (Exception $e) {
        return [];
    }
}


/**
 * isFather
 * Testa se o ticket informado pode ser um chamado pai
 * @param \PDO $conn
 * @param int $ticket
 * @return bool
 */
function isFatherOk (\PDO $conn, ?int $ticket): bool
{
    $sql = "SELECT o.numero 
            FROM ocorrencias o, `status` s 
            WHERE 
                o.`status` = s.stat_id AND 
                s.stat_painel NOT IN (3) AND 
                o.numero = :ticket";
    try {
        $res = $conn->prepare($sql);
        $res->bindParam(':ticket', $ticket, PDO::PARAM_INT);
        $res->execute();
        
        if ($res->rowCount()) {
            return true;
        }
        return false;
    }
    catch (Exception $e) {
        return false;
    }
}


/**
 * getCustomFields
 * Retorna a listagem de campos personalizados de acordo com os filtros $tableTo e $type. 
 * Possíveis $type para filtro: text, number, select, select_multi, date, time, datetime, textarea, checkbox 
 * Ou retorna um registro específico caso o $id seja fornecido
 *
 * @param \PDO $conn
 * @param int|null $id
 * @param string|null $tableTo
 * @param array|null $type
 * @param int|null $active
 * 
 * @return array
 */
function getCustomFields (\PDO $conn, ?int $id = null, ?string $tableTo = null, ?array $type = null, ?int $active = 1): array
{

    $terms = ' WHERE 1 = 1 ';
    $typeList = ["text", "number", "select", "select_multi", "date", "time", "datetime", "textarea", "checkbox"];
    
    
    if (!$id) {
        if ($type && is_array($type)) {

            $typesOk = array_intersect($type,$typeList);
            
            if (count($typesOk)) {
                $typesOk = implode("','", $typesOk);
                $terms .= " AND field_type IN ('$typesOk') ";
            } else {
                return [];
            }
            
        }

        if (!empty($tableTo)) {
            $terms .= " AND field_table_to = :tableTo ";
        }

        if (!empty($active)) {
            $terms .= " AND field_active = :active ";
        }
    } else {
        /* Se tiver $id não importa o $type nem o $tableTo*/
        $terms = "WHERE id = :id ";
    }
    

    $sql = "SELECT * FROM custom_fields {$terms} ORDER BY field_active, field_order, field_label";
    try {
        $res = $conn->prepare($sql);
        if ($id)
            $res->bindParam(':id', $id, PDO::PARAM_INT);
        else {
            if ($tableTo)
                $res->bindParam(':tableTo', $tableTo, PDO::PARAM_STR);
            // if ($type)
            //     $res->bindParam(':type', $type, PDO::PARAM_STR);
            if ($active)
                $res->bindParam(':active', $active, PDO::PARAM_STR);
        }
        
        $res->execute();
        
        if ($res->rowCount()) {
            foreach ($res->fetchAll() as $row) {
                $data[] = $row;
            }
            if ($id)
                return $data[0];
            return $data;
        }
        return [];
    }
    catch (Exception $e) {
        dump($sql);
        echo $e->getMessage();
        return [];
    }
}


/**
 * getCustomFieldOptionValues
 * Retorna o array com a listagem de opções de seleção para o custom Field ID $fieldId informado
 *
 * @param \PDO $conn
 * @param int $fieldId
 * 
 * @return array
 */
function getCustomFieldOptionValues(\PDO $conn, int $fieldId): array
{
    $sql = "SELECT * FROM custom_fields_option_values WHERE custom_field_id = :fieldId ORDER BY option_value ";
    try {
        $res = $conn->prepare($sql);
        $res->bindParam(':fieldId', $fieldId, PDO::PARAM_INT);
        $res->execute();

        if ($res->rowCount()) {
            foreach ($res->fetchAll() as $row) {
                $data[] = $row;
            }
            
            return $data;
        }
        return [];
    }
    catch (Exception $e) {
        // $exception .= "<hr>" . $e->getMessage();
        return [];
    }
}



/**
 * getCustomFieldValue
 * Retorna o valor de um option em um campo personalizado do tipo 'select'.
 * Se o campo for do tipo 'select_multi' então retorna a lista de valores
 *
 * @param \PDO $conn
 * @param string $id
 * 
 * @return string|null
 */
function getCustomFieldValue(\PDO $conn, ?string $id = null): ?string
{

    if (!$id) {
        return null;
    }

    $values = "";
    $ids = explode(',', $id);

    foreach ($ids as $id) {
        $sql = "SELECT option_value FROM custom_fields_option_values WHERE id = :id";
        try {
            $res = $conn->prepare($sql);
            $res->bindParam(':id', $id, PDO::PARAM_INT);
            $res->execute();
            if ($res->rowCount()) {
                if (strlen($values)) $values .= ", ";
                $values .= $res->fetch()['option_value'];
            }
            // return null;
        }
        catch (Exception $e) {
            // $exception .= "<hr>" . $e->getMessage();
            $values = "";
        }
    }
    return $values;
    
}



/**
 * hasCustomFields
 * Retorna se o ticket informado possui informações em campos extras
 *
 * @param \PDO $conn
 * @param int $ticket
 * 
 * @return bool
 */
function hasCustomFields(\PDO $conn, int $ticket) : bool
{
    $sql = "SELECT id FROM tickets_x_cfields WHERE ticket = :ticket";
    try {
        $res = $conn->prepare($sql);
        $res->bindParam(':ticket', $ticket, PDO::PARAM_INT);
        $res->execute();

        if ($res->rowCount()) {
            return true;
        }
        return false;
    }
    catch (Exception $e) {
        // $exception .= "<hr>" . $e->getMessage();
        return false;
    }
}


/**
 * getTicketCustomFields
 * Retorna um array com todas as informações dos campos extras (campos personalizados) de um ticket informado
 * Índices: field_name, field_label, field_type, field_title, field_placeholder, field_description, 
 * field_value_idx, field_value
 * @param \PDO $conn
 * @param int $ticket
 * 
 * @return array
 */
function getTicketCustomFields(\PDO $conn, int $ticket, ?int $fieldId = null): array
{
    $ticketExtraInfo = [];
    $empty = [];
    $empty['field_id'] = "";
    $empty['field_name'] = "";
    $empty['field_type'] = "";
    $empty['field_label'] = "";
    $empty['field_title'] = "";
    $empty['field_placeholder'] = "";
    $empty['field_description'] = "";
    $empty['field_attributes'] = "";
    $empty['field_value_idx'] = "";
    $empty['field_value'] = "";
    $empty['field_is_key'] = "";
    $empty['field_order'] = "";


    $terms = "";
    if ($fieldId) {
        $terms = " AND c.id = :fieldId ";
    }

    $sql = "SELECT 
                c.id field_id, c.field_name, c.field_type, c.field_label, c.field_title, c.field_placeholder, 
                c.field_description, c.field_attributes, c.field_order, t.cfield_value as field_value_idx,
                t.cfield_value as field_value, t.cfield_is_key as field_is_key
            FROM 
                custom_fields c, tickets_x_cfields t WHERE t.cfield_id = c.id AND ticket = :ticket 
                {$terms}
            ORDER BY field_order, field_label";
    try {
        $res = $conn->prepare($sql);
        $res->bindParam(':ticket', $ticket, PDO::PARAM_INT);
        if ($fieldId) {
            $res->bindParam(':fieldId', $fieldId, PDO::PARAM_INT);
        }
        $res->execute();
        if ($res->rowCount()) {
            $idx = 0;
            foreach ($res->fetchAll() as $row) {
                
                if ($row['field_is_key']) {
                    /* Buscar valor correspondente ao cfield_value */
                    $ticketExtraInfo[$idx]['field_id'] = $row['field_id'];
                    $ticketExtraInfo[$idx]['field_name'] = $row['field_name'];
                    $ticketExtraInfo[$idx]['field_type'] = $row['field_type'];
                    $ticketExtraInfo[$idx]['field_label'] = $row['field_label'];
                    $ticketExtraInfo[$idx]['field_title'] = $row['field_title'];
                    $ticketExtraInfo[$idx]['field_placeholder'] = $row['field_placeholder'];
                    $ticketExtraInfo[$idx]['field_description'] = $row['field_description'];
                    $ticketExtraInfo[$idx]['field_attributes'] = $row['field_attributes'];
                    $ticketExtraInfo[$idx]['field_value_idx'] = $row['field_value'];
                    $ticketExtraInfo[$idx]['field_value'] = getCustomFieldValue($conn, $row['field_value']);
                    $ticketExtraInfo[$idx]['field_is_key'] = $row['field_is_key'];
                    $ticketExtraInfo[$idx]['field_order'] = $row['field_order'];
                } else {
                    $ticketExtraInfo[] = $row;
                }
                $idx++;
            }
            if ($fieldId) {
                /* Único registro retornado */
                return $ticketExtraInfo[0];
            }
            return $ticketExtraInfo;
        }
        return $empty;
    }
    catch (Exception $e) {
        // echo "<hr>" . $e->getMessage();
        return [];
    }
}



/**
 * getChannels
 * Retorna um array com a listagem dos canais de entrada ou do canal específico caso o id seja informado
 * O $type filtra se os canais exibidos estão marcados como only_set_by_system:0|1 (de utilização por meios automatizados)
 * @param \PDO $conn
 * @param null|int $id
 * @param null|string $type : restrict|open| null:todos => Tipos de canais
 * @return array
 */
function getChannels (\PDO $conn, ?int $id = null, ?string $type = null): array
{
    $return = [];

    $terms = '';
    $typeList = ["restrict", "open"];
    
    if (!$id && !empty($type)) {
        if (in_array($type, $typeList)) {
            $terms = "WHERE only_set_by_system = :type ";
        } else {
            $return[] = "Invalid type for channel";
            return $return;
        }
        $filter = ($type == "restrict" ? 1 : 0);
    }

    $terms = ($id ? "WHERE id = :id " : $terms); /* Se tiver $id não importa o $type */

    $sql = "SELECT * FROM channels {$terms} ORDER BY name";
    try {
        $res = $conn->prepare($sql);
        if ($id)
            $res->bindParam(':id', $id, PDO::PARAM_INT);
        elseif ($type) 
            $res->bindParam(':type', $filter, PDO::PARAM_INT);

        $res->execute();
        
        if ($res->rowCount()) {
            foreach ($res->fetchAll() as $row) {
                $data[] = $row;
            }
            if ($id)
                return $data[0];
            return $data;
        }
        return $return;
    }
    catch (Exception $e) {
        return $return;
    }
}

/**
 * getDefaultChannel
 * Retorna o canal padrão
 * @param PDO $conn
 * @return array
 */
function getDefaultChannel (\PDO $conn): array
{
    $return = [];
    
    $sql = "SELECT * FROM channels WHERE is_default = 1 ";
    try {
        $res = $conn->prepare($sql);
        $res->execute();
        if ($res->rowCount()) {
            $row = $res->fetch();
            return $row;
        }
        return $return;
    }
    catch (Exception $e) {
        return $return;
    }
}


/**
 * isSystemChannel
 * Retorna se o canal informado pelo $id é de utilização interna do sistema ou não
 * @param \PDO $conn
 * @param int $id
 * @return bool
 */
function isSystemChannel (\PDO $conn, int $id): bool
{
    $sql = "SELECT id FROM channels WHERE id = :id AND only_set_by_system = 1 ";
    try {
        $res = $conn->prepare($sql);
        $res->bindParam(':id', $id, PDO::PARAM_INT);
        $res->execute();
        
        if ($res->rowCount()) {
            return true;
        }
        return false;
    }
    catch (Exception $e) {
        return false;
    }
}


/**
 * getWorktime
 * Retorna um array com as informações de horarios da jornada de trabalho
 * @param \PDO $conn
 * @param int $profileId
 * @return array
 */
function getWorktime ($conn, $profileId): array
{
    $empty = [];
    
    if (empty($profileId)) {
        return $empty;
    }
        
    $sql = "SELECT * FROM worktime_profiles WHERE id = '{$profileId}'";
    try {
        $res = $conn->query($sql);
        if ($res->rowCount())
            return $res->fetch();
        return $empty;
    }
    catch (Exception $e) {
        return $empty;
    }
}

/**
 * getStatementsInfo
 * Retorna um array com os textos do termo de responsabilidade informado
 * @param \PDO $conn
 * @param string $slug
 * @return array
 */
function getStatementsInfo (\PDO $conn, string $slug): array
{
    $empty = [];
    $empty['header'] = "";
    $empty['title'] = "";
    $empty['p1_bfr_list'] = "";
    $empty['p2_bfr_list'] = "";
    $empty['p3_bfr_list'] = "";
    $empty['p1_aft_list'] = "";
    $empty['p2_aft_list'] = "";
    $empty['p3_aft_list'] = "";
    
    if (empty($slug)) {
        return $empty;
    }
        
    $sql = "SELECT * FROM asset_statements WHERE slug = '{$slug}'";
    try {
        $res = $conn->query($sql);
        if ($res->rowCount())
            return $res->fetch();
        return $empty;
    }
    catch (Exception $e) {
        return $empty;
    }
}


/**
 * getUnits
 * Retorna um array com a listagem com as unidades/instituicoes ou de uma unidade específica caso o id seja informado
 * @param \PDO $conn
 * @param null|int $id
 * @param null|inst $status : 0 - inactive | 1 - active
 * @return array
 * keys: inst_cod | inst_nome | inst_status
 */
function getUnits (\PDO $conn, int $status = 1, ?int $id = null ): array
{
    $return = [];

    $terms = '';
    
    if (!$id) {
        if ($status == 1 || $status == 0)
            $terms = "WHERE inst_status = :status ";
        else 
            $terms = "";
    }

    $terms = ($id ? "WHERE inst_cod = :id " : $terms); /* Se tiver $id não importa o $status */

    $sql = "SELECT * FROM instituicao {$terms} ORDER BY inst_nome";
    try {
        $res = $conn->prepare($sql);
        if ($id)
            $res->bindParam(':id', $id, PDO::PARAM_INT);
        else
            $res->bindParam(':status', $status, PDO::PARAM_INT);

        $res->execute();
        
        if ($res->rowCount()) {
            foreach ($res->fetchAll() as $row) {
                $data[] = $row;
            }
            if ($id)
                return $data[0];
            return $data;
        }
        return $return;
    }
    catch (Exception $e) {
        return $return;
    }
}



/**
 * getDepartments
 * Retorna um array com a listagem com os departamentos (com prédio) ou de uma departamento específico caso o id seja informado
 * @param \PDO $conn
 * @param null|int $id
 * @param null|inst $status : 0 - inactive | 1 - active
 * @return array
 * keys: loc_id | local | loc_reitoria | loc_prior | loc_dominio | loc_predio | loc_status
 */
function getDepartments (\PDO $conn, int $status = 1, ?int $id = null ): array
{
    $return = [];

    $terms = '';
    
    if (!$id) {
        if ($status == 1 || $status == 0)
            $terms = "WHERE l.loc_status = :status ";
        else 
            $terms = "";
    }

    $terms = ($id ? "WHERE l.loc_id = :id " : $terms); /* Se tiver $id não importa o $status */

    $sql = "SELECT l.*, p.pred_desc FROM localizacao l
                LEFT JOIN predios as p on p.pred_cod = l.loc_predio 
                {$terms} ORDER BY local";

    try {
        $res = $conn->prepare($sql);
        if ($id)
            $res->bindParam(':id', $id, PDO::PARAM_INT);
        else
            $res->bindParam(':status', $status, PDO::PARAM_INT);

        $res->execute();
        
        if ($res->rowCount()) {
            foreach ($res->fetchAll() as $row) {
                $data[] = $row;
            }
            if ($id)
                return $data[0];
            return $data;
        }
        return $return;
    }
    catch (Exception $e) {
        return $return;
    }
}



/**
 * getPriorities
 * Retorna um array com a listagem de prioridades de atendimento ou uma prioridade específica caso o id seja informado
 * @param \PDO $conn
 * @param null|int $id
 * @return array
 * keys: pr_cod | pr_nivel | pr_default | pr_desc | pr_color 
 */
function getPriorities (\PDO $conn, ?int $id = null ): array
{
    $return = [];

    $terms = '';
    
    $terms = ($id ? "WHERE pr_cod = :id " : $terms); /* Se tiver $id não importa o $status */

    $sql = "SELECT * FROM prior_atend {$terms} ORDER BY pr_desc";
    try {
        $res = $conn->prepare($sql);
        if ($id)
            $res->bindParam(':id', $id, PDO::PARAM_INT);

        $res->execute();
        
        if ($res->rowCount()) {
            foreach ($res->fetchAll() as $row) {
                $data[] = $row;
            }
            if ($id)
                return $data[0];
            return $data;
        }
        return $return;
    }
    catch (Exception $e) {
        return $return;
    }
}


/**
 * getDefaultPriority
 * Retorna um array com a prioridade padrão de atendimento
 * @param \PDO $conn
 * @return array
 * keys: pr_cod | pr_nivel | pr_default | pr_desc | pr_color 
 */
function getDefaultPriority (\PDO $conn): array
{
    $default = 1;
    $sql = "SELECT * FROM prior_atend WHERE pr_default = :default ";
    try {
        $res = $conn->prepare($sql);
        $res->bindParam(':default', $default, PDO::PARAM_INT);

        $res->execute();
        
        if ($res->rowCount()) {
            // $data[] = $res->fetch();
            return $res->fetch();
        }
        return [];
    }
    catch (Exception $e) {
        return [];
    }
}


/**
 * updateLastLogon
 * Atualiza a informação sobre a data do último logon do usuário
 * @param \PDO $conn
 * @param int $userId
 * @return void
 */
function updateLastLogon (\PDO $conn, int $userId): void
{
    $sql = "UPDATE usuarios SET last_logon = '" . date("Y-m-d H:i:s") . "', forget = NULL WHERE user_id = '{$userId}' ";
    try {
        $conn->exec($sql);
    }
    catch (Exception $e) {
        return ;
    }
}

/**
 * getMailConfig
 * Retorna o array com as informações de configuração de e-mail
 * @param \PDO $conn
 * @return array
 */
function getMailConfig (\PDO $conn): array
{
    $sql = "SELECT * FROM mailconfig";
    try {
        $res = $conn->query($sql);
        if ($res->rowCount())
            return $res->fetch();
        return [];
    }
    catch (Exception $e) {
        return [];
    }
}
/**
 * getEventMailConfig
 * Retorna o array com as informações dos templates de mensagens de e-mail para cada evento
 * @param \PDO $conn
 * @param string $event
 * @return array
 */
function getEventMailConfig (\PDO $conn, string $event): array
{
    $sql = "SELECT * FROM msgconfig WHERE msg_event like (:event)";
    try {
        $res = $conn->prepare($sql);
        $res->bindParam(':event', $event, PDO::PARAM_STR);
        $res->execute();

        if ($res->rowCount())
            return $res->fetch();
        return [];
    }
    catch (Exception $e) {
        return [];
    }
}

/**
 * getStatusInfo
 * Retorna o array com as informações do status filtrado
 * [stat_id], [status], [stat_cat], [stat_painel], [stat_time_freeze]
 * @param \PDO $conn
 * @param int $statusId
 * @return array
 */
function getStatusInfo ($conn, int $statusId): array
{
    $sql = "SELECT * FROM `status` WHERE stat_id = '" . $statusId . "'";
    try {
        $res = $conn->query($sql);
        if ($res->rowCount())
            return $res->fetch();
        return [];
    }
    catch (Exception $e) {
        return [];
    }
}



/**
 * getOperatorTickets
 * Retorna o total de chamados vinculados a um determinado operador
 * @param \PDO $conn
 * @param int $userId
 * @return int
 */
function getOperatorTickets (\PDO $conn, int $userId): int
{
    $sql = "SELECT 
                count(*) AS total 
            FROM 
                ocorrencias o, `status` s 
            WHERE 
                o.operador = {$userId} AND 
                o.status = s.stat_id AND 
                s.stat_painel = 1  AND 
                o.oco_scheduled = 0
            ";
    try {
        $res = $conn->query($sql);
        if ($res->rowCount())
            return $res->fetch()['total'];
        return 0;
    }
    catch (Exception $e) {
        return 0;
    }
}


/**
 * getAreaInfo
 * Retorna o array com as informações da área de atendimento:
 * [area_id], [area_name], [status], [email], [atende], [screen], [wt_profile], [sis_months_done ]
 * @param \PDO $conn
 * @param int $areaId
 * @return array
 */
function getAreaInfo (\PDO $conn, int $areaId): array
{
    $sql = "SELECT 
                sis_id as area_id, 
                sistema as area_name, 
                sis_status as status, 
                sis_email as email, 
                sis_atende as atende, 
                sis_screen as screen, 
                sis_wt_profile as wt_profile, 
                sis_months_done 
            FROM 
                sistemas 
            WHERE 
                sis_id = '" . $areaId . "'";
    try {
        $res = $conn->query($sql);
        if ($res->rowCount())
            return $res->fetch();
        return [];
    }
    catch (Exception $e) {
        return [];
    }
}


/**
 * getAreaAdmins
 * Retorna os admins da área informada por $areaId ou vazio
 * Indices retornados: user_id | nome | email
 * @param \PDO $conn
 * @param int $areaId
 * 
 * @return array
 */
function getAreaAdmins (\PDO $conn, int $areaId):array
{
    $data = [];
    $sql = "SELECT user_id, nome, email FROM usuarios WHERE AREA = :areaId AND user_admin = 1 ORDER BY nome";
    try {
        $res = $conn->prepare($sql);
        $res->bindParam(':areaId', $areaId);
        $res->execute();
        if ($res->rowCount()) {
            foreach ($res->fetchAll() as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return [];
    }
    catch (Exception $e) {
        return [];
    }
}

/**
 * getAreas
 * Retorna o array de registros das áreas cadastradas:
 * [sis_id], [sistema], [status], [sis_email], [sis_atende], [sis_screen], [sis_wt_profile]
 * @param \PDO $conn
 * @param int $all 1: todos os registros 0: checará os outros parametros de filtro
 * @param int | null $status 0: inativas 1: ativas | null: qualquer
 * @param int $atende 0: somente abertura 1: atende chamados | null: qualquer
 * @return array
 */
function getAreas (\PDO $conn, int $all = 1, ?int $status = 1, ?int $atende = 1): array
{
    $terms = "";
    if ($all == 0) {
        // $terms .= ($status == 1 ? " AND sis_status = 1 " : " AND sis_status = 0 ");
        $terms .= (isset($status) && $status == 1 ? " AND sis_status = 1 " : (isset($status) && $status == 0 ? " AND sis_status = 0 " : ""));
        // $terms .= ($atende == 1 ? " AND sis_atende = 1 " : " AND sis_atende = 0 ");
        $terms .= (isset($atende) && $atende == 1 ? " AND sis_atende = 1 " : (isset($atende) && $atende == 0 ? " AND sis_atende = 0 " : ""));
    }
    
    $data = [];
    $sql = "SELECT 
                *
            FROM 
                sistemas 
            WHERE 
                1 = 1 
                {$terms}
            ORDER BY sistema";
    try {
        $res = $conn->query($sql);
        if ($res->rowCount()) {
            foreach ($res->fetchAll() as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return [];
    }
    catch (Exception $e) {
        return [];
    }
}

/**
 * getModuleAccess
 * Retorna se a área tem permissão de acesso ao módulo do sistema:
 * [perm_area], [perm_modulo]
 * @param \PDO $conn: conexão PDO
 * @param int $module - 1: ocorrências - 2: inventário
 * @param mixed $areaId - id da área de atendimento - podem ser várias áreas (secundárias) 
 * @return bool
 */
function getModuleAccess (\PDO $conn, int $module, $areaId): bool
{
    $sql = "SELECT 
                perm_area, perm_modulo
            FROM 
                permissoes 
            WHERE 
                perm_modulo = '" . $module . "' 
            AND
                perm_area IN ('" . $areaId . "') ";
    try {
        $res = $conn->query($sql);
        if ($res->rowCount())
            return true;
        return false;
    }
    catch (Exception $e) {
        return false;
    }
}


/**
 * getStatus
 * Retorna o array de registros dos status cadastradas:
 * [stat_id], [status], [stat_cat], [stat_painel], [stat_time_freeze]
 * @param \PDO $conn
 * @param int $all 1: todos os registros | 0: checará os outros parametros de filtro
 * @param string $painel 1: vinculado ao operador, 2: principal  3: oculto
 * @param string $timeFreeze 0: status sem parada 1: status de parada
 * @param array | null $except : array com ids de status para não serem listados
 * @return array
 */
function getStatus (\PDO $conn, int $all = 1, string $painel = '1,2,3', string $timeFreeze = '0,1', ?array $except = null): array
{
    $terms = "";
    $excluding = "";
    if ($all == 0) {
        $terms .= " AND stat_painel in ({$painel}) ";
        $terms .= " AND stat_time_freeze in ({$timeFreeze}) ";

        if ($except && !empty($except)) {
            $treatedExcept = array_map('intval', $except);
            foreach ($treatedExcept as $exclude) {
                if (strlen($excluding)) $excluding .= ",";
                $excluding .= $exclude;
            }
            $terms .= " AND stat_id NOT IN ({$excluding}) ";
        }
    }
    
    $data = [];
    $sql = "SELECT 
                *
            FROM 
                status 
            WHERE 
                1 = 1 
                {$terms}
            ORDER BY status";
    try {
        $res = $conn->query($sql);
        if ($res->rowCount()) {
            foreach ($res->fetchAll() as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return [];
    }
    catch (Exception $e) {
        return [];
    }
}

/**
 * getStatusById
 * Retorna o array com o registro pesquisado
 * @param \PDO $conn
 * @param int $id
 * @return array
 */
function getStatusById(\PDO $conn, int $id): array
{
    $empty = [];

    $sql = "SELECT * FROM `status` WHERE stat_id = {$id} ";
    try {
        $res = $conn->query($sql);
        if ($res->rowCount())
            return $res->fetch();
        return $empty;
    }
    catch (Exception $e) {
        // return $e->getMessage();
        return $empty;
    }
}



/**
 * getTicketEntries
 * Retorna os assentamentos do chamado informado
 * Fields: 
 * @param \PDO $conn
 * @param int $ticket
 * @param bool|null $private
 * 
 * @return array|null
 */
function getTicketEntries(\PDO $conn, int $ticket, ?bool $private = false): ?array
{

    $terms = "";
    if (!$private) {
        $terms = " AND a.asset_privated = 0 ";
    }

    $data = [];
    $sql = "SELECT 
                a.*, u.*
            FROM 
                assentamentos a, usuarios u 
            WHERE 
                a.responsavel = u.user_id AND
                a.ocorrencia = :ticket 
                {$terms}
            ORDER BY numero";
    try {
        $res = $conn->prepare($sql);
        $res->bindParam(':ticket', $ticket, PDO::PARAM_INT);
        $res->execute();
        if ($res->rowCount()) {
            foreach ($res->fetchAll() as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return [];
    }
    catch (Exception $e) {
        return [];
    }
}



/**
 * getLastEntry
 * Retorna o array com as informações do último assentamento do chamado:
 * [numero], [ocorrencia], [assentamento], [data], [responsavel], [asset_privated], [tipo_assentamento]
 * @param \PDO $conn
 * @param int $ticket
 * @param bool $onlyPublic Define se também será considerado assentamento privado
 * @return array
 */
function getLastEntry (\PDO $conn, int $ticket, bool $onlyPublic = true): array
{
    $empty = [];
    $empty['numero'] = "";
    $empty['ocorrencia'] = "";
    $empty['assentamento'] = "";
    $empty['data'] = "";
    $empty['responsavel'] = "";
    $empty['asset_privated'] = "";
    $empty['tipo_assentamento'] = "";

    $terms = ($onlyPublic ? " AND asset_privated = 0 " : "");
    
    $sql = "SELECT 
                * 
            FROM 
                assentamentos 
            WHERE 
                ocorrencia = '{$ticket}' 
                AND
                numero = (SELECT MAX(numero) FROM assentamentos WHERE ocorrencia = '{$ticket}' {$terms} )
            ";
    $res = $conn->query($sql);
    if ($res->rowCount())
        return $res->fetch();
    return $empty;
}



/**
 * getTicketFiles
 * Retorna um array com as informações dos arquivos anexos ao chamado informado
 * @param \PDO $conn
 * @param int $ticket
 * 
 * @return array|null
 */
function getTicketFiles(\PDO $conn, int $ticket): ?array
{
    $sql = "SELECT * FROM imagens WHERE img_oco = :ticket";
    try {
        $res = $conn->prepare($sql);
        $res->bindParam(":ticket", $ticket, PDO::PARAM_INT);
        $res->execute();
        if ($res->rowCount()) {
            foreach ($res->fetchAll() as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return [];
    }
    catch (Exception $e) {
        return [];
    }
}


/**
 * getTicketRelatives
 * Retorna um array com os números dos chamados relacionados (pai e filhos)
 * @param \PDO $conn
 * @param int $ticket
 * 
 * @return array|null
 */
function getTicketRelatives(\PDO $conn, int $ticket): ?array
{
    $sql = "SELECT * FROM ocodeps WHERE dep_pai = :ticket OR dep_filho = :ticket";
    try {
        $res = $conn->prepare($sql);
        $res->bindParam(":ticket", $ticket, PDO::PARAM_INT);
        $res->execute();
        if ($res->rowCount()) {
            foreach ($res->fetchAll() as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return [];
    }
    catch (Exception $e) {
        return [];
    }
}



/**
 * hasDependency
 * Retorna se um dado chamado possui dependências em subchamados
 * @param \PDO $conn
 * @param int $ticket
 * 
 * @return bool
 */
function hasDependency(\PDO $conn, int $ticket): bool
{
    $sql = "SELECT * FROM ocodeps WHERE dep_pai = :ticket ";
    try {
        $res = $conn->prepare($sql);
        $res->bindParam(":ticket", $ticket, PDO::PARAM_INT);
        $res->execute();
        if ($res->rowCount()){
            foreach ($res->fetchAll() as $row) {
                $sql = "SELECT o.numero FROM ocorrencias o, `status` s 
                        WHERE
                            o.numero = :ticket AND 
                            o.`status` = s.stat_id AND 
                            s.stat_painel NOT IN (3)
                ";
                try {
                    $result = $conn->prepare($sql);
                    $result->bindParam(':ticket', $row['dep_filho'], PDO::PARAM_INT);
                    $result->execute();
                    if ($result->rowCount()) {
                        return true;
                    }
                }
                catch (Exception $e) {
                    return true;
                }
            }
        }
        return false;
    }
    catch (Exception $e) {
        return true;
    }
    return false;
}




/**
 * getSolutionInfo
 * Retorna o array com as informações de descrição técnica e solução para o chamado ou vazio caso nao tenha registro:
 * [numero], [problema], [solucao], [data], [responsavel]
 * @param \PDO $conn
 * @param int $ticket
 * @return array
 */
function getSolutionInfo (\PDO $conn, int $ticket): array
{
    $sql = "SELECT 
                * 
            FROM 
                solucoes 
            WHERE 
                numero = :ticket 
            ";
    
    try {
        $res = $conn->prepare($sql);
        $res->bindParam(':ticket', $ticket, PDO::PARAM_INT);
        $res->execute();
        if ($res->rowCount()) {
            return $res->fetch();
        }
        return [];
    }
    catch (Exception $e) {
        return [];
    }

}


/**
 * getGlobalUri
 * Retorna a url de acesso global da ocorrencia
 * @param \PDO $conn
 * @param int $ticket
 * @return string
 */
function getGlobalUri (\PDO $conn, int $ticket): string
{
    $config = getConfig($conn);

    $sql = "SELECT * FROM global_tickets WHERE gt_ticket = '" . $ticket . "' ";
    $res = $conn->query($sql);
    if ( $res->rowCount() ) {
        $row = $res->fetch();
        return $config['conf_ocomon_site'] . "/ocomon/geral/ticket_show.php?numero=" . $ticket . "&id=" . urlencode($row['gt_id']);
    }

    $rand = random64();
    $sql = "INSERT INTO global_tickets (gt_ticket, gt_id) VALUES ({$ticket}, '" . $rand . "')";
    $conn->exec($sql);
    
    return $config['conf_ocomon_site'] . "/ocomon/geral/ticket_show.php?numero=" . $ticket . "&id=" . urlencode($rand);
}


/**
 * getGlobalTicketId
 * Retorna o id global da ocorrência para acesso por qualquer usuário
 * @param \PDO $conn
 * @param int $ticket
 * 
 * @return string|null
 */
function getGlobalTicketId (\PDO $conn, int $ticket): ?string
{
    $sql = "SELECT gt_id FROM global_tickets WHERE gt_ticket = :ticket ";
    try {
        $res = $conn->prepare($sql);
        $res->bindParam(':ticket', $ticket);
        $res->execute();
        if ($res->rowCount()) {
            return $res->fetch()['gt_id'];
        }
        return null;
    }
    catch (Exception $e) {
        // $exception .= "<hr>" . $e->getMessage();
        return null;
    }
}


/**
 * getEnvVarsValues
 * Retorna um array com os valores das variáveis de ambiente para serem utilizadas nos templates de envio de e-mail
 * @param \PDO $conn
 * @param int $ticket
 * @return array
 */
function getEnvVarsValues (\PDO $conn, int $ticket): array
{
    include ("../../includes/queries/queries.php");
    
    $config = getConfig($conn);
    $lastEntry = getLastEntry($conn, $ticket);
    $solution = getSolutionInfo($conn, $ticket);

    $sql = $QRY["ocorrencias_full_ini"] . " WHERE o.numero = {$ticket} ";
    $res = $conn->query($sql);
    $row = $res->fetch();

    /* Variáveis de ambiente para os e-mails */
    $vars = array();

    $vars = array();
    $vars['%numero%'] = $row['numero'];
    $vars['%usuario%'] = $row['contato'];
    $vars['%contato%'] = $row['contato'];
    $vars['%contato_email%'] = $row['contato_email'];
    $vars['%descricao%'] = nl2br($row['descricao']);
    $vars['%departamento%'] = $row['setor'];
    $vars['%telefone%'] = $row['telefone'];
    $vars['%site%'] = "<a href='" . $config['conf_ocomon_site'] . "'>" . $config['conf_ocomon_site'] . "</a>";
    $vars['%area%'] = $row['area'];
    $vars['%area_email%'] = $row['area_email'];
    $vars['%operador%'] = $row['nome'];
    $vars['%editor%'] = $row['nome'];
    $vars['%aberto_por%'] = $row['aberto_por'];
    $vars['%problema%'] = $row['problema'];
    $vars['%versao%'] = VERSAO;
    $vars['%url%'] = getGlobalUri($conn, $ticket);
    $vars['%url%'] = str_replace(" ", "+", $vars['%url%']);
    $vars['%linkglobal%'] = $vars['%url%'];

    $vars['%unidade%'] = $row['unidade'];
    $vars['%etiqueta%'] = $row['etiqueta'];
    $vars['%patrimonio%'] = $row['unidade']."&nbsp;".$row['etiqueta'];
    $vars['%data_abertura%'] = dateScreen($row['oco_real_open_date']);
    $vars['%status%'] = $row['chamado_status'];
    $vars['%data_agendamento%'] = (!empty($row['oco_scheduled_to']) ? dateScreen($row['oco_scheduled_to']) : "");
    $vars['%data_fechamento%'] = (!empty($row['data_fechamento']) ? dateScreen($row['data_fechamento']) : "");

    $vars['%dia_agendamento%'] = (!empty($vars['%data_agendamento%']) ? explode(" ", $vars['%data_agendamento%'])[0] : '');
    $vars['%hora_agendamento%'] = (!empty($vars['%data_agendamento%']) ? explode(" ", $vars['%data_agendamento%'])[1] : '');

    $vars['%descricao_tecnica%'] = $solution['problema'] ?? "";
    $vars['%solucao%'] = $solution['solucao'] ?? "";
    $vars['%assentamento%'] = nl2br($lastEntry['assentamento']);

    return $vars;
}

/**
 * getEnvVars
 * Retorna o registro gravado com as variáveis de ambiente disponíveis
 * @param \PDO $conn
 * @return bool | array
 */
function getEnvVars (\PDO $conn)
{
    $sql = "SELECT vars FROM environment_vars";
    try {
        $res = $conn->query($sql);
        return $res->fetch()['vars'];
    }
    catch (Exception $e) {
        return false;
    }
}


/** 
 * insert_ticket_stage
 * Realiza a inserção das informações de período de tempo para o chamado
 * @param \PDO $conn
 * @param int $ticket: número do chamado
 * @param string $stage_type: start|stop
 * @param int $tk_status: status do chamado - só será gravado quando o $stage_type for 'start'
 * @param string $specificDate: data específica para gravar - para os casos de chamados saindo 
 *  da fila de agendamento por meio de processos automatizados
 * @return bool
 * 
*/
function insert_ticket_stage (\PDO $conn, int $ticket, string $stageType, int $tkStatus, string $specificDate = ''): bool
{

    $date = (!empty($specificDate) ? $specificDate : date("Y-m-d H:i:s"));
    
    $sqlTkt = "SELECT * FROM `tickets_stages` 
                WHERE ticket = {$ticket} AND id = (SELECT max(id) FROM tickets_stages WHERE ticket = {$ticket}) ";
    $resultTkt = $conn->query($sqlTkt);
    $recordsTkt = $resultTkt->rowCount();

    /* Nenhum registro do chamado na tabela. Nesse caso posso apenas inserir um novo */
    if (!$recordsTkt && $stageType == 'start') {
        
        $sql = "INSERT INTO tickets_stages (id, ticket, date_start, status_id) 
        values (null, {$ticket}, '" . $date . "', {$tkStatus}) ";
    
    } elseif (!$recordsTkt && $stageType == 'stop') {
        
        /* Para chamados existentes anteriormente à implementação da tickets_stages */
        $sqlDateTicket = "SELECT data_abertura, oco_real_open_date FROM ocorrencias WHERE numero = {$ticket} ";
        $resDateTicket = $conn->query($sqlDateTicket);

        $rowDateTicket = $resDateTicket->fetch();

        $openDate = $rowDateTicket['data_abertura'];
        $realOpenDate = $rowDateTicket['oco_real_open_date'];

        $recordDate = (!empty($realOpenDate) ? $realOpenDate : $openDate);

        /* Chamado já existia - nesse caso adiciono um período de start e stop com data de abertura registrada para o chamado*/
        /* o Status zero será para identificar que o período foi inserido nessa condição especial */
        $sql = "INSERT INTO tickets_stages (id, ticket, date_start, date_stop, status_id) 
        values (null, {$ticket}, '" . $recordDate . "', '" . $date . "', 0) ";
        try {
            $conn->exec($sql);
        }
        catch (Exception $e) {
            return false;
        }
        
        //Não posso iniciar um estágio de tempo sem ter primeiro um registro de 'start'
        // return false;
        return true;
    }

    /* Já há registro para esse chamado na tabela de estágios de tempo */
    if ($recordsTkt) {
        $row = $resultTkt->fetch();

        /* há uma data de parada no último registro */
        if (!empty($row['date_stop'])) {
            /* Então preciso inserir novo registro de start */
            if ($stageType == 'start') {
                $sql = "INSERT INTO tickets_stages (id, ticket, date_start, status_id) 
                        values (null, {$ticket}, '" . $date . "', {$tkStatus}) ";
            } elseif ($stageType == 'stop') {
                return false;
            }
        } else {
            /* Preciso atualizar o registro com a parada (stop) */
            if ($stageType == 'stop') {
                $sql = "UPDATE tickets_stages SET date_stop = '" . $date . "' WHERE id = " . $row['id'] . " ";
            } elseif ($stageType == 'start') {
                return false;
            }
        }
    }
    try {
        $conn->exec($sql);
    }
    catch (Exception $e) {
        return false;
    }

    return true;
}


/**
 * firstLog
 * Insere um registro em ocorrencias_log com o estado atual do chamado caso esse registro não exista
 * @param \PDO $conn
 * @param int $numero: número do chamado
 * @param mixed $tipo_edicao: código do tipo de edição - (0: abertura, 1: edição, ...)
 * @param mixed $auto_record
 * @return bool
 */
function firstLog(\PDO $conn, int $numero, $tipo_edicao='NULL', $auto_record = ''): bool
{
    
    /* $tipo_edicao='NULL' */
    include ("../../includes/queries/queries.php");
    
    //Checando se já existe um registro para o chamado
    $sql_log_base = "SELECT * FROM ocorrencias_log WHERE log_numero = '".$numero."' ";
    $qry = $conn->query($sql_log_base);
    $existe_log = $qry->rowCount();

    if (!$existe_log){//AINDA NAO EXISTE REGISTRO - NESSE CASO ADICIONO UM REGISTRO COMPLETO COM O ESTADO ATUAL DO CHAMADO
    
        $qryfull = $QRY["ocorrencias_full_ini"]." WHERE o.numero = " . $numero;
        $qFull = $conn->query($qryfull);
        $rowfull = $qFull->fetch(PDO::FETCH_OBJ);
        
        $base_descricao = $rowfull->descricao;
        $base_departamento = $rowfull->setor_cod;
        $base_area = $rowfull->area_cod;
        $base_prioridade = $rowfull->oco_prior;
        $base_problema = $rowfull->prob_cod;
        $base_unidade = $rowfull->unidade_cod;
        $base_etiqueta = $rowfull->etiqueta;
        $base_contato = $rowfull->contato;
        $base_contato_email = $rowfull->contato_email;
        $base_telefone = $rowfull->telefone;
        $base_operador = $rowfull->operador_cod;
        $base_data_agendamento = $rowfull->oco_scheduled_to;
        $base_status = $rowfull->status_cod;
        
        $val = array();
        $val['log_numero'] = $rowfull->numero;
        
        if ($auto_record == ''){
            $val['log_quem'] = $_SESSION['s_uid'];
        } else
            $val['log_quem'] = $base_operador;            
        
        // $val['log_data'] = date("Y-m-d H:i:s");            
        $val['log_data'] = $rowfull->oco_real_open_date;            
        $val['log_prioridade'] = ($rowfull->oco_prior == "" || $rowfull->oco_prior == "-1" )?'NULL':"'$base_prioridade'";  
        $val['log_descricao'] = $rowfull->descricao == ""?'NULL':"'$base_descricao'";  
        $val['log_area'] = ($rowfull->area_cod == "" || $rowfull->area_cod =="-1")?'NULL':"'$base_area'";  
        $val['log_problema'] = ($rowfull->prob_cod == "" || $rowfull->prob_cod =="-1")?'NULL':"'$base_problema'";  
        $val['log_unidade'] = ($rowfull->unidade_cod == "" || $rowfull->unidade_cod =="-1" || $rowfull->unidade_cod =="0")?'NULL':"'$base_unidade'";  
        $val['log_etiqueta'] = ($rowfull->etiqueta == "" || $rowfull->etiqueta =="-1" || $rowfull->etiqueta =="0")?'NULL':"'$base_etiqueta'";  
        $val['log_contato'] = ($rowfull->contato == "")?'NULL':"'$base_contato'";  
        $val['log_contato_email'] = ($rowfull->contato_email == "")?'NULL':"'$base_contato_email'";  
        $val['log_telefone'] = ($rowfull->telefone == "")?'NULL':"'$base_telefone'";  
        $val['log_departamento'] = ($rowfull->setor_cod == "" || $rowfull->setor_cod =="-1")?'NULL':"'$base_departamento'";  
        $val['log_responsavel'] = ($rowfull->operador_cod == "" || $rowfull->operador_cod =="-1")?'NULL':"'$base_operador'";  
        $val['log_data_agendamento'] = ($rowfull->oco_scheduled_to == "")?'NULL':"'$base_data_agendamento'";  
        $val['log_status'] = ($rowfull->status_cod == "" || $rowfull->status_cod =="-1")?'NULL':"'$base_status'";  
        $val['log_tipo_edicao'] = $tipo_edicao;
        
    
        //GRAVA O REGISTRO DE LOG DO ESTADO ANTERIOR A EDICAO
        $sql_base = "INSERT INTO `ocorrencias_log` ".
            "\n\t(`log_numero`, `log_quem`, `log_data`, `log_descricao`, `log_prioridade`, ".
            "\n\t`log_area`, `log_problema`, `log_unidade`, `log_etiqueta`, ".
            "\n\t`log_contato`, `log_contato_email`, `log_telefone`, `log_departamento`, `log_responsavel`, `log_data_agendamento`, ".
            "\n\t`log_status`, ".
            "\n\t`log_tipo_edicao`) ".
            "\nVALUES ".
            "\n\t('".$val['log_numero']."', '".$val['log_quem']."', '".$val['log_data']."', ".$val['log_descricao'].", ".$val['log_prioridade'].", ".
            "\n\t".$val['log_area'].", ".$val['log_problema'].", ".$val['log_unidade'].", ".$val['log_etiqueta'].", ".
            "\n\t".$val['log_contato'].", ".$val['log_contato_email'].", ".$val['log_telefone'].", ".$val['log_departamento'].", ".$val['log_responsavel'].", ".$val['log_data_agendamento'].", ".
            "\n\t".$val['log_status'].", ".
            "\n\t".$val['log_tipo_edicao']." ".
            "\n\t )";
        
        try {
            $conn->exec($sql_base);
            return true;
        }
        catch (Exception $e) {
            return false;
        }
    }
    return false;
}

/**
 * recordLog
 * Grava o registro de modificações do chamado na tabela ocorrencias_log
 * @param \PDO $conn: conexão
 * @param int $ticket: número do chamado
 * @param array $beforePost: array de informações do chamado antes de sofrer modificações
 * @param array $afterPost: array das informações postadas para modificar o chamado
 * @param int $operationType: código do tipo de operação - 0:abertura | 1:edição... ver o restante
 * @return bool: true se conseguir realizar a inserção e false em caso de falha
 */
function recordLog(\PDO $conn, int $ticket, array $beforePost, array $afterPost, int $operationType): bool
{
    $logPrioridade = (array_key_exists("prioridade", $afterPost) ? $afterPost['prioridade'] : "dontCheck");
    $logArea = (array_key_exists("area", $afterPost) ? $afterPost['area'] : "dontCheck");
    $logProblema = (array_key_exists("problema", $afterPost) ? $afterPost['problema'] : "dontCheck");
    $logUnidade = (array_key_exists("unidade", $afterPost) ? $afterPost['unidade'] : "dontCheck");
    $logEtiqueta = (array_key_exists("etiqueta", $afterPost) ? $afterPost['etiqueta'] : "dontCheck");
    $logContato = (array_key_exists("contato", $afterPost) ? $afterPost['contato'] : "dontCheck");
    $logContatoEmail = (array_key_exists("contato_email", $afterPost) ? $afterPost['contato_email'] : "dontCheck");
    $logTelefone = (array_key_exists("telefone", $afterPost) ? $afterPost['telefone'] : "dontCheck");
    $logDepartamento = (array_key_exists("departamento", $afterPost) ? $afterPost['departamento'] : "dontCheck");
    $logOperador = (array_key_exists("operador", $afterPost) ? $afterPost['operador'] : "dontCheck");
    // $logLastEditor = (array_key_exists("last_editor", $afterPost) ? $afterPost['last_editor'] : "dontCheck");


    $logStatus = (array_key_exists("status", $afterPost) ? $afterPost['status'] : "dontCheck");
    $logAgendadoPara = (array_key_exists("agendadoPara", $afterPost) ? $afterPost['agendadoPara'] : "dontCheck");

    $val = array();
    $val['log_numero'] = $ticket;
    $val['log_quem'] = $_SESSION['s_uid'];            
    $val['log_data'] = date("Y-m-d H:i:s");            

    if ($logPrioridade == "dontCheck") $val['log_prioridade'] = 'NULL'; else
        $val['log_prioridade'] = (($beforePost['oco_prior'] == $logPrioridade) || ((empty($beforePost['oco_prior']) || $beforePost['oco_prior']=="-1" || $beforePost['oco_prior']==NULL)  && ($logPrioridade == "" || $logPrioridade == "-1" || $logPrioridade == NULL)))?'NULL': "'$logPrioridade'"; 
    
    if ($logArea == "dontCheck") $val['log_area'] = 'NULL'; else
        $val['log_area'] = ($beforePost['area_cod'] == $logArea)?'NULL':"'$logArea'";
    
    if ($logProblema == "dontCheck") $val['log_problema'] = 'NULL'; else
        $val['log_problema'] = ($beforePost['prob_cod'] == $logProblema)?'NULL':"'$logProblema'";
    
    if ($logUnidade == "dontCheck") $val['log_unidade'] = 'NULL'; else
        $val['log_unidade'] = (($beforePost['unidade_cod'] == $logUnidade) || ((empty($beforePost['unidade_cod']) || $beforePost['unidade_cod']=="-1" || $beforePost['unidade_cod']==NULL)  && ($logUnidade == "" || $logUnidade == "-1" || $logUnidade == NULL)))?'NULL':"'$logUnidade'";  

    if ($logEtiqueta == "dontCheck") $val['log_etiqueta'] = 'NULL'; else
        $val['log_etiqueta'] = ($beforePost['etiqueta'] == $logEtiqueta)?'NULL':"'".noHtml($logEtiqueta)."'";

    if ($logContato == "dontCheck") $val['log_contato'] = 'NULL'; else
        $val['log_contato'] = ($beforePost['contato'] == $logContato)?'NULL':"'".noHtml($logContato)."'";
    
    if ($logContatoEmail == "dontCheck") $val['log_contato_email'] = 'NULL'; else
        $val['log_contato_email'] = ($beforePost['contato_email'] == $logContatoEmail)?'NULL':"'".noHtml($logContatoEmail)."'";

    if ($logTelefone == "dontCheck") $val['log_telefone'] = 'NULL'; else
        $val['log_telefone'] = ($beforePost['telefone'] == $logTelefone)?'NULL':"'$logTelefone'";

    if ($logDepartamento == "dontCheck") $val['log_departamento'] = 'NULL'; else    
        $val['log_departamento'] = (($beforePost['setor_cod'] == $logDepartamento) || ((empty($beforePost['setor_cod']) || $beforePost['setor_cod']=="-1" || $beforePost['setor_cod']==NULL)  && ($logDepartamento == "" || $logDepartamento == "-1" || $logDepartamento == NULL)))?'NULL':"'$logDepartamento'"; 

    if ($logOperador == "dontCheck") $val['log_responsavel'] = 'NULL'; else
        $val['log_responsavel'] = ($beforePost['operador_cod'] == $logOperador)?'NULL':"'$logOperador'";

    if ($logStatus == "dontCheck") $val['log_status'] = 'NULL'; else
        $val['log_status'] = ($beforePost['status_cod'] == $logStatus)?'NULL':"'$logStatus'";

    if ($logAgendadoPara == "dontCheck") $val['log_data_agendamento'] = 'NULL'; else
        $val['log_data_agendamento'] = ($beforePost['oco_scheduled_to'] == $logAgendadoPara || $logAgendadoPara == "")?'NULL':"'$logAgendadoPara'";

    $val['log_tipo_edicao'] = $operationType; //Edição     


    //GRAVA O REGISTRO DE LOG DA ALTERACAO REALIZADA
    $sqlLog = "INSERT INTO `ocorrencias_log` 
    (`log_numero`, `log_quem`, `log_data`, `log_prioridade`, 
    `log_area`, `log_problema`, `log_unidade`, `log_etiqueta`, `log_departamento`, 
    `log_contato`, `log_contato_email`, `log_telefone`, `log_responsavel`, 
    `log_data_agendamento`, `log_status`, 
    `log_tipo_edicao`) 
    VALUES 
    ('".$val['log_numero']."', '".$val['log_quem']."', '".$val['log_data']."', ".$val['log_prioridade'].", 
    ".$val['log_area'].", ".$val['log_problema'].", ".$val['log_unidade'].", ".$val['log_etiqueta'].", 
    ".$val['log_departamento'].",
    ".$val['log_contato'].", ".$val['log_contato_email'].", ".$val['log_telefone'].", ".$val['log_responsavel'].", ". $val['log_data_agendamento'].", 
    ".$val['log_status'].", ".$val['log_tipo_edicao'].")";

    try {
        $conn->exec($sqlLog);
        return true;
    }
    catch (Exception $e) {
        echo $e->getMessage() . "<br/>" . $sqlLog . "<br/>";
        return false;
    }
}


/*************************
 * ****** INVENTÁRIO *****
 ************************/


/**
 * Retorna o array com as informações da tabela de equipamentos
 * Podem ser passados os dados de etiqueta (unidade e etiqueta) ou o código da tabela de equipamentos
 * Retorna o array vazio se não localizar o registro
 * @param PDO $conn variável de conexão
 * @param int $unit código da unidade
 * @param varchar $tag etiqueta do equipamento
 * @param int $cod código do equipamento na tabela de equipamentos
 */
function getEquipmentInfo ($conn, $unit, $tag, $cod = null): array
{

    $terms = "";
    if (!empty($cod)) {
        $terms .= " AND comp_cod = '{$cod}' ";
    } elseif (empty($unit) || empty($tag)) {
        return [];
    }
    
    if (empty($cod)) {
        $terms .= " AND comp_inv = '{$tag}' AND comp_inst = '{$unit}' ";
    }

    $sql = "SELECT * FROM equipamentos WHERE 1 = 1 {$terms} ";
    try {
        $res = $conn->query($sql);
        if ($res->rowCount())
            return $res->fetch();
        return [];
    }
    catch (Exception $e) {
        return [];
    }
}


/**
 * getManufacturers
 * Retorna um array com a listagem de fabricantes
 * @param PDO $conn
 * @param int $type: 1: hw | 2: sw | 0: any(default)
 * @return array
 */
function getManufacturers ($conn, $type = 0): array
{
    $empty = [];
    $data = [];
    
    $terms = ($type != 0 ? "WHERE fab_tipo IN ({$type},3) OR fab_tipo IS NULL " : '');

    $sql = "SELECT * FROM fabricantes {$terms} ORDER BY fab_nome";
    try {
        $res = $conn->query($sql);
        if ($res->rowCount()) {
            foreach ($res->fetchAll() as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return $empty;
    }
    catch (Exception $e) {
        return $empty;
    }
}


/**
 * getPeripheralInfo
 * Retorna um array com as informações do componente interno (não avulso)
 * @param \PDO $conn
 * @param mixed $peripheralCod
 * @return array
 */
function getPeripheralInfo (\PDO $conn, $peripheralCod): array
{
    $empty = [];
    $empty['mdit_cod'] = "";
    $empty['mdit_manufacturer'] = "";
    $empty['mdit_fabricante'] = "";
    $empty['mdit_desc'] = "";
    $empty['mdit_desc_capacidade'] = "";
    $empty['mdit_sufixo'] = "";
    
    if (empty($peripheralCod)) {
        return $empty;
    }
        
    $sql = "SELECT * FROM modelos_itens WHERE mdit_cod = '{$peripheralCod}'";
    try {
        $res = $conn->query($sql);
        if ($res->rowCount())
            return $res->fetch();
        return $empty;
    }
    catch (Exception $e) {
        return $empty;
    }
}

/**
 * getCostCenterInfo
 * Retorna o array com as informações da tabela de Centros de Custos
 * Retorna o array vazio se não localizar o registro
 * Campos de retorno (se não vazio): ccusto_id, ccusto_name, ccusto_cod
 * @param \PDO $conn
 * @param int $ccId
 * @return array
 */
function getCostCenterInfo (\PDO $conn, ?int $ccId = null): array
{
    if (!$ccId){
        return [];
    }
    
    if (empty($ccId)) {
        return [];
    }
    $sql = "SELECT 
                " . CCUSTO_ID . " AS ccusto_id, 
                " . CCUSTO_DESC . " AS ccusto_name, 
                " . CCUSTO_COD . " AS ccusto_cod 
            FROM 
                `" . DB_CCUSTO . "`.`" . TB_CCUSTO . "` 
            WHERE `" . CCUSTO_ID . "` = '{$ccId}' ";
    try {
        $res = $conn->query($sql);
        if ($res->rowCount())
            return $res->fetch();
        return [];
    }
    catch (Exception $e) {
        return [];
    }
}