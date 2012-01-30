<?php
use li3_metrics\extensions\util\Metrics;
use lithium\action\Dispatcher;

Dispatcher::applyFilter('_callable', function($self, $params, $chain) {
    $request = $params['request'];
	
	// Note: It's important to first take access into consideration before saving metric info.
	// Someone who tries to access a protected area, but isn't successful, shouldn't really be counted.
	// Also, don't count the metrics controller pages at all.
	if($request->controller != 'metrics') {
		// Here is really the only call you need.
		Metrics::storeSiteMetrics($request->url);
	}
	
    return $chain->next($self, $params, $chain);
});
?>