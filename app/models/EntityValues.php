<?php

namespace app\models;

class EntityValues extends \lithium\data\Model {

	public $_meta = array(
		'connection' => 'pellucid'
	);
	
	protected $_schema = array(
		'measure_id' => array('type' => 'integer', 'default' => null, 'null' => false),
		'entity_id' => array('type' => 'integer', 'default' => null, 'null' => false),
		'date_start' => array('type' => 'date', 'default' => null, 'null' => false),
		'date_end' => array('type' => 'date', 'default' => null, 'null' => false),
		'source_id' => array('type' => 'integer', 'default' => null, 'null' => false),
		'is_comparator' => array('type' => 'integer', 'default' => null, 'null' => false),
		'is_private' => array('type' => 'integer', 'default' => null, 'null' => false),
		'value' => array('type' => 'decimal', 'default' => null, 'null' => false),
		'unit' => array('type' => 'string', 'default' => null, 'null' => false),
		'numerator' => array('type' => 'integer', 'default' => null, 'null' => false),
		'denominator' => array('type' => 'integer', 'default' => null, 'null' => false),
		'sig_var_state' => array('type' => 'integer', 'default' => null, 'null' => false),
		'sig_var_nation' => array('type' => 'integer', 'default' => null, 'null' => false),
		'sig_change' => array('type' => 'integer', 'default' => null, 'null' => false),
		'std_dev_from_state' => array('type' => 'decimal', 'default' => null, 'null' => false),
		'std_dev_from_nation' => array('type' => 'decimal', 'default' => null, 'null' => false),
		'std_dev' => array('type' => 'decimal', 'default' => null, 'null' => false),
		'lower_ci' => array('type' => 'decimal', 'default' => null, 'null' => false),
		'upper_ci' => array('type' => 'decimal', 'default' => null, 'null' => false),
		'percentile_nation' => array('type' => 'integer', 'default' => null, 'null' => false),
		'percentile_state' => array('type' => 'integer', 'default' => null, 'null' => false),
		'rank_place_nation' => array('type' => 'integer', 'default' => null, 'null' => false),
		'rank_denominator_nation' => array('type' => 'integer', 'default' => null, 'null' => false),
		'rank_place_state' => array('type' => 'integer', 'default' => null, 'null' => false),
		'rank_denominator_state' => array('type' => 'integer', 'default' => null, 'null' => false),
		'created' => array('type' => 'date', 'default' => null, 'null' => false),
		'modified' => array('type' => 'date', 'default' => null, 'null' => false),
		'batch_id' => array('type' => 'integer', 'default' => null, 'null' => false)
	);
}