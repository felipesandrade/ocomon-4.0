
-- phpMyAdmin SQL Dump
-- version 5.0.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Tempo de geração: 21/07/2020 às 17:34
-- Versão do servidor: 8.0.18
-- Versão do PHP: 7.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `ocomon_2`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `worktime_profiles`
--

CREATE TABLE `worktime_profiles` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `is_default` tinyint(1) DEFAULT NULL,
  `week_ini_time_hour` varchar(2) NOT NULL,
  `week_ini_time_minute` varchar(2) NOT NULL,
  `week_end_time_hour` varchar(2) NOT NULL,
  `week_end_time_minute` varchar(2) NOT NULL,
  `week_day_full_worktime` int(5) NOT NULL,
  `sat_ini_time_hour` varchar(2) NOT NULL,
  `sat_ini_time_minute` varchar(2) NOT NULL,
  `sat_end_time_hour` varchar(2) NOT NULL,
  `sat_end_time_minute` varchar(2) NOT NULL,
  `sat_day_full_worktime` int(5) NOT NULL,
  `sun_ini_time_hour` varchar(2) NOT NULL,
  `sun_ini_time_minute` varchar(2) NOT NULL,
  `sun_end_time_hour` varchar(2) NOT NULL,
  `sun_end_time_minute` varchar(2) NOT NULL,
  `sun_day_full_worktime` int(5) NOT NULL,
  `off_ini_time_hour` varchar(2) NOT NULL,
  `off_ini_time_minute` varchar(2) NOT NULL,
  `off_end_time_hour` varchar(2) NOT NULL,
  `off_end_time_minute` varchar(2) NOT NULL,
  `off_day_full_worktime` int(5) NOT NULL,
  `247` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Cargas horárias para controle de parada de relógio e SLAs';

--
-- Índices de tabelas apagadas
--

