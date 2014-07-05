<?php
  class @@CLASSNAME@@ {
    public $name = "Channel";
    private $options = array();

    public function getOption($key) {
      // Retrieve the requested option if it exists, otherwise return false.
      return (isset($this->options[$key]) ? $this->options[$key] : false);
    }

    public function setOption($key, $value) {
      // Set an option for this connection.
      $this->options[$key] = $value;
      return true;
    }

    public function isInstantiated() {
      return true;
    }
  }
?>
