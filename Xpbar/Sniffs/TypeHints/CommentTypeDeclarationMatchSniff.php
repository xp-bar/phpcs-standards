<?php declare(strict_types = 1);

namespace XpBar\Sniffs\TypeHints;

use PHP_CodeSniffer\Files\File as PHP_CodeSniffer_File;
use PHP_CodeSniffer\Sniffs\Sniff as PHP_CodeSniffer_Sniff;
use SlevomatCodingStandard\Helpers\DocCommentHelper as SlevomatDocCommentHelper;
use SlevomatCodingStandard\Helpers\FunctionHelper as SlevomatFunctionHelper;
use SlevomatCodingStandard\Helpers\ReturnTypeHint as SlevomatReturnTypeHint;
use SlevomatCodingStandard\Helpers\TokenHelper as SlevomatTokenHelper;
use XpBar\Helpers\Warnings;

class CommentTypeDeclarationMatchSniffSniff implements PHP_CodeSniffer_Sniff
{
    use Warnings;

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


    /**
     * Check Type hint parity with comments
     *
     * @param PHP_CodeSniffer_File $phpcsFile
     * @param array $tokens
     * @param int $funcPtr
     * @return void
     */
    private function checkParity(int $funcPtr): void
    {
        $functionName = $this->phpcsFile->getDeclarationName($funcPtr);
        $returnType = SlevomatFunctionHelper::findReturnTypeHint($this->phpcsFile, $funcPtr);
        if ($functionName !== "__construct" && ! $returnType) {
            $this->addMissingReturnTypeWarning($funcPtr);
        }

        $docCommentOpenPointer = SlevomatDocCommentHelper::findDocCommentOpenToken($this->phpcsFile, $funcPtr);
        if ($docCommentOpenPointer == null) {
            $this->addMissingDocBlockError($funcPtr);
            return;
        } elseif ($docCommentOpenPointer != null && $this->tokens[$docCommentOpenPointer] != null) {
            // has doc block
            $tags = $this->tokens[$docCommentOpenPointer]['comment_tags'];
            $arguments = SlevomatFunctionHelper::getParametersTypeHints($this->phpcsFile, $funcPtr);
            $parsedTags = [];
            if (! empty($tags)) {
                // has comment tags
                foreach ($tags as $tag) {
                    $parsedTag = $this->parseCommentTagStrings($tag);
                    $parsedTags[] = $parsedTag;
                    $this->validateParsedCommentTags($parsedTag, $arguments, $returnType);
                }
            }
            $this->validateArgumentsHaveDocComments($parsedTags, $arguments, $funcPtr);
        }
    }

    private function parseCommentTagStrings(int $tagPointer)
    {
        $parsedTag = [
            'name' => null,
            'type_hint' => null,
            'comment' => null,
            'tag_type' => null,
            'pointer' => $tagPointer,
        ];

        $tagType = $this->tokens[$tagPointer]['content'];

        $parsedTag['tag_type'] = $tagType;

        $commentPointer = SlevomatTokenHelper::findNextExcluding($this->phpcsFile, T_DOC_COMMENT_WHITESPACE, $tagPointer + 1);

        $content = $this->tokens[$commentPointer]['content'];

        // Match variable names
        preg_match('/\$[A-z_]*\w/', $content, $varMatches);
        if (! empty($varMatches) && $varMatches[0] != null) {
            $parsedTag['name'] = trim($varMatches[0]);
        }

        // Match type hints
        preg_match('/[\\A-z_\|]*(?<!\$)/', $content, $typeMatches);
        if (! empty($typeMatches) && $typeMatches[0] != null) {
            $parsedTag['type_hint'] = trim($typeMatches[0]);
        }

        // Match param comments
        preg_match('/(\$[A-z_]*\w*)\K.*$/', $content, $commentMatches);
        if (! empty($commentMatches) && $commentMatches[0] != null) {
            $parsedTag['comment'] = trim($commentMatches[0]);
        }

        return $parsedTag;
    }