--
-- Índices de tabela `worktime_profiles`
--
ALTER TABLE `worktime_profiles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `is_default` (`is_default`);

--
-- AUTO_INCREMENT de tabelas apagadas
--

--
-- AUTO_INCREMENT de tabela `worktime_profiles`
--
ALTER TABLE `worktime_profiles`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
  
  
INSERT INTO `worktime_profiles` (`id`, `name`, `is_default`, `week_ini_time_hour`, `week_ini_time_minute`, `week_end_time_hour`, `week_end_time_minute`, `week_day_full_worktime`, `sat_ini_time_hour`, `sat_ini_time_minute`, `sat_end_time_hour`, `sat_end_time_minute`, `sat_day_full_worktime`, `sun_ini_time_hour`, `sun_ini_time_minute`, `sun_end_time_hour`, `sun_end_time_minute`, `sun_day_full_worktime`, `off_ini_time_hour`, `off_ini_time_minute`, `off_end_time_hour`, `off_end_time_minute`, `off_day_full_worktime`, `247`) VALUES ('1', 'DEFAULT', '1', '00', '00', '23', '59', '1440', '00', '00', '23', '59', '1440', '00', '00', '23', '59', '1440', '00', '00', '23', '59', '1440', '1');

  
  
ALTER TABLE `sistemas` ADD `sis_wt_profile` INT(2) NOT NULL DEFAULT '1' COMMENT 'id do perfil de jornada de trabalho' AFTER `sis_screen`, ADD INDEX (`sis_wt_profile`); 
  

ALTER TABLE `config` ADD `conf_wt_areas` ENUM('1','2') NOT NULL DEFAULT '2' COMMENT '1: área origem, 2: área destino' AFTER `conf_qtd_max_anexos`, ADD INDEX (`conf_wt_areas`); 
  
  
ALTER TABLE `status` ADD `stat_time_freeze` TINYINT(1) NOT NULL DEFAULT '0' AFTER `stat_painel`, ADD INDEX (`stat_time_freeze`); 

UPDATE `status` SET `stat_time_freeze` = 1 WHERE stat_id IN (4,12,16);
  
  

CREATE TABLE `tickets_stages` ( `id` BIGINT NOT NULL AUTO_INCREMENT , `ticket` INT NOT NULL , `date_start` DATETIME NOT NULL , `date_stop` DATETIME NOT NULL , `status_id` INT NOT NULL , PRIMARY KEY (`id`), INDEX (`ticket`), INDEX (`status_id`)) ENGINE = InnoDB COMMENT = 'Intervalos de tempo para cada status do chamado'; 
  
ALTER TABLE `tickets_stages` CHANGE `date_stop` `date_stop` DATETIME NULL DEFAULT NULL; 

ALTER TABLE `ocorrencias` ADD `oco_scheduled_to` DATETIME NULL DEFAULT NULL AFTER `oco_scheduled`; 
  
  
  
CREATE TABLE `ocorrencias_log` ( `log_id` INT(11) NOT NULL AUTO_INCREMENT , `log_numero` INT(11) NOT NULL , `log_quem` INT(5) NOT NULL , `log_data` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP , `log_prioridade` INT(2) NULL DEFAULT NULL , `log_area` INT(4) NULL DEFAULT NULL , `log_problema` INT(4) NULL DEFAULT NULL , `log_unidade` INT(4) NULL DEFAULT NULL , `log_etiqueta` INT(11) NULL DEFAULT NULL , `log_contato` VARCHAR(255) NULL DEFAULT NULL , `log_telefone` VARCHAR(255) NULL DEFAULT NULL , `log_departamento` INT(4) NULL DEFAULT NULL , `log_responsavel` INT(5) NULL DEFAULT NULL , `log_data_agendamento` DATETIME NULL DEFAULT NULL , `log_status` INT(4) NULL DEFAULT NULL , `log_tipo_edicao` INT(2) NULL DEFAULT NULL , PRIMARY KEY (`log_id`), INDEX (`log_numero`)) ENGINE = InnoDB COMMENT = 'Log de alteracoes nas informacoes dos chamados';

ALTER TABLE `ocorrencias_log` ADD `log_descricao` TEXT NULL DEFAULT NULL AFTER `log_data`;   
  
  
  
  
  
ALTER TABLE `utmp_usuarios` CHANGE `utmp_nome` `utmp_nome` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT ''; 
ALTER TABLE `utmp_usuarios` CHANGE `utmp_email` `utmp_email` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '', CHANGE `utmp_passwd` `utmp_passwd` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '', CHANGE `utmp_rand` `utmp_rand` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '';
ALTER TABLE `utmp_usuarios` ADD `utmp_phone` VARCHAR(255) NULL AFTER `utmp_email`; 
ALTER TABLE `utmp_usuarios` ADD `utmp_date` DATETIME NULL DEFAULT CURRENT_TIMESTAMP AFTER `utmp_rand`; 


ALTER TABLE `usuarios` ADD `last_logon` DATETIME NULL AFTER `user_admin`; 
  
  
  
ALTER TABLE `global_tickets` CHANGE `gt_id` `gt_id` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL; 
ALTER TABLE `imagens` CHANGE `img_tipo` `img_tipo` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL; 




ALTER TABLE `prob_tipo_1` CHANGE `probt1_desc` `probt1_desc` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL; 
ALTER TABLE `prob_tipo_2` CHANGE `probt2_desc` `probt2_desc` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL; 
ALTER TABLE `prob_tipo_3` CHANGE `probt3_desc` `probt3_desc` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL; 





ALTER TABLE `ocorrencias` CHANGE `equipamento` `equipamento` VARCHAR(255) NULL DEFAULT NULL; 
ALTER TABLE `ocorrencias_log` CHANGE `log_etiqueta` `log_etiqueta` VARCHAR(255) NULL DEFAULT NULL; 
ALTER TABLE `imagens` CHANGE `img_inv` `img_inv` VARCHAR(255) NULL DEFAULT NULL; 
ALTER TABLE `equipamentos` CHANGE `comp_inv` `comp_inv` VARCHAR(255) NOT NULL; 



ALTER TABLE `estoque` CHANGE `estoq_tag_inv` `estoq_tag_inv` VARCHAR(255) NULL DEFAULT NULL; 
ALTER TABLE `historico` CHANGE `hist_inv` `hist_inv` VARCHAR(255) NOT NULL DEFAULT '0'; 
ALTER TABLE `hist_pieces` CHANGE `hp_comp_inv` `hp_comp_inv` VARCHAR(255) NULL DEFAULT NULL; 
ALTER TABLE `hw_alter` CHANGE `hwa_inv` `hwa_inv` VARCHAR(255) NOT NULL; 
ALTER TABLE `hw_sw` CHANGE `hws_hw_cod` `hws_hw_cod` VARCHAR(255) NOT NULL DEFAULT '0'; 
ALTER TABLE `moldes` CHANGE `mold_inv` `mold_inv` VARCHAR(255) NULL DEFAULT NULL; 






INSERT INTO `msgconfig` (`msg_cod`, `msg_event`, `msg_fromname`, `msg_replyto`, `msg_subject`, `msg_body`, `msg_altbody`) VALUES (NULL, 'agendamento-para-area', 'Sistema OcoMon', 'ocomon@yourdomain.com', 'Chamado Agendado', 'Caro operador\r\n\r\nO chamado número %numero% foi editado e marcado como agendado para a seguinte data:\r\nDia: %dia_agendamento%\r\nHorário: %hora_agendamento%\r\n\r\nO dia e horário marcados indicam quando o chamado entrará novamente na fila de atendimento.\r\n\r\nAtte. Equipe de Suporte', 'Caro operador\r\n\r\nO chamado número %numero% foi editado e marcado como agendado para a seguinte data:\r\nDia: %data_agendamento%\r\nHorário: %hora_agendamento%\r\n\r\nO dia e horário marcados indicam quando o chamado entrará novamente na fila de atendimento.\r\n\r\nAtte. Equipe de Suporte'); 

INSERT INTO `msgconfig` (`msg_cod`, `msg_event`, `msg_fromname`, `msg_replyto`, `msg_subject`, `msg_body`, `msg_altbody`) VALUES (NULL, 'agendamento-para-usuario', 'Sistema OcoMon', 'ocomon@yourdomain.com', 'Chamado Agendado', 'Caro %usuario%,\r\n\r\nSeu chamado foi marcado como agendado para a seguinte data e horário:\r\nDia: %dia_agendamento%\r\nHorário: %hora_agendamento%\r\n\r\nO agendamento do chamado indica que ele entrará novamente na fila de atendimento a partir da data informada.\r\n\r\nAtte.\r\nEquipe de Suporte.', 'Caro %usuario%,\r\n\r\nSeu chamado foi marcado como agendado para a seguinte data e horário:\r\nDia: %dia_agendamento%\r\nHorário: %hora_agendamento%\r\n\r\nO agendamento do chamado indica que ele entrará novamente na fila de atendimento a partir da data informada.\r\n\r\nAtte.\r\nEquipe de Suporte.'); 



CREATE TABLE `environment_vars` ( `id` INT NOT NULL AUTO_INCREMENT , `vars` TEXT NULL DEFAULT NULL , PRIMARY KEY (`id`)) ENGINE = InnoDB COMMENT = 'Variáveis de ambiente para e-mails de notificações'; 

INSERT INTO `environment_vars` (`id`, `vars`) VALUES (NULL, '<p><strong>N&uacute;mero do chamado:</strong> %numero%<br />\r\n<strong>Contato:</strong> %usuario%<br />\r\n<strong>Contato: </strong>%contato%<br />\r\n<strong>E-mail do Contato: </strong>%contato_email%<br />\r\n<strong>Descri&ccedil;&atilde;o do chamado:</strong> %descricao%<br />\r\n<strong>Departamento do chamado:</strong> %departamento%<br />\r\n<strong>Telefone:</strong> %telefone%<br />\r\n<strong>Site para acesso ao OcoMon:</strong> %site%<br />\r\n<strong>&Aacute;rea de atendimento:</strong> %area%<br />\r\n<strong>Operador do chamado:</strong> %operador%<br />\r\n<strong>Operador do chamado:</strong> %editor%<br />\r\n<strong>Quem abriu o chamado:</strong> %aberto_por%<br />\r\n<strong>Tipo de problema:</strong> %problema%<br />\r\n<strong>Vers&atilde;o do OcoMon:</strong> %versao%<br />\r\n<strong>Url global para acesso ao chamado:</strong> %url%<br />\r\n<strong>Url global para acesso ao chamado:</strong> %linkglobal%<br />\r\n<strong>Unidade: </strong>%unidade%<br />\r\n<strong>Etiqueta:</strong> %etiqueta%<br />\r\n<strong>Unidade e Etiqueta:</strong> %patrimonio%<br />\r\n<strong>Data de abertura do chamado:</strong> %data_abertura%<br />\r\n<strong>Status do chamado:</strong> %status%<br />\r\n<strong>Data de agendamento do chamado:</strong> %data_agendamento%<br />\r\n<strong>Data de encerramento do chamado:</strong> %data_fechamento%<br />\r\n<strong>Apenas o dia do agendamento:</strong> %dia_agendamento%<br />\r\n<strong>Apenas a hora do agendamento:</strong> %hora_agendamento%<br />\r\n<strong>Descri&ccedil;&atilde;o t&eacute;cnica (para chamados encerrados):</strong> %descricao_tecnica%<br />\r\n<strong>Solu&ccedil;&atilde;o t&eacute;cnica (para chamados encerrados):</strong> %solucao%<br />\r\n<strong>&Uacute;ltimo assentamento do chamado:</strong> %assentamento%</p>');



ALTER TABLE `avisos` ADD `expire_date` DATETIME NULL DEFAULT NULL AFTER `origembkp`, ADD `is_active` TINYINT NULL DEFAULT NULL AFTER `expire_date`, ADD INDEX (`expire_date`), ADD INDEX (`is_active`); 

ALTER TABLE `avisos` ADD `title` VARCHAR(30) NULL DEFAULT NULL AFTER `aviso_id`; 
ALTER TABLE `avisos` CHANGE `area` `area` VARCHAR(255) NULL DEFAULT NULL; 



CREATE TABLE `user_notices` ( `id` INT NOT NULL AUTO_INCREMENT , `user_id` INT NOT NULL , `notice_id` INT NOT NULL , `last_shown` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP , PRIMARY KEY (`id`), INDEX (`user_id`), INDEX (`notice_id`), INDEX (`last_shown`)) ENGINE = InnoDB COMMENT = 'Avisos do Mural já exibidos para o usuário'; 


ALTER TABLE `config` ADD `conf_sla_tolerance` INT(2) NOT NULL DEFAULT '20' COMMENT 'Percentual de Tolerância de SLA - entre o verde e o vermelho' AFTER `conf_wt_areas`; 



ALTER TABLE `ocorrencias` ADD `contato_email` VARCHAR(255) NULL DEFAULT NULL AFTER `contato`, ADD INDEX (`contato_email`); 

ALTER TABLE `configusercall` ADD `conf_scr_contact_email` INT(1) NOT NULL DEFAULT '0' AFTER `conf_scr_prior`; 


ALTER TABLE `ocorrencias_log` ADD `log_contato_email` VARCHAR(255) NULL DEFAULT NULL AFTER `log_contato`; 
  
  
INSERT INTO `avisos` (`aviso_id`, `title`, `avisos`, `data`, `origem`, `status`, `area`, `origembkp`, `expire_date`, `is_active`) VALUES (NULL, 'Bem vindo!', '<p>Seja muito bem vindo ao OcoMon 4.0, o melhor OcoMon de todos os tempos!</p><hr />
<p>N&atilde;o esque&ccedil;a de ajustar as configura&ccedil;&otilde;es do sistema de acordo com suas necessidades.</p><hr />
<p>Acesse o <a href="https://www.youtube.com/c/OcoMonOficial" target="_blank">canal no Youtube</a> para dicas e informa&ccedil;&otilde;es diversas a respeito do sistema.</p>', CURRENT_TIME(), '1', 'success', '1', NULL, CURRENT_TIME(), '1'); 
  




--
-- Estrutura para tabela `asset_statements`
--

CREATE TABLE `asset_statements` (
  `id` int(11) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `header` text,
  `title` text,
  `p1_bfr_list` text,
  `p2_bfr_list` text,
  `p3_bfr_list` text,
  `p1_aft_list` text,
  `p2_aft_list` text,
  `p3_aft_list` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Textos para os termos de responsabilidade';

--
-- Despejando dados para a tabela `asset_statements`
--

INSERT INTO `asset_statements` (`id`, `slug`, `name`, `header`, `title`, `p1_bfr_list`, `p2_bfr_list`, `p3_bfr_list`, `p1_aft_list`, `p2_aft_list`, `p3_aft_list`) VALUES
(1, 'termo-compromisso', 'Termo de Compromisso', 'CENTRO DE INFORMÁTICA - SIGLA / SUPORTE AO USUÁRIO - HELPDESK', 'Termo de Compromisso para Equipamento', 'Por esse termo acuso o recebimento do(s) equipamento(s) abaixo especificado(s), comprometendo-me a mantê-lo(s) sob a minha guarda e responsabilidade, dele(s) fazendo uso adequado, de acordo com a resolução xxx/ano que define políticas, normas e procedimentos que disciplinam a utilização de equipamentos, recursos e serviços de informática da SUA_EMPRESA.', NULL, NULL, 'O suporte para qualquer problema que porventura vier a ocorrer na instalação ou operação do(s) equipamento(s), deverá ser solicitado à área de Suporte, através do telefone/ramal xxxx, pois somente através desde procedimento os chamados poderão ser registrados e atendidos.', 'Em conformidade com o preceituado no art. 1º da Resolução nº xxx/ano, é expressamente vedada a instalação de softwares sem a necessária licença de uso ou em desrespeito aos direitos autorais.', 'A SUA_EMPRESA, através do seu Departamento Responsável (XXXX), em virtude das suas disposições regimentais e regulamentadoras, adota sistema de controle de instalação de softwares em todos os seus equipamentos, impedindo a instalação destes sem prévia autorização do Departamento Competente.'),
(2, 'termo-transito', 'Formulário de Trânsito', 'CENTRO DE INFORMÁTICA - SIGLA / SUPORTE AO USUÁRIO - HELPDESK', 'Formulário de Trânsito de Equipamentos de Informática', 'Informo que o(s) equipamento(s) abaixo descriminado(s) está(ão) autorizado(s) pelo departamento responsável a serem transportados para fora da Unidade pelo portador citado.', NULL, NULL, 'A constatação de inconformidade dos dados aqui descritos no ato de verificação na portaria implica na não autorização de saída dos equipamentos, nesse caso o departamento responsável deve ser contactado.', NULL, NULL);


--
-- Índices de tabela `asset_statements`
--
ALTER TABLE `asset_statements`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- AUTO_INCREMENT de tabela `asset_statements`
--
ALTER TABLE `asset_statements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;



ALTER TABLE `materiais` CHANGE `mat_cod` `mat_cod` INT(6) NOT NULL AUTO_INCREMENT; 
ALTER TABLE `materiais` CHANGE `mat_nome` `mat_nome` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL; 
ALTER TABLE `materiais` CHANGE `mat_caixa` `mat_caixa` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL; 
ALTER TABLE `materiais` CHANGE `mat_obs` `mat_obs` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL; 

ALTER TABLE `ocorrencias_log` CHANGE `log_data` `log_data` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP; 

CREATE TABLE `email_warranty_equipment` ( `id` INT NOT NULL AUTO_INCREMENT , `equipment_id` INT NOT NULL , `sent_date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP , PRIMARY KEY (`id`), INDEX (`equipment_id`)) ENGINE = InnoDB COMMENT = 'Controle de envio de e-mails sobre vencimento garantia'; 



ALTER TABLE `config` ADD `conf_isolate_areas` INT(1) NOT NULL DEFAULT '0' COMMENT 'Visibilidade entre areas para consultas e relatorios' AFTER `conf_sla_tolerance`; 

  
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



ALTER TABLE `equipxpieces` CHANGE `eqp_equip_inv` `eqp_equip_inv` VARCHAR(255) NOT NULL;   

  
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
