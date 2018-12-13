<?php

namespace XpBar\Helpers;

use SlevomatCodingStandard\Helpers\ParameterTypeHint as SlevomatParameterTypeHint;
use SlevomatCodingStandard\Helpers\ReturnTypeHint as SlevomatReturnTypeHint;

trait Warnings
{
    /**
     * Add missing typehint warning for parameter
     *
     * @param string $parameter
     * @param int $functionPointer
     */
    private function addMissingParamTypeHintWarning(string $parameter, int $functionPointer): void
    {
        $warning = "Type hint missing for " . $parameter;
        $code = "XpBar.TypeHints.ParamTypeHintMissing";
        $severity = 6;

        $this->phpcsFile->addWarning($warning, $functionPointer, $code, [], $severity);
    }

    /**
     * Add missing doc comment warning for parameter
     *
     * @param string $parameter
     * @param int $functionPointer
     * @return void
     */
    private function addMissingDocParamWarning(string $parameter, int $functionPointer): void
    {
        $warning = "@param comment missing for " . $parameter;
        $code = "XpBar.TypeHints.ParamCommentMissing";
        $severity = 6;

        $this->phpcsFile->addWarning($warning, $functionPointer, $code, [], $severity);
    }

    /**
     * Add missing doc comment type hint warning for parameter
     *
     * @param array $param
     * @param SlevomatParameterTypeHint $argument
     * @return void
     */
    private function addMissingParamDocTypeHintWarning(array $param): void
    {
        $warning = "@param " . $param['name']
            . " is missing a type hint";
        $code = "XpBar.TypeHints.DocCommentMissingTypeHint";
        $severity = 6;

        $this->phpcsFile->addWarning($warning, $param['pointer'], $code, [], $severity);
    }

    /**
     * Add mismatched param type hinting warning
     *
     * @param array $param
     * @param SlevomatParameterTypeHint $argument
     * @return void
     */
    private function addMismatchedParamTypeHintWarning(array $param, SlevomatParameterTypeHint $argument): void
    {
        $warning = "@param " . $param['type_hint'] . " " . $param['name']
            . " does not match function parameter declaration of type "
            . $argument->getTypeHint();
        $code = "XpBar.TypeHints.DocCommentParamTypeMismatch";
        $severity = 6;

        $this->phpcsFile->addWarning($warning, $param['pointer'] + 2, $code, [], $severity);
    }

    /**
     * Add mismatched return type hinting warning
     *
     * @param array $returnTag
     * @param SlevomatReturnTypeHint $returnType
     * @return void
     */
    private function addMismatchedReturnTypeHintWarning(
        array $returnTag,
        SlevomatReturnTypeHint $returnType
    ): void {
        $warning = "@return " . trim($returnTag['type_hint']) . " " . $returnTag['name']
            . " does not match function return type declaration of type "
            . $returnType->getTypeHint();
        $code = "XpBar.TypeHints.DocCommentReturnTypeMismatch";
        $severity = 6;

        $this->phpcsFile->addWarning($warning, $returnTag['pointer'] + 2, $code, [], $severity);
    }

    /**
     * Add missing return param typehint warning
     *
     * @param array $returnTag
     * @return void
     */
    private function addMissingReturnParamTypeHintWarning(array $returnTag): void
    {
        $warning = "@return is missing typehint";
        $code = "XpBar.TypeHints.ReturnTagMissingTypeHint";
        $severity = 6;

        $this->phpcsFile->addWarning($warning, $returnTag['pointer'], $code, [], $severity);
    }

    /**
     * Add a warning for missing return type
     *
     * @param int $functionPointer
     */
    private function addMissingReturnTypeWarning(int $functionPointer): void
    {
        $warning = "Return type declaration missing for " . $this->phpcsFile->getDeclarationName($functionPointer);
        $code = "XpBar.TypeHints.ReturnTypeDeclarationMissing";
        $severity = 4;

        $this->phpcsFile->addWarning($warning, $functionPointer, $code, [], $severity);
    }

    /**
     * Add nullable doc comment declaration missing warning
     *
     * @param array $param
     * @param SlevomatParameterTypeHint $argument
     * @return void
     */
    private function addNullableDocCommentMissingWarning(array $param): void
    {
        $warning = "Method parameter " . $param['name'] . " is nullable, null type hint missing from comment";
        $code = "XpBar.TypeHints.DocCommentParamMissingNullableMismatch";
        $severity = 4;

        $this->phpcsFile->addWarning($warning, $param['pointer'] + 2, $code, [], $severity);
    }

    /**
     * Add nullable doc comment declaration missing warning
     *
     * @param array $returnTag
     * @return void
     */
    private function addNullableReturnTypeDocCommentMissingWarning(array $returnTag): void
    {
        $warning = "Return type " . $returnTag['type_hint'] . " is nullable, null type hint missing from @return comment";
        $code = "XpBar.TypeHints.DocCommentParamMissingNullableMismatch";
        $severity = 4;

        $this->phpcsFile->addWarning($warning, $returnTag['pointer'] + 2, $code, [], $severity);
    }

    /**
     * Add doc comment says nullable, argument is not warning
     *
     * @param array $param
     * @param string $tag
     * @return void
     */
    private function addDocCommentNullableWarning(array $param, string $tag): void
    {
        $warning = $tag . " " . $param['type_hint'] . ($param['name'] ?? "")
            . " comment suggests " . ($param['name'] ?? "return type") . " is nullable, but nullable operator is missing from "
            . ($tag === "@param" ? "parameter" : "return type declaration");
        $code = "XpBar.TypeHints."
            . ( $tag === "@param" ?
                "ParamNullableParamCommentMismatch" :
                "ReturnTypeNullableReturnTypeCommentMismatch"
            );
        $severity = 4;

        $this->phpcsFile->addWarning($warning, $param['pointer'] + 2, $code, [], $severity);
    }

    /**
     * Add too many possible typehints warning
     *
     * @param array $param
     * @param string $tag
     * @return void
     */
    private function addTooManyPossibleTypesWarning(array $param, string $tag): void
    {
        $warning = $tag . " " . trim($param['type_hint']) . " " . $param['name']
            . " suggests multiple possible types: " . str_replace("|", ", ", $param['type_hint'])
            . "; consider refactoring to only " . ($tag == "@param" ? "pass" : "return") . " one possible type / null.";
        $code = "XpBar.TypeHints.DocCommentTooManyTypes";
        $severity = 3;

        $this->phpcsFile->addWarning($warning, $param['pointer'], $code, [], $severity);
    }

    /**
     * Add warning suggesting refactor because of mixed return type
     *
     * @param array $returnTag
     * @return void
     */
    private function addMixedReturnTagWarning(array $returnTag): void
    {
        $warning = "@return " . $returnTag['type_hint'] . " indicates more than two possible return types; consider refactoring.";
        $code = "XpBar.TypeHints.ReturnTypeHintMixedTooManyTypes";
        $severity = 3;

        $this->phpcsFile->addWarning($warning, $returnTag['pointer'], $code, [], $severity);
    }
}
