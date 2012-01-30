<?php
namespace li3_metrics\models;

class SiteMetric extends \lithium\data\Model {
    
	protected $_meta = array(
		'locked' => true
	);
	
    protected $_schema = array(
        '_id' => array('type' => 'id'),
		// url
		'url' => array('type' => 'string'),
		// browser
        'b' => array('type' => 'string'),
		// browser version
        'bv' => array('type' => 'string'),
		// operating system
        'os' => array('type' => 'string'),
		// mobile device... or not
        'm' => array('type' => 'boolean'),
		// language
        'l' => array('type' => 'string'),
		// page views (non-unique visits)
		'pv' => array('type' => 'number'),
		// created (first access for this IP at the url)
        'c' => array('type' => 'date')
    );
    
    public $validates = array(
    );
    
    public $search_schema = array(
		'url' => array(
			'weight' => 1
		)
	);
	
    /**
     * Returns the search schema for the model.
     * Note: If this model has been extended by another model then
     * the combined schema will be returned if that other model was
     * instantiated. The __init() method handles that.
     *
     * @param $field String The field for which to return the search schema for,
     * 			    if not provided, all fields will be returned
     * @return array
    */
    public function searchSchema($field=null) {
		$class =  __CLASS__;
		$self = $class::_object();
		if (is_string($field) && $field) {
			return isset($self->search_schema[$field]) ? $self->search_schema[$field] : array();
		}
		return $self->search_schema;
    }
    
}
?>