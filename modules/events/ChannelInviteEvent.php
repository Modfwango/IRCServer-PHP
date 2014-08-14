<?php
  class @@CLASSNAME@@ {
    public $name = "ChannelInviteEvent";

    public function isInstantiated() {
      EventHandling::createEvent("channelInviteEvent", $this);
      return true;
    }
  }
?>
