<?php

class SequenceTest extends \PHPUnit_Framework_TestCase
{
    public function testGet()
    {
        YiiFactoryGirl\Sequence::resetAll();
        $this->assertEquals('hoge', YiiFactoryGirl\Sequence::get('hoge'));
        $this->assertEquals('test_0', YiiFactoryGirl\Sequence::get('test_{{sequence}}'));
        $this->assertEquals('test_1', YiiFactoryGirl\Sequence::get('test_{{sequence}}'));
        $this->assertEquals('test_0', YiiFactoryGirl\Sequence::get('test_{{sequence(:hoge)}}'));
    }
}