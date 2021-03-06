<?php
/**
 * OpenID_ExtensionTest
 *
 * PHP Version 5.2.0+
 *
 * @uses      PHPUnit_Framework_TestCase
 * @category  Auth
 * @package   OpenID
 * @author    Bill Shupp <hostmaster@shupp.org>
 * @copyright 2009 Bill Shupp
 * @license   http://www.opensource.org/licenses/bsd-license.php FreeBSD
 * @link      http://github.com/shupp/openid
 */

require_once 'OpenID/Extension.php';
require_once 'OpenID/Message.php';
require_once 'OpenID/Extension/Mock.php';
require_once 'OpenID/Extension/MockInvalidAlias.php';
require_once 'OpenID/Extension/MockNoResponseKeys.php';

/**
 * OpenID_ExtensionTest
 *
 * Test class for OpenID_Extension.
 * Generated by PHPUnit on 2009-04-29 at 00:48:40.
 *
 * @uses      PHPUnit_Framework_TestCase
 * @category  Auth
 * @package   OpenID
 * @author    Bill Shupp <hostmaster@shupp.org>
 * @copyright 2009 Bill Shupp
 * @license   http://www.opensource.org/licenses/bsd-license.php FreeBSD
 * @link      http://github.com/shupp/openid
 */
class OpenID_ExtensionTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var    OpenID_Extension
     * @access protected
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->object = new OpenID_Extension_Mock(OpenID_Extension::REQUEST);
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     *
     * @return void
     */
    protected function tearDown()
    {
        unset($this->object);
    }

    /**
     * testConstructorFailure
     *
     * @expectedException OpenID_Extension_Exception
     * @return void
     */
    public function testConstructorFailure()
    {
        $object = new OpenID_Extension_Mock('bogus');
    }

    /**
     * testConstructorWithMessage
     *
     * @return void
     */
    public function testConstructorWithMessage()
    {
        $key     = 'four';
        $value   = '4';
        $message = new OpenID_Message();
        $message->set('openid.ns.mock', 'http://example.com/mock');
        $message->set("openid.mock.$key", $value);
        $this->object = new OpenID_Extension_Mock(OpenID_Extension::REQUEST,
                                                  $message);
        $this->assertSame($value, $this->object->get($key));
    }

    /**
     * testSetAndGet
     *
     * @return void
     */
    public function testSetAndGet()
    {
        $this->object->set('one', 'bar');
        $this->assertSame('bar', $this->object->get('one'));
        $this->assertNull($this->object->get('fubar'));
    }

    /**
     * testSetFailure
     *
     * @expectedException OpenID_Extension_Exception
     * @return void
     */
    public function testSetFailure()
    {
        $this->object->set('bogus', 'bar');
    }

    /**
     * testToMessage
     *
     * @return void
     */
    public function testToMessage()
    {
        $this->object->set('one', 'foo');
        $message = new OpenID_Message;
        $this->assertNotSame('foo', $message->get('openid.mock.one'));
        $this->object->toMessage($message);
        $this->assertSame('foo', $message->get('openid.mock.one'));
    }

    /**
     * testFromMessageResponse
     *
     * @return void
     */
    public function testFromMessageResponse()
    {
        $this->object = new OpenID_Extension_Mock(OpenID_Extension::RESPONSE);
        $this->object->set('four', 'foo');
        $message = new OpenID_Message;
        $this->assertNotSame('foo', $message->get('openid.mock.four'));
        $this->object->toMessage($message);
        $values = $this->object->fromMessageResponse($message);
        $this->assertSame($values['four'], $message->get('openid.mock.four'));
    }

    /**
     * testToMessageFailureInvalidAlias
     *
     * @expectedException OpenID_Extension_Exception
     * @return void
     */
    public function testToMessageFailureInvalidAlias()
    {
        $extension = new OpenID_Extension_MockInvalidAlias(
            OpenID_Extension::RESPONSE
        );

        $message = new OpenID_Message;
        $extension->toMessage($message);
    }

    /**
     * testToMessageFailureAliasCollide
     *
     * @expectedException OpenID_Extension_Exception
     * @return void
     */
    public function testToMessageFailureAliasCollide()
    {
        $extension = new OpenID_Extension_Mock(OpenID_Extension::RESPONSE);
        $message   = new OpenID_Message;
        $message->set('openid.ns.mock', 'foo');
        $extension->toMessage($message);
    }

    /**
     * testFromMessageReponseFailure
     *
     * @return void
     */
    public function testFromMessageReponseFailure()
    {
        $extension = new OpenID_Extension_Mock(OpenID_Extension::RESPONSE);
        $message   = new OpenID_Message;
        // Make sure we iterate over the message at least once
        $message->set('openid.ns.foo', 'bar');
        $response = $extension->fromMessageResponse($message);
        $this->assertSame(0, count($response));
    }

    /**
     * testFromMessageReponseNoResponseKeys
     *
     * @return void
     */
    public function testFromMessageReponseNoResponseKeys()
    {
        $extension = new OpenID_Extension_MockNoResponseKeys(
            OpenID_Extension::RESPONSE
        );

        $message = new OpenID_Message;
        // Make sure we iterate over the message at least once
        $message->set('openid.ns.mock', 'http://example.com/mock');
        $message->set('openid.mock.foo', 'bar');
        $response = $extension->fromMessageResponse($message);
        $this->assertSame(1, count($response));
    }

    /**
     * testGetNameSpace
     *
     * @return void
     */
    public function testGetNameSpace()
    {
        $this->assertSame($this->object->getNamespace(), 'http://example.com/mock');
    }
}
?>
