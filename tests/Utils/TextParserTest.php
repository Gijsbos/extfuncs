<?php
declare(strict_types=1);

namespace gijsbos\ExtFuncs\Utils;

use PHPUnit\Framework\TestCase;

final class TextParserTest extends TestCase 
{
    public function testEscapeColon()
    {
        $input = "#this is a value:and colon separator;";
        $result = TextParser::escape($input, COLON);
        $expectedResult = "#this is a valueU+003Aand colon separator;";
        $this->assertEquals($expectedResult, $result);
    }

    public function testUnescapeColon()
    {
        $input = "#this is a valueU+003Aand colon separator;";
        $result = TextParser::unescape($input, COLON);
        $expectedResult = "#this is a value:and colon separator;";
        $this->assertEquals($expectedResult, $result);
    }

    public function testEscapeColonSemicolon()
    {
        $input = "#this is a value:and colon separator;";
        $result = TextParser::escape($input, COLON|SEMICOLON);
        $expectedResult = "#this is a valueU+003Aand colon separatorU+003B";
        $this->assertEquals($expectedResult, $result);
    }

    public function testUnescapeColonSemicolon()
    {
        $input = "#this is a valueU+003Aand colon separatorU+003B";
        $result = TextParser::unescape($input, COLON|SEMICOLON);
        $expectedResult = "#this is a value:and colon separator;";
        $this->assertEquals($expectedResult, $result);
    }

    public function testApplyToOpenCloseChar1()
    {
        $input = 'this is my "key:value" test string';
        $result = TextParser::applyToOpenCloseChar($input, '"', '"', function($item) { return TextParser::escape($item, COLON) ;});
        $expectedResult = 'this is my "keyU+003Avalue" test string';
        $this->assertEquals($expectedResult, $result);
    }

    public function testApplyToOpenCloseChar2()
    {
        $input = 'this is my (key:value) test string';
        $result = TextParser::applyToOpenCloseChar($input, '(', ')', function($item) { return TextParser::escape($item, COLON) ;});
        $expectedResult = 'this is my (keyU+003Avalue) test string';
        $this->assertEquals($expectedResult, $result);
    }

    public function testApplyToInnerQuotes()
    {
        $input = 'this is my "key:value" test string';
        $result = TextParser::applyToInnerQuotes($input, function($item) { return TextParser::escape($item, COLON) ;});
        $expectedResult = 'this is my "keyU+003Avalue" test string';
        $this->assertEquals($expectedResult, $result);
    }

    public function testApplyToInnerQuotesTwoQuotes()
    {
        $input = '"key": "value"';
        $result = TextParser::applyToInnerQuotes($input, function($item) { return TextParser::escape($item, COLON) ;});
        $expectedResult = '"key": "value"';
        $this->assertEquals($expectedResult, $result);
    }

    public function testApplyToInnerQuotesStringCallable()
    {
        $input = 'this is my "key:value" test string';
        $result = TextParser::applyToInnerQuotes($input, 'strlen');
        $expectedResult = 'this is my 11 test string';
        $this->assertEquals($expectedResult, $result);
    }

    public function testGetInnerQuote()
    {
        $input = 'this is my "key:value" test string';
        $result = TextParser::getInnerQuote($input);
        $expectedResult = "key:value";
        $this->assertEquals($expectedResult, $result);
    }

    public function testRemoveCommentsInLineSlash()
    {
        $input = "# comment gone";
        $result = TextParser::removeComments($input);
        $expectedResult = "";
        $this->assertEquals($expectedResult, $result);
    }

    public function testRemoveCommentsInLineHash()
    {
        $input = "# comment gone";
        $result = TextParser::removeComments($input);
        $expectedResult = "";
        $this->assertEquals($expectedResult, $result);
    }

    public function testRemoveCommentsInLineBlock()
    {
        $input = "/* comment */";
        $result = TextParser::removeComments($input);
        $expectedResult = "";
        $this->assertEquals($expectedResult, $result);
    }

    public function testRemoveCommentsInCodeSlash()
    {
        $input = '$varset = "this //"; // comment gone';
        $result = TextParser::removeComments($input);
        $expectedResult = '$varset = "this //"; ';
        $this->assertEquals($expectedResult, $result);
    }

    public function testRemoveCommentsInCodeSlashGraveQuote()
    {
        $input = '$varset = `this //`; // comment gone';
        $result = TextParser::removeComments($input);
        $expectedResult = '$varset = `this //`; ';
        $this->assertEquals($expectedResult, $result);
    }

    public function testRemoveCommentsInCodeHash()
    {
        $input = '$varset = "this #"; // comment gone';
        $result = TextParser::removeComments($input);
        $expectedResult = '$varset = "this #"; ';
        $this->assertEquals($expectedResult, $result);
    }

    public function testRemoveCommentsInCodeBlock()
    {
        $input = '$varset = "this /** test */"; /** test */';
        $result = TextParser::removeComments($input);
        $expectedResult = '$varset = "this /** test */"; ';
        $this->assertEquals($expectedResult, $result);
    }

