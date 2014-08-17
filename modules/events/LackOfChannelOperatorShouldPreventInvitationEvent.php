<?php
  class @@CLASSNAME@@ {
    public $name = "LackOfChannelOperatorShouldPreventInvitationEvent";

    public function isUnloadable() {
      return false;
    }

    public function isInstantiated() {
      EventHandling::createEvent(
        "lackOfChannelOperatorShouldPreventInvitationEvent", $this);
      return true;
    }
  }
?>
