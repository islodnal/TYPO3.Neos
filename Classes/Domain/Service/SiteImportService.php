<?php
declare(ENCODING = 'utf-8');
namespace F3\TYPO3\Domain\Service;

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
 * The Site Import Service
 *
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 * @scope prototype
 * @api
 */
class SiteImportService {

	/**
	 * @inject
	 * @var F3\FLOW3\Object\ObjectManagerInterface
	 */
	protected $objectManager;

	/**
	 * @inject
	 * @var F3\FLOW3\Package\PackageManagerInterface
	 */
	protected $packageManager;

	/**
	 * @inject
	 * @var F3\TYPO3\Domain\Repository\Structure\SiteRepository
	 */
	protected $siteRepository;

	/**
	 * @inject
	 * @var F3\TYPO3\Domain\Repository\Structure\ContentNodeRepository
	 */
	protected $contentNodeRepository;

	/**
	 * @inject
	 * @var F3\TYPO3\Domain\Repository\Configuration\DomainRepository
	 */
	protected $domainRepository;

	/**
	 * Checks for the presence of Content.xml in the given package and imports
	 * it if found.
	 *
	 * @param string $packageKey
	 * @return void
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 * @author Christian Müller <christian@kitsunet.de>
	 */
	public function importPackage($packageKey) {
		if (!$this->packageManager->isPackageActive($packageKey)) {
			throw new \F3\FLOW3\Exception('Error: Package "' . $packageKey . '" is not active.');
		} elseif (!file_exists('resource://' . $packageKey . '/Private/Content/Sites.xml')) {
			throw new \F3\FLOW3\Exception('Error: No content found in package "' . $packageKey . '".');
		} else {
			$this->siteRepository->removeAll();
			$this->contentNodeRepository->removeAll();
			$this->domainRepository->removeAll();

			try {
				$this->importSitesFromPackage($packageKey);
			} catch (\Exception $e) {
				throw new \F3\FLOW3\Exception('Error: During import an exception occured. ' . $e->getMessage());
			}
		}
	}

	/**
	 * Parses the Content.xml in the given package and imports the content into TYPO3.
	 *
	 * @param string $packageKey
	 * @return void
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	protected function importSitesFromPackage($packageKey) {
		$xml = new \SimpleXMLElement(file_get_contents('resource://' . $packageKey . '/Private/Content/Sites.xml'));
		foreach ($xml->structure as $site) {
			$siteNode = $this->objectManager->create((string)$site['type']);
			$siteNode->setNodeName((string)$site['nodename']);
			$siteNode->setName((string)$site->name);
			$siteNode->setState((integer)$site->state);
			$siteNode->setSiteResourcesPackageKey($packageKey);
			$this->parseSections($site->section, $siteNode);
			$this->siteRepository->add($siteNode);
		}
	}

	/**
	 * Iterates over the sections and adds the structure and content found to
	 * the $referencingNode.
	 *
	 * @param \SimpleXMLElement $sections
	 * @param \F3\TYPO3\Domain\Model\Structure\NodeInterface $referencingNode
	 * @return void
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	protected function parseSections(\SimpleXMLElement $sections, \F3\TYPO3\Domain\Model\Structure\NodeInterface $referencingNode) {
		foreach ($sections as $section) {
			$sectionName = (string)$section['name'];
			foreach ($section->structure as $structure) {
				$locale = $this->objectManager->create('F3\FLOW3\I18n\Locale', (string)$structure['locale']);
				$structureNode = $this->objectManager->create((string)$structure['type']);
				$structureNode->setNodeName((string)$structure['nodename']);
				$referencingNode->addChildNode($structureNode, $locale, $sectionName);
				if ($structure->content) {
					$this->createContentObject($structure->content, $structureNode);
				}
				$this->parseSections($structure->section, $structureNode);
			}
		}
	}

	/**
	 * Creates a content object attached to the $structureNode.
	 *
	 * @param \SimpleXMLElement $content
	 * @param \F3\TYPO3\Domain\Model\Structure\NodeInterface $structureNode
	 * @return void
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	protected function createContentObject(\SimpleXMLElement $content, \F3\TYPO3\Domain\Model\Structure\NodeInterface $structureNode) {
		$contentNode = $this->objectManager->create((string)$content['type'], $this->objectManager->create('F3\FLOW3\I18n\Locale', (string)$content['locale']), $structureNode);

		foreach ($content->children() as $child) {
		 if (\F3\FLOW3\Reflection\ObjectAccess::isPropertySettable($contentNode, $child->getName())) {
			 \F3\FLOW3\Reflection\ObjectAccess::setProperty($contentNode, $child->getName(), (string)$child);
		 }
		}
	}

}
?>