<?php
  class @@CLASSNAME@@ {
    public $depend = array("ChannelJoinEvent", "ChannelMessageEvent",
      "NickChangeEvent", "UserQuitEvent");
    public $name = "Channel";
    private $options = array();

    public function broadcast($name, $data, $exclude = null) {
      if (!is_array($exclude)) {
        $exclude = array($exclude);
      }
      $channel = $this->getChannelByName($name);
      if ($channel != false) {
        foreach ($channel["members"] as $id) {
          if (!in_array($id, $exclude)) {
            foreach (ConnectionManagement::getConnections() as $connection) {
              if ($connection->getOption("id") == $id) {
                $connection->send($data);
              }
            }
          }
        }
      }
    }

    public function getChannelByName($name) {
      $channels = $this->getOption("channels");
      if ($channels == false) {
        $channels = array();
      }
      foreach ($channels as $channel) {
        if (strtolower($channel["name"]) == strtolower($name)) {
          return $channel;
        }
      }
      return false;
    }

    public function getOption($key) {
      // Retrieve the requested option if it exists, otherwise return false.
      return (isset($this->options[$key]) ? $this->options[$key] : false);
    }

    public function receiveChannelJoin($name, $data) {
      $source = $data[0];
      $target = $data[1];

      $channels = $source->getOption("channels");
      if ($channels == false) {
        $channels = array();
      }
      $source->setOption("channels", array_values(array_unique(array_merge(
        $channels, array($target)))));
      $channel = $this->getChannelByName($target);
      if ($channel != false) {
        $channel["members"][] = $source->getOption("id");
        $this->setChannelByName($channel["name"], $channel);
      }
      else {
        $channel = array(
          "name" => $target,
          "members" => array(),
          "time" => time()
        );
        $this->setChannelByName($target, $channel);
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
      Logger::info(var_export($this->getOption("channels"), true));
    }

    public function receiveChannelMessage($name, $data) {
      $source = $data[0];
      $target = $data[1];
      $message = $data[2];
      $base = ":".$source->getOption("nick")."!".$source->getOption("ident").
        "@".$source->getHost()." PRIVMSG ".$target->getOption("name")." :";

      if (strlen($base.$message) > 510) {
        $chunks = str_split($message, (510 - strlen($base)));
        foreach ($chunks as $chunk) {
          $this->broadcast($target, $base.$chunk, $source->getOption("id"));
        }
      }
      else {
        $this->broadcast($target, $base.$message, $source->getOption("id"));
      }
    }

    public function receiveNickChange($name, $data) {
      $source = $data[0];
      $oldnick = $data[1];
      $channels = $source->getOption("channels");
      if ($channels == false) {
        return;
      }

      $targets = array();
      foreach ($channels as $channel) {
        if ($this->getOption("channels") != false) {
          $ch = $this->getChannelByName($channel);
          if ($ch != false) {
            $targets = array_values(array_unique(array_merge(
              array_values($targets), array_values($ch["members"]))));
          }
        }
      }

      foreach ($targets as $target) {
        foreach (ConnectionManagement::getConnections() as $t) {
          if ($t->getOption("id") == $target) {
            $t->send(":".$oldnick."!".$source->getOption("ident").
              "@".$source->getHost()." NICK :".$source->getOption("nick"));
          }
        }
      }
    }

    public function setChannelByName($name, $c) {
      $channels = $this->getOption("channels");
      if ($channels == false) {
        $channels = array();
      }
      foreach ($channels as &$channel) {
        if (strtolower($channel["name"]) == strtolower($name)) {
          $channel = $c;
          $this->setOption("channels", $channels);
          return true;
        }
      }
      return false;
    }

    public function setOption($key, $value) {
      // Set an option for this connection.
      $this->options[$key] = $value;
      return true;
    }

    public function receiveUserQuit($name, $data) {
      $source = $data[0];
      $message = $data[1];
      $channels = $source->getOption("channels");
      if ($channels == false) {
        return;
      }

      $targets = array();
      foreach ($channels as $channel) {
        if ($this->getOption("channels") != false) {
          $ch = $this->getChannelByName($channel);
          if ($ch != false) {
            $targets = array_values(array_unique(array_merge(
              array_values($targets), array_values($ch["members"]))));
          }
        }
      }

      foreach ($targets as $target) {
        foreach (ConnectionManagement::getConnections() as $t) {
          if ($t->getOption("id") == $target) {
            $t->send(":".$source->getOption("nick")."!".
              $source->getOption("ident")."@".$source->getHost()." QUIT :".
              $message);
          }
        }
      }
    }

    public function isInstantiated() {
      EventHandling::registerForEvent("channelJoinEvent", $this,
        "receiveChannelJoin");
      EventHandling::registerForEvent("channelMessageEvent", $this,
        "receiveChannelMessage");
      EventHandling::registerForEvent("nickChangeEvent", $this,
        "receiveNickChange");
      EventHandling::registerForEvent("userQuitEvent", $this,
        "receiveUserQuit");
      return true;
    }
  }
?>
