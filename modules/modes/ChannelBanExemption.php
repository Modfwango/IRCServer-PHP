<?php
  class __CLASSNAME__ {
    public $depend = array("BanShouldPreventActionEvent", "Channel", "Client",
      "ChannelModeEvent", "Modes", "Numeric", "Self");
    public $name = "ChannelBanExemption";
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
        array("ChannelBanExemption"));
      Logger::debug("Channel [".$channel["name"]."] has modes:");
      Logger::debug(var_export($has, true));
      if (is_array($has) && count($has) > 0) {
        foreach ($has as $m) {
          $h[strtolower($m["param"])] = true;
        }
      }
      foreach ($modes as $key => &$mode) {
        if ($mode["name"] == "ChannelBanExemption") {
          if (isset($mode["param"]) && trim($mode["param"]) != null) {
            $mode["author"] = $source->getOption("nick")."!".
              $source->getOption("ident")."@".$source->getHost();
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
              Logger::debug("Found list mode for [".$channel["name"].
                "] with param [".$mo["param"]."] author [".$mo["author"].
                "] and time [".$mo["time"]."]");
              Logger::debug("Sending to client [".
                $source->getOption("nick")."]");
              $source->send($this->numeric->get("RPL_EXCEPTLIST", array(
                $this->self->getConfigFlag("serverdomain"),
                $source->getOption("nick"),
                $channel["name"],
                $mo["param"],
                $mo["author"],
                $mo["time"]
              )));
            }
            $source->send($this->numeric->get("RPL_ENDOFEXCEPTLIST", array(
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

    public function receiveBanShouldPreventAction($name, $data) {
      $action = $data[0];
      $source = $data[1];
      $channel = $data[2];

      if (is_array($channel)) {
        $channel = $channel["name"];
      }

      $modes = $this->channel->hasModes($channel,
        array("ChannelBanExemption"));
      if ($modes != false) {
        foreach ($modes as $mode) {
          if ($this->client->clientMatchesMask($source, $mode["param"])) {
            // Ban is exempted.
            return false;
          }
        }
      }

      // Ban is not exempted.
      return true;
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
      $this->modes->setMode(array("ChannelBanExemption", "e", "0",
        "3"));
      EventHandling::registerAsEventPreprocessor("channelModeEvent", $this,
        "receiveChannelMode");
      EventHandling::registerForEvent("banShouldPreventActionEvent", $this,
        "receiveBanShouldPreventAction");
      return true;
    }
  }
?>
