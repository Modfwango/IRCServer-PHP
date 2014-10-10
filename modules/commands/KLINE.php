<?php
  class __CLASSNAME__ {
    public $depend = array("Client", "CommandEvent", "ConnectionCreatedEvent",
      "Numeric", "QUIT", "RehashEvent", "Self");
    public $name = "KLINE";
    private $client = null;
    private $config = array();
    private $numeric = null;
    private $self = null;

    public function receiveConnectionCreated($name, $connection) {
      foreach ($this->config as $mask => $entry) {
        if ($this->client->clientMatchesMask($connection, $mask)) {
          $message = "K-Lined";
          $connection->send("ERROR :Closing Link: ".$connection->getHost().
            " (".$message.": ".$entry["reason"].")");
          ModuleManagement::getModuleByName("QUIT")->notifyQuit(
            null, $connection, $message);
          $connection->setOption("registered", false);
          $connection->disconnect();
          return;
        }
      }
    }

    public function receiveKLINE($name, $data) {
      $connection = $data[0];
      $command = $data[1];

      foreach ($command as $key => $param) {
        if (trim($param) == null) {
          unset($command[$key]);
        }
      }
      $command = array_values($command);

      if ($connection->getOption("registered") == true) {
        if ($connection->getOption("operator") != false) {
          if (count($command) > 1) {
            $minutes = 0;
            if (count($command) > 2 && is_numeric($command[0])) {
              $minutes = intval(array_shift($command));
            }
            $mask = array_shift($command);
            $reason = implode(" ", $command);
            if (!stristr($mask, "!")) {
              if (!stristr($mask, "@")) {
                $mask = "*@".$mask;
              }
              $mask = $this->client->getPrettyMask($mask);
              if (!isset($this->config[$mask])) {
                // Notify operators of K-Line
                foreach (ConnectionManagement::getConnections() as $c) {
                  if ($c->getOption("operator") != false) {
                    $c->send(":".$this->self->getConfigFlag("serverdomain").
                      " NOTICE ".$c->getOption("nick")." :*** Notice -- ".
                      $connection->getOption("nick")."!".
                      $connection->getOption("ident").$connection->getHost().
                      "{".$connection->getOption("operator")."} added ".
                      ($minutes > 0 ? $minutes." min. " : null).
                      "K-Line [".$mask."] [".$reason."]");
                  }
                }
                // Notify author of K-Line
                $connection->send(":".$this->self->getConfigFlag(
                  "serverdomain")." NOTICE ".$connection->getOption(
                  "nick")." :Added ".($minutes > 0 ? $minutes." min. " :
                  null)."K-Line [".$mask."]");

                // Add K-Line to config
                $this->config[$mask] = array(
                  "author" => $connection->getOption("nick")."!".
                    $connection->getOption("ident").$connection->getHost().
                    "{".$connection->getOption("operator")."}",
                  "minutes" => ($minutes * 60),
                  "reason" => $reason,
                  "start" => time()
                );
                $this->flushConfig();

                // Kill affected clients
                foreach ($this->client->getClientsByMatchingMask($mask) as
                          $client) {
                  if ($client != false
                      && $client->getOption("operator") == false) {
                    $message = "K-Lined";
                    $client->send("ERROR :Closing Link: ".$client->getHost().
                      " (".$message.": ".$reason.")");
                    ModuleManagement::getModuleByName("QUIT")->notifyQuit(
                      null, $client, $message);
                    $client->setOption("registered", false);
                    $client->disconnect();
                  }
                }
              }
              else {
                // Already K-Lined
                $connection->send(":".$this->self->getConfigFlag(
                  "serverdomain")." NOTICE ".$connection->getOption(
                  "nick")." :[".$mask."] already K-Lined by ".
                  $this->config[$mask]["author"]." - ".
                  $this->config[$mask]["reason"]);
              }
            }
            else {
              // Invalid host(mask)
              $connection->send(":".$this->self->getConfigFlag(
                "serverdomain")." NOTICE ".$connection->getOption(
                "nick")." :K-Line must be a user@host or host");
            }
          }
          else {
            $connection->send(":".$this->numeric->get("ERR_NEEDMOREPARAMS",
              array(
                $this->self->getConfigFlag("serverdomain"),
                $connection->getOption("nick"),
                $this->name
              )
            ));
          }
        }
        else {
          $connection->send($this->numeric->get("ERR_NOPRIVILEGES", array(
            $this->self->getConfigFlag("serverdomain"),
            ($connection->getOption("nick") ?
            $connection->getOption("nick") : "*")
          )));
        }
      }
      else {
        $connection->send($this->numeric->get("ERR_NOTREGISTERED", array(
          $this->self->getConfigFlag("serverdomain"),
          ($connection->getOption("nick") ?
          $connection->getOption("nick") : "*")
        )));
      }
    }

    public function receiveUNKLINE($name, $data) {
      $connection = $data[0];
      $command = $data[1];

      foreach ($command as $key => $param) {
        if (trim($param) == null) {
          unset($command[$key]);
        }
      }
      $command = array_values($command);

      if ($connection->getOption("registered") == true) {
        if ($connection->getOption("operator") != false) {
          if (count($command) > 0) {
            $mask = array_shift($command);
            $reason = implode(" ", $command);
            if (!stristr($mask, "!")) {
              if (!stristr($mask, "@")) {
                $mask = "*@".$mask;
              }
              $mask = $this->client->getPrettyMask($mask);
              if (isset($this->config[$mask])) {
                // Notify author of K-Line removal
                $connection->send(":".$this->self->getConfigFlag(
                  "serverdomain")." NOTICE ".$connection->getOption(
                  "nick")." :Un-klined [".$mask."]");

                // Notify operators of K-Line removal
                foreach (ConnectionManagement::getConnections() as $c) {
                  if ($c->getOption("operator") != false) {
                    $c->send(":".$this->self->getConfigFlag("serverdomain").
                      " NOTICE ".$c->getOption("nick")." :*** Notice -- ".
                      $connection->getOption("nick")."!".
                      $connection->getOption("ident").$connection->getHost().
                      "{".$connection->getOption("operator")."} removed ".
                      "K-Line [".$mask."]");
                  }
                }

                // Remove K-Line from config
                unset($this->config[$mask]);
                $this->flushConfig();
              }
              else {
                // Not K-Lined
                $connection->send(":".$this->self->getConfigFlag(
                  "serverdomain")." NOTICE ".$connection->getOption(
                  "nick")." :No K-Line for [".$mask."]");
              }
            }
            else {
              // Invalid host(mask)
              $connection->send(":".$this->self->getConfigFlag(
                "serverdomain")." NOTICE ".$connection->getOption(
                "nick")." :K-Line must be a user@host or host");
            }
          }
          else {
            $connection->send(":".$this->numeric->get("ERR_NEEDMOREPARAMS",
              array(
                $this->self->getConfigFlag("serverdomain"),
                $connection->getOption("nick"),
                "UN".$this->name
              )
            ));
          }
        }
        else {
          $connection->send($this->numeric->get("ERR_NOPRIVILEGES", array(
            $this->self->getConfigFlag("serverdomain"),
            ($connection->getOption("nick") ?
            $connection->getOption("nick") : "*")
          )));
        }
      }
      else {
        $connection->send($this->numeric->get("ERR_NOTREGISTERED", array(
          $this->self->getConfigFlag("serverdomain"),
          ($connection->getOption("nick") ?
          $connection->getOption("nick") : "*")
        )));
      }
    }

    private function flushConfig() {
      return StorageHandling::saveFile($this, "config.json",
        json_encode($this->config, JSON_PRETTY_PRINT));
    }

    public function loadConfig($name = null, $data = null) {
      $config = @json_decode(trim(StorageHandling::loadFile($this,
        "config.json")), true);
      if (!is_array($config)) {
        $config = array();
        StorageHandling::saveFile($this, "config.json", json_encode($config,
          JSON_PRETTY_PRINT));
      }
      $this->config = $config;
    }

    public function isInstantiated() {
      $this->loadConfig();
      $this->client = ModuleManagement::getModuleByName("Client");
      $this->numeric = ModuleManagement::getModuleByName("Numeric");
      $this->self = ModuleManagement::getModuleByName("Self");
      EventHandling::registerForEvent("commandEvent", $this, "receiveKLINE",
        "kline");
      EventHandling::registerForEvent("commandEvent", $this, "receiveUNKLINE",
        "unkline");
      EventHandling::registerForEvent("connectionCreatedEvent", $this,
        "receiveConnectionCreated");
      EventHandling::registerForEvent("rehashEvent", $this, "loadConfig");
      return true;
    }
  }
?>
