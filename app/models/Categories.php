<?php

namespace app\models;

class Categories extends \lithium\data\Model {

	public $validates = array();
	
	public static function allCategories() {
	
		return array(
			'Books' => 'Books',
			'Business' => 'Business',
			'Education' => 'Education',
			'Entertainment' => 'Entertainment',
			'Finance' => 'Finance',
			'Games' => 'Games',
			'Healthcare & Fitness' => 'Healthcare & Fitness',
			'Lifestyle' => 'Lifestyle',
			'Medical' => 'Medical',
			'Music' => 'Music',
			'Navigation' => 'Navigation',
			'News' => 'News',
			'Photography' => 'Photography',
			'Productivity' => 'Productivity',
			'Reference' => 'Reference',
			'Social Networking' => 'Social Networking',
			'Sport' => 'Sport',
			'Travel' => 'Travel',
			'Utilities' => 'Utilities',
			'Weather' => 'Weather'
		);
	
	}
}

?>