<?php declare(strict_types = 1);

namespace XpBar\Sniffs\TypeHints;

use PHP_CodeSniffer\Files\File as PHP_CodeSniffer_File;
use PHP_CodeSniffer\Sniffs\Sniff as PHP_CodeSniffer_Sniff;
use SlevomatCodingStandard\Helpers\DocCommentHelper as SlevomatDocCommentHelper;
use SlevomatCodingStandard\Helpers\FunctionHelper as SlevomatFunctionHelper;
use SlevomatCodingStandard\Helpers\TokenHelper as SlevomatTokenHelper;
use XpBar\Helpers\Errors;
use XpBar\Helpers\Generic;
use XpBar\Helpers\Parity;
use XpBar\Helpers\Validators;
use XpBar\Helpers\Warnings;

class CommentTypeDeclarationMatchSniffSniff implements PHP_CodeSniffer_Sniff
{
    use Warnings, Errors, Generic, Validators, Parity;

    const T_RETURN_TYPE = [T_STRING, T_ARRAY, T_ARRAY_HINT, T_CALLABLE, T_SELF, T_PARENT];
    const T_TYPE_HINT = [T_STRING, T_ARRAY, T_ARRAY_HINT, T_CALLABLE];

    /**
     * @return array
     */
    public function register()
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
