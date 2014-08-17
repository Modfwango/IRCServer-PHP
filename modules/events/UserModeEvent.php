<?php
  class __CLASSNAME__ {
    public $name = "UserModeEvent";

    public function isUnloadable() {
      return false;
    }

    public function isInstantiated() {
      EventHandling::createEvent("userModeEvent", $this);
      return true;
    }
  }
?>
