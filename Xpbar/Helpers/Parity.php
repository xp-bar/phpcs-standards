<?php
namespace XpBar\Helpers;

use SlevomatCodingStandard\Helpers\DocCommentHelper as SlevomatDocCommentHelper;
use SlevomatCodingStandard\Helpers\FunctionHelper as SlevomatFunctionHelper;

trait Parity
{
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
        $returnType = SlevomatFunctionHelper::findReturnTypeHint($this->phpcsFile, $funcPtr);
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
                    $this->validateParsedCommentTags($parsedTag, $arguments, $returnType, $funcPtr);
                }
            }
            $this->validateArgumentsHaveDocComments($parsedTags, $arguments, $funcPtr);
        }
    }

    /**
     * evaluates the parity between two typehint strings
     *
     * @param string $typeA
     * @param string $typeB
     * @param bool $nullable
     * @return bool|null
     */
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
}
