<?php
/**
 * This file is part of CaptainHook.
 *
 * (c) Sebastian Feldmann <sf@sebastian.feldmann.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CaptainHook\App\Runner\Hook;

use CaptainHook\App\Hooks;
use CaptainHook\App\Runner\Hook;
use SebastianFeldmann\Git;

/**
 *  Hook
 *
 * @package CaptainHook
 * @author  Sebastian Feldmann <sf@sebastian-feldmann.info>
 * @link    https://github.com/sebastianfeldmann/captainhook
 * @since   Class available since Release 3.1.0
 */
class PrepareCommitMsg extends Hook
{
    /**
     * Hook to execute
     *
     * @var string
     */
    protected $hookName = Hooks::PREPARE_COMMIT_MSG;

    /**
     * @var string
     */
    private $commentChar;

    /**
     * Path to commit message file
     *
     * @var string
     */
    private $file;

    /**
     * Commit mode, empty or [message|template|merge|squash|commit]
     *
     * @var string
     */
    private $mode;

    /**
     * Commit hash if mode is commit during -c or --amend
     *
     * @var string
     */
    private $hash;

    /**
     * Fetch the original hook arguments and message related config settings
     *
     * @return void
     */
    public function beforeHook()
    {
        $this->commentChar = $this->repository->getConfigOperator()->getSafely('core.commentchar', '#');
        $this->file        = (string)$this->arguments->get('file');
        $this->mode        = (string)$this->arguments->get('mode');
        $this->hash        = (string)$this->arguments->get('hash');

        if (empty($this->file)) {
            throw new \RuntimeException('commit message file argument is missing');
        }

        parent::beforeHook();
    }

    /**
     * Read the commit message from file
     *
     * @return void
     */
    public function beforeAction()
    {
        $this->repository->setCommitMsg(Git\CommitMessage::createFromFile($this->file, $this->commentChar));
        parent::beforeAction();
    }

    /**
     * Write the commit message to disk so git or the next action can proceed further
     *
     * @return void
     */
    public function afterAction()
    {
        file_put_contents($this->file, $this->repository->getCommitMsg()->getRawContent());
        parent::afterAction();
    }
}