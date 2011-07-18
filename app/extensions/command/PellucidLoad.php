<?php
/**
 * 
 *
 */
 
namespace app\extensions\command;
use app\models\EntityHospitals;
use app\models\EntityHospitalAttributes;
use app\models\EntityValues;
use app\models\Measures;
use app\models\Footnotes;
use app\models\FootnotesValues;
use app\models\PellucidEntities;
use app\models\PellucidMeasures;
use \lithium\util\Set;

class PellucidLoad extends \lithium\console\Command {
	
	/**
	 * Auto run the help command.
	 *
	 * @param string $command Name of the command to return help about.
	 * @return void
	 */
	public function run($command = null) {
		
		// TODO Make this splittable by command so you can run multiple chunks on 
		// different threads
		$this->loadHospitals($command);
		
		$this->loadMeasures($command);
		
		
	}
	
	/*! ----- Load Measures ----- */
	
	
	/**
	 * loadMeasures function.
	 * 
	 * @access public
	 * @param mixed $command (default: null)
	 * @return void
	 */
	public function loadMeasures($command = null) {
	

		// Find all measures
		$measures = Measures::all();
		$count = Measures::count();
		echo 'Count: ' . $count . "\n";
		echo 'Start measures loop: ' . time() . "\n";
		
		// Attach measure groups
		
		// Loop through the measures and get the latest value for
		// every entity that reports on this measure
		foreach ($measures as $measure) {
			
			$data = $measure->data();
			$data["_id"] = $measure->measure_id;
			
			// Exists?
			$pellucid_measure = PellucidMeasures::first(array(
				'conditions' => array('_id'=>$measure->measure_id),
				'fields' => array(
					'_id'
				
				)));
			
			// Doesn't exist - create and save
			if (!$pellucid_measure) {
				$pellucid_measure = PellucidMeasures::create();
				$pellucid_measure->save($data);
			} else {
			// Just update
				PellucidMeasures::update(
					$data,
					array('_id' => $measure->measure_id)
				);
			}
			

			$data = null;
		
			// Find entities for this measure
			$values = EntityValues::all(
				array('conditions'=> array(
					'measure_id' => $measure->measure_id
				
				), 'fields' => array(
					'entity_id'
				))
			);
			
			//! TODO: Rework when "distinct" becomes available
			$entity_ids = Set::extract($values->data(), '/entity_id');
			
			$entity_ids = array_flip(array_flip($entity_ids));
			
			$all_entities = array();
			
			// Only grab latest value? or set of 8
			foreach ($entity_ids as $entity_id) {
			
				// Grab entity information
				$entity = PellucidEntities::first(array(
					'conditions' => array(
						'_id' => "$entity_id"
					),
					'fields' => array('_id', 'EntityAttributes', 'address', 'aha_id', 'aha_service_id', 'aha_sys_id', 'aka', 'city', 'closed_date', 'county_fips', 'county_name', 'created', 'entity_id', 'entity_type', 'geo_lat', 'geo_long', 'hrr_code', 'hsa_code', 'local_id', 'modified', 'mpn_id', 'name', 'npi_id', 'phone_number', 'state', 'zipcode')
					
				));
				
				// Do we have this entity?
				if ($entity) {
					//print_r($entity->data());
				
					// Get 8 latest values and attach footnotes
					//$values = $this->loadValues(array('measure_id' => $measure->measure_id, 'entity_id' => $entity_id), array('limit'=>1));
				
					// Save incrementally to the current measure
					/*
					PellucidMeasures::update(
						array('Entities.' . $entity_id => 
							$entity->data() + array('Values' => $values[0]['Values'])
						),
						array('_id' => $measure->measure_id)
					);
					*/
					PellucidMeasures::update(
						array('Entities.' . $entity_id => 
							$entity->data()
						),
						array('_id' => $measure->measure_id)
					);
				} else {
				
					echo 'ERROR: Unable to locate entity_id: ' . $entity_id . "\n";
				
				}		
					
				//$all_entities[$entity_id] = $entity->data();
				//$all_entities[$entity_id]['Values'] = $values;
				
			
			}
			
			//$data['Entities'] = $all_entities;
			
		}
	
		
		echo 'End measures loop: ' . time() . "\n";
	}
	
