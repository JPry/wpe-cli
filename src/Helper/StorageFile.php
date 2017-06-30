<?php

namespace JPry\Helper;

use Symfony\Component\Console\Helper\HelperInterface;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Class for handling a custom storage file.
 *
 * @package JPry\Helper
 */
class StorageFile implements HelperInterface
{
    protected $accounts;
    protected $defaultAccount;
    protected $file;
    protected $fs;

    // Helper properties
    protected $helperSet;
    protected $name;

    public function __construct(Filesystem $filesystem, $path = '', $name = '')
    {
        if (empty($path)) {
            $path = getenv('HOME');
        }

        $this->fs = $filesystem;
        $this->file = "{$path}/.wpe-cli";
        $this->name = $name ?: 'storage';

        $this->readStorage();
    }

    /**
     * Get all of the accounts that are registered.
     *
     * @author Jeremy Pry
     * @return array
     */
    public function getAccounts()
    {
        return array_keys($this->accounts);
    }

    /**
     * Get the default account.
     *
     * @author Jeremy Pry
     * @return string
     */
    public function getDefaultAccount()
    {
        return $this->defaultAccount;
    }

    /**
     * Set an account API key.
     *
     * @author Jeremy Pry
     *
     * @param string $accountName
     * @param string $apiKey
     * @param bool   $default
     */
    public function setAccountKey($accountName, $apiKey, $default = false)
    {
        $this->accounts[$accountName] = $apiKey;
        if ($default || empty($this->defaultAccount)) {
            $this->defaultAccount = $accountName;
        }
        $this->writeFile();
    }

    /**
     * Get the API key for a given account.
     *
     * @author Jeremy Pry
     *
     * @param string $accountName
     *
     * @return string|null
     */
    public function getAccountKey($accountName)
    {
        return isset($this->accounts[$accountName]) ? $this->accounts[$accountName] : null;
    }

    /**
     * Set the default account name.
     *
     * @author Jeremy Pry
     *
     * @param string $account
     * @param string $apiKey
     */
    public function setDefaultAccount($account, $apiKey)
    {
        $this->setAccountKey($account, $apiKey, true);
    }

    /**
     * Get the accounts information from the storage file
     *
     * @author Jeremy Pry
     */
    protected function readStorage()
    {
        if (!$this->fs->exists($this->file)) {
            $this->accounts = array();
            $this->defaultAccount = '';
            $this->writeFile();
        }

        $contents = json_decode(file_get_contents($this->file), true);
        $this->accounts = isset($contents['accounts']) ? $contents['accounts'] : array();
        $this->defaultAccount = isset($contents['default_account']) ? $contents['default_account'] : '';
    }

    /**
     * Remove an API account key from storage.
     *
     * @author Jeremy Pry
     *
     * @param string $accountName
     */
    public function removeAccountKey($accountName)
    {
        unset($this->accounts[$accountName]);
        $this->writeFile();
    }

    /**
     * Get the contents of the storage file.
     *
     * @author Jeremy Pry
     * @return string
     */
    protected function getContents()
    {
        if ($this->fs->exists($this->file)) {
            return file_get_contents($this->file);
        }

        return '';
    }

    /**
     * Write out the storage file.
     *
     * @author Jeremy Pry
     */
    protected function writeFile()
    {
        $contents = json_decode($this->getContents(), true);
        if (null === $contents) {
            $contents = array();
        }

        $contents['accounts'] = $this->accounts;
        $contents['default_account'] = $this->defaultAccount;
        $this->fs->dumpFile($this->file, json_encode($contents));
        $this->fs->chmod($this->file, 0600);
    }

    /**
     * Sets the helper set associated with this helper.
     *
     * @param HelperSet $helperSet A HelperSet instance
     */
    public function setHelperSet(HelperSet $helperSet = null)
    {
        $this->helperSet = $helperSet;
    }

    /**
     * Gets the helper set associated with this helper.
     *
     * @return HelperSet A HelperSet instance
     */
    public function getHelperSet()
    {
        return $this->helperSet;
    }

    /**
     * Returns the canonical name of this helper.
     *
     * @return string The canonical name
     */
    public function getName()
    {
        return $this->name;
    }
}
