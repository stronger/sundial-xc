ALTER TABLE member ADD COLUMN forgot_token VARCHAR(20) DEFAULT NULL AFTER password;
ALTER TABLE member ADD COLUMN forgot_expiry DATETIME DEFAULT NULL AFTER forgot_token;
