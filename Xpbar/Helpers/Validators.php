<?php

namespace XpBar\Helpers;

use SlevomatCodingStandard\Helpers\ReturnTypeHint as SlevomatReturnTypeHint;

trait Validators
{
    /**
     * Validate parsed comment tags
     *
     * @param array $parsedTag
     * @param array $arguments
     * @param SlevomatReturnTypeHint|null $returnType
     * @return void
     */
    private function validateParsedCommentTags(
        array $parsedTag,
        array $arguments,
        ?SlevomatReturnTypeHint $returnType,
        int $funcPtr
    ): void {
        switch ($parsedTag['tag_type']) {
            case '@param':
                $this->validateParsedParamTag($parsedTag, $arguments);
                break;
            case '@return':
                $this->validateParsedReturnTag($parsedTag, $returnType, $funcPtr);
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
            $paramTagStatesNullable = strpos($paramTag['type_hint'], '|null');

            if ($paramTag['type_hint'] == null) {
                $this->addMissingParamDocTypeHintWarning($paramTag);
                return;
            }
            $parity = static::evaluateTypeHintParity($argumentTypeHint, $paramTag['type_hint'], $argumentIsNullable);

            if ($parity === null) {
                $this->addTooManyPossibleTypesWarning($paramTag);
                return;
            } elseif ($parity === false || $argumentIsNullable || $paramTagStatesNullable) {
                if (!$argumentIsNullable && !$paramTagStatesNullable) {
                    $this->addMismatchedParamTypeHintWarning($paramTag, $argument);
                    return;
                }
                if ($argumentIsNullable && ! $paramTagStatesNullable) {
                    $this->addNullableDocCommentMissingWarning($paramTag);
                    return;
                } elseif ($paramTagStatesNullable && !$argumentIsNullable) {
                    $this->addDocCommentNullableWarning($paramTag);
                    return;
                }
            }
            return;
        }
        return;
    }

    private function validateParsedReturnTag(array $returnTag, ?SlevomatReturnTypeHint $returnType, int $funcPtr): void
    {
        $returnParamTypeHint = $returnTag['type_hint'];
        $returnCommentStatesNullable = (bool) strpos($returnTag['type_hint'], '|null');

        if ($returnType === null) {
            $functionName = $this->phpcsFile->getDeclarationName($funcPtr);
            if ($functionName !== "__construct" && count(explode('|', $returnParamTypeHint)) <= 2) {
                $this->addMissingReturnTypeWarning($funcPtr);
            }
            return;
        }

        $returnTypeHint = $returnType->getTypeHint();
        $returnTypeIsNullable = $returnType->isNullable();

        if ($returnTypeHint == 'void' && $returnTypeIsNullable) {
            $this->addVoidTypesNotNullableError($returnType);
            return;
        }

        if (strpos($returnParamTypeHint, '|void') != -1 && $returnCommentStatesNullable) {
            $this->addVoidTypesNotNullableCommentError($returnTag);
            return;
        }

        $parity = static::evaluateTypeHintParity($returnParamTypeHint, $returnTypeHint, $returnTypeIsNullable);

        if ($parity === null) {
            // too many possible return types, consider refactoring warning
        } elseif ($parity === false || $returnTypeIsNullable || $returnCommentStatesNullable) {
            if (!$returnTypeIsNullable && !$returnCommentStatesNullable) {
                // mismatched return type warning
            } elseif ($returnTypeIsNullable && !$returnCommentStatesNullable) {
                // return type is nullable, comment doesn't say warning
            } elseif ($returnCommentStatesNullable && !$returnTypeIsNullable) {
                // return type comment says null is possible, but return type is not nullable
            }
        }
        return;
    }
}
