<?php
declare(strict_types=1);

namespace gijsbos\ExtFuncs\Utils;

use PHPUnit\Framework\TestCase;

final class ResolveCallableTest extends TestCase
{
    public function testResolveClass()
    {
        $this->assertSame("DateTime", resolve_class("DateTime"));
    }

    public function testResolveClassNamespace()
    {
        $this->assertSame("gijsbos\ExtFuncs\Utils\Functions", resolve_class("Functions", "gijsbos\ExtFuncs\Utils"));
    }

    public function testResolveClassNotFound()
    {
        $this->assertFalse(resolve_class("NoSuchClass", null, false));
    }

    public function testResolveClassNotFoundThrows()
    {
        $this->expectException(\Exception::class);
        resolve_class("NoSuchClass");
    }

    public function testResolveClassNotFoundThrowsCustom()
    {
        $this->expectException(\InvalidArgumentException::class);
        resolve_class("NoSuchClass", null, new \InvalidArgumentException());
    }

    # @bugfix return type did not allow strings, resolving a function threw a TypeError
    public function testResolveCallableFunction()
    {
        $this->assertSame("strtoupper", resolve_callable("strtoupper"));
    }

    public function testResolveCallableFunctionNotFound()
    {
        $this->assertFalse(resolve_callable("no_such_function", null, null, false));
    }

    public function testResolveCallableStaticMethod()
    {
        $this->assertSame(["gijsbos\ExtFuncs\Utils\Functions", "executable"], resolve_callable("Functions::executable", null, "gijsbos\ExtFuncs\Utils"));
    }

    public function testResolveCallableInstanceMethod()
    {
        $this->assertSame(["DateTime", "format"], resolve_callable("DateTime->format"));
    }

    public function testResolveCallableClassArgument()
    {
        $this->assertSame(["DateTime", "format"], resolve_callable("format", "DateTime"));
    }

    public function testResolveCallableMethodNotFound()
    {
        $this->assertFalse(resolve_callable("DateTime::nope", null, null, false));
    }

    # @bugfix unresolvable class with throws=false passed false to method_exists and threw a TypeError
    public function testResolveCallableClassNotFound()
    {
        $this->assertFalse(resolve_callable("NoSuchClass::method", null, null, false));
    }
}
