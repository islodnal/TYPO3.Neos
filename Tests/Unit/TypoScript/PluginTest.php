<?php
declare(ENCODING = 'utf-8');
namespace F3\TYPO3\Tests\Unit\TypoScript;

/*                                                                        *
 * This script belongs to the FLOW3 package "TYPO3".                      *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU General Public License as published by the Free   *
 * Software Foundation, either version 3 of the License, or (at your      *
 * option) any later version.                                             *
 *                                                                        *
 * This script is distributed in the hope that it will be useful, but     *
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHAN-    *
 * TABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General      *
 * Public License for more details.                                       *
 *                                                                        *
 * You should have received a copy of the GNU General Public License      *
 * along with the script.                                                 *
 * If not, see http://www.gnu.org/licenses/gpl.html                       *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

/**
 * Testcase for the Plugin TypoScript object
 *
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class PluginTest extends \F3\FLOW3\Tests\UnitTestCase {

	/**
	 * @var \F3\TYPO3\TypoScript\Plugin
	 */
	protected $plugin;

	/**
	 * @var \F3\FLOW3\MVC\Controller\ControllerContext
	 */
	protected $mockControllerContext;

	/**
	 * @var \F3\TypoScript\RenderingContext
	 */
	protected $mockRenderingContext;

	/**
	 * @var \F3\FLOW3\MVC\Web\Request
	 */
	protected $mockRequest;

	/**
	 * @var \F3\FLOW3\MVC\Web\Response
	 */
	protected $mockResponse;

	/**
	 * @var \F3\FLOW3\Object\ObjectManagerInterface
	 */
	protected $mockObjectManager;

	/**
	 * @var \F3\FLOW3\MVC\Dispatcher
	 */
	protected $mockDispatcher;

	/**
	 * @var \F3\FLOW3\MVC\Web\SubRequestBuilder
	 */
	protected $mockSubRequestBuilder;

	/**
	 * @return void
	 */
	public function setUp() {
		$this->mockSubRequestBuilder = $this->getMock('F3\FLOW3\MVC\Web\SubRequestBuilder');
		$this->mockObjectManager = $this->getMock('F3\FLOW3\Object\ObjectManagerInterface');
		$this->mockDispatcher = $this->getMock('F3\FLOW3\MVC\Dispatcher', array(), array(), '', FALSE);
		$this->mockRequest = $this->getMock('F3\FLOW3\MVC\Web\Request');
		$this->mockResponse = $this->getMock('F3\FLOW3\MVC\Web\Response');
		$this->mockControllerContext = $this->getMock('F3\FLOW3\MVC\Controller\ControllerContext', array(), array(), '', FALSE);
		$this->mockControllerContext->expects($this->any())->method('getRequest')->will($this->returnValue($this->mockRequest));
		$this->mockControllerContext->expects($this->any())->method('getResponse')->will($this->returnValue($this->mockResponse));
		$this->mockRenderingContext = $this->getMock('F3\TypoScript\RenderingContext');
		$this->mockRenderingContext->expects($this->any())->method('getControllerContext')->will($this->returnValue($this->mockControllerContext));
		$this->plugin = $this->getAccessibleMock('F3\TYPO3\TypoScript\Plugin', array('getPluginNamespace'));
		$this->plugin->expects($this->any())->method('getPluginNamespace')->will($this->returnValue('f3_plugin_namespace'));
		$this->plugin->setRenderingContext($this->mockRenderingContext);
		$this->plugin->_set('subRequestBuilder', $this->mockSubRequestBuilder);
		$this->plugin->_set('objectManager', $this->mockObjectManager);
		$this->plugin->_set('dispatcher', $this->mockDispatcher);
	}

	/**
	 * @test
	 * @expectedException \InvalidArgumentException
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function setRenderingContextThrowsExceptionIfRenderingContextIsNoTypoScriptRenderingContext() {
		$mockRenderingContext = $this->getMock('F3\Fluid\Core\Rendering\RenderingContextInterface');
		$this->plugin->setRenderingContext($mockRenderingContext);
	}

	/**
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function renderCreatesAndDispatchesSubRequestAndReturnItsContent() {
		$expectedResult = 'pluginResponse content';
		$mockPluginRequest = $this->getMock('F3\FLOW3\MVC\Web\SubRequest', array(), array(), '', FALSE);
		$mockPluginResponse = $this->getMock('F3\FLOW3\MVC\Web\SubResponse', array(), array(), '', FALSE);
		$mockPluginResponse->expects($this->atLeastOnce())->method('getContent')->will($this->returnValue($expectedResult));
		$this->mockObjectManager->expects($this->once())->method('create')->with('F3\FLOW3\MVC\Web\SubResponse', $this->mockResponse)->will($this->returnValue($mockPluginResponse));
		$this->mockSubRequestBuilder->expects($this->once())->method('build')->with($this->mockRequest, 'f3_plugin_namespace')->will($this->returnValue($mockPluginRequest));
		$this->mockDispatcher->expects($this->once())->method('dispatch')->with($mockPluginRequest, $mockPluginResponse);

		$actualResult = $this->plugin->render();
		$this->assertEquals($expectedResult, $actualResult);
	}

	/**
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function renderSetsRequestPackageKey() {
		$mockPluginRequest = $this->getMock('F3\FLOW3\MVC\Web\SubRequest', array(), array(), '', FALSE);
		$mockPluginResponse = $this->getMock('F3\FLOW3\MVC\Web\SubResponse', array(), array(), '', FALSE);
		$this->mockObjectManager->expects($this->once())->method('create')->with('F3\FLOW3\MVC\Web\SubResponse', $this->mockResponse)->will($this->returnValue($mockPluginResponse));
		$this->mockSubRequestBuilder->expects($this->once())->method('build')->with($this->mockRequest, 'f3_plugin_namespace')->will($this->returnValue($mockPluginRequest));

		$this->plugin->setPackage('SomePackageKey');
		$mockPluginRequest->expects($this->once())->method('setControllerPackageKey')->with('SomePackageKey');

		$this->plugin->render();
	}

	/**
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function renderDoesNotSetRequestPackageKeyIfItIsAlreadySet() {
		$mockPluginRequest = $this->getMock('F3\FLOW3\MVC\Web\SubRequest', array(), array(), '', FALSE);
		$mockPluginResponse = $this->getMock('F3\FLOW3\MVC\Web\SubResponse', array(), array(), '', FALSE);
		$this->mockObjectManager->expects($this->once())->method('create')->with('F3\FLOW3\MVC\Web\SubResponse', $this->mockResponse)->will($this->returnValue($mockPluginResponse));
		$this->mockSubRequestBuilder->expects($this->once())->method('build')->with($this->mockRequest, 'f3_plugin_namespace')->will($this->returnValue($mockPluginRequest));

		$mockPluginRequest->expects($this->once())->method('getControllerPackageKey')->will($this->returnValue('SomePackage'));
		$mockPluginRequest->expects($this->never())->method('setControllerPackageKey');

		$this->plugin->render();
	}

	/**
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function renderSetsRequestSubpackageKey() {
		$mockPluginRequest = $this->getMock('F3\FLOW3\MVC\Web\SubRequest', array(), array(), '', FALSE);
		$mockPluginResponse = $this->getMock('F3\FLOW3\MVC\Web\SubResponse', array(), array(), '', FALSE);
		$this->mockObjectManager->expects($this->once())->method('create')->with('F3\FLOW3\MVC\Web\SubResponse', $this->mockResponse)->will($this->returnValue($mockPluginResponse));
		$this->mockSubRequestBuilder->expects($this->once())->method('build')->with($this->mockRequest, 'f3_plugin_namespace')->will($this->returnValue($mockPluginRequest));

		$this->plugin->setSubpackage('SomeSubpackageKey');
		$mockPluginRequest->expects($this->once())->method('setControllerSubpackageKey')->with('SomeSubpackageKey');

		$this->plugin->render();
	}

	/**
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function renderDoesNotSetRequestSubpackageKeyIfItIsAlreadySet() {
		$mockPluginRequest = $this->getMock('F3\FLOW3\MVC\Web\SubRequest', array(), array(), '', FALSE);
		$mockPluginResponse = $this->getMock('F3\FLOW3\MVC\Web\SubResponse', array(), array(), '', FALSE);
		$this->mockObjectManager->expects($this->once())->method('create')->with('F3\FLOW3\MVC\Web\SubResponse', $this->mockResponse)->will($this->returnValue($mockPluginResponse));
		$this->mockSubRequestBuilder->expects($this->once())->method('build')->with($this->mockRequest, 'f3_plugin_namespace')->will($this->returnValue($mockPluginRequest));

		$mockPluginRequest->expects($this->once())->method('getControllerSubpackageKey')->will($this->returnValue('SomeSubpackage'));
		$mockPluginRequest->expects($this->never())->method('setControllerSubpackageKey');

		$this->plugin->render();
	}

	/**
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function renderSetsRequestControllerName() {
		$mockPluginRequest = $this->getMock('F3\FLOW3\MVC\Web\SubRequest', array(), array(), '', FALSE);
		$mockPluginResponse = $this->getMock('F3\FLOW3\MVC\Web\SubResponse', array(), array(), '', FALSE);
		$this->mockObjectManager->expects($this->once())->method('create')->with('F3\FLOW3\MVC\Web\SubResponse', $this->mockResponse)->will($this->returnValue($mockPluginResponse));
		$this->mockSubRequestBuilder->expects($this->once())->method('build')->with($this->mockRequest, 'f3_plugin_namespace')->will($this->returnValue($mockPluginRequest));

		$this->plugin->setController('SomeControllerName');
		$mockPluginRequest->expects($this->once())->method('setControllerName')->with('SomeControllerName');

		$this->plugin->render();
	}

	/**
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function renderDoesNotSetRequestControllerNameIfItIsAlreadySet() {
		$mockPluginRequest = $this->getMock('F3\FLOW3\MVC\Web\SubRequest', array(), array(), '', FALSE);
		$mockPluginResponse = $this->getMock('F3\FLOW3\MVC\Web\SubResponse', array(), array(), '', FALSE);
		$this->mockObjectManager->expects($this->once())->method('create')->with('F3\FLOW3\MVC\Web\SubResponse', $this->mockResponse)->will($this->returnValue($mockPluginResponse));
		$this->mockSubRequestBuilder->expects($this->once())->method('build')->with($this->mockRequest, 'f3_plugin_namespace')->will($this->returnValue($mockPluginRequest));

		$mockPluginRequest->expects($this->once())->method('getControllerName')->will($this->returnValue('SomeController'));
		$mockPluginRequest->expects($this->never())->method('setControllerName');

		$this->plugin->render();
	}

	/**
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function renderSetsRequestControllerActionName() {
		$mockPluginRequest = $this->getMock('F3\FLOW3\MVC\Web\SubRequest', array(), array(), '', FALSE);
		$mockPluginResponse = $this->getMock('F3\FLOW3\MVC\Web\SubResponse', array(), array(), '', FALSE);
		$this->mockObjectManager->expects($this->once())->method('create')->with('F3\FLOW3\MVC\Web\SubResponse', $this->mockResponse)->will($this->returnValue($mockPluginResponse));
		$this->mockSubRequestBuilder->expects($this->once())->method('build')->with($this->mockRequest, 'f3_plugin_namespace')->will($this->returnValue($mockPluginRequest));

		$this->plugin->setAction('SomeControllerActionName');
		$mockPluginRequest->expects($this->once())->method('setControllerActionName')->with('SomeControllerActionName');

		$this->plugin->render();
	}

	/**
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function renderDoesNotSetRequestControllerActionNameIfItIsAlreadySet() {
		$mockPluginRequest = $this->getMock('F3\FLOW3\MVC\Web\SubRequest', array(), array(), '', FALSE);
		$mockPluginResponse = $this->getMock('F3\FLOW3\MVC\Web\SubResponse', array(), array(), '', FALSE);
		$this->mockObjectManager->expects($this->once())->method('create')->with('F3\FLOW3\MVC\Web\SubResponse', $this->mockResponse)->will($this->returnValue($mockPluginResponse));
		$this->mockSubRequestBuilder->expects($this->once())->method('build')->with($this->mockRequest, 'f3_plugin_namespace')->will($this->returnValue($mockPluginRequest));

		$mockPluginRequest->expects($this->once())->method('getControllerActionName')->will($this->returnValue('SomeAction'));
		$mockPluginRequest->expects($this->never())->method('setControllerActionName');

		$this->plugin->render();
	}
}
?>