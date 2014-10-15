<?php
  class __CLASSNAME__ {
    public $depend = array("BanShouldPreventActionEvent", "Channel", "Client",
      "ChannelJoinEvent", "ChannelMessageEvent", "ChannelModeEvent",
      "ChannelNoticeEvent", "Modes", "Numeric", "Self");
    public $name = "ChannelBan";
    private $channel = null;
    private $client = null;
    private $modes = null;
    private $numeric = null;
    private $self = null;

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
          if (isset($mode["param"])) {
            $mode["author"] = $source->getOption("nick")."!".
              $source->getOption("ident").$source->getHost();
            $mode["time"] = time();

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
          else {
            unset($modes[$key]);
            foreach ($has as $mo) {
              $source->send($this->numeric->get("RPL_BANLIST", array(
                $this->self->getConfigFlag("serverdomain"),
                $source->getOption("nick"),
                $channel["name"],
                $mo["param"],
                $mo["author"],
                $mo["time"]
              )));
            }
            $source->send($this->numeric->get("RPL_ENDOFBANLIST", array(
              $this->self->getConfigFlag("serverdomain"),
              $source->getOption("nick"),
              $channel["name"]
            )));
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
              $source->send($this->numeric->get("ERR_CANNOTSENDTOCHAN", array(
                $this->self->getConfigFlag("serverdomain"),
                $source->getOption("nick"),
                $channel
              )));
            }
            if ($name == "channelJoinEvent") {
              $source->send($this->numeric->get("ERR_BANNEDFROMCHAN", array(
                $this->self->getConfigFlag("serverdomain"),
                $source->getOption("nick"),
                $channel,
                "b"
              )));
            }
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
      $this->client = ModuleManagement::getModuleByName("Client");
      $this->modes = ModuleManagement::getModuleByName("Modes");
      $this->numeric = ModuleManagement::getModuleByName("Numeric");
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
