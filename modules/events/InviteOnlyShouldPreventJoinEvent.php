<?php
  class @@CLASSNAME@@ {
    public $name = "InviteOnlyShouldPreventJoinEvent";

    public function isInstantiated() {
      EventHandling::createEvent("inviteOnlyShouldPreventJoinEvent", $this);
      return true;
    }
  }
?>
