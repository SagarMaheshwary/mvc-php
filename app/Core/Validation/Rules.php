<?php

namespace App\Core\Validation;

use Exception;
use App\Core\Http\Request;
use App\Core\Support\Session;
use App\Core\Database\QueryBuilder;

class Rules
{
    /**
     * Current request object.
     * 
     * @var \App\Core\Http\Request
     */
    protected $request;

    /**
     * Message Bag for our Validation errors.
     * 
     * @var \App\Core\Validation\MessageBag
     */
    protected $messageBag;

    /**
     * Valid mime types (images)
     * 
     * @var array
     */
    protected $mimeTypes = [
        'png'  => 'image/png',
        'jpeg' => 'image/jpeg',
        'jpg'  => 'image/jpeg',
        'gif'  => 'image/gif',
        'bmp'  => 'image/bmp',
        'ico'  => 'image/vnd.microsoft.icon',
        'tiff' => 'image/tiff',
        'tif'  => 'image/tiff',
        'svg'  => 'image/svg+xml',
    ];

    /**
     * Get the current Request and MessageBag object.
     * 
     * @return void
     */
    public function __construct(Request $request, MessageBag $messageBag)
    {
        $this->setRequest($request);
        $this->setMessageBag($messageBag);
    }

    /**
     * Check if an input exists.
     * 
     * @param string $field
     * @return bool
     */
    public function validateOptional($field)
    {
        return !$this->request->has($field) ? true : false;
    }

    /**
     * Check if an input/file exists.
     * 
     * @param string $field
     * @return void
     */
    public function validateRequired($field)
    {
        if(!$this->getRequest()->has($field)){
            $this->error($field,'is required!');
        }
    }

    /**
     * Check if the input value is integer.
     * 
     * @param string $field
     * @return void
     */
    public function validateInteger($field)
    {
        if(!is_int($this->value($field))){
            $this->error($field,'should be an integer!');
        }
    }

    /**
     * Check if the input value is integer.
     * 
     * @param string $field
     * @return void
     */
    public function validateString($field)
    {
        if(!is_string($this->value($field))){
            $this->error($field,'should be a string!');
        }
    }

    /**
     * Check if the input value is a valid email.
     * 
     * @param string $field
     * @return void
     */
    protected function validateEmail($field)
    {
        if(!filter_var($this->value($field),FILTER_VALIDATE_EMAIL)){
            $this->error($field,'should be a valid email!');
        }
    }

    /**
     * Check if the input value contains alphanumerical
     * characters.
     * 
     * @param string $field
     * @return void
     */
    public function validateAlphaNumeric($field)
    {
        if(!ctype_alnum($this->value($field))){
            $this->error($field,'should be alpha numerical!');
        }
    }

    /**
     * Check if the input value contains numeric
     * characters.
     * 
     * @param string $field
     * @return void
     */
    public function validateNumeric($field)
    {
        if(!is_numeric($this->value($field))){
            $this->error($field,'should be numeric!');
        }
    }

    /**
     * Check if the input value equals to or
     * is less than the maximum length specified.
     * 
     * @param string $field
     * @param int $length
     * @return void
     */
    public function validateFile($field)
    {
        if(!$this->getRequest()->hasFile($field)){
            $this->error($field,'should be a file!');
        }
    }

    /**
     * Check if the input value equals to or
     * is greater than the minimum length specified.
     * 
     * @param string $field
     * @param int $length
     * @return void
     */
    public function validateMin($field, $length)
    {
        $value = $this->value($field);

        if(is_string($value)){
            if(strlen($value) < $length){
                $this->error($field,"should contain characters more than or equals to {$length}!");
            }
        }elseif (is_int($value)) {
            if(($val) < $length){
                $this->error($field,"should be greater than or equals to {$length}!");
            }
        }
    }

    /**
     * Check if the input value equals to or
     * is less than the maximum length specified.
     * 
     * @param string $field
     * @param int $length
     * @return void
     */
    public function validateMax($field, $length)
    {
        $value = $this->value($field);

        if(is_string($value)){
            if(strlen($value) > $length){
                $this->error($field,"should contain characters less than or equals to {$length}!");
            }
        }elseif (is_int($value)) {
            if(($value) > $length){
                $this->error($field,"should be less than or equals to {$length}!");
            }
        }
    }
    
    /**
     * Check if the input value is unique to the
     * database column.
     * 
     * @param string $field
     * @param string $table
     * @param string $column
     * @param int|null $id
     * @param string|"id" $pk
     * @return void
     */
    public function validateUnique($field, $table, $column, $id = null, $pk = "id")
    {
        //check with just column.
        $query = QueryBuilder::table($table)
            ->select()
            ->where(
                $column, '=', $this->value($field)
            );
        
        //add the primary key value to ignore the specific row.
        //Note: This will help in updating a specific row.
        $rs = $id ? $query->whereAnd($pk,'<>',$id) : $query;

        if($rs->first()){

            //if we get a row from the db then that means
            //the value not unique.
            $this->error($field,"already exists!");

        }
    }

    /**
     * Check if the uploaded file is an image.
     * 
     * @param string $field
     * @return void
     */
    public function validateImage($field)
    {
        $this->checkMime($field,"is not an image!");
    }

    /**
     * Check if the uploaded file matches the mime
     * types.
     * 
     * @param string $field
     * @param string|array $mimes
     * @return void
     */
    public function validateMimeType($field, $mimes)
    {
        //make it an array if it's a one value string.
        $mimes = (array)($mimes);

        $this->checkMime(
            $field, "should be a valid: ".implode(', ',$mimes).".", $mimes
        );
    }

    /**
     * Check mime type of a given file.
     * 
     * @param string $field
     * @param string $msg
     * @param array|[] $mimes
     * @return void
     */
    protected function checkMime($field,$msg,$mimes = [])
    {
        if(!$this->getRequest()->hasFile($field)) return;
        
        $file = $this->getRequest()->file($field);

        if($mimes && 
        !$mimes = array_intersect_key($this->mimeTypes,array_flip($mimes))) {
            throw new Exception("Invalid mime in the validation rules!");
        }

        $validMimes = $mimes ?: array_values($this->mimeTypes);

        $mime = mime_content_type($file['tmp_name']);

        if(!in_array($mime,$validMimes)){
            $this->error($field,$msg);
        }
    }

    /**
     * Get the input field value.
     * 
     * @return mixed
     */
    protected function value($field)
    {
        return $this->getRequest()->input($field);
    }

    /**
     * Set an error message to the MessageBag.
     * 
     * @param string $field
     * @param string $msg
     * @return void
     */
    protected function error($field, $msg)
    {
        $displayName = str_replace(['-','_'],' ',$field);

        $this->getMessageBag()->setMessage($field, ucwords($displayName).' '.$msg);
    }

    /**
     * Set current request object.
     * 
     * @param \App\Core\Http\Request $request
     * @return void
     */
    protected function setRequest(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Get current request object.
     * 
     * @return \App\Core\Http\Request
     */
    protected function getRequest()
    {
        return $this->request;
    }

    /**
     * Set messageBag object.
     * 
     * @param \App\Core\Validation\MessageBag $messageBag
     * @return void
     */
    protected function setMessageBag(MessageBag $messageBag)
    {
        $this->messageBag = $messageBag;
    }

    /**
     * Get messageBag object.
     * 
     * @param \App\Core\Validation\MessageBag $messageBag
     * @return void
     */
    public function getMessageBag()
    {
        return $this->messageBag;
    }

}