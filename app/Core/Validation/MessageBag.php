<?php

namespace App\Core\Validation;

use App\Core\Support\Session;

class MessageBag
{
    /**
     * All messages or errors. We will use it
     * specifically for Validation but it can
     * be used for others stuff.
     * 
     * @var array|[]
     */
    protected $messages = [];

    /**
     * Session object for storing messages into
     * the session for next request.
     * 
     * @var \App\Core\Support\Session
     */
    protected $session;

    /**
     * 
     */
    public function __construct(Session $session)
    {
       $this->setSession($session);
    }

    /**
     * Set a message/error to an array on messages.
     * 
     * @param string $key
     * @param string $message
     * @return void
     */
    public function setMessage($key,$message)
    {
        $this->messages[$key][] = $message;
    }

    /**
     * Check if we have any messages/errors in
     * the main messages array.
     * 
     * @return bool
     */
    public function hasMessages()
    {
        return $this->messages ? true : false;
    }

    /**
     * Retreive all the messages/errors.
     * 
     * @return array
     */
    public function all()
    {
        return $this->messages;
    }

    /**
     * Check if we have a certain messages array
     * inside the main array.
     * 
     * @param string $key
     * @return bool
     */
    public function has($key)
    {
        return isset($this->messages[$key]);
    }

    /**
     * Retrieve the first value from sub-messages array.
     * 
     * @param string $key
     * @return string|''
     */
    public function first($key)
    {
        return $this->has($key) ? $this->messages[$key][0] : '';
    }
    
    /**
     * Retrieve all messages from sub-messages array.
     * 
     * @param string $key
     * @return array|''
     */
    public function messages($key)
    {
        return $this->has($key) ? $this->messages[$key] : '';
    }

    /**
     * Set messages/errors to the main messges
     * array.
     * 
     * @param string $key
     * @param string $message
     * @return void
     */
    public function setMessages($messages)
    {
        $this->messages = $messages;
    }

    /**
     * Store all the messages to the session.
     * 
     * @return void
     */
    public function store()
    {
        $this->session->set('errors',$this->messages);
    }

    /**
     * Remove all the messages from the session.
     * 
     * @return void
     */
    public function destroy()
    {
        $this->session->unset('errors');
    }

    /**
     * Set Session.
     * 
     * @param \App\Core\Support\Session $session
     * @return void
     */
    protected function setSession(Session $session)
    {
        $this->session = $session;
    }

    /**
     * Get Session.
     * 
     * @return \App\Core\Support\Session
     */
    protected function getSession()
    {
        return $this->session;
    }
}