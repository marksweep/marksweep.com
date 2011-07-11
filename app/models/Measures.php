<?php

namespace app\models;

class Measures extends \lithium\data\Model {

	public $_meta = array(
		'connection' => 'pellucid'
	);
	
	protected $_schema = array(
		'measure_id' => array('type' => 'integer', 'default' => null, 'null' => false),
		'author_id' => array('type' => 'integer', 'default' => null, 'null' => false),
		'parent_id' => array('type' => 'integer', 'default' => null, 'null' => false),
		'name_friendly' => array('type' => 'string', 'default' => null, 'null' => false),
		'name_long' => array('type' => 'string', 'default' => null, 'null' => false),
		'name_internal' => array('type' => 'string', 'default' => null, 'null' => false),
		'description_friendly' => array('type' => 'string', 'default' => null, 'null' => false),
		'description_long' => array('type' => 'string', 'default' => null, 'null' => false),
		'author_measure_id' => array('type' => 'string', 'default' => null, 'null' => false),
		'author_measure_specs' => array('type' => 'string', 'default' => null, 'null' => false),
		'rationale_long' => array('type' => 'string', 'default' => null, 'null' => false),
		'rationale_friendly' => array('type' => 'string', 'default' => null, 'null' => false),
		'more_is_better' => array('type' => 'string', 'default' => null, 'null' => false),
		'measure_type' => array('type' => 'string', 'default' => null, 'null' => false),
		'measure_incept_date' => array('type' => 'date', 'default' => null, 'null' => false),
		'measure_expiry_date' => array('type' => 'date', 'default' => null, 'null' => false),
		'nqf_endorsed' => array('type' => 'string', 'default' => null, 'null' => false),
		'nqf_endorsed_date' => array('type' => 'date', 'default' => null, 'null' => false),
		'created' => array('type' => 'string', 'date' => null, 'null' => false),
		'description_friendly' => array('type' => 'date', 'default' => null, 'null' => false)
	);
}