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
		//$this->loadHospitals($command);
		
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
			
			// Exists?
			$pellucid_measure = PellucidMeasures::first(array('conditions'=>array('_id'=>$measure->measure_id)));
			if (!$pellucid_measure) {
				$pellucid_measure = PellucidMeasures::create();
			}
			
			$data = $measure->data();
			$data["_id"] = $measure->measure_id;
			$pellucid_measure->save($data);

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
					)
				));
				
				echo "Entity id: $entity_id \n";
				//print_r($entity->data());
			
				// Get 8 latest values and attach footnotes
				$values = $this->loadValues(array('measure_id' => $measure->measure_id, 'entity_id' => $entity_id), array('limit'=>2));
				print_r($values);
				
				// Save incrementally to the current measure
				PellucidMeasures::update(
					array('Entities' => array(
						$entity->data() + array('Values' => $values)
					)),
					array('_id' => $measure->measure_id)
				);
				
				//$all_entities[$entity_id] = $entity->data();
				//$all_entities[$entity_id]['Values'] = $values;
				
			
			}
			
			//$data['Entities'] = $all_entities;
			
			break;
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
			$data['_id'] = $data['entity_id'];
					
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
			$data['Measures'] = $this->loadValues(array('entity_id' => $data['_id']), array('attachMeasures'=>true));
			
			$entity->save($data);
		}
		echo 'End hospital loop: ' . time() . "\n";
	
	
	}
	
	
	/**
	 * loadValues function.
	 * 
	 * @access protected
	 * @param mixed $_id
	 * @return void
	 */
	protected function loadValues($conditions = array(), $options = array()) {
		print_r($options);
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
			'order' => array('measure_id'=>'asc', 'date_end' => 'asc'),
			'limit' => $limit
		));
		$indexed_values = array();
		
		// Create date_end/date_start for all the values
		$latest_date_end = null;
		$latest_date_end_value = null;
		
		foreach($values as $id => $value){
			$indexed_values[$id] = $value->data();
			$indexed_values[$id]['date_range'] = $indexed_values[$id]['date_end'] . '|' . $indexed_values[$id]['date_start'];
			
			// Track the latest date_end found
			if ($indexed_values[$id]['date_end'] > $latest_date_end) {
				$latest_date_end = $indexed_values[$id]['date_end'];
				$latest_date_end_value = $indexed_values[$id];
			
			}
			
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
				$all_values[] = $measure->data() + array('Values' => $combined_values[$measure_id]) + array('Latest' => $latest_date_end_value);
				
			}
			
		// If we are not grouping by measures
		// This is the DEFAULT
		} else {
			$all_values[] = array(
				'Values' => $indexed_values,
				'Latest' => $latest_date_end_value
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