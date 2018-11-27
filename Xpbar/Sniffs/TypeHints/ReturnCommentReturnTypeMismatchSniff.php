<?php declare(strict_types = 1);

namespace XpBar\Sniffs\TypeHints;

use PHP_CodeSniffer\Files\File as PHP_CodeSniffer_File;
use PHP_CodeSniffer\Sniffs\Sniff as PHP_CodeSniffer_Sniff;

class ReturnCommentReturnTypeMismatchSniff implements PHP_CodeSniffer_Sniff
{
    const T_RETURN_TYPE = [T_STRING, T_ARRAY, T_ARRAY_HINT, T_CALLABLE, T_SELF, T_PARENT];
    /**
     * @return string[]
     */
    public function register()
    {
        return array(
            T_FUNCTION,
            T_DOC_COMMENT,
            T_RETURN_TYPE
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

        $functionName = $phpcsFile->getDeclarationName($funcPtr);
        $returnDocPtr = $this->getDocReturnTypePointer($phpcsFile, $tokens, $funcPtr);
        $returnPtr = $this->getReturnTypePointer($phpcsFile, $tokens, $funcPtr);

        if ($returnPtr >= 0 && $returnDocPtr >= 0) {
            $returnType = trim($tokens[$returnPtr]['content']);

            $fullyQualifiedReturnType = trim($tokens[$returnDocPtr]['content']);
            preg_match('/(?!\\\\)[A-Z][a-z]*$/', $fullyQualifiedReturnType, $matchedReturnTypeClass);
            $docReturnType = count($matchedReturnTypeClass) > 0 ? $matchedReturnTypeClass[0] : null;

            if ($returnType != $docReturnType) {
                $warning = "@return tag '{$docReturnType}' does not match function return type declaration of '{$returnType}'";
                $fix = $phpcsFile->addFixableWarning(
                    $warning,
                    $returnDocPtr,
                    "XpBar_ReturnCommentReturnTypeMismatch"
                );
                if (!$fix) {
                    return;
                }
                $phpcsFile->fixer->beginChangeset();
                $phpcsFile->fixer->replaceToken($returnDocPtr, $returnType);
                $phpcsFile->fixer->endChangeset();
            }
        }

    }//end process()

    private function getReturnTypePointer(PHP_CodeSniffer_File $phpcsFile, $tokens, int $functionPtr): int
    {
        $colonPtr = $phpcsFile->findNext(T_COLON, $functionPtr + 1, null, false, null, true);
        if (! $colonPtr || ! isset($tokens[$colonPtr])) {
            return -1;
        }

        $returnTypePtr = $phpcsFile->findNext(self::T_RETURN_TYPE, $colonPtr + 1, null, false, null, true);
        if (! $returnTypePtr || ! isset($tokens[$returnTypePtr])) {
            return -1;
        }

        return $returnTypePtr;
    }

    private function getDocReturnTypePointer(PHP_CodeSniffer_File $phpcsFile, $tokens, int $functionPtr): int
    {
        $returnCommentPtr = $phpcsFile->findPrevious(T_DOC_COMMENT_TAG, $functionPtr - 1, null, false, "@return", false);
        if (! $returnCommentPtr || ! isset($tokens[$returnCommentPtr])) {
            return -1;
        }

        $returnTypeDocPtr = $phpcsFile->findNext(T_DOC_COMMENT_STRING, $returnCommentPtr + 1, null, false, null, false);
        if (! $returnTypeDocPtr || ! isset($tokens[$returnTypeDocPtr])) {
            return -1;
        }

        return $returnTypeDocPtr;
    }
}//end class
