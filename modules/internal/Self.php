<?php
  class __CLASSNAME__ {
    public $depend = array();
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
        $motd = "This is a test!";
        StorageHandling::saveFile($this, "motd.txt", $motd);
      }
      if (count($this->config) == 0) {
        $config = @json_decode(trim(StorageHandling::loadFile($this,
          "config.json")), true);
        if (!is_array($config)) {
          $config = array(
            "netname" => "PHPNet",
            "pingtime" => 120,
            "version" => "IRCServer-PHP-dev",
            "serverdomain" => "home.clayfreeman.com",
            "serverdescription" => "Oh, look; a server!"
          );
          StorageHandling::saveFile($this, "config.json", json_encode($config,
            JSON_PRETTY_PRINT));
        }
        $this->config = $config;
        $this->config["motd"] = $motd;
      }
      else {
        $this->config["motd"] = $motd;
      }
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
