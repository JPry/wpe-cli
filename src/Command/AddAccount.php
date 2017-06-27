<?php
/**
 *
 */

namespace JPry\Command;

use JPry\Helper\StorageFile;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class AddAccount extends Command
{
    protected function configure()
    {
        $this
            ->setName('add-account')
            ->setAliases(array('add', 'a'))
            ->setDescription('Add an account to the storage file.')
            ->addArgument(
                'account',
                InputArgument::REQUIRED,
                'The name of the account with WP Engine.'
            )
            ->addArgument(
                'apikey',
                InputArgument::REQUIRED,
                'The API key for the account.'
            )
            ->addOption(
                'default',
                'd',
                InputOption::VALUE_NONE,
                'Whether to make this the default account.'
            )
            ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var StorageFile $storage */
        $storage = $this->getHelper('storage');
        $account = $input->getArgument('account');
        $apikey = $input->getArgument('apikey');
        $default = $input->getOption('default');

        $storage->setAccountKey($account, $apikey, $default);
        $output->writeln("<info>{$account}</info> has been stored.");
    }
}
