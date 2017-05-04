<?php
/**
 * Created by IntelliJ IDEA.
 * User: z.wieczorek
 * Date: 19.04.17
 * Time: 08:59
 */

namespace Dfi;


class Worker
{
    private $file;
    private $args;
    private $guid;

    /**
     * @return string
     */
    public function getGuid()
    {
        return $this->guid;
    }


    public function __construct($file, $args)
    {
        $this->guid = $this->makeGUID();
        $this->args = $args;
        $this->file = $file;
    }

    private function makeGUID()
    {
        mt_srand((double)microtime() * 10000);//optional for php 4.2.0 and up.
        $charid = strtoupper(md5(uniqid(rand(), true)));
        $hyphen = chr(45);// "-"
        $uuid = substr($charid, 0, 8) . $hyphen
            . substr($charid, 8, 4) . $hyphen
            . substr($charid, 12, 4) . $hyphen
            . substr($charid, 16, 4) . $hyphen
            . substr($charid, 20, 12);

        return $uuid;
    }


    public function run($runInBackground = true)
    {
        $res = false;

        if ($runInBackground) {


            $logFile = realpath(APPLICATION_PATH . "/../data/worker") . '/' . $this->guid . '.log';
            $errorLog = realpath(APPLICATION_PATH . "/../data/worker") . '/' . $this->guid . '.error.log';

            $phpOptions = [
                '-n',
                '-dextension=mysqlnd.so',
                '-dextension=pdo.so',
                '-dextension=pdo_mysql.so',
                '-dextension=json.so',
                '-dextension=dom.so',
                '-dextension=xml.so',
                '-dextension=simplexml.so',
                '-dextension=xmlwriter.so',
                '-dextension=iconv.so',
                '-dlog_errors=On',
                '-derror_reporting=E_ALL',
                '-derror_log=' . $errorLog,

                '-f'
            ];
            $command = "/usr/bin/php " . implode(" ", $phpOptions) . realpath(APPLICATION_PATH . "/../vendor/dafik/dfi/src/Dfi/Worker/Task.php") . ' "' . $this->guid . '" "' . $this->file . '" "' . implode("\" \"", $this->args) . "\"";
            $fullCommand = 'nohup ' . $command . ' > ' . $logFile . ' 2>&1 & echo $!';
            $pid = exec($fullCommand);


        } else {

            $dtkPath = realpath(APPLICATION_PATH . "/../vendor/dafik/dtk/src/");

            $class = new $this->file;
            $class->setFile($this->args[0]);

            $res = $class->run();


        }
        return $res;
    }
}