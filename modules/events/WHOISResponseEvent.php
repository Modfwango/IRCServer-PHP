<?php
  class @@CLASSNAME@@ {
    public $name = "WHOISResponseEvent";

    public function isInstantiated() {
      EventHandling::createEvent("WHOISResponseEvent", $this);
      return true;
    }
  }
?>
