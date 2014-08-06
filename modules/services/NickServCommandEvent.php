<?php
  class @@CLASSNAME@@ {
    public $name = "NickServCommandEvent";

    public function isInstantiated() {
      EventHandling::createEvent("NickServCommandEvent", $this);
      return true;
    }
  }
?>
