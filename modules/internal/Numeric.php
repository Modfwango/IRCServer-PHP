<?php
  class __CLASSNAME__ {
    public $name = "Numeric";
    private $config = null;

    public function get($numeric, $params = array()) {
      if (isset($this->config[$numeric])) {
        return call_user_func_array("sprintf", array_merge(array(
          $this->config[$numeric]), array_values($params)));
      }
      return false;
    }

    public function loadConfig($name = null, $data = null) {
      $config = @json_decode(trim(StorageHandling::loadFile($this,
        "config.json")), true);
      if (!is_array($config)) {
        $config = array(
          "RPL_WELCOME" =>
            ":%1$s 001 %2$s :Welcome to the %3$s Internet Relay Chat Network ".
            "%2$s",
          "RPL_YOURHOST" =>
            ":%1$s 002 %2$s :Your host is %1$s [%3$s/%4$s], running version ".
            "%5$s",
          "RPL_CREATED" =>
            ":%s 003 %s :This server was created %s at %s",
          "RPL_MYINFO" =>
            ":%1$s 004 %2$s %1$s %3$s %4$s %5$s %6$s",
          "RPL_ISUPPORT" =>
            ":%s 005 %s %s :are supported by this server",
          "RPL_UMODEIS" =>
            ":%s 221 %s %s",
          "RPL_STATSCONN" =>
            ":%s 250 %s :Highest connection count: %s (%s clients) (%s ".
            "connections received)",
          "RPL_LUSERCLIENT" =>
            ":%s 251 %s :There are %s users and %s invisible on %s servers",
          "RPL_LUSEROP" =>
            ":%s 252 %s %s :IRC Operators online",
          "RPL_LUSERCHANNELS" =>
            ":%s 254 %s %s :channels formed",
          "RPL_LUSERME" =>
            ":%s 255 %s :I have %s clients and %s servers",
          "RPL_ADMINME" =>
            ":%1$s 256 %2$s %1$s :Administrative Info",
          "RPL_ADMINLOC1" =>
            ":%s 257 %s :%s",
          "RPL_ADMINLOC2" =>
            ":%s 258 %s :%s",
          "RPL_ADMINEMAIL" =>
            ":%s 259 %s :%s",
          "RPL_LOCALUSERS" =>
            ":%s 265 %s %s %s :Current local users %s, max %s",
          "RPL_GLOBALUSERS" =>
            ":%s 266 %s %s %s :Current global users %s, max %s",
          "RPL_ISON" =>
            ":%s 303 %s :%s",
          "RPL_WHOISUSER" =>
            ":%s 311 %s %s %s %s * :%s",
          "RPL_WHOISSERVER" =>
            ":%s 312 %s %s %s :%s",
          "RPL_WHOISOPERATOR" =>
            ":%s 313 %s %s :is an IRC Operator",
          "RPL_ENDOFWHO" =>
            ":%s 315 %s %s :End of /WHO list.",
          "RPL_WHOISIDLE" =>
            ":%s 317 %s %s %s %s :seconds idle, signon time",
          "RPL_ENDOFWHOIS" =>
            ":%s 318 %s %s :End of /WHOIS list.",
          "RPL_WHOISCHANNELS" =>
            ":%s 319 %s %s :%s",
          "RPL_LISTSTART" =>
            ":%s 321 %s Channel :Users Name",
          "RPL_LIST" =>
            ":%s 322 %s %s %s :%s",
          "RPL_LISTEND" =>
            ":%s 323 %s :End of /LIST",
          "RPL_CHANNELMODEIS" =>
            ":%s 324 %s %s %s",
          "RPL_CREATIONTIME" =>
            ":%s 329 %s %s %s",
          "RPL_NOTOPIC" =>
            ":%s 331 %s %s :No topic is set.",
          "RPL_TOPIC" =>
            ":%s 332 %s %s :%s",
          "RPL_TOPICWHOTIME" =>
            ":%s 333 %s %s %s %s",
          "RPL_INVITING" =>
            ":%s 341 %s %s %s",
          "RPL_WHOREPLY" =>
            ":%s 352 %s %s %s %s %s %s %s :%s %s",
          "RPL_NAMREPLY" =>
            ":%s 353 %s = %s :%s",
          "RPL_ENDOFNAMES" =>
            ":%s 366 %s %s :End of /NAMES list.",
          "RPL_ENDOFBANLIST" =>
            ":%s 368 %s %s :End of Channel Ban List",
          "RPL_MOTD" =>
            ":%s 372 %s :- %s",
          "RPL_MOTDSTART" =>
            ":%s 375 %s :- %s Message of the Day - ",
          "RPL_ENDOFMOTD" =>
            ":%s 376 %s :End of /MOTD command.",
          "RPL_YOUREOPER" =>
            ":%s 381 %s :You are now an IRC operator",
          // TODO: Implement this numeric
          "RPL_REHASHING" =>
            ":%s 382 %s %s :Rehashing",
          "ERR_NOSUCHNICK" =>
            ":%s 401 %s %s :No such nick/channel",
          "ERR_NOSUCHSERVER" =>
            ":%s 402 %s %s :No such server",
          "ERR_NOSUCHCHANNEL" =>
            ":%s 403 %s %s :No such channel",
          "ERR_CANNOTSENDTOCHAN" =>
            ":%s 401 %s %s :Cannot send to channel",
          "ERR_NOORIGIN" =>
            ":%s 409 %s :No origin specified",
          "ERR_NORECIPIENT" =>
            ":%s 411 %s :No recipient given (%s)",
          "ERR_NOTEXTTOSEND" =>
            ":%s 412 %s :No text to send",
          "ERR_UNKNOWNCOMMAND" =>
            ":%s 421 %s %s :Unknown command",
          "ERR_NOMOTD" =>
            ":%s 422 %s :MOTD File is missing",
          "ERR_NONICKNAMEGIVEN" =>
            ":%s 431 %s :No nickname given",
          "ERR_ERRONEUSNICKNAME" =>
            ":%s 432 %s %s :Erroneous Nickname",
          "ERR_NICKNAMEINUSE" =>
            ":%s 433 %s %s :Nickname is already in use.",
          "ERR_NOTONCHANNEL" =>
            ":%s 442 %s %s :You're not on that channel",
          "ERR_USERONCHANNEL" =>
            ":%s 443 %s %s %s :is already on channel",
          "ERR_NOTREGISTERED" =>
            ":%s 451 %s :You have not registered",
          "ERR_NEEDMOREPARAMS" =>
            ":%s 461 %s %s :Not enough parameters",
          "ERR_ALREADYREGISTERED" =>
            ":%s 462 %s :You may not reregister",
          "ERR_PASSWDMISMATCH" =>
            ":%s 464 %s :Password Incorrect",
          "ERR_INVITEONLYCHAN" =>
            ":%s 473 %s %s :Cannot join channel (+i) - you must be invited",
          "ERR_BANNEDFROMCHAN" =>
            ":%s 474 %s %s :Cannot join channel (+b) - you are banned",
          "ERR_BADCHANNAME" =>
            ":%s 479 %s %s :Illegal channel name",
          "ERR_NOPRIVILEGES" =>
            ":%s 481 %s :Permission Denied - You're not an IRC operator",
          "ERR_CHANOPRIVSNEEDED" =>
            ":%s 482 %s %s :You're not a channel operator",
          "ERR_MEMBERSNOTSECURE" =>
            ":%s 489 %s %s :all members of the channel must be connected via ".
            "SSL",
          "ERR_SECUREONLYCHAN" =>
            ":%s 490 %s %s :Cannot join channel; SSL users only (+S)",
          "ERR_NOOPERHOST" =>
            ":%s 491 %s :No appropriate operator blocks were found for your ".
            "host",
          "ERR_USERSDONTMATCH" =>
            ":%s 502 %s :Can't change mode for other users",
          "RPL_WHOISSECURE" =>
            ":%s 671 %s %s :is using a secure connection"
        );
        StorageHandling::saveFile($this, "config.json", json_encode($config,
          JSON_PRETTY_PRINT));
      }
      $this->config = $config;
    }

    public function isInstantiated() {
      $this->loadConfig();
      EventHandling::registerForEvent("rehashEvent", $this, "loadConfig");
      return true;
    }
  }
?>
