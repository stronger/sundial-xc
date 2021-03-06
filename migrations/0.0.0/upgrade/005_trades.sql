CREATE TABLE trades (
	trade_id MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
	trade_date TIMESTAMP NOT NULL,
	status CHAR(1) DEFAULT NULL,
	member_id_from VARCHAR(15) NOT NULL DEFAULT '',
	member_id_to VARCHAR(15) NOT NULL DEFAULT '',
	amount DECIMAL(8,2) NOT NULL DEFAULT 0.00,
	category SMALLINT(4) UNSIGNED NOT NULL DEFAULT 0,
	description VARCHAR(255) DEFAULT NULL,
	type CHAR(1) NOT NULL DEFAULT '',
	PRIMARY KEY (trade_id)
) ENGINE InnoDB DEFAULT CHARACTER SET utf8;
