<?php
  class @@CLASSNAME@@ {
    public $name = "LackOfChannelOperatorShouldPreventInvitationEvent";

    public function isInstantiated() {
      EventHandling::createEvent(
        "lackOfChannelOperatorShouldPreventInvitationEvent", $this);
      return true;
    }
  }
?>
