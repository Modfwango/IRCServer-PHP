<?php
  class @@CLASSNAME@@ {
    public $depend = array("ChannelCreatedEvent", "ChannelInviteEvent",
      "ChannelJoinEvent", "ChannelMessageEvent", "ChannelModeEvent",
      "ChannelNoticeEvent", "ChannelPartEvent", "ChannelTopicEvent", "Client",
      "Modes", "NickChangeEvent", "Self", "UserQuitEvent");
    public $name = "Channel";
    private $client = null;
    private $channels = array();
    private $modes = null;
    private $self = null;

    public function broadcast($name, $data, $exclude = null) {
      if (!is_array($exclude)) {
        if ($exclude == null) {
          $exclude = array();
        }
        else {
          $exclude = array($exclude);
        }
      }
      $channel = $this->getChannelByName($name);
      if ($channel != false) {
        foreach ($channel["members"] as $id) {
          if (!in_array($id, $exclude)) {
            $connection = $this->client->getClientByID($id);
            if ($connection != false) {
              $connection->send($data);
            }
          }
        }
      }
    }

    public function clientIsOnChannel($id, $channel) {
      if (isset($this->channels[strtolower($channel)])) {
        if (in_array($id, $this->channels[strtolower($channel)]["members"])) {
          return true;
        }
      }
      return false;
    }

    public function clientsShareChannel($clients) {
      if (is_array($clients)) {
        foreach ($this->channels as $channel) {
          $shareChannel = true;
          foreach ($clients as $client) {
            if (!$this->clientIsOnChannel($client->getOption("id"),
                $channel["name"])) {
              $shareChannel = false;
            }
          }
          if ($shareChannel == true) {
            return true;
          }
        }
      }
      return false;
    }

    public function getChannelByName($name) {
      // Retrieve the requested channel if it exists, otherwise return false.
      return (isset($this->channels[strtolower($name)]) ?
        $this->channels[strtolower($name)] : false);
    }

    public function getChannelMembers($name) {
      $ch = $this->getChannelByName($name);
      if ($ch != false) {
        if (count($ch["members"]) > 1) {
          return $ch["members"];
        }
      }
      return false;
    }

    public function getChannelMembershipByID($id) {
      $channels = array();
      foreach ($this->getChannels() as $channel) {
        if (in_array($id, $channel["members"])) {
          $channels[] = $channel["name"];
        }
      }
      return $channels;
    }

    public function getChannels() {
      return $this->channels;
    }

    public function hasModes($channel, $modes) {
      $c = $this->getChannelByName($channel);
      if ($c != false) {
        $return = array();
        foreach ($modes as $mode) {
          if (isset($c["modes"]) && is_array($c["modes"])
              && count($c["modes"]) > 0) {
            foreach ($c["modes"] as $cm) {
              if ($cm["name"] == $mode) {
                $return[] = $cm;
              }
            }
          }
        }
        if (count($return) > 0) {
          return $return;
        }
      }
      return false;
    }

    public function receiveChannelCreated($name, $data) {
      return true;
    }

    public function receiveChannelEvent($name, $data) {
      $source = $data[0];
      $target = $data[1];
      $message = $data[2];

      $source->setOption("idle", time());

      if (isset($data[3])) {
        $exceptions = $data[3];
      }
      else {
        $exceptions = array();
      }

      $exceptions[] = $source->getOption("id");
      $exceptions = array_unique($exceptions);
      $base = ":".$source->getOption("nick")."!".$source->getOption("ident").
        "@".$source->getHost().($name == "channelMessageEvent" ? " PRIVMSG " :
        null).($name == "channelNoticeEvent" ? " NOTICE " : null).
        $target["name"]." :";

      if (strlen($base.$message) > 510) {
        $chunks = str_split($message, (510 - strlen($base)));
        foreach ($chunks as $chunk) {
          $this->broadcast($target["name"], $base.$chunk, $exceptions);
        }
      }
      else {
        $this->broadcast($target["name"], $base.$message, $exceptions);
      }
    }

    public function receiveChannelInvite($name, $data) {
      $source = $data[0];
      $recipient = $data[1];
      $target = $data[2];

      $source->setOption("idle", time());

      $channel = $this->getChannelByName($target);
      if ($channel != false) {
        $channel["invites"][] = $recipient->getOption("id");
        $this->setChannel($channel);
        $recipient->send(":".$source->getOption("nick")."!".
          $source->getOption("ident")."@".$source->getHost()." INVITE ".
          $recipient->getOption("nick")." :".$target);
        $source->send(":".$this->self->getConfigFlag(
          "serverdomain")." 341 ".$source->getOption("nick")." ".
          $recipient->getOption("nick")." ".$target);
        return true;
      }
      return false;
    }

    public function receiveChannelJoin($name, $data) {
      $source = $data[0];
      $target = $data[1];

      $source->setOption("idle", time());

      if (!$this->clientIsOnChannel($source->getOption("id"), $target)) {
        $channel = $this->getChannelByName($target);
        if ($channel != false) {
          if (in_array($source->getOption("id"), $channel["invites"])) {
            $channel["invites"] = array_diff($channel["invites"],
              array($source->getOption("id")));
          }
          $channel["members"][] = $source->getOption("id");
          $this->setChannel($channel);
        }
        else {
          $channel = array(
            "name" => $target,
            "invites" => array(),
            "modes" => array(),
            "members" => array($source->getOption("id")),
            "created" => time()
          );
          $this->setChannel($channel);
          $event = EventHandling::getEventByName("channelCreatedEvent");
          if ($event != false) {
            foreach ($event[2] as $id => $registration) {
              // Trigger the channelCreatedEvent event for each registered
              // module.
              EventHandling::triggerEvent("channelCreatedEvent", $id, $channel);
            }
          }
          $channel = $this->getChannelByName($channel["name"]);
        }
        $this->broadcast($channel["name"], ":".$source->getOption("nick")."!".
          $source->getOption("ident")."@".$source->getHost()." JOIN ".
          $channel["name"]);
        $event = EventHandling::getEventByName("commandEvent");
        if ($event != false) {
          if (count($channel["modes"]) > 0) {
            foreach ($event[2] as $id => $registration) {
              // Trigger the commandEvent event for each registered module.
              if (strtolower(trim($registration[2])) == "mode") {
                EventHandling::triggerEvent("commandEvent", $id,
                  array($source, array($channel["name"]), true));
              }
            }
          }
          foreach ($event[2] as $id => $registration) {
            // Trigger the commandEvent event for each registered module.
            if (strtolower(trim($registration[2])) == "topic") {
              EventHandling::triggerEvent("commandEvent", $id,
                array($source, array($channel["name"]), true));
            }
          }
          foreach ($event[2] as $id => $registration) {
            // Trigger the commandEvent event for each registered module.
            if (strtolower(trim($registration[2])) == "names") {
              EventHandling::triggerEvent("commandEvent", $id,
                array($source, array($channel["name"])));
            }
          }
        }
      }
    }

    public function receiveChannelMode($name, $data) {
      $source = $data[0];
      $channel = $data[1];
      $modes = $data[2];

      $source->setOption("idle", time());

      if (count($modes) == 0) {
        return;
      }

      $modesdone = array();
      $ch = $this->getChannelByName($channel["name"]);
      if ($ch != false) {
        foreach ($modes as $mode) {
          if ($mode["operation"] == "+") {
            $modesdone[] = $mode;
            unset($mode["operation"]);
            $ch["modes"][] = $mode;
          }
          else {
            if (!isset($mode["param"])) {
              foreach ($ch["modes"] as $key => $m) {
                if ($m["name"] == $mode["name"]) {
                  $modesdone[] = $mode;
                  unset($ch["modes"][$key]);
                }
              }
            }
            else {
              foreach ($ch["modes"] as $key => $m) {
                if ($m["name"] == $mode["name"]
                    && $m["param"] == $mode["param"]) {
                  $modesdone[] = $mode;
                  unset($ch["modes"][$key]);
                }
              }
            }
          }
        }
      }

      if (count($modesdone) == 0) {
        return;
      }

      $ch["modetime"] = time();
      $this->setChannel($ch);

      $modes = null;
      $params = null;
      $lastOperation = null;
      foreach ($modesdone as $mode) {
        if ($lastOperation != $mode["operation"]) {
          $lastOperation = $mode["operation"];
          $modes .= $mode["operation"];
        }
        $omode = $this->modes->getModeByName($mode["name"]);
        $modes .= $omode[1];
        if (isset($mode["displayparam"])) {
          $params .= " ".$mode["displayparam"];
        }
        elseif (isset($mode["param"])) {
          $params .= " ".$mode["param"];
        }
      }
      $modeString = $modes.$params;

      $this->broadcast($ch["name"], ":".$source->getOption("nick")."!".
        $source->getOption("ident")."@".$source->getHost()." MODE ".$ch["name"].
        " ".$modeString);
    }

    public function receiveChannelPart($name, $data) {
      $source = $data[0];
      $channel = $data[1];
      $message = $data[2];

      $source->setOption("idle", time());

      if (!$this->clientIsOnChannel($source->getOption("id"),
          $channel["name"])) {
        return;
      }

      $targets = array();
      $ch = $this->getChannelByName($channel["name"]);
      if ($ch != false) {
        $targets = array_values(array_unique(array_merge(
          array_values($targets), array_values($ch["members"]))));
        $ch["members"] = array_diff($ch["members"],
          array($source->getOption("id")));
        if (count($ch["members"]) == 0) {
          $this->unsetChannel($ch);
        }
        else {
          $this->setChannel($ch);
        }
      }

      foreach ($targets as $target) {
        $t = $this->client->getClientByID($target);
        if ($t != false) {
          $t->send(":".$source->getOption("nick")."!".
            $source->getOption("ident")."@".$source->getHost()." PART ".
            $channel["name"].($message != null ? " :".$message : null));
        }
      }
    }

    public function receiveChannelTopic($name, $data) {
      $source = $data[0];
      $channel = $data[1];
      $message = $data[2];

      $source->setOption("idle", time());

      $ch = $this->getChannelByName($channel["name"]);
      if ($ch != false) {
        if (!isset($ch["topic"])) {
          $ch["topic"] = array();
        }
        $ch["topic"]["text"] = $message;
        $ch["topic"]["author"] = $source->getOption("nick")."!".
          $source->getOption("ident")."@".$source->getHost();
        $ch["topic"]["timestamp"] = time();
        $this->setChannel($ch);
        $this->broadcast($ch["name"], ":".$ch["topic"]["author"]." TOPIC ".
          $ch["name"]." :".$ch["topic"]["text"]);
      }
    }

    public function receiveNickChange($name, $data) {
      $source = $data[0];
      $oldnick = $data[1];

      $source->setOption("idle", time());

      $targets = array();
      foreach ($this->channels as &$channel) {
        if ($this->clientIsOnChannel($source->getOption("id"),
            $channel["name"])) {
          $targets = array_values(array_unique(array_merge(
            array_values($targets), array_values($channel["members"]))));
        }
      }

      $targets = array_diff($targets, array($source->getOption("id")));
      foreach ($targets as $target) {
        $t = $this->client->getClientByID($target);
        if ($t != false) {
          $t->send(":".$oldnick."!".$source->getOption("ident").
            "@".$source->getHost()." NICK ".$source->getOption("nick"));
        }
      }
    }

    public function setChannel($c) {
      $this->channels[strtolower($c["name"])] = $c;
      return true;
    }

    public function receiveUserQuit($name, $data) {
      $source = $data[0];
      $message = $data[1];

      $source->setOption("idle", time());

      $targets = array();
      foreach ($this->channels as $key => &$channel) {
        if (in_array($source->getOption("id"), $channel["invites"])) {
          $channel["invites"] = array_diff($channel["invites"],
            array($source->getOption("id")));
        }
        if ($this->clientIsOnChannel($source->getOption("id"),
            $channel["name"])) {
          $channel["members"] = array_diff($channel["members"],
            array($source->getOption("id")));
          $targets = array_values(array_unique(array_merge(
            array_values($targets), array_values($channel["members"]))));
          if (count($channel["members"]) == 0) {
            $this->unsetChannel($channel);
          }
        }
      }

      foreach ($targets as $target) {
        $t = $this->client->getClientByID($target);
        if ($t != false) {
          $t->send(":".$source->getOption("nick")."!".
            $source->getOption("ident")."@".$source->getHost()." QUIT :".
            $message);
        }
      }
    }

    public function unsetChannel($c) {
      if (isset($this->channels[strtolower($c["name"])])) {
        unset($this->channels[strtolower($c["name"])]);
        return true;
      }
      return false;
    }

    public function isUnloadable() {
      return false;
    }

    public function isInstantiated() {
      $this->client = ModuleManagement::getModuleByName("Client");
      $this->modes = ModuleManagement::getModuleByName("Modes");
      $this->self = ModuleManagement::getModuleByName("Self");
      EventHandling::registerForEvent("channelCreatedEvent", $this,
        "receiveChannelCreated");
      EventHandling::registerForEvent("channelInviteEvent", $this,
        "receiveChannelInvite");
      EventHandling::registerForEvent("channelJoinEvent", $this,
        "receiveChannelJoin");
      EventHandling::registerForEvent("channelMessageEvent", $this,
        "receiveChannelEvent");
      EventHandling::registerForEvent("channelModeEvent", $this,
        "receiveChannelMode");
      EventHandling::registerForEvent("channelNoticeEvent", $this,
        "receiveChannelEvent");
      EventHandling::registerForEvent("channelPartEvent", $this,
        "receiveChannelPart");
      EventHandling::registerForEvent("channelTopicEvent", $this,
        "receiveChannelTopic");
      EventHandling::registerForEvent("nickChangeEvent", $this,
        "receiveNickChange");
      EventHandling::registerForEvent("userQuitEvent", $this,
        "receiveUserQuit");
      return true;
    }
  }
?>
