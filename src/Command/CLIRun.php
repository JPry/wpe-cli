<?php

namespace JPry\Command;

use JPry\Helper\StorageFile;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CLIRun extends Command
{
    protected function configure()
    {
        $this
            ->setName('cli:run')
            ->setDescription('Run WP CLI commands against WP Engine install.')
            ->addOption(
                'account',
                'a',
                InputOption::VALUE_REQUIRED,
                'The WP Engine account name.'
            )
            ->addOption(
                'apikey',
                'k',
                InputOption::VALUE_REQUIRED,
                'The WP Engine API key.'
            )
            ->addOption(
                'store',
                's',
                InputOption::VALUE_NONE,
                'Whether to store the provided account and API key.'
            )
            ->addOption(
                'default',
                'd',
                InputOption::VALUE_NONE,
                'When storing, whether to set the provided account as the default.'
            )
            ->addOption(
                'dry-run',
                'r',
                InputOption::VALUE_NONE,
                'Whether to make a dry run only.'
            )
            ->addArgument(
                'cmd',
                InputArgument::REQUIRED | InputArgument::IS_ARRAY,
                'Commands to pass to the WP Engine account.'
            );
    }


    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->maybeStoreAccount($input, $output);
        $account = $this->getAccount($input);
        $apikey = $this->getApiKey($input, $account);
        $baseUrl = 'https://api.wpengine.com/1.2/?method=wp-cli';
        $baseUrl .= '&account_name=' . urlencode($account);
        $baseUrl .= '&wpe_apikey=' . urlencode($apikey);

        $cmds = $input->getArgument('cmd');
        foreach ($cmds as $cmd) {
            $baseUrl .= '&cmd[]=' . urlencode($cmd);
        }

        if ($output->isVerbose()) {
            $output->writeln('Running command <info>wp ' . join(' ', $cmds) . '</info>');
        }

        if ($input->getOption('dry-run')) {
            $output->writeln('<info>Request to be made to URL:</info>');
            $output->writeln($baseUrl);
            return;
        }

        $request = \Requests::get($baseUrl);
        $output->writeln($request->body);
    }


    protected function getAccount(InputInterface $input)
    {
        // Default to using the passed value.
        if ($input->getOption('account')) {
            return $input->getOption('account');
        }

        // Get the default account name from storage
        /** @var StorageFile $storage */
        $storage = $this->getHelper('storage');
        $account = $storage->getDefaultAccount();
        if (empty($account)) {
            throw new RuntimeException('No default account has been set, and [--account] was not passed.');
        }

        return $account;
    }


    protected function getApiKey(InputInterface $input, $account)
    {
        /** @var StorageFile $storage */
        $storage = $this->getHelper('storage');

        // Default to using the passed value.
        if ($input->getOption('apikey')) {
            return $input->getOption('apikey');
        }

        $apikey = $storage->getAccountKey($account);
        if (empty($apikey)) {
            throw new RuntimeException("No API key has been defined for account <info>{$account}</info>");
        }

        return $apikey;
    }

    protected function maybeStoreAccount(InputInterface $input, OutputInterface $output)
    {
        if ($input->getOption('store') && $input->getOption('account') && $input->getOption('apikey')) {
            /** @var StorageFile $storage */
            $storage = $this->getHelper('storage');
            $storage->setAccountKey(
                $input->getOption('account'),
                $input->getOption('apikey'),
                $input->getOption('default')
            );
            $output->writeln('<info>Account data stored for future use</info>');
        }
    }
}
