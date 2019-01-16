<?php
namespace Controller;

use Psr\Container\ContainerInterface;
use PDO;

class PayRecordController
{
    protected $db;
    public function __construct($db) 
    {
        $this->db = $db;
    }

    public function GetPayRecords($sid)
    {
        $prepare = $this->db->prepare('SELECT * FROM `paidrecords` WHERE `sid`=?');
        $prepare->execute(array($sid));
        $pay_records = $prepare->fetchAll();
        return $pay_records;
    }

    public function InsertRecord($sid, $paid, $date, $expired)
    {
        //insert pay record
        $prepare = $this->db->prepare('INSERT INTO `paidrecords` (`sid`, `paid`, `date`, `expired`) VALUES (?,?,?,?)');
        $prepare->execute(array($sid, $paid, $date, $expired));

        //update member table
        $prepare = $this->db->prepare(
            'UPDATE `member` 
            SET `totalpay`=(SELECT sum(paid) FROM `paidrecords` WHERE `sid`=?),
            `expire`=?
            WHERE `sid`=?');
        $prepare->execute(array($sid, $expired, $sid));
    }
}