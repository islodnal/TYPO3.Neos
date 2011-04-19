<?php
declare(ENCODING = 'utf-8');
namespace F3\TYPO3\Tests\Functional\Controller\Backend;

/*                                                                        *
 * This script belongs to the FLOW3 framework.                            *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License as published by the *
 * Free Software Foundation, either version 3 of the License, or (at your *
 * option) any later version.                                             *
 *                                                                        *
 * This script is distributed in the hope that it will be useful, but     *
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHAN-    *
 * TABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Lesser       *
 * General Public License for more details.                               *
 *                                                                        *
 * You should have received a copy of the GNU Lesser General Public       *
 * License along with the script.                                         *
 * If not, see http://www.gnu.org/licenses/lgpl.html                      *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use \F3\TYPO3\Domain\Model\User;

/**
 * Testcase for method security of the backend controller
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */
class BackendControllerSecurityTest extends \F3\FLOW3\Tests\FunctionalTestCase {



	/**
	 * We need to enable this, so that the database is set up. Otherwise
	 * there will be an error along the lines of:
	 *  "Table 'functional_tests.domain' doesn't exist"
	 *
	 * @var boolean
	 */
	static protected $testablePersistenceEnabled = TRUE;

	/**
	 * @var boolean
	 */
	protected $testableSecurityEnabled = TRUE;

	/**
	 * @test
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 */
	public function indexActionIsGrantedForAdministrator() {
		$user = new User();
		$user->getPreferences()->set('context.workspace', 'user-admin');

		$account = $this->authenticateRoles(array('Administrator'));
		$account->setParty($user);
		$this->sendWebRequest('Backend\Backend', 'TYPO3', 'index');
	}

	/**
	 * @test
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 * @expectedException \F3\FLOW3\Security\Exception\AccessDeniedException
	 */
	public function indexActionIsDeniedForEverybody() {
		$this->sendWebRequest('Backend\Backend', 'TYPO3', 'index');
	}
}