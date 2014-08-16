<?php
  class @@CLASSNAME@@ {
    public $depend = array("WHOISResponseEvent");
    public $name = "ServerInfoWHOISResponse";

    public function receiveWHOISResponse($name, $data) {
      $source = $data[0];
      $target = $data[1];
      $response = $data[2];

      $weight = "67";
      if (!isset($response[$weight])) {
        $response[$weight] = array();
      }
      $response[$weight][] = ":".__SERVERDOMAIN__." 312 ".
        $source->getOption("nick")." ".$target->getOption("nick")." ".
        __SERVERDOMAIN__." :".__SERVERDESCRIPTION__;
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
