<?php
  class @@CLASSNAME@@ {
    public $depend = array("Modes", "NickChangeEvent", "PrivateMessageEvent",
      "UserModeEvent", "UserRegistrationEvent");
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
      if ($this->matchGlob($nick, $client->getOption("nick"))
          && $this->matchGlob($ident, $client->getOption("ident"))
          && $this->matchGlob($host, $client->getHost())) {
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

    public function getClientByHost($host) {
      // Retrieve the requested client if it exists, otherwise return false.
      return $this->getClientByID($this->getClientIDByHost($host));
    }

    public function getClientByIdent($ident) {
      // Retrieve the requested client if it exists, otherwise return false.
      return $this->getClientByID($this->getClientIDByIdent($ident));
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

    public function getClientByRealname($realname) {
      // Retrieve the requested client if it exists, otherwise return false.
      return $this->getClientByID($this->getClientIDByRealname($realname));
    }

    public function getClientIDByHost($host) {
      // Retrieve the requested ID if it exists, otherwise return false.
      return (isset($this->clients["byhost"][strtolower($host)]) ?
        $this->clients["byhost"][strtolower($host)] : false);
    }

    public function getClientIDByIdent($ident) {
      // Retrieve the requested ID if it exists, otherwise return false.
      return (isset($this->clients["byident"][strtolower($ident)]) ?
        $this->clients["byident"][strtolower($ident)] : false);
    }

    public function getClientIDByNick($nick) {
      // Retrieve the requested ID if it exists, otherwise return false.
      return (isset($this->clients["bynick"][strtolower($nick)]) ?
        $this->clients["bynick"][strtolower($nick)] : false);
    }

    public function getClientIDByRealname($realname) {
      // Retrieve the requested ID if it exists, otherwise return false.
      return (isset($this->clients["byrealname"][strtolower($realname)]) ?
        $this->clients["byrealname"][strtolower($realname)] : false);
    }

    public function getClientIDsByMatchingHost($pattern) {
      $clients = array();
      foreach ($this->clients["byhost"] as $host => $id) {
        if ($this->matchGlob($pattern, $host)) {
          $clients[] = $id;
        }
      }
      return $clients;
    }

    public function getClientIDsByMatchingIdent($pattern) {
      $clients = array();
      foreach ($this->clients["byident"] as $ident => $id) {
        if ($this->matchGlob($pattern, $ident)) {
          $clients[] = $id;
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

      $matches = array_unique(array_merge(
        $this->getClientIDsByMatchingNick($nick),
        $this->getClientIDsByMatchingIdent($ident),
        $this->getClientIDsByMatchingHost($host)));
      return $matches;
    }

    public function getClientIDsByMatchingNick($pattern) {
      $clients = array();
      foreach ($this->clients["bynick"] as $nick => $id) {
        if ($this->matchGlob($pattern, $nick)) {
          $clients[] = $id;
        }
      }
      return $clients;
    }

    public function getClientIDsByMatchingRealname($pattern) {
      $clients = array();
      foreach ($this->clients["byrealname"] as $realname => $id) {
        if ($this->matchGlob($pattern, $realname)) {
          $clients[] = $id;
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
      Logger::info(var_export($pattern, true));
      Logger::info(var_export($string, true));
      $regex = str_replace(array("*", "?"), array(".*", "."),
        preg_quote($pattern));
      return preg_match('/^'.$regex.'$/i', $string);
    }

    public function receiveNickChange($name, $data) {
      $source = $data[0];
      $oldnick = $data[1];

      $source->send(":".$oldnick."!".$source->getOption("ident").
        "@".$source->getHost()." NICK ".$source->getOption("nick"));
      $this->setClient($source);
    }

    public function receivePrivateMessage($name, $data) {
      $source = $data[0];
      $target = $data[1];
      $message = $data[2];
      $base = ":".$source->getOption("nick")."!".$source->getOption("ident").
        "@".$source->getHost()." PRIVMSG ".$target->getOption("nick")." :";

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
      $this->setClient($connection);
    }

    public function setClient($client) {
      // Remove any pre-existing client indexes.
      $this->unsetClient($client);
      // Set a client.
      if ($client->getOption("id") != false) {
        $this->clients["byid"][$client->getOption("id")] = $client;
        if ($client->getHost() != false) {
          $this->clients["byhost"][strtolower($client->getHost())] =
            $client->getOption("id");
        }
        if ($client->getOption("ident") != false) {
          $this->clients["byident"][strtolower($client->getOption("ident"))] =
            $client->getOption("id");
        }
        if ($client->getOption("nick") != false) {
          $this->clients["bynick"][strtolower($client->getOption("nick"))] =
            $client->getOption("id");
        }
        if ($client->getOption("realname") != false) {
          $this->clients["byrealname"][strtolower(
            $client->getOption("realname"))] = $client->getOption("id");
        }
        return true;
      }
      return false;
    }

    public function unsetClient($client) {
      if (isset($this->clients["byid"][$client->getOption("id")])) {
        unset($this->clients["byid"][$client->getOption("id")]);
        if (in_array($client->getOption("id"), $this->clients["byhost"])) {
          $this->clients["byhost"] = array_diff($this->clients["byhost"],
            array($client->getOption("id")));
        }
        if (in_array($client->getOption("id"), $this->clients["byident"])) {
          $this->clients["byident"] = array_diff($this->clients["byident"],
            array($client->getOption("id")));
        }
        if (in_array($client->getOption("id"), $this->clients["bynick"])) {
          $this->clients["bynick"] = array_diff($this->clients["bynick"],
            array($client->getOption("id")));
        }
        if (in_array($client->getOption("id"), $this->clients["byrealname"])) {
          $this->clients["byrealname"] = array_diff(
            $this->clients["byrealname"], array($client->getOption("id")));
        }
      }
    }

    public function isInstantiated() {
      $this->modes = ModuleManagement::getModuleByName("Modes");
      EventHandling::registerForEvent("nickChangeEvent", $this,
        "receiveNickChange");
      EventHandling::registerForEvent("privateMessageEvent", $this,
        "receivePrivateMessage");
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
