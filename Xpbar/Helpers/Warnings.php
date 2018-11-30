<?php

namespace XpBar\Helpers;

trait Warnings
{
    private function addMissingParamTypeHintWarning(string $parameter, int $functionPointer): void
    {
        $warning = "Type hint missing for " . $parameter;

        $this->phpcsFile->addWarning(
            $warning,
            $functionPointer,
            "XpBar.TypeHints.ParamTypeHintMissing"
        );
    }

    private function addMissingDocParamWarning(string $parameter, int $functionPointer): void
    {
        $warning = "@param comment missing for " . $parameter;

        $this->phpcsFile->addWarning(
            $warning,
            $functionPointer,
            "XpBar.TypeHints.ParamCommentMissing"
        );
    }

    private function addMissingReturnTypeWarning(int $functionPointer): void
    {
        $warning = "Return type declaration missing for " . $this->phpcsFile->getDeclarationName($functionPointer);

        $this->phpcsFile->addWarning(
            $warning,
            $functionPointer,
            "XpBar.TypeHints.ReturnTypeDeclarationMissing"
        );
    }

    private function addMissingDocBlockWarning(int $functionPointer): void
    {
        $warning = "Doc comment is missing for " . $this->phpcsFile->getDeclarationName($functionPointer);

        $this->phpcsFile->addWarning(
            $warning,
            $functionPointer,
            "XpBar.TypeHints.DocCommentMissing"
        );
    }

    private function addTooManyPossibleTypesWarning(array $param, $argument): void
    {
        $warning = "@param " . $param['type_hint']. " " . $param['name']
            . " suggests multiple possible types: " . str_replace("|", ", ", $param['type_hint'])
            . "; consider refactoring to only pass one possible type / null.";

        $this->phpcsFile->addWarning(
            $warning,
            $param['pointer'],
            "XpBar.TypeHints.DocCommentTooManyTypes"
        );
    }

    private function addMissingParamDocTypeHintWarning(array $param, $argument): void
    {
        $warning = "@param " . $param['name']
            . " is missing a type hint";

        $this->phpcsFile->addWarning(
            $warning,
            $param['pointer'],
            "XpBar.TypeHints.DocCommentMissingTypeHint"
        );
    }

    private function addMismatchedParamTypeHintWarning(array $param, $argument): void
    {
        $warning = "@param " . $param['type_hint'] . " " . $param['name']
            . " does not match function parameter declaration of type "
            . $argument->getTypeHint();

        $this->phpcsFile->addWarning(
            $warning,
            $param['pointer'] + 2,
            "XpBar.TypeHints.DocCommentParamTypeMismatch"
        );
    }

    private function addNullableDocCommentMissingWarning(array $param, $argument): void
    {
        $warning = "method parameter " . $param['name'] . " is nullable, null typehint missing from comment";

        $this->phpcsFile->addWarning(
            $warning,
            $param['pointer'] + 2,
            "XpBar.TypeHints.DocCommentParamMissingNullableMismatch"
        );
    }

    private function addDocCommentNullableWarning(array $param, $argument): void
    {
        $warning = "parameter " . $param['name'] . " comment is nullable, but nullable operator is missing from parameter";

        $this->phpcsFile->addWarning(
            $warning,
            $param['pointer'] + 2,
            "XpBar.TypeHints.ParamNullableParamCommentMismatch"
        );
    }
}
