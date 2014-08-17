<?php
  class @@CLASSNAME@@ {
    public $depend = array("BanShouldPreventActionEvent", "Channel", "Client",
      "ChannelJoinEvent", "ChannelMessageEvent", "ChannelModeEvent",
      "ChannelNoticeEvent", "Modes", "Self");
    public $name = "ChannelBan";
    private $channel = null;
    private $client = null;
    private $modes = null;

    public function receiveChannelMode($name, $id, $data) {
      $source = $data[0];
      $channel = $data[1];
      $modes = $data[2];

      $h = array();
      $has = $this->channel->hasModes($channel["name"],
        array("ChannelBan"));
      if (is_array($has) && count($has) > 0) {
        foreach ($has as $m) {
          $h[strtolower($m["param"])] = true;
        }
      }
      foreach ($modes as $key => &$mode) {
        if ($mode["name"] == "ChannelBan") {
          $mode["param"] = $this->client->getPrettyMask($mode["param"]);
          if (!isset($h[strtolower($mode["param"])])) {
            $h[strtolower($mode["param"])] = false;
          }
          if ($mode["operation"] == "+") {
            if ($h[strtolower($mode["param"])] != false) {
              unset($modes[$key]);
            }
            else {
              $h[strtolower($mode["param"])] = true;
            }
          }
          if ($mode["operation"] == "-") {
            if ($h[strtolower($mode["param"])] == false) {
              unset($modes[$key]);
            }
            else {
              $h[strtolower($mode["param"])] = false;
            }
          }
        }
      }
      $data[2] = $modes;
      return array(null, $data);
    }

    public function receiveChannelEvent($name, $id, $data) {
      $source = $data[0];
      $channel = $data[1];

      if (is_array($channel)) {
        $channel = $channel["name"];
      }

      $modes = $this->channel->hasModes($channel,
        array("ChannelBan"));
      if ($modes != false) {
        foreach ($modes as $mode) {
          if ($this->client->clientMatchesMask($source, $mode["param"])) {
            // Allow for dynamic ban exceptions.
            $event = EventHandling::getEventByName(
              "banShouldPreventActionEvent");
            if ($event != false) {
              foreach ($event[2] as $id => $registration) {
                // Trigger the banShouldPreventActionEvent event for each
                // registered module.
                if (!EventHandling::triggerEvent("banShouldPreventActionEvent",
                    $id, array($name, $source, $channel))) {
                  return array(true);
                }
              }
            }

            // Prevent the action, and inform the user.
            if ($name == "channelMessageEvent"
                || $name == "channelNoticeEvent") {
              $source->send(":".$this->self->getConfigFlag(
                "serverdomain")." 404 ".$source->getOption("nick")." ".
                $channel." :Cannot send to channel");
            }
            if ($name == "channelJoinEvent") {
              $source->send(":".$this->self->getConfigFlag(
                "serverdomain")." 474 ".$source->getOption("nick")." ".
                $channel." :Cannot join channel (+b) - you are banned");
            }
            return array(false);
          }
        }
      }

      return array(true);
    }

    public function isInstantiated() {
      $this->channel = ModuleManagement::getModuleByName("Channel");
      $this->client = ModuleManagement::getModuleByName("Client");
      $this->modes = ModuleManagement::getModuleByName("Modes");
      $this->self = ModuleManagement::getModuleByName("Self");
      $this->modes->setMode(array("ChannelBan", "b", "0", "3"));
      EventHandling::registerAsEventPreprocessor("channelJoinEvent", $this,
        "receiveChannelEvent");
      EventHandling::registerAsEventPreprocessor("channelMessageEvent", $this,
        "receiveChannelEvent");
      EventHandling::registerAsEventPreprocessor("channelModeEvent", $this,
        "receiveChannelMode");
      EventHandling::registerAsEventPreprocessor("channelNoticeEvent", $this,
        "receiveChannelEvent");
      return true;
    }
  }
?>
