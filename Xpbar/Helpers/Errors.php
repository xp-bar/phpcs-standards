<?php
namespace XpBar\Helpers;

use SlevomatCodingStandard\Helpers\ReturnTypeHint as SlevomatReturnTypeHint;

trait Errors
{
    /**
     * Add error for missing doc block comment
     *
     * @param int $functionPointer
     * @return void
     */
    private function addMissingDocBlockError(int $functionPointer): void
    {
        $error = "Doc comment is missing for " . $this->phpcsFile->getDeclarationName($functionPointer);
        $code = "XpBar.TypeHints.DocCommentMissing";
        $severity = 7;

        $this->phpcsFile->addError($error, $functionPointer, $code, [], $severity);
    }

    /**
     * Add error for attempt to state that void return types can be nullable
     *
     * @param SlevomatReturnTypeHint $returnType
     * @return void
     */
    private function addVoidTypesNotNullableError(SlevomatReturnTypeHint $returnType): void
    {
        $warning = "void return types are not nullable";
        $code = "XpBar.TypeHints.ReturnTypesNotNullable";
        $severity = 9;

        $this->phpcsFile->addError($warning, $returnType->getStartPointer(), $code, [], $severity);
    }

    /**
     * Add error for attempt to state that void return types can be nullable
     *
     * @param array $returnTag
     * @return void
     */
    private function addVoidTypesNotNullableCommentError(array $returnTag): void
    {
        $warning = "@return " . $returnTag['type_hint'] . " is invalid; void return types cannot be nullable";
        $code = "XpBar.TypeHints.ReturnTypesNotNullableComment";
        $severity = 9;

        $this->phpcsFile->addError($warning, $returnTag['pointer'], $code, [], $severity);
    }
}
