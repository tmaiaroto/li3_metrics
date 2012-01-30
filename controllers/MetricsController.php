<?php
namespace li3_metrics\controllers;

use li3_metrics\models\SiteMetric;
use li3_metrics\extensions\util\Metrics;
use li3_flash_message\extensions\storage\FlashMessage;
use lithium\util\Inflector;
use MongoDate;

/**
 * The MetricsController is responsible just for reviewing the visitor metrics
 * for the site.
 */
class MetricsController extends \lithium\action\Controller {
	
	public function admin_index() {
		$this->_render['layout'] = 'admin';
		
		$conditions = array();
		// If a search query was provided, search all "searchable" fields (any field in the model's $search_schema property)
		// NOTE: the values within this array for "search" include things like "weight" etc. and are not yet fully implemented...But will become more robust and useful.
		// Possible integration with Solr/Lucene, etc.
		if((isset($this->request->query['q'])) && (!empty($this->request->query['q']))) {
			$search_schema = SiteMetric::searchSchema();
			$search_conditions = array();
			// For each searchable field, adjust the conditions to include a regex
			foreach($search_schema as $k => $v) {
				// TODO: possibly factor in the weighting later. also maybe note the "type" to ensure our regex is going to work or if it has to be adjusted (string data types, etc.)
				// var_dump($k);
				// The search schema could be provided as an array of fields without a weight
				// In this case, the key value will be the field name. Otherwise, the weight value
				// might be specified and the key would be the name of the field.
				$field = (is_string($k)) ? $k:$v;
				$search_regex = new \MongoRegex('/' . $this->request->query['q'] . '/i');
				$conditions['$or'][] = array($field => $search_regex);
			}
		}
		
		$limit = 25;
		$page = $this->request->page ?: 1;
		$order = array('c' => 'desc');
		$total = SiteMetric::count(compact('conditions'));
		$documents = SiteMetric::all(compact('conditions','order','limit','page'));
		
		$page_number = (int)$page;
		$total_pages = ((int)$limit > 0) ? ceil($total / $limit):0;
		
		
		//$total_pageviews = SiteMetric::find('count');
		$this->set(compact('documents', 'total', 'page', 'limit', 'total_pages'));
	}
	
	/**
	 * Returns metrics for a specific URL.
	 * 
	*/
	public function admin_url($url=null) {
		$this->_render['layout'] = 'admin';
		
		if(empty($url)) {
			FlashMessage::write('Invalid URL specified.', array(), 'default');
			$this->redirect(array('controller' => 'metrics', 'action' => 'index', 'admin' => true));
		}
		
		// Can't exactly pass urls as params
		// $url = urldecode($url); // <-- doesn't always work. ex. any url that ends in .php
		$url = unserialize(gzuncompress(stripslashes(base64_decode(strtr($url, '-_,', '+/=')))));
		
		$unique_visitors = SiteMetric::find('count', array('conditions' => array('url' => $url)));
		
		$this->set(compact('unique_visitors'));
	}
	
}
?>