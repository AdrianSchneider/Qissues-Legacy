<?php

namespace Qissues\Interfaces\Console\Input;

use Qissues\Interfaces\Console\Shell\Shell;
use Symfony\Component\Console\Input\InputInterface;

class GitId
{
    protected $shell;

    public function __construct(Shell $shell)
    {
        $this->shell = $shell;
    }

    /**
     * Grabs the issue ID from the input
     * Then attempts to grab it from Git (branchname-x)
     *
     * @param InputInterface $input
     * @return integer|null issue id
     */
    public function getId(InputInterface $input)
    {
        if ($id = $input->getArgument('issue')) {
            return $id;
        }

        if (is_dir('.git')) {
            $branch = trim($this->shell->run('git rev-parse --symbolic-full-name --abbrev-ref HEAD'));
            $matches = null;
            if (preg_match('/^(.*)-([0-9]+)$/', $branch, $matches)) {
                return $matches[2];
            }
        }
    }
}
