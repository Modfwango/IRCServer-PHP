<?php
  class @@CLASSNAME@@ {
    public $depend = array("CommandEvent", "UserRegistrationEvent");
    public $name = "Debug";

    public function receiveCommand($name, $data) {
      $connection = $data[0];
      $command = $data[1];

      Logger::info(var_export($command, true));
    }

    public function receiveUserRegistration($name, $data) {
      Logger::info(var_export($data, true));
    }

    public function isInstantiated() {
      EventHandling::registerForEvent("commandEvent", $this, "receiveCommand");
      EventHandling::registerForEvent("userRegistrationEvent", $this,
        "receiveUserRegistration");
      return true;
    }
  }
?>
