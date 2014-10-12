<?php
  class __CLASSNAME__ {
    public $name = "Self";
    private $config = array();

    public function getConfigFlag($key) {
      if (isset($this->config[$key])) {
        return $this->config[$key];
      }
      return false;
    }

    public function loadConfig($name = null, $data = null) {
      $motd = StorageHandling::loadFile($this, "motd.txt");
      if ($motd == false) {
        $motd = "This is an example MOTD.\nConfiguration files are located at ".
          "\"".StorageHandling::getPath($this)."\"";
        StorageHandling::saveFile($this, "motd.txt", $motd);
      }
      if (count($this->config) == 0) {
        $config = @json_decode(trim(StorageHandling::loadFile($this,
          "config.json")), true);
        if (!is_array($config)) {
          $config = array(
            "netname" => "DefaultIRC",
            "pingtime" => 120,
            "version" => "IRCServer-PHP;Modfwango-v".__MODFWANGOVERSION__,
            "serverdomain" => "irc.default.tld",
            "serverdescription" => "My owner hasn't given me a proper ".
              "description yet... :("
          );
          StorageHandling::saveFile($this, "config.json", json_encode($config,
            JSON_PRETTY_PRINT));
        }
        $this->config = $config;
      }
      $this->config["motd"] = $motd;
    }

    public function isUnloadable() {
      return false;
    }

    public function isInstantiated() {
      $this->loadConfig();
      EventHandling::registerForEvent("rehashEvent", $this, "loadConfig");
      return true;
    }
  }
?>
