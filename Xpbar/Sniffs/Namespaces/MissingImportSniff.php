<?php declare(strict_types = 1);

namespace XpBar\Sniffs\Namespaces;

use PHP_CodeSniffer\Files\File as PHP_CodeSniffer_File;
use PHP_CodeSniffer\Sniffs\Sniff as PHP_CodeSniffer_Sniff;
use SlevomatCodingStandard\Helpers\ClassHelper;
use SlevomatCodingStandard\Helpers\NamespaceHelper;
use SlevomatCodingStandard\Helpers\ReferencedName;
use SlevomatCodingStandard\Helpers\ReferencedNameHelper;
use SlevomatCodingStandard\Helpers\TokenHelper;
use SlevomatCodingStandard\Helpers\UseStatement;
use SlevomatCodingStandard\Helpers\UseStatementHelper;
use XpBar\Helpers\Errors;
use XpBar\Helpers\Warnings;

/**
 * Lints Doc blocks, function arguments and return types to make sure they match
 */
class MissingImportSniff implements PHP_CodeSniffer_Sniff
{
    use Warnings, Errors;

    /**
     * Register the tokens to parse in the file for these rules.
     *
     * @return array
     */
    public function register(): array
    {
        return array(
            T_OPEN_TAG
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
        $imports = UseStatementHelper::getUseStatements($phpcsFile, $stackPtr);
        $referencedNames = ReferencedNameHelper::getAllReferencedNames($phpcsFile, $stackPtr);

        $missingImports = [];

        foreach ($referencedNames as $referencedName) {
            $name = $referencedName->getNameAsReferencedInFile();
            if ($name == null) {
                continue;
            }
            $nameParts = NamespaceHelper::getNameParts($name);
            $nameAsReferencedInFile = $nameParts[0];
            $nameReferencedWithoutSubNamespace = count($nameParts) === 1;
            $uniqueId = $nameReferencedWithoutSubNamespace
                ? UseStatement::getUniqueId($referencedName->getType(), $nameAsReferencedInFile)
                : UseStatement::getUniqueId(ReferencedName::TYPE_DEFAULT, $nameAsReferencedInFile);
            if (NamespaceHelper::isFullyQualifiedName($name)) {
                continue;
            }

            if (isset($imports[$uniqueId])) {
                continue;
            }

            $missingImports[] = $referencedName;
        }
        $classPtr = TokenHelper::findNext($phpcsFile, [T_CLASS], $stackPtr);
        $className = ClassHelper::getName($phpcsFile, $classPtr);
        $files = $this->getClassFileNames($phpcsFile);

        foreach ($missingImports as $reference) {
            $name = $reference->getNameAsReferencedInFile();
            $checkedTypes = ['default'];
            if (in_array($reference->getType(), $checkedTypes) && !in_array($name, $files) && $name != $className) {
                $message = "Missing Import for ".$name.", it's not in this directory!";
                $phpcsFile->addWarning(
                    $message,
                    $reference->getEndPointer(),
                    "XpBar.Namespaces.MissingImport",
                    [],
                    6
                );
            }
        }
    }//end process()

    /**
     * Parse the current directory so we can compare the filenames against the missing import classes
     *
     * @param PHP_CodeSniffer_File $phpcsFile
     * @return array
     */
    private function getClassFilenames(PHP_CodeSniffer_File $phpcsFile): array
    {
        $currentDirectory = dirname($phpcsFile->path);
        $directory = opendir($currentDirectory);
        $files = [];
        while (($item = readdir($directory)) != false) {
            if ($item == "." || $item == ".." || is_dir($item)) {
                continue;
            }
            preg_match('/^[A-Z].*(?=\.php$)/', $item, $matches);
            if (count($matches) > 0) {
                $files[] = $matches[0];
            }
        }

        return $files;
    }
}//end class
