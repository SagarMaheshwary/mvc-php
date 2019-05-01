<?php

namespace App\Core\Database;

use Exception;
use PDOException;

class QueryBuilder
{
    /*
    |--------------------------------------------------------------------------
    | QueryBuilder
    |--------------------------------------------------------------------------
    | 
    | This class will be responsible for query data from the database. With this
    | class, you can do CRUD as well as chain where clause(s).
    |
    */
    
    /**
     * PDO connection.
     * 
     * @var PDO
     */
    private $pdo = null;

    /**
     * Current table to query from.
     * 
     * @var string
     */
    protected $table;

    /**
     * Primary key.
     * 
     * @var string|"id"
     */
    protected $pk = "id";

    /**
     * Parameters for prepared statements.
     * 
     * @var array|[]
     */
    private $params = [];

    /**
     * Current SQL statement.
     * 
     * @var string|''
     */
    private $sql = '';

    /**
     * Where clause(s).
     * 
     * @var string|''
     */
    private $where = '';

    /**
     * Operators for where clause.
     * 
     * @var array
     */
    private $operators = ['=', '>', '<', '>=', '<=','LIKE','<>'];

    /**
     * Magic method that's invoked when a class instance
     * is created.
     * 
     * @param PDO|null $pdo
     */
    public function __construct(PDO $pdo = null)
    {
        //if we have a different db connection.
        $conn = $pdo ?: Connection::make();

        $this->setPDO($conn);
    }

    /**
     * Retrieve all the rows from the specified
     * table.
     * 
     * @return stdClass[]
     */
    public static function all()
    {
        $builder = self::instance();

        return $builder->selectColumns()->get();
    }

    /**
     * Only select specified columns.
     * 
     * @param array|[] $columns
     */
    public static function select(array $columns = [])
    {
        $builder = self::instance();

        return $builder->selectColumns($columns);
    }

    /**
     * Retrieve a single row by "primary key"
     * from the specified table.
     * 
     * @param int $pk
     * @return stdClass
     */
    public static function find(int $pk)
    {
        $builder = self::instance();

        return $builder->selectColumns()
            ->where($builder->pk, "=", $pk)
            ->setParams([ $builder->pk => $pk ])
            ->first();
    }

    /**
     * Create a row.
     * 
     * @param array $params
     * @return bool
     */
    public static function create(array $params)
    {
        $builder = self::instance();

        return $builder->createOrUpdate($params);
    }

    /**
     * Update a row.
     * 
     * @param array $params
     * @param int $pk
     * @return bool
     */
    public static function update(array $params,int $pk)
    {
        $builder = self::instance();

        return $builder->createOrUpdate($params,$pk);
    }

    /**
     * Delete a row.
     * 
     * @param int $pk
     * @return bool
     */
    public static function delete(int $pk)
    {
        $builder = self::instance();
        
        return $builder->setSQL("DELETE FROM {$builder->table}")
            ->where($builder->pk, "=", $pk)
            ->setParams([ $builder->pk => $pk ])
            ->query() ? true : false;
    }

    /**
     * Fetch multiple rows.
     * 
     * @return stdClass[]|false
     * @throws Exception
     */
    public function get()
    {
        try{
            return $this->query()->fetchAll();
        }catch(PDOException $e){
            throw $e;
        }
    }

    /**
     * Fetch the first row.
     * 
     * @return stdClass|false
     * @throws Exception
     */
    public function first()
    {
        try{
            return $this->appendSQL("LIMIT 1")->query()->fetch();
        }catch(PDOException $e){
            throw $e;
        }
    }

    /**
     * Add where clause to an SQL select.
     * 
     * @param string $col
     * @param string $op
     * @param string $val
     * @return App\Core\Database\QueryBuilder
     */
    public function where($col,$op,$val)
    {
        $this->checkOperator($op);
        
        $this->makeWhere($col,$op,$val);
        return $this;
    }

    /**
     * Append where "OR" to an SQL statement with where
     * clause.
     * 
     * @param string $col
     * @param string $op
     * @param string $val
     * @return App\Core\Database\QueryBuilder
     */
    public function whereOr($col,$op,$val)
    {
        $this->checkOperator($op);
    
        $this->makeWhere($col,$op,$val,'OR');
        return $this;
    }

    /**
     * Append where "AND" to an SQL statement with where
     * clause.
     * 
     * @param string $col
     * @param string $op
     * @param string $val
     * @return App\Core\Database\QueryBuilder
     */
    public function whereAnd($col,$op,$val)
    {
        $this->checkOperator($op);
    
        $this->makeWhere($col,$op,$val,'AND');
        return $this;
    }

    /**
     * Add where "LIKE" to an SQL select.
     * 
     * @param string $col
     * @param string $val
     * @return App\Core\Database\QueryBuilder
     */
    public function whereLike($col,$val)
    {
        $this->makeWhere($col,'LIKE',$val);
        return $this;
    }

    /**
     * Add where "BETWEEN" to an SQL select.
     * 
     * @param string $col
     * @param string $op
     * @param array $val
     * @return App\Core\Database\QueryBuilder
     */
    public function whereBetween($col,array $val)
    {
        $this->makeWhere($col,'',$val,'BETWEEN');
        return $this;
    }

