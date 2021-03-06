CREATE TABLE admin_activity (
	log_id MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
	log_date TIMESTAMP NOT NULL,
	admin_id VARCHAR(15) NOT NULL DEFAULT '',
	category CHAR(1) NOT NULL DEFAULT '',
	action CHAR(1) NOT NULL DEFAULT '',
	ref_id VARCHAR(15) NOT NULL DEFAULT '',
	note VARCHAR(100) DEFAULT NULL,
	PRIMARY KEY (log_id)
) ENGINE InnoDB DEFAULT CHARACTER SET utf8;
