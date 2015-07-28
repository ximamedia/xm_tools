<?php

namespace Xima\XmTools\Classes\Typo3;

/**
 * Helper for FeUser.
 *
 * @author Steve Lenz <sle@xima.de>, Sebastian Gierth <sgi@xima.de>
 */
class FeUser
{
    /**
     * Checks if fe_user is authenticated.
     *
     * @return bool
     */
    public function feUserIsAuthenticated()
    {
        if (empty($GLOBALS['TSFE']->fe_user->user['uid'])) {
            return false;
        }

        return true;
    }

    /**
     * Checks if fe_user is authenticated and has given role.
     *
     * @param string $role
     *
     * @return bool
     */
    public function feUserHasRole($role)
    {
        if ($this->feUserIsAuthenticated() && in_array($role, $GLOBALS['TSFE']->fe_user->groupData['title'])) {
            return true;
        }

        return false;
    }

    /**
     * Checks if fe_user is authenticated and has given role id.
     *
     * @param int $role
     *
     * @return bool
     */
    public function feUserHasRoleId($role)
    {
        if ($this->feUserIsAuthenticated() && in_array($role, $GLOBALS['TSFE']->fe_user->groupData['uid'])) {
            return true;
        }

        return false;
    }
}
