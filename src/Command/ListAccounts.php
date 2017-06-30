<?php

namespace JPry\Command;

use JPry\Helper\StorageFile;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * List the accounts that are stored in the storage file.
 *
 * @package JPry\Command
 */
class ListAccounts extends Command
{
    protected function configure()
    {
        $this
            ->setName('account:list')
            ->setAliases(array('ls'))
            ->setDescription('Add an account to the storage file.')
            ->addOption(
                'show-key',
                's',
                InputOption::VALUE_NONE,
                'Show the API key for each account.'
            )
            ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var StorageFile $storage */
        $storage = $this->getHelper('storage');
        $accounts = $storage->getAccounts();
        $default = $storage->getDefaultAccount();

        // Determine maximum width.
        $maxwidth = 1;
        foreach ($accounts as $account) {
            if (strlen($account) + 1 > $maxwidth) {
                $maxwidth = strlen($account) + 1;
            }
        }

        // Output each account
        $messages = array();
        foreach ($accounts as $account) {
            $isDefault = $default === $account;
            $message = str_pad($account, $maxwidth, ' ');
            if ($isDefault) {
                $message .= '<info>(default)</info>';
            }
            if ($input->getOption('show-key')) {
                $message .= (!$isDefault ? str_pad('', 10, ' ') : ' ') . $storage->getAccountKey($account);
            }
            $messages[] = $message;
        }

        if (!empty($messages)) {
            $output->writeln($messages);
            return;
        }

        $output->writeln('<error>No accounts found.</error>');
    }
}
