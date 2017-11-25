<?php
//turn on debugging messages
//ini_set('display_errors', 'On');
error_reporting(E_ERROR );
define('DATABASE', 'srk77');
define('USERNAME', 'srk77');
define('PASSWORD', 'shweta13');
define('CONNECTION', 'sql2.njit.edu');
class dbConn{
    //variable to hold connection object.
    protected static $db;
    //private construct - class cannot be instatiated externally.
    private function __construct() {
        try {
            // assign PDO object to db variable
            self::$db = new PDO( 'mysql:host=' . CONNECTION .';dbname=' . DATABASE, USERNAME, PASSWORD );
            self::$db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
        }
        catch (PDOException $e) {
            //Output error - would normally log this to error file rather than output to user.
            echo "Connection Error: " . $e->getMessage();
        }
    }
    // get connection function. Static method - accessible without instantiation
    public static function getConnection() {
        //Guarantees single instance, if no connection object exists then create one.
        if (!self::$db) {
            //new connection object.
            new dbConn();
        }
        //return connection.
        return self::$db;
    }
}
class collection {
    static public function create() {
        $model = new static::$modelName;
        return $model;
    }
    static public function findAll() {
        $db = dbConn::getConnection();
        $tableName = get_called_class();
        $sql = 'SELECT * FROM ' . $tableName;
        $statement = $db->prepare($sql);
        $statement->execute();
        $class = static::$modelName;
        $statement->setFetchMode(PDO::FETCH_CLASS, $class);
        $recordsSet =  $statement->fetchAll();
        echo " Display the entire table- " .$tableName;
        return $recordsSet;
    }
    static public function findOne($id) {
        $db = dbConn::getConnection();
        $tableName = get_called_class();
        $sql = 'SELECT * FROM ' . $tableName . ' WHERE id =' . $id;
        $statement = $db->prepare($sql);
        $statement->execute();
        $class = static::$modelName;
        $statement->setFetchMode(PDO::FETCH_CLASS, $class);
        $recordsSet =  $statement->fetchAll();
        echo "Display one record- ". $tableName;
        return $recordsSet;
    }
}

    class accounts extends collection

    {

    protected static $modelName = 'account';

    }

    class todos extends collection
    {

    protected static $modelName = 'todo';

    }

    class model {
    //protected $id;
    public function save()
        {
        if ($this->id == '') {
            $sql = $this->insert();
        } else
            {

            $sql = $this->update();
            }
        $db = dbConn::getConnection();
        $statement = $db->prepare($sql);
        $statement->execute();
        $tableName = get_called_class();
        $array = get_object_vars($this);
        $columnString = implode(',', $array);
        $valueString = ":".implode(',:', $array);
        //echo "INSERT INTO $tableName (" . $columnString . ") VALUES (" .$valueString . ")</br>";
        echo 'I just saved record: ' . $this->id;
    }


    private function insert() {
        $modelName=static::$modelName;
        $tableName = $modelName::getTablename();
        $array = get_object_vars($this);
        array_pop($array);
        $colStr = array_keys($array);
        $columnString = implode(',', $colStr);
        // $valueString = ':'.implode(',:', array_flip($array));
        $valueString = "'".implode("','", $array)."'";
        $sql =  'INSERT INTO '.$tableName.' ('.$columnString.') VALUES ('.$valueString.')';
        echo $sql;
        return $sql;
    }
    private function update() {
        $modelName=static::$modelName;
        $tableName = $modelName::getTablename();
        $array = get_object_vars($this);
        $comma = " ";
        $sql = 'UPDATE '.$tableName.' SET ';
        foreach ($array as $key=>$value){
            if( ! empty($value) && $key != "id")
            {
                $sql .= $comma . $key . ' = "'. $value .'"';
                $comma = ", ";
            }
        }
        $sql .= ' WHERE id='.$this->id;
        echo $sql;
        return $sql;
    }
    public function delete() {
        $db = dbConn::getConnection();
        $modelName=static::$modelName;
        $tableName = $modelName::getTablename();
        $sql = 'DELETE FROM '.$tableName.' WHERE id='.$this->id;
        echo $sql;
        $statement = $db->prepare($sql);
        $statement->execute();
        echo " One record deleted";
    }
}
class account extends model {
    public $id;
    public $email;
    public $fname;
    public $lname;
    public $phone;
    public $birthday;
    public $gender;
    public $password;
    protected static $modelName = 'account';
    public static function getTablename(){
        $tableName='accounts';
        return $tableName;
    }
}
class todo extends model {
    public $id;
    public $owneremail;
    public $ownerid;
    public $createddate;
    public $duedate;
    public $message;
    public $isdone;

    protected static $modelName = 'todo';
    public static function getTablename(){
        $tableName='todos';
        return $tableName;
    }
}

class htmlTable{
    public function genarateTable($record){
        $tableGen = '<table border="1" cellpadding="2" cellspacing="3">';


            foreach($record as $row => $innerArray){
                $tableGen .= '<tr>';
                foreach($innerArray as $innerRow => $value){

                    $tableGen .= '<td>' . $value.'</td>';

                }
                $tableGen.='</tr>';
            }

            $tableGen.='</table>';
            print_r($tableGen);
        }
}

$obj = new htmlTable();
$obj = new main();


    class main
        {
            public function __construct()
        {

        $records = todos::findAll();
        $tableGen = htmlTable::genarateTable($records);

        $id=4;
        $records = todos::findOne($id);
        $tableGen = htmlTable::genarateTable($records);

        // $records = accounts::findAll();
        // $tableGen = htmlTable::genarateTable($records);

        //inserting a record

        $record = new todo();
        $record->id='';
        $record->owneremail="srk77@gmail.com";
        $record->ownerid="9";
        $record->createddate="2017-11-15";
        $record->duedate="2017-12-20";
        $record->message="inseting test";
        $record->isdone="1";
        $record->save();

        $records = todos::findAll();
        $tableGen = htmlTable::genarateTable($records);

        //updating a record


        $record = new todo();
        $record->id=8;
        $record->owneremail="srk77@njit.edu";
        $record->message="update";
        $record->save();

        $records = todos::findAll();

        $tableGen = htmlTable::genarateTable($records);

        //Deleting a record

        $record= new todo();
        $record->id=7;
        $record->delete();


        $records = todos::findAll();
        $obj->genarateTable($records);
        $tableGen = htmlTable::genarateTable($records);


    }
}
?>
