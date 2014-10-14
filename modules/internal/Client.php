<?php
  class __CLASSNAME__ {
    public $depend = array("Modes", "NickChangeEvent", "PrivateMessageEvent",
      "PrivateNoticeEvent", "UserModeEvent", "UserQuitEvent",
      "UserRegistrationEvent");
    public $name = "Client";
    private $clients = array("byhost" => array(), "byident" => array(),
      "byid" => array(), "bynick" => array(), "byrealname" => array());
    private $modes = null;

    public function clientHostMatchesPattern($client, $pattern) {
      // Check if the client's host matches the provided glob pattern.
      return ($this->matchGlob($pattern, $client->getHost()) ? true : false);
    }

    public function clientIdentMatchesPattern($client, $pattern) {
      // Check if the client's ident matches the provided glob pattern.
      return ($this->matchGlob($pattern, $client->getOption("ident")) ? true :
        false);
    }

    public function clientMatchesMask($client, $mask) {
      $mask = $this->getPrettyMask($mask);
      $nick = explode("!", $mask);
      $ident = explode("@", array_pop($nick));
      $nick = array_shift($nick);
      $host = array_pop($ident);
      $ident = array_shift($ident);

      // If the client's nick, ident, and host match the provided mask,
      // return true.
      if ($this->clientNickMatchesPattern($client, $nick)
          && $this->clientIdentMatchesPattern($client, $ident)
          && $this->clientHostMatchesPattern($client, $host)) {
        return true;
      }
      return false;
    }

    public function clientNickMatchesPattern($client, $pattern) {
      // Check if the client's nick matches the provided glob pattern.
      return ($this->matchGlob($pattern, $client->getOption("nick")) ? true :
        false);
    }

    public function clientRealnameMatchesPattern($client, $pattern) {
      // Check if the client's realname matches the provided glob pattern.
      return ($this->matchGlob($pattern, $client->getOption("realname")) ?
        true : false);
    }

    public function getClientByID($id) {
      // Retrieve the requested client if it exists, otherwise return false.
      return (isset($this->clients["byid"][$id]) ? $this->clients["byid"][$id] :
        false);
    }

    public function getClientByNick($nick) {
      // Retrieve the requested client if it exists, otherwise return false.
      return $this->getClientByID($this->getClientIDByNick($nick));
    }

    public function getClientIDByNick($nick) {
      // Retrieve the requested ID if it exists, otherwise return false.
      return (isset($this->clients["bynick"][strtolower($nick)]) ?
        $this->clients["bynick"][strtolower($nick)] : false);
    }

    public function getClientIDsByMatchingHost($pattern) {
      $clients = array();
      foreach ($this->clients["byhost"] as $host => $ids) {
        foreach ($ids as $id) {
          if ($this->matchGlob($pattern, $host)) {
            Logger::debug("Client host [".$host."] matches [".$pattern."]");
            $clients[] = $id;
          }
          else {
            Logger::debug("Client host [".$host."] doesn't match [".
              $pattern."]");
          }
        }
      }
      return $clients;
    }

    public function getClientIDsByMatchingIdent($pattern) {
      $clients = array();
      foreach ($this->clients["byident"] as $ident => $ids) {
        foreach ($ids as $id) {
          if ($this->matchGlob($pattern, $ident)) {
            Logger::debug("Client ident [".$ident."] matches [".$pattern."]");
            $clients[] = $id;
          }
          else {
            Logger::debug("Client ident [".$ident."] doesn't match [".
              $pattern."]");
          }
        }
      }
      return $clients;
    }

    public function getClientIDsByMatchingMask($mask) {
      $mask = $this->getPrettyMask($mask);
      $nick = explode("!", $mask);
      $ident = explode("@", array_pop($nick));
      $nick = array_shift($nick);
      $host = array_pop($ident);
      $ident = array_shift($ident);

      Logger::debug("Returning client IDs that intersect with matching globs ".
        "for nick: [".$nick."]  ident: [".$ident."]  and host [".$host."]");

      $matches = array_unique(array_intersect(
        $this->getClientIDsByMatchingNick($nick),
        $this->getClientIDsByMatchingIdent($ident),
        $this->getClientIDsByMatchingHost($host)));
      return $matches;
    }

    public function getClientIDsByMatchingNick($pattern) {
      $clients = array();
      foreach ($this->clients["bynick"] as $nick => $id) {
        if ($this->matchGlob($pattern, $nick)) {
          Logger::debug("Client nick [".$nick."] matches [".$pattern."]");
          $clients[] = $id;
        }
        else {
          Logger::debug("Client nick [".$nick."] doesn't match [".$pattern."]");
        }
      }
      return $clients;
    }

    public function getClientIDsByMatchingRealname($pattern) {
      $clients = array();
      foreach ($this->clients["byrealname"] as $realname => $ids) {
        foreach ($ids as $id) {
          if ($this->matchGlob($pattern, $realname)) {
            Logger::debug("Client realname [".$realname."] matches [".
              $pattern."]");
            $clients[] = $id;
          }
          else {
            Logger::debug("Client realname [".$realname."] doesn't match [".
              $pattern."]");
          }
        }
      }
      return $clients;
    }

    public function getClientsByMatchingHost($pattern) {
      $clients = array();
      foreach ($this->getClientIDsByMatchingHost($pattern) as $id) {
        $clients[] = $this->getClientByID($id);
      }
      return $clients;
    }

    public function getClientsByMatchingIdent($pattern) {
      $clients = array();
      foreach ($this->getClientIDsByMatchingIdent($pattern) as $id) {
        $clients[] = $this->getClientByID($id);
      }
      return $clients;
    }

    public function getClientsByMatchingMask($pattern) {
      $clients = array();
      foreach ($this->getClientIDsByMatchingMask($pattern) as $id) {
        $clients[] = $this->getClientByID($id);
      }
      return $clients;
    }

    public function getClientsByMatchingNick($pattern) {
      $clients = array();
      foreach ($this->getClientIDsByMatchingNick($pattern) as $id) {
        $clients[] = $this->getClientByID($id);
      }
      return $clients;
    }

    public function getClientsByMatchingRealname($pattern) {
      $clients = array();
      foreach ($this->getClientIDsByMatchingRealname($pattern) as $id) {
        $clients[] = $this->getClientByID($id);
      }
      return $clients;
    }

    public function getPrettyMask($mask) {
      $nick = "*";
      $ident = "*";
      $host = "*";
      if (stristr($mask, "!") && stristr($mask, "@")) {
        preg_match("/(.*)!(.*)@(.*)/i", $mask, $matches);
        if (trim($matches[1]) != null) {
          $nick = str_ireplace("!", null, str_ireplace("@", null, $matches[1]));
        }
        if (trim($matches[2]) != null) {
          $ident = str_ireplace("!", null, str_ireplace("@", null,
            $matches[2]));
        }
        if (trim($matches[3]) != null) {
          $host = str_ireplace("!", null, str_ireplace("@", null, $matches[3]));
        }
      }
      elseif (stristr($mask, "!")) {
        preg_match("/(.*)!(.*)/i", $mask, $matches);
        if (trim($matches[1]) != null) {
          $nick = str_ireplace("!", null, str_ireplace("@", null, $matches[1]));
        }
        if (trim($matches[2]) != null) {
          $ident = str_ireplace("!", null, str_ireplace("@", null,
            $matches[2]));
        }
      }
      elseif (stristr($mask, "@")) {
        preg_match("/(.*)@(.*)/i", $mask, $matches);
        if (trim($matches[1]) != null) {
          $ident = str_ireplace("!", null, str_ireplace("@", null,
            $matches[1]));
        }
        if (trim($matches[2]) != null) {
          $host = str_ireplace("!", null, str_ireplace("@", null, $matches[2]));
        }
      }
      else {
        if (trim($mask) != null) {
          if (stristr($mask, ".")) {
            $host = str_ireplace("!", null, str_ireplace("@", null, $mask));
          }
          else {
            $nick = str_ireplace("!", null, str_ireplace("@", null, $mask));
          }
        }
      }
      return $nick."!".$ident."@".$host;
    }

    public function hasModes($id, $modes) {
      $c = $this->getClientByID($id);
      if ($c != false) {
        $return = array();
        foreach ($modes as $mode) {
          if (is_array($c->getOption("modes"))
              && count($c->getOption("modes")) > 0) {
            foreach ($c->getOption("modes") as $cm) {
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

    private function matchGlob($pattern, $string) {
      return preg_match('/^'.str_replace(array("\\*", "\\?"), array(".*", "."),
        preg_quote($pattern)).'$/i', $string);
    }

    public function receiveNickChange($name, $data) {
      $source = $data[0];
      $oldnick = $data[1];

      $source->setOption("idle", time());

      $source->send(":".$oldnick."!".$source->getOption("ident").
        "@".$source->getHost()." NICK ".$source->getOption("nick"));
      $this->setClient($source);
    }

    public function receivePrivateEvent($name, $data) {
      $source = $data[0];
      $target = $data[1];
      $message = $data[2];
      $base = ":".$source->getOption("nick")."!".$source->getOption("ident").
        "@".$source->getHost().($name == "privateMessageEvent" ? " PRIVMSG " :
        null).($name == "privateNoticeEvent" ? " NOTICE " : null).
        $target->getOption("nick")." :";

      $source->setOption("idle", time());

      if (strlen($base.$message) > 510) {
        $chunks = str_split($message, (510 - strlen($base)));
        foreach ($chunks as $chunk) {
          $target->send($base.$chunk);
        }
      }
      else {
        $target->send($base.$message);
      }
    }

    public function receiveUserMode($name, $data) {
      $source = $data[0];
      $modes = $data[1];

      $source->setOption("idle", time());

      if (count($modes) == 0) {
        return;
      }

      $modesdone = array();
      $cl = $source->getOption("modes");
      if ($cl == false) {
        $cl = array();
      }
      foreach ($modes as $mode) {
        if ($mode["operation"] == "+") {
          $modesdone[] = $mode;
          $cl[] = $mode;
        }
        else {
          if (!isset($mode["param"])) {
            foreach ($cl as $key => $m) {
              if ($m["name"] == $mode["name"]) {
                $modesdone[] = $mode;
                unset($cl[$key]);
              }
            }
          }
          else {
            foreach ($cl as $key => $m) {
              if ($m["name"] == $mode["name"]
                  && $m["param"] == $mode["param"]) {
                $modesdone[] = $mode;
                unset($cl[$key]);
              }
            }
          }
        }
      }

      if (count($modesdone) == 0) {
        return;
      }

      $source->setOption("modes", $cl);

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
        if (isset($mode["param"])) {
          $params .= " ".$mode["param"];
        }
      }
      $modeString = $modes.$params;

      $source->send(":".$source->getOption("nick")." MODE ".
        $source->getOption("nick")." :".$modeString);
    }

    public function receiveUserQuit($name, $data) {
      $this->unsetClient($data[0]);
    }

    public function receiveUserRegistration($name, $connection) {
      $connection->setOption("nickts", time());
      $this->setClient($connection);
    }

    public function setClient($client) {
      // Remove any pre-existing client indexes.
      $this->unsetClient($client);
      // Set a client.
      if ($client->getOption("id") != false) {
        $this->clients["byid"][$client->getOption("id")] = $client;
        if ($client->getHost() != false) {
          if (!isset($this->clients["byhost"][strtolower(
              $client->getHost())])) {
            $this->clients["byhost"][strtolower($client->getHost())] = array();
          }
          $this->clients["byhost"][strtolower($client->getHost())][] =
            $client->getOption("id");
        }
        if ($client->getOption("ident") != false) {
          if (!isset($this->clients["byident"][strtolower($client->getOption(
              "ident"))])) {
            $this->clients["byident"][strtolower($client->getOption("ident"))] =
              array();
          }
          $this->clients["byident"][strtolower($client->getOption("ident"))][] =
            $client->getOption("id");
        }
        if ($client->getOption("nick") != false) {
          $this->clients["bynick"][strtolower($client->getOption("nick"))] =
            $client->getOption("id");
        }
        if ($client->getOption("realname") != false) {
          if (!isset($this->clients["byrealname"][strtolower(
              $client->getOption("realname"))])) {
            $this->clients["byrealname"][strtolower($client->getOption(
              "realname"))] = array();
          }
          $this->clients["byrealname"][strtolower(
            $client->getOption("realname"))][] = $client->getOption("id");
        }
        return true;
      }
      return false;
    }

    public function unsetClient($client) {
      if (isset($this->clients["byid"][$client->getOption("id")])) {
        unset($this->clients["byid"][$client->getOption("id")]);
        foreach ($this->clients["byhost"] as &$byhost) {
          if (in_array($client->getOption("id"), $byhost)) {
            $byhost = array_diff($byhost, array($client->getOption("id")));
          }
        }
        foreach ($this->clients["byident"] as &$byident) {
          if (in_array($client->getOption("id"), $byident)) {
            $byident = array_diff($byident, array($client->getOption("id")));
          }
        }
        if (in_array($client->getOption("id"), $this->clients["bynick"])) {
          $this->clients["bynick"] = array_diff($this->clients["bynick"],
            array($client->getOption("id")));
        }
        foreach ($this->clients["byrealname"] as &$byrealname) {
          if (in_array($client->getOption("id"), $byrealname)) {
            $byrealname = array_diff($byrealname, array(
              $client->getOption("id")));
          }
        }
      }
    }

    public function isUnloadable() {
      return false;
    }

    public function isInstantiated() {
      $this->modes = ModuleManagement::getModuleByName("Modes");
      EventHandling::registerForEvent("nickChangeEvent", $this,
        "receiveNickChange");
      EventHandling::registerForEvent("privateMessageEvent", $this,
        "receivePrivateEvent");
      EventHandling::registerForEvent("privateNoticeEvent", $this,
        "receivePrivateEvent");
      EventHandling::registerForEvent("userQuitEvent", $this,
        "receiveUserQuit");
      EventHandling::registerForEvent("userModeEvent", $this,
        "receiveUserMode");
      EventHandling::registerForEvent("userRegistrationEvent", $this,
        "receiveUserRegistration");
      return true;
    }
  }
?>
