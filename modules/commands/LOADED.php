<?php
  class __CLASSNAME__ {
    public $depend = array("CommandEvent", "Numeric", "Self");
    public $name = "LOADED";
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
        if ($connection->getOption("operator") != false) {
          $connection->send(":".$this->self->getConfigFlag(
            "serverdomain")." NOTICE ".$connection->getOption("nick")." ".
            ":*** Currently loaded modules:");
          $modules = array();
          foreach (ModuleManagement::getLoadedModules() as $m) {
            $modules[$m->name] = array(get_class($m), (isset($m->depend) ?
              $m->depend : array()));
          }
          ksort($modules);
          foreach ($modules as $name => $info) {
            $connection->send(":".$this->self->getConfigFlag(
              "serverdomain")." NOTICE ".$connection->getOption("nick")." ".
              ":*** ".$name.":");
            $connection->send(":".$this->self->getConfigFlag(
              "serverdomain")." NOTICE ".$connection->getOption("nick")." ".
              ":    *** Class:    ".$info[0]);
            $connection->send(":".$this->self->getConfigFlag(
              "serverdomain")." NOTICE ".$connection->getOption("nick")." ".
              ":    *** Depends:  ".implode(", ",$info[1]));
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

    public function isInstantiated() {
      $this->numeric = ModuleManagement::getModuleByName("Numeric");
      $this->self = ModuleManagement::getModuleByName("Self");
      EventHandling::registerForEvent("commandEvent", $this, "receiveCommand",
        "loaded");
      return true;
    }
  }
?>
