<?php declare(strict_types = 1);

namespace XpBar\Sniffs\TypeHints;

use PHP_CodeSniffer\Config as PHP_CodeSniffer_Config;
use PHP_CodeSniffer\Files\File as PHP_CodeSniffer_File;
use PHP_CodeSniffer\Sniffs\Sniff as PHP_CodeSniffer_Sniff;
use XpBar\Helpers\Errors;
use XpBar\Helpers\Generic;
use XpBar\Helpers\Parity;
use XpBar\Helpers\Validators;
use XpBar\Helpers\Warnings;

/**
 * Lints Doc blocks, function arguments and return types to make sure they match
 */
class CommentTypeDeclarationMatchSniffSniff implements PHP_CodeSniffer_Sniff
{
    use Warnings, Errors, Generic, Validators, Parity;

    /**
     * Register the tokens to parse in the file for these rules.
     *
     * @return array
     */
    public function register(): array
    {
        return array(
            T_FUNCTION
        );
    }

    /**
     * Processes this test, when one of its tokens is encountered.
     *
     * @param PHP_CodeSniffer_File $phpcsFile The current file being processed.
     * @param int                  $stackPtr  The position of the current token
     *                                        in the stack passed in $tokens.
     * @return void
     */
    public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr): void
    {
        $this->phpVersion = PHP_CodeSniffer_Config::getConfigData('php_version') ?? PHP_VERSION_ID;
        $tokens = $phpcsFile->getTokens();
        $funcPtr = $phpcsFile->findNext(T_FUNCTION, $stackPtr, null, false, null, true);
        if (! $funcPtr || ! isset($tokens[$funcPtr])) {
            return;
        }

        $this->phpcsFile = $phpcsFile;
        $this->tokens = $tokens;
        
        $this->checkParity($funcPtr);
    }//end process()
}//end class
