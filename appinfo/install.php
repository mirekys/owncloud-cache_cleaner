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

namespace OCA\Cache_Cleaner\AppInfo;

\OC::$server->getJobList()->add('OCA\Cache_Cleaner\BackgroundJob\CleanCache');
