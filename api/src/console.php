<?php

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

$console = new Application('Daedalus', 1.0);
$console->getDefinition()->addOption(new InputOption('--env', '-e', InputOption::VALUE_REQUIRED, 'The Environment name.', 'dev'));
$console->setDispatcher($app['dispatcher']);
$console
    ->register('process-route')
    ->setDefinition(array(
        new InputArgument('token', null, InputOption::VALUE_REQUIRED, 'token'),
    ))
    ->setDescription('Process a pending route')
    ->setCode(function (InputInterface $input, OutputInterface $output) use ($app) {
        $token = $input->getArgument('token');
        $app['route.service']->process($token);
    })
;

return $console;
