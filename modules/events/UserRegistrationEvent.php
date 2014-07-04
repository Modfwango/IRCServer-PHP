<?php
  class @@CLASSNAME@@ {
    public $name = "UserRegistrationEvent";

    public function isInstantiated() {
      EventHandling::createEvent("userRegistrationEvent", $this);
      return true;
    }
  }
?>
