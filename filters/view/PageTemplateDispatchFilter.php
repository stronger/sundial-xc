<?php

/**
 * Dispatch filter picking up template and setting up View object on controller
 *
 * Picks up template by looking at @Page annotation.
 * If no annotation picks up template using the following convention: pages/[controller]/[action].phtml
 * where [controller] is lower case and does not have the suffix "Controller".
 *
 * Warns in case when no template could be found.
 *
 * @author Michał Rudnicki <michal.rudnicki@epsi.pl>
 */
final class PageTemplateDispatchFilter implements DispatchFilter {

	public function before() {
		$dispatcher = Dispatcher::getInstance();

		// discover page template based on @Page annotation or by obeying naming convention
		$pageAnnotation = $dispatcher->getAnnotation("Page");
		if ($pageAnnotation) {
			$pageTemplate = "pages/{$pageAnnotation}";
		} else {
			$pageTemplate = "pages/" . $dispatcher->getControllerName() . "/" . $dispatcher->getActionName();
		}

		// check if template file exists
		if (file_exists(ROOT_DIR . "/templates/" . $pageTemplate . View::getExtension())) {
			Debug::log("Found page $pageTemplate", Debug::INFO);
			$view = new View($pageTemplate);
			$controller = $dispatcher->getController();
			$controller->setView($view);
			PageView::getInstance()->displayPage($view);
		} else {
			Debug::log("Page $pageTemplate not present", Debug::WARNING);
		}
	}

	public function after() { }

}