	/*!----- Load Hospitals ----- */
	
	/**
	 * loadHospitals function.
	 * 
	 * @access public
	 * @param mixed $command (default: null)
	 * @return void
	 */
	public function loadHospitals($command = null) {
	
		// Grab all entity hospitals from mysql
		$hospitals = EntityHospitals::all();
		$count = EntityHospitals::count();
		echo 'Count: ' . $count . "\n";
		echo 'Start hospital loop: ' . time() . "\n";
		
		// Loop through the hospitals
		foreach ($hospitals as $hospital) {

			// Write this data out to mongo
			$data = $hospital->data();
			$entity_id = $data['entity_id'];
			$data['_id'] = $entity_id;
					
			// Grab any attribtues associated with this hospital
			$attributes = EntityHospitalAttributes::all(array('conditions'=>array()));
			
			
			// Grab values associated with this hospital
			
			// (Cache this)
			// Grab Measures associated with these values
			
			// Exists?
			$entity = PellucidEntities::first(array('conditions'=>array('_id'=>$data['_id'])));
			if (!$entity) {
				$entity = PellucidEntities::create();
			}
			
			// Combine attributes for the entity
			$data['EntityAttributes'] = $this->combineAttributes($data['_id']);
			
			// Grab values associated with this entity and
			// linked to measure information
			//$data['Measures'] = $this->loadValues(array('entity_id' => $data['_id']), array('attachMeasures'=>true));
			
			// Find the measures for this entity
			$measures = $this->findReportedMeasures($entity_id);
			
			// Loop through the measures for each hospital
			foreach ($measures as $measure_id) {
				$data['Measures'][$measure_id . '|' . $entity_id] = $this->loadEntityValues($entity_id, $measure_id);
				
				// Lookup the measure information
				$measure_info = Measures::first(array(
					'conditions' => array(
						'measure_id' => $measure_id
					)
				));
				$data['Measures'][$measure_id . '|' . $entity_id]['Measure'] = $measure_info->data();
				
			}
			
			print_r($data);
			$entity->save($data);
		}
		echo 'End hospital loop: ' . time() . "\n";
	
	
	}
	
	
	/**
	 * findReportedMeasures function.
	 * 
	 * @access protected
	 * @param mixed $entity_id
	 * @return void
	 */
	protected function findReportedMeasures($entity_id) {
	
		$values = EntityValues::all(array(
			'conditions' => array(
				'entity_id' => $entity_id
			),
			'fields' => array('measure_id')
		));
		$measure_ids = null;
		if ($values) {
			$measure_ids = Set::extract($values->data(), '/measure_id');
			print_r($measure_ids);
		} 
		return $measure_ids;
	}
	
	
	/**
	 * loadEntityValues function
	 * Loads up the entities values by measure_id
	 *
	 */
	protected function loadEntityValues($entity_id, $measure_id, $options = array()) {
	
		// Parse some options
		$limit = null;
		if (isset($options['limit'])) {
			$limit = $options['limit'];
		}
		$conditions = array();
		if (isset($options['additional_conditions'])){
			$conditions = $options['additional_conditions'];
		}
		
		$conditions['measure_id'] = $measure_id;
		$conditions['entity_id'] = $entity_id;
		
		// Grab all the values for this measure/ entity combination
		$values = EntityValues::all(array(
			'conditions' => $conditions,
			'order' => array('date_end' => 'desc', 'date_start' => 'desc')
		));
		$indexed_values = array();
		
		// Create some variables to store the latest value for a given
		// measure/entity combination
		$latest_date_end = null;
		$latest_date_end_value = null;
		
		// Create date_end/date_start indexes for all the values for easy reference
		foreach ($values as $id => $value) {
			$daterange = $value->date_end . '|' . $value->date_start;
			$indexed_values[$daterange] = $value->data();
			
			// Grab footnotes for each value
			$footnotes = FootnotesValues::all(
				array('conditions'=> array(
					'measure_id' => $value->measure_id,
					'entity_id' => $value->entity_id,
					'date_start' => $value->date_start,
					'date_end' => $value->date_end
				), 'fields' => array(
					'footnote_id'
				))
			);
			
			$indexed_values[$daterange]['Footnotes'] = $footnotes->data();
			
			// Check to see if we should update the latest date end
			if (!isset($latest_date_end) || $indexed_values[$daterange]['date_end'] > $latest_date_end) {
				$latest_date_end = $indexed_values[$daterange]['date_end'];
				$latest_date_end_value = $indexed_values[$daterange];
			}
		}
		
		// Attach latest value
		$indexed_values['Latest'] = $latest_date_end_value;
		
		// Collate the values together
		$all_values['Values'] = $indexed_values;
		
		return $all_values;
		
	} 
	 
