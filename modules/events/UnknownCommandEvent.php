<?php
  class __CLASSNAME__ {
    public $name = "UnknownCommandEvent";

    public function isUnloadable() {
      return false;
    }

    public function isInstantiated() {
      EventHandling::createEvent("unknownCommandEvent", $this);
      return true;
    }
  }
?>
