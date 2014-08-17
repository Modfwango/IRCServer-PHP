<?php
  class __CLASSNAME__ {
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
