<?php
namespace Application\Build;

class ComparisonAnalyzer
{
    private $rExecPath;
    private $rScriptPath;
    private $database;
    private $username;
    private $password;

    public function __construct($database, $rExecPath) {
        $this->rExecPath = $rExecPath;
        $this->rScriptPath = __DIR__ . '/Script/analyze.R';
        $this->database = $database['name'];
        $this->username = $database['user'];
        $this->password = $database['password'];
    }

    public function run() {
        $argString = $this->getArgString(array(
            $this->database,
            $this->username,
            $this->password
        ));
        $command = "\"{$this->rExecPath}\" --vanilla --args {$argString} < \"{$this->rScriptPath}\"";
        exec($command);
    }

    private function getArgString($args) {
        $quotedArgs = array();
        foreach ($args as $arg) {
            $escapedArg = str_replace('"', '\"', $arg);
            $quotedArgs[] = '"' . $escapedArg . '"';
        }
        return join(' ', $quotedArgs);
    }

}