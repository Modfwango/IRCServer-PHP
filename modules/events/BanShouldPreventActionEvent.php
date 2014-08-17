<?php
  class __CLASSNAME__ {
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
