<?php

namespace app\models;

class Footnotes extends \lithium\data\Model {

	public $_meta = array(
		'connection' => 'pellucid'
	);
	
	protected $_schema = array(
		'footnote_id'  => array('type' => 'id'), // required for Mongo
		'text' => array('type' => 'string', 'default' => null, 'null' => false),
		'source_text' => array('type' => 'string', 'default' => null, 'null' => false),
		'source' => array('type' => 'string', 'default' => null, 'null' => false),
		'last_edit_date' => array('type' => 'date', 'default' => null, 'null' => false),
		'created' => array('type' => 'string', 'default' => null, 'null' => false),
		'modified' => array('type' => 'string', 'default' => null, 'null' => false)
	);
}