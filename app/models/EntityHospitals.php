<?php

namespace app\models;

class EntityHospitals extends \lithium\data\Model {

	public $_meta = array(
		'connection' => 'pellucid'
	);
	
	protected $_schema = array(
		'entity_id'  => array('type' => 'id'), // required for Mongo
		'aha_id' => array('type' => 'string', 'default' => null, 'null' => false),
		'mpn_id' => array('type' => 'string', 'default' => null, 'null' => false),
		'npi_id' => array('type' => 'string', 'default' => null, 'null' => false),
		'local_id' => array('type' => 'string', 'default' => null, 'null' => false),
		'aha_sys_id' => array('type' => 'string', 'default' => null, 'null' => false),
		'aha_service_id' => array('type' => 'string', 'default' => null, 'null' => false),
		'name' => array('type' => 'string', 'default' => null, 'null' => false),
		'aka' => array('type' => 'string', 'default' => null, 'null' => false),
		'address' => array('type' => 'string', 'default' => null, 'null' => false),
		'city' => array('type' => 'string', 'default' => null, 'null' => false),
		'state' => array('type' => 'string', 'default' => null, 'null' => false),
		'zipcode' => array('type' => 'string', 'default' => null, 'null' => false),
		'county_name' => array('type' => 'string', 'default' => null, 'null' => false),
		'county_fips' => array('type' => 'string', 'default' => null, 'null' => false),
		'phone_number' => array('type' => 'string', 'default' => null, 'null' => false),
		'entity_type' => array('type' => 'string', 'default' => null, 'null' => false),
		'hsa_code' => array('type' => 'string', 'default' => null, 'null' => false),
		'hrr_code' => array('type' => 'string', 'default' => null, 'null' => false),
		'geo_lat' => array('type' => 'decimal', 'default' => null, 'null' => false),
		'geo_long' => array('type' => 'decimal', 'default' => null, 'null' => false),
		'closed_date' => array('type' => 'date', 'default' => null, 'null' => false),
		'created' => array('type' => 'date', 'default' => null, 'null' => false),
		'modified' => array('type' => 'date', 'default' => null, 'null' => false)
	);
}