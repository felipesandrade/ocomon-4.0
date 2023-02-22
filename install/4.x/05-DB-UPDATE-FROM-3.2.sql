/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

ALTER TABLE `hw_alter` CHANGE `hwa_item` `hwa_item` INT(4) NULL; 

ALTER TABLE `mailconfig` ADD `mail_send` TINYINT(1) NOT NULL DEFAULT '1' AFTER `mail_from_name`; 

ALTER TABLE `modelos_itens` ADD `mdit_manufacturer` INT(6) NULL AFTER `mdit_cod`, ADD INDEX (`mdit_manufacturer`); 

ALTER TABLE `modelos_itens` CHANGE `mdit_fabricante` `mdit_fabricante` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL; 

ALTER TABLE `estoque` ADD `estoq_assist` INT(2) NULL DEFAULT NULL AFTER `estoq_partnumber`, ADD `estoq_warranty_type` INT(2) NULL DEFAULT NULL AFTER `estoq_assist`, ADD INDEX (`estoq_assist`), ADD INDEX (`estoq_warranty_type`); 



ALTER TABLE `sistemas` CHANGE `sis_email` `sis_email` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL; 
ALTER TABLE `config` CHANGE `conf_upld_file_types` `conf_upld_file_types` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '%%IMG%'; 
ALTER TABLE `predios` CHANGE `pred_desc` `pred_desc` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT ''; 



ALTER TABLE `usuarios` CHANGE `password` `password` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT ''; 
ALTER TABLE `usuarios` ADD `hash` VARCHAR(255) NULL AFTER `password`; 



CREATE TABLE `channels` ( `id` INT(2) NOT NULL AUTO_INCREMENT , `name` VARCHAR(255) NOT NULL, PRIMARY KEY (`id`)) ENGINE = InnoDB CHARSET=utf8 COLLATE utf8_general_ci COMMENT = 'Canais disponíveis para abertura de chamados';

INSERT INTO `channels` (`id`, `name`) VALUES (NULL, 'Sistema Web'), (NULL, 'Telefone') ;
INSERT INTO `channels` (`id`, `name`) VALUES (NULL, 'Automático: via Email'), (NULL, 'Email') ;

ALTER TABLE `channels` ADD `is_default` TINYINT(1) NOT NULL DEFAULT '0' AFTER `name`, ADD INDEX (`is_default`); 
UPDATE `channels` SET `is_default` = '1' WHERE `channels`.`id` = 1; 

ALTER TABLE `channels` ADD `only_set_by_system` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'Apenas para processos automatizados' AFTER `is_default`; 

UPDATE `channels` SET `only_set_by_system` = '1' WHERE `channels`.`id` = 3; 


ALTER TABLE `configusercall` ADD `conf_scr_channel` TINYINT(1) NOT NULL DEFAULT '0' AFTER `conf_scr_contact_email`; 

ALTER TABLE `ocorrencias` ADD `oco_channel` INT(2) NULL DEFAULT 1 AFTER `oco_prior`, ADD INDEX (`oco_channel`); 



CREATE TABLE `config_keys` ( `id` INT(3) NOT NULL AUTO_INCREMENT , `key_name` VARCHAR(255) NOT NULL , `key_value` VARCHAR(255) NULL DEFAULT NULL , PRIMARY KEY (`id`), UNIQUE (`key_name`)) ENGINE = InnoDB COMMENT = 'Configuracoes relacionadas a API e outras operacoes'; 

ALTER TABLE `config_keys` CHANGE `key_value` `key_value` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL; 

