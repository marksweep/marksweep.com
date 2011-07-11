<?php
/**
 * Lithium: the most rad php framework
 *
 * @copyright     Copyright 2011, Union of RAD (http://union-of-rad.org)
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 */

use lithium\core\ErrorHandler;
use lithium\action\Response;
use lithium\net\http\Media;

error_reporting(-1);

ErrorHandler::apply('lithium\action\Dispatcher::run', array(), function($info, $params) {
	$response = new Response(array(
		'request' => $params['request'],
		'status' => $info['exception']->getCode()
	));

	Media::render($response, compact('info', 'params'), array(
		'controller' => '_errors',
		'template' => 'development',
		'layout' => 'error',
		'request' => $params['request']
	));
	return $response;
});

/**
 * Prints out debug information about given variable.
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright 2005-2010, Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright 2005-2010, Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       cake
 * @subpackage    cake.cake
 * @since         CakePHP(tm) v 0.2.9
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
function debug($data){
        $calledFrom = debug_backtrace();
        echo '<pre><strong>' . substr(str_replace(ROOT, '', $calledFrom[0]['file']), 1) . '</strong>';
        echo ' (line <strong>' . $calledFrom[0]['line'] . '</strong>)';
        echo "\n". str_replace('<', '&lt;', str_replace('>', '&gt;', print_r($data, true))) . "\n</pre>\n";
}
?>