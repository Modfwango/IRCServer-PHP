<?php
  class @@CLASSNAME@@ {
    public $name = "RehashEvent";

    public function isInstantiated() {
      EventHandling::createEvent("rehashEvent", $this);
      return true;
    }
  }
?>
