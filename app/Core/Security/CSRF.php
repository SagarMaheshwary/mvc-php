<?php

namespace App\Core\Security;

use App\Core\Support\{Config,Session};

/**
 * CSRF Protection.
 */
class CSRF
{
    /**
     * Generate a csrf token.
     * 
     * @return string
     */
    public static function generate()
    {
        echo Config::get('session.csrf_token');
        $key = '';
        $token = Hash::unique();
        Session::set($key,$token);
        return $token;
    }

    /**
     * Match the csrf token and delete the
     * token cookie if exists.
     * 
     * @return string $token
     * @return string
     */
    public static function match($token)
    {
        $key = Config::get('session.csrf_token');
        $csrf = Session::get($key);
        if($csrf && $token == $csrf){
            Session::unset($key);
            return true;
        }
        return false;
    }

    /**
     * Create a hidden csrf input field.
     * 
     * @return string
     */
    public static function csrfField()
    {
        $token = self::generate();
        return "<input type=\"hidden\" name=\"csrf_token\" value=\"{$token}\">";
    }

}
