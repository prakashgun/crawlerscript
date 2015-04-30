<?php

use Sgun\Web;

/**
 *  UrlTest
 *
 * @author prakash
 */
class UrlTest extends PHPUnit_Framework_TestCase {

    public function testSettingValues() {
        $crawler = new Web\Crawler();
        $this->assertTrue(true, $crawler->setDepth(4));
    }

}
