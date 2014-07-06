<?php
  class @@CLASSNAME@@ {
    public $depend = array("NickChangeEvent", "PrivateMessageEvent",
      "UserRegistrationEvent");
    public $name = "Client";
    private $clients = array("byhost" => array(), "byident" => array(),
      "byid" => array(), "bynick" => array());

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

    public function getClientIDByHost($host) {
      // Retrieve the requested ID if it exists, otherwise return false.
      return (isset($this->clients["byhost"][strtolower($host])) ?
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
        Logger::info(var_export($this->clients, true));
        return true;
      }
      return false;
    }

    public function unsetClient($client) {
      if (isset($this->clients["byid"][$client->getOption("id")])) {
        unset($this->clients["byid"][$client->getOption("id")]);
        foreach ($this->clients["byhost"] as $key => $byhost) {
          if ($byhost == $client->getOption("id")) {
            unset($this->clients["byhost"][$key]);
          }
        }
        foreach ($this->clients["byident"] as $key => $byident) {
          if ($byident == $client->getOption("id")) {
            unset($this->clients["byident"][$key]);
          }
        }
        foreach ($this->clients["bynick"] as $key => $bynick) {
          if ($bynick == $client->getOption("id")) {
            unset($this->clients["bynick"][$key]);
          }
        }
      }
    }

    public function isInstantiated() {
      EventHandling::registerForEvent("nickChangeEvent", $this,
        "receiveNickChange");
      EventHandling::registerForEvent("privateMessageEvent", $this,
        "receivePrivateMessage");
      EventHandling::registerForEvent("userRegistrationEvent", $this,
        "receiveUserRegistration");
      return true;
    }
  }
?>
