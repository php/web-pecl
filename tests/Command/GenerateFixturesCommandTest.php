<?php

/*
  +----------------------------------------------------------------------+
  | The PECL website                                                     |
  +----------------------------------------------------------------------+
  | Copyright (c) 1999-2018 The PHP Group                                |
  +----------------------------------------------------------------------+
  | This source file is subject to version 3.01 of the PHP license,      |
  | that is bundled with this package in the file LICENSE, and is        |
  | available through the world-wide-web at the following url:           |
  | https://php.net/license/3_01.txt                                     |
  | If you did not receive a copy of the PHP license and are unable to   |
  | obtain it through the world-wide-web, please send a note to          |
  | license@php.net so we can mail you a copy immediately.               |
  +----------------------------------------------------------------------+
  | Authors: Peter Kokot <petk@php.net>                                  |
  +----------------------------------------------------------------------+
*/

namespace App\Tests\Command;

use App\Command\GenerateFixturesCommand;
use App\Config;
use App\Database;
use App\Database\Adapter;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

use PHPUnit\Framework\TestCase;

class GenerateFixturesCommandTest extends TestCase
{
    private $database;
    private $generateFixturesCommand;

    public function setUp()
    {
        $adapter = new Adapter();
        $adapter->setDsn('sqlite::memory:');
        $this->database = new Database($adapter->getInstance());

        $this->generateFixturesCommand = new GenerateFixturesCommand();
        $this->generateFixturesCommand->setDatabase($this->database);
    }

    public function testExecute()
    {
        $config = new Config(['env' => 'dev']);
        $this->generateFixturesCommand->setConfig($config);

        $application = new Application();
        $application->add($this->generateFixturesCommand);

        $command = $application->find('app:generate-fixtures');
        $commandTester = new CommandTester($command);
        $commandTester->setInputs(['n']);
        $exitCode = $commandTester->execute(['command' => $command->getName()]);

        $this->assertRegExp('/.../', $commandTester->getDisplay());
        $this->assertSame(0, $exitCode);
    }

    public function testExecuteInProdEnv()
    {
        $config = new Config(['env' => 'prod']);
        $this->generateFixturesCommand->setConfig($config);

        $application = new Application();
        $application->add($this->generateFixturesCommand);

        $command = $application->find('app:generate-fixtures');
        $commandTester = new CommandTester($command);
        $commandTester->setInputs(['n']);
        $exitCode = $commandTester->execute(['command' => $command->getName()]);

        $this->assertRegExp('/This command can be executed only in dev/', $commandTester->getDisplay());
        $this->assertSame(0, $exitCode);
    }
}
