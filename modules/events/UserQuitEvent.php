<?php
  class @@CLASSNAME@@ {
    public $name = "UserQuitEvent";

    public function isInstantiated() {
      EventHandling::createEvent("userQuitEvent", $this);
      return true;
    }
  }
?>
