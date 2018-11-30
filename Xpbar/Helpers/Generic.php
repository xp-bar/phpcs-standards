<?php
namespace XpBar\Helpers;

use SlevomatCodingStandard\Helpers\TokenHelper as SlevomatTokenHelper;

trait Generic
{
    /**
     * Get the classname or primitive of a namespaced class typehint
     *
     * @param string $class
     */
    private static function getSimpleClassNameOrPrimitive(string $class): ?string
    {
        preg_match('/[\/\\A-z]*[^\w\| ]\K[\| A-z]*.*/', $class, $classMatch);
        $possible = explode("|", $class);
        if (count($possible) > 1) {
            $filtered = array_filter($possible, function ($type) {
                return strtolower($type) != "null";
            });
            if (count($filtered) > 1) {
                // too many possible types, return null to bubble up to where pointer is
                return null;
            }
            return $filtered[0];
        }

        if (count($classMatch) > 0 && $classMatch[0] != null) {
            return $possible[0];
        }
        return $class;
    }

    /**
     * Parse out each @tag in a doc block
     *
     * @param int $tagPointer
     */
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

        $commentPointer = SlevomatTokenHelper::findNextExcluding(
            $this->phpcsFile,
            T_DOC_COMMENT_WHITESPACE,
            $tagPointer + 1
        );

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
}
