<?php
namespace Controller;

use Psr\Container\ContainerInterface;
use PDO;

class UserController
{
    protected $container;
    protected $payrecords;
    private $ColoumnArray;

    // constructor receives container instance
    public function __construct(ContainerInterface $container) {
        $this->container = $container;
        $this->payrecords = new PayRecordController($this->container->get('db'));
        $this->ColoumnArray = $this->GetColoumnNameArray();
    }

    private function GetColoumnNameArray()
    {
        $db = $this->container->get('db');
        $logger = $this->container->get('logger');
        $q = $db->prepare("DESCRIBE `member`");
        $q->execute();
        $table_fields = $q->fetchAll(PDO::FETCH_COLUMN);

        $logger->info("GetColoumnNameArray");

        return $table_fields;
    }

    

    public function All($request, $response, $args) 
    {
        $db = $this->container->get('db');
        $logger = $this->container->get('logger');
        $prepared = $db->prepare(
            'SELECT member.*, paidrecords.paid, paidrecords.date, paidrecords.expired 
            FROM `member` 
            LEFT JOIN `paidrecords` 
            ON member.sid=paidrecords.sid
            ORDER BY joindate DESC'
            );
        $prepared->execute();

        $students = array ();

        $lastStudent = NULL;

        // get the first post (plus it's JOINed photo)
        while ( $student = $prepared->fetch (PDO::FETCH_OBJ) ) {

            if ( $lastStudent != $student->name ) {
                if ( $lastStudent !== NULL ) {
                    // add this obj to the post array
                    $students[] = $s;
                }
                // start a new temp object
                $s = new \stdClass();
                $s->sid = $student->sid;
                $s->department = $student->department;
                $s->name = $student->name;
                $s->phone = $student->phone;
                $s->email = $student->email;
                $s->joindate = $student->joindate;
                $s->totalpay = $student->totalpay;
                $s->expire = $student->expire;
                $s->paidrecords  = array();
            }

            $record = new \stdClass();
            $record->paid = $student->paid;
            $record->date = $student->date;
            $record->expired = $student->expired;

            // Add photo_id to current tPost obj
            $s->paidrecords[] = $record;

            $lastStudent = $student->name;
        }


        //$member_list = $prepared->fetchAll();
        $Jresponse = $response->withJson($students, 200);

        //log
        $logger->addInfo("json response");

        return $Jresponse;
    }

    public function Sid($request, $response, $args)
    {
        $db = $this->container->get('db');
        $sid = $args['sid'];
        $pay_records = $this->payrecords->GetPayRecords($sid);
        $prepared = $db->prepare('SELECT * FROM `member` WHERE `sid`= ?');
        $prepared->execute(array($sid));
        $member_list = $prepared->fetchAll();
        $Jresponse = $response->withJson(array('student' => $member_list[0], 'paidrecords' => $pay_records), 200);
        return $Jresponse;
    }

    public function Add($request, $response, $args)
    {
        $db = $this->container->get('db');
        $logger = $this->container->get('logger');
        $parsedBody = $request->getParsedBody();

        $t = $this->ColoumnArray;
        $logger->debug(print_r($parsedBody, true));
        foreach($parsedBody as $key => $value)
        {
            if(!in_array($key, $t))
            {
                unset($parsedBody[$key]);
            }
        }
        /* $col_list = join(',', $this->ColoumnArray);
        $param_list = join(',', array_map(function($col) { return ":$col"; }, $this->ColoumnArray));

        if(count($this->ColoumnArray) != count($parsedBody))
        {
            $logger->warning("testdsf");
            return $response->withJson(array('status' => 'Fail', 'message' => 'Invalid Arguments'), 400);
        } */
        $logger->debug(print_r($t, true));
        $logger->debug(print_r($parsedBody, true));
        $sql = "INSERT INTO `member` 
        (`sid`, `name`, `department`, `phone`, `email`, `joindate`, `totalpay`, `expire`)
        VALUES (:sid, :name, :department, :phone, :email, :joindate, :totalpay, :expire)";
        $prepared = $db->prepare($sql);
        $status = $prepared->execute($parsedBody);

        $this->payrecords->InsertRecord(
            $parsedBody['sid'], 
            $parsedBody['totalpay'], 
            $parsedBody['joindate'], 
            $parsedBody['expire']
        );
        
        return $response->withJson(array('message' => 'Add Member Success'), 200);
    }

    public function Update($request, $response, $args)
    {
        $sid = $args['sid'];
        $db = $this->container->get('db');
        $logger = $this->container->get('logger');

        
        $parsedBody = $request->getParsedBody();
        unset($parsedBody['sid']);
        $t = $this->ColoumnArray;
        foreach($parsedBody as $key => $value)
        {
            if(!in_array($key, $t))
            {
                unset($parsedBody[$key]);
            }
        }
        $logger->debug(print_r($t, true));
        $logger->debug(print_r($parsedBody, true));
        /*$key_array = array_keys($filted_array);
        $update_target = join(',', array_map(function($col) { 
            return "$col=:$col"; 
        }, $key_array));*/

        $sql = "UPDATE `member` 
        SET 
        `name`=:name,
        `department`=:department,
        `phone`=:phone, 
        `email`=:email,
        `joindate`=:joindate,
        `totalpay`=:totalpay,
        `expire`=:expire
        WHERE `sid`=:sid";

        $logger->info($sql);
        //$logger->info($filted_array);

        $parsedBody['sid'] = $sid;

        $prepare = $db->prepare($sql);
        try
        {
            $prepare->execute($parsedBody);
        }
        catch(PDOException $e)
        {
            die();
        }
        return $response->withJson(array('message' => 'Update Member Success'), 200);
    }
    
    public function InsertPayRecord($request, $response, $args)
    {
        $sid = $args['sid'];
        $db = $this->container->get('db');
        $logger = $this->container->get('logger');
        $parsedBody = $request->getParsedBody();
        $this->payrecords->InsertRecord(
            $sid, 
            $parsedBody['paid'],
            $parsedBody['date'],
            $parsedBody['expired']
        );

        return $response->withJson(array('message' => 'Add Paid Record Success'), 200);
    }
}