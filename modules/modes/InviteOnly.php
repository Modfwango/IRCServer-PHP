<?php
  class __CLASSNAME__ {
    public $depend = array("Channel", "ChannelJoinEvent", "ChannelModeEvent",
      "InviteOnlyShouldPreventJoinEvent", "Modes", "Numeric", "Self");
    public $name = "InviteOnly";
    private $channel = null;
    private $modes = null;
    private $numeric = null;
    private $self = null;

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

      $c = $this->channel->getChannelByName($channel);
      if ($c != false) {
        $modes = $this->channel->hasModes($channel,
          array("InviteOnly"));
        if ($modes != false) {
          if (!in_array($source->getOption("id"), $c["invites"])) {
            // Allow for dynamic invite exceptions.
            $event = EventHandling::getEventByName(
              "inviteOnlyShouldPreventJoinEvent");
            if ($event != false) {
              foreach ($event[2] as $id => $registration) {
                // Trigger the inviteOnlyShouldPreventActionEvent event for each
                // registered module.
                if (!EventHandling::triggerEvent(
                    "inviteOnlyShouldPreventJoinEvent", $id, array($source,
                    $channel))) {
                  return array(true);
                }
              }
            }

            // Prevent the action, and inform the user.
            $source->send($this->numeric->get("ERR_INVITEONLYCHAN", array(
              $this->self->getConfigFlag("serverdomain"),
              $source->getOption("nick"),
              $channel,
              "i"
            )));
            return array(false);
          }
        }
      }

      return array(true);
    }

    public function isUnloadable() {
      return false;
    }

    public function isInstantiated() {
      $this->channel = ModuleManagement::getModuleByName("Channel");
      $this->modes = ModuleManagement::getModuleByName("Modes");
      $this->numeric = ModuleManagement::getModuleByName("Numeric");
      $this->self = ModuleManagement::getModuleByName("Self");
      $this->modes->setMode(array("InviteOnly", "i", "0", "0"));
      EventHandling::registerAsEventPreprocessor("channelJoinEvent", $this,
        "receiveChannelJoin");
      EventHandling::registerAsEventPreprocessor("channelModeEvent", $this,
        "receiveChannelMode");
      return true;
    }
  }
?>
