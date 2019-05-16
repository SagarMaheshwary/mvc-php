<?php

namespace App\Core\Validation;

use Exception;
use App\Core\Support\Session;
use App\Core\Http\{Request,Response};

class Validator
{

    /**
     * Current request object.
     * 
     * @var \App\Core\Http\Request
     */
    protected $request;

    /**
     * All rules for validator.
     * 
     * @var \App\Core\Validation\Rules
     */
    protected $rules;

    /**
     * Check if we have an optional rule.
     * 
     * @var bool|false
     */
    protected $isOptionalField = false;

    /**
     * Available validation rules.
     * 
     * @var array
     */
    private $validRules = [
        'required', //Field is required.
        'optional', //Field is optional or nullable.
        'email', //Field should be a valid email.
        'string', //Field should be a string value.
        'integer', //Field should be an integer value.
        'file', //Field should an uploaded file.
        'alpha_numeric', //Field can have alphabets and digits.
        'numeric', //Field should be a numeric value.

        /**
         * Should be an image.
         * Available image types: png,svg,bmp,jpeg,
         * jpg,gif,tif,tiff,ico.
         */
        'image',

    ];

    /**
     * Available parametered validation rules.
     * 
     * @var array
     */
    private $validParamRules = [
        
        /**
         * Unique value to a database column value.
         * Rule syntax: unique:table,column,id,
         * primary-key-column.
         * pass the id (primary key) value if you
         * are updating a row.
         */
        'unique',

        /**
         * Minimum limit of characters/integers.
         * Rule syntax: min:length
         */
        'min',

        /**
         * Maximum limit of characters/integers.
         * Rule syntax: max:length
         */
        'max',

        /**
         * Check the mime type.
         * Rule syntax: mime:type1,type2...
         */
        'mime',

    ];

    /**
     * Validate the current request data.
     * 
     * @param App\Core\Http\Request $request
     * @param array $rules
     * @return void
     */
    public function validate(Request $request, $rules)
    {
        $this->setRequest($request);

        //set the rules object where all the rule methods are.
        $this->setRules(
            new Rules($request, new MessageBag(new Session))
        );
        
        //start validation.
        foreach($rules as $field => $fieldRules){
            $this->validateFields($field,explode('|',$fieldRules));
        }

        if($this->errors()){

            $response = new Response();
                
            //if we have at least one failed error then we
            //will store the messageBag errors to the session.
            $this->getRules()->getMessageBag()->store();

            //store the post request input values in the session.
            Session::setOldInput();

            //redirecting the user back to the previous url.
            $response->redirect($request->previousUrl());

            //stop the execution of any other code after the
            //validation.
            exit();

        }
    }

    /**
     * Start validating each field.
     * 
     * @param string $field
     * @param array $rules
     * @return void
     */
    protected function validateFields($field,$rules)
    {

        foreach($rules as $rule){

            //if we have an optioned rule then we will
            //break the loop.
            if($this->isOptionalField()){

                //we want to set it to false so it doesn't
                //break every other field/input.
                $this->setIsOptionalField(false);

                break;

            }
            
            if(!preg_match('(:)',$rule)){

                //check if the rule exists.
                if(!in_array($rule,$this->getValidRules())){
                    throw new Exception("Invalid Rule \"{$rule}\"");
                }

                //validate the current rule.
                $this->validateRule($field,$rule);

            }else{
                
                //split the rule from ":" character.
                $paramRule = explode(':',$rule);
                
                //check if the rule exists.
                if(!in_array($paramRule[0],$this->getValidParamRules())){
                    throw new Exception("Invalid Rule \"{$paramRule[0]}\"");
                }

                //validate the current parametered rule.
                $this->validateParamRule($field,$paramRule);

            }

        }

    }

    /**
     * This method with do the actual validation
     * for each field/input.
     * 
     * @param string $field
     * @param string $rule
     * @return void
     */
    protected function validateRule($field,$rule)
    {   
        $rules = $this->getRules();

        switch ($rule) {
            case 'optional':
                $this->setIsOptionalField(
                    $rules->validateOptional($field)
                );
            break;

            case 'required':
                $rules->validateRequired($field);
            break;

            case 'string':
                $rules->validateString($field);
            break;

            case 'integer':
                $rules->validateInteger($field);
            break;

            case 'email':
                $rules->validateEmail($field);
            break;

            case 'alpha_numeric':
                $rules->validateAlphaNumeric($field);
            break;

            case 'numeric':
                $rules->validateNumeric($field);
            break;

            case 'file':
                $rules->validateFile($field);
            break;

            case 'image':
                $rules->validateImage($field);
            break;
        }
    }

    /**
     * This method with do the actual validation
     * from each parametered rule.
     * 
     * @param string $field
     * @param string $rule
     * @return void
     */
    protected function validateParamRule($field,$rule)
    {
        $rules = $this->getRules();

        switch($rule[0]){
            case 'min':
                $rules->validateMin(
                    $field,$rule[1]
                );
            break;
            
            case 'max':
                $rules->validateMax(
                    $field,$rule[1]
                );
            break;

            case 'unique':
                $rules->validateUnique(
                    $field,...explode(',',$rule[1])
                );
            break;

            case 'mime':
                $rules->validateMimeTYpe(
                    $field, explode(',',$rule[1])
                );
            break;
        }
    }

    /**
     * Get isOptionalField.
     * 
     * @return bool
     */
    protected function isOptionalField()
    {
        return $this->isOptionalField;
    }

    /**
     * Get isOptionalField.
     * 
     * @param bool $bool
     * @return void
     */
    protected function setIsOptionalField($bool)
    {
        $this->isOptionalField = $bool;
    }

    /**
     * Set rules object.
     * 
     * @param \App\Core\Validation\Rules $rules
     * @return void
     */
    protected function setRules($rules)
    {
        $this->rules = $rules;
    }

    /**
     * Get rules object.
     * 
     * @return \App\Core\Validation\Rules
     */
    protected function getRules()
    {
        return $this->rules;
    }

    /**
     * Get all the available rules from the
     * validator.
     */
    protected function getValidRules()
    {
        return $this->validRules;
    }

    /**
     * Get all the available parametered rules from
     * the validator.
     */
    protected function getValidParamRules()
    {
        return $this->validParamRules;
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
     * Get all the validation errors.
     * 
     * @return array
     */
    public function errors()
    {
        return $this->getRules()->getMessageBag()->all();
    }

}
