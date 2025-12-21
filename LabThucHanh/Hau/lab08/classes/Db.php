<?php
class Db
{
    protected $dbh;
    protected $_numRow;

    public function __construct($host, $db, $user, $pass)
    {
        $dsn = "mysql:host={$host};dbname={$db};charset=utf8";
        try {
            $this->dbh = new PDO($dsn, $user, $pass, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
        } catch (PDOException $e) {
            echo "DB connection error: " . $e->getMessage();
            exit;
        }
    }

    public function getRowCount()
    {
        return $this->_numRow;
    }

    public function select($sql, $arr = array(), $mode = PDO::FETCH_ASSOC)
    {
        $stm = $this->dbh->prepare($sql);
        if (!$stm->execute($arr)) {
            return array();
        }
        $this->_numRow = $stm->rowCount();
        return $stm->fetchAll($mode);
    }

    public function insert($sql, $arr = array())
    {
        $stm = $this->dbh->prepare($sql);
        $stm->execute($arr);
        return $stm->rowCount();
    }

    public function update($sql, $arr = array())
    {
        $stm = $this->dbh->prepare($sql);
        $stm->execute($arr);
        return $stm->rowCount();
    }

    public function delete($sql, $arr = array())
    {
        $stm = $this->dbh->prepare($sql);
        $stm->execute($arr);
        return $stm->rowCount();
    }
}