INSERT INTO `config_keys` (`id`, `key_name`, `key_value`) VALUES (NULL, 'MAIL_GET_ADDRESS', NULL), (NULL, 'MAIL_GET_IMAP_ADDRESS', NULL), (NULL, 'MAIL_GET_PORT', NULL) ;
INSERT INTO `config_keys` (`id`, `key_name`, `key_value`) VALUES (NULL, 'API_TICKET_BY_MAIL_USER', NULL) ;
INSERT INTO `config_keys` (`id`, `key_name`, `key_value`) VALUES (NULL, 'API_TICKET_BY_MAIL_APP', NULL);
INSERT INTO `config_keys` (`id`, `key_name`, `key_value`) VALUES (NULL, 'API_TICKET_BY_MAIL_TOKEN', NULL);
INSERT INTO `config_keys` (`id`, `key_name`, `key_value`) VALUES (NULL, 'MAIL_GET_CERT', '1');
INSERT INTO `config_keys` (`id`, `key_name`, `key_value`) VALUES (NULL, 'MAIL_GET_PASSWORD', NULL);
INSERT INTO `config_keys` (`id`, `key_name`, `key_value`) VALUES (NULL, 'MAIL_GET_MAILBOX', 'INBOX');
INSERT INTO `config_keys` (`id`, `key_name`, `key_value`) VALUES (NULL, 'MAIL_GET_MOVETO', 'OCOMON');
INSERT INTO `config_keys` (`id`, `key_name`, `key_value`) VALUES (NULL, 'MAIL_GET_MARK_SEEN', NULL);
INSERT INTO `config_keys` (`id`, `key_name`, `key_value`) VALUES (NULL, 'MAIL_GET_SUBJECT_CONTAINS', NULL);
INSERT INTO `config_keys` (`id`, `key_name`, `key_value`) VALUES (NULL, 'MAIL_GET_BODY_CONTAINS', NULL);
INSERT INTO `config_keys` (`id`, `key_name`, `key_value`) VALUES (NULL, 'MAIL_GET_DAYS_SINCE', '3');
INSERT INTO `config_keys` (`id`, `key_name`, `key_value`) VALUES (NULL, 'ALLOW_OPEN_TICKET_BY_EMAIL', '0') ;
INSERT INTO `config_keys` (`id`, `key_name`, `key_value`) VALUES (NULL, 'API_TICKET_BY_MAIL_CHANNEL', '3');
INSERT INTO `config_keys` (`id`, `key_name`, `key_value`) VALUES (NULL, 'API_TICKET_BY_MAIL_AREA', NULL), (NULL, 'API_TICKET_BY_MAIL_STATUS', '1') ;




--
-- Estrutura para tabela `mail_queue`
--

