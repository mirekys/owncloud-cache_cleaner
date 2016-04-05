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

namespace OCA\Cache_Cleaner\BackgroundJob;

use OC\Files\Filesystem;
use OC\Files\View;

use OCP\IConfig;
use OCP\ILogger;
use OCP\IUserManager;

use OCA\Cache_Cleaner\ChunkGc;

class CleanCache extends \OC\BackgroundJob\TimedJob {

	private $appName;
	private $logger;
	private $config;
	private $userManager;
	private $logCtx;
	
	/**
	 * @param string $appName
	 * @param ILogger $logger
	 * @param IConfig $config
	 * @param IUserManager $userManager
	 */
	public function __construct(
		string $appName = null,
		ILogger $logger = null,
		IConfig $config = null,
		IUserManager $userManager = null
	) {
		$this->appName = $appName? $appName : 'cache_cleaner';
		$this->logger = $logger? $logger : \OC::$server->getLogger();
		$this->config = $config? $config : \OC::$server->getConfig();
		$this->userManager = $userManager? $userManager : \OC::$server->getUserManager();
		$this->logCtx = array('app' => $this->appName);

		// Clean cache every 15 min (by default)
		$this->setInterval(
			$this->config->getSystemValue('chunkgc.period', 60*15));
	}

	/**
	 * Run the cleaning job
	 *
	 * @param array(string) $arg job arguments
	 */
	public function run($arg) {
		$offset = $this->config->getAppValue($this->appName, 'offset', 0);
		$batch = $this->config->getSystemValue('chunkgc.userlimit', 100);
		$timeLimit = $this->config->getSystemValue('chunkgc.timelimit', 60);
		$timeStart = time();
		$countDone = 0;
		$this->logger->info(
			'Chunk GC started [offset='.$offset.']', $this->logCtx);

		$users = $this->userManager->search('', $batch, $offset);
		if (count($users) !== $batch) {
			// Reached the end of userlist, start from 0
			$offset = 0;
			$usersFromStart =
				$this->userManager->search(
					'', $batch - count($users), $offset);
			$users = array_merge($users, $usersFromStart);
		}

		foreach ($users as $user) {
			if (time() > ($timeLimit + $timeStart)) { break; }
			$offset += 1;
			$uid = $user->getUID();
			\OC_Util::tearDownFS();
			if (!\OC_Util::setupFS($uid)) {
				$this->logger->warning(
					"Couldn't setup FS for " . $uid,
					$this->logCtx);
				continue;
			}
			try {
				$this->logger->debug(
					'Running chunk GC for: '.$uid,
					$this->logCtx);
				$gc = new ChunkGc();
				$gc->gc($uid);
				$countDone++;
			} catch (\Exception $e) {
				$this->logger->warning(
					'Exception when running '
					. 'chunk GC: '
					. $e->getMessage(),
					$this->logCtx);
			}
			\OC_Util::tearDownFS();
		}

		$this->config->setAppValue($this->appName, 'offset', $offset);
		$this->logger->info('Chunk GC finished [offset='.$offset.']'
			.' Processed ' . $countDone . ' users.', $this->logCtx);
	}
}
