<?php
  class @@CLASSNAME@@ {
    public $name = "RehashEvent";

    public function isUnloadable() {
      return false;
    }

    public function isInstantiated() {
      EventHandling::createEvent("rehashEvent", $this);
      return true;
    }
  }
?>
