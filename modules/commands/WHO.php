<?php
  class @@CLASSNAME@@ {
    public $depend = array("Channel", "CommandEvent");
    public $name = "WHO";
    private $channel = null;

    public function receiveCommand($name, $data) {
      $connection = $data[0];
      $command = $data[1];

      foreach ($command as $key => $param) {
        if (trim($param) == null) {
          unset($command[$key]);
        }
      }
      $command = array_values($command);

      if (strtolower($command[0]) == "who") {
        if ($connection->getOption("registered") == true) {
          if (count($command) > 1) {
            $users = array();
            foreach ($targets as ) {
              $channel = $this->channel->getChannelByName($command[1]);
              if ($channel != false) {
                foreach ($channel["members"] as $member) {
                  $users[] = $this->client->getClientByID($member);
                }
              }
              else {
                if ($command[1] == "0") {
                  $command[1] = "*";
                  $users = ConnectionManagement::getConnections();
                }
                else {
                  foreach ($this->client->getClientsByMatchingHost($command[1])
                            as $client) {
                    $users[] = $client;
                  }
                  foreach ($this->client->getClientsByMatchingIdent($command[1])
                            as $client) {
                    $users[] = $client;
                  }
                  foreach ($this->client->getClientsByMatchingNick($command[1])
                            as $client) {
                    $users[] = $client;
                  }
                  foreach ($this->client->getClientsByMatchingRealname(
                            $command[1]) as $client) {
                    $users[] = $client;
                  }
                }
              }
            }
            foreach ($users as $user) {
              $connection->send(":".__SERVERDOMAIN__." 352 ".
                $connection->getOption("nick")." ".$command[1]." ".
                $user->getOption("ident")." ".$user->getHost()." ".
                __SERVERDOMAIN__." ".$user->getOption("nick").
                " H :0 ".$user->getOption("realname"));
            }
            $connection->send(":".__SERVERDOMAIN__." 315 ".
              $connection->getOption("nick")." ".$command[1].
              " :End of /WHO list.");
          }
          else {
            $connection->send(":".__SERVERDOMAIN__." 461 ".
              $connection->getOption("nick")." WHO :Not enough parameters");
          }
        }
        else {
          $connection->send(":".__SERVERDOMAIN__." 451 ".(
            $connection->getOption("nick") ? $connection->getOption("nick") :
            "*")." :You have not registered");
        }
        return true;
      }
      return false;
    }

    public function isInstantiated() {
      $this->channel = ModuleManagement::getModuleByName("Channel");
      EventHandling::registerForEvent("commandEvent", $this, "receiveCommand");
      return true;
    }
  }
?>
