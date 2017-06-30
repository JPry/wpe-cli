<?php

namespace JPry\Command;

use JPry\Helper\StorageFile;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RemoveAccount extends Command
{
    protected function configure()
    {
        $this
            ->setName('account:remove')
            ->setAliases(array('rm'))
            ->setDescription('Remove an account from the storage file.')
            ->addArgument(
                'account',
                InputArgument::REQUIRED,
                'The name of the account with WP Engine.'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var StorageFile $storage */
        $storage = $this->getHelper('storage');
        $account = $input->getArgument('account');

        $storage->removeAccountKey($account);
        $output->writeln("<info>{$account}</info> has been removed.");
    }
}
