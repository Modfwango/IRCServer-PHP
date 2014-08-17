<?php
  class @@CLASSNAME@@ {
    public $depend = array("CommandEvent", "Self");
    public $name = "UNLOAD";

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
            if (ModuleManagement::unloadModule($command[0])) {
              $connection->send(":".$this->self->getConfigFlag(
                "serverdomain")." NOTICE ".$connection->getOption("nick")." ".
                ":*** Unloaded module: ".$command[0]);
            }
            else {
              $connection->send(":".$this->self->getConfigFlag(
                "serverdomain")." NOTICE ".$connection->getOption("nick")." ".
                ":*** Unable to unload module: ".$command[0]);
            }
          }
          else {
            $connection->send(":".$this->self->getConfigFlag(
              "serverdomain")." 461 ".$connection->getOption("nick")." LOAD ".
              ":Not enough parameters");
          }
        }
        else {
          $connection->send(":".$this->self->getConfigFlag(
            "serverdomain")." 481 ".($connection->getOption("nick") ?
            $connection->getOption("nick") : "*")." :Permission Denied - ".
            "You're not an IRC operator");
        }
      }
      else {
        $connection->send(":".$this->self->getConfigFlag(
          "serverdomain")." 451 ".($connection->getOption("nick") ?
          $connection->getOption("nick") : "*")." :You have not registered");
      }
    }

    public function isInstantiated() {
      $this->self = ModuleManagement::getModuleByName("Self");
      EventHandling::registerForEvent("commandEvent", $this, "receiveCommand",
        "unload");
      return true;
    }
  }
?>
