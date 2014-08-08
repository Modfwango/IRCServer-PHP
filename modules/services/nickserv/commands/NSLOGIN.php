<?php
  class @@CLASSNAME@@ {
    public $depend = array("Database", "NSClient", "Util");
    public $name = "NSLOGIN";
    private $db = null;
    private $util = null;

    public function receiveNickServCommand($name, $data) {
      $source = $data[0];
      $target = $data[1];
      $params = $data[2];

      if (!is_string($source->getOption("loggedin"))) {
        if (count($params) >= 2) {
          // An account name was specified.
          $account = $this->getAccount($params[0]);
          if (is_array($account)) {
            // The account was found.
            if (password_verify($params[1], $account["password"])) {
              // Password correct!
              $source->setOption("loggedin", $account["guid"]);
              $source->send(":".$target->getOption("nick")."!".
                $target->getOption("ident")."@".$target->getHost()." ".
                "PRIVMSG ".$source->getOption("nick")." :You are now logged ".
                "in as \002".$account["name"]."\002.");
            }
            else {
              // Password wrong.
              $source->send(":".$target->getOption("nick")."!".
                $target->getOption("ident")."@".$target->getHost()." ".
                "PRIVMSG ".$source->getOption("nick")." :The password you ".
                "provided is incorrect.");
            }
          }
          else {
            // No matching account was found.
            $source->send(":".$target->getOption("nick")."!".
              $target->getOption("ident")."@".$target->getHost()." ".
              "PRIVMSG ".$source->getOption("nick")." :That account was not ".
              "found.");
          }
        }
        elseif (count($params) == 1) {
          // Only a password was specified.
          $account = $this->getAccount($source->getOption("nick"));
          if (is_array($account)) {
            // The account was found.
            if (password_verify($params[0], $account["password"])) {
              // Password correct!
              $source->setOption("loggedin", $account["guid"]);
              $source->send(":".$target->getOption("nick")."!".
                $target->getOption("ident")."@".$target->getHost()." ".
                "PRIVMSG ".$source->getOption("nick")." : You are now logged ".
                "in as \002".$account["name"]."\002.");
            }
            else {
              // Password wrong.
              $source->send(":".$target->getOption("nick")."!".
                $target->getOption("ident")."@".$target->getHost()." ".
                "PRIVMSG ".$source->getOption("nick")." :The password you ".
                "provided is incorrect.");
            }
          }
          else {
            // This nickname isn't registered.
            $source->send(":".$target->getOption("nick")."!".
              $target->getOption("ident")."@".$target->getHost()." ".
              "PRIVMSG ".$source->getOption("nick")." :Your nickname isn't ".
              "registered.");
          }
        }
        else {
          // Not enough parameters.
          $source->send(":".$target->getOption("nick")."!".
            $target->getOption("ident")."@".$target->getHost()." ".
            "PRIVMSG ".$source->getOption("nick")." :This command requires ".
            "more parameters.  Refer to /msg NickServ HELP <command> for ".
            "correct usage.");
        }
      }
      else {
        // Already logged in.
        $account = $this->getAccountByID($source->getOption("loggedin"));
        if (is_array($account)) {
          // Everything is in order; proceed with notice.
          $source->send(":".$target->getOption("nick")."!".
            $target->getOption("ident")."@".$target->getHost()." ".
            "PRIVMSG ".$source->getOption("nick")." :You're already logged in ".
            "as \002".$account["name"]."\002.");
        }
        else {
          // Something weird is happening; log out the user just to be safe.
          $source->send(":".$target->getOption("nick")."!".
            $target->getOption("ident")."@".$target->getHost()." ".
            "PRIVMSG ".$source->getOption("nick")." :Due to an internal ".
            "error, you have been logged out.");
        }
      }
    }

    public function receiveUserQuit($name, $data) {
      $data[0]->setOption("loggedin", false);
    }

    private function getAccount($account) {
      // Load a list of accounts with provided account name.
      $accounts = $this->db->getRows("nickserv", "accounts", "name", $account,
        true);
      // If a result was found, return it.
      if (count($accounts) > 0) {
        // The account exists.
        return array_shift($accounts);
      }

      // Load a list of all accounts.
      $accounts = $this->db->getAllRows("nickserv", "accounts");
      foreach ($accounts as $acct) {
        foreach ($acct["nicks"] as $nick) {
          if (strtolower($nick) == strtolower($account)) {
            // The account exists.
            return $acct;
          }
        }
      }

      // The account doesn't exist.
      return false;
    }

    private function getAccountByID($id) {
      // Load a list of accounts with provided account id.
      $accounts = $this->db->getRows("nickserv", "accounts", "guid", $id, true);
      // If a result was found, return it.
      if (count($accounts) > 0) {
        // The account exists.
        return array_shift($accounts);
      }

      // The account doesn't exist.
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
        "receiveNickServCommand", array("identify", "Allows you to login to ".
          "your account.\nUsage: /msg NickServ IDENTIFY [account] <password>",
          "Examples:\n/msg NickServ IDENTIFY password\n".
          "/msg NickServ IDENTIFY username password"));
      EventHandling::registerForEvent("nsCommandEvent", $this,
        "receiveNickServCommand", array("id", "A command alias for IDENTIFY.",
          null));
      EventHandling::registerForEvent("nsCommandEvent", $this,
        "receiveNickServCommand", array("login", "A command alias for ".
          "IDENTIFY.", null));
      EventHandling::registerForEvent("userQuitEvent", $this,
        "receiveUserQuit");
      return true;
    }
  }
?>
