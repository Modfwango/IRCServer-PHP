<?php
  class @@CLASSNAME@@ {
    public $depend = array("WHOISResponseEvent");
    public $name = "UserInfoWHOISResponse";

    public function receiveWHOISResponse($name, $id, $data) {
      $source = $data[0];
      $target = $data[1];
      $response = $data[2];

      $weight = 100;
      if (!isset($response[$weight])) {
        $response[$weight] = array();
      }
      $response[$weight][] = ":".__SERVERDOMAIN__." 311 ".
        $source->getOption("nick")." ".$target->getOption("nick")." ".
        $target->getOption("ident")." ".$target->getHost()." * :".
        $target->getOption("realname");
      $data[2] = $response;
      return array(null, $data);
    }

    public function isInstantiated() {
      EventHandling::registerAsEventPreprocessor("WHOISResponseEvent", $this,
        "receiveWHOISResponse");
      return true;
    }
  }
?>
