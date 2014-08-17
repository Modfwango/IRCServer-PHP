<?php
  class __CLASSNAME__ {
    public $name = "WHOISResponseEvent";

    public function isUnloadable() {
      return false;
    }

    public function isInstantiated() {
      EventHandling::createEvent("WHOISResponseEvent", $this);
      return true;
    }
  }
?>
