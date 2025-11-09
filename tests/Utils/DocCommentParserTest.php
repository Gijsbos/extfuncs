<?php
declare(strict_types=1);

namespace gijsbos\ExtFuncs\Utils;

use ReflectionMethod;
use PHPUnit\Framework\TestCase;

define("DOC_COMMENT_PARSER_TEST", "test value");

final class DocCommentParserTest extends TestCase 
{
    const CONSTANT = 'test-value';

    /**
     * testParse
     * @Prop1: 'string value'
     * @Prop2: "string value"
     * @Prop3: `string value`
     * @Prop4: true
     * @Prop5: false
     * @Prop6: DOC_COMMENT_PARSER_TEST
     * @Prop7: [key1 => 'array delimiters ,=> test', key2 => true, key3 => [key4 => 'sub-array-@'], key5 => quoteless string]
     */
    public function testParse()
    {
        $result = DocCommentParser::parse(new ReflectionMethod($this, "testParse"));
        $expectedResult = [
            "Prop1" => "string value",
            "Prop2" => "string value",
            "Prop3" => "string value",
            "Prop4" => true,
            "Prop5" => false,
            "Prop6" => "test value",
            "Prop7" => [
                "key1" => "array delimiters ,=> test",
                "key2" => true,
                "key3" => [
                    "key4" => "sub-array-@"
                ],
                "key5" => "quoteless string"
            ]
        ];
        
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * testParseMultiLineArray1
     * @Prop1: 
        [
            key1? => [
                key2 => "hi"
            ]
        ]
     */
    public function testParseMultiLineArray1()
    {
        $result = DocCommentParser::parse(new ReflectionMethod($this, "testParseMultiLineArray1"));
        $expectedResult = [
            "Prop1" => [
                "key1?" => [
                    "key2" => "hi"
                ]
            ],
        ];
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * testParseMultiLineArray2
     * @Prop1: 
        [
            company => [companyId],
            campaigns => [
                campaignId,
                company => [
                    name,
                ],
                members,
            ]
        ]
     */
    public function testParseMultiLineArray2()
    {
        $result = DocCommentParser::parse(new ReflectionMethod($this, "testParseMultiLineArray2"));
        $expectedResult = [
            "Prop1" => [
                "company" => [
                    "companyId"
                ],
                "campaigns" => [
                    "campaignId",
                    "company" => ["name"],
                    "members"
                ],
            ]
        ];
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * testParseMultiLineArrayWithEmptyArray
     * @Prop1: 
        [
            company => [companyId],
            campaigns => [

            ]
        ]
     */
    public function testParseMultiLineArrayWithEmptyArray()
    {
        $result = DocCommentParser::parse(new ReflectionMethod($this, "testParseMultiLineArrayWithEmptyArray"));
        $expectedResult = [
            "Prop1" => [
                "company" => [
                    "companyId"
                ],
                "campaigns" => [

                ],
            ]
        ];
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * testParseSpaceDelimiter
     * @Prop1 random(INT, 1, 10)
     */
    public function testParseSpaceDelimiter()
    {
        $result = DocCommentParser::parse(new ReflectionMethod($this, "testParseSpaceDelimiter"), [
            "propertyAppendix" => ' '
        ]);
        $expectedResult = [
            "Prop1" => "random(INT, 1, 10)",
        ];
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * testParseSpaceDelimiter
     * @Prop1 "/^(?=.*[\p{L}])(?=.*\d)(?=.*[~!^(){}<>%@#&*+=_-])[^\s$`,.\/\\;:\'\"|]{4,32}$/i"
     */
    public function testParseEcapeUnescapeDelimiterSymbolAt()
    {
        $result = DocCommentParser::parse(new ReflectionMethod($this, "testParseEcapeUnescapeDelimiterSymbolAt"), [
            "propertyAppendix" => ' '
        ]);

        $expectedResult = [
            "Prop1" => "/^(?=.*[\p{L}])(?=.*\d)(?=.*[~!^(){}<>%@#&*+=_-])[^\s$`,.\/\\\\;:\'\\\"|]{4,32}$/i",
        ];
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * testParseShortArrayTrailingComma
     * @Prop1 [userId,address=>[addressId,street,]]
     */
    public function testParseShortArrayTrailingComma()
    {
        $result = DocCommentParser::parse(new ReflectionMethod($this, "testParseShortArrayTrailingComma"), [
            "propertyAppendix" => ' '
        ]);
        $expectedResult = [
            "Prop1" => [
                "userId",
                "address" => [
                    "addressId",
                    "street",
                    ""
                ]
            ],
        ];
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * testParseShortArray
     * @Prop1 [userId,name,address=>[addressId,city,street]]
     */
    public function testParseShortArray()
    {
        $result = DocCommentParser::parse(new ReflectionMethod($this, "testParseShortArray"), [
            "propertyAppendix" => ' '
        ]);
        $expectedResult = [
            "Prop1" => [
                "userId",
                "name",
                "address" => [
                    "addressId",
                    "city",
                    "street"
                ]
            ],
        ];
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * testParseSkipProperty
     * @Prop1: [userId,name,address=>[addressId,city,street]]
     */
    public function testParseSkipProperty()
    {
        $result = DocCommentParser::parse(new ReflectionMethod($this, "testParseSkipProperty"), [
            "skipProperties" => ["Prop1"]
        ]);
        $expectedResult = [
            "Prop1" => "[userId,name,address=>[addressId,city,street]]",
        ];
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * testParseUseRef
     * @Prop1: {\gijsbos\ExtFuncs\Utils\DocCommentParserTest::testParse::Prop1}
     * @Prop2: [key1 => {\gijsbos\ExtFuncs\Utils\DocCommentParserTest::testParse::Prop1}, key2 => {\gijsbos\ExtFuncs\Utils\DocCommentParserTest::testParse::Prop4}]
     */
    public function testParseUseRef()
    {
        $result = DocCommentParser::parse(new ReflectionMethod($this, "testParseUseRef"));

        $expectedResult = [
            "Prop1" => "string value",
            "Prop2" => [
                "key1" => "string value",
                "key2" => true,
            ]
        ];
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * testParseUseRef
     * @Prop1: {\gijsbos\ExtFuncs\Utils\DocCommentParserTest::CONSTANT}
     */
    public function testParseUsePropertyConstant()
    {
        $result = DocCommentParser::parse(new ReflectionMethod($this, "testParseUsePropertyConstant"));
        $expectedResult = [
            "Prop1" => "test-value",
        ];
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * testMergeArrayProperties
     * @Prop1: [userId,name,address=>[addressId,city,street]]
     * @Prop2: {Prop1} + [extra]
     */
    public function testMergeArrayProperties()
    {
        $result = DocCommentParser::parse(new ReflectionMethod($this, "testMergeArrayProperties"));
        $expectedResult = [
            "Prop1" => [
                "userId",
                "name",
                "address" => [
                    "addressId",
                    "city",
                    "street"
                ]
            ],
            "Prop2" => [
                "userId",
                "name",
                "address" => [
                    "addressId",
                    "city",
                    "street"
                ],
                "extra"
            ],
        ];
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * testMergeArrayProperties2
     * @Prop1: [userId,name,address=>[addressId,city,street]]
     * @Prop2: {testMergeArrayProperties::Prop2} + [more]
     */
    public function testMergeArrayProperties2()
    {
        $result = DocCommentParser::parse(new ReflectionMethod($this, "testMergeArrayProperties2"));
        $expectedResult = [
            "Prop1" => [
                "userId",
                "name",
                "address" => [
                    "addressId",
                    "city",
                    "street"
                ]
            ],
            "Prop2" => [
                "userId",
                "name",
                "address" => [
                    "addressId",
                    "city",
                    "street"
                ],
                "extra",
                "more"
            ],
        ];
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * testMergeArrayProperties3
     * @Prop1: [userId,name,address=>[addressId,city,street]]
     * @Prop2: {gijsbos\ExtFuncs\Utils\DocCommentParserTest::testMergeArrayProperties2::Prop2} + [again]
     */
    public function testMergeArrayProperties3()
    {
        $result = DocCommentParser::parse(new ReflectionMethod($this, "testMergeArrayProperties3"));
        $expectedResult = [
            "Prop1" => [
                "userId",
                "name",
                "address" => [
                    "addressId",
                    "city",
                    "street"
                ]
            ],
            "Prop2" => [
                "userId",
                "name",
                "address" => [
                    "addressId",
                    "city",
                    "street"
                ],
                "extra",
                "more",
                "again"
            ],
        ];
        $this->assertEquals($expectedResult, $result);
    }
}