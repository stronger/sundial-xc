<?php

final class HomeController extends Controller {

	/**
	 * @Public
	 * @Title "Posición global"
	 */
	public function index() {
		$this->view->isLoggedOn = (boolean)User::getCurrentUser()->id;
	}

}