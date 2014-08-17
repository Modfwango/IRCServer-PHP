<?php
  class @@CLASSNAME@@ {
    public $name = "BanShouldPreventActionEvent";

    public function isUnloadable() {
      return false;
    }

    public function isInstantiated() {
      EventHandling::createEvent("BanShouldPreventActionEvent", $this);
      return true;
    }
  }
?>
