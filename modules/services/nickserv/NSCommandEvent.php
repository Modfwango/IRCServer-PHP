<?php
  class @@CLASSNAME@@ {
    public $name = "NSCommandEvent";

    public function isUnloadable() {
      return false;
    }

    public function isInstantiated() {
      EventHandling::createEvent("nsCommandEvent", $this);
      return true;
    }
  }
?>
