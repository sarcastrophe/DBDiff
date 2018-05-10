<?php
namespace DBDiff;

use DBDiff\Params\ParamsFactory;
use DBDiff\DB\DiffCalculator;
use DBDiff\SQLGen\SQLGenerator;
use DBDiff\Exceptions\BaseException;
use DBDiff\Logger;
use DBDiff\Templater;

/**
 * This is installed with @dev, commit is c001e9f09083e16998d0005a7e33a3432e7f083a June 2017
 *
 * Class DBDiff
 * @package DBDiff
 */
class DBDiff {

    public function run($server1, $server2) {

        // Increase memory limit
        ini_set('memory_limit', '512M');

        try {

            $params = ParamsFactory::get($server1, $server2);

            // Diff
            $diffCalculator = new DiffCalculator;
            $diff = $diffCalculator->getDiff($params);

            // Empty diff
            if (empty($diff['schema']) && empty($diff['data'])) {
                Logger::info("Identical resources");
            } else {
                // SQL
                $sqlGenerator = new SQLGenerator($diff);
                $up =''; $down = '';
                $params->include = "all";
                if ($params->include !== 'down') {
                    $up = $sqlGenerator->getUp();
                }
                if ($params->include !== 'up') {
                    $down = $sqlGenerator->getDown();
                }

                // @todo configure
                //$templater = new Templater($params, $up, $down);
                //$templater->output();
                return array("up" => $up, "down" => $down);
            }

            Logger::success("Completed");

        } catch (\Exception $e) {
            if ($e instanceof BaseException) {
                Logger::error($e->getMessage(), true);
            } else {
                Logger::error("Unexpected error: " . $e->getMessage());
                throw $e;
            }
        }

    }
}
