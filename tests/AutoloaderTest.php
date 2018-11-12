<?php

namespace App\Tests;

use App\Autoloader;
use PHPUnit\Framework\TestCase;

class MockAutoloader extends Autoloader
{
    protected $files = [];

    public function setFiles(array $files)
    {
        $this->files = $files;
    }

    protected function requireFile($file)
    {
        return in_array($file, $this->files);
    }
}

class AutoloaderTest extends TestCase
{
    protected $loader;

    protected function setUp()
    {
        $this->loader = new MockAutoloader;

        $this->loader->setFiles([
            '/vendor/foo.bar/src/ClassName.php',
            '/vendor/foo.bar/src/DoomClassName.php',
            '/vendor/foo.bar/tests/ClassNameTest.php',
            '/vendor/foo.bardoom/src/ClassName.php',
            '/vendor/foo.bar.baz.dib/src/ClassName.php',
            '/vendor/foo.bar.baz.dib.zim.gir/src/ClassName.php',
            '/src/lib/ClassName.php',
            '/src/libfoo/ClassFoo.php',
        ]);

        $this->loader->addNamespace(
            'Foo\Bar',
            '/vendor/foo.bar/src'
        );

        $this->loader->addNamespace(
            'Foo\Bar',
            '/vendor/foo.bar/tests'
        );

        $this->loader->addNamespace(
            'Foo\\BarDoom',
            '/vendor/foo.bardoom/src/'
        );

        $this->loader->addNamespace(
            'Foo\Bar\Baz\Dib',
            '/vendor/foo.bar.baz.dib/src/'
        );

        $this->loader->addNamespace(
            'Foo\Bar\Baz\Dib\Zim\Gir',
            '/vendor/foo.bar.baz.dib.zim.gir/src/'
        );

        $this->loader->addClassmap(
            'ClassName',
            '/src/lib/ClassName.php'
        );

        $this->loader->addClassmap(
            'ClassFoo',
            '/src/libfoo/ClassFoo.php'
        );
    }

    public function testExistingFile()
    {
        $actual = $this->loader->load('Foo\Bar\ClassName');
        $expect = '/vendor/foo.bar/src/ClassName.php';
        $this->assertSame($expect, $actual);

        $actual = $this->loader->load('Foo\Bar\ClassNameTest');
        $expect = '/vendor/foo.bar/tests/ClassNameTest.php';
        $this->assertSame($expect, $actual);

        $actual = $this->loader->load('ClassName');
        $expect = '/src/lib/ClassName.php';
        $this->assertSame($expect, $actual);

        $actual = $this->loader->load('ClassFoo');
        $expect = '/src/libfoo/ClassFoo.php';
        $this->assertSame($expect, $actual);
    }

    public function testMissingFile()
    {
        $actual = $this->loader->load('No_Vendor\No_Package\NoClass');
        $this->assertFalse($actual);
    }

    public function testDeepFile()
    {
        $actual = $this->loader->load('Foo\Bar\Baz\Dib\Zim\Gir\ClassName');
        $expect = '/vendor/foo.bar.baz.dib.zim.gir/src/ClassName.php';
        $this->assertSame($expect, $actual);
    }

    public function testConfusion()
    {
        $actual = $this->loader->load('Foo\Bar\DoomClassName');
        $expect = '/vendor/foo.bar/src/DoomClassName.php';
        $this->assertSame($expect, $actual);

        $actual = $this->loader->load('Foo\BarDoom\ClassName');
        $expect = '/vendor/foo.bardoom/src/ClassName.php';
        $this->assertSame($expect, $actual);
    }
}
