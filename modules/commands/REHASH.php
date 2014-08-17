<?php
  class @@CLASSNAME@@ {
    public $depend = array("CommandEvent", "RehashEvent", "Self");
    public $name = "REHASH";
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
          $event = EventHandling::getEventByName("rehashEvent");
          if ($event != false) {
            foreach ($event[2] as $id => $registration) {
              // Trigger the rehashEvent event for each registered
              // module.
              EventHandling::triggerEvent("rehashEvent", $id);
            }
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
        "rehash");
      return true;
    }
  }
?>
