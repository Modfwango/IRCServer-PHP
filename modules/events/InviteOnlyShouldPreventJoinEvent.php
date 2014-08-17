<?php
  class @@CLASSNAME@@ {
    public $name = "InviteOnlyShouldPreventJoinEvent";

    public function isUnloadable() {
      return false;
    }

    public function isInstantiated() {
      EventHandling::createEvent("inviteOnlyShouldPreventJoinEvent", $this);
      return true;
    }
  }
?>
