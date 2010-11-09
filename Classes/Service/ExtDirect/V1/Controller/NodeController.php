<?php
declare(ENCODING = 'utf-8');
namespace F3\TYPO3\Service\ExtDirect\V1\Controller;

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
 * ExtDirect Controller for managing Nodes
 *
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class NodeController extends \F3\FLOW3\MVC\Controller\ActionController {

	/**
	 * @var string
	 */
	protected $viewObjectNamePattern = 'F3\ExtJS\ExtDirect\View';

	/**
	 * Select special error action
	 *
	 * @return void
	 * @author Robert Lemke <robert@typo3.org>
	 */
	protected function initializeAction() {
		$this->errorMethodName = 'extErrorAction';
	}

	/**
	 * Shows the specified node
	 *
	 * @param \F3\TYPO3CR\Domain\Model\Node $node
	 * @return string View output for the specified node
	 * @author Robert Lemke <robert@typo3.org>
	 * @extdirect
	 */
	public function showAction(\F3\TYPO3CR\Domain\Model\Node $node) {
		$this->view->setConfiguration(
			array(
				'value' => array(
					'data' => array(
						'only' => array('name', 'path', 'identifier', 'properties', 'contentType'),
						'descend' => array('properties' => array())
					)
				)
			)
		);
		$this->view->assign('value', array('data' => $node, 'success' => TRUE));
	}

	/**
	 * Creates a new node
	 *
	 * @param \F3\TYPO3CR\Domain\Model\Node $parentNode
	 * @param array $nodeData
	 * @return void
	 * @author Robert Lemke <robert@typo3.org>
	 * @extdirect
	 */
	public function createAction(\F3\TYPO3CR\Domain\Model\Node $parentNode, array $nodeData) {
		$newNode = $parentNode->createNode($nodeData['nodeName']);
		$newNode->setContentType($nodeData['contentType']);
		foreach ($nodeData['properties'] as $propertyName => $propertyValue) {
			$newNode->setProperty($propertyName, $propertyValue);
		}

		$this->view->setConfiguration(
			array(
				'value' => array(
					'data' => array(
						'only' => array('name', 'path', 'identifier', 'properties', 'contentType'),
						'descend' => array('properties' => array())
					)
				)
			)
		);
		$this->view->assign('value', array('data' => $newNode, 'success' => TRUE));
	}

	/**
	 * Updates the specified node
	 *
	 * @param \F3\TYPO3CR\Domain\Model\Node $node
	 * @return string View output for the specified node
	 * @author Robert Lemke <robert@typo3.org>
	 * @extdirect
	 * @todo the updateAction now implicitly saves the node, as the NodeObjectConverter does not clone the node right now. This is a hack, and needs to be cleaned up.
	 */
	public function updateAction(\F3\TYPO3CR\Domain\Model\Node $node) {
		$this->view->assign('value', array('data' => '', 'success' => TRUE));
	}

	/**
	 * Deletes the specified node and all of its sub nodes
	 *
	 * @param \F3\TYPO3CR\Domain\Model\Node $node
	 * @return string A response string
	 * @author Robert Lemke <robert@typo3.org>
	 * @extdirect
	 */
	public function deleteAction(\F3\TYPO3CR\Domain\Model\Node $node) {
		$node->remove();
		$this->view->assign('value', array('data' => '', 'success' => TRUE));
	}

	/**
	 * Return child nodes of specified node
	 *
	 * @param \F3\TYPO3CR\Domain\Model\Node $node
	 * @param string $contentType
	 * @return string A response string
	 * @author Christian Müller <christian@kitsunet.de>
	 * @extdirect
	 */
	public function getChildNodesAction(\F3\TYPO3CR\Domain\Model\Node $node, $contentType) {
		$childNodes = $node->getChildNodes($contentType);

		$tempConfiguration =	array(
			'value' => array(
				'descend' => array(
					'data' => array(
						'descend' => array(
						)
					)
				)
			)
		);
		$tempData = array();
		foreach ($childNodes as $key => $childNode) {
			$tempConfiguration['value']['descend']['data']['descend'][$key] = array(
				'only' => array('path'),
			);

			$tempData[$key] = array(
				'id' => $childNode->getPath(),
				'text' => $childNode->getProperty('title')

			);

		}
		$this->view->setConfiguration($tempConfiguration);
		$this->view->assign('value',
			array(
				'data' => $tempData,
				'success' => TRUE,
			)
		);
	}
}
?>