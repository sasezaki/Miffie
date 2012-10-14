<?php
namespace Miffie;

use Zend\Console\Getopt;

class GetoptExt extends Getopt
{
    public function getOptionVars()
    {
        $this->parse();
        return $this->options;
    }
}