    /**
     * Create or update a row.
     * 
     * @param array $params
     * @param int|null $pk
     * @return bool
     * @throws Exception
     */
    protected function createOrUpdate(array $params, $pk = null)
    {
        try{

            //append "pk" field to the params array if available.
            $pk ? $params[$this->pk] = $pk : null;
            
            $this->setParams($params);

            if($pk){
                
                $params = $this->updateParams();
                
                $sql = "UPDATE {$this->table} SET {$params} WHERE {$this->pk} = :{$this->pk}";

            }else{

                $cols = $this->getColumnNames(array_keys($params));
                
                $placeholders = $this->insertParams();

                $sql = "INSERT INTO {$this->table} ( {$cols} ) VALUES ( {$placeholders} )";
            }

            return $this->setSQL($sql)->query() ? true : false;
            
        }catch(PDOException $e){
            throw $e;           
        }
    }

    /**
     * Query the current SQL statement.
     * 
     * @return PDOStatement|false
     * @throws Exception
     */
    protected function query()
    {
        try {
            $query = $this->getPDO()->prepare($this->getSQL());
            return $query->execute($this->getParams()) ? $query : false;

        } catch (PDOException $e) {
            throw $e;
        }
    }

    /**
     * Select all or specific columns.
     * 
     * @param array|[] $columns
     * @return App\Core\Database\QueryBuilder
     */
    protected function selectColumns($columns = [])
    {
        $cols = $columns ? $this->getColumnNames($columns) : "*";
        $this->setSQL("SELECT {$cols} FROM {$this->table}");
        return $this;
    }

    /**
     * Make a where clause for SQL statement.
     * 
     * @param string $col
     * @param string $op
     * @param string|array $val
     * @param string|'' $whereOp
     * @return void
     */
    protected function makeWhere($col,$op,$val,$whereOp = '')
    {
        $append =  "{$col} {$op} :{$col}";

        switch ($whereOp) {
            case "BETWEEN":
                $append = ''; //set it to null
                $prepend = "WHERE {$col} BETWEEN :val1 AND :val2";
            break;
            case "OR":
                $prepend = "OR ";
            break;
            case "AND":
                $prepend = "AND ";
            break;
            default:
                $prepend = "WHERE ";
            break;
        }
        
        if($this->getWhere() != '' && $whereOp == "BETWEEN"){
            $this->appendWhere($prepend.$append);
        }else{
            $this->setWhere($prepend.$append);
        }

        //if the $val is an array then that means
        //it's params are for between statement.
        $params = is_array($val) 
        ? [':val1' => $val[0], ':val2' => $val[1]] 
        : [$col => $val];

        $this->setParams($params)->appendSQL($this->getWhere());
    }

    /**
     * create a new instance
     * 
     * @return App\Core\Database\QueryBuilder
     */
    protected static function instance()
    {
        return new static();
    }

    /**
     * Set column name and placeholders from params
     * for updating a row.
     * 
     * @return string
     */
    protected function updateParams()
    {
        $params = $this->getParams();
        
        $placeholders = '';
        $x = 1;

        foreach ($params as $col => $value) {

            $placeholders .= "{$col} = :{$col}";
            
            if(count($params) > $x) $placeholders .= ", ";
            
            $x++;

        }

        return $placeholders;
    }

    /**
     * Set column names to a string from an array.
     * 
     * @param array $columns
     * @return string
     */
    protected function getColumnNames($columns)
    {
        return implode(', ',$columns);
    }

    /**
     * Set column name and placeholders from params
     * for inserting a row.
     * 
     * @return array
     */
    protected function insertParams()
    {
        return ":".implode(', :',array_keys($this->getParams()));
    }

    /**
     * Set current PDO connection.
     * 
     * @param PDO|null $pdo
     * @return void
     */
    protected function setPDO($pdo = null)
    {
        $this->pdo = $pdo;
    }

    /**
     * Get current PDO connection.
     * 
     * @return PDO
     */
    protected function getPDO()
    {
        return $this->pdo ?: Connection::make();
    }

    /**
     * Set the current SQL statement.
     * 
     * @param string $sql
     * @return App\Core\Database\QueryBuilder
     */
    protected function setSQL($sql)
    {
        $this->sql = $sql;

        return $this;
    }

    /**
     * Append to the current SQL statement.
     * 
     * @param string $sql
     * @return App\Core\Database\QueryBuilder
     */
    protected function appendSQL($sql)
    {
        $this->sql .= " {$sql}";

        return $this;
    }

    /**
     * Get the current SQL statement.
     * 
     * @return string
     */
    protected function getSQL()
    {
        return $this->sql;
    }

    /**
     * Set parameters for current statement.
     * 
     * @param array|[] $params
     * @return App\Core\Database\QueryBuilder
     */
    protected function setParams($params = [])
    {
        $this->params = array_merge($this->params,$params);

        return $this;
    }

    /**
     * Get parameters for current statement.
     * 
     * @return array
     */
    protected function getParams()
    {
        return $this->params;
    }

    /**
     * Set where clause.
     * 
     * @param string $sql
     * @return App\Core\Database\QueryBuilder
     */
    protected function setWhere($sql)
    {
        $this->where = " {$sql}";

        return $this;
    }

    /**
     * Append to where string.
     * 
     * @param string $sql
     * @return App\Core\Database\QueryBuilder
     */
    protected function appendWhere($sql)
    {
        $this->where .= " {$sql}";

        return $this;
    }

    /**
     * Get where statement.
     * 
     * @return string
     */
    protected function getWhere()
    {
        return $this->where ?: '';
    }

    /**
     * Get available operators for where clause.
     * 
     * @return array
     */
    protected function getOperators()
    {
        return $this->operators;
    }

    /**
     * Check if the operator exists.
     * 
     * @param string $op
     * @return void
     * @throws Exception
     */
    protected function checkOperator($op){
        if(!in_array($op,$this->getOperators())){
            throw new Exception("Invalid Operator!");
        }
    }

}
