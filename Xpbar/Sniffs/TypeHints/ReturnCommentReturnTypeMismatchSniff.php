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
            T_VARIABLE,
            T_RETURN_TYPE,
            T_OPEN_PARENTHESIS,
            T_CLOSE_PARENTHESIS
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

        $this->checkReturnTypeDocParity($phpcsFile, $tokens, $funcPtr);
        $this->checkFunctionParameterDocParity($phpcsFile, $tokens, $funcPtr);
    }//end process()

    /**
     * Confirm that the return type in the doc block matches the return type declaration.
     *
     * @param PHP_CodeSniffer_File $phpcsFile
     * @param array $tokens
     * @param int $funcPtr
     * @return void
     */
    private function checkFunctionParameterDocParity(PHP_CodeSniffer_File $phpcsFile, array $tokens, int $funcPtr): void
    {
        $docParams = $this->getAssociatedTypeAndVariableDocNames($phpcsFile, $tokens, $funcPtr);
        $typedParams = $this->getTypedArgumentPointers($phpcsFile, $tokens, $funcPtr);
        $functionName = $phpcsFile->getDeclarationName($funcPtr);
        foreach ($typedParams as $param) {
            $docParam = $docParams[$param["variable_name"]] ?? null;
              
            if (count($docParams) == 0) {
                $warning = "No documentation comment detected for " . $functionName;
                $phpcsFile->addWarning(
                    $warning,
                    $funcPtr,
                    "XpBar.TypeHints.MissingDocBlock"
                );
            } elseif ($docParam == null) {
                $warning = "@param tag missing for " .$param['variable_name'];
                $phpcsFile->addWarning(
                    $warning,
                    $param['pointer'],
                    "XpBar.TypeHints.MissingDocParamTag"
                );
            } elseif (!isset($docParam["type_hint"])) {
                $warning = "@param tag " . $param['variable_name']
                    . "is missing a typehint.";
                $phpcsFile->addWarning(
                    $warning,
                    $param['pointer'],
                    "XpBar.TypeHints.MissingDocParamTypeHint"
                );
            } elseif (count(explode("|", $docParam["type_hint"])) > 1) {
                $warning = "@param tag suggests multiple types for parameter "
                    . $param['variable_name']
                    . "; consider refactoring to only accept one type of input.";
                $phpcsFile->addWarning(
                    $warning,
                    $docParam['pointer'],
                    "XpBar.TypeHints.DocParamRefactorPotential"
                );
            } elseif ($docParam['type_hint'] != $param['type_hint'] && !empty($param['type_hint'])) {
                $warning = "@param tag for ".$docParam['variable_name']
                    . " with type ".$docParam['type_hint']
                    . " does not match function paramater declaration of type "
                    . $param['type_hint'];
                $fix = $phpcsFile->addFixableWarning(
                    $warning,
                    $docParam['pointer'],
                    "XpBar.TypeHints.DocCommentParamTypeMismatch"
                );
                if (!$fix) {
                    return;
                }
                $phpcsFile->fixer->beginChangeset();
                $original = $tokens[$docParam['pointer']]['content'];
                $replaced = preg_replace('/' . $docParam['type_hint'] . '/', $param['type_hint'], $original, 1);
                $phpcsFile->fixer->replaceToken($docParam['pointer'], $replaced);
                $phpcsFile->fixer->endChangeset();
            }
        }
    }

    private function getTypedArgumentPointers(PHP_CodeSniffer_File $phpcsFile, array $tokens, int $functionPtr): array
    {
        $typedArguments = [];
        $argummentsBeginToken = $phpcsFile->findNext(T_OPEN_PARENTHESIS, $functionPtr + 1, null, false, null, true);
        $argummentsEndToken = $tokens[$argummentsBeginToken]["parenthesis_closer"];
        $currentPtr = $argummentsBeginToken;
        while ($currentPtr != -1 && $currentPtr != false && $currentPtr < $argummentsEndToken) {
            $varPtr = $phpcsFile->findNext(T_VARIABLE, $currentPtr + 1, $argummentsEndToken, false, null, true);
            if ($varPtr != false) {
                $typePtr = $phpcsFile->findPrevious(T_STRING, $varPtr - 1, $varPtr - 2, false, null, true);
                $typedArguments[] = [
                    'variable_name' => $tokens[$varPtr]['content'],
                    'type_hint' => $typePtr != false ? $tokens[$typePtr]['content'] : "",
                    'pointer' => $varPtr
                ];
            }
            $currentPtr = $varPtr;
        }
        return $typedArguments;
    }

    private function getAssociatedTypeAndVariableDocNames(PHP_CodeSniffer_File $phpcsFile, array $tokens, int $functionPtr): array
    {
        $assocativeParamDocHints = [];
        $currentPtr = $functionPtr;
        while ($currentPtr != -1 && $currentPtr != false) {
            $paramPtrs = $this->getFunctionParamCommentPointers($phpcsFile, $tokens, $currentPtr);
            $paramPtr = $paramPtrs[1];
            if ($paramPtr >= 0) {
                $contents = $tokens[$paramPtr]['content'];
                preg_match('/\$[A-z]*\b/', $contents, $paramVarNameMatches);
                if (count($paramVarNameMatches) > 0) {
                    $paramVarName = $paramVarNameMatches[0];
                    $paramTypeHint = trim(preg_replace('/\\'.$paramVarName.'.*$/', '', $contents));
                    $assocativeParamDocHints[$paramVarName] = [
                        'variable_name' => $paramVarName,
                        'type_hint' => $paramTypeHint,
                        'pointer' => $paramPtr
                    ];
                }
            }
            $currentPtr = $paramPtrs[0];
        }
        return $assocativeParamDocHints;
    }

    private function getFunctionParamCommentPointers(PHP_CodeSniffer_File $phpcsFile, array $tokens, int $functionPtr): array
    {
        $functionParamPtr = $phpcsFile->findPrevious(T_DOC_COMMENT_TAG, $functionPtr - 1, null, false, "@param", false);

        $functionParamTypeHint = $phpcsFile->findNext(T_DOC_COMMENT_STRING, $functionParamPtr + 1, null, false, null, false);

        return [$functionParamPtr, $functionParamTypeHint];
    }

    /**
     * Confirm that the return type in the doc block matches the return type declaration.
     *
     * @param PHP_CodeSniffer_File $phpcsFile
     * @param array $tokens
     * @param int $funcPtr
     * @return void
     */
    private function checkReturnTypeDocParity(PHP_CodeSniffer_File $phpcsFile, array $tokens, int $funcPtr): void
    {
        $returnDocPtr = $this->getDocReturnTypePointer($phpcsFile, $tokens, $funcPtr);
        $returnPtr = $this->getReturnTypePointer($phpcsFile, $tokens, $funcPtr);

        if ($returnPtr >= 0 && $returnDocPtr >= 0) {
            $returnType = trim($tokens[$returnPtr]['content']);

            $fullyQualifiedReturnType = trim($tokens[$returnDocPtr]['content']);
            preg_match('/(?!\\\\)[A-Z]{0,1}[a-z]*$/', $fullyQualifiedReturnType, $matchedReturnTypeClass);
            $docReturnType = count($matchedReturnTypeClass) > 0 ? $matchedReturnTypeClass[0] : null;

            if ($returnType != $docReturnType) {
                $warning = "@return tag '{$docReturnType}' does not match function return type declaration of '{$returnType}'";
                $fix = $phpcsFile->addFixableWarning(
                    $warning,
                    $returnDocPtr,
                    "XpBar.TypeHints.DocCommentReturnTypeMismatch"
                );
                if (!$fix) {
                    return;
                }
                $phpcsFile->fixer->beginChangeset();
                $phpcsFile->fixer->replaceToken($returnDocPtr, $returnType);
                $phpcsFile->fixer->endChangeset();
            }
        }
    }

    /**
     * Returns the pointer for a function's return type, or -1 if not found.
     *
     * @param PHP_CodeSniffer_File $phpcsFile
     * @param mixed[] $tokens
     * @param int $funcPtr
     * @return int
     */
    private function getReturnTypePointer(PHP_CodeSniffer_File $phpcsFile, array $tokens, int $functionPtr): int
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

    /**
     * Returns the pointer for a function document's return type, or -1 if not found.
     *
     * @param PHP_CodeSniffer_File $phpcsFile
     * @param mixed[] $tokens
     * @param int $funcPtr
     * @return int
     */
    private function getDocReturnTypePointer(PHP_CodeSniffer_File $phpcsFile, array $tokens, int $functionPtr): int
    {
        $returnCommentPtr = $phpcsFile->findPrevious(T_DOC_COMMENT_TAG, $functionPtr - 1, $functionPtr - 15, false, "@return", false);
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
