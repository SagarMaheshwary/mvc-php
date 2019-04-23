<?php

namespace App\Core\Database;

use PDOException;

/**
 * This child-class is used for manipulating database
 * tables. You may use it to make CRUD operations
 * on the database.
 */
class QueryBuilder
{
    
    /**
     * PDO connection.
     * 
     * @var PDO
     */
    private $pdo;

    /**
     * Current table to query from.
     * 
     * @var string
     */
    protected $table;

    /**
     * Primary key.
     * 
     * @var string
     */
    protected $pk = "id";

    /**
     * Parameters for prepared statements.
     * 
     * @var array|null
     */
    private $params = [];

    /**
     * Query results.
     * 
     * @var mixed
     */
    private $results;

    /**
     * Query errors.
     * 
     * @var array|[]
     */
    private $errors = [];

    /**
     * Current query statement.
     * 
     * @var PDOStatement
     */
    private $query = null;

    /**
     * Result row count.
     * 
     * @var int|0
     */
    private $rowCount = 0;

    /**
     * Magic method that's invoked when a class instance
     * is created.
     * 
     * @param PDO|null $pdo
     */
    public function __construct(PDO $pdo = null)
    {
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

        $rows = $builder->setSQL("SELECT * FROM %s")->get();

        return $rows;
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

        $row = $builder->setSQL("SELECT * FROM %s WHERE %s = :%s")
            ->setParams([ $pk => $pk ])
            ->first();
        
        return $row;
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
     * @param int $id
     * @return bool
     */
    public static function delete($id)
    {
        $builder = self::instance();

        $builder->setSQL("DELETE FROM %s WHERE %s = :%s");
        return $builder->query() ? true : false;
    }

    /**
     * Create or update a row.
     * 
     * @param array $params
     * @param int $id
     * @return bool
     */
    public function createOrUpdate(array $params = [], $id = null)
    {
        try{

            //append "id" field to the params array if
            //available.
            $id ? $params['id'] = $id : null;

            $this->setParams($params);

            if($id){
                //we need to update a row.
                $params = $this->updateParams();
                $sql = "UPDATE %s SET {$params} WHERE id = :id";
            }else{
                //we will insert a row.
                $cols = $this->insertParams()['cols'];
                $params = $this->insertParams()['params'];
                $sql = "INSERT INTO %s ( {$cols} ) VALUES ( $params )";
            }
            
            return $this->setSQL($sql)->query() ? true : false;
            
        }catch(PDOException $e){
            $this->setErrors($this->getMessage());
        }
    }

    /**
     * Fetch multiple rows.
     * 
     * @return stdClass[]|bool
     */
    public function get()
    {
        try{
            return $this->query()->fetchAll();
        }catch(PDOException $e){
            $this->setErrors($e->getMessage());
        }
    }

    /**
     * Fetch the first row from the results.
     * 
     * @return stdClass
     */
    public function first()
    {
        try{
            return $this->setSQL($this->getSQL()." LIMIT 1")->query()->fetch();
        }catch(PDOException $e){
            $this->setErrors($e->getMessage());
        }
    }

    /**
     * Query the current SQL statement.
     * 
     * @return QueryBuilder
     */
    protected function query()
    {
        try {
            $query = $this->getPDO()->prepare($this->getSQL());
            
            if(!$query->execute($this->getParams())) return false;
            $this->setQuery($query);
            return $query;

        } catch (PDOException $e) {
            $this->setErrors($e->getMessage());
        }
    }

    /**
     * create a new instance
     * 
     * @return QueryBuilder
     */
    protected static function instance()
    {
        return new static();
    }

    /**
     * dynamically set column name and placeholders
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
     * dynamically set column name and placeholders
     * for inserting a row.
     * 
     * @return array
     */
    protected function insertParams()
    {
        $params = array_keys($this->getParams());

        $cols = implode(', ',$params);
        $placeholders = ":".implode(', :',$params);

        return [
            'cols' => $cols, 'params' => $placeholders,
        ];
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
     * @return QueryBuilder
     */
    protected function setSQL($sql)
    {
        $this->sql = vsprintf($sql,[
            $this->table, $this->pk, $this->pk,
        ]);

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
     * Set current statement.
     * 
     * @param array|[] $query
     * @return void
     */
    protected function setErrors($errors = [])
    {
        array_push($this->errors,$errors);
    }

    /**
     * Get current statement.
     * 
     * @return array
     */
    public function errors()
    {
        return $this->errors ?: false;
    }

    /**
     * Set parameters for current statement.
     * 
     * @param array|[] $params
     * @return QueryBuilder
     */
    protected function setParams($params = [])
    {
        $this->params = $params;

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
     * Set current statement.
     * 
     * @param PDOStatement $query
     * @return QueryBuilder
     */
    protected function setQuery($query)
    {
        $this->query = $query;

        return $this;
    }

    /**
     * Get current statement.
     * 
     * @param array $columns
     * @return PDOStatement
     */
    protected function getQuery()
    {
        return $this->query;
    }

    /**
     * Set query results.
     * 
     * @param object $rs
     * @return QueryBuilder
     */
    protected function setResults($rs)
    {
        $this->results = $rs;

        return $this;
    }

}
