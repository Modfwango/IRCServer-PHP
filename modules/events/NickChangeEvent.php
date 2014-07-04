<?php
  class @@CLASSNAME@@ {
    public $name = "NickChangeEvent";

    public function isInstantiated() {
      EventHandling::createEvent("nickChangeEvent", $this);
      return true;
    }
  }
?>
