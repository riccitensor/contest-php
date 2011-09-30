<?php

/** ***********************************************************************
 *  TODO: Cleanup this config and beautify the autoloader
 ** *********************************************************************** */

// this function tries to autoload classes whose definition is unknown to the interpreter at runtime
function autoload_plista_contest($className) {
	if (is_readable(dirname(__FILE__) . '/library/contest/' . $className . '.php')) {
		require_once dirname(__FILE__) . '/library/contest/' . $className . '.php';
	} elseif (is_readable(dirname(__FILE__) . '/library/' . $className . '.php')) {
		require_once dirname(__FILE__) . '/library/' . $className . '.php';
	} elseif (is_readable(dirname(__FILE__) . '/library/models/' . $className . '.php')) {
		require_once dirname(__FILE__) . '/library/models/' . $className . '.php';
	}
}
spl_autoload_register('autoload_plista_contest');

// this function is a simple wrapper around json_encode and tries to call __toJSON() on any object it gets passed first
function plista_json_encode($elem) {
	if (is_object($elem)) {
		if (is_callable(array($elem, '__toJSON'))) {
			return $elem->__toJSON();
		}
	}

	return json_encode($elem);
}

// defines the network timeout for HttpRequest.php
define('PLISTA_CONTEST_TIMEOUT', 0.5);
