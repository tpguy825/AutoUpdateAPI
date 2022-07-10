<?php
declare(strict_types=1);

namespace tpguy825\AutoUpdateAPI;

use pocketmine\plugin\PluginBase;
use Exception;
use tpguy825\AutoUpdateAPI\tasks\UpdateCheckerTask;

final class Main extends PluginBase{

    private $instance = null;
    public $version = "0.1.0";

    public function checkUpdate() {
		//if ($this->config->isCheckUpdate()) {
			try {
				$this->getServer()->getAsyncPool()->submitTask(new UpdateCheckerTask);
			} catch (Exception $e) {
				$this->getLogger()->warning($e->getMessage());
			}
		//}
	}

    public static function getInstance(): Main {
        return Main::$instance;
    }

    public function onLoad(): void {
        $this->instance = $this;
    }

    public function onUpdateCheckComplete(?array $data): void {
        if ($data !== null) {
            $this->getLogger()->info("New version available: " . $data["tag_name"]);
            $this->getLogger()->info("Download link: " . $data["html_url"]);
        } else {
            $this->getLogger()->info("No new version available.");
        }
    }
}