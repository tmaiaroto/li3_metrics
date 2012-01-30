<?php
/**
 * This class helps collect some metrics for the application.
 *
*/
namespace li3_metrics\extensions\util;

use li3_metrics\extensions\util\Browscap;
use li3_metrics\extensions\util\Language;
use li3_metrics\models\SiteMetric;

use MongoDate;

class Metrics {
    
    /**
     * Returns the visitor's information.
     * IP address, user agent, etc.
     *
     * @return array The visitor's information
    */
    public static function visitorInfo() {
        $info = array();
        
        // First, the easy one; IP address. Also check if behind HAPRoxy.
        $info['ip'] = $_SERVER['REMOTE_ADDR'];
        if(isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $info['ip'] = $_SERVER['HTTP_X_FORWARDED_FOR'];
        }
        
        // Next, a little more tricky; the User-Agent...
        $user_agent = '';
        
        // Use this awesome Browscap class that goes out and gets the browscap.ini file automatically and caches
        $browscap = new Browscap(LITHIUM_APP_PATH . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR . 'cache');
        $user_agent = $browscap->getBrowser($_SERVER['HTTP_USER_AGENT']);
        
        // return an empty array if its a crawler
        if($user_agent->Crawler) {
            return array();
        }
        
        // set the fields to save...note these are abbreviated for optimization, see ProjectMetric model for more info
        $info['b'] = $user_agent->Browser;
        $info['bv'] = $user_agent->MajorVer;
        $info['os'] = $user_agent->Platform;
        $info['m'] = $user_agent->isMobileDevice;
        $info['c'] = new MongoDate();
        
        // Then get the language based on the data their browser sends (the first will be the primary language)
        // We may collect all languages in the future....
        $language = Language::getLanguages('full_code');
        $info['l'] = (isset($language[0])) ? $language[0]:'';
            
        return $info;
    }
    
    /**
     * Stores metrics in the sites_metrics collection.
     * Important note: A unique index must be made on the _key field.
     * 
     * The _key field holds the visitor's ip address along with the project url
     * in order to provide a unique value. More importantly, a unique value per
     * visitor per project. With the unique index, we know metrics won't be saved
     * more than once per project per visitor ip.
     *
     * @param string $url The project URL
     * @return Boolean
    */
    public static function storeSiteMetrics($url=null) {
        if(empty($url)) {
            return false;
        }
        // Get some basic analytic data for the project.
        $visitor_info = Metrics::visitorInfo();
        // Set the url
        $visitor_info['url'] = $url;
        
        // for testing, make up fake ip and hit the project page.
        // $visitor_info['ip'] = '192.168.126.6';
        
        // set a unique key. a combination of ip address and project url
        // this field has a unique index on it so that only one entry per ip per project can exist
        // and it helps us figure out how many unique visitors (roughly) saw the project
        // NOTE: this was a _k field...but why not just convert our _id to it? that should work fine and save overhead
        //$visitor_info['_id'] = new MongoBinData($visitor_info['ip'] . '@' . $url);
        // ANOTHER NOTE: binary data actually increases the storage space used when the URL gets longer
        // and visitor ip + @ + project url is short enough really. AND you can read it too. so let's just use a string.
        // we'd use BinData() if we were trying to store an image or something on this collection instead of GridFS
        $visitor_info['_id'] = $visitor_info['ip'] . '@' . $url;
        
        // Save metric data for the project
        $document = SiteMetric::create();
        $save = $document->save($visitor_info);
		
		$update = SiteMetric::update(
			// query
			array('$inc' => array('pv' => 1)),
			// conditions
			array('url' => $url),
			array('atomic' => false)
		);
		
		return $update;
    }
    
}
?>