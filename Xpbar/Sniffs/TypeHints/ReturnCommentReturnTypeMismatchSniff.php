<?php declare(strict_types = 1);

namespace XpBar\Sniffs\TypeHints;

use PHP_CodeSniffer\Files\File as PHP_CodeSniffer_File;
use PHP_CodeSniffer\Sniffs\Sniff as PHP_CodeSniffer_Sniff;
use SlevomatCodingStandard\Helpers\DocCommentHelper as SlevomatDocCommentHelper;
use SlevomatCodingStandard\Helpers\FunctionHelper as SlevomatFunctionHelper;

class ReturnCommentReturnTypeMismatchSniff implements PHP_CodeSniffer_Sniff
{
    const T_RETURN_TYPE = [T_STRING, T_ARRAY, T_ARRAY_HINT, T_CALLABLE, T_SELF, T_PARENT];
    const T_TYPE_HINT = [T_STRING, T_ARRAY, T_ARRAY_HINT, T_CALLABLE];

    /**
     * Holds pointers to all functions in the file and their associated warnings.
     *
     * @var array
     */
    private $functionPointers = [];

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
        $funcName = $phpcsFile->getDeclarationName($funcPtr);

        $this->functionPointers[$funcName] = [
            'pointer' => $funcPtr,
            'warnings' => [],
            'errors' => []
        ];

