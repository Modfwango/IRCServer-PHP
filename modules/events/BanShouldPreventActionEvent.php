<?php
  class @@CLASSNAME@@ {
    public $name = "BanShouldPreventActionEvent";

    public function isInstantiated() {
      EventHandling::createEvent("BanShouldPreventActionEvent", $this);
      return true;
    }
  }
?>
