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
     * @param int $funcPtr
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

    /**
     * Validate that each argument has a doc comment
     *
     * @param array $tags
     * @param array $arguments
     * @param int $funcPtr
     * @return void
     */
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

    /**
     * Validate that a parsed param tag matches it's declaration
     *
     * @param array $paramTag
     * @param array $arguments
     * @return void
     */
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
                $this->addTooManyPossibleTypesWarning($paramTag, "@param");
                return;
            } elseif ($parity === false || $argumentIsNullable || $paramTagStatesNullable) {
                if (!$argumentIsNullable && !$paramTagStatesNullable) {
                    $this->addMismatchedParamTypeHintWarning($paramTag, $argument);
                } elseif ($argumentIsNullable && ! $paramTagStatesNullable) {
                    $this->addNullableDocCommentMissingWarning($paramTag);
                } elseif ($paramTagStatesNullable && !$argumentIsNullable) {
                    $this->addDocCommentNullableWarning($paramTag, "@param");
                }

                $withoutNullParamTagName = str_replace('|null', '', $paramTag['type_hint']);
                $withoutNullParity = static::evaluateTypeHintParity(
                    $argumentTypeHint,
                    $withoutNullParamTagName,
                    $argumentIsNullable
                );

                if ($withoutNullParity == false) {
                    $this->addMismatchedParamTypeHintWarning($paramTag, $argument);
                }
            }
            return;
        }
        return;
    }

    /**
     * Validate that a parsed param tag matches it's declaration
     *
     * @param array $returnTag
     * @param SlevomatReturnTypeHint|null $returnType
     * @param int $funcPtr
     * @return void
     */
    private function validateParsedReturnTag(array $returnTag, ?SlevomatReturnTypeHint $returnType, int $funcPtr): void
    {
        $returnParamTypeHint = $returnTag['type_hint'];
        $returnCommentStatesNullable = (bool) strpos($returnTag['type_hint'], '|null');

        if($returnParamTypeHint === 'mixed') {
            $this->addMixedReturnTagWarning($returnTag);
            return;
        }


        if ($returnType === null) {
            $functionName = $this->phpcsFile->getDeclarationName($funcPtr);
            if ($functionName !== "__construct" && count(explode('|', $returnParamTypeHint)) <= 2) {
                if ($returnParamTypeHint === "void" && $this->phpVersion != null && $this->phpVersion < 702000) {
                    return;
                }
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

        if ($returnParamTypeHint === null) {
            $this->addMissingReturnParamTypeHintWarning($returnTag);
            return;
        }

        if (strpos($returnParamTypeHint, '|void') !== false && $returnCommentStatesNullable) {
            $this->addVoidTypesNotNullableCommentError($returnTag);
            return;
        }

        $parity = static::evaluateTypeHintParity($returnParamTypeHint, $returnTypeHint, $returnTypeIsNullable);

        if ($parity === null) {
            $this->addTooManyPossibleTypesWarning($returnTag, "@return");
        } elseif ($parity === false || $returnTypeIsNullable || $returnCommentStatesNullable) {
            if (!$returnTypeIsNullable && !$returnCommentStatesNullable) {
                $this->addMismatchedReturnTypeHintWarning($returnTag, $returnType);
            } elseif ($returnTypeIsNullable && !$returnCommentStatesNullable) {
                $this->addNullableReturnTypeDocCommentMissingWarning($returnTag);
            } elseif ($returnCommentStatesNullable && !$returnTypeIsNullable) {
                $this->addDocCommentNullableWarning($returnTag, "@return");
            }
        }
        return;
    }
}
