<?php
  class __CLASSNAME__ {
    public $depend = array("Numeric", "Self");
    public $name = "KLINE";
    private $numeric = null;
    private $self = null;
    private $config = null;

    public function receiveKLINE($name, $data) {
      $connection = $data[0];
      $command = $data[1];

      foreach ($command as $key => $param) {
        if (trim($param) == null) {
          unset($command[$key]);
        }
      }
      $command = array_values($command);

      if ($connection->getOption("registered") == true) {
        if ($connection->getOption("operator") == true) {
          if (count($command) > 0) {
            //
          }
          else {
            $connection->send(":".$this->numeric->get("ERR_NEEDMOREPARAMS",
              array(
                $this->self->getConfigFlag("serverdomain"),
                $connection->getOption("nick"),
                $this->name
              )
            ));
          }
        }
        else {
          $connection->send($this->numeric->get("ERR_NOPRIVILEGES", array(
            $this->self->getConfigFlag("serverdomain"),
            ($connection->getOption("nick") ?
            $connection->getOption("nick") : "*")
          )));
        }
      }
      else {
        $connection->send($this->numeric->get("ERR_NOTREGISTERED", array(
          $this->self->getConfigFlag("serverdomain"),
          ($connection->getOption("nick") ?
          $connection->getOption("nick") : "*")
        )));
      }
    }

    public function receiveUNKLINE($name, $data) {
      $connection = $data[0];
      $command = $data[1];

      foreach ($command as $key => $param) {
        if (trim($param) == null) {
          unset($command[$key]);
        }
      }
      $command = array_values($command);

      if ($connection->getOption("registered") == true) {
        if ($connection->getOption("operator") == true) {
          if (count($command) > 0) {
            //
          }
          else {
            $connection->send(":".$this->numeric->get("ERR_NEEDMOREPARAMS",
              array(
                $this->self->getConfigFlag("serverdomain"),
                $connection->getOption("nick"),
                "UN".$this->name
              )
            ));
          }
        }
        else {
          $connection->send($this->numeric->get("ERR_NOPRIVILEGES", array(
            $this->self->getConfigFlag("serverdomain"),
            ($connection->getOption("nick") ?
            $connection->getOption("nick") : "*")
          )));
        }
      }
      else {
        $connection->send($this->numeric->get("ERR_NOTREGISTERED", array(
          $this->self->getConfigFlag("serverdomain"),
          ($connection->getOption("nick") ?
          $connection->getOption("nick") : "*")
        )));
      }
    }

    private function flushConfig() {
      return StorageHandling::saveFile($this, "config.json",
        json_encode($this->config, JSON_PRETTY_PRINT));
    }

    public function loadConfig($name = null, $data = null) {
      $config = @json_decode(trim(StorageHandling::loadFile($this,
        "config.json")), true);
      if (!is_array($config)) {
        $config = array();
        StorageHandling::saveFile($this, "config.json", json_encode($config,
          JSON_PRETTY_PRINT));
      }
      $this->config = $config;
    }

    public function isInstantiated() {
      $this->loadConfig();
      $this->numeric = ModuleManagement::getModuleByName("Numeric");
      EventHandling::registerForEvent("commandEvent", $this, "receiveKLINE",
        "kline");
      EventHandling::registerForEvent("commandEvent", $this, "receiveUNKLINE",
        "unkline");
      return true;
    }
  }
?>
