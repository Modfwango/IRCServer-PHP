<?php
  class @@CLASSNAME@@ {
    public $depend = array("Database", "NSClient", "Util");
    public $name = "NSREGISTER";
    private $db = null;
    private $util = null;

    public function receiveNickServCommand($name, $data) {
      $source = $data[0];
      $target = $data[1];
      $params = $data[2];

      if (count($params) >= 2) {
        if (!$this->accountExists($source->getOption("nick"))) {
          if (strlen($params[0]) > 7) {
            if ($this->util->validateEmail($params[1])) {
              // Register the nickname.
              if ($this->registerAccount($source->getOption("nick"),
                password_hash($params[0], PASSWORD_BCRYPT), $params[1])) {
                // Success!
                $source->send(":".$target->getOption("nick")."!".
                  $target->getOption("ident")."@".$target->getHost()." ".
                  "PRIVMSG ".$source->getOption("nick")." :Your current ".
                  "nickname is now registered with the email address \002".
                  $params[1]."\002.");
              }
              else {
                // Problem registering account.
                $source->send(":".$target->getOption("nick")."!".
                  $target->getOption("ident")."@".$target->getHost()." ".
                  "PRIVMSG ".$source->getOption("nick")." :Unfortunately, ".
                  "there was an internal problem registering your account.  ".
                  "Please try again later.");
              }
            }
            else {
              // Invalid email address.
              $source->send(":".$target->getOption("nick")."!".
                $target->getOption("ident")."@".$target->getHost()." PRIVMSG ".
                $source->getOption("nick")." :The email address you provided ".
                "is invalid.");
            }
          }
          else {
            // Password is too simple.
            $source->send(":".$target->getOption("nick")."!".
              $target->getOption("ident")."@".$target->getHost()." PRIVMSG ".
              $source->getOption("nick")." :Passwords have an 8 character ".
              "minimum requirement.");
          }
        }
        else {
          // Account with this nick already exists.
          $source->send(":".$target->getOption("nick")."!".
            $target->getOption("ident")."@".$target->getHost()." PRIVMSG ".
            $source->getOption("nick")." :This nickname is already ".
            "registered.");
        }
      }
      else {
        // Not enough parameters.
        $source->send(":".$target->getOption("nick")."!".
          $target->getOption("ident")."@".$target->getHost()." ".
          "PRIVMSG ".$source->getOption("nick")." :This command requires more ".
          "parameters.  Refer to /msg NickServ HELP <command> for correct ".
          "usage.");
      }
    }

    private function accountExists($account) {
      // Check if an account with the same display name exists.
      if (count($this->db->getRows("nickserv", "accounts", "name", $account,
          true)) > 0) {
        // The account exists.
        return true;
      }

      // Check for overlapping accounts that own this nickname.
      $accounts = $this->db->getAllRows("nickserv", "accounts");
      foreach ($accounts as $acct) {
        foreach ($acct["nicks"] as $nick) {
          if (strtolower($nick) == strtolower($account)) {
            // The account exists.
            return true;
          }
        }
      }

      // The account doesn't exist.
      return false;
    }

    private function registerAccount($name, $password, $email) {
      // Prepare the row.
      $account = array(
        "guid" => $this->util->genUUID(),
        "name" => $name,
        "nicks" => array($name),
        "password" => $password,
        "email" => $email
      );
      // Attempt to add the row to the accounts table.
      if ($this->db->addRow("nickserv", "accounts", $account)) {
        // Success!
        return true;
      }
      // Failure.
      return false;
    }

    public function isInstantiated() {
      $this->db = ModuleManagement::getModuleByName("Database");
      if (!$this->db->databaseExists("nickserv")) {
        $this->db->createDatabase("nickserv");
      }
      if (!$this->db->tableExists("nickserv", "accounts")) {
        $this->db->createTable("nickserv", "accounts");
      }
      $this->util = ModuleManagement::getModuleByName("Util");
      EventHandling::registerForEvent("nsCommandEvent", $this,
        "receiveNickServCommand", array("register", "Allows you to protect ".
          "your nickname with a password by registering an account using your ".
          "email address.\nUsage: /msg NickServ REGISTER <password> <email>",
          "Example:\n/msg NickServ REGISTER password user@email.com"));
      return true;
    }
  }
?>
