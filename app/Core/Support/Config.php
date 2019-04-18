<?php

namespace App\Core\Support;

/**
 * Config values from config directory.
 */
class Config
{
    /**
     * Get a value
     * 
     * @param string $key
     * @return mixed
     */
    public function get($key)
    {
        $config = App::get('config');
        $keys = explode('.',$key);
        foreach($keys as $key){
            if(isset($config[$key])){
                $config = $config[$key];
            }else{
                return false;
            }
        }
        return $config;
    }
}
