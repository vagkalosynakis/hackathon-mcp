CREATE TABLE `session`(
`session_id` char(32) NOT NULL,
`session_data` longtext NOT NULL,
`last_accessed` int(10) unsigned NOT NULL,
`created` int(10) unsigned NOT NULL,
PRIMARY KEY (`session_id`)
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `acl_user_type`(
`id` smallint(5) unsigned NOT NULL auto_increment,
`name` varchar(200) NOT NULL,
`default_role` varchar(200) default NULL,
`is_admin` tinyint(3) unsigned NOT NULL default '0',
`is_trainer` tinyint(3) unsigned NOT NULL default '0',
`is_learner` tinyint(3) unsigned NOT NULL default '0',
`metadata` longtext NOT NULL,
UNIQUE (`name`),
PRIMARY KEY (`id`)
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `user`(
`id` mediumint(8) unsigned NOT NULL auto_increment,
`login` varchar(150) NOT NULL,
`password` char(64) NOT NULL,
`name` varchar(100) NOT NULL,
`surname` varchar(100) NOT NULL,
`email` varchar(150) default NULL,
`restrict_email` tinyint(1) NOT NULL default '0',
`acl_user_type_id` smallint(5) unsigned default NULL,
`timezone` varchar(100) NOT NULL default '',
`language` varchar(10) default NULL,
`status` enum('active', 'inactive', 'dummy', 'archived') NOT NULL default 'active',
`deactivation_date` int(10) unsigned default NULL,
`level` smallint(5) unsigned NOT NULL default '1',
`points` int(10) unsigned NOT NULL default '0',
`credits` float default '0',
`created` int(10) unsigned NOT NULL,
`last_login` int(10) unsigned NOT NULL,
`last_updated` int(10) unsigned default NULL,
`description` text,
`avatar` varchar(255) default NULL,
`avatar_md` varchar(255) default NULL,
`avatar_lg` varchar(255) default NULL,
`avatar_xl` varchar(255) default NULL,
`viewed_announcement` tinyint(3) default NULL,
`viewed_license` tinyint(3) default NULL,
`shared_emails` longtext default NULL,
`salt` char(10) default NULL,
`login_key` varchar(100) default NULL,
`facebook_id` varchar(255) default NULL,
`google_id` varchar(255) default NULL,
`linkedin_id` varchar(255) default NULL,
`stripe_id` varchar(50) default NULL,
`salesforce_id` varchar(50) default NULL,
`bamboohr_id` int(10) unsigned default NULL,
`marketo_id` int(10) unsigned default NULL,
`external_id` varchar(255) default NULL,
`metadata` text default NULL,
`custom_user_fields` JSON default NULL,
PRIMARY KEY (`id`),
KEY `acl_user_type_id` (`acl_user_type_id`),
UNIQUE (`login`),
CONSTRAINT `user_ibfk_1` FOREIGN KEY (`acl_user_type_id`) REFERENCES `acl_user_type` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `category`(
`id` smallint(5) unsigned NOT NULL auto_increment,
`name` varchar(150) NOT NULL,
`price` float default '0',
`parent_id` smallint(5) unsigned default NULL,
PRIMARY KEY (`id`),
KEY `parent_id` (`parent_id`),
CONSTRAINT `category_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `category` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `certification`(
`id` mediumint(8) unsigned NOT NULL auto_increment,
`name` varchar(100) NOT NULL,
`background` varchar(255) NOT NULL,
`template` longtext NOT NULL,
`orientation` enum('landscape', 'portrait') NOT NULL default 'landscape',
`archived` tinyint(1) NOT NULL default '0',
`cached_preview_url` varchar(255) default NULL,
`marketplace_certification_id` mediumint(8) unsigned default NULL,
PRIMARY KEY (`id`)
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `course`(
`id` mediumint(8) unsigned NOT NULL auto_increment,
`category_id` smallint(5) unsigned default NULL,
`name` varchar(150) NOT NULL,
`description` text,
`course_code` varchar(20) NOT NULL,
`status` enum('active', 'inactive', 'archived') NOT NULL default 'active',
`creation_date` int(10) unsigned default NULL,
`last_update_on` int(10) unsigned default NULL,
`hide_catalog` tinyint default 0,
`price` float default '0',
`avatar` varchar(255) default NULL,
`avatar_big` varchar(255) default NULL,
`avatar_x4` varchar(255) default NULL,
`intro_video_url` varchar(255) default NULL,
`intro_video_id` int(11) default NULL,
`time_limit` float default NULL,
`time_limit_retain_access` tinyint(1) unsigned default '0',
`start_datetime` int(10) unsigned default NULL,
`expiration_datetime` int(10) unsigned default NULL,
`user_id` mediumint(8) unsigned default NULL,
`certification_id` mediumint(8) unsigned default NULL,
`certification_duration_type` tinyint(3) default NULL,
`certification_duration` int(10) unsigned default NULL,
`certification_expiration_date` varchar(10) default NULL,
`certification_expiration_timezone` varchar(100) default NULL,
`level` tinyint(3) unsigned default NULL,
`shared` tinyint(1) unsigned default '0',
`reassign` tinyint(1) unsigned default '0',
`reassign_when` smallint(5) unsigned NOT NULL default '0',
`from_marketplace` tinyint(1) unsigned default '0',
`from_course_library` tinyint(1) unsigned default '0',
`available` tinyint(1) unsigned default '1',
`rating` float(4,3) default NULL,
`estimated_duration` int(11) unsigned default NULL,
`is_manual_duration` tinyint(1) unsigned default '0',
`metadata` text default NULL,
`custom_course_fields` JSON default NULL,
`capacity` mediumint(8) unsigned default '0',
`enrollment_approval` tinyint unsigned default '0',
`locked` tinyint(1) unsigned default '0',
`open_sesame_id` varchar (36) default NULL,
`ai_jobs` json DEFAULT NULL,
`ai_coach` tinyint unsigned default 0,
`is_sample` tinyint unsigned NOT NULL default 0,
PRIMARY KEY (`id`),
KEY `category_id` (`category_id`),
KEY `user_id` (`user_id`),
KEY `certification_id` (`certification_id`),
CONSTRAINT `course_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `category` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
CONSTRAINT `course_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
CONSTRAINT `course_ibfk_3` FOREIGN KEY (`certification_id`) REFERENCES `certification` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `learning_path`(
`id` mediumint unsigned NOT NULL auto_increment,
`category_id` smallint unsigned default NULL,
`name` varchar(150) NOT NULL,
`description` text,
`code` varchar(20) NOT NULL,
`status` enum('active', 'inactive', 'archived') NOT NULL default 'active',
`creation_date` int unsigned default NULL,
`last_updated_on` int unsigned default NULL,
`hide_catalog` tinyint default '0',
`price` float default '0',
`avatar` varchar(255) default NULL,
`avatar_big` varchar(255) default NULL,
`avatar_x4` varchar(255) default NULL,
`time_limit` float default NULL,
`time_limit_retain_access` tinyint unsigned default '0',
`start_datetime` int unsigned default NULL,
`expiration_datetime` int unsigned default NULL,
`user_id` mediumint unsigned default NULL,
`certification_id` mediumint unsigned default NULL,
`certification_duration_type` tinyint default NULL,
`certification_duration` int unsigned default NULL,
`certification_expiration_date` varchar(10) default NULL,
`certification_expiration_timezone` varchar(100) default NULL,
`level` tinyint unsigned default NULL,
`reassign` tinyint unsigned default '0',
`reassign_when` smallint unsigned NOT NULL default '0',
`from_marketplace` tinyint unsigned default '0',
`from_course_library` tinyint unsigned default '0',
`available` tinyint unsigned default '1',
`rating` float(4,3) default NULL,
`metadata` text default NULL,
`capacity` mediumint unsigned default '0',
`enrollment_approval` tinyint unsigned default '0',
`locked` tinyint unsigned default '0',
PRIMARY KEY (`id`),
KEY `category_id` (`category_id`),
KEY `user_id` (`user_id`),
KEY `certification_id` (`certification_id`),
CONSTRAINT `learning_path_id_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `category` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
CONSTRAINT `learning_path_id_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
CONSTRAINT `learning_path_id_ibfk_3` FOREIGN KEY (`certification_id`) REFERENCES `certification` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `configuration`(
`name` varchar(100) NOT NULL,
`value` text NOT NULL,
PRIMARY KEY (`name`)
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `affiliator`(
`id` mediumint(8) unsigned NOT NULL auto_increment,
`login` varchar(100) NOT NULL,
`password` char(64) NOT NULL,
`first_name` varchar(100) NOT NULL,
`last_name` varchar(100) NOT NULL,
`email` varchar(150) NOT NULL,
`paypal_email` varchar(150) NOT NULL,
`commision` float NOT NULL,
`key` varchar(100) NOT NULL,
`created_on` int(10) unsigned NOT NULL,
`salt` char(10) default NULL,
PRIMARY KEY (`id`),
UNIQUE (`key`)
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `custom_plan`(
`id` mediumint(8) unsigned NOT NULL auto_increment,
`key` varchar(50) NOT NULL,
`name` varchar(100) NOT NULL,
`price` float NOT NULL,
`additional_charge` float default NULL,
`users` mediumint(5) NOT NULL,
`courses` mediumint(5) NOT NULL,
`branches` mediumint(5) NOT NULL default '-1',
`based_on` varchar(20) NOT NULL,
`interval` mediumint(5) NOT NULL,
`sso` tinyint(1) NOT NULL default '0',
`success_manager` tinyint(1) NOT NULL default '0',
`domain_map_support` tinyint(1) NOT NULL default '0',
`custom_reports` tinyint(1) NOT NULL default '0',
`automated_actions` tinyint(1) NOT NULL default '0',
`chat_support` tinyint(1) NOT NULL default '0',
`priority_support` tinyint(1) NOT NULL default '0',
`custom_app` tinyint(1) NOT NULL default '0',
`course_library` tinyint(1) NOT NULL default '0',
`legacy` tinyint(1) NOT NULL default '0',
`api_limit_per_hour` mediumint(5) unsigned NOT NULL default '0',
`generic_max_upload` mediumint(5) NOT NULL,
`small_image_max_upload` mediumint(5) NOT NULL,
`image_max_upload` mediumint(5) NOT NULL,
`document_max_upload` mediumint(5) NOT NULL,
`video_max_upload` mediumint(5) NOT NULL,
`audio_max_upload` mediumint(5) NOT NULL,
`flash_max_upload` mediumint(5) NOT NULL,
`scorm_max_upload` mediumint(5) NOT NULL,
`attachment_max_upload` mediumint(5) NOT NULL,
`discussion_max_upload` mediumint(5) NOT NULL,
`caption_max_upload` mediumint(5) NOT NULL,
`stripe_product_id` varchar(50) default NULL,
`phone_support` tinyint(1) NOT NULL default '0',
`white_label` tinyint(1) NOT NULL default '0',
`premium_integrations` tinyint(1) NOT NULL default '0',
`talentcraft_ai` tinyint(1) NOT NULL default '0',
`skills` tinyint(1) NOT NULL default '0',
`ai_credits` mediumint(5) NOT NULL default '1000',
`onboarding` enum('none', 'simple', 'regular', 'premium') NOT NULL default 'none',
`lti_support` tinyint(1) NOT NULL default '0',
`linkedin_learning` tinyint(1) NOT NULL default '0',
`ai_coach` tinyint(1) NOT NULL default '0',
`analytics` tinyint(1) NOT NULL default '0',
`ai_course_translation` tinyint(1) NOT NULL default '0',
`ai_test_questions_generation` tinyint(1) NOT NULL default '0',
`learning_paths` mediumint(5) NOT NULL default '0',
`hr_compliance` tinyint(1) NOT NULL default '0',
`type` varchar(50) NOT NULL DEFAULT 'regular',
PRIMARY KEY (`id`),
UNIQUE (`key`)
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `announcement`(
`id` mediumint(8) unsigned NOT NULL auto_increment,
`name` varchar(100) NOT NULL,
`body` text NOT NULL,
`created_on` int(10) unsigned NOT NULL,
`last_update_on` int(10) unsigned NOT NULL,
`plan` varchar(50) default NULL,
`affiliator_id` mediumint(8) unsigned default NULL,
`interest_some_point` tinyint(1) default NULL,
`db_server` varchar(50) default NULL,
`interest_from` smallint(5) unsigned default NULL,
`interest_to` smallint(5) unsigned default NULL,
`domain_creation_interval_from` smallint(5) unsigned default NULL,
`domain_creation_interval_to` smallint(5) unsigned default NULL,
`domain_creation_from` int(10) unsigned default NULL,
`domain_creation_to` int(10) unsigned default NULL,
`last_login_from` int(10) unsigned default NULL,
`last_login_to` int(10) unsigned default NULL,
`status` enum('active', 'inactive') NOT NULL default 'inactive',
`valid_for` smallint(5) unsigned default NULL,
PRIMARY KEY (`id`),
KEY `affiliator_id` (`affiliator_id`),
CONSTRAINT `announcement_ibfk_1` FOREIGN KEY (`affiliator_id`) REFERENCES `affiliator` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `domain`(
`id` mediumint(8) unsigned NOT NULL auto_increment,
`domain` varchar(150) NOT NULL,
`first_name` varchar(150) default NULL,
`last_name` varchar(150) default NULL,
`email` varchar(150) default NULL,
`timestamp` int(10) unsigned default NULL,
`status` enum('active', 'inactive', 'deleted') NOT NULL default 'active',
`country` varchar(50) default NULL,
`first_payment_on` int(10) unsigned default NULL,
`cancelled_on` int(10) unsigned default NULL,
`last_cancelled_on` int(10) unsigned default NULL,
`subscription_cancelled_on` int(10) unsigned default NULL,
`reverse_trial_started_on` int(10) unsigned default NULL,
`reverse_trial_ended_on` int(10) unsigned default NULL,
`account_type` varchar(50) NOT NULL default 'free',
`notes` text,
`referer` varchar(2048) default NULL,
`db_host` varchar(150) default NULL,
`db_user` varchar(150) default NULL,
`db_password` varchar(150) default NULL,
`db_name` varchar(150) default NULL,
`domain_map` varchar(150) default NULL,
`domain_map_enabled` tinyint(1) NOT NULL default '0',
`affiliator_id` mediumint(8) unsigned default NULL,
`flags` int(10) unsigned default 15,
`db_version` mediumint(5) unsigned default NULL,
`generic_upgrade_version` mediumint(5) unsigned default NULL,
`stripe_connect_id` varchar(50) default NULL,
`sso_integration` text NOT NULL,
`metadata` text default NULL,
`customer_country` varchar(50) default NULL,
`customer_state` varchar(50) default NULL,
`customer_address` json default NULL,
`account_tax` decimal(5,2) default NULL,
`client_ip` varchar(50) default NULL,
`clearbit_company_domain` varchar(150) default NULL,
`clearbit_employee_range` varchar(50) default NULL,
`clearbit_annual_revenue` varchar(50) default NULL,
`clearbit_company_type` varchar(100) default NULL,
`clearbit_role` varchar(100) default NULL,
`clearbit_seniority` varchar(100) default NULL,
`referrer_id` mediumint(8) unsigned default NULL,
`referral_enabled` tinyint(1) NOT NULL default 1,
`is_sandbox` tinyint(1) NOT NULL default 0,
`demo_metadata` json default NULL,
`talent_library` tinyint(1) NOT NULL default 0,
`aws_customer_account_id` varchar(150) default NULL,
`aws_customer_id` varchar(150) default NULL,
`migration_version` bigint unsigned default 0 NOT NULL,
PRIMARY KEY (`id`),
KEY `affiliator_id` (`affiliator_id`),
UNIQUE (`domain`),
CONSTRAINT `domain_ibfk_1` FOREIGN KEY (`affiliator_id`) REFERENCES `affiliator` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
INDEX `domain_account_type_index` (`account_type`),
INDEX `domain_domain_map_index` (`domain_map`),
INDEX `domain_stripe_connect_id_index` (`stripe_connect_id`)
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `custom_plan_to_domain`(
`id` mediumint(8) unsigned NOT NULL auto_increment,
`plan_id` mediumint(8) unsigned NOT NULL,
`domain_id` mediumint(8) unsigned NOT NULL,
PRIMARY KEY (`id`),
KEY `plan_id` (`plan_id`),
KEY `domain_id` (`domain_id`),
CONSTRAINT `custom_plan_to_domain_ibfk_1` FOREIGN KEY (`plan_id`) REFERENCES `custom_plan` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
CONSTRAINT `custom_plan_to_domain_ibfk_2` FOREIGN KEY (`domain_id`) REFERENCES `domain` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `domain_stats`(
`id` mediumint(8) unsigned NOT NULL auto_increment,
`domain_id` mediumint(8) unsigned NOT NULL,
`last_login` int default NULL,
`week_logins` int default NULL,
`total_logins` int unsigned default NULL,
`users_count` int default NULL,
`inactive_users_count` int default NULL,
`courses_count` int default NULL,
`branches_count` int default NULL,
`groups_count` int default NULL,
`categories_count` int default NULL,
`automations_count` int default NULL,
`custom_report_count` int default NULL,
`total_size` int default NULL,
`total_files` int default NULL,
`default_language` varchar(100) default NULL,
`gamification` tinyint(1) NOT NULL default 0,
`gamification_points` tinyint(1) NOT NULL default 0,
`gamification_badges` tinyint(1) NOT NULL default 0,
`gamification_levels` tinyint(1) NOT NULL default 0,
`gamification_rewards` tinyint(1) NOT NULL default 0,
`gamification_leaderboard` tinyint(1) NOT NULL default 0,
`tlmsplus_enabled` tinyint(1) NOT NULL default 0,
PRIMARY KEY (`id`),
KEY `domain_id` (`domain_id`),
CONSTRAINT `domain_stats_ibfk_1` FOREIGN KEY (`domain_id`) REFERENCES `domain` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `domain_to_invoice`(
`id` mediumint(8) unsigned NOT NULL auto_increment,
`domain_id` mediumint(8) unsigned default NULL,
`invoice_id` varchar(50) NOT NULL,
`payment_date` int(10) unsigned NOT NULL,
`amount_due` float NOT NULL,
`plan` varchar(50) NOT NULL,
`period_start` int(10) unsigned NOT NULL,
`period_end` int(10) unsigned NOT NULL,
`renew` tinyint(1) NOT NULL default '0',
PRIMARY KEY (`id`),
KEY `domain_id` (`domain_id`),
CONSTRAINT `domain_to_invoice_ibfk_1` FOREIGN KEY (`domain_id`) REFERENCES `domain` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `group`(
`id` mediumint(8) unsigned NOT NULL auto_increment,
`name` varchar(150) NOT NULL,
`description` text,
`group_key` varchar(100) default NULL,
`price` float default '0',
`user_id` mediumint(8) unsigned default NULL,
`branch_id` mediumint(8) unsigned default NULL,
`max_redemptions` mediumint(5) unsigned default NULL,
`redemptions_sofar` mediumint(5) unsigned default NULL,
`auto_sync` tinyint(1) NOT NULL default 0,
`external_id` varchar(255) default NULL,
PRIMARY KEY (`id`),
KEY `user_id` (`user_id`),
CONSTRAINT `group_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `question`(
`id` mediumint(8) unsigned NOT NULL auto_increment,
`type` varchar(50) NOT NULL default 'raw_text',
`text` text NOT NULL,
`answers` text,
`correct_answers` text,
`options` text,
`course_id` mediumint(8) unsigned default NULL,
`parent_id` mediumint(8) unsigned default NULL,
`feedback` text,
`for_survey` tinyint(1) NOT NULL default '0',
`temporary` tinyint(1) NOT NULL default '0',
PRIMARY KEY (`id`),
KEY `course_id` (`course_id`),
KEY `parent_id` (`parent_id`),
CONSTRAINT `question_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `course` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
CONSTRAINT `question_ibfk_2` FOREIGN KEY (`parent_id`) REFERENCES `question` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `random_question_to_question`(
`id` mediumint(8) unsigned NOT NULL auto_increment,
`random_question_id` mediumint(8) unsigned NOT NULL,
`question_id` mediumint(8) unsigned NOT NULL,
PRIMARY KEY (`id`),
KEY `question_id` (`question_id`),
UNIQUE (`random_question_id`, `question_id`),
CONSTRAINT `random_question_to_question_ibfk_1` FOREIGN KEY (`random_question_id`) REFERENCES `question` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
CONSTRAINT `random_question_to_question_ibfk_2` FOREIGN KEY (`question_id`) REFERENCES `question` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `file_tag`(
`id` mediumint(8) unsigned NOT NULL auto_increment,
`name` varchar(255) NOT NULL,
PRIMARY KEY (`id`),
UNIQUE (`name`)
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `tag`(
`id` mediumint(8) unsigned NOT NULL auto_increment,
`name` varchar(255) NOT NULL,
PRIMARY KEY (`id`),
UNIQUE (`name`)
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `tag_to_question`(
`id` mediumint(8) unsigned NOT NULL auto_increment,
`tag_id` mediumint(8) unsigned NOT NULL,
`question_id` mediumint(8) unsigned NOT NULL,
PRIMARY KEY (`id`),
KEY `question_id` (`question_id`),
UNIQUE (`tag_id`, `question_id`),
CONSTRAINT `tag_to_question_ibfk_1` FOREIGN KEY (`tag_id`) REFERENCES `tag` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
CONSTRAINT `tag_to_question_ibfk_2` FOREIGN KEY (`question_id`) REFERENCES `question` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `scheduler_item`(
`id` mediumint(8) unsigned NOT NULL auto_increment,
`condition` varchar(200) NOT NULL,
`name` varchar(200) NOT NULL,
`created_on` int(10) unsigned NOT NULL,
`receiver` smallint(5) unsigned NOT NULL,
`timing_type` tinyint(3) unsigned NOT NULL,
`timing_value` mediumint(8) unsigned NOT NULL,
`arguments` text,
`status` enum('inactive', 'active') NOT NULL default 'active',
`event` varchar(100) NOT NULL,
`repeat` tinyint(1) unsigned default '0',
`parent_id` mediumint(8) unsigned default NULL,
`repeat_days` smallint(5) unsigned default NULL,
`repeat_times` smallint(5) unsigned default NULL,
PRIMARY KEY (`id`)
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `theme`(
`id` mediumint(8) unsigned NOT NULL auto_increment,
`name` varchar(100) NOT NULL,
`options` text NOT NULL,
`css` longtext NOT NULL,
`custom_css` longtext NOT NULL,
`custom_js` longtext default NULL,
`plus_theme_id` varchar(50) NOT NULL default '',
`parent_id` mediumint(8) unsigned DEFAULT NULL,
`branch_id` mediumint(8) unsigned DEFAULT NULL,
PRIMARY KEY (`id`),
UNIQUE `name_plus_them_id_idx`(`name`, `plus_theme_id`)
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `unit_content`(
`id` mediumint(8) unsigned NOT NULL auto_increment,
`version` varchar(255) default NULL,
`timestamp` int(10) unsigned default NULL,
`data` longtext,
`secondary_data` longtext,
`file_id` int(11) default NULL,
`url` VARCHAR(2048) default NULL,
PRIMARY KEY (`id`)
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `unit_scorm_report` (
`id` mediumint(8) unsigned NOT NULL auto_increment,
`data` longtext,
PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `unit`(
`id` mediumint(8) unsigned NOT NULL auto_increment,
`name` varchar(255) NOT NULL,
`course_id` mediumint(8) unsigned default NULL,
`type` enum('Unit', 'Document', 'Video', 'Scorm', 'Webpage', 'Test', 'Survey', 'Audio', 'Flash', 'IFrame', 'Assignment', 'Ilt', 'Section', 'Craft', 'Lti') NOT NULL default 'Unit',
`options` text,
`unit_metadata` json DEFAULT NULL,
`unit_content_id` mediumint(8) unsigned default NULL,
`scorm_report_id` mediumint(8) unsigned default NULL,
`draft_content_id` mediumint(8) unsigned default NULL,
`previous_id` mediumint(8) unsigned default NULL,
`linked_unit_id` mediumint(8) unsigned default NULL,
`completion` enum('browse', 'checkbox', 'question', 'time', 'upload', 'accept', 'session_attended', 'session_ended', 'other') NOT NULL default 'browse',
`completion_options` varchar(255) default NULL,
`status` enum('inactive', 'active') NOT NULL default 'active',
`maxtimeallowed` mediumint default NULL,
`timelimitaction` enum('exit,message', 'exit,no message', 'continue,message', 'continue,no message') NOT NULL default 'exit,message',
`completion_threshold` float default NULL,
`temporary` tinyint default 0,
`delay_time` int(10) unsigned default NULL,
`estimated_time` mediumint(8) unsigned default NULL,
`is_manual_time` tinyint(1) unsigned default '0',
`marketplace_course` tinyint(1) unsigned default '0',
`sample` tinyint(1) unsigned default '0',
`created_in_plus` tinyint(1) unsigned NOT NULL default '0',
`due_datetime` int(10) unsigned default NULL,
`open_sesame_id` varchar (36) default NULL,
`ai_jobs` json DEFAULT NULL,
`lti_registration_id` int unsigned DEFAULT NULL,
PRIMARY KEY (`id`),
KEY `course_id` (`course_id`),
KEY `previous_id` (`previous_id`),
KEY `unit_content_id` (`unit_content_id`),
KEY `draft_content_id` (`draft_content_id`),
KEY `linked_unit_id` (`linked_unit_id`),
KEY `scorm_report_id` (`scorm_report_id`),
CONSTRAINT `unit_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `course` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
CONSTRAINT `unit_ibfk_3` FOREIGN KEY (`previous_id`) REFERENCES `unit` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
CONSTRAINT `unit_ibfk_4` FOREIGN KEY (`unit_content_id`) REFERENCES `unit_content` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
CONSTRAINT `unit_ibfk_5` FOREIGN KEY (`draft_content_id`) REFERENCES `unit_content` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
CONSTRAINT `unit_ibfk_6` FOREIGN KEY (`linked_unit_id`) REFERENCES `unit` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
CONSTRAINT `unit_ibfk_7` FOREIGN KEY (`scorm_report_id`) REFERENCES `unit_scorm_report` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `course_progress`(
`id` int(10) unsigned NOT NULL auto_increment,
`user_id` mediumint(8) unsigned default NULL,
`course_id` mediumint(8) unsigned default NULL,
`archive` int(10) unsigned NOT NULL default '0',
`total_time` mediumint(8) unsigned default NULL,
`comments` text default NULL,
`score` float default NULL,
`average_score` float default NULL,
`completion_status` enum('not_attempted', 'incomplete', 'completed', 'failed', 'unknown') default 'not_attempted',
`completion_date` int(10) unsigned default NULL,
`completion_percentage` float default 0,
`credit` enum('credit', 'no-credit') default 'credit',
`location` mediumint(8) unsigned default NULL,
PRIMARY KEY (`id`),
KEY `course_id` (`course_id`),
UNIQUE (`user_id`, `course_id`, `archive`),
UNIQUE `custom_report_idx` (`user_id`, `course_id`, `archive`, `completion_status`, `completion_date`, `credit`) USING BTREE,
CONSTRAINT `course_progress_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
CONSTRAINT `course_progress_ibfk_2` FOREIGN KEY (`course_id`) REFERENCES `course` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
CONSTRAINT `course_progress_ibfk_3` FOREIGN KEY (`location`) REFERENCES `unit` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `unit_progress`(
`id` int(10) unsigned NOT NULL auto_increment,
`course_progress_id` int(10) unsigned NOT NULL,
`unit_id` mediumint(8) unsigned NOT NULL,
`archive` int(10) unsigned NOT NULL default '0',
`lesson_location` text,
`datafromlms` text,
`entry` varchar(255) default NULL,
`total_time` mediumint(8) unsigned default NULL,
`comments` text default NULL,
`comments_from_lms` text,
`lesson_status` varchar(255) default NULL,
`score` float default NULL,
`score_sofar` float default NULL,
`scorm_exit` varchar(255) default NULL,
`suspend_data` text,
`completion_status` enum('not_attempted', 'incomplete', 'completed', 'failed', 'pending', 'unknown') default 'not_attempted',
`completion_date` int(10) unsigned default NULL,
`completed_as_learner` tinyint(1) default NULL,
`score_scaled` varchar(255) default NULL,
`success_status` varchar(255) default NULL,
`progress_measure` varchar(255) default NULL,
`credit` enum('credit', 'no-credit') default 'credit',
`first_access_time` int(10) unsigned default NULL,
`last_access_time` int(10) unsigned default NULL,
`last_access_action` enum('entry', 'exit') default 'exit',
`first_attempt` tinyint(1) NOT NULL default '0',
`interactions` longtext,
`assignment_reply_type` enum('file', 'textbox'),
`assignment_reply` longtext default NULL,
`grade_date` int(10) unsigned default NULL,
`test_evidence` varchar(255) default NULL,
`options` text,
PRIMARY KEY (`id`),
KEY `unit_id` (`unit_id`),
UNIQUE (`course_progress_id`, `unit_id`, `archive`),
CONSTRAINT `unit_progress_ibfk_1` FOREIGN KEY (`course_progress_id`) REFERENCES `course_progress` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
CONSTRAINT `unit_progress_ibfk_2` FOREIGN KEY (`unit_id`) REFERENCES `unit` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `unit_to_question`(
`id` mediumint(8) unsigned NOT NULL auto_increment,
`unit_id` mediumint(8) unsigned NOT NULL,
`question_id` mediumint(8) unsigned NOT NULL,
`previous_id` mediumint(8) unsigned default NULL,
`weight` smallint(3) unsigned NOT NULL default 1,
`appearances` smallint(3) unsigned NOT NULL default 1,
PRIMARY KEY (`id`),
KEY `question_id` (`question_id`),
KEY `previous_id` (`previous_id`),
UNIQUE (`unit_id`, `question_id`),
CONSTRAINT `unit_to_question_ibfk_1` FOREIGN KEY (`unit_id`) REFERENCES `unit` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
CONSTRAINT `unit_to_question_ibfk_2` FOREIGN KEY (`question_id`) REFERENCES `question` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
CONSTRAINT `unit_to_question_ibfk_4` FOREIGN KEY (`previous_id`) REFERENCES `question` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `rule`(
`id` mediumint(8) unsigned NOT NULL auto_increment,
`course_id` mediumint(8) unsigned NOT NULL,
`rule_type` varchar(255) default NULL,
`unit_id` mediumint(8) unsigned default NULL,
`rule_course_id` mediumint(8) unsigned default NULL,
`completion_percentage` float default NULL,
`rule_set` int default NULL,
`unit_weight` int default NULL,
PRIMARY KEY (`id`),
KEY `course_id` (`course_id`),
KEY `unit_id` (`unit_id`),
KEY `rule_course_id` (`rule_course_id`),
CONSTRAINT `rule_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `course` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
CONSTRAINT `rule_ibfk_2` FOREIGN KEY (`unit_id`) REFERENCES `unit` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
CONSTRAINT `rule_ibfk_3` FOREIGN KEY (`rule_course_id`) REFERENCES `course` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `user_online`(
`id` mediumint(8) unsigned NOT NULL auto_increment,
`user_id` mediumint(8) unsigned NOT NULL,
`timestamp` int(10) unsigned NOT NULL,
`session_id` varchar(200) default NULL,
`sso_session_id` varchar(200) DEFAULT NULL,
PRIMARY KEY (`id`),
KEY `user_id` (`user_id`),
CONSTRAINT `user_online_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `branch`(
`id` mediumint(8) unsigned NOT NULL auto_increment,
`name` varchar(200) NOT NULL default 'New Branch',
`title` varchar(200) default NULL,
`description` text,
`homepage` tinyint(1) NOT NULL default '0',
`homepage_plus` tinyint(1) NOT NULL default '0',
`avatar` varchar(255) default NULL,
`avatar_big` varchar(255) default NULL,
`avatar_x4` varchar(255) default NULL,
`theme_id` mediumint(8) unsigned default NULL,
`plus_theme_id` varchar(50) default NULL,
`badge_set_id` smallint(5) unsigned NOT NULL default 1,
`timezone` varchar(100) NOT NULL default '',
`status` enum('active', 'inactive') NOT NULL default 'active',
`language` varchar(100) NOT NULL default 'en',
`group_id` mediumint(8) unsigned default NULL,
`user_type_id` smallint(5) unsigned default NULL,
`disallow_global_login` tinyint(1) NOT NULL default '0',
`registration_email_restriction` text default NULL,
`users_limit` mediumint(8) unsigned default NULL,
`payment_processor` varchar(50) default NULL,
`currency` varchar(30) NOT NULL,
`paypal_email` varchar(150) default NULL,
`ecommerce_subscription` tinyint(1) unsigned default '0',
`ecommerce_subscription_price` float default '0',
`ecommerce_subscription_plan` varchar(100) default NULL,
`ecommerce_subscription_interval` smallint(3) unsigned default NULL,
`ecommerce_subscription_trial_period` tinyint(1) unsigned default '0',
`ecommerce_credits` tinyint(1) NOT NULL default '0',
`signup_method` varchar(30) NOT NULL default '',
`license_note` text default NULL,
`internal_announcement` text default NULL,
`external_announcement` text default NULL,
`sso_integration` text default NULL,
`favicon` varchar(255) default NULL,
`ai_features_enabled` tinyint(1) NOT NULL default '1',
`default_course_avatar` varchar(255) default NULL,
`default_course_avatar_big` varchar(255) default NULL,
`default_course_avatar_x4` varchar(255) default NULL,
`integrations_settings` JSON default NULL,
PRIMARY KEY (`id`),
KEY `theme_id` (`theme_id`),
KEY `group_id` (`group_id`),
KEY `user_type_id` (`user_type_id`),
KEY `plus_theme_id` (`plus_theme_id`),
UNIQUE (`name`),
CONSTRAINT `branch_ibfk_3` FOREIGN KEY (`theme_id`) REFERENCES `theme` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
CONSTRAINT `branch_ibfk_4` FOREIGN KEY (`group_id`) REFERENCES `group` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
CONSTRAINT `branch_ibfk_5` FOREIGN KEY (`user_type_id`) REFERENCES `acl_user_type` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `file`(
`id` int(11) NOT NULL auto_increment,
`name` varchar(255) NOT NULL,
`nameS3` varchar(1024) NOT NULL,
`size` int(11) unsigned NOT NULL,
`type` varchar(100) NOT NULL,
`acl` varchar(20) NOT NULL,
`course_id` mediumint(9) unsigned,
`original_course_id` mediumint(9) unsigned,
`uploaded_on` int(10) unsigned,
`shared` tinyint(1) unsigned default '0',
`status` enum('ready', 'processing', 'failed') NOT NULL default 'ready',
`task_id` varchar(255) default NULL,
`original_nameS3` varchar(255) default NULL,
`converted_document_pathS3` varchar(1024) default NULL,
`api_task_id` varchar(100) default NULL,
`hash` char(40) default NULL,
`visible` tinyint(1) NOT NULL default '1',
`deleted` tinyint(1) NOT NULL default '0',
`from_marketplace` tinyint(1) NOT NULL default '0',
`file_type` enum('profile', 'group', 'branch') default NULL,
`user_id` mediumint(8) unsigned default NULL,
`group_id` mediumint(8) unsigned default NULL,
`branch_id` mediumint(8) unsigned default NULL,
`options` text default NULL,
`open_sesame_id` varchar (36) default NULL,
PRIMARY KEY (`id`),
KEY `user_id` (`user_id`),
KEY `group_id` (`group_id`),
KEY `branch_id` (`branch_id`),
KEY `nameS3` (`nameS3`),
CONSTRAINT `file_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `course` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
CONSTRAINT `file_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
CONSTRAINT `file_ibfk_3` FOREIGN KEY (`group_id`) REFERENCES `group` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
CONSTRAINT `file_ibfk_4` FOREIGN KEY (`branch_id`) REFERENCES `branch` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `tag_to_file`(
`id` mediumint(8) unsigned NOT NULL auto_increment,
`tag_id` mediumint(8) unsigned NOT NULL,
`file_id` int(11) NOT NULL,
PRIMARY KEY (`id`),
KEY `file_id` (`file_id`),
UNIQUE (`tag_id`, `file_id`),
CONSTRAINT `tag_to_file_ibfk_1` FOREIGN KEY (`tag_id`) REFERENCES `file_tag` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
CONSTRAINT `tag_to_file_ibfk_2` FOREIGN KEY (`file_id`) REFERENCES `file` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `personal_message`(
`id` mediumint(8) unsigned NOT NULL auto_increment,
`user_id` mediumint(8) unsigned NOT NULL,
`sender_id` mediumint(8) unsigned default NULL,
`recipients` text default NULL,
`timestamp` int(10) unsigned NOT NULL default '0',
`title` varchar(250) NOT NULL default '',
`body` text NOT NULL,
`bcc` tinyint(3) unsigned NOT NULL default '0',
`viewed` tinyint(3) unsigned NOT NULL default '0',
`self` tinyint(3) unsigned NOT NULL default '0',
`attachment_id` int(11) default NULL,
PRIMARY KEY (`id`),
KEY `user_id` (`user_id`),
KEY `sender_id` (`sender_id`),
KEY `attachment_id` (`attachment_id`),
CONSTRAINT `personal_message_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
CONSTRAINT `personal_message_ibfk_2` FOREIGN KEY (`sender_id`) REFERENCES `user` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
CONSTRAINT `personal_message_ibfk_3` FOREIGN KEY (`attachment_id`) REFERENCES `file` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `user_to_branch`(
`id` mediumint(8) unsigned NOT NULL auto_increment,
`user_id` mediumint(8) unsigned NOT NULL,
`branch_id` mediumint(8) unsigned NOT NULL,
`assigned_on` int(10) unsigned default NULL,
`last_login` int(10) unsigned default NULL,
`viewed_license` tinyint(1) NOT NULL default '0',
`viewed_announcement` tinyint(1) NOT NULL default '0',
PRIMARY KEY (`id`),
KEY `branch_id` (`branch_id`),
UNIQUE (`user_id`, `branch_id`),
CONSTRAINT `user_to_branch_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
CONSTRAINT `user_to_branch_ibfk_2` FOREIGN KEY (`branch_id`) REFERENCES `branch` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `user_to_certification`(
`id` mediumint(8) unsigned NOT NULL auto_increment,
`user_id` mediumint(8) unsigned NOT NULL,
`certification_id` mediumint(8) unsigned NOT NULL,
`course_id` mediumint(8) unsigned,
`learning_path_id` mediumint(8) unsigned,
`unique_id` varchar(40) NOT NULL,
`issued_date` int(10) unsigned default NULL,
`last_issued_date` int(10) unsigned default NULL,
`expiration_date` int(10) unsigned default NULL,
`archived` int(10) unsigned NOT NULL default '0',
`synchronize` tinyint(1) unsigned NOT NULL default '0',
`course_name` varchar(150) NULL,
`course_code` varchar(20) NULL,
`learning_path_name` varchar(150) NULL,
`learning_path_code` varchar(20) NULL,
`average_score` float default NULL,
`first_name` varchar(100) default NULL,
`surname` varchar(100) default NULL,
`email` varchar(150) default NULL,
`instructor_first_name` varchar(100) default NULL,
`instructor_last_name` varchar(100) default NULL,
`custom_fields` text default NULL,
`nameS3` varchar(255) default NULL,
`options` text default NULL,
PRIMARY KEY (`id`),
KEY `user_id` (`user_id`),
KEY `certification_id` (`certification_id`),
KEY `course_id` (`course_id`),
KEY `learning_path_id` (`learning_path_id`),
UNIQUE (`unique_id`),
CONSTRAINT `user_to_certification_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
CONSTRAINT `user_to_certification_ibfk_2` FOREIGN KEY (`certification_id`) REFERENCES `certification` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
CONSTRAINT `user_to_certification_ibfk_3` FOREIGN KEY (`course_id`) REFERENCES `course` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
CONSTRAINT `user_to_certification_ibfk_4` FOREIGN KEY (`learning_path_id`) REFERENCES `learning_path` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `user_to_course`(
`id` int(10) unsigned NOT NULL auto_increment,
`user_id` mediumint(8) unsigned NOT NULL,
`course_id` mediumint(8) unsigned NOT NULL,
`course_role` enum('trainer', 'learner') NOT NULL default 'learner',
`status` enum('active', 'expired', 'cancelled') NOT NULL default 'active',
`enrolled_on` int(10) unsigned NOT NULL default '0',
`expiration_date` int(10) unsigned default NULL,
`course_rating` float(4,3) default NULL,
`last_access_time` int(10) unsigned default NULL,
`from_learning_path` tinyint(1) default 0,
PRIMARY KEY (`id`),
KEY `course_id` (`course_id`),
UNIQUE (`user_id`, `course_id`),
CONSTRAINT `user_to_course_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
CONSTRAINT `user_to_course_ibfk_2` FOREIGN KEY (`course_id`) REFERENCES `course` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `user_to_group`(
`id` mediumint(8) unsigned NOT NULL auto_increment,
`user_id` mediumint(8) unsigned NOT NULL,
`group_id` mediumint(8) unsigned NOT NULL,
PRIMARY KEY (`id`),
KEY `group_id` (`group_id`),
UNIQUE (`user_id`, `group_id`),
CONSTRAINT `user_to_group_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
CONSTRAINT `user_to_group_ibfk_2` FOREIGN KEY (`group_id`) REFERENCES `group` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `user_to_subscription`(
`id` mediumint(8) unsigned NOT NULL auto_increment,
`user_id` mediumint(8) unsigned NOT NULL,
`branch_id` mediumint(8) unsigned default NULL,
`subscription_id` varchar(30) NOT NULL,
`plan_id` varchar(100) NOT NULL,
`plan_interval` smallint(3) unsigned NOT NULL default 1,
`status` enum('active', 'cancelled') NOT NULL default 'active',
`started_on` int(10) unsigned NOT NULL,
`cancelled_on` int(10) unsigned default NULL,
`amount` float NOT NULL,
`currency` varchar(30) NOT NULL,
`courses` text NOT NULL,
PRIMARY KEY (`id`),
KEY `user_id` (`user_id`),
KEY `branch_id` (`branch_id`),
CONSTRAINT `user_to_subscription_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
CONSTRAINT `user_to_subscription_ibfk_2` FOREIGN KEY (`branch_id`) REFERENCES `branch` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `completed_question`(
`id` int(10) unsigned NOT NULL auto_increment,
`type` varchar(50) NOT NULL default 'raw_text',
`text` text NOT NULL,
`answers` text,
`correct_answers` text,
`user_answers` text,
`correct` tinyint(1) default NULL,
`options` text,
`course_id` mediumint(8) unsigned default NULL,
`parent_id` int(10) unsigned default NULL,
`feedback` text,
`question_id` mediumint(8) unsigned default NULL,
`for_survey` tinyint(1) NOT NULL default '0',
`temporary` tinyint(1) NOT NULL default '0',
PRIMARY KEY (`id`),
KEY `question_id` (`question_id`),
KEY `course_id` (`course_id`),
KEY `parent_id` (`parent_id`),
CONSTRAINT `completed_question_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `course` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
CONSTRAINT `completed_question_ibfk_2` FOREIGN KEY (`question_id`) REFERENCES `question` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
CONSTRAINT `completed_question_ibfk_3` FOREIGN KEY (`parent_id`) REFERENCES `completed_question` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `completed_unit`(
`id` int(10) unsigned NOT NULL auto_increment,
`name` varchar(255) NOT NULL,
`type` enum('Unit', 'Document', 'Video', 'Scorm', 'Webpage', 'Test', 'Survey', 'Audio', 'Flash', 'IFrame', 'Assignment', 'Ilt', 'Section','Craft', 'Lti') NOT NULL default 'Unit',
`options` text,
`unit_content_id` mediumint(8) unsigned default NULL,
`completion` enum('browse', 'checkbox', 'question', 'time', 'upload', 'accept', 'session_attended', 'session_ended', 'other') NOT NULL default 'browse',
`completion_options` varchar(255) default NULL,
`maxtimeallowed` mediumint default NULL,
`timelimitaction` enum('exit,message', 'exit,no message', 'continue,message', 'continue,no message') NOT NULL default 'exit,message',
`completion_threshold` float default NULL,
`unit_id` mediumint(8) unsigned default NULL,
`unit_progress_id` int(10) unsigned NOT NULL,
PRIMARY KEY (`id`),
KEY `unit_id` (`unit_id`),
KEY `unit_content_id` (`unit_content_id`),
UNIQUE (`unit_progress_id`),
CONSTRAINT `completed_unit_ibfk_1` FOREIGN KEY (`unit_content_id`) REFERENCES `unit_content` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
CONSTRAINT `completed_unit_ibfk_2` FOREIGN KEY (`unit_progress_id`) REFERENCES `unit_progress` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
CONSTRAINT `completed_unit_ibfk_3` FOREIGN KEY (`unit_id`) REFERENCES `unit` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `completed_unit_to_question`(
`id` int(10) unsigned NOT NULL auto_increment,
`completed_unit_id` int(10) unsigned NOT NULL,
`completed_question_id` int(10) unsigned NOT NULL,
`previous_id` int(10) unsigned default NULL,
`weight` smallint(3) unsigned NOT NULL default 1,
`appearances` smallint(3) unsigned NOT NULL default 1,
PRIMARY KEY (`id`),
KEY `completed_question_id` (`completed_question_id`),
KEY `previous_id` (`previous_id`),
UNIQUE (`completed_unit_id`, `completed_question_id`),
CONSTRAINT `completed_unit_to_question_ibfk_1` FOREIGN KEY (`completed_unit_id`) REFERENCES `completed_unit` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
CONSTRAINT `completed_unit_to_question_ibfk_2` FOREIGN KEY (`completed_question_id`) REFERENCES `completed_question` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
CONSTRAINT `completed_unit_to_question_ibfk_4` FOREIGN KEY (`previous_id`) REFERENCES `completed_question` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `core_log`(
`id` int(10) unsigned NOT NULL auto_increment,
`message` varchar(255) default NULL,
`user_id` mediumint(8) unsigned default NULL,
`course_id` mediumint(8) unsigned default NULL,
`learning_path_id` mediumint(8) unsigned default NULL,
`event_type` varchar(255) default NULL,
`timestamp` int(10) unsigned NOT NULL,
`first_timestamp` int(10) unsigned NOT NULL,
`count` smallint(3) unsigned NOT NULL default 1,
`entity_id` mediumint(8) unsigned default NULL,
`arguments` text,
PRIMARY KEY (`id`),
KEY `user_id` (`user_id`),
KEY `course_id` (`course_id`),
CONSTRAINT `core_log_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
CONSTRAINT `core_log_ibfk_2` FOREIGN KEY (`course_id`) REFERENCES `course` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
CONSTRAINT `core_log_ibfk_3` FOREIGN KEY (`learning_path_id`) REFERENCES `learning_path` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
INDEX `core_log_event_type_index` (`event_type`),
INDEX `core_log_timestamp_index` (`timestamp`),
INDEX `core_log_event_id_type_index` (`entity_id`, `event_type`)
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `timeline_login`(
`id` int(10) unsigned NOT NULL auto_increment,
`user_id` mediumint(8) unsigned default NULL,
`timestamp` int(10) unsigned NOT NULL,
`first_timestamp` int(10) unsigned NOT NULL,
`count` smallint(3) unsigned NOT NULL default 1,
`arguments` text,
PRIMARY KEY (`id`),
KEY `user_id` (`user_id`),
CONSTRAINT `timeline_login_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
INDEX `timeline_login_timestamp_index` (`timestamp`)
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `timeline_course_completion`(
`id` int(10) unsigned NOT NULL auto_increment,
`user_id` mediumint(8) unsigned default NULL,
`course_id` mediumint(8) unsigned default NULL,
`timestamp` int(10) unsigned NOT NULL,
`arguments` text,
PRIMARY KEY (`id`),
KEY `user_id` (`user_id`),
KEY `course_id` (`course_id`),
CONSTRAINT `timeline_course_completion_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
CONSTRAINT `timeline_course_completion_ibfk_2` FOREIGN KEY (`course_id`) REFERENCES `course` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
INDEX `timeline_course_completion_timestamp_index` (`timestamp`)
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `timeline_learning_path_completion`(
`id` int(10) unsigned NOT NULL auto_increment,
`user_id` mediumint(8) unsigned default NULL,
`learning_path_id` mediumint(8) unsigned default NULL,
`timestamp` int(10) unsigned NOT NULL,
`arguments` text,
PRIMARY KEY (`id`),
KEY `user_id` (`user_id`),
KEY `learning_path_id` (`learning_path_id`),
CONSTRAINT `timeline_learning_path_completion_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
CONSTRAINT `timeline_learning_path_completion_ibfk_2` FOREIGN KEY (`learning_path_id`) REFERENCES `learning_path` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
INDEX `timeline_learning_path_completion_timestamp_index` (`timestamp`)
)ENGINE = InnoDB DEFAULT CHARSET = utf8;

CREATE TABLE `course_to_branch`(
`id` mediumint(8) unsigned NOT NULL auto_increment,
`course_id` mediumint(8) unsigned NOT NULL,
`branch_id` mediumint(8) unsigned NOT NULL,
PRIMARY KEY (`id`),
KEY `branch_id` (`branch_id`),
UNIQUE (`course_id`, `branch_id`),
CONSTRAINT `course_to_branch_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `course` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
CONSTRAINT `course_to_branch_ibfk_2` FOREIGN KEY (`branch_id`) REFERENCES `branch` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `course_to_group`(
`id` mediumint(8) unsigned NOT NULL auto_increment,
`course_id` mediumint(8) unsigned NOT NULL,
`group_id` mediumint(8) unsigned NOT NULL,
PRIMARY KEY (`id`),
KEY `group_id` (`group_id`),
UNIQUE (`course_id`, `group_id`),
CONSTRAINT `course_to_group_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `course` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
CONSTRAINT `course_to_group_ibfk_2` FOREIGN KEY (`group_id`) REFERENCES `group` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `conference`(
`id` varchar(255) NOT NULL,
`name` varchar(255),
`welcome` text,
`logoutURL` varchar(255),
`duration` mediumint(8) unsigned,
`start_datetime` int(10) unsigned NOT NULL,
`type` enum('html5_bigbluebutton', 'custom_bigbluebutton', 'gotomeeting', 'gotowebinar', 'gototraining', 'zoom', 'zoom_webinar', 'msteams') NOT NULL default 'html5_bigbluebutton',
`goto_webinar_key` varchar(255) default NULL,
`goto_meeting_id` varchar(255) default NULL,
`join_url` varchar(255) default NULL,
`start_url` text default NULL,
`max_participants` mediumint(8) unsigned default NULL,
`has_started` tinyint(1) unsigned default '0',
`visible` tinyint(1) unsigned NOT NULL default '1',
`recording` tinyint(1) unsigned NOT NULL default '0',
`metadata` text default NULL,
PRIMARY KEY (`id`)
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `user_to_conference`(
`id` mediumint(8) unsigned NOT NULL auto_increment,
`user_id` mediumint(8) unsigned NOT NULL,
`conference_id` varchar(255) NOT NULL,
`role` enum('moderator', 'attendee') NOT NULL default 'attendee',
`joined` tinyint(1) unsigned NOT NULL default '0',
`gotowebinar_registrant_key` varchar(255) default NULL,
`gotowebinar_join_url` varchar(255) default NULL,
PRIMARY KEY (`id`),
KEY `conference_id` (`conference_id`),
UNIQUE (`user_id`, `conference_id`),
CONSTRAINT `user_to_conference_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
CONSTRAINT `user_to_conference_ibfk_2` FOREIGN KEY (`conference_id`) REFERENCES `conference` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `conference_log`(
`id` mediumint(8) unsigned NOT NULL auto_increment,
`domain_id` mediumint(8) unsigned NOT NULL,
`conf_id`varchar(255) NOT NULL,
`duration` mediumint(8) unsigned,
`start_datetime` int(10) unsigned NOT NULL,
`type` enum('html5_bigbluebutton', 'custom_bigbluebutton', 'gotomeeting', 'gotowebinar', 'gototraining', 'zoom', 'zoom_webinar', 'msteams') default NULL,
PRIMARY KEY (`id`),
UNIQUE (`domain_id`, `conf_id`),
CONSTRAINT `conference_log_ibfk_1` FOREIGN KEY (`domain_id`) REFERENCES `domain` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `notification_settings`(
`history_size` smallint(5) unsigned NOT NULL COMMENT 'up to 65535',
`notifications_per_cycle` tinyint(3) unsigned NOT NULL COMMENT 'up to 255',
`defer_time` tinyint(3) unsigned NOT NULL COMMENT 'The time that the sending of a failed notification will be postponed for in hours - up to 255'
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `notification_queue`(
`id` mediumint(8) unsigned NOT NULL auto_increment,
`timestamp` int(10) NOT NULL,
`from` varchar(255) default 'noreply@talentlms.com',
`recipient` varchar(255) default NULL,
`bcc` varchar(255) default NULL,
`subject` varchar(255) NOT NULL,
`body` text,
`priority` tinyint(3) unsigned default '0',
`stamp` char(32) comment 'Used to keep track of notifications being processed in a cron cycle.',
`visible` tinyint(1) NOT NULL default '1',
`send_now` tinyint(1) NOT NULL default '1',
PRIMARY KEY (`id`)
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `notification_sent`(
`id` mediumint(8) unsigned NOT NULL auto_increment,
`timestamp` int(10) NOT NULL,
`recipient` varchar(255) default NULL,
`subject` varchar(255) NOT NULL,
`body` text,
PRIMARY KEY (`id`)
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `domain_to_affiliator`(
`id` mediumint(8) unsigned NOT NULL auto_increment,
`domain_id` mediumint(8) unsigned default NULL,
`affiliator_id` mediumint(8) unsigned default NULL,
`invoice_id` varchar(50) NOT NULL,
`payment_date` int(10) unsigned NOT NULL,
`payment_status` tinyint(1) NOT NULL default '0',
`amount_due` float NOT NULL,
`commision` float NOT NULL,
`renew` tinyint(1) NOT NULL default '0',
`invoice_sent` timestamp NULL default NULL,
PRIMARY KEY (`id`),
KEY `domain_id` (`domain_id`),
KEY `affiliator_id` (`affiliator_id`),
CONSTRAINT `domain_to_affiliator_ibfk_1` FOREIGN KEY (`domain_id`) REFERENCES `domain` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
CONSTRAINT `domain_to_affiliator_ibfk_2` FOREIGN KEY (`affiliator_id`) REFERENCES `affiliator` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `affiliator_payment`(
`id` mediumint(8) unsigned NOT NULL auto_increment,
`affiliator_id` mediumint(8) unsigned default NULL,
`payment_date` int(10) unsigned NOT NULL,
`amount` float NOT NULL,
PRIMARY KEY (`id`),
KEY `affiliator_id` (`affiliator_id`),
CONSTRAINT `affiliator_payment_ibfk_1` FOREIGN KEY (`affiliator_id`) REFERENCES `affiliator` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `affiliate_visitor`(
`id` bigint(20) unsigned NOT NULL auto_increment,
`affiliate_key` varchar(100) NOT NULL,
`timestamp` int(10) unsigned NOT NULL,
PRIMARY KEY (`id`)
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `tincan_log2`(
`id` int(11) unsigned NOT NULL auto_increment,
`statement_id` varchar(255) NOT NULL,
`user_id` mediumint(8) unsigned NOT NULL,
`verb` varchar(255) NOT NULL,
`object` varchar(255) default NULL,
`result` varchar(255) default NULL,
`context` varchar(255) default NULL,
`authority` varchar(255) default NULL,
`voided` tinyint(1) NOT NULL default '0',
`timestamp` int(10) unsigned,
`stored` int(10) unsigned,
`event_type` varchar(255) default NULL,
`course_id` mediumint(8) unsigned default NULL,
`arguments` text,
PRIMARY KEY (`id`),
KEY `user_id` (`user_id`),
KEY `course_id` (`course_id`),
CONSTRAINT `tincan_log2_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
CONSTRAINT `tincan_log2_ibfk_2` FOREIGN KEY (`course_id`) REFERENCES `course` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `tincan_activity`(
`id` mediumint(8) unsigned NOT NULL auto_increment,
`activity_id` varchar(255) NOT NULL,
`type` varchar(255) NOT NULL,
`name` varchar(255) NOT NULL,
`description` text default NULL,
`root_id` mediumint(8) unsigned default NULL,
`url` varchar(255) default NULL,
`launch_method` varchar(50) default NULL,
`move_on` varchar(50) default NULL,
`mastery_score` varchar(50) default NULL,
PRIMARY KEY (`id`),
CONSTRAINT `tincan_ibfk_1` FOREIGN KEY (`root_id`) REFERENCES `tincan_activity` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `tincan_state`(
`id` int(11) unsigned NOT NULL auto_increment,
`user_id` mediumint(8) unsigned NOT NULL,
`unit_id` mediumint(8) unsigned NOT NULL,
`activity_id` varchar(255) NOT NULL,
`state_id` varchar(255) DEFAULT NULL,
`content` text NOT NULL,
`content_type` VARCHAR(255) DEFAULT NULL,
PRIMARY KEY (`id`),
KEY `unit_id` (`unit_id`),
UNIQUE KEY `tincan_state_unique_1` (`user_id`, `unit_id`, `state_id`),
CONSTRAINT `tincan_state_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
CONSTRAINT `tincan_state_ibfk_2` FOREIGN KEY (`unit_id`) REFERENCES `unit` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `payments_coupon`(
`id` mediumint(8) unsigned NOT NULL auto_increment,
`code` varchar(50) NOT NULL,
`valid_from` int(10) unsigned NOT NULL,
`valid_to` int(10) unsigned NOT NULL,
`percentage_discount` float default NULL,
`fixed_amount_discount` float default NULL,
`discount_currency` varchar(30) default NULL,
`max_redemptions` mediumint(5) default NULL,
`redemptions_sofar` mediumint(5) default NULL,
`stripe_connected` tinyint(1) NOT NULL default '0',
`courses` text default NULL,
`groups` text default NULL,
`categories` text default NULL,
PRIMARY KEY (`id`),
UNIQUE (`code`)
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `invoice`(
`id` mediumint(8) unsigned NOT NULL auto_increment,
`user_id` mediumint(8) unsigned NOT NULL,
`course_id` mediumint(8) unsigned default NULL,
`category_id` smallint(5) unsigned default NULL,
`group_id` mediumint(8) unsigned default NULL,
`unique_id` varchar(40) NOT NULL,
`charge_id` varchar(40) default NULL,
`description` varchar(200) NOT NULL,
`timestamp` int(10) unsigned NOT NULL,
`period_start` int(10) unsigned default NULL,
`period_end` int(10) unsigned default NULL,
`status` varchar(30) NOT NULL,
`bundle` tinyint(1) NOT NULL default '0',
`subscription` tinyint(1) NOT NULL default '0',
`paid_with_credits` tinyint(1) NOT NULL default '0',
`item_price` float NOT NULL,
`amount` float NOT NULL,
`currency` varchar(30) NOT NULL,
`payment_processor` varchar(50) default NULL,
`coupon_code` varchar(50) default NULL,
`coupon_percentage_discount` float default NULL,
`coupon_fixed_amount_discount` float default NULL,
`global_discount` float default NULL,
`rewards_discount` float default NULL,
PRIMARY KEY (`id`),
KEY `user_id` (`user_id`),
KEY `course_id` (`course_id`),
KEY `category_id` (`category_id`),
UNIQUE (`unique_id`),
CONSTRAINT `invoice_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
CONSTRAINT `invoice_ibfk_2` FOREIGN KEY (`course_id`) REFERENCES `course` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
CONSTRAINT `invoice_ibfk_3` FOREIGN KEY (`category_id`) REFERENCES `category` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
CONSTRAINT `invoice_ibfk_4` FOREIGN KEY (`group_id`) REFERENCES `group` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `discussion`(
`id` mediumint(8) unsigned NOT NULL auto_increment,
`title` varchar(255) NOT NULL,
`description` text NOT NULL,
`user_id` mediumint(8) unsigned NOT NULL,
`timestamp` int(10) unsigned NOT NULL,
`branch_id` mediumint(8) unsigned default NULL,
`course_id` mediumint(8) unsigned default NULL,
`group_id` mediumint(8) unsigned default NULL,
`private` tinyint(1) NOT NULL default '0',
`attachment_id` int(11) default NULL,
PRIMARY KEY (`id`),
CONSTRAINT `discussion_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
CONSTRAINT `discussion_ibfk_2` FOREIGN KEY (`branch_id`) REFERENCES `branch` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
CONSTRAINT `discussion_ibfk_3` FOREIGN KEY (`course_id`) REFERENCES `course` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
CONSTRAINT `discussion_ibfk_4` FOREIGN KEY (`group_id`) REFERENCES `group` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
CONSTRAINT `discussion_ibfk_5` FOREIGN KEY (`attachment_id`) REFERENCES `file` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
INDEX `discussion_timestamp_index` (`timestamp`)
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `discussion_reply`(
`id` int(11) unsigned NOT NULL auto_increment,
`reply_text` text NOT NULL,
`user_id` mediumint(8) unsigned NOT NULL,
`parent_id` int(11) unsigned default NULL,
`discussion_id` mediumint(8) unsigned NOT NULL,
`timestamp` int(10) unsigned NOT NULL,
`attachment_id` int(11) default NULL,
PRIMARY KEY (`id`),
CONSTRAINT `discussion_reply_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
CONSTRAINT `discussion_reply_ibfk_2` FOREIGN KEY (`parent_id`) REFERENCES `discussion_reply` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
CONSTRAINT `discussion_reply_ibfk_3` FOREIGN KEY (`discussion_id`) REFERENCES `discussion` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
CONSTRAINT `discussion_reply_ibfk_4` FOREIGN KEY (`attachment_id`) REFERENCES `file` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
INDEX `discussion_reply_timestamp_index` (`timestamp`)
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `discussion_reply_vote`(
`id` int(11) unsigned NOT NULL auto_increment,
`user_id` mediumint(8) unsigned NOT NULL,
`reply_id` int(11) unsigned NOT NULL,
`timestamp` int(10) unsigned default NULL,
PRIMARY KEY (`id`),
KEY `reply_id` (`reply_id`),
UNIQUE (`user_id`, `reply_id`),
CONSTRAINT `discussion_reply_vote_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
CONSTRAINT `discussion_reply_vote_ibfk_2` FOREIGN KEY (`reply_id`) REFERENCES `discussion_reply` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `custom_report`(
`id` mediumint(8) unsigned NOT NULL auto_increment,
`name` varchar(255) NOT NULL,
`rules` text NOT NULL,
`course_rules` text default NULL,
`output_type` tinyint(1) unsigned NOT NULL default '0',
`output_columns` text NOT NULL,
`created_on` int(10) unsigned NOT NULL,
`branch_id` mediumint(8) unsigned default NULL,
PRIMARY KEY (`id`),
KEY `branch_id` (`branch_id`),
CONSTRAINT `custom_report_ibfk_1` FOREIGN KEY (`branch_id`) REFERENCES `branch` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `scheduled_report`(
`id` mediumint(8) unsigned NOT NULL auto_increment,
`interval_type` tinyint(1) NOT NULL default '0',
`interval` smallint(5) unsigned NOT NULL,
`interval_months` smallint(5) NOT NULL default '0',
`day` smallint(5) unsigned NOT NULL,
`minute` smallint(5) unsigned default NULL,
`type` enum('system', 'user', 'course', 'branch', 'group', 'test', 'survey', 'assignment', 'ilt', 'custom', 'scorm', 'training_matrix', 'unit_matrix') NOT NULL,
`entity_id` mediumint(8) unsigned default NULL,
`recipients` text default NULL,
`creator_id` mediumint(8) unsigned default NULL,
`branch_id` mediumint(8) unsigned default NULL,
`branch_supervisor` tinyint(1) default NULL,
`next_run` int(10) unsigned default NULL,
`last_attempt` int(10) unsigned default NULL,
`last_run` int(10) unsigned default NULL,
PRIMARY KEY (`id`),
KEY `creator_id` (`creator_id`),
KEY `branch_id` (`branch_id`),
CONSTRAINT `scheduled_report_ibfk_1` FOREIGN KEY (`creator_id`) REFERENCES `user` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
CONSTRAINT `scheduled_report_ibfk_2` FOREIGN KEY (`branch_id`) REFERENCES `branch` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `language_overrides`(
`id` mediumint(8) unsigned NOT NULL auto_increment,
`original` text NOT NULL,
`overridden` text NOT NULL,
`language` varchar(10) NOT NULL,
PRIMARY KEY (`id`)
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `marketplace_course`(
`id` mediumint(8) unsigned NOT NULL auto_increment,
`course_id` mediumint(8) unsigned NOT NULL,
`licenses` mediumint(8) default NULL,
`paid` tinyint(1) NOT NULL default '0',
`marketplace_domain_id` mediumint(8) unsigned NOT NULL,
`marketplace_course_id` mediumint(8) unsigned NOT NULL,
PRIMARY KEY (`id`),
CONSTRAINT `marketplace_course_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `course` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `course_to_provider_domain`(
`id` mediumint(8) unsigned NOT NULL auto_increment,
`course_id` mediumint(8) unsigned NOT NULL,
`provider_domain_id` mediumint(8) unsigned,
`provider_domain_site_name` varchar(100) default NULL,
PRIMARY KEY (`id`),
CONSTRAINT `course_to_provider_domain_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `course` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `badge`(
`id` smallint(5) unsigned NOT NULL auto_increment,
`criteria` text NOT NULL,
`description` varchar(255) default NULL,
`type` varchar(40) NOT NULL,
`issue_condition` smallint(5) unsigned NOT NULL,
PRIMARY KEY (`id`)
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `badge_to_set`(
`id` smallint(5) unsigned NOT NULL auto_increment,
`badge_id` smallint(5) unsigned NOT NULL,
`badge_set` smallint(5) unsigned NOT NULL default 1,
`name` varchar(100) NOT NULL,
`image` varchar(255) NOT NULL,
PRIMARY KEY (`id`),
KEY `badge_id` (`badge_id`),
CONSTRAINT `badge_to_set_ibfk_1` FOREIGN KEY (`badge_id`) REFERENCES `badge` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `badge_assertion`(
`id` int(11) unsigned NOT NULL auto_increment,
`user_id` mediumint(8) unsigned NOT NULL,
`badge_id` smallint(5) unsigned NOT NULL,
`uid` varchar(50) NOT NULL,
`issued_on` int(10) unsigned NOT NULL,
`expires` int(10) unsigned default NULL,
PRIMARY KEY (`id`),
KEY `user_id` (`user_id`),
KEY `badge_id` (`badge_id`),
UNIQUE (`uid`),
CONSTRAINT `badge_assertion_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
CONSTRAINT `badge_assertion_ibfk_2` FOREIGN KEY (`badge_id`) REFERENCES `badge` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `gamification_points_statistics`(
`id` mediumint(8) unsigned NOT NULL auto_increment,
`user_id` mediumint(8) unsigned NOT NULL,
`points` int(10) unsigned NOT NULL default '0',
`timestamp_from` int(10) unsigned NOT NULL,
`timestamp_to` int(10) unsigned NOT NULL,
PRIMARY KEY (`id`),
KEY `user_id` (`user_id`),
CONSTRAINT `gamification_points_statistics_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `cms_homepage`(
`id` mediumint(8) unsigned NOT NULL auto_increment,
`branch_id` mediumint(8) unsigned default NULL,
`title` varchar(250) NOT NULL,
`headline` varchar(250) default '',
`subheadline` varchar(250) default '',
`featured_courses_headline` varchar(250) default '',
`featured_courses_subheadline` varchar(250) default '',
`featured_courses_ids` varchar(512) default '',
`image` varchar(255) default NULL,
`status` enum('active', 'inactive') NOT NULL default 'active',
`is_plus` tinyint(1) NOT NULL default '0',
PRIMARY KEY (`id`),
KEY `branch_id` (`branch_id`),
CONSTRAINT `cms_homepage_ibfk_1` FOREIGN KEY (`branch_id`) REFERENCES `branch` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `cms_page`(
`id` mediumint(8) unsigned NOT NULL auto_increment,
`cms_homepage_id` mediumint(8) unsigned NOT NULL,
`previous_id` mediumint(8) unsigned default NULL,
`type` varchar(30) NOT NULL,
`title` varchar(250) NOT NULL,
`headline` varchar(250) default '',
`subheadline` varchar(250) default '',
`url_path` varchar(250) default '',
`content` longtext default '',
`image` varchar(255) default NULL,
`display` enum('internal', 'external', 'both', 'inactive_internal', 'inactive_external', 'inactive_both') NOT NULL default 'inactive_external',
PRIMARY KEY (`id`),
KEY `cms_homepage_id` (`cms_homepage_id`),
KEY `previous_id` (`previous_id`),
CONSTRAINT `cms_page_ibfk_1` FOREIGN KEY (`cms_homepage_id`) REFERENCES `cms_homepage` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
CONSTRAINT `cms_page_ibfk_2` FOREIGN KEY (`previous_id`) REFERENCES `cms_page` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `cms_section`(
`id` mediumint(8) unsigned NOT NULL auto_increment,
`cms_page_id` mediumint(8) unsigned NOT NULL,
`previous_id` mediumint(8) unsigned default NULL,
`type` tinyint(3) unsigned NOT NULL,
`headline` varchar(250) default '',
`subheadline` varchar(250) default '',
`content` longtext default '',
`featured_courses_ids` varchar(512) default '',
`image_url` varchar(255) default NULL,
`image` varchar(255) default NULL,
`video_url` varchar(255) default NULL,
`video_id` int(11) default NULL,
`media_placement` tinyint(1) default NULL,
`cta` varchar(250) default NULL,
`cta_url` varchar(255) default NULL,
`cta_placement` tinyint(1) default NULL,
`divider` tinyint(1) default NULL,
PRIMARY KEY (`id`),
KEY `cms_page_id` (`cms_page_id`),
KEY `previous_id` (`previous_id`),
KEY `video_id` (`video_id`),
CONSTRAINT `cms_section_ibfk_1` FOREIGN KEY (`cms_page_id`) REFERENCES `cms_page` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
CONSTRAINT `cms_section_ibfk_2` FOREIGN KEY (`previous_id`) REFERENCES `cms_section` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
CONSTRAINT `cms_section_ibfk_3` FOREIGN KEY (`video_id`) REFERENCES `file` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `cms_key_point`(
`id` mediumint(8) unsigned NOT NULL auto_increment,
`title` varchar(250) default NULL,
`content` longtext default '',
`icon` varchar(250) default '',
`cta` varchar(250) default NULL,
`cta_url` varchar(255) default NULL,
`order` int(10) NOT NULL,
`section_id` mediumint(8) unsigned NOT NULL,
PRIMARY KEY (`id`),
KEY `section_id` (`section_id`),
CONSTRAINT `cms_key_point_ibfk_1` FOREIGN KEY (`section_id`) REFERENCES `cms_section` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `calendar_event`(
`id` mediumint(8) unsigned NOT NULL auto_increment,
`linked_id` mediumint(8) unsigned default NULL,
`owner_id` mediumint(8) unsigned NOT NULL,
`branch_id` mediumint(8) unsigned default NULL,
`unit_id` mediumint(8) unsigned default NULL,
`instructor_id` mediumint(8) unsigned default NULL,
`webinar_id` varchar(255) default NULL,
`type` enum('regular', 'ilt') default 'regular',
`name` varchar(150) NOT NULL,
`multiname` varchar(150) default '',
`description` text,
`start_datetime` int(10) unsigned NOT NULL,
`capacity` smallint(5) unsigned default NULL,
`session_type` enum('webinar', 'classroom') default NULL,
`location` varchar(150) default '',
`duration` mediumint(8) unsigned NOT NULL,
`visibility` enum('public', 'private', 'audience') NOT NULL default 'private',
`audience` text default NULL,
`recurring` varchar(512) default NULL,
`background_color` varchar(7) default '',
`text_color` varchar(7) default '',
`revision` int(10) unsigned NOT NULL default '0',
PRIMARY KEY (`id`),
KEY `owner_id` (`owner_id`),
KEY `branch_id` (`branch_id`),
KEY `unit_id` (`unit_id`),
KEY `instructor_id` (`instructor_id`),
KEY `webinar_id` (`webinar_id`),
KEY `linked_id` (`linked_id`),
CONSTRAINT `calendar_event_ibfk_1` FOREIGN KEY (`owner_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
CONSTRAINT `calendar_event_ibfk_2` FOREIGN KEY (`branch_id`) REFERENCES `branch` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
CONSTRAINT `calendar_event_ibfk_3` FOREIGN KEY (`unit_id`) REFERENCES `unit` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
CONSTRAINT `calendar_event_ibfk_4` FOREIGN KEY (`instructor_id`) REFERENCES `user` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
CONSTRAINT `calendar_event_ibfk_5` FOREIGN KEY (`webinar_id`) REFERENCES `conference` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
CONSTRAINT `calendar_event_ibfk_6` FOREIGN KEY (`linked_id`) REFERENCES `calendar_event` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `user_to_calendar_event`(
`id` int(11) unsigned NOT NULL auto_increment,
`user_id` mediumint(8) unsigned NOT NULL,
`calendar_event_id` mediumint(8) unsigned NOT NULL,
`attended` tinyint NOT NULL DEFAULT 0 ,
`last_updated_on` int unsigned DEFAULT NULL,
PRIMARY KEY (`id`),
KEY `calendar_event_id` (`calendar_event_id`),
UNIQUE (`user_id`, `calendar_event_id`),
CONSTRAINT `user_to_calendar_event_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
CONSTRAINT `user_to_calendar_event_ibfk_2` FOREIGN KEY (`calendar_event_id`) REFERENCES `calendar_event` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `automated_action`(
`id` mediumint(8) unsigned NOT NULL auto_increment,
`name` varchar(100) NOT NULL,
`action` tinyint(3) unsigned NOT NULL,
`entity_id` mediumint(8) unsigned default NULL,
`entities` JSON default NULL,
`type` tinyint(2) unsigned NOT NULL,
`status` enum('active', 'inactive') NOT NULL default 'active',
`created_on` int(10) unsigned NOT NULL,
`arguments` text NOT NULL,
PRIMARY KEY (`id`)
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `course_field`(
`id` mediumint(8) unsigned NOT NULL auto_increment,
`previous_id` mediumint(8) unsigned default NULL,
`name` varchar(100) NOT NULL,
`type` enum('text','dropdown','checkbox','date') NOT NULL default 'text',
`mandatory` tinyint(1) NOT NULL default '0',
`visible_on_reports` tinyint(1) NOT NULL default '0',
`visible_to_learners` tinyint(1) NOT NULL default '0',
`selective_availability` tinyint(1) NOT NULL default '0',
`branches_availability` text default NULL,
`main_availability` tinyint(1) NOT NULL default '0',
`checked` tinyint(1) NOT NULL default '0',
`dropdown_values` mediumtext default NULL,
PRIMARY KEY (`id`),
KEY `previous_id` (`previous_id`),
UNIQUE (`name`),
CONSTRAINT `course_field_ibfk_1` FOREIGN KEY (`previous_id`) REFERENCES `course_field` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `user_field`(
`id` mediumint(8) unsigned NOT NULL auto_increment,
`previous_id` mediumint(8) unsigned default NULL,
`name` varchar(100) NOT NULL,
`type` enum('text','dropdown','checkbox','date') NOT NULL default 'text',
`mandatory` tinyint(1) NOT NULL default '0',
`visible_on_reports` tinyint(1) NOT NULL default '0',
`selective_availability` tinyint(1) NOT NULL default '0',
`branches_availability` text default NULL,
`main_availability` tinyint(1) NOT NULL default '0',
`checked` tinyint(1) NOT NULL default '0',
`dropdown_values` mediumtext default NULL,
PRIMARY KEY (`id`),
KEY `previous_id` (`previous_id`),
UNIQUE (`name`),
CONSTRAINT `user_field_ibfk_1` FOREIGN KEY (`previous_id`) REFERENCES `user_field` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `cmi5_session`(
`id` int(11) unsigned NOT NULL auto_increment,
`session_id` varchar(100) NOT NULL,
`token` varchar(100) default NULL,
`status` enum('issued', 'initialized', 'terminated') NOT NULL default 'issued',
`registration` varchar (100) NOT NULL,
`timestamp` int(10) unsigned NOT NULL,
PRIMARY KEY (`id`)
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `infographic`(
`id` mediumint(8) unsigned NOT NULL auto_increment,
`branch_id` mediumint(8) unsigned default NULL,
`group_id` mediumint(8) unsigned default NULL,
`title` varchar(250) NOT NULL,
`theme` tinyint(3) unsigned NOT NULL default '1',
`background` varchar(255) default NULL,
`default` tinyint(3) unsigned NOT NULL default '0',
`period_type` tinyint(3) unsigned default '0',
`period_from` int(10) unsigned default NULL,
`period_to` int(10) unsigned default NULL,
PRIMARY KEY (`id`),
KEY `group_id` (`group_id`),
KEY `branch_id` (`branch_id`),
CONSTRAINT `infographic_ibfk_1` FOREIGN KEY (`group_id`) REFERENCES `group` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
CONSTRAINT `infographic_ibfk_2` FOREIGN KEY (`branch_id`) REFERENCES `branch` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
)ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE `infographic_section`(
`id` mediumint(8) unsigned NOT NULL auto_increment,
`infographic_id` mediumint(8) unsigned NOT NULL,
`previous_id` mediumint(8) unsigned default NULL,
`title` varchar(250) NOT NULL,
`title_alignment` tinyint(3) unsigned NOT NULL default '2',
`title_background` tinyint(3) unsigned NOT NULL default '0',
`title_visibility` tinyint(3) unsigned NOT NULL default '1',
`type` tinyint(3) unsigned NOT NULL default '1',
`style` tinyint(3) unsigned NOT NULL default '1',
`options` text NOT NULL,
PRIMARY KEY (`id`),
KEY `infographic_id` (`infographic_id`),
KEY `previous_id` (`previous_id`),
CONSTRAINT `infographic_section_ibfk_1` FOREIGN KEY (`infographic_id`) REFERENCES `infographic` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
CONSTRAINT `infographic_section_ibfk_2` FOREIGN KEY (`previous_id`) REFERENCES `infographic_section` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `marketo_event_queue`(
`id` mediumint(8) unsigned NOT NULL auto_increment,
`timestamp` int(10) unsigned NOT NULL,
`user_id` mediumint(8) unsigned default NULL,
`method` enum('GET', 'POST', 'PUT', 'PATCH', 'DELETE') NOT NULL default 'POST',
`api_endpoint` varchar(255) NOT NULL,
`request_type` tinyint(3) unsigned NOT NULL,
`arguments` JSON default NULL,
`priority` tinyint(3) unsigned default '0',
`stamp` char(32),
PRIMARY KEY (`id`),
KEY `user_id` (`user_id`),
CONSTRAINT `marketo_event_queue_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
INDEX `marketo_event_queue_timestamp_index` (`timestamp`)
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `user_to_discussion`(
`id` mediumint(8) unsigned NOT NULL auto_increment,
`user_id` mediumint(8) unsigned NOT NULL,
`discussion_id` mediumint(8) unsigned NOT NULL,
PRIMARY KEY (`id`),
KEY `discussion_id` (`discussion_id`),
UNIQUE (`user_id`, `discussion_id`),
CONSTRAINT `user_to_discussion_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
CONSTRAINT `user_to_discussion_ibfk_2` FOREIGN KEY (`discussion_id`) REFERENCES `discussion` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `zoom_authorized_users`(
`id` mediumint(8) unsigned NOT NULL auto_increment,
`domain_id` mediumint(8) unsigned NOT NULL,
`user_id` mediumint(8) unsigned NOT NULL,
`zoom_user_id` varchar(150) NOT NULL,
PRIMARY KEY (`id`),
KEY `domain_id` (`domain_id`),
CONSTRAINT `zoom_authorized_users_ibfk_1` FOREIGN KEY (`domain_id`) REFERENCES `domain` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `stripe_event`(
`id` bigint unsigned NOT NULL auto_increment, # Used along with checkpoint to create idempotent requests https://stripe.com/docs/api/idempotent_requests
`event_id` varchar(255) NOT NULL, # Stripe event ID
`checkpoint` tinyint unsigned NOT NULL default 0, # The checkpoint to continue this failed event
`payload` text NOT NULL, # Can store up to 64KB
`completed` tinyint unsigned NOT NULL default 0, # 0 initially and 1 when the job is completed
`created_on` datetime NOT NULL default NOW(),
KEY `completed` (`completed`),
UNIQUE (`event_id`), # Prevent processing the same event twice https://stripe.com/docs/webhooks/best-practices#duplicate-events
PRIMARY KEY (`id`),
INDEX `stripe_event_completed_created_index` (`completed`, `created_on`)
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `blocked_domains`(
`id` mediumint(8) unsigned NOT NULL auto_increment,
`domain` varchar(150) NOT NULL,
`created_at` int(10) unsigned default NULL,
UNIQUE (`domain`),
PRIMARY KEY (`id`)
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `paypal_ipn_event`(
`id` bigint unsigned NOT NULL auto_increment,
`transaction_id` varchar(255) NOT NULL,
`payment_status` varchar(64) NOT NULL,
`payload` text NOT NULL, # Can store up to 64KB
`completed` tinyint unsigned NOT NULL default 0,
`created_on` datetime NOT NULL default NOW(),
`checkpoint` tinyint unsigned NOT NULL default 0,
KEY `completed` (`completed`),
UNIQUE (`transaction_id`, `payment_status`), # Prevent processing the same event twice
PRIMARY KEY (`id`),
INDEX `paypal_ipn_completed_created_index` (`completed`, `created_on`)
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `widget` (
`id` mediumint(8) unsigned NOT NULL auto_increment,
`user_id` mediumint(8) unsigned DEFAULT NULL,
`user_role` varchar(255) DEFAULT NULL,
`user_type_id` smallint(5) unsigned DEFAULT NULL,
`data` json NOT NULL,
`last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
CONSTRAINT `fk_user_id` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
CONSTRAINT `fk_user_type_id_id` FOREIGN KEY (`user_type_id`) REFERENCES `acl_user_type` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
UNIQUE `user_unique` (user_id, user_role),
UNIQUE `user_type` (user_type_id),
PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `task` (
`id` int unsigned NOT NULL auto_increment,
`user_id` mediumint(8) unsigned NOT NULL,
`type` varchar(32) NOT NULL,
`status` enum('created', 'processing', 'completed', 'failed') NOT NULL default 'created',
`is_canceled` tinyint(1) unsigned default '0',
`failure_reason` varchar(64) default NULL,
`progress` float default NULL,
`processed_items` int default null,
`arguments` text default NULL,
`created_at` int(10) unsigned NOT NULL,
`ended_at` int(10) unsigned default NULL,
CONSTRAINT `task_fk_user_id` FOREIGN KEY (`user_id`) REFERENCES `user`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `referrer`(
`id` mediumint(8) unsigned NOT NULL auto_increment,
`domain_id` mediumint(8) unsigned,
`user_id` mediumint(8) unsigned NOT NULL,
`started_on` int(10) unsigned NOT NULL,
`key` varchar(255) NOT NULL,
`status` enum('active', 'inactive', 'archived', 'deleted') NOT NULL default 'active',
PRIMARY KEY (`id`),
UNIQUE (`key`),
UNIQUE `domain_id_user_id_idx` (`domain_id`, `user_id`),
CONSTRAINT `referrer_ibfk_1` FOREIGN KEY (`domain_id`) REFERENCES `domain` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `referrer_reward`(
`id` mediumint(8) unsigned NOT NULL auto_increment,
`domain_id` mediumint(8) unsigned,
`referrer_id` mediumint(8) unsigned,
`referrer_email` varchar(150) default NULL,
`status` enum('ineligible', 'claimable', 'pending', 'claimed') NOT NULL default 'ineligible',
`issued_on` int(10) unsigned default NULL,
`amount` float NOT NULL,
`region` varchar(50) default NULL,
`issuance_type` enum('manual', 'automatic') NOT NULL default 'manual',
`metadata` json default NULL,
PRIMARY KEY (`id`),
UNIQUE `domain_id_idx` (`domain_id`),
CONSTRAINT `referrer_reward_ibfk_1` FOREIGN KEY (`domain_id`) REFERENCES `domain` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
CONSTRAINT `referrer_reward_ibfk_2` FOREIGN KEY (`referrer_id`) REFERENCES `referrer` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `referrer_visit`(
`id` bigint unsigned NOT NULL auto_increment,
`referrer_key` varchar(255) NOT NULL,
`timestamp` int(10) unsigned NOT NULL,
PRIMARY KEY (`id`)
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `reverse_trial_tracked_entity`(
`id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
`entity_id` int(11) unsigned NOT NULL,
`entity_type` varchar(255) NOT NULL,
`created_at` int(10) unsigned DEFAULT NULL,
`metadata` json DEFAULT NULL,
PRIMARY KEY (`id`)
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `skill`(
`id` mediumint(8) unsigned NOT NULL auto_increment,
`type` enum('user', 'template') NOT NULL default 'user',
`status` enum('inactive', 'active') NOT NULL default 'active',
`name` varchar(255) NOT NULL,
`image` varchar(255) default NULL,
`description` text,
`created_on` int(10) unsigned NOT NULL,
`last_update_on` int(10) unsigned default NULL,
`is_suggested` tinyint(1) NOT NULL default 0,
`show_ext_resources` tinyint(1) NOT NULL default 1,
`ai_jobs` json DEFAULT NULL,
PRIMARY KEY (`id`),
INDEX `name_index` (`name`),
INDEX `type_index` (`type`),
INDEX `status_index` (`status`),
INDEX `is_suggested_index` (`is_suggested`),
INDEX `created_on_index` (`created_on`),
INDEX `last_update_on_index` (`last_update_on`)
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `user_to_skill` (
`id` mediumint(8) unsigned NOT NULL auto_increment,
`user_id` mediumint(8) unsigned NOT NULL,
`skill_id` mediumint(8) unsigned NOT NULL,
`current_level_id` mediumint(8) unsigned NOT NULL,
`assigned_on` int(10) unsigned default NULL,
`expires_on` int(10) unsigned default NULL,
PRIMARY KEY (`id`),
UNIQUE INDEX `user_skill_unique` (`user_id`, `skill_id`),
INDEX `skill_id_index` (`skill_id`),
INDEX `user_id_index` (`user_id`),
CONSTRAINT `user_to_skill_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
CONSTRAINT `user_to_skill_ibfk_2` FOREIGN KEY (`skill_id`) REFERENCES `skill` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `skill_recommendation`(
`id` mediumint(8) unsigned NOT NULL auto_increment,
`user_id` mediumint(8) unsigned NOT NULL,
`skill_id` mediumint(8) unsigned NOT NULL,
`from_user_id` mediumint(8) unsigned NOT NULL,
`requested_on` int(10) unsigned NOT NULL,
PRIMARY KEY (`id`),
UNIQUE INDEX user_skill_referer_unique (user_id, skill_id, from_user_id),
INDEX `skill_id_index` (`skill_id`),
INDEX `user_id_index` (`user_id`),
INDEX `from_user_id_index` (`from_user_id`),
CONSTRAINT `skill_recommendation_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
CONSTRAINT `skill_recommendation_ibfk_2` FOREIGN KEY (`skill_id`) REFERENCES `skill` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
CONSTRAINT `skill_recommendation_ibfk_3` FOREIGN KEY (`from_user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `skill_upgrade`(
`id` mediumint(8) unsigned NOT NULL auto_increment,
`user_to_skill_id` mediumint(8) unsigned,
`user_id` mediumint(8) unsigned NOT NULL,
`skill_id` mediumint(8) unsigned NOT NULL,
`level_id` mediumint(8) unsigned NOT NULL,
`requested_on` int(10) unsigned NOT NULL,
PRIMARY KEY (`id`),
UNIQUE INDEX user_skill_unique (user_id, skill_id),
INDEX `skill_id_index` (`skill_id`),
INDEX `user_id_index` (`user_id`),
CONSTRAINT `skill_upgrade_ibfk_1` FOREIGN KEY (`user_to_skill_id`) REFERENCES `user_to_skill` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
CONSTRAINT `skill_upgrade_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
CONSTRAINT `skill_upgrade_ibfk_3` FOREIGN KEY (`skill_id`) REFERENCES `skill` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `skill_ext_resource`(
`id` mediumint(8) unsigned NOT NULL auto_increment,
`name` varchar(255) NOT NULL,
`url` varchar(1000) default NULL,
`skill_id` mediumint(8) unsigned NOT NULL,
`created_on` int(10) unsigned NOT NULL,
PRIMARY KEY (`id`),
INDEX `skill_id_index` (`skill_id`),
CONSTRAINT `skill_ext_resource_ibfk_1` FOREIGN KEY (`skill_id`) REFERENCES `skill` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `user_skill_level_history` (
`id` mediumint(8) unsigned NOT NULL auto_increment,
`user_to_skill_id` mediumint(8) unsigned NOT NULL,
`user_id` mediumint(8) unsigned NOT NULL,
`skill_id` mediumint(8) unsigned NOT NULL,
`level_id` mediumint(8) unsigned NOT NULL,
`assigned_on` int(10) unsigned default NULL,
PRIMARY KEY (`id`),
UNIQUE INDEX `user_skill_level_unique` (`user_id`, `skill_id`, `level_id`),
INDEX `skill_id_index` (`skill_id`),
INDEX `user_id_index` (`user_id`),
INDEX `level_id_index` (`level_id`),
INDEX `assigned_on_index` (`assigned_on`),
CONSTRAINT `user_skill_level_history_ibfk_1` FOREIGN KEY (`user_to_skill_id`) REFERENCES `user_to_skill` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
CONSTRAINT `user_skill_level_history_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
CONSTRAINT `user_skill_level_history_ibfk_3` FOREIGN KEY (`skill_id`) REFERENCES `skill` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `course_to_skill`(
`id` mediumint(8) unsigned NOT NULL auto_increment,
`course_id` mediumint(8) unsigned NOT NULL,
`skill_id` mediumint(8) unsigned NOT NULL,
`assigned_on` int(10) unsigned default NULL,
PRIMARY KEY (`id`),
UNIQUE INDEX `course_skill_unique` (`course_id`, `skill_id`),
INDEX `skill_id_index` (`skill_id`),
INDEX `course_id_index` (`course_id`),
INDEX `assigned_on_index` (`assigned_on`),
CONSTRAINT `course_to_skill_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `course` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
CONSTRAINT `course_to_skill_ibfk_2` FOREIGN KEY (`skill_id`) REFERENCES `skill` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `skill_assessment_question`(
`id` mediumint(8) unsigned NOT NULL auto_increment,
`skill_id` mediumint(8) unsigned default NULL,
`level_id` mediumint(8) unsigned default NULL,
`type` varchar(50) NOT NULL,
`question` json NOT NULL,
`created_on` int(10) unsigned NOT NULL,
`last_update_on` int(10) unsigned default NULL,
`is_active` tinyint(1) NOT NULL default 1,
PRIMARY KEY (`id`),
INDEX `skill_id_index` (`skill_id`),
INDEX `level_id_index` (`level_id`),
INDEX `is_active_index` (`is_active`),
CONSTRAINT `skill_assessment_question_ibfk_1` FOREIGN KEY (`skill_id`) REFERENCES `skill` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `skill_assessment_attempt`(
`id` mediumint(8) unsigned NOT NULL auto_increment,
`user_id` mediumint(8) unsigned default NULL,
`skill_id` mediumint(8) unsigned default NULL,
`level_id` mediumint(8) unsigned default NULL,
`current_attempt_question_id` mediumint(8) unsigned default NULL,
`started_on` int(10) unsigned NOT NULL,
`max_time_allowed` mediumint NOT NULL,
`completed_on` int(10) unsigned default NULL,
`completion_threshold` float NOT NULL,
`score` float unsigned default NULL,
`questions_count` mediumint(8) unsigned NOT NULL,
`completion_status` varchar(50) default NULL,
PRIMARY KEY (`id`),
INDEX `user_id_index` (`user_id`),
INDEX `skill_id_index` (`skill_id`),
INDEX `level_id_index` (`level_id`),
INDEX `started_on_index` (`started_on`),
CONSTRAINT `skill_assessment_attempt_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
CONSTRAINT `skill_assessment_attempt_ibfk_2` FOREIGN KEY (`skill_id`) REFERENCES `skill` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `skill_assessment_attempt_question`(
`id` mediumint(8) unsigned NOT NULL auto_increment,
`attempt_id` mediumint(8) unsigned NOT NULL,
`original_id` mediumint(8) unsigned default NULL,
`type` varchar(50) NOT NULL,
`question` json NOT NULL,
`position` int unsigned NOT NULL,
`user_answers` json default NULL,
PRIMARY KEY (`id`),
INDEX `attempt_id_index` (`attempt_id`),
INDEX `position_index` (`position`),
CONSTRAINT `skill_assessment_attempt_question_ibfk_1` FOREIGN KEY (`attempt_id`) REFERENCES `skill_assessment_attempt` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
CONSTRAINT `skill_assessment_attempt_question_ibfk_2` FOREIGN KEY (`original_id`) REFERENCES `skill_assessment_question` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `job_role_cache`(
`id` mediumint(8) unsigned NOT NULL auto_increment,
`name` varchar(255) NOT NULL,
`description` text,
`status` json NOT NULL,
`skills` json,
`similar_role_names` json,
`ai_jobs` json NOT NULL,    # To be deprecated
`created_on` int(10) unsigned NOT NULL,
`last_update_on` int(10) unsigned NOT NULL,
PRIMARY KEY (`id`),
INDEX `name_index` (`name`)
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `open_sesame_course`(
`id` mediumint(8) unsigned NOT NULL auto_increment,
`open_sesame_id` varchar (36) NOT NULL,
`name` varchar(255) NOT NULL,
`description` text,
`file_url` varchar(255) NOT NULL,
`active` tinyint NOT NULL,
`updated_at` int(10) unsigned NOT NULL,
`category_name` varchar(150) default NULL,
`has_changes` tinyint NOT NULL default '0',
`image_url` varchar(255) default NULL,
`client_integration_id` varchar(36),
UNIQUE (`open_sesame_id`),
PRIMARY KEY (`id`)
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `ai_job` (
`id` mediumint(8) unsigned NOT NULL auto_increment,
`services_job_id` VARCHAR(255) NULL,
`type` VARCHAR(255) NOT NULL,
`status` ENUM('working', 'completed', 'failed') NOT NULL DEFAULT 'working',
`data` json DEFAULT NULL,
`progress` json DEFAULT NULL,
`started_on` bigint(20) unsigned NOT NULL,
`updated_on` bigint(20) unsigned default NULL,
`completed_on` bigint(20) unsigned default NULL,
PRIMARY KEY (`id`),
KEY `type` (`type`),
KEY `status` (`status`)
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `job_relationships` (
`id` mediumint(8) unsigned NOT NULL auto_increment,
dependant_job_id mediumint(8) unsigned,
dependency_job_id mediumint(8) unsigned,
PRIMARY KEY (`id`),
CONSTRAINT `job_relationships_ibfk_1` FOREIGN KEY (`dependant_job_id`) REFERENCES `ai_job` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
CONSTRAINT `job_relationships_ibfk_2` FOREIGN KEY (`dependency_job_id`) REFERENCES `ai_job` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `ai_actions_history` (
`id` BIGINT unsigned NOT NULL auto_increment,
`type` VARCHAR(255) NOT NULL,
`user_id` mediumint(8) unsigned default NULL,
`status` ENUM('working', 'completed', 'failed') NOT NULL DEFAULT 'working',
`started_at` BIGINT(20) unsigned NOT NULL,
`completed_at` BIGINT(20) unsigned default NULL,
`model_name` VARCHAR(255) NULL,
`input_tokens` INT UNSIGNED DEFAULT 0,
`output_tokens` INT UNSIGNED DEFAULT 0,
`other_items_count` INT UNSIGNED DEFAULT 0,
`internal_job_id` mediumint(8) unsigned default NULL,
`job_id` VARCHAR(255) NULL,
`entity_type` VARCHAR(255) NULL,
`entity_id` mediumint(8) unsigned default NULL,
PRIMARY KEY (`id`),
KEY `type` (`type`),
KEY `user_id` (`user_id`),
KEY `status` (`status`),
KEY `model_name` (`model_name`),
KEY `job_id` (`job_id`),
KEY `internal_job_id` (`internal_job_id`)
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `ai_text_content` (
`id` mediumint(8) unsigned NOT NULL auto_increment,
`entity_id` mediumint(8) unsigned NOT NULL,
`entity_type` varchar(100) NOT NULL,
`content_file_path` varchar(255) NULL,
`metadata` mediumtext NULL,
`cached_on_date` bigint(20) UNSIGNED NOT NULL,
`content_state` text default NULL,
PRIMARY KEY (`id`),
KEY `entity_id` (`entity_id`),
KEY `entity_type` (`entity_type`)
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `chatbot_conversation_cache` (
`id` mediumint(8) unsigned AUTO_INCREMENT NOT NULL,
`conversation_id` varchar(255) NOT NULL,
`chatbot` varchar(32) NOT NULL,
`user_id` mediumint(8) unsigned NOT NULL,
`data` json default NULL,
`created_at` bigint(20) unsigned NOT NULL,
`updated_at` bigint(20) unsigned default NULL,
`effective_date` bigint(20) unsigned GENERATED ALWAYS AS (COALESCE(updated_at, created_at)) STORED,
PRIMARY KEY (`id`),
UNIQUE KEY `conversation_id_unique` (`conversation_id`),
KEY `user_id_index` (`user_id`),
KEY `effective_date_index` (`effective_date`),
CONSTRAINT `chatbot_conversation_cache_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `chatbot_conversation_to_job` (
`id` mediumint(8) unsigned NOT NULL auto_increment,
`chatbot_conversation_cache_id` mediumint(8) unsigned NOT NULL,
`job_id` mediumint(8) unsigned NOT NULL,
PRIMARY KEY (`id`),
UNIQUE INDEX `chatbot_conversation_to_job_cache_id_job_id_unique` (`chatbot_conversation_cache_id`, `job_id`),
INDEX `chatbot_conversation_cache_id_index` (`chatbot_conversation_cache_id`),
INDEX `job_id_index` (`job_id`),
CONSTRAINT `chatbot_conversation_to_job_ibfk_1` FOREIGN KEY (`chatbot_conversation_cache_id`) REFERENCES `chatbot_conversation_cache` (`id`) ON DELETE CASCADE,
CONSTRAINT `chatbot_conversation_to_job_ibfk_2` FOREIGN KEY (`job_id`) REFERENCES `ai_job` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `course_to_job` (
`id` mediumint(8) unsigned AUTO_INCREMENT NOT NULL,
`course_id` mediumint(8) unsigned NOT NULL,
`job_id` mediumint(8) unsigned NOT NULL,
PRIMARY KEY (`id`),
UNIQUE (`course_id`, `job_id`),
CONSTRAINT `course_to_job_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `course` (`id`) ON DELETE CASCADE,
CONSTRAINT `course_to_job_ibfk_2` FOREIGN KEY (`job_id`) REFERENCES `ai_job` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `unit_to_job` (
`id` mediumint(8) unsigned AUTO_INCREMENT NOT NULL,
`unit_id` mediumint(8) unsigned NOT NULL,
`job_id` mediumint(8) unsigned NOT NULL,
PRIMARY KEY (`id`),
UNIQUE (`unit_id`, `job_id`),
CONSTRAINT `unit_to_job_ibfk_1` FOREIGN KEY (`unit_id`) REFERENCES `unit` (`id`) ON DELETE CASCADE,
CONSTRAINT `unit_to_job_ibfk_2` FOREIGN KEY (`job_id`) REFERENCES `ai_job` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `file_to_job` (
`id` mediumint(8) unsigned AUTO_INCREMENT NOT NULL,
`file_id` int(11) NOT NULL,
`job_id` mediumint(8) unsigned NOT NULL,
PRIMARY KEY (`id`),
UNIQUE (`file_id`, `job_id`),
CONSTRAINT `file_to_job_ibfk_1` FOREIGN KEY (`file_id`) REFERENCES `file` (`id`) ON DELETE CASCADE,
CONSTRAINT `file_to_job_ibfk_2` FOREIGN KEY (`job_id`) REFERENCES `ai_job` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `skill_to_job` (
`id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT ,
`skill_id` mediumint(8) unsigned NOT NULL,
`job_id` mediumint(8) unsigned NOT NULL,
PRIMARY KEY (`id`),
UNIQUE (`skill_id`, `job_id`),
CONSTRAINT `skill_to_job_ibfk_1` FOREIGN KEY (`skill_id`) REFERENCES `skill` (`id`) ON DELETE CASCADE,
CONSTRAINT `skill_to_job_ibfk_2` FOREIGN KEY (`job_id`) REFERENCES `ai_job` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `job_role_to_job` (
`id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT ,
`job_role_id` mediumint(8) unsigned NOT NULL,
`job_id` mediumint(8) unsigned NOT NULL,
PRIMARY KEY (`id`),
UNIQUE (`job_role_id`, `job_id`),
CONSTRAINT `job_role_to_job_ibfk_1` FOREIGN KEY (`job_role_id`) REFERENCES `job_role_cache` (`id`) ON DELETE CASCADE,
CONSTRAINT `job_role_to_job_ibfk_2` FOREIGN KEY (`job_id`) REFERENCES `ai_job` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `course_approval_request` (
`id` int unsigned NOT NULL AUTO_INCREMENT ,
`course_id` mediumint unsigned NOT NULL,
`user_id` mediumint unsigned NOT NULL,
`created_at` int unsigned NOT NULL,
`last_updated_on` int unsigned NOT NULL,
PRIMARY KEY (id),
KEY `course_id` (`course_id`),
KEY `user_id` (`user_id`),
UNIQUE (`course_id`, `user_id`),
CONSTRAINT `course_approval_request_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `course`(`id`) ON DELETE CASCADE,
CONSTRAINT `course_approval_request_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `user`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `user_search_history` (
`id` mediumint(8) unsigned NOT NULL auto_increment,
`user_id` mediumint(8) unsigned NOT NULL,
`search_context` enum('skills_talentpool', 'skills_pathfinder') NOT NULL,
`search_term` varchar(255) NOT NULL,
`created_on` int(10) unsigned NOT NULL,
`data` json default NULL,
PRIMARY KEY (`id`),
UNIQUE (`user_id`, `search_context`, `search_term`),
INDEX `user_id_search_context_created_on_index` (`user_id`, `search_context`, `created_on` DESC),
CONSTRAINT `user_search_history_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `analytics_dashboard` (
`id` mediumint(8) unsigned NOT NULL auto_increment,
`type` enum('system','main_portal','branches','specific_branch','groups','specific_group') NOT NULL,
`title` varchar(250) NOT NULL,
`banner_image` varchar(255) DEFAULT NULL,
`widgets` json NOT NULL,
`branch_id` mediumint unsigned DEFAULT NULL,
`group_id` mediumint unsigned DEFAULT NULL,
`is_default` tinyint(1) NOT NULL default '0',
PRIMARY KEY (`id`),
KEY `fk_dashboard_branch` (`branch_id`),
KEY `fk_dashboard_group` (`group_id`),
CONSTRAINT `fk_dashboard_branch` FOREIGN KEY (`branch_id`) REFERENCES `branch` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
CONSTRAINT `fk_dashboard_group` FOREIGN KEY (`group_id`) REFERENCES `group` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `lti_registration` (
`id` INT unsigned AUTO_INCREMENT NOT NULL,
`name` VARCHAR(255) NOT NULL,
`issuer` VARCHAR(255) NOT NULL,
`client_id` VARCHAR(255) NOT NULL,
`auth_login_url` VARCHAR(255) NOT NULL,
`key_set_url` VARCHAR(255) NOT NULL,
`icon_url` VARCHAR(255) DEFAULT NULL,
`client_url` VARCHAR(255) DEFAULT NULL,
`tos_url` VARCHAR(255) DEFAULT NULL,
`policy_url` VARCHAR(255) DEFAULT NULL,
`redirect_urls` TEXT DEFAULT NULL,
`scope` TEXT DEFAULT NULL,
`domain` VARCHAR(255) NOT NULL,
`target_link_url` VARCHAR(255) NOT NULL,
`rl_target_link_url` VARCHAR(255) DEFAULT NULL,
`dl_target_link_url` VARCHAR(255) DEFAULT NULL,
`private_key` TEXT DEFAULT NULL,
`kid` VARCHAR(255) DEFAULT NULL,
`deployment_id` VARCHAR(255) NOT NULL,
`created_at` INT unsigned NOT NULL,
`last_updated_on` INT unsigned NOT NULL,
`branch_id` MEDIUMINT(8) unsigned DEFAULT NULL,
`is_linkedin_learning` TINYINT(1) NOT NULL DEFAULT 0,
PRIMARY KEY (id),
CONSTRAINT `lti_registration_ibfk_1` FOREIGN KEY (`branch_id`) REFERENCES `branch` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `user_to_learning_path`(
`id` int unsigned NOT NULL auto_increment,
`user_id` mediumint unsigned NOT NULL,
`learning_path_id` mediumint unsigned NOT NULL,
`role` enum('trainer', 'learner') NOT NULL default 'learner',
`status` enum('active', 'expired', 'cancelled') NOT NULL default 'active',
`enrolled_on` int unsigned NOT NULL default '0',
`expiration_date` int unsigned default NULL,
PRIMARY KEY (`id`),
KEY `learning_path_id` (`learning_path_id`),
UNIQUE (`user_id`, `learning_path_id`),
CONSTRAINT `user_to_learning_path_id_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
CONSTRAINT `user_to_learning_path_id_ibfk_2` FOREIGN KEY (`learning_path_id`) REFERENCES `learning_path` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `learning_path_progress`(
`id` int unsigned NOT NULL auto_increment,
`user_id` mediumint unsigned default NULL,
`learning_path_id` mediumint unsigned default NULL,
`total_time` mediumint unsigned default NULL,
`score` float default NULL,
`completion_status` enum('not_attempted', 'incomplete', 'completed', 'failed') default 'not_attempted',
`completion_date` int unsigned default NULL,
`courses_completed` int unsigned NOT NULL DEFAULT 0,
`credit` enum('credit', 'no-credit') default 'credit',
PRIMARY KEY (`id`),
KEY `learning_path_id` (`learning_path_id`),
UNIQUE (`user_id`, `learning_path_id`),
CONSTRAINT `learning_path_progress_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
CONSTRAINT `learning_path_progress_ibfk_2` FOREIGN KEY (`learning_path_id`) REFERENCES `learning_path` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE learning_path_section (
`id` MEDIUMINT unsigned PRIMARY KEY AUTO_INCREMENT,
`learning_path_id` MEDIUMINT unsigned,
`title` VARCHAR(255),
`position` SMALLINT NOT NULL,
`created_at` INT unsigned NOT NULL,
KEY (`learning_path_id`),
CONSTRAINT `learning_path_section_ibfk_1` FOREIGN KEY (`learning_path_id`) REFERENCES `learning_path` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `course_to_learning_path`(
`id` int unsigned NOT NULL auto_increment,
`course_id` mediumint unsigned NOT NULL,
`learning_path_id` mediumint unsigned NOT NULL,
`created_at` int unsigned NOT NULL,
`section_id` mediumint unsigned NOT NULL,
`position` smallint unsigned NOT NULL,
PRIMARY KEY (`id`),
KEY `learning_path_id` (`learning_path_id`),
UNIQUE (`course_id`, `learning_path_id`),
UNIQUE (`section_id`, `position`),
CONSTRAINT `course_to_learning_path_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `course` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
CONSTRAINT `course_to_learning_path_ibfk_2` FOREIGN KEY (`learning_path_id`) REFERENCES `learning_path` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
CONSTRAINT `course_to_learning_path_ibfk_3` FOREIGN KEY (`section_id`) REFERENCES `learning_path_section` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `learning_path_to_group`(
`id` mediumint(8) unsigned NOT NULL auto_increment,
`learning_path_id` mediumint(8) unsigned NOT NULL,
`group_id` mediumint(8) unsigned NOT NULL,
PRIMARY KEY (`id`),
KEY `group_id` (`group_id`),
UNIQUE (`learning_path_id`, `group_id`),
CONSTRAINT `learning_path_to_group_ibfk_1` FOREIGN KEY (`learning_path_id`) REFERENCES `learning_path` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
CONSTRAINT `learning_path_to_group_ibfk_2` FOREIGN KEY (`group_id`) REFERENCES `group` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `learning_path_to_branch`(
`id` mediumint(8) unsigned NOT NULL auto_increment,
`learning_path_id` mediumint(8) unsigned NOT NULL,
`branch_id` mediumint(8) unsigned NOT NULL,
PRIMARY KEY (`id`),
KEY `branch_id` (`branch_id`),
UNIQUE (`learning_path_id`, `branch_id`),
CONSTRAINT `learning_path_to_branch_ibfk_1` FOREIGN KEY (`learning_path_id`) REFERENCES `learning_path` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
CONSTRAINT `learning_path_to_branch_ibfk_2` FOREIGN KEY (`branch_id`) REFERENCES `branch` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8;

CREATE TABLE `learning_path_approval_request`(
`id` int unsigned NOT NULL auto_increment,
`learning_path_id` mediumint unsigned NOT NULL,
`user_id` mediumint unsigned NOT NULL,
`created_at` int unsigned NOT NULL,
`last_updated_on` int unsigned NOT NULL,
PRIMARY KEY (`id`),
KEY `learning_path_id` (`learning_path_id`),
KEY `user_id` (`user_id`),
UNIQUE (`learning_path_id`, `user_id`),
CONSTRAINT `learning_path_approval_request_ibfk_1` FOREIGN KEY (`learning_path_id`) REFERENCES `learning_path` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
CONSTRAINT `learning_path_approval_request_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `learning_path_rule`(
`id` mediumint unsigned NOT NULL auto_increment,
`learning_path_id` mediumint unsigned NOT NULL,
`course_id` mediumint unsigned DEFAULT NULL,
`rule_type` varchar(255) NOT NULL,
`rule_learning_path_id` mediumint unsigned DEFAULT NULL,
`completion_percentage` float DEFAULT NULL,
`rule_set` int DEFAULT NULL,
PRIMARY KEY (`id`),
CONSTRAINT `learning_path_rule_ibfk_1` FOREIGN KEY (`learning_path_id`) REFERENCES `learning_path` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
CONSTRAINT `learning_path_rule_ibfk_2` FOREIGN KEY (`course_id`) REFERENCES `course` (`id`) ON UPDATE CASCADE ON DELETE CASCADE,
CONSTRAINT `learning_path_rule_ibfk_3` FOREIGN KEY (`rule_learning_path_id`) REFERENCES `learning_path` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
INDEX `learning_path_id` (`learning_path_id`),
INDEX `course_id` (`course_id`),
INDEX `rule_learning_path_id` (`rule_learning_path_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `ai_coach_question` (
`id` int unsigned NOT NULL auto_increment,
`type` varchar(50) NOT NULL,
`unit_id` mediumint unsigned NOT NULL,
`user_id` mediumint unsigned NOT NULL,
`conversation_id` varchar(255) NOT NULL,
`question` text NOT NULL,
`answers` json NOT NULL,
`created_at` bigint unsigned NOT NULL,
`updated_at` bigint unsigned default NULL,
PRIMARY KEY (`id`),
CONSTRAINT `ai_coach_question_ibfk_1` FOREIGN KEY (`conversation_id`) REFERENCES `chatbot_conversation_cache` (`conversation_id`) ON DELETE CASCADE ON UPDATE CASCADE,
KEY `conversation_id_index` (`conversation_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `user_to_integration`(
`user_id`             MEDIUMINT UNSIGNED NOT NULL,
`integration`         VARCHAR(255) NOT NULL,
`integration_user_id` VARCHAR(255) NOT NULL,
`created_at`          TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
PRIMARY KEY (`user_id`, `integration`),
CONSTRAINT `user_to_integration_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `learning_path_to_job` (
`id` mediumint(8) unsigned AUTO_INCREMENT NOT NULL,
`learning_path_id` mediumint(8) unsigned NOT NULL,
`job_id` mediumint(8) unsigned NOT NULL,
PRIMARY KEY (`id`),
UNIQUE (`learning_path_id`, `job_id`),
CONSTRAINT `learning_path_to_job_ibfk_1` FOREIGN KEY (`learning_path_id`) REFERENCES `learning_path` (`id`) ON DELETE CASCADE,
CONSTRAINT `learning_path_to_job_ibfk_2` FOREIGN KEY (`job_id`) REFERENCES `ai_job` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `event_tracker`(
`id` bigint unsigned  NOT NULL AUTO_INCREMENT,
`event_id` varchar(255) NOT NULL,
`event_type` varchar(255) NOT NULL,
`checkpoint` tinyint unsigned NOT NULL DEFAULT '0',
`metadata` json DEFAULT NULL,
`created_on` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
PRIMARY KEY (`id`),
UNIQUE KEY `event_id` (`event_id`),
KEY `event_type` (`event_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `migration_log` (
`version` BIGINT NOT NULL,
`migration_name` VARCHAR(100) DEFAULT NULL,
`start_time` TIMESTAMP NULL DEFAULT NULL,
`end_time` TIMESTAMP NULL DEFAULT NULL,
`breakpoint` TINYINT(1) NOT NULL DEFAULT 0,
PRIMARY KEY (`version`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE lti_lineitems (
id INT NOT NULL AUTO_INCREMENT,
unit_id mediumint(8) unsigned NOT NULL,
resourceId VARCHAR(1024) NOT NULL,
PRIMARY KEY (id),
CONSTRAINT lti_lineitem_ibfk_1 FOREIGN KEY (unit_id) REFERENCES unit (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `domain_packages` (
`id`            MEDIUMINT UNSIGNED NOT NULL AUTO_INCREMENT,
`domain_id`     MEDIUMINT UNSIGNED NOT NULL,
`group_key`     VARCHAR(20) NOT NULL,
`created_at`    INT UNSIGNED NOT NULL,
`updated_at`    INT UNSIGNED NOT NULL,

    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_domain_group` (`domain_id`, `group_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `package_licenses` (
`id`                MEDIUMINT UNSIGNED NOT NULL AUTO_INCREMENT,
`domain_package_id` MEDIUMINT UNSIGNED NOT NULL,
`quantity`          INT UNSIGNED       NOT NULL,
`valid_from`        INT UNSIGNED       NOT NULL,
`valid_until`       INT UNSIGNED       NOT NULL,
`status`            VARCHAR(50) NOT NULL DEFAULT 'active',
`created_by`        INT UNSIGNED       NULL,
`created_at`        INT UNSIGNED       NOT NULL,
`updated_by`        INT UNSIGNED       NULL,
`updated_at`        INT UNSIGNED       NOT NULL,
`consumed`          INT UNSIGNED       NOT NULL DEFAULT 0,

    PRIMARY KEY (`id`),
    CONSTRAINT `domain_packages_ibfk_1` FOREIGN KEY (`domain_package_id`) REFERENCES `domain_packages`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    KEY `pkg_status_until_idx` (`domain_package_id`, `valid_until`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `package_license_transactions` (
`id`                MEDIUMINT UNSIGNED NOT NULL AUTO_INCREMENT,
`package_license_id` MEDIUMINT UNSIGNED NOT NULL,
`quantity`          INT UNSIGNED       NULL,
`action`            VARCHAR(50)        NOT NULL,
`performed_by`      INT UNSIGNED       NULL,
`created_at`        INT UNSIGNED       NOT NULL,

    PRIMARY KEY (`id`),
    CONSTRAINT `fk_plt_package_license` FOREIGN KEY (`package_license_id`) REFERENCES `package_licenses`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    KEY `ix_plt_package_license_id` (`package_license_id`),
    KEY `ix_plt_action` (`action`),
    KEY `ix_plt_performed_by` (`performed_by`),
    KEY `ix_plt_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `license_consumptions` (
`id`                 INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
`package_license_id` MEDIUMINT UNSIGNED NOT NULL,
`user_id`            MEDIUMINT UNSIGNED NOT NULL,       
`course_id`          MEDIUMINT UNSIGNED NOT NULL,
`created_at`         INT UNSIGNED NOT NULL,
`updated_at`         INT UNSIGNED NOT NULL,

    CONSTRAINT fk_lc_package FOREIGN KEY (package_license_id) REFERENCES package_licenses(id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_lc_user    FOREIGN KEY (user_id)            REFERENCES user(id) ON DELETE CASCADE ON UPDATE CASCADE,

    UNIQUE KEY uq_lc_user_license (package_license_id, user_id),
    KEY ix_lc_package_license_id (package_license_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `course_metadata` (
`id`        MEDIUMINT UNSIGNED NOT NULL AUTO_INCREMENT,
`course_id` MEDIUMINT UNSIGNED NOT NULL,
`key`       VARCHAR(255)       NOT NULL,
`value`     VARCHAR(255)       NOT NULL,

    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_course_key` (`course_id`, `key`),
    KEY `idx_key` (`key`),
    CONSTRAINT `course_metadata_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `course`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `group` ADD KEY `branch_id` (`branch_id`);
ALTER TABLE `group` ADD CONSTRAINT `group_ibfk_1` FOREIGN KEY (`branch_id`) REFERENCES `branch` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;
ALTER TABLE `course` ADD KEY `intro_video_id` (`intro_video_id`);
ALTER TABLE `course` ADD CONSTRAINT `course_ibfk_4` FOREIGN KEY (`intro_video_id`) REFERENCES `file` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;
ALTER TABLE `course` ADD CONSTRAINT `course_ibfk_5` FOREIGN KEY (`open_sesame_id`) REFERENCES `open_sesame_course` (`open_sesame_id`) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE `unit_content` ADD KEY `file_id` (`file_id`);
ALTER TABLE `unit_content` ADD CONSTRAINT `unit_content_ibfk_1` FOREIGN KEY (`file_id`) REFERENCES `file` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;
ALTER TABLE `domain` ADD KEY `referrer_id` (`referrer_id`);
ALTER TABLE `domain` ADD CONSTRAINT `domain_ibfk_2` FOREIGN KEY (`referrer_id`) REFERENCES `referrer` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;
ALTER TABLE `skill_assessment_attempt` ADD CONSTRAINT `skill_assessment_attempt_ibfk_3` FOREIGN KEY (`current_attempt_question_id`) REFERENCES `skill_assessment_attempt_question` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;
ALTER TABLE `unit` ADD CONSTRAINT `unit_ibfk_8` FOREIGN KEY (`open_sesame_id`) REFERENCES `open_sesame_course` (`open_sesame_id`) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE `unit` ADD CONSTRAINT `unit_ibfk_9` FOREIGN KEY (`lti_registration_id`) REFERENCES `lti_registration` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;
ALTER TABLE `file` ADD CONSTRAINT `file_ibfk_5` FOREIGN KEY (`open_sesame_id`) REFERENCES `open_sesame_course` (`open_sesame_id`) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE `theme` ADD CONSTRAINT `theme_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `theme` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE `theme` ADD CONSTRAINT `theme_ibfk_2` FOREIGN KEY (`branch_id`) REFERENCES `branch` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