    public function testRemoveCommentsNoBlock()
    {
        $input = <<< EOD
/**
 * Preserve
 */
\$remove = " /* do not */ "; /* remove */
EOD;
        $result = TextParser::removeComments($input, INLINE);
        $expectedResult = <<< EOD
/**
 * Preserve
 */
\$remove = " /* do not */ "; /* remove */
EOD;
        $this->assertEquals($expectedResult, $result);
    }

    public function testRemoveCommentsBlock()
    {
        $input = <<< EOD
/**
 * Preserve
 */
\$remove = " /* do not */ "; /* remove */
EOD;
        $result = TextParser::removeComments($input, BLOCK);
        $expectedResult = <<< EOD



\$remove = " /* do not */ "; 
EOD;
        $this->assertEquals($expectedResult, $result);
    }

    public function testRemoveCommentsLineCountPreserved()
    {
        $input = $fileContent = file_get_contents("./src/Utils/App.php");
        $result = substr_count($newFileContent = TextParser::removeComments($input), "\n");
        $expectedResult = substr_count($fileContent, "\n");
        $this->assertEquals($expectedResult, $result);
    }

    public function testReplaceInCommentsInLineSlash()
    {
        $input = "// ' replace quotes";
        $result = TextParser::replaceInComments($input, "'", ":quot:");
        $expectedResult = "// :quot: replace quotes";
        $this->assertEquals($expectedResult, $result);
    }

    public function testReplaceInCommentsInLineHash()
    {
        $input = "# ' replace quotes";
        $result = TextParser::replaceInComments($input, "'", ":quot:");
        $expectedResult = "# :quot: replace quotes";
        $this->assertEquals($expectedResult, $result);
    }

    public function testReplaceInCommentsInLineBlock()
    {
        $input = "/* ' */";
        $result = TextParser::replaceInComments($input, "'", ":quot:");
        $expectedResult = "/* :quot: */";
        $this->assertEquals($expectedResult, $result);
    }

    public function testReplaceInCommentsInCodeSlash()
    {
        $input = "\$varset = `hi // ' `; // ' ";
        $result = TextParser::replaceInComments($input, "'", ":quot:");
        $expectedResult = "\$varset = `hi // ' `; // :quot: ";
        $this->assertEquals($expectedResult, $result);
    }

    public function testReplaceInCommentsInCodeHash()
    {
        $input = "\$varset = `hi # ' `; # ' ";
        $result = TextParser::replaceInComments($input, "'", ":quot:");
        $expectedResult = "\$varset = `hi # ' `; # :quot: ";
        $this->assertEquals($expectedResult, $result);
    }

    public function testReplaceInCommentsInCodeBlock()
    {
        $input = "\$varset = `hi /* ' */ `; /* ' */ ";
        $result = TextParser::replaceInComments($input, "'", ":quot:");
        $expectedResult = "\$varset = `hi /* ' */ `; /* :quot: */ ";
        $this->assertEquals($expectedResult, $result);
    }

    public function testIsCommentSlash()
    {
        $input = "// Comment";
        $result = TextParser::isComment($input);
        $this->assertTrue($result);
    }

    public function testIsCommentHash()
    {
        $input = "# Comment";
        $result = TextParser::isComment($input);
        $this->assertTrue($result);
    }

    public function testIsCommentBlock()
    {
        $input = "/* */";
        $result = TextParser::isComment($input);
        $this->assertTrue($result);
    }

    public function testIsQuoteDouble()
    {
        $input = '"comment"';
        $result = TextParser::isQuote($input);
        $this->assertTrue($result);
    }

    public function testIsQuoteSingle()
    {
        $input = "'comment'";
        $result = TextParser::isQuote($input);
        $this->assertTrue($result);
    }

    public function testIsQuoteGrave()
    {
        $input = "`comment`";
        $result = TextParser::isQuote($input);
        $this->assertTrue($result);
    }

    public function testIsQuoteFalse()
    {
        $input = "comment`";
        $result = TextParser::isQuote($input);
        $this->assertFalse($result);
    }

    public function testReplaceInQuote()
    {
        $input = "' replace '";
        $result = TextParser::replaceInQuote($input, "replace", "replaced");
        $expectedResult = "' replaced '";
        $this->assertEquals($expectedResult, $result);
    }

    public function testReplaceCommentQuoteComment()
    {
        $input = <<< EOD

\$varset = new DateTime("this is a value"); // These comment's need to be parsed
EOD;
        // Apply to text
        $result = TextParser::replaceCommentQuote($input, function($match) {
            if(TextParser::isComment($match))
                return "";
            else
                return $match;
        });

        $expectedResult = <<< EOD

\$varset = new DateTime("this is a value"); 
EOD;
        $this->assertEquals($expectedResult, $result);

    }

    public function testReplaceCommentQuoteQuote()
    {
        $input = <<< EOD

\$varset = new DateTime("this is a value with ' quote"); // These comment's need to be parsed
EOD;
        // Apply to text
        $result = TextParser::replaceCommentQuote($input, function($match) {
            return TextParser::replaceInQuote($match, "'", ":quot:");
        });

        $expectedResult = <<< EOD

\$varset = new DateTime("this is a value with :quot: quote"); // These comment's need to be parsed
EOD;
        $this->assertEquals($expectedResult, $result);
    }
}