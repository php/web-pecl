<?php

/*
  +----------------------------------------------------------------------+
  | The PECL website                                                     |
  +----------------------------------------------------------------------+
  | Copyright (c) 1999-2019 The PHP Group                                |
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

namespace App\Command;

use App\Config;
use App\Database;
use App\Fixtures\AppFixtures;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

/**
 * Command line command for generating development data. It uses Symfony Console
 * component.
 */
class GenerateFixturesCommand extends Command
{
    private $database;
    private $config;
    private $fixtures;

    /**
     * Set database handler dependency.
     */
    public function setDatabase(Database $database)
    {
        $this->database = $database;
    }

    /**
     * Set configuration dependency.
     */
    public function setConfig(Config $config)
    {
        $this->config = $config;
    }

    public function setFixtures(AppFixtures $fixtures)
    {
        $this->fixtures = $fixtures;
    }

    /**
     * Configure command.
     */
    protected function configure()
    {
        $this->setName('app:generate-fixtures')
            ->setDescription('Insert fixtures in database.')
            ->setHelp('This command inserts demo data fixtures in the database.')
        ;
    }

    /**
     * Run the command, for example, bin/console app:generate-fixtures. It can
     * be executed only in development environment as a safety measure to not
     * delete something important.
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($this->config->get('env') !== 'dev') {
            $output->writeln('This command can be executed only in development.');

            return;
        }

        $helper = $this->getHelper('question');
        $question = new ConfirmationQuestion(
            'This will erase entire database. Are you sure you want to continue? [y/N]',
            false
        );

        if (!$helper->ask($input, $output, $question)) {
            $output->writeln('Exiting...');
            return;
        }

        // Delete all current users and add new ones
        $output->writeln('Deleting existing users');
        $this->database->run('DELETE FROM users');
        $output->writeln('Adding new users');
        $this->fixtures->insertUsers();

        // Delete all existing categories and add new ones
        $output->writeln('Deleting existing categories');
        $this->database->run('DELETE FROM categories');
        $output->writeln('Adding new categories');
        $this->fixtures->insertCategories();

        // Delete existing packages and add new ones
        $output->writeln('Deleting existing packages');
        $this->database->run('DELETE FROM packages');
        $this->database->run('DELETE FROM maintains');
        $output->writeln('Adding new packages');
        $this->fixtures->insertPackages();
    }
}
