<?php
  class @@CLASSNAME@@ {
    public $depend = array("Client", "CommandEvent");
    public $name = "OPER";
    private $client = null;
    private $opers = array();

    public function receiveCommand($name, $data) {
      $connection = $data[0];
      $command = $data[1];

      foreach ($command as $key => $param) {
        if (trim($param) == null) {
          unset($command[$key]);
        }
      }
      $command = array_values($command);

      if ($connection->getOption("registered") == true) {
        if (count($command) > 1) {
          if ($connection->getOption("operator") != true) {
            foreach ($this->opers as $oname => $oper) {
              if (strtolower($oname) == strtolower($command[0])) {
                if (isset($oper["mask"])) {
                  if (!is_array($oper["mask"])) {
                    $oper["mask"] = array($oper["mask"]);
                  }
                  $matches = false;
                  foreach ($oper["mask"] as $mask) {
                    if ($this->client->clientMatchesMask($connection, $mask)) {
                      $matches = true;
                      break;
                    }
                  }
                  if ($matches == true) {
                    if (password_verify($command[1], $oper["hash"])) {
                      $connection->setOption("operator", true);
                      $connection->send(":".__SERVERDOMAIN__." 381 ".
                        $connection->getOption("nick")." :You are now an IRC ".
                        "operator");
                      return;
                    }
                    else {
                      $connection->send(":".__SERVERDOMAIN__." 464 ".
                        $connection->getOption("nick")." :Password Incorrect");
                      return;
                    }
                  }
                }
              }
            }
            $connection->send(":".__SERVERDOMAIN__." 491 ".
              $connection->getOption("nick")." :No appropriate operator blocks ".
              "were found for your host");
          }
          else {
            $connection->send(":".__SERVERDOMAIN__." 381 ".
              $connection->getOption("nick")." :You are now an IRC operator");
          }
        }
        else {
          $connection->send(":".__SERVERDOMAIN__." 461 ".
            $connection->getOption("nick")." OPER :Not enough parameters");
        }
      }
      else {
        $connection->send(":".__SERVERDOMAIN__." 451 ".(
          $connection->getOption("nick") ? $connection->getOption("nick") :
          "*")." :You have not registered");
      }
    }

    public function isInstantiated() {
      $opers = json_decode(StorageHandling::loadFile($this, "opers.txt"), true);
      if (!is_array($opers)) {
        $opers = array(
          "clay" => array(
            "mask" => array(
              "clayfreeman!*@clayfreeman.com",
              "clay@*.clayfreeman.com",
              "192.168.1.*"
            ),
            "hash" => "Run '/mkpasswd <password>' on the IRCd to get a hash"
          ),
          "matthew" => array(
            "mask" => "*@mattwb65.com",
            "hash" => "$2y$10$.vGA1O9wmRjrwAVXD98HNOgsNpDczlqm3Jq7KnEd1rVAGv3F".
              "ykk1a"
          )
        );
        StorageHandling::saveFile($this, "opers.txt", json_encode($opers));
      }
      $this->opers = $opers;
      $this->client = ModuleManagement::getModuleByName("Client");
      EventHandling::registerForEvent("commandEvent", $this, "receiveCommand",
        "oper");
      return true;
    }
  }
?>
