<?php declare(strict_types = 1);

namespace Xpbar\Sniffs\TypeHints;

use PHP_CodeSniffer\Files\File as PHP_CodeSniffer_File;
use PHP_CodeSniffer\Sniffs\Sniff as PHP_CodeSniffer_Sniff;

class MissingReturnTypeSniff implements PHP_CodeSniffer_Sniff
{
    public function register()
    {
        return array(
                T_FUNCTION,
                T_RETURN_TYPE,
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
    public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();

        $func = $phpcsFile->findNext(T_FUNCTION, $stackPtr);

        $phpcsFile->addWarning(
            trim($tokens[$stackPtr]['content']),
            $stackPtr,
            "XpBar_MissingReturnType"
        );
    }//end process()
}//end class
