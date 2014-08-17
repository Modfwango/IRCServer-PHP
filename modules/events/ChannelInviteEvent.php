<?php
  class @@CLASSNAME@@ {
    public $name = "ChannelInviteEvent";

    public function isUnloadable() {
      return false;
    }

    public function isInstantiated() {
      EventHandling::createEvent("channelInviteEvent", $this);
      return true;
    }
  }
?>
