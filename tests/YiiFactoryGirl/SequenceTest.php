<?php

use YiiFactoryGirl\Sequence;

class SequenceTest extends \PHPUnit_Framework_TestCase
{
    public function testGet()
    {
        Sequence::resetAll();
        $this->assertEquals('hoge', Sequence::get('hoge'));
        $this->assertEquals('test_0', Sequence::get('test_{{sequence}}'));
        $this->assertEquals('test_1', Sequence::get('test_{{sequence}}'));
        $this->assertEquals('test_0', Sequence::get('test_{{sequence(:hoge)}}'));
    }
}