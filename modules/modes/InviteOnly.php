<?php
  class @@CLASSNAME@@ {
    public $depend = array("Channel", "ChannelJoinEvent", "ChannelModeEvent",
      "Modes");
    public $name = "InviteOnly";
    private $channel = null;
    private $modes = null;

    public function receiveChannelMode($name, $id, $data) {
      $source = $data[0];
      $channel = $data[1];
      $modes = $data[2];

      $has = $this->channel->hasModes($channel["name"],
        array("InviteOnly"));
      foreach ($modes as $key => $mode) {
        if ($mode["name"] == "InviteOnly") {
          if ($mode["operation"] == "+") {
            if ($has != false) {
              unset($modes[$key]);
            }
            else {
              $has = true;
            }
          }
          if ($mode["operation"] == "-") {
            if ($has == false) {
              unset($modes[$key]);
            }
            else {
              $has = false;
            }
          }
        }
      }
      $data[2] = $modes;
      return array(null, $data);
    }

    public function receiveChannelJoin($name, $id, $data) {
      $source = $data[0];
      $channel = $data[1];

      if (is_array($channel)) {
        $channel = $channel["name"];
      }

      $modes = $this->channel->hasModes($channel,
        array("InviteOnly"));
      if ($modes != false) {
        // TODO: Check for manual invites for this channel.
        // Allow for dynamic invite exceptions.
        $event = EventHandling::getEventByName(
          "inviteOnlyShouldPreventActionEvent");
        if ($event != false) {
          foreach ($event[2] as $id => $registration) {
            // Trigger the inviteOnlyShouldPreventActionEvent event for each
            // registered module.
            if (!EventHandling::triggerEvent(
                "inviteOnlyShouldPreventActionEvent", $id, array($source,
                $channel))) {
              return array(true);
            }
          }
        }

        // Prevent the action, and inform the user.
        $source->send(":".__SERVERDOMAIN__." 473 ".
          $source->getOption("nick")." ".$channel.
          " :Cannot join channel (+i) - you must be invited");
        return array(false);
      }

      return array(true);
    }

    public function isInstantiated() {
      $this->channel = ModuleManagement::getModuleByName("Channel");
      $this->modes = ModuleManagement::getModuleByName("Modes");
      $this->modes->setMode(array("InviteOnly", "i", "0", "0"));
      EventHandling::createEvent("inviteOnlyShouldPreventActionEvent", $this);
      EventHandling::registerAsEventPreprocessor("channelJoinEvent", $this,
        "receiveChannelJoin");
      EventHandling::registerAsEventPreprocessor("channelModeEvent", $this,
        "receiveChannelMode");
      return true;
    }
  }
?>
