<?php
/**
 * OpenID_AssociationTest
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

require_once 'OpenID/Association.php';
require_once 'OpenID/Message.php';

/**
 * OpenID_AssociationTest
 *
 * Test class for OpenID_Association.
 * Generated by PHPUnit on 2009-04-28 at 21:47:58.
 *
 * @uses      PHPUnit_Framework_TestCase
 * @category  Auth
 * @package   OpenID
 * @author    Bill Shupp <hostmaster@shupp.org>
 * @copyright 2009 Bill Shupp
 * @license   http://www.opensource.org/licenses/bsd-license.php FreeBSD
 * @link      http://github.com/shupp/openid
 */
class OpenID_AssociationTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var    OpenID_Association
     * @access protected
     */
    protected $object;

    /**
     * List of parameters to use
     *
     * @var array
     */
    protected $params = array();

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->params = array(
            'uri'          => 'http://example.com',
            'expiresIn'    => 600,
            'created'      => 1240980848,
            'assocType'    => 'HMAC-SHA256',
            'assocHandle'  => 'foobar{}',
            'sharedSecret' => '12345qwerty'
        );

        $this->object = new OpenID_Association($this->params);
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
     * testMissingParam
     *
     * @expectedException OpenID_Association_Exception
     * @return void
     */
    public function testMissingParam()
    {
        unset($this->params['uri']);
        $object = new OpenID_Association($this->params);
    }

    /**
     * testInvalidURI
     *
     * @expectedException OpenID_Association_Exception
     * @return void
     */
    public function testInvalidURI()
    {
        $this->params['uri'] = 'htttp:///foobar.^';

        $object = new OpenID_Association($this->params);
    }

    /**
     * testInvalidAssocType
     *
     * @expectedException OpenID_Association_Exception
     * @return void
     */
    public function testInvalidAssocType()
    {
        $this->params['assocType'] = 'HMAC-SHA2112';

        $object = new OpenID_Association($this->params);
    }

    /**
     * testMagicGet
     *
     * @return void
     */
    public function testMagicGet()
    {
        $this->assertSame('http://example.com', $this->object->uri);
    }

    /**
     * testGetAlgorithm
     *
     * @return void
     */
    public function testGetAlgorithm()
    {
        $this->assertSame('SHA256', $this->object->getAlgorithm());
    }

    /**
     * testSignatures
     *
     * @return void
     */
    public function testSignatures()
    {
        $message = new OpenID_Message;
        $message->set('openid.foo', 'bar');
        $message->set('openid.bar', 'foo');
        $message->set('openid.op_endpoint', 'http://example.com');
        $message->set('openid.assoc_handle', $this->object->assocHandle);

        $this->object->signMessage($message);
        $this->assertTrue($message->get('openid.sig') !== null);
        $this->assertTrue($message->get('openid.signed') !== null);
        $this->assertTrue($this->object->checkMessageSignature($message));
    }

    /**
     * testURLsDoNotMatch
     *
     * @expectedException OpenID_Association_Exception
     * @return void
     */
    public function testURLsDoNotMatch()
    {
        $message = new OpenID_Message;
        $message->set('openid.foo', 'bar');
        $message->set('openid.bar', 'foo');
        $message->set('openid.assoc_handle', $this->object->assocHandle);

        $this->object->signMessage($message);
        $this->assertTrue($this->object->checkMessageSignature($message));
    }

    /**
     * testConflictingSignatures
     *
     * @expectedException OpenID_Association_Exception
     * @return void
     */
    public function testConflictingSignatures()
    {
        $message = new OpenID_Message;
        $message->set('openid.assoc_handle', 'foobar');

        $this->object->checkMessageSignature($message);
    }

    /**
     * testConflictingSignatures2
     *
     * @expectedException OpenID_Association_Exception
     * @return void
     */
    public function testConflictingSignatures2()
    {
        $message = new OpenID_Message;
        $message->set('openid.assoc_handle', 'foobar');

        $this->object->signMessage($message);
    }

    /**
     * testAlreadySigned
     *
     * @expectedException OpenID_Association_Exception
     * @return void
     */
    public function testAlreadySigned()
    {
        $message = new OpenID_Message;
        $message->set('openid.assoc_handle', 'foobar');
        $message->set('openid.sig', '1234');

        $this->object->signMessage($message);
    }

    /**
     * testAlreadySigned2
     *
     * @expectedException OpenID_Association_Exception
     * @return void
     */
    public function testAlreadySigned2()
    {
        $message = new OpenID_Message;
        $message->set('openid.assoc_handle', 'foobar');
        $message->set('openid.signed', '1234');

        $this->object->signMessage($message);
    }

    /**
     * testNoSignedKeys
     *
     * @return void
     */
    public function testNoSignedKeys()
    {
        $message = new OpenID_Message;
        $message->set('openid.assoc_handle', $this->object->assocHandle);
        $message->set('openid.op_endpoint', 'http://example.com');

        $this->assertFalse($this->object->checkMessageSignature($message));
    }
}
?>
