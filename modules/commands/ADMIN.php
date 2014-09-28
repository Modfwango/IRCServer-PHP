<?php
  class __CLASSNAME__ {
    public $depend = array("CommandEvent", "Self");
    public $name = "ADMIN";
    private $self = null;

    public function receiveCommand($name, $data) {
      $connection = $data[0];
      $command = $data[1];

      foreach ($command as $key => $param) {
        if (trim($param) == null) {
          unset($command[$key]);
        }
      }
      $command = array_values($command);

      if ($connection->getOption("registered") == true) {
        $connection->send(":".$this->self->getConfigFlag("serverdomain")." ".
        "256 ".$connection->getOption("nick")." ".
        $this->self->getConfigFlag("serverdomain")." :Administrative Info");
        $connection->send(":".$this->self->getConfigFlag("serverdomain")." ".
        "257 ".$connection->getOption("nick")." :".
        $this->config["admin1"]);
        $connection->send(":".$this->self->getConfigFlag("serverdomain")." ".
        "258 ".$connection->getOption("nick")." :".
        $this->config["admin2"]);
        $connection->send(":".$this->self->getConfigFlag("serverdomain")." ".
        "259 ".$connection->getOption("nick")." :".
        $this->config["adminemail"]);
      }
      else {
        $connection->send(":".$this->self->getConfigFlag(
          "serverdomain")." 451 ".($connection->getOption("nick") ?
          $connection->getOption("nick") : "*")." :You have not registered");
      }
    }

    public function loadConfig($name = null, $data = null) {
      $config = @json_decode(trim(StorageHandling::loadFile($this,
        "config.json")), true);
      if (!is_array($config)) {
        $config = array(
          "admin1" => "Jonathan Flusser Jr.",
          "admin2" => "Network Administrator/Owner",
          "adminemail" => "admin@default.domain.tld"
        );
        StorageHandling::saveFile($this, "config.json", json_encode($config,
          JSON_PRETTY_PRINT));
      }
      $this->config = $config;
    }

    public function isInstantiated() {
      $this->loadConfig();
      $this->self = ModuleManagement::getModuleByName("Self");
      EventHandling::registerForEvent("commandEvent", $this, "receiveCommand",
        "admin");
      EventHandling::registerForEvent("rehashEvent", $this, "loadConfig");
      return true;
    }
  }
?>
