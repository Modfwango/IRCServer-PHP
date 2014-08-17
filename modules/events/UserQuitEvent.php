<?php
  class @@CLASSNAME@@ {
    public $name = "UserQuitEvent";

    public function isUnloadable() {
      return false;
    }

    public function isInstantiated() {
      EventHandling::createEvent("userQuitEvent", $this);
      return true;
    }
  }
?>