    private function validateParsedCommentTags(array $parsedTag, array $arguments, ?SlevomatReturnTypeHint $returnType)
    {
        switch ($parsedTag['tag_type']) {
            case '@param':
                $this->validateParsedParamTag($parsedTag, $arguments);
                break;
            case '@return':
                if ($returnType == null) {
                    // TODO: add "return type should be possible" warning
                    break;
                }
                $this->validateParsedReturnTag($parsedTag, $returnType);
                break;
            default:
                break;
        }
    }

    private function validateArgumentsHaveDocComments(array $tags, array $arguments, int $funcPtr): void
    {
        $tagVars = array_map(function ($tag) {
            return $tag['name'];
        }, $tags);

        foreach ($arguments as $name => $argument) {
            if ($argument == null) {
                $this->addMissingParamTypeHintWarning($name, $funcPtr);
            }

            $hasTag = in_array($name, $tagVars);
            if (! $hasTag) {
                $this->addMissingDocParamWarning($name, $funcPtr);
            }
        }
    }

    private function validateParsedParamTag(array $paramTag, array $arguments): void
    {
        if ($paramTag['name'] != null && isset($arguments[$paramTag['name']])) {
            $argument = $arguments[$paramTag['name']];
            $argumentTypeHint = $argument->getTypeHint();
            $argumentIsNullable = $argument->isNullable();

            if ($paramTag['type_hint'] == null) {
                $this->addMissingParamDocTypeHintWarning($paramTag);
                return;
            }
            $parity = static::evaluateTypeHintParity($argumentTypeHint, $paramTag['type_hint'], $argumentIsNullable);

            if ($parity === null) {
                $this->addTooManyPossibleTypesWarning($paramTag);
                return;
            } elseif ($parity === false || $argumentIsNullable) {
                if (! $argumentIsNullable && strpos($paramTag['type_hint'], '|null') === false) {
                    $this->addMismatchedParamTypeHintWarning($paramTag, $argument);
                    return;
                } elseif ($argumentIsNullable && strpos($paramTag['type_hint'], '|null') === false) {
                    $this->addNullableDocCommentMissingWarning($paramTag);
                    return;
                } elseif (strpos($paramTag['type_hint'], '|null') !== false && !$argumentIsNullable) {
                    $this->addDocCommentNullableWarning($paramTag);
                    return;
                }
                $this->addMismatchedParamTypeHintWarning($paramTag, $argument);
                return;
            }
            return;
        }
        return;
    }

    private static function evaluateTypeHintParity(string $typeA, string $typeB, bool $nullable): ?bool
    {
        if ($typeA === $typeB) {
            return true;
        }

        $typeAClass = static::getSimpleClassNameOrPrimitive($typeA);
        $typeBClass = static::getSimpleClassNameOrPrimitive($typeB);

        if ($typeAClass === null || $typeBClass === null) {
            return null;
        }

        if (preg_replace('/\|[ ]*null/', '', $typeB) === $typeA && ! $nullable) {
            return false;
        }

        if ($typeAClass === $typeBClass) {
            return true;
        }


        return false;
    }

    private static function getSimpleClassNameOrPrimitive(string $class): ?string
    {
        preg_match('/[\/\\A-z]*[^\w\| ]\K[\| A-z]*.*/', $class, $classMatch);
        $possible = explode("|", $class);
        if (count($possible) > 1) {
            $filtered = array_filter($possible, function ($type) {
                return strtolower($type) != "null";
            });
            if (count($filtered) > 1) {
                // too many possible types
                return null;
            }
            return $filtered[0];
        }

        if (count($classMatch) > 0 && $classMatch[0] != null) {
            return $possible[0];
        }
        return $class;
    }

    private function validateParsedReturnTag(array $returnTag, SlevomatReturnTypeHint $returnType)
    {
        /* var_dump($returnTag, $returnType); */
        /* die; */
    }
}//end class
