<?php

namespace app\controllers;

use app\models\MForms;
use app\models\Categories;

/**
 * MFormsController class.
 */
class MFormsController extends \lithium\action\Controller {

	public function index() {
		$mforms = MForms::all();
		
		return compact('mforms', 'categories');
	}

	public function view($id = null) {
		
		if (!$id) {
			$this->redirect(array('MForms::index'));
		}
		
		$mform = MForms::first(array('id'=>$id));
		return compact('mform');
	}

	public function add() {
		$mform = MForms::create();

		if (($this->request->data) && $mform->save($this->request->data)) {
			$this->redirect(array('MForms::view', 'args' => $mform->id));
		}
		
		$categories = Categories::allCategories();
		return compact('mform', 'categories');
	}

	public function edit($id = null) {
		$mform = MForms::first(array('id'=>$id));

		if (!$mform) {
			$this->redirect('MForms::index');
		}
		if (($this->request->data) && $mform->save($this->request->data)) {
			$this->redirect(array('MForms::view', 'args' => array($mform->id)));
		}
		return compact('mform');
	}

	public function delete() {
		if (!$this->request->is('post') && !$this->request->is('delete')) {
			$msg = "MForms::delete can only be called with http:post or http:delete.";
			throw new DispatchException($msg);
		}
		MForms::find($this->request->id)->delete();
		$this->redirect('MForms::index');
	}
	
	public function updates() {
		
	
	}
	
	public function category($category = null) {
	
		// Default to Most Recent if category is not set
		if (!$category) {
			$category = 'Most Recent';
		}
		
		// Look up forms which are in a particular category
		
	
	}
}

?>