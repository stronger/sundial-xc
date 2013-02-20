<?php

final class ContactController extends Controller {

	/**
	 * @Public
	 * @Title "Ponte en contacto con nosotros"
	 */
	public function index() {
		$config = Config::getInstance();
		$form = new ContactForm();

		$this->page->name = $config->site->title;
		$this->view->form = $form;

		if (!$form->validate()) {
			return;
		}

		$form->freeze();
		$form->process();
		$values = $form->getValues();
		$howHeard = $form::howHeard();

		$mailed = mail(EMAIL_ADMIN, SITE_SHORT_TITLE ." Formulario de Contacto", "De: ". $values["name"]. "\n". "Teléfono: ". $values["phone"] ."\n". "Como nos conoció: ". $howHeard[$values["how_heard"]] ."\n\n". wordwrap($values["message"], 64) , "From:". $values["email"]);
		$mailed
			? $message = "Muchas gracias."
			: $message = "Hubo un problema enviando el mensaje. Estas seguro que has insertado bién tu dirección de correo?";
		PageView::getInstance()->setMessage($message);
	}

	/**
	 * @Title "Enviar correo a socios"
	 * @Level 1
	 */
	public function all() {
		$form = new ContactAllForm();
		$this->view->form = $form;
		$this->view->siteName = Config::getInstance()->site->title;

		if (!$form->validate()) {
			return;
		}
		$form->freeze();
		$form->process();
		$values = $form->exportValues();

		$filter = new UserFilter();
		$filter->active();
		$users = User::filter($filter);

		$message = new EmailMessage(EMAIL_ADMIN, $values["subject"], wordwrap($values["message"], 64));
		$message->toAll($users);
		$message->save();

		PageView::setMessage("Tu mensaje ha sido enviado.");
	}

}