<?php
  class @@CLASSNAME@@ {
    public $depend = array("ChannelJoinEvent", "ChannelMessageEvent",
      "ChannelPartEvent", "Client", "NickChangeEvent", "UserQuitEvent");
    public $name = "Channel";
    private $client = null;
    private $channels = array();

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

    public function getChannelByName($name) {
      // Retrieve the requested channel if it exists, otherwise return false.
      return (isset($this->channels[$name]) ? $this->channels[$name] : false);
    }

    public function receiveChannelJoin($name, $data) {
      $source = $data[0];
      $target = $data[1];

      $channel = $this->getChannelByName($target);
      if ($channel != false) {
        $channel["members"][] = $source->getOption("id");
        $this->setChannel($channel);
      }
      else {
        $channel = array(
          "name" => $target,
          "members" => array($source->getOption("id")),
          "created" => time()
        );
        $this->setChannel($channel);
      }
      $this->broadcast($channel["name"], ":".$source->getOption("nick")."!".
        $source->getOption("ident")."@".$source->getHost()." JOIN ".
        $channel["name"]);
      $event = EventHandling::getEventByName("commandEvent");
      if ($event != false) {
        foreach ($event[2] as $id => $registration) {
          // Trigger the commandEvent event for each registered module.
          EventHandling::triggerEvent("commandEvent", $id,
              array($source, array("NAMES", $channel["name"])));
        }
      }
    }

    public function receiveChannelMessage($name, $data) {
      $source = $data[0];
      $target = $data[1];
      $message = $data[2];
      $base = ":".$source->getOption("nick")."!".$source->getOption("ident").
        "@".$source->getHost()." PRIVMSG ".$target["name"]." :";

      if (strlen($base.$message) > 510) {
        $chunks = str_split($message, (510 - strlen($base)));
        foreach ($chunks as $chunk) {
          $this->broadcast($target["name"], $base.$chunk,
            $source->getOption("id"));
        }
      }
      else {
        $this->broadcast($target["name"], $base.$message,
          $source->getOption("id"));
      }
    }

    public function receiveChannelPart($name, $data) {
      $source = $data[0];
      $channel = $data[1];
      $message = $data[2];

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
        $this->setChannel($ch);
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

    public function receiveNickChange($name, $data) {
      $source = $data[0];
      $oldnick = $data[1];

      $targets = array();
      foreach ($this->channels as $channel) {
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

      $targets = array();
      foreach ($this->channels as &$channel) {
        if ($this->clientIsOnChannel($source->getOption("id"),
            $channel["name"])) {
          $channel["members"] = array_diff($channel["members"],
            array($source->getOption("id")));
          $targets = array_values(array_unique(array_merge(
            array_values($targets), array_values($channel["members"]))));
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

    public function isInstantiated() {
      $this->client = ModuleManagement::getModuleByName("Client");
      EventHandling::registerForEvent("channelJoinEvent", $this,
        "receiveChannelJoin");
      EventHandling::registerForEvent("channelMessageEvent", $this,
        "receiveChannelMessage");
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
