<?php
/**
 * Service updater class that maintains 
 */
class ServiceUpdater {
	
	/**
	 * Refresh interval for a service.
	 * 
	 * @var integer
	 */
	public static $updateInterval = 300; // 5 minutes
	
	/**
	 * Fetches the specified user's feeds and updates the database.
	 * 
	 * @return void
	 */
	public static function updateFeeds($userId) {
		global $db;
		
		// extract all user's feeds from the database
		$feeds = $db->fetch_all("SELECT * FROM `user_feeds` WHERE `user_id`='".$userId."'");
		foreach ($feeds as $feed) {
			$serviceObj = BaseService::loadService($feed['service']);
			$serviceAuth = $serviceObj->getAuthenticator();
			$serviceAuth->authenticate(@unserialize($feed['auth_data']));
			
			$items = $serviceObj->fetchLatest($feed['last_update']);
			$lastFeedUpdate = strtotime($feed['last_update']);
			foreach ($items as $item) {
				$updated = strtotime($item->timestamp);
				if ($updated < $lastFeedUpdate) {
					break;
				}
				$item->feed_id = $feed['id'];
				$item->saveToDb();
			}
		}
	}
	
}
