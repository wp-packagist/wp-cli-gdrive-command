<?php

namespace test\unit;

use PHPUnit\Framework\TestCase;

class GDriveCommandTests extends TestCase
{

    /** @test */
    public function existGDriveClass()
    {
        $this->assertTrue(class_exists('WP_CLI_Google_Drive'));
    }

    /** @test */
    public function existHelperClass()
    {
        $this->assertTrue(class_exists('WP_CLI_Helper'));
        $this->assertTrue(class_exists('WP_CLI_Util'));
        $this->assertTrue(class_exists('WP_CLI_FileSystem'));
    }

}
