<?php
  class @@CLASSNAME@@ {
    public $name = "UserRegistrationEvent";

    public function isUnloadable() {
      return false;
    }

    public function isInstantiated() {
      EventHandling::createEvent("userRegistrationEvent", $this);
      return true;
    }
  }
?>
