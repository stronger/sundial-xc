<?php

final class LoginForgotPasswordForm extends Form {

	public function __construct() {
		parent::__construct();
		$this->addElement("text", "email", "Email", array("size" => 30, "maxlength" => 60));
	}

}