CREATE TABLE `mail_queue` (
  `id` int(11) UNSIGNED NOT NULL,
  `subject` varchar(255) NOT NULL DEFAULT '',
  `body` text NOT NULL,
  `from_email` varchar(255) NOT NULL DEFAULT '',
  `from_name` varchar(255) NOT NULL DEFAULT '',
  `recipient_email` varchar(255) NOT NULL DEFAULT '',
  `recipient_name` varchar(255) NOT NULL DEFAULT '',
  `sent_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Índices de tabela `mail_queue`
--
ALTER TABLE `mail_queue`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT de tabela `mail_queue`
--
ALTER TABLE `mail_queue`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;


ALTER TABLE `mail_queue` ADD `ticket` INT(11) NULL DEFAULT NULL AFTER `id`, ADD INDEX (`ticket`); 




CREATE TABLE `access_tokens` ( `id` INT(11) NOT NULL AUTO_INCREMENT , `user_id` INT(11) NULL DEFAULT NULL , `app` VARCHAR(255) NULL DEFAULT NULL , `token` VARCHAR(255) NOT NULL , PRIMARY KEY (`id`), INDEX (`user_id`), INDEX (`app`)) ENGINE = InnoDB; 

ALTER TABLE `access_tokens` CHANGE `token` `token` LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL; 

ALTER TABLE `access_tokens` ADD `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `token`, ADD `updated_at` TIMESTAMP on update CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `created_at`; 






CREATE TABLE `apps_register` ( `id` INT NOT NULL AUTO_INCREMENT , `app` VARCHAR(255) NOT NULL , `controller` VARCHAR(255) NOT NULL , `methods` TEXT NOT NULL , PRIMARY KEY (`id`), UNIQUE (`app`, `controller`)) ENGINE = InnoDB COMMENT = 'Registro de apps para controle de acesso pela API'; 

INSERT INTO `apps_register` (`id`, `app`, `controller`, `methods`) VALUES (NULL, 'ticket_by_email', 'OcomonApi\\Controllers\\Tickets', 'create') ;


ALTER TABLE `utmp_usuarios` ADD `utmp_hash` TEXT NULL DEFAULT NULL AFTER `utmp_passwd`; 

ALTER TABLE `usuarios` CHANGE `password` `password` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL; 

ALTER TABLE `utmp_usuarios` CHANGE `utmp_passwd` `utmp_passwd` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL; 







CREATE TABLE `form_fields` ( `id` INT NOT NULL AUTO_INCREMENT , `entity_name` VARCHAR(30) NOT NULL , `field_name` VARCHAR(50) NOT NULL , `action_name` ENUM('new','edit','close') NOT NULL , `not_empty` TINYINT(1) NULL DEFAULT NULL , PRIMARY KEY (`id`), INDEX (`entity_name`), INDEX (`field_name`), INDEX (`action_name`)) ENGINE = InnoDB COMMENT = 'Obrigatoriedade de preenchimento de campos nos formulários';

-- Inicializacao padrao do form_fields
INSERT INTO `form_fields` (`id`, `entity_name`, `field_name`, `action_name`, `not_empty`) VALUES 
(NULL, 'ocorrencias', 'issue', 'new', '1'), 
(NULL, 'ocorrencias', 'asset_tag', 'new', '0'), (NULL, 'ocorrencias', 'area', 'new', '1'), 
(NULL, 'ocorrencias', 'contact', 'new', '1'), (NULL, 'ocorrencias', 'contact_email', 'new', '1'), 
(NULL, 'ocorrencias', 'phone', 'new', '1'), (NULL, 'ocorrencias', 'department', 'new', '1'), 
(NULL, 'ocorrencias', 'operator', 'new', '1'), (NULL, 'ocorrencias', 'unit', 'new', '0'), 
(NULL, 'ocorrencias', 'priority', 'new', '1'), (NULL, 'ocorrencias', 'channel', 'new', '1');

INSERT INTO `form_fields` (`id`, `entity_name`, `field_name`, `action_name`, `not_empty`) VALUES 
(NULL, 'ocorrencias', 'issue', 'edit', '1'), 
(NULL, 'ocorrencias', 'asset_tag', 'edit', '0'), (NULL, 'ocorrencias', 'area', 'edit', '1'), 
(NULL, 'ocorrencias', 'contact', 'edit', '1'), (NULL, 'ocorrencias', 'contact_email', 'edit', '1'), 
(NULL, 'ocorrencias', 'phone', 'edit', '1'), (NULL, 'ocorrencias', 'department', 'edit', '1'), 
(NULL, 'ocorrencias', 'operator', 'edit', '1'), (NULL, 'ocorrencias', 'unit', 'edit', '0'), 
(NULL, 'ocorrencias', 'priority', 'edit', '1'), (NULL, 'ocorrencias', 'channel', 'edit', '1');

INSERT INTO `form_fields` (`id`, `entity_name`, `field_name`, `action_name`, `not_empty`) VALUES 
(NULL, 'ocorrencias', 'issue', 'close', '1'), 
(NULL, 'ocorrencias', 'asset_tag', 'close', '0'), (NULL, 'ocorrencias', 'area', 'close', '1'), 
(NULL, 'ocorrencias', 'contact', 'close', '1'), (NULL, 'ocorrencias', 'contact_email', 'close', '1'), 
(NULL, 'ocorrencias', 'phone', 'close', '1'), (NULL, 'ocorrencias', 'department', 'close', '1'), 
(NULL, 'ocorrencias', 'operator', 'close', '1'), (NULL, 'ocorrencias', 'unit', 'close', '0'), 
(NULL, 'ocorrencias', 'priority', 'close', '1'), (NULL, 'ocorrencias', 'channel', 'close', '1');




ALTER TABLE `prior_atend` ADD `pr_font_color` VARCHAR(7) NULL DEFAULT '#000000' AFTER `pr_color`; 




CREATE TABLE `input_tags` ( `tag_name` VARCHAR(30) NOT NULL , UNIQUE (`tag_name`)) ENGINE = InnoDB COMMENT = 'Tags de referência'; 
ALTER TABLE `input_tags` ADD `id` INT NOT NULL AUTO_INCREMENT FIRST, ADD PRIMARY KEY (`id`); 

ALTER TABLE `ocorrencias` ADD `oco_tag` TEXT NULL DEFAULT NULL AFTER `oco_channel`, ADD FULLTEXT (`oco_tag`); 

INSERT INTO `config_keys` (`id`, `key_name`, `key_value`) VALUES (NULL, 'API_TICKET_BY_MAIL_TAG', NULL) ;

ALTER TABLE `mailconfig` ADD `mail_queue` TINYINT(1) NOT NULL DEFAULT '0' AFTER `mail_send`; 

ALTER TABLE `localizacao` CHANGE `local` `local` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL; 


ALTER TABLE `usuarios` ADD `forget` VARCHAR(255) NULL DEFAULT NULL AFTER `last_logon`; 

INSERT INTO `msgconfig` (`msg_cod`, `msg_event`, `msg_fromname`, `msg_replyto`, `msg_subject`, `msg_body`, `msg_altbody`) VALUES (NULL, 'forget-password', 'Sistema OcoMon', 'ocomon@yourdomain.com', 'Esqueceu sua senha?', 'Esqueceu sua senha %usuario%?\r\n\r\nVocê está recebendo esse e-mail porque solicitou a recuperação de senha de acesso ao sistema de suporte.\r\n\r\nCaso não tenha sido você o autor da solicitação, apenas ignore essa mensagem. Seus dados estão protegidos.\r\n\r\nClique aqui para definir uma nova senha de acesso: %forget_link%\r\n\r\nAtte.\r\nEquipe de Suporte.', 'Esqueceu sua senha %usuario%?\r\n\r\nVocê está recebendo esse e-mail porque solicitou a recuperação de senha de acesso ao sistema de suporte.\r\n\r\nCaso não tenha sido você o autor da solicitação, apenas ignore essa mensagem. Seus dados estão protegidos.\r\n\r\nClique aqui para definir uma nova senha de acesso: %forget_link%\r\n\r\nAtte.\r\nEquipe de Suporte.');

ALTER TABLE `mail_templates` CHANGE `tpl_msg_html` `tpl_msg_html` MEDIUMTEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL; 

ALTER TABLE `mail_templates` CHANGE `tpl_msg_text` `tpl_msg_text` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL; 

ALTER TABLE `msgconfig` CHANGE `msg_body` `msg_body` MEDIUMTEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL; 
ALTER TABLE `scripts` CHANGE `scpt_script` `scpt_script` MEDIUMTEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL; 
ALTER TABLE `mail_queue` CHANGE `body` `body` MEDIUMTEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL; 

ALTER TABLE `usuarios` ADD INDEX `AREA` (`AREA`);
ALTER TABLE `usuarios` ADD INDEX `user_admin` (`user_admin`);  

ALTER TABLE `problemas` ADD `prob_not_area` VARCHAR(255) NULL DEFAULT NULL AFTER `prob_descricao`, ADD INDEX (`prob_not_area`); 

ALTER TABLE `problemas` ADD `prob_active` TINYINT(1) NOT NULL DEFAULT '1' AFTER `prob_not_area`, ADD INDEX (`prob_active`); 


INSERT INTO `config_keys` (`id`, `key_name`, `key_value`) VALUES (NULL, 'ANON_OPEN_ALLOW', NULL), (NULL, 'ANON_OPEN_SCREEN_PFL', NULL) ;
INSERT INTO `config_keys` (`id`, `key_name`, `key_value`) VALUES (NULL, 'ANON_OPEN_USER', NULL), (NULL, 'ANON_OPEN_STATUS', '1') ;
INSERT INTO `config_keys` (`id`, `key_name`, `key_value`) VALUES (NULL, 'ANON_OPEN_CHANNEL', NULL), (NULL, 'ANON_OPEN_TAGS', NULL);
INSERT INTO `config_keys` (`id`, `key_name`, `key_value`) VALUES (NULL, 'ANON_OPEN_CAPTCHA_CASE', '1');



ALTER TABLE `sistemas` ADD `sis_months_done` INT(3) NOT NULL DEFAULT '12' COMMENT 'Tempo em meses, para filtro de exibição de encerrados' AFTER `sis_wt_profile`; 





CREATE TABLE `custom_fields` ( `id` INT(3) NOT NULL AUTO_INCREMENT , `field_name` VARCHAR(255) NOT NULL , `field_type` ENUM('text','number','select','select_multi','date','time','datetime','textarea','checkbox') NOT NULL , `field_default_value` TEXT NULL DEFAULT NULL , `field_required` TINYINT(1) NOT NULL DEFAULT '0' , PRIMARY KEY (`id`), UNIQUE (`field_name`)) ENGINE = InnoDB COMMENT = 'Campos customizaveis';

ALTER TABLE `custom_fields` ADD `field_table_to` VARCHAR(255) NOT NULL AFTER `field_required`, ADD `field_label` VARCHAR(255) NOT NULL AFTER `field_table_to`, ADD `field_order` INT NULL AFTER `field_label`, ADD INDEX (`field_table_to`); 



ALTER TABLE `custom_fields` ADD `field_title` VARCHAR(255) NULL AFTER `field_order`, ADD `field_placeholder` VARCHAR(255) NULL AFTER `field_title`, ADD `field_description` VARCHAR(255) NULL AFTER `field_placeholder`; 

ALTER TABLE `custom_fields` ADD `field_active` TINYINT(1) NOT NULL DEFAULT '1' AFTER `field_description`, ADD INDEX (`field_active`); 

ALTER TABLE `custom_fields` CHANGE `field_default_value` `field_default_value` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL; 

ALTER TABLE `custom_fields` ADD `field_attributes` TEXT NULL DEFAULT NULL AFTER `field_active`; 

ALTER TABLE `custom_fields` CHANGE `field_order` `field_order` VARCHAR(10) NULL DEFAULT NULL COMMENT 'Campo utilizado para ordenação nas telas do sistema'; 


CREATE TABLE `custom_fields_option_values` ( `id` INT NOT NULL AUTO_INCREMENT , `custom_field_id` INT(3) NOT NULL , `option_value` TEXT NOT NULL , PRIMARY KEY (`id`), INDEX (`custom_field_id`)) ENGINE = InnoDB COMMENT = 'Valores para os campos customizados do tipo select '; 


CREATE TABLE `tickets_x_cfields` ( `id` INT NOT NULL AUTO_INCREMENT , `ticket` INT NOT NULL , `cfield_id` INT NOT NULL , `cfield_value` TEXT NULL DEFAULT NULL , PRIMARY KEY (`id`), INDEX (`ticket`), INDEX (`cfield_id`)) ENGINE = InnoDB COMMENT = 'Registros com campos personalizados'; 

ALTER TABLE `tickets_x_cfields` ADD `cfield_is_key` TINYINT NULL DEFAULT NULL AFTER `cfield_value`; 


ALTER TABLE `configusercall` ADD `conf_scr_custom_ids` TEXT NULL DEFAULT NULL COMMENT 'Ids dos campos personalizados' AFTER `conf_scr_channel`; 


ALTER TABLE `config` ADD `conf_cfield_only_opened` TINYINT(1) NOT NULL DEFAULT '1' COMMENT 'Define se na edição, os campos personalizados serão limitados aos utilizados na abertura do chamado' AFTER `conf_isolate_areas`; 




ALTER TABLE `config` ADD `conf_updated_issues` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'Flag para saber se o update da tabela de tipos de problemas foi realizado.' AFTER `conf_isolate_areas`, ADD INDEX (`conf_updated_issues`); 

ALTER TABLE `config` ADD `conf_allow_op_treat_own_ticket` TINYINT(1) NOT NULL DEFAULT '1' COMMENT 'Define se o operador pode tratar chamados abertos por ele mesmo' AFTER `conf_isolate_areas`; 

ALTER TABLE `config` ADD `conf_reopen_deadline` INT(2) NOT NULL DEFAULT '0' COMMENT 'Limite de tempo em dias para a reabertura de chamados' AFTER `conf_allow_reopen`; 

CREATE TABLE `areas_x_issues` ( `id` INT NOT NULL AUTO_INCREMENT , `area_id` INT NULL , `prob_id` INT NOT NULL , `old_prob_id` INT NULL, PRIMARY KEY (`id`), INDEX (`area_id`), INDEX (`prob_id`), INDEX (`old_prob_id`)) ENGINE = InnoDB COMMENT = 'NxN Areas x Problemas'; 



CREATE TABLE `screen_field_required` ( `id` INT(6) NOT NULL AUTO_INCREMENT , `profile_id` INT(6) NOT NULL , `field_name` VARCHAR(64) NOT NULL COMMENT 'Nome do campo na tabela configusercall' , `field_required` TINYINT NOT NULL DEFAULT '1' , PRIMARY KEY (`id`), INDEX (`profile_id`), INDEX (`field_name`)) ENGINE = InnoDB COMMENT = 'Obrigatoriedade de preenchim. dos campos nos perfis de tela';

ALTER TABLE `avisos` ADD `is_recurrent` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'Indica se o aviso será exibindo novamente no outro dia' AFTER `is_active`, ADD INDEX (`is_recurrent`); 


ALTER TABLE `custom_fields` ADD `field_mask` TEXT NULL DEFAULT NULL COMMENT 'Máscara para campos tipo texto' AFTER `field_attributes`; 

ALTER TABLE `custom_fields` ADD `field_mask_regex` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'Se a máscara é uma expressão regular' AFTER `field_mask`; 

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
