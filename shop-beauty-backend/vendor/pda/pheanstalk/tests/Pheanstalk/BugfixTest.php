<?php

namespace Pheanstalk;

/**
 * Tests for reported/discovered issues & bugs which don't fall into
 * an existing category of tests.
 * Does not depend on a running beanstalkd server.
 * @see http://github.com/pda/pheanstalk/issues
 *
 * @author Paul Annesley
 * @package Pheanstalk
 * @license http://www.opensource.org/licenses/mit-license.php
 */
class BugfixTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Issue: Stats() Command fails if Version isn't set
     * @see http://github.com/pda/pheanstalk/issues/12
     */
    public function testIssue12YamlParsingMissingValue()
    {
        // missing version number
        $data = "---\r\npid: 123\r\nversion: \r\nkey: value\r\n";

        $command = new Command\StatsCommand();

        $this->_assertResponse(
            $command->getResponseParser()->parseResponse('OK '.strlen($data), $data),
            Response::RESPONSE_OK,
            array('pid' => '123', 'version' => '', 'key' => 'value')
        );
    }

    // ----------------------------------------
    // private

    /**
     * @param Response $response
     * @param string $expectName
     * @param array $data
     */
    private function _assertResponse($response, $expectName, $data = array())
    {
        $this->assertEquals($response->getResponseName(), $expectName);
        $this->assertEquals($response->getArrayCopy(), $data);
    }
}
