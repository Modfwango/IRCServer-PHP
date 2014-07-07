<?php
  class @@CLASSNAME@@ {
    public $name = "UserModeEvent";

    public function isInstantiated() {
      EventHandling::createEvent("userModeEvent", $this);
      return true;
    }
  }
?>
