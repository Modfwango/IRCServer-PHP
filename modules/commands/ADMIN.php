<?php
  class __CLASSNAME__ {
    public $depend = array("CommandEvent", "Numeric", "Self");
    public $name = "ADMIN";
    private $config = null;
    private $numeric = null;
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
        $connection->send($this->numeric->get("RPL_ADMINME", array(
          $this->self->getConfigFlag("serverdomain"),
          $connection->getOption("nick")
        )));
        $connection->send($this->numeric->get("RPL_ADMINLOC1", array(
          $this->self->getConfigFlag("serverdomain"),
          $connection->getOption("nick"),
          $this->config["admin1"]
        )));
        $connection->send($this->numeric->get("RPL_ADMINLOC1", array(
          $this->self->getConfigFlag("serverdomain"),
          $connection->getOption("nick"),
          $this->config["admin2"]
        )));
        $connection->send($this->numeric->get("RPL_ADMINEMAIL", array(
          $this->self->getConfigFlag("serverdomain"),
          $connection->getOption("nick"),
          $this->config["adminemail"]
        )));
      }
      else {
        $connection->send($this->numeric->get("ERR_NOTREGISTERED", array(
          $this->self->getConfigFlag("serverdomain"),
          ($connection->getOption("nick") ?
          $connection->getOption("nick") : "*")
        )));
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
      $this->numeric = ModuleManagement::getModuleByName("Numeric");
      $this->self = ModuleManagement::getModuleByName("Self");
      EventHandling::registerForEvent("commandEvent", $this, "receiveCommand",
        "admin");
      EventHandling::registerForEvent("rehashEvent", $this, "loadConfig");
      return true;
    }
  }
?>