        $this->checkParity($phpcsFile, $tokens, $funcPtr);
        /* $this->checkFunctionParameterDocParity($phpcsFile, $tokens, $funcPtr); */
        /* $this->checkReturnTypeDocParity($phpcsFile, $tokens, $funcPtr); */
    }//end process()


    private function checkParity(PHP_CodeSniffer_File $phpcsFile, array $tokens, int $funcPtr): void
    {
        $hasDoc = SlevomatDocCommentHelper::hasDocComment($phpcsFile, $funcPtr);
    }

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
            $docParam = $docParams[trim($param["variable_name"])] ?? null;


            if (! $hasDoc) {
                $warning = "No documentation comment detected for " . $functionName;
                $code = "XpBar.TypeHints.MissingDocBlock";
                $phpcsFile->addWarning(
                    $warning,
                    $funcPtr,
                    $code
                );
                $this->functionPointers[$functionName]['warnings'][] = $code;
                return;
            } elseif ($docParam == null) {
                $warning = "@param tag missing for " .$param['variable_name'];
                $phpcsFile->addWarning(
                    $warning,
                    $param['pointer'],
                    "XpBar.TypeHints.MissingDocParamTag"
                );
                return;
            } elseif (!isset($docParam["type_hint"])) {
                $warning = "@param tag " . $param['variable_name']
                    . "is missing a typehint.";
                $phpcsFile->addWarning(
                    $warning,
                    $param['pointer'],
                    "XpBar.TypeHints.MissingDocParamTypeHint"
                );
                return;
            } elseif (count(explode("|", $docParam["type_hint"])) > 1) {
                $warning = "@param tag suggests multiple types for parameter "
                    . $param['variable_name']
                    . "; consider refactoring to only accept one type of input.";
                $phpcsFile->addWarning(
                    $warning,
                    $docParam['pointer'],
                    "XpBar.TypeHints.DocParamRefactorPotential"
                );
                return;
            } elseif ($docParam['type_hint'] != $param['type_hint'] && !empty($param['type_hint'])) {
                $warning = "@param tag for ".$docParam['variable_name']
                    . " with type ".$docParam['type_hint']
                    . " does not match function parameter declaration of type "
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
                return;
            }
        }
    }

    /**
     * Get typed argument pointers for a function
     *
     * @param PHP_CodeSniffer_File $phpcsFile
     * @param array $tokens
     * @param int $functionPtr
     * @return array
     */
    private function getTypedArgumentPointers(PHP_CodeSniffer_File $phpcsFile, array $tokens, int $functionPtr): array
    {
        $typedArguments = [];
        $argummentsBeginToken = $phpcsFile->findNext(T_OPEN_PARENTHESIS, $functionPtr + 1, null, false, null, true);
        $argummentsEndToken = $tokens[$argummentsBeginToken]["parenthesis_closer"];
        $currentPtr = $argummentsBeginToken;
        while ($currentPtr != -1 && $currentPtr != false && $currentPtr < $argummentsEndToken) {
            $typePtr = $phpcsFile->findNext(self::T_TYPE_HINT, $currentPtr + 1, $argummentsEndToken, false, null, true);
            if ($typePtr != -1 && $typePtr != false) {
                $varPtr = $phpcsFile->findNext(T_VARIABLE, $typePtr + 1, $typePtr + 3, false, null, true);
                if ($varPtr != -1 && $varPtr != false) {
                    $typedArguments[] = [
                        'variable_name' => $tokens[$varPtr]['content'],
                        'type_hint' => $typePtr != false ? $tokens[$typePtr]['content'] : "",
                        'pointer' => $varPtr
                    ];
                }
            }
            $currentPtr = $typePtr;
        }
        return $typedArguments;
    }

    /**
     * Get typed argument pointers for a function
     *
     * @param PHP_CodeSniffer_File $phpcsFile
     * @param array $tokens
     * @param int $functionPtr
     * @return array
     */
    private function getAssociatedTypeAndVariableDocNames(
        PHP_CodeSniffer_File $phpcsFile,
        array $tokens,
        int $functionPtr
    ): array {
        $assocativeParamDocHints = [];
        $currentPtr = $functionPtr;
        while ($currentPtr != -1 && $currentPtr != false) {
            $paramPtrs = $this->getFunctionParamCommentPointers($phpcsFile, $tokens, $currentPtr);
            if (count($paramPtrs) == 0) {
                return [];
            }
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

    /**
     * Get a single @param comment pointer.
     *
     * @param PHP_CodeSniffer_File $phpcsFile
     * @param array $tokens
     * @param int $functionPtr
     * @return array
     */
    private function getFunctionParamCommentPointers(PHP_CodeSniffer_File $phpcsFile, array $tokens, int $functionPtr): array
    {
        $functionParamPtr = $phpcsFile->findPrevious(
            T_DOC_COMMENT_TAG,
            $functionPtr - 1,
            null,
            false,
            "@param",
            false
        );

        if ($functionParamPtr || ! isset($tokens[$functionParamPtr])) {
            return [];
        }

        $functionParamTypeHint = $phpcsFile->findNext(
            T_DOC_COMMENT_STRING,
            $functionParamPtr + 1,
            null,
            false,
            null,
            false
        );

        if ($functionParamTypeHint || ! isset($tokens[$functionParamTypeHint])) {
            return [];
        }

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
        $returnType = SlevomatFunctionHelper::findReturnTypeHint($phpcsFile, $funcPtr);

        return;
        $returnDocPtr = $this->getDocReturnTypePointer($phpcsFile, $tokens, $funcPtr);
        $returnPtr = $this->getReturnTypePointer($phpcsFile, $tokens, $funcPtr);
        $functionName = $phpcsFile->getDeclarationName($funcPtr);

        if ($returnDocPtr < 0) {
            if (in_array(
                "XpBar.TypeHints.MissingDocBlock",
                $this->functionPointers[$functionName]['warnings']
            )) {
                return;
            }

            $warning = "@return tag missing for {$functionName}";
            $phpcsFile->addWarning(
                $warning,
                $funcPtr,
                "XpBar.TypeHints.DocCommentReturnTypeMismatch"
            );
        } elseif ($returnPtr >= 0 && $returnDocPtr >= 0) {
            $returnType = trim($tokens[$returnPtr]['content']);

            $fullyQualifiedReturnType = trim($tokens[$returnDocPtr]['content']);
            preg_match('/(?!\\\\)[A-Z]{0,1}[a-z]*$/', $fullyQualifiedReturnType, $matchedReturnTypeClass);
            $docReturnType = count($matchedReturnTypeClass) > 0 ? $matchedReturnTypeClass[0] : null;

            if ($returnType != $docReturnType) {
                $warning = "@return tag '{$docReturnType}'"
                    . " does not match function return type declaration of '{$returnType}'";
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
     * @param array $tokens
     * @param int $functionPtr
     * @return int
     */
    private function getReturnTypePointer(PHP_CodeSniffer_File $phpcsFile, array $tokens, int $functionPtr): int
    {
        $closeBracketPtr = $phpcsFile->findNext(T_CLOSE_PARENTHESIS, $functionPtr + 1, null, false, null, true);
        if (! $closeBracketPtr || ! isset($tokens[$closeBracketPtr])) {
            return -1;
        }

        $colonPtr = $phpcsFile->findNext(T_COLON, $closeBracketPtr + 1, $closeBracketPtr + 3, false, null, false);
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
     * @param array $tokens
     * @param int $functionPtr
     * @return int
     */
    private function getDocReturnTypePointer(PHP_CodeSniffer_File $phpcsFile, array $tokens, int $functionPtr): int
    {
        $returnCommentPtr = $phpcsFile->findPrevious(
            T_DOC_COMMENT_TAG,
            $functionPtr - 1,
            $functionPtr - 15,
            false,
            "@return",
            false
        );
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
