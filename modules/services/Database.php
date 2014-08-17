<?php
  class @@CLASSNAME@@ {
    public $name = "Database";
    private $db = null;

    public function addRow($db, $table, $row) {
      if (is_array($row) && $this->tableExists($db, $table)) {
        $this->db[$db][$table][] = $row;
        $this->flush();
        return true;
      }
      return false;
    }

    public function createDatabase($db) {
      if (!isset($this->db[$db])) {
        $this->db[$db] = array();
        $this->flush();
        return true;
      }
      return false;
    }

    public function createTable($db, $table) {
      if ($this->databaseExists($db) && !$this->tableExists($db, $table)) {
        $this->db[$db][$table] = array();
        $this->flush();
        return true;
      }
      return false;
    }

    public function databaseExists($db) {
      if (isset($this->db[$db])) {
        return true;
      }
      return false;
    }

    public function delRows($db, $table, $by, $value, $ci = false) {
      if ($this->tableExists($db, $table)) {
        foreach ($this->db[$db][$table] as $key => $row) {
          if (isset($row[$by]) && (($ci == false && $row[$by] === $value)
              || ($ci == true && strtolower($row[$by]) ===
              strtolower($value)))) {
            unset($this->db[$db][$table][$key]);
          }
        }
        return true;
      }
      return false;
    }

    public function getRows($db, $table, $by, $value, $ci = false) {
      if ($this->tableExists($db, $table)) {
        $ret = array();
        foreach ($this->db[$db][$table] as $row) {
          if (isset($row[$by]) && (($ci == false && $row[$by] === $value)
              || ($ci == true && strtolower($row[$by]) ===
              strtolower($value)))) {
            $ret[] = $row;
          }
        }
        return $ret;
      }
      return false;
    }

    public function getAllRows($db, $table) {
      if (isset($this->db[$db][$table])) {
        return $this->db[$db][$table];
      }
      return false;
    }

    public function setAllRows($db, $table, $rows) {
      if (is_array($rows) && $this->tableExists($db, $table)) {
        foreach ($rows as $row) {
          if (!is_array($row)) {
            return false;
          }
        }
        $this->db[$db][$table] = $rows;
        $this->flush();
        return true;
      }
      return false;
    }

    public function tableExists($db, $table) {
      if (isset($this->db[$db][$table])) {
        return true;
      }
      return false;
    }

    private function flush() {
      if (StorageHandling::saveFile($this, "database.txt",
          serialize($this->db))) {
        return true;
      }
      return false;
    }

    public function isUnloadable() {
      return false;
    }

    public function isInstantiated() {
      $db = StorageHandling::loadFile($this, "database.txt");
      $this->db = @unserialize($db);
      if ($db == false || !is_array($this->db)) {
        $db = serialize(array());
        StorageHandling::saveFile($this, "database.txt", $db);
        $this->db = @unserialize($db);
      }
      return true;
    }
  }
?>
