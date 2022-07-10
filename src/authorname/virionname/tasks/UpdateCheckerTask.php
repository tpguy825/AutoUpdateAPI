<?php

declare(strict_types=1);

namespace tpguy825\AutoUpdateAPI\tasks;

use Exception;
use tpguy825\AutoUpdateAPI\Main;
use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;
use pocketmine\utils\Internet;
use function curl_close;
use function curl_errno;
use function curl_error;
use function curl_exec;
use function curl_init;
use function curl_setopt_array;
use function json_decode;

class UpdateCheckerTask extends AsyncTask {
	private $main;

	/**
	 * @throws Exception
	 */
	public function onRun(): void {
		$this->main = Main::getInstance();
		$result = Internet::getURL("https://api.github.com/repos/tpguy825/AutoUpdateAPI/releases/latest", 10, ["AutoUpdateAPI by tpguy825 v".$this->main->version.", php ".PHP_VERSION]);
		if ($result->getCode() !== 200) {
			throw new Exception("Could not check for updates: Received HTTP code " . $result->getCode());
		}
		$data = json_decode($result->getBody(), true);
		$this->setResult($data);
	}

	public function onCompletion(): void {
		if ($this->main instanceof Main) {
            $this->main->onUpdateCheckComplete($this->getResult());
        }
	}
}