	/**
	 * loadValues function.
	 * 
	 * @access protected
	 * @param mixed $_id
	 * @return void
	 */
	protected function loadValues($conditions = array(), $options = array()) {
		// Parse some options
		$limit = null;
		if (isset($options['limit'])) {
			$limit = $options['limit'];
		}

		// Cache this
		$all_footnotes = Footnotes::all();
		$all_footnotes = Set::combine($all_footnotes->data(), '/footnote_id', '/');
		
		$values = EntityValues::all(array(
			'conditions' => $conditions,
			'order' => array('measure_id'=>'asc', 'date_end' => 'desc'),
			'limit' => $limit
		));
		$indexed_values = array();
		
		// Create date_end/date_start for all the values
		$latest_date_end = array();
		$latest_date_end_value = null;
		
		foreach($values as $id => $value){
			
			$indexed_values[$id] = $value->data();
			$indexed_values[$id]['date_range'] = $indexed_values[$id]['date_end'] . '|' . $indexed_values[$id]['date_start'];
			
			$measure_id = $indexed_values[$id]['measure_id'];
			
			// Find and attach footnotes
			$footnotes = FootnotesValues::all(
				array('conditions'=> array(
					'measure_id' => $value->measure_id,
					'entity_id' => $value->entity_id,
					'date_start' => $value->date_start,
					'date_end' => $value->date_end
				), 'fields' => array(
					'footnote_id'
				))
			);
			// Track the latest date_end found per measure
			if (!isset($latest_date_end[$measure_id]) || $indexed_values[$id]['date_end'] > $latest_date_end[$measure_id]) {
				$latest_date_end[$measure_id] = $indexed_values[$id]['date_end'];
				$latest_date_end_value[$measure_id] = $indexed_values[$id];
				$latest_date_end_value[$measure_id]['Footnotes'] = $footnotes->data();
			}
			
			$indexed_values[$id]['Footnotes'] = $footnotes->data();
		}
		
		// If we are grouping be measures (attachMeasures)
		// Then we organize our values by measure_id
		if (isset($options['attachMeasures'])) {
			$measure_ids = array();
			if (count($indexed_values) > 0) {
				$combined_values = Set::combine($indexed_values, '/date_range', '/', '/measure_id');
				
				// Grab measures related
				$measure_ids = array_keys($combined_values);
			}
			
			
			
			$all_values = array();
		
			// Cache these measure_ids;
			foreach ($measure_ids as $measure_id) {
			
				// If the measure_id is loaded in the cache
				
				// else load up the measure info
				$measure = Measures::first(
					array(
						'conditions' => array(
							'measure_id' => $measure_id
						),
						'order' => array(
							'measure_id' => 'desc'
						)
					)
				);
				
				// Measures are large array
				$all_values[$measure_id . '|' . $entity_id] = $measure->data() + array('Values' => $combined_values[$measure_id]) + array('Latest' => $latest_date_end_value[$measure_id]);
				
			}
			
		// If we are not grouping by measures
		// This is the DEFAULT
		} else {
			$indexed_values = Set::combine($indexed_values, '/date_range', '/');
			$all_values[] = array(
				'Values' => $indexed_values,
				'Latest' => $latest_date_end_value[$measure_id]
			);
		}
		
		return $all_values;
		
	}
	
	
	/**
	 * combineAttributes function.
	 * 
	 * @access protected
	 * @param mixed $_id
	 * @return void
	 */
	protected function combineAttributes($_id) {
		//echo $_id . "\n";
		$attribs = EntityHospitalAttributes::all(array('conditions'=>array('entity_id'=>$_id)));
		
		if ($attribs) {
			$attribs = Set::combine($attribs->data(), '/attribute_id', '/value', '/attribute');
		}
		
		return $attribs;
	
	}
}