<?php

namespace app\models;

class FootnotesValues extends \lithium\data\Model {

	public $_meta = array(
		'connection' => 'pellucid'
	);
	
	protected $_schema = array(
		'measure_id'  => array('type' => 'integer'),		
		'entity_id' => array('type' => 'integer', 'default' => null, 'null' => false),
		'date_start' => array('type' => 'date', 'default' => null, 'null' => false),
		'date_end' => array('type' => 'date', 'default' => null, 'null' => false),
		'footnote_id' => array('type' => 'integer', 'default' => null, 'null' => false)
	);
}