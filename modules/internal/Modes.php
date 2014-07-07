<?php
  class @@CLASSNAME@@ {
    public $name = "Modes";
    private $modes = array("byname" => array(),
      "bychar" => array(0 => array(), 1 => array()));
    private $prefixes = array("byprefix" => array(), "bychar" => array());

    public function getCharByPrefix($prefix) {
      if (isset($this->prefixes["byprefix"][$prefix])) {
        return $this->prefixes["byprefix"][$prefix];
      }
      return false;
    }

    public function getModeByName($name) {
      if (isset($this->modes["byname"][$name])) {
        return $this->modes["byname"][$name];
      }
      return false;
    }

    public function getModeByChar($target, $char) {
      // Retrieve the requested mode if it exists, otherwise return false.
      return $this->getModeByName($this->getModeNameByChar($target, $char));
    }

    public function getModeNameByChar($target, $char) {
      // Retrieve the requested name if it exists, otherwise return false.
      return (isset($this->modes["bychar"][$target][$char]) ?
        $this->modes["bychar"][$target][$char] : false);
    }

    public function getPrefixes() {
      $return = array();
      foreach ($this->prefixes["byprefix"] as $prefix => $char) {
        $return[] = array($prefix, $char);
      }
      return $return;
    }

    public function getPrefixByChar($char) {
      if (isset($this->prefixes["bychar"][$char])) {
        return $this->prefixes["bychar"][$char];
      }
      return false;
    }

    public function setMode($mode) {
      $this->unsetMode($mode);
      // [name, char, [0 (channel), 1 (user)], type]
      // Types:
      // 0 - Never requires a parameter.
      // 1 - Parameter required when set/unset.
      // 2 - Parameter required when set.
      // 3 - List type mode.
      // 4 - Status type mode.
      // 5 - Key.
      $this->modes["byname"][$mode[0]] = array($mode[0], $mode[1], $mode[2],
        $mode[3]);
      $this->modes["bychar"][$mode[2]][$mode[1]] = $mode[0];
    }

    public function setPrefix($prefix) {
      $this->unsetPrefix($prefix);
      $this->prefixes["byprefix"][$prefix[0]] = $prefix[1];
      $this->prefixes["bychar"][$prefix[1]] = $prefix[0];
    }

    public function unsetMode($mode) {
      foreach ($this->modes["byname"] as $name => $val) {
        if ($name == $mode[0]) {
          unset($this->modes["byname"][$name]);
        }
      }
      foreach ($this->modes["bychar"][0] as $key => $name) {
        if ($name == $mode[0]) {
          unset($this->modes["bychar"][0][$key]);
        }
      }
      foreach ($this->modes["bychar"][1] as $key => $name) {
        if ($name == $mode[0]) {
          unset($this->modes["bychar"][1][$key]);
        }
      }
    }

    public function unsetPrefix($prefix) {
      foreach ($this->prefixes["byprefix"] as $prefix => $char) {
        if ($prefix == $prefix[0]) {
          unset($this->prefixes["byprefix"][$prefix]);
          if (isset($this->prefixes["bychar"][$char])) {
            unset($this->prefixes["bychar"][$char]);
          }
        }
      }
      foreach ($this->prefixes["bychar"] as $char => $prefix) {
        if ($char == $prefix[1]) {
          unset($this->prefixes["bychar"][$char]);
          if (isset($this->prefixes["byprefix"][$prefix])) {
            unset($this->prefixes["byprefix"][$prefix]);
          }
        }
      }
    }

    public function isInstantiated() {
      return true;
    }
  }
?>
