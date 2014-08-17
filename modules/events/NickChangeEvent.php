<?php
  class __CLASSNAME__ {
    public $name = "NickChangeEvent";

    public function isUnloadable() {
      return false;
    }

    public function isInstantiated() {
      EventHandling::createEvent("nickChangeEvent", $this);
      return true;
    }
  }
?>
