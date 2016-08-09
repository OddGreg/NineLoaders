<?php

/**
 * @package Nine Loader
 * @version 0.5.0
 * @author  Greg Truesdell <odd.greg@gmail.com>
 */
class SymbolForGet
{
    /**
     * @var string
     */
    public $message;

    public function __construct($message = 'none given')
    {
        $this->message = $message;
    }

    public function getMessage()
    {
        return $this->message;
    }

}
