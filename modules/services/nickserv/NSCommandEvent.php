<?php
  class @@CLASSNAME@@ {
    public $name = "NSCommandEvent";

    public function isInstantiated() {
      EventHandling::createEvent("nsCommandEvent", $this);
      return true;
    }
  }
?>
