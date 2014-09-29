<?php
  class __CLASSNAME__ {
    public $depend = array("CommandEvent", "Numeric", "Self");
    public $name = "LOAD";
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
        if ($connection->getOption("operator") == true) {
          if (count($command) > 0) {
            if (ModuleManagement::loadModule($command[0])) {
              $connection->send(":".$this->self->getConfigFlag(
                "serverdomain")." NOTICE ".$connection->getOption("nick")." ".
                ":*** Loaded module: ".$command[0]);
            }
            else {
              $connection->send(":".$this->self->getConfigFlag(
                "serverdomain")." NOTICE ".$connection->getOption("nick")." ".
                ":*** Unable to load module: ".$command[0]);
            }
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

    public function isInstantiated() {
      $this->numeric = ModuleManagement::getModuleByName("Numeric");
      $this->self = ModuleManagement::getModuleByName("Self");
      EventHandling::registerForEvent("commandEvent", $this, "receiveCommand",
        "load");
      return true;
    }
  }
?>
