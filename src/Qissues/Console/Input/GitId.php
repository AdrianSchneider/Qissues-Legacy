<?php

namespace Qissues\Console\Input;

use Qissues\Model\Number;
use Symfony\Component\Console\Input\InputInterface;

class GitId
{
    public function getId(InputInterface $input)
    {
        if ($id = $input->getArgument('issue')) {
            return $id;
        }

        if (is_dir('./.git')) {
            $branch = trim(shell_exec('git rev-parse --symbolic-full-name --abbrev-ref HEAD'));
            $matches = null;
            if (preg_match('/^(.*)-([0-9]+)$/', $branch, $matches)) {
                return $matches[2];
            }
        }
    }
}
