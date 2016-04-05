<?php
/**
 * ownCloud - cache_cleaner
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Miroslav Bauer @ CESNET <bauer@cesnet.cz>
 * @copyright Miroslav Bauer @ CESNET 2016
 */

namespace OCA\Cache_Cleaner;

use OC\Files\Filesystem;
use OC\Files\View;

class ChunkGc extends \OC\Cache\File {

	/**
	 * Runs GC for a specific user
	 *
	 * @param string $uid user id
	 * @throws \OC\ForbiddenException
	 */
	public function gc($uid=null) {
		$storage = $this->getStorage($uid);
		parent::gc();
	}

	/**
	 * Returns the cache storage for a user
	 *
	 * @param string $user user id
	 * @return \OC\Files\View cache storage
	 * @throws \OC\ForbiddenException
	 * @throws \OC\User\NoUserException
	 */
	protected function getStorage($uid=null) {
		if (($uid === null) || ($uid === '')) { 
			return parent::getStorage();
		}
		$rootView = new View();
		Filesystem::initMountPoints($uid);
		if (!$rootView->file_exists('/' . $uid . '/cache')) {
			$rootView->mkdir('/' . $uid . '/cache');
		}
		$this->storage = new View('/' . $uid . '/cache');
		return $this->storage;
	}
}
