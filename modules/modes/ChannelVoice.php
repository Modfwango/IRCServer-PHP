<?php
  class @@CLASSNAME@@ {
    public $depend = array("Channel", "Client", "ChannelModeEvent",
      "ChannelPartEvent", "Modes", "NickChangeEvent", "UserQuitEvent");
    public $name = "ChannelVoice";
    private $channel = null;
    private $client = null;
    private $modes = null;

    public function receiveChannelMode($name, $id, $data) {
      $source = $data[0];
      $channel = $data[1];
      $modes = $data[2];

      $h = array();
      $has = $this->channel->hasModes($channel["name"],
        array("ChannelVoice"));
      if (is_array($has) && count($has) > 0) {
        foreach ($has as $m) {
          $client = $this->client->getClientByNick($m["param"]);
          if ($client != false && $this->channel->clientIsOnChannel(
              $client->getOption("id"), $channel["name"])) {
            $m["param"] = $client->getOption("nick");
            $h[$m["param"]] = true;
          }
        }
      }
      foreach ($modes as $key => $mode) {
        if ($mode["name"] == "ChannelVoice") {
          $client = $this->client->getClientByNick($mode["param"]);
          if ($client != false && $this->channel->clientIsOnChannel(
              $client->getOption("id"), $channel["name"])) {
            $mode["param"] = $client->getOption("nick");
            if (!isset($h[$mode["param"]])) {
              $h[$mode["param"]] = false;
            }
            if ($mode["operation"] == "+") {
              if ($h[$mode["param"]] != false) {
                unset($modes[$key]);
              }
              else {
                $h[$mode["param"]] = true;
              }
            }
            if ($mode["operation"] == "-") {
              if ($h[$mode["param"]] == false) {
                unset($modes[$key]);
              }
              else {
                $h[$mode["param"]] = false;
              }
            }
          }
          else {
            unset($modes[$key]);
          }
        }
      }
      $data[2] = $modes;
      return array(null, $data);
    }

    public function receiveChannelPart($name, $data) {
      $source = $data[0];
      $channel = $data[1];
      $message = $data[2];

      $ch = $this->channel->getChannelByName($channel["name"]);
      if ($ch != false && $this->channel->hasModes($ch["name"],
          array("ChannelVoice"))) {
        foreach ($ch["modes"] as $key => $mode) {
          if ($mode["name"] == "ChannelVoice"
              && $mode["param"] == $source->getOption("nick")) {
            unset($ch["modes"][$key]);
          }
        }
        $this->channel->setChannel($ch);
      }
    }

    public function receiveNickChange($name, $data) {
      $source = $data[0];
      $oldnick = $data[1];

      foreach ($this->channel->getChannels() as $ch) {
        if ($this->channel->hasModes($ch["name"],
            array("ChannelVoice"))) {
          foreach ($ch["modes"] as $key => &$mode) {
            if ($mode["name"] == "ChannelVoice"
                && $mode["param"] == $oldnick) {
              $mode["param"] = $source->getOption("nick");
            }
          }
          $this->channel->setChannel($ch);
        }
      }
    }

    public function receiveUserQuit($name, $data) {
      $source = $data[0];
      $message = $data[1];

      foreach ($this->channel->getChannels() as $ch) {
        if ($this->channel->hasModes($ch["name"],
            array("ChannelVoice"))) {
          foreach ($ch["modes"] as $key => $mode) {
            if ($mode["name"] == "ChannelVoice"
                && $mode["param"] == $source->getOption("nick")) {
              unset($ch["modes"][$key]);
            }
          }
          $this->channel->setChannel($ch);
        }
      }
    }

    public function isInstantiated() {
      $this->channel = ModuleManagement::getModuleByName("Channel");
      $this->client = ModuleManagement::getModuleByName("Client");
      $this->modes = ModuleManagement::getModuleByName("Modes");
      $this->modes->setMode(array("ChannelVoice", "v", "0", "4", "+", 500));
      EventHandling::registerAsEventPreprocessor("channelModeEvent", $this,
        "receiveChannelMode");
      EventHandling::registerForEvent("channelPartEvent", $this,
        "receiveChannelPart");
      EventHandling::registerForEvent("nickChangeEvent", $this,
        "receiveNickChange");
      EventHandling::registerForEvent("userQuitEvent", $this,
        "receiveUserQuit");
      return true;
    }
  }
?>
