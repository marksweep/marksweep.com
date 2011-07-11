<?php

namespace app\models;

class EntityHospitalAttributes extends \lithium\data\Model {

	public $_meta = array(
		'connection' => 'pellucid'
	);
	
	protected $_schema = array(
		'attribute_id'  => array('type' => 'id'), // required for Mongo
		'entity_id' => array('type' => 'integer', 'default' => null, 'null' => false),
		'attribute' => array('type' => 'string', 'default' => null, 'null' => false),
		'value' => array('type' => 'string', 'default' => null, 'null' => false)
	);
}