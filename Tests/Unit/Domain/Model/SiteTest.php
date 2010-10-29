<?php
declare(ENCODING = 'utf-8');
namespace F3\TYPO3\Domain\Model;

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
 * Testcase for the "Site" domain model
 *
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class SiteTest extends \F3\Testing\BaseTestCase {

	/**
	 * @test
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function aNameCanBeSetAndRetrievedFromTheSite() {
		$site = new \F3\TYPO3\Domain\Model\Site('');
		$site->setName('My cool website');
		$this->assertSame('My cool website', $site->getName());
	}

	/**
	 * @test
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function theDefaultStateOfASiteIsOnline() {
		$site = new \F3\TYPO3\Domain\Model\Site('');
		$this->assertSame(\F3\TYPO3\Domain\Model\Site::STATE_ONLINE, $site->getState());
	}

	/**
	 * @test
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function theStateCanBeSetAndRetrieved() {
		$site = new \F3\TYPO3\Domain\Model\Site('');
		$site->setState(\F3\TYPO3\Domain\Model\Site::STATE_OFFLINE);
		$this->assertSame(\F3\TYPO3\Domain\Model\Site::STATE_OFFLINE, $site->getState());
	}

	/**
	 * @test
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function theSiteResourcesPackageKeyCanBeSetAndRetrieved() {
		$site = new \F3\TYPO3\Domain\Model\Site('');
		$site->setSiteResourcesPackageKey('Foo');
		$this->assertSame('Foo', $site->getSiteResourcesPackageKey());
	}

}

?>