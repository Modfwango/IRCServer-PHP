<?php
  class __CLASSNAME__ {
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
