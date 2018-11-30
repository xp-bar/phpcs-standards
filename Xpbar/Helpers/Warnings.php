<?php

namespace XpBar\Helpers;

use SlevomatCodingStandard\Helpers\ParameterTypeHint as SlevomatParameterTypeHint;

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
        $warning = "method parameter " . $param['name'] . " is nullable, null typehint missing from comment";
        $code = "XpBar.TypeHints.DocCommentParamMissingNullableMismatch";
        $severity = 4;

        $this->phpcsFile->addWarning($warning, $param['pointer'] + 2, $code, [], $severity);
    }

    /**
     * Add doc comment says nullable, argument is not warning
     *
     * @param array $param
     * @param SlevomatParameterTypeHint $argument
     * @return void
     */
    private function addDocCommentNullableWarning(array $param): void
    {
        $warning = "parameter " . $param['name']
            . " comment is nullable, but nullable operator is missing from parameter";
        $code = "XpBar.TypeHints.ParamNullableParamCommentMismatch";
        $severity = 4;

        $this->phpcsFile->addWarning($warning, $param['pointer'] + 2, $code, [], $severity);
    }

    /**
     * Add too many possible typehints warning
     *
     * @param array $param
     * @param SlevomatParameterTypeHint $argument
     * @return void
     */
    private function addTooManyPossibleTypesWarning(array $param): void
    {
        $warning = "@param " . $param['type_hint']. " " . $param['name']
            . " suggests multiple possible types: " . str_replace("|", ", ", $param['type_hint'])
            . "; consider refactoring to only pass one possible type / null.";
        $code = "XpBar.TypeHints.DocCommentTooManyTypes";
        $severity = 3;

        $this->phpcsFile->addWarning($warning, $param['pointer'], $code, [], $severity);
    }
}
