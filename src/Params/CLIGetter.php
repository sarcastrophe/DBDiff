<?php
namespace DBDiff\Params;

use DBDiff\Exceptions\CLIException;
use Aura\Cli\CliFactory;
use Aura\Cli\Status;

class CLIGetter implements ParamsGetter {

    private $db1;
    private $db2;
    private $server1;
    private $server2;
    private $format;
    private $template;
    private $type;
    private $include;
    private $nocomments;
    private $config;
    private $output;
    private $debug;

    /**
     * CLIGetter constructor.
     * @param $db1
     * @param $db2
     * @internal param $server1
     * @internal param $server2
     */
    public function __construct($db1, $db2)
    {
        $this->db1 = $db1;
        $this->db2 = $db2;
    }

    public function getParams() {
        $params = new \StdClass;
        $params->type = "data";
        $input = "server1.".$this->db1.":server2.".$this->db2;
        $params->input = $this->parseInput($input);
        $db = array(
            'host'      => "localhost",
            'port'      => 3306,
            'user'  => "root",
            'password'  => "",
            'db' => $this->db1);
        $db2 = array(
            'host'      => "localhost",
            'port'      => 3306,
            'user'  => "root",
            'password'  => "",
            'db' => $this->db2);
        $params->server1 = $db;
        $params->server2 = $db2;
        return $params;
    }

    protected function parseServer($server) {
        $parts = explode('@', $server);
        $creds = explode(':', $parts[0]);
        $dns   = explode(':', $parts[1]);
        return [
            'user'     => $creds[0],
            'password' => $creds[1],
            'host'     => $dns[0],
            'port'     => $dns[1]
        ];
    }

    protected function parseInput($input) {
        $parts  = explode(':', $input);
        if (sizeof($parts) !== 2) {
            throw new CLIException("You need two resources to compare");
        }
        $first  = explode('.', $parts[0]);
        $second = explode('.', $parts[1]);

        if (sizeof($first) !== sizeof($second)) {
            throw new CLIException("The two resources must be of the same kind");
        }

        if (sizeof($first) === 2) {
            return [
                'kind' => 'db',
                'source' => ['server' => $first[0], 'db' => $first[1]],
                'target' => ['server' => $second[0], 'db' => $second[1]],
            ];
        } else if (sizeof($first) === 3) {
            return [
                'kind' => 'table',
                'source' => ['server' => $first[0],  'db' => $first[1],  'table' => $first[2]],
                'target' => ['server' => $second[0], 'db' => $second[1], 'table' => $second[2]],
            ];
        } else throw new CLIException("Unkown kind of resources");
    }
}
